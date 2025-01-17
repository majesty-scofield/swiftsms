<?php

namespace FedaPay\Error;

/**
 * Class SignatureVerification
 *
 * @package FedaPay\Error
 */
class SignatureVerification extends Base
{
    private $sigHeader;

    public function __construct(
        $message,
        $sigHeader,
        $httpBody = null
    ) {
        parent::__construct($message, null, $httpBody, null, null);
        $this->sigHeader = $sigHeader;
    }

    public function getSigHeader()
    {
        return $this->sigHeader;
    }
}
