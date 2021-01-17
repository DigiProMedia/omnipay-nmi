<?php
declare(strict_types=1);

namespace Omnipay\NMI\Message;


class Transactions extends AbstractRequest
{

    protected $endpoint = 'https://secure.networkmerchants.com/api/query.php';

    /**
     * @inheritDoc
     */
    public function getData()
    {
        return [
           'username' => $this->getUsername(),
           'password' => $this->getPassword(),
           /*'start_date' => $this->getStartDate(),
           'end_date' => $this->getEndDate(),*/
           'transaction_id' => '5907245136',
        ];
    }


    public function getStartDate()
    {
        return $this->getParameter('startDate');
    }

    public function setStartDate($value)
    {
        return $this->setParameter('startDate', $value);
    }

    public function getEndDate()
    {
        return $this->getParameter('endDate');
    }

    public function setEndDate($value)
    {
        return $this->setParameter('endDate', $value);
    }
}