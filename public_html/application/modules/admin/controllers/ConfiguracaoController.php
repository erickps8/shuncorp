<?php
class Admin_ConfiguracaoController extends Zend_Controller_Action {
		
	public function init(){
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

	public function meumenuAction(){
		$this->topoAction();
		$this->view->objMenu 	= MenuBO::listarMenu();
		$this->view->objSubmenu = MenuBO::listarSubmenu();
		
		if($this->_request->isPost()):
			MenuBO::gravaMenuusuario($this->_getAllParams());
		endif;
		
		$this->view->objMenuusuario = MenuBO::buscaMenuusuario();
	}
	
	public function indexAction(){
	    $this->topoAction();
	    
	    if($this->_request->isPost()):
	    	$params = $this->_getAllParams();
	    	$return = UsuarioBO::cadastraDadospessoais($params);
		    if($return == false):
			    $this->view->objRes = "falha";
		    else:
		    	$this->view->objRes = "sucesso";
		    endif;
	    endif;
	    
	    $this->view->objPaises		= EstadosBO::listaPaises();
	    
	    $this->view->objPerfil 		= PerfilBO::listarPerfil();
	    $this->view->objRegioes		= RegioesBO::listaRegioesclientes();
	    $this->view->objRegtelv		= RegioesBO::buscaRegioestelevendas();
	    
	    $usuario = Zend_Auth::getInstance()->getIdentity();
	    
	    $params = array('usuario' => $usuario->id);
	    foreach (UsuarioBO::buscaUsuario($params) as $user);
	    $this->view->objEstados		= EstadosBO::buscaEstadosmd(md5($user->id_paises));
	    $this->view->objCidades		= EstadosBO::buscaCidadesidestado($user->id_estados);
	    $this->view->objUser		= UsuarioBO::buscaUsuario($params);
	}
	
	
	public function senhaAction(){
		$this->topoAction();
		
		if($this->_request->isPost()):
			$params = $this->_getAllParams();
			$this->view->objRes = UsuarioBO::trocarSenha($params);			
		endif;
		
	}
	
	public function credenciaisemailAction(){
		$this->_helper->layout->disableLayout();
		 
		if($this->_request->isPost()):
			UsuarioBO::atualizaCredenciaisemail($this->_getAllParams());
		endif;
		
		$this->_redirect("/admin/correio/caixa/tp/entrada");
	}
	
	
}