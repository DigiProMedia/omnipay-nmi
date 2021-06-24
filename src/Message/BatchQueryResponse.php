<?php

namespace Omnipay\NMI\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;

class BatchQueryResponse extends AbstractResponse
{
    public function __construct(RequestInterface $request, $data)
    {
        $this->request = $request;
        $unformattedData = json_decode(json_encode($data), true)['transaction'] ?? [];
        $this->data = [];
        foreach ($unformattedData as $transaction) {
            $this->data[] = new QueryResponse($request, $transaction);
        }
    }

    public function isSuccessful()
    {
        return count($this->data) > 0;
    }

    public function getCode()
    {
        return null;
    }

    public function getResponseCode()
    {
        return null;
    }

    public function getMessage()
    {
        return null;
    }

    public function getTransactions() {
        return $this->data;
    }

}
