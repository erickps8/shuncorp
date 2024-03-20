<?php
class Admin_AdministracaoController extends Zend_Controller_Action {
		
	public function init()
	{
        if ( !Zend_Auth::getInstance()->hasIdentity() ) {
        	$this->_redirect('/');
        }
	}
	
	public function topoAction($ativo=''){

	    date_default_timezone_set('America/Sao_Paulo');
	    
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
	
	public function buscaidiomaAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		
		$sessao = new Zend_Session_Namespace('Idiomas');
	    $sessao->idioma = $params['idioma'];	    
	}
	
	public function buscainfoAction(){
		$this->_helper->layout->disableLayout();
		date_default_timezone_set('America/Sao_Paulo');
		$this->view->objAtiv = AtividadesBO::informaAtividades();		
	}
		
	//---Lista as pefils--------------
	public function perfilsAction(){
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 66) as $list);

        if($list->visualizar==1):
			$this->topoAction('admin');
			$this->view->objPerfil = PerfilBO::listarPerfil();
			LogBO::cadastraLog("ADM/Perfil",1,$usuario->id,'','','');
		else:		
			$this->_redirect("/admin/administracao/erro");
        endif;
	}
	
	//----Cadastra as perfil----------------------	
	public function perfilcadAction(){		
		$this->topoAction('admin');
		$bo = new MenuBO();
		$this->view->objPerfilmenu 	= $bo->listarMenu("");
		$this->view->objPerfilsubmenu = $bo->listarSubmenuperfil();
		
		$params = $this->_getAllParams();		
		$this->view->objPerfil		= PerfilBO::buscaPerfil($params);
		
		
		if(!empty($params['id'])){
			$bop = new PerfilBO();
			foreach ($bop->listarPerfil("") as $list){
				if(md5($list->id) == $params[id]){
					$id_perfil 		= $list->id;
					$nome_perfil 	= $list->descricao;
					$sit			= $list->sit;
					$nivel			= $list->nivel;
				}
			}
			
			$this->view->objAcess	= $bop->listarPerfilacesso($id_perfil);
		}
		
		LogBO::cadastraLog("ADM/Perfil",1,$usuario->id,$id_perfil,'PERFIL ID '.$id_perfil,'');
	}
	
	public function gravapefilAction(){
				
		$bo = new PerfilBO();
		$id = $bo->cadastraPerfil($this->_getAllParams());
	
		$this->_redirect("/admin/administracao/perfilcad/id/".md5($id));
		
	}
	
	//------------------------------------------------------------------------
	//---log----
	
	public function logacessoAction(){
		$this->topoAction('admin');
		$sessaobusca = new Zend_Session_Namespace('Log');
		$usuario = Zend_Auth::getInstance()->getIdentity();
		
		//--- Controle de perfil ------------------------------------------
		foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $listnivel);
		$this->view->objNivel	= $listnivel->nivel;
		
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 37) as $list);

        if($list->visualizar==1):
        	if($this->_request->isPost()):
        		$sessaobusca->where = "";
        	
        	endif;
        
        	$params = $this->_getAllParams();
			
			Zend_Paginator::setDefaultScrollingStyle('Sliding');
			Zend_View_Helper_PaginationControl::setDefaultViewPartial('index/paginator.phtml');
			$paginator = Zend_Paginator::factory(LogBO::listaLog($params));
			$currentPage = $this->_getParam('page', 1);
			$paginator
			->setCurrentPageNumber($currentPage)
			->setItemCountPerPage(15);
			
			$this->view->objList = $paginator;
		else:		
			$this->_redirect("/admin/administracao/erro");
        endif;
	}
	
	public function logacessoentAction(){
		$this->topoAction('admin');
		$this->view->translate	=	Zend_Registry::get('translate');
		$usuario = Zend_Auth::getInstance()->getIdentity();
        
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 65) as $list);
		if($list->visualizar==1):
        	$params = $this->_getAllParams();
        	$bo = new LogBO();			
			Zend_Paginator::setDefaultScrollingStyle('Sliding');
			Zend_View_Helper_PaginationControl::setDefaultViewPartial('index/paginator.phtml');
			$paginator = Zend_Paginator::factory($bo->listaLogin($params));
			$currentPage = $this->_getParam('page', 1);
			$paginator
				->setCurrentPageNumber($currentPage)
				->setItemCountPerPage(15);
			
			$this->view->objList = $paginator;
		else:		
			$this->_redirect("/admin/administracao/erro");
        endif;
	}
	
	//--Relatorio de pendencias-------------------------
	public function relpendenciasAction(){
		$this->topoAction('admin');
		$this->view->objRegioes = RegioesBO::listaRegioes();
		$this->view->objEmpresas = ClientesBO::buscaParceiros("clientes");
		$params = $this->_getAllParams();
		if($params['pesquisar']): 
			$this->view->objRel = PendenciasBO::listarPendencias($params);
			$this->view->objVen = PendenciasBO::listarVendasvalor($params);
		endif;
		
		if(!empty($params['pesq_emp'])){ 
			$this->view->objTipo = 1;
			$this->view->objValor = $params['pesq_emp'];
		}elseif(!empty($params['pesq_reg'])){ 
			$this->view->objTipo = 2;
			$this->view->objValor = $params['pesq_reg'];
		}elseif(!empty($params['cod_prod'])){ 
			$this->view->objTipo = 3;
			$this->view->objValor = $params['cod_prod'];
		}		
	}
			
	//-------------Financeiro ZTL----------------------------------------------------------------
	//---Listar Plano de contas--------------------
	public function planocontasAction(){
		$this->topoAction('admin');
		$this->view->objList = FinanceiroBO::listaPc();
				
		LogBO::cadastraLog("ADM/Financeiro",1,$usuario->id,'','','');
		
	}
	//---Grava Plano de contas--------------------
	public function gravapcAction(){
		FinanceiroBO::gravaPlanocontas($this->_getAllParams());
		$this->_redirect("/admin/administracao/planocontas");
	}
	
	//----Remove PC----------------------	
	public function removepcAction(){
		$params = $this->_getAllParams();
		foreach (FinanceiroBO::listaPc() as $list){
			if(md5($list->id) == $params['rem']){  
				$id_pc = $list->id;
			}
		}
		
		FinanceiroBO::removePc($id_pc);
		$this->_redirect("/admin/administracao/planocontas");
		
	}
	//---Listar contas--------------------
	public function financeirocontasAction(){
		$this->topoAction('admin');
		$this->view->objList = FinanceiroBO::listaContas();
				
		LogBO::cadastraLog("ADM/Contas bancárias",1,$usuario->id,'','','');
		
	}
	
	//---Grava contas--------------------
	public function gravacontaAction(){
		FinanceiroBO::gravaContas($this->_getAllParams());
		$this->_redirect("/admin/administracao/financeirocontas");
	}
	
	//----Remove Contas----------------------	
	public function removecontaAction(){
		$params = $this->_getAllParams();
		foreach (FinanceiroBO::listaContas() as $list){
			if(md5($list->id) == $params['rem']){  
				$id_pc = $list->id;
				$conta = $list->nome;
			}
		}
		
		FinanceiroBO::removeConta($id_pc,$conta);
		$this->_redirect("/admin/administracao/financeirocontas");
		
	}
	
	//--Contas a pagar-----------------------------------------
	//---Listar contas--------------------
	public function financeiropagarAction(){
		$this->topoAction('admin');

		/*$params = $this->_getAllParams();
		
		Zend_Paginator::setDefaultScrollingStyle('Sliding');
		Zend_View_Helper_PaginationControl::setDefaultViewPartial('shuntaicompras/paginator.phtml');
		$paginator = Zend_Paginator::factory(TaicomprasBO::listaPedidos());
		$currentPage = $this->_getParam('page', 1);
		$paginator
		->setCurrentPageNumber($currentPage)
		->setItemCountPerPage(35);
				
		$this->view->objList = $paginator;
		$this->view->objPag = $params[page];
		
		LogBO::cadastraLog("Shuntai Compras/Pedidos",1,$usuario->id,"","");*/
		
	}
	
	//---Nova contas--------------------
	public function financeironpagarAction(){
		$this->topoAction('admin');
	}
	
	//---Imprime validate transation-----------------------------------
	public function financevalidateimpAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		$this->view->objList	= FinanceirochinaBO::listaValidate($params);
		$this->view->objTipo = $params[idc];
	}

	public function salvarfinpurchaseAction(){
		//$this->_redirect("http://localhost/homologacao/acessoRestrito/financeiro.php");
	}
	
	public function finbuscacontasAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		if($params['q']==1){ 
			$this->view->objCompras 	= KangcomprasBO::listaComprasabertas();
			$this->view->objTipo = 1;
		}elseif($params['q']==2){ 
			$this->view->objCompras		= TaicomprasBO::listaComprasabertas();
			$this->view->objTipo = 2;
		}
	}
	
	
	public function buscaclientesportipoAction(){
	    $this->_helper->layout->disableLayout();
	    $params = $this->_getAllParams();
	    if(count(ClientesBO::listaemailsAllclientes($params))>0):
	    	?>
<table>
			<?php 
	    	foreach (ClientesBO::listaemailsAllclientes($params) as $emaills):
	    		?>
	    		<tr>
		<td><?=$emaills->NOME_CONTATO?></td>
		<td><?=$emaills->EMAIL?></td>
	</tr>
		    	<?php 
	    	endforeach;
	    	?>
			</table>
<?php 
	   	endif;
	   	exit();
	}
	
	
		
	//---Erro por falta de acesso-----------------------------------
	public function erroAction(){
		$this->topoAction('admin');
	}
	
	//---Cameras-----------------------------------
	public function camerasAction(){
		$this->topoAction('admin');
	}
	
	//---Financeiro china - contas a pagar -----------------------------------
	
	public function liberachinapagAction(){
		$params = $this->_getAllParams();
		FinanceirochinaBO::liberarContaspag($params['pag']);
		$this->_redirect("/admin/administracao/financeirochinapagcad/pay/".$params['pag']);
	}
	
	public function liberachinarecAction(){
		$params = $this->_getAllParams();
		FinanceirochinaBO::liberarContasrec($params['rec']);
		$this->_redirect("/admin/administracao/financeirochinareccad/rec/".$params['rec']);
	}
	
	//-- Financeiro Chines ----------------------------------------------------
	public function financeirochinaAction(){
		$this->topoAction('admin');
		$this->view->translate	=	Zend_Registry::get('translate');
	
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 55) as $list);
		
		if($list->visualizar==1):
		 
			//-- Contas a pagar ------------------------
			$this->view->objContasvencpag		= FinanceirochinaBO::listaContaspagar(2);
			$this->view->objContasvencsempag	= FinanceirochinaBO::listaContaspagar(7);
			$this->view->objContasvenchojepag	= FinanceirochinaBO::listaContaspagar(1);
			$this->view->objContasvencidaspag	= FinanceirochinaBO::listaContaspagar(-1);
			 
			//-- Contas a receber ------------------------			
			$this->view->objContasvencrec		= FinanceirochinaBO::listaContasreceber(2);
			$this->view->objContasvencsemrec	= FinanceirochinaBO::listaContasreceber(7);
			$this->view->objContasvenchojerec	= FinanceirochinaBO::listaContasreceber(1);
			$this->view->objContasvencidasrec	= FinanceirochinaBO::listaContasreceber(-1); 
		else:
			$this->_redirect("/admin/administracao/erro");
	    endif;
	}
	
	public function financeirochinapagAction(){
		$this->topoAction('admin');
		$this->view->translate	=	Zend_Registry::get('translate');
			
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 55) as $list);

        if($list->visualizar==1):
        	$params = $this->_getAllParams();
			
        	$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
			$this->view->objLib		= $list->aba1;
			$this->view->objAdm		= $list->aba2;
        	
			$this->view->objFornec			= ClientesBO::buscaParceiros("fornecedorchines");
			$this->view->objPlcontas		= FinanceirochinaBO::listarPlanosconta(); 
			
			$perfil['perfil'] = '28,13,14,15';
			$this->view->objUsuarios		= UsuarioBO::buscaUsuario($perfil);
			
			if($params['tipo']==1):
				if(!empty($params['buscaid'])):
					$link = "/tipo/1/buscaid/".$params['buscaid'];
				endif;
			elseif ($params['tipo']==2):
				if($params['buscafor']!=0):
					$link = "/tipo/2/buscafor/".$params['buscafor'];
				endif;
			elseif ($params['tipo']==3):
				if(!empty($params['buscavalor'])):
					$link = "/tipo/3/buscavalor/".$params['buscavalor'];
				endif;
			endif;
			
			if(!empty($params[dtini])):
				$link .= "/dtini/".$params[dtini];
			endif;
			
			if(!empty($params[dtfim])):
				$link .= "/dtfim/".$params[dtfim];
			endif;
			$this->view->objLink 	= $link;		
			
			$this->view->objContaspagar 	= FinanceirochinaBO::listaContaspagar($params);
			
		else:		
			$this->_redirect("/admin/administracao/erro");
        endif;
        		
		LogBO::cadastraLog("ADM/Financeiro chines - Pagamentos",1,$usuario->id,"","",'');
	}	
	
	public function financeirochinapagcadAction(){
		$this->topoAction('admin');
		$this->view->translate	=	Zend_Registry::get('translate');
			
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 55) as $list);

        if($list->visualizar==1):
        	$params = $this->_getAllParams();
			
        	$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
			$this->view->objLib		= $list->aba1;
        				
			$this->view->objPlanocontas		= FinanceirochinaBO::listarPlanosconta();
			$this->view->objFornec			= ClientesBO::buscaParceiros("fornecedorchines");
							
			$perfil['perfil'] = '28,13,14,15';
			$this->view->objUsuarios		= UsuarioBO::buscaUsuario($perfil);
			
			if(!empty($params['pay'])):
				$this->view->objPag	   		= FinanceirochinaBO::buscarContapag($params['pay']);
				$this->view->objAnepag		= FinanceirochinaBO::listarAnexosapagar($params['pay']);
				$this->view->objPurc		= FinanceirochinaBO::listarPurchasepagar($params['pay']);
				$this->view->objContasbanc	= FinanceirochinaBO::listarContasbancarias();
				
				$bov = new KangvendasModel();
	            $bo  = new KanginvoiceModel();
				
	            $this->view->objInvoice     = $bo->fetchAll('md5(id_fin_contasapagar) = "'.$params['pay'].'"');
	            $this->view->objInvFrete    = $bo->fetchAll('md5(id_fretepag) = "'.$params['pay'].'"');

				foreach (FinanceirochinaBO::buscarContapag($params['pay']) as $lista);								
				LogBO::cadastraLog("ADM/Fin chines/Pagamentos",1,$usuario->id,$lista->id,"P".substr("000000".$lista->id,-6,6),'');
				
			endif;			
			
		else:		
			$this->_redirect("/admin/administracao/erro");
        endif;        
	}
	
	public function gravafinanceiropagAction(){
	    $this->_helper->layout->disableLayout();
	     
	    try{
	        $bo    = new FinanceirochinavalidateModel();
	        $bof   = new FinanceirochinaModel();
	        $bofp  = new FinanceirochinapagarModel();
	        
	        $params = $this->_getAllParams();
	        $financeiroChina = new FinanceirochinapagBO();
	        	
	        $id = $financeiroChina->gravarContaspag($params);
	        $financeiroChina->gravarPurchase($params, $id);
	        $financeiroChina->gravarArquivos($params, $id);
	        $financeiroChina->gravaRetorno($params, $id);
	        $financeiroChina->gravaFrete($params, $id);

	        echo md5($id)."|";
	        
	        if($params['bancopagamentopag'] == 11 && $params['baixarpag'] == 1){
	            $arrayConc = array(
                    'data'          => substr($params['datapagamentopag'],6,4).'-'.substr($params['datapagamentopag'],3,2).'-'.substr($params['datapagamentopag'],0,2),
                    'valor'         => str_replace(',','.',str_replace('.','',$params['valorpagamentopag'])) * (-1),
                    'situacao'      => 1,
                    'valida'        => 0,
                    's_conta'       => $params['bancopagamentopag'],
	            );
	            
	            $idconc = $bo->insert($arrayConc);
	            
	            $bofp->update(array('st_conc' => $idconc), 'id = "'.$id.'"');
	        }
	        
	        echo md5($id)."|";
	        
	        	
	    }catch (Exception $e){
	        echo "erro|".$e->getMessage();
	    }
	    exit();
	}
	
	public function corrigeconcAction(){
	    $this->_helper->layout->disableLayout();
	    
	    $bo    = new FinanceirochinavalidateModel();
	    $bof   = new FinanceirochinaModel();
	    $bofp  = new FinanceirochinapagarModel();
	    $bofr  = new FinanceirochinareceberModel();
	    
	    foreach ($bofp->fetchAll("id_mod_pagamento = 11 and baixa = 1 and (st_conc is null || st_conc = '')") as $contasp){
	       echo $contasp->id;
	       
	       $arrayConc = array(
	           'data'          => $contasp->dt_pagamento,
	           'valor'         => $contasp->valor_pago * (-1),
               'situacao'      => 1,
               'valida'        => 1,
               's_conta'       => 11,
	       );
	       
	       echo " - ";
	       echo $idconc = $bo->insert($arrayConc);
	       
	       $bofp->update(array('st_conc' => $idconc), 'id = "'.$contasp->id.'"');
	       
	       echo "<br>";
	        
	    }
	    
	    foreach ($bofr->fetchAll("id_mod_pagamento = 11 and baixa = 1 and (st_conc is null || st_conc = '')") as $contasr){
	        echo $contasr->id;
	        
	        $arrayConc = array(
                'data'          => $contasr->dt_pagamento,
                'valor'         => $contasr->valor_pago,
                'situacao'      => 1,
                'valida'        => 1,
                's_conta'       => 11,
	        );
	        
	        echo " - ";
	        echo $idconc = $bo->insert($arrayConc);
	        
	        $bofr->update(array('st_conc' => $idconc), 'id = "'.$contasr->id.'"');
	        
	        echo "<br>";
	        
	    }
	    
	    exit();
	}
	
	
	//---Financeiro china - contas a receber -----------------------------------
	public function financeirochinarecAction(){
		$this->topoAction('admin');
		$this->view->translate	=	Zend_Registry::get('translate');
			
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 55) as $list);

        if($list->visualizar==1):
        	$params = $this->_getAllParams();
			
        	$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
			$this->view->objLib		= $list->aba1;
			$this->view->objAdm		= $list->aba2;
        	
			$this->view->objFornec			= ClientesBO::buscaParceiros("clienteschines","","A");
			$this->view->objPlcontas		= FinanceirochinaBO::listarPlanosconta(); 			
			$this->view->objContasrece		= FinanceirochinaBO::listaContasreceber($params);			
			
			$linkrec = "";
			
			if(isset($pesq['tiporec'])){
    			if($params['tiporec']==1):
    				if(!empty($params['buscaidrec'])):
    					$linkrec = "/tiporec/1/buscaidrec/".$params['buscaidrec'];
    				endif;
    			elseif ($params['tiporec']==2):
    				if($params['buscaforrec']!=0):
    					$linkrec = "/tiporec/2/buscaforrec/".$params['buscaforrec'];
    				endif;
    			elseif ($params['tiporec']==3):
    				if(!empty($params['buscavalorrec'])):
    					$linkrec = "/tiporec/3/buscavalorrec/".$params['buscavalorrec'];
    				endif;
    			endif;
			}
			
			if(!empty($params['dtinirec'])):
				$linkrec .= "/dtinirec/".$params['dtinirec'];
			endif;
			
			if(!empty($params['dtfimrec'])):
				$linkrec .= "/dtfimrec/".$params['dtfimrec'];
			endif;
			
			$linkrec .= "/aba/1";
			$this->view->objLinkrec = $linkrec;	
			
		else:		
			$this->_redirect("/admin/administracao/erro");
        endif;
        		
		LogBO::cadastraLog("ADM/Fin chines/Recebimentos",1,$usuario->id,"","",'');
	}	
	
	
	public function buscainvoiceAction(){
	    $this->_helper->layout->disableLayout();
	    $this->view->translate	=	Zend_Registry::get('translate');
	    $params = $this->_getAllParams();
	    
	    if($params['buscacli'] != 0):
	    	$this->view->objInvoice = KangvendasBO::listaVendaskang($params);
	    endif;
	    
	    if($params['conta']!=""):
	    	$this->view->objInvfin	= FinanceirochinaBO::listarInvoicesrec(md5($params['conta']));
	    endif;	    
	}
	
	
	public function financeirochinareccadAction(){
		$this->topoAction('admin');
		$this->view->translate	=	Zend_Registry::get('translate');
			
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 55) as $list);

        if($list->visualizar==1):
        	$params = $this->_getAllParams();
			
        	$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
			$this->view->objLib		= $list->aba1;
			        	
			$this->view->objPlanocontas		= FinanceirochinaBO::listarPlanosconta();
			$this->view->objFornec			= ClientesBO::buscaParceiros("clienteschines","","A");
			$this->view->objContasbanc		= FinanceirochinaBO::listarContasbancarias();
			
			if(!empty($params['rec'])):
				$this->view->objRec	   		= FinanceirochinaBO::buscarContarec($params['rec']);
				$this->view->objAnerec		= FinanceirochinaBO::listarAnexosareceber($params['rec']);
				$this->view->objInvoice		= FinanceirochinaBO::listarInvoicesrec($params['rec']);

				$bov = new KangvendasModel();
				$bo  = new KanginvoiceModel();
				
				$this->view->objCominvoice  = $bo->fetchAll('md5(id_fin_contasareceber) = "'.$params['rec'].'"');
                $this->view->objInvFrete    = $bo->fetchAll('md5(id_freterec) = "'.$params['rec'].'"');
				
				foreach (FinanceirochinaBO::buscarContarec($params['rec']) as $lista);								
				LogBO::cadastraLog("ADM/Fin chines/Recebimentos",1,$usuario->id,$lista->id,"R".substr("000000".$lista->id,-6,6),'');
			endif;			
			
		else:		
			$this->_redirect("/admin/administracao/erro");
        endif;        
	}
	
	public function gravafinanceirorecAction(){
	    $this->_helper->layout->disableLayout();
	    
	    try{
	        
	        $bo    = new FinanceirochinavalidateModel();
	        $bof   = new FinanceirochinaModel();
	        $bofr  = new FinanceirochinareceberModel();
	        
	        $params = $this->_getAllParams();
	        $financeiroChina = new FinanceirochinarecBO();
	        
	        $id = $financeiroChina->gravarContasrec($params);
	        $financeiroChina->gravarInvoice($params, $id);
	        $financeiroChina->gravarArquivos($params, $id);
	        $financeiroChina->gravaRetorno($params, $id);
	        $financeiroChina->gravaFrete($params, $id);

	        if($params['bancopagamentorec'] == 11 && $params['baixarec'] == 1){
    	        $arrayConc = array(
    	            'data'          => substr($params['datapagamentorec'],6,4).'-'.substr($params['datapagamentorec'],3,2).'-'.substr($params['datapagamentorec'],0,2),
                    'valor'         => str_replace(',','.',str_replace('.','',$params['valorpagamentorec'])),
                    'situacao'      => 1,
                    'valida'        => 0,
                    's_conta'       => $params['bancopagamentorec'],
    	        );
    	            	        
    	        $idconc = $bo->insert($arrayConc);
    	        
    	        $bofr->update(array('st_conc' => $idconc), 'id = "'.$id.'"');
	        }
	        
	        echo md5($id)."|";
	        
	    }catch (Zend_Exception $e){
	        echo "erro|".$e->getMessage();
	    }
	    
	    exit();
	}
	
	
	public function excluirchinarecAction(){
		$params = $this->_getAllParams();
		FinanceirochinaBO::excluirContasrec($params['conta']);
		$this->_redirect("/admin/administracao/financeirochinareccad/rec/".$params['conta']);
	}
	
	public function excluirchinapagAction(){
		$params = $this->_getAllParams();
		FinanceirochinaBO::excluirContaspag($params['conta']);
		$this->_redirect("/admin/administracao/financeirochinapagcad/pay/".$params['conta']);
	}
	
	
	//---Financeiro china - Bancos -----------------------------------
	public function financeirochinabancosAction(){
		$this->topoAction('admin');
		$this->view->translate	=	Zend_Registry::get('translate');
			
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 55) as $list);

        if(($list->visualizar==1) and (($usuario->id_perfil==1) || ($usuario->id_perfil==7) || ($usuario->id_perfil==28))):
        	$params = $this->_getAllParams();
			
        	$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
			$this->view->objLib		= $list->aba1;
			$this->view->objAdm		= $list->aba2;
        	
			$this->view->objContasbanc	= FinanceirochinaBO::listarContasbancarias();
			
			LogBO::cadastraLog("ADM/Fin chines/Bancos",1,$usuario->id,"","");	
		else:		
			$this->_redirect("/admin/administracao/erro");
        endif;
        
	}
	
	//---Financeiro china - Plano de contas -----------------------------------
	public function financeirochinapcontasAction(){
		$this->topoAction('admin');
		$this->view->translate	=	Zend_Registry::get('translate');
			
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 55) as $list);

        if(($list->visualizar==1) and (($usuario->id_perfil==1) || ($usuario->id_perfil==7)|| ($usuario->id_perfil==28))):
        	$params = $this->_getAllParams();
			
        	$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
			$this->view->objLib		= $list->aba1;
			$this->view->objAdm		= $list->aba2;
        	
			$this->view->objPlanocontas	= FinanceirochinaBO::listarPlanosconta();
			LogBO::cadastraLog("ADM/Fin chines/Plano contas",1,$usuario->id,"","");	
		else:		
			$this->_redirect("/admin/administracao/erro");
        endif;
        
	}
	
	//---Financeiro china - retorno de impostos -----------------------------------
	public function financeirochinareturnAction(){
		$this->topoAction('admin');
				
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 55) as $list);

        if($list->visualizar==1):
        	$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
			$this->view->objLib		= $list->aba1;
			$this->view->objAdm		= $list->aba2;
			
        	$this->view->translate	=	Zend_Registry::get('translate');
		
			$params = $this->_getAllParams();
			
			$this->view->objFornec	= ClientesBO::buscaParceiros("fornecedorchines");

			if(empty($params['tipo'])):
				Zend_Paginator::setDefaultScrollingStyle('Sliding');
				Zend_View_Helper_PaginationControl::setDefaultViewPartial('index/paginator.phtml');
				$paginator = Zend_Paginator::factory(KangvendasBO::listaCominvoicekang($params));
				$currentPage = $this->_getParam('page', 1);
				$paginator
				->setCurrentPageNumber($currentPage)
				->setItemCountPerPage(20);
						
				$this->view->objList 	= $paginator;
			else: 
				$this->view->objList 	= KangvendasBO::listaVendaskang($params);
				$this->view->objPesq	= 1;
			endif;			
			
		else:		
			$this->_redirect("/admin/administracao/erro");
        endif;
        		
		LogBO::cadastraLog("ADM/Fin china/Ret impostos",1,$usuario->id,"","");
	}
	
	//---Financeiro china - retorno de impostos -----------------------------------
	public function financeirochinareturnforAction(){
		$this->topoAction('admin');				
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 55) as $list);

        if($list->visualizar==1):
        	$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
			$this->view->objLib		= $list->aba1;
			$this->view->objAdm		= $list->aba2;
			
        	$this->view->translate	=	Zend_Registry::get('translate');
		
			$params = $this->_getAllParams();
			
			$this->view->objPk		= KangvendasBO::listaVendaskangforn($params['invoice']);
			$this->view->objProd	= KangvendasBO::listaCominvoiceprod($params['invoice']);
			$this->view->objCont	= FinanceirochinaBO::listaPagamentoscompras($params['invoice']);
			$this->view->objTax		= FinanceirochinaBO::listaTaxreturn($params['invoice']);

			
			foreach (KangvendasBO::listaVendaskangforn($params['invoice']) as $list);
			LogBO::cadastraLog("ADM/Fin chines/Ret impostos",1,$usuario->id,$list->id_cominvoice,"S".substr("000000".$list->id_cominvoice,-6,6));
			
		else:		
			$this->_redirect("/admin/administracao/erro");
        endif;
        
	}
	
	public function financchinagerareturnAction(){
		$params	= $this->_getAllParams();
		FinanceirochinaBO::gravaInvoicereturn($this->_getAllParams());
		$this->_redirect("/admin/administracao/financeirochinareturnfor/invoice/".md5($params['cominvoice']));
	}
	
	public function financchinaatreturnAction(){
		FinanceirochinaBO::atualizaInvoicereturn($this->_getAllParams());
		$this->_redirect("/admin/administracao/financeirochinareturncad");
	}	
	
	//---Financeiro china - retorno de impostos cadastrados-----------------------------------
	public function financeirochinareturncadAction(){
		$this->topoAction('admin');
		$params = $this->_getAllParams();		
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 55) as $list);

        if($list->visualizar==1):
        	$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
			$this->view->objLib		= $list->aba1;
			$this->view->objAdm		= $list->aba2;
			
        	$this->view->translate	=	Zend_Registry::get('translate');			
        	$this->view->objLista		= FinanceirochinaBO::listaInvoicesregisrered();
			
			if($params['tipo']=='conc'):
				
				Zend_Paginator::setDefaultScrollingStyle('Sliding');
				Zend_View_Helper_PaginationControl::setDefaultViewPartial('index/paginator.phtml');
				$paginator = Zend_Paginator::factory(FinanceirochinaBO::listaInvoicereturn(1));
				$currentPage = $this->_getParam('page', 1);
				$paginator
				->setCurrentPageNumber($currentPage)
				->setItemCountPerPage(20);
						
				$this->view->objRet 	= $paginator;
			else:
				$this->view->objRet			= FinanceirochinaBO::listaInvoicereturn(0);
			endif;
			
			$this->view->objTipo		= $params['tipo'];			
			LogBO::cadastraLog("ADM/Fin chines/Ret impostos",1,$usuario->id,"","");
			
		else:		
			$this->_redirect("/admin/administracao/erro");
        endif;        
        		
	}
	
	//---Financeiro china - Conciliacao de contas -----------------------------------
	public function financeirochinaconcAction(){
		$this->topoAction('admin');
		$params = $this->_getAllParams();
		$this->view->translate	=	Zend_Registry::get('translate');
		$sessaoFin = new Zend_Session_Namespace('Default');

        $this->view->params = $params;
		
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 55) as $list);

        if($list->visualizar==1):
        	$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
			$this->view->objLib		= $list->aba1;
			$this->view->objAdm		= $list->aba2;
			
			if(!empty($params['contasval'])):
				$contascel	= $params['contasval'];
				$sessaoFin->contasel = $params['contasval'];
			else:
				$contascel	= $sessaoFin->contasel;
			endif;
			
			$contascel  = explode("|", $contascel);
			
			$this->view->objConciliac	= FinanceirochinaBO::listarConcilhacao($contascel[0], $params);
			$this->view->objSaldoc		= FinanceirochinaBO::listarSaldocontas($contascel[0], $params);
			
			if($contascel!=""):
				$this->view->objContaconc	= $contascel[0];
			    $this->view->moeda          = $contascel[1];
			endif;		
			
			//----Filtros da concilicao---------------- 
			//----filtro por id-----------
			if(!empty($params['buscaidconc'])):
				$linkconc	= "/buscaidconc/".$params['buscaidconc'];
			endif;
				
			//----filtro por valor-----------
			if((!empty($params['buscavliniconc'])) || (!empty($params['buscavlfimconc']))):
				if(!empty($params['buscavliniconc'])) $linkconc .= "/buscavliniconc/".$params['buscavliniconc'];
				if(!empty($params['buscavlfimconc'])) $linkconc .= "/buscavlfimconc/".$params['buscavlfimconc'];
			endif;
				
			if(!empty($params['dtiniconc'])): 
				$linkconc .= "/dtiniconc/".str_replace("/","-",$params['dtiniconc']);
			endif;
			
			if(!empty($params['dtfimconc'])): 
				$linkconc .= "/dtfimconc/".str_replace("/","-",$params['dtfimconc']);
			endif;
					
			$this->view->objLinkconc = $linkconc;	
			
			$this->view->objContasbanc	= FinanceirochinaBO::listarContasbancarias();

			$this->view->dtFim = date("d/m/Y");
			if(isset($params['dtfimconc']) and $params['dtfimconc'] != null) {
                $this->view->dtFim = $params['dtfimconc'];
            }

			LogBO::cadastraLog("ADM/Fin chines/Conciliação",1,$usuario->id,"","");
			
		else:		
			$this->_redirect("/admin/administracao/erro");
        endif;
	}
	
	//remove anexo das contas---
	public function remanexofinchinaAction(){
		$params  = $this->_getAllParams();
		FinanceirochinaBO::remAnexos($params);
		if(!empty($params['pay'])):
			$this->_redirect("/admin/administracao/financeirochinapagcad/pay/".$params['pay']);

		elseif(!empty($params['rec'])):
			$this->_redirect("/admin/administracao/financeirochinareccad/rec/".$params['rec']);
		endif;
	}
	
	//--cadastra plano de contas---
	public function cadplanocontasAction(){
		FinanceirochinaBO::gravaPlanosconta($this->_getAllParams());
		$this->_redirect("/admin/administracao/financeirochinapcontas");		
	}
	
	//--remove plano de contas---
	public function remplanocontasAction(){
		FinanceirochinaBO::removePlanosconta($this->_getAllParams());
		$this->_redirect("/admin/administracao/financeirochinapcontas");
	}
	//--cadastra contas bancarias---
	public function cadbancosAction(){
		FinanceirochinaBO::gravaContasbancarias($this->_getAllParams());
		$this->_redirect("/admin/administracao/financeirochinabancos");
	}
	//--remove contas bancarias---
	public function rembancosAction(){
		FinanceirochinaBO::removeContasbancarias($this->_getAllParams());
		$this->_redirect("/admin/administracao/financeirochinabancos");
	}
	
	//---Financeiro china exportacao xls-----------------------------------
	public function financeirochinaexpAction(){
		$this->_helper->layout->disableLayout();
		date_default_timezone_set('America/Sao_Paulo');
		$this->view->translate	=	Zend_Registry::get('translate');
		
		$params	= $this->_getAllParams();
		if($params['aba']==1):
			$this->view->objContaspagar		= FinanceirochinaBO::listaContasreceber($params);
		else:
			$this->view->objContaspagar		= FinanceirochinaBO::listaContaspagar($params);
		endif;
		$this->view->objTipoe	= $params['texp'];
		$this->view->objAba		= $params['aba'];
	}
	
	//---Financeiro china impressao-----------------------------------
	public function financeirochinaimpAction(){
		$this->_helper->layout->disableLayout();
		date_default_timezone_set('America/Sao_Paulo');
		$this->view->translate	=	Zend_Registry::get('translate');
		
		$params	= $this->_getAllParams();
		if($params['aba']==1):
			$this->view->objContaspagar		= FinanceirochinaBO::listaContasreceber($params);
		else:
			$this->view->objContaspagar		= FinanceirochinaBO::listaContaspagar($params);
		endif;
		$this->view->objAba		= $params['aba'];
	}
	
	
	//--Conciliacao china------------------------------
	public function cadconciliacaoAction(){
		$params	= $this->_getAllParams();
		FinanceirochinaBO::gravaValidacaocontas($params);
		$this->_redirect("/admin/administracao/financeirochinaconc");
	}
	
	public function remconciliacaoAction(){
		FinanceirochinaBO::removeConciliacaocontas($this->_getAllParams());
		$this->_redirect("/admin/administracao/financeirochinaconc");
	}
	
	public function conciliarcontAction(){
		FinanceirochinaBO::conciliarContas($this->_getAllParams());
		$this->_redirect("/admin/administracao/financeirochinaconc");
	}
	
	public function buscacontasfinAction(){
		$this->_helper->layout->disableLayout();
		
		$this->view->translate	=	Zend_Registry::get('translate');
		
		$this->view->objContasbanc	= FinanceirochinaBO::listarContasbancarias();
		
		$params	= $this->_getAllParams();
		
		if(($params['tipo']==3) and ($params['bancobusca']!=0)){
			$this->view->objContaspagar = FinanceirochinaBO::listarExtratonconc($params);
			
			if($params['bancobusca']==9):
				foreach (FinanceirochinaBO::buscaContaconc($params) as $listc);
				$this->view->objVlbusca	= $listc->valor;
			endif;
			
		}elseif($params['tipo']==3){
			$this->view->objErro = 1;
			
		}else{
			$this->view->objContaspagar = FinanceirochinaBO::listarContasvalidar($params);
			
		}		
		
		$this->view->objIdc			= $params['idc'];
		$this->view->objTipo		= $params['tipo'];
		$this->view->objTpant		= $params['tpant'];
		$this->view->objConta		= $params['conta'];
		$this->view->objBancobusca	= $params['bancobusca'];
	}
	
	public function buscacontasfinpedAction(){
		$this->_helper->layout->disableLayout();
		date_default_timezone_set('America/Sao_Paulo');
		$this->view->translate	=	Zend_Registry::get('translate');
		
		$params	= $this->_getAllParams();
		
		if($params['tipo']==1):
			$this->view->objContas 	= KangcomprasBO::listaPedidosabertos($params);
			$this->view->objTipo	= 1;
		elseif($params['tipo']==2):
			$this->view->objContas 	= TaicomprasBO::listaComprasabertas($params);
			$this->view->objTipo	= 2;
		endif;	
		
		$this->view->objPurc	= FinanceirochinaBO::listarPurchasepagar(md5($params['conta']));
		$this->view->objFor		= $params['forn'];
		$this->view->objPed		= $params['conta'];
		$this->view->objPurch	= $params['npurc'];
	}
	
	public function regconciliaAction(){
		FinanceirochinaBO::gerarConciliacao($this->_getAllParams());
		$this->_redirect("/admin/administracao/financeirochinaconc");
	}
	
	//---Financeiro china concilia impressao-----------------------------------
	public function financeirochinaconcimpAction(){
		$this->_helper->layout->disableLayout();
		date_default_timezone_set('America/Sao_Paulo');
		$this->view->translate	=	Zend_Registry::get('translate');
		
		$params	= $this->_getAllParams();
		
		$contascel	= $params['contasval'];
		$this->view->objContaconc	= $contascel;
		$this->view->objContasbanc	= FinanceirochinaBO::listarContasbancarias();
		$this->view->objConciliac	= FinanceirochinaBO::listarConcilhacao($contascel,$params);
		$this->view->objSaldoc		= FinanceirochinaBO::listarSaldocontas($contascel,$params);
		
	}
	
	//---Financeiro china concilia exporta-----------------------------------
	public function financeirochinaconcexpAction(){
		$this->_helper->layout->disableLayout();
		date_default_timezone_set('America/Sao_Paulo');
		$this->view->translate	=	Zend_Registry::get('translate');
		
		$params	= $this->_getAllParams();
		
		$contascel	= $params['contasval'];
		$this->view->objContaconc	= $contascel;
		$this->view->objContasbanc	= FinanceirochinaBO::listarContasbancarias();
		$this->view->objConciliac	= FinanceirochinaBO::listarConcilhacao($contascel, $params);
		$this->view->objSaldoc		= FinanceirochinaBO::listarSaldocontasAudit($params, $contascel);

		$this->view->objTipoe		= $params['texp'];

        $this->view->dtFim = date("d/m/Y");
        $this->view->dtIni = $params['dtiniconc'];
        if(isset($params['dtfimconc']) and $params['dtfimconc'] != null) {
            $this->view->dtFim = $params['dtfimconc'];
        }
	}

    /**
     * Exporta somente IDs
     * @throws Zend_Db_Adapter_Exception
     * @throws Zend_Db_Exception
     * @throws Zend_Db_Statement_Exception
     * @throws Zend_Exception
     */
    public function financeirochinaexpidAction(){
        $this->_helper->layout->disableLayout();
        date_default_timezone_set('America/Sao_Paulo');
        $this->view->translate	=	Zend_Registry::get('translate');

        $params	= $this->_getAllParams();

        $this->view->objConciliac	= FinanceirochinaBO::listarConcilhacaoAudit($params);
        $this->view->objSaldoc		= FinanceirochinaBO::listarSaldocontasAudit($params);

        $this->view->dtFim = date("d/m/Y");
        $this->view->dtIni = $params['dtiniconc'];
        if(isset($params['dtfimconc']) and $params['dtfimconc'] != null) {
            $this->view->dtFim = $params['dtfimconc'];
        }
    }
	
	//---Financeiro china tax return impressao-----------------------------------
	public function financeirochinataximpAction(){
		$this->_helper->layout->disableLayout();
		date_default_timezone_set('America/Sao_Paulo');
		$this->view->translate	=	Zend_Registry::get('translate');
		
		$params	= $this->_getAllParams();
		
		$this->view->objForn		= FinanceirochinaBO::listaFornecedorecontasreturn();
		$this->view->objContas		= FinanceirochinaBO::listaContasreturn();
		if($params['ir']=="reg"):
			$this->view->objT			= $params['ir'];
			$this->view->objLista		= FinanceirochinaBO::listaInvoicesregisrered();
			$this->view->objRet			= FinanceirochinaBO::listaInvoicereturn();

		elseif($params['ir']=="fin"):
			$this->view->objT			= $params['ir'];
			$this->view->objLista		= FinanceirochinaBO::listaInvoicesregisrered();
			$this->view->objRet			= FinanceirochinaBO::listaInvoicereturnfinal();

		elseif($params['ir']=="pend"):
			$this->view->objForn		= FinanceirochinaBO::listaFornecedorecontasreturn();
			$this->view->objContas		= FinanceirochinaBO::listaContasreturn();

		endif;
	}
	
	//---Financeiro china tax return exporta-----------------------------------
	public function financeirochinataxexpAction(){
		$this->_helper->layout->disableLayout();
		date_default_timezone_set('America/Sao_Paulo');
		$this->view->translate	=	Zend_Registry::get('translate');
		
		$params	= $this->_getAllParams();
		
		$contascel	= $params['contasval'];
		$this->view->objContaconc	= $contascel;
		$this->view->objContasbanc	= FinanceirochinaBO::listarContasbancarias();
		$this->view->objConciliac	= FinanceirochinaBO::listarConcilhacao($contascel,$params);
		$this->view->objSaldoc		= FinanceirochinaBO::listarSaldocontas($contascel,$params);
		$this->view->objTipoe		= $params['texp'];
		
	}
		
	//---Painel de atividades-----------------------------------
	public function painelatividadesAction(){
		$this->topoAction('admin');
		$usuario 	= Zend_Auth::getInstance()->getIdentity();
		
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 74) as $list);
		
		if($list->visualizar==1){
			$this->view->objIns		= $list->inserir;
			
			LogBO::cadastraLog("Administração/Atividades",1,$usuario->id,"","");
		}else{
			$this->_redirect("/admin/administracao/erro");
		}
	}
	
	public function buscapainelatividadesAction(){
	    $this->_helper->layout->disableLayout();
	    $params = $this->_getAllParams();	    
	    
	    AtividadesBO::listarAtividades($params);
	    
	    exit();	    
	}
	
	public function redlistaatividadesAction(){
		$params = $this->_getAllParams();
		$this->_helper->flashMessenger->addMessage(array('tppesq' => $params['tppesq']));
		$this->_redirect("/admin/administracao/painelatividadeslista");
	}
	
	public function painelatividadeslistaAction(){
		$this->topoAction('admin');
		$usuario 	= Zend_Auth::getInstance()->getIdentity();
	
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 74) as $list);
	
		if($list->visualizar==1){
			$this->view->objIns		= $list->inserir;
				
			LogBO::cadastraLog("Administração/Atividades",1,$usuario->id,"","");
		}else{
			$this->_redirect("/admin/administracao/erro");
		}
	}
	
	public function buscapainelatividadeslistaAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		 
		AtividadesBO::listarAtividadesuser($params);
		 
		exit();
	}
	
	public function montatividadesAction(){
	    $this->_helper->layout->disableLayout();
	    $params = $this->_getAllParams();

	    AtividadesBO::formAtividade($params);
	    
	    exit();
	}
	
	public function gravaatividadeAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		echo AtividadesBO::cadastraAtividades($params);		 
		exit();
	}
	
	public function buscaatividadesAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
	
		AtividadesBO::buscaAtividade($params);
		 
		exit();
	}
	
	public function gravacomentarioAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		echo AtividadesBO::cadastraComentarios($params);
		exit();
	}
	
	public function gravainicioatividadeAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		echo AtividadesBO::iniciarAtividade($params);
		exit();
	}
	
	public function fechaatividadeAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		echo AtividadesBO::fechaAtividade($params);
		exit();
	}
	
	public function encerraratividadeAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		echo AtividadesBO::encerraAtividade($params);
		exit();
	}
	
	public function reabriratividadeAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		echo AtividadesBO::reabrirAtividade($params);
		exit();
	}
	
	public function qtatividadeAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		AtividadesBO::qtAtividades($params);
		exit();
	}
	

	
	
	
//------ Traducao ----------------------------------------------------------------------------------------
	
	public function traducoesAction(){
		$this->topoAction('admin');
		$params = $this->_getAllParams();
		
		if($this->_request->isPost()):
			InternacionalizacaoBO::gravarTraducoes($params);
			$this->_redirect("/admin/administracao/traducoes/page/".$params['pagina']);
		endif;
		
		Zend_Paginator::setDefaultScrollingStyle('Sliding');
		Zend_View_Helper_PaginationControl::setDefaultViewPartial('index/paginator.phtml');
		$paginator = Zend_Paginator::factory(InternacionalizacaoBO::listaTraducoes());
		$currentPage = $this->_getParam('page', 1);
		$paginator->setCurrentPageNumber($currentPage)
				  ->setItemCountPerPage(25);
		
		$this->view->objList = $paginator;
		$this->view->objPage = $params['page'];
		
	}
	
	public function copiatraducaoAction(){
		$this->_helper->layout->disableLayout();
		InternacionalizacaoBO::gerarTraducao();
	}
	
	public function gerarpoAction(){
		$this->_helper->layout->disableLayout();
		InternacionalizacaoBO::gerarPO();
	}
		


}