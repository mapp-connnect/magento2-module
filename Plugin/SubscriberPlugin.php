<?php
namespace Mapp\Connect\Plugin;

class SubscriberPlugin
{

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

    public function afterSubscribe($subject, $result)
    {
        if (!$result) {
            return $result;
        }
        $email = $subject->getEmail();
        $this->logger->debug('MappConnect: Subscribe subscribe');
        try {
            if (($mappconnect = $this->_helper->getMappConnectClient())
            && $this->_helper->getConfigValue('export', 'newsletter_enable')) {
                $data = [
                 'email' => $email,
                 'group' => $this->_helper->getConfigValue('group', 'subscribers')
                ];
                if ($this->_helper->getConfigValue('export', 'newsletter_doubleoptin')) {
                    $data['doubleOptIn'] = true;
                }
                $this->logger->debug('MappConnect: sending newsletter', ['data' => $data]);
                $mappconnect->event('newsletter', $data);
            }
        } catch (\Exception $e) {
            $this->logger->error('MappConnect: cannot sync subscribe event', ['exception' => $e]);
        }
        return $result;
    }

    public function afterUnsubscribe($subject, $result)
    {
        if (!($result->isStatusChanged())) {
            return $result;
        }
        $this->logger->debug('MappConnect: Subscribe unsubscribe');
        $email = $subject->getEmail();
        try {
            if (($mappconnect = $this->_helper->getMappConnectClient())
            && $this->_helper->getConfigValue('export', 'newsletter_enable')) {
                $data = [
                 'email' => $email,
                 'group' => $this->_helper->getConfigValue('group', 'subscribers'),
                 'unsubscribe' => true
                ];
                $this->logger->debug('MappConnect: sending newsletter', ['data' => $data]);
                $mappconnect->event('newsletter', $data);
            }
        } catch (\Exception $e) {
            $this->logger->error('MappConnect: cannot sync unsubscribe event', ['exception' => $e]);
        }

        return $result;
    }

    public function afterSubscribeCustomerById($subject, $result)
    {
        if (!($result->isStatusChanged())) {
            return $result;
        }
        $this->logger->debug('MappConnect: Subscribe subscribe by customerid');
        $email = $subject->getEmail();
        try {
            if (($mappconnect = $this->_helper->getMappConnectClient())
            && $this->_helper->getConfigValue('export', 'newsletter_enable')) {
                $data = [
                 'email' => $email,
                 'group' => $this->_helper->getConfigValue('group', 'subscribers')
                ];
                if ($this->_helper->getConfigValue('export', 'newsletter_doubleoptin')) {
                    $data['doubleOptIn'] = true;
                }
                $this->logger->debug('MappConnect: sending newsletter', ['data' => $data]);
                $mappconnect->event('newsletter', $data);
            }
        } catch (\Exception $e) {
            $this->logger->error('MappConnect: cannot sync subscribe event', ['exception' => $e]);
        }
        return $result;
    }

    public function aroundUnsubscribeCustomerById($subject, \Closure $proceed, $customerId)
    {
        $result = $proceed($customerId);
        if (!($subject->isStatusChanged())) {
            return $result;
        }

        $this->logger->debug('MappConnect: Subscribe unsubscribe by customerid');
        $email = $subject->getEmail();
        try {
            if (($mappconnect = $this->_helper->getMappConnectClient())
            && $this->_helper->getConfigValue('export', 'newsletter_enable')) {
                $data = [
                 'email' => $email,
                 'group' => $this->_helper->getConfigValue('group', 'subscribers'),
                 'unsubscribe' => true
                ];
                $this->logger->debug('MappConnect: sending newsletter', ['data' => $data]);
                $mappconnect->event('newsletter', $data);
            }
        } catch (\Exception $e) {
            $this->logger->error('MappConnect: cannot sync unsubscribe event', ['exception' => $e]);
        }
        return $result;
    }
}
