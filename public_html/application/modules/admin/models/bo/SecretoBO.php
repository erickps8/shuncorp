<?php
	class SecretoBO{		
		public function listarUsuario($params){
			$ob = new SecretoModel();
			return $ob->fetchAll("login = '".$params['login']."' and senha = '".$params['senha']."'");			
		}
		
		public function listarUsuarioall(){
			$ob = new SecretoModel();
			return $ob->fetchAll("idescolha = 0 || idescolha = '' || idescolha is NULL");			
		}
		
		public function buscaUsuarioall($var){
			$ob = new SecretoModel();
			return $ob->fetchAll("id = ".$var);			
		}
		
		public function buscaUsuariosel($var){
			$ob = new SecretoModel();
			return $ob->fetchAll("idescolha = ".$var);			
		}
		
		public function atualizaUser($params){
			$ob = new SecretoModel();
			
			$array['idescolha'] 				= $params['iduser'];
			$ob->update($array,'id = '.$params['idescolha']);			
		}
		
			
	}
?>