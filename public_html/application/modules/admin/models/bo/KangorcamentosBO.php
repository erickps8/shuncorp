<?php
	class KangorcamentosBO{		

		//--- Orcamentos ------------------------------------
		function gerarOrcamento($var){
			$bov	= new KangvendasModel();
			$bop	= new KangorcamentosModel();
			$usuario	= Zend_Auth::getInstance()->getIdentity();
			
			$array['id_user']			= $usuario->ID;
			$array['id_cliente'] 		= $var['buscacli'];
			$array['dt_atualizacao'] 	= date("Y-m-d");
			$array['status']   			= 1;
			
			$id = $bop->insert($array);
			
			LogBO::cadastraLog("Kang/Orçamento Venda",3,$usuario->ID,$id,$id);
			
			return  $id;
		}
		
		//-- Importar ped -----------------------------------------------
		function importacaoPedido($var){
			$bov	= new KangvendasModel();
			$bop	= new KangorcamentosModel();
			$bopo	= new KangorcamentosprodModel();
			$usuario	= Zend_Auth::getInstance()->getIdentity();
			
         	$arquivo = isset($_FILES['arquivo']) ? $_FILES['arquivo'] : FALSE;
	        $pasta = Zend_Registry::get('pastaPadrao')."public/importpedkang/";
			 	     
			if (!(is_dir($pasta))){
				if(!(mkdir($pasta, 0777))){
                   	echo ("Alerta: pasta de upload nao existe, e nao pode ser criada");
                   	echo $pasta;
                	return $this;                           
                 }
            }
                   
            if(!(is_writable($pasta))){
             	echo ("Alerta: pasta sem permissao de escrita");
                return $this;                   
            }
			 				 
			if(is_uploaded_file($arquivo['tmp_name'])){                                
                  if (move_uploaded_file($arquivo["tmp_name"], $pasta . "pedtmp.xml")) {
                  		
                  } else {
                        echo ("Alerta: Nao foi possivel fazer o upload para $pasta");
                        return $this;                                           
                  }                               
            }else{
	             //echo "erro ao carregar arquivo";
            }
	         
            $xml = simplexml_load_file(Zend_Registry::get('pastaPadrao')."public/importpedkang/pedtmp.xml");
			
			$array['id_user']			= $usuario->ID;
			$array['id_cliente'] 		= '662';
			$array['dt_atualizacao'] 	= date("Y-m-d H:m");
			$array['dtentrega'] 		= $xml->dataentrega;
			$array['pedidocli'] 		= $xml->pedidocli;
			$array['parcial']	 		= $xml->parcial;
			$array['status']   			= 1;
			
			$idorc = $bop->insert($array);
			
			foreach ($xml as $listimp):	
				foreach($listimp->produto as $lp):		   			
		   			//--Verifico se produtos pedidos existem-----------------------------
					foreach (ProdutosBO::buscaProdutoscodigo($lp->cod) as $produto);
					if(!empty($produto)):
						$arrayprod['id_prod']			= $produto->ID;
						$arrayprod['qt']				= $lp->qt;
						$arrayprod['preco_ut']			= $lp->preco;
						$arrayprod['id_pedido_tmp']		= $idorc;
						$arrayprod['moeda']				= $lp->moeda;

						$bopo->insert($arrayprod);
					endif;
		   		endforeach;  
		   		
		    endforeach;	

			return $idorc; 
		}
		
		//-- Usado em orcamentosAction ------------------------------
		function listaOrcamentos(){
		
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
		
			$select = $db->select();
		
			$select->from(array('t'=>'tb_kang_pedidos_tmp','*'),
					array('t.id as idped','DATE_FORMAT(t.dt_atualizacao,"%d/%m/%Y %H:%i" ) as dtvenda','c.EMPRESA','t.status'))
					->join(array('c'=>'clientes'),'t.id_cliente = c.ID')
					->where("t.status = 1");
		
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		
		}
		
		/*--- Usado em orcamentosnovodetAction ----------------- 
		 * */
		function buscaOrcamentos($params){
			$usuario	= Zend_Auth::getInstance()->getIdentity();
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('p'=>'tb_kang_pedidos_tmp','*'), array('*','p.id as idped','DATE_FORMAT(p.dtentrega,"%d/%m/%Y" ) as dt_entrega'))
			->join(array('c'=>'clientes'),'p.id_cliente = c.ID')
			->joinLeft(array('ck'=>'tb_clientes_infokang'),'ck.id_cliente = c.ID')
			->where("p.status = 1 and md5(p.id) = '".$params['orc']."'");
			
			$stmt = $db->query($select);
			return $stmt->fetchAll();
			
		}
		
		/*--- Usado em orcamentosnovodetAction -----------------
		 * */
		function buscaOrcamentosprod($params){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('p'=>'tb_kang_pedidos_tmp_prod','*'), array('*','p.id as idprodtmp','p.id_produtosclasses as idclasse'))
			        ->join(array('pr'=>'produtos'),'p.id_prod = pr.ID')
			        ->joinLeft(array('c'=>'tb_produtosclasses'),'c.id = p.id_produtosclasses')
			        ->where("md5(id_pedido_tmp) = '".$params['orc']."'");			        
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		function gravarprodOrcamento($params){
			$bo		= new KangvendasModel();
			$bot	= new KangorcamentosprodModel();
			
			foreach (ProdutosBO::buscaProdutoscodigo($params['codigo']) as $produto);
			
			if(count($produto)>0):
				$data	= array(
					'id_prod'		            => $produto->ID,
					'qt'			            => $params['qt'],
					'id_pedido_tmp'	            => $params['ped'],
					'preco_ut'		            => str_replace(",", ".", str_replace(".", "", $params['preco'])),
					'moeda'			            => 'USD',
					'cod_cli'		            => $params['codigocli'],
				    'id_produtosclasses'        => (!empty($params['classe']) and $params['classe'] != 0) ? $params['classe'] : null,
				);
				Zend_Debug::dump($data);
			
				$bot->insert($data);
			endif;
		}
		
		function removeprodOrcamento($params){
			$bo		= new KangvendasModel();
			$bot	= new KangorcamentosprodModel();

			$bot->delete("id = ".$params['prodorc']);			
		}
		
		function removeOrcamento($params){
			$bo		= new KangvendasModel();
			$boo	= new KangorcamentosModel();
			
			$data = array('status' => false);
			$boo->update($data, "md5(id) = '".$params['orc']."'");
		}
		
		
		
		function gerarPedidovenda($params){
			$bo		= new KangvendasModel();
			$bop	= new VendasprodModel();
			$boo	= new KangorcamentosModel();
			$bot	= new KangorcamentosprodModel();
		
			foreach (KangorcamentosBO::buscaOrcamentos($params) as $orcamento); 
		
			if(count($orcamento) > 0):
				$data	= array(
					'DATA'				=> date("Y-m-d H:i:s"),
					'STATUS'			=> "ORDERED",
					'OBS'				=> $params['obs'],
					'EMPRESA'			=> $orcamento->id_cliente,
					'sit'				=> 1,
					'dt_entrega'		=> substr($params['dataent'],6,4).'-'.substr($params['dataent'],3,2).'-'.substr($params['dataent'],0,2),
					'you_order'			=> $params['you_order'],
					'defrom'			=> $params['defrom'],
					'para'				=> $params['para'],
					'freight'			=> $params['freight'],
					'payment'			=> $params['payment'],
					'partial_shipment'	=> $params['partial_shipment'],
					'shipment_agent'	=> $params['shipment_agent'],
			        'frete'	          	=> str_replace(",", ".", str_replace(".", "", $params['frete'])),
				);
				
				$id	= $bo->insert($data);
			
				foreach (KangorcamentosBO::buscaOrcamentosprod($params) as $prod):				
					$dataprod = array(
						'ID_PEDIDO'		        => $id,
						'ID_PRODUTO'	        => $prod->id_prod,
						'QT'			        => $prod->qt,
						'PRECO'			        => $prod->preco_ut,
						'MOEDA'			        => $prod->moeda,
						'COD_PRODCLI'	        => $prod->cod_cli,
				        'extra'	                => $prod->extra,
				        'id_produtosclasse'     => $prod->idclasse,
						'comprado'		        => 0
					);
										
					$bop->insert($dataprod);
				endforeach;
				
				$dataorc = array('status' => 0);
				$boo->update($dataorc, "md5(id) = '".$params['orc']."'");
				
			endif;
			
			return $id;
		}
		
		function removeOR($pedido){
		    $bo		= new KangvendasModel();
		    $bo->update(array('sit' => false), "ID = '".$pedido."'");
		}
	}
?>
