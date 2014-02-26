ZF2-PHPBol
==========

# Introdução

Módulo Zend Framework 2 para geração de Boletos/Bloquetos de cobrança 
dos principais banco brasileiros. Este projeto é baseado no [PHPBol](https://github.com/rafaelgou/PHPBol)

## Características

* Gera XHTML validado pela W3C
* Impressão de carnês com quebra de página
* Altamente extensível
* Facilita integração banco de dados

## Dependências
As dependências já serão resolvidas com a utilização do composer

* PHP >=5.3.3
* [Zend Framework 2.2.*](https://github.com/zendframework/zf2)
* [DOMPdf-Module](https://github.com/raykolbe/DOMPDFModule)
* [Twig ](https://github.com/fabpot/Twig)
* [Twig Extensions](https://github.com/fabpot/Twig-extensions)

## Instalação via Composer

  1. `cd my/project/directory`
  2. criar um arquivo `composer.json` com o seguinte conteudo:

     ```json
     {
         "require": {
             "adaoex/zf2-phpbol": "dev-master"
         }
     }
     ```
  3. install PHP Composer via `curl -s http://getcomposer.org/installer | php` (on windows, download
     http://getcomposer.org/installer and execute it with PHP)
  4. executar `php composer.phar install`
  5. abrir `my/project/directory/config/application.config.php` e adicionar os seguinte código em `modules`: 

     ```php
     'PHPBol',
     ```

# Carteiras Implementadas

## Banco do Brasil
A geração de boletos de cobraça do Banco do Brasil é descrita neste
documento: www.bb.com.br/docs/pub/emp/empl/dwn/Doc5175Bloqueto.pdf

* Carteira 18
Até o momente foram implementadas alguns convênios da carteira 18:
- Carteira 18, convênio 8 dígitos, nosso número com 9 dígitos
- Carteira 18, convênio 7 dígitos, nosso número com 10 dígitos
- Carteira 18, convênio 6 dígitos, nosso número com 17 dígitos

## Caixa Econômica Federal
A geração de boletos de cobraça do CEF é descrita neste documento
http://downloads.caixa.gov.br/_arquivos/cobranca_caixa_sigcb/manuais/CODIGO_BARRAS_SIGCB.PDF

* até o momento nenhuma carteira implementara

## Utilização

No arquivo `my/project/directory/config/module.config.php` configure:
```php
'view_manager' => array(
   ...
   'template_map' => array(
	   ...
	   'boleto/layout' => __DIR__ . '/../../Application/view/layout/boletobb.phtml',
   ),
   'template_path_stack' => array(
	   ...
	   __DIR__ . '/../../Application/view',
   ),
),
```


```php
<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use DOMPDFModule\View\Model\PdfModel;
use PHPBol\Boleto\Factory;

class BoletoController extends AbstractActionController
{
    public function boletoPdfAction()
    {
		/* seta layout para o boleto */
		$this->layout('layout/boletobb');
		
		$cedente = array(
            'nome'     => 'Nome da Empresa',
            'cpfcnpj'  => 'NUMERO CNPJ ou CPF',
            'endereco' => '',
            'bairro'   => '',
            'cidade'   => '',
            'uf'       => '',
            'cep'      => '',
        );

        $sacado = array(
            'logo'     => '',
            'nome'     => '',
            'cpfcnpj'  => '',
            'endereco' => '',
            'bairro'   => '',
            'cidade'   => '',
            'uf'       => '',
            'cep'      => '',
        );

        $avalista = array(
            'nome'     => '',
            'cpfcnpj'  => '',
        );
        
        $boletoData = array(
            'nossoNumero'          => $nossoNumero,
            'numeroDocumento'      => '',
            'dataVencimento'       => new \DateTime(),
            'dataEmissaoDocumento' => new \DateTime(),
            'dataProcessamento'    => new \DateTime(),
            'valorBoleto'          => 100.00,
            'quantidade'           => 1,
            'valorUnitario'        => null,
            'aceite'               => '',
            'especie'              => 'R$',
            'especieDoc'           => 'DM',
            'codigoBarra'          => '',
            'demonstrativo'        => '',
            'instrucoes'           => '<br />Senhor caixa,<br />'
                                    . '- Após o vencimento, cobrar multa de 2%<br />'
                                    . '- Após o vencimento, cobrar juros diário de 1%.<br />',
        );

        $img_logo = fread(fopen( realpath('./public/img/boleto/logobb.jpg'), "r"), filesize(realpath('./public/img/boleto/logobb.jpg')));
        
        $banco = array(
            'logo' => base64_encode($img_logo),
            'codigoCedente' => '0055',
            'codigo' => '001',
            'codigoDv' => '9',
            'agencia' => '0055',
            'agenciaDv' => '',
            'conta' => '0055',
            'contaDv' => 'X',
            'carteira' => '18',
            'variacao' =>  '027',
            'convenio' => '000555',
            'qtd_nosso_numero' => '17',
        );

        // Criando instância e definindo dados
        // Utilizando o recurso de chain
        $boleto = Factory::create('BB')
                ->setBanco($banco)
                ->setCedente($cedente)
                ->setSacado($sacado)
                ->setAvalista($avalista)
                ->setBoletoData($boletoData)
                ->setBarcodeImgBase64();
		
        $pdf = new PdfModel();
		/* Triggers PDF download, automatically appends ".pdf" */
        $pdf->setOption('filename', 'monthly-report');
        $pdf->setOption('paperSize', 'a4'); // Defaults to "8x11"
        $pdf->setOption('paperOrientation', 'landscape'); // Defaults to "portrait"
        
        // To set view variables
        $pdf->setVariables(array(
			'html' => $boleto->render('bb_boleto')'
        ));
        
        return $pdf;
    }
}
```

No arquivo da view `my/project/directory/Application/view/boleto/boleto-pdf.phtml` codifique:
```php
<?php 
echo $this->html;

```

Dúvidas? Comente!