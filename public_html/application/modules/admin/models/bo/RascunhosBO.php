<?php
	class RascunhosBO{		
				
		public function listaProdutosvendas($cat){
								
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('p'=>'produtos','*'),
			        array('p.ID as id_prod','p.CODIGO','p.custo_shuntai','c.EMPRESA','g.NOME'))
			        ->join(array('c'=>'clientes'),'p.id_cliente_shuntai = c.ID')
			        ->join(array('g'=>'grupos'),'p.ID_GRUPO = g.ID')
			        ->where("c.ID = ".$cat['fornecedor'])
			        ->order('p.codigo_mask','');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
								
		}
		
		public function listaPedidos(){
					
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_rascunho','*'),
			        array('t.id_racunho','t.status','DATE_FORMAT(t.data, "%d/%m/%Y") as data','t.sit','t.obs'))
			        ->where("t.sit = true")
			        ->order('t.id_racunho desc','');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();	
			
		}
		
		//--Grava pedido-------
		public function gravaPedido($params){
			$tai 		= new RascunhosModel();
			$taiprod 	= new RascunhosprodModel(); 
			$array['status']		= "O";
			$array['data']			= date("Y-m-d H:i:s");
			$array['sit']			= true;
			$array['obs']			= $params["obs"];
						
			$idcli = $tai->insert($array);
			
			foreach(ProdutosBO::listaallProdutos() as $listprod):
				if(!empty($params[$listprod->ID])):
					$arrayprod['id_rascunho']		= $idcli;
					$arrayprod['id_prod']			= $listprod->ID;
					$arrayprod['qt']				= $params[$listprod->ID];
					$arrayprod['preco']				= $params["valor_".$listprod->ID];
					$taiprod->insert($arrayprod);
				endif;
			endforeach;
			
			LogBO::cadastraLog("Kang/Gerar rascunho",2,$_SESSION['S_ID'],$idcli,'Racunho D'.substr("000000".$idcli,-6,6));
						
		}
				
		//--Fecha pedido-------
		public function removerRascunho($params){
			$tai 		= new RascunhosModel();
			 
			$array['sit']		= false;
			$tai->update($array,"id_racunho = ".$params);
			
			LogBO::cadastraLog("Kang/Remover rascunho",3,$_SESSION['S_ID'],$params,'Rascunho D'.substr("000000".$params,-6,6));
		}

		//--Edita rascunho-------
		public function editstatusRascunho($params){
			$tai 		= new RascunhosModel();
			 
			$array['status']		= "C";
			$tai->update($array,"id_racunho = ".$params['st']);
			
			LogBO::cadastraLog("Kang/Fechar rascunho",4,$_SESSION['S_ID'],$params,'Rascunho D'.substr("000000".$params['st'],-6,6));
		}
		
		//--Lista produtos pedidos------------------
		public function listaProdutoscompra($idfor){
								
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('p'=>'produtos','*'),
			        array('p.ID as id_prod','p.CODIGO','t.qt','t.preco','p.DESCRICAO'))
			        ->join(array('t'=>'tb_rascunhoprod'),'t.id_prod = p.ID')
			        ->where("t.id_rascunho = ".$idfor)
			        ->order('p.codigo_mask','');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
								
		}
		
		
		
	}
?>