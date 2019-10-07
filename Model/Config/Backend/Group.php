<?php

namespace Mapp\Connect\Model\Config\Backend;

class Group implements \Magento\Framework\Option\ArrayInterface {

  protected $_helper;
  protected $_messageManager;

  public function __construct(
     	\Mapp\Connect\Helper\Data  	$helper,
      \Magento\Framework\Message\ManagerInterface $messageManager
  ) {
    $this->_helper = $helper;
    $this->_messageManager = $messageManager;
  }

  private static $cache = null;

  private function getGroups() {
    if (!is_null(self::$cache)) {
      return self::$cache;
    }
    try {
      if ($mc = $this->_helper->getMappConnectClient()) {
        self::$cache = $mc->getGroups();
      } else {
        return array();
      }
    } catch (\Exception $e) {
      $this->_messageManager->addExceptionMessage($e);
      self::$cache = array();
    }
    return self::$cache;
  }


  public function toOptionArray() {
    $ret = array(array(
      'value' => 0,
      'label' => __('Integration Default')
    ));
    foreach ($this->getGroups() as $value => $label) {
      array_push($ret, array('value' => $value, 'label' => $label));
    }
    return $ret;
  }

}
