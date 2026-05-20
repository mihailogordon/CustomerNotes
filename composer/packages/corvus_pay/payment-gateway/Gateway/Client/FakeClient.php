<?php

namespace CorvusPay\PaymentGateway\Gateway\Client;

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

/**
 * Class FakeClient fakes sending a client request.
 */
class FakeClient implements ClientInterface
{
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
        return $transferObject->getBody();
    }
}
