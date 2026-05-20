<?php

namespace CorvusPay\PaymentGateway\Gateway\Command;

use Magento\Framework\Exception\NotFoundException;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Command\ResultInterface;
use Magento\Payment\Gateway\CommandInterface;

/**
 * Class FetchTransactionInfoCommand fetches transaction info.
 */
class FetchTransactionInfoCommand implements CommandInterface
{
    private $commandPool;

    /**
     * @param CommandPoolInterface $commandPool
     */
    public function __construct(
        CommandPoolInterface $commandPool
    ) {
        $this->commandPool = $commandPool;
    }

    /**
     * @inheritdoc
     *
     * @param array $commandSubject
     *
     * @return array|ResultInterface|null
     * @throws NotFoundException
     * @throws CommandException
     */
    public function execute(array $commandSubject)
    {
        $command  = $this->commandPool->get('status');
        $result   = $command->execute($commandSubject);
        $response = $result->get();

        $xml = simplexml_load_string($response['response']);
        $data = array_map(function ($element) {
            return (string)$element;
        }, (array)$xml);

        $responseCode = (string)$xml->{'response-code'};

        if ('0' === $responseCode || '300' === $responseCode) {
            $command  = $this->commandPool->get('status-pis');
            $result   = $command->execute($commandSubject);
            $response = $result->get();
            $pisXml = simplexml_load_string($response['response']);
            $pisData = array_map(function ($element) {
                return (string)$element;
            }, (array)$pisXml);
            $data = array_merge($pisData, $data);
        }

        return $data;
    }
}
