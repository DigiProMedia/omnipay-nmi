<?php

namespace Omnipay\NMI\Message;

/**
 * NMI Direct Post Authorize Request
 */
class DirectPostCheckAuthRequest extends DirectPostAuthRequest
{
    protected function getPaymentData(): array
    {
        return $this->getCheckPaymentData();
    }

    public function getData()
    {
        $data = parent::getData();
        $data['payment'] = 'check';
        return $data;
    }
}
