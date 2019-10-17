<?php

namespace Omnipay\NMI\Message;

/**
 * NMI Direct Post Authorize Request
 */
class DirectPostAuthRequest extends AbstractRequest
{
    protected $type = 'auth';

    public function getData()
    {
        $this->validate('amount');

        $data = $this->getBaseData();
        $data['amount'] = $this->getAmount();

        if ($this->getPaymentReference()) {
            $data['customer_vault_id'] = $this->getPaymentReference();
            return $data;
        }

        $paymentData = $this->getPaymentData();

        return array_merge(
           $data,
           $paymentData,
           $this->getOrderData(),
           $this->getBillingData(),
           $this->getShippingData()
        );
    }

    protected function getPaymentReference()
    {
        return $this->getCardReference();
    }

    protected function getPaymentData(): array
    {
        $this->getCard()->validate();

        return [
           'ccnumber' => $this->getCard()->getNumber(),
           'ccexp' => $this->getCard()->getExpiryDate('my'),
           'cvv' => $this->getCard()->getCvv(),
        ];
    }
}
