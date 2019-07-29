<?php

namespace Mapp\Connect\Plugin\Mail;

use Mapp\Connect\Helper\Data;

class TransportBuilderPlugin {

    protected $dataHelper;

    protected $parameters;

    public function __construct(Data $dataHelper) {
      $this->dataHelper = $dataHelper;
    }

    public function aroundGetTransport(\Magento\Framework\Mail\Template\TransportBuilder $subject, \Closure $proceed) {
        //return new \Mapp\Connect\Framework\Mail\Transport([ $this->parameters ]);

        $returnValue = $proceed();
        return $returnValue;
    }

    public function beforeSetTemplateOptions(\Magento\Framework\Mail\Template\TransportBuilder $subject, $templateOptions) {
        $this->parameters['options'] = $templateOptions;
        return [$templateOptions];
    }
}
