<?php

class Admin_SoapController extends Zend_Controller_Action
{
    public function init(){
        $options = array(
    		'location' => 'http://homologacao.ztlbrasil.com.br/soap/soap',
    		'uri' => 'http://homologacao.ztlbrasil.com.br/soap/'
        );
        
        $this->soap = new SoapClient(null, $options);
    }
    
    public function atualizaprodutoAction(){
        $this->_helper->viewRenderer->setNoRender();
        $params = $this->_getAllParams();
        
        $arrayRes = ProdutosBO::atualizaProd($params);
                
        /* if($params['tipo'] == 'produto') {
            $produto = $this->soap->buscaProdutoscodigo($params['codigo']);
            echo ProdutosBO::atualizaProd($produto);            
        }else if($params['tipo'] == 'componentes') {
            $kit = $this->soap->listarKits($params['codigo']);
            echo ProdutosBO::atualizaComponentes($kit);
        } */
        
        echo $arrayRes['sit']."|".$arrayRes['titulo']."|".$arrayRes['motivo'];
        
    	exit();
    }
    
}