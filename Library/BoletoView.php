<?php

namespace Paggy\BoletoBundle\Library;

use Twig_Environment as Environment;
use Paggy\BoletoBundle\Helper\BoletoHelper;

class BoletoView
{
    protected $config;
    protected $twig;

    protected $template = 'PaggyBoletoBundle:Layouts:layout_%s.html.twig';
    protected $parameters = array();

    protected $bank = '';
    protected $helper;

    // Very similar to DependencyInjection Configuration, but different:
    // The $viewFields are the fields required by the Twig templates, no more, no less
    // The configuration is mostly used to populate them, but there is no strict one-to-one relationship
    protected $viewFields = array(
        'cedant'  => array('name', 'cpf_cnpj', 'account_bank_code', 'account_code'),
        'drawer'  => array('name', 'cpf_cnpj', 'address_line1', 'address_line2'),
        'payer'   => array('name', 'cpf_cnpj', 'address_line1', 'address_line2'),
        'payslip' => array(
            'title', 'value', 'due_date', 'currency', 'quantity', 'document_number', 'description', 'instructions', 'payment_location',
            'wallet', 'acceptance', 'document_type', 'document_date', 'rendering_date', 'our_number', 'bar_code', 'typable_line',
        ),
        'paths'   => array('favicon', 'logo_cedant_bank', 'logo_drawer'),
    );

    public function __construct(Array $config, Environment $twig)
    {
        $this->config = $config;
        $this->twig   = $twig;
    }

    public function render($data, $template = '')
    {
        $this->buildViewParameters($data);

        if (empty($template)) {
            $template = sprintf($this->template, strtolower($this->bank));
        }

        return $this->twig->render($template , $this->parameters);
    }

    protected function buildViewParameters($data)
    {
        foreach ($this->viewFields as $subject => $fields) {
            // First, we create an array with all subject's VIEW FIELDS set as empty strings
            $viewValues = array_fill_keys($fields, '');

            // Next, we merge with provided $data in format "subject_view_field"
            $this->mergeArrayData($viewValues, $data, $subject);

            // We will also look for a corresponding key in the $data array
            $subjectData = (isset($data[$subject]) && is_array($data[$subject]) ? $data[$subject] : array());

            // And merge the corresponding configuration into it
            $this->mergeConfigData($subjectData, $subject, $data);

            // Finally, we merge $data[$subject] + configuration (AKA $subjectData) to our $viewValues
            foreach ($fields as $field) {
                if (empty($viewValues[$field]) && !empty($subjectData[$field])) {
                    $viewValues[$field] = $subjectData[$field];
                }
            }

            // At this point, we have aggregated in "$viewValues" all view fields for the subject, using:
            // 1) configuration
            // 2) $data["$subject"]
            // 3) $data["$subject_$viewField"]
            $this->parameters[$subject] = $viewValues;

            // We will complete the parameters generation now by applying
            // Boleto-View modifiers and Bank-Specific Helper functions
            $this->adjustParameters($this->parameters, $subject, $subjectData);
        }
    }

    protected function adjustParameters(&$parameters, $subject, $subjectData)
    {
        $param =& $parameters[$subject];

        if (isset($param['cpf_cnpj']) && empty($param['cpf_cnpj'])) {
            if (!empty($subjectData['cpf'])) {
                $param['cpf_cnpj'] = $subjectData['cpf'];
            }
            if (!empty($subjectData['cnpj'])) {
                $param['cpf_cnpj'] = $subjectData['cnpj'];
            }
        }

        if ($subject == 'cedant') {
            if (empty($param['account_bank_code'])) {
                if (!empty($subjectData['bank'])) {
                    $param['account_bank_code'] = BoletoHelper::getBankCode($subjectData['bank']);
                }
            }
            $this->bank = BoletoHelper::getBankSlug($param['account_bank_code']);
        }

        if ($subject == 'paths') {
            if (empty($param['logo_cedant_bank'])) {
                $param['logo_cedant_bank'] = 'bundles/paggyboleto/images/logo_' . strtolower($this->bank) . '.jpg';
            }
        }

        $this->getHelper()->adjustParameters($parameters, $subject, $subjectData);
    }

    protected function getHelper()
    {
        if (empty($this->helper)) {
            if (!empty($this->config['paths']['helper_class'])) {
                $class = $this->config['paths']['helper_class'];
            } else {
                $class = 'Paggy\\BoletoBundle\\Helper\\' . ucfirst(strtolower($this->bank)) . 'BoletoHelper';
            }
            $this->helper = new $class($this->config);
        }
        return $this->helper;
    }

    protected function mergeArrayData(&$target, $data, $suffix = '')
    {
        if (!empty($suffix)) {
            $suffix = $suffix . '_';
        } 
        foreach (array_keys($target) as $key) {
            if (!empty($data[$suffix . $key])) {
                $target[$key] = $data[$suffix . $key];
            }
        }
    }

    protected function mergeConfigData(&$target, $subject, $data)
    {
        $config_key = rtrim($subject, 's') . 's';
        if (!empty($this->config[$config_key])) {
            // Handling multiple configurations
            if (isset($data[$subject]) && is_string($data[$subject]) && isset($this->config[$config_key][$data[$subject]])) {
                $config = $this->config[$config_key][$data[$subject]];
            }
            else {
                // Default is the first one
                $config = reset($this->config[$config_key]);
            }
            $target = array_merge($config, $target);
        }
    }
}
