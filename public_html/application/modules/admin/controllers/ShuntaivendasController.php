<?php

class Admin_ShuntaivendasController extends Zend_Controller_Action {
	
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
	
	public function vendagerarxmlAction(){
		$this->_helper->layout()->disableLayout();
		$this->view->objDom = TaivendasBO::gerarXml();
		
	}
	
		
} 