<?php

class Admin_ComprasController extends Zend_Controller_Action {
			
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
	
	//--Lista produtos pedidos -------------------------------
	public function produtospedidosAction(){
		$this->topoAction();
				
		Zend_Paginator::setDefaultScrollingStyle('Sliding');
		Zend_View_Helper_PaginationControl::setDefaultViewPartial('index/paginator.phtml');
		$paginator = Zend_Paginator::factory(ComprasBO::listaProdutospedidos($this->_getAllParams()));
		$currentPage = $this->_getParam('page', 1);
		$paginator
		->setCurrentPageNumber($currentPage)
		->setItemCountPerPage(20);
		
		LogBO::cadastraLog("Compras/Produtos Pedidos",1,$_SESSION['S_ID'],"","");
		
		$this->view->objProdutos = $paginator;
		$params = $this->_getAllParams();
		$this->view->objPage = $params[page]; 
		
	}
	
	//--Imprime produtos pedidos -------------------------------
	public function produtospedidosimpAction(){
		$this->_helper->layout->disableLayout();
		$this->view->objProdutos = ComprasBO::listaProdutospedidos();
		
	}
	//---Busca estoque-----------------------------------
	public function estoqueAction(){
		$this->topoAction();

		$this->view->objGrupo 		= GruposprodBO::listaGrupos();
		$this->view->objGrupocompra = GruposprodBO::listaGruposcompra();
	}
	
	//--Exibe o extrato do produto-----------------------------------------
	public function extratoprodAction(){
		$sessao = new Zend_Session_Namespace('Default');
		date_default_timezone_set('America/Sao_Paulo');
		
		$this->topoAction();
		$params = $this->_getAllParams();
		
		if(!empty($params[codproduto]))	$sessao->busca = $params;
		
		$this->view->objCod 		= $sessao->busca[codproduto];
					
		Zend_Paginator::setDefaultScrollingStyle('Sliding');
		Zend_View_Helper_PaginationControl::setDefaultViewPartial('index/paginator.phtml');
		$paginator = Zend_Paginator::factory(ComprasBO::listaExtratoproduto($sessao->busca));
		$currentPage = $this->_getParam('page', 1);
		$paginator
		->setCurrentPageNumber($currentPage)
		->setItemCountPerPage(25);
		
		$this->view->objList = $paginator;
		$this->view->objPag = $params[page];		
		
		$cont=0;
		foreach (ComprasBO::listaExtratoproduto($sessao->busca) as $list):
			$cont++;
			if($cont==1):
				$this->view->objQtfinal = $list->qt_atual;	
			endif;
		endforeach;		
		
		if((!empty($sessao->busca['dataini'])) and (!empty($sessao->busca['datafin']))):
			$this->view->objDtini = $sessao->busca[dataini];
			$this->view->objDtfin = $sessao->busca[datafin];
		elseif((!empty($sessao->busca['dataini'])) and (empty($sessao->busca['datafin']))):
			$this->view->objDtini = $sessao->busca[dataini];
			$this->view->objDtfin = date("d/m/Y");
		elseif((empty($sessao->busca['dataini'])) and (!empty($sessao->busca['datafin']))):
			$this->view->objDtfin = $sessao->busca[datafin];
		else:
			$this->view->objDtini = (date("d"))."/".(date("m")-1)."/".date("Y");
			$this->view->objDtfin = date("d/m/Y");
		endif;
		
	}
	
	//--Exibe o extrato do produto-----------------------------------------
	public function extratoprodexpAction(){
		$this->_helper->layout->disableLayout();
		$sessao = new Zend_Session_Namespace('Default');
		$params = $this->_getAllParams();		
		if(!empty($params[codproduto])):	
			$sessao->busca = $params;
		endif;
		
		$this->view->objCod 		= $sessao->busca[codproduto];
		$this->view->objTipo		= $params['tipo'];			
		$this->view->objList 		= ComprasBO::listaExtratoproduto($sessao->busca);		
	}
	
	public function extratoprodimpAction(){
		$sessao = new Zend_Session_Namespace('Default');
		date_default_timezone_set('America/Sao_Paulo');
		$this->_helper->layout->disableLayout();
		
		$params = $this->_getAllParams();
		
		if(!empty($params[codproduto]))	$sessao->busca = $params;
		
		$this->view->objCod 		= $sessao->busca[codproduto];
				
		$this->view->objList = ComprasBO::listaExtratoproduto($sessao->busca);
		$this->view->objPag = $params[page];		
		
		$cont=0;
		foreach (ComprasBO::listaExtratoproduto($sessao->busca) as $list):
			$cont++;
			if($cont==1):
				$this->view->objQtfinal = $list->qt_atual;	
			endif;
		endforeach;
		
		if((!empty($sessao->busca['dataini'])) and (!empty($sessao->busca['datafin']))):
			$this->view->objDtini = $sessao->busca[dataini];
			$this->view->objDtfin = $sessao->busca[datafin];
		elseif((!empty($sessao->busca['dataini'])) and (empty($sessao->busca['datafin']))):
			$this->view->objDtini = $sessao->busca[dataini];
			$this->view->objDtfin = date("d/m/Y");
		elseif((empty($sessao->busca['dataini'])) and (!empty($sessao->busca['datafin']))):
			$this->view->objDtfin = $sessao->busca[datafin];
		else:
			$this->view->objDtini = (date("d"))."/".(date("m")-1)."/".date("Y");
			$this->view->objDtfin = date("d/m/Y");
		endif;
		
	}
	
	//---Lista os produtos por grupo de estoque-------------------------------------
	public function estoquegrupoAction(){
		$params = $this->_getAllParams();
		
		$usuario = Zend_Auth::getInstance()->getIdentity();
		
		foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $listr);
		$this->view->objNivel	= $listr->nivel;
		
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 62) as $list);
		
		if(($list->visualizar==1)){
		
		    $sessaobusca = new Zend_Session_Namespace('Estoqueprod');
		    
			if(($params['fil']=='comp')):
				$sessaobusca->where = "";
				$sessaobusca->wheremax = "";
			endif;			
				
			//--- Memoriza os valores da busca ----------------------------------------
			if($this->_request->isPost()){
			    $sessaobusca->pesq 		= $this->_getAllParams();
			    $sessaobusca->wheremax 	= "";
			    $sessaobusca->where 	= "";
			}
			
			$this->view->objPesq = $sessaobusca->pesq;
			
			$this->topoAction();
			$grupo = $this->_getAllParams();
			
			LogBO::cadastraLog("Compras/Estoque",1,$usuario->id,"","");
			
			Zend_Paginator::setDefaultScrollingStyle('Sliding');
			Zend_View_Helper_PaginationControl::setDefaultViewPartial('index/paginator.phtml');
			$paginator = Zend_Paginator::factory(ComprasBO::listaEstoquegrupo($this->_getAllParams()));
			$currentPage = $this->_getParam('page', 1);
			$paginator->setCurrentPageNumber($currentPage)
					  ->setItemCountPerPage(10);
			
			$this->view->objProdutos 	= $paginator;
			$this->view->objGrupo	= GruposprodBO::listaGruposcompra();
			$this->view->objGrupov 	= GruposprodBO::listaGruposprodutos();
			
			if(!empty($grupo['grupovenda'])):
				foreach (GruposprodBO::listaGruposprodutos() as $listgrup):
					if($grupo['grupovenda']==$listgrup->id):
						$filtro = "Grupo: <b>".$listgrup->descricao."</b>";
					endif;
				endforeach;
				
				if(!empty($grupo['buscagruposub'])):
					foreach (GruposprodBO::listaGruposprodutossub("") as $listsubg):
						if($grupo['buscagruposub']==$listsubg->id):
							$filtro .= " &nbsp; Subgrupo: <b>".$listsubg->descricao."</b>";
						endif;
					endforeach;
					
				endif;
				$this->view->objFil	= $filtro;
				
			elseif(!empty($grupo['buscagrupo'])):
				foreach (GruposprodBO::listaGruposcompra() as $listgrup):
					if($grupo['buscagrupo']==$listgrup->id):
						$filtro = "Grupo de Compra: <b>".$listgrup->purchasing."</b>";
					endif;
				endforeach;			
				$this->view->objFil	= $filtro;				
				
			endif;
			
		}else{		
			$this->_redirect("/admin/compra/erro");
		};	
	}

	public function estoqueresumoAction(){
	    $usuario = Zend_Auth::getInstance()->getIdentity();
	    
	    foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 62) as $list);
	    
	    if(($list->visualizar==1)){
	    	$this->topoAction();	    	
	    }else{
	        $this->_redirect("/admin/compra/erro");
	    }
	}
	
	public function estoquevalorAction(){
		$usuario = Zend_Auth::getInstance()->getIdentity();
		 
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 62) as $list);
		 
		if(($list->visualizar==1)){
			$this->topoAction();
		}else{
			$this->_redirect("/admin/compra/erro");
		}
	}
	
	public function buscaestoqueresumoAction(){
	    $this->_helper->layout->disableLayout();
	    $params = $this->_getAllParams();
	    EstoqueBO::valorEstoque($params['periodo']);
	    exit();
	}
	
	public function buscaestoqueresumototalAction(){
		$this->_helper->layout->disableLayout();
		EstoqueBO::valorEstoqueatual();
		exit();
	}
	
	//---Exporta pra xls/ods produtos por grupo de estoque-------------------------------------
	public function estoquegrupoexpAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		if(!empty($params['codproduto'])):
			$this->_redirect('/admin/compras/extratoprod/codproduto/'.$params['codproduto']);	
		else:		
			$this->view->objProdutos 	= ComprasBO::listaEstoquegrupo($this->_getAllParams());
			$this->view->objGrupo		= GruposprodBO::listaGruposcompra();
			$this->view->objGrupov 		= GruposprodBO::listaGruposprodutos();	
			$this->view->objTipo		= $params['tipo'];		
		endif;
	}
		
	//--Ajustes no estoque-----------------------------
	//--Lista ajuste estoque-------
	public function ajustestoqueAction(){
		$this->topoAction();

		$params = $this->_getAllParams();
		
		Zend_Paginator::setDefaultScrollingStyle('Sliding');
		Zend_View_Helper_PaginationControl::setDefaultViewPartial('index/paginator.phtml');
		$paginator = Zend_Paginator::factory(EstoqueBO::listaAjuste($params));
		$currentPage = $this->_getParam('page', 1);
		$paginator
		->setCurrentPageNumber($currentPage)
		->setItemCountPerPage(10);
		
		$this->view->objList = $paginator;
		$this->view->objPag = $params[page];
		
		$usuario = Zend_Auth::getInstance()->getIdentity();
        
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 62) as $list);
		
		$this->view->objIns		= $list->inserir;
		$this->view->objEdi		= $list->editar;
		$this->view->objNeg		= $list->saldo_neg;
		$this->view->objEnt		= ComprasBO::buscaUltimaentrada();		
		
		LogBO::cadastraLog("Estoque/Ajuste",1,$usuario->id,"","");
		
	}
	
	//--Lista produtos ajuste estoque-------
	public function ajusteprodAction(){
		$this->topoAction();
		$params = $this->_getAllParams();
		
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 62) as $list);
		$this->view->objAut		= $list->aba1;
		
		$this->view->objList = EstoqueBO::listaProdutosajuste($params);
		$this->view->objAjus = EstoqueBO::buscaAjuste($params);
	
		foreach (EstoqueBO::buscaAjuste($params) as $ajuste);		
		LogBO::cadastraLog("Estoque/Ajuste",1,$usuario->id,$ajuste->idajuste,"AJUSTE A".substr("000000".$ajuste->idajuste,-6,6));
		
	}
	
	public function autorizarajusteAction(){
		EstoqueBO::autorizaAjuste($this->_getAllParams());
		$this->_redirect('/admin/compras/ajustestoque');	
	}	
	
	//--Lista produtos ajuste estoque-------
	public function ajusteprodimpAction(){
		$this->_helper->layout->disableLayout();
		$this->topoAction();
		$params = $this->_getAllParams();
		
		$this->view->objList = EstoqueBO::listaProdutosajuste($params);
		$this->view->objAjus = EstoqueBO::buscaAjuste($params);
		
		foreach (EstoqueBO::buscaAjuste($params) as $ajuste);
		LogBO::cadastraLog("Estoque/Ajuste Imp",1,$usuario->id,$ajuste->idajuste,"AJUSTE A".substr("000000".$ajuste->idajuste,-6,6));
	}
	
	//--Novo ajuste estoque-------
	public function ajustenovoAction(){
		$this->topoAction();
		
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 62) as $list);
		$this->view->objNeg		= $list->saldo_neg;
		
		$params = $this->_getAllParams();
		if(!empty($params['entrada'])):
			$this->view->objProdutos		= ComprasBO::listaProdutosentgroup($params);
			$this->view->objEntrada			= ComprasBO::listaEntrada($params);
		endif;
	}
	
	//--Remove ajuste--------------------------------------------------
	public function ajusteremAction(){
	    $this->_helper->layout->disableLayout();
	    
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 62) as $list);
		
		if($list->editar==true):
			$idaj = EstoqueBO::finalizaAjuste($this->_getAllParams());
			
			LogBO::cadastraLog("Estoque/Ajuste",3,$usuario->id,$idaj,"AJUSTE A".substr("000000".$idaj,-6,6));
			$this->_redirect('/admin/compras/ajustestoque');
		
		else:	
			$this->_redirect('/admin/compras/erro');
		endif;
		
		exit();
	}
	
	//--Gerar Ajuste manual - Busca produto --------------------------
	public function buscaprodutoAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		$this->view->objProd 	= ProdutosBO::listaProdutoscodigo($params);
		$this->view->qt			= $params[v2];
		$this->view->vl		  	= $params[v3];		
	}
	
	public function gravaajusteAction(){
	    //$this->_helper->layout->disableLayout();
		EstoqueBO::gravaAjuste($this->_getAllParams());
		$this->_redirect('/admin/compras/ajustestoque');
		//exit();						
	}
	
	public function gravaajusteentAction(){
		EstoqueBO::gravaAjusteent($this->_getAllParams());
		$this->_redirect('/admin/compras/ajustestoque');						
	}
	
	//----Entrada de produtos no estoque-------------------		
	//--- Entrada nacional -----------------------------------------------
	public function entradaestoqAction(){
		$this->topoAction();
		$params = $this->_getAllParams();
		
		$busca['idparceiromd5'] 		= $params['fornecedor'];
		$this->view->objForn			= ClientesBO::buscaParceiros("",$busca);
		$this->view->objProdped			= ComprasBO::listaProdutosporfornecedor();
		/* $this->view->objRet				= $params['ret'];
		$this->view->objQt				= $params['qtver'];
		$this->view->objProdutos		= ComprasBO::listaProdutosenttmpgroup();
		$this->view->objProddet			= ComprasBO::listaProdutosenttmp(); */
		
		LogBO::cadastraLog("Compras/Entrada Estoque",1,$_SESSION['S_ID'],"","");
			
	}
		
	public function entradaestoqimpnacAction(){
		$this->topoAction();
		$this->view->objProdutos	= ComprasBO::listaProdutosenttmpgroup();
		$this->view->objProddet		= ComprasBO::listaProdutosenttmp();
		$this->view->objImp			= ComprasBO::lerimportacaoPed();
		
		$params = $this->_getAllParams();
		$this->view->objErro = $params['res'];
		
		LogBO::cadastraLog("Compras/Entrada Estoque",1,$_SESSION['S_ID'],"","");
	}	
	
	public function entradaestoqncmnacAction(){
		$this->topoAction();
		$this->view->objProd		= ComprasBO::listaProdutosenttmpgroup();
		$this->view->objImp			= ComprasBO::lerimportacaoPed();
		$this->view->objNcm			= ComprasBO::lerimportacaoNcmpednac();
		
		
		$this->view->objTributos	= ComprasBO::buscaEmpresatmp();		
		
		LogBO::cadastraLog("Compras/Entrada Estoque",1,$_SESSION['S_ID'],"","");
	}
	
	public function importapednacAction(){
		$ret = ComprasBO::importacaoPedidonac($this->_getAllParams());
		$this->_redirect("/admin/compras/entradaestoqimpnac/res/".$ret);
	}
	
	public function entradaestoqncmAction(){
		$this->topoAction();
		$this->view->objNcm			= ComprasBO::listaProdutosenttmpgroupncm();
		$this->view->objCfop		= TributosBO::listaCfop();
		$this->view->objUf			= EstadosBO::buscaEstados(1);
		
		LogBO::cadastraLog("Compras/Entrada Estoque",1,$_SESSION['S_ID'],"","");
	}
		
	//--- Entrada Importacao ----------------------------------------------------------
	public function entradaestoqimportAction(){
		$this->topoAction();
		$usuario = Zend_Auth::getInstance()->getIdentity();
		//$this->view->objEnvoice 	= ComprasBO::listaEnvoice("");
		$params = $this->_getAllParams();
		
		$this->view->objProdutos		= ComprasBO::listaProdutosenttmpgroup();
		//$this->view->objProddet			= ComprasBO::listaProdutosenttmp();
		$this->view->objImp				= ComprasBO::lerimportacaoPed();
		$this->view->objVer				= $params['ver'];
		
		LogBO::cadastraLog("Compras/Entrada Estoque",1,$usuario->id,"","");
	}
	
	public function entradasimulacaoAction(){
		$this->topoAction();
		
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 62) as $list);
		
		if(($list->visualizar==1)){
		    
		    $this->view->objIns		= $list->inserir;
		    $this->view->objEdi		= $list->editar;
		    
			$params = $this->_getAllParams();
		
			Zend_Paginator::setDefaultScrollingStyle('Sliding');
			Zend_View_Helper_PaginationControl::setDefaultViewPartial('index/paginator.phtml');
			$paginator = Zend_Paginator::factory(ComprasBO::buscaEntradasimulacoes($params));
			$currentPage = $this->_getParam('page', 1);
			$paginator
				->setCurrentPageNumber($currentPage)
				->setItemCountPerPage(10);
			
			$this->view->objSimulacoes = $paginator;

			LogBO::cadastraLog("Estoque/Simulação de entrada",1,$usuario->id,"","");
		}else{
			$this->_redirect("/admin/compra/erro");
		};
		
		
		
		//LogBO::cadastraLog("Compras/Entrada Estoque",1,$_SESSION['S_ID'],"","");
	}

	public function entradasimulacaoprodAction(){
	    $this->topoAction();
	    $params = $this->_getAllParams();
	    
	    if($this->_request->isPost()):
	    	$idsimula = ComprasBO::gravaSimulacao($this->_getAllParams());
	    	$this->_redirect("/admin/compras/entradasimulacaoprod/simulacao/".md5($idsimula));
	    endif;
	    
	    if(!empty($params['simulacao'])){
			$this->view->objSimulacao = ComprasBO::buscaEntradasimulacoes($params);
	    }
	    
	    $this->view->etapa = $params['etapa'];
	    
	    if($params['etapa'] == 2){
	        $ret = ComprasBO::gravaSimulacaoadcao($params);
	        $this->view->objAdcoes = ComprasBO::buscaEntradasimulacoesadcoes($params);
	    }elseif($params['etapa'] == 3){
	        if(!empty($params['simulacao'])){
	        	$this->view->objProdutos  	= ComprasBO::buscaEntradasimulacoesprod($params);
	        	$this->view->objAdcoes 		= ComprasBO::buscaEntradasimulacoesadcoes($params);
	        }
	    }else{
	        if(!empty($params['simulacao'])){
	        	$this->view->objProdutos  = ComprasBO::buscaEntradasimulacoesprod($params);
	        } 
	    }
	    
	    $this->view->objErro = $ret;
	}
	
	public function gravasimulacaoadcaoAction(){
	    $this->_helper->layout->disableLayout();
	    $params = $this->_getAllParams();
	    $this->view->ret = ComprasBO::atualizaEntradasimulacaoadcoes($params);
	    $this->_redirect("/admin/compras/entradasimulacaoprod/simulacao/".$params['simulacao']."/etapa/3");
	    exit();	    
	}
	
	public function removesimulacaoAction(){
		$params = $this->_getAllParams();
		$this->view->ret = ComprasBO::removeSimulacao($params);
		$this->_redirect("/admin/compras/entradasimulacao");
	}
	
	public function buscaprodutosAction(){
	    $this->_helper->layout->disableLayout();
	    $params = $this->_getAllParams();
	    echo (count(ProdutosBO::buscaProdutoscodigo($params['codigo'])) > 0) ? 1 : 0;
	    exit();
	}
	
	public function importapedAction(){
		$erro = ComprasBO::importacaoPedido($this->_getAllParams());
		$this->_redirect("/admin/compras/entradaestoqimport/ver/".$erro);
	}
	
	/* public function importapedsimulacaoAction(){
	    $this->_helper->layout->disableLayout();
		$erro = ComprasBO::importacaoPedido($this->_getAllParams());
		$this->_redirect("/admin/compras/entradasimulacaoprod/ver/".$erro);
		//exit();
	}*/
	
	public function entradaestoqcadAction(){
		$this->topoAction();
		$usuario = Zend_Auth::getInstance()->getIdentity();
		
		$params = $this->_getAllParams();
		$erro = ComprasBO::gravaEntradatmp($params);
		$this->_redirect("/admin/compras/entradaestoq/qtver/".$params['qt']."/ret/".$erro);
		LogBO::cadastraLog("Compras/Entrada Estoque",2,$usuario->id,"","");
	}
	
	public function entradaestoqremAction(){
	    $usuario = Zend_Auth::getInstance()->getIdentity();
		ComprasBO::removeEntradatmp($this->_getAllParams());
		$this->_redirect("/admin/compras/entradaestoq");
		
		LogBO::cadastraLog("Compras/Entrada Estoque",3,$usuario->id,"","");
	}
	
	public function entradaestoqregAction(){
		$usuario = Zend_Auth::getInstance()->getIdentity();
		$this->_redirect("/admin/compras/entradaestoqncm");
		
		LogBO::cadastraLog("Compras/Entrada Estoque",1,$usuario->id,"","");
	}
	
	public function gerarentradaestoqueAction(){
	    $this->_helper->layout->disableLayout();
		$ident = ComprasBO::gerarEntradacompra($this->_getAllParams());
		ComprasBO::calculaAduaneiro($ident);
		$this->_redirect("/admin/compras/entradaprodordem/entrada/".md5($ident));
		exit();
	}
	
	public function removeentradaAction(){
	    $this->_helper->layout->disableLayout();
		ComprasBO::removeEntrada($this->_getAllParams());
		$this->_redirect("/admin/compras/entradaest");
	}
	
	
	
	public function gerarentradaestoquenacAction(){
	    $this->_helper->layout->disableLayout();
		$ident = ComprasBO::gerarEntradacompranac($this->_getAllParams());
		$this->_redirect("/admin/compras/entradaprodnac/entrada/".md5($ident));		
	}
	
	public function entradaestoqueprodAction(){
	    $usuario = Zend_Auth::getInstance()->getIdentity();
	    $params = $this->_getAllParams();
	    
	    ComprasBO::gravarEntradaestoque($params);
		$this->_redirect("/admin/compras/entradaprodnac/entrada/".$params['entrada']);
		
		LogBO::cadastraLog("Compras/Entrada Estoque",1,$usuario->id,"","");
			
	}
	
	public function entradaestoqueprodintAction(){
	    $usuario = Zend_Auth::getInstance()->getIdentity();
		$ident = ComprasBO::gravarEntradaestoque($this->_getAllParams());
		$this->_redirect("/admin/compras/entradaprod/entrada/".$ident);
		
		LogBO::cadastraLog("Compras/Entrada Estoque",1,$usuario->id,"","");
			
	}
	
	//--Lista entradas estoque-------
	public function entradaestAction(){
		$this->topoAction();

		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 62) as $list);
		
        $this->view->objVis		= $list->visualizar;
		$this->view->objIns		= $list->inserir;
		$this->view->objEdi		= $list->editar;
		$this->view->objNeg		= $list->saldo_neg;
			
        if($list->visualizar==1):
	        $params = $this->_getAllParams();
			
			Zend_Paginator::setDefaultScrollingStyle('Sliding');
			Zend_View_Helper_PaginationControl::setDefaultViewPartial('index/paginator.phtml');
			$paginator = Zend_Paginator::factory(ComprasBO::listaEntrada($params));
			$currentPage = $this->_getParam('page', 1);
			$paginator
			->setCurrentPageNumber($currentPage)
			->setItemCountPerPage(10);
			
			$this->view->objList 	= $paginator;
			$this->view->objPag 	= $params[page];
			$this->view->objFor		= ComprasBO::listaFornecedorescompra();
			
		else:		
			$this->_redirect("venda/erro");
        endif;
				
		LogBO::cadastraLog("Estoque/Entrada",1,$usuario->id,"","");
		
	}
	
	public function entradaestoqueAction(){
		$params = $this->_getAllParams();
		foreach (ClientesBO::listaEnderecosUp($params['fornecedor'],1) as $cliente);
		ComprasBO::registraEmpresaentrada($params);		
		
		if($cliente->PAIS == 1):
			$this->_redirect("/admin/compras/entradaestoq/fornecedor/".md5($params['fornecedor']));
		else:
			$this->_redirect("/admin/compras/entradaestoqimport");
		endif;
		
	}
	
	//--Lista produtos entrada estoque---------------------------------
	public function entradaprodAction(){
		$this->topoAction();
		
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 62) as $list);
		
        $this->view->objIns		= $list->inserir;
		$this->view->objEdi		= $list->editar;
			
        if($list->visualizar==1):
			$params = $this->_getAllParams();
			$this->view->objProdutos		= ComprasBO::listaProdutosentgroup($params);
			$this->view->objProddet			= ComprasBO::listaProdutosent($params);
			$this->view->objEntrada			= ComprasBO::listaEntrada($params);
			//$this->view->objProdncm			= ComprasBO::listaProdutosentncm($params);
			
			//$this->view->objCmv				= ComprasBO::listaProdutosentcmv($params);
			
			foreach (ComprasBO::listaEntrada($params) as $ped);
			LogBO::cadastraLog("Estoque/Entrada Produtos",1,$usuario->id,$ped->identrada,"ENTRADA E".substr("000000".$ped->identrada,-6,6));
		else:		
			$this->_redirect("venda/erro");
        endif;
        
	}

	//--Lista produtos entrada estoque para ordenacao pela adicao da DI ---------------------------------
	public function entradaprodordemAction(){
		$this->topoAction();
	
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 62) as $list);
		$this->view->objIns		= $list->inserir;
		$this->view->objEdi		= $list->editar;
			
		if($list->visualizar==1):
			$params = $this->_getAllParams();
			$this->view->objEntrada			= ComprasBO::listaEntrada($params);
			$this->view->objProdncm			= ComprasBO::listaProdutosentncm($params);				
			$this->view->objProddet			= ComprasBO::listaProdutosent($params);
			if(isset($params['adicao'])){
			    $this->view->objAdicao			= $params['adicao'];
			}
			
			if(!empty($params['adicao'])):
				$this->view->objProdutos		= ComprasBO::listaProdutosentgroup($params);				
			endif;
							
			foreach (ComprasBO::listaEntrada($params) as $ped);
			LogBO::cadastraLog("Estoque/Entrada Produtos",1,$usuario->id,$ped->identrada,"ENTRADA E".substr("000000".$ped->identrada,-6,6));
		else:
			$this->_redirect("venda/erro");
		endif;
	
	}
	
	public function gravaordemprodutosAction(){
		$params = $this->_getAllParams();
		$this->_helper->layout->disableLayout();
		ComprasBO::atualizaOrdemprodutos($params);
	}
			
	public function entradadadosajaxAction(){
	    
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
	
		if(!empty($params['gerarentrada'])):
			ComprasBO::gravarDadosnfe($params);
		elseif(!empty($params['baixarestoque'])):
			$var = array(
				'entrada'	=>	md5($params['baixarestoque']),
			    'nfe'		=>	$params['nfe']
			);			
			ComprasBO::gravarEntradaestoque($var);
		elseif(!empty($params['baixaentrada'])):
			$var = array(
				'entrada'	=>	md5($params['baixaentrada']),
				'nfe'		=>	$params['nfe']
			);
			ComprasBO::gravarEntradasemestoque($var);
		endif;
				
	}

	public function gravarestoqueAction(){
	    $params = $this->_getAllParams();
	    ComprasBO::gravarEntradaestoque($params);
	    $this->_redirect("/admin/compras/entradaprodutos/entrada/".$params['entrada']);
	}
	
	public function entradaprodutosAction(){
		$usuario = Zend_Auth::getInstance()->getIdentity();
	
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 62) as $list);
		
		if($list->visualizar==1):
			$this->topoAction();
			$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
		
			$params = $this->_getAllParams();
						
			if(empty($params['entradaid'])):
			
				$this->view->objEntrada			= ComprasBO::listaEntrada($params);
				foreach(ComprasBO::listaEntrada($params) as $entrada);
							
				if($entrada->id_nfe==""):
					$this->_redirect('/admin/compras/entradaprod/entrada/'.($params['entrada']));
				else:
					$this->view->objNfe		= NfeBO::buscaNfe($entrada->id_nfe);
					$this->view->objList 	= NfeBO::buscaProdutosnfe($entrada->id_nfe);
					$this->view->objFin		= FinanceiroBO::buscarFinanceironfe($entrada->id_nfe);
				endif;
			else:
				$this->_redirect('/admin/compras/entradaprodutos/entrada/'.md5($params['entradaid']));
			endif;
		else:
			$this->_redirect("/admin/compras/erro");
		endif;
	
		LogBO::cadastraLog("Compras/Entradas",1,$usuario->id,$ped->id,"Entrada ".$ped->id);
	
	}
	
	
	
	
	
	
	public function entradaprodnacAction(){
		$this->topoAction();
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 62) as $list);
		
        $this->view->objVis		= $list->visualizar;
		$this->view->objIns		= $list->inserir;
		$this->view->objEdi		= $list->editar;
			
        if($list->visualizar==1):
			$params = $this->_getAllParams();
			$this->view->objProdutos		= ComprasBO::listaProdutosentgroup($params);
			$this->view->objProddet			= ComprasBO::listaProdutosent($params);
			$this->view->objEntrada			= ComprasBO::listaEntrada($params);
			$this->view->objCmv				= ComprasBO::listaProdutosentcmv($params);
		
			foreach (ComprasBO::listaEntrada($params) as $ped);
			LogBO::cadastraLog("Estoque/Entrada Produtos",1,$usuario->id,$ped->identrada,"ENTRADA E".substr("000000".$ped->identrada,-6,6));
		else:		
			$this->_redirect("venda/erro");
        endif;
		
	}
	
	public function entradaprodcmvAction(){
		$this->topoAction();
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 62) as $list);
		
        $this->view->objIns		= $list->inserir;
		$this->view->objEdi		= $list->editar;
			
	    if($list->visualizar==1):
			$params = $this->_getAllParams();
			if($this->_request->isPost()):
				ComprasBO::geraCmvestoque($params);				
			endif;
			
			//ComprasBO::gerarCmventradaprod($params);
			$this->view->objProdutos		= ComprasBO::listaProdutosentcmv($params);
			$this->view->objEntrada			= ComprasBO::listaEntrada($params);
			
			$this->view->objProdutosgroup	= ComprasBO::listaProdutosentgroup($params);
			$this->view->objProddet			= ComprasBO::listaProdutosent($params);

			
			///$this->view->objProdutos		= ComprasBO::listaProdutosentgroup($params);
			
			
			$this->view->objTipo			= $params['tipo'];
			foreach (ComprasBO::listaEntrada($params) as $ped);
			LogBO::cadastraLog("Estoque/CMV Produtos",1,$usuario->id,$ped->identrada,"ENTRADA E".substr("000000".$ped->identrada,-6,6));
		else:		
			$this->_redirect("venda/erro");
        endif;
		
	}
	
	//--Lista produtos entrada estoque para impressao-------
	public function entradaprodimpAction(){
		$this->_helper->layout->disableLayout();
		$this->topoAction();
		$params = $this->_getAllParams();
		
		/*foreach(ComprasBO::listaEntrada($var) as $lista):
			if(md5($lista->id)==$params[entrada]):
				$id = $lista->id;
			endif;
		endforeach;*/
		
		$this->view->objList = ComprasBO::listaProdutosentgroup($params);
		$this->view->objEnt	 = ComprasBO::listaEntrada($params);
		
	}
	
	
	//---Erro por falta de acesso-----------------------------------
	public function erroAction(){
		$this->topoAction();				
	}

	//----Fabricação de Kits ------------------------------------------------
	//-- Lista produção de kit -------
	public function fabricaAction(){
		$this->topoAction();

		$params = $this->_getAllParams();
		
		Zend_Paginator::setDefaultScrollingStyle('Sliding');
		Zend_View_Helper_PaginationControl::setDefaultViewPartial('index/paginator.phtml');
		$paginator = Zend_Paginator::factory(ComprasBO::listaFabrica($params));
		$currentPage = $this->_getParam('page', 1);
		$paginator
			->setCurrentPageNumber($currentPage)
			->setItemCountPerPage(10);
		
		$this->view->objList = $paginator;
		$this->view->objPag = $params[page];
		
		$usuario = Zend_Auth::getInstance()->getIdentity();
        
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 62) as $list);
		
		$this->view->objIns		= $list->inserir;
		$this->view->objEdi		= $list->editar;
		$this->view->objNeg		= $list->saldo_neg;
				
		LogBO::cadastraLog("Estoque/Montagem",1,$usuario->id,"","");
		
	}
	
	//--Nova montagem estoque-------
	public function fabricanovoAction(){
		$this->topoAction();
		
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 62) as $list);
		$this->view->objNeg		= $list->saldo_neg;
		
	}
	
	public function gerakitAction(){
		ComprasBO::gravaFabricakit($this->_getAllParams());
		$this->_redirect('/admin/compras/fabrica');						
	}
	
	//--Lista produtos fabrica-------
	public function fabricaprodAction(){
		$this->topoAction();
		$params = $this->_getAllParams();
		
		$this->view->objList = ComprasBO::listaProdutosfabrica($params);
		$this->view->objAjus = ComprasBO::listaFabrica($params);
		
		foreach (ComprasBO::listaFabrica($params) as $fab);
		
		$usuario = Zend_Auth::getInstance()->getIdentity();
		LogBO::cadastraLog("Estoque/Fabrica",1,$usuario->id,$fab->idfabrica,"FABRICAÇÃO F".substr("000000".$fab->idfabrica,-6,6));
		
	}
	
	//--Lista produtos fabrica impressao-------
	public function fabricaprodimpAction(){
		$this->_helper->layout->disableLayout();
		$this->topoAction();
		$params = $this->_getAllParams();
		
		foreach(ComprasBO::listaFabrica("") as $lista):
			if(md5($lista->id)==$params[fab]):
				$id = $lista->id;
			endif;
		endforeach;
		
		$this->view->objList = ComprasBO::listaProdutosfabrica($id);
		$this->view->objAjus = ComprasBO::buscaFabrica($id);
		
		foreach (ComprasBO::buscaFabrica($id) as $lisObs);
		$busca['idparceiro']	= $lisObs->id_user;
		$this->view->objClie 	= ClientesBO::buscaParceiros("",$busca);
	}
	
	//--Compras ZTL-------------------------
	public function relatoriovendasAction(){
		date_default_timezone_set('America/Sao_Paulo');
		$this->topoAction();
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 13) as $list);
        
        if(($list->visualizar==1)):
         	$this->view->objVis		= $list->visualizar;
			$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;       
			
			$params	=	$this->_getAllParams();
			
			if($this->_request->isPost()):
				$this->view->objList 	= ProdutosBO::buscaProdrelatoriovenda($params);
				$this->view->objQtmes	= $params['periodo'];
				$this->view->objGselec	= $params['buscagrupo'];
				$this->view->objFsel	= $params['buscafor'];
				$this->view->objGvend	= $params['grupovenda'];
				$this->view->objSgrupo	= $params['buscagruposub'];
				$this->view->objOrd		= $params['ord'];
				
				$this->view->objGruposub = GruposprodBO::listaGruposprodutossub($params['grupovenda']);
			elseif($params['sug']=='compra'):
				$params['periodo']		= 6;
				$params['ord'] 			= 1;
				$this->view->objList 	= ProdutosBO::buscaProdrelatoriovenda($params);
				$this->view->objQtmes	= 6;
				$this->view->objSug		= '1';
			endif;
			
			$this->view->objFor		= ClientesBO::buscaParceiros("fornecedor");
			$this->view->objGrupo	= GruposprodBO::listaGruposcompra();
			$this->view->objGrupov 	= GruposprodBO::listaGruposprodutos();
			$this->view->objVer		= $params['ver'];			
		else:		
			$this->_redirect("venda/erro");
        endif;
        
	}

	public function relatoriovendasforAction(){
		date_default_timezone_set('America/Sao_Paulo');
		$this->topoAction();
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 13) as $list);
        
        if(($list->visualizar==1)):
         	$this->view->objProdutos	= ComprasBO::buscaProdutosfornecedor($this->_getAllParams());
         	$this->view->objDados		= $this->_getAllParams();	
         	$this->view->objFor			= ComprasBO::buscafornecedorprodutos($this->_getAllParams());	
		else:		
			$this->_redirect("/admin/compras/erro");
        endif;
	}
	
	
	
	public function buscagruposfornecedorAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		$this->view->objGrupo 	= GruposprodBO::listaGruposfornecedor($params['idfor']);
	}
	
	public function gerarpedidoAction(){
		$params = $this->_getAllParams();
		$id = ComprasBO::gerarPedido($params);
		$this->_redirect("/admin/compras/pedidoscompra");
	}
	
	public function pedidoscompraAction(){
		date_default_timezone_set('America/Sao_Paulo');
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 23) as $list);
		
        $this->view->objVis		= $list->visualizar;
		$this->view->objIns		= $list->inserir;
		$this->view->objEdi		= $list->editar;			
        
        if($list->visualizar==1):
        	$this->topoAction();
			Zend_Paginator::setDefaultScrollingStyle('Sliding');
			Zend_View_Helper_PaginationControl::setDefaultViewPartial('index/paginator.phtml');
			$paginator = Zend_Paginator::factory(ComprasBO::listaPedidos($this->_getAllParams()));
			$currentPage = $this->_getParam('page', 1);
			$paginator
			->setCurrentPageNumber($currentPage)
			->setItemCountPerPage(10);
			
			$this->view->objList = $paginator;
			$this->view->objCli		= ClientesBO::buscaParceiros("fornecedor");			
			
		else:		
			$this->_redirect("/admin/compras/erro");
        endif;
        		
		LogBO::cadastraLog("Compras/Pedidos",1,$usuario->id,"","");
		
	}
	
	public function pedidoscompraprodAction(){
		date_default_timezone_set('America/Sao_Paulo');
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 23) as $list);
		
        $this->view->objVis		= $list->visualizar;
		$this->view->objIns		= $list->inserir;
		$this->view->objEdi		= $list->editar;
			
        if($list->visualizar==1):
        	$params	=	$this->_getAllParams();
        
        	$this->topoAction();
			$this->view->objList 	= ComprasBO::buscaProdutospedidos($params['ped']);
			$this->view->objPed		= ComprasBO::buscaPedidos($params['ped']);
			$this->view->objEnt		= ComprasBO::listaEntregaped($params);
			//$this->view->objFin		= FinanceiroBO::buscaContaspedidocompra($params['ped']);
			
		else:		
			$this->_redirect("venda/erro");
        endif;
	}
	
	public function removepedcompraAction(){
		$params = $this->_getAllParams();
		ComprasBO::cancelarPedidocompra($params['ped']);
		$this->_redirect("/admin/compras/pedidoscompra");
	}
	
	public function gerarxmlcompraAction(){
		$this->_helper->layout()->disableLayout();
		$params = $this->_getAllParams();
		$this->view->objDom = ComprasBO::gerarXmlcompra($params['ped']);		
	}
	
	//--- Financeiro Pedidos ---------------------
	public function adicionapagpedcompraAction(){
		$params	=	$this->_getAllParams();
		FinanceiroBO::gravaContapedidocompra($params);
		$this->_redirect("/admin/compras/pedidoscompraprod/ped/".md5($params['idped']));
	}
	
	
	
	//--Remove entrega produtos ------------------
	public function remprodentcompraAction(){
		date_default_timezone_set('America/Sao_Paulo');
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 18) as $list);
			
        if($list->visualizar==1):
        	$params	=	$this->_getAllParams();
        	ComprasBO::removeEntrega($params);        	
			$this->_redirect("/admin/compras/pedidoscompraprod/ped/".md5($params['ped']));
		else:		
			$this->_redirect("/admin/compras/erro");
        endif;
	}
	
	public function fecharpedidocompraAction(){
		$this->_helper->layout()->disableLayout();
		date_default_timezone_set('America/Sao_Paulo');
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 18) as $list);
			
        if($list->visualizar==1):
        	$params	=	$this->_getAllParams();
        	ComprasBO::fechaPedidocompra($params['ped']);        	
			$this->_redirect("/admin/compras/pedidoscompraprod/ped/".$params['ped']);
		else:		
			$this->_redirect("/admin/compras/erro");
        endif;
	}
		
	public function gerarrelatorioAction(){
		$this->_helper->layout()->disableLayout();
		//VendaBO::corrigeRelatorio();
		VendaBO::atualizaRelatorio();
		//VendaBO::enviaMail();
	}	
	
	public function corrigeAction(){
		$this->_helper->layout()->disableLayout();
		//ComprasBO::corrigeEntrada();
		ComprasBO::calculaAduaneiro();		
		
	}
	
	public function corrigecmventradaAction(){
		$this->_helper->layout()->disableLayout();
		$this->view->objList	= ComprasBO::listaCmvprod();
	}
	
	public function buscacustoporncmAction(){
	    $this->_helper->layout()->disableLayout();
	    $params	=	$this->_getAllParams();
	    ComprasBO::calculaImpostosentrada($params['idncm'],$params['valor']);
	    exit();
	}
	
	
	
}

