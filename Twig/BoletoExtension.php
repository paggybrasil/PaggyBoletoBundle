<?php

namespace Paggy\BoletoBundle\Twig;

use Twig_Environment as Environment;

class BoletoExtension extends \Twig_Extension
{
    protected $barcodes = array(
        0 => '00110',
        1 => '10001',
        2 => '01001',
        3 => '11000',
        4 => '00101',
        5 => '10100',
        6 => '01100',
        7 => '00011',
        8 => '10010',
        9 => '01010'
    );

    protected $barcodeThin;
    protected $barcodeThick;
    protected $barcodeHeight;
    protected $barcodeBlack;
    protected $barcodeWhite;

    protected $twig;
    protected $assetFunction;

    public function __construct()
    {
        // initialize barcodes data
        for ($f1 = 9; $f1 >=0 ; $f1--) {
            for($f2 = 9; $f2 >= 0; $f2--) {
                $code = '';
                for ($i = 0; $i < 5; $i++) { 
                    $code .=  substr($this->barcodes[$f1], $i, 1) . substr($this->barcodes[$f2], $i, 1);
                }
                $num = ($f1 * 10) + $f2 ;
                $this->barcodes[$num] = $code;
            }
        }
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('onlyNumbers', array($this, 'onlyNumbers')),
            new \Twig_SimpleFilter('formatCpfCnpj', array($this, 'formatCpfCnpj')),
            new \Twig_SimpleFilter('formatCpf', array($this, 'formatCpf')),
            new \Twig_SimpleFilter('formatCnpj', array($this, 'formatCnpj')),
            new \Twig_SimpleFilter('drawBarCode', array($this, 'drawBarCode')),
        );
    }

    public function initRuntime(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function onlyNumbers($string)
    {
        return preg_replace('/[^0-9]/', '', $string);
    }

    public function formatCpfCnpj($cpfCnpj)
    {
        return (strlen($this->onlyNumbers($cpfCnpj)) > 11 ? $this->formatCnpj($cpfCnpj) : $this->formatCpf($cpfCnpj));
    }

    public function formatCpf($cpf)
    {
        $cpf = $this->onlyNumbers($cpf);
        $cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);
        $cpf = substr($cpf, 0, 11);
        return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
    }

    public function formatCnpj($cnpj)
    {
        $cnpj = $this->onlyNumbers($cnpj);
        $cnpj = str_pad($cnpj, 14, '0', STR_PAD_LEFT);
        $cnpj = substr($cnpj, 0, 14);
        return substr($cnpj, 0, 2) . '.' . substr($cnpj, 2, 3) . '.' . substr($cnpj, 5, 3) . '/' . substr($cnpj, 8, 4) . '-' . substr($cnpj, 12, 2);
    }

    public function drawBarCode($barCode, $height = 50, $thinWidth = 1, $thickWidth = 3)
    {
        $this->barcodeHeight = intval($height);
        $this->barcodeThin   = intval($thinWidth);
        $this->barcodeThick  = intval($thickWidth);
        $this->barcodeBlack  = $this->asset('bundles/paggyboleto/images/black.png');
        $this->barcodeWhite  = $this->asset('bundles/paggyboleto/images/white.png');

        $html = '';
        $html .= $this->barHtml('black', 'thin');
        $html .= $this->barHtml('white', 'thin');
        $html .= $this->barHtml('black', 'thin');
        $html .= $this->barHtml('white', 'thin');

        $code = $barCode;
        if ((strlen($code) % 2) <> 0) {
            $code = '0' . $code;
        }

        while (strlen($code) > 0) {
            $two_digits = substr($code, 0, 2);
            $code = substr($code, 2);
            $lines = $this->barcodes[intval($two_digits)];
            for ($i = 0; $i < 10; $i++) {
                $color = ($i % 2 ? 'white' : 'black');
                $width = (substr($lines, $i, 1) == '0' ? 'thin' : 'thick');
                $html .= $this->barHtml($color, $width);
            }
        }

        $html .= $this->barHtml('black', 'thick');
        $html .= $this->barHtml('white', 'thin');
        $html .= $this->barHtml('black', 'thin');

        return $html;
    }

    protected function barHtml($color, $width)
    {
        return sprintf(
            '<img src="%s" width="%s" height="%s" border="0" />',
            ($color == 'white' ? $this->barcodeWhite : $this->barcodeBlack),
            ($width == 'thin' ? $this->barcodeThin : $this->barcodeThick),
            $this->barcodeHeight
        );
    }

    protected function asset($asset)
    {
        if (empty($this->assetFunction)) {
            $this->assetFunction = $this->twig->getFunction('asset')->getCallable();
        }
        return call_user_func($this->assetFunction, $asset);
    }

    public function getName()
    {
        return 'paggy_boleto_extension';
    }
}
