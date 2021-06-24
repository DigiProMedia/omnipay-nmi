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
            'date' => $this->getDate(),
            'report_type' => 'settlement_batch',
            'verbose' => true,
            'status' => 'settled' //settled, not_settled
        ];
/*        Settlement Batch Query Logic
            NEW Variables/Values
            •	report_type=settlement_batch
            •	status=settled, not_settled
            •	processor_id=processorid**
                    •	verbose=true
            •	date=YYYYMMDD
                    **specify the processor ID of the merchant account. This can be found under 'Settings -- Load Balancing' for merchants with more than one MID, or by an Affiliate clicking on the pencil and scrolling to the bottom of the page (it will be the non-editable processor name/id) for those who do NOT have more than one MID.
                Here are some examples:
            No details, settled status
            https://secure.networkmerchants.com/api/query.php?username=demo&password=password&date=20130422&report_type=settlement_batch&status=settled&processor_id=ccprocessorb
            No details, not settled
            https://secure.networkmerchants.com/api/query.php?username=demo&password=password&report_type=settlement_batch&status=not_settled&processor_id=ccprocessorb
            Details not settled
            https://secure.networkmerchants.com/api/query.php?username=demo&password=password&report_type=settlement_batch&status=not_settled&processor_id=ccprocessora&verbose=true
            Details settled
            https://secure.networkmerchants.com/api/query.php?username=demo&password=password&date=20130422&report_type=settlement_batch&status=settled&processor_id=ccprocessora&verbose=true
*/

    }

    protected function getResponseBody($httpResponse) {
        return $httpResponse->xml();
    }


    public function getStartDate()
    {
        return $this->getDate();
    }

    public function setStartDate($value)
    {
        return $this->setDate($value);
    }

    public function getDate()
    {
        return $this->verifyAndFixDate($this->getParameter('date'));
    }

    public function setDate($value)
    {
        return $this->setParameter('date', $value);
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
