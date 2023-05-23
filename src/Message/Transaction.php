<?php
declare(strict_types=1);

namespace Omnipay\NMI\Message;


class Transaction extends AbstractRequest
{
    protected $responseClass = QueryResponse::class;
    protected $endpoint = 'https://secure.networkmerchants.com/api/query.php';

    public function getData()
    {
        return [
           'username' => $this->getUsername(),
           'password' => $this->getPassword(),
           'transaction_id' => $this->getTransactionReference(),
        ];
    }

    protected function getResponseBody($httpResponse) {
        return simplexml_load_string($httpResponse->getBody()->getContents());
    }


    public function getStartDate()
    {
        return $this->getParameter('startDate');
    }

    public function setStartDate($value)
    {
        return $this->setParameter('startDate', $value);
    }
}
