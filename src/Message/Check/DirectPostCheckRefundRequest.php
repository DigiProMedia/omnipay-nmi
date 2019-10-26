<?php
namespace Omnipay\NMI\Message\Check;

use Omnipay\NMI\Message\DirectPostCaptureRequest;

/**
* NMI Direct Post Refund Request
*/
class DirectPostCheckRefundRequest extends DirectPostCaptureRequest
{
    protected $type = 'refund';


    public function getData()
    {
        $data = parent::getData();
        $data['payment'] = 'check';
        return $data;
    }
}
