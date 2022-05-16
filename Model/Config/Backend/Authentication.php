<?php

namespace Mapp\Connect\Model\Config\Backend;

class Authentication extends \Magento\Framework\App\Config\Value {

    const CONFIG_PREFIX = 'mappconnect';

    protected $_configResource;

    public function __construct(
            \Magento\Framework\Model\Context $context,
            \Magento\Framework\Registry $registry,
            \Magento\Framework\App\Config\ScopeConfigInterface $config,
            \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
            \Magento\Framework\App\Config\ConfigResource\ConfigInterface $configResource,
            \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
            \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
            array $data = []
    ) {
        $this->_configResource = $configResource;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    public function beforeSave() {

        if ($this->getConfigValue('integration', 'integration_enable')) {
            $url = $this->getConfigValue('general', 'base_url');
            if ($url == 'custom')
                $url = $this->getConfigValue('general', 'base_url_custom');

            $mc = new \Mapp\Connect\Client(
                $url,
                $this->getConfigValue('integration', 'integration_id'),
                $this->getConfigValue('integration', 'integration_secret')
            );

            if (!$mc->ping())
                throw new \Magento\Framework\Exception\ValidatorException(__('Authentication failed.'));

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $magentourl = (string)$this->_config->getValue('web/secure/base_url', $this->getScope() ?: ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $this->getScopeCode());
            $resp = $mc->connect([
              'params' => [
                'magentourl' => $magentourl,
                'magentoversion' => $objectManager->get('Magento\Framework\App\ProductMetadataInterface')->getVersion(),
                'magentoname' => ($this->getScope() ?: ScopeConfigInterface::SCOPE_TYPE_DEFAULT) . ($this->getScopeCode() ? "|" .$this->getScopeCode() : ""),
                'website' => parse_url($magentourl, PHP_URL_HOST)
              ]
            ]);

            //var_dump($resp); die();

            if (!is_null($resp['customersGroupId']))
                $this->configResource->saveConfig(self::CONFIG_PREFIX . '/group/customers', $resp['customersGroupId'], $this->getScope(), $this->getScopeId());
            if (!is_null($resp['subscribersGroupId']))
                $this->configResource->saveConfig(self::CONFIG_PREFIX . '/group/subscribers', $resp['subscribersGroupId'], $this->getScope(), $this->getScopeId());
            if (!is_null($resp['guestsGroupId']))
                $this->configResource->saveConfig(self::CONFIG_PREFIX . '/group/guests', $resp['guestsGroupId'], $this->getScope(), $this->getScopeId());

        }
        parent::beforeSave();
    }

    private function getConfigValue(string $group, string $field) {
        if ($this->getData("groups/$group/fields/$field/value"))
            return (string)$this->getData("groups/$group/fields/$field/value");

        return (string)$this->_config->getValue(self::CONFIG_PREFIX . "/" . $group . "/" . $field,
           $this->getScope() ?: \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
           $this->getScopeCode()
        );
    }
}
