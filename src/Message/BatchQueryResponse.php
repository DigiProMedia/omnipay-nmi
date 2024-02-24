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

    public function getTransactions()
    {
        $batchNumber = $this->getRequest()->getBatchNumber();
        $formattedData = [];
        foreach ($this->getData() as $transaction) {
            if($batchNumber !== null && $transaction->getBatchNumber() !== $batchNumber) {
                continue;
            }
            $formattedTransaction = $transaction->data;
            $formattedTransaction['transactionType'] = $transaction->getTransactionType();
            $formattedTransaction['isSuccessful'] = $transaction->isSuccessful();
            $formattedTransaction['code'] = $transaction->getCode();
            $formattedTransaction['responseCode'] = $transaction->getResponseCode();
            $formattedTransaction['message'] = $transaction->getMessage();
            $formattedTransaction['authorizationCode'] = $transaction->getAuthorizationCode();
            $formattedTransaction['avsResponse'] = $transaction->getAVSResponse();
            $formattedTransaction['cvvResponse'] = $transaction->getCVVResponse();
            $formattedTransaction['orderId'] = $transaction->getOrderId();
            $formattedTransaction['transactionType'] = $transaction->getTransactionType();
            $formattedTransaction['transactionReference'] = $transaction->getTransactionReference();
            $formattedTransaction['cardReference'] = $transaction->getCardReference();
            $formattedTransaction['checkReference'] = $transaction->getCheckReference();
            $formattedTransaction['amount'] = $transaction->getAmount();
            $formattedTransaction['isPending'] = $transaction->isPending();
            $formattedTransaction['canVoid'] = $transaction->canVoid();
            $formattedTransaction['isVoidable'] = $transaction->isVoidable();
            $formattedTransaction['isVoided'] = $transaction->isVoided();
            $formattedTransaction['isRefunded'] = $transaction->isRefunded();
            $formattedTransaction['isRefundable'] = $transaction->isRefundable();
            $formattedTransaction['canRefund'] = $transaction->canRefund();
            $formattedTransaction['state'] = $transaction->getState();
            $formattedTransaction['settlementDate'] = $transaction->getSettlementDate();
            $formattedTransaction['batchNumber'] = $transaction->getBatchNumber();
            $formattedTransaction['isSettled'] = $transaction->isSettled();
            $formattedTransaction['isDeclined'] = $transaction->isDeclined();
            $formattedTransaction['isCancelled'] = $transaction->isCancelled();
            $formattedTransaction['data'] = $transaction->getData();
            $formattedTransaction['transactionId'] = $transaction->getTransactionId();
            $formattedData[] = $formattedTransaction;
        }
        return $formattedData;
    }

}
