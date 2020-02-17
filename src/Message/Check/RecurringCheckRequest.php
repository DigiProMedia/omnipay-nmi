<?php
declare(strict_types=1);

namespace Omnipay\NMI\Message\Check;

use Omnipay\NMI\Message\RecurringRequest;

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

    protected function getGatewayName()
    {
        return 'NMI_Check';
    }
}
