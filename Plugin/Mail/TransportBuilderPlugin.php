<?php

namespace Mapp\Connect\Plugin\Mail;

use Mapp\Connect\Helper\Data;

class TransportBuilderPlugin {

    protected $dataHelper;

    protected $parameters;

    protected $templateFactory;

    protected $_config;

    public function __construct(
      Data $dataHelper,
      \Magento\Framework\Mail\Template\FactoryInterface $templateFactory
    ) {
      $this->dataHelper = $dataHelper;
      $this->templateFactory = $templateFactory;
      $this->parameters = [
        'options' => null,
        'identifier' => null,
        'model' => null,
        'vars' => null,
        'to' => []
      ];
    }

    public function aroundGetTransport(\Magento\Framework\Mail\Template\TransportBuilder $subject, \Closure $proceed) {
      if (($mappconnect = $this->dataHelper->getMappConnectClient())
        && ($messageId = $this->dataHelper->templateIdToConfig($this->parameters['identifier']))) {

          if ($this->dataHelper->getConfigValue('export', 'transaction_enable')
            && in_array($this->parameters['identifier'], [
                "sales_email_order_template",
                "sales_email_order_guest_template"
              ])) {
                $messageId = 0;
          }

          $template = $this->templateFactory->get($this->parameters['identifier'], $this->parameters['model'])
             ->setVars($this->parameters['vars'])
             ->setOptions($this->parameters['options']);

          $template->processTemplate();
          $filer = $template->getTemplateFilter();

          $params = $this->parameters;

          $params['params'] = [];
          foreach ($template->getVariablesOptionArray() as $v) {
            $label = 'param_'.strtolower($v['label']->render());
            $label = preg_replace('/[^a-z0-9]+/', '_', $label);
            $label = preg_replace('/_+$/', '', $label);
            $params['params'][$label] = $filer->filter($v['value']);
          }

          return new \Mapp\Connect\Framework\Mail\Transport($mappconnect, $messageId, $params);
      }
      $returnValue = $proceed();
      return $returnValue;
    }

    public function beforeAddTo(\Magento\Framework\Mail\Template\TransportBuilder $subject, $address,	$name = '') {
        $this->parameters['to'][] = $address;
        return null;
    }

    public function beforeSetTemplateOptions(\Magento\Framework\Mail\Template\TransportBuilder $subject, $templateOptions) {
        $this->parameters['options'] = $templateOptions;
        return null;
    }

    public function beforeSetTemplateIdentifier(\Magento\Framework\Mail\Template\TransportBuilder $subject, $templateIdentifier) {
        $this->parameters['identifier'] = $templateIdentifier;
        return null;
    }

    public function beforeSetTemplateModel(\Magento\Framework\Mail\Template\TransportBuilder $subject, $templateModel) {
        $this->parameters['model'] = $templateModel;
        return null;
    }

    public function beforeSetTemplateVars(\Magento\Framework\Mail\Template\TransportBuilder $subject, $templateVars) {
        $this->parameters['vars'] = $templateVars;
        return null;
    }

}
