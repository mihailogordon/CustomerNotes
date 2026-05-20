<?php

namespace CorvusPay\PaymentGateway\Gateway\Client;


use CorvusPay\ApiRequestor;
use CorvusPay\CorvusPayClient;
use CorvusPay\PaymentGateway\Gateway\Options;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Filesystem\DirectoryList;

/**
 * Class CurlClient implements a curl client.
 */
class CurlClient implements ClientInterface
{
    /** @var Curl */
    private $curl;

    /** @var Options */
    private $options;

    /** @var LoggerInterface */
    private $logger;

    /**
     * CorvusPay Client.
     *
     * @var CorvusPayClient CorvusPay Client.
     */
    private $client;

    /**
     * CurlClient constructor.
     *
     * @param  ConfigInterface     $config
     * @param  EncryptorInterface  $encryptor
     * @param  LoggerInterface     $logger
     * @param  Curl                $curl
     * @param  DirectoryList       $dir
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        ConfigInterface $config,
        EncryptorInterface $encryptor,
        LoggerInterface $logger,
        Curl $curl,
        DirectoryList $dir
    ) {
        $this->options = new Options($config, $encryptor);
        $this->logger  = $logger;
        $this->curl    = $curl;

        $curl_options = [
            CURLOPT_POST           => 1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSLCERT        => $dir->getPath(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA) . '/corvuspay/'
                                      . $this->options->environment . '/'
                                      . $this->options->certificate[$this->options->environment]['certificate'],
            CURLOPT_KEYPASSWD      => $this->options->certificate[$this->options->environment]['password'],
            CURLOPT_SSLCERTTYPE    => 'P12',
            CURLOPT_SSL_VERIFYPEER => true,
        ];

        $this->curl->setOptions($curl_options);

        //Config sdk.
        $config_params = ['store_id'    => $this->options->store_id[ $this->options->environment ],
                          'secret_key'  => $this->options->secret_key[ $this->options->environment ],
                          'environment' => $this->options->environment,
                          'logger'      => $this->logger];
        $this->client  = new CorvusPayClient($config_params);
        $fp = fopen($dir->getPath(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA) . '/corvuspay/' . $this->options->environment . '/'
            . $this->options->certificate[ $this->options->environment ]['certificate'], 'r');
        $this->client->setCertificate($fp, $this->options->certificate[ $this->options->environment ]['password'], $this->options->environment);

    }

    /**
     * Places request to gateway. Returns result as ENV array
     *
     * @param TransferInterface $transferObject
     *
     * @return array
     */
    public function placeRequest(
        TransferInterface $transferObject
    ) {
        $request = $transferObject->getBody();

        switch ($request['api_endpoint']) {
            case ApiRequestor::API_ENDPOINTS[$this->options->environment] . "next_sub_payment":
                $body = $this->client->subscription->pay($request['parameters'],true);
                break;
            case ApiRequestor::API_ENDPOINTS[$this->options->environment] . "refund":
                $body = $this->client->transaction->refund($request['parameters'], true);
                break;
            case ApiRequestor::API_ENDPOINTS[$this->options->environment] . "partial_refund":
                $body = $this->client->transaction->partiallyRefund($request['parameters'], true);
                break;
            case ApiRequestor::API_ENDPOINTS[$this->options->environment] . "complete":
                $body = $this->client->transaction->complete($request['parameters'], true);
                break;
            case ApiRequestor::API_ENDPOINTS[$this->options->environment] . "partial_complete":
                $body = $this->client->transaction->partiallyComplete($request['parameters'], true);
                break;
            case ApiRequestor::API_ENDPOINTS[$this->options->environment] . "cancel":
                $body = $this->client->transaction->cancel($request['parameters'], true);
                break;
            case ApiRequestor::API_ENDPOINTS[$this->options->environment] . "status":
                $body = $this->client->transaction->status($request['parameters']);
                break;
            case ApiRequestor::API_ENDPOINTS[$this->options->environment] . "check_pis_status":
                $body = $this->client->pisTransaction->status($request['parameters']);
                break;
            default:
                $this->curl->post($request['api_endpoint'], $request['parameters']);
                $body = $this->curl->getBody();
        }

        $this->logger->debug(
            'CorvusPay API query',
            ['api_endpoint' => $request['api_endpoint'], 'parameters' => $request['parameters'], 'result' => $body]
        );

        return [
            'response' => $body,
        ];
    }
}
