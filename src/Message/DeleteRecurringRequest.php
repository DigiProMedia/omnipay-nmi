<?php
declare(strict_types = 1);
namespace Omnipay\NMI\Message;

use Guzzle\Http\Exception\BadResponseException;
use Omnipay\NMI\Message\AbstractRequest;
use Omnipay\NMI\Message\RecurringResponse;
use RecurringPayment\RecurringPayment as RecurringPayment;
use RecurringPayment\Payment as Payment;

class DeleteRecurringRequest extends AbstractRequest {
    public function getData() {
        $this->validate('recurringReference');
        $data = ['recurring_reference' => $this->getRecurringReference()];
        return $data;
    }

    public function sendData($data) {
        $recurringPayments = new RecurringPayment();
        $success = $recurringPayments->deletePayment($data['recurring_reference']);
        $responseData['recurring_id'] = $data['recurring_reference'];
        $responseData['successful'] = $success;
        if(!$success) {
            $responseData['responsetext'] = 'Recurring payment not found.';
            $responseData['response_code'] = '0';
            $responseData['response'] = '0';
        } else {
            $responseData['responsetext'] = 'Recurring payment deleted successfully.';
            $responseData['response_code'] = '0';
            $responseData['response'] = '0';
        }
        return new RecurringResponse($this, $responseData);
    }

    public function getRecurringReference() {
        return $this->getParameter('recurringReference');
    }

    public function setRecurringReference($value) {
        return $this->setParameter('recurringReference', $value);
    }
}
