<?php
class MenuBO{
	 public function listarMenu(){
	 	$usuario	= Zend_Auth::getInstance()->getIdentity();
	 	
	 	$listSubmenu = "";
	 	foreach (PerfilBO::listarPerfilacesso($usuario->id_perfil) as $list){
	 		if($list->visualizar==1){ 
	 			$listSubmenu = $listSubmenu.$list->id_menu_sub.",";	
	 		}		 		
	 	}
	 		 	
	 	if($listSubmenu!=""):
	 		$where  = "s.id in (".substr($listSubmenu,0,-1).")";
	 	else:
	 		$where  = "s.id < 0";
	 	endif;
	 	
	 	$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		
		$select = $db->select();
		
		$select->from(array('m'=>'tb_menu','*'), array('m.id as id_m','m.item as item_m','m.orderm','m.class'))
		        ->join(array('s'=>'tb_menu_sub'),
		        's.id_menu = m.id')
		        ->where($where)
		        ->group("m.id")
		        ->order('m.orderm','desc');
		  
		$stmt = $db->query($select);
		return $stmt->fetchAll();		
	 	
	 }
	 
	function listarSubmenu(){
	    $bom 		= new MenuModel();
		$bo 		= new SubmenuModel();
		$usuario	= Zend_Auth::getInstance()->getIdentity();
		
	 	foreach (PerfilBO::listarPerfilacesso($usuario->id_perfil) as $list){
	 		if($list->visualizar==1){ 
	 			$listSubmenu = $listSubmenu.$list->id_menu_sub.",";	
	 		}		 		
	 	}		 	
	 			 	
	 	if($listSubmenu!=""):
	 		$where  = "id in (".substr($listSubmenu,0,-1).")";
	 	else:
	 		$where  = "id < 0";
	 	endif;
	 	
	 	return $bo->fetchAll($where);	
		
	 }

	function listarSubmenuperfil(){
		$bom		= new MenuModel();
		$bo 		= new SubmenuModel();
		$usuario	= Zend_Auth::getInstance()->getIdentity();
		
		if($usuario->id_perfil == 1):
			$where = "id is not NULL";
		else:
			$where = "adm = false";
		endif;
		
	 	return $bo->fetchAll($where);	
	 }
	 
	 function buscarSubmenu($params){
	 	$usuario	= Zend_Auth::getInstance()->getIdentity();
	 	
	 	$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
	 	$db->setFetchMode(Zend_Db::FETCH_OBJ);
	 		
	 	$select = $db->select();
	 		
	 	$select->from(array('s'=>'tb_menu_sub','*'), array('*'))
	 		->join(array('a'=>'tb_perfil_acesso'), 'a.id_menu_sub = s.id')
 			->where("a.visualizar = true and s.id_menu = ".$params['idmenu']." and a.id_perfil = ".$usuario->id_perfil)
 			->order('s.item','asc');
	 		
	 	$stmt = $db->query($select);
	 	return $stmt->fetchAll();
	 	 	 	
	 }
	 
	 function gravaMenuusuario($params){
	     $usuario	= Zend_Auth::getInstance()->getIdentity();
	     $bo	= new MenuModel();
	     $bom	= new MenuusuarioModel();
	     
	     $bom->delete('id_usuario = '.$usuario->id);
	     
	     foreach (MenuBO::listarSubmenu() as $menu):
	     	if(!empty($params[$menu->id])):
	     		$data	= array('id_menusub' => $menu->id, 'id_usuario' => $usuario->id);
	     		$bom->insert($data);
	     	endif; 
	     endforeach; 
	 }
	 
	 function buscaMenuusuario(){
	 	$usuario	= Zend_Auth::getInstance()->getIdentity();
	 	
	 	$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
	 	$db->setFetchMode(Zend_Db::FETCH_OBJ);
	 		
	 	$select = $db->select();
	 		
	 	$select->from(array('s'=>'tb_menuusuario','*'), array('m.id as idsm','m.item as itemsm'))
	 		->join(array('a'=>'tb_perfil_acesso'), 'a.id_menu_sub = s.id_menusub')
	 		->join(array('m'=>'tb_menu_sub'), 'm.id = s.id_menusub')
 			->where("a.visualizar = true and a.id_perfil = ".$usuario->id_perfil." and s.id_usuario = ".$usuario->id)
 			->order('m.item','asc');
	 		
	 	$stmt = $db->query($select);
	 	return $stmt->fetchAll();
	 }
}
