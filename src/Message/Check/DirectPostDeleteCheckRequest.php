<?php

namespace Omnipay\NMI\Message\Check;

use Omnipay\NMI\Message\AbstractRequest;

class DirectPostDeleteCheckRequest extends AbstractRequest
{
    protected $customer_vault = 'delete_customer';

    public function getData()
    {
        $this->validate('checkReference');
        $data = $this->getBaseData();
        $data['customer_vault_id'] = $this->getCheckReference();
        $data['payment'] = 'check';

        return $data;
    }
}
