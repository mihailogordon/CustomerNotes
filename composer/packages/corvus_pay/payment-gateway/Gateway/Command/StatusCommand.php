<?php

namespace CorvusPay\PaymentGateway\Gateway\Command;

use Exception;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Command\Result\ArrayResult;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Validator\ValidatorInterface;

/**
 * Class StatusCommand fetches transaction status.
 */
class StatusCommand implements CommandInterface
{
    /**
     * @var BuilderInterface
     */
    private $requestBuilder;

    /**
     * @var TransferFactoryInterface
     */
    private $transferFactory;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * StatusCommand constructor.
     *
     * @param BuilderInterface $requestBuilder
     * @param TransferFactoryInterface $transferFactory
     * @param ClientInterface $client
     * @param ValidatorInterface|null $validator
     */
    public function __construct(
        BuilderInterface $requestBuilder,
        TransferFactoryInterface $transferFactory,
        ClientInterface $client,
        ?ValidatorInterface $validator = null
    ) {
        $this->requestBuilder     = $requestBuilder;
        $this->transferFactory    = $transferFactory;
        $this->client             = $client;
        $this->validator          = $validator;
    }

    /**
     * Execute.
     *
     * @param array $commandSubject
     *
     * @return ArrayResult
     * @throws CommandException
     */
    public function execute(array $commandSubject)
    {
        $transferObject = $this->transferFactory->create(
            $this->requestBuilder->build($commandSubject)
        );

        try {
            $response = $this->client->placeRequest($transferObject);
        } catch (Exception $e) {
            throw new CommandException(__('There was an error while trying to process the request.'));
        }

        if ($this->validator !== null) {
            $result = $this->validator->validate(
                array_merge($commandSubject, ['response' => $response])
            );
            if (!$result->isValid()) {
                throw new CommandException(__('Transaction has been declined. Please try again later.'));
            }
        }

        return new ArrayResult($response);
    }
}
