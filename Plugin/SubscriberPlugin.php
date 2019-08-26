<?php
namespace Mapp\Connect\Plugin;

class SubscriberPlugin {

    protected $scopeConfig;
    protected $helper;
    protected $logger;

    public function __construct(
      \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
      \Mapp\Connect\Helper\Data $helper,
      \Psr\Log\LoggerInterface $logger
    ) {
      $this->scopeConfig = $scopeConfig;
      $this->_helper = $helper;
      $this->logger = $logger;
    }

    public function aroundSubscribe($subject, \Closure $proceed, $email) {

        $result = $proceed($email);

        try {
          if (($mappconnect = $this->_helper->getMappConnectClient())
           && $this->_helper->getConfigValue('export', 'newsletter_enable')) {
            $mappconnect->event('newsletter', [
              'email' => $email,
              'group' => $this->_helper->getConfigValue('group', 'subscribers')
            ]);
          }
        } catch(\Exception $e) {
          $this->logger->error('MappConnect: cannot sync subscribe event', ['exception' => $e]);
        }
        return $result;
    }

    public function aroundUnsubscribe($subject, \Closure $proceed) {

        $email = $subject->getEmail();
        $result = $proceed($email);

        try {
          if (($mappconnect = $this->_helper->getMappConnectClient())
           && $this->_helper->getConfigValue('export', 'newsletter_enable')) {
            $mappconnect->event('newsletter', [
              'email' => $email,
              'group' => $this->_helper->getConfigValue('group', 'subscribers'),
              'unsubscribe' => true
            ]);
          }
        } catch(\Exception $e) {
          $this->logger->error('MappConnect: cannot sync unsubscribe event', ['exception' => $e]);
        }

        return $result;
    }

}
