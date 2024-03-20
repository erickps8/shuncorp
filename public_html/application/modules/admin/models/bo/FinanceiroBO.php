<?php
	class FinanceiroBO{		
		
		//--- Lista financeiro para o CMV ------------------------------------------------------
		function buscaFimprodutosentregues($var){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('pc'=>'pedidos_compra'), array('f.*','pc.ID as idped'))
					->join(array('pd'=>'produtos_pedidos_compra'),'pd.ID_PEDIDO_COMPRA = pc.ID')        
					->join(array('f'=>'tb_financeiropag'),'f.id_pedcompra = pc.ID')
					->where("pc.STATUS = 'FINALIZADO' and pd.ID_PRODUTO = ".$var)
			        ->order('pc.ID','asc');
			  
			$stmt = $db->query($select);
			return  $stmt->fetchAll();
		}		
		
		//-- Contas bancarias -----------------------------------------
		function listarContasbancarias($id = ""){
			$bo		= new FinanceiroModel();
			
			$where = "";
			
			if($id!="" and $id!=0) $where = " and id =  ".$id;
			return $bo->fetchAll("sit = true".$where);
		}
		
		function gravaContasbancarias($params){
			$bo		= new FinanceiroModel();
			$array['nome']		= strtoupper($params['nome']);
			$array['banco']		= strtoupper($params['banco']);
			$array['agencia']	= strtoupper($params['agencia']);
			$array['conta']		= strtoupper($params['conta']);
			$array['sit']		= true;
			
			if(!empty($params['idbanco'])):
				$bo->update($array, "id = ".$params['idbanco']);
				$id = $params['idbanco'];
			else:
				$id = $bo->insert($array);
			endif;
			
			$usuario = Zend_Auth::getInstance()->getIdentity();
			LogBO::cadastraLog("ADM/Financeiro/Bancos",2,$usuario->ID,$id,"Conta ".$id);
		}
		
		function removeContasbancarias($params){
			$bo		= new FinanceiroModel();
			$array['sit']	= false;
			
			$bo->update($array, "md5(id) = '".$params['idbanco']."'");
			
			foreach ($bo->fetchAll("md5(id) = '".$params['idbanco']."'") as $lista);
			
			$usuario = Zend_Auth::getInstance()->getIdentity();
			LogBO::cadastraLog("ADM/Financeiro/Bancos",3,$usuario->ID,$lista->id,"Conta ".$lista->id);
		}
		
		function buscaContasbancarias($params){
		    $bo		= new FinanceiroModel();
		    return $bo->fetchAll("id = '".$params['idbanco']."'");
		}
		
		
		
		//--Plano de contas------------------------------
				
		function listarPlanoscontas($var=""){
			$bof	= new FinanceiroModel();
			$bo		= new FinanceiroplanocontasModel();
			
			if(!empty($var['conta'])):
				$where = " and md5(id_sup) = '".$var['conta']."'";
			else:
				$where = " and id_sup is NULL";
			endif;
			
			return $bo->fetchAll("sit = true ".$where,"id asc");
		}
		
		
		function listartodosPlanoscontas(){
			$bof	= new FinanceiroModel();
			$bo		= new FinanceiroplanocontasModel();
			
			return $bo->fetchAll("sit = true","navegacao asc");			
		}
		
		function listartodosPlanoscontasold(){
			$bof	= new FinanceiroModel();
			$bo		= new FinanceiroplanocontasoldModel();
				
			return $bo->fetchAll("sit = true","navegacao asc");
		}
		
		function buscaPlanoscontasmd5($var){
			$bof	= new FinanceiroModel();
			$bo		= new FinanceiroplanocontasModel();
				
			return $bo->fetchAll("md5(id) = '".$var."'");
		}
				
		function buscaArvoreplanoconta($var){
			$sessaoFin = new Zend_Session_Namespace('buscaplconta');
			$bof	= new FinanceiroModel();
			$bo		= new FinanceiroplanocontasModel();
			
			$list3="";
			foreach ($bo->fetchAll("sit = true and id_sup in (".$var.")") as $contaslist):
				$list3 .= $contaslist->id.",";
			endforeach;			
			
			if($list3!=""):
				$sessaoFin->plcontas .= $list3;
				FinanceiroBO::buscaArvoreplanoconta(substr($list3, 0,-1));				
			endif;
			
			return $sessaoFin->plcontas.$var;
		}
		
		
		function buscaPlanoscontas($var){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('pc'=>'tb_financeiroplcontas'), array('pc.id as id2','pc.cod as cod2','pc.navegacao as navsup2','pc.nivel as nivel2','p.cod as cod1','p.nivel as nivel1','p.id as id1','p.navegacao as navsup1'))
					->joinLeft(array('p'=>'tb_financeiroplcontas'),'p.id = pc.id_sup')
					->where("md5(pc.id) = '".$var['conta']."'");
			  
			$stmt = $db->query($select);
			return  $stmt->fetchAll();
			
		}
		
		function gravaPlanosconta($params){
			$bof	= new FinanceiroModel();
			$bo		= new FinanceiroplanocontasModel();
			$array['nome']			= $params['planoconta'];
			$array['sit']			= true;
			$array['dt_cadastro']	= date("Y-m-d H:i:s");
			$array['utilizavel']	= $params['utilizavel'];
			
			if(!empty($params['nivel'])):			
				$array['nivel']			= $params['nivel']+1;
			else:
				$array['nivel']			= 1;
			endif;
			
			if(!empty($params['idsup'])):	
				$array['id_sup']		= $params['idsup'];
			
			endif;
			
			if(!empty($params['idsup']) and empty($params['idplanoconta'])):	

				$array['id_sup']		= $params['idsup'];
			
				$cont = 1;
				foreach ($bo->fetchAll("id_sup = ".$params['idsup']) as $qtcontas):
					$cont++;
				endforeach;
				
				$cod	= $params['codsup'].$cont;
				
				if(($params['nivel']+1) >= 4):
					$cont = str_pad($cont,2,'0',STR_PAD_LEFT);					
				endif;
				$nav 	= $params['navegacaosup'].".".$cont;
				
				/* $nav = "";
				$n_caracteres = strlen($cod);
				for($i=0; $i<$n_caracteres; $i++):
				   $nav .= $cod[$i].".";
				endfor; */
				
				$array['navegacao']	= $nav;
				$array['cod']		= $cod;
				
			elseif(empty($params['idplanoconta'])):
				$cont = 1;
				foreach ($bo->fetchAll("id_sup is NULL") as $qtcontas):
					$cont++;
				endforeach;
				$array['navegacao']	= $cont;
				$array['cod']		= $cont;
			endif;						
			
			if(!empty($params['idplanoconta'])):
				$bo->update($array, "id = ".$params['idplanoconta']);
				$id = $params['idplanoconta'];
			else:
				$id = $bo->insert($array);
			endif;
			
			$usuario = Zend_Auth::getInstance()->getIdentity();
			LogBO::cadastraLog("ADM/Financeiro/Plano contas",2,$usuario->ID,$id,"Plano conta ".$id);
		}
		
		/* function removePlanosconta($params){
			$bof	= new FinanceiroModel();
			$bo		= new FinanceiroplanocontasModel();
			
			$array['sit']	= false;			
			$bo->update($array, "md5(id) = '".$params['idplano']."'");			
			foreach ($bo->fetchAll("md5(id) = '".$params['idplano']."'") as $lista);
			
			$usuario = Zend_Auth::getInstance()->getIdentity();
			LogBO::cadastraLog("ADM/Financeiro/Cat Plano contas",3,$usuario->ID,$lista->id,"Plano conta ".$lista->id);
		} */
		
		function removePlanosconta($idcontas,$idcontasant=""){
		    $bof	= new FinanceiroModel();
		    $bo		= new FinanceiroplanocontasModel();
		    $boppag = new FinanceiropagarparcModel();
		    $boprec = new FinanceiroreceberparcModel();
		    
		    $idcontasant .= $idcontas;
		    
		    $idcontasger = "";
		    foreach ($bo->fetchAll("sit = true and id_sup in (".substr($idcontas, 0,-1).")") as $contas):
		    	$idcontasger .= $contas->id.",";
		    endforeach;
		    
		    if($idcontasger!=""):
		    	FinanceiroBO::removePlanosconta($idcontasger,$idcontasant);
		    else:
		    
		    	$contaver = 0;
		    	if(count($boppag->fetchAll("sit = true and id_planocontas in (".substr($idcontasant, 0,-1).")"))>0):
			    	$contaver = 1;
			    endif;
			    
			    if(count($boprec->fetchAll("sit = true and id_planocontas in (".substr($idcontasant, 0,-1).")"))>0):
			    	$contaver = 1;
			    endif;
			    
			    echo $contaver;
			    
			    if($contaver == 0):
				    $array['sit']	= false;
				    $bo->update($array, "id in (".substr($idcontasant, 0,-1).")");				    
			    endif;			    
		    endif;		    		    
		}
		
		function gravaPlanoscontasub($params){
			$bof	= new FinanceiroModel();
			$bo		= new FinanceiroplanocontasModel();
			$usuario = Zend_Auth::getInstance()->getIdentity();
						
			$array['nome']				= strtoupper($params['planoconta']);
			$array['sit']				= true;
			$array['dt_cadastro']		= date("Y-m-d H:i:s");
			$array['id_planocontascat']	= $params['idcat'];
			
			
			if(!empty($params['idplanoconta'])):
				$bo->update($array, "id = ".$params['idplanoconta']);
				$id = $params['idplanoconta'];
				LogBO::cadastraLog("ADM/Financeiro/Plano contas",4,$usuario->ID,$id,"Plano conta ".$id);
			else:
				$id = $bo->insert($array);
				LogBO::cadastraLog("ADM/Financeiro/Plano contas",2,$usuario->ID,$id,"Plano conta ".$id);
			endif;
			
		}
		
		function removePlanoscontasub($params){
			$bof	= new FinanceiroModel();
			$bo		= new FinanceiroplanocontasModel();
			$array['sit']	= false;
			
			$bo->update($array, "md5(id) = '".$params['idplano']."'");
			
			foreach ($bo->fetchAll("md5(id) = '".$params['idplano']."'") as $lista);
			
			$usuario = Zend_Auth::getInstance()->getIdentity();
			LogBO::cadastraLog("ADM/Financeiro/Plano contas",3,$usuario->ID,$lista->id,"Plano conta ".$lista->id);
			
			return $lista->id_planocontascat;
		}
		
		
		
		//---Lista contas a pagar----------------------------------
		function listaContaspagar($pesq=""){
			//$where = " and f.baixa != 1";
			
		    $usuario 	= Zend_Auth::getInstance()->getIdentity();
		    $where 		= "";
			$limit = " and f.emissao >= '".date('Y-m-d',mktime(0,0,0,date('m'),date('d')-7,date('Y')))."'";
			if(!empty($pesq['buscaid'])):
				$where = " and f.id = '".substr($pesq['buscaid'],1)."'";
			endif;
		
			
			if(($pesq['buscafor']!=0) and ($pesq['buscafor']!='out')):
				$forn = explode('|', $pesq['buscafor']);
			
				if($forn[1]==1):
					$where .= " and f.id_usuarios = ".$forn[0];
				else:
					$where .= " and f.id_fornecedor = ".$forn[0];
				endif;
				
			elseif(!empty($pesq['buscaoutfor'])):
				$where .= " and f.out_fornecedor like '%".$pesq['buscaoutfor']."%'";
			endif;
			
			if($pesq['tipovl'] == 1):			
				if(!empty($pesq['buscavalor1'])):
					$valor = str_replace(",",".",str_replace(".","",$pesq['buscavalor1']));
					$where .= " and p.valor_apagar >= '".$valor."'";
				endif;
				
				if(!empty($pesq['buscavalor2'])):
					$valor = str_replace(",",".",str_replace(".","",$pesq['buscavalor2']));
					$where .= " and p.valor_apagar <= '".$valor."'";
				endif;
			else:
				if(!empty($pesq['buscavalor1'])):
					$valor = str_replace(",",".",str_replace(".","",$pesq['buscavalor1']));
					$where .= " and f.valor >= '".$valor."'";
				endif;
					
				if(!empty($pesq['buscavalor2'])):
					$valor = str_replace(",",".",str_replace(".","",$pesq['buscavalor2']));
					$where .= " and f.valor <= '".$valor."'";
				endif;
			endif;
				
			if(!empty($pesq['buscaplano'])):			
				$planos = FinanceiroBO::buscaArvoreplanoconta($pesq['buscaplano']);
				
				if(!empty($planos)):
					$where .= " and p.id_financeiroplcontas in (".$planos.")";
				else:
					$where .= " and p.id_financeiroplcontas in (0)";
				endif;
			endif;
			
			
			if(isset($pesq['buscasit']) and $pesq['buscasit'] != 'sit'){
				if($pesq['buscasit']==0):
					$where .= " and p.baixa = 0";// and p.vencimento >= '".date("Y-m-d")."'";
				elseif($pesq['buscasit']==1):
					$where .= " and p.baixa = 0 and p.vencimento < '".date("Y-m-d")."'";
				elseif($pesq['buscasit']==2):
					//-- Baixado -----------
					$where .= " and p.baixa = 1" ;
				elseif($pesq['buscasit']==3):
					//-- Conciliado -----------
					$where .= " and (p.st_conc != '' and p.st_conc != 0)";
				endif;
			}				

			if(isset($pesq['buscafatura']) and $pesq['buscafatura'] != ""){
				$where = " and f.n_documento like '%".$pesq['buscafatura']."%'";
			}
			
			if(isset($pesq['fil'])){
				if($pesq['fil']=='avencerhoje'):
					$where .= " and p.valor_pago is NULL and p.vencimento = now()";
				elseif($pesq['fil']=='avencersem'):
					$data = date('Y-m-d',mktime(0,0,0,date('m'),date('d')+7,date('Y')));
					$where .= " and p.valor_pago is NULL and p.vencimento >= now() and p.vencimento <= '".$data."'";
				elseif($pesq['fil']=='vencidas'):
					$where .= " and p.valor_pago is NULL and p.vencimento < now()";
				elseif($pesq['fil']=='avencer'):
					$where .= " and p.valor_pago is NULL and p.vencimento >= now()";
				endif;			
			}
			//------- Filtro de datas --------------------------------------------
			if($pesq['datapesq']==0):
				if((!empty($pesq['dtini'])) || (!empty($pesq['dtfim']))):
					if(!empty($pesq['dtini'])) $di	= substr($pesq['dtini'],6,4).'-'.substr($pesq['dtini'],3,2).'-'.substr($pesq['dtini'],0,2);
					if(!empty($pesq['dtfim'])) $df	= substr($pesq['dtfim'],6,4).'-'.substr($pesq['dtfim'],3,2).'-'.substr($pesq['dtfim'],0,2);
				
					if((!empty($di)) and (!empty($df))):
						$where .= ' and p.vencimento between "'.$di.'" and "'.$df.'"';
					elseif((!empty($di)) and (empty($df))):
						$where .= ' and p.vencimento >= "'.$di.'"';
					elseif((empty($di)) and (!empty($df))):
						$where .= ' and p.vencimento <= "'.$df.'"';
					endif;
				endif;
			else:
				if((!empty($pesq['dtini'])) || (!empty($pesq['dtfim']))):
					if(!empty($pesq['dtini'])) $di	= substr($pesq['dtini'],6,4).'-'.substr($pesq['dtini'],3,2).'-'.substr($pesq['dtini'],0,2);
					if(!empty($pesq['dtfim'])) $df	= substr($pesq['dtfim'],6,4).'-'.substr($pesq['dtfim'],3,2).'-'.substr($pesq['dtfim'],0,2);
				
					if((!empty($di)) and (!empty($df))): 
						$where .= ' and f.emissao between "'.$di.'" and "'.$df.'"';
					elseif((!empty($di)) and (empty($df))): 
						$where .= ' and f.emissao >= "'.$di.'"';
					elseif((empty($di)) and (!empty($df))): 
						$where .= ' and f.emissao <= "'.$df.'"';
					endif;
				endif;
			endif;
			
			//--- Controle de perfil ------------------------------------------
			foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
			if($list->nivel==1):
				$where .= " and f.id = 0";				
			elseif($list->nivel==0):
				$where .= " and f.id_fornecedor = ".$usuario->id_cliente;
			endif;
			
			if($where != ""){
				$limit = "";
				$limite = '100000000000';
			}
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('f'=>'tb_financeiropag'),
		        array('f.*','f.id as idfin','DATE_FORMAT(f.emissao,"%d/%m/%Y") as dtcad','c.EMPRESA','DATE_FORMAT(p.vencimento,"%d/%m/%Y") as dtvencimento', 'p.id as idparc', 'p.baixa as quitado', 'p.st_conc as conciliado', 'f.baixa as contaquitada','u.nome'))
		        ->joinLeft(array('c'=>'clientes'),'c.ID = f.id_fornecedor')			        
		        ->join(array('p'=>'tb_financeiropagparc'),'f.id = p.id_financeiropag and p.sit = true')
		        ->joinLeft(array('u'=>'tb_usuarios'),'u.id = f.id_usuarios')
		        ->where("f.sit = true ".$where.$limit)
		        ->order('p.vencimento')
		        ->order('f.id desc')
		        ->group("f.id");
			  	
			$stmt = $db->query($select);
			
			return $stmt->fetchAll();
		}
		
		//---Gravar conta------------------------------------------
		/*
		 * o campo bloq da tabela eh usado para bloquear edicao da parcela pela pessal nao habilitada a liberar contas -----
		 * o campo baixa bloqueia totalmente a parcela, pois jah foi conciliada com o extrato bancario ----------------------
		 * 
		 * */		
		function gravarContaspag($params){
			$bo 	= new FinanceiroModel();
			$bop	= new FinanceiropagarModel();
			$boa	= new FinanceiroanexopagarModel();
			$boparc = new FinanceiropagarparcModel();
			$bonfe	= new NfeModel();
			$bofc	= new FinanceirocreditosModel();
			
			$usuario = Zend_Auth::getInstance()->getIdentity();			
			
			if(($params['fornpag']!=0) and ($params['fornpag']!='out')):
				$forn = explode('|', $params['fornpag']);
				
				if($forn[1]==0):
					$array['id_fornecedor']		=	$forn[0];
					$array['id_usuarios']		=	NULL;
					$array['out_fornecedor']	=   NULL;
				else:
					$array['id_fornecedor']		=	NULL;
					$array['id_usuarios']		=	$forn[0];
					$array['out_fornecedor']	=   NULL;
				endif;
			elseif(!empty($params['outfornpag'])):
				$array['out_fornecedor']	=	$params['outfornpag'];
				$array['id_usuarios']		=	NULL;
				$array['id_fornecedor']		=   NULL;
			endif;
			
			if(isset($params['faturapag']) and isset($params['emissaopag']) and isset($params['valortotalpag'])):
				$array['emissao']			=	substr($params['emissaopag'],6,4).'-'.substr($params['emissaopag'],3,2).'-'.substr($params['emissaopag'],0,2);
				$array['n_documento']		=	$params['faturapag'];
				$array['obs']				=	$params['obspag'];
				$array['moeda']				=   $params['moedapagconta'];
				$array['valor']				=	str_replace(',','.',str_replace('.','',$params['valortotalpag']));
				$array['baixa']				= 	0;
				$array['sit']				= 	true;				
			endif;
			
			if(!empty($params['idcontapag'])){
				foreach ($bop->fetchAll("id = ".$params['idcontapag']) as $conta);
			
			    if($conta->baixa == 0):
			    	$array['sit']				= 	true;
					$bop->update($array, "id = ".$params['idcontapag']);
				endif;
				$id = $params['idcontapag'];										
				
				LogBO::cadastraLog("ADM/Financeiro/Pagamentos",4,$usuario->id,$id,"P".substr("000000".$id,-6,6));
				
				//--Fixar Nfe--------------------
				$ids_array = explode(',',$params['idnfe']);
				for($i=0 ; $i < sizeof($ids_array); $i++):
					if($ids_array[$i]!=""):
						$arraynfe['id_pagfrete']		= $id;
						$bonfe->update($arraynfe,"id = ".$ids_array[$i]);
					endif;
				endfor;
			}else{
				$id = $bop->insert($array);
			
				LogBO::cadastraLog("ADM/Financeiro/Pagamentos",2,$usuario->ID,$id,"P".substr("000000".$id,-6,6));				
				
			}					
			
			$arraynfe['id_pagfrete']			= NULL;
			$arraynfe['valorfrete']				= NULL;
			$bonfe->update($arraynfe,"id_pagfrete = ".$id);
			
			//--Fixar Nfe--------------------
			$ids_array = explode(',',$params['idnfe']);
			for($i=0 ; $i < sizeof($ids_array); $i++):
				if($ids_array[$i]!=""):						
					$arraynfe['id_pagfrete']		= $id;
					$arraynfe['valorfrete']			= str_replace(',','.',str_replace('.','',$params['valor_'.$ids_array[$i]]));
					$bonfe->update($arraynfe,"id = ".$ids_array[$i]);					
				endif;
			endfor;	
			
			for ($i=1;$i<=$params['intparcela'];$i++):
				$qtparc = count($boparc->fetchAll("sit = true and id_financeiropag = ".$id));
			
				$arrayparc['id_financeiropag']		=	$id;
				$arrayparc['emissao']				= 	date("Y-m-d"); 
				$arrayparc['vencimento']			=	substr($params['vencpar_'.$i],6,4).'-'.substr($params['vencpar_'.$i],3,2).'-'.substr($params['vencpar_'.$i],0,2);
				$arrayparc['moeda']					=	$params['moedapar_'.$i];
				$arrayparc['valor_apagar']			=	str_replace(',','.',str_replace('.','',$params['valorpar_'.$i]));
				$arrayparc['id_financeiroplcontas']	=	$params['contapar_'.$i];
				$arrayparc['codbarras']				=	$params['codbarras_'.$i];
				$arrayparc['sit']					= 	true; 
				$arrayparc['baixa']					= 	0;
				$arrayparc['parc']					= 	$qtparc+1;

				if(isset($params["idparcrec"]) and $params["idparcrec"]!="") $arrayparc['id_financeirorecparc']	=  	$params["idparcrec"];
				
				if(!empty($params['valorpago_'.$i]) and !empty($params['datapar_'.$i]) and ($params['bancopar_'.$i]!=0)):
					$arrayparc['valor_pago']			=	str_replace(',','.',str_replace('.','',$params['valorpago_'.$i]));
					$arrayparc['txcambio']				=	str_replace(',','.',str_replace('.','',$params['txcambiopago_'.$i]));
					$arrayparc['id_financeirocontas']	=	$params['bancopar_'.$i];
					$arrayparc['dt_pagamento']			=	substr($params['datapar_'.$i],6,4).'-'.substr($params['datapar_'.$i],3,2).'-'.substr($params['datapar_'.$i],0,2);
					$arrayparc['baixa']					= 	1;
										
				endif;
				
				if(!empty($params['vencpar_'.$i]) and !empty($params['valorpar_'.$i])):
					$boparc->insert($arrayparc);
				endif;
			endfor;
			
			
			foreach (FinanceiroBO::listarParcelasapagar(md5($id)) as $listaparc):
				if(!empty($params['valorpag_'.$listaparc->idparc]) and !empty($params['vencimentopag_'.$listaparc->idparc]) and ($params['planocontapag_'.$listaparc->idparc]!=0)):
					$arrayparcedit['vencimento']		=	substr($params['vencimentopag_'.$listaparc->idparc],6,4).'-'.substr($params['vencimentopag_'.$listaparc->idparc],3,2).'-'.substr($params['vencimentopag_'.$listaparc->idparc],0,2);
					$arrayparcedit['moeda']				=	$params['moedapag_'.$listaparc->idparc];
					$arrayparcedit['valor_apagar']		=	str_replace(',','.',str_replace('.','',$params['valorpag_'.$listaparc->idparc]));
					$arrayparcedit['codbarras']			=	$params['codbarras_'.$listaparc->idparc];
					$pconta	= explode("|", $params['planocontapag_'.$listaparc->idparc]);
					$arrayparcedit['id_financeiroplcontas']	=	$pconta[0];
						
					$boparc->update($arrayparcedit, "id = ".$listaparc->idparc);
				endif;
			
				if(!empty($params['valorpagamentopag_'.$listaparc->idparc]) and !empty($params['datapagamentopag_'.$listaparc->idparc]) and ($params['bancopagamentopag_'.$listaparc->idparc]!=0)):
					$arraypag['valor_pago']				=	str_replace(',','.',str_replace('.','',$params['valorpagamentopag_'.$listaparc->idparc]));
					$arraypag['txcambio']				=	str_replace(',','.',str_replace('.','',$params['txcambio_'.$listaparc->idparc]));
					$arraypag['id_financeirocontas']	=	$params['bancopagamentopag_'.$listaparc->idparc];
					$arraypag['dt_pagamento']			=	substr($params['datapagamentopag_'.$listaparc->idparc],6,4).'-'.substr($params['datapagamentopag_'.$listaparc->idparc],3,2).'-'.substr($params['datapagamentopag_'.$listaparc->idparc],0,2);
					$arraypag['baixa']					= 	1;
						
					$boparc->update($arraypag, "id = ".$listaparc->idparc);
					
				endif;
			endforeach;
				
			$baixa = 1;
			foreach (FinanceiroBO::listarParcelasapagar(md5($id)) as $listaparc):
				if($listaparc->baixa == 0):
					$baixa = 0;
				endif;
			endforeach;

			if(($baixa == 1) and (count(FinanceiroBO::listarParcelasapagar(md5($id)))>0)):
				$arrayb['baixa']		= $baixa;
				$bop->update($arrayb, "id = ".$id);
				LogBO::cadastraLog("ADM/Financeiro/Pagamentos",4,$usuario->ID,$id,"BAIXA P".substr("000000".$id,-6,6));
			endif;
			
			//-- Creditos -------------------------------------
			$bofc->delete("id_financeiropag = ".$id);
			$creditos = explode(",", $params['idpurch'],-1);
			foreach ($creditos as $i => $value){
				if($value){
					$datacred = array('id_creditos' => $value, 'id_financeiropag' => $id);
					$bofc->insert($datacred);
				}
			}
			
			//---Arquivos-------------------------------
			$ic = 0;
			foreach ($boa->fetchAll('id_financeiropag = '.$id) as $listanex);
			if(count($listanex)>0):
				$ianex = explode(".",$listanex->nome);
				$ic = substr($ianex[0],-1);
			endif;
			
			$pasta = Zend_Registry::get('pastaPadrao')."public/sistema/upload/financeiro/pagar";
				
			$upload = new Zend_File_Transfer_Adapter_Http();
			$upload->setDestination($pasta);
			$files = $upload->getFileInfo();
			
			if($files){
			    
				foreach ($files as $file => $info){
				    $ic++;
				    
					$ext = substr(strrchr($info['name'], "."), 1);
					$nome = $id."_".$ic.".".$ext;
					$upload->addFilter('Rename', array('target' => $pasta.'/'.$nome, 'overwrite' => true));
					 
					if ($upload->isValid($file)) {
						echo $upload->receive($file);
						$boa->insert(array('nome' => $nome, 'id_financeiropag' => $id));
					}
				}
				 
			}
			
	         
	         return $id;
			
		}
		
		function listarAnexosapagar($id){
			$bof	= new FinanceiroModel();
			$boa	= new FinanceiroanexopagarModel();
			
			return $boa->fetchAll('md5(id_financeiropag) = "'.$id.'"');
		}
		
		function listarParcelasapagar($id){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('f'=>'tb_financeiropagparc','*'),array('f.*','f.id as idparc' ,'p.nome as nomepc','f.id_planocontas as idpc','f.id_financeiroplcontas as idplc','p.cod'))
			        ->joinLeft(array('p'=>'tb_financeiroplcontas'), 'f.id_financeiroplcontas = p.id')
			        ->where('f.sit = true and md5(id_financeiropag) = "'.$id.'"')
			        ->order('f.id asc');
			  		
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		function listarParcelasapagarporpedido($ped){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('fp'=>'tb_financeiropagparc','*'),array('*'))
			        ->join(array('f'=>'tb_financeiropag'), 'f.id = fp.id_financeiropag')
			        ->where('f.sit = true and fp.sit = true and f.id_pedcompra = '.$ped);
			  		
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		function listarPurchasepagar($id){
			$bof	= new FinanceirochinaModel();
			$boa	= new FinanceirochinapurchaseModel();
			
			return $boa->fetchAll('sit = true and md5(id_contasapagar) = "'.$id.'"');
		}
		
		function removeParcelasapagar($var){
			$bof	= new FinanceiroModel();
			$boa	= new FinanceiropagarparcModel();
			
			$array['sit']	= false;
			$boa->update($array,'md5(id) = "'.$var['parc'].'"');
			
			$qtparc = count($boa->fetchAll('sit = true and md5(id_financeiropag) = "'.$var['pag'].'"'));
			
			if($qtparc>0):
				$i = 0;
				foreach ($boa->fetchAll('sit = true and md5(id_financeiropag) = "'.$var['pag'].'"') as $listparc):
					$i++;
					$arraypar['parc']	= $i; 				
					$boa->update($arraypar, "id = ".$listparc->id);
				endforeach;
			endif;
			
			$arraypar['parc']	= 0;
			$boa->update($arraypar, 'sit = false and md5(id_financeiropag) = "'.$var['pag'].'"');			
			
		}
		
		function liberaParcelasapagar($var){
			$bof	= new FinanceiroModel();
			$bop	= new FinanceiropagarModel();
			$boa	= new FinanceiropagarparcModel();
				
			$array['baixa']	= false;
			$boa->update($array,'md5(id) = "'.$var['parc'].'"');	

			$arrayb['baixa']	= false;
			$bop->update($arrayb,'md5(id) = "'.$var['pag'].'"');
			
			/* $baixas = 0;
			foreach (FinanceiroBO::listarParcelasapagar($var['parc']) as $listaparc):
				if($listaparc->baixa == 1):
					$baixas = 1;
				endif;
			endforeach;
			
			if($baixas == 0):
				$arrayb['baixa']	= false;
				$bop->update($arrayb,'md5(id) = "'.$var['pag'].'"');
			endif; */
		}
		
		function liberaContasapagar($var){
			$bof	= new FinanceiroModel();
			$bop	= new FinanceiropagarModel();
			
			$arrayb['baixa']	= false;
			$bop->update($arrayb,'md5(id) = "'.$var['pag'].'"');
			
		}
		
		function liberaContasareceber($var){
			$bof	= new FinanceiroModel();
			$bop	= new FinanceiroreceberModel();
				
			$arrayb['baixa']	= false;
			$bop->update($arrayb,'md5(id) = "'.$var['rec'].'"');
				
		}
		
		function remAnexos($params){
			$bof	= new FinanceiroModel();
			$boap	= new FinanceiroanexopagarModel();
			$boar	= new FinanceiroanexoreceberModel();
			$usuario = Zend_Auth::getInstance()->getIdentity();
			
			if(!empty($params['pay'])):
				$boap->delete('id = '.$params['idanex']);
				
				foreach ($boap->fetchAll('id = '.$params['idanex']) as $listanex);
				@unlink(Zend_Registry::get('pastaPadrao')."/admin/financeiro/pagar/".$listanex->nome);
				
				LogBO::cadastraLog("ADM/Fin chines/Pagamentos",3,$usuario->ID,$listanex->nome,"Remove anexo ".$listanex->nome);
			elseif(!empty($params['rec'])):
				$boar->delete('id = '.$params['idanex']);
				
				foreach ($boar->fetchAll('id = '.$params['idanex']) as $listanex);
				@unlink(Zend_Registry::get('pastaPadrao')."/admin/financeiro/receber/".$listanex->nome);
				LogBO::cadastraLog("ADM/Financeiro/Recebimentos",3,$usuario->ID,$listanex->nome,"Remove anexo ".$listanex->nome);
			endif;
		}
		
		function buscarContapag($id){
			$bof	= new FinanceiroModel();
			$bop	= new FinanceiropagarModel();
			
			return $bop->fetchAll("md5(id) = '".$id."'");
		}
		
		function removerContaspag($id){
			$bof	= new FinanceiroModel();
			$bop	= new FinanceiropagarModel();
			
			$array['sit']		= false;			
			$bop->update($array, "md5(id) = '".$id."'");
		}
		
		//---Lista contas a receber----------------------------------
		/* Usado em Relatorios::financeirorelreceberAction
		 * Usado em Administracao::financeiroztlrecAction
		 * 
		 * */
		public function listaContasreceber($pesq){
			$limit = " and f.emissao >= '".date('Y-m-d',mktime(0,0,0,date('m'),date('d')-7,date('Y')))."'";
			$where = "";

			//-- busca por ID do lancamento --------------------------------------
			if(!empty($pesq['buscaid'])) $where = " and f.id = '".ereg_replace("[^0-9]", " ", $pesq['buscaid'])."'";
			
			//-- busca por fornecedor do lancamento --------------------------------------
			if(($pesq['buscafor']!=0) and ($pesq['buscafor']!='out')):
				$forn = explode('|', $pesq['buscafor']);
					
				if($forn[1]==1):
					$where .= " and f.id_usuarios = ".$forn[0];
				else:
					$where .= " and f.id_fornecedor = ".$forn[0];
				endif;
			
			elseif(!empty($pesq['buscaoutfor'])):
				$where .= " and f.out_fornecedor like '%".$pesq['buscaoutfor']."%'";
			endif;
			
			//-- busca por valor do lancamento --------------------------------------
			if(isset($pesq['tipovl']) and $pesq['tipovl'] == 1):			
				if(!empty($pesq['buscavalor1'])):
					$valor = str_replace(",",".",str_replace(".","",$pesq['buscavalor1']));
					$where .= " and p.valor_apagar >= '".$valor."'";
				endif;
				
				if(!empty($pesq['buscavalor2'])):
					$valor = str_replace(",",".",str_replace(".","",$pesq['buscavalor2']));
					$where .= " and p.valor_apagar <= '".$valor."'";
				endif;
			else:
				if(!empty($pesq['buscavalor1'])):
					$valor = str_replace(",",".",str_replace(".","",$pesq['buscavalor1']));
					$where .= " and f.valor >= '".$valor."'";
				endif;
					
				if(!empty($pesq['buscavalor2'])):
					$valor = str_replace(",",".",str_replace(".","",$pesq['buscavalor2']));
					$where .= " and f.valor <= '".$valor."'";
				endif;
			endif;
			
			if(!empty($pesq['buscaplano'])){			
				$planos = FinanceiroBO::buscaArvoreplanoconta($pesq['buscaplano']);
				
				if(!empty($planos)) $where .= " and p.id_financeiroplcontas in (".$planos.")";
				else $where .= " and p.id_financeiroplcontas in (0)";
				
			}
			
			//-- busca por situacao --------------------------------------
			
			if(isset($pesq['buscasit']) and $pesq['buscasit'] != 'sit'){
				if($pesq['buscasit']==0):
				    $where .= " and p.baixa = 0";// and p.vencimento >= '".date("Y-m-d")."'";
				elseif($pesq['buscasit']==1):
				    $where .= " and p.baixa = 0 and p.vencimento < '".date("Y-m-d")."'";
				elseif($pesq['buscasit']==2):
    				//-- Baixado -----------
    				$where .= " and p.baixa = 1" ;
				elseif($pesq['buscasit']==3):
    				//-- Conciliado -----------
    				$where .= " and (p.st_conc != '' and p.st_conc != 0)";
				endif;
			}
			
			
			if(!empty($pesq['buscafatura'])) $where .= " and f.n_documento like '%".$pesq['buscafatura']."%'";
			
			if(isset($pesq['fil'])){
				if($pesq['fil']=='avencerhoje'){
					$where = " and p.valor_pago is NULL and p.vencimento = '".date('Y-m-d')."'";
				}elseif($pesq['fil']=='avencersem'){
					$data = date('Y-m-d',mktime(0,0,0,date('m'),date('d')+7,date('Y')));
					$where = " and p.valor_pago is NULL and p.vencimento >= '".date('Y-m-d')."' and p.vencimento <= '".$data."'";
				}elseif($pesq['fil']=='vencidas'){
					$where = " and p.valor_pago is NULL and p.vencimento < '".date('Y-m-d')."'";
				}elseif($pesq['fil']=='avencer'){
					$where = " and p.valor_pago is NULL and p.vencimento >= '".date('Y-m-d')."'";
				}
			}
			
			//------- Filtro de datas --------------------------------------------
			if(isset($pesq['datapesq'])){
				if($pesq['datapesq']==0){
					if((!empty($pesq['dtini'])) || (!empty($pesq['dtfim']))){
						if(!empty($pesq['dtini'])) $di	= substr($pesq['dtini'],6,4).'-'.substr($pesq['dtini'],3,2).'-'.substr($pesq['dtini'],0,2);
						if(!empty($pesq['dtfim'])) $df	= substr($pesq['dtfim'],6,4).'-'.substr($pesq['dtfim'],3,2).'-'.substr($pesq['dtfim'],0,2);
					
						if((!empty($di)) and (!empty($df))):
							$where .= ' and p.vencimento between "'.$di.'" and "'.$df.'"';
						elseif((!empty($di)) and (empty($df))):
							$where .= ' and p.vencimento >= "'.$di.'"';
						elseif((empty($di)) and (!empty($df))):
							$where .= ' and p.vencimento <= "'.$df.'"';
						endif;
					}
				}else{
					if((!empty($pesq['dtini'])) || (!empty($pesq['dtfim']))){
						if(!empty($pesq['dtini'])) $di	= substr($pesq['dtini'],6,4).'-'.substr($pesq['dtini'],3,2).'-'.substr($pesq['dtini'],0,2);
						if(!empty($pesq['dtfim'])) $df	= substr($pesq['dtfim'],6,4).'-'.substr($pesq['dtfim'],3,2).'-'.substr($pesq['dtfim'],0,2);
					
						if((!empty($di)) and (!empty($df))):
							$where .= ' and f.emissao between "'.$di.'" and "'.$df.'"';
						elseif((!empty($di)) and (empty($df))):
							$where .= ' and f.emissao >= "'.$di.'"';
						elseif((empty($di)) and (!empty($df))):
							$where .= ' and f.emissao <= "'.$df.'"';
						endif;
					}
				}
			}
			
			$group = "";
			$order = "";
						
			if(isset($pesq['tpgroup']) and $pesq['tpgroup'] == 1):
				$group = "p.id";
				$order = "f.emissao";
				$where .= " and p.baixa = 0";
			else:
				$group = "f.id";
				$order = "p.vencimento";
			endif;
			
			if(isset($pesq['limite']) and $pesq['limite']):
				$limite = $pesq['limite'];
				$order = "p.vencimento";
			else:
				$limite = '10';
			endif;			
			
			if($where != ""){
			    $limit = "";
			    $limite = '100000000000';
			}
						
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('f'=>'tb_financeirorec','*'), 
				array('f.*','f.id as idfin','DATE_FORMAT(f.emissao,"%d/%m/%Y") as dtcad','c.EMPRESA', 'DATE_FORMAT(p.vencimento,"%d/%m/%Y") as dtvencimento', 
				'p.baixa as quitado','p.st_conc as conciliado', 'f.baixa as contaquitada','f.n_documento as fat','p.parc','u.nome'))
				->joinLeft(array('c'=>'clientes'),'c.ID = f.id_fornecedor')
				->joinLeft(array('p'=>'tb_financeirorecparc'),'f.id = p.id_financeirorec and p.sit = true')
				->joinLeft(array('u'=>'tb_usuarios'),'u.id = f.id_usuarios')
				->where("f.sit = true ".$where.$limit)
				->order($order)
				->order('f.id desc')
				->group($group)
				->limit($limite);
			
			$stmt = $db->query($select);
			
			return $stmt->fetchAll();
		}
		
		
		//--- Listar duplicatas -------------------------------
		public function listaDuplicatas($pesq){
		    
		    $bof	= new FinanceiroModel();
		    $boprec = new FinanceiroreceberparcModel();
		    
			$limite  = " and f.emissao >= '".date('Y-m-01',mktime(0,0,0,date('m'),date('d')-7,date('Y')))."'";
			$periodo = "apartir de ".date('01-m-Y',mktime(0,0,0,date('m'),date('d')-7,date('Y')));
			$where = "";
				
			if($pesq['bancorecebimentorec'] != 0){
			    $where .= " and id_financeirocontas = '".$pesq['bancorecebimentorec']."'";
			}
			
			//------- Filtro de datas --------------------------------------------
			
		    if(!empty($pesq['dtini'])) $di	= substr($pesq['dtini'],6,4).'-'.substr($pesq['dtini'],3,2).'-'.substr($pesq['dtini'],0,2);
		    if(!empty($pesq['dtfim'])) $df	= substr($pesq['dtfim'],6,4).'-'.substr($pesq['dtfim'],3,2).'-'.substr($pesq['dtfim'],0,2);
		    
			if((!empty($pesq['dtini'])) || (!empty($pesq['dtfim']))){
				if((!empty($di)) and (!empty($df))) 	$where .= ' and p.dt_descontodup between "'.$di.'" and "'.$df.'"';
				elseif((!empty($di)) and (empty($df))) 	$where .= ' and p.dt_descontodup >= "'.$di.'"';
				elseif((empty($di)) and (!empty($df))) 	$where .= ' and p.dt_descontodup <= "'.$df.'"';

				$limite = "";
			}
			
			if((!empty($di)) and (!empty($df))) 	$periodo = 'de '.str_replace('-', '/', $pesq['dtini']).' à '.str_replace('-', '/', $pesq['dtfim']);
			elseif((!empty($di)) and (empty($df))) 	$periodo = 'apartir de '.str_replace('-', '/', $pesq['dtini']);
			elseif((empty($di)) and (!empty($df)))  $periodo = 'até '.str_replace('-', '/', $pesq['dtfim']);

			
			foreach (FinanceiroBO::listarContasbancarias($pesq['bancorecebimentorec']) as $contasbancarias){
				//--- busco e agrupo duplicatas descontadas por data do desconto ------------------------------------------------
				$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
				$db->setFetchMode(Zend_Db::FETCH_OBJ);
					
				$select = $db->select();
					
				$select->from(array('f'=>'tb_financeirorec','*'), array('DATE_FORMAT(p.dt_descontodup,"%d/%m/%Y") as dtdesc'))
						->join(array('p'=>'tb_financeirorecparc'),'f.id = p.id_financeirorec and p.sit = true')
						
						->where("f.sit = true and p.descontodup = 1 ".$where.$limite." and p.id_financeirocontas = ".$contasbancarias->id)
						->group('p.dt_descontodup')
						->order('p.dt_descontodup asc');
					
				$stmt = $db->query($select);
				
				$objContas =  $stmt->fetchAll();
			
				if(count($objContas)>0){
				    foreach ($objContas as $descontogrup){
				        $dtgrup 	= $descontogrup->dtdesc;
				        $dtgrusql	= $descontogrup->dt_descontodup;
					
				        $wheredt = ' and p.dt_descontodup = "'.$dtgrusql.'"';
				        
						$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
						$db->setFetchMode(Zend_Db::FETCH_OBJ);
							
						$select = $db->select();
							
						$select->from(array('f'=>'tb_financeirorec','*'), array('f.*','f.id as idfin',
					        'DATE_FORMAT(f.emissao,"%d/%m/%Y") as dtcad',
					        'DATE_FORMAT(p.dt_descontodup,"%d/%m/%Y") as dtdesc',
					        'DATE_FORMAT(p.vencimento,"%d/%m/%Y") as dtvencimento',
					        'DATE_FORMAT(p.dt_pagamento,"%d/%m/%Y") as dtpagamento',
					        'c.EMPRESA', 'p.baixa as quitado','f.bloq as conciliado', 'f.baixa as contaquitada','f.n_documento as fat','p.parc','p.id as idparc'))
							->join(array('p'=>'tb_financeirorecparc'),'f.id = p.id_financeirorec and p.sit = true')
							->joinLeft(array('c'=>'clientes'),'c.ID = f.id_fornecedor')
							->joinLeft(array('pl'=>'tb_financeiroplcontas'),'pl.id = p.id_financeiroplcontas')
							
							->where("f.sit = true and p.descontodup = 1 ".$wheredt." and p.id_financeirocontas = ".$contasbancarias->id)
							->order('p.dt_descontodup asc');
					
						$stmt = $db->query($select);
						
						$objContasreceber =  $stmt->fetchAll();
						
						?>
						<div class="widget">
						<div class="head" style="border-bottom: 0px solid #d5d5d5;"><h5 class="iMoney"><?php echo $contasbancarias->nome?> (<?php echo $dtgrup?>)</h5></div>
						<?php 
						if(count($objContasreceber)>0){
							?>
							<table style="width: 100%;" class="tableStatic" >
				            	<thead>
				                	<tr>
				                        <td >Id</td>
				                        <td >Cliente</td>
				                        <td >Fatura</td>
				                        <td >Emissão</td>
				                        <td >Valor</td>
				                        <td >Valor Adiant</td>
				                        <td >Dt Venc</td>
				                        <td >Dt pag</td>
				                        <td >Juros</td>	                        
				                    </tr>
				                </thead>
				                <tbody>
								<?php			
		
								$total = $totaldesc = $totaljuros = 0;
								foreach ($objContasreceber as $listreceber){
								$total += $listreceber->valor_apagar;
								$totaldesc += $listreceber->valor_pago;
		
								?>
								<tr  >
									<td style="text-align: center;" >
									    <a href="/admin/administracao/financeiroztlreccad/rec/<?=md5($listreceber->idfin)?>" target="_blank">R<?=substr("000000".$listreceber->idfin,-6)?></a>										
									</td>
									<td style="text-align: left;" >
									    <a href="javascript:void(0)" title="<?php if(($listreceber->id_fornecedor!=0) || ($listpagar->$listreceber!=NULL)) echo $listreceber->EMPRESA; else echo $listreceber->out_fornecedor; ?>">
											<?php echo $listreceber->EMPRESA?>
										</a>									
									</td>
									<td style="text-align: center;" >
										<a href="javascript:void(0)" title="<?php if(($listreceber->id_fornecedor!=0) || ($listpagar->$listreceber!=NULL)) echo $listreceber->EMPRESA; else echo $listreceber->out_fornecedor; ?>">
										<?php echo $listreceber->fat?>/<?php echo $listreceber->parc?>
										</a>
									</td>
									<td style="text-align: center;">
										<?=$listreceber->dtcad?>
									</td>
									<td style="text-align: right;">
										<?=number_format($listreceber->valor_apagar,2,",",".")?>
									</td>
									<td style="text-align: right;">
										<?=number_format($listreceber->valor_pago,2,",",".")?>
									</td>
									<td style="text-align: center;">
										<?=$listreceber->dtvencimento?>
									</td>
									<td style="text-align: center;">
										<?=$listreceber->dtpagamento?>
									</td>
									<td style="text-align: right;">
										<?php 
										if(count($boprec->fetchAll('id_financeiroplcontas = 121 and id_parcrelacionada = '.$listreceber->idparc))>0){
											foreach ($boprec->fetchAll('id_financeiroplcontas = 121 and id_parcrelacionada = '.$listreceber->idparc) as $jurosparc);
											echo number_format($jurosparc->valor_pago,2,",",".");
											$totaljuros += $jurosparc->valor_pago;
										}
										?>
									</td>					  
								</tr>
								<?php								
								}					
								?>
								<tr  >
									<td style="background-color: #E5E3E3" colspan="4">&nbsp;</td>
									<td style="text-align: right; background-color: #E5E3E3; font-weight: bold;">
										<?=number_format($total,2,",",".")?>
									</td>
									<td style="text-align: right; background-color: #E5E3E3; font-weight: bold;">
										<?=number_format($totaldesc,2,",",".")?>
									</td>
									<td style="background-color: #E5E3E3" colspan="2">&nbsp;</td>
									<td style="text-align: right; background-color: #E5E3E3; font-weight: bold;" >
										<?=number_format($totaljuros,2,",",".")?>
									</td>					  
								</tr>
								</tbody>
							</table>
							
						<?php 
					    }else{
							?>
							<div style="border-top: 1px solid #d5d5d5; padding: 15px">
					 			Nenhuma conta encontrada!
							</div>	
							<?php 
						}
		
						?></div><?php 
					}		
				}
			}
		}
		
		//--- Listar duplicatas -------------------------------
		public function buscaDuplicatasremessa($pesq){
		
			$bof	= new FinanceiroModel();
			$boprec = new FinanceiroreceberparcModel();
		
			$where = "";
			$returno = "";
			//------- Filtro de datas --------------------------------------------
				
			if(!empty($pesq['dtini'])) $di	= substr($pesq['dtini'],6,4).'-'.substr($pesq['dtini'],3,2).'-'.substr($pesq['dtini'],0,2);
			if(!empty($pesq['dtfim'])) $df	= substr($pesq['dtfim'],6,4).'-'.substr($pesq['dtfim'],3,2).'-'.substr($pesq['dtfim'],0,2);
		
			if((!empty($pesq['dtini'])) and (!empty($pesq['dtfim']))){
				if((!empty($di)) and (!empty($df))) 	$where .= ' and p.dt_descontodup between "'.$di.'" and "'.$df.'"';
			}
				
			foreach (FinanceiroBO::listarContasbancarias($pesq['bancorecebimentorec']) as $contasbancarias){
				//--- busco e agrupo duplicatas descontadas por data do desconto ------------------------------------------------
				$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
				$db->setFetchMode(Zend_Db::FETCH_OBJ);
					
				$select = $db->select();
					
				$select->from(array('f'=>'tb_financeirorec','*'), array('DATE_FORMAT(p.dt_descontodup,"%d/%m/%Y") as dtdesc'))
					->join(array('p'=>'tb_financeirorecparc'),'f.id = p.id_financeirorec and p.sit = true')
					->where("f.sit = true and p.descontodup = 1 ".$where." and p.id_financeirocontas = ".$contasbancarias->id)
					->group('p.dt_descontodup')
					->order('p.dt_descontodup asc');
					
				$stmt = $db->query($select);
		
				$objContas =  $stmt->fetchAll();
					
				if(count($objContas)>0){
					foreach ($objContas as $descontogrup){
						$dtgrup 	= $descontogrup->dtdesc;
						$dtgrusql	= $descontogrup->dt_descontodup;
							
						$wheredt = ' and p.dt_descontodup = "'.$dtgrusql.'"';
		
						$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
						$db->setFetchMode(Zend_Db::FETCH_OBJ);
							
						$select = $db->select();
							
						$select->from(array('f'=>'tb_financeirorec','*'), array('f.*','f.id as idfin',
							'DATE_FORMAT(f.emissao,"%d/%m/%Y") as dtcad',
							'DATE_FORMAT(p.dt_descontodup,"%d/%m/%Y") as dtdesc',
							'DATE_FORMAT(p.vencimento,"%d/%m/%Y") as dtvencimento',
							'DATE_FORMAT(p.dt_pagamento,"%d/%m/%Y") as dtpagamento',
							'c.EMPRESA', 'p.baixa as quitado','f.bloq as conciliado', 'f.baixa as contaquitada','f.n_documento as fat','p.parc','p.id as idparc'))
							->join(array('p'=>'tb_financeirorecparc'),'f.id = p.id_financeirorec and p.sit = true')
							->joinLeft(array('c'=>'clientes'),'c.ID = f.id_fornecedor')
							->where("f.sit = true and p.descontodup = 1 ".$wheredt." and p.id_financeirocontas = ".$contasbancarias->id)
							->order('p.dt_descontodup asc');
							
						$stmt = $db->query($select);
		
						$objContasreceber =  $stmt->fetchAll();
						 
						if(count($objContasreceber)>0){
						$returno .= '
						<table style="width: 100%; border: 1px solid #d5d5d5; margin-top: 10px" >
			            	<thead>
			            		<tr>
			                        <td colspan="8" style="background-color: #E5E3E3; font-weight: bold; text-align: left; border: 1px solid #d5d5d5; padding: 5px;">'.$contasbancarias->nome.' ('.$dtgrup.')</td>                       
			                    </tr>
			                	<tr>
			                        <td  style="background-color: #E5E3E3; font-weight: bold; border: 1px solid #d5d5d5; padding: 5px; text-align: center">Id</td>
			                        <td  style="background-color: #E5E3E3; font-weight: bold; border: 1px solid #d5d5d5; padding: 5px; text-align: center">Fatura</td>
			                        <td  style="background-color: #E5E3E3; font-weight: bold; border: 1px solid #d5d5d5; padding: 5px; text-align: center">Emissão</td>
			                        <td style="background-color: #E5E3E3; font-weight: bold; border: 1px solid #d5d5d5; padding: 5px; text-align: center" >Valor</td>
			                        <td  style="background-color: #E5E3E3; font-weight: bold; border: 1px solid #d5d5d5; padding: 5px; text-align: center">Valor Adiant</td>
			                        <td  style="background-color: #E5E3E3; font-weight: bold; border: 1px solid #d5d5d5; padding: 5px; text-align: center">Dt Venc</td>
			                        <td  style="background-color: #E5E3E3; font-weight: bold; border: 1px solid #d5d5d5; padding: 5px; text-align: center">Dt pag</td>
			                        <td  style="background-color: #E5E3E3; font-weight: bold; border: 1px solid #d5d5d5; padding: 5px; text-align: center">Juros</td>	                        
			                    </tr>
			                </thead>
			                <tbody>';
						
							$total = $totaldesc = $totaljuros = 0;
							foreach ($objContasreceber as $listreceber){
								$total += $listreceber->valor_apagar;
								$totaldesc += $listreceber->valor_pago;
		
								$empresa = "";
								if(($listreceber->id_fornecedor!=0) || ($listpagar->$listreceber!=NULL)){
									$empresa = $listreceber->EMPRESA;
								}else{
									$empresa = $listreceber->out_fornecedor; 
			    				}
								
								$returno .= '
								<tr  >
									<td style="text-align: center;" >
									    R'.substr("000000".$listreceber->idfin,-6).'/'.$listreceber->parc.'										
									</td>
									<td style="text-align: left;" >
										'.$listreceber->fat.' - '.$empresa.'
									</td>
									<td style="text-align: center;">
										'.$listreceber->dtcad.'
									</td>
									<td style="text-align: right;">
										'.number_format($listreceber->valor_apagar,2,",",".").'
									</td>
									<td style="text-align: right;">
										'.number_format($listreceber->valor_pago,2,",",".").'
									</td>
									<td style="text-align: center;">
										'.$listreceber->dtvencimento.'
									</td>
									<td style="text-align: center;">
										'.$listreceber->dtpagamento.'
									</td>
									<td style="text-align: right;">';
								 
									if(count($boprec->fetchAll('id_financeiroplcontas = 121 and id_parcrelacionada = '.$listreceber->idparc))>0){
										foreach ($boprec->fetchAll('id_financeiroplcontas = 121 and id_parcrelacionada = '.$listreceber->idparc) as $jurosparc);
										$returno .= number_format($jurosparc->valor_pago,2,",",".");
										$totaljuros += $jurosparc->valor_pago;
									}
								$returno .= '</td></tr>';
							}					

							$returno .= '
								<tr  >
									<td style="background-color: #E5E3E3" colspan="3">&nbsp;</td>
									<td style="text-align: right; background-color: #E5E3E3; font-weight: bold;">
										'.number_format($total,2,",",".").'
									</td>
									<td style="text-align: right; background-color: #E5E3E3; font-weight: bold;">
										'.number_format($totaldesc,2,",",".").'
									</td>
									<td style="background-color: #E5E3E3" colspan="2">&nbsp;</td>
									<td style="text-align: right; background-color: #E5E3E3; font-weight: bold;" >
										'.number_format($totaljuros,2,",",".").'
									</td>					  
								</tr>
								</tbody>
							</table>';
						 
					    }else{
							$returno .= '<div style="border-top: 1px solid #d5d5d5; padding: 15px">Nenhuma duplicata encontrada!</div>'; 
						}
					}		
				}
			}
			
			return $returno;
			
		}
		
		
		/* Usado em Relatorios::financeirorelreceber -------------------------------- */
		function buscaContasafingrupadas($pesq){
		    $bo 	= new FinanceiroModel();
		    $bop	= new FinanceiroreceberModel();
		    
		    
		    if((!empty($pesq['dtini'])) || (!empty($pesq['dtfim']))):
			    if(!empty($pesq['dtini'])) $di	= substr($pesq['dtini'],6,4).'-'.substr($pesq['dtini'],3,2).'-'.substr($pesq['dtini'],0,2);
			    if(!empty($pesq['dtfim'])) $df	= substr($pesq['dtfim'],6,4).'-'.substr($pesq['dtfim'],3,2).'-'.substr($pesq['dtfim'],0,2);
			    
			    if((!empty($di)) and (!empty($df))):
				    $where .= ' and f.emissao between "'.$di.'" and "'.$df.'"';				    
			    elseif((!empty($di)) and (empty($df))):
				    $where .= ' and f.emissao >= "'.$di.'"';				    
			    elseif((empty($di)) and (!empty($df))):
				    $where .= ' and f.emissao <= "'.$df.'"';
			    endif;
		    endif;
		    
		    
		    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		    $db->setFetchMode(Zend_Db::FETCH_OBJ);
		    	
		    $select = $db->select();
		    	
		    $select->from(array('f'=>'tb_financeirorec','*'), array('DATE_FORMAT(f.emissao,"%d/%m/%Y") as dtcad'))
		    		->where("f.sit = true and f.baixa = 0 ".$where)
		    		->order('f.emissao asc')
		    		->group('f.emissao');
		    	
		     
		    $stmt = $db->query($select);
		    return $stmt->fetchAll();
		    
		}
		
		function gravarCreditopag($params){
		    try{
			    $bo 	= new FinanceiroModel();
			    $bofc	= new FinanceirocreditosModel();
			    
			    $bofc->delete("id_financeiropag = ".$params['idpagamento']);
			    $datacred = array('id_creditos' => $params['credito'], 'id_financeiropag' => $params['idpagamento']);
			    $bofc->insert($datacred);
			    
		    }catch (Zend_Exception $e){
				$boerro	= new ErrosModel();
				$dataerro = array('descricao' => $e->getMessage(), 'pagina' => 'CreditoBO::gravarCreditopag()');
				$boerro->insert($dataerro);
				return false;
			}
		    	
		}
		
		function gravarContasrec($params){
			$bo 	= new FinanceiroModel();
			$bop	= new FinanceiroreceberModel();
			$boa	= new FinanceiroanexoreceberModel();
			$boparc = new FinanceiroreceberparcModel();
			$bofc	= new FinanceirocreditosModel();

			$usuario = Zend_Auth::getInstance()->getIdentity();
			
			if(($params['fornrec']!=0) and ($params['fornrec']!='out')):
				$forn = explode('|', $params['fornrec']);
				
				if($forn[1]==0):
					$array['id_fornecedor']		=	$forn[0];
					$array['id_usuarios']		=	NULL;
					$array['out_fornecedor']	=   NULL;
				else:
					$array['id_fornecedor']		=	NULL;
					$array['id_usuarios']		=	$forn[0];
					$array['out_fornecedor']	=   NULL;
				endif;
			elseif(!empty($params['outfornrec'])):
				$array['out_fornecedor']	=	$params['outfornrec'];
				$array['id_usuarios']		=	NULL;
				$array['id_fornecedor']		=   NULL;
			endif;
			
			if(isset($params['faturarec']) and isset($params['emissaorec']) and isset($params['valortotalrec'])){
				$array['emissao']			=	substr($params['emissaorec'],6,4).'-'.substr($params['emissaorec'],3,2).'-'.substr($params['emissaorec'],0,2);
				$array['n_documento']		=	$params['faturarec'];
				$array['obs']				=	$params['obsrec'];
				$array['moeda']				=   $params['moedarecconta'];
				$array['valor']				=	str_replace(',','.',str_replace('.','',$params['valortotalrec']));
				$array['sit']				= 	true;
			}

			if(!empty($params['idcontarec'])){
				foreach ($bop->fetchAll("id = ".$params['idcontarec']) as $conta);
				$array['baixa']				= 	0;
				if($conta->baixa == 0):
					$bop->update($array, "id = ".$params['idcontarec']);
				endif;
				$id = $params['idcontarec'];										
			}else{
				$array['baixa']				= 	0;
				$id = $bop->insert($array);
			}					
								
				
			for ($i=1;$i<=$params['intparcela'];$i++){
				$qtparc = count($boparc->fetchAll("sit = true and id_financeirorec = ".$id));
				
				if(!empty($params['vencpar_'.$i]) and !empty($params['moedapar_'.$i]) and !empty($params['contapar_'.$i])){
			
					$arrayparc['id_financeirorec']		=	$id;
					$arrayparc['emissao']				= 	date("Y-m-d"); 
					$arrayparc['vencimento']			=	substr($params['vencpar_'.$i],6,4).'-'.substr($params['vencpar_'.$i],3,2).'-'.substr($params['vencpar_'.$i],0,2);
					$arrayparc['moeda']					=	$params['moedapar_'.$i];
					$arrayparc['valor_apagar']			=	str_replace(',','.',str_replace('.','',$params['valorpar_'.$i]));
					$arrayparc['id_financeiroplcontas']	=	$params['contapar_'.$i];
					$arrayparc['sit']					= 	true; 
					$arrayparc['baixa']					= 	0;
					$arrayparc['parc']					= 	$qtparc+1;
					
					if(!empty($params['valorpago_'.$i]) and !empty($params['datapar_'.$i]) and ($params['bancopar_'.$i]!=0)):
						$arrayparc['valor_pago']			=	str_replace(',','.',str_replace('.','',$params['valorpago_'.$i]));
						$arrayparc['txcambio']				=	str_replace(',','.',str_replace('.','',$params['txcambiopago_'.$i]));
						$arrayparc['id_financeirocontas']	=	$params['bancopar_'.$i];
						$arrayparc['dt_pagamento']			=	substr($params['datapar_'.$i],6,4).'-'.substr($params['datapar_'.$i],3,2).'-'.substr($params['datapar_'.$i],0,2);
						$arrayparc['baixa']					= 	0;			
	
					endif;
					
					$boparc->insert($arrayparc);
				}
			}
			
			foreach (FinanceiroBO::listarParcelasareceber(md5($id)) as $listaparc){
				
				if(!empty($params['valorrec_'.$listaparc->idparc]) and !empty($params['vencimentorec_'.$listaparc->idparc]) and ($params['planocontarec_'.$listaparc->idparc]!=0)):
					$arrayparcedit['vencimento']		=	substr($params['vencimentorec_'.$listaparc->idparc],6,4).'-'.substr($params['vencimentorec_'.$listaparc->idparc],3,2).'-'.substr($params['vencimentorec_'.$listaparc->idparc],0,2);
					$arrayparcedit['moeda']				=	$params['moedarec_'.$listaparc->idparc];
					$arrayparcedit['valor_apagar']		=	str_replace(',','.',str_replace('.','',$params['valorrec_'.$listaparc->idparc]));
					
					$pconta	= explode("|", $params['planocontarec_'.$listaparc->idparc]);
					$arrayparcedit['id_financeiroplcontas']	=	$pconta[0];
						 
					$boparc->update($arrayparcedit, "id = ".$listaparc->idparc);
				endif;
			
				if(!empty($params['valorrecebimentorec_'.$listaparc->idparc]) and !empty($params['datarecebimentorec_'.$listaparc->idparc]) and ($params['bancorecebimentorec_'.$listaparc->idparc]!=0)){
					$arrayrec = array();
					$arrayrec['txcambio']				=	str_replace(',','.',str_replace('.','',$params['txcambio_'.$listaparc->idparc]));
					$arrayrec['id_financeirocontas']	=	$params['bancorecebimentorec_'.$listaparc->idparc];
					$arrayrec['valor_pago']				=	str_replace(',','.',str_replace('.','',$params['valorrecebimentorec_'.$listaparc->idparc]));
					
					if($params['descontardup_'.$listaparc->idparc]){
					    $arrayrec['dt_descontodup']			=	substr($params['datarecebimentorec_'.$listaparc->idparc],6,4).'-'.substr($params['datarecebimentorec_'.$listaparc->idparc],3,2).'-'.substr($params['datarecebimentorec_'.$listaparc->idparc],0,2);
					    $arrayrec['descontodup']			= 	1;
					    //$arrayrec['valor_descontado']		=	str_replace(',','.',str_replace('.','',$params['valorrecebimentorec_'.$listaparc->idparc]));
					    $arrayrec['baixa']					= 	0;
					
					}else{
						$arrayrec['dt_pagamento']			=	substr($params['datarecebimentorec_'.$listaparc->idparc],6,4).'-'.substr($params['datarecebimentorec_'.$listaparc->idparc],3,2).'-'.substr($params['datarecebimentorec_'.$listaparc->idparc],0,2);
					    $arrayrec['baixa']					= 	1;					    
					}
					
					$boparc->update($arrayrec, "id = ".$listaparc->idparc);
					
				}
				
				if($listaparc->descontodup == 1){
				    if(!empty($params['databaixaparc_'.$listaparc->idparc])){
						$arrayrec2 = array();

						//-- baixa parcela -----------------------------
						$dt_pagamento 							= "";
						$dt_pagamento							=	substr($params['databaixaparc_'.$listaparc->idparc],6,4).'-'.substr($params['databaixaparc_'.$listaparc->idparc],3,2).'-'.substr($params['databaixaparc_'.$listaparc->idparc],0,2);
					    $arrayrec2['dt_pagamento']				=	$dt_pagamento;
					    $arrayrec2['baixa']						= 	1;
					    //$arrayrec2['valor_pago']				=	$listaparc->valor_apagar - (str_replace(',','.',str_replace('.','',$params['descontoparc_'.$listaparc->idparc])));
					    $arrayrec2['id_financeirocontasdup']	=	$params['bancoparc_'.$listaparc->idparc];
					    
					    $boparc->update($arrayrec2, "id = ".$listaparc->idparc);
					    
					    //-- cria parcelas de juros e multa -----------------------
					    
					    $arrrem = array('sit' => 0);
					    $boparc->update($arrrem, "id_parcrelacionada = ".$listaparc->idparc);
					    
					    $qtparc = count($boparc->fetchAll("sit = true and id_financeirorec = ".$id));
					    
					    if($listaparc->extornado){
					    	
					    	$arrayparcdup['id_financeirorec']			=	$id;
					    	$arrayparcdup['emissao']					= 	$dt_pagamento;
					    	$arrayparcdup['vencimento']					=	$dt_pagamento;
					    	$arrayparcdup['moeda']						=	$listaparc->moeda;
					    	$arrayparcdup['valor_apagar']				=	$listaparc->valor_apagar;
					    	$arrayparcdup['id_financeiroplcontas']		=	205;
					    	$arrayparcdup['sit']						= 	true;
					    	$arrayparcdup['parc']						= 	$qtparc+1;
					    	$arrayparcdup['valor_pago']					=	$listaparc->valor_apagar - (str_replace(',','.',str_replace('.','',$params['descontoparc_'.$listaparc->idparc])));
					    	$arrayparcdup['id_financeirocontas']		=	$params['bancoparc_'.$listaparc->idparc];
					    	$arrayparcdup['dt_pagamento']				=	$dt_pagamento;
					    	$arrayparcdup['baixa']						= 	1;
					    	$arrayparcdup['id_parcrelacionada']			=	$listaparc->idparc;
					    
					    	$boparc->insert($arrayparcdup);
					    }
					    
					    $qtparc = count($boparc->fetchAll("sit = true and id_financeirorec = ".$id));
					    
					    if(!empty($params['multaparc_'.$listaparc->idparc])){
					    		
					    	$arrayparcdup['id_financeirorec']			=	$id;
					    	$arrayparcdup['emissao']					= 	$dt_pagamento;
					    	$arrayparcdup['vencimento']					=	$dt_pagamento;
					    	$arrayparcdup['moeda']						=	$listaparc->moeda;
					    	$arrayparcdup['valor_apagar']				=	str_replace(',','.',str_replace('.','',$params['multaparc_'.$listaparc->idparc]));
					    	$arrayparcdup['id_financeiroplcontas']		=	121;
					    	$arrayparcdup['sit']						= 	true;
					    	$arrayparcdup['parc']						= 	$qtparc+1;
					    	$arrayparcdup['valor_pago']					=	str_replace(',','.',str_replace('.','',$params['multaparc_'.$listaparc->idparc]));
					    	$arrayparcdup['id_financeirocontas']		=	$params['bancoparc_'.$listaparc->idparc];
					    	$arrayparcdup['dt_pagamento']				=	$dt_pagamento;
					    	$arrayparcdup['baixa']						= 	1;
					    	$arrayparcdup['id_parcrelacionada']			=	$listaparc->idparc;
					    	
					    	$boparc->insert($arrayparcdup);
					    }
					 	
				    }elseif($listaparc->baixa == 0){
				        
				        if($params['dataextornoparc_'.$listaparc->idparc] != "" and $params['tarifaextornoparc_'.$listaparc->idparc]){

					        foreach($bop->fetchAll("id = ".$id) as $recextorno);
					        
					        $tarifa 	= str_replace(',','.',str_replace('.','',$params['tarifaextornoparc_'.$listaparc->idparc]));
					        $idconta 	= "R".substr("000000".$id,-6);
					        
					        $arrayPag = array(
					        	"fornpag" 				=> $recextorno->id_fornecedor."|0",
	        					"emissaopag" 			=> $params['dataextornoparc_'.$listaparc->idparc],
	        					"faturapag" 			=> $recextorno->n_documento,
	        					"moedapagconta" 		=> "BRL",
	        					"valortotalpag" 		=> number_format($listaparc->valor_apagar+$tarifa,2,",","."),
	        					"moedapar_2" 			=> "BRL",
		        				"valorpar_2" 			=> number_format($listaparc->valor_apagar,2,",","."),
		        				"vencpar_2" 			=> $params['dataextornoparc_'.$listaparc->idparc],
		        				"contapar_2" 			=> "221",
		        				"datapar_2" 			=> $params['dataextornoparc_'.$listaparc->idparc],
	        					"valorpago_2" 			=> number_format($listaparc->valor_apagar,2,",","."),
	        					"bancopar_2" 			=> $listaparc->id_financeirocontas,
								"moedapar_3" 			=> "BRL",
								"valorpar_3" 			=> number_format($tarifa,2,",","."),
								"vencpar_3" 			=> $params['dataextornoparc_'.$listaparc->idparc],
								"contapar_3" 			=> "191",
								"datapar_3" 			=> $params['dataextornoparc_'.$listaparc->idparc],
								"valorpago_3" 			=> number_format($tarifa,2,",","."),
								"bancopar_3" 			=> $listaparc->id_financeirocontas,
				        		"intparcela" 			=> "3",
				        		"idparcrec"  			=> $listaparc->idparc,
				        		"obspag" 				=> "Extorno de duplicata não paga no vencimento (".$idconta.")"
					        );
					        
					        FinanceiroBO::gravarContaspag($arrayPag);
					        
					        //$arrayrec2['extornado'] = 	1;
					        $boparc->update(array('extornado' => 1), "id = ".$listaparc->idparc);
				        }else{
							$arrayrec2 = array('dt_pagamento' => NULL, 'baixa' => 0);
							$boparc->update($arrayrec2, "id = ".$listaparc->idparc);
						}
				    }
				}
			}
			
			$baixa = 1;
			foreach (FinanceiroBO::listarParcelasareceber(md5($id)) as $listaparc):
				if($listaparc->baixa == 0):
					$baixa = 0;
				endif;
			endforeach;
			
			if(($baixa == 1) and (count(FinanceiroBO::listarParcelasareceber(md5($id)))>0)):
				$arrayb['baixa']		= 1;
				$bop->update($arrayb, "id = ".$id);
				LogBO::cadastraLog("ADM/Financeiro/Recebimentos",4,$usuario->id,$id,"BAIXA R".substr("000000".$id,-6,6));
			endif;
				
			//-- Creditos -------------------------------------
			$bofc->delete("id_financeirorec = ".$id);
			$creditos = explode(",", $params['idpurch'],-1);
			foreach ($creditos as $i => $value){
				if($value){
				    $datacred = array('id_creditos' => $value, 'id_financeirorec' => $id);
				    $bofc->insert($datacred);
				}    
			}
			
						
			/* Se alguma parcela foi paga, a conta eh baixada -----
			 * Para finalizar eh necessario que todas as parcelas sejam consilhadas no extrato bancario ----------
			* */
			
				//$usuario = Zend_Auth::getInstance()->getIdentity();
				//LogBO::cadastraLog("ADM/Financeiro/Recebimentos",2,$usuario->ID,$id,"Baixa ".$id);
			
			//---Arquivos-------------------------------
			$ic = 0;
			$listanex = "";
			if(count($boa->fetchAll('id_financeirorec = '.$id)>0)){
			    foreach ($boa->fetchAll('id_financeirorec = '.$id) as $listanex);
			    $ianex = explode(".",$listanex->nome);
				$ic = substr($ianex[0],-1);
			}
			
			$pasta = Zend_Registry::get('pastaPadrao')."public/sistema/upload/financeiro/receber";

			$upload = new Zend_File_Transfer_Adapter_Http();
			$upload->setDestination($pasta);
			$files = $upload->getFileInfo();
				
			if($files){
				
				foreach ($files as $file => $info){
					$ic++;
			
					$ext = substr(strrchr($info['name'], "."), 1);
					$nome = $id."_".$ic.".".$ext;
					$upload->addFilter('Rename', array('target' => $pasta.'/'.$nome, 'overwrite' => true));
			
					if ($upload->isValid($file)) {
						$upload->receive($file);
						$boa->insert(array('nome' => $nome, 'id_financeirorec' => $id));
					}
				}
			}
			 
	        return $id;
			
		}
		
		function removeParcelasareceber($var){
			$bof	= new FinanceiroModel();
			$boa	= new FinanceiroreceberparcModel();
			$array['sit']	= false;
			$boa->update($array,'md5(id) = "'.$var['parc'].'"');
			
			$qtparc = count($boa->fetchAll('sit = true and md5(id_financeirorec) = "'.$var['rec'].'"'));
			
			if($qtparc>0):
				$i = 0;
				foreach ($boa->fetchAll('sit = true and md5(id_financeirorec) = "'.$var['rec'].'"') as $listparc):
					$i++;
					$arraypar['parc']	= $i; 				
					$boa->update($arraypar, "id = ".$listparc->id);
				endforeach;
			endif;
			
			$arraypar['parc']	= 0;
			$boa->update($arraypar, 'sit = false and md5(id_financeirorec) = "'.$var['rec'].'"');			
			
		}
		
		function removePagamento($var){
			$bof	= new FinanceiroModel();
			$boa	= new FinanceiropagarModel();
			$bop	= new FinanceiropagarparcModel();
			$bope	= new PedidosvendaModel();
			$boc	= new VendacomissaoModel();
			$bocr	= new FinanceirocreditosModel();
			
			$array['sit']	= false;
			$bop->update($array,'md5(id_financeiropag) = "'.$var['pag'].'"');
			$boa->update($array,'md5(id) = "'.$var['pag'].'"');
			$bocr->update(array('id_financeiropag'=>NULL), "md5(id_financeiropag) = '".$var['pag']."'");
			
			$boc->delete("md5(id_financeiropag) = '".$var['pag']."'");
		}
		
		function removeRecebimento($var){
			$bof	= new FinanceiroModel();
			$boa	= new FinanceiroreceberModel();
			$bop	= new FinanceiroreceberparcModel();
			$bocr	= new FinanceirocreditosModel();
			
			$array['sit']	= false;
			$bop->update($array,'md5(id_financeirorec) = "'.$var['rec'].'"');
			$boa->update($array,'md5(id) = "'.$var['rec'].'"');
			
			$bocr->update(array('id_financeirorec'=>NULL), "md5(id_financeirorec) = '".$var['rec']."'");
		}
		
		function removeRecebimentonfe($var){
			$bof	= new FinanceiroModel();
			$boa	= new FinanceiroreceberModel();
			$bop	= new FinanceiroreceberparcModel();
				
			foreach ($boa->fetchAll('id_nfe = '.$var['rmfin']) as $fin);
			
			$array['sit']	= false;
			$bop->update($array,'id_financeirorec = '.$fin->id);
			$boa->update($array,'id = '.$fin->id);
			
			echo "sucessormfinanceiro";
		}
		
		function removeRecjuros(){
			
		}
		
		function removeRecextorno($var){
			FinanceiroBO::removePagamento($var);
		}
				
		function listarAnexosareceber($id){
			$bof	= new FinanceiroModel();
			$boa	= new FinanceiroanexoreceberModel();
			
			return $boa->fetchAll('md5(id_financeirorec) = "'.$id.'"');
		}
				
		function buscarContarec($id){
			$bof	= new FinanceiroModel();
			$bop	= new FinanceiroreceberModel();
						
			return $bop->fetchAll("md5(id) = '".$id."'");
		}
		
		function liberarContasrec($id){
			$bof	= new FinanceiroModel();
			$bop	= new FinanceiroreceberModel();
						
			$array['baixa']		= 1;			
			$bop->update($array, "md5(id) = '".$id."'");
			
		}
		
		function listarParcelasareceber($id, $tp = 0){

			if($tp == 0){
				$where = ' and f.id_parcrelacionada is NULL';
			}else{
				$where = ' and f.id_parcrelacionada is not NULL';
			}

			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('f'=>'tb_financeirorecparc','*'),array('f.*','f.id as idparc' ,'p.nome as nomepc','f.id_planocontas as idpc','f.id_financeiroplcontas as idplc','p.cod','p.navegacao'))
			        ->joinLeft(array('p'=>'tb_financeiroplcontas'), 'f.id_financeiroplcontas = p.id')
			        ->where('f.sit = true and md5(id_financeirorec) = "'.$id.'"'.$where)
			        ->order('f.id asc');
			  		
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		function listarRelparcreceber($id){
			try{
				$idparcelas = "";
				foreach (FinanceiroBO::listarParcelasareceber($id) as $parcelas){
					$idparcelas .= $parcelas->idparc.",";
				}
				
				if($idparcelas!=""){
					$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
					$db->setFetchMode(Zend_Db::FETCH_OBJ);
						
					$select = $db->select();
						
					$select->from(array('f'=>'tb_financeiropagparc','*'),array('f.*','f.id as idparc' ,'p.nome as nomepc','f.id_planocontas as idpc','f.id_financeiroplcontas as idplc','p.cod','p.navegacao'))
					->joinLeft(array('p'=>'tb_financeiroplcontas'), 'f.id_financeiroplcontas = p.id')
					->where('f.sit = true and f.id_financeirorecparc in ('.substr($idparcelas,0,-1).')')
					->order('f.id asc');
						
					$stmt = $db->query($select);
					return $stmt->fetchAll();
				}
			}catch (Zend_Exception $e){
				$boerro	= new ErrosModel();
				$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "FinanceiroBO::listarRelparcreceber()");
				$boerro->insert($dataerro);
				return 'erro';
			}
		}
		
		
		function liberaParcelasareceber($var){
			$bof	= new FinanceiroModel();
			$bor	= new FinanceiroreceberModel();
			$boa	= new FinanceiroreceberparcModel();
		
			$array['baixa']	= false;
			$boa->update($array,'md5(id) = "'.$var['parc'].'"');
			
			$arrayb['baixa']	= false;
			$bor->update($array,'md5(id) = "'.$var['rec'].'"');
			
			/* $baixas = 0;
			foreach (FinanceiroBO::listarParcelasareceber($var['rec']) as $listaparc):
				if($listaparc->baixa == 1):
					$baixas = 1;
				endif;
			endforeach;
						
			if($baixas == 0):
				$arrayb['baixa']	= false;
				$bor->update($array,'md5(id) = "'.$var['rec'].'"');					
			endif; */
		}
		
		//---- Conciliacao -----------------------------------------
		//--Validacao de contas------------------------------
		function listarConcilhacao($pesq, $tp="0"){
			
		    $usuario = Zend_Auth::getInstance()->getIdentity();
		    foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 71) as $list);
		    
		    if($list->visualizar==1){
			    $this->objIns		= $list->inserir;
			    $this->objEdi		= $list->editar;
			    $this->objLib		= $list->aba1;
			    $this->objAdm		= $list->aba2;
		    }
		    
		    try{

			    $conta = $pesq['contasval'];
			    
				$where = $extract = "";
				//----filtro por id-----------
				if(!empty($pesq['buscaidconc'])):
					$where	= " and f.id = ".$pesq['buscaidconc'];
				endif;
				
				//----filtro por valor-----------
				if((!empty($pesq['buscavliniconc'])) || (!empty($pesq['buscavlfimconc']))):
					if(!empty($pesq['buscavliniconc'])) $vli	= str_replace(",",".",str_replace(".","",$pesq['buscavliniconc']));
					if(!empty($pesq['buscavlfimconc'])) $vlf	= str_replace(",",".",str_replace(".","",$pesq['buscavlfimconc']));
				
					if((!empty($vli)) and (!empty($vlf))): 
						$where .= ' and f.valor >= "'.$vli.'" and f.valor <="'.$vlf.'"';
						$limit=1000;
						$extract = "";
					elseif((!empty($vli)) and (empty($vlf))): 
						$where .= ' and f.valor >= "'.$vli.'"';
						$limit=1000;
						$extract = "";
					elseif((empty($vli)) and (!empty($vlf))): 
						$where .= ' and f.valor <= "'.$vlf.'"';
						$limit=1000;
						$extract = "";
					endif;
				endif;
				
				//----filtro por data-----------
				if((!empty($pesq['dtiniconc'])) || (!empty($pesq['dtfimconc']))):
					if(!empty($pesq['dtiniconc'])) $di	= substr($pesq['dtiniconc'],6,4).'-'.substr($pesq['dtiniconc'],3,2).'-'.substr($pesq['dtiniconc'],0,2);
					if(!empty($pesq['dtfimconc'])) $df	= substr($pesq['dtfimconc'],6,4).'-'.substr($pesq['dtfimconc'],3,2).'-'.substr($pesq['dtfimconc'],0,2);
				
					if((!empty($di)) and (!empty($df))): 
						$where .= ' and f.data between "'.$di.'" and "'.$df.' 23:59:59"';
						$limit=1000;
						$extract = "";
					elseif((!empty($di)) and (empty($df))): 
						$where .= ' and f.data >= "'.$di.'"';
						$limit=1000;
						$extract = "";
					elseif((empty($di)) and (!empty($df))): 
						$where .= ' and f.data <= "'.$df.'"';
						$limit=1000;
						$extract = "";
					endif;
				endif;
				
				if($where=="") $where = " and f.data >= '".date("Y-m-01")."'";
				
				if(!empty($conta)){ 
				    
				    
				    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
				    $db->setFetchMode(Zend_Db::FETCH_OBJ);
				    $select = $db->select();
				    
				    $select->from(array('f'=>'tb_financeiroconcilia','*'), array('sum(f.valor) as total'))
				    		->where("f.situacao = 1 and f.id_financeirocontas = ".$conta);
				    
				    $stmt = $db->query($select);
				    $objSaldoc = $stmt->fetchAll();
				    
				    ?>
				    <div>
		    	 		<?php if(count($objSaldoc)>0){ ?>
		    	 		<table style="margin-top: 10px; margin-bottom: 10px; font-weight: bold; width: 40%; background-color: #ccc;"  >
		    				<tr>
		    					<td style="padding: 10px">
		    						Saldo
		    					</td>
		    					<td style="text-align: center;">
		    						<?=date("d/m/Y");?>					
		    					</td>
		    					<td style="padding: 10px; text-align: right;">
		    						<?php 
		    						foreach($objSaldoc as $saldo);
		    						echo number_format($saldo->total,2,",",".");
		    						?>
		    					</td>		
		    				</tr>
		            	</table>
		            	<?php } ?>
		          	</div>				 
		    		<?php
				    
					$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
					$db->setFetchMode(Zend_Db::FETCH_OBJ);
					
					$select = $db->select();
					
					$select->from(array('f'=>'tb_financeiroconcilia','*'),
				        array('f.*','f.id as idconc','DATE_FORMAT(f.data,"%d/%m/%Y") as dtcad', 'b.nome as bancotrans'))
						->joinLeft(array('b'=>'tb_financeirocontas'),'f.id_financeirocontastransf = b.id') 
						->where("f.situacao = 1 and f.id_financeirocontas = ".$conta.$where.$extract)
				        ->order('f.data desc')
				        ->order('f.id desc');
					
					$stmt = $db->query($select);
					
					$objConciliac = $stmt->fetchAll();
										
					if(!empty($objConciliac)){ ?>
						<div class="widget">
				 			<table style="width: 100%" class="tableStatic">
				            	<thead>
				                	<tr>
				                        <td >Id</td>
				                        <td >Data</td>
				                        <td >Valor</td>
				                        <td >Lanc</td>
				                        <td >Forn/Cliente</td>
				                        <td >Valor Lanc</td>
				                        <td >Fatura</td>
				                        <?php if($tp==1){ ?>
				                        <td >Conta</td>
				                        <td >Obs</td>
				                        <?php } ?>
				                        <td >Opções</td>
				                    </tr>
				                </thead>
				                <tbody>	
									<?php 
					        		$txconc = $transac = $pconci = $rconci = $idpconci = "";
												
									foreach ($objConciliac as $listconc){
										
										if($listconc->valor<0){ 
											$cor = "FF0000"; 
											$tpc = 1;
										}else{ 
											$cor = "";
											$tpc = 2;
										} 											
										
										$select = $db->select();
											
										if($tpc == 2){
											$select->from(array('f'=>'tb_financeirorec','*'), array('f.*','f.n_documento as docfatura','f.id as idfin','c.EMPRESA','u.nome as usuario','p.parc','pl.nome as plano'))
											->join(array('p'=>'tb_financeirorecparc'),'f.id = p.id_financeirorec and p.sit = true')
											->joinLeft(array('c'=>'clientes'),'c.ID = f.id_fornecedor')
											->joinLeft(array('u'=>'tb_usuarios'),'u.id = f.id_usuarios')
											->joinLeft(array('pl'=>'tb_financeiroplcontas'),'pl.id = p.id_financeiroplcontas')
											->where("f.sit = true and p.st_conc = ".$listconc->idconc);
										}else{
											$select->from(array('f'=>'tb_financeiropag','*'), array('f.*','f.n_documento as docfatura','f.id as idfin','c.EMPRESA','u.nome as usuario','p.parc','pl.nome as plano'))
											->join(array('p'=>'tb_financeiropagparc'),'f.id = p.id_financeiropag and p.sit = true')
											->joinLeft(array('c'=>'clientes'),'c.ID = f.id_fornecedor')
											->joinLeft(array('u'=>'tb_usuarios'),'u.id = f.id_usuarios')
											->joinLeft(array('pl'=>'tb_financeiroplcontas'),'pl.id = p.id_financeiroplcontas')
											->where("f.sit = true and p.st_conc = ".$listconc->idconc);
										}
											
										$stmt = $db->query($select);
										
										$objParcelas = $stmt->fetchAll();
										
										$countParc = count($objParcelas);
										
										if($countParc<1) $countParc = 1;
										
										?>
										<tr  >
											<td style="text-align: center;" rowspan="<?php echo $countParc?>">
												<font color="<?php echo $cor?>"><?php echo str_pad($listconc->idconc, 6, '0',STR_PAD_LEFT)?></font>
											</td>
											<td style="text-align: center;" rowspan="<?php echo $countParc?>">
												<font color="<?php echo $cor?>"><?php echo $listconc->dtcad?></font>
											</td>											
											<td style="text-align: right;" rowspan="<?php echo $countParc?>">
												<font color="<?php echo $cor?>"><?php echo number_format($listconc->valor,2,",",".")?></font>
											</td>
											<?php 
											
											if(($listconc->valida==0) and ($this->objIns==1)): $link = 'onclick="buscaContasconcilia('.$tpc.','.$listconc->idconc.','.$conta.',0);" style="cursor: hand; cursor: pointer; text-align: center; padding: 0px"'; 
											else: $link = 'style="text-align: center;"'; endif;
											
											$verparc = $verrow = 0;
											if(count($objParcelas)>0){
												$verparc = 1;
												foreach ($objParcelas as $listparc){
													$verrow++;
													if($verrow == 1){
														if($tpc == 1){
															?>
															<td style="text-align: center" <?php echo $link?>>
																P<?php echo str_pad($listparc->idfin, 6, '0',STR_PAD_LEFT)?></td>
									                        <td >
									                        <?php 
									                        if(($listparc->id_fornecedor!=0) and ($listparc->id_fornecedor!=NULL)) echo $listparc->EMPRESA;
									                        elseif(($listparc->id_usuarios!=NULL) and ($listparc->id_usuarios!="")) echo $listparc->usuario;
									                        else echo $listparc->out_fornecedor; ?></td>
									                        <td style="text-align: right;"><?php echo number_format($listparc->valor_pago,2,",",".")?></td>
									                        <td style="text-align: center;"><?php if($listparc->docfatura != "") echo $listparc->docfatura."/".$listparc->parc; ?></td>
									                        <?php if($tp==1){ ?>
									                        <td style="text-align: left;"><?php echo $listparc->plano?></td>
									                        <td style="text-align: left;"><?php echo $listparc->obs?></td>
									                        <?php } ?>
									                        <?php														
														}else{
															?>
															<td style="text-align: center" <?php echo $link?>>
																R<?php echo str_pad($listparc->idfin, 6, '0',STR_PAD_LEFT)?>
															</td>
									                        <td >
									                        <?php
									                        if(($listparc->id_fornecedor!=0) and ($listparc->id_fornecedor!=NULL)) echo $listparc->EMPRESA;
									                        elseif(($listparc->id_usuarios!=NULL) and ($listparc->id_usuarios!="")) echo $listparc->usuario;
									                        else echo $listparc->out_fornecedor;
									                        ?></td>
									                        <td style="text-align: right;"><?php echo number_format($listparc->valor_pago,2,",",".")?></td>
									                        <td style="text-align: center;"><?php if($listparc->docfatura != "") echo $listparc->docfatura."/".$listparc->parc; ?></td>
									                        <?php if($tp==1){ ?>
									                        <td style="text-align: left;"><?php echo $listparc->plano?></td>
									                        <td style="text-align: left;"><?php echo $listparc->obs?></td>
									                        <?php } ?>
									                        <?php
														}	
													}												
												}
											}
											
											if($listconc->id_financeirocontastransf!=""):
												$verparc = 1;
												?><td colspan="<?php if($tp==1){ echo 6;}else{ echo 4;} ?>"  <?php if(($listconc->valida==0) and ($this->objIns==1)):?>  onclick="buscaContasconcilia('<?php echo $tpc?>','<?php echo $listconc->idconc?>','<?php echo $conta?>',0,'');" style="cursor: hand; cursor: pointer; text-align: center; padding: 0px" <?php else: ?> style="text-align: center;" <?php endif; ?>><?php 
												echo " &nbsp; &nbsp;";
												echo strtoupper($listconc->bancotrans);
												
												if(!empty($listconc->tax_cambio)):
													echo " | ".number_format($listconc->tax_cambio,5,",",".");
												endif;
												
												if(!empty($listconc->conctransf)):
													echo " | ID ".$listconc->conctransf;
												endif;
												?></td><?php 
											endif;
											
											if($verparc==0){
												?><td colspan="<?php if($tp==1){ echo 6;}else{ echo 4;} ?>"  <?php echo $link?>><?php	
											}
											
											?>
											
											<td style="text-align: center;" rowspan="<?php echo $countParc?>">
												<?php if($this->objIns==1){ ?> 
													<?php if($listconc->valida==0){ ?>
													<a href="javascript:void(0);" onclick="editaConciliacao('<?php echo $listconc->dtcad?>','<?php echo number_format($listconc->valor,2,",",".")?>','<?php echo $listconc->idconc?>')" ><img src="/public/sistema/imagens/icons/middlenav/pencil.png" width="14" border="0" title="Editar"></a>&nbsp;
													<a href="javascript:void(0);" onclick="removeConcilia('<?php echo md5($listconc->idconc)?>');"><img src="/public/sistema/imagens/icons/middlenav/close.png" width="14" border="0" title="Remover"></a>&nbsp;
													<?php if($verparc == 1){ ?>
													<a href="javascript:void(0);" onclick="confirmConcilia('<?php echo md5($listconc->idconc)?>');"><img src="/public/sistema/imagens/icons/dark/record.png" width="14" border="0" title="Concilhar"></a>
													<?php }
													}else{
														echo " &nbsp; Validado";
													}
												}
												?>
											</td>
										</tr>
										<?php
										
										//-- outros lancamentos na mesma conciliacao --------------------------
										if(count($objParcelas)>0){
											$verrow = 0;
											foreach ($objParcelas as $listparc){
												$verrow++;
												if($verrow > 1){
													if($tpc == 1){
														?>
														<tr>
															<td style="text-align: center; border-left: 1px solid #e7e7e7">P<?php echo str_pad($listparc->idfin, 6, '0',STR_PAD_LEFT)?></td>
									                        <td >
									                        <?php 
									                        if(($listparc->id_fornecedor!=0) and ($listparc->id_fornecedor!=NULL)) echo $listparc->EMPRESA;
									                        elseif(($listparc->id_usuarios!=NULL) and ($listparc->id_usuarios!="")) echo $listparc->usuario;
									                        else echo $listparc->out_fornecedor; ?></td>
									                        <td style="text-align: right;"><?php echo number_format($listparc->valor_pago,2,",",".")?></td>
									                        <td style="text-align: center;"><?php if($listparc->docfatura != "") echo $listparc->docfatura."/".$listparc->parc; ?></td>
									                        <?php if($tp==1){ ?>
									                        <td style="text-align: left;"><?php echo $listparc->plano?></td>
									                        <td style="text-align: left;"><?php echo $listparc->obs?></td>
									                        <?php } ?>
								                       	</tr>
								                        <?php														
													}else{
														?>
														<tr>
															<td style="text-align: center; border-left: 1px solid #e7e7e7">R<?php echo str_pad($listparc->idfin, 6, '0',STR_PAD_LEFT)?></td>
									                        <td >
									                        <?php
									                        if(($listparc->id_fornecedor!=0) and ($listparc->id_fornecedor!=NULL)) echo $listparc->EMPRESA;
									                        else echo $listparc->out_fornecedor;
									                        ?></td>
									                        <td style="text-align: right;"><?php echo number_format($listparc->valor_pago,2,",",".")?></td>
									                        <td style="text-align: center"><?php if($listparc->docfatura != "") echo $listparc->docfatura."/".$listparc->parc; ?></td>
									                        <?php if($tp==1){ ?>
									                        <td style="text-align: left;"><?php echo $listparc->plano?></td>
									                        <td style="text-align: left;"><?php echo $listparc->obs?></td>
									                        <?php } ?>
								                        </tr>
								                        <?php
													}	
												}												
											}

										}
									}						
									?>
								</tbody>
							</table>
						</div>
					<?php 
					}
					
				}
		    }catch (Zend_Exception $e){
				$boerro	= new ErrosModel();
				$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "FinanceiroBO::listarConcilhacao()");
				$boerro->insert($dataerro);
				return 'erro';
			}
		}
		
		function listarSaldocontas($conta){
			if(!empty($conta)): 
				$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
				$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
				$select = $db->select();
				
				$select->from(array('f'=>'tb_financeiroconcilia','*'),
				        array('sum(f.valor) as total'))
				        ->where("f.situacao = 1 and f.id_financeirocontas = ".$conta);
				  		
				$stmt = $db->query($select);
				return $stmt->fetchAll();
			endif;
		}
		
		function gravaValidacaocontas($params){
			$bof	= new FinanceiroModel();
			$bo		= new FinanceiroconciliaModel();
			
			$array['valor']			= str_replace(",",".",str_replace(".","",$params['valorconc']));
			$array['data']			= substr($params['dataconc'],6,4).'-'.substr($params['dataconc'],3,2).'-'.substr($params['dataconc'],0,2);
			$array['situacao']		= 1; 
			$array['valida']		= 0;
			$array['id_financeirocontas']	= $params['idcontaconcilha'];
			
			if($params['contasval']!=0):
				$array['id_financeirocontastransf']	= $params['contasval'];
				$array['tax_cambio']	= str_replace(",",".",str_replace(".","",$params['taxcambio']));
				
				$array2['valor']		= str_replace(",",".",str_replace(".","",$params['taxcambio']))*(str_replace(",",".",str_replace(".","",$params['valorconc']))-(2*str_replace(",",".",str_replace(".","",$params['valorconc']))));
				$array2['data']			= substr($params['dataconc'],6,4).'-'.substr($params['dataconc'],3,2).'-'.substr($params['dataconc'],0,2);
				$array2['situacao']		= 1; 
				$array2['valida']		= 1;
				$array2['id_financeirocontas']		= $params['contasval'];
				$array2['id_financeirocontastransf']= $params['idcontaconcilha'];
				
			endif;
			
			if(!empty($params['idconci'])):
				$bo->update($array, "id = ".$params['idconci']);
				$usuario = Zend_Auth::getInstance()->getIdentity();
				LogBO::cadastraLog("ADM/Financeiro/Conciliação",4,$usuario->ID,$params['idconci'],"Conciliação ".$params['idconci']);
			else:
				$id = $bo->insert($array);
				$usuario = Zend_Auth::getInstance()->getIdentity();
				LogBO::cadastraLog("ADM/Financeiro/Conciliação",4,$usuario->ID,$id,"Conciliação ".$id);
				
			endif;
		}
		
		function removeConciliacaocontas($params){
			$bof	= new FinanceiroModel();
			$bop	= new FinanceiropagarparcModel();
			$bor	= new FinanceiroreceberparcModel();
			$bo		= new FinanceiroconciliaModel();
			
			//---remove conciliacao nos pagamentos---------------------------
			$array['st_conc']	= 0;
			$bop->update($array, "md5(st_conc) = '".$params['conc']."'");
			$bor->update($array, "md5(st_conc) = '".$params['conc']."'");
			
			$arrayc['situacao']	= 0;
			//---remove a conciliacao---------------------------
			$bo->update($arrayc, "md5(id) = '".$params['conc']."'");
			
			//---remove conciliacao de transferencia---------------------------
			$bo->update($arrayc, "md5(conctransf) = '".$params['conc']."'");
			
		}
		
		function conciliarContas($params){
			$bof	= new FinanceiroModel();
			//$bop	= new FinanceiropagarModel();
			//$bor	= new FinanceiroreceberModel();
			$bo		= new FinanceiroconciliaModel();
			
			//$array['bloq']	= 1;
			//$bop->update($array, "md5(st_conc) = '".$params['conc']."'");
			//$bor->update($array, "md5(st_conc) = '".$params['conc']."'");
			
			$arrayc['valida']	= 1;
			$bo->update($arrayc, "md5(id) = '".$params['conc']."'");
			
		}
		
		//--Lista contas para valicar------------------------------
		function listarContasvalidar($var){
		    $bof	= new FinanceiroModel();
		    $bo		= new FinanceiroconciliaModel();
		    
		    foreach ($bo->fetchAll("id = '".$var['idc']."'") as $conc);
		    
		    $dtsub = date('Y-m-d', strtotime("-3 days", strtotime($conc->data)));
		    //$dtsub = $conc->data;
		    $dtsom = date('Y-m-d', strtotime("+3 days", strtotime($conc->data)));
		    
		    
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
							
			if($var['tipo']==1):			
				$select->from(array('f'=>'tb_financeiropagparc','*'), array('f.*','f.id as idfin','DATE_FORMAT(f.emissao,"%d/%m/%Y") as dtcad','DATE_FORMAT(f.vencimento,"%d/%m/%Y") as dtvenc','f.moeda as moedacont'))
			        ->join(array('fin'=>'tb_financeiropag'), 'f.id_financeiropag = fin.id and fin.sit = 1')
			        ->where("(st_conc is NULL || st_conc = 0 || st_conc = ".$var['idc'].") and f.sit = true 
						and f.id_financeirocontas = '".$var['conta']."' and (f.dt_pagamento >= '".$dtsub."' and f.dt_pagamento <= '".$dtsom."' )")
			        ->order('f.id desc');
			else:
				$select->from(array('f'=>'tb_financeirorecparc','*'), array('f.*','f.id as idfin','DATE_FORMAT(f.emissao,"%d/%m/%Y") as dtcad','DATE_FORMAT(f.vencimento,"%d/%m/%Y") as dtvenc','f.moeda as moedacont'))
			        ->join(array('fin'=>'tb_financeirorec'), 'f.id_financeirorec = fin.id and fin.sit = 1')
			        ->where("(st_conc is NULL || st_conc = 0 || st_conc = ".$var['idc'].") and f.sit = true 
			        	and (f.baixa = 1 || f.descontodup = 1)  and f.id_financeirocontas = ".$var['conta']." 
			            and ((dt_pagamento >= '".$dtsub."' and dt_pagamento <= '".$dtsom."') || (dt_descontodup >= '".$dtsub."' and dt_descontodup <= '".$dtsom."')) ")
			        ->order('f.id desc')
					->limit(100);
			endif;
				
			$stmt = $db->query($select);
			
			return $stmt->fetchAll();
		}
			
		function gerarConciliacao($val){
			$bof	= new FinanceiroModel();
			$boc	= new FinanceiroconciliaModel();
			
			if($val['tipo']==3):
				$array['valida']		= 2;
				$array['conctransf']	= $val['radiovalida'];
				$array['id_financeirocontastransf']	= $val['buscconta'];
				
				$boc->update($array, "id = ".$val['idc']);
				
				$array['conctransf']	= $val['idc'];
				$array['id_financeirocontastransf']	= $val['conta'];
				$boc->update($array, "id = ".$val['radiovalida']);
			else:
				$bop	= new FinanceiropagarparcModel();
				$bor	= new FinanceiroreceberparcModel();
						
				$array['st_conc']	= 0;
				$bop->update($array, "st_conc = ".$val['idc']);
				$bor->update($array, "st_conc = ".$val['idc']);
				
				foreach (FinanceiroBO::listarContasvalidar($val) as $lista):
					if(!empty($val[$lista->idfin])):
						$array['st_conc']	= $val['idc'];
						if($val['tipo']==1):
							$bop->update($array, "id = ".$lista->idfin);
						else:
							$bor->update($array, "id = ".$lista->idfin);
						endif;
					endif;
				endforeach;				
			endif;
			
			$usuario = Zend_Auth::getInstance()->getIdentity();
			LogBO::cadastraLog("ADM/Financeiro/Conciliação",4,$usuario->ID,$val['idc'],"Conciliacao ".$val['idc']);
			
		}
		
		function listarExtratonconc($conta){
			$bof	= new FinanceiroModel();
			$boc	= new FinanceiroconciliaModel();
			
			foreach ($boc->fetchAll("id = ".$conta['idc']) as $listc);
			$where	= " and f.valor = (".($listc->valor)*(-1).")";
			 
			if(!empty($conta)): 
				
				$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
				$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
				$select = $db->select();
				
				$select->from(array('f'=>'tb_financeiroconcilia','*'), array('f.*','f.id as idconc','DATE_FORMAT(f.data,"%d/%m/%Y") as dtcad'))
				        ->where("valida = 0 ".$where." and f.situacao = 1 and f.conctransf is null and f.id_financeirocontas = ".$conta['bancobusca'])
				        ->order('f.id desc');
				  		
				$stmt = $db->query($select);
				return $stmt->fetchAll();
			endif;
		
		}
		
		function buscaContaconc($conta){
			$bof	= new FinanceiroModel();
			$boc	= new FinanceiroconciliaModel();
			
			return $boc->fetchAll("id = ".$conta['idc']);
		}
		
		function buscarFinanceironfe($idnfe){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('f'=>'tb_financeirorec','*'), array('p.*','f.id_nfe'))
					->join(array('p'=>'tb_financeirorecparc'), 'f.id = p.id_financeirorec')
					->where("f.sit = true and p.sit = true and f.id_nfe = ".$idnfe);
			
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		function buscarFinanceironfemd5($idnfe){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
			$select = $db->select();
				
			$select->from(array('f'=>'tb_financeirorec','*'), array('p.*','f.id_nfe'))
				->join(array('p'=>'tb_financeirorecparc'), 'f.id = p.id_financeirorec')
				->where("md5(f.id_nfe) = '".$idnfe['nfe']."'");
				
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		
		///------------- Boletos ------------------------------------
		function buscarParcelasrecboleto($idparc){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
		
			$select = $db->select();
		
			$select->from(array('f'=>'tb_financeirorec','*'), array('*','p.id as idparc'))
			->join(array('p'=>'tb_financeirorecparc'), 'f.id = p.id_financeirorec')
			->join(array('c'=>'clientes'), 'c.ID = f.id_fornecedor')
			->where("md5(p.id) = '".$idparc."'");
		
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		/*----- Arquivo remessa -------------------------------------------------
		 * Usado em Administracao::arquivoremessaAction();
		 */
		function listarParcelasareceberremessa(){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
		
			$select = $db->select();
		
			$select->from(array('f'=>'tb_financeirorecparc','*'),array('*','f.id as idparc','DATE_FORMAT(f.vencimento,"%d%m%Y") as dtvenc','DATE_FORMAT(f.vencimento,"%d%m%y") as dtvencitau','DATE_FORMAT(f.emissaoboleto,"%d%m%Y") as dtemissao','DATE_FORMAT(f.emissaoboleto,"%d%m%y") as dtemissaoitau',
					'p.nome as npais','e.nome as nestado','cd.nome as ncidade','e.uf as nuf','cd.codigo as codcidade','fn.id_nfe'))
			->join(array('fn'=>'tb_financeirorec'), 'fn.id = f.id_financeirorec')
			->join(array('c'=>'clientes'), 'c.ID = fn.id_fornecedor')
			->join(array('ce'=>'clientes_endereco'), 'ce.ID_CLIENTE = c.ID and ce.TIPO = 1')
			->joinLeft(array('p'=>'tb_paises'),'p.id = ce.PAIS')
			->joinLeft(array('e'=>'tb_estados'),'e.id = ce.ESTADO')
			->joinLeft(array('cd'=>'tb_cidades'),'cd.id = ce.id_cidades')
			->where('f.id >= 46085 || f.id < 46080')
			->order('f.id desc')
			->group('fn.id')
			->limit(3);
			
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		
		
		//-------- Dashbord -------------------------------------------
		//---Lista contas a pagar----------------------------------
		public function listaContaspagaravencer($pesq){
		    $where="";
		    if($pesq==1):
		    	$where = " and vencimento = '".date('Y-m-d')."'";
		    elseif($pesq==7):
		    	$data = date('Y-m-d',mktime(0,0,0,date('m'),date('d')+7,date('Y')));
		    	$where = " and vencimento >= '".date('Y-m-d')."' and vencimento <= '".$data."'";
		    elseif($pesq==-1):
		    	$where = " and vencimento < '".date('Y-m-d')."'";
		    elseif($pesq==2):
		    	$where = " and vencimento >= '".date('Y-m-d')."'";
		    endif;
		    		    
		    if((!empty($pesq['dtini'])) || (!empty($pesq['dtfim']))):
			    if(!empty($pesq['dtini'])) $di	= substr($pesq['dtini'],6,4).'-'.substr($pesq['dtini'],3,2).'-'.substr($pesq['dtini'],0,2);
			    if(!empty($pesq['dtfim'])) $df	= substr($pesq['dtfim'],6,4).'-'.substr($pesq['dtfim'],3,2).'-'.substr($pesq['dtfim'],0,2);
			    
			    if((!empty($di)) and (!empty($df))):
			    	$where = ' and p.vencimento between "'.$di.'" and "'.$df.'"';
			    elseif((!empty($di)) and (empty($df))):
			    	$where = ' and p.vencimento >= "'.$di.'"';
			    elseif((empty($di)) and (!empty($df))):
			    	$where = ' and p.vencimento <= "'.$df.'"';			    
			    endif;
		    endif;
		    
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
			$select = $db->select();
				
			$select->from(array('f'=>'tb_financeiropag','*'), array('f.*','f.obs as obsfin','f.id as idfin','DATE_FORMAT(p.vencimento,"%d/%m/%Y") as dtvencimento', 'DATE_FORMAT(f.emissao,"%d/%m/%Y") as dtcad','f.n_documento as fatu','u.nome'))
				->join(array('p'=>'tb_financeiropagparc'),'f.id = p.id_financeiropag')
				->joinLeft(array('c'=>'clientes'),'c.ID = f.id_fornecedor and c.sit = true')
				->joinLeft(array('u'=>'tb_usuarios'),'u.id = f.id_usuarios')
				->where("f.sit = true and p.sit = true and valor_pago is NULL".$where)
				->order('p.vencimento asc')
				->order('f.id desc');
			 
			$stmt = $db->query($select);
			
			
			
			return $stmt->fetchAll();
			
		}
		
		//--- Lista contas a receber ----------------------------------
		public function listaContasreceberavencer($pesq){
			$where="";
			if($pesq==1):
				$where = " and vencimento = '".date('Y-m-d')."'";
			elseif($pesq==7):
				$data = date('Y-m-d',mktime(0,0,0,date('m'),date('d')+7,date('Y')));
				$where = " and vencimento >= '".date('Y-m-d')."' and vencimento <= '".$data."'";
			elseif($pesq==-1):
				$where = " and vencimento < '".date('Y-m-d')."'";
			elseif($pesq==2):
				$where = " and vencimento >= '".date('Y-m-d')."'";
			endif;
		
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
		
			$select = $db->select();
		
			$select->from(array('f'=>'tb_financeirorec','*'), array('f.*'))
				->joinLeft(array('p'=>'tb_financeirorecparc'),'f.id = p.id_financeirorec')
				->where("f.sit = true and valor_pago is NULL".$where)
				->order('f.id desc');
		
			$stmt = $db->query($select);
		
			return $stmt->fetchAll();
		}
		
		//-- Busco parcelas a receber vencidas por cliente ------------------------------------------------------
		function buscaParcrecebervencidas($params){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
		
			$select = $db->select();
		
			$select->from(array('f'=>'tb_financeirorec','*'), array('f.*','p.*','DATE_FORMAT(p.vencimento,"%d/%m/%Y") as venc'))
				->joinLeft(array('p'=>'tb_financeirorecparc'),'f.id = p.id_financeirorec')
				->where("p.vencimento < '".date('Y-m-d')."' and f.sit = true and p.sit = true and valor_pago is NULL and f.id_fornecedor = ".$params)
				->order('p.vencimento');
		
			$stmt = $db->query($select);
			return $stmt->fetchAll();			
		}
		
		//----- Relatorios -------------------------------------------------------------------------------------------
		//---Dre Despesas----------------------------------
		function dreFinanceiro($pesq, $tp){
			
		    $sessaoDre = new Zend_Session_Namespace('findre');
		    $sessaoFin = new Zend_Session_Namespace('buscaplconta');
			$sessaoFin->plcontas = "";
						
			$bof	= new FinanceiroModel();
			$bo		= new FinanceiroplanocontasModel();
			
			$planos = FinanceiroBO::buscaArvoreplanoconta($tp);
			$where = " and p.id_financeiroplcontas in (".$planos.")";

			$where .= $sessaoDre->dataBusca;

			
			foreach ($bo->fetchAll("id = ".$tp) as $planotp);
			$tipo = substr($planotp->cod,0,1);
			
		    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
			$select = $db->select();
				
			if($sessaoDre->tipoBusca==1):
				if($tipo!=1):
					$select->from(array('f'=>'tb_financeiropag','*'), array('sum(p.valor_pago) as valortotal'))
						->join(array('p'=>'tb_financeiropagparc'),'f.id = p.id_financeiropag')
						->where("p.baixa = 1 and f.sit = true and p.sit = true ".$where);
				else:
					$select->from(array('f'=>'tb_financeirorec','*'), array('sum(p.valor_pago) as valortotal'))
						->join(array('p'=>'tb_financeirorecparc'),'f.id = p.id_financeirorec')
						->where("p.baixa = 1 and f.sit = true  and p.sit = true ".$where);
				endif;
			else:
				if($tipo!=1):
					$select->from(array('f'=>'tb_financeiropag','*'), array('sum(p.valor_apagar) as valortotal'))
						->join(array('p'=>'tb_financeiropagparc'),'f.id = p.id_financeiropag')
						->where("f.sit = true and p.sit = true ".$where);
				else:
					$select->from(array('f'=>'tb_financeirorec','*'), array('sum(p.valor_apagar) as valortotal'))
						->join(array('p'=>'tb_financeirorecparc'),'f.id = p.id_financeirorec')
						->where("f.sit = true  and p.sit = true ".$where);
				endif;
			endif;
			
			$stmt = $db->query($select);
			
			return $stmt->fetchAll();
		}

		function buscaDrefinanceiro($pesq){
		    $total = 0;
		    $bof	= new FinanceiroModel();
		    $bo		= new FinanceiroplanocontasModel();
		    	
		    if($pesq['idpl'] == 0):
		    	$where = " is NULL";
		    else:
		    	$where = " = ".$pesq['idpl'];
		    endif;
		    
		    $buscafech = $bo->fetchAll("sit = true and id_sup ".$where);
		    
		    if(count($buscafech)>0){
		        
		        if($pesq['tp'] != 1){ ?><td style="font-size: 10px"><?php }
			    foreach ($bo->fetchAll("sit = true and id_sup ".$where) as $contaslist){
			    	if(count(FinanceiroBO::dreFinanceiro($pesq, $contaslist->id, 1))>0){
			    		foreach (FinanceiroBO::dreFinanceiro($pesq, $contaslist->id, 1) as $despesas);
			    
			    		$color = "#fff";
			    		if($contaslist->nivel == 1) $color = "#ccc";
			    		if($contaslist->nivel == 2) $color = "#dedcdc";
			    		if($contaslist->nivel == 3) $color = "#efeded";
			    
			    		?>
	    				<table style="width: 100%; margin: 0 auto;" >
							<tr style="background-color: <?php echo $color?>; cursor: hand; cursor: pointer" onclick="mostrarDiv('<?php echo $contaslist->id?>'); <?php if($contaslist->utilizavel == 1): ?> buscaDrecontas('<?php echo $contaslist->id?>'); <?php else:?> buscaDre('<?php echo $contaslist->id?>'); <?php endif;?>">
								<td style="width: 10%; text-align: left; padding: 5px; padding-left: 15px"><?php echo $contaslist->navegacao?></td>
								<td style="width: 70%; padding: 5px"><?php echo $contaslist->nome?></td>
								<td style="text-align: right; padding: 5px"><?php echo number_format($despesas->valortotal,"2",",",".")?></td>
							</tr>
						</table>
						<table style="width: 920px; margin: 0 auto;" >
							<tr style="display: none;" id="<?php echo $contaslist->id?>" >
					        	<td >
					        		<img alt="Carregando ..." src="/public/images/loading.gif"> <i>Carregando ...</i>
					        	</td>
					        </tr>
				        </table>    				
	    				<?php 
	    				
	    				if($pesq['tp'] == 1){
							if($contaslist->navegacao == 1){
								$total += ($despesas->valortotal);
							}else{
								$total -= ($despesas->valortotal);
							}
						}
	    			}
			    }
			    if($pesq['tp'] != 1){ ?></td><?php }else{ 
			    	?>
    				<table style="width: 50%; margin-left: 465px; margin-top: 15px" >
						<tr style="background-color: #ccc" >
							<td style="width: 10%; text-align: left; padding: 5px; padding-left: 15px">Total</td>
							<td style="text-align: right; padding: 5px"><?php echo number_format($total,"2",",",".")?></td>
						</tr>
					</table>
					<?php 
			    }
		    } 
		    
		}
		
		function buscaDrefinanceirototal($pesq){
		
			$bof	= new FinanceiroModel();
			$bo		= new FinanceiroplanocontasModel();
			 		
			if(count($bo->fetchAll("sit = true and id = ".$pesq['idpl'])>0)):
				?><td style="font-size: 10px"><?php
			    foreach ($bo->fetchAll("sit = true and id = ".$pesq['idpl']) as $contaslist);
			    if(count(FinanceiroBO::dreFinanceiro($pesq, $contaslist->id, 1))>0):
			    	foreach (FinanceiroBO::dreFinanceiro($pesq, $contaslist->id, 1) as $despesas);

		    		$color = "#fff";
		    		if($contaslist->nivel == 1) $color = "#ccc";
		    		if($contaslist->nivel == 2) $color = "#dedcdc";
		    		if($contaslist->nivel == 3) $color = "#efeded";
			    
		    		?>
    				<table style="width: 910px; margin: 0 auto;" >
    					<tr style="background-color: <?php echo $color?>; cursor: hand; cursor: pointer" onclick="mostrarDiv('<?php echo $contaslist->id?>'); <?php if($contaslist->utilizavel == 1): ?> buscaDrecontas('<?php echo $contaslist->id?>'); <?php else:?> buscaDre('<?php echo $contaslist->id?>'); <?php endif;?>">
							<td style="width: 10%; text-align: center; padding: 5px"><?php echo $contaslist->navegacao?></td>
							<td style="width: 70%; padding: 5px"><?php echo $contaslist->nome?></td>
							<td style="text-align: right; padding: 5px"><?php echo number_format($despesas->valortotal,"2",",",".")?></td>
						</tr>
					</table>
					<table style="width: 910px; margin: 0 auto;" >
						<tr style="display: none;" id="<?php echo $contaslist->id?>" >
				        	<td >
				        		<img alt="Carregando ..." src="/public/images/loading.gif"> <i>Carregando ...</i>
				        	</td>
				        </tr>
			        </table>
			        <?php 
	    		endif;
			   	?></td><?php
		    endif; 
		    
		}
		
		
		
		function buscaDrefinanceirocontas($pesq){
			$sessaoDre 	= new Zend_Session_Namespace('findre');
			$bof		= new FinanceiroModel();
			$bo			= new FinanceiroplanocontasModel();
			
			/* $sessaoDre->tipoBusca  -> Se a busca e por competencia ou por fluxo de caixa ---
			 * $tipo -> verifica se o plano de conta eh de receita ou de custos ---------------
			 * 
			*/
			
			foreach ($bo->fetchAll("id = ".$pesq['idpl']) as $planotp);
			$tipo = substr($planotp->cod,0,1);
			
			$where = $sessaoDre->dataBusca." and p.id_financeiroplcontas = ".$pesq['idpl'];
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);				
			$select = $db->select();
				
			if($sessaoDre->tipoBusca==1){
				if($tipo!=1):
					$select->from(array('f'=>'tb_financeiropag','*'), array('p.valor_pago','p.valor_apagar','f.id as idconta','c.EMPRESA','f.out_fornecedor','f.id_fornecedor','p.parc','p.id as idparc','t.nome as usuario'))
						->join(array('p'=>'tb_financeiropagparc'),'f.id = p.id_financeiropag')
						->joinLeft(array('c'=>'clientes'),'c.ID = f.id_fornecedor')
						->joinLeft(array('t'=>'tb_usuarios'),'t.id = f.id_usuarios')
						->where("p.baixa = 1 and p.sit = true and f.sit = true ".$where)
						->group("p.id");
				else:
					$select->from(array('f'=>'tb_financeirorec','*'), array('p.valor_pago','p.valor_apagar','f.id as idconta','c.EMPRESA','f.out_fornecedor','f.id_fornecedor','p.parc','p.id as idparc','t.nome as usuario'))
						->join(array('p'=>'tb_financeirorecparc'),'f.id = p.id_financeirorec')
						->joinLeft(array('c'=>'clientes'),'c.ID = f.id_fornecedor')
						->joinLeft(array('t'=>'tb_usuarios'),'t.id = f.id_usuarios')
						->where("p.baixa = 1 and p.sit = true and f.sit = true ".$where)
						->group("p.id");
				endif;
				
				$stmt 		= $db->query($select);
				$contasger 	= $stmt->fetchAll();
				
				if(count($contasger)>0):
				?>
				<td style="font-size: 10px; text-align: center; width: 900px" >
					<table style="width: 900px; margin: 0 auto;" >
					<?php
					foreach ($contasger as $contas):
						?>		
						<tr style="background-color: #e8e8e8;">
							<td style="width: 10%; text-align: center; padding: 5px">
								<?php if($tipo!=1): ?>
								<a href="/admin/administracao/financeiroztlpagcad/pay/<?php echo md5($contas->idconta)?>" target="_blank">P<?php echo substr("00000".$contas->idconta, -6,6)."/".$contas->parc?></a>
								<?php else: ?>
								<a href="/admin/administracao/financeiroztlreccad/rec/<?php echo md5($contas->idconta)?>" target="_blank">R<?php echo substr("00000".$contas->idconta, -6,6)."/".$contas->parc?></a>
								<?php endif; ?>
							</td>
							<td style="text-align: left; padding: 5px"><?php if(!empty($contas->usuario)) echo $contas->usuario; elseif(!empty($contas->id_fornecedor)) echo $contas->EMPRESA; else echo $contas->out_fornecedor?></td>
							<td style="text-align: right; padding: 5px">
								<?php 
									if(!empty($contas->valor_pago)) echo number_format($contas->valor_pago,"2",",",".");
									//else echo number_format($contas->valor_apagar,"2",",",".");								
								?></td>
						</tr>
						<?php
					endforeach;
					?></table>
				</td>
				<?php 
				endif;
			}else{
				if($tipo!=1):
					$select->from(array('f'=>'tb_financeiropag','*'), array('f.valor as valor','p.valor_apagar','f.id as idconta','c.EMPRESA','f.out_fornecedor','f.id_fornecedor','t.nome as usuario'))
						->join(array('p'=>'tb_financeiropagparc'),'f.id = p.id_financeiropag')
						->joinLeft(array('c'=>'clientes'),'c.ID = f.id_fornecedor')
						->joinLeft(array('t'=>'tb_usuarios'),'t.id = f.id_usuarios')
						->where("p.sit = true and f.sit = true ".$where)
						->group("p.id");
				else:
					$select->from(array('f'=>'tb_financeirorec','*'), array('f.valor as valor','p.valor_apagar','f.id as idconta','c.EMPRESA','f.out_fornecedor','f.id_fornecedor'))
						->join(array('p'=>'tb_financeirorecparc'),'f.id = p.id_financeirorec and p.sit = true')
						->joinLeft(array('c'=>'clientes'),'c.ID = f.id_fornecedor')
						->where("p.sit = true and f.sit = true ".$where)
						->group("p.id");				
				endif;
				
				
				$stmt 		= $db->query($select);
				$contasger 	= $stmt->fetchAll();
				
				if(count($contasger)>0):
				?>
				<td style="font-size: 10px; text-align: center; width: 900px" >
					<table style="width: 900px; margin: 0 auto;" >
					<?php
					foreach ($contasger as $contas):
						?>		
						<tr style="background-color: #e8e8e8;">
							<td style="width: 10%; text-align: center; padding: 5px">
								<?php if($tipo!=1): ?>
								<a href="/admin/administracao/financeiroztlpagcad/pay/<?php echo md5($contas->idconta)?>" target="_blank">P<?php echo substr("00000".$contas->idconta, -6,6)."/".$contas->parc?></a>
								<?php else: ?>
								<a href="/admin/administracao/financeiroztlreccad/rec/<?php echo md5($contas->idconta)?>" target="_blank">R<?php echo substr("00000".$contas->idconta, -6,6)."/".$contas->parc?></a>
								<?php endif; ?>
							</td>
							<td style="text-align: left; padding: 5px"><?php if(!empty($contas->usuario)) echo $contas->usuario; elseif(!empty($contas->id_fornecedor)) echo $contas->EMPRESA; else echo $contas->out_fornecedor?></td>
							<td style="text-align: right; padding: 5px">
							<?php
								/* if(!empty($contas->valor_pago)) echo number_format($contas->valor_pago,"2",",",".");
								else  */
								echo number_format($contas->valor_apagar,"2",",",".");							
							?></td>
						</tr>
						<?php
					endforeach;
					?></table>
				</td>
				<?php 
				endif;
			}			    
		}		
		
		function buscaNfefrete($params){
			try{
				$bo		= new NfeModel();
					
				if(!empty($params['pay'])):
					$where = " and md5(id_pagfrete) = '".$params['pay']."'";
				else:
					$where = " and ((id_pagfrete = '".$params['pag']."') || (id_pagfrete is NULL)) ";
				endif;
				
				return $bo->fetchAll("data > '2014-01-01' and status = 1 and id_transportadoras is not NULL and id_transportadoras != 662 ".$where);
			}catch (Zend_Exception $e){
				$boerro	= new ErrosModel();
				$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "ClientesBO::buscaNfefrete()");
				$boerro->insert($dataerro);
				return false;
			}
		}
		
		function buscaNferepresentante($params){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
			$select = $db->select();
			
			$select->from(array('t'=>'tb_pedidos','*'), array('cr.EMPRESA as representante','t.id_nfe'))
				->joinLeft(array('cr'=>'clientes'),'t.id_representante = cr.id')
				->joinLeft(array('n'=>'tb_nfe'),'n.id = t.id_nfe')
				->where("t.id is not NULL ")
				->order('t.id desc','');
				
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		//-- Busco total a pagar ------------------------------------------------------
		function buscaTotalcontasreceber($tp = 1){

			if($tp == 1){
				$where = "p.vencimento < now() and";
			}elseif($tp == 2){
				$where = "p.vencimento >= now() and";
			}

			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
		
			$select = $db->select();
		
			$select->from(array('f'=>'tb_financeirorec','*'), array('sum(p.valor_apagar) as valor'))
				->join(array('p'=>'tb_financeirorecparc'),'f.id = p.id_financeirorec')
				->where($where." f.sit = true and  p.sit = true and p.baixa = 0 and f.baixa = 0 and p.valor_pago is NULL"); 			
		
			$stmt = $db->query($select);
			
			return $stmt->fetchAll();
				
		}
		
		//-- Busco total a receber/pagar ------------------------------------------------------
		function buscaTotalcontaspagar($tp = 1){
		
			if($tp == 1){
				$where = "p.vencimento < now() and";
			}elseif($tp == 2){
				$where = "p.vencimento >= now() and";
			}
		
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
		
			$select = $db->select();
		
			$select->from(array('f'=>'tb_financeiropag','*'), array('sum(p.valor_apagar) as valor'))
				->join(array('p'=>'tb_financeiropagparc'),'f.id = p.id_financeiropag')
				->where($where." f.sit = true and  p.sit = true and p.baixa = 0 and f.baixa = 0 and p.valor_pago is NULL");
		
			$stmt = $db->query($select);
						
			return $stmt->fetchAll();
		
		}
		
		function gerarDre($pesq){

			$wheredup = $where = "";
			if((!empty($pesq['dtini']) and !empty($pesq['dtfim'])) || !empty($pesq['dtini'])){
				if(!empty($pesq['dtini'])) $di	= substr($pesq['dtini'],6,4).'-'.substr($pesq['dtini'],3,2).'-'.substr($pesq['dtini'],0,2);
				if(!empty($pesq['dtfim'])) $df	= substr($pesq['dtfim'],6,4).'-'.substr($pesq['dtfim'],3,2).'-'.substr($pesq['dtfim'],0,2);
					
				if((!empty($di)) and (!empty($df))){
					$where 		= ' and f.emissao between "'.$di.'" and "'.$df.' 23:59:59"';
					$wheredup 	= ' and p.dt_descontodup between "'.$di.'" and "'.$df.' 23:59:59"';
					$wherenfe 	= ' and data between "'.$di.'" and "'.$df.'"';
					$wherejur	= ' and p.dt_pagamento between "'.$di.'" and "'.$df.'"';
					
					$periodo 	= "Período de ".$pesq['dtini']." até ".$pesq['dtfim'];
					
				}elseif((!empty($di)) and (empty($df))){
					$where 		= ' and f.emissao >= "'.$di.'"';
					$wheredup 	= ' and p.dt_descontodup >= "'.$di.'"';
					$wherenfe 	= ' and data >= "'.$di.'"';
					$wherejur	= ' and p.dt_pagamento >= "'.$di.'"';
					$periodo 	= "Período apartir de ".$pesq['dtini'];
				}
				
				$dtinicial = str_replace("/", "-", $pesq['dtini']);
				
				/* }else{
				$where = ' and p.dt_pagamento >= "'.date("Y-m-01").'"';
				$periodo = "Período apartir de ".date("01/m/Y");
				$dtinicial = date("Y-m"); */
			}
			
			
			$whereimposto = "dt_inicial like '".date('Y-m', strtotime("-1 month",strtotime($dtinicial)))."%'";
			$params = array('dataini' => $pesq['dtini'], 'datafim' => $pesq['dtfim']);
			
			
			/* $where 		= ' and f.emissao between "2013-09-01" and "2013-09-30 23:59:59"';
			$wheredup 	= ' and p.dt_descontodup between "2013-09-01" and "2013-09-30 23:59:59"';
			$pesq['dtini'] = "01/09/2013";
			$pesq['dtfim'] = "30/09/2013"; */
			
			$totalprovisao = 0;

			$retorno = '<table style="width: 100%" class="dre"><tbody>
			    <tr><td colspan="2" class="tdresultado">
		        	Demostração do Resultado do Exercício<br />
		        	<span style="font-size: 12px">'.str_replace("-", "/", $periodo).'</span>		        
		       	</td></tr>';
			
			//--- Valor das mercadorias ----------------------------------
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			
			$select->from(array('n'=>'tb_nfe','*'), array('sum(n.totalnota) as valor'))
				->where("n.tipo = 1 and n.cfop not in (5916,6916,6911,6915,5915,6901,6949,7949) and n.status = 1 ".$wherenfe);
			
			/* $select->from(array('f'=>'tb_financeirorec','*'), array('sum(p.valor_apagar) as valor'))
			->join(array('p'=>'tb_financeirorecparc'),'f.id = p.id_financeirorec')
			->where(" f.sit = true and p.sit = true and p.id_financeiroplcontas = 120 ".$where); */
			
			$stmt = $db->query($select);
			
			$objfin = $stmt->fetchAll();
			
			foreach ($objfin as $valorvendas);
			if($valorvendas->valor == NULL) $venda = 0;
			else $venda = $valorvendas->valor;
			
			$totalprovisao = $venda;
			$totalfaturamento = $venda;
						
			$retorno .= '<tr><td class="tdtitulo">Receita operacional bruta</td><td class="tdtitulovalor">'.number_format($venda,2,",",".").'</td></tr>';
			
			//--- Impostos sobre a venda ----------------------------------
			
			$this->objIcms			= FaturamentoBO::buscaImpostos("icms", $params, 1);
			$this->objSt			= FaturamentoBO::buscaImpostos("st", $params, 1);
			$this->objIpi			= FaturamentoBO::buscaImpostos("ipi", $params, 1);
			$this->objPis			= FaturamentoBO::buscaImpostos("pis", $params, 1);
			$this->objCofins		= FaturamentoBO::buscaImpostos("cofins", $params, 1);
			 
			$this->objIcmsent		= FaturamentoBO::buscaImpostos("icms", $params, 0);
			$this->objStent			= FaturamentoBO::buscaImpostos("st", $params, 0);
			$this->objIpient		= FaturamentoBO::buscaImpostos("ipi", $params, 0);
			$this->objPisent		= FaturamentoBO::buscaImpostos("pis", $params, 0);
			$this->objCofinsent		= FaturamentoBO::buscaImpostos("cofins", $params, 0);
			
			$totalicmsent = $totalpisent = $totalcofinsent = $totalipient = $totalstent = 0;
			if(count($this->objIcmsent)>0) foreach ($this->objIcmsent as $icms)  if($icms->alicms != 0) $totalicmsent += $icms->tvlicms;
			if(count($this->objPisent)>0) foreach ($this->objPisent as $pis) if($pis->alpis != 0) $totalpisent += $pis->tvlpis;
			if(count($this->objCofinsent)>0) foreach ($this->objCofinsent as $cofins) if($cofins->alcofins != 0) $totalcofinsent += $cofins->tvlcofins;
			if(count($this->objStent)>0) foreach ($this->objStent as $st) if($st->icmsst != 0) $totalstent += $st->tvlicmsst;
			if(count($this->objIpient)>0) foreach ($this->objIpient as $ipi) if($ipi->alipi != 0) $totalipient += $ipi->tvlipi;

			$totalicms = $totalpis = $totalcofins = $totalst = $totalipi = 0;
			if(count($this->objIcms)>0) foreach ($this->objIcms as $icms) if($icms->alicms != 0) $totalicms += $icms->tvlicms;
			if(count($this->objPis)>0) foreach ($this->objPis as $pis) if($pis->alpis != 0) $totalpis += $pis->tvlpis;
			if(count($this->objCofins)>0) foreach ($this->objCofins as $cofins) if($cofins->alcofins != 0) $totalcofins += $cofins->tvlcofins;
			if(count($this->objSt)>0) foreach ($this->objSt as $st) if($st->icmsst != 0) $totalst += $st->tvlicmsst;
			if(count($this->objIpi)>0) foreach ($this->objIpi as $ipi) if($ipi->alipi != 0) $totalipi += $ipi->tvlipi;
			
			$bo		= new NfeModel();
			$bor	= new NferemessaModel();
			
			$mes = date("m", strtotime("-2 month"));
			$ano = date("Y", strtotime("-2 month"));
			
			foreach ($bor->fetchAll($whereimposto) as $remessaant);
			 
			if($remessaant->icmsapuracao > 0) $creditoicms = $remessaant->icmsapuracao;
			else $creditoicms = 0;
			 
			if($remessaant->ipiapuracao > 0) $creditoipi = $remessaant->ipiapuracao;
			else $creditoipi = 0;
			 
			$icmsapurado 	= $totalicms - ($totalicmsent+$creditoicms);
			$ipiapurado 	= $totalipi - ($totalipient+$creditoipi);
			$pisapurado 	= ($totalpis+$totalpisent);
			$cofinsapurado 	= ($totalcofins+$totalcofinsent);
			$icmsstapurado 	= ($totalstent+$totalst);
			
			$impostos = $impostosrep = 0;
			if($icmsapurado > 0) $impostos += $icmsapurado;
			if($ipiapurado > 0) $impostosrep += $ipiapurado;
			
			$impostos += $pisapurado;
			$impostos += $cofinsapurado;
			$impostosrep += $icmsstapurado;
			
			$retorno .= '<tr><td class="tddre">Deduções da receita (Impostos repassados)</td><td class="tdvalor red">-'.number_format($impostosrep,2,",",".").'</td></tr>';
			$totalprovisao -= $impostosrep;
			
			$retorno .= '<tr><td class="tddre">Vendas</td><td class="tdvalor">'.number_format($totalprovisao,2,",",".").'</td></tr>';
			
			$retorno .= '<tr><td class="tddre">Deduções da receita</td><td class="tdvalor red">-'.number_format($impostos,2,",",".").'</td></tr>';
			$totalprovisao -= $impostos; 
			
			$retorno .= '<tr><td class="tddre">Receita líquida de vendas</td><td class="tdvalor">'.number_format($totalprovisao,2,",",".").'</td></tr>';
			
			//--- Outras receitas ----------------------------------
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			$select->from(array('f'=>'tb_financeirorec','*'), array('sum(p.valor_apagar) as valor'))
				->join(array('p'=>'tb_financeirorecparc'),'f.id = p.id_financeirorec')
				->where(" f.sit = true and  p.sit = true and p.id_financeiroplcontas in (122) ".$where);
			
			$stmt = $db->query($select);
						
			$objout = $stmt->fetchAll();
			
			foreach ($objout as $valor);
			if($valor->valor == NULL) $outreceitas = 0;
			else $outreceitas = $valor->valor;
				
			$totalprovisao 		+= $outreceitas;
			$totalfaturamento 	+= $outreceitas;
			
			$retorno .= '<tr><td class="tddre">Outras receitas operacionais</td><td class="tdvalor">'.number_format($outreceitas,2,",",".").'</td></tr>';
			$retorno .= '<tr><td class="tdtitulo">Receita operacional líquida</td><td class="tdtitulovalor">'.number_format($totalprovisao,2,",",".").'</td></tr>';
			
			//--- CMV ----------------------------------
			$cmvestoque = EstoqueBO::calcularValorcmv($pesq);
			
			$cmvestoque = $cmvestoque*(-1);
			$classcusto = "";
			if($cmvestoque < 0){
				$classcusto = 'red';
			}
			$retorno .= '<tr><td class="tddre">Custo das mercadorias vendidas</td><td class="tdvalor '.$classcusto.'">'.number_format($cmvestoque,2,",",".").'</td></tr>';
				
			$totalprovisao += $cmvestoque;
			$retorno .= '<tr><td class="tdtitulo">Lucro bruto</td><td class="tdtitulovalor">'.number_format($totalprovisao,2,",",".").'</td></tr>';
			
			//--- Despesas com vendas ----------------------------------
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			$select->from(array('f'=>'tb_financeiropag','*'), array('sum(p.valor_apagar) as valor'))
			->join(array('p'=>'tb_financeiropagparc'),'f.id = p.id_financeiropag')
			->where(" f.sit = true and  p.sit = true and p.id_financeiroplcontas in (153,154,161,162,163,164,150,178,202) ".$where);
				
			$stmt = $db->query($select);
			$objdesp = $stmt->fetchAll();
			
			foreach ($objdesp as $valor);
			if($valor->valor == NULL) $despvendas = 0;
			else $despvendas = $valor->valor;
			
			$retorno .= '<tr><td class="tddre">Despesas comerciais</td><td class="tdvalor red">-'.number_format($despvendas,2,",",".").'</td></tr>';
			
			$totalprovisao -= $despvendas;
			
			//--- Despesas administrativas ----------------------------------
			$adm = 0;
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			$select->from(array('f'=>'tb_financeiropag','*'), array('sum(p.valor_apagar) as valor'))
				->join(array('p'=>'tb_financeiropagparc'),'f.id = p.id_financeiropag')
				->where("f.sit = true and  p.sit = true and p.id_financeiroplcontas in (149,156,157,158,159,160,165,166,167,168,170,171,172,173,174,175,176,177,180,181,182,183,184,185,186,189,193,195,196,197,198,199,200,201,203,212,214,217) ".$where);
				
			$stmt = $db->query($select);
			$objdespadm = $stmt->fetchAll();
			
			foreach ($objdespadm as $valor);
			if($valor->valor == NULL) $despadm = 0;
			else $despadm = $valor->valor;
				
			$adm = $despadm;
			
			//--- Prolabores ----------------------------------
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			$select->from(array('f'=>'tb_financeiropag','*'), array('sum(p.valor_apagar) as valor'))
				->join(array('p'=>'tb_financeiropagparc'),'f.id = p.id_financeiropag')
				->where(" f.sit = true and  p.sit = true and p.id_financeiroplcontas in (187) ".$where);
				
			$stmt = $db->query($select);
			$objpro = $stmt->fetchAll();
			
			foreach ($objpro as $valor);
			if($valor->valor == NULL) $prolabores = 0;
			else $prolabores = $valor->valor;
				
			$adm += $prolabores;
			
			//--- Gratificacoes ----------------------------------
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			$select->from(array('f'=>'tb_financeiropag','*'), array('sum(p.valor_apagar) as valor'))
			->join(array('p'=>'tb_financeiropagparc'),'f.id = p.id_financeiropag')
			->where(" f.sit = true and  p.sit = true and p.id_financeiroplcontas in (188,179) ".$where);
			
			$stmt = $db->query($select);
			$objgrat = $stmt->fetchAll();
				
			foreach ($objgrat as $valor);
			if($valor->valor == NULL) $gratificacoes = 0;
			else $gratificacoes = $valor->valor;
			
			$adm += $gratificacoes;
			
			$retorno .= '<tr><td class="tddre">Despesas administrativas</td><td class="tdvalor red">-'.number_format($adm,2,",",".").'</td></tr>';
			
			$totalprovisao -= $adm;
			
			//--- Tributos ----------------------------------
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			$select->from(array('f'=>'tb_financeiropag','*'), array('sum(p.valor_apagar) as valor'))
			->join(array('p'=>'tb_financeiropagparc'),'f.id = p.id_financeiropag')
			->where(" f.sit = true and  p.sit = true and p.id_financeiroplcontas in (169,134,144,143,147,148,149,141,142,213) ".$where);
				
			$stmt = $db->query($select);
			$objimp = $stmt->fetchAll();
			
			foreach ($objimp as $valor);
			if($valor->valor == NULL) $tributos = 0;
			else $tributos = $valor->valor;
			
			$retorno .= '<tr><td class="tddre">Despesas tributárias</td><td class="tdvalor red"> -'.number_format($tributos,2,",",".").'</td></tr>';
			
			$totalprovisao -= $tributos;
			
			$despfinanceiras = 0;
			
			//--- Juros ----------------------------------
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			$select->from(array('f'=>'tb_financeiropag','*'), array('sum(p.valor_apagar) as valor'))
			->join(array('p'=>'tb_financeiropagparc'),'f.id = p.id_financeiropag')
			->where(" f.sit = true and  p.sit = true and p.id_financeiroplcontas in (192,146,204) ".$where);
			
			$stmt = $db->query($select);
			$objjuros = $stmt->fetchAll();
				
			foreach ($objjuros as $valor);
			if($valor->valor == NULL) $juros = 0;
			else $juros = $valor->valor;
			
			$despfinanceiras = $juros;
			
			//-- desconto de duplicatas ---------------------------------------
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			$select->from(array('f'=>'tb_financeirorec','*'), array('sum(p.valor_apagar - p.valor_pago) as valor'))
			->join(array('p'=>'tb_financeirorecparc'),'f.id = p.id_financeirorec')
			->where(" f.sit = true and p.sit = true and descontodup = 1 ".$wheredup);
			
			$stmt = $db->query($select);
			
			$objdup = $stmt->fetchAll();
			
			foreach ($objdup as $valor);
			if($valor->valor == NULL) $desdup = 0;
			else $desdup = $valor->valor;
			
			$despfinanceiras += $desdup;
			
			//--- Taxas ----------------------------------
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			$select->from(array('f'=>'tb_financeiropag','*'), array('sum(p.valor_apagar) as valor'))
			->join(array('p'=>'tb_financeiropagparc'),'f.id = p.id_financeiropag')
			->where(" f.sit = true and  p.sit = true and p.id_financeiroplcontas in (191,221) ".$where);
			
			$stmt = $db->query($select);
			$objtaxas = $stmt->fetchAll();
				
			foreach ($objtaxas as $valor);
			if($valor->valor == NULL) $taxas = 0;
			else $taxas = $valor->valor;
			
			$despfinanceiras += $taxas;
			$retorno .= '<tr><td class="tddre">Despesas financeiras</td><td class="tdvalor red">-'.number_format($despfinanceiras,2,",",".").'</td></tr>';
			
			$totalprovisao -= $despfinanceiras;
			
			//--- Financeira ----------------------------------
			$receitafinanceira = 0;
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			$select->from(array('f'=>'tb_financeirorec','*'), array('sum(p.valor_apagar) as valor'))
				->join(array('p'=>'tb_financeirorecparc'),'f.id = p.id_financeirorec')
				->where(" f.sit = true and  p.sit = true and p.id_financeiroplcontas in (121,205) ".$wherejur);
				
			$stmt = $db->query($select);
			$objout = $stmt->fetchAll();
			
			foreach ($objout as $valor);
			if($valor->valor != NULL) $receitafinanceira = $valor->valor;
			
			//--- Financeira diferenca juros ----------------------------------
			/* $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			$select->from(array('f'=>'tb_financeirorec','*'), array('sum(p.valor_pago-p.valor_apagar) as valor'))
				->join(array('p'=>'tb_financeirorecparc'),'f.id = p.id_financeirorec')
				->where("p.dt_pagamento > p.vencimento and f.sit = true and  p.sit = true and p.id_financeiroplcontas in (120,122) 
						and p.valor_apagar < p.valor_pago ".$wherejur);
			
			$stmt = $db->query($select);
			
			$objout = $stmt->fetchAll();
			
			foreach ($objout as $valor);
			if($valor->valor != NULL) $receitafinanceira += $valor->valor; */
			
			$retorno .= '<tr><td class="tddre">Receitas financeiras</td><td class="tdvalor">'.number_format($receitafinanceira,2,",",".").'</td></tr>';
			
			$totalprovisao += $receitafinanceira;	
			
			$classcusto = '';
			if($totalprovisao < 0){
				$classcusto = 'red';
			}
			
			$retorno .= '<tr><td class="tdtitulo">Resultado operacional antes do IRPJ e CSLL</td><td class="tdtitulovalor '.$classcusto.'">'.number_format($totalprovisao,2,",",".").'</td></tr>';
			
			//--- IPI e ICMSST para calculo da provisao do IRPJ e CSLL ----------------------------------
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			$select->from(array('f'=>'tb_financeiropag','*'), array('sum(p.valor_apagar) as valor'))
				->join(array('p'=>'tb_financeiropagparc'),'f.id = p.id_financeiropag')
				->where(" f.sit = true and  p.sit = true and p.id_financeiroplcontas in (138,151) ".$where);
				
			$stmt = $db->query($select);
			$objprovisao = $stmt->fetchAll();
			
			foreach ($objprovisao as $valor);
			if($valor->valor == NULL) $valorpro = 0;
			else $valorpro = $valor->valor;
			
			$totalfaturamento -= $valorpro;
			
			$lucroirpj = ($totalfaturamento*8)/100;
			$lucroirpj = ($lucroirpj*15)/100;
			
			$lucroclss = ($totalfaturamento*12)/100;
			$lucroclss = ($lucroclss*9)/100;
			
			$totalprovisao -= $lucroclss+$lucroirpj;
			
			
			//--- IPI e ICMSST para calculo da provisao do IRPJ e CSLL ----------------------------------
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			$select->from(array('f'=>'tb_financeiropag','*'), array('sum(p.valor_apagar) as valor'))
				->join(array('p'=>'tb_financeiropagparc'),'f.id = p.id_financeiropag')
				->where(" f.sit = true and  p.sit = true and p.id_financeiroplcontas in (211) ".$where);
			
			$stmt = $db->query($select);
			$objprovisao = $stmt->fetchAll();
				
			foreach ($objprovisao as $valor);
			if($valor->valor == NULL) $valorprov = 0;
			else $valorprov = $valor->valor;
			
			$totalprovisao -= $valorprov;
			
			$retorno .= '<tr><td class="tddre">Provisão para IRPJ e CSLL</td><td class="tdvalor red">-'.number_format($lucroclss+$lucroirpj,2,",",".").'</td></tr>';
			$retorno .= '<tr><td class="tddre">Provisão de antecipação de lucro</td><td class="tdvalor">'.number_format($valorprov,2,",",".").'</td></tr>';
			
			$classcusto = '';
			if($totalprovisao < 0){
				$classcusto = 'red';
			}
			$retorno .= '<tr><td class="tdtitulo grande">Resultado líquido do mês</td><td class="tdtitulovalor grande '.$classcusto.'">'.number_format($totalprovisao,2,",",".").'</td></tr>';
			echo $retorno;
			
			/* $wheredup = $where = "";
			if((!empty($pesq['dtini'])) || (!empty($pesq['dtfim']))){
				if(!empty($pesq['dtini'])) $di	= substr($pesq['dtini'],6,4).'-'.substr($pesq['dtini'],3,2).'-'.substr($pesq['dtini'],0,2);
				if(!empty($pesq['dtfim'])) $df	= substr($pesq['dtfim'],6,4).'-'.substr($pesq['dtfim'],3,2).'-'.substr($pesq['dtfim'],0,2);
			
				if((!empty($di)) and (!empty($df))){
					$where 		= ' and f.emissao between "'.$di.'" and "'.$df.' 23:59:59"';
					$wheredup 	= ' and p.dt_descontodup between "'.$di.'" and "'.$df.' 23:59:59"';
					$periodo 	= "Período de ".$pesq['dtini']." até ".$pesq['dtfim'];
				}elseif((!empty($di)) and (empty($df))){
					$where 		= ' and f.emissao >= "'.$di.'"';
					$wheredup 	= ' and p.dt_descontodup >= "'.$di.'"';
					$periodo 	= "Período apartir de ".$pesq['dtini'];
				}elseif((empty($di)) and (!empty($df))){
					$where 		= ' and f.emissao <= "'.$df.'"';
					$wheredup 	= ' and p.dt_descontodup <= "'.$df.'"';
					$periodo 	= "Período até ".$pesq['dtfim'];
				}
			}else{
				$where = ' and p.dt_pagamento >= "'.date("Y-m-01").'"';
				$periodo = "Período apartir de ".date("01/m/Y");
			}
				
			$totalprovisao = 0;
				
			$retorno = '<table style="width: 100%" class="dre"><tbody>
			    <tr><td colspan="2" class="tdresultado">
		        	Demostração do Resultado do Exercício<br />
		        	<span style="font-size: 12px">'.str_replace("-", "/", $periodo).'</span>
		       	</td></tr>
			
		        <tr><td class="tdtitulo">(+)Receita operacional bruta</td><td class="tdtitulovalor">&nbsp;</td></tr>';
				
			//--- Valor das mercadorias ----------------------------------
				
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			$select->from(array('f'=>'tb_financeirorec','*'), array('sum(p.valor_apagar) as valor'))
			->join(array('p'=>'tb_financeirorecparc'),'f.id = p.id_financeirorec')
			->where(" f.sit = true and p.sit = true and p.id_financeiroplcontas = 120 ".$where);
				
			$stmt = $db->query($select);
			$objfin = $stmt->fetchAll();
				
			foreach ($objfin as $valorvendas);
			if($valorvendas->valor == NULL) $venda = 0;
			else $venda = $valorvendas->valor;
				
			$totalprovisao = $venda;
				
			$retorno .= '<tr><td class="tddre">Vendas de mercadoria</td><td class="tdvalor">'.number_format($venda,2,",",".").'</td></tr>';
				
			//--- Financeira ----------------------------------
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			$select->from(array('f'=>'tb_financeirorec','*'), array('sum(p.valor_apagar) as valor'))
			->join(array('p'=>'tb_financeirorecparc'),'f.id = p.id_financeirorec')
			->where(" f.sit = true and  p.sit = true and p.id_financeiroplcontas in (121) ".$where);
			
			$stmt = $db->query($select);
			$objout = $stmt->fetchAll();
				
			foreach ($objout as $valor);
			if($valor->valor == NULL) $outreceitas = 0;
			else $outreceitas = $valor->valor;
			
			$totalprovisao += $outreceitas;
				
			$retorno .= '<tr><td class="tddre">Financeira</td><td class="tdvalor">'.number_format($outreceitas,2,",",".").'</td></tr>';
				
			//--- Outras receitas ----------------------------------
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			$select->from(array('f'=>'tb_financeirorec','*'), array('sum(p.valor_apagar) as valor'))
			->join(array('p'=>'tb_financeirorecparc'),'f.id = p.id_financeirorec')
			->where(" f.sit = true and  p.sit = true and p.id_financeiroplcontas in (122) ".$where);
			
			$stmt = $db->query($select);
			$objout = $stmt->fetchAll();
				
			foreach ($objout as $valor);
			if($valor->valor == NULL) $outreceitas = 0;
			else $outreceitas = $valor->valor;
			
			$totalprovisao += $outreceitas;
				
			$retorno .= '<tr><td class="tddre">Outras receitas</td><td class="tdvalor">'.number_format($outreceitas,2,",",".").'</td></tr>';
				
			$retorno .= '<tr><td class="tdtitulo">(-)Deduções da receita bruta</td><td class="tdtitulovalor">&nbsp;</td></tr>';
				
			$retorno .= '<tr><td class="tddre">Deduções de vendas</td><td class="tdvalor">0,00</td></tr>';
			$retorno .= '<tr><td class="tddre">Abatimentos</td><td class="tdvalor">0,00</td></tr>';
				
			//--- Impostos sobre a venda ----------------------------------
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			$select->from(array('f'=>'tb_financeiropag','*'), array('sum(p.valor_apagar) as valor'))
			->join(array('p'=>'tb_financeiropagparc'),'f.id = p.id_financeiropag')
			->where(" f.sit = true and  p.sit = true and p.id_financeiroplcontas in (138,139,140,144,145,151) ".$where);
				
			$stmt = $db->query($select);
			$objimp = $stmt->fetchAll();
			
			foreach ($objimp as $valor);
			if($valor->valor == NULL) $impostos = 0;
			else $impostos = $valor->valor;
				
			$retorno .= '<tr><td class="tddre">Impostos e contribuições incidentes sobre vendas</td><td class="tdvalor">'.number_format($impostos,2,",",".").'</td></tr>';
				
			$receitaOpebruto = ($venda+$outreceitas)-$impostos;
			$retorno .= '<tr><td class="tdresultado">Receita operacional bruta</td><td class="tdresultadovalor">'.number_format($receitaOpebruto,2,",",".").'</td></tr>';
			$retorno .= '<tr><td class="tdtitulo">(-)Custo das vendas</td><td class="tdtitulovalor">&nbsp;</td></tr>';
				
			//--- CMV ----------------------------------
			$cmvestoque = EstoqueBO::calcularValorcmv($pesq);;
			$receitaopeliq = $receitaOpebruto-$cmvestoque;
			$retorno .= '<tr><td class="tddre">Custo das mercadorias vendidas</td><td class="tdvalor">'.number_format($cmvestoque,2,",",".").'</td></tr>';
				
			$retorno .= '<tr><td class="tdresultado">Resultado operacional líquida</td><td class="tdresultadovalor">'.number_format($receitaopeliq,2,",",".").'</td></tr>';
				
			$retorno .= '<tr><td class="tdtitulo">(-)Despesas operacionais</td><td class="tdtitulovalor">&nbsp;</td></tr>';
				
			//--- Despesas com vendas ----------------------------------
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			$select->from(array('f'=>'tb_financeiropag','*'), array('sum(p.valor_apagar) as valor'))
			->join(array('p'=>'tb_financeiropagparc'),'f.id = p.id_financeiropag')
			->where(" f.sit = true and  p.sit = true and p.id_financeiroplcontas in (153,154,161,162,163,164,147,148,150,178,202) ".$where);
			
			$stmt = $db->query($select);
			$objdesp = $stmt->fetchAll();
				
			foreach ($objdesp as $valor);
			if($valor->valor == NULL) $despvendas = 0;
			else $despvendas = $valor->valor;
				
			$retorno .= '<tr><td class="tddre">Despesas com vendas</td><td class="tdvalor">'.number_format($despvendas,2,",",".").'</td></tr>';
				
			//--- Despesas administrativas ----------------------------------
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			$select->from(array('f'=>'tb_financeiropag','*'), array('sum(p.valor_apagar) as valor'))
			->join(array('p'=>'tb_financeiropagparc'),'f.id = p.id_financeiropag')
			->where(" f.sit = true and  p.sit = true and p.id_financeiroplcontas in (156,157,158,159,160,149,165,166,167,168,169,141,142,170,171,172,173,174,175,176,177,180,181,182,183,184,185,186,189,193,195,196,197,198,199,200,201,203) ".$where);
				
			$stmt = $db->query($select);
			$objdespadm = $stmt->fetchAll();
			
			foreach ($objdespadm as $valor);
			if($valor->valor == NULL) $despadm = 0;
			else $despadm = $valor->valor;
			
			$retorno .= '<tr><td class="tddre">Despesas administrativas</td><td class="tdvalor">'.number_format($despadm,2,",",".").'</td></tr>';
				
			//--- Prolabores ----------------------------------
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			$select->from(array('f'=>'tb_financeiropag','*'), array('sum(p.valor_apagar) as valor'))
			->join(array('p'=>'tb_financeiropagparc'),'f.id = p.id_financeiropag')
			->where(" f.sit = true and  p.sit = true and p.id_financeiroplcontas in (187) ".$where);
				
			$stmt = $db->query($select);
			$objpro = $stmt->fetchAll();
			
			foreach ($objpro as $valor);
			if($valor->valor == NULL) $prolabores = 0;
			else $prolabores = $valor->valor;
				
			$retorno .= '<tr><td class="tddre">Pró-labores</td><td class="tdvalor">'.number_format($prolabores,2,",",".").'</td></tr>';
				
			//--- Gratificacoes ----------------------------------
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			$select->from(array('f'=>'tb_financeiropag','*'), array('sum(p.valor_apagar) as valor'))
			->join(array('p'=>'tb_financeiropagparc'),'f.id = p.id_financeiropag')
			->where(" f.sit = true and  p.sit = true and p.id_financeiroplcontas in (188,179) ".$where);
			
			$stmt = $db->query($select);
			$objgrat = $stmt->fetchAll();
				
			foreach ($objgrat as $valor);
			if($valor->valor == NULL) $gratificacoes = 0;
			else $gratificacoes = $valor->valor;
				
			$retorno .= '<tr><td class="tddre">Outras despesas</td><td class="tdvalor">'.number_format($gratificacoes,2,",",".").'</td></tr>';
			$retorno .= '<tr><td class="tdtitulo">(-)Despesas financeiras líquidas</td><td class="tdtitulovalor">&nbsp;</td></tr><tr>';
				
			//--- Juros ----------------------------------
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			$select->from(array('f'=>'tb_financeiropag','*'), array('sum(p.valor_apagar) as valor'))
			->join(array('p'=>'tb_financeiropagparc'),'f.id = p.id_financeiropag')
			->where(" f.sit = true and  p.sit = true and p.id_financeiroplcontas in (192,146) ".$where);
				
			$stmt = $db->query($select);
			$objjuros = $stmt->fetchAll();
			
			foreach ($objjuros as $valor);
			if($valor->valor == NULL) $juros = 0;
			else $juros = $valor->valor;
				
			//-- desconto de duplicatas ---------------------------------------
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			$select->from(array('f'=>'tb_financeirorec','*'), array('sum(p.valor_apagar - p.valor_pago) as valor'))
			->join(array('p'=>'tb_financeirorecparc'),'f.id = p.id_financeirorec')
			->where(" f.sit = true and p.sit = true and descontodup = 1 ".$wheredup);
				
			$stmt = $db->query($select);
			$objdup = $stmt->fetchAll();
				
			foreach ($objdup as $valor);
			if($valor->valor == NULL) $desdup = 0;
			else $desdup = $valor->valor;
				
			$retorno .= '<td class="tddre">Despesas financeiras</td><td class="tdvalor">'.number_format($juros+$desdup,2,",",".").'</td></tr>';
				
			//--- Taxas ----------------------------------
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			$select->from(array('f'=>'tb_financeiropag','*'), array('sum(p.valor_apagar) as valor'))
			->join(array('p'=>'tb_financeiropagparc'),'f.id = p.id_financeiropag')
			->where(" f.sit = true and  p.sit = true and p.id_financeiroplcontas in (191) ".$where);
				
			$stmt = $db->query($select);
			$objtaxas = $stmt->fetchAll();
			
			foreach ($objtaxas as $valor);
			if($valor->valor == NULL) $taxas = 0;
			else $taxas = $valor->valor;
				
			$retorno .= '<tr><td class="tddre">Tarifas bancárias</td><td class="tdvalor">'.number_format($taxas,2,",",".").'</td></tr>';
				
			$receitasemiprjcsll = $receitaopeliq - ($despvendas+$despadm+$prolabores+$gratificacoes+$juros+$taxas);
			$retorno .= '<tr><td class="tdresultado">Resultado operacional antes do IPRJ e CSLL</td><td class="tdresultadovalor">'.number_format($receitasemiprjcsll,2,",",".").'</td></tr>';
				
			//--- IPI e ICMSST para calculo da provisao do IRPJ e CSLL ----------------------------------
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			$select->from(array('f'=>'tb_financeiropag','*'), array('sum(p.valor_apagar) as valor'))
			->join(array('p'=>'tb_financeiropagparc'),'f.id = p.id_financeiropag')
			->where(" f.sit = true and  p.sit = true and p.id_financeiroplcontas in (138,151) ".$where);
			
			$stmt = $db->query($select);
			$objprovisao = $stmt->fetchAll();
				
			if(isset($valorpro->valor) and $valorpro->valor != NULL) $totalprovisao -= $valorpro->valor;
				
			$lucroirpj = ($totalprovisao*8)/100;
			$lucroirpj = ($lucroirpj*15)/100;
				
			$lucroclss = ($totalprovisao*12)/100;
			$lucroclss = ($lucroclss*9)/100;
				
			$totalprovisao = $lucroclss+$lucroirpj;
				
			$retorno .= '<tr><td class="tddre">Provisão para imposto de renda e contribuição social sobre o lucro</td><td class="tdvalor">'.number_format(($totalprovisao),2,",",".").'</td></tr>';
			$retorno .= '<tr><td class="tdresultado">(=) Resultado líquido do exercício</td><td class="tdresultadovalor">'.number_format($receitasemiprjcsll-$totalprovisao,2,",",".").'</td></tr></tbody></table>';
				
			echo $retorno; */
			
		}
		
		function importaExtrato($params){
			
			$transferencia = new Zend_File_Transfer_Adapter_Http();
			$name = $transferencia->getFileInfo();
				
			if($name){
				try{

					if(($params['contasbanco'] == 1)||($params['contasbanco'] == 2)){
							
						foreach ($name as $val){
							$fname=$val['tmp_name'];
						}
						
						$arquivo = fopen($fname, "r" );
						
						$count = 0;
						while(!feof($arquivo)) {
							$linha = "";
							$linha = fgets($arquivo);
							
							if($params['contasbanco'] == 1){
								$valor = trim(substr($linha,75,15));
								if(trim(substr($linha, 90,3)) == "D") $valor = "-".$valor;
								
								$params['valorconc'] 			= $valor;
								$params['dataconc']				= trim(substr($linha, 2,11));
								$params['idcontaconcilha']		= 1;
								
								if(!empty($valor)){
									FinanceiroBO::gravaValidacaocontas($params);	
								}
							}elseif($params['contasbanco'] == 2){
								$arraylinha = explode(";", $linha);
								$params['valorconc'] 			= $arraylinha[2];
								$params['dataconc']				= $arraylinha[0];
								$params['idcontaconcilha']		= 2;
								
								if(!empty($arraylinha[2])){
									FinanceiroBO::gravaValidacaocontas($params);	
								}
							}
							
							$count++;
						}
						
						fclose($arquivo);
						$count--;
						$arrayRet = array('sit' => 1, 'msg' => 'Extrato impotado com sucesso!<br />Quantidade de linhas importadas: '.$count);
					}else{
						$erro = "Selecione um banco!";
						$arrayRet = array('sit' => 0, 'msg' => $erro);
					}				
				}catch (Zend_Exception $e){
					$erro = "Erro ao importar o arquivo!";
					$erro .= $e->getMessage();
					
					$arrayRet = array('sit' => 0, 'msg' => $erro);
				}		
			}else{
				$erro = "Erro ao fazer enviar o arquivo para o servidor!";
				$arrayRet = array('sit' => 0, 'msg' => $erro);
			}
			
			return $arrayRet;
			
		}
		
	}
?>