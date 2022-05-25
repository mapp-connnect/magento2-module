<?php

namespace Mapp\Connect\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{

    protected $client = null;

    protected $_config;
    protected $_value;
    protected $_request;

    const CONFIG_PREFIX = 'mappconnect';

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\State $state
    ) {
        $this->_request = $request;
        $this->_config = $config;
        $this->_state = $state;
    }

    public function getBaseURL()
    {
        $url = $this->getConfigValue('general', 'base_url');
        if ($url == 'custom') {
            $url = $this->getConfigValue('general', 'base_url_custom');
        }
        return $url;
    }

    public function getMappConnectClient()
    {
        if ($this->client === null) {
            if ($this->getConfigValue('integration', 'integration_enable')) {
                $this->client = new \Mapp\Connect\Client(
                    $this->getBaseURL(),
                    $this->getConfigValue('integration', 'integration_id'),
                    $this->getConfigValue('integration', 'integration_secret')
                );
            }
        }
        return $this->client;
    }

    public function getConfigValue(string $group, string $field)
    {

        if ($this->_state->getAreaCode() === \Magento\Framework\App\Area::AREA_ADMINHTML) {
            if ($storeId = $this->_request->getParam("store")) {
                return (string)$this->_config->getValue(
                    self::CONFIG_PREFIX . "/" . $group . "/" . $field,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeId
                );
            }
            if ($websiteId = $this->_request->getParam("website")) {
                return (string)$this->_config->getValue(
                    self::CONFIG_PREFIX . "/" . $group . "/" . $field,
                    \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
                    $websiteId
                );
            }

        }

        return (string)$this->_config->getValue(
            self::CONFIG_PREFIX . "/" . $group . "/" . $field,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function templateIdToConfig($templeteId)
    {
        $map = [
        "sales_email_order_template" => "mappconnect_messages/order/template",
        "sales_email_order_guest_template" => "mappconnect_messages/order/guest_template",
        "sales_email_order_comment_template" => "mappconnect_messages/order_comment/template",
        "sales_email_order_comment_guest_template" => "mappconnect_messages/order_comment/guest_template",
        "sales_email_invoice_template" => "mappconnect_messages/invoice/template",
        "sales_email_invoice_guest_template" => "mappconnect_messages/invoice/guest_template",
        "sales_email_invoice_comment_template" => "mappconnect_messages/invoice_comment/template",
        "sales_email_invoice_comment_guest_template" => "mappconnect_messages/invoice_comment/guest_template",
        "sales_email_shipment_template" => "mappconnect_messages/shipment/template",
        "sales_email_shipment_guest_template" => "mappconnect_messages/shipment/guest_template",
        "sales_email_shipment_comment_template" => "mappconnect_messages/shipment_comment/template",
        "sales_email_shipment_comment_guest_template" => "mappconnect_messages/shipment_comment/guest_template",
        "sales_email_creditmemo_template" => "mappconnect_messages/creditmemo/template",
        "sales_email_creditmemo_guest_template" => "mappconnect_messages/creditmemo/guest_template",
        "sales_email_creditmemo_comment_template" => "mappconnect_messages/creditmemo_comment/template",
        "sales_email_creditmemo_comment_guest_template" => "mappconnect_messages/creditmemo_comment/guest_template",

        "customer_create_account_email_template" => "mappconnect_messages/create_account/email_template",
        "customer_create_account_email_no_password_template"
            => "mappconnect_messages/create_account/email_no_password_template",
        "customer_create_account_email_confirmation_template"
            => "mappconnect_messages/create_account/email_confirmation_template",
        "customer_create_account_email_confirmed_template"
            => "mappconnect_messages/create_account/email_confirmed_template",
        "customer_password_forgot_email_template" => "mappconnect_messages/password/forgot_email_template",
        "customer_password_remind_email_template" => "mappconnect_messages/password/remind_email_template",
        "customer_password_reset_password_template" => "mappconnect_messages/password/reset_password_template",
        "customer_account_information_change_email_template"
            => "mappconnect_messages/account_information/change_email_template",
        "customer_account_information_change_email_and_password_template"
            => "mappconnect_messages/account_information/change_email_and_password_template",

        "sendfriend_email_template" => "mappconnect_messages/sendfriend/template",
        "catalog_productalert_email_price_template" => "mappconnect_messages/productalert/email_price_template",
        "catalog_productalert_email_stock_template" => "mappconnect_messages/productalert/email_stock_template",
        "wishlist_email_email_template" => "mappconnect_messages/wishlist/email_template"
        ];

        if (!array_key_exists($templeteId, $map)) {
            return 0;
        }

        if ($this->_state->getAreaCode() === \Magento\Framework\App\Area::AREA_ADMINHTML) {
            if ($storeId = $this->_request->getParam("store")) {
                return (string)$this->_config->getValue(
                    $map[$templeteId],
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeId
                );
            }
            if ($websiteId = $this->_request->getParam("website")) {
                return (string)$this->_config->getValue(
                    $map[$templeteId],
                    \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
                    $websiteId
                );
            }
        }

        return (int)$this->_config->getValue($map[$templeteId], \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
