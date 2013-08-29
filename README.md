ZF2-PHPBol
==========

# Introdução

Módulo Zend Framework 2 para geração de Boletos/Bloquetos de cobrança 
dos principais banco brasileiros. Este projeto é baseado no [PHPBol](https://github.com/rafaelgou/PHPBol)

## Características

* Suporte ao PHP 5.3 (namespaces) e 5.2
* Completamente escrito seguindo padrões
* Gera XHTML validado pela W3C
* Impressão de carnês com quebra de página
* Altamente extensível
* Código desacoplado
* Facilita integração com banco de dados

## Dependências
As dependências já serão resolvidas com a utilização do composer

* PHP >=5.3.3
* [Zend Framework 2.2.*](https://github.com/zendframework/zf2)
* [DOMPdf-Module](https://github.com/raykolbe/DOMPDFModule)
* [Twig ](https://github.com/fabpot/Twig)
* [Twig Extensions](https://github.com/fabpot/Twig-extensions)

## Instalação via Composer

  1. `cd my/project/directory`
  2. crie um arquivo `composer.json` com o conteúdo seguinte:

     ```json
     {
         "require": {
             "adaoex/zf2-phpbol": "dev-master"
         }
     }
     ```
  3. instalar PHP Composer via `curl -s http://getcomposer.org/installer | php` (no windows, download
     http://getcomposer.org/installer e execute o PHP)
  4. execute `php composer.phar install`
  
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

O controller 'IndexController' do módulo 'Application' (extends o [ZendSkeletonApplication](https://github.com/zendframework/ZendSkeletonApplication) )
localizados em 'my/project/directory/module/Application/src/Application/Controller/IndexController.php'
pode ser utilizado como exemplo de implementação.
