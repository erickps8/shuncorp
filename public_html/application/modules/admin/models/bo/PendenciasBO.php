<?php
	class PendenciasBO{		
		function listarPendencias($pesq){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			if((!empty($pesq['dt_inicial'])) and (!empty($pesq['dt_final']))){
				$dt_ini = substr($pesq['dt_inicial'],6,4).'-'.substr($pesq['dt_inicial'],3,2).'-'.substr($pesq['dt_inicial'],0,2);
				$dt_fin = substr($pesq['dt_final'],6,4).'-'.substr($pesq['dt_final'],3,2).'-'.substr($pesq['dt_final'],0,2);
				$where = "p.dt_pend between '".$dt_ini."' and  '".$dt_fin."'";  
			}

			if(!empty($pesq['pesq_emp'])){
						
				$select->from(array('p'=>'tb_pedidos_pend','*'),
				        array('sum(p.qt) as qttotal','sum(p.valor*p.qt) as valortotal','DATE_FORMAT(p.dt_pend,"%c/3/%Y") as dt_pend'))
				        ->where("p.id_cliente = ".$pesq['pesq_emp']." and ".$where)
				        ->group("extract(month from p.dt_pend)");
			        
			}elseif(!empty($pesq['pesq_reg'])){
				$select->from(array('p'=>'tb_pedidos_pend','*'),
				        array('sum(p.qt) as qttotal','sum(p.valor*p.qt) as valortotal','DATE_FORMAT(p.dt_pend,"%c/3/%Y") as dt_pend'))
				        ->join(array('c'=>'clientes'),'c.ID = p.id_cliente')
				        ->where("c.ID_REGIOES = ".$pesq['pesq_reg']." and ".$where)
				        ->group("extract(month from p.dt_pend)");
				
			}elseif(!empty($pesq['cod_prod'])){
				$select->from(array('p'=>'tb_pedidos_pend','*'),
				        array('sum(p.qt) as qttotal','sum(p.valor*p.qt) as valortotal','DATE_FORMAT(p.dt_pend,"%c/3/%Y") as dt_pend'))
				        ->join(array('pr'=>'produtos'),'pr.ID = p.id_prod')
				        ->where("pr.CODIGO = '".$pesq['cod_prod']."' and ".$where)
				        ->order("p.dt_pend")
				        ->group("extract(month from p.dt_pend)");
				        				
			}
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
			
			/*			SELECT  sum(p.qt) as qttotal, sum(p.preco_unit*p.qt) as valortotal, DATE_FORMAT(t.data_vend,"%c/3/%Y") as dt_vend  
			FROM tb_pedidos_prod p, tb_pedidos t
			where t.id = p.id_ped and t.data_vend != "" group by extract(month from t.data_vend)
			;*/
			
		}
		
		function listarVendasvalor($pesq){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			if((!empty($pesq['dt_inicial'])) and (!empty($pesq['dt_final']))){
				$dt_ini = substr($pesq['dt_inicial'],6,4).'-'.substr($pesq['dt_inicial'],3,2).'-'.substr($pesq['dt_inicial'],0,2);
				$dt_fin = substr($pesq['dt_final'],6,4).'-'.substr($pesq['dt_final'],3,2).'-'.substr($pesq['dt_final'],0,2);
				$where = "t.data_vend between '".$dt_ini."' and  '".$dt_fin."'  and t.data_vend != ''";  
			}

			if(!empty($pesq['pesq_emp'])){
						
				$select->from(array('p'=>'tb_pedidos_prod','*'),
				        array('sum(p.qt) as qttotal','sum(p.preco_unit*p.qt) as valortotal','DATE_FORMAT(t.data_vend,"%c/3/%Y") as dt_vend'))
				        ->join(array('t'=>'tb_pedidos'),'t.id = p.id_ped')
				        ->where("t.id_parceiro = ".$pesq['pesq_emp']." and ".$where)
				        ->group("extract(month from t.data_vend)");
			        
			}elseif(!empty($pesq['pesq_reg'])){
				$select->from(array('p'=>'tb_pedidos_pend','*'),
				        array('sum(p.qt) as qttotal','sum(p.valor*p.qt) as valortotal','DATE_FORMAT(p.dt_pend,"%c/3/%Y") as dt_pend'))
				        ->join(array('t'=>'tb_pedidos'),'t.id = p.id_ped')
				        ->join(array('c'=>'clientes'),'c.ID = t.id_parceiro')
				        ->where("c.ID_REGIOES = ".$pesq['pesq_reg']." and ".$where)
				        ->group("extract(month from t.data_vend)");
				
			}elseif(!empty($pesq['cod_prod'])){
				$select->from(array('p'=>'tb_pedidos_pend','*'),
				        array('sum(p.qt) as qttotal','sum(p.valor*p.qt) as valortotal','DATE_FORMAT(p.dt_pend,"%c/3/%Y") as dt_pend'))
				        ->join(array('t'=>'tb_pedidos'),'t.id = p.id_ped')
				        ->join(array('pr'=>'produtos'),'pr.ID = p.id_prod')
				        ->where("pr.CODIGO = '".$pesq['cod_prod']."' and ".$where)
				        ->order("t.data_vend")
				        ->group("extract(month from t.data_vend)");
				        				
			}
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
			
				/*		SELECT  sum(p.qt) as qttotal, sum(p.preco_unit*p.qt) as valortotal, DATE_FORMAT(t.data_vend,"%c/3/%Y") as dt_vend  
		FROM tb_pedidos_prod p, tb_pedidos t
		where t.data_vend between "2010-03-01" and "2010-09-01" and t.id = p.id_ped and t.data_vend != "" group by extract(month from t.data_vend)
				;*/
			
		}
		
		function listarPendenciasAll($pesq){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			
			
			
			if($pesq['tp'] == 2){
				$select->from(array('p'=>'tb_pedidos_pend','*'),
				        array('p.id_ped','p.id_cliente','p.qt','p.id_prod','pr.CODIGO','DATE_FORMAT(p.dt_pend,"%d/%m/%Y") as dt_pend','c.EMPRESA','c.RAZAO_SOCIAL','c.CPF_CNPJ'))
				        ->join(array('c'=>'clientes'),'c.ID = p.id_cliente')
				        ->join(array('pr'=>'produtos'),'pr.ID = p.id_prod')
				        ->where("p.status = 0 and c.ID_REGIOES = ".$pesq['bsc'])
				        ->order("p.id_cliente");
				
			}elseif($pesq['tp'] == 4){
				$select->from(array('p'=>'tb_pedidos_pend','*'),
				        array('p.id_ped','p.id_cliente','p.qt','p.id_prod','pr.CODIGO','DATE_FORMAT(p.dt_pend,"%d/%m/%Y") as dt_pend','c.EMPRESA','c.RAZAO_SOCIAL','c.CPF_CNPJ'))
				        ->join(array('pr'=>'produtos'),'pr.ID = p.id_prod')
				        ->join(array('c'=>'clientes'),'c.ID = p.id_cliente')
				        ->where("p.status = 0 and pr.CODIGO = '".$pesq['bsc']."'")
				        ->order("p.id_cliente");
				        				
			}
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
			
		}
		
		function gerarPendencias($params){
			$bo = new PendenciasModel();
			$bov	= new PedidosvendaModel();
			$bop	= new PedidosvendaprodModel();
			
			$usuario = Zend_Auth::getInstance()->getIdentity();
			foreach (VendaBO::listaPedidosprod($params[ped]) as $listProd):
				if($params["pend_".$listProd->ID]==1):
					$array[id_ped]		= $params[ped];
					$array[id_cliente]	= $params[pedcli];
					$array[id_user]		= $usuario->ID;
					$array[dt_pend]		= date("Y-m-d H:i:s");
					$array[id_prod]		= $listProd->ID;
					$array[qt]			= $listProd->qt;
					$array[valor]		= $listProd->preco_unit;
					$bo->insert($array);
					
					$bop->delete("id_ped = ".$params[ped]." and id_prod = ".$listProd->ID);
					
				endif;
			endforeach;
		}
		
		function buscaPendencias($cli, $ped, $idperfil=""){
		    
		    $usuario 	= Zend_Auth::getInstance()->getIdentity();
		    	
		    if($idperfil==""):
			    $busca['idparceiro']		= $cli;
			    foreach (ClientesBO::buscaParceiros("",$busca) as $cliente);
			    $idperfil = $cliente->id_despesasfiscais;
		    endif;
		    	
		    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		    $db->setFetchMode(Zend_Db::FETCH_OBJ);
		    	
			$select = $db->select();
			
			$select->from(array('p'=>'tb_pedidos_pend','*'),
		        array('p.id as idpend','p.id_ped','p.id_cliente','sum(p.qt) as qttotal','p.id_prod','pr.CODIGO',
		                'DATE_FORMAT(p.dt_pend,"%d/%m/%Y") as dt_pend','pr.*','v.valor as custo','pc.markup','pc.markupmin'))
		        ->join(array('pr'=>'produtos'),'pr.ID = p.id_prod')
		        ->joinLeft(array('n'=>'tb_produtosncm'),'pr.id_ncm = n.id')
		        ->joinLeft(array('tn'=>'tb_tributosncm'),'tn.id_produtosncm = n.id and id_tributosfiscais = '.$idperfil)
		        ->joinLeft(array('v'=>'tb_produtoscmv'),'p.id_prod = v.id_produtos and v.id = (select max(v.id) from tb_produtoscmv v where v.id_produtos = p.id_prod)')
		        ->joinLeft(array('pc'=>'tb_produtosclasses'),'pc.id = pr.id_produtosclasses')
		        
		        ->where("(p.id_peddes != ".$ped." || p.id_peddes is NULL) and p.status = 0 and p.id_cliente = ".$cli)
		        ->order("pr.codigo_mask")
		        ->group("pr.ID");
				        
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		function listaPendenciasprod($pesq){
			$obj = new ClientesModel();
			$bor = new RegioesModel();
			$boc = new RegioesclientesModel();
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
					
			$sessaobusca = new Zend_Session_Namespace('pendencia');
		    if(isset($sessaobusca->where)):
		   		$where = $sessaobusca->where;
		   	endif;	
			
			if($pesq['buscagruposub']!=0 and $pesq['tipo']==1):
				$where = "";
				$where = ' and s.id = '.$pesq['buscagruposub'];
			elseif($pesq['tipo']==1):
				$where = "";
				if($pesq['buscagrupo']!=0):
					$where = ' and g.id = '.$pesq['buscagrupo'];
				endif;
			elseif($pesq['tipo']==2):
				$where = "";
				$where = ' and p.CODIGO like  "%'.$pesq['buscacod'].'%"';
			
			endif;
			
			if(!empty($where)):
		   		$sessaobusca->where = $where;
		   	endif;		   	
		   				
			//--- Controle de perfil ------------------------------------------
			foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
			if($list->nivel==1):
				$sql 	= "";
				foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
				if($list->nivel == 1):
					if($usuario->id_perfil == 31):
						$where .= " and c.id_regioestelevendas in (".RegioesBO::buscaRegioesusuariolog().")";
					else:
						$where .= " and c.ID_REGIOES in (".RegioesBO::buscaRegioesusuariolog().")";
					endif;
				endif;
			elseif($list->nivel==0):
				$where .= " and c.ID = ".$usuario->id_cliente;
			endif;
			
			
		  	$where = 'p.ID is not NULL'.$where;
		   	
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('p'=>'produtos'), array('p.ID', 'p.CODIGO','g.descricao as grupo','s.descricao as subgrupo', 'p.DESCRICAO', 'sum(d.qt) as qtprod'))
			        ->join(array('d'=>'tb_pedidos_pend'), 'p.ID = d.id_prod and d.status = 0')
			        ->join(array('c'=>'clientes'), 'c.ID = d.id_cliente')
			        
			        ->joinLeft(array('s'=>'tb_gruposprodsub'), 's.id = p.id_gruposprodsub')
			        ->joinLeft(array('g'=>'tb_gruposprod'), 'g.id = s.id_gruposprod')
			         
			        
			        ->where($where)
			        ->group("p.ID")
			        ->order("p.codigo_mask");
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}

		function listaPendenciasprodcli($pesq){
			$obj = new ClientesModel();
			$bor = new RegioesModel();
			$boc = new RegioesclientesModel();
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			//--- Controle de perfil ------------------------------------------
			foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
			if($list->nivel==1):
				$sql 	= "";
				foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
				if($list->nivel == 1):
					if($usuario->id_perfil == 31):
						$where .= " and c.id_regioestelevendas in (".RegioesBO::buscaRegioesusuariolog().")";
					else:
						$where .= " and c.ID_REGIOES in (".RegioesBO::buscaRegioesusuariolog().")";
					endif;
				endif;
			elseif($list->nivel==0):
				$where .= " and c.ID = ".$usuario->id_cliente;
			endif;
						
			
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('c'=>'clientes'), array('c.EMPRESA','c.CPF_CNPJ','sum(d.qt) as qtprod','d.id_ped','DATE_FORMAT(d.dt_pend,"%d/%m/%Y") as dtpend'))
			       	->join(array('d'=>'tb_pedidos_pend'), 'c.ID = d.id_cliente and d.status = 0')
			        ->where("md5(d.id_prod) = '".$pesq['produto']."'".$where)
			        ->group("c.ID")
			        ->order("c.EMPRESA");
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}

		function listaPendenciasemp($pesq){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('p'=>'produtos'), array('p.ID', 'p.CODIGO','p.DESCRICAO', 'd.qt','DATE_FORMAT(d.dt_pend,"%d/%m/%Y") as dtpend','d.id as idpend'))
			        ->join(array('d'=>'tb_pedidos_pend'), 'p.ID = d.id_prod and d.status = 0')
			        ->where("md5(d.id_cliente) = '".$pesq['empresa']."'")
			        ->order("d.id_ped");
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}	
		
		function listaPendenciasregiao($pesq){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
			$select = $db->select();
				
			$select->from(array('p'=>'produtos'), array('p.ID', 'p.CODIGO','p.DESCRICAO', 'd.qt','DATE_FORMAT(d.dt_pend,"%d/%m/%Y") as dtpend','d.id as idpend','e.qt_atual'))
				->join(array('d'=>'tb_pedidos_pend'), 'p.ID = d.id_prod and d.status = 0')
				->joinLeft(array('e'=>'tb_estoqueztl'),'p.ID = e.id_prod and e.id = (SELECT max(id) from tb_estoqueztl e where p.ID = e.id_prod)')
				->join(array('c'=>'clientes'), 'c.ID = d.id_cliente')
				->where("c.ID_REGIOES = ".$pesq)
				->order("d.id_ped");
				
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		function listaPendenciasestoque($pesq){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			$sessaobusca = new Zend_Session_Namespace('pendenciaest');
		    if(isset($sessaobusca->where)):
		   		$where = $sessaobusca->where;
		   	endif;	
			
			if($pesq['buscagruposub']!=0 and $pesq['tipo']==1):
				$where = "";
				$where = ' and s.id = '.$pesq['buscagruposub'];
			elseif($pesq['tipo']==1):
				$where = "";
				if($pesq['buscagrupo']!=0):
					$where = ' and g.id = '.$pesq['buscagrupo'];
				endif;
			elseif($pesq['tipo']==2):
				$where = "";
				$where = ' and p.CODIGO like  "%'.$pesq['buscacod'].'%"';			
			endif;
			
			if(!empty($where)):
		   		$sessaobusca->where = $where;
		   	endif;

		   	
		   	//--- Controle de perfil ------------------------------------------
			foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
			if($list->nivel!=2):
				$where = "p.ID = 0";
			endif;
			
		  	$where = 'p.ID is not NULL'.$where.$clientes;
		   	
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('p'=>'produtos'), array('p.ID', 'p.CODIGO','g.descricao as grupo','s.descricao as subgrupo', 'p.DESCRICAO', 'sum(d.qt) as qtprod','e.qt_atual'))
			        ->join(array('s'=>'tb_gruposprodsub'), 's.id = p.id_gruposprodsub')
			        ->join(array('g'=>'tb_gruposprod'), 'g.id = s.id_gruposprod')
			        ->join(array('d'=>'tb_pedidos_pend'), 'p.ID = d.id_prod and d.status = 0')
			        ->join(array('e'=>'tb_estoqueztl'),'e.qt_atual > 0 and p.ID = e.id_prod and e.id = (SELECT max(id) from tb_estoqueztl e where p.ID = e.id_prod)')
			        ->where($where)
			        ->group("p.ID")
			        ->order("p.codigo_mask");
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		function listaPendenciasporempresa($pesq){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
						
			$sessaobusca = new Zend_Session_Namespace('pendenciaempresa');
		    if(isset($sessaobusca->where)):
		   		$where = $sessaobusca->where;
		   	endif;	
			
		   	if(!empty($pesq['nome'])):
		   		$where = ' and c.EMPRESA like "%'.$pesq['nome'].'%"';
		   	elseif($pesq['buscaregioes'] != 0):
		   		$where = " and c.ID_REGIOES = ".$pesq['buscaregioes'];
		   	endif;
			
		   	if(!empty($where)):
		   		$sessaobusca->where = $where;
		   	endif;
		   	
		   	//--- Controle de perfil ------------------------------------------
			foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
			if($list->nivel==1):
				$sql 	= "";
				foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
				if($list->nivel == 1):
					if($usuario->id_perfil == 31):
						$where .= " and c.id_regioestelevendas in (".RegioesBO::buscaRegioesusuariolog().")";
					else:
						$where .= " and c.ID_REGIOES in (".RegioesBO::buscaRegioesusuariolog().")";
					endif;
				endif;
			elseif($list->nivel==0):
				$where .= " and c.ID = ".$usuario->id_cliente;
			endif;
			
		  	$where = 'c.ID is not NULL'.$where.$sql;
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('c'=>'clientes'), array('*'))
			       	->join(array('d'=>'tb_pedidos_pend'), 'c.ID = d.id_cliente')
			        ->where($where)
			        ->group("c.ID")
			        ->order("c.EMPRESA");
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		/** 
		 * Lista pendencias dos clientes
		 * @data 19/02/14
		 * @autor Cleitonsb
		 * */
		
		function listaPendencias($pesq, $tp = "1"){
		    
		    $where = "";		    
		    if(isset($pesq['canceladas']) and $pesq['canceladas'] == "on") $where = " and d.id_ped_fat is NULL";
		    
		    if(isset($pesq['dataini'])){
		    	$dataini = substr($pesq['dataini'],6,4).'-'.substr($pesq['dataini'],3,2).'-'.substr($pesq['dataini'],0,2);
		    }
		    	
		    if(isset($pesq['datafim'])){
		    	$datafin = substr($pesq['datafim'],6,4).'-'.substr($pesq['datafim'],3,2).'-'.substr($pesq['datafim'],0,2);
		    }
		    
		    if((!empty($pesq['dataini'])) and (!empty($pesq['datafim']))):
				$where .= " and d.dt_pend between '".$dataini."' and '".$datafin."'";
			elseif((!empty($pesq['dataini'])) and (empty($pesq['datafim']))):
				$where .= " and d.dt_pend >= '".$dataini."'";
			elseif((empty($pesq['dataini'])) and (!empty($pesq['datafim']))):
				$where .= " and d.dt_pend <= '".$datafin."'";
			endif;
		    
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
			$select = $db->select();
				
			$select->from(array('d'=>'tb_pedidos_pend'), array('d.id_ped','d.id_ped_fat','p.ID', 'p.CODIGO','p.DESCRICAO', 'd.qt','DATE_FORMAT(d.dt_pend,"%d/%m/%Y") as dtpend','d.id as idpend'))
				->join(array('p'=>'produtos'), 'p.ID = d.id_prod and d.status = "'.$tp.'"')
				->where("d.id_cliente = '".$pesq['empresa']."' ".$where)
				->order("d.id_ped_fat asc");
				
			$stmt = $db->query($select);
			$objpend = $stmt->fetchAll();
						
			if (count($objpend) > 0){			
			?>			
			<form class="mainForm" id="pendencias">
				<div class="widget">       
	        	<div class="head" style="border-bottom: 0px"><h5 class="iFrames">Produtos</h5></div>
	        	<table class="tableStatic" style="width: 100%" >
	            	<thead>
	                	<tr>
	                        <td width="20%">Código</td>
	                        <td width="">Descrição</td>
	                        <td width="">Pedido</td>
	                        <td width="13%">Dt pend</td>
	                        <td width="7%">Qt</td>
	                        <td width="">Faturado</td>
	                        <td width="7%" style="text-align: center;">&nbsp;</td>
	                    </tr>
	                </thead>
	                <tbody>
	        			<?php 
						$cor=0;
				    	foreach($objpend as $lista){
				    	?>
						<tr >
			                <td align="center" >
			                   	<?=$lista->CODIGO?>  
			                </td>
			                <td align="left" >
			                	<?=$lista->DESCRICAO?>
			                </td>
			                <td align="center" >
			                	<a href="/admin/venda/pedidosvenda/ped/<?=md5($lista->id_ped)?>" target="_blank"><?=$lista->id_ped?></a>
			                </td>
			                <td align="center" >
			                	<?=$lista->dtpend?>
			                </td>                
			                <td align="center" >
			                	<?=$lista->qt?>               	
			                </td>
			                <td align="center" >
			                	<a href="/admin/venda/pedidosvenda/ped/<?=md5($lista->id_ped_fat)?>" target="_blank"><?=$lista->id_ped_fat?></a>
			                </td>
			                <td align="center" >
			                	<?php if(!$lista->id_ped_fat){ ?>
			                	<input type="checkbox" name="<?=$lista->idpend?>">
			                	<?php } ?>
			                </td>                
			            </tr>            
						<?php  } ?>
						</tbody>
					</table>
				</div>
				
				<div style="margin-top: 10px">
					<input type="button" class="greenBtn" value="Restaurar" id="btnRestaurar" onclick="restauraPendencias();">
				</div>
			</form>
			<?php 
			}else{
				?>
				<div style="border: 1px solid #d5d5d5; padding: 10px; text-align: center;">
		 			Nenhuma pendência encontrada
				</div>
				<?php 
			}
		}
		
		function restauraPendencias($pesq, $tp = "1"){
			try{
				$bop 		= new PendenciasModel();
				$where 		= "";
				if(isset($pesq['dataini'])){
					$dataini = substr($pesq['dataini'],6,4).'-'.substr($pesq['dataini'],3,2).'-'.substr($pesq['dataini'],0,2);
				}
				 
				if(isset($pesq['datafim'])){
					$datafin = substr($pesq['datafim'],6,4).'-'.substr($pesq['datafim'],3,2).'-'.substr($pesq['datafim'],0,2);
				}
				
				if((!empty($pesq['dataini'])) and (!empty($pesq['datafim']))):
					$where .= " and dt_pend between '".$dataini."' and '".$datafin."'";
				elseif((!empty($pesq['dataini'])) and (empty($pesq['datafim']))):
					$where .= " and dt_pend >= '".$dataini."'";
				elseif((empty($pesq['dataini'])) and (!empty($pesq['datafim']))):
					$where .= " and dt_pend <= '".$datafin."'";
				endif;
				
				
				$objpend 	= $bop->fetchAll("id_cliente = '".$pesq['empresa']."' and status = 1 and id_ped_fat is NULL ".$where);
			
				if (count($objpend) > 0){
					foreach($objpend as $lista){
						if(isset($pesq[$lista->id])){
							$bop->update(array('status' => 0), 'id = "'.$lista->id.'"');						
						}
					}
				}
				
				return true;
			}catch (Zend_Exception $e){
				$boerro	= new ErrosModel();
				$dataerro = array('descricao' => $e->getMessage(), 'pagina' => 'PendenciasBO::restauraPendencias()');
				$boerro->insert($dataerro);
				return false;
			}
		
		}
		
		/**
		 * buscaPendenciasemp
		 *
		 * @author programador
		 * @data 22/04/2014
		 * @tags @param array $pesq
		 * @tags @return Ambigous <multitype:, multitype:mixed Ambigous <string, boolean, mixed> >
		 */
		function buscaPendenciasemp($pesq){
			try{

				$bo 	= new ContatosModel();
				$boe 	= new ContatosempModel();
				
				$empresas = $pesq['cliente'].",";
				if(isset($pesq['pendfil'])){
					foreach ($boe->fetchAll("status = 1 and id_matriz = '".$pesq['idempresa']."'") as $filias){
						if($filias->id_clientes){
							$empresas .= $filias->id_clientes.",";
						}
					}
				}
				
				$where = " and id_cliente in (".substr($empresas,0,-1).")";

				
				$usuario = Zend_Auth::getInstance()->getIdentity();
				foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $listr);
				if($listr->nivel==1):
					if($listr->reggen == 1):
						$clireg = "";
						foreach (ClientesBO::buscaParceiros('clientes') as $clientes):
							$clireg .= $clientes->id_cliente.",";
						endforeach;
					
						if(!empty($clireg)):
							$where .= " and c.id_cliente in (".substr($clireg,0,-1).")";
						else:
							$where .= " and c.id_cliente < 0";
						endif;
					else:
						$where .= " and id_cliente = ".$usuario->id_cliente;
					endif;
				endif;
								
				
				$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
				$db->setFetchMode(Zend_Db::FETCH_OBJ);
					
				$select = $db->select();
					
				$select->from(array('p'=>'produtos'), array('p.ID', 'p.CODIGO','p.DESCRICAO', 'd.qt','DATE_FORMAT(d.dt_pend,"%d/%m/%Y") as dtpend','d.id as idpend'))
					->join(array('d'=>'tb_pedidos_pend'), 'p.ID = d.id_prod')
					->where("d.status = 0 ".$where)
					->order("d.id_ped");
					
				$stmt = $db->query($select);
				$objList = $stmt->fetchAll();
				
				if (count($objList) > 0){
					?>
				    <form action="/admin/venda/baixapendempresa" method="post" name="pendendencias" id="pendendencias" class="mainForn">
				    <div class="widget">
			 			<div class="head" style="border-bottom: 0px"><h5 class="iFrames">Produtos</h5></div>
			        	<table class="tableStatic" style="width: 100%">
			            	<thead>
			                	<tr>
			                        <td width="20%">Código</td>
			                        <td width="40%">Descrição</td>
			                        <td width="13%">Pedido</td>
			                        <td width="13%">Dt pend</td>
			                        <td width="7%">Qt</td>
			                        <td width="7%" style="text-align: center;">&nbsp;</td>
			                    </tr>
			                </thead>
			                <tbody>
			        			<?php 
								$cor=0;
						    	foreach($objList as $lista):
						    	?>
								<tr >
					                <td align="center" >
					                   	<?=$lista->CODIGO?>  
					                </td>
					                <td align="left" >
					                	<?=$lista->DESCRICAO?>
					                </td>
					                <td align="center" >
					                	<a href="/admin/venda/pedidosvenda/ped/<?=md5($lista->id_ped)?>" target="_blank">
					                	<?=$lista->id_ped?></a>
					                </td>
					                <td align="center" >
					                	<?=$lista->dtpend?>
					                </td>                
					                <td align="center" >
					                	<?=$lista->qt?>               	
					                </td>
					                <td align="center" >
					                	<input type="checkbox" value="<?=$lista->idpend?>" name="pend_<?=$lista->idpend?>">
					                </td>                
					            </tr>            
								<?php  endforeach; ?>
								</tbody>
							</table>
						</div>	
						<input style="margin-top: 10px" class="redBtn" type="button" value="Cancelar selecionados" id="btnCancpend">
					</form>					
								
				<?php 
				}else{
				?>
				<div style="border: 1px solid #d5d5d5; padding: 10px; text-align: center; margin-top: 10px">
		 			Nenhuma pendência encontrada
				</div>	
				<?php 
				}
					
			}catch (Zend_Exception $e){
				echo "erro";
			}
		}
		
		function baixarPendencias($var){
			$bo	= new PendenciasModel();
				
			/* foreach ($bo->fetchAll("id_cliente = ".$var['cliente']." and status = 0") as $pend):
			if(!empty($var[$pend->id])):
			$array['status']	= 1;
			$bo->update($array, "id = ".$pend->id);
			endif;
			endforeach; */
			
			foreach ($var as $linha => $valor){
				echo $linha;
				if(strpos($linha, "pend_") !== false){ 
					$bo->update(array('status' => 1), "id = ".$valor);
					echo $valor;
				}
			}
			
			echo "teste";
			
		}
		
		
	}
?>