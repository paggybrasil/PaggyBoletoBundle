<?php

namespace Paggy\BoletoBundle\Helper;

class CefBoletoHelper extends BoletoHelper
{
    public function adjustParameters(&$parameters, $subject, $subjectData)
    {
        $param =& $parameters[$subject];

        if ($subject == 'cedant') {
            if (empty($param['account_code'])) {
                $param['account_code'] = $this->getBankAccountCode($subjectData);
            }
        }

        // continue only if we are dealing with "payslip"
        if ($subject != 'payslip') {
            return;
        }

        if (empty($param['currency'])) {
            $param['currency'] = $this->getPayslipCurrency();
        }

        if (empty($param['payment_location'])) {
            $param['payment_location'] = $this->getPayslipPaymentLocation();
        }

        if (empty($param['wallet'])) {
            $param['wallet'] = $this->getPayslipWallet();
        }

        if (empty($param['acceptance'])) {
            $param['acceptance'] = $this->getPayslipAcceptance();
        }

        if (empty($param['document_type'])) {
            $param['document_type'] = $this->getPayslipDocumentType();
        }

        if (empty($param['rendering_date'])) {
            $param['rendering_date'] = $this->getPayslipRenderingDate();
        }

        if (empty($param['document_date'])) {
            $param['document_date'] = $this->getPayslipDocumentDate();
        }

        $param['our_number']   = $this->getPayslipOurNumber($parameters['payslip']);
        $param['bar_code']     = $this->getPayslipBarCode($parameters['payslip'], $parameters['cedant']);
        $param['typable_line'] = $this->getPayslipTypableLine($parameters['payslip'], $parameters['cedant']);
    }

    function getBankAccountCode($cedantData)
    {
        $accountCode = '';
        if (!empty($cedantData['branch'])) {
            $accountCode .= $cedantData['branch'] . ' / ';
        }
        if (!empty($cedantData['account'])) {
            $accountCode .= $cedantData['account'] . '-' . $this->getChecksumModule11($cedantData['account'], 6);
        }
        return $accountCode;
    }

    function getPayslipPaymentLocation()
    {
        return 'PREFERENCIALMENTE NAS CASAS LOTÉRICAS ATÉ O VALOR LIMITE';
    }

    function getPayslipOurNumber($payslip)
    {
        $ourNumber  = ($payslip['wallet'] == 'RG' ? '1' : '2');
        $ourNumber .= '4';
        $baseNumber = (empty($payslip['our_number']) ? $payslip['document_number'] : $payslip['our_number']); 
        $ourNumber .= substr(str_pad(preg_replace('/[^0-9]/', '', $baseNumber), 15, '0', STR_PAD_LEFT), 0, 15);
        $ourNumber .= '-' . $this->getChecksumModule11($ourNumber, 17);
        return $ourNumber;
    }

    function getPayslipFreeField($payslip, $cedant)
    {
        $freeField = substr(str_pad(preg_replace('/[^0-9]/', '', $cedant['account_code']), 7, '0', STR_PAD_LEFT), -7);
        $freeField .= substr($payslip['our_number'], 2, 3);
        $freeField .= substr($payslip['our_number'], 0, 1);
        $freeField .= substr($payslip['our_number'], 5, 3);
        $freeField .= substr($payslip['our_number'], 1, 1);
        $freeField .= substr($payslip['our_number'], 8, 9);
        $freeField .= $this->getChecksumModule11($freeField, 24);
        return $freeField;
    }

    function getPayslipBarCode($payslip, $cedant)
    {
        $barCode  = '104';
        $barCode .= '9';
        $barCode .= '%';
        $barCode .= $this->getDueFactor($payslip['due_date']);
        $barCode .= substr(str_pad(preg_replace('/[^0-9]/', '', $payslip['value']), 10, '0', STR_PAD_LEFT), -10);
        $barCode .= $this->getPayslipFreeField($payslip, $cedant);
        $checksum = $this->getChecksumModule11(str_replace('%', '', $barCode), 43, false);
        $barCode = str_replace('%', $checksum, $barCode);
        return $barCode;
    }

    function getPayslipTypableLine($payslip)
    {
        $barCode = $payslip['bar_code'];
        $barCode = preg_replace('/[^0-9]/', '', $barCode);
        $barCode = str_pad($barCode, 44, '0', STR_PAD_LEFT);

        $p1 = substr($barCode, 0, 4);
        $p2 = substr($barCode, 19, 5);
        $p3 = $this->getChecksumModule10("$p1$p2");
        $p4 = "$p1$p2$p3";
        $p5 = substr($p4, 0, 5);
        $p6 = substr($p4, 5);
        $field1 = "$p5.$p6";

        $p1 = substr($barCode, 24, 10);
        $p2 = $this->getChecksumModule10($p1);
        $p3 = "$p1$p2";
        $p4 = substr($p3, 0, 5);
        $p5 = substr($p3, 5);
        $field2 = "$p4.$p5";

        $p1 = substr($barCode, 34, 10);
        $p2 = $this->getChecksumModule10($p1);
        $p3 = "$p1$p2";
        $p4 = substr($p3, 0, 5);
        $p5 = substr($p3, 5);
        $field3 = "$p4.$p5";

        $field4 = substr($barCode, 4, 1);

        $p1 = substr($barCode, 5, 4);
        $p2 = substr($barCode, 9, 10);
        $field5 = "$p1$p2";

        $typableLine = "$field1 $field2 $field3 $field4 $field5";
        return $typableLine;
    }
}
