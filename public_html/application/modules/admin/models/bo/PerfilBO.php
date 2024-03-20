<?php
	class PerfilBO{		
		
		public function cadastraPerfil($params){
			$boper 	= new PerfilModel();
			$boac	= new PerfilacessoModel();
			$boMenu = new MenuBO();	
			$usuario	= Zend_Auth::getInstance()->getIdentity();

			$array['garantias']     			= $params['garantias'];
			$array['pedidos']     				= $params['pedidos'];
			$array['logs']  					= $params['logs'];
			$array['financeiro']     			= $params['financeiro'];
			$array['faturamento']   			= $params['faturamento'];
			$array['atividades']     			= $params['atividades'];
			$array['compras']     				= $params['compras'];
			
			$array['interno']	     			= $params['interno'];
			
			if(empty($params["id_perfil"])){
				
				$array['dt_cadastro']  		= date("Y-m-d H:i:s");
				$array['descricao']   		= $params['perfil'];
				$array['nivel']	   			= $params['nivel'];
				$array['sit']     			= $params['sit'];
				
				$idperf = $boper->insert($array);
				
				LogBO::cadastraLog("ADM/Perfil",2,$_SESSION['S_ID'],$idperf,"PERFIL ID ".$idperf);
				
			}else{
				$idperf 					= $params["id_perfil"];
				$array['descricao']   		= $params['perfil'];
				$array['nivel']	   			= $params['nivel'];
				$array['sit']     			= $params['sit'];
				
				
				$boper->update($array,"id = ".$idperf);
				
				if($params['sit']==1) $sit = 4;
				elseif($params['sit']==0) $sit = 3;
				LogBO::cadastraLog("ADM/Perfil",$sit,$_SESSION['S_ID'],$idperf,"PERFIL ID ".$idperf);
			}
			
			$boMenu->listarMenu("");
			
			foreach ($boMenu->listarSubmenuperfil() as $list){
				$boac->delete("id_perfil = ".$idperf." and id_menu_sub = ".$list->id);
			    /*  foreach ($boac->fetchAll("id_perfil = ".$idperf." and id_menu_sub = ".$list->id) as $listaacesso);
			    if(count($listaacesso)>0):
			    	$array_a['analise_garantia']    = $listaacesso->analise_garantia;
			    	$array_a['saldo_neg']    		= $listaacesso->saldo_neg;
			    	$array_a['aba1']    			= $listaacesso->aba1;
			    	$array_a['aba2']    			= $listaacesso->aba2;
			    	$array_a['aba3']    			= $listaacesso->aba3;
			    	$array_a['aba4']    			= $listaacesso->aba4;
			    	$array_a['aba5']    			= $listaacesso->aba5;
			    	
			    	$boac->delete("id_perfil = ".$idperf." and id_menu_sub = ".$list->id);
			    endif; */
			    
				$ver = 0;
				
				$array_a = array();
				$array_a['aba1']    = false;
				$array_a['aba2']    = false;
				$array_a['aba3']    = false;
				$array_a['aba4']    = false;
				$array_a['aba5']    = false;
				
				if($params["ins_".$list->id]==1){ 
					$array_a['inserir']    = true;
					$ver = 1;
				}else $array_a['inserir']    = false;
				
				if($params["regional_".$list->id]==1){ 
					$array_a['regional']    = true;
					$ver = 1;
				}else $array_a['regional']    = false;
				
				if($params["edit_".$list->id]==1){ 
					$array_a['editar']    = true;
					$ver = 1;
				}else $array_a['editar']    = false;
				
				if($params["vis_".$list->id]==1){ 
					$array_a['visualizar']    = true;
					$ver = 1;
				}else $array_a['visualizar']    = false;
				
				if($params["aba1_".$list->id]==1): 
					$array_a['aba1']    = true;
					$ver = 1;
				endif;				
				
				if($usuario->id_perfil == 1): 
								
					if($params["ang_".$list->id]==1){ 
						$array_a['analise_garantia']    = true;
						$ver = 1;
					}else $array_a['analise_garantia']    = false;
					
					if($params["neg_".$list->id]==1){
						$array_a['saldo_neg']    = true;
						$ver = 1;
					}else $array_a['saldo_neg']    = false;					
					
				endif;
				
				if(($usuario->id_perfil == 1)||($list->id==13)||($listasub->id==9)):
				
					if(($params["aba2_".$list->id]==1)||($params["admfinchina_".$list->id]==1)){ 
						$array_a['aba2']    = true;
						$ver = 1;
					}else $array_a['aba2']    = false;
					
					if($params["aba3_".$list->id]==1){ 
						$array_a['aba3']    = true;
						$ver = 1;
					}else $array_a['aba3']    = false;
					
					if($params["aba4_".$list->id]==1){ 
						$array_a['aba4']    = true;
						$ver = 1;
					}else $array_a['aba4']    = false;
					
					if($params["aba5_".$list->id]==1){ 
						$array_a['aba5']    = true;
						$ver = 1;
					}else $array_a['aba5']    = false;
				endif;
								
				
				$array_a['id_menu_sub']		= $list->id;
				$array_a['item']    		= $list->item;
				$array_a['target']    		= $list->target;
				$array_a['src']    			= $list->src;
				$array_a['id_perfil']    	= $idperf;
				
				if($ver==1) $boac->insert($array_a);				
			}
			
			return $idperf;	        
		}
		
				
		function listarPerfil($params=""){
			$obj = new PerfilModel();
			$usuario	= Zend_Auth::getInstance()->getIdentity();
			
			$var['id'] = md5($usuario->id_perfil);
			foreach (PerfilBO::buscaPerfil($var) as $list);
			
			$where = "";
			if(($list->nivel!=2)):
				$where = " and interno = 2";
			elseif($usuario->id_perfil!=1):
				$where	= " and id != 1";
			endif;
			
			if(!empty($params)){
			    return $obj->fetchAll("id = ".$params);
			}else{
			    return $obj->fetchAll("sit = true ".$where);
			}					
		}
		
		function buscaPerfil($params){
			$obj = new PerfilModel();			
			return $obj->fetchAll("md5(id) = '".$params['id']."'");								
		}
		
		function listarPerfilacesso($params){
			$obj = new PerfilacessoModel();
			return $obj->fetchAll("id_perfil = ".$params);
		}
				
		function listarPerfildet($idp, $ida){
			$obj = new PerfilacessoModel();
			return $obj->fetchAll("id_perfil = ".$idp." and id_menu_sub = ".$ida);
		}		

		function listarPerfilclientes(){
			$obj = new PerfilModel();
			$ob = new PerfilclientesModel();
			return $ob->fetchAll("sit = true","descricao asc");
			
		}
		
		/**
		 * buscarAcesso
		 *
		 * @author programador
		 * @data 27/02/2014
		 * @tags @param $params
		 * @tags @return array de objetos
		 */
		function buscarAcesso($params){
		    $usuario = Zend_Auth::getInstance()->getIdentity();
			$params = str_replace("/admin", "", $params);
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
			$select = $db->select();
			
			$select->from(array('t'=>'tb_menu_sub','*'), array('*'))
				->join(array('p'=>'tb_perfil_acesso'),'p.id_menu_sub = t.id')
				->where("p.id_perfil = ".$usuario->id_perfil." and t.item_new = '".$params."'");
				
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		function buscaPermissoesreigoes($tp=0){
		    $bo			= new RegioesModel();
		    $bor		= new RegioesclientesModel();
		    $usuario 	= Zend_Auth::getInstance()->getIdentity();
		    $where 		= "";
		    
		    //-- Busca regioes do perfil nivel regional ----------------------------------------------------
		    if(isset($usuario->id_perfil)){
		    	foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
		    	if($list->nivel == 1){
			    	$reg = "";
			    	foreach ($bor->fetchAll("id_clientes = ".$usuario->id) as $regioes){
			    		$reg .= $regioes->id_regioes.",";
			    	}
			    
			    	if($reg != ""){
			    		$where = " and c.ID in (".substr($reg,0,-1).")";
			    	}elseif($tp==0){
			    		$where = " and c.ID = 0";
			    	}
		    	}
		    }else{
		    	$where = " and c.ID = 0";
		    }
		}
		
		
	}
