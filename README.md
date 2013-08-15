Using PaggyBoletoBundle
===================

Welcome to PaggyBoletoBundle - creating payslips is fun again!

**Basic Doc**

* [Installation](#installation)
* [Your first payslip](#first-payslip)

<a name="installation"></a>

## Installation

### Step 1) Get the bundle using composer

Add on composer.json (see http://getcomposer.org/)

    "require" :  {
        // ...
        "paggy/boleto-bundle": "dev-master",
    }

And run:

    composer update paggy/boleto-bundle

### Step 2) Register the bundle

To start using the bundle, register it in your Kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Paggy\BoletoBundle\PaggyBoletoBundle,
    );
    // ...
}
```

And run the console command to install its assets:

     php app/console assets:install

### Step 3) Configure the bundle

This step is not required, but if you skip this step,
you may need to provide the same information in your PHP code.

```yaml
# app/config/config.yml
# PaggyBoleto Configuration:
paggy_boleto:
    cedants:
        mybusiness:
            name: My Enterprise Inc.
            cnpj: 01.234.567/0001-89
            bank: CEF
            branch: 1234
            account: 345678
    paths:
        default:
            favicon: bundles/mysite/images/favicon.ico
    payslips:
        cef:
            wallet: RG
            instructions: |
                - Sr. Caixa, após o vencimento, cobrar multa de 2%% e juros de mora de 0,33%% ao dia
                - Receber até 30 dias após o vencimento
```

<a name="first-payslip"></a>

## Create your first payslip!

To create a payslip, get the `paggy_boleto.view` service and call its
`render` function, passing the payslip data as an array argument.

An example would look like this:

```php
<?php
// src/Acme/DemoBundle/Controller/Payment.php
namespace Acme\DemoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends Controller
{
    public function payslipAction()
    {
        $data = array(
            'payer_name'              => 'John Doe',
            'payer_address_line1'     => 'Success Street, 108',
            'payer_address_line2'     => 'Success City, SS',
            'payslip_value'           => number_format('180', 2, ',', ''),
            'payslip_due_date'        => date('d/m/Y'),
            'payslip_document_number' => '1567',
            'payslip_description'     => 'Premium Hosting',
        );
        return new Response($this->get('paggy_boleto.view')->render($data));
    }
}
```
