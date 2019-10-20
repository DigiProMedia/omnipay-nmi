<?php

namespace Omnipay\NMI\Message;

/**
* NMI Direct Post Create Card Request
*/
class DirectPostCreateCheckRequest extends AbstractRequest
{
    protected $customer_vault = 'add_customer';

    public function getData()
    {

        $data = $this->getBaseData();

        $checkData = $this->getCheckPaymentData();
        $data['payment'] = 'check';

        if ('update_customer' === $this->customer_vault) {
            $data['customer_vault_id'] = $this->getCheckReference();
        }

        return array_merge(
            $checkData,
            $data,
            $this->getBillingData(),
            $this->getShippingData()
        );
    }
}
