<?php
	class TransacoesBO{		
		public function listaTransacoes(){
			//$bo = new TransacoesModel();			
			
			$params = array (
			    'host'     => '127.0.0.1',
			    'username' => 'ztlrolamentos',
			    'password' => 'BdMySql2008',
			    'dbname'   => 'projeto'
			);
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_transacoes','*'),
			        array('id_transacoes','dt_ent','dt_venc','p.tb_planocontas','f.nome_fantasia'))
			        ->join(array('p'=>'tb_planocontas'),
			        't.id_planocontas = p.id_planocontas')
			        ->join(array('f'=>'tb_fornecedores'),
			        't.id_fornecedor = f.id_fornecedores')
			        ->order('t.dt_venc','desc');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
	
		}
		
	public function cadastraTransacoes($params){
		$bo = new TransacoesModel();
		
		$array['dt_ent']    		= substr($params['dt_ent'],6,4).'-'.substr($params['dt_ent'],3,2).'-'.substr($params['dt_ent'],0,2);
		$array['dt_venc']     		= substr($params['dt_ven'],6,4).'-'.substr($params['dt_ven'],3,2).'-'.substr($params['dt_ven'],0,2);
		$array['valordoc']        	= $params['valor'];
		$array['id_fornecedor']     = $params['fornecedor'];
		$array['id_planocontas']    = $params['plano'];
		$array['obs']    			= $params['obs'];
		$array['documento'] 		= $params['documento'];
        $array['sit']	  			= true;
		
        if(empty($params['id_transacoes'])){
			$bo->insert($array);
        }else{
			$bo->update($array,'id_transacoes='.$params['id_transacoes']);
        }
	}

	public function buscaTransacoes($params){
			$obj = new TransacoesModel();
			return $obj->fetchAll('id_transacoes = '.$params['id']);			
			
	}
	
		
}
?>