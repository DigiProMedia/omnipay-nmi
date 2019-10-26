<?php
namespace Omnipay\NMI\Message\Check;

use Omnipay\NMI\Message\DirectPostVoidRequest;

/**
* NMI Direct Post Void Request
*/
class DirectPostCheckVoidRequest extends DirectPostVoidRequest
{
    protected $type = 'void';

    public function getData()
    {
        $data = parent::getData();
        $data['payment'] = 'check';
        return $data;
    }
}
