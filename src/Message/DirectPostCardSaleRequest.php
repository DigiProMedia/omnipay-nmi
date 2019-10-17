<?php
namespace Omnipay\NMI\Message;

/**
* NMI Direct Post Sale Request
*/
class DirectPostCardSaleRequest extends DirectPostAuthRequest
{
    protected $type = 'sale';
}
