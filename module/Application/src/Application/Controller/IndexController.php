<?php
/**
 * ZF2-PHPBol (http://blog.adao.eti.br/phpbol/)
 *
 * @link      http://github.com/adaoex/zf2-phpbol
 * @copyright Adão Gonçalves
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use DOMPDFModule\View\Model\PdfModel;
use PHPBol\Boleto\Factory;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }
    
    public function boletoAction(){
        
        /** 
         * Defini layout proprio do boleto
         * 
         * Confirugações necessárias no módulo que utilizará este controller
         * no arquivo "module.config.php"
         *  
         * 'view_manager' => array(
         *      ...
         *      'template_map' => array(
         *          ...
         *          'boleto/layout'           => __DIR__ . '/../../Base/view/layout/boletobb.phtml',
         *      ),
         *      'template_path_stack' => array(
         *          ...
         *          __DIR__ . '/../../Base/view',
         *      ),
         *  ),
         */
        // $this->layout('layout/boletobb');
        
        // Definindo dados com array
        $global = array(
            'titulo'    => 'Boleto BB Teste',
            'logo'      => '',
        );

        $cedente = array(
            'nome'     => 'MUTUA CAIXA DE ASSISTENCIA DOS PROF DO CREA',
            'cpfcnpj'  => '01.123.500/0001-45',
            'endereco' => 'SCLN 409, Bloco B, Ed. Mútua',
            'bairro'   => 'Asa Norte',
            'cidade'   => 'Brasília',
            'uf'       => 'DF',
            'cep'      => '70857-550',
        );

        $sacado = array(
            'logo'     => '',
            'nome'     => 'Rafael Goulart',
            'endereco' => 'Rua da Feira, s/n',
            'cpfcnpj'  => '555.666.777-88',
            'bairro'   => 'Centro',
            'cidade'   => 'Santana do Livramento',
            'uf'       => 'RS',
            'cep'      => '97000-222',
        );

        $avalista = array(
            'nome'     => 'Joaquim José da Silva Xavier',
            'cpfcnpj'  => '001.002.003-44',
        );

        $nossoNumero = '10738850201200001';
        $_dataVencimento = new \DateTime('2013-08-10');
        $_dataEmissaoDocumento = new \DateTime('2013-08-01');
        $_dataProcessamento = new \DateTime('2013-08-01');
        $dataVencimento = $_dataVencimento->format('d/m/Y');
        $dataEmissaoDocumento = $_dataEmissaoDocumento->format('d/m/Y');
        $dataProcessamento = $_dataProcessamento->format('d/m/Y');
        $valorBoleto = 153.88;
        
        $boletoData = array(
            'nossoNumero'          => $nossoNumero,
            'numeroDocumento'      => '',
            'dataVencimento'       => $_dataVencimento,
            'dataEmissaoDocumento' => $_dataEmissaoDocumento,
            'dataProcessamento'    => $_dataProcessamento,
            'valorBoleto'          => $valorBoleto,
            'quantidade'           => 1,
            'valorUnitario'        => null,
            'aceite'               => '',
            'especie'              => 'R$',
            'especieDoc'           => 'DM',
            'codigoBarra'          => '',
            'demonstrativo'        => 'Mensalidade 4/2010<br/>'
                                    . 'Evite corte de serviços, pague em dia<br/>'
                                    . 'Não esqueça da minha calói',
            'instrucoes'           => '- Conceder desconto de pontualidade de R$ 5,00 para pagamento até a data do vencimento.<br/>'
                                    . '- Após o vencimento, cobrar juros diário de R$ 0,20.<br/>'
                                    . '- 10 dias após o vencimento, cobrar valor fixo de R$ 47,00. (Serviços suspensos até o pagamento).',
        );

        /* Conversão da imagem para base64 */
        $img_path_logo = './public/img/boleto/logobb.jpg';
        $img_logo = fread(fopen( realpath( $img_path_logo ), "r"), filesize(realpath( $img_path_logo )));
        
        $banco = array(
            'logo'              => base64_encode($img_logo),
            'codigoCedente'     => '3382',
            'codigo'            => '001',
            'codigoDv'          => '9',
            'agencia'           => '3382',
            'agenciaDv'         => '0',
            'conta'             => '3245',
            'contaDv'           => 'X',
            'carteira'          => '18',
            'variacao'          => '027',
            'convenio'          => '811976',
        );

        // Criando instância e definindo dados
        // Utilizando o recurso de chain
        $boleto = Factory::create('BB')
                ->setGlobal($global)
                ->setBanco($banco)
                ->setCedente($cedente)
                ->setSacado($sacado)
                ->setAvalista($avalista)
                ->setBoletoData($boletoData)
                ->setBarcodeImgBase64();
       
        return new ViewModel(array(
          'html' => $boleto->render('bb_boleto')
        ));
        
        /* :: Para Imprimir PDF ::
         * 
        $pdf = new PdfModel(); // PdfModel extends ViewModel
        $pdf->setOption('filename', 'boleto'); // Triggers PDF download, automatically appends ".pdf"
        $pdf->setOption('paperSize', 'a4'); // Defaults to "8x11"
        // $pdf->setOption('paperOrientation', 'landscape'); // Defaults to "portrait"

        $pdf->setVariables(array(
            'html' => $boleto->render('bb_boleto')
        ));
        return $pdf;
        */
        
    }
}
