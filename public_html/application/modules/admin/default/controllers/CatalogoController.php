<?php
class CatalogoController extends Zend_Controller_Action  {
	
	public function init(){
	}	
	
	/* public function indexAction(){
		$this->_helper->layout->disableLayout();
		$this->view->objMontadora	= ProdutosBO::listaMontadoras();
		$this->view->objGrupo		= GruposprodBO::listaGruposprodutos();
		$this->view->translate	=	Zend_Registry::get('translate');
		if($this->_request->isPost()):
			$this->view->objProdgrup	= ProdutosBO::buscaGruposcatalogo($this->_getAllParams());
			$this->view->objProdutos	= ProdutosBO::buscaProdutoscatalogo($this->_getAllParams());
			$this->view->objProdveic	= ProdutosBO::buscaProdutosveiculoscatalogo($this->_getAllParams());
			
		endif;		
	} */
	
	public function indexAction(){
		$this->_helper->layout->setLayout('sitepadrao');
		$this->view->objMontadora	= ProdutosBO::listaMontadoras();
		$this->view->objGrupo		= GruposprodBO::listaGruposprodutos();
		$this->view->translate		= Zend_Registry::get('translate');
	
		$sessaobusca = new Zend_Session_Namespace('catalogo');
	
		if($this->_request->isPost()):
			$sessaobusca->where = "";
		endif;
	
		$params = $this->_getAllParams();
	
		if($this->_request->isPost() || $sessaobusca->where != ""):
			$this->view->objProdgrup	= ProdutosBO::buscaGruposcatalogo($this->_getAllParams());
			$this->view->objProdutos	= ProdutosBO::buscaProdutoscatalogo();
			$this->view->objProdveic	= ProdutosBO::buscaProdutosveiculoscatalogo();				
			
		endif;
	}
	
	public function buscacatalogoAction(){
	    $this->_helper->layout->disableLayout();
	    $params = $this->_getAllParams();
	    CatalogosBO::montarCatalogo($params);
	    exit();
	}
	
	
	public function buscasubgrupoAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		$this->view->objGrupsub	= GruposprodBO::listaGruposprodutossub($params['id_grupo']);		
	}
	
	
	public function antigoAction(){
		$this->_helper->layout->setLayout('sitepadrao');
		$this->view->objMontadora	= ProdutosBO::listaMontadoras();
		$this->view->objGrupo		= GruposprodBO::listaGruposprodutos();
		$this->view->translate		= Zend_Registry::get('translate');
	
		$sessaobusca = new Zend_Session_Namespace('catalogo');
	
		if($this->_request->isPost()):
		$sessaobusca->where = "";
		endif;
	
		$params = $this->_getAllParams();
	
		if($this->_request->isPost() || $sessaobusca->where != ""):
		$this->view->objProdgrup	= ProdutosBO::buscaGruposcatalogo($this->_getAllParams());
		$this->view->objProdutos	= ProdutosBO::buscaProdutoscatalogo();
		$this->view->objProdveic	= ProdutosBO::buscaProdutosveiculoscatalogo();
			
		endif;
	}
	
}
?>