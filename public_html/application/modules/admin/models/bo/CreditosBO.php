<?php
	class CreditosBO{				 
		//-- gerar credito apartir das garantias -----------------------------------------
		function gerarcreditoGarantia($params){
		    $bo			= new GarantiaModel();
		    $boprod 	= new GarantiaprodModel();
		    $boproddet	= new GarantiaproddetModel();
		    $bohis 		= new GarantiahistoricoModel();
		    $boc		= new CreditosModel();
		    
		    $usuario	= Zend_Auth::getInstance()->getIdentity();
		    		    
		    $vltotal	= 0;
		    $erro 		= false;
		    //-- listo os produtos detalhados --------------------------
		    foreach (GarantiasBO::listaProdgardetalhado($params['idgarantia']) as $produtosdet){
		        //--- verifico se foram marcados para credito --------------------------
		        if($params[$produtosdet->idt]){
		            //-- busco os produtos para verificaro valor ------------------------ 
		       		
		            if(count($boprod->fetchAll("id_garantiaztl = ".$params['idgarantia']." and id_prod = ".$produtosdet->idproduto)) > 0){
		            	foreach ($boprod->fetchAll("id_garantiaztl = ".$params['idgarantia']." and id_prod = ".$produtosdet->idproduto) as $produto);
		            	$vltotal += $produto->preco_nf;		            	
		            }else{
		                $erro = true;
		            }
		        }
		    }
		    
		    if($erro === false){
		        try{
			        //-- gravo credito -------------------------------
				    $data = array(
				    	'id_clientes'		=> $params['idcliente'],
				        'data'				=> date("Y-m-d"),
				        'id_autorizacao'	=> $usuario->id,
				        'id_garantia'		=> $params['idgarantia'],
				        'valor'				=> $vltotal
				    );
				    
				    $idcredito = $boc->insert($data);
				  	$dataprod = array('id_creditos' => $idcredito);
				  	
				  	//-- marco produtos da garantia pagos com credito --------------------------------------
				  	foreach (GarantiasBO::listaProdgardetalhado($params['idgarantia']) as $produtosdet){
				  		if($params[$produtosdet->idt]){
				  			$boproddet->update($dataprod, "id = ".$produtosdet->idt);
				  			
				  			
				  			$verbaixa = 0;
				  			foreach ($boproddet->fetchAll("id_garantiaztl = ".$params['idgarantia']) as $garantia):
					  			if(empty($garantia->id_nfeprod) and empty($garantia->id_creditos)):
					  				$verbaixa = 1;
					  			endif;
				  			endforeach;
				  			
				  			if($verbaixa==0):
					  			//--Atualizo historico da garantia-------------------------
					  			$arrayh['data']				= date("Y-m-d H:i:s");
					  			$arrayh['status']			= "FINALIZADO";
					  			$arrayh['id_garantiaztl']	= $params['idgarantia'];
					  			$arrayh['id_user']			= $usuario->id;
					  			$bohis->insert($arrayh);
					  				
					  			//--Atualiza garantia------------
					  			$arrayg['status']			= "FINALIZADO";
					  			$arrayg['data_atualizacao'] = date("Y-m-d H:i:s");
					  			$bo->update($arrayg,"id = ".$params['idgarantia']);
					  			
				  			else:
					  			foreach (GarantiasBO::listarGarantiascliente($produtos->id_garantiaztl) as $lista);
					  			if(strripos($lista->status, " PARCIAL")===false):
						  			//--Atualiza historico da garantia-------------------------
						  			$arrayh['data']				= date("Y-m-d H:i:s");
						  			$arrayh['status']			= "FINALIZAÇÃO PARCIAL";
						  			$arrayh['id_garantiaztl']	= $params['idgarantia'];
						  			$arrayh['id_user']			= $usuario->id;
						  			$bohis->insert($arrayh);
						  				
						  			//--Atualiza garantia------------
						  			$arrayg['status']			= "FINALIZAÇÃO PARCIAL";
						  			$arrayg['data_atualizacao'] = date("Y-m-d H:i:s");
						  			$bo->update($arrayg,"id = ".$params['idgarantia']);
					  			endif;
				  			endif;
				  			
				  			
				  		}
				  	}
				  	
				  	return true;
			  	
		        }catch (Zend_Exception $e){
		            $boerro	= new ErrosModel();
		            $dataerro = array('descricao' => $e->getMessage(), 'pagina' => 'CreditoBO::gerarcreditoGarantia()');
		            $boerro->insert($dataerro);
		            return false;
		        }
			  	
		    }else{
		        return false;
		    }
		    
		}	

		function listaCredito($params){
		    
		    try{
			    if($params['cliente']) $where = " and id_clientes = '".$params['cliente']."'";
			    
			    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			    $db->setFetchMode(Zend_Db::FETCH_OBJ);
			    	
			    $select = $db->select();
			    	
			    $select->from(array('c'=>'clientes'), array('c.EMPRESA','cr.*', 'DATE_FORMAT(cr.data,"%d/%m/%Y") as dtcad','cr.id as idcredito','fc.id_financeirorec', 'fc.id_financeiropag', 'cr.valor as valorcredito'))
			    	->join(array('cr'=>'tb_creditos'),'cr.id_clientes = c.ID')
			    	->joinLeft(array('fc'=>'tb_financeirocredito'),'cr.id = fc.id_creditos')
			    	->where("cr.sit = true ".$where)
			    	->order('cr.id desc');
			    				    	
			    $stmt = $db->query($select);
			    
			    return $stmt->fetchAll();
			    
			    
		    }catch (Zend_Exception $e){
		    	$boerro	= new ErrosModel();
		    	$dataerro = array('descricao' => $e->getMessage(), 'pagina' => 'CreditoBO::listaCredito()');
		    	$boerro->insert($dataerro);
		    	return false;
		    }
		}
		
		/* $tp = "" - Busca creditos nao utilizados ---------
		 * $tp = 1  - Busca creditos utilizados ----------------		 * 
		 * */
		
		function buscaCredito($params, $tp=""){
		
			try{
				if(isset($params['cliente']) and $params['cliente']) 	$where = " and cr.id_clientes = '".$params['cliente']."'";
				if(isset($params['rec']) and $params['rec']) 			$where = " and md5(c.id_financeirorec) = '".$params['rec']."'";
				if(isset($params['pay']) and $params['pay']) 			$where = " and md5(c.id_financeiropag) = '".$params['pay']."'";
				if(isset($params['credito']) and $params['credito']) 	$where = " and cr.id  = '".$params['credito']."'";
				
				$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
				$db->setFetchMode(Zend_Db::FETCH_OBJ);
		
				$select = $db->select();
				
				if($tp == 1){
				    $select->from(array('cr'=>'tb_creditos'), array('cr.*', 'DATE_FORMAT(cr.data,"%d/%m/%Y") as dtcad','cr.id as idcredito','cr.valor as valorcredito'))
					    ->join(array('c'=>'tb_financeirocredito'),'cr.id = c.id_creditos')
					    ->where("cr.sit = true".$where)
					    ->order('cr.id');
				}else{
					$select->from(array('cr'=>'tb_creditos'), array('cr.*', 'DATE_FORMAT(cr.data,"%d/%m/%Y") as dtcad','cr.id as idcredito','cr.valor as valorcredito'))
						->joinLeft(array('c'=>'tb_financeirocredito'),'cr.id = c.id_creditos')
						->where("cr.sit = true and c.id is NULL ".$where)
						->order('cr.id');
				}
		
				$stmt = $db->query($select);
				return $stmt->fetchAll();
				 
				 
			}catch (Zend_Exception $e){
				$boerro	= new ErrosModel();
				$dataerro = array('descricao' => $e->getMessage(), 'pagina' => 'CreditoBO::listaCredito()');
				$boerro->insert($dataerro);
				return false;
			}
		}
		
	
	function cancelarCreditos($params){
		$bo			= new GarantiaModel();
		$boprod 	= new GarantiaprodModel();
		$boproddet	= new GarantiaproddetModel();
		$bohis 		= new GarantiahistoricoModel();
		$boc		= new CreditosModel();
	
		$usuario	= Zend_Auth::getInstance()->getIdentity();
	
		
		try{
			foreach ($boproddet->fetchAll("id_creditos = ".$params['credito']) as $produtos){
			    $data = array('id_creditos' => NULL);
			    $boproddet->update($data, "id = ".$produtos->id);
			    
			    $verbaixa = 0;
			    foreach ($boproddet->fetchAll("id_garantiaztl = ".$produtos->id_garantiaztl) as $garantia):
				    if($garantia->id_nfeprod != NULL || $garantia->id_creditos != NULL) $verbaixa = 1;
			    endforeach;
			    
			    if($verbaixa==0):
					//--Atualizo historico da garantia-------------------------
					$arrayh = array(
						'data'			=> date("Y-m-d H:i:s"),
						'status'		=> "ANÁLISE CONCLUIDA",
						'id_garantiaztl'=> $produtos->id_garantiaztl,
						'id_user'		=> $usuario->id
					);
						
					$bohis->insert($arrayh);
					 
					//--Atualiza garantia------------
					$arrayg = array('status' => "ANÁLISE CONCLUIDA", 'data_atualizacao'	=> date("Y-m-d H:i:s"));
					$bo->update($arrayg,"id = ".$produtos->id_garantiaztl);
	
				else:
					//--Atualizo historico da garantia-------------------------
					$arrayh = array(
						'data'			=> date("Y-m-d H:i:s"),
						'status'		=> "FINALIZAÇÃO PARCIAL",
						'id_garantiaztl'=> $produtos->id_garantiaztl,
						'id_user'		=> $usuario->id
					);
						
					$bohis->insert($arrayh);
						
					//--Atualiza garantia------------
					$arrayg = array('status' => "FINALIZAÇÃO PARCIAL", 'data_atualizacao'	=> date("Y-m-d H:i:s"));
					$bo->update($arrayg,"id = ".$produtos->id_garantiaztl);
					
				endif;
			    
			}
			
			$datacredito = array('sit' => false);
			$boc->update($datacredito, "id = ".$params['credito']);
		
			return true;
		
		}catch (Zend_Exception $e){
			$boerro	= new ErrosModel();
			$dataerro = array('descricao' => $e->getMessage(), 'pagina' => 'CreditoBO::cancelarCredito()');
			$boerro->insert($dataerro);
			return false;
		}
	
	
	}
}
	
?>
