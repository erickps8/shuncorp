<?php
	class RegioesBO{		
		function listaRegioes(){
			$obj = new RegioesModel();
			return $obj->fetchAll();			
		}
		
		function listaRegioesclientes($tp=0){
		    $bo			= new RegioesModel();
		    $bor		= new RegioesclientesModel();
		    $usuario 	= Zend_Auth::getInstance()->getIdentity();
		    $where 		= "";
		    
		    //-- Busca regioes do perfil nivel regional ----------------------------------------------------
		    if(isset($usuario->id_perfil)){
		        foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
			    if($list->nivel == 1):
				    $reg = "";
				    foreach ($bor->fetchAll("id_clientes = ".$usuario->id) as $regioes):
				    	$reg .= $regioes->id_regioes.",";
				    endforeach;
				    
				    if($reg != ""):
				    	$where = " and c.ID in (".substr($reg,0,-1).")";
				    elseif($tp==0):
				    	$where = " and c.ID = 0";
				    endif;
			    endif;
		    }else{
		        $where = " and c.ID = 0"; 
		    }
		    
		    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			
			$select->from(array('c'=>'clientes_regioes','*'), array('*','c.id as idreg','cl.ID as id_clientes'))
			        ->joinLeft(array('cl'=>'clientes'),'c.id_usuarios = cl.ID')
			        ->where("c.sit = true ".$where)
			        ->order('c.id');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		    
		}
		
		function gravaRegioes($var){
			$bo		= new RegioesModel();
			
			$rep = ($var['representante'] == 0) ? NULL : $var['representante'];
			
			$array['NOME']			= $var['regiao'];
			$array['id_usuarios']	= $rep;
			$array['comissao']		= str_replace(",", ".",str_replace(".", "",$var['comissao']));
			$array['sit']			= true;
			
			if(!empty($var['idregiao'])):
				$bo->update($array, "id = ".$var['idregiao']);
			else:
				$bo->insert($array);
			endif;			
			
		}
		
		function removeRegioes($var){
			$bo		= new RegioesModel();
			$array['sit']			= false;
			$bo->update($array, "md5(id) = '".$var['regiao']."'");			
		}
		
		
		//--- busca regioes por ID ---------------------------------------------
		function buscaRegioesrep($params){
		    $bo		= new RegioesModel();
		    return $bo->fetchAll('ID = "'.$params.'"');
		    
		}
		
		function buscaRegioestvendas($params){
			$bor		= new RegioesModel();
			$bo			= new RegioestelevendasModel();
			
			return $bo->fetchAll('id = "'.$params.'"');		
		}
		
		
		
		function buscaRegioestelevendas(){
			$bo		= new RegioesModel();
			$bor	= new RegioesclientestelevendasModel();
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			$where = "";
			
			//-- Busca regioes do perfil nivel regional ----------------------------------------------------
			foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
			if($list->nivel == 1):
				$reg = "";
				foreach ($bor->fetchAll("id_usuarios = ".$usuario->id) as $regioes):
					$reg .= $regioes->id_regioes.",";
				endforeach;
				 
				if($reg != ""):
					$where = " and c.id in (".substr($reg,0,-1).")";
				endif;
			endif;
		
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
				
			$select->from(array('c'=>'tb_regioestelevendas','*'), array('cl.id as iduser','c.nome as regiao','cl.nome as usuario','c.comissao','c.id as idreg','c.comissaorep'))
				->joinLeft(array('cl'=>'tb_usuarios'),'c.id_usuarios = cl.id')
				->where("c.sit = true ".$where)
				->order('c.id');
				
			$stmt = $db->query($select);
			return $stmt->fetchAll();
				
		}
		
		function gravaRegioestelevendas($var){
		    $bor	= new RegioesModel();
			$bo		= new RegioestelevendasModel();
				
			$rep = ($var['representante'] == 0) ? NULL : $var['representante'];
			
			$array['nome']			= $var['regiao'];
			$array['id_usuarios']	= $rep;
			$array['comissao']		= str_replace(",", ".",str_replace(".", "",$var['comissao']));
			$array['comissaorep']	= str_replace(",", ".",str_replace(".", "",$var['comissaorep']));
			$array['sit']			= true;
				
			if(!empty($var['idregiao'])):
				$bo->update($array, "id = ".$var['idregiao']);
			else:
				$bo->insert($array);
			endif;
				
		}
		
		function removeRegioestelevendas($var){
		    $bor	= new RegioesModel();
			$bo		= new RegioestelevendasModel();
			$array['sit']			= false;
			$bo->update($array, "md5(id) = '".$var['regiao']."'");
				
		}
		
		/*
		 * @params['usermd'] 
		 * */
		
		function listaRegioesuser($params, $tp=""){
			$bo		= new RegioesModel();
			$bor	= new RegioesclientesModel();
			$bot	= new RegioesclientestelevendasModel();
			
			foreach (UsuarioBO::buscaUsuario($params) as $user);
			
			if($tp == 1){
			    return $bot->fetchAll("md5(id_usuarios) = '".$params['usermd']."'");				
			}else{
			    return $bor->fetchAll("md5(id_usuarios) = '".$params['usermd']."'");
			}
		}
		
		
		function buscaRegioesusuariolog(){
		    $usuario 	= Zend_Auth::getInstance()->getIdentity();
		    $bo		= new RegioesModel();
		    $bor	= new RegioesclientesModel();
		    $bot	= new RegioesclientestelevendasModel();
		    	
		    $reg = "";
		    if($usuario->id_perfil == 31): //-- perfil 31 - Televenda --------------------------------
		    	foreach ($bot->fetchAll("id_usuarios = ".$usuario->id) as $regioes):
			    	$reg .= $regioes->id_regioes.",";
			    endforeach;			    		    
		    else:
		   		foreach ($bor->fetchAll("id_usuarios = ".$usuario->id) as $regioes):
			    	$reg .= $regioes->id_regioes.",";
			    endforeach;
		    endif;
		    
		    if(empty($reg)) return '10000';
		    else return substr($reg,0,-1);
		    
		}
		
		function listaRegioesusuarios($tp=""){
			$bo			= new RegioesModel();
			$bor		= new RegioesclientesModel();
			$bot		= new RegioesclientestelevendasModel();
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
				
			$reg = "";
			if($tp == 1){
				foreach ($bot->fetchAll("id_usuarios = ".$usuario->id) as $regioes):
					$reg .= $regioes->id_regioes.",";
				endforeach;				
			}else{
				foreach ($bor->fetchAll("id_usuarios = ".$usuario->id) as $regioes):
			    	$reg .= $regioes->id_regioes.",";			    
				endforeach;			    
			}
			
			if(empty($reg)) return '10000';
			else return substr($reg,0,-1);
		}
		
	}
?>