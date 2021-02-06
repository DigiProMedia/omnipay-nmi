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
        $this->data = json_decode(json_encode($data), true)['transaction'] ?? [];
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

    public function getTransactionType()
    {
        //AUTHORIZATION, STR/FWD, REFUND, CAPTURE, FORCE,
        if ($this->isVoided()) {
            return 'VOID';
        } else if ($this->isRefunded()) {
            return 'REFUND';
        } else if ($this->isPending()) {
            return 'PENDING';
        } else if ($this->isSettled()) {
            return 'SETTLED';
        } else if ($this->isDeclined()) {
            return 'DECLINED';
        }
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
        return null; //customer_vault_id
    }

    public function getCheckReference()
    {
        return null; //customer_vault_id
    }

    public function getAmount()
    {
        return $this->actions[0]['amount'] ?? null;
    }

    public function isPending()
    {
        return $this->getState() === 'pending' ||
            $this->getState() === 'pendingsettlement';
    }

    public function canVoid()
    {
        return $this->isVoidable();
    }

    public function isVoidable()
    {
        return $this->getState() === 'complete';
    }

    public function isVoided()
    {
        return $this->getState() === 'canceled';
    }

    public function isRefunded()
    {
        return $this->getState() === 'canceled';
    }

    public function isRefundable()
    {
        return $this->isPending();
    }

    public function canRefund()
    {
        return $this->isRefundable();
    }

    public function getState()
    {
        return $this->data['condition'] ?? null;
    }

    public function getSettlementDate()
    {
        foreach ($this->actions as $action) {
            if ($action['action_type'] === 'settle') {
                return date_create($action['date'], timezone_open('UTC'))->format(DATE_ATOM);
            }
        }
        return null;
    }

    public function getBatchNumber()
    {
        foreach ($this->actions as $action) {
            if (array_key_exists('action_type', $action) && $action['action_type'] === 'settle') {
                return $action['batch_id'];
            }
        }
        return null;
    }

    public function isSettled()
    {
        return $this->getState() === 'complete';
    }

    public function isDeclined()
    {
        return $this->getState() === 'failed';
    }

}
