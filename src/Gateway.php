<?php

namespace Omnipay\NMI;

use Omnipay\Common\AbstractGateway;
use Omnipay\NMI\Message\DeleteRecurringRequest;
use Omnipay\NMI\Message\DirectPostAuthRequest;
use Omnipay\NMI\Message\DirectPostCaptureRequest;
use Omnipay\NMI\Message\DirectPostCardSaleRequest;
use Omnipay\NMI\Message\DirectPostCreateCardRequest;
use Omnipay\NMI\Message\DirectPostCreditRequest;
use Omnipay\NMI\Message\DirectPostDeleteCardRequest;
use Omnipay\NMI\Message\DirectPostRefundRequest;
use Omnipay\NMI\Message\DirectPostUpdateCardRequest;
use Omnipay\NMI\Message\DirectPostVoidRequest;
use Omnipay\NMI\Message\RecurringRequest;
use Omnipay\NMI\Message\Transaction;
use Omnipay\NMI\Message\UpdateRecurringRequest;

/**
 * NMI Direct Post Gateway
 *
 * @link https://www.nmi.com/
 * @link https://gateway.perpetualpayments.com/merchants/resources/integration/integration_portal.php
 */
class Gateway extends AbstractGateway
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'NMI Direct Post';
    }

    /**
     * @return array
     */
    public function getDefaultParameters()
    {
        return [
           'username' => '',
           'password' => ''
        ];
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->getParameter('username');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setUsername($value)
    {
        return $this->setParameter('username', $value);
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->getParameter('password');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setPassword($value)
    {
        return $this->setParameter('password', $value);
    }

    /**
     * @return string
     */
    public function getProcessorId()
    {
        return $this->getParameter('processor_id');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setProcessorId($value)
    {
        return $this->setParameter('processor_id', $value);
    }

    /**
     * @return string
     */
    public function getAuthorizationCode()
    {
        return $this->getParameter('authorization_code');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setAuthorizationCode($value)
    {
        return $this->setParameter('authorization_code', $value);
    }

    /**
     * @return string
     */
    public function getDescriptor()
    {
        return $this->getParameter('descriptor');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setDescriptor($value)
    {
        return $this->setParameter('descriptor', $value);
    }

    /**
     * @return string
     */
    public function getDescriptorPhone()
    {
        return $this->getParameter('descriptor_phone');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setDescriptorPhone($value)
    {
        return $this->setParameter('descriptor_phone', $value);
    }

    /**
     * @return string
     */
    public function getDescriptorAddress()
    {
        return $this->getParameter('descriptor_address');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setDescriptorAddress($value)
    {
        return $this->setParameter('descriptor_address', $value);
    }

    /**
     * @return string
     */
    public function getDescriptorCity()
    {
        return $this->getParameter('descriptor_city');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setDescriptorCity($value)
    {
        return $this->setParameter('descriptor_city', $value);
    }

    /**
     * @return string
     */
    public function getDescriptorState()
    {
        return $this->getParameter('descriptor_state');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setDescriptorState($value)
    {
        return $this->setParameter('descriptor_state', $value);
    }

    /**
     * @return string
     */
    public function getDescriptorPostal()
    {
        return $this->getParameter('descriptor_postal');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setDescriptorPostal($value)
    {
        return $this->setParameter('descriptor_postal', $value);
    }

    /**
     * @return string
     */
    public function getDescriptorCountry()
    {
        return $this->getParameter('descriptor_country');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setDescriptorCountry($value)
    {
        return $this->setParameter('descriptor_country', $value);
    }

    /**
     * @return string
     */
    public function getDescriptorMcc()
    {
        return $this->getParameter('descriptor_mcc');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setDescriptorMcc($value)
    {
        return $this->setParameter('descriptor_mcc', $value);
    }

    /**
     * @return string
     */
    public function getDescriptorMerchantId()
    {
        return $this->getParameter('descriptor_merchant_id');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setDescriptorMerchantId($value)
    {
        return $this->setParameter('descriptor_merchant_id', $value);
    }

    /**
     * @return string
     */
    public function getDescriptorUrl()
    {
        return $this->getParameter('descriptor_url');
    }

    public function setDescriptorUrl($value)
    {
        return $this->setParameter('descriptor_url', $value);
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setEndpoint($value)
    {
        return $this->setParameter('endpoint', $value);
    }

    public function purchase(array $parameters = [])
    {
        return $this->sale($parameters);
    }

    public function authorize(array $parameters = [])
    {
        return $this->auth($parameters);
    }

    /**
     * Transaction sales are submitted and immediately flagged for settlement.
     * @param array $parameters
     * @return DirectPostSaleRequest
     */
    public function sale(array $parameters = [])
    {
        return $this->createRequest(DirectPostCardSaleRequest::class, $parameters);
    }

    /**
     * Transaction authorizations are authorized immediately but are not flagged
     * for settlement. These transactions must be flagged for settlement using
     * the capture transaction type. Authorizations typically remain active for
     * three to seven business days.
     * @param array $parameters
     * @return \Omnipay\NMI\Message\DirectPostAuthRequest
     */
    public function auth(array $parameters = [])
    {
        return $this->createRequest(DirectPostAuthRequest::class, $parameters);
    }

    /**
     * Transaction captures flag existing authorizations for settlement.
     * Only authorizations can be captured. Captures can be submitted for an
     * amount equal to or less than the original authorization.
     * @param array $parameters
     * @return \Omnipay\NMI\Message\DirectPostCaptureRequest
     */
    public function capture(array $parameters = [])
    {
        return $this->createRequest(DirectPostCaptureRequest::class, $parameters);
    }

    /**
     * Transaction voids will cancel an existing sale or captured authorization.
     * In addition, non-captured authorizations can be voided to prevent any
     * future capture. Voids can only occur if the transaction has not been settled.
     * @param array $parameters
     * @return \Omnipay\NMI\Message\DirectPostVoidRequest
     */
    public function void(array $parameters = [])
    {
        return $this->createRequest(DirectPostVoidRequest::class, $parameters);
    }

    /**
     * Transaction refunds will reverse a previously settled transaction. If the
     * transaction has not been settled, it must be voided instead of refunded.
     * @param array $parameters
     * @return \Omnipay\NMI\Message\DirectPostRefundRequest
     */
    public function refund(array $parameters = [])
    {
        return $this->createRequest(DirectPostRefundRequest::class, $parameters);
    }

    /**
     * Transaction credits apply an amount to the cardholder's card that was not
     * originally processed through the Gateway. In most situations credits are
     * disabled as transaction refunds should be used instead.
     * @param array $parameters
     * @return \Omnipay\NMI\Message\DirectPostCreditRequest
     */
    public function credit(array $parameters = [])
    {
        return $this->createRequest(DirectPostCreditRequest::class, $parameters);
    }

    /**
     * @param array $parameters
     * @return \Omnipay\NMI\Message\CreateCardRequest
     */
    public function createCard(array $parameters = [])
    {
        return $this->createRequest(DirectPostCreateCardRequest::class, $parameters);
    }

    /**
     * @param array $parameters
     * @return \Omnipay\NMI\Message\UpdateCardRequest
     */
    public function updateCard(array $parameters = [])
    {
        return $this->createRequest(DirectPostUpdateCardRequest::class, $parameters);
    }

    /**
     * @param array $parameters
     * @return \Omnipay\NMI\Message\DeleteCardRequest
     */
    public function deleteCard(array $parameters = [])
    {
        return $this->createRequest(DirectPostDeleteCardRequest::class, $parameters);
    }

    public function createRecurring(array $params = [])
    {
        return $this->createRequest(RecurringRequest::class, $params);
    }

    public function updateRecurring(array $params = [])
    {
        return $this->createRequest(UpdateRecurringRequest::class, $params);
    }

    public function deleteRecurring(array $params = [])
    {
        return $this->createRequest(DeleteRecurringRequest::class, $params);
    }

    public function transaction(array $params = [])
    {
        return $this->createRequest(Transaction::class, $params);
    }
}
