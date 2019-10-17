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

    protected function getPaymentData(): array
    {
        $this->getCheck()->validate();

        return [
           'checkname' => $this->getCheck()->getAccountName(),
           'checkaba' => $this->getCheck()->getRoutingNumber(),
           'checkaccount' => $this->getCheck()->getAccountNumber(),
        ];
    }
}
