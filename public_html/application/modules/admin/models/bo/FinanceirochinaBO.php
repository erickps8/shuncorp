<?php
	class FinanceirochinaBO{		
		function listaContasabertas($params){
			$obj = new CompraspedModel();
			return $obj->fetchAll('fin = 3 and entregue != 1 and id_cliente = 662');			
		}
				
		/*--Pagamentos a compras---------------------------
		 * Lista compras por Venda OR ------------------
		 */				
		function listaPagamentoscompras($var){
			
			foreach (KangvendasBO::listaVendaskangforn($var) as $listpk):
				$ids .= $listpk->id_kang_compra.",";
			endforeach;
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('p'=>'tb_fin_contasapagar','*'),
			        array('p.id as idconta','p.*','DATE_FORMAT(p.dt_pagamento,"%d/%m/%Y") as dtpag','DATE_FORMAT(p.vencimento,"%d/%m/%Y") as dtven','f.id_kang_compra'))
			        ->join(array('f'=>'tb_finpurchase'), 'f.id_contasapagar = p.id')
			        ->where("p.exclusao = 0 and f.id_kang_compra in (".substr($ids, 0,-1).")");
			  		
			$stmt = $db->query($select);
			return $stmt->fetchAll();				
		}
		
		//-- Busca pagamento por ID de compra PK -----------------------------------------
		function buscaPagamentoscompras($var,$tipo){
			
			if($tipo==2):	
				$where = "and md5(f.id_tai_compra) = '".$var."'";
			else:
				$where = "and md5(f.id_kang_compra) = '".$var."'";
			endif;			
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('p'=>'tb_fin_contasapagar','*'), array('p.id as idconta','DATE_FORMAT(p.emissao,"%d/%m/%Y") as dtpag','DATE_FORMAT(p.vencimento,"%d/%m/%Y") as dtven','DATE_FORMAT(p.dt_pagamento,"%d/%m/%Y") as dtpagamento','f.valor as vlparc'))
			        ->join(array('f'=>'tb_finpurchase'), 'f.id_contasapagar = p.id')
			        ->where("p.exclusao = 0 ".$where);
			  		
			$stmt = $db->query($select);
			return $stmt->fetchAll();				
		}
		
		/*-- Busca pagamento por ID de venda  -----------------------------------------
		 * Usado em gerarpedidocompra
		*/
		function buscaPagamentosporvenda($var){
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_kang_compra','*'), array('p.id as idconta','DATE_FORMAT(p.emissao,"%d/%m/%Y") as dtpag','DATE_FORMAT(p.vencimento,"%d/%m/%Y") as dtven','f.valor as vlparc'))
			        ->join(array('f'=>'tb_finpurchase'), 'f.id_kang_compra = t.id_kang_compra')
			        ->join(array('p'=>'tb_fin_contasapagar'), 'f.id_contasapagar = p.id')
			        ->where("p.exclusao = 0 and md5(t.id_ped) = '".$var['ped']."'");
			  		
			$stmt = $db->query($select);
			return $stmt->fetchAll();				
		}
		
		/*-- Busca pagamento por ID de comercial invoice  -----------------------------------------
		 * Usado em vendaspurchase
		*/
		function buscaPagamentosporinvoice($var){
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_kang_compra','*'), array('p.id as idconta','DATE_FORMAT(p.emissao,"%d/%m/%Y") as dtpag','DATE_FORMAT(p.vencimento,"%d/%m/%Y") as dtven','DATE_FORMAT(p.dt_pagamento,"%d/%m/%Y") as dtpagamento','f.valor as vlparc'))
					->join(array('v'=>'tb_kang_cominvoiceprod'), 'v.id_kang_compra = t.id_kang_compra')
			        ->join(array('f'=>'tb_finpurchase'), 'f.id_kang_compra = t.id_kang_compra')
			        ->join(array('p'=>'tb_fin_contasapagar'), 'f.id_contasapagar = p.id')
			        ->where("p.exclusao = 0 and md5(v.id_cominvoice) = '".$var['ped']."'")
			        ->group('f.id')
			        ->order('p.id');
			  		
			$stmt = $db->query($select);
			return $stmt->fetchAll();				
		}
		
		function listaTaxreturn($var){
			
			foreach (KangvendasBO::listaVendaskangforn($var) as $listpk):
				$ids .= $listpk->id_kang_compra.",";
			endforeach;
			
			$boa	= new FinanceirochinavalidateModel();
			$bo		= new FinanceirochinataxreturnModel();
			
			return $bo->fetchAll("id_kang_compra in (".substr($ids, 0,-1).")");	
		}
		
		//--listas 2 estagio do tax return ------------------------------------
		function listaTaxreturncad(){
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_taxreturn','*'),
			        array('t.id as idtax','DATE_FORMAT(t.dtcad,"%d/%m/%Y") as dtcad'))
			        ->joinLeft(array('p'=>'tb_fin_contasareceber'), 't.id_fin_contasareceber = p.id')
			        ->order("t.id desc");
			  		
			$stmt = $db->query($select);
			return $stmt->fetchAll();				
		}
		
		
		//----- tax return antigo---- apagar----------------
		
		function listaContasreturn(){
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('p'=>'tb_fin_contasapagar','*'),
			        array('p.id as idconta','p.*','DATE_FORMAT(p.emissao,"%d/%m/%Y") as dtcad','DATE_FORMAT(p.vencimento,"%d/%m/%Y") as dtven','c.EMPRESA as FORNECEDOR'))
			        ->joinLeft(array('c'=>'clientes'),'p.id_fornecedor = c.ID')
			        ->where("p.id_planoconta = 4 and p.exclusao = 0 and id_taxreturn is NULL")
			        ->order('p.id_fornecedor','asc')
			  		->order('p.id','asc');
			  		
			$stmt = $db->query($select);
			return $stmt->fetchAll();				
		}
		
		function listaFornecedorecontasreturn(){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select = $db->select();
			
			$select->from(array('p'=>'tb_fin_contasapagar','*'),
			        array('p.*','DATE_FORMAT(p.emissao,"%d/%m/%Y") as dtcad','DATE_FORMAT(p.vencimento,"%d/%m/%Y") as dtven','c.EMPRESA as FORNECEDOR','ch.nome'))
			        ->joinLeft(array('c'=>'clientes'),'p.id_fornecedor = c.ID')
			        ->joinLeft(array('ch'=>'tb_clientechina'), 'p.id_fornecedor = ch.id_cliente')
			        ->where("p.id_planoconta = 4 and p.exclusao = 0 and id_taxreturn is NULL")
			        ->order('c.EMPRESA','asc')
			        ->order('p.out_fornecedor','asc')
			  		->group('c.ID')
			  		->group('p.out_fornecedor');
			  		
			$stmt = $db->query($select);
			return $stmt->fetchAll();	
		}

		
		///---------------------------------		
		
		function gravaInvoicereturn($params){
			$usuario = Zend_Auth::getInstance()->getIdentity();
			
			$bop	= new FinanceirochinavalidateModel();
			$bo		= new FinanceirochinataxreturnModel();
			
			$bof	= new FinanceirochinaModel();
			$boc	= new FinanceirochinapagarModel();
			
			$bov	= new KangvendasModel();
			$boi	= new KanginvoiceModel();
						
			$pk		= $params['pkcominvoice'];
			
			$array['invoice']			= $params['invoice'];
			$array['dtcad']				= date("Y-m-d");
			$array['valor']				= str_replace(",",".",str_replace(".","",$params['vl_invoice']));
			$array['id_kang_compra']	= $pk;
			
			$id = $bo->insert($array);
			
			$vertpk = 0;
			foreach (KangvendasBO::listaVendaskangforn(md5($params['cominvoice'])) as $listpk):
				if(count($bo->fetchAll("id_kang_compra = ".$listpk->id_kang_compra))<1):
					$vertpk = 1;
				endif;				
			endforeach;
			
			if($vertpk==0):
				$arrayinv['statusfin'] = 2;
			else:
				$arrayinv['statusfin'] = 1;
			endif;
			
			$boi->update($arrayinv, "id = ".$params['cominvoice']);
			
			LogBO::cadastraLog("ADM/Fin chines/Ret impostos",2,$usuario->ID,$id,"Retorno de imposto ".$id);
			
			
			 //---Arquivos-------------------------------
	         	
         	 $arquivo = isset($_FILES['anexo_invoice']) ? $_FILES['anexo_invoice'] : FALSE;
         	 $ext1 = end(explode(".",$_FILES['anexo_invoice']['name'])); 
	         
			 $pasta = Zend_Registry::get('pastaPadrao')."public/imgfinanceiro/imginvoice/";
			 				 
			 if (!(is_dir($pasta))){
				if(!(mkdir($pasta, 0777))){
                   	echo ("Alerta: pasta de upload nao existe, e nao pode ser criada");
                	return $this;                           
                 }
             }
                   
             if(!(is_writable($pasta))){
             	echo ("Alerta: pasta sem permissao de escrita");
                return $this;                   
             }
			 				 
			 if(is_uploaded_file($arquivo['tmp_name'])){                                
                  if (move_uploaded_file($arquivo["tmp_name"], $pasta . $id.".".$ext1)) {
                  		//print "Upload executado com sucesso!!!<br />";
                    	//Zend_Debug::dump($arquivo1);
                    	$arrayarq['anexo'] = $id.".".$ext1;
                    	$bo->update($arrayarq,"id = ".$id);
                  } else {
                        echo ("Alerta: Nao foi possivel fazer o upload para $pasta");
                        return $this;                                           
                  }                               
             }else{
	             //echo "erro ao carregar imagem";
             }
             
		    				 
		}		
		
		function listaInvoicesregisrered(){
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_taxreturn','*'),
			        array('t.*','t.id as idtax','DATE_FORMAT(t.dtcad,"%d/%m/%Y") as dtcad','DATE_FORMAT(t.dtaplic,"%d/%m/%Y") as dtaplic',
			        'p.id as idcont','t.valor','t.valoreturn','p.valor_pago','DATE_FORMAT(p.dt_pagamento,"%d/%m/%Y") as dtpag','DATE_FORMAT(p.vencimento,"%d/%m/%Y") as dtvenc'))
			        ->join(array('c'=>'tb_finpurchase'),'c.id_kang_compra = t.id_kang_compra')
			        ->join(array('p'=>'tb_fin_contasapagar'),'p.id = c.id_contasapagar')
			  		->order('t.id','desc');
			  		
			$stmt = $db->query($select);
			return $stmt->fetchAll();				
		}
				
		function listaInvoicereturn($var){			
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			if($var==1):
				$select->from(array('t'=>'tb_taxreturn','*'),
			        array('t.*','t.anexo as anexotax','t.id as idtax','DATE_FORMAT(t.dtcad,"%d/%m/%Y") as dtcad',
			        'DATE_FORMAT(t.dtaplic,"%d/%m/%Y") as dtaplic','p.id as idcont','t.valor','t.valoreturn','p.valor_pago',
			        'r.id as idreceb','DATE_FORMAT(r.vencimento,"%d/%m/%Y") as dtvencimento'))
			        ->join(array('c'=>'tb_finpurchase'),'c.id_kang_compra = t.id_kang_compra')
			        ->join(array('p'=>'tb_fin_contasapagar'),'p.id = c.id_contasapagar')
			        ->join(array('r'=>'tb_fin_contasareceber'),'r.id = t.id_fin_contasareceber')
			        ->where("t.id_fin_contasareceber is not NULL")
			  		->order('t.id desc')
			  		->group("t.id");
			else:				
				$select->from(array('t'=>'tb_taxreturn','*'),
			        array('t.*','t.anexo as anexotax','t.id as idtax','DATE_FORMAT(t.dtcad,"%d/%m/%Y") as dtcad','DATE_FORMAT(t.dtaplic,"%d/%m/%Y") as dtaplic','p.id as idcont','t.valor','t.valoreturn','p.valor_pago'))
			        ->join(array('c'=>'tb_finpurchase'),'c.id_kang_compra = t.id_kang_compra')
			        ->join(array('p'=>'tb_fin_contasapagar'),'p.id = c.id_contasapagar')
			        ->where("t.id_fin_contasareceber is NULL")
			  		->order('t.id desc')
			  		->group("t.id");				
			endif;
			  		
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		/*function listaInvoicereturnfinal(){			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_taxreturn','*'),
			        array('t.*','t.id as idtax','DATE_FORMAT(t.dtcad,"%d/%m/%Y") as dtcad','DATE_FORMAT(t.dtaplic,"%d/%m/%Y") as dtaplic','DATE_FORMAT(p.vencimento,"%d/%m/%Y") as dtrec','p.id as idrec'))
			        ->join(array('p'=>'tb_fin_contasareceber'),'p.id = t.id_fin_contasareceber and p.st_conc != 0')
			        ->order('t.id','desc');
			  		
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}*/
		
		function atualizaInvoicereturn($params){
			$bop	= new FinanceirochinavalidateModel();
			$bo		= new FinanceirochinataxreturnModel();
			
			$boc	= new FinanceirochinaModel();
			$bof	= new FinanceirochinareceberModel();
			
			$usuario = Zend_Auth::getInstance()->getIdentity();
			
			foreach (FinanceirochinaBO::listaInvoicereturn() as $listar):
				if(!empty($params['vl_'.$listar->idtax])):
					$arrayfin['valor_apagar'] 	= str_replace(",",".",str_replace(".","",$params['vl_'.$listar->idtax]));
					$arrayfin['vencimento'] 	= substr($params['dtrec_'.$listar->idtax],6,4).'-'.substr($params['dtrec_'.$listar->idtax],3,2).'-'.substr($params['dtrec_'.$listar->idtax],0,2);
										
					if(empty($listar->id_fin_contasareceber)):
						$arrayfin['emissao']  			= date("Y-m-d");
						$arrayfin['moeda']				= "RMB";
						$arrayfin['id_fornecedor'] 		= 863;
						$arrayfin['n_documento'] 		= $listar->invoice;
						$arrayfin['fatura'] 			= "1/1";
						$arrayfin['id_planoconta'] 		= 14; 
						$arrayfin['id_mod_pagamento'] 	= 2;
						
						$idf = $bof->insert($arrayfin);
					else:
						$bof->update($arrayfin, "id = ".$listar->id_fin_contasareceber);
						$idf = $listar->id_fin_contasareceber;
					endif;
				
					$array['dtaplic']				= substr($params['dt_'.$listar->idtax],6,4).'-'.substr($params['dt_'.$listar->idtax],3,2).'-'.substr($params['dt_'.$listar->idtax],0,2);
					$array['valoreturn']			= str_replace(",",".",str_replace(".","",$params['vl_'.$listar->idtax]));
					$array['id_fin_contasareceber']	= $idf; 
					$bo->update($array,"id = ".$listar->idtax);				 
					LogBO::cadastraLog("ADM/Fin chines/Ret impostos",2,$usuario->ID,$listar->idtax,"Atualiza Ret imposto ".$listar->idtax);
							
				endif;
			endforeach;
			
		}
		
		
		function listaValidate($params){
			$obj = new FinanceirochinavalidateModel();
			
	        if($params[tp] == 2) $sql = $sql.'and tipo = "Entrada" ';
	        elseif($params[tp] == 3) $sql = $sql.'and tipo = "Saida" ';
	        
	        if($params[sit] == 3) $sql = $sql.'and id_conta is not null ';
	        elseif($params[sit] == 2) $sql = $sql.'and id_conta is null ';
	        
	        if((!empty($params[di])) and (!empty($params[df]))) $sql = $sql.'and data between "'.$params[di].'" and "'.$params[df].'"';
	        if((!empty($params[di])) and (empty($params[df]))) $sql = $sql.'and data >= "'.$params[di].'"';
	        if((empty($params[di])) and (!empty($params[df]))) $sql = $sql.'and data <= "'.$params[df].'"';
						
			return $obj->fetchAll('situacao = true and s_conta = '.$params['idc'].' '.$sql,'data desc');			
		}
		
		//---Lista contas a pagar----------------------------------
		function listaContaspagar($pesq){
			$where = " and f.baixa != 1";
			$limit=20;
			
			if($pesq==1):
				$where = " and vencimento = '".date('Y-m-d')."'";
			elseif($pesq==7):
				$data = date('Y-m-d',mktime(0,0,0,date('m'),date('d')+7,date('Y')));
				$where = " and f.exclusao = 0 and vencimento >= '".date('Y-m-d')."' and vencimento <= '".$data."'";
			elseif($pesq==-1):
				$where = " and f.exclusao = 0 and vencimento < '".date('Y-m-d')."'";
			elseif($pesq==2):
				$where = " and f.exclusao = 0 and vencimento >= '".date('Y-m-d')."'";
			endif;
			
			
			if(!empty($pesq['fil'])){
			    $where = " and f.exclusao = 0";
			    
				if($pesq['fil']=='avencerhoje'):
					$where .= " and f.vencimento = '".date('Y-m-d')."'";
					$limit = "";
				elseif($pesq['fil']=='avencersem'):
					$data = date('Y-m-d',mktime(0,0,0,date('m'),date('d')+7,date('Y')));
					$where .= " and f.vencimento >= '".date('Y-m-d')."' and f.vencimento <= '".$data."'";
					$limit = "";
				elseif($pesq['fil']=='vencidas'):
					$where .= " and f.vencimento < '".date('Y-m-d')."'";
					$limit = "";
				elseif($pesq['fil']=='avencer'):
					$where .= " and f.vencimento >= '".date('Y-m-d')."'";
					$limit = "";
				endif;
			}
			
			
			if($pesq['tipo']==1):
				if(!empty($pesq['buscaid'])):
					$where = " and f.id = '".substr($pesq['buscaid'],1)."'";
					$limit=1000;
				else:
					$where = "";
				endif;
			elseif ($pesq['tipo']==2):
				if(($pesq['buscafor']!=0) and ($pesq['buscafor']!='out')):
					$forn = explode('|', $pesq['buscafor']);
					
					if($forn[1]==1):
						$where = " and f.id_usuarios = ".$forn[0];
						$limit="";
					else:
						$where = " and f.id_fornecedor = ".$forn[0];
						$limit="";
					endif;
					
				elseif(!empty($pesq['buscaoutfor'])):
					$where = " and f.id_fornecedor is NULL and id_usuarios is NULL and f.out_fornecedor like '%".$pesq['buscaoutfor']."%'";
				endif;
			
				$limit="";
				
			elseif ($pesq['tipo']==3):
				if(!empty($pesq['buscavalor'])):
					$where = " and f.valor_apagar = '".str_replace(",",".",str_replace(".","",$pesq['buscavalor']))."'";
					$limit=1000;
				endif;
			elseif ($pesq['tipo']==4):
				if(!empty($pesq['buscapurc'])):
					$boc	= new FinanceirochinaModel();
					$bocp	= new FinanceirochinapurchaseModel();
					if((substr($pesq['buscapurc'],0,2)=="PK") || (substr($pesq['buscapurc'],0,2)=="pk")):
						foreach ($bocp->fetchAll("id_kang_compra = ".substr($pesq['buscapurc'],2)) as $listpurc):
							$ids	.= $listpurc->id_contasapagar.",";
						endforeach;					
					elseif((substr($pesq['buscapurc'],0,2)=="PT")||(substr($pesq['buscapurc'],0,2)=="pt")):
						foreach ($bocp->fetchAll("id_tai_compra = ".substr($pesq['buscapurc'],2)) as $listpurc):
							$ids	.= $listpurc->id_contasapagar.",";
						endforeach;
					endif;
					if(!empty($ids)):
						$where = " and f.id in (".substr($ids,0,-1).")";
						$limit=1000;
					else:
						$where = " and f.id in ('')";
						$limit=1000;
					endif;
				endif;
			elseif ($pesq['tipo']==5):
				if($pesq['buscaplcontas']!=0):
					$where = " and f.id_planoconta = ".$pesq['buscaplcontas'];
					$limit=1000;
				endif;
			else:
				$limit=20;
			endif;
			
			if((!empty($pesq[dtini])) || (!empty($pesq[dtfim]))):
				if(!empty($pesq[dtini])) $di	= substr($pesq[dtini],6,4).'-'.substr($pesq[dtini],3,2).'-'.substr($pesq[dtini],0,2);
				if(!empty($pesq[dtfim])) $df	= substr($pesq[dtfim],6,4).'-'.substr($pesq[dtfim],3,2).'-'.substr($pesq[dtfim],0,2);
			
				if((!empty($di)) and (!empty($df))): 
					$where .= ' and emissao between "'.$di.'" and "'.$df.'"';
					$limit=1000;
				elseif((!empty($di)) and (empty($df))): 
					$where .= ' and emissao >= "'.$di.'"';
					$limit=1000;
				elseif((empty($di)) and (!empty($df))): 
					$where .= ' and emissao <= "'.$df.'"';
					$limit=1000;
				endif;
			endif;
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('f'=>'tb_fin_contasapagar','*'),
			        array('f.*','f.id as idfin','DATE_FORMAT(f.emissao,"%d/%m/%Y") as dtcad','DATE_FORMAT(f.vencimento,"%d/%m/%Y") as dtvenc','c.EMPRESA','f.moeda as moedacont', 'u.nome as nomeusuario'))
			        ->joinLeft(array('c'=>'clientes'),'c.ID = f.id_fornecedor')
			        ->joinLeft(array('u'=>'tb_usuarios'),'u.id = f.id_usuarios')
			        ->join(array('p'=>'tb_conta_plano'), 'f.id_planoconta = p.id_conta_plano')
			        ->where("f.id is not null ".$where)
			        ->order('f.id desc')
			        ->limit($limit);
			  		
			$stmt = $db->query($select);
			
			return $stmt->fetchAll();
		}
		
		//---Lista contas a receber----------------------------------
		function listaContasreceber($pesq){
			$where = " and f.baixa != 1";
			
			$where="";
			if($pesq==1):
				$where = " and vencimento = '".date('Y-m-d')."'";
			elseif($pesq==7):
				$data = date('Y-m-d',mktime(0,0,0,date('m'),date('d')+7,date('Y')));
				$where = " and f.exclusao = 0 and vencimento >= '".date('Y-m-d')."' and vencimento <= '".$data."'";
			elseif($pesq==-1):
				$where = " and f.exclusao = 0 and f.baixa != 1 and vencimento < '".date('Y-m-d')."'";
			elseif($pesq==2):
				$where = " and f.exclusao = 0 and vencimento >= '".date('Y-m-d')."'";
			endif;
				
				
			if(!empty($pesq['fil'])){
				$where = " and f.exclusao = 0";
				 
				if($pesq['fil']=='avencerhoje'):
					$where .= " and f.vencimento = '".date('Y-m-d')."'";
					$limit = "";
				elseif($pesq['fil']=='avencersem'):
					$data = date('Y-m-d',mktime(0,0,0,date('m'),date('d')+7,date('Y')));
					$where .= " and f.vencimento >= '".date('Y-m-d')."' and f.vencimento <= '".$data."'";
					$limit = "";
				elseif($pesq['fil']=='vencidas'):
					$where .= " and f.baixa != 1 and f.vencimento < '".date('Y-m-d')."'";
					$limit = "";
				elseif($pesq['fil']=='avencer'):
					$where .= " and f.vencimento >= '".date('Y-m-d')."'";
					$limit = "";
				endif;
			}
			
			
			if(isset($pesq['tiporec'])){
    			if($pesq['tiporec']==1):
    				$where = "";
    				if(!empty($pesq['buscaidrec'])):
    					$where = " and f.id = '".substr($pesq['buscaidrec'],1)."'";
    				else:
    					$where = "";
    				endif;
    				$limit=1000;
    			elseif ($pesq['tiporec']==2):
    				$where = "";
    				if($pesq['buscaforrec']=='out'):
    					$where = " and f.id_fornecedor = 0 and f.out_fornecedor like '%".$pesq['buscaoutforrec']."%'";
    					$limit=1000;
    				elseif($pesq['buscaforrec']!=0):
    					$where = " and f.id_fornecedor = ".$pesq['buscaforrec'];
    					$limit=1000;
    				endif;
    				
    				
    			elseif ($pesq['tiporec']==3):
    				$where = "";
    				if(!empty($pesq['buscavalorrec'])):
    					$where = " and f.valor_apagar = '".str_replace(",",".",str_replace(".","",$pesq['buscavalorrec']))."'";
    				endif;
    				$limit=1000;
    				
    			elseif ($pesq['tiporec']==4):
    				if($pesq['buscaplcontas']!=0):
    					$where = " and f.id_planoconta = ".$pesq['buscaplcontas'];
    					$limit=1000;
    				endif;
    			elseif ($pesq['tiporec']==5):
    				if($pesq['buscainvoice']!=""):
    					$bo		= new FinanceirochinaModel();
    					$boi	= new FinanceirochinainvoiceModel();
    				
    					foreach ($boi->fetchAll("id_kang_cominvoice = '".ereg_replace("[^0-9]", " ", $pesq['buscainvoice'])."'") as $invoice){
    					    $idfininvoice .= $invoice->id_fin_contasareceber.",";
    					}
    					
    					if(count($invoice)>0){
    					    $where = " and f.id in (".substr($idfininvoice,0,-1).")";
    					}
    					
    				endif;
    			endif;
			}else $limit=20;
			
			if((!empty($pesq['dtinirec'])) || (!empty($pesq['dtfimrec']))):
				if(!empty($pesq['dtinirec'])) $di	= substr($pesq['dtinirec'],6,4).'-'.substr($pesq['dtinirec'],3,2).'-'.substr($pesq['dtinirec'],0,2);
				if(!empty($pesq['dtfimrec'])) $df	= substr($pesq['dtfimrec'],6,4).'-'.substr($pesq['dtfimrec'],3,2).'-'.substr($pesq['dtfimrec'],0,2);
			
				if((!empty($di)) and (!empty($df))): 
					if($limit==1000):
						$where .= ' and emissao between "'.$di.'" and "'.$df.'"';
					endif;
				elseif((!empty($di)) and (empty($df))):
					if($limit==1000): 
						$where .= ' and emissao >= "'.$di.'"';
					endif;
				elseif((empty($di)) and (!empty($df))):
					if($limit==1000): 
						$where .= ' and emissao <= "'.$df.'"';
					endif;
				endif;
			endif;			
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('f'=>'tb_fin_contasareceber','*'),
			        array('f.*','f.id as idfin','DATE_FORMAT(f.emissao,"%d/%m/%Y") as dtcad','DATE_FORMAT(f.vencimento,"%d/%m/%Y") as dtvenc','c.EMPRESA','f.moeda as moedacont','p.*'))
			        ->joinLeft(array('c'=>'clientes'),'c.ID = f.id_fornecedor')
			        ->join(array('p'=>'tb_conta_plano'), 'f.id_planoconta = p.id_conta_plano')
			        ->where("f.id is not null ".$where)
			        ->order('f.id desc')
			        ->limit($limit);
			  		
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		//--Plano de contas------------------------------
		function listarPlanosconta(){
			$bo		= new FinanceirochinaplanocontasModel();
			return $bo->fetchAll("sit = true","no_conta_plano asc");
		}
		
		function gravaPlanosconta($params){
			$bo		= new FinanceirochinaplanocontasModel();
			$array['no_conta_plano']	= strtoupper($params['planoconta']);
			
			if(!empty($params['idplanoconta'])):
				$bo->update($array, "id_conta_plano = ".$params['idplanoconta']);
				$id = $params['idplanoconta'];
			else:
				$id = $bo->insert($array);
			endif;
			$usuario = Zend_Auth::getInstance()->getIdentity();
			LogBO::cadastraLog("ADM/Fin chines/Plano contas",2,$usuario->ID,$id,"Plano conta ".$id);
		}
		
		function removePlanosconta($params){
			$bo		= new FinanceirochinaplanocontasModel();
			$array['sit']	= false;
			
			$bo->update($array, "md5(id_conta_plano) = '".$params['idplano']."'");
			
			foreach ($bo->fetchAll("md5(id_conta_plano) = '".$params['idplano']."'") as $lista);
			
			$usuario = Zend_Auth::getInstance()->getIdentity();
			LogBO::cadastraLog("ADM/Fin chines/Plano contas",3,$usuario->ID,$lista->id_conta_plano,"Plano conta ".$lista->id_conta_plano);
		}
		
		//--Contas bancarias------------------------------
		function listarContasbancarias(){
			$bo		= new FinanceirochinacontasbancModel();
			return $bo->fetchAll("status = true");
		}
		
		function gravaContasbancarias($params){
			$bo		= new FinanceirochinacontasbancModel();
			$array['nome']		= strtoupper($params['nome']);
			$array['banco']		= strtoupper($params['banco']);
			$array['agencia']	= strtoupper($params['agencia']);
			$array['conta']		= strtoupper($params['conta']);
			$array['moeda']		= strtoupper($params['moeda']);
			$array['status']	= true;
			
			if(!empty($params['idbanco'])):
				$bo->update($array, "id = ".$params['idbanco']);
				$id = $params['idbanco'];
			else:
				$id = $bo->insert($array);
			endif;
			
			$usuario = Zend_Auth::getInstance()->getIdentity();
			LogBO::cadastraLog("ADM/Fin chines/Bancos",2,$usuario->ID,$id,"Conta ".$id);
		}
		
		function removeContasbancarias($params){
			$bo		= new FinanceirochinacontasbancModel();
			$array['status']	= false;
			
			$bo->update($array, "md5(id) = '".$params['idbanco']."'");
			
			foreach ($bo->fetchAll("md5(id) = '".$params['idbanco']."'") as $lista);
			
			$usuario = Zend_Auth::getInstance()->getIdentity();
			LogBO::cadastraLog("ADM/Fin chines/Bancos",3,$usuario->ID,$lista->id,"Conta ".$lista->id);
			
		}
		
		//--Validacao de contas------------------------------
		function listarConcilhacao($conta = null, $pesq){
            $where = "";
			$whereSeg = "f.situacao = 1";

			//----filtro por id-----------
			if(!empty($pesq['buscaidconc'])):
				$where	= " and f.id = ".$pesq['buscaidconc'];
			endif;
			
			//----filtro por valor-----------
			if((!empty($pesq['buscavliniconc'])) || (!empty($pesq['buscavlfimconc']))):
				if(!empty($pesq['buscavliniconc'])) $vli	= str_replace(",",".",str_replace(".","",$pesq['buscavliniconc']));
				if(!empty($pesq['buscavlfimconc'])) $vlf	= str_replace(",",".",str_replace(".","",$pesq['buscavlfimconc']));
			
				if((!empty($vli)) and (!empty($vlf))): 
					$where .= ' and ((f.valor >= "'.$vli.'" and f.valor <="'.$vlf.'") or (f.valor <= "-'.$vli.'" and f.valor >="-'.$vlf.'"))';
				elseif((!empty($vli)) and (empty($vlf))): 
					$where .= ' and (f.valor >= "'.$vli.'" or f.valor = "-'.$vli.'")';
				elseif((empty($vli)) and (!empty($vlf))): 
					$where .= ' and (f.valor <= "'.$vlf.'" or f.valor <= "-'.$vlf.'")';
				endif;
			endif;
			
			//----filtro por data-----------
			if((!empty($pesq['dtiniconc'])) || (!empty($pesq['dtfimconc']))) {
                if (!empty($pesq['dtiniconc'])) $di = substr($pesq['dtiniconc'], 6, 4) . '-' . substr($pesq['dtiniconc'], 3, 2) . '-' . substr($pesq['dtiniconc'], 0, 2);
                if (!empty($pesq['dtfimconc'])) $df = substr($pesq['dtfimconc'], 6, 4) . '-' . substr($pesq['dtfimconc'], 3, 2) . '-' . substr($pesq['dtfimconc'], 0, 2);

                if ((!empty($di)) and (!empty($df))):
                    $where .= ' and f.data between "' . $di . '" and "' . $df . '"';
                elseif ((!empty($di)) and (empty($df))):
                    $where .= ' and f.data >= "' . $di . '"';
                elseif ((empty($di)) and (!empty($df))):
                    $where .= ' and f.data <= "' . $df . '"';
                endif;
            }

            $sessaoFin = new Zend_Session_Namespace('Default');
			
			if($where != ""):
				$sessaoFin->whereconc = $where;
				$sessaoFin->contaconc = $conta;
			elseif($sessaoFin->whereconc != ""):
				if($conta == $sessaoFin->contaconc):
					$where	= $sessaoFin->whereconc;
				else:
					$where = " and f.data >= '".date("Y-m-01")."'";
				endif;
			else:
				$where = " and f.data >= '".date("Y-m-01")."'";
			endif;

            $where = $whereSeg . $where;

			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);

			$select = $db->select();

			$select->from(array('f'=>'tb_fin_conc','*'),
				array('f.*','f.id as idconc','DATE_FORMAT(f.data,"%d/%m/%Y") as dtcad','p.id as pconc','r.id as rconc'))
				->joinLeft(array('p'=>'tb_fin_contasapagar'),'f.id = p.st_conc')
				->joinLeft(array('r'=>'tb_fin_contasareceber'),'f.id = r.st_conc')
				->where($where)
				->order('f.data desc')
				->order('f.id desc')
				->limit(1000);

			if($conta and $conta != '') {
				$select->where("f.s_conta = '".$conta."'");
			}

			$stmt = $db->query($select);
            //echo $select->__toString()."\n"; die();
			return $stmt->fetchAll();
		}

		//--Validacao de contas------------------------------
//		function listarConcilhacaoAudit($pesq){
//			//----filtro por data-----------
//			if(!empty($pesq['dtiniconc'])) $di	= substr($pesq['dtiniconc'],6,4).'-'.substr($pesq['dtiniconc'],3,2).'-'.substr($pesq['dtiniconc'],0,2);
//			if(!empty($pesq['dtfimconc'])) $df	= substr($pesq['dtfimconc'],6,4).'-'.substr($pesq['dtfimconc'],3,2).'-'.substr($pesq['dtfimconc'],0,2);
//
//			$where = (!empty($di) and empty($df)) ? ' and f.data >= "' . $di . '"' : '';
//			$where .= (empty($di) and !empty($df)) ? ' and f.data <= "' . $df . '"' : '';
//
//			if($where == "") {
//				$where = " and f.data >= '" . date("Y-m-01") . "'";
//			}
//
//			$where = "(f.situacao = 1 or f.situacao = 0) " . $where;
//
//			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
//			$db->setFetchMode(Zend_Db::FETCH_OBJ);
//
//			$select = $db->select();
//
//			$select->from(array('f'=>'tb_fin_conc','*'),
//				array('f.id as idconc',
//                    'DATE_FORMAT(f.data,"%d/%m/%Y") as dtcad',
//                    'c.id as idC',
//                    'c.nome as contaC',
//                    'f.valor as valorConc',
//                    'c.moeda as moedaConta',
//                    "concat(fcc.nome, ' / ID ', f.conctransf) as contaConc",
//                    "(select group_concat(
//                       concat('P', lpad(p.id, 6, '0'), ' (', DATE_FORMAT(p.emissao,'%d/%m/%Y'), '-', DATE_FORMAT(p.vencimento,'%d/%m/%Y'), ')')
//                    ) from tb_fin_contasapagar p where f.id = p.st_conc) as pagamentos",
//                    "(select group_concat(
//                       concat('R', lpad(f.id, 6, '0'), ' (', DATE_FORMAT(r.emissao,'%d/%m/%Y'), '-', DATE_FORMAT(r.vencimento,'%d/%m/%Y'), ')')
//                    ) from tb_fin_contasareceber r where f.id = r.st_conc) as recebimentos",
//                    'f.situacao as sitConc',
//                    'f.valida'
//                    ))
//				->joinLeft(array('c'=>'tb_fin_contas'),'f.s_conta = c.id')
//				->joinLeft(array('fc'=>'tb_fin_conc'),'f.conctransf = fc.id')
//                ->joinLeft(array('fcc'=>'tb_fin_contas'),'fc.s_conta = fcc.id')
//				->where($where)
//				->order('f.data desc')
//				->order('f.id desc')
//				->limit(1000);
//
//			$stmt = $db->query($select);
//
//			return $stmt->fetchAll();
//		}

    function listarConcilhacaoAudit($pesq){

        if( !empty($pesq['dtiniconc'])) {
            $di	= substr($pesq['dtiniconc'],6,4).'-'.substr($pesq['dtiniconc'],3,2).'-'.substr($pesq['dtiniconc'],0,2);
            $where = ' and f.data >= "'.$di.'"';
        }

        if (!empty($pesq['dtfimconc'])) {
            $df	= substr($pesq['dtfimconc'],6,4).'-'.substr($pesq['dtfimconc'],3,2).'-'.substr($pesq['dtfimconc'],0,2);
            $where .= ' and f.data <= "'.$df.'"';
        }

        if($where == "") {
            $where = " and f.data >= '" . date("Y-m-01") . "'";
        }

        $where = "(f.situacao = 1 or f.situacao = 0) " . $where;

        $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
        $db->setFetchMode(Zend_Db::FETCH_OBJ);

        $select = $db->select();

        $select->from(array('f'=>'tb_fin_conc','*'),
            array('f.id as idconc',
                'DATE_FORMAT(f.data,"%d/%m/%Y") as dtcad',
                'c.id as idC',
                'c.nome as contaC',
                'f.valor as valorConc',
                'c.moeda as moedaConta',
                "concat(fcc.nome, ' / ID ', f.conctransf) as contaConc",
                "(select group_concat(
                       concat('P', lpad(p.id, 6, '0'), ' (', DATE_FORMAT(p.emissao,'%d/%m/%Y'), '-', DATE_FORMAT(p.vencimento,'%d/%m/%Y'), ')')
                    ) from tb_fin_contasapagar p where f.id = p.st_conc) as pagamentos",
                "(select group_concat(
                       concat('R', lpad(f.id, 6, '0'), ' (', DATE_FORMAT(r.emissao,'%d/%m/%Y'), '-', DATE_FORMAT(r.vencimento,'%d/%m/%Y'), ')')
                    ) from tb_fin_contasareceber r where f.id = r.st_conc) as recebimentos",
                'f.situacao as sitConc',
                'f.valida'
            ))
            ->joinLeft(array('c'=>'tb_fin_contas'),'f.s_conta = c.id')
            ->joinLeft(array('fc'=>'tb_fin_conc'),'f.conctransf = fc.id')
            ->joinLeft(array('fcc'=>'tb_fin_contas'),'fc.s_conta = fcc.id')
            ->where($where)
            ->order('f.id desc')
            ->limit(1000);

        $stmt = $db->query($select);

        return $stmt->fetchAll();
    }


		/**
		 * @throws Zend_Db_Statement_Exception
		 * @throws Zend_Exception
		 * @throws Zend_Db_Adapter_Exception
		 * @throws Zend_Db_Exception
		 */
		function listarSaldocontas($conta = null, $params = array()){

			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);

			$select = $db->select();

			$select->from(array('f'=>'tb_fin_conc','*'),
					array('sum(f.valor) as total'))
					->where("f.situacao = 1");

			if(isset($params['dtfimconc']) and $params['dtfimconc'] != null) {
				$df	= substr($params['dtfimconc'],6,4).'-'.substr($params['dtfimconc'],3,2).'-'.substr($params['dtfimconc'],0,2);
				$select->where("f.data <= '" . $df . "'");
			}

			if($conta) {
				$select->where("f.s_conta = '".$conta."'");
			}

			return $db->query($select)->fetchAll();
		}

        function listarSaldocontasAudit($params = array(), $conta = null){
            if(isset($params['dtfimconc']) and $params['dtfimconc'] != null) {
                $df	= substr($params['dtfimconc'],6,4).'-'.substr($params['dtfimconc'],3,2).'-'.substr($params['dtfimconc'],0,2);
            }else{
                $df = date("Y-m-d");
            }

            $where = ($conta) ? " and f.s_conta = '" . $conta . "'" : "";

            $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
            $db->setFetchMode(Zend_Db::FETCH_OBJ);

            $select = $db->select();

            $select->from(array('f'=>'tb_fin_conc','*'),
                array('f.s_conta as id', 'sum(f.valor)as saldo'))
                ->where("f.situacao = 1 and f.data <= '" . $df . "'" . $where)
                ->group('f.s_conta');

            return $db->query($select)->fetchAll();
        }

//        function listarSaldocontasAudit($params = array()){
//            if(isset($params['dtfimconc']) and $params['dtfimconc'] != null) {
//                $df	= substr($params['dtfimconc'],6,4).'-'.substr($params['dtfimconc'],3,2).'-'.substr($params['dtfimconc'],0,2);
//            }else{
//                $df = date("Y-m-d");
//            }
//
//            $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
//            $db->setFetchMode(Zend_Db::FETCH_OBJ);
//
//            $select = $db->select();
//
//            $select->from(array('c'=>'tb_fin_contas','*'),
//                array(
//                    'c.id',
//                    "((select sum(r.valor_pago) from tb_fin_contasareceber r inner join tb_fin_conc fc on fc.id = r.st_conc
//                            where r.id_mod_pagamento = c.id and fc.data <= '" . $df . "' group by r.id_mod_pagamento)
//                        - (select sum(p.valor_pago) from tb_fin_contasapagar p inner join tb_fin_conc fc on fc.id = p.st_conc
//                            where p.id_mod_pagamento = c.id  and fc.data <= '" . $df . "' group by p.id_mod_pagamento))
//                            as saldo"
//                ))
//                ->where("c.status = 1");
//
//            return $db->query($select)->fetchAll();
//        }
		
		function gravaValidacaocontas($params){
		    $usuario = Zend_Auth::getInstance()->getIdentity();
		    
			$bo		= new FinanceirochinavalidateModel();
			
			$data    = substr($params['dataconc'],6,4).'-'.substr($params['dataconc'],3,2).'-'.substr($params['dataconc'],0,2);
			$cambio  = str_replace(",",".",str_replace(".","",$params['taxcambio']));
			$valor   = str_replace(",",".",str_replace(".","",$params['valorconc']));
			
			$array['valor']			= $valor;
			$array['data']			= $data;
			$array['situacao']		= 1; 
			$array['valida']		= 0;
			$array['s_conta']		= $params['idcontaconcilha'];
			
			if($params['contasval']!=0):
			
				$array['id_fin_contas']	= $params['contasval'];
				$array['tax_cambio']	= $cambio;
				
				$array2 = array(
				    'valor'          => ($cambio * $valor) * -1,        
				    'data'           => $data,
				    'valida'         => false,
			        's_conta'        => $params['contasval'],
			        'id_fin_contas'  => $params['idcontaconcilha'],
				    'situacao'       => true,
				);
					
			endif;
			
			if(!empty($params['idconci'])){
			    $bo->update($array, "id = '".$params['idconci']."'");				
				$id = $params['idconci'];				
			}else{
				$id = $bo->insert($array);				
			}
			
			if($params['contasval']!=0){
				$array2['conctransf'] = $id;
				$id2 = $bo->insert($array2);
				
				$array['conctransf'] = $id2;
				$bo->update($array, "id = '".$id."'");
			}
			
			LogBO::cadastraLog("ADM/Fin chines/Conciliação",4,$usuario->id,$id,"Conciliação ".$id);			
		}
		
		function removeConciliacaocontas($params){
			$bof	= new FinanceirochinaModel();
			$bop	= new FinanceirochinapagarModel();
			$bor	= new FinanceirochinareceberModel();
			$bo		= new FinanceirochinavalidateModel();
			
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
			$bof	= new FinanceirochinaModel();
			$bop	= new FinanceirochinapagarModel();
			$bor	= new FinanceirochinareceberModel();
			$bo		= new FinanceirochinavalidateModel();
			
			$array['bloq']	= 1;
			$bop->update($array, "md5(st_conc) = '".$params['conc']."'");
			$bor->update($array, "md5(st_conc) = '".$params['conc']."'");
			
			$arrayc['valida']	= 1;
			
			$bo->update($arrayc, "md5(id) = '".$params['conc']."'");
			
		}
		
		//--Lista contas para valicar------------------------------
		function listarContasvalidar($var){
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
				
			if($var['tipo']==1):			
    			$select->from(array('f'=>'tb_fin_contasapagar','*'), array('f.*','f.id as idfin','DATE_FORMAT(f.emissao,"%d/%m/%Y") as dtcad','DATE_FORMAT(f.vencimento,"%d/%m/%Y") as dtvenc','c.EMPRESA','f.moeda as moedacont','u.nome as userfornecedor'))
    		        ->joinLeft(array('c'=>'clientes'),'c.ID = f.id_fornecedor')
    		        ->joinLeft(array('u'=>'tb_usuarios'),'u.id = f.id_usuarios')
    		        ->join(array('p'=>'tb_conta_plano'), 'f.id_planoconta = p.id_conta_plano')
    		        ->where("(st_conc = 0 || st_conc = '".$var['idc']."')  and baixa = 1 and f.id_mod_pagamento = '".$var['conta']."'")
    		        ->order('f.id desc');
			else:
    			$select->from(array('f'=>'tb_fin_contasareceber','*'), array('f.*','f.id as idfin','DATE_FORMAT(f.emissao,"%d/%m/%Y") as dtcad','DATE_FORMAT(f.vencimento,"%d/%m/%Y") as dtvenc','c.EMPRESA','f.moeda as moedacont'))
    		        ->joinLeft(array('c'=>'clientes'),'c.ID = f.id_fornecedor')
    		        ->join(array('p'=>'tb_conta_plano'), 'f.id_planoconta = p.id_conta_plano')
    		        ->where("(st_conc = 0 || st_conc = '".$var['idc']."') and baixa = 1 and f.id_mod_pagamento = '".$var['conta']."'")
    		        ->order('f.id desc');
			endif;
				
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		function gerarConciliacao($val){
			
			if($val['tipo']==3):
				$boc	= new FinanceirochinavalidateModel();
				$array['conctransf']	= $val['radiovalida'];
				$array['id_fin_contas']	= $val['buscconta'];
				$boc->update($array, "id = ".$val['idc']);
				
				$array['conctransf']	= $val['idc'];
				$array['id_fin_contas']	= $val['conta'];
				$boc->update($array, "id = '".$val['radiovalida']."'");
				
			else:
				$bof	= new FinanceirochinaModel();
				$bop	= new FinanceirochinapagarModel();
				$bor	= new FinanceirochinareceberModel();
						
				$array['st_conc']	= 0;
				$bop->update($array, "st_conc = ".$val['idc']);
				$bor->update($array, "st_conc = ".$val['idc']);
				
				foreach (FinanceirochinaBO::listarContasvalidar($val) as $lista):
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
			LogBO::cadastraLog("ADM/Fin chines/Conciliação",4,$usuario->ID,$val['idc'],"Conciliacao ".$val['idc']);
			
		}
		
		function listarExtratonconc($conta){

			$bof	= new FinanceirochinaModel();
			$bop	= new FinanceirochinapagarModel();
			$bor	= new FinanceirochinareceberModel();
			$boc	= new FinanceirochinavalidateModel();
			
			/* foreach ($bop->fetchAll("st_conc != 0") as $lista):
				$idsconc .= $lista->st_conc.",";
			endforeach;
			
			foreach ($bor->fetchAll("st_conc != 0") as $lista):
				$idsconc .= $lista->st_conc.",";
			endforeach; */
			
			
			$where = "f.id > 0 ";
			
			/* if($conta['bancobusca']!=9):
				foreach ($boc->fetchAll("id = ".$conta['idc']) as $listc);
				$where	= " and f.valor = (".($listc->valor)*(-1).")";
			else:
				foreach ($boc->fetchAll("id = ".$conta['idc']) as $listc);
				if($listc->valor>0):
					$where	= " and f.valor <= 0";
				else:
					$where	= " and f.valor >= 0";
				endif;
				$where	.= " and f.id_fin_contas = ".$conta['conta'];
				
			endif;			
			 
			if($conta['bancobusca']!=9):
				foreach ($boc->fetchAll("id = ".$conta['idc']) as $listc);
				$where	= " and f.valor <= ((".($listc->valor)*(-1).")+0.1)";
				$where	.= " and f.valor >= ((".($listc->valor)*(-1).")-0.1)";
			else:
				foreach ($boc->fetchAll("id = ".$conta['idc']) as $listc);
				$where	= " and (f.valor) <= (((".($listc->valor)*(-1).") / f.tax_cambio)+0.1)";
				$where	.= " and (f.valor) >= (((".($listc->valor)*(-1).") / f.tax_cambio)-0.1)";
			endif; */
					
			$listc = $boc->fetchRow("id = '".$conta['idc']."'");
			
			$where	.= " and f.valor >= '".($listc->valor+0.1)*(-1)."'";
			$where	.= " and f.valor <= '".($listc->valor-0.1)*(-1)."'";
						
			if(!empty($conta)): 
			
				$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			    $db->setFetchMode(Zend_Db::FETCH_OBJ);
				
				$select = $db->select();
				
				$select->from(array('f'=>'tb_fin_conc','*'), array('f.*','f.id as idconc','DATE_FORMAT(f.data,"%d/%m/%Y") as dtcad','f.tax_cambio'))
				    ->joinLeft(array('p'=>'tb_fin_contasapagar'),'p.st_conc = f.id')
				    ->joinLeft(array('r'=>'tb_fin_contasareceber'),'r.st_conc = f.id')
				
				    ->where($where." and f.situacao = 1 and (f.conctransf is null || f.conctransf = '".$conta['idc']."') and f.s_conta = '".$conta['bancobusca']."' and p.id is null and r.id is null")
			        ->order('f.id desc');
				  		
			        $stmt = $db->query($select); 
				return $stmt->fetchAll();
			endif;
		
			//"f.id not in (".substr($idsconc,0,-1).") ".
			
		}
		
		function buscaContaconc($conta){
			$bof	= new FinanceirochinaModel();
			$boc	= new FinanceirochinavalidateModel();
			
			return $boc->fetchAll("id = ".$conta['idc']);
		}
		
		/*-- baixa = 0 -- Cadastrado 
		 * - baixa = 1 -- Baixado 
		 * - baixa = 2 --            */
		
		function gravarContaspag($params){
			$bof	= new FinanceirochinaModel();
			$bop	= new FinanceirochinapagarModel();
			$boa	= new FinanceirochinaanexopagModel();
			$boh	= new FinanceirochinapurchaseModel();
			
			try{
			
				$array['emissao']			=	substr($params['emissaopag'],6,4).'-'.substr($params['emissaopag'],3,2).'-'.substr($params['emissaopag'],0,2);
				$array['vencimento']		=	substr($params['vencimentopag'],6,4).'-'.substr($params['vencimentopag'],3,2).'-'.substr($params['vencimentopag'],0,2);
				$array['moeda']				=	$params['moedapag'];
				$array['valor_apagar']		=	str_replace(',','.',str_replace('.','',$params['valorpag']));
				$array['id_fornecedor']		=	$params['fornpag'];
				$array['out_fornecedor']	=	$params['outfornpag'];
				$array['n_documento']		=	$params['faturapag'];
				$array['fatura']			=	$params['parcpag'];
				$array['obs']				=	$params['obspag'];
				$array['id_planoconta']		=	$params['planocontapag'];
				$array['npurchase']			=	$params['tipopurch'];
				$array['baixa']				= 	0;	
				
				
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
				
				
				
				
				if(!empty($params['idcontapag'])):
					if(!empty($params['liberarpag'])):
						$array['baixa']		= 2;				
					elseif(!empty($params['baixarpag'])):
						$array['baixa']		= 1;					
					endif;
					$bop->update($array, "id = ".$params['idcontapag']);
					$id = $params['idcontapag'];
					
					$boh->delete("id_contasapagar = ".$id);
					
				else:
					$id = $bop->insert($array);
				endif;			
				
				$arrayRes = array('id' => $id, 'erro' => 0);			
			}catch (Zend_Exception $e){
				$arrayRes = array('id' => $id, 'erro' => 1, 'texto' => "Erro ao gravar a conta!");
			}
				
			try{				    
			    if(!empty($id)){
					//--Fixar pedidos kang/tai--------------------
					$ids_array = explode(',',$params['idpurch']);
					for($i=0 ; $i < sizeof($ids_array); $i++){
						if(($params['tipopurch']==1) and ($ids_array[$i]!="")){
							$arraypur['id_contasapagar']	= $id;
							$arraypur['id_kang_compra']		= $ids_array[$i];
							$arraypur['valor']				= str_replace(',','.',str_replace('.','',$params['vlcompra_'.$ids_array[$i]]));
							$boh->insert($arraypur);					
						}elseif(($params['tipopurch']==2) and ($ids_array[$i]!="")){
							$arraypur['id_contasapagar']	= $id;
							$arraypur['id_tai_compra']		= $ids_array[$i];
							$arraypur['valor']				= str_replace(',','.',str_replace('.','',$params['vlcompra_'.$ids_array[$i]]));
							$boh->insert($arraypur);
						}
					}
			    }
			}catch (Zend_Exception $e){
				$arrayRe['erro'] 	= 1;
				$arrayRe['texto'] 	= "Erro ao anexar a invoice na conta!";
			}
				
			$usuario = Zend_Auth::getInstance()->getIdentity();
			LogBO::cadastraLog("ADM/Fin chines/Pagamentos",2,$usuario->ID,$id,"Baixa ".$id);
			
			try{
				if(!empty($params['baixarpag'])):
					$array['valor_pago']		=	str_replace(',','.',str_replace('.','',$params['valorpagamentopag']));
					$array['id_mod_pagamento']	=	$params['bancopagamentopag'];
					$array['baixa']				= 1;	
					
					if(!empty($params['datapagamentopag'])):
						$array['dt_pagamento']	=	substr($params['datapagamentopag'],6,4).'-'.substr($params['datapagamentopag'],3,2).'-'.substr($params['datapagamentopag'],0,2);
					endif;
					
					$bop->update($array, "id = ".$params['idcontapag']);
					$id = $params['idcontapag'];
					
					$usuario = Zend_Auth::getInstance()->getIdentity();
					LogBO::cadastraLog("ADM/Fin chines/Pagamentos",4,$usuario->ID,$id,"Baixa ".$id);
					
				endif;
			}catch (Zend_Exception $e){
				$arrayRes = array('id' => $id, 'erro' => 1, 'texto' => "Erro ao baixar a conta!");
			}
			//---Arquivos-------------------------------
			
			try{
			    if(!empty($id)){
					$ic = 0;
					foreach ($boa->fetchAll('id_fin_contasapagar = '.$id) as $listanex);
					if(count($listanex)>0):
						$ianex = explode(".",$listanex->nome);
						$ic = substr($ianex[0],-1);
					endif;
					
			        for($i=1;$i<=$params['intarchive'];$i++):
			        	$ic++;
			        	
			         	 $arquivo = isset($_FILES['arquivo'.$i]) ? $_FILES['arquivo'.$i] : FALSE;
						 $nome = $id."_".$ic.substr($_FILES['arquivo'.$i]['name'], strrpos($_FILES['arquivo'.$i]['name'], "."), strlen($_FILES['arquivo'.$i]['name']));
						 
				         echo $pasta = Zend_Registry::get('pastaPadrao')."public/sistema/upload/financeirochina/pagar/";
						 		  	 
						 if (!(is_dir($pasta))){
							if(!(mkdir($pasta, 0777))){
			                   	echo ("Alerta: pasta de anexos a pagar nao existe, e nao pode ser criada");
			                	return $this;                           
			                 }
			             }
			                   
			             if(!(is_writable($pasta))){
			             	echo ("Alerta: pasta sem permissao de escrita");
			                return $this;                   
			             }
						 				 
						 if(is_uploaded_file($_FILES['arquivo'.$i]["tmp_name"])){                                
			             	if (move_uploaded_file($arquivo["tmp_name"], $pasta . $nome)) {
		                  		$arrayarq['nome'] 				 	= $nome;
		                    	$arrayarq['id_fin_contasapagar']	= $id;
		                    	$boa->insert($arrayarq);
			                } else {
			                	echo ("Alerta: Nao foi possivel fazer o upload para $pasta");
				                return $this;                                           
			                }                               
			             }else{
				             //echo "erro ao carregar imagem";
			             }
				    
			         endfor;
			 	}
	         }catch (Zend_Exception $e){
	         	$arrayRe['erro'] 	= 1;
	         	$arrayRe['texto'] 	= "Erro ao anexar os arquivos na conta!";
	         }
		         
	         return $arrayRes;
		}
		
		function listarAnexosapagar($id){
			$bof	= new FinanceirochinaModel();
			$boa	= new FinanceirochinaanexopagModel();
			
			return $boa->fetchAll('md5(id_fin_contasapagar) = "'.$id.'"');
		}
		
		function listarPurchasepagar($id){
			$bof	= new FinanceirochinaModel();
			$boa	= new FinanceirochinapurchaseModel();
			
			return $boa->fetchAll('md5(id_contasapagar) = "'.$id.'"');
		}
		
		function remAnexos($params){
			$bof	= new FinanceirochinaModel();
			$boap	= new FinanceirochinaanexopagModel();
			$boar	= new FinanceirochinaanexorecModel();
			$usuario = Zend_Auth::getInstance()->getIdentity();
			
			if(!empty($params['pay'])):
				$boap->delete('id = '.$params['idanex']);
				
				foreach ($boap->fetchAll('id = '.$params['idanex']) as $listanex);
				@unlink(Zend_Registry::get('pastaPadrao')."public/sistema/upload/financeirochina/pagar/".$listanex->nome);
				
				LogBO::cadastraLog("ADM/Fin chines/Pagamentos",3,$usuario->ID,$listanex->nome,"Remove anexo ".$listanex->nome);
			elseif(!empty($params['rec'])):
				$boar->delete('id = '.$params['idanex']);
				
				foreach ($boar->fetchAll('id = '.$params['idanex']) as $listanex);
				@unlink(Zend_Registry::get('pastaPadrao')."public/sistema/upload/financeirochina/receber/".$listanex->nome);
				LogBO::cadastraLog("ADM/Fin chines/Recebimentos",3,$usuario->ID,$listanex->nome,"Remove anexo ".$listanex->nome);
			endif;
		}
		
		function buscarContapag($id){
			$bof	= new FinanceirochinaModel();
			$bop	= new FinanceirochinapagarModel();
			
			return $bop->fetchAll("md5(id) = '".$id."'");
		}
		
		function liberarContaspag($id){
			$bof	= new FinanceirochinaModel();
			$bop	= new FinanceirochinapagarModel();
			
			$array['baixa']		= 2;			
			$bop->update($array, "md5(id) = '".$id."'");
			
		}
				
		function gravarContasrec($params){
			$bof	= new FinanceirochinaModel();
			$bor	= new FinanceirochinareceberModel();
			$boa	= new FinanceirochinaanexorecModel();
			$boi	= new FinanceirochinainvoiceModel();
			
			try{			
				$array['emissao']			=	substr($params['emissaorec'],6,4).'-'.substr($params['emissaorec'],3,2).'-'.substr($params['emissaorec'],0,2);
				$array['vencimento']		=	substr($params['vencimentorec'],6,4).'-'.substr($params['vencimentorec'],3,2).'-'.substr($params['vencimentorec'],0,2);
				$array['moeda']				=	$params['moedarec'];
				$array['valor_apagar']		=	str_replace(',','.',str_replace('.','',$params['valorrec']));
				$array['id_fornecedor']		=	$params['fornrec'];
				$array['out_fornecedor']	=	$params['outfornrec'];
				$array['n_documento']		=	$params['faturarec'];
				$array['fatura']			=	$params['parcrec'];
				$array['obs']				=	$params['obsrec'];
				$array['id_planoconta']		=	$params['planocontarec'];
				
				$array['valor_pago']		=	str_replace(',','.',str_replace('.','',$params['valorpagamentorec']));
				$array['id_mod_pagamento']	=	$params['bancopagamentorec'];
				
				if(!empty($params['datapagamentorec'])):
					$array['dt_pagamento']	=	substr($params['datapagamentorec'],6,4).'-'.substr($params['datapagamentorec'],3,2).'-'.substr($params['datapagamentorec'],0,2);
				endif;
				
				if(!empty($params['idcontarec'])):
					if(!empty($params['liberarrec'])):
						$array['baixa']		= 2;				
					elseif(!empty($params['baixarec'])):
						$array['baixa']		= 1;				
					endif;
					
					/* baixa = 0 - Excluido
					 * baixa = 1 - Ativo pago
					 * baixa = 2 - Ativo salvo
					 * */
					
					$bor->update($array, "id = ".$params['idcontarec']);
					$id = $params['idcontarec'];
				else:
					$id = $bor->insert($array);
				endif;			
				
				$arrayRes = array('id' => $id, 'erro' => 0);
				
			}catch (Zend_Exception $e){
			    $arrayRes = array('id' => $id, 'erro' => 1, 'texto' => "Erro ao gravar a conta!");
			}
				

			try{
				if(!empty($id)){
					//--Fixar Invoices--------------------
					$boi->delete("id_fin_contasareceber = ".$id);
					
					$ids_array = explode(',',$params['idpurch']);
					for($i=0 ; $i < sizeof($ids_array); $i++){
					    if(!empty($ids_array[$i])):				
							$arraypur['id_fin_contasareceber']	= $id;
							$arraypur['id_kang_cominvoice']		= $ids_array[$i];
							$arraypur['valor']					= str_replace(',','.',str_replace('.','',$params['vlinvoice_'.$ids_array[$i]]));
							$boi->insert($arraypur);
						endif;				
					}
				}
			}catch (Zend_Exception $e){
			    $arrayRe['erro'] 	= 1;
			    $arrayRe['texto'] 	= "Erro ao anexar a invoice na conta!";
			}	
				
				
			//---Arquivos-------------------------------
			
			try{
			    if(!empty($id)){
					$ic = 0;
					foreach ($boa->fetchAll('id_fin_contasareceber = '.$id) as $listanex);
					if(count($listanex)>0):
						$ianex = explode(".",$listanex->nome);
						$ic = substr($ianex[0],-1);
					endif;
					
			        for($i=1;$i<=$params['intarchive'];$i++):
			        	$ic++;
			        	
			         	 $arquivo = isset($_FILES['arquivo'.$i]) ? $_FILES['arquivo'.$i] : FALSE;
						 $nome = $id."_".$ic.substr($_FILES['arquivo'.$i]['name'], strrpos($_FILES['arquivo'.$i]['name'], "."), strlen($_FILES['arquivo'.$i]['name']));
						 
				         $pasta = Zend_Registry::get('pastaPadrao')."public/sistema/upload/financeirochina/receber/";
						 				 
						 if (!(is_dir($pasta))){
							if(!(mkdir($pasta, 0777))){
			                   	echo ("Alerta: pasta de upload nao existe, e nao pode ser criada");
			                	return $this;                           
			                 }
			             }
			                   
			             if(!(is_writable($pasta))){
			             	echo ("Alerta: pasta sem permissao de escrita");
			                return $this;                   
			             }
						 	
						 if(is_uploaded_file($_FILES['arquivo'.$i]["tmp_name"])){                                
			                  if (move_uploaded_file($arquivo["tmp_name"], $pasta . $nome)) {
			                  		//print "Upload executado com sucesso!!!<br />";
			                    	//Zend_Debug::dump($arquivo1);
			                    	$arrayarq['nome'] 				 	= $nome;
			                    	$arrayarq['id_fin_contasareceber']	= $id;
			                    	$boa->insert($arrayarq);
			                  } else {
			                        echo ("Alerta: Nao foi possivel fazer o upload para $pasta");
			                        return $this;                                           
			                  }                               
			             }else{
				            // echo "erro ao carregar imagem";
			             }
				    
			         endfor;
			    }
	         }catch (Zend_Exception $e){
	         	$arrayRe['erro'] 	= 1;
	         	$arrayRe['texto'] 	= "Erro ao anexar os arquivos na conta!";
	         }
		         
	         return $arrayRes;
	         
		}
		
		function listarInvoicesrec($id){
		    $bo		= new FinanceirochinaModel();
		    $boi	= new FinanceirochinainvoiceModel();
		    
		    return $boi->fetchAll('md5(id_fin_contasareceber) = "'.$id.'"','id_kang_cominvoice asc');
		}
		
		function listarFininvoice($id){
			$bo		= new FinanceirochinaModel();
			$boi	= new FinanceirochinainvoiceModel();
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
			$select = $db->select();
				
			$select->from(array('p'=>'tb_fin_contasareceber','*'), 
			        array('p.id as idconta','DATE_FORMAT(p.emissao,"%d/%m/%Y") as dtpag','DATE_FORMAT(p.vencimento,"%d/%m/%Y") as dtven','DATE_FORMAT(p.dt_pagamento,"%d/%m/%Y") as dtpagamento','f.valor as vlparc'))
			->join(array('f'=>'tb_fininvoice'), 'f.id_fin_contasareceber = p.id')
			->where("p.exclusao = 0 and md5(f.id_kang_cominvoice) = '".$id."'")
			->order('p.id');
			 
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		function listarAnexosareceber($id){
			$bof	= new FinanceirochinaModel();
			$boa	= new FinanceirochinaanexorecModel();
			
			return $boa->fetchAll('md5(id_fin_contasareceber) = "'.$id.'"');
		}
				
		function buscarContarec($id){
			$bof	= new FinanceirochinaModel();
			$bop	= new FinanceirochinareceberModel();
			
			return $bop->fetchAll("md5(id) = '".$id."'");
		}
		
		function liberarContasrec($id){
			$bof	= new FinanceirochinaModel();
			$bop	= new FinanceirochinareceberModel();
			
			$array['baixa']		= 0;			
			$bop->update($array, "md5(id) = '".$id."'");			
		}
		
		function excluirContasrec($id){
			$bof	= new FinanceirochinaModel();
			$bop	= new FinanceirochinareceberModel();
				
			$array['exclusao']	= 1;
			$bop->update($array, "md5(id) = '".$id."'");
		}
		
		function excluirContaspag($id){
			$bof	= new FinanceirochinaModel();
			$bop	= new FinanceirochinapagarModel();
		
			$array['exclusao']	= 1;
			$bop->update($array, "md5(id) = '".$id."'");
		}
	
	}