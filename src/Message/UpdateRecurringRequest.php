<?php
declare(strict_types = 1);
namespace Omnipay\NMI\Message;

use Guzzle\Http\Exception\BadResponseException;
use RecurringPayment\RecurringPayment as RecurringPayment;
use RecurringPayment\Payment as Payment;

class UpdateRecurringRequest extends RecurringRequest {


    protected function createPaymentFromData(array $data) {
        $recurringPayments = new RecurringPayment();
        $newPayment = $recurringPayments->getPayment($data['id'], true);
        foreach ($data as $key => $value) {
            if ($value !== null) {
                $newPayment->$key = $value;
                if($key === 'frequency'){
                    $newPayment->frequency_id = null;
                }
            }
        }
        return $newPayment;
    }

    protected function getRecurringPaymentFunction() {
        return 'updatePayment';
    }

    protected function verifyRequiredParameters() {
        $this->validate('recurringReference');
    }
}
