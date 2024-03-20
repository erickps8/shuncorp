<?php
	class GruposprodBO{		
		public function listaGrupos(){
			$obj = new GruposprodModel();
			return $obj->fetchAll();			
		}
		
		public function listaGruposcompra(){
			$ob  = new GruposprodModel();
			$obj = new GruposcompraModel();
			return $obj->fetchAll("id is not NULL","purchasing asc");			
		}
		
		public function listaGruposprodutos(){
			$ob 	= new GruposprodModel();
			$obj	= new GruposprodutosModel();
			return $obj->fetchAll("id is not NULL","descricao asc");			
		}
		
		function listaGruposvenda(){
			$ob 	= new GruposprodModel();
			$obj	= new GruposprodutosModel();
			return $obj->fetchAll("tipo = 1","descricao asc");
		}
		
		function buscaGruposvenda($desc){
			$ob 	= new GruposprodModel();
			$obj	= new GruposprodutosModel();
			return $obj->fetchRow("descricao = '".$desc."'");
		}
		
		function buscaSubgruposvenda($idgrupo, $desc){
			$ob 	= new GruposprodModel();
			$obj	= new GruposprodutossubModel();
			return $obj->fetchRow("descricao = '".$desc."' and id_gruposprod = '".$idgrupo."'");
		}
		
		public function listaGruposprodutossub($params=""){
			$ob 	= new GruposprodModel();
			$obj	= new GruposprodutossubModel();
			if(!empty($params)) $where = "id_gruposprod = ".$params;
			return $obj->fetchAll($where);			
		}
		
		public function buscaSubgrupo($params,$tp = ""){
			$ob 	= new GruposprodModel();
			$obj	= new GruposprodutossubModel();
			if($tp == 2){
				return $obj->fetchAll("tipo = 1 and sit = 1 and id_gruposprod = ".$params['idgrupo']); 
			}else{
				if(!empty($params)) $where = "id = ".$params;
				return $obj->fetchAll($where,"descricao asc");
			}			
		}
		
		public function gravarGrupo($params){
			$ob 	= new GruposprodModel();
			$obj	= new GruposprodutosModel();
			
			$array['descricao'] 	= strtoupper($params['grupo']);
			$array['sit']	= true;
			if(empty($params['idgrupo'])):
				 $obj->insert($array);
			else:
				$obj->update($array,"id = ".$params['idgrupo']);
			endif;
		}
		
		public function gravarSubgrupo($params){
			$ob 	= new GruposprodModel();
			$obj	= new GruposprodutossubModel();
			
			$array['descricao'] 	= strtoupper($params['subgrupo']);
			$array['id_gruposprod'] = $params['grupo'];
			$array['sit']			= true;
			
			if(empty($params['idsubgrupo'])):
				 $obj->insert($array);
			else:
				$obj->update($array,"id = ".$params['idsubgrupo']);
			endif;
		}
		
		public function gravarGruposcompra($params){
			$ob 	= new GruposprodModel();
			$obj	= new GruposcompraModel();
			
			$array['purchasing'] 	= strtoupper($params['grupo']);
			
			if(empty($params['idgrupo'])):
				 $obj->insert($array);
			else:
				$obj->update($array,"id = ".$params['idgrupo']);
			endif;
		}
		
		function listaSubgrupos($pesq=""){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('s'=>'tb_gruposprodsub','*'), array('s.descricao as subgrupo','s.id as idsub','g.descricao as grupo','g.id as idgrupo'))
			        ->join(array('g'=>'tb_gruposprod'), 'g.id = s.id_gruposprod')
			        ->order("g.descricao","s.descricao");
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
								
		}
		
		public function listaGruposfornecedor($pesq){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('s'=>'tb_purchasing','*'),
			        array('s.id as idgrupo','s.purchasing as grupo'))
			        ->join(array('p'=>'produtos'), 'p.Purchasing_group = s.id')
			        ->where("p.ID_CLIENTE_FORNECEDOR = ".$pesq)
			        ->order("s.purchasing")
			        ->group("s.id");
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
								
		}
	}
?>