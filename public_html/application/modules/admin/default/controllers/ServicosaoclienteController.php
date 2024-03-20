<?php
class ServicosaoclienteController extends Zend_Controller_Action  {
	
	public function init(){
	}	
	
	public function indexAction(){
		$this->_helper->layout->disableLayout();
		$this->view->objMontadora	= ProdutosBO::listaMontadoras();
		$this->view->objGrupo		= GruposprodBO::listaGruposprodutos();
		$this->view->translate	=	Zend_Registry::get('translate');
		if($this->_request->isPost()):
			$this->view->objProdgrup	= ProdutosBO::buscaGruposcatalogo($this->_getAllParams());
			$this->view->objProdutos	= ProdutosBO::buscaProdutoscatalogo($this->_getAllParams());
			$this->view->objProdveic	= ProdutosBO::buscaProdutosveiculoscatalogo($this->_getAllParams());
			
		endif;		
	}
	
	public function boletoAction(){
		$this->_helper->layout->disableLayout();
		BoletosBO::geraBoleto($this->_getAllParams());
	}
}
?>