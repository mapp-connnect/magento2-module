<?php

namespace Mapp\Connect\Plugin\Sales\Api;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;


class OrderRepositoryInterfacePlugin {

  protected $scopeConfig;
  protected $_helper;
  protected $productHelper;
  protected $addressConfig;
  protected $paymentHelper;
  protected $logger;

  public function __construct(
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    \Mapp\Connect\Helper\Data  	$helper,
    \Magento\Catalog\Helper\Product $productHelper,
    \Magento\Customer\Model\Address\Config $addressConfig,
    \Magento\Payment\Helper\Data $paymentHelper,
    \Magento\Framework\Session\StorageInterface $storage,
    \Psr\Log\LoggerInterface $logger
  ) {
    $this->scopeConfig = $scopeConfig;
    $this->_helper = $helper;
    $this->productHelper = $productHelper;
    $this->addressConfig = $addressConfig;
    $this->paymentHelper = $paymentHelper;
    $this->storage = $storage;
    $this->logger = $logger;
  }

  protected function getSelectedOptions($item) {
    $options = $item->getProductOptions();
    $options = array_merge(
      isset($options['options']) ? $options['options'] : [],
      isset($options['additional_options']) ? $options['additional_options'] : [],
      isset($options['attributes_info']) ? $options['attributes_info'] : []
    );
    $ret = [];
    foreach ($options as $opt)
      $ret[] = $opt['label'].': '.$opt['value'];
    return implode(', ', $ret);
  }

  protected function getCategories($item) {
    $ret = [];
    foreach ($item->getProduct()->getCategoryCollection()->addAttributeToSelect('name') as $category) {
      $ret[]  = $category->getName();
    }
    return implode(', ', $ret);
  }

  public function afterSave(OrderRepositoryInterface $subject, OrderInterface $order): OrderInterface {
      $transaction_key = 'mappconnect_transaction_'.$order->getId();

      if ($order->getState() != \Magento\Sales\Model\Order::STATE_NEW)
          return $order;

      if ($this->_helper->getConfigValue('export', 'transaction_enable') && ($this->storage->getData($transaction_key) != true)) {
          $data = $order->getData();
          $data['items'] = array();
          unset($data['status_histories'], $data['extension_attributes'], $data['addresses'], $data['payment']);

          foreach ($order->getAllVisibleItems() as $item) {
            $item_data = $item->getData();
            $item_data['base_image'] = $this->productHelper->getImageUrl($item->getProduct());
            $item_data['url_path'] = $item->getProduct()->getProductUrl();
            $item_data['categories'] = $this->getCategories($item);
            $item_data['manufacturer'] = $item->getProduct()->getAttributeText('manufacturer');

            $item_data['variant'] = $this->getSelectedOptions($item);
            unset($item_data['product_options'], $item_data['extension_attributes'], $item_data['parent_item']);

            $data['items'][] = $item_data;
          }

          $data['billingAddress'] = $order->getBillingAddress()->getData();
          $data['shippingAddress'] = $order->getShippingAddress()->getData();

          $renderer = $this->addressConfig->getFormatByCode('html')->getRenderer();

          $data['billingAddressFormatted'] = $renderer->renderArray($order->getBillingAddress());
          $data['shippingAddressFormatted'] = $renderer->renderArray($order->getShippingAddress());

          $data['payment_info'] = $this->paymentHelper->getInfoBlockHtml(
             $order->getPayment(),
             $data['store_id']
          );

          $messageId = $this->_helper->templateIdToConfig("sales_email_order_template");

          if (isset($data['customer_is_guest']) && $data['customer_is_guest']) {
            $messageId = $this->_helper->templateIdToConfig("sales_email_order_guest_template");
            if ($this->_helper->getConfigValue('group', 'guests'))
              $data['group'] = $this->_helper->getConfigValue('group', 'guests');
          }

          if ($messageId) {
            $data['messageId'] = strval($messageId);
          }

          try {
            if ($mc = $this->_helper->getMappConnectClient()) {
              $mc->event('transaction', $data);
              $this->storage->setData($transaction_key, true);
            }
          } catch(\Exception $e) {
            $this->logger->error('MappConnect: cannot sync transaction event', ['exception' => $e]);
          }
      } else if ($this->_helper->getConfigValue('export', 'customer_enable')) {
          $data = $order->getData();
          if (isset($data['customer_is_guest']) && $data['customer_is_guest']) {
            $data = [
                'dob' => $order->getCustomerDob(),
                'email' => $order->getCustomerEmail(),
                'firstname' => $order->getCustomerFirstname (),
                'gender' => $order->getCustomerGender(),
                'lastname' => $order->getCustomerLastname(),
                'middlename' => $order->getCustomerMiddlename(),
                'note' => $order->getCustomerNote()
            ];

            $data['group'] = $this->_helper->getConfigValue('group', 'guests');
            try {
              if ($mc = $this->_helper->getMappConnectClient()) {
                $this->logger->debug('MappConnect: sending guest customer', ['data' => $data]);
                $mc->event('user', $data);
              }
            } catch(\Exception $e) {
              $this->logger->error('MappConnect: cannot sync guest customer', ['exception' => $e]);
            }
          }
      }
      return $order;
  }
}
