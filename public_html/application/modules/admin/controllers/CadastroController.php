<?php
class Admin_CadastroController extends Zend_Controller_Action {
		
	public function init(){
	    date_default_timezone_set('America/Sao_Paulo');
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
	
	//---Parceiros--------------
	public function parceiroslistaAction(){
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 12) as $list);
		foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $listp);
						
        if($list->visualizar==1):
        	$params	= $this->_getAllParams();
        
        	$sessaobusca = new Zend_Session_Namespace('Parceiros');			
			if($params['filt']=="all"):
				$sessaobusca->where = "";
			endif;	        
        
        	$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
			$this->view->objNivel	= $listp->nivel;
					
			$this->topoAction('cadastro');
			
			Zend_Paginator::setDefaultScrollingStyle('Sliding');
			Zend_View_Helper_PaginationControl::setDefaultViewPartial('correio/paginator.phtml');
			$paginator = Zend_Paginator::factory(ClientesBO::buscaClientes($this->_getAllParams()));
			$currentPage = $this->_getParam('page', 1);
			$paginator->setCurrentPageNumber($currentPage)
					  ->setItemCountPerPage(10);
			
			$this->view->objList 		= $paginator;
			$this->view->objPag 		= $params[page];
			$this->view->page	 		= $params['page']; 
			$this->view->objRegioes		= RegioesBO::listaRegioesclientes();
			$this->view->objRegtelvend	= RegioesBO::buscaRegioestelevendas();
			
		else:		
			$this->_redirect("/admin/cadastro/erro");
        endif;
        		
		LogBO::cadastraLog("Cadastro/Parceiros",1,$usuario->id,"","");		
	}
	
	public function parceirosAction(){
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 12) as $list);
        
        if(($list->visualizar==1)):        
        	$this->topoAction('cadastro');
			$params = $this->_getAllParams();
			
			$this->view->objEdi			= $list->editar;
			$this->view->objPaises		= EstadosBO::listaPaises();
			$this->view->objUfChina 	= EstadosBO::listarEstadosChina();
			$this->view->objPerfil 		= PerfilBO::listarPerfilclientes();
			$this->view->objRegioes		= RegioesBO::listaRegioesclientes();
			$this->view->objRegtelvend	= RegioesBO::buscaRegioestelevendas();
			$this->view->objTransport	= ClientesBO::buscaParceiros('transportadoras');
			
			$this->view->objGinteresse	= ClientesBO::buscaClientesgrupos();
						
			if($params[idcad]=="true") $this->view->objConf = 1;
			if($params[idcad]=="false") $this->view->objConf = 2;
			
			if($this->_request->isPost()){
				if(($list->editar==1)||($list->inserir==1)):
					$arrayret = ClientesBO::cadastraClientes($this->_getAllParams());
					
					if(!empty($params['id_cliente'])):
						LogBO::cadastraLog("Cadastro/Parceiros",4,$usuario->id,$arrayret['idcliente'],"Parceiro ID ".$params['id_cliente']);
					else:
						LogBO::cadastraLog("Cadastro/Parceiros",2,$usuario->id,$arrayret['idcliente'],"Parceiro ID ".$arrayret['idcliente']);
					endif;
					
					$this->view->objRet = $arrayret;
					
				endif;
			}
			
			if(!empty($params[idparceiro])):
				$bo = new ClientesBO();
				$idparc = '';
				
				$busca['idparceiromd5']		= $params[idparceiro];
				foreach ($bo->buscaParceiros("",$busca,"T") as $lista);
				$idparc = $lista->ID;
				
				$this->view->objParceiro 	= $bo->buscaParceiros("",$busca,"T");
				$this->view->objEnd			= $bo->listaEnderecos($idparc);
				$this->view->objEndchi		= $bo->listaEnderecoschines($idparc);
				$this->view->objEmail		= $bo->listaEmails($idparc);
				$this->view->objinfkang		= $bo->listaInfokang($idparc);
				$this->view->objChina		= $bo->listaChina($idparc);
				$this->view->objTelefones	= $bo->listaTelefones($idparc);
				$this->view->objArquser		= ClientesBO::listaArquivoscliente($params);
								
				
				$estadosBO = new EstadosBO();
				
				foreach ($bo->listaEnderecos($idparc) as $enderecos):
					if($enderecos->TIPO==1):
					if(!empty($enderecos->ESTADO)):  $this->view->objCidades		= $estadosBO->buscaCidadesidestado($enderecos->ESTADO); endif;
					if(!empty($enderecos->PAIS)): 	$this->view->objUf 				= $estadosBO->buscaEstados($enderecos->PAIS); endif;	
					$arrayuf['iduf'] 				= md5($enderecos->ESTADO);
						
					elseif($enderecos->TIPO==2):
					if(!empty($enderecos->ESTADO)): $this->view->objCidades2		= $estadosBO->buscaCidadesidestado($enderecos->ESTADO); endif;
					if(!empty($enderecos->PAIS)):   $this->view->objUf2				= $estadosBO->buscaEstados($enderecos->PAIS); endif;
					endif;
				endforeach;				
				
				$this->view->page	 		= $params['page']; 
								
				
				$bo     = new ClientesModel();
				$boc    = new ClientesconsigneeModel();
				
				$bocidade = new CidadesModel();
				$boestado = new EstadosModel();
				
				$this->view->objConsignee = $boc->fetchRow('id_cliente = "'.$idparc.'"');
				
				if($this->view->objConsignee->id_cidade != null){
				    $cidade = $bocidade->fetchRow("id = '".$this->view->objConsignee->id_cidade."'");
				    $this->view->objCidadesconsignee = $estadosBO->buscaCidadesidestado($cidade->id_estados);
				    
				    $estado = $boestado->fetchRow("id = '".$cidade->id_estados."'");
				    $this->view->objEstadosconsignee = $estadosBO->buscaEstados($estado->id_paises);
				    
				    $this->view->cidadeconsignee = $cidade;
				    $this->view->estadoconsignee = $estado;
				}
				
				LogBO::cadastraLog("Cadastro/Parceiros",1,$usuario->id,$idparc,"PARCEIRO ID ".$idparc);
				
			else:
				LogBO::cadastraLog("Cadastro/Parceiros",1,$usuario->id,"","");
			endif; 
			
			
		else:		
			$this->_redirect("/admin/cadastro/erro");
        endif;
		
	}
	
	//--- Exportacao da lista de parceiro para excel o odt ----------------------------
	public function parceiroslistaexpAction(){
		$this->_helper->layout->disableLayout();
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 12) as $list);
		foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $listp);
						
        if($list->visualizar==1):
           $params = $this->_getAllParams();        	
	       $this->view->objList = ClientesBO::buscaClientes($this->_getAllParams());
	       $this->view->objTipo = $params['tipo'];
		else:		
			$this->_redirect("/admin/cadastro/erro");
        endif;
        		
		LogBO::cadastraLog("Cadastro/Parceiros Exp",1,$usuario->id,"","");
		
	}
	
	//------------- Produtos ------------------------
	public function buscaprodutoAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		
		$this->view->objGrupos		= GruposprodBO::listaSubgrupos();
		$this->view->objProd		= VendaBO::buscaGruposvendprodscli($params['idcli']);
	}
	
	/* ---- Usado para busca de cidades, estados e paises, via ajax -------------*/
	public function buscaufAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		$this->view->objParams  = $params;
		
		if($params['tipo'] == 1):
			$this->view->objList		= EstadosBO::buscaEstados($params['id']);
		else:
			$this->view->objList		= EstadosBO::buscaCidadesidestado($params['id']);
		endif;
				
	}
	
	public function buscacidadeAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		$this->view->objCidades		= EstadosBO::buscaCidadesuf($params['uf']);		
	}
	
	public function buscacidadeibgeAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		$this->view->objCidades		= EstadosBO::buscaCidadesidestado($params['idestado']);
		$this->view->tipo			= $params['tipo'];	
	}
	
	/**
	 * Busca os estados conforme o pais
	 */
	public function buscaufbypaisAction(){
	    $this->_helper->layout->disableLayout();
	    $params = $this->_getAllParams();
	    
	    $obj = new EstadosModel();
	    $objList = $obj->fetchAll("id_paises = '".$params['idpais']."'",'nome');			
	    
	    ?><option value="0">Selecione</option><?php 
	    foreach ($objList as $estados){
	        ?><option value='<?php echo $estados->id?>'><?php echo $estados->nome?></option><?php
	    }
	    
	    exit();
	}
	
	/**
	 * Busca as cidades conforme os estados
	 */
	public function buscacidadebyufAction(){
	    $this->_helper->layout->disableLayout();
	    $params = $this->_getAllParams();
	    
	    $obj = new CidadesModel();
	    $objList = $obj->fetchAll("id_estados = '".$params['iduf']."'",'nome');	
	 
	    ?><option value="0">Selecione</option><?php
	    foreach ($objList as $cidades){
	        ?><option value='<?php echo $cidades->id?>'><?php echo $cidades->nome?></option><?php
	    }
	    
	    exit();
	}
	
	
	public function buscacnpjAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		if(count(ClientesBO::buscaClientescnpj($params['cnpj'])) > 0):
			echo '1';
		endif;
		exit();
	}
	
	public function cadparceiroAction(){
		$this->_helper->layout->disableLayout();
		$usuario = Zend_Auth::getInstance()->getIdentity();
		$params = $this->_getAllParams();
        
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 12) as $list);
        
        if(($list->editar==1)||($list->inserir==1)): 
			$bo = new ClientesBO();
        	
			$arrayret = $bo->cadastraClientes($this->_getAllParams());
			
			if(!empty($params['id_cliente'])):
				LogBO::cadastraLog("Cadastro/Parceiros",4,$usuario->id,$arrayret['idcliente'],"PARCEIRO ID ".$params['id_cliente']);
				$this->_redirect("/admin/cadastro/parceiros/idparceiro/".md5($params['id_cliente']));
			else: 
				
			
				LogBO::cadastraLog("Cadastro/Parceiros",2,$usuario->id,$arrayret['idcliente'],"PARCEIRO ID ".$arrayret['idcliente']);
				$this->_redirect("/admin/cadastro/parceiros/idparceiro/".md5($arrayret['idcliente'])."/idcad/true");
			endif;  
			
		else:		
			$this->_redirect("/admin/cadastro/erro");
        endif;
        
        exit();
	}
	
	public function parceirosremanexoAction(){
		$params = $this->_getAllParams();
		$iduser = ClientesBO::removeAnexo($params);
		$this->_redirect("/admin/cadastro/parceiros/idparceiro/".md5($iduser));
	}
	
	
	public function parceirosbuscaAction(){		
		$this->topoAction('cadastro');
					
		Zend_Paginator::setDefaultScrollingStyle('Sliding');
		Zend_View_Helper_PaginationControl::setDefaultViewPartial('correio/paginator.phtml');
		$paginator = Zend_Paginator::factory(ClientesBO::buscaClientes($this->_getAllParams()));
		$currentPage = $this->_getParam('page', 1);
		$paginator
		->setCurrentPageNumber($currentPage)
		->setItemCountPerPage(25);
		
		$this->view->objList = $paginator;
	}
	
	public function parceirosvisualizarAction(){
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 12) as $list);
        
        if(($list->visualizar==1)):
         	$this->view->objVis		= $list->visualizar;
			$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;        
			
			
			$this->topoAction('cadastro');
			$this->view->objPaises		= EstadosBO::listaPaises();
			$this->view->objUfChina 	= EstadosBO::listarEstadosChina();
			$this->view->objPerfil 		= PerfilBO::listarPerfil();
			$this->view->objRegioes		= RegioesBO::listaRegioes();
			$this->view->objGrupos		= GruposprodBO::listaGrupos();
			$this->view->objTransport	= ClientesBO::buscaParceiros('transportadoras');
			
			$params = $this->_getAllParams();
			if($params[idcad]=="true") $this->view->objConf = 1;
			if($params[idcad]=="false") $this->view->objConf = 2;
			
			if(!empty($params[idparceiro])):
				$bo = new ClientesBO();
				$idparc = '';
				
				$busca['idparceiromd5']	= $params[idparceiro];
				foreach (ClientesBO::buscaParceiros("",$busca,"T") as $lista);
				$idparc = $lista->ID;
				
				$busca['idparceiro']		= $idparc;
				$this->view->objParceiro 	= ClientesBO::buscaParceiros("",$busca);
				$this->view->objEnd			= $bo->listaEnderecos($idparc);
				$this->view->objEndchi		= $bo->listaEnderecoschines($idparc);
				$this->view->objEmail		= $bo->listaEmails($idparc);
				$this->view->objinfkang		= $bo->listaInfokang($idparc);
				$this->view->objChina		= $bo->listaChina($idparc);
				$this->view->objTelefones	= $bo->listaTelefones($idparc);
				$this->view->objDesc		= $bo->listaDesc($idparc);
				//$this->view->objGcli		= $bo->listaGruposcli($idparc);
				$this->view->page	 		= $params['page']; 
				
				
				foreach ($bo->listaEnderecos($idparc) as $enderecos):
					if($enderecos->TIPO==1):
						if(!empty($enderecos->ESTADO)):  $this->view->objCidades			= EstadosBO::buscaCidadesidestado($enderecos->ESTADO); endif;
						if(!empty($enderecos->PAIS)): 	 $this->view->objUf 				= EstadosBO::buscaEstados($enderecos->PAIS); endif;	
						
						$arrayuf['iduf'] 				= md5($enderecos->ESTADO);
						if(!empty($enderecos->ESTADO)):  $this->view->objTributos		= TributosBO::buscaDespesas($arrayuf); endif;	
					elseif($enderecos->TIPO==2):
						if(!empty($enderecos->ESTADO)):  $this->view->objCidades2		= EstadosBO::buscaCidadesidestado($enderecos->ESTADO); endif;
						if(!empty($enderecos->PAIS)):    $this->view->objUf2			= EstadosBO::buscaEstados($enderecos->PAIS); endif;
					endif;
				endforeach;	
				
				
				LogBO::cadastraLog("Cadastro/Parceiros",1,$usuario->id,$idparc,"PARCEIRO ID ".$idparc);
				
			else:
				LogBO::cadastraLog("Cadastro/Parceiros",1,$usuario->id,"","");
			endif; 
			
			
		else:		
			$this->_redirect("/admin/cadastro/erro");
        endif;
		
	}
	
	
	public function parceirosgruposAction(){
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 12) as $list);
		
		if($list->visualizar==1):
			$params	= $this->_getAllParams();
		
			$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
				
			$this->topoAction('cadastro');
			$this->view->objList 		= ClientesBO::buscaClientesgrupos();
						
		else:
			$this->_redirect("/admin/cadastro/erro");
		endif;
	
		LogBO::cadastraLog("Cadastro/Parceiros Grupos",1,$usuario->id,"","");
	}
	
	public function parceirosgruposcadAction(){
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 12) as $list);
	
		if($list->visualizar==1):
			$this->topoAction('cadastro');
			$params	= $this->_getAllParams();
			
			if($this->_request->isPost()):
				$this->view->gravaSucesso = ClientesBO::gravaGrupointeresse($params);				
			endif;
			
			$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
			
			if(!empty($params['grupo'])):
				$this->view->objList 		= ClientesBO::buscaClientesgrupos($params);
				$this->view->objGcli		= ClientesBO::listaGruposinteresse($params);
			endif;
			
			$this->view->objGrupos		= GruposprodBO::listaSubgrupos();
			
		else:
			$this->_redirect("/admin/cadastro/erro");
		endif;
	
		LogBO::cadastraLog("Cadastro/Parceiros Grupos",1,$usuario->id,"","");
	}
	
	public function removergrupoparceiroAction(){
		$params = $this->_getAllParams();
		ClientesBO::removeGruposinteresse($params);
		$this->_redirect("/admin/cadastro/parceirosgrupos");
	}
	
	public function analiseopAction(){
		
		$this->topoAction('cadastro');
		$params = $this->_getAllParams();
		if(!empty($params[diag])):
			VendaBO::gravdiagAnalise($params);
		endif;
		$this->view->objAnalises = VendaBO::listardiagAnalise($params);
	}
	
	public function analiseopcadAction(){
	    
		
	}

	//---Erro por falta de acesso-----------------------------------
	public function erroAction(){
		$this->topoAction('cadastro');				
	}
	
	public function correcaoAction(){
		$this->_helper->layout->disableLayout();
	}
	
	//----Produtos-------------------------------------------------
	//----Lista de Produtos----------------------------------------
	public function produtosAction(){
		
		$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 13) as $list);
        
        if(($list->visualizar==1)):
        	$params = $this->_getAllParams();
        	$sessaobusca = new Zend_Session_Namespace('produtos');
        	if((isset($params['fil']) and $params['fil']=='comp')||($this->_request->isPost())):
				$sessaobusca->where 		= "";	
				$sessaobusca->buscamedia	= "";
				$sessaobusca->tipo			= "";
			endif;
		   	
         	$this->view->objVis		= $list->visualizar;
			$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
			
			$this->view->objGrupo	= GruposprodBO::listaGruposprodutos();
			$this->view->objVer		= (isset($params['ver'])) ? $params['ver'] : "";
			$this->view->objCod		= (isset($params['buscacod'])) ? $params['buscacod'] : "";
			$this->view->page	 	= (isset($params['page'])) ? $params['page'] : ""; 
			$this->view->objTipo	= $sessaobusca->tipo;
			$this->view->objMed		= $sessaobusca->buscamedia;
			
		else:		
			$this->_redirect("/admin/cadastro/erro");
        endif;
		
	}
	
	public function buscaprodutosAction(){
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 13) as $list);
		
		if(($list->visualizar==1)){
			$this->_helper->layout->disableLayout();
			
			ProdutosBO::listaProdutosgrupo($this->_getAllParams());
			exit();	
		}else{
			$this->_redirect("/admin/cadastro/erro");
		}
	}
		
	public function produtosexpAction(){
	    $usuario = Zend_Auth::getInstance()->getIdentity();
	    foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 13) as $list);
	    
	    if(($list->visualizar==1)){
		    $this->_helper->layout->disableLayout();
		    
		    header("Content-type: application/msexcel");
		    header("Content-Disposition: attachment; filename=Produtos.xls");
		    
		    ?>
	    	<html>
	    		<head>
		    		<title>ZTL Brasil - www.ztlbrasil.com.br </title>
		    		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	    		</head>
	    		<body>
	    			<?php 
	    			ProdutosBO::exportalistaProdutos();
	    			?>
	    		</body>
	    	</html>
	    	<?php	    
		    exit();
		    
	    }else{
		    $this->_redirect("/admin/cadastro/erro");
	    }
	}
		
	public function buscasubgrupoAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		$this->view->objGrupsub	= GruposprodBO::listaGruposprodutossub($params['id_grupo']);
		$this->view->objTipo	= $params['tipo'];	
	}
	
	public function buscaprodutokitAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		
		$this->view->qt				= $params['qt'];
		$this->view->objProd		= ProdutosBO::listaProdutosfornecedorleft($params);
	}
	
	public function produtosgruposAction(){
		
			$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 13) as $list);
        
        if($this->_request->isPost()):
        	GruposprodBO::gravarGrupo($this->_getAllParams());
        endif;
        
        if(($list->visualizar==1)):
         	$this->view->objVis		= $list->visualizar;
			$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;       

			$this->view->objList 	= GruposprodBO::listaGruposprodutos($this->_getAllParams());
			
		else:		
			$this->_redirect("/admin/cadastro/erro");
        endif;
		
	}
	
	public function produtosubgruposAction(){
		
			$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 13) as $list);
        
        if($this->_request->isPost()):
        	GruposprodBO::gravarSubgrupo($this->_getAllParams());
        endif;
        
        if(($list->visualizar==1)):
         	$this->view->objVis		= $list->visualizar;
			$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;       

			$this->view->objList 	= GruposprodBO::listaSubgrupos("");
			$this->view->objGrupo	= GruposprodBO::listaGruposprodutos();
		else:		
			$this->_redirect("/admin/cadastro/erro");
        endif;
		
	}
	
	public function produtosclassesAction(){
	
		$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 13) as $list);
	
		if(($list->visualizar==1)):
			if($this->_request->isPost()):
				ProdutosBO::gravarClasses($this->_getAllParams());
			endif;
			
			$this->view->objList = ProdutosBO::listaClasses();
			
		else:
			$this->_redirect("/admin/cadastro/erro");
		endif;
	
	}
	
	//--- localizacao de produtos ---------------------------------
	public function localizacaodeprodutosAction(){
	
		$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 13) as $list);
		
		if(($list->visualizar==1)){
			$this->view->objProdutos	= ProdutosBO::listaallProdutos(); 
			$params	=	$this->_getAllParams();
			if(!empty($params['busca']) || !empty($params['prod'])){
			    
			    if($params['gravar'] == 1){
			        $this->view->objRes = ProdutosBO::gravaLocalizacao($params);
			        $this->view->gravar = $params['gravar'];
			    }
			    
			    $busca = "";
			    if(!empty($params['busca'])){
			    	$busca = $params['busca'];
			    }elseif(!empty($params['prod'])){
			        $busca = $params['prod'];
			    }
			    
			    $this->view->objLocalizacao = ProdutosBO::buscaLocalizacao($busca);
			    $this->view->busca = $params['busca'];
			}
			
		}else{
			$this->_redirect("/admin/cadastro/erro");
		}
	
	}
	
	public function localizacaoremoveaAction(){
	    $params	=	$this->_getAllParams();
	    ProdutosBO::removeLocalizacao($params);
	    $this->_redirect("/admin/cadastro/localizacaodeprodutos/prod/".$params['prod']);
	}
	
	
	public function despesasfiscaisAction(){
		
		$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 80) as $list);
        
        if(($list->visualizar==1)):
         	$this->view->objVis		= $list->visualizar;
			$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;       

			$this->view->objList 	= EstadosBO::buscaEstados(1);
			
		else:		
			$this->_redirect("/admin/cadastro/erro");
        endif;		
	}
	
	public function despesasfiscaiscadAction(){
		
			$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 80) as $list);
        
        if(($list->visualizar==1)):
         	$this->view->objVis		= $list->visualizar;
			$this->view->objEdi		= $list->editar;       

			$params	=	$this->_getAllParams();
			
			$this->view->tp			= $params['tp'];
			$this->view->objList	= TributosBO::buscaDespesasid($params['desp']);
			$this->view->objCfop	= TributosBO::listaCfop();			
			
			if(empty($params['iduf'])):
				foreach (TributosBO::buscaDespesasid($params['desp']) as $despesa);
				$array['iduf']		= md5($despesa->id_estados);
				$this->view->objUf 		= EstadosBO::buscaEstadosid($array);
			else:
				$this->view->objUf 		= EstadosBO::buscaEstadosid($params);
			endif;
		else:		
			$this->_redirect("/admin/cadastro/erro");
        endif;		
	}
	
	public function gravadespesasfiscaisAction(){
		$id = TributosBO::gravarDespesas($this->_getAllParams());
        $this->_redirect("/admin/cadastro/despesasfiscaiscad/desp/".md5($id));
	}
	
	public function despesasfiscaisremAction(){
		$params	=	$this->_getAllParams();
		$id = TributosBO::removeDespesas($params['desp']);
        $this->_redirect("/admin/cadastro/despesasfiscaisuf/iduf/".$params['iduf']);        
	}
	
	public function despesasfiscaisufAction(){
		
			$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 80) as $list);
        
        if(($list->visualizar==1)):
         	$this->view->objVis		= $list->visualizar;
			$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;       

			$this->view->objUf 		= EstadosBO::buscaEstadosid($this->_getAllParams());
			$this->view->objList	= TributosBO::buscaDespesas($this->_getAllParams());
			
		else:		
			$this->_redirect("/admin/cadastro/erro");
        endif;		
	}
	
	
	public function tributocfopAction(){
		
			$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 80) as $list);
        
        if(($list->visualizar==1)):
         	$this->view->objVis		= $list->visualizar;
			$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;       

			$this->view->objList 	= TributosBO::listaCfop();
			
		else:		
			$this->_redirect("/admin/cadastro/erro");
        endif;		
	}
	
	public function tributocfopcadAction(){
		
			$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 80) as $list);
        
        if(($list->visualizar==1)):
         	$this->view->objIns			= $list->inserir;
			$this->view->objEdi			= $list->editar;       

			$this->view->objList 		= TributosBO::buscaCfop($this->_getAllParams());			
			
		else:		
			$this->_redirect("/admin/cadastro/erro");
        endif;
		
	}
	
	public function gravatributocfopAction(){
		TributosBO::gravarCfop($this->_getAllParams());
        $this->_redirect("/admin/cadastro/tributocfop");
	}
	
	//---- CST ICMS ----------------------------------------------------
	public function tributocstAction(){
		
			$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 80) as $list);
        
        if(($list->visualizar==1)):
         	$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;  
			$this->view->objList 	= TributosBO::listaCst();			
		else:		
			$this->_redirect("/admin/cadastro/erro");
        endif;		
	}
	
	public function tributocstcadAction(){
		
			$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 80) as $list);
        
        if(($list->visualizar==1)):
         	$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;       
			$this->view->objList 	= TributosBO::buscaCst($this->_getAllParams());			
		else:		
			$this->_redirect("/admin/cadastro/erro");
        endif;		
	}
	
	public function gravatributocstAction(){
		TributosBO::gravarCst($this->_getAllParams());
        $this->_redirect("/admin/cadastro/tributocst");
	}
	
	public function removetributocstAction(){
		TributosBO::removeCst($this->_getAllParams());
		$this->_redirect("/admin/cadastro/tributocst");
	}
	
	//---- CST IPI ----------------------------------------------------
	public function tributocstipiAction(){
		
			$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 80) as $list);
	
		if(($list->visualizar==1)):
			$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
			$this->view->objList 	= TributosBO::listaCstipi();
		else:
			$this->_redirect("/admin/cadastro/erro");
		endif;
	}
	
	public function tributocstipicadAction(){
		
			$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 80) as $list);
	
		if(($list->visualizar==1)):
		$this->view->objIns		= $list->inserir;
		$this->view->objEdi		= $list->editar;
		$this->view->objList 	= TributosBO::buscaCstipi($this->_getAllParams());
		else:
		$this->_redirect("/admin/cadastro/erro");
		endif;
	}
	
	public function gravatributocstipiAction(){
		TributosBO::gravarCstipi($this->_getAllParams());
		$this->_redirect("/admin/cadastro/tributocstipi");
	}
	
	public function removetributocstipiAction(){
		TributosBO::removeCstipi($this->_getAllParams());
		$this->_redirect("/admin/cadastro/tributocstipi");
	}
	
	//---- CST PIS/PASEP ----------------------------------------------------
	public function tributocstpisAction(){
		
			$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 80) as $list);
	
		if(($list->visualizar==1)):
			$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
			$this->view->objList 	= TributosBO::listaCstpis();
		else:
			$this->_redirect("/admin/cadastro/erro");
		endif;
	}
	
	public function tributocstpiscadAction(){
		
			$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 80) as $list);
	
		if(($list->visualizar==1)):
		$this->view->objIns		= $list->inserir;
		$this->view->objEdi		= $list->editar;
		$this->view->objList 	= TributosBO::buscaCstpis($this->_getAllParams());
		else:
		$this->_redirect("/admin/cadastro/erro");
		endif;
	}
	
	public function gravatributocstpisAction(){
		TributosBO::gravarCstpis($this->_getAllParams());
		$this->_redirect("/admin/cadastro/tributocstpis");
	}
	
	public function removetributocstpisAction(){
		TributosBO::removeCstpis($this->_getAllParams());
		$this->_redirect("/admin/cadastro/tributocstpis");
	}
	
	//---- COFINS ----------------------------------------------------
	public function tributocstcofinsAction(){
		
			$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 80) as $list);
	
		if(($list->visualizar==1)):
		$this->view->objIns		= $list->inserir;
		$this->view->objEdi		= $list->editar;
		$this->view->objList 	= TributosBO::listaCstcofins();
		else:
		$this->_redirect("/admin/cadastro/erro");
		endif;
	}
	
	public function tributocstcofinscadAction(){
		
			$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 80) as $list);
	
		if(($list->visualizar==1)):
		$this->view->objIns		= $list->inserir;
		$this->view->objEdi		= $list->editar;
		$this->view->objList 	= TributosBO::buscaCstcofins($this->_getAllParams());
		else:
		$this->_redirect("/admin/cadastro/erro");
		endif;
	}
	
	public function gravatributocstcofinsAction(){
		TributosBO::gravarCstcofins($this->_getAllParams());
		$this->_redirect("/admin/cadastro/tributocstcofins");
	}
	
	public function removetributocstcofinsAction(){
		TributosBO::removeCstcofins($this->_getAllParams());
		$this->_redirect("/admin/cadastro/tributocstcofins");
	}
	
	//---- NCM ----------------------------------------------------
	public function produtosncmAction(){
		
			$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 80) as $list);
        
        if(($list->visualizar==1)):
         	$this->view->objVis		= $list->visualizar;
			$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;       

			$this->view->objList 	= TributosBO::listaNcm();
			
		else:		
			$this->_redirect("/admin/cadastro/erro");
        endif;		
	}
	
	public function produtosncmcadAction(){
		
			$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 80) as $list);
        
        if(($list->visualizar==1)){
        	$params	= $this->_getAllParams();
         	$this->view->objVis		= $list->visualizar;
			$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;       

			$this->view->objList 	= TributosBO::buscaNcm($this->_getAllParams());
			$this->view->objCsticms 	= TributosBO::listaCst();
			$this->view->objCstipi  	= TributosBO::listaCstipi();
			$this->view->objCstpis  	= TributosBO::listaCstpis();
			$this->view->objCstcofins  	= TributosBO::listaCstcofins();
			
			if(isset($params['res'])){ $this->view->objRes			= $params['res']; }
			
        }else{		
			$this->_redirect("/admin/cadastro/erro");
        }
		
	}
	
	public function gravaprodutosncmAction(){
	    $this->_helper->layout->disableLayout();
	    $params	= $this->_getAllParams();
		$res = TributosBO::gravarNcm($params);
        $this->_redirect("/admin/cadastro/produtosncmcad/res/".$res."/ncm/".md5($params['idncm']));
        
	}
	
	
	//---- HSCODE ----------------------------------------------------
	public function produtoshscodeAction(){
		
			$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 13) as $list);
	
		if(($list->visualizar==1)):
			$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
		
			$this->view->objList 	= TributosBO::listaHscode();
				
		else:
			$this->_redirect("/admin/cadastro/erro");
		endif;
	}
	
	public function produtoshscodecadAction(){
		
			$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 13) as $list);
	
		if(($list->visualizar==1)):
			$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
		
			$this->view->objList 	= TributosBO::buscaHscode($this->_getAllParams());
		else:
			$this->_redirect("/admin/cadastro/erro");
		endif;
	
	}
	
	public function gravaprodutoshscodeAction(){
		TributosBO::gravarHscode($this->_getAllParams());
		$this->_redirect("/admin/cadastro/produtoshscode");
	}
	
	//---- NCM Perfil Clientes----------------------------------------------------
	public function despesasfiscaisncmAction(){
		
			$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 80) as $list);
		$params	= $this->_getAllParams();
		
		if(($list->visualizar==1)):
			$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
			
			$this->view->objDesp	= TributosBO::buscaDespesasid($params['desp']);
			$this->view->objList 	= TributosBO::listaNcmclientes($params);
			foreach (TributosBO::buscaDespesasid($params['desp']) as $despesas);
			$this->view->objUf 		= EstadosBO::listarEstados($despesas->id_estados);
				
		else:
			$this->_redirect("/admin/cadastro/erro");
		endif;
	}
	
	public function despesasfiscaisncmcadAction(){
		
			$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 80) as $list);
		$params	= $this->_getAllParams();
		
		if(($list->visualizar==1)):
			$this->view->objIns			= $list->inserir;
			$this->view->objEdi			= $list->editar;
						
			$this->view->objList 		= TributosBO::buscaNcmclientes($params);			
			$this->view->objDesp		= TributosBO::buscaDespesasid($params['desp']);
			
			foreach (TributosBO::buscaDespesasid($params['desp']) as $despesas);
			$this->view->objUf 			= EstadosBO::listarEstados($despesas->id_estados);
			
			$this->view->objNcm			= TributosBO::buscaNcmlivresclientes($params);
			$this->view->objCsticms 	= TributosBO::listaCst();
			$this->view->objCstipi  	= TributosBO::listaCstipi();
			$this->view->objCstpis  	= TributosBO::listaCstpis();
			$this->view->objCstcofins  	= TributosBO::listaCstcofins();
		else:
			$this->_redirect("/admin/cadastro/erro");
		endif;
	
	}
	
	public function gravadespesasfiscaisncmAction(){
		$id = TributosBO::gravarNcmclientes($this->_getAllParams());
		$this->_redirect("/admin/cadastro/despesasfiscaisncm/desp/".md5($id));
	}
	
	
	public function produtosgpcompraAction(){
		
			$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 13) as $list);
        
        if($this->_request->isPost()):
        	GruposprodBO::gravarGruposcompra($this->_getAllParams());
        endif;
        
        if(($list->visualizar==1)):
         	$this->view->objVis		= $list->visualizar;
			$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;       

			$this->view->objList 	= GruposprodBO::listaGruposcompra();
		else:		
			$this->_redirect("/admin/cadastro/erro");
        endif;
		
	}
	
	public function logalteracoesAction(){
		
			$this->topoAction('cadastro');
		
		Zend_Paginator::setDefaultScrollingStyle('Sliding');
		Zend_View_Helper_PaginationControl::setDefaultViewPartial('correio/paginator.phtml');
		$paginator = Zend_Paginator::factory(LogBO::listalogAlteracoes(""));
		$currentPage = $this->_getParam('page', 1);
		$paginator->setCurrentPageNumber($currentPage)
				  ->setItemCountPerPage(20);
		
		$this->view->objList 	= $paginator;
	}
	
	public function logalteracoesverAction(){
		
			$this->topoAction('cadastro');
		$this->view->objList 	= LogBO::listalogAlteracoes($this->_getAllParams());
	}
	
	public function verificalogalteracoesAction(){
		
			$this->topoAction('cadastro');
		$this->view->objList 	= LogBO::marcarlogAlteracoes($this->_getAllParams());
		$this->_redirect("/admin/cadastro/logalteracoes");
	}
	
	
	//---Cadastro de produtos--------------------------------------
	public function produtoscadAction(){
	    
		date_default_timezone_set('America/Sao_Paulo'); 
		$this->view->translate	=	Zend_Registry::get('translate');
				
		$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 13) as $list);
        
        $params = $this->_getAllParams();
        
        if(($list->visualizar==1)){       
            
	        if(!empty($params['produto'])):

	        	$params['tipo'] = 2;
	        	foreach (ProdutosBO::buscaProduto($params) as $listprod);
        		$idproduto 	= $listprod->ID;
        		$idsubgrupo = $listprod->id_gruposprodsub;
        		$codprod	= $listprod->CODIGO;
        		$fornkang 	= $listprod->id_cliente_fornecedor_shuntai;
        		$forntai 	= $listprod->id_cliente_shuntai;


	        	$this->view->objProd	= $listprod; // ProdutosBO::listaProdutos($idproduto);
	        	$this->view->objSubg	= GruposprodBO::buscaSubgrupo($idsubgrupo);
				$this->view->objVeic	= ProdutosBO::listarVeiculosprod($idproduto);
				$this->view->objRel		= ProdutosBO::listarRelacionamentos($idproduto);

				$this->view->objMedida	= ProdutosBO::buscaMedidasprod($idproduto);

				$this->view->objComp	   = ProdutosBO::listarKitprodchina($idproduto);
				$this->view->objHisc	   = ProdutosBO::listarHistoricofornchina($idproduto);
				$this->view->objMaterial   = ProdutosBO::listaMaterial();

				$arraybysca = array('idprod'    => $idproduto, 'forn' => $fornkang);
				$this->view->objCodigocross     = ProdutosBO::listaFornecedoresprodcross($arraybysca);

				$arraybysca = array('idprod'    => $idproduto, 'forn' => $forntai);
				$this->view->objCodigocrosstai  = ProdutosBO::listaFornecedoresprodcross($arraybysca);

				foreach (GruposprodBO::buscaSubgrupo($idsubgrupo) as $listsubg);
				$this->view->objSubgp	= GruposprodBO:: listaGruposprodutossub($listsubg->id_gruposprod);

				LogBO::cadastraLog("Cadastro/Produtos",1,$usuario->id,"","COD ".$codprod);
	        endif;
        	
        	$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
			$this->view->objPri		= $list->aba1;
			$this->view->objVen		= $list->aba2;
			$this->view->objCom		= $list->aba3;
			$this->view->objTec		= $list->aba4;
			$this->view->objCro		= $list->aba5;
			
			$this->view->objNcm		= TributosBO::listaNcm();
			$this->view->objGrupo	= GruposprodBO::listaGruposprodutos();
			$this->view->objGrupcom = GruposprodBO::listaGruposcompra();
			$this->view->objHscode 	= TributosBO::listaHscode();
			$this->view->objFor		= ClientesBO::buscaParceiros("fornecedorchines");
			
			if(isset($params['aba'])) $this->view->aba	 	= $params['aba'];  
			
        }else{		
			$this->_redirect("/admin/cadastro/erro");
		}
		
	}
	
	public function cadprodutoAction(){
		$this->_helper->layout->disableLayout();
		$usuario = Zend_Auth::getInstance()->getIdentity();
	
		$params = $this->_getAllParams();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 13) as $list);
			
		if($list->aba1 == 1){
			try{
				//-- submete aba principal ------------------------------------
	
				$produto = ProdutosBO::salvaPrincipal($params);
				$produto = explode("|", $produto);
					
				//marco se produto eh novo ---
				$params['novo'] = (isset($params['id_produto']) and $params['id_produto']!="") ? 0 : 1;
					
				if($produto[0] == true){
	
					$resp = 1;
					$erros = "";
	
					$params['id_produto'] = $produto[1];
	
					if($list->aba3 == 1){
						try{
							ProdutosBO::salvaCompra($params);
						}catch (Exception $e){
							$erros .= "Compra: ".$e->getMessage()."<br>";
							$resp = 0;
						}
						
					}
	
					//-- salva historico -------------------------
					try{
						ProdutosBO::salvaHistoricos($params);
					}catch (Exception $e){
						$erros .= "Histórico: ".$e->getMessage();
						$resp = 0;
					}
					
					echo $resp."|".str_replace("'", "", str_replace('"', '', $erros));
	
					$tp = (isset($params['id_produto']) and $params['id_produto']!="") ? 4 : 2;
					LogBO::cadastraLog("Cadastro/Produtos",$tp,$usuario->id,$params['id_produto'],"COD '".$params['codigo']."'");
	
				}else{
					echo "erro|";
				}
					
			}catch (Exception $e){
				echo "erro|".$e->getMessage();
			}
		}
	
		exit();
	}

    public function getanexosAction(){
        $this->_helper->layout->disableLayout();

        try{
            $codProduto = $this->_getParam('codigo');

            if(!$codProduto) throw new Exception("Favor informar o código do produto");

            $ftp_server = "ftp.hbr.ind.br";
            $ftp_conn = ftp_connect($ftp_server) or die("Erro ao tentar conectar no servidor $ftp_server");
            ftp_login($ftp_conn, "hbr3", "admZtl24596");

            $file_list = ftp_nlist($ftp_conn, "public_html/public/sistema/upload/produtos/".$codProduto."/projetos/");


            $link = "http://hbr.ind.br/public/sistema/upload/produtos/".$codProduto."/projetos/";
            ?>

            <table style="width: 100%" class="tableStatic" id="archive">
                <tbody>
                <?php

                foreach ($file_list as $arquivo){
                    if($arquivo != "." && $arquivo != ".."){
                        ?>
                        <tr>
                            <td width="10%" style="text-align: center;">
                                <a href="<?php echo $link.$arquivo?>" target="_blank" >
                                    <img src="<?php echo $this->baseUrl()?>/public/sistema/imagens/icons/middlenav/paperclip.png" border="0" >
                                </a>
                            </td>
                            <td >
                                <a href="<?php echo $link.$arquivo?>" target="_blank" >
                                    <?php echo $arquivo?>
                                </a>
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>
                </tbody>
            </table>
            <?php
            ftp_close($ftp_conn);
        }catch (Exception $e) {
            echo "erro:" . $e->getMessage();
        }

        exit();
    }
	
	//-- atualiza produtos soap --------------------------------
	public function atualizaprodutoAction(){
		$this->_helper->layout->disableLayout();
		$usuario = Zend_Auth::getInstance()->getIdentity();
		 
		$params = $this->_getAllParams();
		try{
			 
			if($params['tp'] == 1){
				echo ProdutosimportaBO::atualizaProd($params['codigo']);
			}elseif($params['tp'] == 2){
				echo ProdutosimportaBO::atualizaImagens($params['codigo'], $params['idprod']);
				//ProdutosimportaBO::atualizaImagenscomponentes($params['idprod']);
			}elseif($params['tp'] == 3){
				echo ProdutosimportaBO::atualizaAnexos($params['codigo'], $params['idprod']);
				//ProdutosimportaBO::atualizaAnexoscomponentes($params['idprod']);
			}
			 
			LogBO::cadastraLog("Cadastro/Produtos",4,$usuario->id,$params['idprod'],"COD '".$params['codigo']."'");
		}catch (Exception $e){
			echo $e->getMessage();
		}
		 
		exit();
	}
	
	public function produtosatualizaAction(){
		 
		$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 13) as $list);
		 
		$params = $this->_getAllParams();		 
	}
	
	public function buscaprodutosatualizaAction(){
		$this->_helper->layout->disableLayout();
		ProdutosimportaBO::buscaProdutosdesatualizados();
		exit();
	}
	
	
	public function buscaveiculoAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		$idmont = explode("-",$params['idmont']);
		$this->view->objVeiculos	= ProdutosBO::listaVeiculos($idmont[0]);	
	}
	
	public function gravahistcompraAction(){
		$params = $this->_getAllParams();
		echo ProdutosBO::gravarHistoricoforn($params);
		//$this->_redirect("/admin/cadastro/produtoscad/produto/".md5($params['id_produto'])."/aba/2");
		exit();
	}
	
	public function removehistoricoAction(){
		$params = $this->_getAllParams();
		foreach (ProdutosBO::listaallProdutos() as $listprod):
        	if($params['produto'] == md5($listprod->ID)):
        		$idproduto 	= $listprod->ID;
        	endif;        	
        endforeach;
		
		ProdutosBO::removeHistoricofonr($params['idihistc']);
		$this->_redirect("/admin/cadastro/produtoscad/produto/".md5($idproduto)."/aba/2");
	}
		
	public function buscacomposicaoAction(){
		$this->_helper->layout->disableLayout();
		$this->view->translate	=	Zend_Registry::get('translate');
		
		$params = $this->_getAllParams();
		foreach (ProdutosBO::buscaProdutoscodigo($params['cod']) as $list);
		if(!empty($list->ID)):
			$this->view->objComp	= ProdutosBO::listarKitprod($list->ID);
		endif;	
	}
	
	public function buscacompveicAction(){
		$this->_helper->layout->disableLayout();
		$this->view->translate	=	Zend_Registry::get('translate');
		
		$params = $this->_getAllParams();
		foreach (ProdutosBO::buscaProdutoscodigo($params['cod']) as $list);
		if(!empty($list->ID)):
			$this->view->objVeic	= ProdutosBO::listarVeiculosprod($list->ID);
		else:
			$this->view->objConf	= 1;
		endif;	
	}
	
	//-- Listar materiaris dos produtos ----------------------------
	public function produtosmaterialAction(){
		
			$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 13) as $list);
	
		if($list->visualizar==1):
			$params = $this->_getAllParams();
			$this->view->objMaterial 		= ProdutosBO::listaMaterial();
			
			$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
		else:
			$this->_redirect("/admin/kang/erro");
		endif;	
	}
	
	//--Gravar material--------------------------------
	public function gravarprodmaterialAction(){
		$params = $this->_getAllParams();
		ProdutosBO::gravaMaterial($params);
		$this->_redirect('/admin/cadastro/produtosmaterial');
	}
	
	public function removematerialAction(){
		$params = $this->_getAllParams();
		ProdutosBO::removeMaterial($params);
		$this->_redirect('/admin/cadastro/produtosmaterial');
	}
	
		
	public function buscacrossporfornecedorAction(){
	    $this->_helper->layout->disableLayout();
	    $params = $this->_getAllParams();
	    
	    if(count(ProdutosBO::listaFornecedoresprodcross($params))>0):
	    	foreach (ProdutosBO::listaFornecedoresprodcross($params) as $prod);
	    	echo $prod->codigocross;
	    else:
	    	echo "Sem cross cadastrado";
	    endif;
	    
	    exit();
	}
	
	public function gravahistcomprachinaAction(){
		$params = $this->_getAllParams();
		echo ProdutosBO::gravarHistoricofornchina($params);
		//$this->_redirect("/admin/cadastro/produtoschinacad/produto/".md5($params['id_produto']));
		exit();
	}
	
	public function removehistoricochinaAction(){
		$params = $this->_getAllParams();
		echo ProdutosBO::removeHistoricofonrchina($params['idihistc']);
		
		
		//$this->_redirect("/admin/cadastro/produtoschinacad/produto/".md5($idproduto));
		exit();
	}
	
	
	//------ Referencia Cruzada -------------------------------------
	public function referenciacruzadaAction(){
		date_default_timezone_set('America/Sao_Paulo'); 
		$this->view->translate	=	Zend_Registry::get('translate');
		
		
			$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 9) as $listPer);
        
		if(($listPer->visualizar==1)){
        	$params = $this->_getAllParams();
			if(!empty($params['buscacod'])):
				$this->view->objCross		= ProdutosBO::buscaCross($params);
				$this->view->objCodigo		= $params['buscacod'];
				$this->view->objFabrica		= ProdutosBO::buscaFabricas($params);
				
				if($params['fabrica']==1):
					foreach (ProdutosBO::buscaProdutoscodigo($params['buscacod']) as $list);
					if(empty($list)):
						$this->view->objZtl	= 1; 
					endif;
				endif;
				
				LogBO::cadastraLog("Adm/Ref Cruzada",1,$usuario->id,$params['buscacod'],"COD ".$params['buscacod']);
				
			endif;
			
			$this->view->objFabricas	= ProdutosBO::listaFabricas();
			$this->view->objHistb		= ProdutosBO::listaHistoricosbusca();
			$this->view->objIns			= $listPer->inserir;
			$this->view->objEdi			= $listPer->editar;
			$this->view->objRel			= $listPer->aba1;           
			
			if(isset($params['rep'])) $this->view->objRes = $params['rep'];
		}else{		
			$this->_redirect("/admin/cadastro/erro");
		}
	}
	
	
	//------ Referencia Cruzada Cadastro ------------------------------------
	public function cadreferenciasAction(){
		
		$this->topoAction('cadastro');
		
		$params = $this->_getAllParams();
		$return = ProdutosBO::gravaReferencias($params);
		
		if($return == true){
			$codcross	= explode(":", $params['codigonovo']);
			$this->_redirect("/admin/cadastro/referenciacruzada/buscacod/".$codcross[0]."/fabricamd/".md5($codcross[1])."/rep/sucesso");
		}else{
		    $this->_redirect("/admin/cadastro/referenciacruzada");
		}
		//exit();
	}
	
	//------ Referencia Cruzada Cadastro Produtos ------------------------------------
	public function referenciacruzadacadAction(){
		$sessaobusca = new Zend_Session_Namespace('Default');
		
			$this->topoAction('cadastro');
		$params = $this->_getAllParams();
		
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 9) as $listPer);
        
		if(($listPer->visualizar==1)):		
			$this->view->objFabricas	= ProdutosBO::listaFabricas();
			
			if($this->_request->isPost()):
				$this->view->objProdutos	= ProdutosBO::listaCodigoscrossbusca($params);
				$this->view->objCodigo		= $params['buscacod'];
				$this->view->objFabrica		= $params['buscafabrica'];
			else:	
				$this->view->objProdutos	= ProdutosBO::listaCodigoscross($params);
			endif;
			$this->view->prodEdit		= $params['proded'];
			$this->view->objIns			= $listPer->inserir;
			$this->view->objEdi			= $listPer->editar;       
			//$this->view->objExten		= $sessaobusca->tr;
			
		else:		
			$this->_redirect("/admin/cadastro/erro");
        endif;
	}
	
	//------ Referencia Cruzada Relatorios ------------------------------------
	public function referenciacruzadarelAction(){
		
			$this->topoAction('cadastro');
		$params = $this->_getAllParams();
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 9) as $listPer);
        
		if(($listPer->visualizar==1) and ($listPer->aba1==1)):
			$this->view->objFabricas	= ProdutosBO::listaFabricas();
			if($this->_request->isPost()):
				$this->view->objProdutos	= ProdutosBO::relatorioCodigoscross($params);
			endif;
			
			$this->view->objVis			= $listPer->visualizar;
			$this->view->objIns			= $listPer->inserir;
			$this->view->objEdi			= $listPer->editar;       
			
		else:		
			$this->_redirect("/admin/cadastro/erro");
        endif;
	}
	
	public function referenciacruzadarelxlsAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		$this->view->translate	=	Zend_Registry::get('translate');
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 9) as $listPer);
        
		if(($listPer->visualizar==1) and ($listPer->aba1==1)):
			$this->view->objProdutos	= ProdutosBO::relatorioCodigoscross($params);
			$this->view->tipo			= $params['tipo'];
		else:		
			$this->_redirect("/admin/cadastro/erro");
        endif;
	}
	
	public function referenciacruzadarelimpAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		$this->view->translate	=	Zend_Registry::get('translate');
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 9) as $listPer);
        
		if(($listPer->visualizar==1) and ($listPer->aba1==1)):
			$this->view->objProdutos	= ProdutosBO::relatorioCodigoscross($params);
		else:		
			$this->_redirect("/admin/cadastro/erro");
        endif;
	}
	
	public function cadcodigoprodfabricasAction(){
	    $this->_helper->layout->disableLayout();
	    ProdutosBO::gravarCodigosrefcruzada($this->_getAllParams());
		$this->_redirect("/admin/cadastro/referenciacruzadacad");
		exit();
	}
	
	public function remcodigoprodfabricasAction(){
		$params = $this->_getAllParams();
		ProdutosBO::removeCodigosrefcruzada($params['idcodigo']);
		$this->_redirect("/admin/cadastro/referenciacruzadacad");
	}
	
	public function corrigeAction(){
		$this->_helper->layout->disableLayout();
		
		$params = $this->_getAllParams();
		
		EstadosBO::corrigeCidadecontatos($params);
		
		$this->view->objUf		= EstadosBO::buscaEstados(1);
		$this->view->objUfsel 	= $params['uf']; 
		
		$this->view->objCid		= EstadosBO::buscaCidadesidestado($params['uf']);
		$this->view->objCidant  = EstadosBO::buscaCidadesidestadotemporario($params['uf']);
		
	}
	
	public function buscarefcruzadaAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		$this->view->objCross		= ProdutosBO::buscaCross($params);		
	}
	
	public function buscatipoparceiroAction(){
		$this->_helper->layout->disableLayout();
		$this->view->translate	=	Zend_Registry::get('translate');
		$params = $this->_getAllParams();
		if($params['tipo']==1) $array['rep']			= 1;
		if($params['tipo']==2) $array['ger']			= 1;
		if($params['tipo']==3) $array['func']			= 1;
		if($params['tipo']==4) $array['cliente']		= 1;
		
		$this->view->objClientes		= ClientesBO::listaemailsAllclientes($array);		
	}
	
	public function buscadespesasfiscaisAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		
		$arrayuf['iduf'] = md5($params['uf']);
		$this->view->objTributos	= TributosBO::buscaDespesas($arrayuf);
	}
	
	
	//--Paises/estados/cidades----------------------------------------
	public function paisesAction(){
		
			$this->topoAction('cadastro');
		$this->view->translate	= Zend_Registry::get('translate');
		$this->view->objList	= EstadosBO::listaPaises();
		
		$usuario = Zend_Auth::getInstance()->getIdentity();
		LogBO::cadastraLog("Cadastro/Paises",1,$usuario->id,"","");
	}
	
	public function cadastrapaisesAction(){
		EstadosBO::cadastraPais($this->_getAllParams());
		$this->_redirect("/admin/cadastro/paises");	
	}
	
	public function estadosAction(){
		
		$this->topoAction('cadastro');
		$this->view->translate	=	Zend_Registry::get('translate');
		$params = $this->_getAllParams();
		$this->view->objPais	= EstadosBO::buscaPaises($params);
		$this->view->objList	= EstadosBO::buscaEstadosmd($params['idpais']);
		
		$usuario = Zend_Auth::getInstance()->getIdentity();
		LogBO::cadastraLog("Cadastro/Estados",1,$usuario->id,"","");
	}
	
	public function cadastraestadoAction(){
		$params = $this->_getAllParams();
		EstadosBO::cadastraEstados($params);
		$this->_redirect("/admin/cadastro/estados/idpais/".md5($params['idpais']));	
	}
	
	public function cidadesAction(){
		
			$this->topoAction('cadastro');
		$this->view->translate	=	Zend_Registry::get('translate');
		$params = $this->_getAllParams();
		$this->view->objUf		= EstadosBO::buscaEstadosuf($params);
		$this->view->objList	= EstadosBO::buscaCidades($params);
		
		$usuario = Zend_Auth::getInstance()->getIdentity();
		LogBO::cadastraLog("Cadastro/Cidades",1,$usuario->id,"","");
	}
	
	public function cadastracidadesAction(){
		$params = $this->_getAllParams();
		EstadosBO::cadastraCidades($params);
		$this->_redirect("/admin/cadastro/cidades/uf/".$params['uf']);	
	}
	
	public function contatosAction(){
		
		$this->topoAction('cadastro');	
		$usuario = Zend_Auth::getInstance()->getIdentity();
		
		
		foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $listperfil);
		$this->view->objNivel = $listperfil->nivel;
		
		$this->view->objUf		= EstadosBO::buscaEstados(1);
		
		$this->view->objEmpm	= ContatosBO::buscaQtempresas("",1);
		$this->view->objEmpa	= ContatosBO::buscaQtempresas("",2);
		$this->view->objEmpn	= ContatosBO::buscaQtempresas("",3);
		$this->view->objEmpp	= ContatosBO::buscaQtempresas("",4);
		
		$this->view->objCont	= ContatosBO::buscaQtcontatos();
		$this->view->objLista	= ContatosBO::listaCampanhas();
		
		LogBO::cadastraLog("Cadastro/Contatos",1,$usuario->id,"","");
	}
	
	public function contatosestadoAction(){
		$params = $this->_getAllParams();
		$this->view->objUf		= $params['uf'];
		
		
			$this->topoAction('cadastro');
		
		$this->view->objEmp		= ContatosBO::listaMatrizendereco($params);
	}
	
	public function contatoscadAction(){
		
			$this->topoAction('cadastro');
		$params = $this->_getAllParams();		
		$usuario = Zend_Auth::getInstance()->getIdentity();
		$this->view->translate	=	Zend_Registry::get('translate');
		
		foreach (PerfilBO::listarPerfil($usuario->id_perfil, 11) as $list);
		$this->view->objInterno	= $list->interno;
		
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 11) as $listPer);
		
        if($listPer->visualizar==1):
			$this->view->objIns			= $listPer->inserir;
			$this->view->objEdi			= $listPer->editar;  
			$this->view->objEditar		= $params['ed'];
						
			$this->view->objUf			= EstadosBO::buscaEstados(1);
			$this->view->objPaises		= EstadosBO::listaPaises();
			$this->view->objCidades		= EstadosBO::listaCidades();
			$this->view->objGinteresse	= ContatosBO::listaGrupointeresse();
			$this->view->objEmpresa		= ContatosBO::buscaEmpresa($params['empresa'], $params['tp']);
			$this->view->objContato		= ContatosBO::buscaContatos($params);
			$this->view->objTipo		= $params['tp'];
			$this->view->objConf		= $params['res'];
		else:		
			$this->_redirect("/admin/cadastro/erro");
        endif;
	}
	
	public function contatosempAction(){
		
		$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
		
		foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $listperfil);
		$this->view->objNivel = $listperfil->nivel;
		
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 11) as $listPer);
		
        if($listPer->visualizar==1){
        
           	$this->view->objUf			= EstadosBO::buscaEstados(1);
			$this->view->objRegioes		= RegioesBO::listaRegioesclientes();
			$this->view->objGinteresse	= ContatosBO::listaGrupointeresse();
			$this->view->objTel			= RegioesBO::buscaRegioestelevendas();
			
			$this->view->objIns			= $listPer->inserir;
			$this->view->objEdi			= $listPer->editar;  
			
        }else{		
			$this->_redirect("/admin/cadastro/erro");
        }
	}
	
	public function buscarcontatosempAction(){
	    $this->_helper->layout->disableLayout();
	    try{
			ContatosBO::listaEmpresas($this->_getAllParams());
		}catch (Zend_Exception $e){
	    	$boerr	= new ErrosModel();
	    	$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "CadastroController::buscarcontatosemp()");
	    	$boerr->insert($dataerro);
	    		
	    	echo "erro";
	    }
	    exit();
	}
	
	public function buscarcontatosfilAction(){
		$this->_helper->layout->disableLayout();
		try{
			ContatosBO::listaFiliais($this->_getAllParams());
		}catch (Zend_Exception $e){
			$boerr	= new ErrosModel();
			$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "CadastroController::buscarcontatosfil()");
			$boerr->insert($dataerro);
			 
			echo "erro";
		}
		exit();
	}
	
	public function buscarcontatosAction(){
		$this->_helper->layout->disableLayout();
		try{
		    ContatosBO::listaContatos($this->_getAllParams());
		}catch (Zend_Exception $e){
			$boerr	= new ErrosModel();
			$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "CadastroController::buscarcontatos()");
			$boerr->insert($dataerro);
			 
			echo "erro";
		}
		exit();
	}
	
	
	public function contatosimpAction(){
	    $this->_helper->layout->disableLayout();
	    
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 11) as $listPer);
	
		if($listPer->visualizar==1):
			$params = $this->_getAllParams();
			$sessaobusca = new Zend_Session_Namespace('Contatos');
			$tipopesq = $sessaobusca->tipoPesq;
			$this->view->objPesq 	= $tipopesq;
		
			if($tipopesq == 1):
				$this->view->objEmp = ContatosBO::buscaContatos($params);
			else:
				$this->view->objEmp = ContatosBO::listaEmpresascontatos($params);
			endif;
				
			$this->view->objFil			= ContatosBO::listaFiliascontatos($params);
			$this->view->objCom			= ContatosBO::listarContatosmatriz($params);
			$this->view->objCof			= ContatosBO::listarContatosfilial($params);
									
		else:
			$this->_redirect("/admin/cadastro/erro");
		endif;
	}
	
	public function contatosrelAction(){
	
		$this->topoAction('cadastro');
		$this->view->translate	=	Zend_Registry::get('translate');
	
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 11) as $listPer);
	
		if($listPer->visualizar==1):
			$params = $this->_getAllParams();
			$sessaobusca = new Zend_Session_Namespace('Contatos');
			$sessaorel = new Zend_Session_Namespace('Contatosrel');
			
			//-- limpa o filtro para busca geral --------------------------------------------
			if(isset($params['filrel']) and ($params['filrel']=='todos')) Zend_Session::namespaceUnset('Contatos');
			
			//-- limpa o filtro para busca referente ao relatorio --------------------------------------------
			if(isset($params['fil']) and ($params['fil']=='todos')) Zend_Session::namespaceUnset('Contatosrel'); 
			
			
			if((isset($params['filrel']) and $params['filrel']=='todos') and (isset($params['fil']) and $params['fil']=='todos')){
			    $sessaorel->contatossel = "";
			    $this->_redirect("/admin/cadastro/contatosrel");
			}else{
				//-- marca a busca para trazer somente contatos relacionado no relatorio -------------------------------------
				//if(!isset($sessaorel->contatossel)) $sessaorel->contatossel	 = 1;
				if($sessaorel->contatossel == 1) $params['contatosrel'] = substr($sessaorel->contatosrel,0,-1);
			}
			
			Zend_Paginator::setDefaultScrollingStyle('Sliding');
			Zend_View_Helper_PaginationControl::setDefaultViewPartial('cadastro/paginatorcontatos.phtml');
				
			$paginator = Zend_Paginator::factory(ContatosBO::buscaContatos($params));
			
			
			$paginator 	->setCurrentPageNumber($this->_getParam('page', 1))
						->setItemCountPerPage(10);
				
			$this->view->objEmp 		= $paginator;
			$this->view->objUf			= EstadosBO::buscaEstados(1);
			$this->view->objRegioes		= RegioesBO::listaRegioesclientes();
			$this->view->objGinteresse	= ContatosBO::listaGrupointeresse();
			
			if(isset($sessaorel->contatosrelid)):
				$this->view->objContcamp	= ContatosBO::contarContatoscampanha($sessaorel->contatosrelid);
			endif;
			
						
			$this->view->contSelecionados 	= $sessaorel->contatosrel;
			$this->view->contNome			= $sessaorel->contatosrelnome;
			$this->view->contId				= $sessaorel->contatosrelid;
			$this->view->dtRel				= $sessaorel->contatosreldt;
			$this->view->checkSel			= $sessaorel->contatossel;
			
		else:
			$this->_redirect("/admin/cadastro/erro");
		endif;
	}
	
	public function gravacontatosrelAction(){
	    try{
			$this->_helper->layout->disableLayout();
			
			$sessaorel = new Zend_Session_Namespace('Contatosrel');		
			$params	= $this->_getAllParams();
			
			$ids = ','.$sessaorel->contatosrel;
			$contrel = explode(',', $params['contatosids']);
			
			foreach ($contrel as $idcont):
				if(!empty($idcont)) $ids = str_replace(','.$idcont.',', ",", $ids);
			endforeach;
			
			$sessaorel->contatosrel = substr($ids,1).$params['contsel'];
			
			echo $sessaorel->contatosrel;
			
			if(isset($params['idcampanha'])):
				if($params['selecionados']):
					$sessaorel->contatossel = 1;					
				else:
					$sessaorel->contatossel = 0;
				endif;
				$this->_redirect("/admin/cadastro/contatosrel");
			endif;
			
			if(isset($params['id'])):
				ContatosBO::salvaCampanha($params);
			endif;
	    }catch (Zend_Exception $e){
	        echo "erro";
	        
	        $boerro	= new ErrosModel();
	        $dataerro = array('descricao' => $e->getMessage(), 'pagina' => 'CadastroControler::gravacontatosrel()');
	        $boerro->insert($dataerro);
	    }		
		exit();
	}
	
	public function contatosrelsalvosAction(){
	
		$this->topoAction('cadastro');
		
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 11) as $listPer);
	
		if($listPer->visualizar==1):
			
			Zend_Paginator::setDefaultScrollingStyle('Sliding');
			Zend_View_Helper_PaginationControl::setDefaultViewPartial('correio/paginator.phtml');
			$paginator = Zend_Paginator::factory(ContatosBO::listaCampanhas($this->_getAllParams()));
				
			$currentPage = $this->_getParam('page', 1);
			$paginator 	
				->setCurrentPageNumber($currentPage)
				->setItemCountPerPage(10);
		
			$this->view->objRelatorios 		= $paginator;
		else:
			$this->_redirect("/admin/cadastro/erro");
		endif;
	}
		
	public function salvarcontatosrelAction(){
	    $this->_helper->layout->disableLayout();
	    ContatosBO::salvaCampanha($this->_getAllParams());
	    
	    $this->_redirect("/admin/cadastro/contatosrelsalvos");
	    exit();
	}
	
	public function abrircontatosrelAction(){
	    $this->_helper->layout->disableLayout();
	    ContatosBO::buscaCampanha($this->_getAllParams());
	    $this->_redirect("/admin/cadastro/contatosrel");
	    exit();
	}	
	
	public function removecontatosrelAction(){
		$this->_helper->layout->disableLayout();
		ContatosBO::removeCampanhas($this->_getAllParams());
		$this->_redirect("/admin/cadastro/contatosrelsalvos");
		exit();
	}
	
	public function contatosrelimpAction(){
	    $this->_helper->layout->disableLayout();
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 11) as $listPer);
	
		if($listPer->visualizar==1):
			$params = $this->_getAllParams();
			
			$sessaorel = new Zend_Session_Namespace('Contatosrel');
			$params['contatosrel'] = substr($sessaorel->contatosrel,0,-1);
				
			$this->view->objEmp 		= ContatosBO::buscaContatos($params);
			
		else:
			$this->_redirect("/admin/cadastro/erro");
		endif;
	}
	
	public function contatosrelimpconfAction(){
		$this->_helper->layout->disableLayout();
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 11) as $listPer);
	
		if($listPer->visualizar==1):
			$params = $this->_getAllParams();
				
			$sessaorel = new Zend_Session_Namespace('Contatosrel');
			$params['contatosrel'] = substr($sessaorel->contatosrel,0,-1);
		
			$this->view->objEmp 		= ContatosBO::buscaContatos($params);
			
		else:
			$this->_redirect("/admin/cadastro/erro");
		endif;
	}
	
	public function contatosinteracoesAction(){
	    $this->topoAction('cadastro');
	    $params = $this->_getAllParams();
	    
	    $this->view->objRegioes		= RegioesBO::listaRegioesclientes();
	    $this->view->objRegtelvend	= RegioesBO::buscaRegioestelevendas();
	    
	    if(isset($params['pendentes'])){
	        $this->view->pend = 1;
	    }
	    
	}
	
	public function buscacontatosinteracoesAction(){
	    $this->_helper->layout->disableLayout();
	    ContatosBO::exibeInteracoes($this->_getAllParams());
	    exit();
	}
	
	public function contadorinteracoesAction(){
	    $this->_helper->layout->disableLayout();
		echo count(ContatosBO::buscaInteracoes($this->_getAllParams()));
		exit();
	}
	
	public function contatoscampanhaAction(){
		
		$this->topoAction('cadastro');
		$this->view->translate	=	Zend_Registry::get('translate');
	
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 11) as $listPer);
	
		if($listPer->visualizar==1):
		$params = $this->_getAllParams();
	
		if($params['fil']=='todos'):
			$sessaobusca = new Zend_Session_Namespace('Contatos');
			$sessaobusca->where = "";
		endif;
		 
		$this->view->objList 	= ContatosBO::campanhaEmbreagem($params);
		$this->view->objRegioes		= RegioesBO::listaRegioesclientes();
					
		$this->view->objIns			= $listPer->inserir;
		$this->view->objEdi			= $listPer->editar;
			
		else:
		$this->_redirect("/admin/cadastro/erro");
		endif;
	}
	
	public function contatosvalidaAction(){
	    $this->_helper->layout->disableLayout();
		$params	= $this->_getAllParams();
		ContatosBO::validarContatos($params);
		
		exit();
	}
	
	public function contatosempcadAction(){
		
		$this->topoAction('cadastro');
		$params	= $this->_getAllParams();		
		$usuario = Zend_Auth::getInstance()->getIdentity();
		
		foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
		
		$this->view->objInterno	= $list->interno;
		$this->view->nivel		= $list->nivel;
		
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 11) as $listPer);
		
        if($listPer->visualizar==1):
			$this->view->translate		= Zend_Registry::get('translate');
			
			$this->view->objPaises		= EstadosBO::listaPaises();
			$this->view->objRegioes		= RegioesBO::listaRegioesclientes(1);
			$this->view->objRegtelvend	= RegioesBO::buscaRegioestelevendas();
			
		 	$this->view->objClientes	= ContatosBO::listaClientesnaocadcontato();
		 	$this->view->objGinteresse	= ClientesBO::buscaClientesgrupos();
						
		 	$this->view->objGrupo 		= GruposprodBO::listaGruposprodutos();
		 	
			if(!empty($params['empresa'])):
				$this->view->objEmpresa		= ContatosBO::buscaEmpresa($params['empresa']);
				foreach (ContatosBO::buscaEmpresa($params['empresa']) as $empresa);
				
				if(!empty($empresa->id_estados)):  		$this->view->objCidades		= EstadosBO::buscaCidadesidestado($empresa->id_estados); endif;
				if(!empty($empresa->id_paises)): 		$this->view->objUf 		= EstadosBO::buscaEstados($empresa->id_paises); endif;
				
				//-- conta filiais para habilitar a mudanca para matriz/filial ----------------------- 
				$this->view->qtFiliais = ContatosBO::buscaFiliais($params['empresa']);
								
			endif;
			
			$this->view->objIns			= $listPer->inserir;
			$this->view->objEdi			= $listPer->editar;  
			$this->view->idMatriz 		= (isset($params['idmatriz'])) ? $params['idmatriz'] : null;
			
		else:		
			$this->_redirect("/admin/cadastro/erro");
        endif;
	}
	
	public function buscacontatosempAction(){
	    $this->_helper->layout->disableLayout();
	    $params	= $this->_getAllParams();
	    $usuario = Zend_Auth::getInstance()->getIdentity();
	    
	    //--- busco perfil do usuario que estah logado -------------------------------------
	    foreach (PerfilBO::listarPerfil($usuario->id_perfil, 11) as $list);
	    $this->view->objInterno	= $list->interno;
	    	    
	    foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 11) as $listPer);
	    $this->view->objEdi			= $listPer->editar;
	    
	    $this->view->tp	= $params['tp'];
	    if($params['tp'] == "int"):
	    	$this->view->objInter		= ContatosBO::listaInteracoesemp($params);
	    elseif($params['tp'] == "com"):
	    	$this->view->objInter		= ContatosBO::buscaInteracaoemp($params);
	    	$this->view->objComent		= ContatosBO::buscaComentariosemp($params);
	    elseif($params['tp'] == "mov"):
	    	if(!empty($params['cliente'])):
			    $busca['buscacli'] 					= $params['cliente'];
			    $this->view->objMovimentacao		= NfeBO::listaNfe($busca);
			endif;		    
		elseif($params['tp'] == "fin"):
			if(!empty($params['cliente'])):
				$busca = array(
					'tipo' 		=> 2,
				    'buscafor'	=> $params['cliente'],
				    'tpgroup'	=> 1,
				    'limite'	=> 10
				);			       
				
				$this->view->objContasreceber 	= FinanceiroBO::listaContasreceber($busca);
			endif;
		elseif($params['tp'] == "sit"):
			echo count(ContatosBO::buscaSitinteracaoemp($params));
		elseif($params['tp'] == "contato"):
			
			$arraycont['contato'] 	= md5($params['cont']);
			$arraycont['tpsession'] = 1;
			
			$this->view->objContato		= ContatosBO::buscaContatos($arraycont);
			$this->view->objUf			= EstadosBO::buscaEstados(1);
						
			if(count(ContatosBO::buscaContatos($arraycont))>0):
				foreach (ContatosBO::buscaContatos($arraycont) as $contato);
				
				if(($contato->ID_END==1)||(empty($this->objContato))):
					if($contato->TIPO == 0){
						$iduf			= $contato->miduf;
					}else{
						$iduf			= $contato->fiduf;
					}
				else:
					$iduf			= $contato->ciduf;
				endif;
			endif;
			
			$this->view->objCidades		= EstadosBO::buscaCidadesidestado($iduf);
			$this->view->objGinteresse	= ContatosBO::listaGrupointeresse();
			$this->view->objTipo		= $params['tipo'];
			$this->view->objEmpresa		= $params['empresa'];
			$this->view->quarentenaVer	= $params['quarentena'];
			
		elseif($params['tp'] == "filial"):
			$this->view->objClientes	= ContatosBO::listaClientesnaocadcontato();
			$this->view->objEmpresa		= ContatosBO::buscaFilial(md5($params['empresa']));
			$this->view->objGinteresse	= ClientesBO::buscaClientesgrupos();
			
			foreach (ContatosBO::buscaFilial(md5($params['empresa'])) as $empresa);
			$this->view->objUf			= EstadosBO::buscaEstados(1);
			$this->view->objCidades		= EstadosBO::buscaCidadesidestado($empresa->ciduf);
			
			$this->view->idMatriz		= $params['matriz'];
			
			if(!empty($params['novaempresa']) and $params['novaempresa'] != 'undefined'):
				$busca['idparceiro']		= $params['novaempresa'];
				$this->view->objEmpresa		= ClientesBO::buscaParceiros("",$busca);
				$this->view->objEndereco	= ClientesBO::listaEnderecocomp($params['novaempresa'], 1);
				$this->view->objNovaemp		= true;
				$this->view->idEmpresa		= $params['empresa'];
			endif;
			
			$this->view->quarentenaVer	= $params['quarentena'];
			
	    endif;
	}
	
	public function buscacontatosAction(){
	    $this->_helper->layout->disableLayout();
	    $params	= $this->_getAllParams();
	    
	    ContatosBO::contatoFom($params);
	    
	    exit();
	}
	
	public function gravacontatosAction(){
		$this->_helper->layout->disableLayout();
		ContatosBO::gravaContatos($this->_getAllParams());
		//$this->_redirect("/admin/cadastro/contatoscad/tp/".$params['tipo']."/empresa/".md5($params['idempresa'])."/res/sucesso");
		exit();
	}
	
	public function corrigecontatosAction(){
	    $this->_helper->layout->disableLayout();
		   
		exit();
	}
	
	public function removecontatoAction(){
	    $this->_helper->layout->disableLayout();
		ContatosBO::removeContato($this->_getAllParams());
		exit();
	}
	
	
	
	public function gravarcomentarioAction(){
	    $this->_helper->layout->disableLayout();
	    $params	= $this->_getAllParams();
	    ContatosBO::gravarComentarios($params);
	    exit();
	}	
	
	public function gravarinteracaoAction(){
		$this->_helper->layout->disableLayout();
		$params	= $this->_getAllParams();
		ContatosBO::gravarInteracao($params);
		exit();
	}
	
	public function gravaempresaAction(){
	    try{
		    $this->_helper->layout->disableLayout();
		    $params	= $this->_getAllParams();
			$id = ContatosBO::gravaEmpresa($this->_getAllParams());
			
			$this->_helper->flashMessenger->addMessage(array('Sucesso!'=>'Empresa cadastrada com sucesso!'));
	   		$this->_redirect("/admin/cadastro/contatosempcad/empresa/".md5($id));
	    }catch (Zend_Exception $e){
	        $this->_helper->flashMessenger->addMessage(array('Erro!'=>'Erro ao realizar cadastro!'));
	        
	        $boerro	= new ErrosModel();
	        $dataerro = array('descricao' => $e->getMessage(), 'pagina' => 'CadastroControler::gravaempresaAction()');
	        $boerro->insert($dataerro);
	    }
		exit();
	}
	
	public function agregaparceiroAction(){
		try{
			$this->_helper->layout->disableLayout();
			$params	= $this->_getAllParams();
			$id = ContatosBO::agregaParceiro($this->_getAllParams());
				
			$this->_helper->flashMessenger->addMessage(array('Sucesso!'=>'Empresa cadastrada com sucesso!'));
			$this->_redirect("/admin/cadastro/contatosempcad/empresa/".md5($id));
		}catch (Zend_Exception $e){
			$this->_helper->flashMessenger->addMessage(array('Erro!'=>'Erro ao realizar cadastro!'));
			 
			$boerro	= new ErrosModel();
			$dataerro = array('descricao' => $e->getMessage(), 'pagina' => 'CadastroControler::gravaempresaAction()');
			$boerro->insert($dataerro);
			
			$this->_redirect("/admin/cadastro/contatosempcad/empresa/".md5($id));
		}
		exit();
	}
	
	public function buscamatrizcontatosAction(){
	    $this->_helper->layout->disableLayout();
	    ContatosBO::listaMatrizselect();
	    exit();
	}
	
	public function defineempresaAction(){
	    try{
	    	$this->_helper->layout->disableLayout();
	    	$params	= $this->_getAllParams();
	    	$id = ContatosBO::defineTipoempresa($this->_getAllParams());
	    	
	    	$this->_redirect("/admin/cadastro/contatosempcad/empresa/".md5($id));
	    }catch (Zend_Exception $e){
	    	$this->_helper->flashMessenger->addMessage(array('Erro!'=>'Erro ao realizar cadastro!'));
	    
	    	$boerro	= new ErrosModel();
	    	$dataerro = array('descricao' => $e->getMessage(), 'pagina' => 'CadastroControler::defineempresaAction()');
	    	$boerro->insert($dataerro);
	    	
	    	$this->_redirect("/admin/cadastro/contatosemp");
	    }
	    exit();
	}
	
	public function removematrizAction(){
	    $this->_helper->layout->disableLayout();
	    try{
		    $params	= $this->_getAllParams();
			$id = ContatosBO::removeMatriz(md5($params['empresa']));
			
			$usuario = Zend_Auth::getInstance()->getIdentity();
			LogBO::cadastraLog("Cadastro/Matriz Contatos",3,$usuario->id,$id,"Matriz ID ".$id);			
			
	    }catch(Zend_Exception $e){
	        $boerro	= new ErrosModel();
	        $dataerro = array('descricao' => $e->getMessage(), 'pagina' => 'CadastroController::removematriz()');
	        $boerro->insert($dataerro);
	    }
	    exit();
	}
	
	public function removeempfilialAction(){
		$this->_helper->layout->disableLayout();
	    try{
		    $params	= $this->_getAllParams();
			$id = ContatosBO::removeMatriz(md5($params['empresa']));
			
			$usuario = Zend_Auth::getInstance()->getIdentity();
			LogBO::cadastraLog("Cadastro/Filial Contatos",3,$usuario->id,$id,"Filial ID ".$id);			
			
	    }catch(Zend_Exception $e){
	        $boerro	= new ErrosModel();
	        $dataerro = array('descricao' => $e->getMessage(), 'pagina' => 'CadastroController::removeempfilial()');
	        $boerro->insert($dataerro);
	        echo "erro";
	    }
	    exit();
	}
	
	public function contatosfilialcadAction(){
		
		$this->topoAction('cadastro');
		$params	= $this->_getAllParams();
		
		$usuario = Zend_Auth::getInstance()->getIdentity();
		
		foreach (PerfilBO::listarPerfil($usuario->id_perfil, 11) as $list);
		$this->view->objInterno	= $list->interno;
		
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 11) as $listPer);
		
        if($listPer->visualizar==1):
			$this->view->objIns			= $listPer->inserir;
			$this->view->objEdi			= $listPer->editar;  
			
			$this->view->translate		= Zend_Registry::get('translate');
			$this->view->objUf			= EstadosBO::buscaEstados(1);
			$this->view->objPaises		= EstadosBO::listaPaises();
			$this->view->objCidades		= EstadosBO::listaCidades();
			$this->view->objConf		= $params['res'];
			$this->view->objEditar		= $params['ed'];
			
			$this->view->objClientes	= ContatosBO::listaClientesnaocadcontato();
			
			if(!empty($params['matriz'])):
				$this->view->objMatriz		= ContatosBO::buscaEmpresa($params['matriz']);
			endif;
			
			if(!empty($params['empresa'])):
				$this->view->objEmpresa	= ContatosBO::buscaFilial($params['empresa']);
			endif;
			
			if(!empty($params['novaempresa'])):
				$busca['idparceiro']		= $params['novaempresa'];
				$this->view->objEmpresa		= ClientesBO::buscaParceiros("",$busca);
				$this->view->objEndereco	= ClientesBO::listaEnderecocomp($params['novaempresa'], 1);
				$this->view->objNovaemp		= true;
				$this->view->idEmpresa	= $params['idempresa'];
			endif;
			
		else:		
			$this->_redirect("/admin/cadastro/erro");
		endif;
	}
	
	public function gravafilialAction(){
	    $this->_helper->layout->disableLayout();
	    
		$params = $this->_getAllParams();
		ContatosBO::gravaFilial($this->_getAllParams());
		exit();
		//$this->_redirect("/admin/cadastro/contatosfilialcad/matriz/".md5($params['idmatriz'])."/empresa/".md5($params['idempresa'])."/res/sucesso");
	}
	
	public function removefilialAction(){
		$params	= $this->_getAllParams();
		ContatosBO::removeFilial($params['empresa']);
		$this->_redirect("/admin/cadastro/contatosfilialcad/matriz/".$params['matriz']."/empresa/".$params['empresa']);
	}
	
	
		
	public function buscaqtcontatosAction(){
		$this->_helper->layout->disableLayout();
		$params	= $this->_getAllParams();
		$this->view->objEmp		= ContatosBO::buscaQtempresas($params);
		$this->view->objCont	= ContatosBO::buscaQtcontatos($params);
		//$this->view->objCamp	= ContatosBO::buscaQtcontatos($params,1);
	}
	
	
	public function contatosempquarAction(){
		$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
	
		foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $listperfil);
		$this->view->objNivel = $listperfil->nivel;
	
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 11) as $listPer);
	
		if($listPer->visualizar==1):
			$params = $this->_getAllParams();
			$sessaobusca = new Zend_Session_Namespace('Contatosquar');
	
			if(($params['fil']=='todos') || ($this->_request->isPost())):
				$sessaobusca->where = "";
			endif;
	
			Zend_Paginator::setDefaultScrollingStyle('Sliding');
			Zend_View_Helper_PaginationControl::setDefaultViewPartial('correio/paginator.phtml');
			$paginator = Zend_Paginator::factory(ContatosBO::contatosQuarentena($params));
			$currentPage = $this->_getParam('page', 1);
			$paginator 	->setCurrentPageNumber($currentPage)
				->setItemCountPerPage(10);
				
			$this->view->objEmp 		= $paginator;
			
			
		else:
			$this->_redirect("/admin/cadastro/erro");
		endif;
	}
	
	
	function buscacontatosquarAction(){
	    $this->_helper->layout->disableLayout();
	    $params = $this->_getAllParams();
	    
	    if($params['tp'] == 0):  
	    	$this->view->objCom			= ContatosBO::listarContatosmatriz($params,'1');
	    elseif($params['tp'] == 1):
	    	$this->view->objCof			= ContatosBO::listarContatosfilial($params,'1');
	    elseif($params['tp'] == 2):
	    	$this->view->objFil			= ContatosBO::listaFiliascontatos($params,'1');	    	
	    endif;
	    
	    $this->view->tp = $params['tp'];
	}
	
	
	/*--- contatos em quarentena --------------------------------------------------*/
	public function contatosquarempAction(){
	
		$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
	
		$sessaobusca = new Zend_Session_Namespace('Contatosquarentena');
		
		foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $listperfil);
		$this->view->objNivel = $listperfil->nivel;
	
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 11) as $listPer);
	
		if($listPer->visualizar==1):
			$params = $this->_getAllParams();

			if(!empty($params['tp'])):
				$this->view->objTp		= $params['tp'];
				$sessaobusca->tp		= $params['tp'];
			else:
				$this->view->objTp		= $sessaobusca->tp;
				$params['tp']			= $sessaobusca->tp;
			endif;
		
			Zend_Paginator::setDefaultScrollingStyle('Sliding');
			Zend_View_Helper_PaginationControl::setDefaultViewPartial('correio/paginator.phtml');
			$paginator = Zend_Paginator::factory(ContatosBO::listaQuarentena($params));
			$currentPage = $this->_getParam('page', 1);
			$paginator 	->setCurrentPageNumber($currentPage)
						->setItemCountPerPage(10);
				
			$this->view->objEmp 		= $paginator;
			
		else:
			$this->_redirect("/admin/cadastro/erro");
		endif;
	}
		
	/*---- Regioes ---------------------------------------------------------------------
	 * Usado para delimitar areas de atuacao dos representantes e televendas ----------------------------
	 */
	public function regioesAction(){
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 12) as $listPer);
		
        if($listPer->visualizar==1):
			
			$this->topoAction('cadastro');
        	$this->view->objIns			= $listPer->inserir;
			$this->view->objEdi			= $listPer->editar;  
			$this->view->objList		= RegioesBO::listaRegioesclientes();
			
			$this->view->objRep			= ClientesBO::buscaParceiros("representantes");
			
		else:		
			$this->_redirect("/admin/cadastro/erro");
		endif;
	}
	
	public function gravaregioesAction(){
		RegioesBO::gravaRegioes($this->_getAllParams());
		$this->_redirect("/admin/cadastro/regioes");
	}
	
	public function removeregioesAction(){
		RegioesBO::removeRegioes($this->_getAllParams());
		$this->_redirect("/admin/cadastro/regioes");
	}
	
	public function regioestelevendasAction(){
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 12) as $listPer);
	
		if($listPer->visualizar==1):
			
			$this->topoAction('cadastro');
			$this->view->objIns			= $listPer->inserir;
			$this->view->objEdi			= $listPer->editar;
			
			$perfil['perfil'] = '31,35,29,4';
			$this->view->objRep			= UsuarioBO::buscaUsuario($perfil,'A');
			$this->view->objList		= RegioesBO::buscaRegioestelevendas();
				
		else:
			$this->_redirect("/admin/cadastro/erro");
		endif;
	}
	
	public function gravaregioestelevendasAction(){
		RegioesBO::gravaRegioestelevendas($this->_getAllParams());
		$this->_redirect("/admin/cadastro/regioestelevendas");
	}
	
	public function removeregioestelevendasAction(){
		RegioesBO::removeRegioestelevendas($this->_getAllParams());
		$this->_redirect("/admin/cadastro/regioestelevendas");
	}
	
	//--- Fabricantes de pecas ---------------------------------------------------------------------------
	public function fabricantesAction(){
		
		$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 16) as $list);
	
		if(($list->visualizar==1)):
			$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
	
			Zend_Paginator::setDefaultScrollingStyle('Sliding');
			Zend_View_Helper_PaginationControl::setDefaultViewPartial('correio/paginator.phtml');
			$paginator = Zend_Paginator::factory(VeiculosBO::listaFabricantes($this->_getAllParams()));
			$currentPage = $this->_getParam('page', 1);
			$paginator->setCurrentPageNumber($currentPage)
					  ->setItemCountPerPage(15);
				
			$this->view->objList 	= $paginator;
			LogBO::cadastraLog("Cadastro/Fabricantes",1,$usuario->id,'','');
		else:
			$this->_redirect("/admin/cadastro/erro");
		endif;
	}
	
	public function fabricantescadAction(){
		
			$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 16) as $list);
	
		if(($list->visualizar==1)):
			$this->view->objVis		= $list->visualizar;
			$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
		
			$this->view->objList 	= VeiculosBO::buscaFabricantes($this->_getAllParams());
			$this->view->objParc	= VeiculosBO::listaParceiro($this->_getAllParams());
		else:
			$this->_redirect("/admin/cadastro/erro");
		endif;
	
	}
	
	public function fabricantesgravaAction(){
		VeiculosBO::gravarFabricante($this->_getAllParams());
		$this->_redirect("/admin/cadastro/fabricantes");
	}
	
	public function fabricantesremAction(){
	    VeiculosBO::removeFabricantes($this->_getAllParams());
	    $this->_redirect("/admin/cadastro/fabricantes");
	}	
	
	//-- Usuarios ---------------------------------------------------------------------------------------------
	public function usuariosAction(){
		
		$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
		
		foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $listperfil);
		$this->view->objNivel = $listperfil->nivel;
		
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 82) as $list);
	
		if(($list->visualizar==1)):
			$this->view->objIns		= $list->inserir;
			$this->view->objEdi		= $list->editar;
		
			$var['ativo'] = 1;
			$this->view->objCliente	= ClientesBO::buscaParceiros("","","T");
			
			
			Zend_Paginator::setDefaultScrollingStyle('Sliding');
			Zend_View_Helper_PaginationControl::setDefaultViewPartial('correio/paginator.phtml');
			$paginator = Zend_Paginator::factory(UsuarioBO::buscaUsuario($this->_getAllParams()));
			$currentPage = $this->_getParam('page', 1);
			$paginator->setCurrentPageNumber($currentPage)
			->setItemCountPerPage(10);
			
			$this->view->objList 	= $paginator;
			
			
		else:
			$this->_redirect("/admin/cadastro/erro");
		endif;
	
	}
	
	public function usuarioscadAction(){
	    
	    $this->topoAction('cadastro');
	    $usuario = Zend_Auth::getInstance()->getIdentity();
	    
	    foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $listperfil);
	    $this->view->objNivel = $listperfil->nivel;
	    
	    foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 82) as $list);
	    
	    if(($list->visualizar==1)):
	    	$params = $this->_getAllParams();
	    	
		    $this->view->objPaises		= EstadosBO::listaPaises();
		    
		    $this->view->objPerfil 		= PerfilBO::listarPerfil();
		    $this->view->objRegioes		= RegioesBO::listaRegioesclientes();
		    $this->view->objRegtelv		= RegioesBO::buscaRegioestelevendas();
		    
		    if(count(UsuarioBO::buscaUsuario($params))>0 and (!empty($params['usermd']) || !empty($params['usuario']))){

				if(!empty($params['usuario'])) $params['usermd'] = md5($params['usuario']);

			    foreach (UsuarioBO::buscaUsuario($params) as $user);
			    $this->view->objEstados		= EstadosBO::buscaEstadosmd(md5($user->id_paises));
			    $this->view->objCidades		= EstadosBO::buscaCidadesidestado($user->id_estados);
			    
			    $this->view->objUser		= UsuarioBO::buscaUsuario($params);
			    $this->view->objArquser		= UsuarioBO::listaArquivosuser($params);
			    $this->view->objReguser		= RegioesBO::listaRegioesuser($params, 0);
			    $this->view->objRegtel		= RegioesBO::listaRegioesuser($params, 1);
			    $params['limite'] 			= 10;
			    $this->view->objLog			= LogBO::listaLog($params);			    
			}
		    
		    $this->view->objCliente	= ClientesBO::buscaParceiros("","","A");
		    
		    LogBO::cadastraLog("Cadastro/Usuarios",1,$usuario->id,$user->iduser,'Usuário ID '.$user->iduser);
		    
	    else:
	    	$this->_redirect("/admin/cadastro/erro");
	    endif;
	}
	
	public function gravausuarioAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		
		try {
			if(UsuarioBO::buscaEmail($params['email'], $params['idusuario']) and $params['email'] != ""){
				echo "erro2";
			}else{
				UsuarioBO::cadastraUsuario($params);
			}
		} catch (Zend_Exception $e) {
			$boerr	= new ErrosModel();
			$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "UsuarioBO::cadastraUsuario(".$params['idusuario'].")");
			$boerr->insert($dataerro);
			
			echo "erro";
		}
		exit();
	}
	
	public function verificaemailAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		echo UsuarioBO::buscaEmail($params['email']);
		exit();
	}
	
	public function uploadusuariosAction(){
		try {
			$this->_helper->layout->disableLayout();
			$params = $this->_getAllParams();
			UsuarioBO::uploadArquivos($params);
			
			$this->_helper->flashMessenger->addMessage(array('Sucesso!'=>'Arquivos anexados com sucesso!'));
			
			$this->_redirect("/admin/cadastro/usuarioscad/usuario/".$params['idusuario']);
			
		} catch (Zend_Exception $e) {
			$boerr	= new ErrosModel();
			$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "UsuarioBO::uploadArquivos(".$params['idusuario'].")");
			$boerr->insert($dataerro);
				
			$this->_helper->flashMessenger->addMessage(array('Erro!'=> $e->getMessage()));
		}
		exit();
	}
	
	public function gravarfaltasAction(){
	    $this->_helper->layout->disableLayout();
	    $params = $this->_getAllParams();
	    $iduser = UsuarioBO::gravaFaltas($params);
	    exit();
	}
	
	
	public function gravarferiasAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		$iduser = UsuarioBO::gravaFerias($params);
		exit();
	}
	
	public function usuariosremanexoAction(){
	    $params = $this->_getAllParams();
	    $iduser = UsuarioBO::removeAnexo($params);
	    $this->_redirect("/admin/cadastro/usuarioscad/user/".md5($iduser));
	}
	
	public function removefaltaAction(){
	    $this->_helper->layout->disableLayout();
	    $params = $this->_getAllParams();
	    $iduser = UsuarioBO::removeAusencias($params);
	    exit();
	}
	
	public function buscaausenciasAction(){
	    $this->_helper->layout->disableLayout();
	    $params = $this->_getAllParams();
	    $this->view->objFaltas		= UsuarioBO::listaAusencias($params);
	    $this->view->objUser		= UsuarioBO::buscaUsuario($params);
	    $this->view->objParams		= $params;
	}
	
	public function buscadesempenhoAction(){
		$this->_helper->layout->disableLayout();
		UsuarioBO::buscaValorvendas($this->_getAllParams());
		exit();
	}
	
	public function buscacontatosuserAction(){
		$this->_helper->layout->disableLayout();
		UsuarioBO::buscaInteracoesempresa($this->_getAllParams());
		exit();
	}
	//---Precadastro parceiros--------------
	public function precadastroAction(){
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 12) as $list);
			
		if($list->visualizar==1){
		    $params = $this->_getAllParams();
			$this->view->objEdi		= $list->editar;
							
			$this->topoAction('cadastro');
				
			Zend_Paginator::setDefaultScrollingStyle('Sliding');
			Zend_View_Helper_PaginationControl::setDefaultViewPartial('correio/paginator.phtml');
			$paginator = Zend_Paginator::factory(ClientesBO::listaPrecadastro());
			$currentPage = $this->_getParam('page', 1);
			$paginator->setCurrentPageNumber($currentPage)
				->setItemCountPerPage(10);
				
			$this->view->objList 	= $paginator;
			$this->view->tp			= $params['tp']; 
		}else{
			$this->_redirect("/admin/cadastro/erro");
		}
	
		LogBO::cadastraLog("Cadastro/Pre cadastro",1,$usuario->id,"","");
	}
	
	public function removeprecadastroAction(){
	    ClientesBO::removePrecadastro($this->_getAllParams());
	    $this->_redirect("/admin/cadastro/precadastro");
	}
	
	public function precadastrovisAction(){
		 
		$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
		 
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 12) as $list);
		 
		if(($list->visualizar==1)){
		    $params = $this->_getAllParams();
			$this->view->objCadastro		= ClientesBO::listaPrecadastro($params);
			$this->view->objArquser			= ClientesBO::listaArquivosprecadastro($params);
		}else{
			$this->_redirect("/admin/cadastro/erro");
		}
	}
	
	public function utilizarprecadastroAction(){
	    $this->_helper->layout->disableLayout();
	    $ret = ClientesBO::inserePrecadastro($this->_getAllParams());
	    if($ret != false) $this->_redirect("/admin/cadastro/parceiros/idparceiro/".md5($ret)."/idcad/true");
	    else $this->_redirect("/admin/cadastro/precadastro/tp/erro");
	    exit();
	}
	
	public function produtosajusteprecosAction(){
		$usuario = Zend_Auth::getInstance()->getIdentity();

		//--- Controle de perfil ------------------------------------------
		foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $listper);
		$this->view->objNivel = $listper->nivel;
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 77) as $list);
				
		if($list->visualizar==1){
            $this->topoAction('venda');
		}else{
			$this->_redirect("/admin/venda/erro");
		}
		
		LogBO::cadastraLog("Cadastro/Ajuste de preços",1,$usuario->id,"","");
	}
	
	public function buscaprodutosajusteprecoAction(){
		$this->_helper->layout->disableLayout();
		ProdutosBO::produtosAjustepreco($this->_getAllParams());
		exit();
	}
	
	public function montadorasAction(){
		$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
			
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 12) as $list);
			
		if(($list->visualizar==1)){
			$params = $this->_getAllParams();
					
		}else{
			$this->_redirect("/admin/cadastro/erro");
		}
	}
	
	public function buscamontadorasAction(){
        $this->_helper->layout->disableLayout();
        try{
            $params = $this->_getAllParams();
        	VeiculosBO::listaMontadoras($params);
        	
        }catch (Zend_Exception $e){
        	$boerr	= new ErrosModel();
        	$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "CadastroController::buscamontadoras()");
        	$boerr->insert($dataerro);
        	 
        	echo "erro";
        }
        exit();
    } 
	
	
	public function montadorascadAction(){
		$this->topoAction('cadastro');
		$usuario = Zend_Auth::getInstance()->getIdentity();
			
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 12) as $list);
			
		if(($list->visualizar==1)){
            
            $params = $this->_getAllParams();

			if($this->_request->isPost()){
				try{
					VeiculosBO::gravarMontadora($params);
					$this->_helper->flashMessenger->addMessage(array('Sucesso!'=>'Montadora cadastrada com sucesso!'));
					$this->_redirect("/admin/cadastro/montadoras");
					
				}catch (Zend_Exception $e){
					$this->view->erro = "Erro ao gravar montadora";
				}
			}

			$this->view->objMontadora 	= VeiculosBO::buscaMontadoras($params);
			
		}else{
			$this->_redirect("/admin/cadastro/erro");
		}
	}
	
	public function montadorasremanexoAction(){
		$params = $this->_getAllParams();
		$iduser = VeiculosBO::removeAnexomontadora($params);
		$this->_redirect("/admin/cadastro/montadorascad/montadora/".md5($params['anexo']));
	}
	
	public function removemontadorasAction(){
        $this->_helper->layout->disableLayout();
        try{
        	$params = $this->_getAllParams();
        	VeiculosBO::removeMontadora($params);
        	 
        }catch (Zend_Exception $e){
        	$boerr	= new ErrosModel();
        	$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "CadastroController::removemontadora()");
        	$boerr->insert($dataerro);
        
        	echo "erro";
        }
        exit();
    }
    
    public function veiculosAction(){
        $this->topoAction('cadastro');
        $usuario = Zend_Auth::getInstance()->getIdentity();
        	
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 12) as $list);
        $params = $this->_getAllParams();
        
        ($list->visualizar!=1) ? $this->_redirect("/admin/cadastro/erro") : $this->view->objMontadora = VeiculosBO::buscaMontadoras($params);
    }
    
    public function buscaveiculosAction(){
    	$this->_helper->layout->disableLayout();
    	try{
    		$params = $this->_getAllParams();
    		VeiculosBO::listaVeiculos($params);
    		 
    	}catch (Zend_Exception $e){
    		$boerr	= new ErrosModel();
    		$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "CadastroController::buscaveiculos()");
    		$boerr->insert($dataerro);
    
    		echo "erro";
    	}
    	exit();
    }
    
    
    public function veiculoscadAction(){
    	$this->topoAction('cadastro');
    	$usuario = Zend_Auth::getInstance()->getIdentity();
    		
    	foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 12) as $list);
    		
    	if(($list->visualizar==1)){
    
    		$params = $this->_getAllParams();
    
    		if($this->_request->isPost()){
    			try{
    				VeiculosBO::gravarVeiculo($params);
    				$this->_helper->flashMessenger->addMessage(array('Sucesso!'=>'Veículo cadastrado com sucesso!'));
    				$this->_redirect("/admin/cadastro/veiculos/montadora/".md5($params['idmontadora']));
    					
    			}catch (Zend_Exception $e){
    				$this->view->erro = "Erro ao gravar o veiculo";
    			}
    		}
    
    		$this->view->objVeiculo   = VeiculosBO::buscaVeiculos($params);
    		$this->view->objMontadora = VeiculosBO::buscaMontadoras($params);
    	}else{
    		$this->_redirect("/admin/cadastro/erro");
    	}
    }
    
    public function veiculosremanexoAction(){
    	$params = $this->_getAllParams();
    	$iduser = VeiculosBO::removeAnexoveiculo($params);
    	$this->_redirect("/admin/cadastro/veiculoscad/veiculo/".md5($params['anexo'])."/montadora/".md5($params['montadora']));
    }
    
    public function removeveiculosAction(){
    	$this->_helper->layout->disableLayout();
    	try{
    		$params = $this->_getAllParams();
    		VeiculosBO::removeVeiculo($params);
    
    	}catch (Zend_Exception $e){
    		$boerr	= new ErrosModel();
    		$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "CadastroController::removeveiculos()");
    		$boerr->insert($dataerro);
    
    		echo "erro";
    	}
    	exit();
    }
}
