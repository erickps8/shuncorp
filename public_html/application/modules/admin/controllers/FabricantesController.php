<?php

class Admin_FabricantesController extends Zend_Controller_Action {
			
	public function init()
	{
		if ( !Zend_Auth::getInstance()->hasIdentity() ) {
	    	$this->_redirect('/');
	    }
		        
        //LogBO::cadastraLoggenerico($this->_getAllParams());	    
	}
	
	public function topoAction($ativo='compras'){		
		$params = $this->_getAllParams();
		if(!empty($params['idioma'])):
			$sessao = new Zend_Session_Namespace('Idiomas');
		    $sessao->idioma = $params['idioma'];
		    
		    $url = str_replace("?".$_SERVER["QUERY_STRING"],"",$_SERVER['REQUEST_URI']);
		    $this->_redirect($url);
		endif;
		
		$usuario = Zend_Auth::getInstance()->getIdentity();
		$this->view->usuario 	= $usuario;
		$this->view->translate	= Zend_Registry::get('translate');
		$this->view->objMenu 	= MenuBO::listarMenu();
		$this->view->objMeumenu	= MenuBO::buscaMenuusuario();
		$this->view->ativo 		= $ativo;
		
		//--- Controle de perfil ------------------------------------------
		$this->view->objPerfilusuario	= PerfilBO::listarPerfil($usuario->id_perfil);
	}
	
	//--- Fabricantes de pecas ---------------------------------------------------------------------------
	public function indexAction(){
	
	    $this->topoAction('cadastro');
	    $usuario = Zend_Auth::getInstance()->getIdentity();
	    foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 16) as $list);
	
	    if(($list->visualizar==1)):
	    $this->view->objIns		= $list->inserir;
	    $this->view->objEdi		= $list->editar;
	
	    Zend_Paginator::setDefaultScrollingStyle('Sliding');
	    Zend_View_Helper_PaginationControl::setDefaultViewPartial('correio/paginator.phtml');
	    $paginator = Zend_Paginator::factory(VeiculosBO::listaFabricantes($this->_getAllParams()));
	    $currentPage = $this->_getParam('page', 1);
	    $paginator->setCurrentPageNumber($currentPage)
	    ->setItemCountPerPage(10);
	
	    $this->view->objList 	= $paginator;
	    //LogBO::cadastraLog("Cadastro/Fabricantes",1,$usuario->id,'','');
	    else:
	    $this->_redirect("/admin/cadastro/erro");
	    endif;
	}
	
	public function fabricantescadAction(){
	
	    $this->topoAction('cadastro');
	    $usuario = Zend_Auth::getInstance()->getIdentity();
	    foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 16) as $list);
	
	    if(($list->visualizar==1)):
	    $this->view->objVis		= $list->visualizar;
	    $this->view->objIns		= $list->inserir;
	    $this->view->objEdi		= $list->editar;
	
	    $this->view->objList 	= VeiculosBO::buscaFabricantes($this->_getAllParams());
	    $this->view->objParc	= VeiculosBO::listaParceiro($this->_getAllParams());
	    else:
	    $this->_redirect("/admin/cadastro/erro");
	    endif;
	
	}
	
	public function fabricantesgravaAction(){
	    VeiculosBO::gravarFabricante($this->_getAllParams());
	    $this->_redirect("/admin/fabricantes");
	}
	
	public function fabricantesremAction(){
	    VeiculosBO::removeFabricantes($this->_getAllParams());
	    $this->_redirect("/admin/fabricantes");
	}
	
	

}