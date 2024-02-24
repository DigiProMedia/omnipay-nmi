<?php
declare(strict_types=1);

namespace Omnipay\NMI\Message;


class Transactions extends AbstractRequest
{
    protected $responseClass = BatchQueryResponse::class;
    protected $endpoint = 'https://secure.networkmerchants.com/api/query.php';

    public function getData()
    {
        return [
            'username' => $this->getUsername(),
            'password' => $this->getPassword(),
            'start_date' => $this->getStartDate(),
            'end_date' => $this->getEndDate(),
            'condition' => 'complete',
            'verbose' => true,
        ];
    }

    protected function getResponseBody($httpResponse) {
        return simplexml_load_string($httpResponse->getBody()->getContents());
    }


    public function getStartDate()
    {
        return $this->verifyAndFixDate($this->getParameter('startDate'));
    }

    public function setStartDate($value)
    {
        return $this->setParameter('startDate', $value);
    }

    public function getEndDate()
    {
        return $this->verifyAndFixDate($this->getParameter('endDate'));
    }


    public function setEndDate($value)
    {
        return $this->setParameter('endDate', $value);
    }

    public function getBatchNumber()
    {
        return $this->getParameter('batchNumber');
    }

    public function setBatchNumber($value)
    {
        return $this->setParameter('batchNumber', $value);
    }

    private function verifyAndFixDate($date)
    {
        if ($this->validateDate($date, 'm-d-Y') || $this->validateDate($date, 'n-j-Y')) {
            $newDate = \DateTime::createFromFormat('m-d-Y', $date);
            return $newDate->format('Ymd');
        }
        if ($this->validateDate($date, 'm/d/Y') || $this->validateDate($date, 'n/j/Y')) {
            $newDate = \DateTime::createFromFormat('m/d/Y', $date);
            return $newDate->format('Ymd');
        }
        if ($this->validateDate($date, 'Y-m-d') || $this->validateDate($date, 'Y-n-j')) {
            $newDate = \DateTime::createFromFormat('Y-m-d', $date);
            return $newDate->format('Ymd');
        }
        return $date;
    }

    private function validateDate($date, $format)
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
}
