<?php
class Admin_IndexController extends Zend_Controller_Action  {
	
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
	
	public function indexAction(){
		$this->topoAction();
	}
	
	public function buscapedidosabertosAction(){
	    $this->_helper->layout->disableLayout();
	    $this->contPed	= VendaBO::contaPedidospendentes();
	    
	    if(count($this->contPed)>0){
	    	foreach ($this->contPed as $qtpedidos);
	    	echo $qtpedidos->qtpedidos;
	    }
	    
	    exit();
	}
	
	public function buscapaineisAction(){
	    $this->_helper->layout->disableLayout();
	    
	    $usuario = Zend_Auth::getInstance()->getIdentity();
	    foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $perfil);
	    
	   
		
		if($perfil->logs == true):
			
			$limit  = array(
				'limite' 	=> 6,
				'usermd' 	=> md5($usuario->id)
			);
			$this->view->objLogs	= LogBO::listaLog($limit);
		endif;
	}
	
	public function buscagarantiasAction(){
		$this->_helper->layout->disableLayout();
		 
		
	}
	
	public function buscalogsAction(){
		$this->_helper->layout->disableLayout();
		 
		$limit  = array('limite' => 3);
		$this->view->objVendas 	= VendaBO::listaPedidospendentes($limit);
		$this->view->contPed	= VendaBO::contaPedidospendentes();
	}
	
	public function dicasAction(){
		$this->_helper->layout->disableLayout();		
	}
	
	public function montagemAction(){
		$this->_helper->layout->disableLayout();
		$this->view->objDicas = VendaBO::listardicasAnalise(1);
	}
	
	public function buscamenuAction(){
	    $this->_helper->layout->disableLayout();
	    
	    if ( Zend_Auth::getInstance()->hasIdentity() ) {
	    
		    $this->view->translate	=	Zend_Registry::get('translate');
		    $params = $this->_getAllParams();
		    
		    if($params['idmenu'] == 'meumenu'):
		    	$this->view->objSubmenu = MenuBO::buscaMenuusuario();
		    else:
		    	$this->view->objSubmenu = MenuBO::buscarSubmenu($this->_getAllParams());
		    endif;
	    
	    }else{
	    	$this->view->menuErro = 1; //--- Sessao expirou --------------------------------
		}
	    
	}
	
	public function gravarreporteAction(){
		$this->_helper->layout->disableLayout();
		DiversosBO::gravarReporte($this->_getAllParams());
		exit();		 
	}
	
	
}