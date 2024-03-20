<?php
class FaturamentoBO{		
		
	//----- relatorio de vendas --------------------------------------------------------------------------
	function buscaVendasagrupadas($var, $order = 'desc'){
		$bo 	= new ContatosModel();
		$boe 	= new ContatosempModel();
		
		//--- Busca memorizada ------------------------------------------
	    $sessaobusca = new Zend_Session_Namespace('relatoriofaturamento');
	    if(isset($sessaobusca->where)):
	    	$where = $sessaobusca->where;
	    endif;	    	    	
	    
	    if(isset($var['tipo'])){
		    if($var['tipo'] == 2):
		    	$empresas = $var['cliente'].",";
		        if(isset($var['vendasfil'])){
			    	foreach ($boe->fetchAll("status = 1 and id_matriz = '".$var['idempresa']."'") as $filias){
			    		if($filias->id_clientes){
			    			$empresas .= $filias->id_clientes.",";
			    		}
			    	}
			    }
		    
			    $where = " and id_parceiro in (".substr($empresas,0,-1).")";
			    
		    elseif($var['tipo'] == 3):
		    	$where = " and cr.id_usuarios = ".$var['representante'];
		    elseif($var['tipo'] == 4):
		    	$where = " and c.ID_REGIOES = ".$var['regiao'];
		    elseif($var['tipo'] == 1):
		    	$where = "";
		    endif;
	    }
	    
		if(!empty($var['dataini']) || !empty($var['datafim'])):
			if(!empty($var['dataini']) and !empty($var['datafim'])):
				$dataini = substr($var['dataini'],6,4).'-'.substr($var['dataini'],3,2).'-'.substr($var['dataini'],0,2);
				$datafim = substr($var['datafim'],6,4).'-'.substr($var['datafim'],3,2).'-'.substr($var['datafim'],0,2);			
				$where .= ' and p.data_vend BETWEEN "'.$dataini.'" and "'.$datafim.'  23:59:59"';
			elseif (!empty($var['dataini'])):
				$dataini = substr($var['dataini'],6,4).'-'.substr($var['dataini'],3,2).'-'.substr($var['dataini'],0,2);
				$where .= ' and p.data_vend >= "'.$dataini.'"';
			elseif (!empty($var['datafim'])):
				$datafim = substr($var['datafim'],6,4).'-'.substr($var['datafim'],3,2).'-'.substr($var['datafim'],0,2);
				$where .= ' and p.data_vend <= "'.$datafim.' 23:59:59"';
			endif;
		else:
			$data	 = date('Y');
			$where .= ' and p.data_vend like "'.$data.'%"';
		endif;
		
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $listr);
		if($listr->nivel==1):
			if($listr->reggen == 1):
				$clireg = "";
				foreach (ClientesBO::buscaParceiros('clientes') as $clientes):
					$clireg .= $clientes->ID.",";
				endforeach;
				
				if(!empty($clireg)):
					$where .= " and c.ID in (".substr($clireg,0,-1).")";
				else:
					$where .= " and c.ID < 0";
				endif;
			else:
				$where .= " and p.id_representante = ".$usuario->id_cliente;
			endif;
		endif;
		
		$sessaobusca->where = $where;
		
		$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$select = $db->select();
	
		$select->from(array('p'=>'tb_pedidos'), array('sum(qt*preco_unit) as precototal', 'EXTRACT(MONTH FROM data_vend) as mes', 'EXTRACT(YEAR FROM data_vend) as ano'))
			->join(array('pd'=>'tb_pedidos_prod'), 'pd.id_ped = p.id')
			->join(array('c'=>'clientes'), 'c.ID = p.id_parceiro')
			->joinLeft(array('cr'=>'clientes_regioes'), 'cr.ID = c.ID_REGIOES')
			->where('p.status = "ped" and p.sit = 0'.$where)
			->group('mes')
			->group('ano')
			->order('mes '.$order)
			->order('ano '.$order);
	
		$stmt = $db->query($select);
		
		return $stmt->fetchAll();
	}
	
	
	//----- vendas abertas--------------------------------------------------------------------------
	function buscaVendasabertas(){
	    $where 	 = "";
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $listr);
		if($listr->nivel==1):
			if($listr->reggen == 1):
				$clireg = "";
				foreach (ClientesBO::buscaParceiros('clientes') as $clientes):
					$clireg .= $clientes->ID.",";
				endforeach;
			
				if(!empty($clireg)):
					$where .= " and p.id_parceiro in (".substr($clireg,0,-1).")";
				else:
					$where .= " and p.id_parceiro < 0";
				endif;
			else:
				$where .= " and p.id_representante = ".$usuario->id;
			endif;
		endif;
	
		$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$select = $db->select();
	
		$select->from(array('p'=>'tb_pedidos'), array('sum(qt*preco_unit) as precototal'))
		->join(array('pd'=>'tb_pedidos_prod'), 'pd.id_ped = p.id')
		->where('p.status = "orc" and p.sit = 0'.$where);
	
		$stmt = $db->query($select);
		return $stmt->fetchAll();
	}
	
	//----- relatorio de descontos por venda --------------------------------------------------------------------------
	function buscaDescontosvenda($var=""){
	
		//--- Busca memorizada ------------------------------------------
		$sessaobusca = new Zend_Session_Namespace('relatoriofaturamento');
		if(isset($sessaobusca->where)):
			$where = $sessaobusca->where;
		endif;
		 
		$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$select = $db->select();
	
		$select->from(array('p'=>'tb_pedidos'), array('EXTRACT(MONTH FROM data_vend) as mes', 'EXTRACT(YEAR FROM data_vend) as ano','sum(p.desconto) as descvendas'))
			->join(array('c'=>'clientes'), 'c.ID = p.id_parceiro')
			->joinLeft(array('cr'=>'clientes_regioes'), 'cr.ID = c.ID_REGIOES')
			->where('p.status = "ped" and p.sit = 0'.$where)
			->group('mes')
			->group('ano')
			->order('mes desc')
			->order('ano desc');
	
		$stmt = $db->query($select);
		return $stmt->fetchAll();
	}
	
	//----- relatorio de pendencias ----------------------------
    function buscaPendenciasprodutos(){
    	
        //--- Busca memorizada ------------------------------------------
        $sessaobusca = new Zend_Session_Namespace('relatoriofaturamento');
        if(isset($sessaobusca->where)):
        	$where = $sessaobusca->where;
        endif;
		
        /*-- Uso os filtros da buscaVendaagrupada, sendo q mesmo que nao haja venda no determinado periodo, pode haver dendencias .... */        
        
		$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$select = $db->select();		
				
		$select->from(array('p'=>'tb_pedidos'), array('sum(qt*valor) as precopend', 'EXTRACT(MONTH FROM dt_pend) as mes, EXTRACT(YEAR FROM dt_pend) as ano'))
			->join(array('pd'=>'tb_pedidos_pend'), 'pd.id_ped = p.id')
			->join(array('c'=>'clientes'), 'c.ID = p.id_parceiro')
			->joinLeft(array('cr'=>'clientes_regioes'), 'cr.ID = c.ID_REGIOES')
			->where('p.id > 0'.$where)
			->group('mes')
			->group('ano')
			->order('mes desc')
			->order('ano desc');
		
		
		$stmt = $db->query($select);
		return $stmt->fetchAll();
	}

	//----- Detalhamento por representantes  --------------------------------------------------------------------------
	function buscaVendasrepresentante($var){
	
		//--- Busca memorizada ------------------------------------------
        $sessaobusca = new Zend_Session_Namespace('relatoriofaturamento');
        if(isset($sessaobusca->where)):
        	$where = $sessaobusca->where;
        endif;
		
        $periodo = $var['ano']."-".str_pad($var['mes'],2,'0',STR_PAD_LEFT);
        $where .= " and p.data_vend like '".$periodo."%'";
        
		$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$select = $db->select();
	
		$select->from(array('p'=>'tb_pedidos','*'), array('sum(qt*preco_unit) as precototal','sum(p.desconto) as descvendas','rep.EMPRESA as representante', 'rep.ID as idrep','EXTRACT(MONTH FROM data_vend) as mes, EXTRACT(YEAR FROM data_vend) as ano'))
			->join(array('pd'=>'tb_pedidos_prod'), 'pd.id_ped = p.id')
			->join(array('c'=>'clientes'), 'c.ID = p.id_parceiro')
			->joinLeft(array('rep'=>'clientes'), 'rep.ID = p.id_representante')
			->where("p.id > 0 and p.status = 'ped' and p.sit = 0 ".$where)
			->group('rep.EMPRESA')
			->order('rep.EMPRESA');

		/* 
		$select->from(array('p'=>'tb_pedidos','p.status = "ped" and p.sit = 0'), array('sum(qt*preco_unit) as precototal','rep.EMPRESA as representante', 'rep.ID as idrep','EXTRACT(MONTH FROM data_vend) as mes, EXTRACT(YEAR FROM data_vend) as ano'))
		->join(array('pd'=>'tb_pedidos_prod'), 'pd.id_ped = p.id')
		->join(array('c'=>'clientes'), 'c.ID = p.id_parceiro')
		->joinLeft(array('cr'=>'clientes_regioes'), 'cr.ID = c.ID_REGIOES')
		->joinLeft(array('rep'=>'clientes'), 'rep.ID = cr.id_clientes')
		->where("p.id > 0".$where)
		->group('rep.EMPRESA')
		->order('rep.EMPRESA');
		 */
		
		$stmt = $db->query($select);
		return $stmt->fetchAll();
	}
	
	//----- relatorio de descontos por representantes --------------------------------------------------------------------------
	function buscaDescontosrepresentante($var){
	
		//--- Busca memorizada ------------------------------------------
        $sessaobusca = new Zend_Session_Namespace('relatoriofaturamento');
        if(isset($sessaobusca->where)):
        	$where = $sessaobusca->where;
        endif;
		
        $periodo = $var['ano']."-".str_pad($var['mes'],2,'0',STR_PAD_LEFT);
        $where .= "and p.data_vend like '".$periodo."%'";
        
		$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$select = $db->select();
	
		$select->from(array('p'=>'tb_pedidos'), array('sum(p.desconto) as descvendas','rep.EMPRESA as representante', 'rep.ID as idrep','EXTRACT(MONTH FROM data_vend) as mes, EXTRACT(YEAR FROM data_vend) as ano'))
			->join(array('c'=>'clientes'), 'c.ID = p.id_parceiro')
			->join(array('rep'=>'clientes'), 'rep.ID = p.id_representante')
			->where('p.status = "ped" and p.sit = 0'.$where)
			->group('rep.EMPRESA')
			->order('rep.EMPRESA');
	
		$stmt = $db->query($select);
		return $stmt->fetchAll();
	}
	
	//----- Detalhamento por clientes  --------------------------------------------------------------------------
	function buscaVendasclientes($var){
	
		//--- Busca memorizada ------------------------------------------
        $sessaobusca = new Zend_Session_Namespace('relatoriofaturamento');
        if(isset($sessaobusca->where)):
        	$where = $sessaobusca->where;
        endif;
		
        $periodo = $var['ano']."-".str_pad($var['mes'],2,'0',STR_PAD_LEFT);
        $where .= "and p.data_vend like '".$periodo."%'";
        
        if($var['rep'] == 'sem'):
        	$where .= "and p.id_representante is NULL";
        else:
        	$where .= "and p.id_representante = ".$var['rep'];
        endif;
        
		$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$select = $db->select();
	
		$select->from(array('p'=>'tb_pedidos'), array('sum(qt*preco_unit) as precototal','c.EMPRESA', 'cr.NOME','sum(p.desconto) as descvendas',
		        'EXTRACT(MONTH FROM data_vend) as mes, EXTRACT(YEAR FROM data_vend) as ano', 'p.id_representante as idrep','p.id_parceiro'))
			->join(array('pd'=>'tb_pedidos_prod'), 'pd.id_ped = p.id')
			->join(array('c'=>'clientes'), 'c.ID = p.id_parceiro')
			->joinLeft(array('cr'=>'clientes_regioes'), 'cr.ID = c.ID_REGIOES')
			
			->where('p.status = "ped" and p.sit = 0'.$where)
			->group('c.ID')
			->order('c.EMPRESA');
	
		$stmt = $db->query($select);
		return $stmt->fetchAll();
	}
	
	//----- relatorio de descontos por clientes --------------------------------------------------------------------------
	function buscaDescontosclientes($var){
	
		//--- Busca memorizada ------------------------------------------
        $sessaobusca = new Zend_Session_Namespace('relatoriofaturamento');
        if(isset($sessaobusca->where)):
        //	$where = $sessaobusca->where;
        endif;
		
        $periodo = $var['ano']."-".str_pad($var['mes'],2,'0',STR_PAD_LEFT);
        $where .= " and p.data_vend like '".$periodo."%'";
                
        if($var['rep'] == 'sem'):
        	$where .= "and p.id_representante is NULL";
        else:
        	$where .= "and p.id_representante = ".$var['rep'];
        endif;
        
        
		$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$select = $db->select();
	
		$select->from(array('p'=>'tb_pedidos'), array('*','sum(p.desconto) as descvendas','c.ID as idcliente'))
			->join(array('c'=>'clientes'), 'c.ID = p.id_parceiro')
			
			->where('p.status = "ped" and p.sit = 0'.$where)
			->group('c.ID')
			->order('c.EMPRESA');
	
		$stmt = $db->query($select);
		return $stmt->fetchAll();
	}
	
	//----- Detalhamento por regioes --------------------------------------------------------------------------
	function buscaVendasregioes($var){
	
		//--- Busca memorizada ------------------------------------------
        $sessaobusca = new Zend_Session_Namespace('relatoriofaturamento');
        if(isset($sessaobusca->where)):
        	$where = $sessaobusca->where;
        endif;
		
        $periodo = $var['ano']."-".str_pad($var['mes'],2,'0',STR_PAD_LEFT);
        $where .= "and p.data_vend like '".$periodo."%'";
       	if($var['rep'] == 'sem'):
        	$where .= "and p.id_representante is NULL";
        else:
        	$where .= "and p.id_representante = ".$var['rep'];
        endif;
        
		$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$select = $db->select();
	
		$select->from(array('p'=>'tb_pedidos'), array('sum(qt*preco_unit) as precototal','cr.NOME', 'cr.ID as idreg', 'sum(p.desconto) as descvendas','EXTRACT(MONTH FROM data_vend) as mes, EXTRACT(YEAR FROM data_vend) as ano'))
			->join(array('pd'=>'tb_pedidos_prod'), 'pd.id_ped = p.id')
			->join(array('c'=>'clientes'), 'c.ID = p.id_parceiro')
			->joinLeft(array('cr'=>'clientes_regioes'), 'cr.ID = c.ID_REGIOES')
			
			->where('p.status = "ped" and p.sit = 0'.$where)
			->group('cr.ID')
			->order('cr.NOME');
	
		$stmt = $db->query($select);
		return $stmt->fetchAll();
	}
	
	//----- relatorio de descontos por regioes--------------------------------------------------------------------------
	function buscaDescontosregioes($var){
	
		//--- Busca memorizada ------------------------------------------
        $sessaobusca = new Zend_Session_Namespace('relatoriofaturamento');
        if(isset($sessaobusca->where)):
        	$where = $sessaobusca->where;
        endif;
		
        $periodo = $var['ano']."-".str_pad($var['mes'],2,'0',STR_PAD_LEFT);
        $where .= " and p.data_vend like '".$periodo."%'";
        if($var['rep'] == 'sem'):
        	$where .= "and p.id_representante is NULL";
        else:
        	$where .= "and p.id_representante = ".$var['rep'];
        endif;
        
		$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$select = $db->select();
	
		$select->from(array('p'=>'tb_pedidos'), array('sum(p.desconto) as descvendas','EXTRACT(MONTH FROM data_vend) as mes, EXTRACT(YEAR FROM data_vend) as ano'))
			->join(array('c'=>'clientes'), 'c.ID = p.id_parceiro')
			->joinLeft(array('cr'=>'clientes_regioes'), 'cr.ID = c.ID_REGIOES')
			
			->where('p.status = "ped" and p.sit = 0'.$where)
			->group('cr.ID')
			->order('cr.NOME');
	
		$stmt = $db->query($select);
		return $stmt->fetchAll();
	}
	
	//----- Detalhamento das vendas por clientes  --------------------------------------------------------------------------
	function buscaVendasdetalhadasporclientes($var){
	
		$periodo = $var['ano']."-".str_pad($var['mes'],2,'0',STR_PAD_LEFT);
		$where .= " and p.data_vend like '".$periodo."%'";
		$where .= " and p.id_parceiro = ".$var['cli'];
		
		if($var['rep'] == 'sem'):
			$where .= " and p.id_representante is NULL";
		else:
			$where .= " and p.id_representante = ".$var['rep'];
		endif;
	
		$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$select = $db->select();
		
		$select->from(array('p'=>'tb_pedidos'), array('sum(qt*preco_unit) as precototal', 'p.desconto','p.id as idvenda','DATE_FORMAT(p.data_vend, "%d/%m/%Y") as data'))
				->join(array('pd'=>'tb_pedidos_prod'), 'pd.id_ped = p.id')
									
				->where('p.status = "ped" and p.sit = 0'.$where)
				->group('p.id');
	
		$stmt = $db->query($select);
		return $stmt->fetchAll();
	}
	
	
	//----- Impostos ------------------------------------------------------------------------------------------------------------
	function buscaImpostos($tipo, $params, $tipomov = "1"){
	    
	    $grupo = "p.alicms";
	    if($tipo == 'icms'):
	    	$grupo = "p.alicms";
	    elseif($tipo == 'st'):
	    	$grupo = "p.icmsst";
	    elseif($tipo == 'ipi'):
	    	$grupo = "p.alipi";
	    elseif($tipo == 'pis'):
	    	$grupo = "p.alpis";
	    elseif($tipo == 'cofins'):
	    	$grupo = "p.alcofins";
	    endif;
	        
	    $movimentacao = " and n.tipo = ".$tipomov;
	    
	    
	    if(!empty($params['dataini']) || !empty($params['datafim'])):
		    if(!empty($params['dataini']) and !empty($params['datafim'])):
			    $dataini = substr($params['dataini'],6,4).'-'.substr($params['dataini'],3,2).'-'.substr($params['dataini'],0,2);
			    $datafim = substr($params['datafim'],6,4).'-'.substr($params['datafim'],3,2).'-'.substr($params['datafim'],0,2);
		    	$where = ' and n.data BETWEEN "'.$dataini.'" and "'.$datafim.'  23:59:59"';
		    elseif (!empty($params['dataini'])):
			    $dataini = substr($params['dataini'],6,4).'-'.substr($params['dataini'],3,2).'-'.substr($params['dataini'],0,2);
			    $where = ' and n.data >= "'.$dataini.' 23:59:59"';
		    elseif (!empty($params['datafim'])):
			    $datafim = substr($params['datafim'],6,4).'-'.substr($params['datafim'],3,2).'-'.substr($params['datafim'],0,2);
			    $where = ' and n.data <= "'.$datafim.' 23:59:59"';
		    endif;
	    else:
		    $data	 = date('Y-m');
		    $where = ' and n.data like "'.$data.'%"';
	    endif;
	    
	    
	   
	    
	   	$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$select = $db->select();
		
		$select->from(array('n'=>'tb_nfe'), array('sum(p.baseicms) as tbaseicms, sum(p.vlicms) as tvlicms, sum(p.basest) as tbasest, sum(p.vlicmsst) as tvlicmsst, 
			    sum(p.qt*p.preco) as tbaseipi, sum(p.vlipi) as tvlipi, sum(p.vlpis) as tvlpis, sum(p.vlcofins) as tvlcofins','n.cfop as cfopnfe'))
				->join(array('p'=>'tb_nfeprod'), 'p.id_nfe = n.id')
				->where('p.id > 1 and n.status = 1 and n.id = p.id_nfe '.$where.$movimentacao)
				->group('n.cfop')
				->group($grupo);
	
		$stmt = $db->query($select);
		return $stmt->fetchAll();
	}
	
	//----- Comissao por representantes  --------------------------------------------------------------------------
	/* Listagem de comissao, onde busco todos os pedidos de um determinado periodos, 
	 * junto com seus representantes de venda e verifico se jah foi lancado como comissao paga.
	 * 
	 *  Eh necessario as variaveis: 
	 *  $data = Periodo a ser pesquisado. Uso somente o mes e ano, no formato 0000-00;
	 *  
	 * 
	 * */
	function buscaComissaorepresentante($var){
	    
		$periodo 	= $var['periodo'];
		$where 		= " and p.data_vend like '".$periodo."%'";
		
		$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
		$representantes = ClientesBO::buscaParceiros('representantes');
		
		?>
		<div class="widget">
 			<table style="width: 100%" class="tableStatic">
            	<thead>
                	<tr>
                        <td >Representante</td>
                        <td >Venda</td>
                        <td >Descontos</td>
                        <td >Total</td>
                        <td >Comissão</td>
                        <td >Opções</td>
                    </tr>
                </thead>
                <tbody>
               	<?php 
				foreach($representantes as $lista){		
					$select = $db->select();
					$select->from(array('p'=>'tb_pedidos','*'), array('sum(pd.qt*pd.preco_unit) as precototal','p.desconto','p.comissaorep'))
					->join(array('pd'=>'tb_pedidos_prod'), 'pd.id_ped = p.id')
					->where("p.status = 'ped' and p.sit = 0 and p.id_representante = '".$lista->ID."'".$where)
					->group('p.id');
					 
					$stmt = $db->query($select);
					$objFaturamento = $stmt->fetchAll();
					 
					$venda = $descontos = $comissao = 0;
					if(count($objFaturamento)>0){
						foreach ($objFaturamento as $fat){
							$venda 		+= $fat->precototal;
							$descontos 	+= $fat->desconto;
							if($fat->comissaorep>0) $comissao 	+= (($fat->precototal-$fat->desconto)*$fat->comissaorep)/100;
						}
					}
					
					if($venda>0){
				    	?>			
						<tr >
			                <td  style="text-align: left;" >
			                   <?php echo $lista->EMPRESA?>
			                </td>
			                <td  style="text-align: right;" >
			                	<?php echo number_format($venda,2,",",".")?>
			                </td>
			                <td style="text-align: right;" >
			                	<?php echo number_format($descontos,2,",",".")?>
			                </td>
			                <td style="text-align: right;" >
			                	<?php echo number_format($venda-$descontos,2,",",".")?>
			                </td>
			                <td style="text-align: right; font-weight: bold;" >
			                	<?php echo number_format($comissao,2,",",".")?>
			                </td>
							<td  style="text-align: center;" >
								<a href="/admin/relatorios/financeirocomissoesdet/rep/<?php echo $lista->ID?>/tp/1/periodo/<?php echo $periodo?>"><img src="/public/sistema/imagens/icons/middlenav/magnify.png" width="16" border="0" title="Visualizar"></a>
								<?php 
								if($comissao>0){
									$objPag = '';
									$select = $db->select();
									$select->from(array('vc'=>'tb_vendacomissao','*'), array('vc.id_financeiropag as pagamento'))
										->where("vc.id_representante = '".$lista->ID."' and vc.competencia = '".$periodo."-01'");
									
									$stmt = $db->query($select);
									$objPag = $stmt->fetchAll();
									
									foreach ($objPag as $pagamento);
									
									if(count($objPag)==0){ ?>
				                		<a href="javascript:void(0);" onclick="gerarPagamento('<?php echo $lista->ID?>','<?php echo $periodo?>','<?php echo number_format($comissao,2,",",".")?>')" ><img src="/images/financeiro.gif" width="16" border="0" title="Gerar Pagamento da comissão"></a>
				                	<?php }else{ ?>
				                		<a href="/admin/administracao/financeiroztlpagcad/pay/<?=md5($pagamento->pagamento)?>" target="_blank">P<?=substr("000000".$pagamento->pagamento, -6,6)?></a>
				                	<?php }				                 
			                	}
			                	?>
			                </td>		                                                
		            	</tr>
					<?php  
					}
				} ?>
				</tbody>
			</table>
		</div>
		<?php 
		
		//$this->view->objFatvend 	= FaturamentoBO::buscaComissaovendedores($params);
		
		$televendas	= UsuarioBO::buscaUsuario(array('perfil' => 31));
		
		?>
		<div class="widget">
 			<table style="width: 100%" class="tableStatic">
            	<thead>
                	<tr>
                        <td >Televenda</td>
                        <td >Venda</td>
                        <td >Descontos</td>
                        <td >Total</td>
                        <td >Comissão</td>
                        <td >Opções</td>
                    </tr>
                </thead>
                <tbody>
               	<?php 
				foreach($televendas as $lista){

					$select = $db->select();
					$select->from(array('p'=>'tb_pedidos','*'), array('sum(pd.qt*pd.preco_unit) as precototal','p.desconto','p.comissaovend'))
					->join(array('pd'=>'tb_pedidos_prod'), 'pd.id_ped = p.id')
					->where("p.status = 'ped' and p.sit = 0 and p.id_televenda = '".$lista->iduser."'".$where)
					->group('p.id');
					 
					$stmt = $db->query($select);
					$objFaturamento = $stmt->fetchAll();
					 
					$venda = $descontos = $comissao = 0;
					if(count($objFaturamento)>0){
						foreach ($objFaturamento as $fat){
							$venda 		+= $fat->precototal;
							$descontos 	+= $fat->desconto;
							if($fat->comissaovend>0) $comissao 	+= (($fat->precototal-$fat->desconto)*$fat->comissaovend)/100;
						}
					}
					 
					if($venda>0){
				
				    	?>			
						<tr >
			                <td  style="text-align: left;" >
			                   <?php echo $lista->nomeusuario?>
			                </td>
			                <td  style="text-align: right;" >
			                	<?php echo number_format($venda,2,",",".")?>
			                </td>
			                <td style="text-align: right;" >
			                	<?php echo number_format($descontos,2,",",".")?>
			                </td>
			                <td style="text-align: right;" >
			                	<?php echo number_format($venda-$descontos,2,",",".")?>
			                </td>
			                <td style="text-align: right; font-weight: bold;" >
			                	<?php echo number_format($comissao,2,",",".")?>
			                </td>
							<td  style="text-align: center;" >
								<a href="/admin/relatorios/financeirocomissoesdet/rep/<?php echo $lista->iduser?>/tp/2/periodo/<?php echo $periodo?>"><img src="/public/sistema/imagens/icons/middlenav/magnify.png" width="16" border="0" title="Visualizar"></a>
								<?php 
								if($comissao>0){
									$objPag = ''; 
									$select = $db->select();
									$select->from(array('vc'=>'tb_vendacomissaointerno','*'), array('vc.id_financeiropag as pagamento'))
										->where("vc.id_usuarios = '".$lista->iduser."' and vc.competencia = '".$periodo."-01'");
									
									$stmt = $db->query($select);
									$objPag = $stmt->fetchAll();
									
									foreach ($objPag as $pagamento);
									
									if(count($objPag)==0){ ?>
				                		<a href="javascript:void(0);" onclick="gerarPagamentotelvendas('<?php echo $lista->iduser?>','<?php echo $periodo?>','<?php echo number_format($comissao,2,",",".")?>',1)" ><img src="/images/financeiro.gif" width="16" border="0" title="Gerar Pagamento da comissão"></a>
				                	<?php }else{ ?>
				                		<a href="/admin/administracao/financeiroztlpagcad/pay/<?=md5($pagamento->pagamento)?>" target="_blank">P<?=substr("000000".$pagamento->pagamento, -6,6)?></a>
				                	<?php }                 
			                	}
			                	?>
			                </td>		                                                
		            	</tr>
					<?php 
					} 
				} ?>
				</tbody>
			</table>
		</div>
		<?php
	}
	
	function buscaDescontoscomissao($var){
	
	    $periodo 	= $var['periodo'];
	    $where 		= " and p.data_vend like '".$periodo."%'";
	    
		$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$select = $db->select();
	
		$select->from(array('p'=>'tb_pedidos'), array('sum(p.desconto) as descvendas','id_representante'))
			->where('p.status = "ped" and p.sit = 0'.$where)
			->group('p.id_representante');
	
		$stmt = $db->query($select);
		return $stmt->fetchAll();
	}
	
	
	function detalhaComissao($var=array()){
		$usuario = Zend_Auth::getInstance()->getIdentity();
		 
		$periodo 	= $var['periodo'];
		$where 		= " and p.data_vend like '".$periodo."%'";
		
		if(!empty($var['rep'])){
			$where .= ($var['tp'] == 1) ? " and p.id_representante = '".$var['rep']."'" : " and p.id_televenda = '".$var['rep']."'";
		}else{
			$where .= ($usuario->id_perfil == 3) ? " and p.id_representante = '".$usuario->id_cliente."'" : " and p.id_televenda = '".$usuario->id."'";
		}  
				
		$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		$db->setFetchMode(Zend_Db::FETCH_OBJ);	
			
		$select = $db->select();
		$select->from(array('p'=>'tb_pedidos','*'), array('sum(pd.qt*pd.preco_unit) as precototal','p.desconto','p.comissaorep','p.comissaovend',
			'c.EMPRESA','p.id as idped','DATE_FORMAT(p.data_vend,"%d/%m/%Y %H:%i") as dtvenda'))
			->join(array('pd'=>'tb_pedidos_prod'), 'pd.id_ped = p.id')
			->join(array('c'=>'clientes'), 'c.ID = p.id_parceiro')
		->where("p.status = 'ped' and p.sit = 0 ".$where)
		->group('p.id');
		 
		$stmt = $db->query($select);
		return $stmt->fetchAll();
						 
		
	
		/* $select = $db->select();
		$select->from(array('p'=>'tb_pedidos','*'), array('sum(pd.qt*pd.preco_unit) as precototal','p.desconto','p.comissaovend'))
		->join(array('pd'=>'tb_pedidos_prod'), 'pd.id_ped = p.id')
		->where("p.status = 'ped' and p.sit = 0 and p.id_televenda = '".$lista->iduser."'".$where)
		->group('p.id'); */
						 
					
	}
	
	function gerarComissaopainel(){
		$usuario = Zend_Auth::getInstance()->getIdentity();
		
		$data = date("01/m/Y");
		$cont = 0;
		$comissoesData = array();
		while($cont <= 5){
			$dataBusca = substr($data, 6,4)."-".substr($data, 3,2);
			
			$where 		= " and p.data_vend like '".$dataBusca."%'";
			
			$where .= ($usuario->id_perfil == 3) ? " and p.id_representante = '".$usuario->id_cliente."'" : " and p.id_televenda = '".$usuario->id."'";
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
			$select = $db->select();
			$select->from(array('p'=>'tb_pedidos','*'), array('sum(pd.qt*pd.preco_unit) as precototal','p.desconto','p.comissaorep','p.comissaovend',
					'c.EMPRESA','p.id as idped','DATE_FORMAT(p.data_vend,"%d/%m/%Y %H:%i") as dtvenda'))
					->join(array('pd'=>'tb_pedidos_prod'), 'pd.id_ped = p.id')
					->join(array('c'=>'clientes'), 'c.ID = p.id_parceiro')
					->where("p.status = 'ped' and p.sit = 0 ".$where)
					->group('p.id');
				
			$stmt = $db->query($select);
			
			$comissao = 0;
			foreach ($stmt->fetchAll() as $lista){
				$percomissao = ($usuario->id_perfil == 3) ? $lista->comissaorep : $lista->comissaovend;
				$comissao += (($lista->precototal-$lista->desconto)*$percomissao)/100;
			}
			
			
			$comissoesData[substr($data,3)] = $comissao;
			
    		$thisyear = substr ( $data, 6, 4 );
    		$thismonth = substr ( $data, 3, 2 );
    		$thisday = substr ( $data, 0, 2 );
    		$nextdate = mktime ( 0, 0, 0, $thismonth-1, $thisday, $thisyear );
    		
			$data = strftime("%d/%m/%Y", $nextdate);
    		$cont++;
    		
 		}
 		
 		return $comissoesData;
	}
	
	
	/*
	 * */	
	function gravarComissao($params){
	    $bop	= new PedidosvendaModel();
	    $boc	= new VendacomissaoModel();
	    
	    $emissao = date("t", mktime(0, 0, 0, substr($params['periodo'],5,2), 1, substr($params['periodo'], 0,4)))."/".substr($params['periodo'],5,2)."/".substr($params['periodo'], 0,4);
	   	
	    $busca['idparceiro']		= $params['rep'];
	    foreach (ClientesBO::buscaParceiros("",$busca) as $cliente);
	    
	    if($cliente->id_perfil == 3):
	    	$plano = 178;
	    else:
	    	$plano = 177;
	    endif;
	    
	    $data = array(
	    	'fornpag'					=> $params['rep']."|0",
	        'emissaopag'				=> $emissao,
            'faturapag'					=> substr($params['periodo'],5,2)."/".substr($params['periodo'], 0,4),
            'moedapagconta'				=> "BRL",
            'valortotalpag'				=> $params['valor'],
            'vencpar_1'					=> date('15/m/Y'),
            'moedapar_1'				=> "BRL",
            'valorpar_1'				=> $params['valor'],
            'contapar_1'				=> $plano,
            'intparcela'				=> 1
	    );
	    	    
	    $id = FinanceiroBO::gravarContaspag($data);
	    
	    $comissao = array(
	        'id_representante' 	=>   $params['rep'],
            'id_financeiropag' 	=>   $id,
            'competencia' 		=>   $params['periodo']."-01",
	    );
	    
	    $boc->insert($comissao);
	}
	
	function gravarComissaovendedores($params){
		$bop	= new PedidosvendaModel();
		$boc	= new VendacomissaotelevendaModel();
		 
		$emissao = date("t", mktime(0, 0, 0, substr($params['periodo'],5,2), 1, substr($params['periodo'], 0,4)))."/".substr($params['periodo'],5,2)."/".substr($params['periodo'], 0,4);
		 
		$data = array(
			'fornpag'					=> $params['ven']."|1",
			'emissaopag'				=> $emissao,
			'faturapag'					=> substr($params['periodo'],5,2)."/".substr($params['periodo'], 0,4),
			'moedapagconta'				=> "BRL",
			'valortotalpag'				=> $params['valor'],
			'vencpar_1'					=> date('15/m/Y'),
			'moedapar_1'				=> "BRL",
			'valorpar_1'				=> $params['valor'],
			'contapar_1'				=> 177,
			'intparcela'				=> 1
		);
		
		$id = FinanceiroBO::gravarContaspag($data);
		 
		$comissao = array(
			'id_usuarios' 		=>   $params['ven'],
			'id_financeiropag' 	=>   $id,
			'competencia' 		=>   $params['periodo']."-01",
		);
		 
		$boc->insert($comissao);
	}
	
	//--- Vendas mobile ------------------------------------------
	//----- Detalhamento por representantes  --------------------------------------------------------------------------
	function buscaTotalvendas(){
	
	    $usuario = Zend_Auth::getInstance()->getIdentity();
	    	
	    $usuario = Zend_Auth::getInstance()->getIdentity();
	    foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $listr);
	    if($listr->nivel==1){
		    if($listr->reggen == 1){
		    	$clireg = "";
			    foreach (ClientesBO::buscaParceiros('clientes') as $clientes){
			    	$clireg .= $clientes->ID.",";
			    }
		    
	   			if(!empty($clireg)){
	    			$where .= " and c.ID in (".substr($clireg,0,-1).")";
	   			}else{
			    	$where .= " and c.ID < 0";
	   			}
	    	}else{
		    	$where .= " and p.id_representante = ".$usuario->ID;
	    	}
	    }
	    	    
	  	//--- 7 meses anteriores -------------------------------------------------------------------------
		$dt = date('Y-m-01', strtotime("-6 month"));
		$wherefat = " and data_vend between ('".$dt."') and ('".date('Y-m-31')."')";
		 
		$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$select = $db->select();
		
		$select->from(array('p'=>'tb_pedidos'), array('sum(p.desconto) as peddesc','sum(pd.qt*pd.preco_unit) as precototal', 'EXTRACT(MONTH FROM data_vend) as mesvenda', 'EXTRACT(YEAR FROM data_vend) as anovenda'))
			->join(array('pd'=>'tb_pedidos_prod'), 'pd.id_ped = p.id')
			->join(array('c'=>'clientes'), 'c.ID = p.id_parceiro')
			->joinLeft(array('cr'=>'clientes_regioes'), 'cr.ID = c.ID_REGIOES')
			->where('p.status = "ped" and p.sit = 0 '.$wherefat.$where)
			->group('mesvenda')
			->group('anovenda')
			->order('mesvenda')
			->order('anovenda')
		;
		
		$stmt = $db->query($select);
		$objres = $stmt->fetchAll();
		
		if(count($objres)>0){
		    $i = 0;
		    foreach ($objres as $mesatual){
			    $i++;
				
			   	$wheredesc = " and data_vend like ('".$mesatual->anovenda."-".str_pad($mesatual->mesvenda,2,'0',STR_PAD_LEFT)."%')";
			    
			    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			    $db->setFetchMode(Zend_Db::FETCH_OBJ);
			    $select = $db->select();
			    
			    $select->from(array('p'=>'tb_pedidos'), array('sum(p.desconto) as descvendas'))
			    	->join(array('c'=>'clientes'), 'c.ID = p.id_parceiro')
			    	->joinLeft(array('cr'=>'clientes_regioes'), 'cr.ID = c.ID_REGIOES')
			    	->where('p.status = "ped" and p.sit = 0'.$wheredesc.$where);
			    
			    $stmt = $db->query($select);
			    
			    $stmt = $db->query($select);
			    $objdesc = $stmt->fetchAll();
			    
			    if(count($objdesc)>0){
			        foreach ($objdesc as $desc);
			        $desconto = $desc->descvendas;
			    }else{
			        $desconto = 0;
			    }
				$arrayres['mes'.$i] = $mesatual->precototal-$desconto;
				 
			}
		}
		
	
		
		//--- media do ano anterior -------------------------------------------------------------------------
		
		$dt = date('d-m-Y', strtotime("-1 year"));
		$dtinicial = date('Y-m', strtotime("-1 month", strtotime(date('d-m-Y', strtotime("-1 year")))))."-01";
		$dtfinal = date('Y-m', strtotime("-1 month", strtotime(date('d-m-Y'))))."-".date("t", mktime(0,0,0,date('m', strtotime("-1 month")),'01',date('Y')));
		
		$where = " and data_vend >= '".$dtinicial."' and data_vend <= '".$dtfinal."'" ;
				
		$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$select = $db->select();
		
		$select->from(array('p'=>'tb_pedidos'), array('sum(qt*preco_unit) as precototal'))
			->join(array('pd'=>'tb_pedidos_prod'), 'pd.id_ped = p.id')
			->join(array('c'=>'clientes'), 'c.ID = p.id_parceiro')
			->joinLeft(array('cr'=>'clientes_regioes'), 'cr.ID = c.ID_REGIOES')
			->where('p.status = "ped" and p.sit = 0'.$where);
		
		$stmt = $db->query($select);
		
		$objres = $stmt->fetchAll();
		
		if(count($objres)>0){
			foreach ($objres as $mesatual);
			$media = $mesatual->precototal/12;
		}else{
			$media = 0;
		}
		
		$arrayres['media'] = $media;
		
		return $arrayres; //array('mes1' => $mes1, 'mes2' => $mes2, 'mes3' => $mes3, 'mes4' => $mes4, 'media' => $media);
		
	}
	
	function verificafinFaturamento(){
	    
	    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
	    $db->setFetchMode(Zend_Db::FETCH_OBJ);
	    $select = $db->select();
	    
	    $select->from(array('n'=>'tb_nfe'), array('*','f.id as idfin'))
	    	->join(array('f'=>'tb_financeirorec'), 'f.id_nfe = n.id')
		    ->where('n.status != 1 and f.sit = 1');
	    
	    $stmt = $db->query($select);
	    $obj = $stmt->fetchAll();
	    
	    if(count($obj)>0){
		    foreach($obj as $contas){
		        ?>
		        <a target="_blank" href="/admin/nfe/visualizarnfe/nfe/<?=md5($contas->id_nfe)?>"><?="NFe".substr("000000".$contas->id_nfe,-6,6);?></a>
		         - 
		        <a target="_blank" href="/admin/administracao/financeiroztlreccad/rec/<?=md5($contas->idfin)?>" target="_blank">R<?=substr("00000".$contas->idfin, -6,6)?></a>
		        <?php 
		   		echo "<br />"; 
		    }
	    }else{
			echo "Nenhum erro encontrado no financeiro";
		}	    
	}
	
	function verificaestoqueFaturamento(){
		$bo = new EstoqueModel();
		
		/* select p.id, z.* from tb_pedidos p left join tb_estoqueztl z on (p.id = z.id_atualizacao and tipo = 'VENDA')
		where z.id is NULL
		limit 20 */
		
		$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$select = $db->select();
		 
		$select->from(array('p'=>'tb_pedidos'), array('pp.id_prod as idproduto','pp.id_ped as idpedido','pp.qt as qtprod'))
			->join(array('pp'=>'tb_pedidos_prod'), 'pp.id_ped = p.id')
			->joinLeft(array('z'=>'tb_estoqueztl'), 'pp.id_prod = z.id_prod  and pp.id_ped = z.id_atualizacao and z.tipo = "VENDA"')
			->where('status = "ped" and p.sit = 0 and pp.id_ped = p.id and p.data_vend >= "2010-07-09" and z.id is NULL and pp.id_prod is not NULL');
		
		$stmt = $db->query($select);
		
		$obj = $stmt->fetchAll();
		 
		if(count($obj)>0){		
			foreach($obj as $contas){
				if(!empty($contas->idproduto)) foreach(ProdutosBO::listaProdutos($contas->idproduto) as $prod);
				?>
				<a target="_blank" href="/admin/venda/pedidosvenda/ped/<?=md5($contas->idpedido)?>" ><?=$contas->idpedido?></a>
				 - 
				<a target="_blank" href="/admin/compras/extratoprod/codproduto/<?php echo $prod->CODIGO?>" target="_blank"><?php echo $prod->CODIGO?></a>
				 - 
				<?php echo $contas->qtprod?> 
				<?php 
				echo "<br />";
				
			}
			
			?>
			<input type="button" value="Corrigir vendas" onclick="window.location='/admin/relatorios/correcaoestoque'" class="basicBtn">
			<?php 
		}else{
			echo "Nenhum erro encontrado no estoque";
		}
	}		
	
	function buscaErroestoque(){
		$bo = new EstoqueModel();
		
		foreach($bo->fetchAll("id_atualizacao <= 5 and tipo = 'VENDA'") as $contas){

			$data = "";
			$data = substr($contas->dt_atualizacao,0,16);
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
				
			$select->from(array('p'=>'tb_pedidos'), array('*'))
				->join(array('pp'=>'tb_pedidos_prod'), 'pp.id_ped = p.id')
				->where('status = "ped" and p.sit = 0 and pp.id_ped = p.id and p.data_vend like "'.$data.'%" and pp.id_prod = '.$contas->id_prod);
			
			$stmt = $db->query($select);
						
			$obj = $stmt->fetchAll();
			
			echo $contas->id." - ".$data;
			
			if(count($obj)>1){
				foreach($obj as $ped){
					echo $ped->id_ped." - ".$ped->id_prod." - ".$contas->id_atualizacao." - ".$contas->dt_atualizacao." - ".$ped->data_vend;
					echo "<br />";
				}
			}elseif(count($obj)==1){
				foreach($obj as $ped);
				echo $ped->id_ped." - ".$ped->id_prod." - ".$contas->id_atualizacao." - ".$contas->dt_atualizacao." - ".$ped->data_vend;
				$array = array('id_atualizacao' => $ped->id_ped, 'obs' => 'Correção ID atualização');
				$bo->update($array, "id = ".$contas->id); 			
			}
			
			echo "<br />";
			
		}
	
	}	
	
	function buscaCurvaclientes($val){
		$usuario 	= Zend_Auth::getInstance()->getIdentity();
		$where 		= $wheres = "";
		$whereped 	= "";
		$bo			= new PedidosvendaModel();
		$boc		= new ContatosModel();
		$bocm 		= new ContatosempModel();
		
		if((!empty($val['dataini'])) || (!empty($val['datafim']))){
			$dataini = substr($val['dataini'],6,4).'-'.substr($val['dataini'],3,2).'-'.substr($val['dataini'],0,2);
			$datafim = substr($val['datafim'],6,4).'-'.substr($val['datafim'],3,2).'-'.substr($val['datafim'],0,2);
		
			if((!empty($val['dataini'])) and (!empty($val['datafim']))):
				$where 		= " and p.data_vend between '".$dataini."' and '".$datafim." 23:59:59'";			
				$wheres 	= " and sp.data_vend between '".$dataini."' and '".$datafim." 23:59:59'";
			elseif((!empty($val['dataini'])) and (empty($val['datafim']))):
				$where 		= " and p.data_vend >= '".$dataini."'";
				$wheres 	= " and sp.data_vend >= '".$dataini."'";
				$datafim 	= date("Y-m-d");			
			elseif((empty($val['dataini'])) and (!empty($val['datafim']))):
				$where 		= " and p.data_vend <= '".$datafim."'";
				$wheres 	= " and sp.data_vend <= '".$datafim."'";
				$dataini 	= "2005-01-01";
			endif;
		}else{
			$dataini = date("Y-m-01", strtotime("-11 month"));
			$datafim = date("Y-m-31");
		}
				
		if(!empty($val['representante']) and ($val['representante'] != 0)){
			$rep = explode("|", $val['representante']);

			$where	.= " and p.id_representante = '".$rep[0]."'";
			$wheres	.= " and sp.id_representante = '".$rep[0]."'";
			$whereped = " and c.ID_REGIOES = '".$rep[1]."'";
			
		}
			
		if(!empty($val['televenda']) and ($val['televenda'] != 0)){
			$tel = explode("|", $val['televenda']);

			$where 	.= " and p.id_televenda = '".$tel[0]."'";
			$wheres	.= " and sp.id_televenda = '".$tel[0]."'";
			$whereped .= " and c.id_regioestelevendas = '".$tel[1]."'";
		}

		if(!empty($val['cliente']) and ($val['cliente'] != 0)){
			$where 	.= " and p.id_parceiro = '".$val['cliente']."'";
			$wheres .= " and sp.id_parceiro = '".$val['cliente']."'";
			$whereped .= " and c.ID = '".$val['cliente']."'";
		}
		
		//--- Controle de perfil ------------------------------------------
		foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
		if($list->nivel==1){
			foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
			if($list->nivel == 1){
				if($usuario->id_perfil == 31){
					$where .= " and c.id_regioestelevendas in (".RegioesBO::listaRegioesusuarios(1).")";
					$whereped .= " and c.id_regioestelevendas in (".RegioesBO::listaRegioesusuarios(1).")";
				}elseif(($usuario->id_perfil == 4) || ($usuario->id_perfil == 5)){
					$where .= " and (c.ID_REGIOES in (".RegioesBO::listaRegioesusuarios(0).") || c.id_regioestelevendas in (".RegioesBO::listaRegioesusuarios(1)."))";
					$whereped .= " and (c.ID_REGIOES in (".RegioesBO::listaRegioesusuarios(0).") || c.id_regioestelevendas in (".RegioesBO::listaRegioesusuarios(1)."))";
				}else{
					$where .= " and c.ID_REGIOES in (".RegioesBO::listaRegioesusuarios(0).")";
					$whereped .= " and c.ID_REGIOES in (".RegioesBO::listaRegioesusuarios(0).")";
				}
			}
		}elseif($list->nivel==0){
			$where .= " and ID = ".$usuario->id_cliente;
			$whereped .= " and ID = ".$usuario->id_cliente;
		}
		
		
		$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		
		//-- lista clientes do filtro -------------------------------------------------------------------------------
		
		$select = $db->select();
		$select->from(array('p'=>'tb_pedidos'), array('c.ID as idcli', 'c.EMPRESA', 'ec.meta as metacontato', 'ec.obsmeta as obsmetacontato',
			'(sum(pp.preco_unit * pp.qt) - (select sum(desconto) FROM tb_pedidos sp where sp.id_parceiro = c.ID '.$wheres.')) AS valor'))
			->join(array('pp'=>'tb_pedidos_prod'), 'pp.id_ped = p.id')
			->join(array('c'=>'clientes'), 'c.ID = p.id_parceiro')
			->joinLeft(array('d'=>'clientes_desc'), 'd.id_cliente = c.ID')
			->joinLeft(array('ec'=>'tb_contatosemp'), 'ec.id_clientes = c.ID')
			->where('p.status = "ped" and p.sit = 0 '.$where)
			->group('c.ID')
			->order('valor desc');
				
		$stmt 	= $db->query($select);
		$bojcli = $stmt->fetchAll();
		
		if ( count($bojcli) > 0) {
			
			$startDate = substr($dataini,0,4);
			$endDate   = substr($datafim,0,4);
				
			$contano = 0;
			while ($endDate >= $startDate) {
				$startDate++;
				$contano++;				
			}
			
			?>
			<table style="width: 100%" class="tableStatic">
            	<thead>
                	<tr>
                        <td width="7%">Ordem</td>
                        <td >Empresa</td>
                        <td width="7%">Contato</td>
                        <td width="4%">Obs</td>
                        <td width="7%">Meta</td>
                        <?php 
                        if($contano>1){
							$startDate = substr($dataini,0,4);
							$endDate   = substr($datafim,0,4);
							while ($endDate >= $startDate) {
								echo "<td >".$startDate."</td>";
								$startDate++;
							}
						}
                        ?>
                        <td >Valor Total</td>                        
                    </tr>
                </thead>
                <tbody>		               
				<?php 
				$cor=0;
				$clientes = "";
		    	foreach($bojcli as $contas){
		    		$cor++;
			    	?>				
					<tr >
		                <td style="text-align: center;" >
		                   <?php echo $cor?>
		                </td>
		                <td align="left">
		                	<a href="javascript:void(0);" rel="<?php echo $contas->ID?>" class="cliente">
		                   		<?php echo $contas->EMPRESA?>                  
		                   	</a>
		                </td>
		                <td align="center">
		                   <?php 
		                   $matriz = $bocm->fetchRow("id_clientes = '".$contas->idcli."'");
		                   if($matriz){
							   $tp = ($matriz->id_matriz) ? "F" : "M";
			                   ?><a target="_blank" href="/admin/cadastro/contatosempcad/empresa/<?=md5($matriz->id)?>"><?php echo $tp.$matriz->id?></a><?php
			               } 
		                   		                   
		                   ?>
		                                     
		                </td>
		                <td align="left">
		                <?php if($contas->obsmetacontato !=""){ ?>
		                   <img src="/public/sistema/imagens/icons/middlenav/info.png" width="18px" title="<?php echo $contas->obsmetacontato?>" />
		                <?php } ?>
		                </td>
		                <td align="right">
		                    <?php echo number_format($contas->metacontato,2,",",".")?>
		                </td>
		                <?php
		                $startDate = substr($dataini,0,4);
		                $endDate   = substr($datafim,0,4);
                        if($contano>1){
							while ($endDate >= $startDate) {
								
								$whereano 	= ' and p.data_vend like "'.$startDate.'%"';
								$wheresano 	= ' and sp.data_vend like "'.$startDate.'%"';

								$select = $db->select();
								$select->from(array('p'=>'tb_pedidos'), array('(sum(pp.preco_unit * pp.qt) - (select sum(desconto) FROM tb_pedidos sp where sp.id_parceiro = '.$contas->idcli.$wheres.$wheresano.')) AS valor'))
									->join(array('pp'=>'tb_pedidos_prod'), 'pp.id_ped = p.id')
									->join(array('c'=>'clientes'), 'c.ID = p.id_parceiro')
									->where('status = "ped" and p.sit = 0 and p.id_parceiro = '.$contas->idcli.$where.$whereano);
								
								$stmt 	= $db->query($select);
								$bojano = $stmt->fetchAll();
								
								foreach ($bojano as $valoscli);
								?><td align='right' style="cursor: pointer;" onclick='buscaMes("<?php echo $dataini?>","<?php echo $datafim?>","<?php echo $contas->idcli?>","<?php echo $startDate?>")'><?php echo number_format($valoscli->valor,2,",",".")?></td><?php 
								$startDate++;
							}
						}
                        ?>
		                <td align="right" <?php if($contano==1){ ?> style="cursor: pointer;" onclick='buscaMes("<?php echo $dataini?>","<?php echo $datafim?>","<?php echo $contas->idcli?>","<?php echo $startDate?>")' <?php } ?>>
		                   <?php echo number_format($contas->valor,2,",",".")?>
		                </td>
		            </tr>
            		<?php  
					$clientes .= $contas->idcli.",";
				}
            	
            	if(!isset($val['faturados']) || $val['faturados'] == ""){

            		$select = $db->select();
            		$select->from(array('c'=>'clientes'), array('c.ID as idcli', 'c.EMPRESA'))
            			->where('c.sit = true and id_perfil in (2,24,27,28) and c.ID not in ('.substr($clientes,0,-1).') '.$whereped)
            			->order('c.EMPRESA'); 
            		            	
	            	$stmt 		= $db->query($select);
	            	
	            	$bojncli 	= $stmt->fetchAll();
	            	if ( count($bojncli) > 0) {
		            	foreach($bojncli as $contas){
						$cor++;
						?>
						<tr >
			                <td style="text-align: center;" >
			                   <?php echo $cor?>
			                </td>
			                <td align="left">
			                   <?php echo $contas->EMPRESA?>                  
			                </td>
			                <td align="left">
			                <?php 
			                   $matriz = $bocm->fetchRow("id_clientes = '".$contas->idcli."'");
			                   
			                   if($matriz){
									$tp = ($matriz->id_matriz) ? "F" : "M";
				               		?><a target="_blank" href="/admin/cadastro/contatosempcad/empresa/<?=md5($matriz->id)?>"><?php echo $tp.$matriz->id?></a><?php
				               } 
		                   	?>              
			                </td>
			                <td style="text-align: center;" >&nbsp;</td>
			                <td style="text-align: center;" >&nbsp;</td>
			                <?php
			                $startDate = substr($dataini,0,4);
			                $endDate   = substr($datafim,0,4);
	                        if($contano>1){
								while ($endDate >= $startDate) {
									?><td align='right' >0,00</td><?php 
									$startDate++;
								}
							}
	                        ?>
			                <td align="right" >0,00</td>
			            </tr>
	            	<?php  }
	            	}            	
            	}
            	?>
            	</tbody>        
			</table>
				
		<?php
		}else{
		?>
			<div style="padding: 10px; border-top: 1px solid #d5d5d5;">
	 			Sem vendas neste período
			</div>	
		<?php 
		}
		
	}	
	
	function buscaFaturamento($params){
		$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		
		$startYear 	= substr($params['dataini'], 0,4);
		$startMonth	= substr($params['dataini'], 5,2);
		
		$endYear 	= substr($params['datafim'], 0,4);
		$endMonth	= substr($params['datafim'], 5,2);
		
		$startDate = strtotime("$startYear/$startMonth/01");
		$endDate   = strtotime("$endYear/$endMonth/01");
		$currentDate = $startDate;
		
		//--- monto cabecario da tabela com os meses ------------------------------------------------
		echo '<div class="widget" style="width: 100%; margin-top: -5px"><table style="width: 100%" class="tableStatic"><thead><tr>';
		while ($currentDate <= $endDate) {

			if(date('Y',$currentDate) == $params['ano'])  echo '<td>'.date('m/Y',$currentDate).'</td>';
			$currentDate = strtotime( date('Y/m/01/',$currentDate).' +1 month');
		}
		echo '</tr></thead><tbody><tr>';
		
		//-- monto os valores de venda de cada mes --------------------------------------------------
		
		$where 		= " and p.data_vend between '".$params['dataini']."' and '".$params['datafim']." 23:59:59'";
		$wheres 	= " and sp.data_vend between '".$params['dataini']."' and '".$params['datafim']." 23:59:59'";
		
		$startDate = strtotime("$startYear/$startMonth/01");
		$endDate   = strtotime("$endYear/$endMonth/01");
		$currentDate = $startDate;
		while ($currentDate <= $endDate) {

			if(date('Y',$currentDate) == $params['ano']){
				$whereano 	= ' and p.data_vend like "'.date('Y-m',$currentDate).'%"';
				$wheresano 	= ' and sp.data_vend like "'.date('Y-m',$currentDate).'%"';
	
				$select = $db->select();
				$select->from(array('p'=>'tb_pedidos'), array('(sum(pp.preco_unit * pp.qt) - (select sum(desconto) FROM tb_pedidos sp where sp.id_parceiro = '.$params['cliente'].$wheres.$wheresano.')) AS valor'))
					->join(array('pp'=>'tb_pedidos_prod'), 'pp.id_ped = p.id')
					->join(array('c'=>'clientes'), 'c.ID = p.id_parceiro')
					->where('status = "ped" and p.sit = 0 and p.id_parceiro = '.$params['cliente'].$where.$whereano);
				
				$stmt 	= $db->query($select);
				$bojano = $stmt->fetchAll();
				
				foreach ($bojano as $valoscli);
				
				echo '<td>'.number_format($valoscli->valor,2,",",".").'</td>';
			}
			
			$currentDate = strtotime( date('Y/m/01/',$currentDate).' +1 month');
		}
				
		echo '</tr></tbody></table></div>';
		
	}	
	
	
	function corrigeParceiro(){
		$where 		= $wheres = "";
		$whereped 	= "";
		$bo			= new PedidosvendaModel();
		$boc		= new ContatosModel();
		$bocm 		= new ContatosempModel();
		$bocli		= new ClientesModel();
	
		$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
	
		//-- lista clientes do filtro -------------------------------------------------------------------------------
	
		$select = $db->select();
		$select->from(array('p'=>'tb_pedidos'), array('p.id_parceiro as idcli'))
				->where('p.status = "ped" and p.sit = 0 ')
				->group('p.id_parceiro');
	
		$stmt 	= $db->query($select);
		$bojcli = $stmt->fetchAll();
	
		if ( count($bojcli) > 0) {
						
			?>
				<html lang="pt-br">
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				</head>
				<table style="width: 100%" class="tableStatic">
	            	
	                <tbody>		               
					
					<?php  
					$cor=0;
					$clientes = "";
			    	foreach($bojcli as $contas){
			    		$clientes .= $contas->idcli.",";
					}
	            	
            		$select = $db->select();
            		$select->from(array('c'=>'clientes'), array('c.ID as idcli', 'c.EMPRESA','e.id as idcontato'))
            			->joinLeft(array('e'=>'tb_contatosemp'), 'e.id_clientes = c.ID')
            			->where('c.sit = true and c.TIPO like "ativo%" and id_perfil in (2,24,27,28) and c.ID not in ('.substr($clientes,0,-1).') '.$whereped)
            			->order('c.EMPRESA'); 
	            		            	
		            	$stmt 		= $db->query($select);
		            	
		            	$bojncli 	= $stmt->fetchAll();
		            	if ( count($bojncli) > 0) {
			            	foreach($bojncli as $contas){
							$cor++;
							?>
							<tr >
				                <td align="left">
				                   <?php echo $cor?>
				                </td>				                
				                <td align="left">
				                   <?php echo $contas->idcli?>
				                </td>
				                <td align="left">
				                   <?php echo $contas->EMPRESA?>
				                </td>
				                <td align="left">
				                   <?php echo $contas->idcontato?>
				                </td>
				            </tr>
		            		<?php  
							
		            		//if(!empty($contas->idcontato)) $bocm->update(array('id_clientes' => NULL), 'id = "'.$contas->idcontato.'"');

		            		$bocli->update(array('TIPO' => 'inativo'), 'ID = '.$contas->idcli);
		            		
							}
		            	}            	
	            	}
	            	?>
	            	</tbody>        
				</table>
				</html>	
			<?php
			
		}	
	
}
?>