<?php

namespace Omnipay\NMI\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;

/**
 * NMI Direct Post Response
 */
class QueryResponse extends AbstractResponse
{
    public function __construct(RequestInterface $request, $data)
    {
        $this->request = $request;
        $this->data = json_decode(json_encode($data),true)['transaction'] ?? [];
        $this->actions = $this->data['action'] ?? [];
    }

    public function isSuccessful()
    {
        return count($this->data) > 0;
    }

    public function getCode()
    {
        return $this->getResponseCode();
    }

    public function getResponseCode()
    {
        return end($this->actions)['response_code'];
    }

    public function getMessage()
    {
        return end($this->actions)['response_text'];
    }

    public function getAuthorizationCode()
    {
        return null;
    }

    public function getAVSResponse()
    {
        return null;
    }

    public function getCVVResponse()
    {
        return null;
    }

    public function getOrderId()
    {
        return null;
    }

    public function getTransactionReference()
    {
        if (isset($this->data['transaction_id'])) {
            return trim($this->data['transaction_id']);
        }

        return null;
    }

    public function getCardReference()
    {
        return null;
    }

    public function getCheckReference()
    {
        return null;
    }
}
