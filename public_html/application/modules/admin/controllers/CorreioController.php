<?php

class Admin_CorreioController extends Zend_Controller_Action {
		
	public function init()
	{
	    date_default_timezone_set('America/Sao_Paulo');
	    if ( !Zend_Auth::getInstance()->hasIdentity() ) {
        	$this->_redirect('/');
        }
	}
	
	public function topoAction($ativo=''){		
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
	
	public function indexAction(){
		$this->_redirect("/admin/correio/caixa/tp/entrada");		
	}

	public function caixaAction(){
	    $bo = new CorreioBO();
	    $this->topoAction();
	
	    if($bo->listaEmail($this->_getAllParams())):
	    
			Zend_Paginator::setDefaultScrollingStyle('Sliding');
			Zend_View_Helper_PaginationControl::setDefaultViewPartial('correio/paginator.phtml');
			$paginator = Zend_Paginator::factory($bo->listaEmail($this->_getAllParams()));
			$currentPage = $this->_getParam('page', 1);
			$paginator
				->setCurrentPageNumber($currentPage)
				->setItemCountPerPage(10);
				
			$this->view->objEmails 	= $paginator;
			$this->view->objCont	= $bo->contarEmail();
			$this->view->page 		= $this->_getParam('page', 1);
			$this->view->tp 		= $this->_getParam('tp', 1); 		
			
			$bo->listaEmail($this->_getAllParams());
		else:
			$this->view->objCont	= $bo->contarEmail();			
		endif;
		
	}
	
	
	public function visualizarAction(){
	    $bo = new CorreioBO();
	    
		$this->topoAction();
		
		$params = $this->_getAllParams();
		$this->view->numEmail	= $params['email'];
		$this->view->objEmail	= $bo->buscarEmail($params);
	}
	
	public function baixaranexoAction(){
	    $this->_helper->layout->disableLayout();
	    $bo = new CorreioBO();
	    
	    $params = $this->_getAllParams();
	    $bo->baixarAnexo($params);
	    
	    exit();
	}
	
	public function novocorreioAction(){
		$this->topoAction();
		$bo = new CorreioBO();
		$params = $this->_getAllParams();
		
		if($params['emp']):
			$this->view->objEmp			= ContatosBO::listarContatosmatriz($params);
			$this->view->emp			= $params['emp'];
		elseif($params['filial']):
		
		elseif($params['contato']):
		
		
		$this->view->erro = $params['erro'];
		
		endif;
		
	}
	
	public function enviaremailAction(){
	    $this->_helper->layout->disableLayout();
	    $bo = new CorreioBO();
	    if($bo->enviaEmail($this->_getAllParams())):
	    	$this->_redirect("/admin/correio/caixa/tp/entrada");
	    else:
	    	$this->_redirect("/admin/correio/novocorreio/erro/1");
	    endif; 
	    
	    exit();
	}
	
	public function removeemailAction(){
	    $bo = new CorreioBO();
	    $bo->removeEmail($this->_getAllParams());
	    $this->_redirect("/admin/correio/caixa/tp/entrada");
	}
	

	
	
	//-- mainling
	
	//---Mailing -------------------------
	public function mailingAction(){
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 12) as $list);
	
		if($list->visualizar==1):
		$this->topoAction();
			
		Zend_Paginator::setDefaultScrollingStyle('Sliding');
		Zend_View_Helper_PaginationControl::setDefaultViewPartial('index/paginator.phtml');
		$paginator = Zend_Paginator::factory(MailingBO::listarMailingenviados());
		$currentPage = $this->_getParam('page', 1);
		$paginator
			->setCurrentPageNumber($currentPage)
			->setItemCountPerPage(15);
			
		$this->view->objList 	= $paginator;
			
		else:
		$this->_redirect("venda/erro");
		endif;
	
		LogBO::cadastraLog("ADM/Mailing",1,$usuario->id,"","",'');
	}
	
	public function mailingnovoAction(){
		
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 12) as $list);
		if($list->visualizar==1):
			
			$this->topoAction('outros');
			$this->view->objPerfil		= PerfilBO::listarPerfil();
			$this->view->objGinteresse  = ContatosBO::listaGruposinteresse();
			$this->view->objGclientes  	= ClientesBO::buscaClientesgrupos();
			
		else:
			$this->_redirect("administracao/erro");
		endif;
			
	}
	
	public function enviarmailingAction(){
		$this->_helper->layout->disableLayout();
		echo MailingBO::enviaMailing($this->_getAllParams());
		exit();
	}
	
	public function disparamailingAction(){
		$this->_helper->layout->disableLayout();
		
		$params = $this->_getAllParams();
		echo MailingBO::dispararBoletimeletronico($params['idm']);
		exit();
	}
	
	
	public function mailingdescadastradosAction(){
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 12) as $list);
	
		if($list->visualizar==1):
		$this->topoAction();
			
		Zend_Paginator::setDefaultScrollingStyle('Sliding');
		Zend_View_Helper_PaginationControl::setDefaultViewPartial('index/paginator.phtml');
		$paginator = Zend_Paginator::factory(MailingBO::listaEmailsdescadastrados());
		$currentPage = $this->_getParam('page', 1);
		$paginator
		->setCurrentPageNumber($currentPage)
		->setItemCountPerPage(15);
			
		$this->view->objList 	= $paginator;
		$this->view->objMot		= MailingBO::listamotivosEmailsdescadastrados();
			
		else:
		$this->_redirect("venda/erro");
		endif;
	
		LogBO::cadastraLog("ADM/Emails descadastrados",1,$usuario->id,"","",'');
	}
	
	public function promocoesAction(){
		$this->_helper->layout->disableLayout();
		$this->view->obj = MailingBO::promocoesCadastrados($this->_getAllParams());
	}
	
	public function lancamentosAction(){
		$this->topoAction();
		$this->view->objList = MailingBO::lancamentosCadastrados();
		$params = $this->_getAllParams();
		if($this->_request->isPost()):
		MailingBO::envialancamentoMailing($this->_getAllParams());
		$this->view->obj = 1;
		 
		if(!empty($params[remove])):
		MailingBO::removeMailinglista();
		endif;
		endif;
	}
	
	public function enviarlancamentosAction(){
		$this->topoAction();
	
		$this->view->obj = MailingBO::envialancamentoMailing($this->_getAllParams());
	
	}
	
	public function navegadordearquivosAction(){
		$this->topoAction();
		
	}
	
	public function removerlancamentosAction(){
		$this->topoAction();
		MailingBO::removeMailinglista();
		$this->_redirect("/admin/administracao/lancamentos");
	}
	
	public function testeAction(){
	    $this->_helper->layout->disableLayout();
	    MailingBO::testeEmail();
	}
	
	
}

