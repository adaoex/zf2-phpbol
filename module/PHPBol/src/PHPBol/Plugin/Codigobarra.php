<?php
/**
 * Plugin responsável pela geração de códigos de barras
 *
 * @author Adão Goncalves <adao@adao.eti.br>
 */
namespace PHPBol\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Barcode\Barcode;

class Codigobarra extends AbstractPlugin {
    
    /**
     * @return Boolean Sucesso na criação da imagem
     */
    public function create( $value, $options = array(), $barcodeType = 'code25interleaved', $type = 'image' ){
        $bol_ret = true;
        $value = trim($value);
        try{
            /* Junta a configuração padrão com $options informado */
            $barcodeOptions = array_merge(array('text' => $value, 'drawText' => false ), $options);

            /*  Não há opções necessárias */
            $rendererOptions = array();

            // Desenha o código de barras em uma nova imagem,
            // enviar os cabeçalhos e a imagem
            
            $imageResource = Barcode::factory(
                $barcodeType, $type, $barcodeOptions, $rendererOptions
            )->draw();
             
            // Enable output buffering
            ob_start();
            imagepng($imageResource);
            imagedestroy($imageResource);
            // Capture the output
            $imagedata = ob_get_contents();
            // Clear the output buffer
            ob_end_clean();

            return base64_encode($imagedata);
            
        } catch (Exception $e){
            $bol_ret = false;
            echo 'Erro: na geraçãod e código de barra!';
            echo 'Código: '. $e->getCode();
            echo 'Mensagem: '. $e->getMessage();
            echo 'Trace: '. $e->getTraceAsString();
        }
        return $bol_ret;
    }
}
