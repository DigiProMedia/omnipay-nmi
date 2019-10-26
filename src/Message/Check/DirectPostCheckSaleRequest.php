<?php

namespace Omnipay\NMI\Message\Check;

use Omnipay\NMI\Message\DirectPostAuthRequest;

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

    protected function getPaymentData(): array
    {
        $this->validateBankAccount();
        return [
           'checkname' => $this->getBankAccount()['name'],
           'checkaba' => $this->getBankAccount()['routingNumber'],
           'checkaccount' => $this->getBankAccount()['number']
        ];
    }

    protected function getPaymentReference()
    {
        return $this->getCheckReference();
    }
}
