<?php

class Admin_PublicoController extends Zend_Controller_Action {
	
	public function init(){		
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
	
	
	
	public function executaajusteprecosAction(){
		$this->_helper->layout->disableLayout();
	}
	
	public function indexAction(){
		$this->_helper->layout->disableLayout();
		
		$this->view->objTennis	= TennisBO::listarJogadores();
		$this->view->objFila	= TennisBO::listarFila();	
		$this->view->objPart	= TennisBO::exibePartida("");
		$this->view->objHist	= TennisBO::contaPartidas();
	}
	
	public function buscainfoAction(){
		$this->_helper->layout->disableLayout();
		TennisBO::atualizaFila($this->_getAllParams());
		$this->view->objFila	= TennisBO::listarFila();		
	}
	
	public function buscajogAction(){
		$this->_helper->layout->disableLayout();
		$this->view->objTennis	= TennisBO::listarJogadores();		
		$this->view->objHist	= TennisBO::contaPartidas();
	}
	
	public function buscapartidaAction(){
		$this->_helper->layout->disableLayout();
		$this->view->objPart	= TennisBO::exibePartida($this->_getAllParams());
	}
	
	public function consultaAction(){
		$this->_helper->layout->disableLayout();
		$this->view->objTennis	= TennisBO::listarJogadores();
		if($this->_request->isPost()):
			$this->view->objHis	= TennisBO::buscarPartidas($this->_getAllParams());	
		endif;
		
	}
	
	public function rankAction(){
		$this->_helper->layout->disableLayout();
		$this->view->objTennis	= TennisBO::listarJogadoresmediapontos();
		$this->view->objHist	= TennisBO::contaPartidasger();
		$this->view->objQtdis	= TennisBO::listarJogadoresqtdisputas();
		
	}
	
	public function catalogoAction(){
		$this->_helper->layout->disableLayout();
		$this->view->objMontadora	= ProdutosBO::listaMontadoras();
		$this->view->objGrupo		= GruposprodBO::listaGruposprodutos();
		
		if($this->_request->isPost()):
			$this->view->objProdgrup	= ProdutosBO::buscaGruposcatalogo($this->_getAllParams());
			$this->view->objProdutos	= ProdutosBO::buscaProdutoscatalogo($this->_getAllParams());
			$this->view->objProdveic	= ProdutosBO::buscaProdutosveiculoscatalogo($this->_getAllParams());
			
		endif;
		
	}
	
	public function buscasubgrupoAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		$this->view->objGrupsub	= GruposprodBO::listaGruposprodutossub($params['id_grupo']);		
	}
	
	
	public function testecronAction(){
		$this->_helper->layout->disableLayout();
		VendaBO::enviaMail();
	}
	
	
	//--- validos -----------------------------------------------------------
	//--- Add no crontrab do servidor p executar todo dia 01 ----------------------------------
	public function atualizarelcomprasAction(){
		$this->_helper->layout->disableLayout();
		VendaBO::atualizaRelatorio();
		exit();
	}
	
	public function gerarpalletAction(){
		$this->_helper->layout->disableLayout();
	
		$params = $this->_getAllParams();

		$this->view->translate	=	Zend_Registry::get('translate');
		
		if(!empty($params['fornmd'])):
			$forn =  $params['fornmd'];
			foreach (KangvendasBO::listaCominvoicefornecedores($params['ped']) as $fornecedores):
				if(md5($fornecedores->id_for) == $forn):
					$fornid	= $fornecedores->id_for;
				endif;
			endforeach;		
		elseif($params['forn']):
			$forn 	= md5($params['forn']);
			$fornid = $params['forn'];
		endif;
		
		//--Verifica acesso ---------------
		if(count(KangvendasBO::verificaAcessopallet($params['ped'],$forn)) > 0):
			$this->view->objForsel	= $fornid;			
			$this->view->objDet		= KangvendasBO::buscaCominvoice($params['ped']);
			$this->view->objProd	= KangvendasBO::listaCominvoiceprod($params['ped'],$forn);
			$this->view->objProdpk	= KangvendasBO::buscaProdutosinvoicecompra($params['ped']);
			$this->view->objFor		= KangvendasBO::listaCominvoicefornecedores($params['ped']);
			
			$this->view->objPack	= KangvendasBO::listarPacklistcad($params,$forn);
			$this->view->objProdpl	= KangvendasBO::listarPacklistprod($params);
			
			LogBO::cadastraLog("Kang/Gerar PL Fornecedor",1,$fornid,"","");
			
		endif;		
	}
	
	public function gerarpacklistforAction(){
		$params = $this->_getAllParams();
		KangvendasBO::gerarPackinglist($params);
		$this->_redirect("/admin/publico/gerarpallet/ped/".md5($params['ped'])."/fornmd/".md5($params['idfor']));
	}
	
	public function removerpacklistforAction(){
		$params = $this->_getAllParams();
		KangvendasBO::cancelarPackinglist($params);
		$this->_redirect("/admin/publico/gerarpallet/ped/".$params['ped']."/fornmd/".md5($params['idfor']));
	}
	
	public function packcopiaforAction(){
		$params = $this->_getAllParams();
		KangvendasBO::copiaPacklist($params);
		$this->_redirect("/admin/publico/gerarpallet/ped/".$params['ped']."/fornmd/".md5($params['idfor']));
	}
	
}