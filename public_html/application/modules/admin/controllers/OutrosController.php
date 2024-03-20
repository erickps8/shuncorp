<?php

class Admin_OutrosController extends Zend_Controller_Action {
		
	public function init()
	{
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
	
	
	public function cartoesgruposAction(){
		$this->topoAction();
		$this->view->translate	=	Zend_Registry::get('translate');
			
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 78) as $list);

        if($list->visualizar==1):
        	$params = $this->_getAllParams();
			
        	$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
			
			$this->view->objGrupos	= ContatosBO::listarCartoesgrupos();
			LogBO::cadastraLog("Grupos Cartões",1,$usuario->id,"","");
		else:		
			$this->_redirect("/admin/outros/erro");
        endif;
        
	}
	
	public function cadcartoesgruposAction(){
		ContatosBO::gravaCartoesgrupos($this->_getAllParams());
		$this->_redirect("/admin/outros/cartoesgrupos");
	}
	
	public function remcartoesgruposAction(){
		ContatosBO::removeCartoesgrupos($this->_getAllParams());
		$this->_redirect("/admin/outros/cartoesgrupos");
	}
	
	public function cartoescontatoAction(){
		$this->topoAction();
		$this->view->translate	=	Zend_Registry::get('translate');
			
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 78) as $list);

        if($list->visualizar==1):
        	$params = $this->_getAllParams();
			
        	$this->view->objIns			= $list->inserir;
			$this->view->objEdi			= $list->editar;
			
			Zend_Paginator::setDefaultScrollingStyle('Sliding');
			Zend_View_Helper_PaginationControl::setDefaultViewPartial('index/paginator.phtml');
			$paginator = Zend_Paginator::factory(ContatosBO::listarCartoescontatos($this->_getAllParams()));
			$currentPage = $this->_getParam('page', 1);
			$paginator
			->setCurrentPageNumber($currentPage)
			->setItemCountPerPage(25);
			
			$this->view->objContatos = $paginator;
			
			$this->view->objGrupos		= ContatosBO::listarCartoesgrupos();
			LogBO::cadastraLog("Grupos Cartões",1,$usuario->id,"","");
		else:		
			$this->_redirect("/admin/outros/erro");
        endif;
	}
	
	public function cartoescontatonovoAction(){
		$this->topoAction();
		$this->view->translate	=	Zend_Registry::get('translate');
			
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 78) as $list);

        if($list->visualizar==1):
        	$params = $this->_getAllParams();
			
        	$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
			$this->view->objUfChina = EstadosBO::listarEstadosChina();
			$this->view->objGrupos	= ContatosBO::listarCartoesgrupos();
			
	        $this->view->objContato		= ContatosBO::buscaCartoescontatos($this->_getAllParams());
	        $this->view->objFones		= ContatosBO::buscaCartoestelefones($this->_getAllParams());
	        $this->view->objAnexos		= ContatosBO::buscaCartoesanexos($this->_getAllParams());
	        
	        $this->view->objPaises		= EstadosBO::listaPaises();
	        foreach (ContatosBO::buscaCartoescontatos($this->_getAllParams()) as $listc);
	        if(!empty($listc->id_paises)) $pais = $listc->id_paises;
	        else $pais = 1;  
			$this->view->objUf 			= EstadosBO::buscaEstados($pais);
	        
		else:		
			$this->_redirect("/admin/outros/erro");
        endif;
	}
	
	public function cartoescontatovisAction(){
		$this->topoAction();
		$this->view->translate	=	Zend_Registry::get('translate');
			
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 78) as $list);

        if($list->visualizar==1):
        	$params = $this->_getAllParams();
			
        	$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
			$this->view->objUfChina = EstadosBO::listarEstadosChina();
			$this->view->objGrupos	= ContatosBO::listarCartoesgrupos();
			
	        $this->view->objContato		= ContatosBO::buscaCartoescontatos($this->_getAllParams());
	        $this->view->objFones		= ContatosBO::buscaCartoestelefones($this->_getAllParams());
	        $this->view->objAnexos		= ContatosBO::buscaCartoesanexos($this->_getAllParams());
	        
	        $this->view->objPaises		= EstadosBO::listaPaises();
	        foreach (ContatosBO::buscaCartoescontatos($this->_getAllParams()) as $listc);
	        if(!empty($listc->id_paises)) $pais = $listc->id_paises;
	        else $pais = 1;  
			$this->view->objUf 			= EstadosBO::buscaEstados($pais);
	        
			LogBO::cadastraLog("OUTROS/Contatos/Grupos Cartões",1,$usuario->id,"","");
		else:		
			$this->_redirect("/admin/outros/erro");
        endif;
	}
	
	public function cadcartoescontatoAction(){
		ContatosBO::gravaCartoescontatos($this->_getAllParams());
		$this->_redirect("/admin/outros/cartoescontato");
	}
	
	public function remanexocartoesAction(){
		$params  = $this->_getAllParams();
		ContatosBO::remAnexos($params);
		
		$this->_redirect("/admin/outros/cartoescontatonovo/contato/".$params['contato']);

	}

	public function cartoesvalidaAction(){
		ContatosBO::validaCartoes($this->_getAllParams());
		$this->_redirect("/admin/outros/cartoescontato");
	}
	
	//---Erro por falta de acesso-----------------------------------
	public function erroAction(){
		$this->topoAction();
	}
	
	//--- Webmail --------------------------------------------
	public function webmailAction(){
		$this->_redirect("https://ztlbrasil.com.br");
	}
	
	
	
}
