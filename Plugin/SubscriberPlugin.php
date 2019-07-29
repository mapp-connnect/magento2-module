<?php
namespace Mapp\Connect\Plugin;

class SubscriberPlugin {

    protected $scopeConfig;

    public function __construct(
      \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
      \Mapp\Connect\Helper\Data  	$helper
    ) {
      $this->scopeConfig = $scopeConfig;
      $this->_helper = $helper;
    }

    public function aroundSubscribe($subject, \Closure $proceed, $email) {

        $result = $proceed($email);

        if (($mappconnect = $this->_helper->getMappConnectClient())
         && $this->_helper->getConfigValue('export', 'newsletter_enable')) {
          $mappconnect->event('newsletter', [
            'email' => $email,
            'group' => $this->_helper->getConfigValue('group', 'subscribers'),
            'subscribe' => true
          ]);
        }

        return $result;
    }

    public function aroundUnsubscribe($subject, \Closure $proceed) {

        $email = $subject->getEmail();
        $result = $proceed($email);

        if (($mappconnect = $this->_helper->getMappConnectClient())
         && $this->_helper->getConfigValue('export', 'newsletter_enable')) {
          $mappconnect->event('newsletter', [
            'email' => $email,
            'group' => $this->_helper->getConfigValue('group', 'subscribers'),
            'subscribe' => false
          ]);
        }

        return $result;
    }

}
