<?php
class Admin_KangController extends Zend_Controller_Action {
	
	public function init(){
        if(!Zend_Auth::getInstance()->hasIdentity()){
        	$this->_redirect('/');
        }
                
        $url = str_replace("?".$_SERVER["QUERY_STRING"],"",$_SERVER['REQUEST_URI']);
        foreach (PerfilBO::buscarAcesso($url) as $list);
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
	
	public function pedidosAction(){ 
		$this->topoAction('kang');
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 44) as $list);
		
        if($list->visualizar==1):
        	$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
			
			$this->view->translate	= Zend_Registry::get('translate');
			$this->view->objFornec	= ClientesBO::buscaParceiros("clienteschines","","A");
			
		else:		
			$this->_redirect("/admin/kang/erro");
        endif;
        		
		LogBO::cadastraLog("Kang/Pedidos",1,$usuario->id,"","");				
	}
	
	
	public function buscapedidosAction(){
		
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 44) as $list);
		
		foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $listper);
		$this->view->objNivel = $listper->nivel;
		 
		$this->view->objIns		= $list->inserir;
		$this->view->objEdi		= $list->editar;
		 
		if($list->visualizar==1){
		    $this->view->translate	=	Zend_Registry::get('translate');
		    
			$this->_helper->layout->disableLayout();
			$params = $this->_getAllParams();
			
			$page = $params['page'];
						 
			$arrayRet = KangvendasBO::listaPedidosvendas($this->_getAllParams());
			
			Zend_Paginator::setDefaultScrollingStyle('Sliding');
			Zend_View_Helper_PaginationControl::setDefaultViewPartial('index/paginatorajax.phtml');
			$paginator = Zend_Paginator::factory($arrayRet['objeto']);
			$currentPage = $this->_getParam('page', $page);
			$paginator
			->setCurrentPageNumber($currentPage)
			->setItemCountPerPage(10);
			 
			$this->view->objList 	= $paginator;
			$this->view->objTp		= $arrayRet['tipo'];
		}else{
			$this->_redirect("/admin/kang/erro");
		}
	}
	
	public function buscaprodutospedAction(){
	
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 44) as $list);
	
		foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $listper);
		$this->view->objNivel = $listper->nivel;
			
		$this->view->objIns		= $list->inserir;
		$this->view->objEdi		= $list->editar;
			
		if($list->visualizar==1){
			$this->view->translate	=	Zend_Registry::get('translate');
	
			$this->_helper->layout->disableLayout();
			$params = $this->_getAllParams();
				
			$page = $params['page'];
				
			Zend_Paginator::setDefaultScrollingStyle('Sliding');
			Zend_View_Helper_PaginationControl::setDefaultViewPartial('index/paginatorajax.phtml');
			$paginator = Zend_Paginator::factory(KangvendasBO::listaPedidosvendas($this->_getAllParams()));
			$currentPage = $this->_getParam('page', $page);
			$paginator
			->setCurrentPageNumber($currentPage)
			->setItemCountPerPage(10);
	
			$this->view->objList = $paginator;
		}else{
			$this->_redirect("/admin/kang/erro");
		}
	}	
	
	public function pedidosprodAction(){
		$this->topoAction('kang');
		$this->view->translate	=	Zend_Registry::get('translate');
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 44) as $list);
		
        if($list->visualizar==1){
        	$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
			
			$params = $this->_getAllParams();
			
			$this->view->objDet		= KangvendasBO::listaPedidosvendasdet($params);
			$this->view->objProd	= KangvendasBO::listaPedidosvendasprod($params);
			
			foreach (KangvendasBO::listaPedidosvendasdet($params) as $lista);
			$idped = $lista->ID;
			$this->view->editar	= (isset($params['editar'])) ? $params['editar'] : "";
			
			$bo = new ProdutosclassesModel();
			$this->view->objProdutosclasses = $bo->fetchAll("id > 0", "letra asc");

			LogBO::cadastraLog("Kang/Pedidos",1,$usuario->id,$idped,"OR".substr("000000".$idped, -6,6));
        }else{		
			$this->_redirect("/admin/kang/erro");
        }        
	}
	
	//--- Imprime pro-form invoice -----------------------------------------
	public function proformaimpAction(){
		$this->_helper->layout->disableLayout();
		$this->view->translate	=	Zend_Registry::get('translate');
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 44) as $list);
	
		if($list->visualizar==1){
			$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
		
			$params = $this->_getAllParams();
		
			$this->view->objDet		= KangvendasBO::listaPedidosvendasdet($params);
			$this->view->objProd	= KangvendasBO::listaPedidosvendasprod($params);

			foreach (KangvendasBO::listaPedidosvendasdet($params) as $det);
			$this->view->objEnd		= ClientesBO::listaEnderecocomp($det->id_cliente, 1);
			$this->view->objTel		= ClientesBO::listaTelefonesUp($det->id_cliente, 'telefone1');
			$this->view->objFax		= ClientesBO::listaTelefonesUp($det->id_cliente, 'fax');

			$bo     = new ClientesModel();
			$boc    = new ClientesconsigneeModel();
			
			$bocidade = new CidadesModel();
			$boestado = new EstadosModel();
			$bopais   = new PaisesModel();
			
			$this->view->objConsignee = $boc->fetchRow('id_cliente = "'.$det->id_cliente.'"'); 
			
			if($this->view->objConsignee->id_cidade != null){ 
			    $this->view->cidadeconsignee = $bocidade->fetchRow("id = '".$this->view->objConsignee->id_cidade."'");			    
			    $this->view->estadoconsignee = $boestado->fetchRow("id = '".$this->view->cidadeconsignee->id_estados."'");
			    $this->view->paisconsignee   = $bopais->fetchRow("id = '".$this->view->estadoconsignee->id_paises."'");
			}
	
		}else{
			$this->_redirect("/admin/kang/erro");
		}
	}
	
	public function buscanovoprodutoprodAction(){
	    $this->_helper->layout->disableLayout();
	    $params = $this->_getAllParams();
	    
	    $bo  = new ProdutosModel();
	    $bov = new KangvendasModel();
	    $bop = new VendasprodModel();
	    
	    $prod = $bo->fetchRow("CODIGO = '".$params['cod']."'");
	    
	    if(count($prod)>0){
	        $prodped = $bop->fetchRow("ID_PRODUTO = '".$prod->ID."' and ID_PEDIDO = '".$params['ped']."'"  );
	        
	        if(count($prodped)>0){
	            echo 'Item já cadastrado';
	        }else{
	            $bop->insert(array('ID_PRODUTO' => $prod->ID, 'ID_PEDIDO' => $params['ped'], 'MOEDA' => 'USD',));
	            echo 'sucesso';
	        }
	        
	    }else{
	        echo 'Código incorreto';
	    }
	    
	    exit();	    
	}
		
	//---Lista orcamentos------------------------------------------------------------
	public function orcamentosAction(){
		$this->topoAction('kang');
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 44) as $list);
		
        if($list->visualizar==1):
        	$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
        	$this->view->objList 	= KangorcamentosBO::listaOrcamentos();
        	LogBO::cadastraLog("Kang/Orcamentos",1,$usuario->id,"","");
        	
		else:		
			$this->_redirect("/admin/kang/erro");
        endif;
        		
		//LogBO::cadastraLog("ADM/Financeiro China",1,$usuario->id,"","");
		
	}
	//---Novo orcamento------------
	public function orcamentosnovoAction(){
		$this->topoAction('kang');
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 44) as $list);
		
        if($list->visualizar==1):
        	$this->view->objFornec	= ClientesBO::buscaParceiros("clienteschines","","A");
		else:		
			$this->_redirect("/admin/kang/erro");
        endif;
        
	}
	
	public function gravarorcamentoAction(){
		$id = KangvendasBO::gerarOrcamento($this->_getAllParams());
		$this->_redirect("/admin/kang/orcamentonovodet/orc/".md5($id));
	}
	
	public function importapedAction(){
	    $this->_helper->layout->disableLayout();
	    
	    $id = KangvendasBO::importacaoPedido($this->_getAllParams());
		$this->_redirect("/admin/kang/orcamentonovodet/orc/".md5($id));
		
		exit();
	}
	
	//-- Pedidos novo -----------------------------
	public function orcamentonovodetAction(){
		$usuario = Zend_Auth::getInstance()->getIdentity();
	
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 44) as $list);
		
		if($list->visualizar==1):
			$this->topoAction('kang');
			$params = $this->_getAllParams();
		
			$this->view->objVis		= $list->visualizar;
			$this->view->objEdi		= $list->editar;
		
			foreach (KangorcamentosBO::buscaOrcamentos($params) as $lista);
			$ped = $lista->idped;
					
			$this->view->objCliente	= KangorcamentosBO::buscaOrcamentos($params);
			$this->view->objList 	= KangorcamentosBO::buscaOrcamentosprod($params);
			
			$bo = new ProdutosclassesModel();
			$this->view->objProdutosclasses = $bo->fetchAll("id > 0", "letra asc");
			
			LogBO::cadastraLog("Kang/Orçamento Venda",1,$usuario->id,$ped,$ped);
		else:
			$this->_redirect("/admin/kang/erro");
		endif;
		
	}
	
	public function cadprodorcamentoAction(){
	    
	    $this->_helper->layout->disableLayout();	 
	    
	    $params = $this->_getAllParams();
		KangorcamentosBO::gravarprodOrcamento($params);
		$this->_redirect("/admin/kang/orcamentonovodet/orc/".md5($params['ped']));
		
		exit();
	}
	
	public function remprodorcamentoAction(){
		$params = $this->_getAllParams();
		KangorcamentosBO::removeprodOrcamento($params);
		$this->_redirect("/admin/kang/orcamentonovodet/orc/".md5($params['ped']));
	}
	
	public function gerarpedidodevendaAction(){
	    $this->_helper->layout->disableLayout();
	    
		$params = $this->_getAllParams();
		$id = KangorcamentosBO::gerarPedidovenda($params);
		
		if(!empty($id)):
			$this->_redirect("/admin/kang/pedidosprod/ped/".md5($id));
		else:
			$this->_redirect("/admin/kang/orcamentonovodet/orc/".$params['orc']);
		endif;
		
		exit();
	}
	
	public function atualizapedidodevendaAction(){
	    $this->_helper->layout->disableLayout();	    
	    
		$params = $this->_getAllParams();
		KangvendasBO::atualizaPedidovenda($params);
		
		$this->_redirect("/admin/kang/pedidosprod/ped/".md5($params['ped']));
		
		exit();
	}
	
	public function remorcamentoAction(){
		$params = $this->_getAllParams();
		KangorcamentosBO::removeOrcamento($params);
		$this->_redirect("/admin/kang/orcamentos");
	}
	
	//------- Vendas (Invoices) -----------------------------------
	public function vendasAction(){
		
		$this->topoAction('kang');
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 44) as $list);
		
        if($list->visualizar==1):
        	$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
			
			$params = $this->_getAllParams();
			
			$this->view->objFornec	= ClientesBO::buscaParceiros("fornecedorchines");

			if(empty($params['tipo'])):
				Zend_Paginator::setDefaultScrollingStyle('Sliding');
				Zend_View_Helper_PaginationControl::setDefaultViewPartial('index/paginator.phtml');
				$paginator = Zend_Paginator::factory(KangvendasBO::listaVendaskang($params));
				$currentPage = $this->_getParam('page', 1);
				$paginator
				->setCurrentPageNumber($currentPage)
				->setItemCountPerPage(20);
						
				$this->view->objList 	= $paginator;
				LogBO::cadastraLog("Kang/Vendas",1,$usuario->id,"","");
			else: 
				$this->view->objList 	= KangvendasBO::listaVendaskang($params);
				$this->view->objPesq	= 1;
			endif;
			
		else:		
			$this->_redirect("/admin/kang/erro");
        endif;
        
	}
	
	function baixarfinanceiroinvoiceAction(){
		KangvendasBO::fecharFininvoice($this->_getAllParams());
		$this->_redirect("/admin/kang/vendas");
	}
	
	
	public function removeorAction(){
	    $params = $this->_getAllParams(); 
	    $kangorcamentosBO = new KangorcamentosBO();
	    $kangorcamentosBO->removeOR($params['ped']);
	    
	    $this->_redirect('/admin/kang/pedidosprod/ped/'.md5($params['ped']));
	}
	
	public function removevendaAction(){
		$params = $this->_getAllParams();
		KangvendasBO::cancelaCominvoice($params);
		$this->_redirect('/admin/kang/vendas');
	}
	
	public function finalizaplvendaAction(){
	    $this->_helper->layout->disableLayout();
	    $params = $this->_getAllParams();
		KangvendasBO::finalizaPlcominvoice($params);
		$this->_redirect('/admin/kang/vendalistapacotes/ped/'.$params['invoice']);
		
	}
	
	
	
	
	
	public function produtosvendAction(){
		$this->topoAction('kang');

		$params = $this->_getAllParams();
		
		Zend_Paginator::setDefaultScrollingStyle('Sliding');
		Zend_View_Helper_PaginationControl::setDefaultViewPartial('index/paginator.phtml');
		$paginator = Zend_Paginator::factory(KangvendasBO::listaProdutosvendas($params));
		$currentPage = $this->_getParam('page', 1);
		$paginator
		->setCurrentPageNumber($currentPage)
		->setItemCountPerPage(35);
				
		$this->view->objList 	= $paginator;
		$this->view->objPag 	= $params[page];
		$this->view->objBusc 	= $params[busca];
		
	}
	
	//---Rascunhos Shunkang----------------------------------------------------
	
	//--Lista rascunhos -------
	public function rascunhosAction(){
		$this->topoAction('kang');

		$params = $this->_getAllParams();
		
		Zend_Paginator::setDefaultScrollingStyle('Sliding');
		Zend_View_Helper_PaginationControl::setDefaultViewPartial('index/paginator.phtml');
		$paginator = Zend_Paginator::factory(RascunhosBO::listaPedidos());
		$currentPage = $this->_getParam('page', 1);
		$paginator
		->setCurrentPageNumber($currentPage)
		->setItemCountPerPage(35);
				
		$this->view->objList = $paginator;
		$this->view->objPag = $params[page];
		
		LogBO::cadastraLog("Kang rascunhos/Rascunhos",1,$_SESSION['S_ID'],"","");
		
	}
	
	//--Gerar novo rascunho----------
	public function gerarrascunhoAction(){
		$this->topoAction('kang');
				
	}	
	
	//--Busca produto --------------------------
	public function buscaprodutoAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		$this->view->qt			= (isset($params['qt'])) ? $params['qt'] : "";
		$this->view->vl			= (isset($params['vl'])) ? $params['vl'] : "";
		$this->view->objProd = ProdutosBO::listaProdutoscodigo($params);
				
	}

	public function gravarascunhoAction(){
		RascunhosBO::gravaPedido($this->_getAllParams());
		$this->_redirect('/admin/kang/rascunhos');
				
	}
	
	public function rascunhoeditAction(){
		RascunhosBO::editstatusRascunho($this->_getAllParams());
		$this->_redirect('/admin/kang/rascunhos');
				
	}
	
	//--Gera o pedido ----------------------
	public function visualizarascunhoAction(){
		$this->topoAction('kang');
		$params = $this->_getAllParams();
		
		foreach(RascunhosBO::listaPedidos() as $lista):
			if(md5($lista->id_racunho)==$params[ped]):
				$idfor 	= $lista->id_racunho;
				$status = $lista->status;
				$obs 	= $lista->obs;
			endif;
		endforeach;
		
		$this->view->objProdutos 	= RascunhosBO::listaProdutoscompra($idfor);
						
		$this->view->objPed			= $idfor;
		$this->view->objSta			= $status;
		$this->view->objObs			= $obs;
	}
	
	//--Remove rascunho--------------------------------------------------
	public function rascunhoremAction(){
		$params = $this->_getAllParams();
		foreach(RascunhosBO::listaPedidos() as $lista):
			if(md5($lista->id_racunho)==$params[rem]):
				$idfor = $lista->id_racunho;
				
			endif;
		endforeach;
		
		RascunhosBO::removerRascunho($idfor);
		$this->_redirect('/admin/kang/rascunhos');
	}
	
	//---Erro por falta de acesso-----------------------------------
	public function erroAction(){
		$this->topoAction('kang');
	}
	
	public function gerarxmlvendaAction(){
		$this->_helper->layout()->disableLayout();
		$this->view->objDom = KangvendasBO::gerarXmlcominvoice($this->_getAllParams());	
			
	}
	
	//---Gerar pedidos de compra apartir do pedido de venda ------------------------
	public function gerarpedidocompraAction(){		
		$this->topoAction('kang');
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 64) as $list);
		
        if($list->visualizar==1):
    		$this->view->objEdi		= $list->editar;
			$params = $this->_getAllParams();			
			
			$this->view->objProd	= KangvendasBO::listaPedidosvendasprod($params);
			$this->view->objPed		= KangvendasBO::listaPedidosvendasdet($params);
			$this->view->objEmp		= KangcomprasBO::listaEmpresaspedidosvenda($params);
			
			$this->view->objPedcomp	= KangcomprasBO::buscaCompraporvenda($params);
			$this->view->objProdped = KangcomprasBO::buscaProdutoscompraporvenda($params);
			
			$this->view->objProdent	= KangcomprasBO::listaProdentreguesporvenda($params);
			$this->view->objFin		= FinanceirochinaBO::buscaPagamentosporvenda($params);
			
			/*$this->view->objProd	= KangcomprasBO::listaProdutoscompra($params['ped']);
			$this->view->objProdent	= KangcomprasBO::listaProdutosentregue($params['ped']);
			$this->view->objFin		= FinanceirochinaBO::buscaPagamentoscompras($params['ped'],1);*/
		else:		
			$this->_redirect("/admin/kang/erro");
        endif;        
	}
	
	//-- Grava pedidos de compra apartir da venda ----------------------------
	public function gravarcompraAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		KangcomprasBO::gerarCompra($params);
		KangcomprasBO::gravarEntregacompraporvenda($params);
		
		if($params['fecharpedido']==1):
			KangcomprasBO::fecharPedidoscompraporvenda($params);
		endif;		
		$this->_redirect('/admin/kang/gerarpedidocompra/ped/'.md5($params['pedido']));				
	}
		
	//--- Pedidos de compra da Shunkang -------------------------------
	public function pedidoscompraAction(){
		$this->topoAction('kang');
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 55) as $list);
		
        if($list->visualizar==1):
        	$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
			
			$params = $this->_getAllParams();
			$this->view->translate	=	Zend_Registry::get('translate');
			
			$this->view->objFornec	= ClientesBO::buscaParceiros("fornecedorchines");
			
		else:		
			$this->_redirect("/admin/kang/erro");
        endif;
        		
		LogBO::cadastraLog("Kang/Pedidos",1,$usuario->id,"","");
	}
	
	public function buscapedidoscompraAction(){
	
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 55) as $list);
		foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $listper);
		$this->view->objNivel = $listper->nivel;
			
		$this->view->objIns		= $list->inserir;
		$this->view->objEdi		= $list->editar;

		$this->_helper->layout->disableLayout();
		
		if($list->visualizar==1){
			$this->view->translate	=	Zend_Registry::get('translate');
			
			$params = $this->_getAllParams();
				
			$page = $params['page'];
				
			$arrayRet = KangvendasBO::listaPedidoscompra($params);
			
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
			$this->_redirect("/admin/kang/erro");
		}		
	}
	
	//---Exibe o pedido de compra ------------------------s
	public function pedidoscomprapedAction(){		
		$this->topoAction('kang');
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 64) as $list);
		
        if($list->visualizar==1):
    		$this->view->objEdi		= $list->editar;
			$params = $this->_getAllParams();
			$this->view->objPed		= KangcomprasBO::buscaCompras($params['ped']);
			$this->view->objProd	= KangcomprasBO::listaProdutoscompra($params['ped']);
			$this->view->objProdent	= KangcomprasBO::listaProdutosentregue($params['ped']);
			$this->view->objFin		= FinanceirochinaBO::buscaPagamentoscompras($params['ped'],1);
			
			
			$this->view->params = $params; 
			
		else:		
			$this->_redirect("/admin/kang/erro");
        endif;        
	}
	
	public function editarpedidoscompraAction(){
	    $this->_helper->layout->disableLayout();
	    $params = $this->_getAllParams();
	    
	    $kangcomprasBO = new KangcomprasBO();	    
	    $kangcomprasBO->editarPedidoscompra($params);
	    
	    echo md5($params['pedido']);
	    
	    exit();
	}
	
	
	//--Finaliza compra-----------------------------------------
	public function fecharcompraAction(){
		$params = $this->_getAllParams();
		KangcomprasBO::gravaEntrega($params);
		KangcomprasBO::fecharPedido($params);
		$this->_redirect('/admin/kang/pedidoscompraped/ped/'.md5($params[pedido]));
	}
	
	//--- Grava a entrega de produto -------------------------------
	public function gravarentregaAction(){
		$params	= $this->_getAllParams();
		KangcomprasBO::gravaEntrega($params);
		$this->_redirect("/admin/kang/pedidoscompraped/ped/".md5($params['pedido']));
	}	
	
	//--Remove entrega--------------------------------------------------
	public function removeentregaAction(){
	    $this->_helper->layout->disableLayout();
	    
		$params = $this->_getAllParams();
		
		foreach (KangcomprasBO::buscaCompras(md5($params['ped'])) as $ped);
		KangcomprasBO::removeEntrega($params);
		
		if($params['tp']==2):
			$this->_redirect('/admin/kang/gerarpedidocompra/ped/'.md5($ped->id_ped));
		else: 
			//$this->_redirect('/admin/kang/pedidoscompraped/ped/'.md5($params['ped']));
		endif;
		
		exit();
	}
		
	//--Listar obs----------------------------
	public function pedidosobsAction(){
		$this->topoAction('kang');
		$this->view->translate	=	Zend_Registry::get('translate');
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 64) as $list);
		
        if($list->visualizar==1):
    		$params = $this->_getAllParams();
			$this->view->objObs 		= KangcomprasBO::listaObs();
			$this->view->objObsgravados = KangcomprasBO::listaObsgravados($this->_getAllParams());
			$this->view->objPed			= KangcomprasBO::buscaCompras($params['ped']);
			$this->view->objVer			= $params['ver'];
			$this->view->objTp			= $params['tp'];
		else:		
			$this->_redirect("/admin/kang/erro");
        endif;
		
	}
	
	function uploadqcrAction(){
	    $this->_helper->layout->disableLayout();
	    
	    $params = $this->_getAllParams();
	    
	    KangcomprasBO::uploadqcr($params);
	    
	    exit();
	}
	
	//--Listar obs----------------------------
	public function regracomprasAction(){
		$this->topoAction('kang');
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 64) as $list);
		
        if($list->visualizar==1):
            $params = $this->_getAllParams();
            $this->view->objObs = KangcomprasBO::listaObs($params);
			$this->view->params = $params;
		else:		
			$this->_redirect("/admin/kang/erro");
        endif;
		
	}
	
	//--Listar obs----------------------------
	public function regracomprasgruposAction(){
		$this->topoAction('kang');
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 64) as $list);
	
		if($list->visualizar==1):
    		$params = $this->_getAllParams();
    		if($this->_request->isPost()){
    			KangcomprasBO::gravaGruposobs($params);
    		}
    		
    		$this->view->objList 		= KangcomprasBO::listaGruposobs();
			
		else:
		      $this->_redirect("/admin/kang/erro");
		endif;
	
	}
	
	//--Remove Regra de compra--------------------------------
	public function removeregracomprasgrupoAction(){
		$params = $this->_getAllParams();
		KangcomprasBO::removerGruposobs($params);
		$this->_helper->flashMessenger->addMessage(array('Sucesso!'=>'Grupo deletado com sucesso!'));
		$this->_redirect('/admin/kang/regracomprasgrupos');
	}
	
	//--Busca regras por pedido -----------------------------
	public function buscaregrascomprasgrupoAction(){
	    $this->_helper->layout->disableLayout();
	    $params = $this->_getAllParams();
	    
	    KangcomprasBO::buscaRegrascomprasgrupos($params);
	    
	    exit();
	}
	
	//--Busca regras por pedido -----------------------------
	public function buscaregrascomprasAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		 
		KangcomprasBO::buscaRegrascompras($params);
		 
		exit();
	}
	
	
	//--Gravar obs--------------------------------
	public function gravarobsAction(){
	    $this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		echo KangcomprasBO::gravaObs($params);
		exit();		
	}
	
	//--Imprimir pedido de compra--------------------------------
	public function pedidoscompimpAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
				
		$this->view->objPed			= KangcomprasBO::buscaCompras($params['ped']);
		$this->view->objProdutos	= KangcomprasBO::listaProdutoscompra($params['ped']);
		$this->view->objObsgravados = KangcomprasBO::listaObspedidos($params['ped']);
		
		foreach (KangcomprasBO::buscaCompras($params['ped']) as $lista);
		$this->view->objCliente		= ClientesBO::listaChina($lista->id_for);
		$this->view->objEndCliente	= ClientesBO::listaEnderecoschines($lista->id_for);
		$this->view->objTelCliente	= ClientesBO::listaTelefones($lista->id_for);
		$this->view->objUfchhina	= EstadosBO::listarEstadosChina();	

		$this->view->objParams = $params;		
				
	}
	
	
	function baixarfinanceirocompraAction(){
		KangcomprasBO::baixarfinanceiroCompra($this->_getAllParams());
		$this->_redirect("/admin/kang/pedidoscompra");
	}	
	
	function removecompraAction(){
	    $this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		
		$kangcomprasBO = new KangcomprasBO();
		
		$id = $kangcomprasBO->removeCompra($params);
		
		if($params['tp']==2):
			$this->_redirect("/admin/kang/gerarpedidocompra/ped/".md5($params['purc']));
		else:
		$this->_redirect("/admin/kang/pedidoscompraped/ped/".md5($params['ped']));
		endif;
		
		exit();		
	}
	
	//--Gravar Regra de compra--------------------------------
	public function gravarregracompraAction(){
		$params = $this->_getAllParams();
		KangcomprasBO::gravaRegrascompra($params);
		$this->_redirect('/admin/kang/regracompras/grupo/'.$params['grupo']);		
	}
	
	//--Remove Regra de compra--------------------------------
	public function removeregracompraAction(){
		$params = $this->_getAllParams();
		KangcomprasBO::removerRegrascompra($params);
		$this->_redirect('/admin/kang/regracompras');		
	}
	
	
	//--- Geracao comercial invoice ---------------------------------------------
	public function gerarvendaAction(){
		$this->topoAction('kang');
		$this->view->translate	=	Zend_Registry::get('translate');		
		$this->view->objEmp		= KangvendasBO::listaEmpresasgerarinvoice();
	}
	
	public function gerarvendaempAction(){
		$this->topoAction('kang');
		$this->view->translate	=	Zend_Registry::get('translate');
		$params		= $this->_getAllParams();
		$this->view->objComp	= KangvendasBO::buscaComprasparainvoice($params);
		$this->view->objProd	= KangvendasBO::buscaProdutosparainvoice($params);
		$this->view->objEmp		= $params['empresa'];
	}
	
	public function gravarvendaempAction(){
		$params		= $this->_getAllParams();
		$id = KangvendasBO::gravarComercialinvoice($this->_getAllParams());
		$this->_redirect("/admin/kang/vendasdet/ped/".md5($id));
	}
	
	
	public function gravaordempalletAction(){
		$params = $this->_getAllParams();
		$this->_helper->layout->disableLayout();
		
		$kangvendasBO = new KangvendasBO();
		$kangvendasBO->atualizaOrdempallet($params);	

		exit();
	}
	
	public function vendasdetAction(){
		$this->topoAction('kang');
		$this->view->translate	=	Zend_Registry::get('translate');
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 44) as $list);
		
        if($list->visualizar==1):
        	$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
			
			$params = $this->_getAllParams();
			
			$this->view->objDet		= KangvendasBO::buscaCominvoice($params['ped']);
			$this->view->objProd	= KangvendasBO::listaCominvoiceprod($params['ped'],"");
			$this->view->objProdpk	= KangvendasBO::buscaProdutosinvoicecompra($params['ped']);
			
			$this->view->objFin		= FinanceirochinaBO::listarFininvoice($params['ped']);
			
			foreach (KangvendasBO::listaPedidosvendasdet($params) as $lista);
			$idped = $lista->ID;

			LogBO::cadastraLog("Kang/Detalha Venda",1,$usuario->id,$idped,"S".substr("000000".$idped, -6,6));
		else:		
			$this->_redirect("/admin/kang/erro");
        endif;        
	}
	
	/**
	 * vendasretornoAction 
	 */
	public function vendasretornoAction(){
	    $this->topoAction('kang');
	    $this->view->translate	=	Zend_Registry::get('translate');
	    $usuario = Zend_Auth::getInstance()->getIdentity();
	    
	    foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 44) as $list);
	
	    if($list->visualizar==1){
    	    $this->view->objIns		= $list->inserir;
    	    $this->view->objEdi		= $list->editar;
    	    	
    	    $params = $this->_getAllParams();
    	    
    	    $kangvendasBO  = new KangvendasBO();
    	    $kangretornoBO = new KangretornoBO();
    	    
    	    $this->view->objProdutos   = $kangretornoBO->listaProdutosinvocie($params['ped']);
    	    $this->view->objDet		   = $kangvendasBO->buscaCominvoice($params['ped']);
    	    
    	    LogBO::cadastraLog("Kang/Detalha Venda",1,$usuario->id,$this->view->objDet[0]->idped,"S".substr("000000".$this->view->objDet[0]->idped, -6,6));
	    }else{
	       $this->_redirect("/admin/kang/erro");
	    }
	}
	
	/*
	* vendasretornoimpAction
	*/
	public function vendasretornoimpAction(){
	    $this->_helper->layout->disableLayout();
	    
	    $this->view->translate	=	Zend_Registry::get('translate');
	    $usuario = Zend_Auth::getInstance()->getIdentity();
	     
	    foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 44) as $list);
	
	    if($list->visualizar==1){
	        	
	        $params = $this->_getAllParams(); 
	        	
	        $kangvendasBO  = new KangvendasBO();
	        $kangretornoBO = new KangretornoBO();
	        	
	        $this->view->objProdutos   = $kangretornoBO->listaProdutosinvocie($params['ped']);
	        $this->view->objDet		   = $kangvendasBO->buscaCominvoice($params['ped']);
	        
	    }else{
	        $this->_redirect("/admin/kang/erro");
	    }
	    
	}
	
	
	//---Pack List-------------------------------------------------	
	public function vendalistapacotesAction(){
		$this->topoAction('kang');
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 44) as $list);
		
        if($list->visualizar==1):
        	$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
			
			$params = $this->_getAllParams();
			$this->view->translate	=	Zend_Registry::get('translate');
			
			$this->view->objDet		= KangvendasBO::buscaCominvoice($params['ped']);
			
			$this->view->objProd	= KangvendasBO::listaCominvoiceprod($params['ped'],"");
			$this->view->objProdpk	= KangvendasBO::buscaProdutosinvoicecompra($params['ped']);			
			
			$this->view->objPack	= KangvendasBO::listarPacklistcad($params,"");
			$this->view->objProdpl	= KangvendasBO::listarPacklistprod($params);
			
			/*Situacoes da invoice:
			 * 0 - Cancelado
			 * 1 - Aguardando documentos
			 * 2 - Aguardando documentos
			 * 3 - Aguardando embarque /Embarcado
			 * */
			
		else:		
			$this->_redirect("/admin/kang/erro");
        endif;
        		
		LogBO::cadastraLog("Kang/PL Venda",1,$usuario->id,"","");
	}
	
	public function buscapaleteAction(){
	    $this->_helper->layout->disableLayout();
	    $params = $this->_getAllParams();
	    $this->view->translate	=	Zend_Registry::get('translate');
	     
	    $kangvendasBO = new KangvendasBO();
	    
	    $this->view->objDet		= $kangvendasBO->buscaCominvoice($params['ped']);
	    	
	    $this->view->objProd	= $kangvendasBO->listaCominvoiceprod($params['ped'],"");
	    $this->view->objProdpk	= $kangvendasBO->buscaProdutosinvoicecompra($params['ped']);
	    	
	    $this->view->objPack	= $kangvendasBO->listarPacklistcad($params,"");
	    $this->view->objProdpl	= $kangvendasBO->listarPacklistprod($params);
	    
	    $bov	= new KangvendasModel();
	    $bo 	= new PacklistModel();
	    
	    $pack = $bo->fetchRow("sit = 1 and md5(id_cominvoice) = '".$params['ped']."'", 'ordemfinal desc');
	    if(count($pack)>0){
	        $this->view->ordem = $pack->ordemfinal+1;
	    }else{
	        $this->view->ordem = 1;
	    }	    
	}
	
	public function buscaduplicapaleteAction(){
	    $params = $this->_getAllParams();
	    $this->_helper->layout->disableLayout();
	    KangvendasBO::buscadadosDuplicapalete($params);
	    exit();
	}
	
	public function gerarpacklistAction(){
		$params = $this->_getAllParams();
		$this->_helper->layout->disableLayout();
		KangvendasBO::gerarPackinglist($params);
		$this->_redirect("/admin/kang/vendalistapacotes/ped/".md5($params['ped']));
	}
	
	public function vendalistapacotesforAction(){
		$this->topoAction('kang');
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 44) as $list);
	
		if($list->visualizar==1):
			$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
			$params = $this->_getAllParams();
			$this->view->translate	=	Zend_Registry::get('translate');
		
			if(!empty($params['fornmd'])):
				$forn =  $params['fornmd'];
				foreach (KangvendasBO::listaCominvoicefornecedores($params['ped']) as $fornecedores):
					if(md5($fornecedores->id_for) == $forn):
						$this->view->objForsel	= $fornecedores->id_for;
					endif;
				endforeach;
				
			elseif($params['forn']):
				$forn =  md5($params['forn']);
				$this->view->objForsel	= $params['forn'];
				
			endif;
			
			$this->view->objDet		= KangvendasBO::buscaCominvoice($params['ped']);
			$this->view->objProd	= KangvendasBO::listaCominvoiceprod($params['ped'],$forn);
			$this->view->objProdpk	= KangvendasBO::buscaProdutosinvoicecompra($params['ped']);
			$this->view->objFor		= KangvendasBO::listaCominvoicefornecedores($params['ped']);
			$this->view->objFordisp	= KangvendasBO::listaCominvoicefornprod($params['ped']);
			
			$this->view->objPack	= KangvendasBO::listarPacklistcad($params,$forn);
			$this->view->objProdpl	= KangvendasBO::listarPacklistprod($params);
		
		else:
			$this->_redirect("/admin/kang/erro");
		endif;
	
		LogBO::cadastraLog("Kang/PL Venda",1,$usuario->id,"","","");
	}
		
	public function gerarpacklistforAction(){
		$params = $this->_getAllParams();
		KangvendasBO::gerarPackinglist($params);
		$this->_redirect("/admin/kang/vendalistapacotesfor/ped/".md5($params['ped'])."/fornmd/".md5($params['idfor']));
	}
	
	public function selecionapacklistAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		KangvendasBO::selecionarPackinglist($params);
		$this->_redirect("/admin/kang/vendalistapacotes/ped/".md5($params['invoice']));		
	} 	
	
	public function removerpacklistAction(){
		$params = $this->_getAllParams();
		KangvendasBO::cancelarPackinglist($params);
		$this->_redirect("/admin/kang/vendalistapacotes/ped/".$params['ped']);		
	}
	
	public function removeallpacklistAction(){
	    $this->_helper->layout->disableLayout();
	    $params = $this->_getAllParams();
	    $kangvendasBO = new KangvendasBO();
	    
	    $kangvendasBO->cancelarTodospackinglist($params);
	    
	    exit();
	}

	public function removerpacklistforAction(){
		$params = $this->_getAllParams();
		KangvendasBO::cancelarPackinglist($params);
		$this->_redirect("/admin/kang/vendalistapacotesfor/ped/".$params['ped']."/fornmd/".md5($params['idfor']));
	}
	
	
	public function packcopiaAction(){
	    $this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		echo KangvendasBO::copiaPacklist($params);
		//$this->_redirect("/admin/kang/vendalistapacotes/ped/".($params['ped']));
		exit();			
	} 
	
	public function packcopiaforAction(){
		$params = $this->_getAllParams();
		KangvendasBO::copiaPacklist($params);
		$this->_redirect("/admin/kang/vendalistapacotesfor/ped/".$params['ped']."/fornmd/".md5($params['idfor']));
	}
	
	public function vendalistapacotesimpAction(){
		$this->_helper->layout->disableLayout();
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 44) as $list);
	
		if($list->visualizar==1){
			$params = $this->_getAllParams();
			$this->view->translate	=	Zend_Registry::get('translate');
		
			$kangvendasBO = new KangvendasBO();
			$clientesBO   = new ClientesBO();
			
			$this->view->objDet		= $kangvendasBO->buscaCominvoice($params['ped']);
			foreach ($kangvendasBO->buscaCominvoice($params['ped']) as $det);
			$this->view->objEnd		= $clientesBO->listaEnderecocomp($det->id_cliente, 1);
			$this->view->objTel		= $clientesBO->listaTelefonesUp($det->id_cliente, 'telefone1');
			$this->view->objFax		= $clientesBO->listaTelefonesUp($det->id_cliente, 'fax');
			
			$this->view->objProd	= $kangvendasBO->listaCominvoiceprod($params['ped'],"");
			$this->view->objProdpk	= $kangvendasBO->buscaProdutosinvoicecompra($params['ped']);
		
			$this->view->objPack	= $kangvendasBO->listarPacklistcad($params,"");
			$this->view->objProdpl	= $kangvendasBO->listarPacklistprod($params);
			
			$bo     = new ClientesModel();
			$boc    = new ClientesconsigneeModel();
			
			$bocidade = new CidadesModel();
			$boestado = new EstadosModel();
			$bopais   = new PaisesModel(); 
			
			$this->view->objConsignee = $boc->fetchRow('id_cliente = "'.$this->view->objDet[0]->id_cliente.'"');
			
			if($this->view->objConsignee->id_cidade != null){
			    $this->view->cidadeconsignee = $bocidade->fetchRow("id = '".$this->view->objConsignee->id_cidade."'");
			    $this->view->estadoconsignee = $boestado->fetchRow("id = '".$this->view->cidadeconsignee->id_estados."'");
			    $this->view->paisconsignee   = $bopais->fetchRow("id = '".$this->view->estadoconsignee->id_paises."'");
			}
						
			//-- total de pallets
			$bov	= new KangvendasModel();
			$bo 	= new PacklistModel();
			 
			$pack = $bo->fetchRow("sit = 1 and md5(id_cominvoice) = '".$params['ped']."'", 'ordemfinal desc');
			$this->view->qtpallets = (count($pack)>0) ? $pack->ordemfinal : 1;
						
		}else{
			$this->_redirect("/admin/kang/erro");
		}
	
		LogBO::cadastraLog("Kang/PL Venda Imp",1,$usuario->id,"","");
	}
	
	
	
	
	
	public function pedidoscompraentAction(){
		$this->topoAction('kang');
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 55) as $list);
		
        if($list->visualizar==1):
        	$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
			
			$params = $this->_getAllParams();
			$this->view->translate	=	Zend_Registry::get('translate');
			
			$this->view->objFor		= KangvendasBO::listaFornecedoreprodentregues();
			$this->view->objProd	= KangvendasBO::listaProdutosentregues();
						
		else:		
			$this->_redirect("/admin/kang/erro");
        endif;
        		
		LogBO::cadastraLog("Kang/Lista de pacotes",1,$usuario->id,"","");
	}
	
	public function vendaspurchaseAction(){
		$this->topoAction('kang');
		$this->view->translate	=	Zend_Registry::get('translate');
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 44) as $list);
	
		if($list->visualizar==1):
			$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
		
			$params = $this->_getAllParams();
		
			$this->view->objDet		= KangvendasBO::buscaCominvoice($params['ped']);
			$this->view->objFor		= KangvendasBO::listaCominvoicefornecedores($params['ped']);
			$this->view->objComp	= KangvendasBO::listaCominvoicepurchase($params['ped']);
			$this->view->objProd	= KangvendasBO::buscaProdutosinvoicecompra($params['ped']);
			$this->view->objFin		= FinanceirochinaBO::buscaPagamentosporinvoice($params);
	
		foreach (KangvendasBO::listaPedidosvendasdet($params) as $lista);
			$idped = $lista->ID;
			LogBO::cadastraLog("Kang/Venda por Fornecedor",1,$usuario->id,$idped,"S".substr("000000".$idped, -6,6));
		else:
			$this->_redirect("/admin/kang/erro");
		endif;
	}
	
	public function vendaspurchaseimpAction(){
		$this->_helper->layout->disableLayout();
		$this->view->translate	=	Zend_Registry::get('translate');
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 44) as $list);
	
		if($list->visualizar==1):
		$this->view->objIns		= $list->inserir;
		$this->view->objEdi		= $list->editar;
	
		$params = $this->_getAllParams();
	
		$this->view->objDet		= KangvendasBO::buscaCominvoice($params['ped']);
		$this->view->objFor		= KangvendasBO::listaCominvoicefornecedores($params['ped']);
		$this->view->objComp	= KangvendasBO::listaCominvoicepurchase($params['ped']);
		$this->view->objProd	= KangvendasBO::buscaProdutosinvoicecompra($params['ped']);
		$this->view->objFin		= FinanceirochinaBO::buscaPagamentosporinvoice($params);
	
		else:
		$this->_redirect("/admin/kang/erro");
		endif;
	}
	
	
	public function vendasdeclaracaoexpAction(){
		$this->topoAction('kang');
		$this->view->translate	=	Zend_Registry::get('translate');
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 44) as $list);
	
		if($list->visualizar==1):
			$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
		
			$params = $this->_getAllParams();
		
			$this->view->objDet		= KangvendasBO::buscaCominvoice($params['ped']);
			$this->view->objProddet = KangvendasBO::listaCominvoiceprodhscode($params['ped'],"");
			
			
			$this->view->objFor		= KangvendasBO::listaCominvoicefornecedores($params['ped']);
			$this->view->objComp	= KangvendasBO::listaCominvoicepurchase($params['ped']);
			$this->view->objProd	= KangvendasBO::buscaProdutosinvoicecomprahscode($params['ped']);
			$this->view->objFin		= FinanceirochinaBO::buscaPagamentosporinvoice($params);
					
			foreach (KangvendasBO::listaPedidosvendasdet($params) as $lista);
			$idped = $lista->ID;
			LogBO::cadastraLog("Kang/Declaracao de exportacao",1,$usuario->id,$idped,"S".substr("000000".$idped, -6,6));
		else:
			$this->_redirect("/admin/kang/erro");
		endif;
	}
	
	public function gravarretornoinvoiceAction(){
		$params = $this->_getAllParams();
		KangvendasBO::gravarDadosretornoinvoice($params);
		$this->_redirect("/admin/kang/vendasdeclaracaoexp/ped/".$params['ped']);
		
	}
	
	public function gravarinvoiceAction(){
	    $this->_helper->layout->disableLayout();
	    
	    $params = $this->_getAllParams(); 
		KangvendasBO::gravarDadoscominvoice($params);
		
		
		$this->_redirect("/admin/kang/vendasdet/ped/".md5($params['invoice']));
	}
	
	public function invoicecomimpAction(){
		$this->_helper->layout->disableLayout();
		$this->view->translate	=	Zend_Registry::get('translate');
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 44) as $list);
	
		if($list->visualizar==1){
			
			$params = $this->_getAllParams();
		
			$this->view->objDet		= KangvendasBO::buscaCominvoice($params['ped']);
			foreach (KangvendasBO::buscaCominvoice($params['ped']) as $det);
			$this->view->objEnd		= ClientesBO::listaEnderecocomp($det->id_cliente, 1);
			$this->view->objTel		= ClientesBO::listaTelefonesUp($det->id_cliente, 'telefone1');
			$this->view->objFax		= ClientesBO::listaTelefonesUp($det->id_cliente, 'fax');
			$this->view->objProd	= KangvendasBO::listaCominvoiceprod($params['ped'],"");
			
			$this->view->objPack	= KangvendasBO::listarPacklistcad($params,"");
			$this->view->objProdpl	= KangvendasBO::listarPacklistprod($params);
		
			$bo     = new ClientesModel();
			$boc    = new ClientesconsigneeModel();
			
			$bocidade = new CidadesModel();
			$boestado = new EstadosModel();
			$bopais   = new PaisesModel();
			
			$this->view->objConsignee = $boc->fetchRow('id_cliente = "'.$det->id_cliente.'"');
			
			if($this->view->objConsignee->id_cidade != null){
			    $this->view->cidadeconsignee = $bocidade->fetchRow("id = '".$this->view->objConsignee->id_cidade."'");
			    $this->view->estadoconsignee = $boestado->fetchRow("id = '".$this->view->cidadeconsignee->id_estados."'");
			    $this->view->paisconsignee   = $bopais->fetchRow("id = '".$this->view->estadoconsignee->id_paises."'");
			}
			
		}else{
		  $this->_redirect("/admin/kang/erro");
		}
	}
	
	/**
	 * Lista de PL
	 */
	public function vendastraducaoAction(){
	    $this->_helper->layout->disableLayout();
	    $this->view->translate	=	Zend_Registry::get('translate');
	    $usuario = Zend_Auth::getInstance()->getIdentity();
	    foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 44) as $list);
	
	    if($list->visualizar==1){
	        	
	        $params = $this->_getAllParams();
	
	        $this->view->objDet		= KangvendasBO::buscaCominvoice($params['ped']);
	        foreach (KangvendasBO::buscaCominvoice($params['ped']) as $det);
	        $this->view->objEnd		= ClientesBO::listaEnderecocomp($det->id_cliente, 1);
	        $this->view->objTel		= ClientesBO::listaTelefonesUp($det->id_cliente, 'telefone1');
	        $this->view->objFax		= ClientesBO::listaTelefonesUp($det->id_cliente, 'fax');
	        $this->view->objProd	= KangvendasBO::listaCominvoiceprod($params['ped'],"");
	        	
	        $this->view->objPack	= KangvendasBO::listarPacklistcad($params,"");
	        $this->view->objProdpl	= KangvendasBO::listarPacklistprod($params);
	
	        $bo     = new ClientesModel();
	        $boc    = new ClientesconsigneeModel();
	        	
	        $bocidade = new CidadesModel();
	        $boestado = new EstadosModel();
	        $bopais   = new PaisesModel();
	        	
	        $this->view->objConsignee = $boc->fetchRow('id_cliente = "'.$det->id_cliente.'"');
	        	
	        if($this->view->objConsignee->id_cidade != null){
	            $this->view->cidadeconsignee = $bocidade->fetchRow("id = '".$this->view->objConsignee->id_cidade."'");
	            $this->view->estadoconsignee = $boestado->fetchRow("id = '".$this->view->cidadeconsignee->id_estados."'");
	            $this->view->paisconsignee   = $bopais->fetchRow("id = '".$this->view->estadoconsignee->id_paises."'");
	        }
	        	
	    }else{
	        $this->_redirect("/admin/kang/erro");
	    }
	}

	public function vendasclassesAction(){
	    $this->_helper->layout->disableLayout();
	    $this->view->translate	=	Zend_Registry::get('translate');
	    $usuario = Zend_Auth::getInstance()->getIdentity();
	    foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 44) as $list);
	    
	    if($list->visualizar==1){
	        
	        $params = $this->_getAllParams();
	        
	        $this->view->objDet		= KangvendasBO::buscaCominvoice($params['ped']);
	        $this->view->objProd	= KangvendasBO::listaCominvoiceprodclasse($params['ped'],"");
	        	        
	    }else{
	        $this->_redirect("/admin/kang/erro");
	    }
	}
	
	
	public function liberarcompraAction(){
		$this->_helper->layout->disableLayout();
	
		$params = $this->_getAllParams();
	
		$bo = new KangcomprasModel();
		$bo->update(array('sit' => 1), "id_kang_compra = '".$params['id']."'");
	
		exit();
	}
	
    /* classes */
	public function produtosclasseAction(){
	    $this->topoAction('kang');
	    $this->view->translate	=	Zend_Registry::get('translate');
	    $usuario = Zend_Auth::getInstance()->getIdentity();
	    foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 44) as $list);
	    
	    if($list->visualizar==1){
    	    $this->view->objIns		= $list->inserir;
    	    $this->view->objEdi		= $list->editar;
    	    
    	    $params = $this->_getAllParams();
    	    
    	    $bo = new ProdutosclassesModel();
    	    $this->view->objProdutosclasses = $bo->fetchAll("id > 0", "letra asc");
    	    
    	    LogBO::cadastraLog("Kang/Produtos classes", 1, $usuario->id, "","");
	    }else{
	        $this->_redirect("/admin/kang/erro");
	    }
	}
	
	public function gravaprodutosclasseAction(){
	    $params = $this->_getAllParams();
	    
	    $bo = new ProdutosclassesModel();
	    
	    $data = array(
            'letra'     => $params['letra'],
            'margem'    => $params['margem'],
	    );
	    
	    if(!empty($params['idprodutosclasse'])){
	        $bo->update($data, "id = '".$params['idprodutosclasse']."'");
	    }else{
	        $bo->insert($data);
	    }
	    	    
	    $this->_redirect("/admin/kang/produtosclasse");	    
	}
	
	public function removeprodutosclasseAction(){
	    $params = $this->_getAllParams();
	    
	    $bo = new ProdutosclassesModel();
	    $bo->delete("id = '".$params['idprodutosclasse']."'");
	    	    
	    $this->_redirect("/admin/kang/produtosclasse");
	}
	
}