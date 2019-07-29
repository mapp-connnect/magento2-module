<?php

namespace Mapp\Connect\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper {

  protected $client = null;

  protected $_config;
  protected $_value;

  const CONFIG_PREFIX = 'mappconnect';

  public function __construct(
     	\Magento\Framework\App\Config\ScopeConfigInterface  	$config,
      \Magento\Framework\App\Config\Value $value
  ) {
    $this->_config = $config;
    $this->_value = $value;
  }

  function getMappConnectClient() {
    if (is_null($this->client)) {
      if ($this->getConfigValue('integration', 'integration_enable')) {
        $this->client = new \Mapp\Connect\Client(
          $this->getConfigValue('general', 'base_url'),
          $this->getConfigValue('integration', 'integration_id'),
          $this->getConfigValue('integration', 'integration_secret')
        );
      }
    }
    return $this->client;
  }

  function getConfigValue(string $group, string $field) {
      if ($this->_value->getData("groups/$group/fields/$field/value"))
          return (string)$this->_value->getData("groups/$group/fields/$field/value");

      return (string)$this->_config->getValue(self::CONFIG_PREFIX . "/" . $group . "/" . $field,
         $this->_value->getScope() ?: \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
         $this->_value->getScopeCode()
      );
  }

  function templateIdToConfig($templeteId) {

  }

}
