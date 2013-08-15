<?php

namespace Paggy\BoletoBundle\Helper;

class BoletoHelper
{
    protected $config;

    protected $dateFormat = 'd/m/Y';

    protected static $bankCodes = array(
        'CEF' => '104-0'
    );

    public function __construct(Array $config)
    {
        $this->config = $config;
    }

    public static function getBankCode($slug)
    {
        $slug = strtoupper($slug);
        return (isset(self::$bankCodes[$slug]) ? self::$bankCodes[$slug] : '');
    }

    public static function getBankSlug($code)
    {
        return strval(array_search($code, self::$bankCodes));
    }

    public function adjustParameters(&$parameters, $subject, $subjectData)
    {
    }

    function getPayslipCurrency()
    {
        return 'R$';
    }

    function getPayslipPaymentLocation()
    {
        return 'Pagável em qualquer Banco até o vencimento';
    }

    function getPayslipWallet()
    {
        return 'SR';
    }

    function getPayslipAcceptance()
    {
        return 'N';
    }

    function getPayslipDocumentType()
    {
        return 'RC';
    }

    function getPayslipRenderingDate()
    {
        return date($this->dateFormat);
    }

    function getPayslipDocumentDate()
    {
        return date($this->dateFormat);
    }

    function getDueFactor($dueDate)
    {
        if (empty($dueDate)) {
            return '0000';
        }
        return \DateTime::createFromFormat($this->dateFormat, $dueDate)->diff(new \DateTime('1997-10-07'))->format('%a');
    }

    function getChecksumModule11($digits, $length = null, $allow_zero = true)
    {
        $digits = preg_replace('/[^0-9]/', '', $digits);

        if (empty($length)) {
            $max = strlen($digits);
        } else {
            $max = abs(intval($length));
            $digits = str_pad($digits, $max, '0', STR_PAD_LEFT);
        }

        $sum = 0;
        for ($i = 1; $i <= $max; $i++) {
            $sum += ((($i - 1) % 8) + 2) * $digits[$max - $i];
        }

        $checksum = 11 - ($sum % 11);

        if ($checksum > 9) {
            $checksum = 0;
        }
        if (!$allow_zero && ($checksum == 0)) {
            $checksum = 1;
        }

        return $checksum;
    }

    function getChecksumModule10($digits)
    {
        $sum = 0;
        $max = strlen($digits);
        for ($i = 1; $i <= $max; $i++) {
            $factor = (($i % 2) + 1) * $digits[$max - $i];
            $sum += (($factor - 1) % 9) + 1;
        }
        $checksum = 10 - ($sum % 10);
        return $checksum;
    }
}
