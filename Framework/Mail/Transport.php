<?php
 namespace Mapp\Connect\Framework\Mail;

 use Magento\Framework\Exception\MailException;
 use Magento\Framework\Phrase;

 class Transport implements \Magento\Framework\Mail\TransportInterface {

    protected $parameters;
    protected $messageId;
    protected $mappconnect;

    public function __construct($mappconnect, $messageId, $parameters) {
      $this->mappconnect = $mappconnect;
      $this->messageId = $messageId;
      $this->parameters = $parameters;
    }

    public function sendMessage() {
      foreach ($this->parameters['to'] as $to) {
        $data = $this->parameters['params'];
        $data['messageId'] = $this->messageId;
        $data['email'] = $to;
        $this->mappconnect->event('email', $data);  
      }
    }

    public function getMessage() {
      return $this->messageId;
    }
 }
