<?php
	class LogBO{
			
		function listaLog($pesq){
		    $where = "";
		    $usuario 	= Zend_Auth::getInstance()->getIdentity();
		    
			$limite = 200;
			if(!empty($pesq['busca'])):
				$where = " and (l.modulo like '%".$pesq['busca']."%' || l.ident_desc like '%".$pesq['busca']."%' ||
				t.nome like '%".$pesq['busca']."%' || l.id_usuarios = '".$pesq['busca']."')";
				$limite = 1000; 
			endif;
			
			if(!empty($pesq['usermd'])):
				$where .= " and md5(l.id_usuarios) = '".$pesq['usermd']."'";
			endif;
			
			
			
			if((isset($pesq['dtini']) and !empty($pesq['dtini'])) || (isset($pesq['dtfim']) and !empty($pesq['dtfim']))):
				if(!empty($pesq[dtini])) $di	= substr($pesq[dtini],6,4).'-'.substr($pesq[dtini],3,2).'-'.substr($pesq[dtini],0,2);
				if(!empty($pesq[dtfim])) $df	= substr($pesq[dtfim],6,4).'-'.substr($pesq[dtfim],3,2).'-'.substr($pesq[dtfim],0,2);
			
				if((!empty($di)) and (!empty($df))): 
					$where .= ' and l.data between "'.$di.'" and "'.$df.'"';
					$limit=1000;
				elseif((!empty($di)) and (empty($df))): 
					$where .= ' and l.data >= "'.$di.'"';
					$limit=1000;
				elseif((empty($di)) and (!empty($df))): 
					$where .= ' and l.data <= "'.$df.'"';
					$limit=1000;
				endif;
			endif;
			
			$sessaobusca = new Zend_Session_Namespace('Log');			    
			
			if(!empty($where)):
				$sessaobusca->where = $where;
			else:
				if(isset($sessaobusca->where)):
			   		$where = $sessaobusca->where;
			   	endif;			   		
			endif;			
			
			//--- limita a pesquisa ------------------------------------
			if(!empty($pesq['limite'])):
				$limite =  $pesq['limite'];
			else:
				$limite =  1000;
			endif;
			
			//--- Controle de perfil ------------------------------------------
			//-- Usuarios de parceiros ----------------------------------------
			foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
			if(($list->nivel!=2)):
				$where .= " and t.id = ".$usuario->id;
			endif;
						
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('l'=>'tb_logacesso','*'), array('l.modulo','l.acao','l.identificador','l.ident_desc','l.data','t.nome as nomeusuario'))
			        ->join(array('t'=>'tb_usuarios'), 'l.id_usuarios = t.id')
			        ->where('l.id > 0 '.$where)
			        ->order('l.data desc')
			        ->limit($limite);
			        			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();							
		}
		/**
		 * 
		 * cadastraLog
		 * 1 - Visualizar 
	     * 2 - Incluir
	     * 3 - Inativar 
	     * 4 - Editar
	     *  
		 * @author programador
		 * @data 10/03/2014
		 * @tags @param var $mod
		 * @tags @param int $acao
		 * @tags @param int $cli
		 * @tags @param int $ident
		 * @tags @param var $ident_desc
		 * @tags @param array $arrayparams
		 */
		
		function cadastraLog($mod,$acao,$cli,$ident=null,$ident_desc=null,$arrayparams=""){
			date_default_timezone_set('America/Sao_Paulo');
			$bo 	= new LogModel();
			$bot	= new LogalteracoesModel();
			
			$array['data']  			= date("Y-m-d H:i:s");
			$array['modulo']        	= $mod;
			$array['acao']     			= $acao;
			$array['id_usuarios']    	= $cli;
			$array['identificador']    	= ($ident != "") ? $ident : 0;
			$array['ident_desc'] 		= $ident_desc;
						
	        $idcli = $bo->insert($array);
	        
	        /*Acao: 
	         * 1 - Visualizar; 
	         * 2 - Incluir ; 
	         * 3 - Inativar ; 
	         * 4 - Editar */
	        	        
	        if(!empty($arrayparams)):
	        	$texto = "";
		        foreach ($arrayparams as $rotulo => $informacao):
		        	 $texto .= $rotulo.": ".$informacao."<br />";
		        endforeach;
		        
		        $data	= array(
		        	'id_logacesso'	=> $idcli,
		        	'descricao'		=> $texto,
		        	'sit'			=> true
		        );
		        		        
		        $bot->insert($data);
	        endif;			
		}
		
		function listalogAlteracoes($params){
			
			if($params['altera']!=""):
				$where = " and md5(l.id) = '".$params['altera']."'";
			endif;
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('l'=>'tb_logacessoalteracoes'), array('*','DATE_FORMAT(c.data,"%d/%m/%Y") as datacad','l.id as idalt'))
					->join(array('c'=>'tb_logacesso'), 'c.id = l.id_logacesso')
					->join(array('t'=>'clientes'),'t.id = c.id_usuarios')
					->join(array('p'=>'produtos'), 'p.ID = c.identificador')
					->where("l.id > 0 ".$where)
					->order("l.id desc");
			
			$stmt = $db->query($select);
			return $stmt->fetchAll();			
		}
		
		function marcarlogAlteracoes($params){
			$bo 	= new LogModel();
			$bot	= new LogalteracoesModel();
			$data = array('sit' => false);
			return $bot->update($data, "md5(id) = '".$params['altera']."'");
		}
		
		
		function listaLogin($pesq){
			
			$limit = 200;
			if(!empty($pesq['busca'])):
				$where .= " and c.EMPRESA like '%".$pesq['busca']."%'";
				$limit = 1000; 
			endif;
			
			if((!empty($pesq[dtini])) || (!empty($pesq[dtfim]))):
				if(!empty($pesq[dtini])) $di	= substr($pesq[dtini],6,4).'-'.substr($pesq[dtini],3,2).'-'.substr($pesq[dtini],0,2);
				if(!empty($pesq[dtfim])) $df	= substr($pesq[dtfim],6,4).'-'.substr($pesq[dtfim],3,2).'-'.substr($pesq[dtfim],0,2);
			
				if((!empty($di)) and (!empty($df))): 
					$where .= ' and l.data between "'.$di.'" and "'.$df.'"';
					$limit=1000;
				elseif((!empty($di)) and (empty($df))): 
					$where .= ' and l.data >= "'.$di.'"';
					$limit=1000;
				elseif((empty($di)) and (!empty($df))): 
					$where .= ' and l.data <= "'.$df.'"';
					$limit=1000;
				endif;
			endif;
			
			$sessaobusca = new Zend_Session_Namespace('Logacesso');			    
			
			if(!empty($where)):
				$where = "l.id > 0".$where;
				$sessaobusca->where = $where;
			else:
				if(isset($sessaobusca->where)):
			   		$where = $sessaobusca->where;
			   	else:
			   		$where = "l.id > 0";
			   	endif;			   		
			endif;			
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('l'=>'tb_logacessoent','*'),
			        array('l.ip','l.situacao','l.data','t.nome as EMPRESA'))
			        ->joinLeft(array('t'=>'tb_usuarios'), 'l.clientes_id = t.id')
			        ->where($where)
			        ->limit($limit)
			        ->order('l.data desc');
			        
			        			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
						
		}
		
		public function cadastraLogin($ip,$sit,$cli){
			date_default_timezone_set('America/Sao_Paulo');
			$boe = new LogModel();
			$bo = new LogloginModel();
		
			$array['data']  			= date("Y-m-d H:i:s");
			$array['ip']	        	= $ip;
			$array['situacao'] 			= $sit;
			$array['clientes_id']    	= $cli;
						
	        $idcli = $bo->insert($array);
	        
		}
		
		public function listaLoginuser(){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('l'=>'tb_logacessoent','*'),
			        array('l.ip','l.situacao','DATE_FORMAT(l.data,"%d/%m/%Y às %H:%i") as data','t.nome as EMPRESA'))
			        ->joinLeft(array('t'=>'tb_usuarios'), 'l.id_usuarios = c.id')
			        ->where("c.ID = ".$usuario->id)
			        ->limit(3)
			        ->order('l.data desc');
			        
			        			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
						
		}
		
		public function listarFalecom(){
			$ob = new LogModel();
			$obj = new LogfalecomModel();
			return $obj->fetchAll("lida = false");			
		}
		
		public function busacaLog($var){
			
			$where	= "l.modulo like '%Atividades%' and l.clientes_id = ".$var;
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('l'=>'tb_logacesso','*'),
			        array('l.modulo','l.acao','l.identificador','l.ident_desc','l.data','t.nome as EMPRESA','l.clientes_id'))
			        ->joinLeft(array('t'=>'tb_usuarios'), 'l.id_usuarios = c.id')
			        ->where($where)
			        ->limit(1)
			        ->order('l.id desc');
			        
			        			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();								
		}
	}
	
	
?>