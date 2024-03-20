<?php
	class TennisBO{		
		function listarJogadores(){
			
			$params = array ('host'     => '127.0.0.1', 'username' => 'ztlrolamentos', 'password' => 'BdMySql2008', 'dbname'   => 'ztlrolamentos');
						
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('j'=>'tb_jogadores','*'),
			        array('j.*','j.id as idj','count(a.venc) as venca'))
			        ->joinLeft(array('a'=>'tb_historico'),'j.id = a.venc and a.data = "'.date("Y-m-d").'"')
			        ->group("j.id")
			        ->order('sit desc');			       
			  			        
			$stmt = $db->query($select);
			return $stmt->fetchAll();
			
		}
		
		function contaPartidas(){
			$obj 	= new TennisModel();	
			$objh	= new HistoricotennisModel();	
			return $objh->fetchAll("data = '".date("Y-m-d")."'");							
		}
		
		function contaPartidasger(){
			$obj 	= new TennisModel();	
			$objh	= new HistoricotennisModel();	
			return $objh->fetchAll();							
		}
		
		function atualizaFila($var){
			$obj 	= new TennisModel();
			$objl	= new FilatennisModel();
			$objp  	= new PartidastennisModel();
			
			if(!empty($var['jog']) and (empty($var['rem']))):
				$array['id_jogador'] = $var['jog'];
				$objl->insert($array);
				
				$arrayst['sit'] = 2;
				$obj->update($arrayst, "id = ".$var['jog']);
				
				if(count($objp->fetchAll()) == 0):
					$arrayjog['id_jogador1'] = $var['jog'];
					$objp->insert($arrayjog);
					$objl->delete("id_jogador = ".$var['jog']);
					
				elseif(count($objp->fetchAll()) > 0):
					foreach ($objp->fetchAll() as $listap);
					if($listap->id_jogador2==""):
						$arrayjog['id_jogador2'] = $var['jog'];
						$objp->update($arrayjog, "id = ".$listap->id);
						$objl->delete("id_jogador = ".$var['jog']);
						
					endif;
				endif;
				
				
			elseif(!empty($var['rem'])):
				$objl->delete("id_jogador = ".$var['jog']);
				
				$arrayst['sit'] = 1;
				$obj->update($arrayst, "id = ".$var['jog']);
				
				//--troca na remocao, se for proximo a jogar-----------------------
				$cont=0;
				foreach (TennisBO::listarFila() as $fila):
					$cont++;
					if($cont==1):
						$idj	= $fila->id_jogador;
					endif;
				endforeach;
				
				foreach ($objp->fetchAll() as $listap);
				
				if($listap->id_jogador1==$var['jog']):
					
					if($listap->id_jogador2!=""):				
						$arrayp['id_jogador1']	= $listap->id_jogador2;
						$arrayp['id_jogador2']	= $idj;
						
						$objp->delete("id > 0");
						$objp->insert($arrayp);
						
						if($idj!=""):
							$objl->delete("id_jogador = ".$idj);
						endif;
					else:
						$objp->delete("id > 0");
					endif;
				
				elseif($listap->id_jogador2==$var['jog']):
				
					if($listap->id_jogador1!=""):	
						$arrayp['id_jogador1']	= $listap->id_jogador1;
						$arrayp['id_jogador2']	= $idj;
						
						$objp->delete("id > 0");
						$objp->insert($arrayp);
						
						if($idj!=""):
							$objl->delete("id_jogador = ".$idj);
						endif;
					else:
						$objp->delete("id > 0");
					endif;
					
				endif;				
				
			endif;	
		}
		
		function listarFila(){
			$obj 	= new TennisModel();
			$objl	= new FilatennisModel();
						
			return $objl->fetchAll();			
		}
		
		function finalizaFila($var){
			$obj 	= new TennisModel();
			$objl	= new FilatennisModel();
			
			$objl->delete();
			$array['sit'] = 1;
			$obj->update($array, "id != 0");			
		}
		
		function atualizaPartida($var){
			$obj 	= new TennisModel();
			$objl	= new FilatennisModel();
			$objp	= new PartidastennisModel();
			
			$objl->delete();
			$array['sit'] = 1;
			$obj->update($array, "id != 0");			
		}
		
		function exibePartida($var){
			$obj 	= new TennisModel();
			$objp	= new PartidastennisModel();
			$objh	= new HistoricotennisModel();
			$objl	= new FilatennisModel();
						
			if(!empty($var['jog'])):
				foreach ($objp->fetchAll() as $listap);
				$array['id_jogador1']	= $listap->id_jogador1;
				$array['id_jogador2']	= $listap->id_jogador2;
				$array['data']			= date("Y-m-d");
				$array['venc']			= $var['jog'];
				
				$objh->insert($array);
				
				if($listap->id_jogador1==$var['jog']):
					//-- insiro na lista jogador q perdeu --------------------
					$arrayl['id_jogador'] = $listap->id_jogador2;
					$objl->insert($arrayl);					
					
					//-- busco o novo jogador na lista -----------------------
					$cont=0;
					foreach (TennisBO::listarFila() as $fila):
						$cont++;
						if($cont==1):
							$idj	= $fila->id_jogador;
						endif;
					endforeach;

					//-- Insiro jogadores na partida -------------------------
					$arrayp['id_jogador1']	= $var['jog'];
					$arrayp['id_jogador2']	= $idj;
					
					$objp->delete("id > 0");
					$objp->insert($arrayp);
					
					//-- removo jogador da lista -----------------------------
					$objl->delete("id_jogador = ".$idj);					
										
				elseif($listap->id_jogador2==$var['jog']):
					//-- insiro na lista jogador q perdeu --------------------
					$arrayl['id_jogador'] = $listap->id_jogador1;
					$objl->insert($arrayl);
					
					//-- busco o novo jogador na lista -----------------------
					$cont=0;
					foreach (TennisBO::listarFila() as $fila):
						$cont++;
						if($cont==1):
							$idj	= $fila->id_jogador;
						endif;
					endforeach;
					
					//-- Insiro jogadores na partida -------------------------
					$arrayp['id_jogador1']	= $var['jog'];
					$arrayp['id_jogador2']	= $idj;
					
					$objp->delete("id > 0");
					$objp->insert($arrayp);
					
					//-- removo jogador da lista -----------------------------
					$objl->delete("id_jogador = ".$idj);
					
				endif;
				
			endif;
			
			return $objp->fetchAll();			
		}
		
		function buscarPartidas($var){
			$params = array ('host'     => '127.0.0.1', 'username' => 'ztlrolamentos', 'password' => 'BdMySql2008', 'dbname'   => 'ztlrolamentos');
						
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('h'=>'tb_historico','*'),array('h.*','count(venc) as venct'))
			        ->where("(id_jogador1 = ".$var['jogador1']." and id_jogador2 = ".$var['jogador2'].") || (id_jogador1 = ".$var['jogador2']." and id_jogador2 = ".$var['jogador1'].")")
			        ->group("venc");			       
			  			        
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		function listarJogadoresmediapontos(){
			
			$params = array ('host'     => '127.0.0.1', 'username' => 'ztlrolamentos', 'password' => 'BdMySql2008', 'dbname'   => 'ztlrolamentos');
						
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('j'=>'tb_jogadores','*'),
			        array('j.*','j.id as idj','count(a.venc) as venca'))
			        ->joinLeft(array('a'=>'tb_historico'),'j.id = a.venc')
			        ->group("j.id")
			        ->order('venca desc');			       
			  			        
			$stmt = $db->query($select);
			return $stmt->fetchAll();
			
		}
		
		function listarJogadoresqtdisputas(){
				
			$params = array ('host'     => '127.0.0.1', 'username' => 'ztlrolamentos', 'password' => 'BdMySql2008', 'dbname'   => 'ztlrolamentos');
		
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
			$select = $db->select();
				
			$select->from(array('j'=>'tb_jogadores','*'), array('j.*','j.id as idj','count(a.id) as qtpart'))
					->joinLeft(array('a'=>'tb_historico'),'j.id = a.id_jogador1 || j.id = a.id_jogador2')
					->group("j.id");
		
			$stmt = $db->query($select);
			return $stmt->fetchAll();
				
		}
			
	}
?>