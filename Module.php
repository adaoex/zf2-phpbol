<?php
/**
 * ZF2-Boleto ( https://github.com/adaoex/ZF2-Boleto )
 * Módulo Zend Framework 2 para geração de boletos
 *
 * @link      https://github.com/adaoex/ZF2-Boleto repositório do projeto
 * @copyright Copyright (c) 2014 Adão Gonçalves
 * @license   https://github.com/adaoex/ZF2-Boleto/blob/master/LICENSE The MIT License (MIT)
 */

namespace PHPBol;

use Zend\Mvc\MvcEvent;

/**
 * Classe Modulo
 * 
 * @author  Adão Gonçalves <adao@adao.eti.br>
 */
class Module
{
    
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
}
