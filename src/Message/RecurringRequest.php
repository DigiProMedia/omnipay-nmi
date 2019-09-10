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
class RecurringRequest extends AbstractRequest
{
    public function getData()
    {
        $this->verifyRequiredParameters();

        $data = [
           'frequency' => $this->lookUpFrequency($this->getFrequency()),
           'start_date' => $this->getStartDate(),
           'next_date' => $this->getNextDate(),
           'description' => $this->getDescription() ?? 'Recurring Payment',
           'total_count' => $this->getTotalCount(),
           'amount' => $this->getAmount(),
           'card_reference' => $this->getPaymentReference(),
           'gateway' => $this->getGatewayName(),
           'gateway_url' => $this->getURL(),
           'gateway_username' => $this->getUserName(),
           'gateway_password' => $this->getPassword(),
           'test_mode' => $this->getTestMode() === true ? 1 : 0,
           'location_id' => $this->getLocationId(),
           'channel_id' => $this->getChannelId(),
           'sub_domain' => $this->getSubDomain(),
           'email' => $this->getEmail(),
           'invoice' => $this->getInvoice()
        ];

        if ($this->getRecurringReference() !== null) {
            $data['id'] = $this->getRecurringReference();
        }

        return $data;
    }

    protected function getGateway()
    {
        return new Gateway();
    }

    protected function getPaymentReference()
    {
        return $this->getCardReference();
    }

    protected function getGatewayName()
    {
        return 'NMI_CreditCard';
    }

    protected function setPaymentReference($newPayment)
    {
        $this->setCardReference($newPayment->card_reference);
    }

    protected function verifyRequiredParameters()
    {
        $this->validate('frequency', 'startDate', 'totalCount', 'email');
    }

    private function lookUpFrequency($value)
    {
        if ($value === null) {
            return null;
        }
        $value = strtolower($value);
        $frequencyLookUp = [
           strtolower('One-Time') => 'once',
           strtolower('once') => 'once',
           strtolower('useOnce') => 'once',
           strtolower('Weekly') => 'weekly',
           strtolower('Daily') => 'daily',
           strtolower('Bi-Weekly') => 'biweekly',
           strtolower('biweekly') => 'biweekly',
           strtolower('Monthly') => 'monthly',
           strtolower('Annually') => 'yearly',
           strtolower('Yearly') => 'yearly'
        ];

        return $frequencyLookUp[$value];
    }

    public function getFrequency()
    {
        return $this->getParameter('frequency');
    }

    public function getStartDate()
    {
        return $this->getParameter('startDate');
    }

    public function getTotalCount()
    {
        return $this->getParameter('totalCount');
    }

    private function getURL()
    {
        if (!isset($_SERVER['SERVER_NAME'])) {
            return 'http://localhost/';
        }
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $serverName = $_SERVER['SERVER_NAME'];
        $port = '';
        if (($protocol === 'https://' && $_SERVER['SERVER_PORT'] !== 443) || ($protocol === 'http://' && $_SERVER['SERVER_PORT'] !== 80)) {
            $port = ':' . $_SERVER['SERVER_PORT'];
        }
        return $protocol . $serverName . $port . '/';
    }

    public function getLocationId()
    {
        return $this->getParameter('locationID');
    }

    public function getSubDomain()
    {
        return $this->getParameter('subDomain');
    }

    public function getEmail()
    {
        return $this->getParameter('email');
    }

    public function getInvoice()
    {
        return $this->getParameter('invoice');
    }

    public function setInvoice($value)
    {
        return $this->setParameter('invoice', $value);
    }

    public function getCommission()
    {
        return $this->getParameter('commission');
    }

    public function sendData($data)
    {
        try {
            $newPayment = $this->createPaymentFromData($data);
            $recurringPayments = new RecurringPayment();
            $function = $this->getRecurringPaymentFunction();
            $newPayment = $recurringPayments->$function($newPayment);
            $now = date('Y-m-d');
            $responseData = [
               'successful' => true,
               'recurring_id' => $newPayment->id,
               'charged' => false,
               'responsetext' => '',
               'response' => 0
            ];
            if ($newPayment->next_date <= $now) {
                $recurringPaymentRunner = new RecurringPaymentRunner();
                $response = $recurringPaymentRunner->processPayment($newPayment);
                $responseData = array_merge_recursive($responseData, (array)$response['result']);
                $responseData['successful'] = $response['success'];
                $responseData['charged'] = $response['success'];
                if ($response['success']) {
                    $responseData['transactionid'] = $responseData['transactionReference'];
                } else {
                    $recurringPayments->deletePayment($newPayment);
                    $responseData['recurring_id'] = null;
                    if (isset($responseData['message'])) {
                        $responseData['responsetext'] = $responseData['message'];
                    }
                }
                if (isset($responseData['code'])) {
                    $responseData['response'] = $responseData['code'];
                }
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            if (strpos($message, 'Not Found') !== false) {
                $message = 'Payment Not Found';
            }
            $responseData = [
               'successful' => false,
               'recurring_id' => null,
               'charged' => false,
               'responsetext' => $message,
               'response' => 0
            ];
        }
        return new RecurringResponse($this, $responseData);
    }

    protected function createPaymentFromData(array $data)
    {
        $newPayment = new Payment();
        $additionalData = [];
        foreach ($data as $key => $value) {
            if ($key === 'SecondaryTransaction') {
                $additionalData['SecondaryTransaction'] = $value;
            } else {
                $newPayment->$key = $value;
            }
        }

        if (\count($additionalData) > 0) {
            $newPayment->additional_data = json_encode($additionalData);
        }
        return $newPayment;
    }

    protected function getRecurringPaymentFunction()
    {
        return 'addPayment';
    }

    public function setFrequency($value)
    {
        return $this->setParameter('frequency', $value);
    }

    public function setStartDate($value)
    {
        return $this->setParameter('startDate', $value);
    }

    public function getNextDate()
    {
        return $this->getParameter('nextDate');
    }

    public function setNextDate($value)
    {
        return $this->setParameter('nextDate', $value);
    }

    public function setTotalCount($value)
    {
        return $this->setParameter('totalCount', $value);
    }

    public function setLocationId($value)
    {
        return $this->setParameter('locationID', $value);
    }

    public function getChannelId()
    {
        return $this->getParameter('channelID');
    }

    public function setChannelId($value)
    {
        return $this->setParameter('channelID', $value);
    }

    public function setSubDomain($value)
    {
        return $this->setParameter('subDomain', $value);
    }

    public function setEmail($value)
    {
        return $this->setParameter('email', $value);
    }

    public function setCommission($value)
    {
        return $this->setParameter('commission', $value);
    }
}
