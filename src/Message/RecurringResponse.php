<?php
declare(strict_types=1);

namespace Omnipay\NMI\Message;

use Omnipay\Common\Message\RequestInterface;
use Omnipay\Common\Message\ResponseInterface;

/**
 * NMI Purchase Response.
 */
class RecurringResponse extends DirectPostResponse implements ResponseInterface
{
    public function __construct(RequestInterface $request, $data)
    {
        $this->request = $request;
        if (is_array($data)) {
            $this->data = $data;
        } else {
            parse_str($data, $this->data);
        }
    }

    public function getMessage()
    {
        $existingMessage = parent::getMessage();
        return $existingMessage !== '' ? $existingMessage : $this->getDefaultMessage();
    }

    public function getTransactionReference()
    {
        try {
            return parent::getTransactionReference();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function isSuccessful()
    {
        return $this->data['successful'] ?? false;
    }

    public function charged()
    {
        return $this->getData()['charged'] ?? false;
    }

    private function getDefaultMessage()
    {
        $verb = 'setup';
        if (strpos(get_class($this->request), 'DeleteRecurring') !== false) {
            $verb = 'deleted';
        } elseif (strpos(get_class($this->request), 'UpdateRecurring') !== false) {
            $verb = 'updated';
        }

        if ($this->charged()) {
            $verb .= ' and charged';
        }

        if ($this->isSuccessful()) {
            return 'Recurring payment ' . $verb . ' successfully.';
        } else {
            return 'Failed to ' . $verb . ' payment';
        }
    }

    public function getRecurringReference()
    {
        return $this->getData()['recurring_id'];
    }
}