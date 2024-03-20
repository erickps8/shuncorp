<?php

class Admin_TesteController extends Zend_Controller_Action {
		
	public function init()
	{
	    date_default_timezone_set('America/Sao_Paulo');
	    if ( !Zend_Auth::getInstance()->hasIdentity() ) {
        	
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
	    $this->_helper->layout->disableLayout();
	    $params = $this->_getAllParams();
	    
	    /* $params['formula'] = 'SE($x<=4;0;SE($x=15;111;666))';
	    $params['formula'] = 'SE(($C$6>150,01)E($C$6<200,009);(192,48/O12)+F12+G12+H12+I12+J12+K12+L12+M12+N12;SE($C$6>200,01;E12+F12+G12+H12+I12+J12+K12+L12+M12+N12))';
	    
	    echo DiversosBO::excelTophp($params['formula']); */
	    
	    
	    DiversosBO::buscaVendas();
	    
	    
	    exit();
	}
	
	public function correioAction(){
		$this->_helper->layout->disableLayout();
	}
	
	public function correio2Action(){
	    date_default_timezone_set('America/Sao_Paulo');
	    $this->_helper->layout->disableLayout();
	    $params = $this->_getAllParams();
	    $this->view->idmsn = $params['email'];
	    //DiversosBO::lerEmail();
	}
	
	
	function corrigeAction(){
	    $this->_helper->layout->disableLayout();
	 
	    exit();
	}

	
	public function mapsAction(){
		$this->_helper->layout->disableLayout();
	}
	
	function relatoriocontatosAction(){
	    $this->_helper->layout->disableLayout();
	    ContatosBO::geraListaembreagem();
	    exit();
	}

	
	public function arquivoremessaAction(){
		$this->_helper->layout->disableLayout();
		BoletosBO::gerarArquivoremessaitau();
		exit();
	}
	
	public function arquivoremessabbAction(){
		$this->_helper->layout->disableLayout();
		BoletosBO::gerarArquivoremessabb();
		exit();
	}
	
	public function gerarboletoAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		BoletosBO::geraBoletoitau(array('parc' => md5($params['parcela'])));
		exit();
	}
	
	public function gerarboletobbAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		BoletosBO::geraBoletobb(array('parc' => md5($params['parcela'])));
		exit();
	}
	
	public function importarextratoAction(){
	    $this->_helper->layout->disableLayout();
	    DiversosBO::importaExtrato();
	    exit();
	}
	
	public function corrigeparceiroAction(){
		$this->_helper->layout->disableLayout();
		FaturamentoBO::corrigeParceiro();
		exit();
	}
}
