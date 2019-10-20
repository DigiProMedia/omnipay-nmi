<?php

namespace Omnipay\NMI\Message;

/**
 * NMI Direct Post Sale Request
 */
class DirectPostCheckSaleRequest extends DirectPostAuthRequest
{
    protected $type = 'sale';

    public function getData()
    {
        $data = parent::getData();
        $data['payment'] = 'check';
        return $data;
    }

    //TODO: Remove Duplicate code below
    protected function getPaymentData(): array
    {
        $this->validateBankAccount();
        return [
           'checkname' => $this->getBankAccount()['name'],
           'checkaba' => $this->getBankAccount()['routingNumber'],
           'checkaccount' => $this->getBankAccount()['number']
        ];
    }
}
