<?php

namespace Omnipay\NMI;

use Omnipay\Common\AbstractGateway;
use Omnipay\NMI\Message\Check\DirectPostCheckRefundRequest;
use Omnipay\NMI\Message\Check\DirectPostCheckSaleRequest;
use Omnipay\NMI\Message\Check\DirectPostCheckVoidRequest;
use Omnipay\NMI\Message\Check\DirectPostCreateCheckRequest;
use Omnipay\NMI\Message\Check\DirectPostUpdateCheckRequest;
use Omnipay\NMI\Message\Check\RecurringCheckRequest;
use Omnipay\NMI\Message\Check\DirectPostDeleteCheckRequest;
use Omnipay\NMI\Message\Check\UpdateRecurringCheckRequest;
use Omnipay\NMI\Message\Transactions;

/**
 * NMI Direct Post Gateway
 *
 * @link https://www.nmi.com/
 * @link https://gateway.perpetualpayments.com/merchants/resources/integration/integration_portal.php
 */
class CheckGateway extends Gateway
{

    /**
     * Transaction voids will cancel an existing sale or captured authorization.
     * In addition, non-captured authorizations can be voided to prevent any
     * future capture. Voids can only occur if the transaction has not been settled.
     * @param array $parameters
     * @return \Omnipay\NMI\Message\DirectPostVoidRequest
     */
    public function void(array $parameters = [])
    {
        return $this->createRequest(DirectPostCheckVoidRequest::class, $parameters);
    }

    public function refund(array $parameters = [])
    {
        return $this->createRequest(DirectPostCheckRefundRequest::class, $parameters);
    }

    public function createCheck(array $parameters = [])
    {
        return $this->createRequest(DirectPostCreateCheckRequest::class, $parameters);
    }

    public function createRecurring(array $params = [])
    {
        return $this->createRequest(RecurringCheckRequest::class, $params);
    }

    public function updateRecurring(array $params = [])
    {
        return $this->createRequest(UpdateRecurringCheckRequest::class, $params);
    }

    public function sale(array $parameters = [])
    {
        return $this->createRequest(DirectPostCheckSaleRequest::class, $parameters);
    }

    public function updateCheck(array $parameters = [])
    {
        return $this->createRequest(DirectPostUpdateCheckRequest::class, $parameters);
    }

    public function deleteCheck(array $parameters = [])
    {
        return $this->createRequest(DirectPostDeleteCheckRequest::class, $parameters);
    }
}
