<?php
 namespace Mapp\Connect\Framework\Mail;

 use Magento\Framework\Exception\MailException;
 use Magento\Framework\Phrase;

 class Transport implements \Magento\Framework\Mail\TransportInterface {

    protected $parameters;

    public function __construct($parameters = null) {
       $this->parameters = $parameters;
    }

    public function sendMessage() {

    }

    public function getMessage() {
       return $this->message;
    }
 }
