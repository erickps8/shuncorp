<?php
class Admin_PainelController extends Zend_Controller_Action  {
	
	public function init()
	{
        if ( !Zend_Auth::getInstance()->hasIdentity() ) {
               $this->_redirect('../');
                //$this->_redirect('http://localhost/homologacao');
        }
	}
	
	public function topoAction(){		
		$params = $this->_getAllParams();
		if(!empty($params['idioma'])):
			$sessao = new Zend_Session_Namespace('Idiomas');
		    $sessao->idioma = $params['idioma'];
		    $_SESSION['S_IDIOMA'] 	= $params['idioma'];
		    $url = str_replace("?".$_SERVER["QUERY_STRING"],"",$_SERVER['REQUEST_URI']);
		    $this->_redirect($url);
		endif;
		
		$usuario = Zend_Auth::getInstance()->getIdentity();
        $this->view->usuario = $usuario;
        $this->view->translate	=	Zend_Registry::get('translate');$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfil($usuario->tb_perfil_id) as $listr);
		$this->view->objNivel	= $listr->nivel;
		
		if($listr->nivel==0):
			//$this->_redirect("/admin/venda/pendenciasempcli");
		endif;
		
		$bo = new MenuBO();
		$this->view->objMenu 	= $bo->listarMenu($usuario->ID);
		$this->view->objSubmenu = $bo->listarSubmenu($usuario->ID);
	}
		
	public function indexAction(){
		$this->topoAction();
		$usuario = Zend_Auth::getInstance()->getIdentity();
		
		$this->view->objGarantias	= 0;
		$this->view->objEstoque		= 0;
		$this->view->objVendas		= 0;
		$this->view->objLog			= 0;
		$this->view->objLogmail		= 0;
		$this->view->objAtiv		= 0;
		
		foreach (PerfilBO::listarPerfil($usuario->tb_perfil_id) as $list);
		$this->view->nperfil	= $list->nivel;
		//--- Clientes -----------------------------
		if($list->garantia_anacli==true): 	
			$this->view->objGaanalci	= GarantiasBO::listaGaranalisecliente();
			$this->view->objGarantias = 1;
		endif;
				
		//--- Local -----------------------------
		if($list->garantia_aut==true): 	
			$this->view->objGaraut	= GarantiasBO::listaGarautoriza();
			$this->view->objGarantias = 1;
		endif;
		
		if($list->garantia_ana==true):
			$this->view->objGarana	= GarantiasBO::listaGaranalise();
			$this->view->objGarantias = 1;
		endif;
		
		if($list->pedido_exp==true):
			$this->view->objPedexp	= VendaBO::listaPedidospainel();
			$this->view->objVendas		= 1;
		endif;
			
		if($list->produto_zer==true):
			$this->view->objEstoqzero	= EstoqueBO::listaEstoquezerado();
			$this->view->objEstoque		= 1;
		endif;
		
		if($list->produto_amedia==true):
		 	$this->view->objMedia		= 1;
			$this->view->objEstoque		= 1;
		endif;
		
		if($list->log_email==true):
			$this->view->objLogemail	= LogBO::listarFalecom();
			$this->view->objLogmail		= 1;
		endif;
		
		if($list->ativ_pend==true):
			//$this->view->objLogemail	= LogBO::listarFalecom();
			$this->view->objAtiv		= 1;
		endif;
		
		if($list->ativ_aprv==true):
			$this->view->objQuantp  = AtividadesBO::quantitativoAtividadespend();
			$this->view->objMinhas	= AtividadesBO::listatodasAtividadessolicitante($usuario->ID);
			$this->view->objAtiv		= 1;
		endif;
		
		$this->view->logins	= LogBO::listaLoginuser();
		
		
	}
	
	public function buscamediaestoqueAction(){
		$this->_helper->layout->disableLayout();
		$usuario = Zend_Auth::getInstance()->getIdentity();
		$this->view->objEstoqmedia	= EstoqueBO::listaEstoquemedia();
	}
	
	public function testeAction(){
		$this->_helper->layout->disableLayout();
	}
	
	public function gameAction(){
		$this->_helper->layout->disableLayout();
	}
	
	public function videosAction(){
	    $this->topoAction();
	    $usuario = Zend_Auth::getInstance()->getIdentity();
	}
	
}
?>
