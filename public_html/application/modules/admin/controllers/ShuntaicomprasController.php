<?php

class Admin_ShuntaiComprasController extends Zend_Controller_Action {
	
	public function init()
	{
	        if ( !Zend_Auth::getInstance()->hasIdentity() ) {
	                $this->_redirect('/');
	        }
	}

	//--Topo-----------
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
	//--Lista pedidos compra shuntai -------
	public function pedidosAction(){
		$this->topoAction();
		
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 69) as $list);
        
        if(($list->visualizar==1)):
			$this->view->objIns		= $list->inserir;
			
            $this->view->translate	= Zend_Registry::get('translate');
            $this->view->objFornec	= ClientesBO::buscaParceiros("fornecedorchines");
			
			
			/* Zend_Paginator::setDefaultScrollingStyle('Sliding');
			Zend_View_Helper_PaginationControl::setDefaultViewPartial('index/paginator.phtml');
			$paginator = Zend_Paginator::factory(TaicomprasBO::listaPedidos());
			$currentPage = $this->_getParam('page', 1);
			$paginator
			->setCurrentPageNumber($currentPage)
			->setItemCountPerPage(15);
					
			$this->view->objList = $paginator;
			$this->view->objPag = $params[page]; */
			
			LogBO::cadastraLog("Shuntai/Pedidos",1,$usuario->id,"","");
		else:		
			$this->_redirect("/admin/shuntaicompras/erro");
        endif;
		
	}
	
	
	public function buscapedidoscompraAction(){
	
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 69) as $list);
		foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $listper);
		$this->view->objNivel = $listper->nivel;
			
		$this->view->objIns		= $list->inserir;
		$this->view->objEdi		= $list->editar;
	
		$this->_helper->layout->disableLayout();
	
		if($list->visualizar==1){
			$this->view->translate	=	Zend_Registry::get('translate');
				
			$params = $this->_getAllParams();
			$page = $params['page'];
	
			$arrayRet = TaicomprasBO::listaPedidos($params);
				
			Zend_Paginator::setDefaultScrollingStyle('Sliding');
			Zend_View_Helper_PaginationControl::setDefaultViewPartial('index/paginatorajax.phtml');
			$paginator   = Zend_Paginator::factory($arrayRet['objeto']);
			$currentPage = $this->_getParam('page', $page);
			$paginator
			->setCurrentPageNumber($currentPage)
			->setItemCountPerPage(10);
	
			$this->view->objList 	= $paginator;
			$this->view->objTp		= $arrayRet['tipo'];
		}else{
			$this->_redirect("/admin/shuntaicompras/erro");
		}
	
	}
	
	
	
	
	//--Gerar novo pedido----------
	public function novopedidoAction(){
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 69) as $list);
        
        if(($list->inserir==1)):
			$this->topoAction();
			$this->view->objFor = ClientesBO::buscaParceiros("fornecedorchines");
		else:		
			$this->_redirect("shuntaicompras/erro");
        endif;
	}
	
	public function listaprodutosAction(){
		
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 69) as $list);
        
        if(($list->visualizar==1)):
			$this->topoAction();
			$this->view->objProdutos 	= TaicomprasBO::listaProdutosvendas($this->_getAllParams());
			$params = $this->_getAllParams();
			$this->view->objFor			= $params['fornecedor'];
			
			$busca['idparceiro']		= $params['fornecedor'];
			$this->view->objNfor 		= ClientesBO::buscaParceiros("",$busca);
			
		else:		
			$this->_redirect("shuntaicompras/erro");
        endif;
	}
	
	public function gravacompraAction(){
		$id = TaicomprasBO::gravaPedido($this->_getAllParams());
		//$this->_redirect('/admin/shuntaicompras/pedidos');				
		$this->_redirect('/admin/shuntaicompras/gerarpedido/ped/'.md5($id));
	}
	
	
	//--Gera o pedido ----------------------
	public function gerarpedidoAction(){
		$this->topoAction();
		$params = $this->_getAllParams();
				
		$this->view->objPedido		= TaicomprasBO::buscaCompras($params['ped']);		
		$this->view->objProdutos 	= TaicomprasBO::listaProdutoscompra($params['ped']);
		$this->view->objProdent 	= TaicomprasBO::listaProdutosentregue($params['ped']);
		$this->view->objObsgravados = TaicomprasBO::listaObsgravados($params['ped']);
		$this->view->objFin			= FinanceirochinaBO::buscaPagamentoscompras($params['ped'],2);
		
	}
	
	//--Remove pedido--------------------------------------------------
	public function pedidosremAction(){
		$params = $this->_getAllParams();
		TaicomprasBO::removerPedido($params);
		$this->_redirect('/admin/shuntaicompras/pedidos');
	}
	
	
	//--Gera a compra, com o gravacao e o prazo ----------------------
	public function gerarcompraAction(){
		$params = $this->_getAllParams();
		TaicomprasBO::geraPedido($params);
		$this->_redirect('/admin/shuntaicompras/gerarpedido/ped/'.md5($params['pedido']));
	}
	
	//--Finaliza compra-----------------------------------------
	public function fecharcompraAction(){
		$params = $this->_getAllParams();
		TaicomprasBO::gravaEntrega($params);
		TaicomprasBO::fecharPedido($params);
		$this->_redirect('/admin/shuntaicompras/gerarpedido/ped/'.md5($params['pedido']));
	}
	
	public function gravarentregaAction(){
		$params = $this->_getAllParams();
		TaicomprasBO::gravaEntrega($params);
		$this->_redirect('/admin/shuntaicompras/gerarpedido/ped/'.md5($params['pedido']));
	}
	
	//--Remove entrega--------------------------------------------------
	public function removeentregaAction(){
		$params = $this->_getAllParams();
		TaicomprasBO::removeEntrega($params);
		$this->_redirect('/admin/shuntaicompras/gerarpedido/ped/'.md5($params['ped']));
	}
	
	//--Gravar obs--------------------------------
	public function gravarobsAction(){
		/* $params = $this->_getAllParams();
		TaicomprasBO::gravaObs($params);
		$this->_redirect('/admin/shuntaicompras/pedidosobs/ver/true/ped/'.md5($params['pedido'])); */
		
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		echo TaicomprasBO::gravaObs($params);
		exit();
		
	}
	
	//--Imprimir pedido de compra--------------------------------
	public function pedidoscompimpAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		
		$this->view->objPed   		= TaicomprasBO::buscaCompras($params['ped']);
		$this->view->objProdutos 	= TaicomprasBO::listaProdutoscompra($params['ped']);
		$this->view->objObsgravados = TaicomprasBO::listaObsgravados($params['ped']);
				
		foreach (TaicomprasBO::buscaCompras($params['ped']) as $lista);
		
		$this->view->objCliente		= ClientesBO::listaChina($lista->id_for);
		$this->view->objEndCliente	= ClientesBO::listaEnderecoschines($lista->id_for);
		$this->view->objTelCliente	= ClientesBO::listaTelefones($lista->id_for);
		$this->view->objUfchhina	= EstadosBO::listarEstadosChina();
		
		$this->view->objObs 		= TaicomprasBO::listaObs();
		$this->view->objParams      = $params;		
						
	}
	
	//--Gerar compra apartir da preordem--------------------------
	public function geracomprapreordemAction(){
		$params = $this->_getAllParams();
		TaicomprasBO::gerarPurchasepreordem($params);
		//$this->_redirect('http://localhost/homologacao/public/legado/acessoRestrito/pre_ordem_view.php?view='.$params[pre].'&ger=true');
		$this->_redirect('http://www.ztlbrasil.com.br/public/legado/acessoRestrito/pre_ordem_view.php?view='.$params[pre].'&ger=true');
				
	}
	
	
	//--Busca regras por pedido -----------------------------
	public function buscaregrascomprasgrupoAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		 
		TaicomprasBO::buscaRegrascomprasgrupos($params);
		 
		exit();
	}
	
	//--Busca regras por pedido -----------------------------
	public function buscaregrascomprasAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
			
		TaicomprasBO::buscaRegrascompras($params);
			
		exit();
	}
	
	//--Gerar compra manual --------------------------
	public function gerarpedidomanAction(){
		$this->topoAction();
		$params = $this->_getAllParams();
		$this->view->objFor = $params['fornecedor'];
		
		$busca['idparceiro']		= $params['fornecedor'];
		$this->view->objNfor 		= ClientesBO::buscaParceiros("",$busca);
		
	}	
	
	//--Gerar compra manual - Busca produto --------------------------
	public function buscaprodutoAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		$this->view->objProd 	= ProdutosBO::listaProdutoscodigo($params);
		$this->view->qt			= $params['qt'];
				
	}
	
	public function pedidosfinAction(){
		TaicomprasBO::finalizaFinance($this->_getAllParams());
		$this->_redirect('/admin/shuntaicompras/pedidos');
	}
	
	
	//--Exibir produtos pedidos----------------------------
	public function produtospedAction(){
		$this->topoAction();

		$params = $this->_getAllParams();
		
		Zend_Paginator::setDefaultScrollingStyle('Sliding');
		Zend_View_Helper_PaginationControl::setDefaultViewPartial('index/paginator.phtml');
		$paginator = Zend_Paginator::factory(TaicomprasBO::listaProdutospedidos($params));
		$currentPage = $this->_getParam('page', 1);
		$paginator
		->setCurrentPageNumber($currentPage)
		->setItemCountPerPage(35);
				
		$this->view->objList 	= $paginator;
		$this->view->objPag 	= $params[page];
		$this->view->objBusc 	= $params[busca];
		
	}
	
	
	
	//----Pre Ordem de produtos ------------------------------------------------
	public function preordemAction(){
		$this->topoAction();
		$this->view->translate	=	Zend_Registry::get('translate');
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 59) as $list);
        
        if(($list->visualizar==1)):
         	$this->view->objVis		= $list->visualizar;
			$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;  
			$params = $this->_getAllParams();
			
			if(($params['fil']=='comp')):
				$sessaobusca = new Zend_Session_Namespace('Preordem');
			  	$sessaobusca->where = "";		  
			endif;
			
			Zend_Paginator::setDefaultScrollingStyle('Sliding');
			Zend_View_Helper_PaginationControl::setDefaultViewPartial('index/paginator.phtml');
			$paginator = Zend_Paginator::factory(TaicomprasBO::listaPreordem($params));
			$currentPage = $this->_getParam('page', 1);
			$paginator
			->setCurrentPageNumber($currentPage)
			->setItemCountPerPage(10);
			
			$this->view->objList = $paginator;
						
			$usuario = Zend_Auth::getInstance()->getIdentity();
	        
			foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 62) as $list);
			
			$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;		
			LogBO::cadastraLog("Shuntai/Preorder",1,$usuario->id,"","");
		else:		
			$this->_redirect("shuntaicompras/erro");
        endif;
		
	}
	
	//--Lista produtos preordem -------
	public function preordemprodAction(){
		$this->topoAction();
		$this->view->translate	=	Zend_Registry::get('translate');
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 59) as $list);
        
        if(($list->visualizar==1)):
         	$this->view->objVis		= $list->visualizar;
			$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;  
			
			$params = $this->_getAllParams();
			
			TaicomprasBO::gerarKitpreordem($params);
			
			$this->view->objList 		= TaicomprasBO::listaProdutospreordem($params);
			$this->view->objListkit		= TaicomprasBO::listaProdutoskitpreordem($params);
			$this->view->objListfor		= TaicomprasBO::listaFornecedoreskitpreordem($params,"id_for is NULL and");
			$this->view->objListforf	= TaicomprasBO::listaFornecedoreskitpreordem($params,"id_for is not NULL and");
			$this->view->objListkitsum	= TaicomprasBO::listaProdutoskitpreordemsum($params,"id_for is NULL and");
			$this->view->objListkitsumf	= TaicomprasBO::listaProdutoskitpreordemsum($params,"id_for is not NULL and");
			$this->view->objListmoudes	= TaicomprasBO::listaProdutoskitmoudes($params);
			
			//LogBO::cadastraLog("Shuntai/Preordem",1,$usuario->id,$id,"Preordem PO".substr("000000".$params,-6,6)); 
		else:		
			$this->_redirect("shuntaicompras/erro");
        endif;
	}
	

	public function gravacomprapreordemAction(){
		$params = $this->_getAllParams();
		TaicomprasBO::gravaPedidopreordem($params);
		$this->_redirect('/admin/shuntaicompras/preordemprod/pordem/'.$params['pordem']);				
	}
	
	public function preordemnovoAction(){
		$this->topoAction();
		$this->view->translate	=	Zend_Registry::get('translate');		
	}
	
	public function gravapreordemAction(){
		$params = $this->_getAllParams();
		TaicomprasBO::gravaPreordem($params);
		$this->_redirect('/admin/shuntaicompras/preordem');
	}
	
	public function removepreordemAction(){
		TaicomprasBO::removePreordem($this->_getAllParams());
		$this->_redirect('/admin/shuntaicompras/preordem');
	}
	
}