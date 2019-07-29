<?php
namespace Mapp\Connect\Observer;

use Magento\Framework\Event\ObserverInterface;

class CustomerUpdate implements ObserverInterface {

  /**
   * @var \Magento\Framework\App\Config\ScopeConfigInterface
   */
  protected $scopeConfig;

  protected $_helper;

  public function __construct(
      \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
      \Mapp\Connect\Helper\Data  	$helper
  ) {
    $this->scopeConfig = $scopeConfig;
    $this->_helper = $helper;
  }

  public function execute(\Magento\Framework\Event\Observer $observer) {

    if (($mappconnect = $this->_helper->getMappConnectClient())
      && $this->_helper->getConfigValue('export', 'customer_enable')) {

        $customer = $observer->getCustomerDataObject();
        $data = $customer->__toArray();
        $data['group'] = $this->_helper->getConfigValue('group', 'customers');
        $data['subscribe'] = true;

        $mappconnect->event('user', $data);
    }
  }
}
