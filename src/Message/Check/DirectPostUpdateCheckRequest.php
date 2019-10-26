<?php

namespace Omnipay\NMI\Message\Check;

class DirectPostUpdateCheckRequest extends DirectPostCreateCheckRequest
{
    protected $customer_vault = 'update_customer';
}
