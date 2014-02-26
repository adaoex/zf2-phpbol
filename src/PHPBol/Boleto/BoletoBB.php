<?php

/*
 * ZF2-Boleto ( https://github.com/adaoex/ZF2-Boleto )
 * Módulo Zend Framework 2 para geração de boletos
 *
 * @link      https://github.com/adaoex/ZF2-Boleto repositório do projeto
 * @copyright Copyright (c) 2014 Adão Gonçalves
 * @license   https://github.com/adaoex/ZF2-Boleto/blob/master/LICENSE The MIT License (MIT)
 */

namespace PHPBol\Boleto;

use Zend\Barcode\Barcode;

/**
 * Classe BoletoBB
 * Contém funções elementares para Templates do Banco do Brasil
 *
 * @package phpbol
 * @subpackage boleto
 * @author Francisco Luz <drupalist@naosei.com>
 * @author Rafael Goulart <rafaelgou@gmail.com>
 * @author Adão Gonçalves <adao@adao.eti.br>
 */
class BoletoBB extends AbstractBoleto {

    const CODIGO_BANCO = '001';
    const MOEDA = '9';

    /**
     * Retorna do codigo de barra 
     * 
     * @author Adão Gonçalves <adao@adao.eti.br>
     * @return string - Posições de 1 á 44 do código de barras
     */
    public function getCodigoBarra() {

        $boleto = $this->offsetGet('boletoData')->getData();
        $banco = $this->offsetGet('banco')->getData();

        /* agencia é sempre com 4 digitos */
        $agencia = str_pad($banco['agencia'], 4, 0, STR_PAD_LEFT);
        /* conta é sempre com 4 digitos */
        $conta = str_pad($banco['conta'], 4, 0, STR_PAD_LEFT);
        /* valor tem 10 digitos, sem virgula */
        $valor = number_format($boleto['valorBoleto'], 2, '', '');
        $valor = str_pad($valor, 10, 0, STR_PAD_LEFT);

        $servico = '21'; // fixo: para serviço atual
        $codigo_banco_com_dv = $this->getCodigoBanco(self::CODIGO_BANCO);
        $fator_vencimento = $this->getFatorVencimento($boleto['dataVencimento']);
        $carteira = explode('-', $banco['carteira']);
        $carteira_sub = $banco['carteira_sub'] = '';

        if (isset($carteira[1])) {
            $carteira_sub = $banco['carteira_sub'] = $carteira[1];
        }
        //pre calculate nosso_numero check digit
        $digit = $this->modulo_11($banco['convenio'] . $boleto['nossoNumero']);
        $checkDigit['digito'] = '-' . $digit;

        /** posições de 20 á 44 */
        switch ($banco['carteira']) {
            case 18:
                // Agora, precisamos saber quantos dígitos número convenio tem
                $conv_len = strlen($banco['convenio']);
                switch ($conv_len) {
                    case 8:
                        // 20-33 -> Convenio                   14
                        // 34-42 -> Nosso Número (sem dígito)   9
                        // 43-44 -> Carteira                    2
                        $convenio = str_pad($banco['convenio'], 14, 0, STR_PAD_LEFT);
                        $nosso_numero = str_pad($boleto['nossoNumero'], 9, 0, STR_PAD_LEFT);

                        // 25 digits long
                        $code = $convenio . $nosso_numero . $banco['carteira'];
                        break;
                    case 7:
                        // 20-32 -> Convenio                   13
                        // 33-42 -> Nosso Número (sem dígito)  10
                        // 43-44 -> Carteira                    2
                        $convenio = str_pad($banco['convenio'], 13, 0, STR_PAD_LEFT);
                        $nosso_numero = str_pad($boleto['nossoNumero'], 10, 0, STR_PAD_LEFT);

                        //25 digits long
                        $code = $convenio . $nosso_numero . $banco['carteira'];

                        //no check digit for nosso_numero
                        $checkDigit['digito'] = '';

                        break;
                    case 6:
                        if ($servico == 21) {
                            // 20-25 -> Convenio                   6
                            // 26-42 -> Nosso Número (sem dígito)  17
                            // 43-44 -> Servico                    2
                            $convenio = str_pad($banco['convenio'], 6, 0, STR_PAD_LEFT);
                            $nosso_numero = str_pad($boleto['nossoNumero'], 17, 0, STR_PAD_LEFT);

                            /* 25 digits long code */
                            $code = $convenio . $nosso_numero . $servico;
                        } else {
                            // 20-25 -> Convenio                   6
                            // 26-30 -> Nosso Número (sem dígito)  5
                            // 31-34 -> Agencia                    4
                            // 35-42 -> Conta                      8
                            // 43-44 -> Carteira                   2
                            $convenio = str_pad($banco['convenio'], 6, 0, STR_PAD_LEFT);
                            $nosso_numero = str_pad($boleto['nossoNumero'], 17, 0, STR_PAD_LEFT);

                            //25 digits long
                            $code = $convenio . $nosso_numero . $agencia . $conta . $banco['carteira'];
                        }
                        break;
                }
                break;
            /**
             * TODO: Detalhes das demais carteiras do Banco do Brasil
             *       Documentção em www.bb.com.br/docs/pub/emp/empl/dwn/Doc5175Bloqueto.pdf
             */
        }

        $dv = $this->modulo_11(self::CODIGO_BANCO . self::MOEDA . $fator_vencimento . $valor . $code);
        
        /** posições de 1 á 44 */
        $codigo = self::CODIGO_BANCO . self::MOEDA . $dv . $fator_vencimento . $valor . $code;
        
        /** seta Linha Digitavel */
        $this->getLinhaDigitavel( $codigo );

        return $codigo;
    }

    /**
     * Gera uma imagem com barras no formato base64
     * seta o parametro 'codigoBarra' das classe 'boletoData'
     */
    public function setBarcodeImgBase64() {
        try {
            /*
             * Opções do Zend\Barcode 
             *  text: valor para gerar as barras
             *  drawText:(false) mostrar o texto do código abaixo das barras
             */
            $barcodeOptions = array('text' => $this->getCodigoBarra(), 'drawText' => false);

            /* Desenha o código de barras em uma nova imagem
             *  $barcodeType = 'code25interleaved' - formato utilizado pelo 
             *      Banco do Brasil
             *  $type = 'image' - gera uma imagem
             */
            $imageResource = Barcode::factory(
                            'code25interleaved', 'image', $barcodeOptions, array()
                    )->draw();

            // habilitar saida buffering
            ob_start();
            imagepng($imageResource);
            imagedestroy($imageResource);
            // Capture a output
            $imagedata = ob_get_contents();
            // limpa saida buffer
            ob_end_clean();

            $img = base64_encode($imagedata);
            
            /* set */
            $this->offsetGet('boletoData')->setData(array('codigoBarra' => $img));
            
            return $this;
        
        } catch (Exception $e) {
            echo 'Erro: na geração de código de barra!';
            echo 'Código: ' . $e->getCode();
            echo 'Mensagem: ' . $e->getMessage();
            echo 'Trace: ' . $e->getTraceAsString();
            return false;
        }
    }

    /**
     * Montagem da linha digitável - Função tirada do PHPBoleto
     * 
     * @param string $linha Código de barras
     * @return $this
     */
    function getLinhaDigitavel($linha = null) {
        
        // Posição 	Conteúdo
        // 1 a 3    Número do banco
        // 4        Código da Moeda - 9 para Real
        // 5        Digito verificador do Código de Barras
        // 6 a 19   Valor (12 inteiros e 2 decimais)
        // 20 a 44  Campo Livre definido por cada banco
        // 1. Campo - composto pelo código do banco, código da moéda, 
        // as cinco primeiras posições
        // do campo livre e DV (modulo10) deste campo
        $p1 = substr($linha, 0, 4);
        $p2 = substr($linha, 19, 5);
        $p3 = $this->modulo_10("$p1$p2");
        $p4 = "$p1$p2$p3";
        $p5 = substr($p4, 0, 5);
        $p6 = substr($p4, 5);
        $campo1 = "$p5.$p6";

        // 2. Campo - composto pelas posiçoes 6 a 15 do campo livre
        // e livre e DV (modulo10) deste campo
        $p1 = substr($linha, 24, 10);
        $p2 = $this->modulo_10($p1);
        $p3 = "$p1$p2";
        $p4 = substr($p3, 0, 5);
        $p5 = substr($p3, 5);
        $campo2 = "$p4.$p5";

        // 3. Campo composto pelas posicoes 16 a 25 do campo livre
        // e livre e DV (modulo10) deste campo
        $p1 = substr($linha, 34, 10);
        $p2 = $this->modulo_10($p1);
        $p3 = "$p1$p2";
        $p4 = substr($p3, 0, 5);
        $p5 = substr($p3, 5);
        $campo3 = "$p4.$p5";

        // 4. Campo - digito verificador do codigo de barras
        $campo4 = substr($linha, 4, 1);

        // 5. Campo composto pelo valor nominal pelo valor nominal do documento, sem
        // indicacao de zeros a esquerda e sem edicao (sem ponto e virgula). Quando se
        // tratar de valor zerado, a representacao deve ser 000 (tres zeros).
        $campo5 = substr($linha, 5, 14);

        $this->offsetGet('boletoData')->setData(array('linhaDigitavel' => "$campo1 $campo2 $campo3 $campo4 $campo5"));

        return $this;
    }

    /**
     * Cálculo do código verificador do banco
     * 
     * @param $numero Codigo do banco
     * @return int Dígito Verificador (DV)
     */
    function getCodigoBanco($numero) {
        $parte1 = substr($numero, 0, 3);
        $parte2 = $this->modulo_11($parte1);
        return $parte1 . "-" . $parte2;
    }

    /**
     * 
     * #################################################
     * FUNÇÃO DO MÓDULO 10 RETIRADA DO PHPBOLETO
     * 
     * ESTA FUNÇÃO PEGA O DÍGITO VERIFICADOR DO PRIMEIRO, SEGUNDO
     * E TERCEIRO CAMPOS DA LINHA DIGITÁVEL
     * #################################################
     * 
     * @param $num Valor para calculo do DV
     * @return int Dígito Verificador (DV)
     */
    private function modulo_10($num) {
        $numtotal10 = 0;
        $fator = 2;

        for ($i = strlen($num); $i > 0; $i--) {
            $numeros[$i] = substr($num, $i - 1, 1);
            $parcial10[$i] = $numeros[$i] * $fator;
            $numtotal10 .= $parcial10[$i];
            if ($fator == 2) {
                $fator = 1;
            } else {
                $fator = 2;
            }
        }

        $soma = 0;
        for ($i = strlen($numtotal10); $i > 0; $i--) {
            $numeros[$i] = substr($numtotal10, $i - 1, 1);
            $soma += $numeros[$i];
        }
        $resto = $soma % 10;
        $digito = 10 - $resto;
        if ($resto == 0) {
            $digito = 0;
        }

        return $digito;
    }

    /**
     * #################################################
     * FUNÇÃO DO MÓDULO 11 RETIRADA DO PHPBOLETO
     * 
     * Esta função caucula o Dígito Verificador de:
     *  NOSSONUMERO
     *  AGENCIA
     *  CONTA
     *  CAMPO 4 DA LINHA DIGITÁVEL
     * #################################################
     * 
     * @param  int $num Valor para calculo do DV
     * @return int Dígito Verificador (DV)
     */
    private function modulo_11($num, $base = 9, $r = 0) {
        $soma = 0;
        $fator = 2;
        for ($i = strlen($num); $i > 0; $i--) {
            $numeros[$i] = substr($num, $i - 1, 1);
            $parcial[$i] = $numeros[$i] * $fator;
            $soma += $parcial[$i];
            if ($fator == $base) {
                $fator = 1;
            }
            $fator++;
        }
        if ($r == 0) {
            $soma *= 10;
            $digito = $soma % 11;

            //corrigido
            if ($digito == 10) {
                $digito = "X";
            }

            /**
              alterado por Daniel Schultz

              Detalhes:

              O módulo 11 só gera os digitos verificadores do nossonumero,
              agencia, conta e digito verificador com codigo de barras (aquele que fica sozinho e triste na linha digitável)
              só que é foi um rolo...pq ele nao podia resultar em 0, e o pessoal do phpboleto se esqueceu disso...

              No BB, os dígitos verificadores podem ser X ou 0 (zero) para agencia, conta e nosso numero,
              mas nunca pode ser X ou 0 (zero) para a linha digitável, justamente por ser totalmente numérica.

              Quando passamos os dados para a função, fica assim:

              Agencia = sempre 4 digitos
              Conta = até 8 dígitos
              Nosso número = de 1 a 17 digitos

              A unica variável que passa 17 digitos é a da linha digitada, justamente por ter 43 caracteres

              Entao vamos definir ai embaixo o seguinte...

              se (strlen($num) == 43) { não deixar dar digito X ou 0 }
             */
            if (strlen($num) == "43") {
                //então estamos checando a linha digitável
                if ($digito == "0" or $digito == "X" or $digito > 9) {
                    $digito = 1;
                }
            }
            return $digito;
        } elseif ($r == 1) {
            $resto = $soma % 11;
            return $resto;
        }
    }

    /**
     * Cálculo do fator de vencimento conforme regras estabelecidas na
     * Documentação do BB em: www.bb.com.br/docs/pub/emp/empl/dwn/Doc5175Bloqueto.pdf
     * 
     * @param DateTime $data 
     * @return int - Fator de vencimento em 4 dígitos
     */
    function getFatorVencimento($data) {
        if ($data instanceof \DateTime) {
            $ano = $data->format('Y');
            $mes = $data->format('m');
            $dia = $data->format('t');
        } else {
            $data = explode("/", $data);
            $ano = $data[2];
            echo $mes = $data[1];
            echo $dia = $data[0];
            $data = new \DateTime($ano . '-' . $mes . '-' . $dia);
        }
        $data_base = new \DateTime('1997-10-07');
        $direfença = $data->diff($data_base);
        /* retorna a diferença em dias */
        return $direfença->days;
    }

    /** :: Demais funções Auxiliares :: */
    function esquerda($entra, $comp) {
        return substr($entra, 0, $comp);
    }

    function direita($entra, $comp) {
        return substr($entra, strlen($entra) - $comp, $comp);
    }

    function _dateToDays($year, $month, $day) {
        $data = new \DateTime($year . '-' . $month . '-' . $day);
        $data_base = new \DateTime('1997-10-07');
        $direfença = $data->diff($data_base);
        return $direfença->days;
    }

}