<?php

namespace Paggy\BoletoBundle\Library;

class Payslip
{
    protected $cedant;
    protected $drawer;
    protected $payer;

    public function __construct()
    {
        $this->cedant = new Cedant();
        $this->drawer = new Drawer();
        $this->payer  = new Payer();
    }

    public function setData($data) {
        $this->setCedant($data);
        $this->setDrawer($data);
        $this->setPayer($data);
    }

    protected function setCedant($data)
    {
        // First, we create an array with all cedant VIEW FIELDS set as empty strings
        $this->cedant = array_fill_keys(array('name', 'cpf_cnpj', 'account_bank_code', 'account_code'), '');

        // Next, we merge with provided $data in format "cedant_view_field"
        $this->mergeArrayData($this->cedant, $data, 'cedant');

        // We will also look for a "cedant" key in the $data array
        $cedant_data = (isset($data['cedant']) && is_array($data['cedant']) ? $data['cedant'] : array());

        // And merge the "cedant" configuration with it
        $this->mergeConfigData($cedant_data, 'cedants', $data, 'cedant');

        // Finally, we will use "cedant" config + data to populate missing view fields
        if (empty($this->cedant['name']) && !empty($cedant_data['name'])) {
            $this->cedant['name'] = $cedant_data['name'];
        }

        if (empty($this->cedant['cpf_cnpj'])) {
            if (!empty($cedant_data['cpf'])) {
                $this->cedant['cpf_cnpj'] = $cedant_data['cpf'];
            }
            if (!empty($cedant_data['cnpj'])) {
                $this->cedant['cpf_cnpj'] = $cedant_data['cnpj'];
            }
        }

        if (empty($this->cedant['account_bank_code'])) {
            if (!empty($cedant_data['bank'])) {
                $this->cedant['account_bank_code'] = $this->helper->getBankCode($cedant_data['bank']);
            }
        }
        $this->bank = $this->helper->getBankSlug($this->cedant['account_bank_code']);

        if (empty($this->cedant['account_code'])) {
            $this->cedant['account_code'] = $this->helper->getBankAccountCode($cedant_data);
        }
    }

    protected function setDrawer($data)
    {
        $this->drawer = array_fill_keys(array('name', 'cpf_cnpj', 'address_line1', 'address_line2'), '');
        $this->mergeArrayData($this->drawer, $data, 'drawer');

        $drawer_data = (isset($data['drawer']) && is_array($data['drawer']) ? $data['drawer'] : array());
        $this->mergeConfigData($drawer_data, 'drawers', $data, 'drawer');

        foreach (array('name', 'address_line1', 'address_line2') as $field) {
            if (empty($this->drawer[$field]) && !empty($drawer_data[$field])) {
                $this->drawer[$field] = $drawer_data[$field];
            }
        }

        if (empty($this->drawer['cpf_cnpj'])) {
            if (!empty($drawer_data['cpf'])) {
                $this->drawer['cpf_cnpj'] = $drawer_data['cpf'];
            }
            if (!empty($drawer_data['cnpj'])) {
                $this->drawer['cpf_cnpj'] = $drawer_data['cnpj'];
            }
        }
    }

    protected function setPayer($data)
    {
        $this->payer = array_fill_keys(array('name', 'cpf_cnpj', 'address_line1', 'address_line2'), '');
        $this->mergeArrayData($this->payer, $data, 'payer');

        $payer_data = (isset($data['payer']) && is_array($data['payer']) ? $data['payer'] : array());
        $this->mergeConfigData($payer_data, 'payers', $data, 'payer');

        foreach (array('name', 'address_line1', 'address_line2') as $field) {
            if (empty($this->payer[$field]) && !empty($payer_data[$field])) {
                $this->payer[$field] = $payer_data[$field];
            }
        }

        if (empty($this->payer['cpf_cnpj'])) {
            if (!empty($payer_data['cpf'])) {
                $this->payer['cpf_cnpj'] = $payer_data['cpf'];
            }
            if (!empty($payer_data['cnpj'])) {
                $this->payer['cpf_cnpj'] = $payer_data['cnpj'];
            }
        }
    }

    protected function setPayslip($data)
    {
        $this->payslip = array_fill_keys(array(
            'title', 'value', 'due_date', 'currency', 'quantity', 'document_number', 'description', 'instructions', 'payment_location',
            'wallet', 'acceptance', 'document_type', 'rendering_date', 'our_number', 'document_date', 'typable_line', 'bar_code',
            'discount_value', 'deductions_value', 'forfeit_value', 'affix_value', 'final_value'
        ), '');
        $this->mergeArrayData($this->payslip, $data, 'payslip');

        $payslip_data = (isset($data['payslip']) && is_array($data['payslip']) ? $data['payslip'] : array());
        $this->mergeConfigData($payslip_data, 'payslips', $data, 'payslip');

        foreach (array_keys($this->payslip) as $field) {
            if (empty($this->payslip[$field]) && !empty($payslip_data[$field])) {
                $this->payslip[$field] = $payslip_data[$field];
            }
        }

        if (empty($this->payslip['currency'])) {
            $this->payslip['currency'] = $this->helper->getPayslipCurrency();
        }

        if (empty($this->payslip['payment_location'])) {
            $this->payslip['payment_location'] = $this->helper->getPayslipPaymentLocation();
        }

        if (empty($this->payslip['wallet'])) {
            $this->payslip['wallet'] = $this->helper->getPayslipWallet();
        }

        if (empty($this->payslip['acceptance'])) {
            $this->payslip['acceptance'] = $this->helper->getPayslipAcceptance();
        }

        if (empty($this->payslip['document_type'])) {
            $this->payslip['document_type'] = $this->helper->getPayslipDocumentType();
        }

        if (empty($this->payslip['rendering_date'])) {
            $this->payslip['rendering_date'] = $this->helper->getPayslipRenderingDate();
        }

        if (empty($this->payslip['document_date'])) {
            $this->payslip['document_date'] = $this->helper->getPayslipDocumentDate();
        }

        $this->payslip['our_number'] = $this->helper->getPayslipOurNumber($this->payslip);
        $this->payslip['bar_code'] = $this->helper->getPayslipBarCode($this->payslip, $this->cedant);
        $this->payslip['typable_line'] = $this->helper->getPayslipTypableLine($this->payslip, $this->cedant);
    }

    protected function setPaths($data)
    {
        $this->paths = array_fill_keys(array('favicon', 'logo_cedant_bank', 'logo_drawer'), '');
        $this->mergeArrayData($this->paths, $data, 'paths');

        $paths_data = (isset($data['paths']) && is_array($data['paths']) ? $data['paths'] : array());
        $this->mergeConfigData($paths_data, 'paths', $data, 'paths');

        foreach (array('favicon', 'logo_drawer') as $field) {
            if (empty($this->paths[$field]) && !empty($paths_data[$field])) {
                $this->paths[$field] = $paths_data[$field];
            }
        }

        if (empty($this->paths['logo_cedant_bank'])) {
            $this->paths['logo_cedant_bank'] = 'bundles/paggyboleto/images/logo_' . $this->bank . '.jpg';
        }
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

    protected function mergeConfigData(&$target, $config_key, $data, $data_key)
    {
        if (!empty($this->config[$config_key])) {
            // Handling multiple configurations
            if (isset($data[$data_key]) && is_string($data[$data_key]) && isset($this->config[$config_key][$data[$data_key]])) {
                $config = $this->config[$config_key][$data[$data_key]];
            }
            else {
                // Default is the first one
                $config = reset($this->config[$config_key]);
            }
            $target = array_merge($config, $target);
        }
    }
}
