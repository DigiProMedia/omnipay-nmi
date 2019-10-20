<?php
declare(strict_types=1);

namespace Omnipay\NMI\Message;

use Omnipay\NMI\Gateway;
use RecurringPayment\Payment as Payment;
use RecurringPayment\RecurringPayment as RecurringPayment;
use RecurringPayment\ScheduledTask as RecurringPaymentRunner;

/**
 * NMI Purchase Request.
 */
class RecurringCheckRequest extends RecurringRequest
{
    protected function getPaymentReferenceName(): string
    {
        return 'card_reference';
    }

    protected function getPaymentReference()
    {
        return $this->getCheckReference();
    }
}
