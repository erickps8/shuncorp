<?php
	class EstadosBO{		
	    /* Usado em
	     * despesasfiscaisncm ------------------ 
	     * */
		function listarEstados($params){
			$obj = new EstadosModel();
			if(empty($params)): 
				return $obj->fetchAll();		
			else: 
				return $obj->fetchAll("ID = ".$params);
			endif;
		}
		
		function listarEstadosChina(){
			$ob  = new EstadosModel();
			$obj = new EstadosChinaModel();
			return $obj->fetchAll("id > 0","nome asc");			
		}
		
		function buscaEstados($pais){
			$obj = new EstadosModel();
			return $obj->fetchAll("id_paises = '".$pais."'",'nome');			
		}
		
		/*--
		 * Usado em 
		 * despesasfiscaisufAction--
		 * despesasfiscaiscadAction--
		 * */
		function buscaEstadosid($params){
			$obj = new EstadosModel();
			return $obj->fetchAll("md5(id) = '".$params['iduf']."'");			
		}
		
		function buscaEstadosuf($params){
			$obj = new EstadosModel();
			return $obj->fetchAll("uf = '".$params['uf']."'",'nome');			
		}
		
		function buscaEstadosmd($params){
			$obj = new EstadosModel();
			return $obj->fetchAll("md5(id_paises) = '".$params."'",'nome');			
		}
		
		function buscaUFporid($id){
			$obj = new EstadosModel();
			foreach ($obj->fetchAll("id = '".$id."'") as $uf);
			return $uf->uf;
		}
		
		
		
		function cadastraEstados($var){
			$bo		= new EstadosModel();
			$usuario = Zend_Auth::getInstance()->getIdentity();
			
			$array['nome']		= $var['estado'];
			$array['id_paises']	= $var['idpais'];
			$array['uf']		= $var['uf'];
			$array['codigo']	= $var['codigo'];
			
			if(empty($var['idestado'])):
				$id = $bo->insert($array);	
				LogBO::cadastraLog("Cadastro/Estado",2,$usuario->ID,$id,"ESTADO ID ".$id);	
			else:
				$bo->update($array, "id = ".$var['idestado']);
				LogBO::cadastraLog("Cadastro/Estado",4,$usuario->ID,$var['idestado'],"ESTADO ID ".$var['idestado']);	
			endif;
		}
		
		//--Paises---------------------------------
		function listaPaises(){
			$obj = new PaisesModel();
			return $obj->fetchAll('id > 0','nome asc');			
		}
		
		function buscaPaises($var){
			$obj = new PaisesModel();
			return $obj->fetchAll("md5(id) = '".$var['idpais']."'");			
		}
		
		function cadastraPais($var){
			$bo		= new PaisesModel();
			$usuario = Zend_Auth::getInstance()->getIdentity();
			
			$array['nome']	= $var['pais'];
			
			if(empty($var['idpais'])):
				$id = $bo->insert($array);
				LogBO::cadastraLog("Cadastro/Pais",2,$usuario->ID,$id,"PAIS ID ".$id);	
			else:
				$bo->update($array, "id = ".$var['idpais']);
				LogBO::cadastraLog("Cadastro/Pais",4,$usuario->ID,$var['idpais'],"PAIS ID ".$var['idpais']);	
			endif;
		}
		
		//--Cidades--------------------------------
		function buscaCidades($var){
			$obj = new CidadesModel();
			return $obj->fetchAll("uf = '".$var['uf']."'",'nome');				
		}
		
		function buscaCidadesid($var){
			$obj = new CidadesModel();
			return $obj->fetchRow("id = '".$var['cidade']."'");
		}
		
		function buscaCidadesuf($var){
			$obj = new CidadesModel();
			return $obj->fetchAll("uf = '".$var."'",'nome');				
		}
		
		function listaCidades(){
			$obj = new CidadesModel();
			return $obj->fetchAll();				
		}
		
		function buscaCidadesidestado($var){
			$obj = new CidadesModel();
			if($var!=""):
				return $obj->fetchAll("id_estados = ".$var,'nome');
			endif;		
		}
		
		
		function buscaCidadesidestadotemporario($var){
			
		    if(!empty($var)):
				$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
				$db->setFetchMode(Zend_Db::FETCH_OBJ);
					
				$select = $db->select();
					
				$select->from(array('c'=>'cidade','*'), array('c.*'))
					->join(array('cf'=>'contatos_emp_filial'), 'cf.cidade = c.idcidade')
					->where("ver = 0 and idestado = ".$var,'nome')
					->group('c.idcidade')			
					->order('c.nome');
					
				$stmt = $db->query($select);
				return $stmt->fetchAll();
			endif;
		}
		
		function corrigeCidadecontatos($params){
		    $bo		= new CidadesModel();
		    $boc	= new CidadesantModel();
		    $bocc	= new ContatosModel();
		    $boce	= new ContatosempModel();
		    $bocn 	= new ContatosnovoModel();
		    $bocf	= new ContatosfilialModel();
		    
		    if(!empty($params['cidade']) and !empty($params['cidant'])):
			    $data = array("cidade" => $params['cidade'],"ver" => 1);
			    $boce->update($data, "ver = 0 and cidade = ".$params['cidant']);
			    $bocn->update($data, "ver = 0 and cidade = ".$params['cidant']);
			    $bocf->update($data, "ver = 0 and cidade = ".$params['cidant']);
			    
			    //$boc->delete("idcidade = ".$params['cidant']);
			    
			    echo "Atualizado com sucesso!";
			    
			endif;  
			
		    
		}
		
		
		
		function cadastraCidades($var){
			$bo		= new CidadesModel();
			$usuario = Zend_Auth::getInstance()->getIdentity();
			
			$array['nome']			= $var['cidade'];
			$array['id_estados']	= $var['idestado'];
			$array['codigo']		= $var['codigo'];
			$array['uf']	 		= $var['uf'];
			
			if(empty($var['idcidade'])):
				$id = $bo->insert($array);	
				LogBO::cadastraLog("Cadastro/Cidade",2,$usuario->ID,$id,"PAIS ID ".$id);	
			else:
				$bo->update($array, "id = ".$var['idcidade']);
				LogBO::cadastraLog("Cadastro/Cidade",4,$usuario->ID,$var['idcidade'],"PAIS ID ".$var['idcidade']);
			endif;
		}		
		
		function listaCidadescod(){
			$obj = new CidadesModel();
			$bp	 = new CidadescodModel();
			return $bp->fetchAll();				
		}		
			
	}
?>