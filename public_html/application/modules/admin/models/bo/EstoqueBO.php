<?php
	class EstoqueBO{		
		//--Lista ajuste estoque---------------------------
		public function listaAjuste($val){
			
			$dataini = substr($val['dataini'],6,4).'-'.substr($val['dataini'],3,2).'-'.substr($val['dataini'],0,2);;
			$datafim = substr($val['datafim'],6,4).'-'.substr($val['datafim'],3,2).'-'.substr($val['datafim'],0,2);;;
			
			if(!empty($val['buscaid'])):
				$where = " and t.id = ".substr($val['buscaid'],1);
			elseif((!empty($val['dataini'])) and (!empty($val['datafim']))):
				$where = " and t.data between '".$dataini."' and '".$datafim."'";
			elseif((!empty($val['dataini'])) and (empty($val['datafim']))):
				$where = " and t.data >= '".$dataini."'";
			elseif((empty($val['dataini'])) and (!empty($val['datafim']))):
				$where = " and t.data <= '".$datafim."'";
			endif;
						
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_ajustestoqueztl','*'), array('t.id as idajuste','DATE_FORMAT(t.data,"%d/%m/%Y") as data','t.sit as sitajuste'))
			        ->joinLeft(array('u'=>'tb_usuarios'),'t.id_user = u.id')
			        ->where("t.sit != 4 ".$where)
			        ->order('t.id desc','');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
						
		//--Lista produtos----------------------------------
		public function listaProdutosajuste($var){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_ajustestoqueztl_prod','*'), array('id','qt','p.CODIGO','p.DESCRICAO','id_prod','preco'))
			        ->join(array('p'=>'produtos'),
			        't.id_prod = p.ID')
			        ->where("md5(t.id_ajuste) = '".$var['ajuste']."'")
			        ->order('p.codigo_mask','asc');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();				
		}
		
		
		//--Grava Ajuste-------
		public function gravaAjuste($params){
			$aj 		= new AjustestoqueModel();
			$ajprod 	= new AjustestoqueprodModel();
			$bo 		= new EstoqueModel();
			$bop		= new ProdutosModel();
			//$boc		= new ProdutoscmvModel();
			
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
        							 
			$array['data']			= date("Y-m-d H:i:s");
			$array['sit']			= 2;
			$array['obs']			= $params["obs"];
			$array['id_user']		= $usuario->id;
			$array['nivelamento']	= $params["nivelamento"];			
			
			$idcli = $aj->insert($array);
			
			Zend_Debug::dump($params);
			
			foreach(ProdutosBO::listaallProdutos() as $listprod):
				if(!empty($params[$listprod->ID])):
					$arrayprod['id_ajuste']			= $idcli;
					$arrayprod['id_prod']			= $listprod->ID;
					$arrayprod['qt']				= $params[$listprod->ID];					
					$ajprod->insert($arrayprod);
					
				endif;
			endforeach;
			
			$usuario = Zend_Auth::getInstance()->getIdentity();
			LogBO::cadastraLog("Estoque/Ajuste",2,$usuario->id,$idcli,"AJUSTE A".substr("000000".$idcli,-6,6));
			
			$arrayat['descricao']       	= "Ajuste A".substr("000000".$idcli,-6,6)." pendente de autorização.";
			$arrayat['dt_previsao']     	= date("Y-m-d");
			$arrayat['privativo']    		= 1;
			$arrayat["user_1017"]			= 1;
						
			AtividadesBO::cadastraAtividades($arrayat);
		}
		
		//--Autoriza Ajuste-------
		public function autorizaAjuste($params){
			
		    try{
			    $aj 		= new AjustestoqueModel();
				$ajprod 	= new AjustestoqueprodModel();
				$bo 		= new EstoqueModel();
				$bop		= new ProdutosModel();			
				
				$usuario 	= Zend_Auth::getInstance()->getIdentity();
				$array['id_usuarioautoriza']	= $usuario->id;
				$array['obsautoriza']			= $params["obs"];
				$array['sit']					= 1;
							
				$idcli = $aj->update($array,"md5(id) = '".$params['ajuste']."'");
				
				
				foreach($ajprod->fetchAll("md5(id_ajuste) = '".$params['ajuste']."'") as $listprod):
					$qtatual	= 0;
					foreach ($bo->fetchAll('id_prod = '.$listprod->id_prod,"id desc",1) as $qt_atual);
					if(count($qt_atual)>0):
						$qtatual	= $qt_atual->qt_atual;
					else:
						$qtatual	= 0;
					endif;
					
					$arrayestq = array();
					$arrayestq['id_prod'] 			= $listprod->id_prod;
					$arrayestq['qt_atual'] 			= $qtatual+$listprod->qt;
					$arrayestq['qt_atualizacao'] 	= $listprod->qt;
					$arrayestq['id_atualizacao'] 	= $listprod->id_ajuste;
					$arrayestq['dt_atualizacao'] 	= date("Y-m-d H:i:s");
					$arrayestq['tipo'] 				= "AJUSTE";
					$arrayestq['id_user'] 			= $usuario->id;
					$bo->insert($arrayestq);
					
					if($params["sicron"]==1):
						$arrayprod['dateverestoque']	= date("Y-m-d");
						$bop->update($arrayprod, "ID = ".$listprod->id_prod);
					endif;
				
				endforeach;
				
				return 1;
		    }catch (Zend_Exception $e){
		        $array = array(
	        		'reporte' => $e->getMessage(),
	        		'pagina'  => 'EstoqueBO::autorizaAjuste()'
		        );
		        
		        DiversosBO::gravarReporte($array);
		        return 0;		        
		    }
			
			$usuario = Zend_Auth::getInstance()->getIdentity();
			LogBO::cadastraLog("Estoque/Ajuste",2,$usuario->id,$idcli,"AJUSTE A".substr("000000".$idcli,-6,6));	
		}
		
		
		function gravaAjusteent($params){
			$aj 		= new AjustestoqueModel();
			$ajprod 	= new AjustestoqueprodModel();
			$bo 		= new EstoqueModel();
			$bop		= new ProdutosModel();
			$boc		= new ProdutoscmvModel();
			$boe		= new EntradaestoqueModel();
			$bocmv		= new EntradaestoquecmvModel();
			
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
        							 
			$array['data']			= date("Y-m-d H:i:s");
			$array['sit']			= true;
			$array['obs']			= $params["obs"];
			$array['id_user']		= $usuario->id;
			$array['id_entradaztl']	= $params['identrada'];
						
			$idcli = $aj->insert($array);
			
			foreach(ComprasBO::listaProdutosentgroup($params) as $listprod):
				if(!empty($params["qt_".$listprod->ID_PRODUTO])):
					$arrayprod['id_ajuste']			= $idcli;
					$arrayprod['id_prod']			= $listprod->ID_PRODUTO;
					$arrayprod['qt']				= $params["qt_".$listprod->ID_PRODUTO];
					$ajprod->insert($arrayprod);
					
					foreach ($bo->fetchAll('id_prod = '.$listprod->ID_PRODUTO,"id desc",1) as $qt_atual);
					
					$arrayestq = array();
					$arrayestq['id_prod'] 			= $listprod->ID;
					$arrayestq['qt_atual'] 			= $qt_atual->qt_atual+($params["qt_".$listprod->ID_PRODUTO]);
					$arrayestq['qt_atualizacao'] 	= $params["qt_".$listprod->ID_PRODUTO];
					$arrayestq['id_atualizacao'] 	= $idcli;
					$arrayestq['dt_atualizacao'] 	= date("Y-m-d H:i:s");
					$arrayestq['tipo'] 				= "AJUSTE";
					$arrayestq['id_user'] 			= $usuario->id;
					
					$bo->insert($arrayestq);
					
					//-- Verifico cmv do produto -------------------------------------
					/*foreach ($boe->fetchAll("id = ".$params['identrada']) as $entradaid);
					if(($entradaid->status == 3)||($entradaid->status == 4)):*/
					foreach ($bocmv->fetchAll("data is not NULL and id_entradaztl = ".$params['identrada']." and id_produtos = ".$listprod->ID_PRODUTO) as $lisentrada);
					if(!empty($lisentrada)):
						$valorentrada = $lisentrada->valor;
					
						foreach ($boc->fetchAll("id = (select max(v.id) from tb_produtoscmv v where v.id_produtos = ".$listprod->ID_PRODUTO.")") as $listacmv);
						$vlcmv = 0;
						if($listacmv->valorant!=0):
							$qtant 		= $listacmv->qtant;
							$vlant 		= $listacmv->valorant;
							$qttotal	= $listprod->qta+($params["qt_".$listprod->ID_PRODUTO]);
							
							$arraycmv['valor'] 			= (($qttotal*$valorentrada)+($qtant*$vlant))/($qttotal+$qtant);
							$arraycmv['valorant'] 		= $listacmv->valorant;
							$arraycmv['qtant'] 			= $listacmv->qtant;
							$arraycmv['data'] 			= date("Y-m-d H:i:s");
							$arraycmv['id_produtos'] 	= $listprod->ID_PRODUTO;
							$boc->insert($arraycmv);						
						endif;
					endif;
				endif;
				//endif;
			endforeach;
			
			$usuario = Zend_Auth::getInstance()->getIdentity();
			LogBO::cadastraLog("Estoque/Ajuste",2,$usuario->id,$idcli,"AJUSTE A".substr("000000".$idcli,-6,6));			
						
		}
		
		//--Exclui ajuste--------------------------------
		public function finalizaAjuste($params){
			try{
			    $aj 	= new AjustestoqueModel();
				$ajp	= new AjustestoqueprodModel();
				$bo		= new EstoqueModel();
				
				$usuario = Zend_Auth::getInstance()->getIdentity();
				$array['id_usuariocancela']		= $usuario->id;
				$array['obscancela']			= $params["obscancela"];
				$array['sit']					= 0;
					
				$aj->update($array,"id = '".$params['ajuste']."'");
				
				foreach ($ajp->fetchAll("id_ajuste = ".$params['ajuste']) as $listprod):
					foreach ($bo->fetchAll('id_prod = '.$listprod->id_prod,"id desc",1) as $qt_atual);
				
					$arrayestq['id_prod'] 			= $listprod->id_prod;
					$arrayestq['qt_atual'] 			= $qt_atual->qt_atual-($listprod->qt);
					$arrayestq['qt_atualizacao'] 	= -($listprod->qt);
					$arrayestq['id_atualizacao'] 	= $params['ajuste'];
					$arrayestq['dt_atualizacao'] 	= date("Y-m-d H:i:s");
					$arrayestq['tipo'] 				= "AJUSTE CANCELADO";
					$arrayestq['id_user'] 			= $usuario->id;
					
					$bo->insert($arrayestq);
				endforeach;
								
				return $params['ajuste'];
			}catch (Zend_Exception $e){
				DiversosBO::gravarReporte(array('reporte' => $e->getMessage(), 'pagina'  => 'EstoqueBO::finalizaAjuste()'));
				return 0;
			}
			
		}
		
		function buscaAjuste($params){
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			$select->from(array('a'=>'tb_ajustestoqueztl','*'), array('a.*','a.id as idajuste','c.nome as solicitante','cl.nome as autorizador','cc.nome as cancelador','a.sit as sitajuste'))
			        ->join(array('c'=>'tb_usuarios'),'c.id = a.id_user')
			        ->joinLeft(array('cl'=>'tb_usuarios'),'cl.id = a.id_usuarioautoriza')
			        ->joinLeft(array('cc'=>'tb_usuarios'),'cc.id = a.id_usuariocancela')
					->where('md5(a.id) = "'.$params['ajuste'].'"');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();
			
		}		
		
		function listaEstoquezerado(){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			$select->from(array('p'=>'produtos','*'),
			        array('p.CODIGO','p.DESCRICAO', 'e.qt_atual','e.id','e.id_prod'))
			        ->join(array('e'=>'tb_estoqueztl'),'p.id = e.id_prod and e.id = (SELECT max(id) from tb_estoqueztl e where p.id = e.id_prod)')
					->where('e.qt_atual < 10 ')
			        ->order('p.codigo_mask','desc');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();				
		}
		
		public function listaEstoquemedia(){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			$select->from(array('p'=>'produtos','*'),
			        array('p.CODIGO','p.DESCRICAO', 'e.qt_atual','e.id','e.id_prod','(select (sum(pd.qt)/6) as quant from tb_pedidos p, tb_pedidos_prod pd where TO_DAYS(NOW()) - TO_DAYS(p.data_vend) <= 180 and p.id = pd.id_ped and p.sit = 0 and pd.id_prod = e.id_prod group by id_prod)  as qtbusca'))
			        ->join(array('e'=>'tb_estoqueztl'),'p.id = e.id_prod and e.id = (SELECT max(id) from tb_estoqueztl e where p.id = e.id_prod) ')
					->where('e.qt_atual < (select (sum(pd.qt)/6) as quant from tb_pedidos p, tb_pedidos_prod pd where TO_DAYS(NOW()) - TO_DAYS(p.data_vend) <= 180 and p.id = pd.id_ped and p.sit = 0 and pd.id_prod = e.id_prod group by id_prod)')
			        ->order('p.codigo_mask','desc');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();				
		}
		
		function buscaEstoque($idprod){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			$select->from(array('p'=>'produtos','*'), array('p.CODIGO','p.DESCRICAO', 'e.qt_atual','e.id','e.id_prod'))
			        ->join(array('e'=>'tb_estoqueztl'),'p.id = e.id_prod and e.id = (SELECT max(id) from tb_estoqueztl e where p.id = e.id_prod)')
					->where('p.ID = '.$idprod)
			        ->order('p.codigo_mask','desc');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();				
		} 
		
		/* function corrigeentradaEstoque(){
			$bo		= new EstoqueModel();
			$bop	= new ProdutosModel();
			foreach ($bop->fetchAll() as $produtos):
				$estoque = "";
				foreach ($bo->fetchAll("id_prod = ".$produtos->ID,"id asc",1) as $estoque);
				if(!empty($estoque)):
					if($estoque->tipo=="COMPRA"):
						if($estoque->qt_atual>$estoque->qt_atualizacao):
							echo $estoque->id_prod." - Cod: ";
							echo $produtos->CODIGO." - Qtatual: ";
							echo $estoque->qt_atual." - Qt Atualizacao: ";
							echo $estoque->qt_atualizacao."<br />";

							$qtatualiza = 0;
							echo $qtatualiza = $estoque->qt_atual-$estoque->qt_atualizacao;
							
							foreach ($bo->fetchAll("id_prod = ".$produtos->ID,"id asc") as $estoque):
								$data['qt_atual']	= 	$estoque->qt_atual - $qtatualiza;
								$bo->update($data, "id = ".$estoque->id);
							endforeach;
						endif;
					endif;				
				endif;
			endforeach;
		} */
		
		
		//----- relatorio de vendas ----------------------------
		function buscaCurvaprodutos($val=""){
			
			$wherep = $where = "";
			
		    if((!empty($val['dataini'])) || (!empty($val['datafim']))){
			    $dataini = substr($val['dataini'],6,4).'-'.substr($val['dataini'],3,2).'-'.substr($val['dataini'],0,2);;
			    $datafim = substr($val['datafim'],6,4).'-'.substr($val['datafim'],3,2).'-'.substr($val['datafim'],0,2);;;
			    	
			    if((!empty($val['dataini'])) and (!empty($val['datafim']))):
			    	$where 		= " and p.data_vend between '".$dataini."' and '".$datafim." 23:59:59'";
			    elseif((!empty($val['dataini'])) and (empty($val['datafim']))):
			    	$where 		= " and p.data_vend >= '".$dataini."'";
			    elseif((empty($val['dataini'])) and (!empty($val['datafim']))):
			    	$where 		= " and p.data_vend <= '".$datafim."'";
			    endif;
		    }
		    
		    if($val['tpdata'] == 1){
		        if(!empty($val['representante']) and ($val['representante'] != 0)){
		            $where 		.= " and c.ID_REGIOES = '".$val['representante']."'";
		        }
		    }else{
		        if(!empty($val['televenda']) and ($val['televenda'] != 0)){
		        	$where 		.= " and c.id_regioestelevendas = '".$val['televenda']."'";
		        }
		    }
		    
		    if(!empty($val['buscagruposub']) and ($val['buscagruposub'] != 0)){
		    	$where 		.= " and s.id = '".$val['buscagruposub']."'";
		    	$wherep 	.= " and s.id = '".$val['buscagruposub']."'";
		    }elseif(!empty($val['grupovenda']) and ($val['grupovenda'] != 0)){
		        $where 		.= " and g.id = '".$val['grupovenda']."'";
		        $wherep 	.= " and g.id = '".$val['buscagruposub']."'";
		    }
		    
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();				
			
			$select->from(array('p'=>'tb_pedidos','*'), array('pd.id_prod','sum(pd.qt*pd.preco_unit) as precototal','sum(pd.qt) as qtvendido','pr.CODIGO'))
				->join(array('pd'=>'tb_pedidos_prod'), 'pd.id_ped = p.id')
				->join(array('pr'=>'produtos'), 'pr.ID = pd.id_prod')
				->join(array('c'=>'clientes'), 'c.ID = p.id_parceiro')
				->join(array('s'=>'tb_gruposprodsub'), 's.id = pr.id_gruposprodsub')
				->join(array('g'=>'tb_gruposprod'), 'g.id = s.id_gruposprod')
				
				->where('p.status = "ped" and p.sit = 0'.$where)
				->order('precototal asc')
				->group('pd.id_prod');
			
			$stmt = $db->query($select);
						
			$vltotal = $qtprod1 = $vlprod1 = $qtp1 = $qtprod2 = $vlprod2 = $qtp2 = $qtprod3 = $vlprod3 = $qtp3 = 0;
			$arrayprod	= array();
			
			$objcurva = $stmt->fetchAll();
			
			if(count($objcurva)>0){
				
				foreach ($objcurva as $curva){
			
				    
				    if($val['pend'] == on){
					    $vlpend = $qtpend = 0;
						if(count(EstoqueBO::buscaCurvaprodutospend($curva->id_prod, $val))>0){
							foreach (EstoqueBO::buscaCurvaprodutospend($curva->id_prod, $val) as $pend);
							$vlpend = $pend->vlpendencias;
							$qtpend = $pend->pendencias;	
											
						}
				    }
			
					$arrayprod[$curva->id_prod]['qtvendido']	= $curva->qtvendido+$qtpend;
					$arrayprod[$curva->id_prod]['precototal']	= $curva->precototal+$vlpend;
					$arrayprod[$curva->id_prod]['codigo']		= $curva->CODIGO;
					$arrayprod[$curva->id_prod]['qtpend']		= $qtpend;
					$arrayprod[$curva->id_prod]['vlpend']		= $vlpend;
					$arrayprod[$curva->id_prod]['qtvend']		= $curva->qtvendido;
					$arrayprod[$curva->id_prod]['vlvend']		= $curva->precototal;
					$arrayprod[$curva->id_prod]['id_prod']		= $curva->id_prod;
					
					$vltotal += $curva->precototal+$vlpend;
			
					//------ calcula giro de estoque por produto ------------------------------------------------
			
					$objprodutos = EstoqueBO::buscaGiroestoque($curva->id_prod, $val);
					$cont1 		= 0;
					$vlprim		= 0;
					foreach (EstoqueBO::buscaGiroestoque($curva->id_prod, $val) as $produtos){
						if($cont1==0){
							$vlprim = $produtos->qt_atual - ($produtos->qt_atualizacao);
							$cont1++;
						}
					}
					
			
					if((!empty($val['dataini'])) and (!empty($val['datafim']))):
						$data 	 = substr($val['dataini'],6,4).'-'.substr($val['dataini'],3,2).'-'.substr($val['dataini'],0,2);
				
						if(count(EstoqueBO::buscaPrimeiraentradastoque($curva->id_prod, $val))>0){
							foreach (EstoqueBO::buscaPrimeiraentradastoque($curva->id_prod, $val) as $dataent);
							$datae 	= $dataent->data;
							$dataar 	= explode('-', $datae);
							$datae 	= $dataar[0].'-'.$dataar[1].'-01';							
						}
				
						if(strtotime($data) < strtotime($datae)) $data = $datae;
				
						$datafim = substr($val['datafim'],6,4).'-'.substr($val['datafim'],3,2).'-'.substr($val['datafim'],0,2);
					elseif((!empty($val['dataini'])) and (empty($val['datafim']))):
						$data 	 = substr($val['dataini'],6,4).'-'.substr($val['dataini'],3,2).'-01';
						$datafim = date('Y-m-01');
					elseif((empty($val['dataini'])) and (!empty($val['datafim']))):
			
						$data 	 = "2010-07-01";
				
						if(count(EstoqueBO::buscaPrimeiraentradastoque($curva->id_prod, $val))>0){
							foreach (EstoqueBO::buscaPrimeiraentradastoque($curva->id_prod, $val) as $dataent);
							$datae 	= $dataent->data;
							$dataar 	= explode('-', $datae);
							$datae 	= $dataar[0].'-'.$dataar[1].'-01';							
						}
				
						$datafim = substr($val['datafim'],6,4).'-'.substr($val['datafim'],3,2).'-'.substr($val['datafim'],0,2);
					else:
						$data 	 = "2010-07-01";
				
						if(count(EstoqueBO::buscaPrimeiraentradastoque($curva->id_prod, $val))>0){
							foreach (EstoqueBO::buscaPrimeiraentradastoque($curva->id_prod, $val) as $dataent);
							$datae 	= $dataent->data;
							$dataar 	= explode('-', $datae);
							$datae 	= $dataar[0].'-'.$dataar[1].'-01';							
						}
						
						$datafim = date('Y-m-01');
					endif;
			
					//-- para qualcular qt dias do giro do estoque ----
					$intervalodias = round((strtotime($datafim)-strtotime($data))/86400);
			
					$arrayprod[$curva->id_prod]['dtgiro']		= $data;
			
					$arraygiro 	= array();
					$proxdata  	= $data;
					$cont = 0;
					while(strtotime($proxdata) <= strtotime($datafim)){
						$arraygiro[$cont]['qtatual'] = 0;
			
						foreach (EstoqueBO::buscaGiroestoque($curva->id_prod, $val) as $produtos){
							if(strtotime($proxdata) == strtotime($produtos->ano."-".$produtos->mes."-01")){
								$arraygiro[$cont]['qtatual'] = $produtos->qt_atual;
							}
						}
			
						$proxdata = date ("Y-m-d", strtotime("+1 month", strtotime($proxdata)));
						$cont++;
						
					}
			
					$totalmedia = 0;
					foreach ($arraygiro as $key => $row) {
						if($row['qtatual']==0){
							$arraygiro[$key]['qtatual'] = $vlprim;
							$totalmedia += $vlprim;
						}else{
							$vlprim = $row['qtatual'];
							$totalmedia += $row['qtatual'];
						}
					}

					//-- transforma dias em periodos ----------------------------------------------
					if($curva->qtvendido>0){
						$diasgiro   = "";
						$diasgiro 	= @($intervalodias/@($curva->qtvendido/($totalmedia/$cont)));
						$time 		= $diasgiro*24*3600;
						 
						$response	= array();
						$years 		= floor($time/(86400*365));
						$time		= $time%(86400*365);
						$months 	= floor($time/(86400*30));
						$time		= $time%(86400*30);
						$days 		= floor($time/86400);
						$time		= $time%86400;
						$hours 		= floor($time/(3600));
						$time		= $time%3600;
						$minutes 	= floor($time/60);
						$seconds	= $time%60;
						 
						if($years>0) $response[]=$years.' ano'. ($years>1?'s':' ');
						if($months>0) $response[]=$months.' mes'.($months>1?'es':' ');
						if($days>0) $response[]=$days.' dia' .($days>1?'s':' ');
						$diasgiro =  implode(', ',$response);
					}
			
					$arrayprod[$curva->id_prod]['mediaestoque']		= @($totalmedia/$cont);
					$arrayprod[$curva->id_prod]['giro']				= @($curva->qtvendido/($totalmedia/$cont));
					$arrayprod[$curva->id_prod]['diasgiro']			= $diasgiro;
					
					
					$idprods .= $curva->id_prod.","; 
			
				}
				
			}
			
			
			if(!empty($idprods)){
				$wherep .= " and pr.ID not in (".substr($idprods, 0,-1).")";
			}
			
			$select = $db->select();
				
			$select->from(array('pr'=>'produtos','*'), array('pr.ID as id_prod','*'))
				->join(array('s'=>'tb_gruposprodsub'), 's.id = pr.id_gruposprodsub')
				->join(array('g'=>'tb_gruposprod'), 'g.id = s.id_gruposprod')
			
			->where('pr.situacao = 0'.$wherep)
			->order('pr.CODIGO asc');
				
			$stmt = $db->query($select);
			$objprod = $stmt->fetchAll();
				
			if(count($objprod)>0){
				foreach ($objprod as $prod){
					$arrayprod[$prod->id_prod]['qtvendido']	= 0;
					$arrayprod[$prod->id_prod]['precototal']	= 0;
					$arrayprod[$prod->id_prod]['codigo']		= $prod->CODIGO;
					$arrayprod[$prod->id_prod]['qtpend']		= 0;
					$arrayprod[$prod->id_prod]['vlpend']		= 0;
					$arrayprod[$prod->id_prod]['qtvend']		= 0;
					$arrayprod[$prod->id_prod]['vlvend']		= 0;
					$arrayprod[$prod->id_prod]['id_prod']		= $prod->id_prod;
					$arrayprod[$prod->id_prod]['mediaestoque']	= 0;
					$arrayprod[$prod->id_prod]['giro']			= 0;
					$arrayprod[$prod->id_prod]['diasgiro']		= 0;
				}
			}
			
			$resultado = array();
			$resultado['vltotal']			= $vltotal;
			$resultado['produtos']			= $arrayprod;
			//---------------------------------------------------------------------------
			
			return $resultado;
			
			
		}
		
		//----- relatorio de vendas ----------------------------
		function buscaCurvaprodutospend($idprod, $val=""){
		
			if((!empty($val['dataini'])) || (!empty($val['datafim']))){
				$dataini = substr($val['dataini'],6,4).'-'.substr($val['dataini'],3,2).'-'.substr($val['dataini'],0,2);;
				$datafim = substr($val['datafim'],6,4).'-'.substr($val['datafim'],3,2).'-'.substr($val['datafim'],0,2);;;
		
				if((!empty($val['dataini'])) and (!empty($val['datafim']))):
					$wheresub 	= " and pend.dt_pend between '".$dataini."' and '".$datafim." 23:59:59'";
				elseif((!empty($val['dataini'])) and (empty($val['datafim']))):
					$wheresub 	= " and pend.dt_pend >= '".$dataini."'";
				elseif((empty($val['dataini'])) and (!empty($val['datafim']))):
					$wheresub 	= " and pend.dt_pend <= '".$datafim."'";
				endif;
			}
		
			if($val['tpdata'] == 1){
				if(!empty($val['representante']) and ($val['representante'] != 0)){
					$wheresub 	.= " and cl.ID_REGIOES = '".$val['representante']."'";
				}
			}else{
				if(!empty($val['televenda']) and ($val['televenda'] != 0)){
					$wheresub 	.= " and cl.id_regioestelevendas = '".$val['televenda']."'";
				}
			}
		
			$wheresub .= " and pend.id_prod = '".$idprod."'";
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
		
			$select->from(array('pend'=>'tb_pedidos_pend','*'), array('pend.id_prod','sum(pend.qt*pend.valor) as vlpendencias','sum(pend.qt) as pendencias'))
				->join(array('pr'=>'produtos'), 'pr.ID = pend.id_prod')
				->join(array('cl'=>'clientes'), 'cl.ID = pend.id_cliente')
				/* ->join(array('s'=>'tb_gruposprodsub'), 's.id = pr.id_gruposprodsub')
				->join(array('g'=>'tb_gruposprod'), 'g.id = s.id_gruposprod') */
					
				->where('pend.id > 0'.$wheresub)
				/* ->group('pend.id_prod') */;
			
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		function buscaGiroestoque($idprod, $val=""){
		     
		    if((!empty($val['dataini'])) || (!empty($val['datafim']))){
		    	$dataini = substr($val['dataini'],6,4).'-'.substr($val['dataini'],3,2).'-'.substr($val['dataini'],0,2);;
		    	$datafim = substr($val['datafim'],6,4).'-'.substr($val['datafim'],3,2).'-'.substr($val['datafim'],0,2);;;
		    
		    	if((!empty($val['dataini'])) and (!empty($val['datafim']))):
			    	$where 		= " and e.dt_atualizacao between '".$dataini."' and '".$datafim." 23:59:59'";
		    	elseif((!empty($val['dataini'])) and (empty($val['datafim']))):
		    		$where 		= " and e.dt_atualizacao >= '".$dataini."'";
		    	elseif((empty($val['dataini'])) and (!empty($val['datafim']))):
		    		$where 		= " and e.dt_atualizacao <= '".$datafim."'";
		    	endif;
		    }

		    
		    $where .= " and e.id_prod = '".$idprod."'";
		    
		    /* if(!empty($val['buscagruposub']) and ($val['buscagruposub'] != 0)){
		    	$where 		.= " and s.id = '".$val['buscagruposub']."'";
		    }elseif(!empty($val['grupovenda']) and ($val['grupovenda'] != 0)){
		    	$where 		.= " and g.id = '".$val['grupovenda']."'";
		    } */
		    
		    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		    $db->setFetchMode(Zend_Db::FETCH_OBJ);
		    $select = $db->select();
		    
		    	
		    $select->from(array('e'=>'tb_estoqueztl','*'), array('e.qt_atual','e.qt_atualizacao','e.id_prod', 'min(e.dt_atualizacao) as data', 'EXTRACT(MONTH FROM e.dt_atualizacao) as mes', 'EXTRACT(YEAR FROM e.dt_atualizacao) as ano'))
			    ->join(array('pr'=>'produtos'), 'pr.ID = e.id_prod')
			    /* ->join(array('s'=>'tb_gruposprodsub'), 's.id = pr.id_gruposprodsub')
			    ->join(array('g'=>'tb_gruposprod'), 'g.id = s.id_gruposprod') */
			    			    
			    ->where('e.id > 0 '.$where)
			    /* ->group('e.id_prod') */
		    	->group('mes')
		    	->group('ano')
		    	->order('e.dt_atualizacao');
		    	
		    $stmt = $db->query($select);
		    
			return $stmt->fetchAll();
		    
		}
		
		function buscaPrimeiraentradastoque($idprod, $val=""){
			 
			/* if(!empty($val['buscagruposub']) and ($val['buscagruposub'] != 0)){
				$where 		= " and s.id = '".$val['buscagruposub']."'";
			}elseif(!empty($val['grupovenda']) and ($val['grupovenda'] != 0)){
				$where 		= " and g.id = '".$val['grupovenda']."'";
			} */
		
		    $where = " and e.id_prod = '".$idprod."'";
		    
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
		
			 
			$select->from(array('e'=>'tb_estoqueztl','*'), array('e.id_prod', 'min(e.dt_atualizacao) as data'))
				->join(array('pr'=>'produtos'), 'pr.ID = e.id_prod')
				/* ->join(array('s'=>'tb_gruposprodsub'), 's.id = pr.id_gruposprodsub')
				->join(array('g'=>'tb_gruposprod'), 'g.id = s.id_gruposprod') */
		
			->where('e.id > 0 '.$where)
			/* ->group('e.id_prod')
			->order('e.id_prod') */;
			 
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		
		}
		
		function listaCmvprodutosgiro($val=""){
			$bo 	= new ProdutosBO();
			$boc 	= new ProdutoscmvModel();

			if(!empty($val['buscagruposub']) and ($val['buscagruposub'] != 0)){
				$where 		= " and s.id = '".$val['buscagruposub']."'";
			}elseif(!empty($val['grupovenda']) and ($val['grupovenda'] != 0)){
				$where 		= " and g.id = '".$val['grupovenda']."'";
			}
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			
			
			$select->from(array('e'=>'tb_produtoscmv','*'), array('e.id_prod', 'min(e.dt_atualizacao) as data'))
			->join(array('pr'=>'produtos'), 'pr.ID = e.id_prod')
			->join(array('s'=>'tb_gruposprodsub'), 's.id = pr.id_gruposprodsub')
			->join(array('g'=>'tb_gruposprod'), 'g.id = s.id_gruposprod')
			
			->where('e.id > 0 '.$where)
			->group('e.id_prod')
			->order('e.id_prod');
			
			$stmt = $db->query($select);
			return $stmt->fetchAll();
			
		}
		
		//---- Lista produtos selecionados no relatorio de curva de produto ------------------------------------------------
		
		/* function listaProdutosestoque($val=""){
			 
		    $produtos = "";
			foreach ($val as $key => $row){
			      $produtos .= $key.",";
			    
			}
			
			$produtos = str_replace('module,controller,action,', '', $produtos);
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
				
			$select->from(array('pr'=>'produtos','*'), array('z.qt_atual as estoque', 'cv.valor as valorcmv', 'pr.CODIGO', 'pr.PRECO_UNITARIO', 's.descricao as descsubgrupo', 'g.descricao as descgrupo'))
				->join(array('cv'=>'tb_produtoscmv'), 'pr.ID = cv.id_produtos and cv.id = (select max(v.id) from tb_produtoscmv v where v.id_produtos = z.id_prod)')
				->join(array('z'=>'tb_estoqueztl'), 'z.id_prod = pr.ID and z.id = (select max(id) from tb_estoqueztl ez where ez.id_prod = pr.ID)')
				->join(array('s'=>'tb_gruposprodsub'), 's.id = pr.id_gruposprodsub')
				->join(array('g'=>'tb_gruposprod'), 'g.id = s.id_gruposprod')
				
				->where('pr.ID in ('.substr($produtos,0,-1).')')
				->order('pr.codigo_mask');
				
			$stmt = $db->query($select);
			return $stmt->fetchAll();				
		} */
		
		
		//----- relatorio de rentabilidade ----------------------------
		function buscaRentabilidadeprodutos($val=""){
				
			if((!empty($val['dataini'])) || (!empty($val['datafim']))){
				$dataini = substr($val['dataini'],6,4).'-'.substr($val['dataini'],3,2).'-'.substr($val['dataini'],0,2);;
				$datafim = substr($val['datafim'],6,4).'-'.substr($val['datafim'],3,2).'-'.substr($val['datafim'],0,2);;;
		
				if((!empty($val['dataini'])) and (!empty($val['datafim']))):
					$where 		= " and p.data_vend between '".$dataini."' and '".$datafim." 23:59:59'";
					$wherepen	= " and pend.dt_pend between '".$dataini."' and '".$datafim." 23:59:59'";
					$wheremarg	= " and p.data_vend between '".$dataini."' and '".$datafim." 23:59:59'";
					
				elseif((!empty($val['dataini'])) and (empty($val['datafim']))):
					$where 		= " and p.data_vend >= '".$dataini."'";
					$wherepen	= " and pend.dt_pend >= '".$dataini."'";
					$wheremarg  = " and p.data_vend >= '".$dataini."'";
				elseif((empty($val['dataini'])) and (!empty($val['datafim']))):
					$where 		= " and p.data_vend <= '".$datafim."'";
					$wherepen	= " and pend.dt_pend <= '".$datafim."'";
					$wheremarg	= " and p.data_vend <= '".$datafim."'";
				endif;
				
				$wheretotal = $where;
			}
		
			if($val['tpbusca'] == 1){
				if(!empty($val['representante']) and ($val['representante'] != 0)){
					$where 		.= " and c.ID_REGIOES = '".$val['representante']."'";
					$wherepen   .= " and c.ID_REGIOES = '".$val['representante']."'";
					$wheremarg 	.= " and c.ID_REGIOES = '".$val['representante']."'";
				}
			}elseif($val['tpbusca'] == 2){
				if(!empty($val['televenda']) and ($val['televenda'] != 0)){
					$where 		.= " and c.id_regioestelevendas = '".$val['televenda']."'";
					$wherepen 	.= " and c.id_regioestelevendas = '".$val['televenda']."'";
					$wheremarg  .= " and c.id_regioestelevendas = '".$val['televenda']."'";
				}
			}
			
			$grupos 	= "";
			$vendas		= "";
			$vendast	= "";
			$pend		= "";
			$margem 	= "";
			$estoque 	= "";
			
			if(!empty($val['grupovenda']) and ($val['grupovenda'] != 0)){
				
				foreach (GruposprodBO::buscaSubgrupo($d = array('idgrupo' => $val['grupovenda']),$tp = 2) as $listagrupos){
				    //-- busca as vendas ------------------------------------------------------------------
				    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
				    $db->setFetchMode(Zend_Db::FETCH_OBJ);
				    $select = $db->select();
				    
				    $select->from(array('p'=>'tb_pedidos','*'), array('sum(pd.qt*pd.preco_unit) as precototal'))
						->join(array('pd'=>'tb_pedidos_prod'), 'pd.id_ped = p.id')
						->join(array('pr'=>'produtos'), 'pr.ID = pd.id_prod')
						->join(array('c'=>'clientes'), 'c.ID = p.id_parceiro')
						
					->where('p.status = "ped" and p.sit = 0 and pr.id_gruposprodsub = '.$listagrupos->id.$where)
					->group('pr.id_gruposprodsub');
						
					$stmt 	= $db->query($select);
					
					$objvendas = $stmt->fetchAll();
					$valorvenda = 0;
					if(count($objvendas)>0){
						foreach ($objvendas as $listavendas);
						$vendast 	   .= round($listavendas->precototal,2).";";
						$valorvenda 	= $listavendas->precototal;
					}else{
						$vendast .= "0;";
					}
					
					//-- busca pendencias ------------------------------------------------------------------
					$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
					$db->setFetchMode(Zend_Db::FETCH_OBJ);
					$select = $db->select();
					
					$select->from(array('pend'=>'tb_pedidos_pend','*'), array('sum(pend.qt*pend.valor) as vlpendencias'))
						->join(array('pr'=>'produtos'), 'pr.ID = pend.id_prod')
						->join(array('cl'=>'clientes'), 'cl.ID = pend.id_cliente')
						
						->where('pend.id > 0 and pr.id_gruposprodsub = '.$listagrupos->id.$wherepen)
						->group('pr.id_gruposprodsub');
					
					$stmt 	= $db->query($select);
						
					$objpend = $stmt->fetchAll();
					if(count($objpend)>0){
						foreach ($objpend as $listapend);
						$pend .= $listapend->vlpendencias.";";
					}else{
						$pend .= "0;";
					}
					
					//-- busca as margem de lucro ------------------------------------------------------------------
					$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
					$db->setFetchMode(Zend_Db::FETCH_OBJ);
					$select = $db->select();
						
					$select->from(array('p'=>'tb_pedidos','*'), array('sum(pd.qt*cv.valor) as precototal'))
					->join(array('pd'=>'tb_pedidos_prod'), 'pd.id_ped = p.id')
					->join(array('cv'=>'tb_produtoscmv'), 'pd.id_prod = cv.id_produtos and cv.id = (select max(v.id) from tb_produtoscmv v where v.id_produtos = pd.id_prod)')
					->join(array('pr'=>'produtos'), 'pr.ID = pd.id_prod')
					->join(array('c'=>'clientes'), 'c.ID = p.id_parceiro')
					
						
					->where('p.status = "ped" and p.sit = 0 and pr.id_gruposprodsub = '.$listagrupos->id.$wheremarg)
					->group('pr.id_gruposprodsub');
						
					$stmt 	= $db->query($select);
					
					$objmargem = $stmt->fetchAll();
					if(count($objmargem)>0){
						foreach ($objmargem as $listamargem);
						$margem .= round($valorvenda-$listamargem->precototal,2).";";
						$vendas .= round($listamargem->precototal,2).";";
					}else{
						$margem .= "0;";
						$vendas .= "0;";
					}
					
					//-- busca as estoque por grupo ------------------------------------------------------------------
					$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
					$db->setFetchMode(Zend_Db::FETCH_OBJ);
					$select = $db->select();
						
					$select->from(array('pr'=>'produtos','*'), array('sum(z.qt_atual*cv.valor) as valorestoque'))
					->join(array('cv'=>'tb_produtoscmv'), 'pr.ID = cv.id_produtos and cv.id = (select max(v.id) from tb_produtoscmv v where v.id_produtos = z.id_prod)')
					->join(array('z'=>'tb_estoqueztl'), 'z.id_prod = pr.ID and z.id = (select max(id) from tb_estoqueztl ez where ez.id_prod = pr.ID)')
					->where('pr.id_gruposprodsub = '.$listagrupos->id);
						
					$stmt 	= $db->query($select);
					
					$objestoque = $stmt->fetchAll();
					if(count($objmargem)>0){
						foreach ($objestoque as $listaestoque);
						$estoque 	.= round($listaestoque->valorestoque,2).";";
					}else{
						$estoque .= "0;";
					}
					
					//-- agrupa os nomes dos grupos --------------------------------------------------------
					$grupos .= $listagrupos->descricao.";";
			    }
			}else{
				foreach (GruposprodBO::listaGruposvenda() as $listagrupos){
				    //-- busca as vendas ------------------------------------------------------------------
				    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
				    $db->setFetchMode(Zend_Db::FETCH_OBJ);
				    $select = $db->select();
				    
				    $select->from(array('p'=>'tb_pedidos','*'), array('sum(pd.qt*pd.preco_unit) as precototal'))
						->join(array('pd'=>'tb_pedidos_prod'), 'pd.id_ped = p.id')
						->join(array('pr'=>'produtos'), 'pr.ID = pd.id_prod')
						->join(array('c'=>'clientes'), 'c.ID = p.id_parceiro')
						->join(array('s'=>'tb_gruposprodsub'), 's.id = pr.id_gruposprodsub and s.tipo = 1')						
				
					->where('p.status = "ped" and p.sit = 0 and s.id_gruposprod = '.$listagrupos->id.$where)
					->group('s.id_gruposprod');
						
					$stmt 	= $db->query($select);
					
					$objvendas = $stmt->fetchAll();
					$valorvenda = 0;
					if(count($objvendas)>0){
						foreach ($objvendas as $listavendas);
						$vendast 	   .= round($listavendas->precototal,2).";";
						$valorvenda 	= $listavendas->precototal;
					}else{
					    $vendast .= "0;";
					}
										
					//-- busca pendencias ------------------------------------------------------------------
					$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
					$db->setFetchMode(Zend_Db::FETCH_OBJ);
					$select = $db->select();
						
					$select->from(array('pend'=>'tb_pedidos_pend','*'), array('sum(pend.qt*pend.valor) as vlpendencias'))
						->join(array('pr'=>'produtos'), 'pr.ID = pend.id_prod')
						->join(array('c'=>'clientes'), 'c.ID = pend.id_cliente')
						->join(array('s'=>'tb_gruposprodsub'), 's.id = pr.id_gruposprodsub and s.tipo = 1')
					
					->where('pend.id > 0 and s.id_gruposprod = '.$listagrupos->id.$wherepen);
						
					$stmt 	= $db->query($select);
					
					$objpend = $stmt->fetchAll();
					if(count($objpend)>0){
						foreach ($objpend as $listapend);
						$pend .= round($listapend->vlpendencias,2).";";
					}else{
						$pend .= "0;";
					}
					
					//-- busca as margem de lucro ------------------------------------------------------------------
					$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
					$db->setFetchMode(Zend_Db::FETCH_OBJ);
					$select = $db->select();
					
					$select->from(array('p'=>'tb_pedidos','*'), array('sum(pd.qt*cv.valor) as precototal'))
						->join(array('pd'=>'tb_pedidos_prod'), 'pd.id_ped = p.id')
						->join(array('cv'=>'tb_produtoscmv'), 'pd.id_prod = cv.id_produtos and cv.id = (select max(v.id) from tb_produtoscmv v where v.id_produtos = pd.id_prod)')
						->join(array('pr'=>'produtos'), 'pr.ID = pd.id_prod')
						->join(array('c'=>'clientes'), 'c.ID = p.id_parceiro')
						->join(array('s'=>'tb_gruposprodsub'), 's.id = pr.id_gruposprodsub and s.tipo = 1')
					
					->where('p.status = "ped" and p.sit = 0 and s.id_gruposprod = '.$listagrupos->id.$wheremarg)
					->group('s.id_gruposprod');
					
					$stmt 	= $db->query($select);
						
					$objmargem = $stmt->fetchAll();
					if(count($objmargem)>0){
						foreach ($objmargem as $listamargem);
						$margem 	.= round($valorvenda-$listamargem->precototal,2).";";
						$vendas 	.= round($listamargem->precototal,2).";";
					}else{
						$margem .= "0;";
						$vendas .= "0;";
					}
					
					//-- busca as estoque por grupo ------------------------------------------------------------------
					$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
					$db->setFetchMode(Zend_Db::FETCH_OBJ);
					$select = $db->select();
					
					$select->from(array('pr'=>'produtos','*'), array('sum(z.qt_atual*cv.valor) as valorestoque'))
						->join(array('cv'=>'tb_produtoscmv'), 'pr.ID = cv.id_produtos and cv.id = (select max(v.id) from tb_produtoscmv v where v.id_produtos = z.id_prod)')
						->join(array('z'=>'tb_estoqueztl'), 'z.id_prod = pr.ID and z.id = (select max(id) from tb_estoqueztl ez where ez.id_prod = pr.ID)')
						->join(array('s'=>'tb_gruposprodsub'), 's.id = pr.id_gruposprodsub and s.tipo = 1')
						->where('s.id_gruposprod = '.$listagrupos->id);
					
					$stmt 	= $db->query($select);
						
					$objestoque = $stmt->fetchAll();
					if(count($objmargem)>0){
						foreach ($objestoque as $listaestoque);
						$estoque 	.= round($listaestoque->valorestoque,2).";";
					}else{
						$estoque .= "0;";
					}
					
					$grupos .= $listagrupos->descricao.";";
			    }
			} 
			
			
			
			//-- busca as total do estoque------------------------------------------------------------------
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			
			$select->from(array('pr'=>'produtos','*'), array('sum(z.qt_atual*cv.valor) as valorestoque'))
			->join(array('cv'=>'tb_produtoscmv'), 'pr.ID = cv.id_produtos and cv.id = (select max(v.id) from tb_produtoscmv v where v.id_produtos = z.id_prod)')
			->join(array('z'=>'tb_estoqueztl'), 'z.id_prod = pr.ID and z.id = (select max(id) from tb_estoqueztl ez where ez.id_prod = pr.ID)');
			
			$stmt 	= $db->query($select);
				
			foreach ($stmt->fetchAll() as $listaestoque);
			$totalestoque =  $listaestoque->valorestoque;
			
			//-- busca as total das vendas ------------------------------------------------------------------
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			
			$select->from(array('p'=>'tb_pedidos','*'), array('sum(pd.qt*pd.preco_unit) as precototal'))
				->join(array('pd'=>'tb_pedidos_prod'), 'pd.id_ped = p.id')
				->where('p.status = "ped" and p.sit = 0'.$wheretotal);
			
			$stmt 	= $db->query($select);
				
			foreach ($stmt->fetchAll() as $listavendas);
			$vendastota = round($listavendas->precototal,2).";";
			
			//-- monta string para o AJAX -------------------------------------------------------------------
			echo substr($grupos,0,-1)."|".substr($vendas,0,-1)."|".substr($pend,0,-1)."|".substr($margem,0,-1);
			
			$grupos = explode(";",substr($grupos,0,-1));
			$vendas = explode(";",substr($vendast,0,-1));
			$pend 	= explode(";",substr($pend,0,-1));
			$margem = explode(";",substr($margem,0,-1));
			$estoque = explode(";",substr($estoque,0,-1));
						
			$totalvenda = $totalpend = $totalmargem = $totalest = 0;
			if(count($grupos)>0){ 
			    echo '|<table style="width: 100%" class="tableStatic">
            	<thead><tr><td width="5%" style="text-align: center">ID</td><td width="" >Descrição</td><td width="">Vendas</td><td width="">Margem</td><td width="">Pendências</td><td width="">Estoque</td></tr></thead>
                <tbody>';
				foreach ($grupos as $key => $row){
				    echo '<tr><td>'.($key+1).'</td><td>'.$row.'</td>';
				    $rowvenda = 0;
				    foreach ($vendas as $keyv => $rowv){
						if($keyv == $key){ 
							echo '<td style="text-align: right;">'.number_format($rowv,2,",",".").' ('.number_format(@(($rowv*100)/$vendastota),2,",",".").'%)</td>';
							$rowvenda = $rowv;
							$totalvenda += $rowv; 
						}
					}	
					foreach ($margem as $keym => $rowm){
						if($keym == $key){ 
						    echo '<td style="text-align: right;">'.number_format($rowm,2,",",".").' ('.number_format(@(($rowm*100)/$rowvenda),2,",",".").'%)</td>';
						    $totalmargem += $rowm;
						}
					}
					foreach ($pend as $keyp => $rowp){
						if($keyp == $key){ 
						    echo '<td style="text-align: right;">'.number_format($rowp,2,",",".").'</td>';
						    $totalpend += $rowp;
						}
					}
					foreach ($estoque as $keye => $rowe){
						if($keye == $key){ 
						    echo '<td style="text-align: right;">'.number_format($rowe,2,",",".").' ('.number_format(($rowe*100)/$totalestoque,2,",",".").'%)</td>';
						    $totalest += $rowe;
						}
					}
					echo '</tr>';
				}
				
				echo '<tr>
				        <td>&nbsp;</td>
				        <td style="font-weight: bold; text-align: center;">Total</td>
				        <td style="font-weight: bold; text-align: right;">'.number_format($totalvenda,2,",",".").' ('.number_format(@(($totalvenda*100)/$vendastota),2,",",".").'%)</td>
				        <td style="font-weight: bold; text-align: right;">'.number_format($totalmargem,2,",",".").' ('.number_format(@(($totalmargem*100)/$totalvenda),2,",",".").'%)</td>
				        <td style="font-weight: bold; text-align: right;">'.number_format($totalpend,2,",",".").'</td>
				        <td style="font-weight: bold; text-align: right;">'.number_format($totalest,2,",",".").' ('.number_format(($totalest*100)/$totalestoque,2,",",".").'%)</td>
				      </tr>';
				echo '</tbody></table>';
				
			}
		}
		
		//----- relatorio de rentabilidade ----------------------------
		function listaProdutosestoque($val=""){
		    $boc	= new ProdutosclassesModel();
		    
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			if((!empty($val['dataini'])) || (!empty($val['datafim']))){
				$dataini = substr($val['dataini'],6,4).'-'.substr($val['dataini'],3,2).'-'.substr($val['dataini'],0,2);;
				$datafim = substr($val['datafim'],6,4).'-'.substr($val['datafim'],3,2).'-'.substr($val['datafim'],0,2);;;
		
				if((!empty($val['dataini'])) and (!empty($val['datafim']))):
					$where 		= " and p.data_vend between '".$dataini."' and '".$datafim." 23:59:59'";
					$wherepen	= " and pend.dt_pend between '".$dataini."' and '".$datafim." 23:59:59'";
					$wheremarg	= " and p.data_vend between '".$dataini."' and '".$datafim." 23:59:59'";
					
				elseif((!empty($val['dataini'])) and (empty($val['datafim']))):
					$where 		= " and p.data_vend >= '".$dataini."'";
					$wherepen	= " and pend.dt_pend >= '".$dataini."'";
					$wheremarg  = " and p.data_vend >= '".$dataini."'";
				elseif((empty($val['dataini'])) and (!empty($val['datafim']))):
					$where 		= " and p.data_vend <= '".$datafim."'";
					$wherepen	= " and pend.dt_pend <= '".$datafim."'";
					$wheremarg	= " and p.data_vend <= '".$datafim."'";
				endif;
		
				$wheretotal = $where;
			}
							
			//-- busca as total do estoque------------------------------------------------------------------
			$select = $db->select();			
			$select->from(array('pr'=>'produtos','*'), array('sum(z.qt_atual*cv.valor) as valorestoque'))
				->join(array('cv'=>'tb_produtoscmv'), 'pr.ID = cv.id_produtos and cv.id = (select max(v.id) from tb_produtoscmv v where v.id_produtos = z.id_prod)')
				->join(array('z'=>'tb_estoqueztl'), 'z.id_prod = pr.ID and z.id = (select max(id) from tb_estoqueztl ez where ez.id_prod = pr.ID)');
			
			$stmt 	= $db->query($select);
			
			foreach ($stmt->fetchAll() as $listaestoque);
			$totalestoque =  $listaestoque->valorestoque;
			
			//-- busca as total das vendas ------------------------------------------------------------------
			$select = $db->select();
			
			$select->from(array('p'=>'tb_pedidos','*'), array('sum(pd.qt*pd.preco_unit) as precototal'))
			->join(array('pd'=>'tb_pedidos_prod'), 'pd.id_ped = p.id')
			->where('p.status = "ped" and p.sit = 0'.$wheretotal);
			
			$stmt 	= $db->query($select);
			
			foreach ($stmt->fetchAll() as $listavendas);
			$vendastotal = round($listavendas->precototal,2).";";
			
			
			//-- busco produtos --------------------------
			$produtos = "";
			foreach ($val as $key => $row){
				$produtos .= $key.",";
			}
			
			$produtos = str_replace('module,controller,action,dataini,datafim,', '', $produtos);
			
			if($produtos!=""){
			
				$select = $db->select();
				$select->from(array('pr'=>'produtos','*'), array('pr.ID','pr.id_produtosclasses','(z.qt_atual*cv.valor) as valorestoque','z.qt_atual as estoque', 'cv.valor as valorcmv', 'pr.CODIGO', 'pr.PRECO_UNITARIO', 's.descricao as descsubgrupo', 'g.descricao as descgrupo'))
					->join(array('cv'=>'tb_produtoscmv'), 'pr.ID = cv.id_produtos and cv.id = (select max(v.id) from tb_produtoscmv v where v.id_produtos = z.id_prod)')
					->join(array('z'=>'tb_estoqueztl'), 'z.id_prod = pr.ID and z.id = (select max(id) from tb_estoqueztl ez where ez.id_prod = pr.ID)')
					->join(array('s'=>'tb_gruposprodsub'), 's.id = pr.id_gruposprodsub')
					->join(array('g'=>'tb_gruposprod'), 'g.id = s.id_gruposprod')
				
				->where('pr.ID in ('.substr($produtos,0,-1).')')
				->order('pr.codigo_mask');
				
				$stmt 	= $db->query($select);
				$return = array();
				$count = 0;
				foreach ($stmt->fetchAll() as $listaprodutos){
				    $sugestao 		= $sugestaoper = $estoqueper = $vendasper = 0;
				    
				    $preco  = ($listaprodutos->PRECO_UNITARIO*0.46);
				    $margem = (($preco-$listaprodutos->valorcmv) * 100)/$preco;
				    $markup = (($preco))/$listaprodutos->valorcmv;
				     
				    $estoqueper = ($listaprodutos->valorestoque*100)/$totalestoque;
				    $return[$count]['codigo'] 		= $listaprodutos->CODIGO;
				    $return[$count]['valorestoque'] = $listaprodutos->valorestoque;
				    $return[$count]['estoqueper'] 	= $estoqueper;
				    $return[$count]['estoque'] 		= $listaprodutos->estoque;
				    $return[$count]['valorcmv'] 	= $listaprodutos->valorcmv;
				    $return[$count]['preco'] 		= $preco;
				    $return[$count]['margem'] 		= $margem;
				    $return[$count]['markup'] 		= $markup;
				    $return[$count]['idprod'] 		= $listaprodutos->ID;
				    $return[$count]['idclasse'] 	= $listaprodutos->id_produtosclasses;
				     
				    //-- busca as vendas ------------------------------------------------------------------
				    $select = $db->select();
				    $select->from(array('p'=>'tb_pedidos','*'), array('sum(pd.qt*pd.preco_unit) as precototal'))
					    ->join(array('pd'=>'tb_pedidos_prod'), 'pd.id_ped = p.id')
					    ->where('p.status = "ped" and p.sit = 0 and pd.id_prod = '.$listaprodutos->ID.$where);
				    
				    $stmt 	= $db->query($select);
				    $objvendas = $stmt->fetchAll();
				    
				    if(count($objvendas)>0){
				    	foreach ($objvendas as $listavendas);
				    	$vendast 	   = round($listavendas->precototal,2);
				    }else{
				    	$vendast = 0;
				    }
				    
				    //-- busca as markup ------------------------------------------------------------------
				    $select = $db->select();
				    $select->from(array('p'=>'tb_pedidos','*'), array('sum(pd.qt*pd.custocompra) as precototal'))
					    ->join(array('pd'=>'tb_pedidos_prod'), 'pd.id_ped = p.id')
					    ->where('p.status = "ped" and p.sit = 0 and pd.id_prod = '.$listaprodutos->ID.$where);
				     
				    $stmt 	= $db->query($select);
				    $objcusto = $stmt->fetchAll();
				     
				    $markupvenda = 0;
				    if(count($objcusto)>0){
				    	foreach ($objcusto as $listacusto);
				    	$vendacusto = round($listacusto->precototal,2);
				    				    	
				    	$markupvenda = ($vendacusto!=0) ? ($vendast)/$vendacusto : 0;			    	
				    }else{
				    	$vendacusto = 0;
				    }			    
				    
				    $vendasper 		= @(($vendast*100)/$vendastotal);
				    $sugestaoper 	= ($vendasper!=0) ? (($vendasper - $estoqueper)*100)/$vendasper : 0;
				    $sugestao		= ($totalestoque * $vendasper)/100;
				    $sugestao		-= $listaprodutos->valorestoque;
				    
				    $return[$count]['vendastotal'] 	= $vendast;
				    $return[$count]['vendasper'] 	= $vendasper;
				    $return[$count]['sugestao']		= $sugestao;
				    $return[$count]['sugestaoper'] 	= $sugestaoper;
				    $return[$count]['vendascusto'] 	= $markupvenda;
				    
				    $count++;			    
				}
				
				//return $return;
				
				$this->objProd 		= $return;
				$this->objParams 	= $val;
				?>
				<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<title>SisZTL 2.0 Alpha - ztlbrasil.com.br</title>
				<link href="/public/sistema/imagens/ztl.ico" rel="shortcut icon" type="image/x-ico"/>
				
				<link href="/public/sistema/css/mainprint.css" rel="stylesheet" type="text/css" />	
				
				</head>
				<!-- Content -->
				<body onload="window.print()">
				    <div class="content" style="width: 1000px; margin: auto">
				    	<div class="widgets">
				    		<div class="left">SisZTL 2.0 Alpha</div>
				    		<div class="right" style="text-align: right;"><?php echo date('d/m/Y H:i')?></div>
				    		<div class="clear"></div>	
				    	</div>   
				    	<?php 
				    	if((!empty($this->objParams['dataini'])) and (!empty($this->objParams['datafim']))):
				    		$periodo = " intervalo entre ".str_replace("-", "/", $this->objParams['dataini'])." e ".str_replace("-", "/", $this->objParams['datafim']);	    	
				    	elseif((!empty($this->objParams['dataini'])) and (empty($this->objParams['datafim']))):
				    		$periodo = " a partir da data ".str_replace("-", "/", $this->objParams['dataini']);
				    	elseif((empty($this->objParams['dataini'])) and (!empty($this->objParams['datafim']))):
				    		$periodo = " até a data  ".str_replace("-", "/", $this->objParams['datafim']);
				    	endif;
				    	?>
					    <div class="title"><h5>Seleção de produtos <?php echo $periodo?></h5></div>
				
					    <div class="widgets">
					    
					    <?php if($this->objProd != 'erro1'){ ?>
					    
							<div class="widget first">
					 		<table style="width: 100%" class="tableStatic">
					        	<thead>
					            	<tr>
					                	<td width="">Código</td>
					                	<td width="">Classe</td>
					                    <td width="">Estoque</td>
					                    <td width="">Custo</td>
					                    <td width="">Preço</td>
					                    <td width="">Margem</td>
					                    <td width="">Markup</td>
					                    <td width="">Vendas</td>
					                    <td width="">Markup Vendas</td>
					                    <td width="">Sugestão</td>                                               
					               	</tr>
					         	</thead>
					            <tbody>
					           		<?php
					           		$arrayprod = $this->objProd;
					           		foreach ($arrayprod as $key => $row) {
					           			$ordenadesc[$key]  = $row['sugestao'];
					           		}
					           		
					           		array_multisort($ordenadesc, SORT_ASC, $arrayprod);
					           		
							        foreach ($arrayprod as $key => $row) {

										//-- busca a classe ------------------------------------------------------------------
										$classe = "";
										if(!empty($row['idclasse'])){
											$classe = $boc->fetchRow("id = '".$row['idclasse']."'");
										}
										?>
						        		<tr >
						        			<td align="left"><?php echo $row['codigo']?></td>
						        			<td align="left"><?php echo (!empty($classe)) ? $classe->letra : ""; ?></td>
							                <td align="right"><?php echo number_format($row['valorestoque'],2,",",".")?> (<?php echo $row['estoque']."/".number_format($row['estoqueper'],3,",",".")?>%)</td>
							                <td align="right"><?php echo number_format($row['valorcmv'],2,",",".")?></td>
							                <td align="right"><?php echo number_format($row['preco'],2,",",".")?></td>
							                <td align="right"><?php echo number_format($row['margem'],2,",",".")?></td>
							                <td align="right"><?php echo number_format($row['markup'],2,",",".")?></td>
							                <td align="right"><?php echo number_format($row['vendastotal'],2,",",".")?> (<?php echo number_format($row['vendasper'],3,",",".")?>%)</td>
							                <td align="right"><?php echo number_format($row['vendascusto'],2,",",".")?></td>
							                <td align="right"><?php echo number_format($row['sugestao'],2,",",".")?> (<?php echo number_format($row['sugestaoper'],3,",",".")?>%)</td>
								     	</tr>
						        		<?php
					        		}
									?>
								</tbody>
					        </table>
					   </div>
					   <?php 
    	}else{
			?>
			<div class="widget first" style="text-align: center; border-top: 1px solid #d5d5d5; padding: 20px">Nenhum produto selecionado!</div>
			<?php 
		}
	   ?>
   </div>
 </div>
 </body>
 </html>
				<?php 
				
				
			}else{
				return 'erro1';
			}
							
						
		}
		
		function calcularValorcmv($pesq){
		    $bo		= new EstoqueModel();
		    $pop	= new ProdutosModel();
		    $bov	= new ProdutoscmvModel();
		    
		    if((!empty($pesq['dtini'])) and (!empty($pesq['dtfim']))){
		    	$di	= substr($pesq['dtini'],6,4).'-'.substr($pesq['dtini'],3,2).'-'.substr($pesq['dtini'],0,2);
		    	$df	= substr($pesq['dtfim'],6,4).'-'.substr($pesq['dtfim'],3,2).'-'.substr($pesq['dtfim'],0,2).' 23:59:59';
		    	
			    try{
			    	$bo		= new EstoqueModel();
			    	$pop	= new ProdutosModel();
			    	$bov	= new ProdutoscmvModel();
			    	
		    		$totalcmv = $totalpecas = 0;
		    		foreach(ProdutosBO::listaallProdutos() as $produtos){
		    			 
		    			$estoqueinicial = "";
		    			$estoque = 0;
		    			foreach ($bo->fetchAll("id = (select max(e.id) from tb_estoqueztl e where e.id_prod = ".$produtos->ID." and e.dt_atualizacao <= '".$di."')") as $estoqueinicial);
		    			if(count($estoqueinicial)>0){
		    				if(!empty($estoqueinicial->qt_atual)) $estoque = $estoqueinicial->qt_atual;
		    			}
		    
		    			//-- busco cmv inicial no periodo ------------
		    			$vlcmvinicial = 0;
		    			$cmvinicial = "";
		    			foreach ($bov->fetchAll("id = (select max(v.id) from tb_produtoscmv v where v.id_produtos = ".$produtos->ID." and v.data <= '".$di."')") as $cmvinicial);
		    			if((count($cmvinicial)>0) and (!empty($cmvinicial->valor))){
		    				$vlcmvinicial = $cmvinicial->valor;
		    			}else{
		    				//-- se nao existir, busco primeiro CMV que foi cadastrado -----------------------------------
		    				foreach ($bov->fetchAll("id = (select min(v.id) from tb_produtoscmv v where v.id_produtos = ".$produtos->ID.")") as $cmvinicial);
		    				if(!empty($cmvinicial->valor)) $vlcmvinicial = $cmvinicial->valor;
		    			}
		    			 
		    			$vlcmvinicial 	= $estoque*$vlcmvinicial;
		    			
		    			//--- busco todas as compras no periodo, multiplicando pelo valor do CMV imediato apos a entrada do estoque -----------------------------------
		    			$objqt = "";
		    			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		    			$db->setFetchMode(Zend_Db::FETCH_OBJ);
		    			$select = $db->select();
		    
		    			$select->from(array('e'=>'tb_estoqueztl','*'), array('e.qt_atualizacao','e.dt_atualizacao'))
		    			->where("e.id_prod = ".$produtos->ID." and e.tipo like '%compra%' and e.dt_atualizacao between '".$di."' and '".$df."'");
		    			$stmt 	= $db->query($select);
		    			$objqt = $stmt->fetchAll();
		    			 
		    			$compra = 0;
		    			if(count($objqt)>0){
		    				foreach ($objqt as $listacompras){
		    					foreach ($bov->fetchAll("id = (select min(v.id) from tb_produtoscmv v where v.id_produtos = ".$produtos->ID." and v.data >= '".$listacompras->dt_atualizacao."')") as $cmvcompra);
		    					$compra 	   += $listacompras->qt_atualizacao*$cmvcompra->valor;
		    				}
		    			}else{
		    				$compra = 0;
		    			}
		    			
		    			$estoquefinal = "";
		    			$estoque = 0;
		    			foreach ($bo->fetchAll("id = (select max(e.id) from tb_estoqueztl e where e.id_prod = ".$produtos->ID." and e.dt_atualizacao <= '".$df."')") as $estoquefinal);
		    			if(count($estoquefinal)>0){
		    				if(!empty($estoquefinal->qt_atual)) $estoque = $estoquefinal->qt_atual;
		    			}
		    			
		    			//-- busco cmv final no periodo ------------
		    			$vlcmvfinal = 0;
		    			$cmvfinal = "";
		    			foreach ($bov->fetchAll("id = (select max(v.id) from tb_produtoscmv v where v.id_produtos = ".$produtos->ID." and v.data <= '".$df."')") as $cmvfinal);
		    			if((count($cmvfinal)>0) and (!empty($cmvfinal->valor))){
		    				$vlcmvfinal = $cmvfinal->valor;
		    			}else{
		    				//-- se nao existir, busco primeiro CMV que foi cadastrado -----------------------------------
		    				foreach ($bov->fetchAll("id = (select min(v.id) from tb_produtoscmv v where v.id_produtos = ".$produtos->ID.")") as $cmvfinal);
		    				if(!empty($cmvfinal->valor)) $vlcmvfinal = $cmvfinal->valor;
		    			}
		    			
		    			$vlcmvfinal 	= $estoque*$vlcmvfinal;
		    			
		    			$totalcmv += ($vlcmvinicial+$compra)-$vlcmvfinal;
		    			
		    		}
		    
		    		return $totalcmv;
		    		
			    }catch (Zend_Exception $e){
			    	$array = array(
			    			'reporte' => $e->getMessage(),
			    			'pagina'  => 'EstoqueBO::valorEstoque()'
			    	);
			    
			    	DiversosBO::gravarReporte($array);
			    
			    	return 0;
			    }
		    }
		       
		}
		
		function valorEstoque($cont=6){
		    try{
			    $bo		= new EstoqueModel();
			    $pop	= new ProdutosModel();
			    $bov	= new ProdutoscmvModel();
			    
			    $interacao = $cont+5;
			    $stringRet = "";
			    for($i=$cont;$i<=$interacao;$i++){
		            
		            $df 	= date('Y-m-31', mktime(0, 0, 0, date('m') - ($i), '01', date('Y')));
		            $mask 	= date('31/m/Y', mktime(0, 0, 0, date('m') - ($i), '01', date('Y')));
		            $dm 	= date('Y-m', mktime(0, 0, 0, date('m') - ($i), '01', date('Y'))); 
			        
		            $where 	= ' and e.dt_atualizacao <= "'.$df.' 23:59:59"';
		            $where2 = ' and e.dt_atualizacao like "'.$dm.'%"';
		            
				    $totalcmv = $totalpecas = $compra = 0;
				    foreach(ProdutosBO::listaallProdutos() as $produtos){
				    	
				        $estoqueinicial = "";
				        $estoque = 0;
				        foreach ($bo->fetchAll("id = (select max(e.id) from tb_estoqueztl e where e.id_prod = ".$produtos->ID." ".$where.")") as $estoqueinicial);
				        if(count($estoqueinicial)>0){
				            if(!empty($estoqueinicial->qt_atual)) $estoque = $estoqueinicial->qt_atual;				        	
				        }
				        
				    	//-- busco cmv inicial no periodo ------------
				    	$vlcmvinicial = 0;
				    	$cmvinicial = "";
				    	foreach ($bov->fetchAll("id = (select max(v.id) from tb_produtoscmv v where v.id_produtos = ".$produtos->ID." and v.data <= '".$df."')") as $cmvinicial);
				    	if((count($cmvinicial)>0) and (!empty($cmvinicial->valor))){
				    		$vlcmvinicial = $cmvinicial->valor;
				    	}else{
				    		//-- se nao existir, busco primeiro CMV que foi cadastrado -----------------------------------
				    		foreach ($bov->fetchAll("id = (select min(v.id) from tb_produtoscmv v where v.id_produtos = ".$produtos->ID.")") as $cmvinicial);
				    		if(!empty($cmvinicial->valor)) $vlcmvinicial = $cmvinicial->valor;
				    	}  	 
				    	
				    	$totalcmv 	+= $estoque*$vlcmvinicial;
				    	$totalpecas += $estoque;
				    	
				    	//--- busco todas as compras no periodo, multiplicando pelo valor do CMV imediato apos a entrada do estoque -----------------------------------
				    	$objqt = "";
				    	$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
				    	$db->setFetchMode(Zend_Db::FETCH_OBJ);
				    	$select = $db->select();
				    
				    	$select->from(array('e'=>'tb_estoqueztl','*'), array('e.qt_atualizacao','e.dt_atualizacao'))
					    	->where("e.id_prod = ".$produtos->ID." and e.tipo like '%compra%' ".$where2);
					    	$stmt 	= $db->query($select);
					    	$objqt = $stmt->fetchAll();
				    	
				    	if(count($objqt)>0){
				    		foreach ($objqt as $listacompras){
				    			foreach ($bov->fetchAll("id = (select min(v.id) from tb_produtoscmv v where v.id_produtos = ".$produtos->ID." and v.data >= '".$listacompras->dt_atualizacao."')") as $cmvcompra);
				    			$compra 	   += $listacompras->qt_atualizacao*$cmvcompra->valor;
				    		}
				    	}else{
				    		$compra += 0;
				    	}
				    	  
				    }
				    
				    $stringRet .= $mask.";".number_format($totalcmv,2,",",".").";".$totalpecas.";".number_format($compra,2,",",".")."|";
		        }
				    
			    echo substr($stringRet,0,-1);
		    }catch (Zend_Exception $e){
		        $array = array(
	        		'reporte' => $e->getMessage(),
	        		'pagina'  => 'EstoqueBO::valorEstoque()'
		        );
		        
		        DiversosBO::gravarReporte($array);

		        echo 'erro|Erro ao gerar histórico do estoque!';
		        
		    }
		    
		}
		
		function valorEstoqueatual(){
			$bo		= new EstoqueModel();
			$pop	= new ProdutosModel();
			$bov	= new ProdutoscmvModel();
		
				 
			$df 	= date('Y-m-d');
			$dm 	= date('Y-m');
			 
			$where 	= ' and e.dt_atualizacao <= "'.$df.' 23:59:59"';
			$where2 = ' and e.dt_atualizacao like "'.$dm.'%"';
			 
			$totalcmv = $totalpecas = 0;
			foreach(ProdutosBO::listaallProdutos() as $produtos){
				
				$estoqueinicial = "";
				foreach ($bo->fetchAll("id = (select max(e.id) from tb_estoqueztl e where e.id_prod = ".$produtos->ID." ".$where.")") as $estoqueinicial);

				//-- busco valor do estoque inicial -----------------------------------------------------------
				$vlestoqueinicial = $vlestoquefinal = 0;

				//-- busco cmv inicial no periodo ------------
				$vlcmvinicial = 0;
				$cmvinicial = "";
				foreach ($bov->fetchAll("id = (select max(v.id) from tb_produtoscmv v where v.id_produtos = ".$produtos->ID." and v.data <= '".$df."')") as $cmvinicial);
				if(isset($cmvinicial) and count($cmvinicial)>0){
					$vlcmvinicial = $cmvinicial->valor;
				}else{
					//-- se nao existir, busco primeiro CMV que foi cadastrado -----------------------------------
					foreach ($bov->fetchAll("id = (select min(v.id) from tb_produtoscmv v where v.id_produtos = ".$produtos->ID.")") as $cmvinicial);
					$vlcmvinicial = $cmvinicial->valor;
				}

				$totalcmv 	+= $estoqueinicial->qt_atual*$vlcmvinicial;
				$totalpecas += $estoqueinicial->qt_atual;

				//--- busco todas as compras no periodo, multiplicando pelo valor do CMV imediato apos a entrada do estoque -----------------------------------
				$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
				$db->setFetchMode(Zend_Db::FETCH_OBJ);
				$select = $db->select();
				 
				$select->from(array('e'=>'tb_estoqueztl','*'), array('e.qt_atualizacao','e.dt_atualizacao'))
				->where("e.id_prod = ".$produtos->ID." and e.tipo like '%compra%' ".$where2);
				$stmt 	= $db->query($select);
				$objqt = $stmt->fetchAll();

				$compra = 0;
				if(count($objqt)>0){
					foreach ($objqt as $listacompras){
						foreach ($bov->fetchAll("id = (select min(v.id) from tb_produtoscmv v where v.id_produtos = ".$produtos->ID." and v.data >= '".$listacompras->dt_atualizacao."')") as $cmvcompra);
						$compra 	   += $listacompras->qt_atualizacao*$cmvcompra->valor;
					}
				}else{
					$compra += 0;
				}
				 
			}
				 
			//$arrayRet = array('estoque'	=> $totalcmv, 'totalpc'	=> $totalpecas, 'compra'	=> $compra);
			//return $arrayRet;
			
			echo "R$ ".number_format($totalcmv,2,",",".")."|".$totalpecas." pçs|R$ ".number_format($compra,2,",",".");
		
		}
		
		
		function corrigeestoqueVenda($params){
			try{
				$bo 		= new EstoqueModel();
					
				$usuario 	= Zend_Auth::getInstance()->getIdentity();
				
				$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
				$db->setFetchMode(Zend_Db::FETCH_OBJ);
				$select = $db->select();
					
				$select->from(array('p'=>'tb_pedidos'), array('pp.id_prod as idproduto','pp.id_ped as idpedido','pp.qt as qtprod'))
					->join(array('pp'=>'tb_pedidos_prod'), 'pp.id_ped = p.id')
					->joinLeft(array('z'=>'tb_estoqueztl'), 'pp.id_prod = z.id_prod  and pp.id_ped = z.id_atualizacao and z.tipo = "VENDA"')
					->where('status = "ped" and p.sit = 0 and pp.id_ped = p.id and p.data_vend >= "2010-07-09" and z.id is NULL and pp.id_prod is not NULL');
				
				$stmt = $db->query($select);
				
				$obj = $stmt->fetchAll();
					
				if(count($obj)>0){
					foreach($obj as $listProd){
					    foreach (EstoqueBO::buscaEstoque($listProd->idproduto) as $estoque);
					    if(count($estoque)>0){
					    	$qt_atual	= $estoque->qt_atual;
					    }
					    	
					    $arrayestq = array(
					    	'id_prod' 			=> $listProd->idproduto,
						    'qt_atual' 			=> $qt_atual-$listProd->qtprod,
						    'qt_atualizacao' 	=> -($listProd->qtprod),
						    'id_atualizacao' 	=> $listProd->idpedido,
						    'dt_atualizacao' 	=> date("Y-m-d H:i:s"),
						    'tipo' 				=> "VENDA",
						    'id_user'			=> $usuario->id,
					    	'obs' 				=> "Correção de estoque"
					    );
					    
					    $bo->insert($arrayestq);
					}
				}
					
				LogBO::cadastraLog("Vendas/Pedidos Correcao",4,$usuario->id,$ped->id,"VENDA ".$ped->id);
					
				echo "Corrigido com sucesso!";
				
			}catch (Zend_Exception $e){
				echo "Erro ao baixar estoque...";
		
				$boerro	= new ErrosModel();
				$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "EstoqueBO::correcaoestoqueVenda(".$params['idped'].")");
				$boerro->insert($dataerro);
		
				return false;
			}
				
		}
		
		//----- relatorio de vendas ----------------------------
		function buscaValorvendas($val=""){
				
			if((!empty($val['dtini'])) || (!empty($val['dtfim']))){
				$dataini = substr($val['dtini'],6,4).'-'.substr($val['dtini'],3,2).'-'.substr($val['dtini'],0,2);;
				$datafim = substr($val['dtfim'],6,4).'-'.substr($val['dtfim'],3,2).'-'.substr($val['dtfim'],0,2);;;
		
				if((!empty($val['dtini'])) and (!empty($val['dtfim']))){
					$where 		= " and p.data_vend between '".$dataini."' and '".$datafim." 23:59:59'";
					$wheredesp  = " and f.emissao between '".$dataini."' and '".$datafim." 23:59:59'";
					$wherenf	= " and n.data between '".$dataini."' and '".$datafim." 23:59:59'";
				}elseif((!empty($val['dtini'])) and (empty($val['dtfim']))){
					$where 		= " and p.data_vend >= '".$dataini."'";
					$wheredesp 	= " and f.emissao >= '".$dataini."'";
					$wherenf 	= " and n.data >= '".$dataini."'";
				}elseif((empty($val['dtini'])) and (!empty($val['dtfim']))){
					$where 		= " and p.data_vend <= '".$datafim."'";
					$wheredesp 	= " and f.emissao <= '".$datafim."'";
					$wherenf 	= " and n.data <= '".$datafim."'";
				}
			}else{
			    $where = " and p.data_vend like '".date('Y-m')."%'";
			    $wheredesp = " and f.emissao like '".date('Y-m')."%'";
			    $wherenf = " and n.data like '".date('Y-m')."%'";
			}
			
			//--- Busca vendas faturadas --------------------------------------------------------------------
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
				
			$select->from(array('p'=>'tb_pedidos','*'), array('sum(pd.qt*pd.preco_unit) as precototal'))
				->join(array('pd'=>'tb_pedidos_prod'), 'pd.id_ped = p.id')		
				->where('p.status = "ped" and p.sit = 0'.$where);
				
			$stmt = $db->query($select);
			$obj = $stmt->fetchAll();
			
			if(count($obj)>0){
			    foreach ($obj as $vendas);
			    $vendas = $vendas->precototal;
			}
			
			//--- Busca descontos  ---------------------------------------------------------------------------
			$select = $db->select();
			$select->from(array('p'=>'tb_pedidos','*'), array('sum(p.desconto) as descontototal'))
				->where('p.status = "ped" and p.sit = 0'.$where);
			
			$stmt = $db->query($select);
			$obj = $stmt->fetchAll();
				
			if(count($obj)>0){
				foreach ($obj as $desconto);
				$desconto = $desconto->descontototal;
			}
			
			$vendas = $vendas-$desconto;
			
			//--- Busca custo dos produtos --------------------------------------------------------------------
			$select = $db->select();
			$select->from(array('p'=>'tb_pedidos','*'), array('sum(pd.qt*pd.custocompra) as custototal'))
				->join(array('pd'=>'tb_pedidos_prod'), 'pd.id_ped = p.id')
				->where('p.status = "ped" and p.sit = 0'.$where);
			
			$stmt = $db->query($select);
			$obj = $stmt->fetchAll();
				
			if(count($obj)>0){
				foreach ($obj as $custo);
				$custo = $custo->custototal;
			}
			
			//--- Busca despesas ------------------------------------------------------------------------------
			$bof	= new FinanceiroModel();
		    $bo		= new RelatoriosModel();
		    
		    $rel = $bo->fetchRow();
		    
		    $admin = 0;
		    if($rel->plcontas != "" and $rel->plcontas != NULL){
			    $wheredesp .= " and p.id_financeiroplcontas in (".substr($rel->plcontas, 1,-1).")";
			    
				$select = $db->select();
				$select->from(array('f'=>'tb_financeiropag','*'), array('sum(p.valor_apagar) as valortotal'))
					->join(array('p'=>'tb_financeiropagparc'),'f.id = p.id_financeiropag')
					->where("f.sit = true and p.sit = true ".$wheredesp);
				
				$stmt = $db->query($select);
				$obj = $stmt->fetchAll();
			
				if(count($obj)>0){
					foreach ($obj as $despesas);
					$admin = $despesas->valortotal;
				} 			
		    }
		    
		    //-- busca impostos pela emissao da nota ---------------------------------------------------------
		    //--icms -----------------------------------------------------------------------------------------
		    $icms = $pis = $cofins = $ipi = 0;
		    $select = $db->select();
		    $select->from(array('n'=>'tb_nfe','*'), array('sum(n.vlicms) as valortotal'))
		    	->where("n.status = 1 and n.cfop not in (5916,6911,6915,6916,6501,6901,6949,7949) and n.tipo = 1 ".$wherenf);
		    
		    $stmt = $db->query($select);
		    $obj = $stmt->fetchAll();
		    	
		    if(count($obj)>0){
		    	foreach ($obj as $despesas);
		    	$icms = $despesas->valortotal;
		    }
		    
		    $select = $db->select();
		    $select->from(array('n'=>'tb_nfe','*'), array('sum(n.vlicms) as valortotal'))
		    	->where("n.status = 1 and n.cfop in (3102) and n.tipo = 0 ".$wherenf);
		    
		    $stmt = $db->query($select);
		    $obj = $stmt->fetchAll();
		     
		    if(count($obj)>0){
		    	foreach ($obj as $despesas);
		    	$icms -= $despesas->valortotal;
		    }
		    
		    //-- ipi ----------------------------------------------------------------------------------------
		    $select = $db->select();
		    $select->from(array('n'=>'tb_nfe','*'), array('sum(n.totalipi) as valortotal'))
		    ->where("n.status = 1 and n.cfop not in (5916,6911,6915,6916,6501,6901,6949,7949) and n.tipo = 1 ".$wherenf);
		    
		    $stmt = $db->query($select);
		    $obj = $stmt->fetchAll();
		     
		    if(count($obj)>0){
		    	foreach ($obj as $despesas);
		    	$ipi = $despesas->valortotal;
		    }
		    
		    $select = $db->select();
		    $select->from(array('n'=>'tb_nfe','*'), array('sum(n.totalipi) as valortotal'))
		    	->where("n.status = 1 and n.cfop in (3102) and n.tipo = 0 ".$wherenf);
		    
		    $stmt = $db->query($select);
		    $obj = $stmt->fetchAll();
		     
		    if(count($obj)>0){
		    	foreach ($obj as $despesas);
		    	$ipi -= $despesas->valortotal;
		    }
		    
		    //-- cofins ----------------------------------------------------------------------------------------
		    $select = $db->select();
		    $select->from(array('n'=>'tb_nfe','*'), array('sum(n.totalcofins) as valortotal'))
		    ->where("n.status = 1 and n.cfop not in (5916,6911,6915,6916,6501,6901,6949,7949) and n.tipo = 1 ".$wherenf);
		    
		    $stmt = $db->query($select);
		    $obj = $stmt->fetchAll();
		     
		    if(count($obj)>0){
		    	foreach ($obj as $despesas);
		    	$cofins = $despesas->valortotal;
		    }
		    
		    $select = $db->select();
		    $select->from(array('n'=>'tb_nfe','*'), array('sum(n.totalcofins) as valortotal'))
		    ->where("n.status = 1 and n.cfop in (3102) and n.tipo = 0 ".$wherenf);
		    
		    $stmt = $db->query($select);
		    $obj = $stmt->fetchAll();
		     
		    if(count($obj)>0){
		    	foreach ($obj as $despesas);
		    	$cofins += $despesas->valortotal;
		    }
		    
		    //-- pis ----------------------------------------------------------------------------------------
		    $select = $db->select();
		    $select->from(array('n'=>'tb_nfe','*'), array('sum(n.totalpis) as valortotal'))
		    ->where("n.status = 1 and n.cfop not in (5916,6911,6915,6916,6501,6901,6949,7949) and n.tipo = 1 ".$wherenf);
		    
		    $stmt = $db->query($select);
		    $obj = $stmt->fetchAll();
		     
		    if(count($obj)>0){
		    	foreach ($obj as $despesas);
		    	$pis = $despesas->valortotal;
		    }
		    
		    $select = $db->select();
		    $select->from(array('n'=>'tb_nfe','*'), array('sum(n.totalpis) as valortotal'))
		    ->where("n.status = 1 and n.cfop in (3102) and n.tipo = 0 ".$wherenf);
		    
		    $stmt = $db->query($select);
		    $obj = $stmt->fetchAll();
		     
		    if(count($obj)>0){
		    	foreach ($obj as $despesas);
		    	$pis += $despesas->valortotal;
		    }
		    
		    //-- irpj e csll ----------------------------------------------------------------------------------------------
		    $totalfaturamento = 0;
		    $select = $db->select();
		    $select->from(array('n'=>'tb_nfe','*'), array('sum(n.totalnota) as valortotal'))
		    ->where("n.status = 1 and n.cfop not in (5916,6911,6915,6916,6501,6901,6949,7949) and n.tipo = 1 ".$wherenf);
		    
		    $stmt = $db->query($select);
		    $obj = $stmt->fetchAll();
		     
		    if(count($obj)>0){
		    	foreach ($obj as $faturado);
		    	$totalfaturamento = $faturado->valortotal;
		    }
		    
		    
		    $lucroirpj = ($totalfaturamento*8)/100;
		    $lucroirpj = ($lucroirpj*15)/100;
		    	
		    $lucroclss = ($totalfaturamento*12)/100;
		    $lucroclss = ($lucroclss*9)/100;
		    
		    
		    $tributaria = $icms+$ipi+$pis+$cofins+$lucroclss+$lucroirpj;
		    
			
		    ?>
		    <style>		    			    	
		    	.red{
		    		color: #f00;
		    		text-align: right;
		    	}
		    	
				.total{
		    		text-align: right;
		    		font-weight: bold;
		    		font-size: 14px;
		    	}
		    	
		    	.cinza{
		    		background-color: #d5d5d5;
		    	}
		    	
		    	.text-right{
		    		text-align: right;
		    	}
		    </style>
		    
		    <div class="widgets">
            	<div class="left">
				    <div class="widget">
		            	<div class="head"><h5 class="iMoney">Resultado no período</h5></div>
		            	<table class="tableStatic" style="width: 100%">
                        <tbody>
                            <tr>
                                <td>Faturamento Líquido</td>
                                <td class="text-right"><?php echo number_format($vendas,2,",",".")?></td>
                            </tr>
                            <tr>
                                <td>Custo dos produtos</td>
                                <td class="red"><?php echo number_format($custo,2,",",".")." (".number_format(($custo*100)/$vendas,2,",",".").")"?>%</td>
                            </tr>
                            <tr>
                                <td>Despesas</td>
                                <td class="red"><?php echo number_format($admin+$tributaria,2,",",".")." (".number_format((($admin+$tributaria)*100)/$vendas,2,",",".").")"?>%</td>
                            </tr>
                            <tr>
                                <td>Resultado</td>
                                <td class="total"><?php echo number_format($vendas-($admin+$tributaria+$custo),2,",",".")." (".number_format((($vendas-($admin+$tributaria+$custo))*100)/$vendas,2,",",".").")"?>%</td>
                            </tr>
                        </tbody>
                    </table>  
		            </div>
		        </div>
		        
		        <div class="right">
				    <div class="widget">
		            	<div class="head"><h5 class="iMoney">Percentuais</h5></div>
		            	<table class="tableStatic" style="width: 100%">
                        <tbody>
                            <tr>
                                <td>Markup</td>
                                <td class="text-right"><?php echo number_format((($vendas-$custo)/$custo)*100,2,",",".")?>%</td>
                            </tr>
                            <tr>
                                <td>Margem</td>
                                <td class="text-right"><?php echo number_format((($vendas-$custo)/$vendas)*100,2,",",".")."% (R$ ".number_format($vendas-$custo,2,",",".").")"; ?></td>
                            </tr>
                        </tbody>
                    </table>  
		            </div>
		            
		            <div class="widget">
		            	<div class="head"><h5 class="iMoney">Despesas</h5></div>
		            	<table class="tableStatic" style="width: 100%">
                        <tbody>
                            <tr>
                                <td>Desp admin</td>
                                <td class="text-right"><?php echo number_format($admin,2,",",".")." (".number_format(($admin*100)/$vendas,2,",",".").")"?>%</td>
                            </tr>
                            <tr>
                                <td>Desp tributária</td>
                                <td class="text-right"><?php echo number_format($tributaria,2,",",".")." (".number_format(($tributaria*100)/$vendas,2,",",".").")"?>%</td>
                            </tr>
                        </tbody>
                    </table>  
		            </div>
		            
		        </div>
		        
		  	</div>
		    
		    <div class="clear"></div>
		 	<?php    			
		}
		
		//----- relatorio de vendas ----------------------------
		function buscaValorvendascontatos($val=array()){
			$bo 	= new ContatosModel();
			$boe 	= new ContatosempModel();
			
			if(empty($val['dtini'])) $val['dtini'] = date("01-m-Y", strtotime("-11 month"));
			if(empty($val['dtfim'])) $val['dtfim'] = date("31-m-Y");
			
			$dataini = substr($val['dtini'],6,4).'-'.substr($val['dtini'],3,2).'-'.substr($val['dtini'],0,2);;
			$datafim = substr($val['dtfim'],6,4).'-'.substr($val['dtfim'],3,2).'-'.substr($val['dtfim'],0,2);;;
	
			if((!empty($val['dtini'])) and (!empty($val['dtfim']))){
				$where 		= " and p.data_vend between '".$dataini."' and '".$datafim." 23:59:59'";
				$wheredesp  = " and f.emissao between '".$dataini."' and '".$datafim." 23:59:59'";
				$wherenf	= " and n.data between '".$dataini."' and '".$datafim." 23:59:59'";
			}elseif((!empty($val['dtini'])) and (empty($val['dtfim']))){
				$where 		= " and p.data_vend >= '".$dataini."'";
				$wheredesp 	= " and f.emissao >= '".$dataini."'";
				$wherenf 	= " and n.data >= '".$dataini."'";
			}elseif((empty($val['dtini'])) and (!empty($val['dtfim']))){
				$where 		= " and p.data_vend <= '".$datafim."'";
				$wheredesp 	= " and f.emissao <= '".$datafim."'";
				$wherenf 	= " and n.data <= '".$datafim."'";
			}
							
			$empresas = $val['cliente'].",";
			if(isset($val['vendasfil'])){
				foreach ($boe->fetchAll("status = 1 and id_matriz = '".$val['idempresa']."'") as $filias){
					if($filias->id_clientes){
						$empresas .= $filias->id_clientes.",";
					}
				}
			}
			
			$where .= " and p.id_parceiro in (".substr($empresas,0,-1).")";
			$wheredesp .= " and f.id_fornecedor in (".substr($empresas,0,-1).")";
			
			//--- Busca vendas faturadas --------------------------------------------------------------------
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
		
			$select->from(array('p'=>'tb_pedidos','*'), array('sum(pd.qt*pd.preco_unit) as precototal'))
				->join(array('pd'=>'tb_pedidos_prod'), 'pd.id_ped = p.id')
				->where('p.status = "ped" and p.sit = 0'.$where);
		
			$stmt = $db->query($select);
			$obj = $stmt->fetchAll();
				
			if(count($obj)>0){
				foreach ($obj as $vendas);
				$vendas = $vendas->precototal;
			}
				
			//--- Busca descontos  ---------------------------------------------------------------------------
			$select = $db->select();
			$select->from(array('p'=>'tb_pedidos','*'), array('sum(p.desconto) as descontototal'))
				->where('p.status = "ped" and p.sit = 0'.$where);
				
			$stmt = $db->query($select);
			$obj = $stmt->fetchAll();
		
			if(count($obj)>0){
				foreach ($obj as $desconto);
				$desconto = $desconto->descontototal;
			}
				
			$vendas = $vendas-$desconto;
				
			//--- Busca custo dos produtos --------------------------------------------------------------------
			$select = $db->select();
			$select->from(array('p'=>'tb_pedidos','*'), array('sum(pd.qt*pd.custocompra) as custototal'))
				->join(array('pd'=>'tb_pedidos_prod'), 'pd.id_ped = p.id')
				->where('p.status = "ped" and p.sit = 0'.$where);
				
			$stmt = $db->query($select);
			$obj = $stmt->fetchAll();
		
			if(count($obj)>0){
				foreach ($obj as $custo);
				$custo = $custo->custototal;
			}
				
			
			echo $custo;
			
			//--- Busca Campanhas ------------------------------------------------------------------------------
			
			$admin = 0;
			$wheredesp .= " and p.id_financeiroplcontas in (154)";
			 
			$select = $db->select();
			$select->from(array('f'=>'tb_financeiropag','*'), array('sum(p.valor_apagar) as valortotal'))
				->join(array('p'=>'tb_financeiropagparc'),'f.id = p.id_financeiropag')
				->where("f.sit = true and p.sit = true ".$wheredesp);
	
			$stmt = $db->query($select);
			$obj = $stmt->fetchAll();
				
			if(count($obj)>0){
				foreach ($obj as $despesas);
				$admin = $despesas->valortotal;
			}
			
		
			//-- busca impostos pela emissao da nota ---------------------------------------------------------
			//--icms -----------------------------------------------------------------------------------------
			$icms = $pis = $cofins = $ipi = 0;
			$select = $db->select();
			$select->from(array('n'=>'tb_nfe','*'), array('sum(n.vlicms) as valortotal'))
				->join(array('p'=>'tb_pedidos'), 'p.id_nfe = n.id')
				->where("n.status = 1 and n.cfop not in (5916,6911,6915,6916,6501,6901,6949,7949) and n.tipo = 1 ".$where);
		
			$stmt = $db->query($select);
			$obj = $stmt->fetchAll();
			 
			if(count($obj)>0){
				foreach ($obj as $despesas);
				$icms = $despesas->valortotal;
			}
		
			//-- ipi ----------------------------------------------------------------------------------------
			$select = $db->select();
			$select->from(array('n'=>'tb_nfe','*'), array('sum(n.totalipi) as valortotal'))
				->join(array('p'=>'tb_pedidos'), 'p.id_nfe = n.id')
				->where("n.status = 1 and n.cfop not in (5916,6911,6915,6916,6501,6901,6949,7949) and n.tipo = 1 ".$where);
		
			$stmt = $db->query($select);
			$obj = $stmt->fetchAll();
			 
			if(count($obj)>0){
				foreach ($obj as $despesas);
				$ipi = $despesas->valortotal;
			}
				
			//-- cofins ----------------------------------------------------------------------------------------
			$select = $db->select();
			$select->from(array('n'=>'tb_nfe','*'), array('sum(n.totalcofins) as valortotal'))
				->join(array('p'=>'tb_pedidos'), 'p.id_nfe = n.id')
				->where("n.status = 1 and n.cfop not in (5916,6911,6915,6916,6501,6901,6949,7949) and n.tipo = 1 ".$where);
		
			$stmt = $db->query($select);
			$obj = $stmt->fetchAll();
			 
			if(count($obj)>0){
				foreach ($obj as $despesas);
				$cofins = $despesas->valortotal;
			}
			
			//-- pis ----------------------------------------------------------------------------------------
			$select = $db->select();
			$select->from(array('n'=>'tb_nfe','*'), array('sum(n.totalpis) as valortotal'))
				->join(array('p'=>'tb_pedidos'), 'p.id_nfe = n.id')
				->where("n.status = 1 and n.cfop not in (5916,6911,6915,6916,6501,6901,6949,7949) and n.tipo = 1 ".$where);
		
			$stmt = $db->query($select);
			$obj = $stmt->fetchAll();
			 
			if(count($obj)>0){
				foreach ($obj as $despesas);
				$pis = $despesas->valortotal;
			}
		
			//-- irpj e csll ----------------------------------------------------------------------------------------------
			$totalfaturamento = 0;
			$select = $db->select();
			$select->from(array('n'=>'tb_nfe','*'), array('sum(n.totalnota) as valortotal'))
				->join(array('p'=>'tb_pedidos'), 'p.id_nfe = n.id')
				->where("n.status = 1 and n.cfop not in (5916,6911,6915,6916,6501,6901,6949,7949) and n.tipo = 1 ".$where);
		
			$stmt = $db->query($select);
			$obj = $stmt->fetchAll();
			 
			if(count($obj)>0){
				foreach ($obj as $faturado);
				$totalfaturamento = $faturado->valortotal;
			}
		
		
			$lucroirpj = ($totalfaturamento*8)/100;
			$lucroirpj = ($lucroirpj*15)/100;
			 
			$lucroclss = ($totalfaturamento*12)/100;
			$lucroclss = ($lucroclss*9)/100;
		
		
			$tributaria = $icms+$ipi+$pis+$cofins+$lucroclss+$lucroirpj;
		
				
			?>
		    <style>		    			    	
		    	.red{
		    		color: #f00;
		    		text-align: right;
		    	}
		    	
				.total{
		    		text-align: right;
		    		font-weight: bold;
		    		font-size: 14px;
		    	}
		    	
		    	.cinza{
		    		background-color: #d5d5d5;
		    	}
		    	
		    	.text-right{
		    		text-align: right;
		    	}
		    </style>
		    
		    
		    	<div class="widget">
	            	<div class="head"><h5 class="iMoney">Resultado no período</h5></div>
	            	<table class="tableStatic" style="width: 100%">
                        <tbody>
                            <tr>
                                <td>Faturamento Líquido</td>
                                <td class="text-right"><?php echo number_format($vendas,2,",",".")?></td>
                            </tr>
                            <tr>
                                <td>Custo dos produtos</td>
                                <td class="red"><?php echo number_format($custo,2,",",".")." (".number_format(($custo*100)/$vendas,2,",",".").")"?>%</td>
                            </tr>
                            <tr>
                                <td>Despesas</td>
                                <td class="red"><?php echo number_format($admin+$tributaria,2,",",".")." (".number_format((($admin+$tributaria)*100)/$vendas,2,",",".").")"?>%</td>
                            </tr>
                            <tr>
                                <td>Resultado</td>
                                <td class="total"><?php echo number_format($vendas-($admin+$tributaria+$custo),2,",",".")." (".number_format((($vendas-($admin+$tributaria+$custo))*100)/$vendas,2,",",".").")"?>%</td>
                            </tr>
                        </tbody>
                    </table>  
		        </div>
		        
		        
		        
			    <div class="widget">
	            	<div class="head"><h5 class="iMoney">Percentuais</h5></div>
	            	<table class="tableStatic" style="width: 100%">
                        <tbody>
                            <tr>
                                <td>Markup</td>
                                <td class="text-right"><?php echo number_format((($vendas-$custo)/$custo)*100,2,",",".")?>%</td>
                            </tr>
                            <tr>
                                <td>Margem</td>
                                <td class="text-right"><?php echo number_format((($vendas-$custo)/$vendas)*100,2,",",".")."% (R$ ".number_format($vendas-$custo,2,",",".").")"; ?></td>
                            </tr>
                        </tbody>
                    </table>  
		       	</div>
		            
	            <div class="widget">
	            	<div class="head"><h5 class="iMoney">Despesas</h5></div>
	            	<table class="tableStatic" style="width: 100%">
                        <tbody>
                            <tr>
                                <td>Campanhas</td>
                                <td class="text-right"><?php echo number_format($admin,2,",",".")." (".number_format(($admin*100)/$vendas,2,",",".").")"?>%</td>
                            </tr>
                            <tr>
                                <td>Desp tributária</td>
                                <td class="text-right"><?php echo number_format($tributaria,2,",",".")." (".number_format(($tributaria*100)/$vendas,2,",",".").")"?>%</td>
                            </tr>
                        </tbody>
                    </table>  
		    	</div>
		            
		    <div class="clear"></div>
		 	<?php    			
		}
		
	}
?>
                              