<?php
declare(strict_types = 1);
namespace Omnipay\NMI\Message\Check;

use Omnipay\NMI\Message\UpdateRecurringRequest;

class UpdateRecurringCheckRequest extends UpdateRecurringRequest {
    protected function getGatewayName()
    {
        return 'NMI_Check';
    }
}
