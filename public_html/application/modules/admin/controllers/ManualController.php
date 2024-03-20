<?php

class Admin_ManualController extends Zend_Controller_Action {
		
	public function init()
	{
	        /*if ( !Zend_Auth::getInstance()->hasIdentity() ) {
				$this->_redirect('http://www.ztlbrasil.com.br');
	        }*/
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
		$this->_helper->layout->disableLayout();
	}
	
	public function buscaidiomaAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		
		$sessao = new Zend_Session_Namespace('Idiomas');
	    $sessao->idioma = $params['idioma'];
	    $_SESSION['S_IDIOMA'] 	= $params['idioma'];
	}
	
	public function buscamanualgarAction(){
		$this->_helper->layout->disableLayout();
		date_default_timezone_set('America/Sao_Paulo');
		$params = $this->_getAllParams();
		$this->view->objImg	= $params['str'];
	}
	
	public function cadastrodegarantiasAction(){
		$this->_helper->layout->disableLayout();
	}
			
}

