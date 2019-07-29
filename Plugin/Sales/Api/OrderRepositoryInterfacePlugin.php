<?php

namespace Mapp\Connect\Plugin\Sales\Api;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;


class OrderRepositoryInterfacePlugin {

  protected $scopeConfig;
  protected $_helper;

  public function __construct(
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    \Mapp\Connect\Helper\Data  	$helper
  ) {
    $this->scopeConfig = $scopeConfig;
    $this->_helper = $helper;
  }

  public function afterSave(OrderRepositoryInterface $subject, OrderInterface $order): OrderInterface {
      if ($this->_helper->getConfigValue('export', 'transaction_enable')) {
          $data = $order->getData();
          $data['items'] = array();
          unset($data['status_histories'], $data['extension_attributes'], $data['addresses'], $data['payment']);

          foreach ($order->getAllVisibleItems() as $item) {
            $item_data = $item->getData();
            unset($item_data['product_options'], $item_data['extension_attributes'], $item_data['parent_item']);
            $data['items'][] = $item_data;
          }

          $data['billingAddress'] = $order->getBillingAddress()->getData();
          $data['shippingAddress'] = $order->getShippingAddress()->getData();

          if ($mc = $this->_helper->getMappConnectClient()) {
            $mc->event('transaction', $data);
          }
      }
      return $order;
  }
}
