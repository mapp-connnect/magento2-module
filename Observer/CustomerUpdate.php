<?php
namespace Mapp\Connect\Observer;

use Magento\Framework\Event\ObserverInterface;

class CustomerUpdate implements ObserverInterface {

  /**
   * @var \Magento\Framework\App\Config\ScopeConfigInterface
   */
  protected $scopeConfig;

  protected $_helper;

  protected $logger;

  public function __construct(
      \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
      \Mapp\Connect\Helper\Data  	$helper,
      \Psr\Log\LoggerInterface $logger
  ) {
    $this->scopeConfig = $scopeConfig;
    $this->_helper = $helper;
    $this->logger = $logger;
  }

  public function execute(\Magento\Framework\Event\Observer $observer) {

    if (($mappconnect = $this->_helper->getMappConnectClient())
      && $this->_helper->getConfigValue('export', 'customer_enable')) {

        $customer = $observer->getCustomerDataObject();
        $data = $customer->__toArray();
        $data['group'] = $this->_helper->getConfigValue('group', 'customers');
        $data['subscribe'] = true;

        try {
          $mappconnect->event('user', $data);
        } catch(\Exception $e) {
          $this->logger->error('MappConnect: cannot sync customer', ['exception' => $e]);
        }
    }
  }
}
