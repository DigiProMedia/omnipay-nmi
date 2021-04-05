<?php
declare(strict_types=1);

namespace Omnipay\NMI\Message;


class Transactions extends AbstractRequest
{
    protected $responseClass = BatchQueryResponse::class;
    protected $endpoint = 'https://secure.networkmerchants.com/api/query.php';

    public function getData()
    {
        return [
            'username' => $this->getUsername(),
            'password' => $this->getPassword(),
            'date' => $this->getDate(),
            'type' => 'settlement_batch',
            'verbose' => true,
            'status' => 'settled' //settled, not_settled
        ];
    }

    protected function getResponseBody($httpResponse) {
        return $httpResponse->xml();
    }


    public function getStartDate()
    {
        return $this->getDate();
    }

    public function setStartDate($value)
    {
        return $this->setDate($value);
    }

    public function getDate()
    {
        return $this->getParameter('date');
    }

    public function setDate($value)
    {
        return $this->setParameter('date', $value);
    }
}
