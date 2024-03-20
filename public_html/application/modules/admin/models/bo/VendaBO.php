<?php
	class VendaBO{		
		 	
		///////--------Relatorio ZTl garantias------------------------------
		//---- Relatorios garantias ---------------------------------------
		 function relatorioComprasztl($pesq){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			$bo		= new RegioesModel();
			$bor	= new RegioesclientesModel();
			
			if(!empty($pesq['prod'])):
				$where = " and p.CODIGO = '".$pesq['prod']."'";
			elseif($pesq['buscagruposub']!=0):
				$where = " and p.id_gruposprodsub = ".$pesq['buscagruposub'];
			elseif($pesq['grupo']!=0):
				foreach (GruposprodBO::listaGruposprodutossub($pesq['grupo']) as $listsubg):
					$idsg .= $listsubg->id.",";
				endforeach;
				$where = " and p.id_gruposprodsub in (".substr($idsg, 0,-1).") ";
			endif;			
			
			if(!empty($pesq['clientes'])):
				$where .= " and pd.id_parceiro = ".$pesq['clientes'];
				
			elseif($pesq['uf']!=0):
				$boc	= new ClientesModel();
				$bocl	= new ClientesEnderecoModel();
				
				foreach ($bocl->fetchAll('ESTADO = '.$pesq['uf']) as $listacliuf):
					$idclis .= $listacliuf->ID_CLIENTE.',';
				endforeach;
				
				if($idclis!=""):
					$where .= " and pd.id_parceiro in (".substr($idclis,0,-1).")";
				endif;		
			endif;
			
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('pd'=>'tb_pedidos','*'), array('pp.id_prod','sum(pp.qt) as qttotal','p.CODIGO'))
			        ->join(array('pp'=>'tb_pedidos_prod'),'pp.id_ped = pd.id')
			        ->join(array('p'=>'produtos'),'pp.id_prod = p.ID')
			        ->where("pd.sit = 0 and pd.status = 'ped' ".$where)
			        ->order('p.codigo_mask','asc')
			        ->group("pp.id_prod");

			        
			        
			$stmt = $db->query($select);
			
			return $stmt->fetchAll();	
						
		}
		
		
		
		
		//------- Pedidos vendas ---------------------------------
		//--Lista Pedidos de venda---------------------------
		 function listaPedidosvenda($val){
			$bo		= new RegioesModel();
			$bor	= new RegioesclientesModel();
			$bot	= new RegioestelevendasModel();
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			$dataini = substr($val['dataini'],6,4).'-'.substr($val['dataini'],3,2).'-'.substr($val['dataini'],0,2);
			$datafin = substr($val['datafin'],6,4).'-'.substr($val['datafin'],3,2).'-'.substr($val['datafin'],0,2);
			
			if(!empty($val['buscaid'])):
				$where = " and t.id = ".substr($val['buscaid'],1);
			elseif((!empty($val['dataini'])) and (!empty($val['datafin']))):
				$where = " and t.data_vend between '".$dataini."' and '".$datafin."'";
			elseif((!empty($val['dataini'])) and (empty($val['datafin']))):
				$where = " and t.data_vend >= '".$dataini."'";
			elseif((empty($val['dataini'])) and (!empty($val['datafin']))):
				$where = " and t.data_vend <= '".$datafin."'";
			elseif(!empty($val['buscacli'])):
				$where = " and t.id_parceiro = ".$val['buscacli'];
			elseif(!empty($val['buscauf'])):
				$where = " and ce.ESTADO = '".$val['buscauf']."'";
			elseif(!empty($val['buscareg'])):
				$where = " and c.ID_REGIOES = '".$val['buscareg']."'";
			endif;
			   
		   	/*$sessaobusca = new Zend_Session_Namespace('Default');
		   	
		   	if(!empty($where)):
		   		//$sessaobusca->where = $where;
		   	elseif(isset($sessaobusca->where)):
		   		//$where = $sessaobusca->where;
		   	endif;*/
		  
			$sql = "";
			foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
			if($list->nivel == 1):
				foreach ($bor->fetchAll("id_clientes = ".$usuario->id) as $regioes):
					$reg .= $regioes->id_regioes.",";
				endforeach;				
				$where .= " and ID_REGIOES in (".substr($reg,0,-1).")";
			endif;
			
			//--- Controle de perfil ------------------------------------------
			foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
			if($list->nivel == 1){
				if($usuario->id_perfil == 31){
					$where .= " and c.id_regioestelevendas in (".RegioesBO::listaRegioesusuarios(1).")";
				}elseif(($usuario->id_perfil == 4) || ($usuario->id_perfil == 5)){
					$where .= " and (c.ID_REGIOES in (".RegioesBO::listaRegioesusuarios(0).") || c.id_regioestelevendas in (".RegioesBO::listaRegioesusuarios(1)."))";
				}else{
					$where .= " and c.ID_REGIOES in (".RegioesBO::listaRegioesusuarios(0).")";
				}
			}elseif($list->nivel==0){
				$where .= " and c.ID = ".$usuario->id_cliente;
			}
			
			
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_pedidos','*'),
			        array('t.id','DATE_FORMAT(t.data_vend,"%d/%m/%Y %H:%i" ) as dtvenda','c.EMPRESA','(select EMPRESA from clientes cl where cl.ID = t.id_user) as vendedor'))
			        ->join(array('c'=>'clientes'),'t.id_parceiro = c.id')
			        ->where("t.status = 'ped' and t.sit = 0  ".$where)
			        ->order('t.id desc','');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		//--Lista Pedidos de venda clientes---------------------------
		 function listaPedidosvendacli($val){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			$dataini = substr($val['dataini'],6,4).'-'.substr($val['dataini'],3,2).'-'.substr($val['dataini'],0,2);
			$datafin = substr($val['datafin'],6,4).'-'.substr($val['datafin'],3,2).'-'.substr($val['datafin'],0,2);
			
			if(!empty($val['buscaid'])):
				$where = " and t.id = ".substr($val['buscaid'],1);
			elseif((!empty($val['dataini'])) and (!empty($val['datafin']))):
				$where = " and t.data_cad between '".$dataini."' and '".$datafin."'";
			elseif((!empty($val['dataini'])) and (empty($val['datafin']))):
				$where = " and t.data_cad >= '".$dataini."'";
			elseif((empty($val['dataini'])) and (!empty($val['datafin']))):
				$where = " and t.data_cad <= '".$datafin."'";
			endif;

			$where .= " and c.ID = ".$usuario->id;
		   	
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_pedidos','*'),
			        array('t.id','DATE_FORMAT(t.data_cad, "%d/%m/%Y") as dtvenda','c.EMPRESA','(select EMPRESA from clientes cl where cl.ID = t.id_user) as vendedor','t.status'))
			        ->join(array('c'=>'clientes'),'t.id_parceiro = c.id')
			        ->where("t.sit = 0  ".$where)
			        ->order('t.id desc','');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		//--Lista pedidos-------------------------------------------------------------------------
		function listaPedidospendentes($var, $tp = ""){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			$bo		= new RegioesModel();
			$bor	= new RegioesclientesModel();

			//--- Busca memorizada ------------------------------------------
			$where = $wheredata = "";
			
			if($tp == ""){
				$sessaobusca = new Zend_Session_Namespace('pedidosvenda');
			    if(isset($sessaobusca->where)):
			   		$where = $sessaobusca->where;
			   	endif;
			   	
			   	if(isset($sessaobusca->wheredata)):
			   		$wheredata = $sessaobusca->wheredata;
			   	endif;
			   	$limit = 100000000;
			}else{
			    $limit = 3;
			    $where = " and t.status = 'orc' and t.sit = 0";
			}
		   	
		   	//--- Nova busca ------------------------------------------
		   	
		   	if(isset($var['id']) and $var['id']!="") $where .= " and t.id = '".$var['id']."'";				
		   	if(isset($var['representante']) and $var['representante']!="0") $where .= " and t.id_representante = '".$var['representante']."'";
		   	if(isset($var['cliente']) and $var['cliente']!="0") $where .= " and t.id_parceiro = '".$var['cliente']."'";
		   	if(isset($var['televenda']) and $var['televenda']!="0") $where .= " and t.id_televenda = '".$var['televenda']."'";
		   	
		   	if(isset($var['buscasit']) and $var['buscasit']!="sit"){
				if($var['buscasit']=="1"):
					$where .= " and t.status = 'orc' and t.sit = 0";
				elseif($var['buscasit']=="2"):
					$where .= " and t.status = 'ped' and t.sit = 0";
				elseif($var['buscasit']=="3"):
					$where .= " and t.sit = 1";
				endif;
		   	}
		   	
			if(isset($var['nfe']) and $var['nfe']!=""){ 
			    $where .= " and t.id_nfe = '".ereg_replace("[^0-9]", " ", $var['nfe'])."'";
			}
	   				
			//--- Data ----------------------------------------------
		   	if(isset($var['tpdata'])){
				if($var['tpdata']==2):
					if(!empty($var['dataini']) || !empty($var['datafim'])):
						if(!empty($var['dataini']) and !empty($var['datafim'])):
							$dataini = substr($var['dataini'],6,4).'-'.substr($var['dataini'],3,2).'-'.substr($var['dataini'],0,2);
							$datafim = substr($var['datafim'],6,4).'-'.substr($var['datafim'],3,2).'-'.substr($var['datafim'],0,2);			
							$wheredata = ' and t.data_vend BETWEEN "'.$dataini.'" and "'.$datafim.'  23:59:59"';
						elseif (!empty($var['dataini'])):
							$dataini = substr($var['dataini'],6,4).'-'.substr($var['dataini'],3,2).'-'.substr($var['dataini'],0,2);
							$wheredata = ' and t.data_vend >= "'.$dataini.' 23:59:59"';
						elseif (!empty($var['datafim'])):
							$datafim = substr($var['datafim'],6,4).'-'.substr($var['datafim'],3,2).'-'.substr($var['datafim'],0,2);
							$wheredata = ' and t.data_vend <= "'.$datafim.' 23:59:59"';
						endif;
					endif;
				elseif($var['tpdata']==1):	
					if(!empty($var['dataini']) || !empty($var['datafim'])):
						if(!empty($var['dataini']) and !empty($var['datafim'])):
							$dataini = substr($var['dataini'],6,4).'-'.substr($var['dataini'],3,2).'-'.substr($var['dataini'],0,2);
							$datafim = substr($var['datafim'],6,4).'-'.substr($var['datafim'],3,2).'-'.substr($var['datafim'],0,2);			
							$wheredata = ' and t.data_cad BETWEEN "'.$dataini.'" and "'.$datafim.'  23:59:59"';
						elseif (!empty($var['dataini'])):
							$dataini = substr($var['dataini'],6,4).'-'.substr($var['dataini'],3,2).'-'.substr($var['dataini'],0,2);
							$wheredata = ' and t.data_cad >= "'.$dataini.'"';
						elseif (!empty($var['datafim'])):
							$datafim = substr($var['datafim'],6,4).'-'.substr($var['datafim'],3,2).'-'.substr($var['datafim'],0,2);
							$wheredata = ' and t.data_cad <= "'.$datafim.' 23:59:59"';
						endif;
					endif;
				endif;
		   	}
		   	
			//----- Busca filtro gravado na secao -----------------------------------
			
			if(!empty($where)):
		   		$sessaobusca->where = $where;
		   	endif;
		   	
			if(!empty($wheredata)):
		   		$sessaobusca->wheredata = $wheredata;
		   	endif;		   	
		   	
		   	//--- Controle de perfil ------------------------------------------
		   	foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
	   		if($list->nivel == 1){
	   			if($usuario->id_perfil == 31){
	   				$where .= " and c.id_regioestelevendas in (".RegioesBO::listaRegioesusuarios(1).")";
	   			}elseif(($usuario->id_perfil == 4) || ($usuario->id_perfil == 5)){
	   				$where .= " and (c.ID_REGIOES in (".RegioesBO::listaRegioesusuarios(0).") || c.id_regioestelevendas in (".RegioesBO::listaRegioesusuarios(1)."))";
	   			}else{
	   				$where .= " and c.ID_REGIOES in (".RegioesBO::listaRegioesusuarios(0).")";
	   			}
	   		}elseif($list->nivel==0){
		   		$where .= " and c.ID = ".$usuario->id_cliente;
		   	}
		   	
		   	// -----------------------------------------------------------------------
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
						
			$select->from(array('t'=>'tb_pedidos','*'),
		        array('t.id as idped','DATE_FORMAT(t.data_cad,"%d/%m/%Y %H:%i") as dtcad','DATE_FORMAT(t.data_vend,"%d/%m/%Y %H:%i") as dtvenda',
		        'c.EMPRESA as parceiro','c.ID as idparceiro','c.ID as idcli', 't.status as statusped','t.sit as sitped', 
		        'cr.EMPRESA as representante','cr.ID as idrepresentante','ct.nome as televendas','ct.id as idtelevendas','t.id_nfe'))
		        
		        ->join(array('c'=>'clientes'),'t.id_parceiro = c.id')
		        ->joinLeft(array('cr'=>'clientes'),'t.id_representante = cr.ID')
		        ->joinLeft(array('ct'=>'tb_usuarios'),'t.id_televenda = ct.id')
		        ->where("t.id is not NULL ".$where.$wheredata)
		        ->order('t.id desc')
				->limit($limit);
			  
			$stmt = $db->query($select);
			
			return $stmt->fetchAll();		
		}
		
		function contaPedidospendentes(){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			$bo		= new RegioesModel();
			$bor	= new RegioesclientesModel();
						
			//--- Controle de perfil ------------------------------------------
			$where = "";
			foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
			if($list->nivel==1){
				if($usuario->id_perfil == 31){
					$where .= " and c.id_regioestelevendas in (".RegioesBO::listaRegioesusuarios(1).")";
				}elseif(($usuario->id_perfil == 4) || ($usuario->id_perfil == 5)){
					$where .= " and (c.ID_REGIOES in (".RegioesBO::listaRegioesusuarios(0).") || c.id_regioestelevendas in (".RegioesBO::listaRegioesusuarios(1)."))";
				}else{
					$where .= " and c.ID_REGIOES in (".RegioesBO::listaRegioesusuarios(0).")";
				}
				
			}elseif($list->nivel==0){
				$where .= " and c.ID = ".$usuario->id_cliente;
			}
			
			// -----------------------------------------------------------------------
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
			$select = $db->select();
		
			$select->from(array('t'=>'tb_pedidos','*'), array('count(t.id) as qtpedidos'))
				->join(array('c'=>'clientes'), 'c.ID = t.id_parceiro')
				->where("t.sit = false and t.status = 'orc' ".$where);
				
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		//--Lista pedidos exportar ----------------------------------------------------------
		 function listaPedidosexportar($var){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			$bo		= new RegioesModel();
			$bor	= new RegioesclientesModel();
			
			//--- Busca memorizada ------------------------------------------
			$sessaobusca = new Zend_Session_Namespace('pedidosvenda');
		    if(isset($sessaobusca->where)):
		   		$where = $sessaobusca->where;
		   	endif;
		   	
		   	if(isset($sessaobusca->wheredata)):
		   		$wheredata = $sessaobusca->wheredata;
		   	endif;
			
			//--- Controle de perfil ------------------------------------------
			foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
			if($list->nivel == 1){
				if($usuario->id_perfil == 31){
					$where .= " and c.id_regioestelevendas in (".RegioesBO::listaRegioesusuarios(1).")";
				}elseif(($usuario->id_perfil == 4) || ($usuario->id_perfil == 5)){
					$where .= " and (c.ID_REGIOES in (".RegioesBO::listaRegioesusuarios(0).") || c.id_regioestelevendas in (".RegioesBO::listaRegioesusuarios(1)."))";
				}else{
					$where .= " and c.ID_REGIOES in (".RegioesBO::listaRegioesusuarios(0).")";
				}
			}elseif($list->nivel==0){
				$where .= " and c.ID = ".$usuario->id_cliente;
			}
			
				   	
		    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_pedidos','*'),
		        array('t.desconto','t.id as idped','DATE_FORMAT(t.data_cad,"%d/%m/%Y %H:%i") as dtcad', 'DATE_FORMAT(t.data_vend,"%d/%m/%Y %H:%i") as dtvenda','c.EMPRESA',
		                'c.ID as idcli','t.status as statusped','t.sit as sitped','sum(tp.qt*tp.preco_unit) as precototalped','t.id_nfe'))
		        ->join(array('c'=>'clientes'),'t.id_parceiro = c.id')
		        ->join(array('tp'=>'tb_pedidos_prod'),'t.id = tp.id_ped')
		        ->where("t.id is not NULL ".$where.$wheredata)
		        ->order('t.id desc','')
		        ->group("t.id");
			  
			$stmt = $db->query($select);
			$objPed = $stmt->fetchAll();		
			
			if(count($objPed)>0){
			    ?>
			    <table>
			    <thead>
                	<tr>
                        <td width="">Id</td>
                        <td width="">NFe</td>
                        <td width="15%">Dt Cadastro</td>
                        <td width="15%">Dt Venda</td>
                        <td width="">Cliente</td>
                        <td width="">Valor</td>
                        <td width="5%">Sit</td>
                    </tr>
                </thead>
                <tbody>
			    <?php 
			    foreach ($objPed as $pedidos){
			        ?>
			        <tr >
		                <td  style="text-align: center;" >
		                <?php echo $pedidos->idped?>
		                </td>
		                <td  style="text-align: center;" >
		                <?php if(!empty($pedidos->id_nfe)) echo "NFe".substr("000000".$pedidos->id_nfe,-6,6); ?>
		                </td>
		                <td  style="text-align: center;" >
		                <?php echo $pedidos->dtcad?>
		                </td>
		                <td  style="text-align: center;" >
		                <?php echo $pedidos->dtvenda?>
		                </td>
		                <td  style="text-align: center;" >
		                <?php echo $pedidos->EMPRESA?>
		                </td>
		                <td  style="text-align: center;" >
		                <?php echo number_format($pedidos->precototalped-$pedidos->desconto,2,",",".")?>
		                </td>
		                <td  style="text-align: center;" >
		                <?php 
		                   if($pedidos->sitped=='1') echo "Cancelado";
		                   elseif($pedidos->statusped=='ped') echo "Faturado";
		                   elseif($pedidos->statusped=='orc') echo "Pedido";
		                   ?>  
		                </td>
		           	</tr>
			        <?php
			    }
			   	?>
			   	</tbody>
			   	</table>
			   	<?php 
			}
			
		}
		
	//--Lista pedidos PAINEL-------------------------------------------------------------------------
		 function listaPedidospainel(){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			$bo		= new RegioesModel();
			$bor	= new RegioesclientesModel();

		 	//--- Controle de perfil ------------------------------------------
			foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
			if($list->nivel == 1){
				if($usuario->id_perfil == 31){
					$where .= " and c.id_regioestelevendas in (".RegioesBO::listaRegioesusuarios(1).")";
				}elseif(($usuario->id_perfil == 4) || ($usuario->id_perfil == 5)){
					$where .= " and (c.ID_REGIOES in (".RegioesBO::listaRegioesusuarios(0).") || c.id_regioestelevendas in (".RegioesBO::listaRegioesusuarios(1)."))";
				}else{
					$where .= " and c.ID_REGIOES in (".RegioesBO::listaRegioesusuarios(0).")";
				}
			}elseif($list->nivel==0){
				$where .= " and c.ID = ".$usuario->id_cliente;
			}
		   	
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_pedidos','*'),
			        array('t.id','DATE_FORMAT(t.data_cad,"%d/%m/%Y %H:%i") as dtcad','DATE_FORMAT(t.data_vend,"%d/%m/%Y %H:%i") as dtvenda','c.EMPRESA','(select EMPRESA from clientes cl where cl.ID = t.id_user) as vendedor','c.ID as idcli','t.status as statusped','t.sit as sitped'))
			        ->join(array('c'=>'clientes'),'t.id_parceiro = c.id')
			        ->where("t.status = 'orc' and sit = 0")
			        ->order('t.id desc','');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		//--Lista produtos pedidos---------------------------
		/* Listo produtos do pedido com
		 * Dados produtos
		 * Estoque produto
		 * Ncm produto
		 * Dados tributarios do produto 
		 * 
		 * Usado em pedidosedit --
		 * usado em VendasBO::gravarDadosnfe
		 * */
		 function listaPedidosprod($var,$idperfil="",$tp = 0){
			
		    if($idperfil==""):
		    	$pedido['ped'] = md5($var);
		    	foreach (VendaBO::buscaPedido($pedido) as $cliente);
		    	$idperfil = $cliente->despesasfiscais;
		    endif;
		     
		    $order = "";
		    if($tp == 1) $order = "t.id asc"; else $order = "l.loca1";
		    
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_pedidos_prod'),
				array('t.qt', 't.promo', 't.preco_alt', 't.preco_unit', 't.preco_tabela', 'p.ID as id_prod', 'p.CODIGO', 'p.PRECO_UNITARIO', 
			    'p.DESCRICAO', 'p.APLICACAO', 'p.valor_promo', 'p.valor_desc','e.qt_atual','p.PESO as pesoliquido','t.id as idprorc', 'p.codigo_ean',
				'cipi.tipo as tipoipi','cpis.tipo as tipopis','cc.tipo as tipocofins','v.valor as custo','n.ibpt_aliqnac','n.ibpt_aliqimp',
				'p.descpromo','p.participapromo','pc.markup as markupprod','pc.markupmin as markupminprod'))
			         
			    ->join(array('p'=>'produtos'),'t.id_prod = p.ID')
			    ->joinLeft(array('n'=>'tb_produtosncm'),'p.id_ncm = n.id')
			        
			    ->joinLeft(array('tn'=>'tb_tributosncm'),'tn.id_produtosncm = n.id and tn.id_tributosfiscais = '.$idperfil)
			        
			    ->joinLeft(array('ci'=>'tb_tributocsticms'),'tn.id_tributocsticms = ci.id')
			    ->joinLeft(array('cipi'=>'tb_tributocstipi'),'tn.id_tributocstipi = cipi.id')
			    ->joinLeft(array('cpis'=>'tb_tributocstpis'),'tn.id_tributocstpis = cpis.id')
			    ->joinLeft(array('cc'=>'tb_tributocstcofins'),'tn.id_tributocstcofins = cc.id')
			    ->joinLeft(array('e'=>'tb_estoqueztl'),'t.id_prod = e.id_prod and e.id = (SELECT max(id) from tb_estoqueztl e where t.id_prod = e.id_prod)')
			    ->joinLeft(array('v'=>'tb_produtoscmv'),'t.id_prod = v.id_produtos and v.id = (select max(v.id) from tb_produtoscmv v where v.id_produtos = t.id_prod)')
			    
			    ->joinLeft(array('l'=>'tb_produtoslocalizacao'),'l.id_prod = t.id_prod')
				->joinLeft(array('pc'=>'tb_produtosclasses'),'pc.id = p.id_produtosclasses')
			    
			    ->where("t.id_ped = ".$var)
				->order($order)
				->group('t.id')
			;
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();

			
			/* foreach ($boc->fetchAll("id = (select max(v.id) from tb_produtoscmv v where v.id_produtos = ".$listProd->ID.")") as $listacmv);
			
			if(count($listacmv)>0):
			$array_up['custocompra']	= $listacmv->valor;
			$bop->update($array_up,"id_ped = ".$ped->id." and id_prod = ".$listProd->ID);
			endif;
			
			foreach (EstoqueBO::buscaEstoque($listProd->ID) as $estoque);
			if(count($estoque)>0):
			$qt_atual	= $estoque->qt_atual;
			endif; */
			
		}
			
		//--Lista produtos pedidos---------------------------
		/* Listo produtos do pedido com
		 * Dados produtos
		* Estoque produto
		* Ncm produto
		* Dados tributarios do produto
		*
		* Usado em pedidosedit --
		* usado em VendasBO::gravarDadosnfe
		* */
		function listaProdutospedvend($var){
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
			$select = $db->select();
				
			$select->from(array('np'=>'tb_nfeprod'), array('np.*'))
			    ->join(array('pp'=>'tb_pedidos_prod'),'np.id_prod = pp.id_prod ')
			    ->join(array('p'=>'tb_pedidos'),'p.id_nfe = np.id_nfe')
			    ->where("p.id = pp.id_ped and np.id_nfe = '".$var."'");
    							
			$stmt = $db->query($select);
    		return $stmt->fetchAll();
    							
		}
		
		
		function buscaLocalprodutos($var){
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_pedidos_prod','*'), array('*'))
			        ->join(array('p'=>'tb_produtoslocalizacao'),'t.id_prod = p.id_prod')
			        ->where("t.id_ped = ".$var);
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		
		function buscaPedido($var){
			/*---Busca pedido com cliente e transportadora---------------------------------
			* Usado em pedidoseditAction;
			* Usado em VendasBO::listaPedidosprod() ---------------------------------
			*/
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_pedidos'), array('t.*','t.tipofrete','t.id as idped','t.sit as sitvenda','c.EMPRESA as nempresa','c.RAZAO_SOCIAL as nrazao_social',
			        'c.CPF_CNPJ as ncpf_cnpj','c.RG_INSC as nrg_insc','ct.EMPRESA as transportadora','ct.RAZAO_SOCIAL as trazao_social',
			        'ct.CPF_CNPJ as tcpf_cnpj','ct.RG_INSC as trg_insc','c.id_despesasfiscais as despesasfiscais',
			        'c.id_transportadoras as idtrans','t.obsnfe as obsnota','c.obsnfe as obsnfecli','c.tptransp as tipotransportadora','t.id_nfe'))
			        ->join(array('c'=>'clientes'),'c.ID = t.id_parceiro')
			        ->joinLeft(array('ct'=>'clientes'),'ct.ID = c.id_transportadoras')
			        ->where("md5(t.id) = '".$var['ped']."'");
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();				
		}
		
		function gravaPedido($params){
			$bov	= new PedidosvendaModel();
			$bop	= new PedidosvendaprodModel();
			
			if(!empty($params[qt])):			
				foreach (ClientesBO::listaDesc($params[pedcli]) as $listDes);
				
				foreach (ProdutosBO::buscaProdutoscodigo($params[codigo]) as $lista);
				
				$array[id_ped]			= $params[ped];
				$array[id_prod]			= $lista->ID;
				$array[preco_tabela]	= $lista->PRECO_UNITARIO;
				$array[qt]				= $params[qt];
				
				if(!empty($params[promo])): 
					$array[promo] = 1;
					
	                if(($lista->valor_promo!='') and ($lista->valor_promo!=0)): 
	                	$valor_unitario = $lista->valor_promo;
	                	
	                elseif(($lista->valor_desc!='') and ($lista->valor_desc!=0)):
	                    $valor_unitario = $lista->PRECO_UNITARIO;
	                    
	                    $valor_unitario = $valor_unitario - (($listDes->desc1 / 100)*$valor_unitario);
	                    $valor_unitario = $valor_unitario - (($listDes->desc2 / 100)*$valor_unitario);
	                    $valor_unitario = $valor_unitario - (($listDes->desc3 / 100)*$valor_unitario);
	                    $valor_unitario = $valor_unitario - (($listDes->desc4 / 100)*$valor_unitario);
	                    $valor_unitario = $valor_unitario - (($listDes->desc5 / 100)*$valor_unitario);
	                
	                    $valor_unitario = $valor_unitario - (($listDes->valor_desc / 100)*$valor_unitario);
					endif;
	            else:
	                $valor_unitario = $lista->PRECO_UNITARIO;
	                $valor_unitario = $valor_unitario - (($listDes->desc1 / 100)*$valor_unitario);
	            	$valor_unitario = $valor_unitario - (($listDes->desc2 / 100)*$valor_unitario);
	                $valor_unitario = $valor_unitario - (($listDes->desc3 / 100)*$valor_unitario);
	                $valor_unitario = $valor_unitario - (($listDes->desc4 / 100)*$valor_unitario);
	                $valor_unitario = $valor_unitario - (($listDes->desc5 / 100)*$valor_unitario);
	               
				endif;
				
				$valor_unitario = round($valor_unitario * 100) / 100; 
	    		$array[preco_unit] = $valor_unitario;
				
				$bop->insert($array);
			endif;			
		}
		
		
		//----Orcamentos--------------------------------------------------
		function gravaProdorcamentos($params){
			try{
				$bov	= new OrcamentosvendaModel();
				$bop	= new OrcamentosvendaprodModel();
				
				if(!empty($params[qt])) $qtprod = $params[qt]; else $qtprod = 1;
				
				if(count(ProdutosBO::buscaProdutoscodigo($params[codigo]))>0){
					foreach (ProdutosBO::buscaProdutoscodigo($params[codigo]) as $lista);
					
					$array['id_pedido_tmp']	= $params['ped'];
					$array['id_prod']		= $lista->ID;
					$array['qt']			= $qtprod;
					$array['promo']			= $params['promo'];
					
					$bop->insert($array);
					return false;
				}else{
				    return true;
				}
			}catch (Zend_Exception $e){
				$boerro	= new ErrosModel();
				$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "VendaBO::gravaProdorcamentos(ped='".$params['ped']."')");
				$boerro->insert($dataerro);
			
				return false;
			}
						
		}
		
		function gravaProdorcamentosmobile($params){
		    
		    /* 1- Produto incluido com sucesso
		     * 2- Produto encontrado no cross
		     * 3- Produto inexistente
		     * 4- Produto jah incluso
		     * */
		    
			try{
				$bov	= new OrcamentosvendaModel();
				$bop	= new OrcamentosvendaprodModel();
				
				if(!empty($params[qt])) $qtprod = $params[qt]; else $qtprod = 1;
				
				//-- Produtos ZTL ----------------------------------------
				if(count(ProdutosBO::buscaProdutoscodigo($params[codigo]))>0){
					foreach (ProdutosBO::buscaProdutoscodigo($params[codigo]) as $lista);
					
					if(count($bop->fetchAll("id_pedido_tmp = ".$params[ped]." and id_prod = ".$lista->ID)) > 0){
					 	echo "4";   
					}else{
				
						$array[id_pedido_tmp]	= $params[ped];
						$array[id_prod]			= $lista->ID;
						$array[qt]				= $qtprod;
						
						$bop->insert($array);
						echo "1|".md5($params[ped]);
					}
				}else{
				
					//-- busca produtos cross ---------------------------------
					$bo		= new RefcruzadaModel();
					$boc	= new CodigoscrossModel();
					$bof	= new FabricasModel();
					$boprod	= new ProdutosModel();
					
					if(count($boc->fetchAll("visualizar = true and sit = true and codigo = '".strtoupper($params['codigo'])."'")) > 0){
					    echo "2|";
					    
					    ?>
					    
					    <div style="font-size: 20px; text-align: center; padding-bottom: 5px">Você está procurando o código referente à:</div>					    
    					<table style="border-collapse: collapse; width: 100%"><tbody>
    					<?php 
    				
					    $class = 'tbaplicacao2pequeno';
						foreach ($boc->fetchAll("visualizar = true and sit = true and codigo = '".strtoupper($params['codigo'])."'") as $list){
						    if($class == 'tbaplicacao2pequeno') $class = 'tbaplicacaopequeno'; else $class = 'tbaplicacao2pequeno';
						    
							$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
							$db->setFetchMode(Zend_Db::FETCH_OBJ);
							$select = $db->select();
					
							$select->from(array('r'=>'tb_crossreference','*'), array('p.id_prod as idcodigo','p.codigo','f.no_fabricante','f.id as idfabricante'))
								->join(array('p'=>'tb_crossprodutos'), 'p.id = r.id_crossprodutos and p.id_fabricante = 1')
								->join(array('f'=>'tb_fabricante'), 'f.id = p.id_fabricante ')
								->where('r.sit = true and p.sit = true and r.id_codprod = '.$list->id);
					
							$stmt = $db->query($select);
					
							foreach ($stmt->fetchAll() as $cross);
							foreach($boprod->fetchAll("ID = '".$cross->idcodigo."'") as $produto);
							foreach($bof->fetchAll("id = '".$list->id_fabricante."'") as $fabrica);
							
							$codcross .= $params['codigo'].":".$fabrica->no_fabricante.":".$produto->CODIGO;
							
							
							?>
							<tr onclick="buscaProduto('<?php echo $params[ped]?>','<?php echo $produto->CODIGO?>','<?php echo $qtprod?>')">
				            	<td class="<?php echo $class?>">
				                	<?php echo $params['codigo']?>
				                </td>
				                <td class="<?php echo $class?>">
				                	<?php echo $fabrica->no_fabricante?>
				                </td>
				                <td class="<?php echo $class?>">
				                	<?php echo $produto->CODIGO?>
				                </td>
				            </tr>
							<?php 
						}
						?>
						</tbody></table>
						<?php 
					}else{
					    echo "3";
					}
				
				}
				
		
				
			}catch (Zend_Exception $e){
				$boerro	= new ErrosModel();
				$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "VendaBO::gravaProdorcamentos(ped='".$params[ped]."')");
				$boerro->insert($dataerro);
					
				return false;
			}
		
		}
		
		
		function rmProdorcamentos($params){
			$bov	= new OrcamentosvendaModel();
			$bop	= new OrcamentosvendaprodModel();
			
			$bop->delete("id = ".$params);						
		}
		
		function removePendorcamentos($params){
			$bo = new PendenciasModel();
			
			$data['id_peddes']	= $params['ped'];
			$bo->update($data,"id = ".$params['id']);						
		}
		
	 	function rmOrcamentos($params){
			$bov	= new OrcamentosvendaModel();
			
			$data['status']	= 0;
			$bov->update($data, "md5(id) = '".$params['orcamento']."'");						
		}
		
		function rmPedidosdevenda($params){
			$bov	= new PedidosvendaModel();
			$usuario = Zend_Auth::getInstance()->getIdentity();
					
			$data['sit']	= 1;
			$id = $bov->update($data, "md5(id) = '".$params['pedido']."'");	
		}		
				
		function rmVendas($params){
			$bov	= new PedidosvendaModel();
			$bo 	= new EstoqueModel();
			$usuario = Zend_Auth::getInstance()->getIdentity();
			
			$data['sit']	= 1;
			$bov->update($data, "id = '".$params['venda']."'");	

			foreach (VendaBO::listaPedidosprod($params['venda']) as $listProd):
				
				$arrayestq = array();
				$arrayestq['id_prod'] 			= $listProd->ID;
				$arrayestq['qt_atual'] 			= $listProd->qt_atual+$listProd->qt;
				$arrayestq['qt_atualizacao'] 	= $listProd->qt;
				$arrayestq['id_atualizacao'] 	= $params['venda'];
				$arrayestq['dt_atualizacao'] 	= date("Y-m-d H:i:s");
				$arrayestq['tipo'] 				= "VENDA CANCELADA";
				$arrayestq['id_user'] 			= $usuario->id;
				$bo->insert($arrayestq);
				
			endforeach;
						
			LogBO::cadastraLog("Vendas/Pedidos",3,$usuario->id,$params['venda'],"VENDA CANCELADA ".$params['venda']);

			echo "sucessobaixa";
			
		}
				
		function removePendorcamentosall($params){
			$bo = new PendenciasModel();
			
			$data['id_peddes']	= $params['ped'];
			$bo->update($data,"status = 0 and id_cliente = ".$params['idcli']);						
		}
		
		//--Lista produtos orcamento---------------------------
		 function listaPedidosorc($ped,$idperfil=""){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			if($idperfil==""):
			$pedido['ped'] = md5($ped);
			foreach (VendaBO::buscaOrcamentos($pedido) as $cliente);
				$idperfil = $cliente->id_despesasfiscais;
			endif;
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_pedidos_tmp_prod','*'),
			        array('t.id as idprorc','t.qt','p.ID as id_prod', 'p.CODIGO', 'p.PRECO_UNITARIO','p.DESCRICAO', 'p.APLICACAO', 'p.valor_promo', 
			               'p.valor_desc','e.qt_atual','t.promo','t.preco_alt','v.valor as custo','pc.markup','pc.markupmin'))
			        ->join(array('p'=>'produtos'),'t.id_prod = p.ID')
			        ->joinLeft(array('n'=>'tb_produtosncm'),'p.id_ncm = n.id')
			        ->joinLeft(array('tn'=>'tb_tributosncm'),'tn.id_produtosncm = n.id and id_tributosfiscais = '.$idperfil)
			        ->joinLeft(array('e'=>'tb_estoqueztl'),'t.id_prod = e.id_prod and e.id = (SELECT max(id) from tb_estoqueztl e where t.id_prod = e.id_prod)')
			        ->joinLeft(array('v'=>'tb_produtoscmv'),'t.id_prod = v.id_produtos and v.id = (select max(v.id) from tb_produtoscmv v where v.id_produtos = t.id_prod)')
			        ->joinLeft(array('pc'=>'tb_produtosclasses'),'pc.id = p.id_produtosclasses')
			        ->where("t.id_pedido_tmp = ".$ped)
					->order('t.id asc');
						  			
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		function editaProdpedidos($params){
			$bov	= new PedidosvendaModel();
			$bop	= new PedidosvendaprodModel();
			$bo 	= new PendenciasModel();
			$usuario = Zend_Auth::getInstance()->getIdentity();
			
			/*foreach (VendaBO::listaPedidosorc($params[ped]) as $listProd):
				if(!empty($params['editqt_'.$listProd->ID])):
					$array_up[qt]	= $params['editqt_'.$listProd->ID];					
				endif;
				$bop->update($array_up,"id = ".$listProd->idprorc);
			endforeach;*/
			if(empty($params['idprodedit'])):
				foreach (VendaBO::listaPedidosprod($params[ped]) as $listProd):
					
					if(!empty($params['editqt_'.$listProd->ID])):
						$array_up[qt]	= $params['editqt_'.$listProd->ID];
						if($listProd->qt > $params['editqt_'.$listProd->ID]):
							$array[id_ped]		= $params[ped];
							$array[id_cliente]	= $params[pedcli];
							$array[id_user]		= $usuario->id;
							$array[dt_pend]		= date("Y-m-d H:i:s");
							$array[id_prod]		= $listProd->ID;
							$array[qt]			= $listProd->qt-$params['editqt_'.$listProd->ID];
							$array[valor]		= $params['pendvl_'.$listProd->ID];
							$bo->insert($array);
						endif;
						//$bop->update($array_up,"id_ped = ".$params[ped]." and id_prod = ".$listProd->ID);
						$bop->update($array_up,"id = ".$listProd->idprorc);	
					endif;
					
				endforeach;
						
			else:
				$array_up[preco_alt]	= str_replace(",",".",str_replace(".","",$params['editvl_'.$params['idprodedit']]));
				$array_up[preco_unit]	= str_replace(",",".",str_replace(".","",$params['editvl_'.$params['idprodedit']]));
				$bop->update($array_up,"id = ".$params['idprodedit']);
			endif;						
		}
		
		function removeProdpedidos($params){
			$bov	= new PedidosvendaModel();
			$bop	= new PedidosvendaprodModel();
			$bo 	= new PendenciasModel();
			$usuario = Zend_Auth::getInstance()->getIdentity();
						
			$array[id_ped]		= $params[ped];
			$array[id_cliente]	= $params[pedcli];
			$array[id_user]		= $usuario->id;
			$array[dt_pend]		= date("Y-m-d H:i:s");
			$array[id_prod]		= $params[idprod];
			$array[qt]			= $params[qt];
			$array[valor]		= $params[valor];
			$bo->insert($array);
			
			$bop->delete("id_ped = ".$params[ped]." and id_prod = ".$params[idprod]);
		}
		
		function gravarDadosvenda($params){
			$bov	= new PedidosvendaModel();
			$boc	= new ClientesModel();
			$boe	= new ClientesEmailModel();
			
			$data_inicial = date('d/m/Y');
			$time_inicial = VendaBO::geraTimestamp($data_inicial);
			
			//-- geracao da data da parcela 1 ---------------
			if($params['prazo1']!=""):
				$array["prazo1"] 		= $params['prazo1'];
				$array["vlprazo1"] 		= str_replace(",",".",str_replace(".","",$params['vlprazo1']));
			elseif($params['dataprazo1']!=""):
				$diferenca = VendaBO::geraTimestamp($params['dataprazo1']) - $time_inicial;
				$dias = (int)floor( $diferenca / (60 * 60 * 24));
				$array["prazo1"] 		= $dias;
				$array["vlprazo1"] 		= str_replace(",",".",str_replace(".","",$params['vlprazo1']));
			else:
				$array["prazo1"] 		= "";
				$array["vlprazo1"] 		= "";
			endif;
			
			
			//-- geracao da data da parcela 2 ---------------			
			if($params['prazo2']!=""):
				$array["prazo2"] 		= $params['prazo2'];
				$array["vlprazo2"] 		= str_replace(",",".",str_replace(".","",$params['vlprazo2']));
			elseif($params['dataprazo2']!=""):
				$diferenca = VendaBO::geraTimestamp($params['dataprazo2']) - $time_inicial;
				$dias = (int)floor( $diferenca / (60 * 60 * 24));
				$array["prazo2"] 		= $dias;
				$array["vlprazo2"] 		= str_replace(",",".",str_replace(".","",$params['vlprazo2']));
			else:
				$array["prazo2"] 		= "";
				$array["vlprazo2"] 		= "";
			endif;
			
			
			//-- geracao da data da parcela 3 ---------------			
			if($params['prazo3']!=""):
				$array["prazo3"] 		= $params['prazo3'];
				$array["vlprazo3"] 		= str_replace(",",".",str_replace(".","",$params['vlprazo3']));
			elseif($params['dataprazo3']!=""):
				$diferenca = VendaBO::geraTimestamp($params['dataprazo3']) - $time_inicial;
				$dias = (int)floor( $diferenca / (60 * 60 * 24));
				$array["prazo3"] 		= $dias;
				$array["vlprazo3"] 		= str_replace(",",".",str_replace(".","",$params['vlprazo3']));
			else:
				$array["prazo3"] 		= "";
				$array["vlprazo3"] 		= "";
			endif;
			
			
			//-- geracao da data da parcela 4 ---------------		
			if($params['prazo4']!=""):
				$array["prazo4"] 		= $params['prazo4'];
				$array["vlprazo4"] 		= str_replace(",",".",str_replace(".","",$params['vlprazo4']));
			elseif($params['dataprazo4']!=""):
				$diferenca = VendaBO::geraTimestamp($params['dataprazo4']) - $time_inicial;
				$dias = (int)floor( $diferenca / (60 * 60 * 24));
				$array["prazo4"] 		= $dias;
				$array["vlprazo4"] 		= str_replace(",",".",str_replace(".","",$params['vlprazo4']));
			else:
				$array["prazo4"] 		= "";
				$array["vlprazo4"] 		= "";
			endif;
		
			
			//-- geracao da data da parcela 5 ---------------			
			if($params['prazo5']!=""):
				$array["prazo5"] 		= $params['prazo5'];
				$array["vlprazo5"] 		= str_replace(",",".",str_replace(".","",$params['vlprazo5']));
			elseif($params['dataprazo5']!=""):
				$diferenca = VendaBO::geraTimestamp($params['dataprazo5']) - $time_inicial;
				$dias = (int)floor( $diferenca / (60 * 60 * 24));
				$array["prazo5"] 		= $dias;
				$array["vlprazo5"] 		= str_replace(",",".",str_replace(".","",$params['vlprazo5']));
			else:
				$array["prazo5"] 		= "";
				$array["vlprazo5"] 		= "";
			endif;			
			
			$desc = str_replace(",",".",str_replace(".","",$params['desconto']));
			$perc = str_replace(",",".",str_replace(".","",$params['descontoperc']));
			if(!empty($params['desconto'])):
				$perc = $desc * 100 / $params['totalnota'];
			elseif($params['descontoperc']):
				$desc = ($perc * $params['totalnota']) / 100;
			endif;
			
			
			$freteperc = 0;
			$frete = str_replace(",",".",str_replace(".","",$params['frete']));
			if(!empty($params['frete'])):
				$freteperc = $frete * 100 / $params['totalnota'];
			endif;						
						
			$array["obs"] 			= $params['obs'];
			$array["obsnfe"]		= $params['obsnfe'];
			$array["frete"] 		= $frete;
			$array["freteperc"]		= $freteperc;
			$array["seguro"] 		= str_replace(",",".",str_replace(".","",$params['seguro']));
			$array["desconto"] 		= $desc;
			$array["descontoperc"] 	= $perc;
			$array["qtpacotes"]		= $params['qtpacote'];
			$array["especie"] 		= $params['especie'];
			$array["placa"] 		= $params['placa'];
			$array["ufplaca"] 		= $params['ufplaca'];
			$array["pesobruto"]		= str_replace(",",".",str_replace(".","",$params['pesobruto']));			
			$array["antt"] 			= $params['antt'];
			$array["tipofrete"] 	= $params['tipofrete'];
			
			/* ---- Gravacao de emails ----------------------------------------------------
			 * try {
				$arraymail	= array(
					'ID_CLIENTE'	=> $params['pedcli'],
					'EMAIL'			=> $params['email'],
					'NOME_CONTATO'	=> $params['contatoemail'],
					'tipo'			=> 3
				);
				
				$boe->delete("ID_CLIENTE = ".$params['pedcli']." and tipo = 3");
				$boe->insert($arraymail);
			}catch (Zend_Exception $e){
				$boerro	= new ErrosModel();
				$dataerro = array('descr' => $e->getMessage(), 'erro' => $params[ped]);
				$boerro->insert($dataerro);
			} */
			
			try {
				$bov->update($array,"id = ".$params['ped']);
				return 1;
			}catch (Zend_Exception $e){
				$boerro	= new ErrosModel();
				$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "VendaBO::gravarDadosvenda(ped='".$params[ped]."')");
				$boerro->insert($dataerro);
				
				return 0;
			}	
			
		}
		
		function geraTimestamp($data) {
			$partes = explode('/', $data);
			return mktime(0, 0, 0, $partes[1], $partes[0], $partes[2]);
		}
		
		function gerarVenda($params){
		    try{
			$bov		= new PedidosvendaModel();
			$bop		= new PedidosvendaprodModel();
			$bopr		= new ProdutosModel();
			$boc		= new ProdutoscmvModel();
			$bo 		= new EstoqueModel();
			$bonfe		= new NfeModel();
			
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			foreach ($bov->fetchAll("id_nfe = ".$params) as $ped);
			
			$busca['idparceiro']		= $ped->id_parceiro;
			foreach (ClientesBO::buscaParceiros("",$busca) as $cliente);
				
			$valrep = 0;
			if(!empty($cliente->ID_REGIOES)):
				foreach (RegioesBO::buscaRegioesrep($cliente->ID_REGIOES) as $regrep);
				if(count($regrep)>0){
					$array["id_representante"]	= $regrep->id_usuarios;
					$array["comissaorep"]		= $regrep->comissao;
					$valrep = 1;
				}
			endif;
				
			if(!empty($cliente->id_regioestelevendas)):
				foreach (RegioesBO::buscaRegioestvendas($cliente->id_regioestelevendas) as $regtvendas);
				$array["id_televenda"]		= $regtvendas->id_usuarios;
				
				if($valrep == 0):
					$array["comissaovend"]		= $regtvendas->comissao;
				else:
					$array["comissaovend"]		= $regtvendas->comissaorep;
				endif;
			endif;
			
			$array["id_user"]			= $usuario->id;
			$array["data_vend"]			= date("Y-m-d H:i:s");
			$array["status"]	 		= "ped";
			
			try {
				$bov->update($array,"id = ".$ped->id);
			}catch (Zend_Exception $e){
				$boerro	= new ErrosModel();
				$dataerro = array('descr' => $e->getMessage(), 'erro' => $ped->id);
				$boerro->insert($dataerro);
			}
			
			foreach (VendaBO::listaPedidosprod($ped->id) as $listProd):
				//-- atualiza custo e markup nos produtos pedidos ----------------------------------------			
				foreach ($boc->fetchAll("id = (select max(v.id) from tb_produtoscmv v where v.id_produtos = ".$listProd->ID.")") as $listacmv);

				if(count($listacmv)>0):
					$array_up['custocompra']	= $listacmv->valor;			
				endif;
				
				$array_up['markup']		= $listProd->markupprod;
				$array_up['markupmin']	= $listProd->markupminprod;
				$bop->update($array_up,"id_ped = ".$ped->id." and id_prod = ".$listProd->ID);
				
				//-- atualiza estoque --------------------------------------------------------------------
				foreach (EstoqueBO::buscaEstoque($listProd->ID) as $estoque);
				if(count($estoque)>0):
					$qt_atual	= $estoque->qt_atual;
				endif;
				
				$arrayestq = array();
				$arrayestq['id_prod'] 			= $listProd->ID;
				$arrayestq['qt_atual'] 			= $qt_atual-$listProd->qt;
				$arrayestq['qt_atualizacao'] 	= -($listProd->qt);
				$arrayestq['id_atualizacao'] 	= $ped->id;
				$arrayestq['dt_atualizacao'] 	= date("Y-m-d H:i:s");
				$arrayestq['tipo'] 				= "VENDA";
				$arrayestq['id_user'] 			= $usuario->id;
				$bo->insert($arrayestq);
				
			endforeach;
			
			LogBO::cadastraLog("Vendas/Pedidos Faturados",2,$usuario->id,$ped->id,"Venda ".$ped->id);
			
			//--- atualiza situacao da empresa nos contatos -----
			$datacont = array('empresa' => $ped->id_parceiro);
			ContatosBO::atualizarSitcontatosemp($datacont, 1);
			
			$etapa = array('etapa' => 11);
			$bonfe->update($etapa, "id = ".$params);
			echo "sucessobaixa";
			
		    }catch (Zend_Exception $e){
		        echo "Erro ao baixar estoque...";
		        
		        $boerro	= new ErrosModel();
		        $dataerro = array('descricao' => $e->getMessage(), 'pagina' => "NfeBO::gerarVenda(".$params.")");
		        $boerro->insert($dataerro);
		        
		        return false;
		    }
			
		}
		
		function gravarDadosnfe($params){
			$bonfe		= new NfeModel();
			$bonfeprod	= new NfeprodModel();
			$bov		= new PedidosvendaModel();
			
			$pedido['ped']	= md5($params['ped']);	
			//--- Busca empresa e transportadora com enderecos ----------------		
			foreach (VendaBO::buscaPedido($pedido) as $listaempresa);
			foreach (ClientesBO::listaEnderecocomp($listaempresa->id_parceiro, 1) as $endempresa);
			
			if(!empty($params['trans']) and $params['trans'] != 'nc' and $params['trans'] != 'cr'){ 
			    $busca['idparceiro'] = $params['trans'];
			    foreach (ClientesBO::buscaParceiros("",$busca) as $transportadora);
			    foreach (ClientesBO::listaEnderecocomp($params['trans'], 1) as $endtransportadora);
			    
			}else{
				if(!empty($listaempresa->idtrans)){
				    $busca['idparceiro'] = $listaempresa->idtrans;
				    foreach (ClientesBO::buscaParceiros("",$busca) as $transportadora);
				    foreach (ClientesBO::listaEnderecocomp($listaempresa->idtrans, 1) as $endtransportadora);
				}
			}
			
			foreach (ClientesBO::listaEmailsUp($listaempresa->id_parceiro, 1) as $telemproesa);
			//--- Busca tributos da empresa ----------------
			foreach (TributosBO::buscaDespesasperfil($listaempresa->despesasfiscais) as $tributos);
			//--- Busca telefones da empresa -----------------------------
			foreach (ClientesBO::listaTelefonesUp($listaempresa->id_parceiro, "telefone1") as $telempresa);

			$total_pedido = $ipi = 0;
			$total_pedido_liquido = 0;
			
			//-- Dados da NFe ------------------------------------
			$datanfe = array(
				'serie'					=> 1,
				'data'					=> date('Y-m-d'),
				'data_saida'			=> date('Y-m-d H:i:s'),
				'cfop'					=> $tributos->cfop,
				'naturezaop'			=> $tributos->desccfop,
				'tipo'					=> 1,
				'id_cliente'			=> $listaempresa->id_parceiro,
				'cnpj'					=> $listaempresa->ncpf_cnpj,
				'inscricao'				=> $listaempresa->nrg_insc,
				'empresa'				=> DiversosBO::pogremoveAcentos($listaempresa->nrazao_social),
				'endereco'				=> DiversosBO::pogremoveAcentos($endempresa->LOGRADOURO),
				'numero'				=> $endempresa->numero,
				'bairro'				=> DiversosBO::pogremoveAcentos($endempresa->BAIRRO),
				'cep'					=> $endempresa->CEP,
				'codcidade'				=> $endempresa->codcidade,
				'cidade' 				=> DiversosBO::pogremoveAcentos($endempresa->ncidade), 
				'uf'					=> $endempresa->nuf,
				'fone'					=> $telempresa->DDD.$telempresa->NUMERO,
		        'tipofrete'				=> $listaempresa->tipofrete,
		        'transantt'				=> $listaempresa->antt,
		        'transplaca'			=> $listaempresa->placa,
		        'transufplaca'			=> $listaempresa->ufplaca,		        	
				'obs'					=> $listaempresa->obsnota,
				'frete'					=> $listaempresa->frete,
			    'freteperc'				=> $listaempresa->freteperc,
				'seguro'				=> $listaempresa->seguro,
				'desconto'				=> $listaempresa->desconto,
			    'descontoperc'			=> $listaempresa->descontoperc,
				'outrasdesp'			=> 0,
				'quantidade'			=> $listaempresa->qtpacotes,
				'especie'				=> $listaempresa->especie,
				'pesobruto'				=> $listaempresa->pesobruto,
				'marca'					=> 'ZTL'
			);						
			
			try {			    
			    if(!empty($listaempresa->id_nfe)){
			        $idnfe = $listaempresa->id_nfe;
			        $bonfe->update($datanfe,"id = ".$idnfe);
			        $bonfeprod->delete("id_nfe = ".$idnfe);
			    }else{
			    	$idnfe = $bonfe->insert($datanfe);
			    }			
			}catch (Zend_Exception $e){
				$boerro	= new ErrosModel();
            	$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "VendaBO::gravarDadosnfe(ped=".$params[ped].")");
            	$boerro->insert($dataerro);
            	
            	return 0;
			} 			
			
			//--- Transportadora ---------------------------------------
			if($listaempresa->tipotransportadora == 1 || (isset($params['trans']) and $params['trans'] == 'nc')):
                $datanfe = array(
					'transportadora'		=> "Nosso Carro",
					'transcnpj'				=> "07555737000110",
					'transie'				=> "0747014000173",
					'transendereco'			=> "Taguatinga Norte QI 08 Lote 45",
					'transcidade' 			=> "Brasilia",
					'transuf'				=> "DF"					
				);
                
			elseif($listaempresa->tipotransportadora == 2 || (isset($params['trans']) and $params['trans'] == 'cr')):
            	$datanfe = array(
					'transportadora'		=> "Cliente Retira",
            	    'transcnpj'				=> $listaempresa->ncpf_cnpj,
            	    'transie'				=> $listaempresa->nrg_insc,
            	    'transendereco'			=> DiversosBO::pogremoveAcentos($endempresa->BAIRRO." ".$endempresa->LOGRADOURO." ".$endempresa->numero),
            	    'transcidade' 			=> DiversosBO::pogremoveAcentos($endempresa->ncidade),
            	    'transuf'				=> $endempresa->nuf,
				);          	
            	
            elseif(!empty($listaempresa->idtrans) || $params['trans'] !=0): 
            	$datanfe = array(
					'id_transportadoras'	=> $transportadora->ID,
					'transportadora'		=> $transportadora->RAZAO_SOCIAL,
					'transcnpj'				=> $transportadora->CPF_CNPJ,
					'transie'				=> $transportadora->RG_INSC,
					'transendereco'			=> DiversosBO::pogremoveAcentos($endtransportadora->BAIRRO." ".$endtransportadora->LOGRADOURO." ".$endtransportadora->numero),
					'transcidade' 			=> DiversosBO::pogremoveAcentos($endtransportadora->ncidade),
					'transuf'				=> $endtransportadora->nuf,				
				);
            
            	/* $datanfe = array(
            		'id_transportadoras'	=> $listaempresa->idtrans,
            		'transportadora'		=> $listaempresa->trazao_social,
            		'transcnpj'				=> $listaempresa->tcpf_cnpj,
            		'transie'				=> $listaempresa->trg_insc,
            		'transendereco'			=> DiversosBO::pogremoveAcentos($endtransportadora->BAIRRO." ".$endtransportadora->LOGRADOURO." ".$endtransportadora->numero),
            		'transcidade' 			=> DiversosBO::pogremoveAcentos($endtransportadora->ncidade),
            		'transuf'				=> $endtransportadora->nuf,
            	); */
            else:
            	echo "- Cadastre uma transportadora para o cliente;";
            endif;
			
            try {
            	$bonfe->update($datanfe,"id = ".$idnfe);
            }catch (Zend_Exception $e){
            	$boerro	= new ErrosModel();
            	$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "VendaBO::gravarDadosnfe(ped=".$params[ped].")");
            	$boerro->insert($dataerro);
            	
            	return 0;
            }
            
            $total_desc = $total_frete = $totalpis = $totalcofins = 0;
            $total_prod = $total_pedido_liquido = $ipi = $pesoliquido = $totalfrete = $totaltrib = 0;
            
            //$bonfeprod->insert($dataprod);
			foreach (VendaBO::listaPedidosprod($params['ped'], $listaempresa->despesasfiscais) as $listProd):
				$precototal = $frete = $desconto = $baseicms = 0;
				$precototal = $listProd->qt*$listProd->preco_unit;
			
				/*-- Calcula o desconto -----------------------------------------
				 * -- O desconto nao se aplica ao IPI -------				  				 
				*/
				$desconto 	= ($precototal*$listaempresa->descontoperc)/100;
				$total_desc	+= $desconto;				
				
				/*-- Calcula o frete ----------------------------------------- */
				$frete 			= ($precototal*$listaempresa->freteperc)/100;
				$total_frete	+= $frete;
			
				$baseicms = $precototal-$desconto;
				//---- calcula icms ---------------------------------------------
				$vlicms = $vlipi = 0;
				$vlicms = (($baseicms)*$tributos->vendaicms)/100;
				
				//---- IPI ------------------------------------------------------
				if($listProd->tipoipi == 1):
					$vlipi = ($precototal*$listProd->ipi)/100;
				else:
					$vlipi = 0;
				endif;				
				
				//---  Calcula o ICMS ST ----------------------------------------
				$bstunit = $vstunit = $mva = $alicmsst = 0;
				if($tributos->usectm==true):
					/*-- Regra valida somente para o estado do  Mato Grosso, para alguns casos especiais ---------*/
					if(($tributos->vendaicmsdest!=0) and ($tributos->usevendaicmsdest==true)):
						$vstunit	= (($vlipi+$baseicms)*$tributos->ctm)/100;
						$bstunit	= ($vlicms+$vstunit)/($tributos->vendaicmsdest/100);
					endif;
				else:
					/* -- IPI + total liquido + (IPI * total liquido * porcentagem do st) --- */
					if(($tributos->vendaicmsdest!=0) and ($tributos->usevendaicmsdest==true)):
						$bstunit	= $frete + $vlipi + $baseicms + ((($frete+$vlipi+$baseicms)*$tributos->vendast)/100);
						//$bst	= $listPed->frete + $ipi + $bicms + ((($listPed->frete+$ipi+$bicms)*$listTri->vendast)/100);
						$vstunit	= (($bstunit*$tributos->vendaicmsdest)/100) - $vlicms;
					endif;
				endif;
			
				//--- PIS --------------------------------------------
				$pis = 0;
				$pis 	= ($baseicms*$listProd->pis)/100;
				$totalpis += $pis; 
				//--- COFINS --------------------------------------------
				$cofins = 0;
				$cofins	= ($baseicms*$listProd->cofins)/100; 
				$totalcofins += $cofins;
				
				if($tributos->usevendaicms==false):
					$baseicms 	= 0;
					$vlicms 	= 0;
					$alicms 	= 0;
				else:
					$alicms = $tributos->vendaicms;
				endif;				
								
				if($tributos->usevendaicmsdest==false):
					$bstunit 	= 0;
					$mva	 	= 0;
					$alicmsst	= 0;
					$vstunit	= 0;
				else:
					$mva	 	= $tributos->vendast;
					$alicmsst	= $tributos->vendaicmsdest;
				endif;				
				
				if($listProd->tipoipi == 1):
					$tpipi = "Trib";
				elseif($listProd->tipoipi == 2):
					$tpipi = "NT";
				else:
					$erro =  "Tipoipi";
				endif;
				
				if($listProd->tipopis == 1):
					$tppis = "Aliq";
				elseif($listProd->tipopis == 2):
					$tppis = "Qtde";
				elseif($listProd->tipopis == 3):
					$tppis = "NT";
				elseif($listProd->tipopis == 4):
					$tppis = "Outr";
				else:
					$erro =  "Tipopis";
				endif;
				
				if($listProd->tipocofins == 1):
					$tpcofins = "Aliq";
				elseif($listProd->tipocofins == 2):
					$tpcofins = "Qtde";
				elseif($listProd->tipocofins == 3):
					$tpcofins = "NT";
				elseif($listProd->tipocofins == 4):
					$tpcofins = "Outr";
				else:
					$erro =  "Tipocofins";
				endif;

				if($listProd->origem == 1){
					$vltotaltrib = (($listProd->qt * $listProd->preco_unit) * $listProd->ibpt_aliqimp) / 100;
				}else{
				    $vltotaltrib = (($listProd->qt * $listProd->preco_unit) * $listProd->ibpt_aliqnac) / 100;
				}
				
				$dataprod = array(
					'id_nfe'		=> $idnfe,
					'id_prod'		=> $listProd->ID,
					'codigo'		=> $listProd->CODIGO,
					'descricao'		=> $listProd->DESCRICAO,
					'ncm'			=> str_replace(".", "", $listProd->ncm),
				    'ncmex'			=> $listProd->ncmex,
					'cfop'			=> "",
					'qt'			=> $listProd->qt,
					'preco'			=> $listProd->preco_unit,
					'alicms'		=> $alicms,
					'baseicms'		=> $baseicms,
					'vlicms'		=> $vlicms,
				    'csticms'		=> str_pad($listProd->csticms, 2, '0',STR_PAD_LEFT),
					'alipi'			=> $listProd->ipi,
					'vlipi'			=> $vlipi,
				    'cstipi'		=> str_pad($listProd->cstipi, 2, '0',STR_PAD_LEFT),
					'origem'		=> $listProd->origem,
					'unidade'		=> $listProd->unidade,
					'codean'		=> $listProd->codigo_ean,
					'basest'		=> $bstunit,
					'mvast'			=> $mva,
					'icmsst'		=> $alicmsst,
					'vlicmsst'		=> $vstunit,
				    'desconto'		=> $desconto,
				    'frete'			=> $frete,
				    'cstpis'		=> str_pad($listProd->cstpis, 2, '0',STR_PAD_LEFT),
				    'alpis'			=> $listProd->pis,
				    'vlpis'			=> $pis,
				    'cstcofins'		=> str_pad($listProd->cstcofins, 2, '0',STR_PAD_LEFT),
				    'alcofins'		=> $listProd->cofins,
				    'vlcofins'		=> $cofins,
				    'csttpipi'		=> $tpipi,
				    'csttppis'		=> $tppis,
				    'csttpcofins'	=> $tpcofins,
				    'valortotaltrib' => $vltotaltrib
				);
				
				$total_prod 			+= $precototal;
				$total_pedido_liquido 	+= $precototal-$desconto;
				$ipi 					+= $vlipi;
				$peso = 0;
				$peso = str_replace(",", ".", $listProd->PESO);
				$pesoliquido += $listProd->qt*$peso;
				$totalfrete 			+= $frete;
				
				$totaltrib += $vltotaltrib;
				
				$bonfeprod->insert($dataprod);
			endforeach;
						
			/*-- calculo total dos impostos -----------------------------
			 * Base ICMS-ST 
			 * -- IPI + total liquido + (IPI * total liquido * porcentagem do st)
			*/
			
			$bicms	= $total_pedido_liquido;
			$vicms	= ($total_pedido_liquido*$tributos->vendaicms)/100;
			
			$bst = $vst = 0;
			if($tributos->usectm==true):
				/*-- Regra valida somente para o estado do  Mato Grosso, para alguns casos especiais ---------*/
				if(($tributos->vendaicmsdest!=0) and ($tributos->usevendaicmsdest==true)):
					$vst	= (($ipi+$total_pedido_liquido)*$tributos->ctm)/100;
					$bst	= ($vicms+$vst)/($tributos->vendaicmsdest/100);
				endif;
			else:
				if(($tributos->vendaicmsdest!=0) and ($tributos->usevendaicmsdest==true)):
					$bst	= $totalfrete+$ipi+$total_pedido_liquido + ((($totalfrete+$ipi+$total_pedido_liquido)*$tributos->vendast)/100);
					$vst	= (($bst*$tributos->vendaicmsdest)/100) - $vicms;
				endif;
			endif;
			
			$vltotalnota	=	$total_pedido_liquido+$ipi+$vst+$totalfrete;
						
			if($tributos->usevendaicms==false || $tributos->vendaicms==0):
				$bicms = 0;
				$vicms = 0;
			endif;
						
			$datanfe = array(
				'baseicms'		=> number_format($bicms,2,".",""),
				'vlicms'		=> number_format($vicms,2,".",""),
				'basest'		=> number_format($bst,2,".",""),
				'vlst'			=> number_format($vst,2,".",""),				
				'totalipi'		=> number_format($ipi,2,".",""),
			    'totalpis'		=> number_format($totalpis,2,".",""),
			    'totalcofins'	=> number_format($totalcofins,2,".",""),
				'totalprodutos'	=> number_format($total_prod,2,".",""),
				'totalnota'		=> number_format($vltotalnota,2,".",""),				
				'pesoliquido'	=> $pesoliquido,
			    'etapa'			=> '2',
		        'valortotaltrib' => $totaltrib
			);
			
			try {
				$bonfe->update($datanfe,'id = '.$idnfe);
				$nfeped = array('id_nfe' => $idnfe);
				$bov->update($nfeped, "id = ".$params['ped']);
				//--- esse echo garente a validacao na nfevenda.js --------------------------
				echo "idnfe:".$idnfe;				
			}catch (Zend_Exception $e){
				$boerro	= new ErrosModel();
				$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "VendaBO::gravarDadosnfe(ped=".$params[ped].")");
				$boerro->insert($dataerro);
				
				//--- remove nfe -----------------------------------------------
				$bonfeprod->delete('id_nfe = '.$idnfe);
				$bonfe->delete('id = '.$idnfe);
				
				if(!empty($idnfe)):
					$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
					$db->setFetchMode(Zend_Db::FETCH_OBJ);
					$db->query('ALTER TABLE `ztlrolamentos`.`tb_nfe` AUTO_INCREMENT = '.$idnfe);
				endif;
				
				echo $e->getMessage();
				return false;
			} 
		}
		
		function gerarFinanceirovenda($params){
			$bo 	= new FinanceiroModel();
			$bop	= new FinanceiroreceberModel();
			$boparc = new FinanceiroreceberparcModel();
			$bonfe 	= new NfeModel();
			
			$usario = Zend_Auth::getInstance()->getIdentity();
			
			$buscped = array('ped' => md5($params));
			foreach (NfeBO::buscarNfevenda($buscped) as $nfe);
			
			$data = array(
				'emissao'			=>	date('Y-m-d'),
				'id_fornecedor'		=>	$nfe->id_parceiro,
				'id_nfe'			=>	$nfe->id_nfe,
				'n_documento'		=>	"NFe".substr("000000".$nfe->id_nfe,-6,6),
				'moeda'				=>  'BRL',
				'valor'				=>	$nfe->totalnota,
				'baixa'				=> 	0,
				'sit'				=> 	true
			);
		
			/*-- marca outros financeiro que possa ter sido gerado por erro pela NFe --------------------- */
			$del['sit'] = false;
			$bop->update($del, 'id_nfe = "'.$nfe->id_nfe.'"');
			 
			foreach ($bop->fetchAll('id_nfe = "'.$nfe->id_nfe.'"') as $fin){
				$boparc->update($del, 'id_financeirorec = '.$fin->id);
			}
				
			try {
			    
			    //--------------------------------------------------------------------------------------------
			    $idfin = $bop->insert($data);
				$arraynfe = array('etapa' => 3);
				$bonfe->update($arraynfe, "id = ".$nfe->id_nfe);
				
				echo "sucessof";				
			}catch (Zend_Exception $e){
				
				$boerro	= new ErrosModel();
				$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "VendaBO::gerarFinanceirovenda(ped=".$params[ped].")");
				$boerro->insert($dataerro);
				
				$bonfe		= new NfeModel();
				$bonfeprod	= new NfeprodModel();
				$bov		= new PedidosvendaModel();
				
				//--- remove chave estrangeira em pedidos venda ----------------
				$nfeped = array('id_nfe' => NULL);
				$bov->update($nfeped, "id = ".$params);
				
				//--- remove nfe -------------------------------------------
				$bonfeprod->delete('id_nfe = '.$nfe->id_nfe);
				$bonfe->delete('id = '.$nfe->id_nfe);
				
				if(!empty($nfe->id_nfe)):
					$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
					$db->setFetchMode(Zend_Db::FETCH_OBJ);
					$db->query('ALTER TABLE `ztlrolamentos`.`tb_nfe` AUTO_INCREMENT = '.$nfe->id_nfe);
				endif;
				
				return 0;
				
			}					
						
			if((!empty($nfe->prazo1)) and (!empty($nfe->vlprazo1))){
				$dataparc = array(
					'id_financeirorec'		=>	$idfin,
					'emissao'				=> 	date("Y-m-d"),
					'vencimento'			=>	date('Y-m-d', strtotime("+".$nfe->prazo1." days")),
					'moeda'					=>	'BRL',
					'valor_apagar'			=>	$nfe->vlprazo1,
					'id_financeiroplcontas'	=>	120,
					'id_financeirocontas'	=>	1,
					'sit'					=> 	true,
					'bloq'					=> 	0,
					'parc'					=> 	1
				);
				$boparc->insert($dataparc);
			}
						
			if((!empty($nfe->prazo2)) and (!empty($nfe->vlprazo2))):
				$dataparc = array(
					'id_financeirorec'		=>	$idfin,
					'emissao'				=> 	date("Y-m-d"),
					'vencimento'			=>	date('Y-m-d', strtotime("+".$nfe->prazo2." days")),
					'moeda'					=>	'BRL',
					'valor_apagar'			=>	$nfe->vlprazo2,
					'id_financeiroplcontas'	=>	120,
					'id_financeirocontas'	=>	1,
					'sit'					=> 	true,
					'bloq'					=> 	0,
					'parc'					=> 	2
				);
				$boparc->insert($dataparc);
			endif;
			
			if((!empty($nfe->prazo3)) and (!empty($nfe->vlprazo3))):
				$dataparc = array(
					'id_financeirorec'		=>	$idfin,
					'emissao'				=> 	date("Y-m-d"),
					'vencimento'			=>	date('Y-m-d', strtotime("+".$nfe->prazo3." days")),
					'moeda'					=>	'BRL',
					'valor_apagar'			=>	$nfe->vlprazo3,
					'id_financeiroplcontas'	=>	120,
					'id_financeirocontas'	=>	1,
					'sit'					=> 	true,
					'bloq'					=> 	0,
					'parc'					=> 	3
				);
				$boparc->insert($dataparc);
			endif;
						
			if((!empty($nfe->prazo4)) and (!empty($nfe->vlprazo4))):
				$dataparc = array(
					'id_financeirorec'		=>	$idfin,
					'emissao'				=> 	date("Y-m-d"),
					'vencimento'			=>	date('Y-m-d', strtotime("+".$nfe->prazo4." days")),
					'moeda'					=>	'BRL',
					'valor_apagar'			=>	$nfe->vlprazo4,
					'id_financeiroplcontas'	=>	120,
					'id_financeirocontas'	=>	1,
					'sit'					=> 	true,
					'bloq'					=> 	0,
					'parc'					=> 	4
				);
				$boparc->insert($dataparc);
			endif;
						
			if((!empty($nfe->prazo5)) and (!empty($nfe->vlprazo5))):
				$dataparc = array(
					'id_financeirorec'		=>	$idfin,
					'emissao'				=> 	date("Y-m-d"),
					'vencimento'			=>	date('Y-m-d', strtotime("+".$nfe->prazo5." days")),
					'moeda'					=>	'BRL',
					'valor_apagar'			=>	$nfe->vlprazo5,
					'id_financeiroplcontas'	=>	120,
					'id_financeirocontas'	=>	1,
					'sit'					=> 	true,
					'bloq'					=> 	0,
					'parc'					=> 	5
				);
				$boparc->insert($dataparc);			
			endif;
		}
		
		function gerarPedido($params){
			$bov		= new PedidosvendaModel();
			$bop		= new PedidosvendaprodModel();
			$boo		= new OrcamentosvendaModel();
			$bopend		= new PendenciasModel();
			
			$boa		= new ContatosModel();
			$bocont		= new ContatosempModel();
			
			$usuario	= Zend_Auth::getInstance()->getIdentity();			
			
			$busca['idparceiro'] = $params[pedcli];
			foreach (ClientesBO::buscaParceiros("",$busca) as $cliente);
			
			if(!empty($cliente->ID_REGIOES)):
				foreach (RegioesBO::buscaRegioesrep($cliente->ID_REGIOES) as $regrepresentante);
				$arrayped["id_representante"]	= $regrepresentante->id_usuarios;
			endif;
			
			if(!empty($cliente->id_regioestelevendas)):
				foreach (RegioesBO::buscaRegioestvendas($cliente->id_regioestelevendas) as $regtelevendas);
				$arrayped["id_televenda"]		= $regtelevendas->id_usuarios;
			endif;
			
			$arrayped["prazo1"] 			= $params[prazo1];
			$arrayped["prazo2"] 			= $params[prazo2];
			$arrayped["prazo3"] 			= $params[prazo3];
			$arrayped["prazo4"] 			= $params[prazo4];
			$arrayped["prazo5"] 			= $params[prazo5];
			$arrayped["id_parceiro"]		= $params[pedcli];
			$arrayped["id_user"]			= $usuario->id;
			$arrayped["data_cad"]			= date("Y-m-d H:i:s");
			$arrayped["status"]	 			= "orc";
			$arrayped["obs"] 				= $params[obs];
						
			$id = $bov->insert($arrayped);
			
			foreach (ProdutosBO::listaallProdutos() as $lista):
				if(!empty($params[$lista->ID])):			
					foreach (ClientesBO::listaDesc($params[pedcli]) as $listDes);
				
					$array['id_ped']			= $id;
					$array['id_prod']			= $lista->ID;
					$array['preco_tabela']		= $lista->PRECO_UNITARIO;
					$array['qt']				= $params[$lista->ID];
				
					
					$array['preco_alt']		= ""; 
					if(!empty($params["pendvl_".$lista->ID])):
						$valor_unitario		= $params["pendvl_".$lista->ID];
						$array['preco_alt'] 	= $params["pendvl_".$lista->ID];
					elseif(!empty($params['promo'])): 
						$array['promo'] = 1;
						
		                if(($lista->valor_promo!='') and ($lista->valor_promo!=0)): 
		                	$valor_unitario = $lista->valor_promo;
		                	
		                elseif(($lista->valor_desc!='') and ($lista->valor_desc!=0)):
		                    $valor_unitario = $lista->PRECO_UNITARIO;
		                    
		                    $valor_unitario = $valor_unitario - (($listDes->desc1 / 100)*$valor_unitario);
		                    $valor_unitario = $valor_unitario - (($listDes->desc2 / 100)*$valor_unitario);
		                    $valor_unitario = $valor_unitario - (($listDes->desc3 / 100)*$valor_unitario);
		                    $valor_unitario = $valor_unitario - (($listDes->desc4 / 100)*$valor_unitario);
		                    $valor_unitario = $valor_unitario - (($listDes->desc5 / 100)*$valor_unitario);
		                
		                    $valor_unitario = $valor_unitario - (($listDes->valor_desc / 100)*$valor_unitario);
						endif;
		            else:
		                $valor_unitario = $lista->PRECO_UNITARIO;
		                $valor_unitario = $valor_unitario - (($listDes->desc1 / 100)*$valor_unitario);
		            	$valor_unitario = $valor_unitario - (($listDes->desc2 / 100)*$valor_unitario);
		                $valor_unitario = $valor_unitario - (($listDes->desc3 / 100)*$valor_unitario);
		                $valor_unitario = $valor_unitario - (($listDes->desc4 / 100)*$valor_unitario);
		                $valor_unitario = $valor_unitario - (($listDes->desc5 / 100)*$valor_unitario);
		               
					endif;
					
					$valor_unitario = round($valor_unitario * 100) / 100; 
		    		$array['preco_unit'] = $valor_unitario;
					
					$bop->insert($array);
					
					$arraypend['status']		= 1;
					$arraypend['id_ped_fat']	= $id;
					$arraypend['dt_fat']		= date("Y-m-d H:i:s");
					$bopend->update($arraypend, "id_prod = ".$lista->ID." and id_cliente = ".$params['pedcli']);					
					
				endif;	
			endforeach;
			
			$dataor['status']	 = 0;
			$boo->update($dataor,"id = ".$params['ped']);
			
			$dataint['data_venda'] = date("Y-m-d H:i:s");
			$bocont->update($dataint, "id_clientes = ".$params['pedcli']);
			
			LogBO::cadastraLog("Vendas/Pedidos",2,$usuario->id,$id,"Pedido ".$id);
			
		}
		
		function gerarOrcamento($params){
			$bo	= new OrcamentosvendaModel();
			$usuario = Zend_Auth::getInstance()->getIdentity();
			
			$array["id_user"] 			= $usuario->id;
			$array["id_cliente"] 		= $params;
			$array["status"] 			= 1;
			$array["dt_atualizacao"] 	= date("Y-m-d H:i:s");
			
			
			LogBO::cadastraLog("Vendas/Orçamentos",2,$usuario->id,$params,"Cliente ID ".$params);
			
			return $bo->insert($array);	
			
		}
		
		function editaProdorcamento($params){
			$bov	= new OrcamentosvendaModel();
			$bop	= new OrcamentosvendaprodModel();
			
			/*foreach (VendaBO::listaPedidosorc($params[ped]) as $listProd):
				if(!empty($params['editqt_'.$listProd->ID])):
					$array_up[qt]	= $params['editqt_'.$listProd->ID];					
				endif;
				$bop->update($array_up,"id = ".$listProd->idprorc);
			endforeach;
			
			if(!empty($params['idprodedit'])):
				$array_up[preco_alt]	= str_replace(",",".",str_replace(".","",$params['editvl_'.$params['idprodedit']]));
				$bop->update($array_up,"id = ".$params['idprodedit']);
			endif;

			*/
					
			if(empty($params['idprodedit'])):
				foreach (VendaBO::listaPedidosorc($params[ped]) as $listProd):
					if(!empty($params['editqt_'.$listProd->ID])):
						$array_up[qt]	= $params['editqt_'.$listProd->ID];					
					endif;
					$bop->update($array_up,"id = ".$listProd->idprorc);
				endforeach;
			else:
				$array_up[preco_alt]	= str_replace(",",".",str_replace(".","",$params['editvl_'.$params['idprodedit']]));
				$bop->update($array_up,"id = ".$params['idprodedit']);
			endif;			
			
		}		
		
		//--Lista Orcamentos-------------------------------------------------------------------------
		function listaOrcamentos(){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();	
						
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();			
			$select->from(array('t'=>'tb_pedidos_tmp','*'),
			        array('t.id','DATE_FORMAT(t.dt_atualizacao,"%d/%m/%Y %H:%i") as dtvenda','c.EMPRESA','c.ID as idcli'))
			        ->join(array('c'=>'clientes'),'t.id_cliente = c.ID')
			        ->where("t.status != '0' and t.id_user = ".$usuario->id)
			        ->order('t.id desc','');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		//--Lista produtos pedidos---------------------------
		function listaOrcamentosprod($ped){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_pedidos_prod','*'),
			        array('t.qt', 't.promo', 't.preco_alt', 't.preco_unit', 't.preco_tabela', 'p.ID', 'p.CODIGO', 'p.PRECO_UNITARIO', 
			        'p.DESCRICAO', 'p.APLICACAO', 'p.valor_promo', 'p.valor_desc','e.qt_atual'))
			        ->join(array('p'=>'produtos'),'t.id_prod = p.ID')
			        ->join(array('e'=>'tb_estoqueztl'),'t.id_prod = e.id_prod and e.id = (SELECT max(id) from tb_estoqueztl e where t.id_prod = e.id_prod)')
			        ->where("t.id_ped = ".$ped)
			        ->order('p.codigo_mask');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		//---Busca orcamentos--------------------
		 function buscaOrcamentos($var){
			
			if(!empty($var['ped'])):
				$where = "and md5(t.id) = '".$var['ped']."'";
			else:
				$where = "and t.id > 0";
			endif;
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_pedidos_tmp','*'),
		        array('t.id as idped','DATE_FORMAT(t.dt_atualizacao,"%d/%m/%Y %H:%i") as dtvenda','c.EMPRESA','c.ID as idcli',
				'c.id_despesasfiscais','c.ID_REGIOES','c.id_regioestelevendas'))
		        ->join(array('c'=>'clientes'),'t.id_cliente = c.ID')
		        ->join(array('cd'=>'clientes_desc'),'cd.id_cliente = c.ID')
		        ->join(array('ce'=>'clientes_endereco'),'ce.ID_CLIENTE = c.ID')
		        ->join(array('e'=>'tb_estados'),'e.id = ce.ESTADO')
		        ->where("ce.tipo = 1 ".$where);
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		
		//--Lista produtos pedidos---------------------------
		function buscaProdutosvend($var=""){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			$bo 	= new ContatosModel();
			$boe 	= new ContatosempModel();
			
			$where = "";
			
			if(empty($var['dataini'])) $var['dataini'] = date("01-m-Y", strtotime("-11 month"));
			if(empty($var['datafim'])) $var['datafim'] = date("31-m-Y");
			
			if(!empty($var['dataini']) || !empty($var['datafim'])):
				if(!empty($var['dataini']) and !empty($var['datafim'])):
					$dataini = substr($var['dataini'],6,4).'-'.substr($var['dataini'],3,2).'-'.substr($var['dataini'],0,2);
					$datafim = substr($var['datafim'],6,4).'-'.substr($var['datafim'],3,2).'-'.substr($var['datafim'],0,2);
					$where .= ' and t.data_vend BETWEEN "'.$dataini.'" and "'.$datafim.'  23:59:59"';
				elseif (!empty($var['dataini'])):
					$dataini = substr($var['dataini'],6,4).'-'.substr($var['dataini'],3,2).'-'.substr($var['dataini'],0,2);
					$where .= ' and t.data_vend >= "'.$dataini.'"';
				elseif (!empty($var['datafim'])):
					$datafim = substr($var['datafim'],6,4).'-'.substr($var['datafim'],3,2).'-'.substr($var['datafim'],0,2);
					$where .= ' and t.data_vend <= "'.$datafim.' 23:59:59"';
				endif;
			endif;
			
			
			$empresas = $var['cliente'].",";
			if(isset($var['vendasfil'])){
				foreach ($boe->fetchAll("status = 1 and id_matriz = '".$var['idempresa']."'") as $filias){
					if($filias->id_clientes){
						$empresas .= $filias->id_clientes.",";
					}
				}
			}
			
			$where = " and id_parceiro in (".substr($empresas,0,-1).")";
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
						
			$select->from(array('t'=>'tb_pedidos','*'),
		        array('sum(pd.qt) as qtt', 'pd.preco_unit', 'p.ID as idproduto', 'p.CODIGO','t.id','t.data_vend as dtv'))
		        ->join(array('pd'=>'tb_pedidos_prod'),'t.id = pd.id_ped')
		        ->join(array('p'=>'produtos'),'pd.id_prod = p.ID')
		        ->where("t.id > 0 ".$where)
		        ->order('t.data_vend asc')
		        ->group('pd.id_prod');

			$stmt = $db->query($select);
			return $stmt->fetchAll();			
		}
		
		 function buscaProdutosvendcliantigo($idcli){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'pedidos','*'),
			        array('sum(pd.QUANTIDADE) as qtt', 'p.ID as idproduto', 'p.CODIGO','t.ID_INT'))
			        ->join(array('pd'=>'produtos_pedidos'),'t.ID_INT = pd.ID_PEDIDO')
			        ->join(array('p'=>'produtos'),'pd.ID_PRODUTO = p.ID')
			        ->where("t.STATUS = 'FATURADO' and t.ID_CLIENTE = ".$idcli)
			        ->group('pd.ID_PRODUTO');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		//--Lista produtos por grupo pedidos---------------------------
		 function buscaGruposvendcli($idcli){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_pedidos','*'),
			        array('count(p.ID) as count', 'pd.preco_unit', 'p.ID as idproduto', 'p.CODIGO','t.id','t.data_vend as dtv','g.NOME','p.ID_GRUPO'))
			        ->join(array('pd'=>'tb_pedidos_prod'),'t.id = pd.id_ped')
			        ->join(array('p'=>'produtos'),'pd.id_prod = p.ID')
			        ->join(array('g'=>'grupos'),'p.ID_GRUPO = g.ID')
			        ->where("t.id_parceiro = ".$idcli)
			        ->order('t.data_vend asc')
			        ->group('p.ID_GRUPO');
			  		//->group('pd.id_prod');
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		 function buscaGruposvendcliantigo($idcli){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'pedidos','*'),
			        array('count(p.ID) as count', 'p.ID as idproduto', 'p.CODIGO','t.ID_INT','g.NOME','p.ID_GRUPO'))
			        ->join(array('pd'=>'produtos_pedidos'),'t.ID_INT = pd.ID_PEDIDO')
			        ->join(array('p'=>'produtos'),'pd.ID_PRODUTO = p.ID')
			        ->join(array('g'=>'grupos'),'p.ID_GRUPO = g.ID')
			        ->where("t.STATUS = 'FATURADO' and t.ID_CLIENTE = ".$idcli)
			        ->group('p.ID_GRUPO');
			        //->group('pd.ID_PRODUTO');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		 function listpedidosant(){
			$bo = new PedidosvendaModel();
			$boa = new PedidosvendaantModel();
			return $boa->fetchAll("STATUS = 'FATURADO' and ID <= 2945");				
		}
		
		 function listpedidosantprod($ped){
			$bo = new PedidosvendaModel();
			$boa = new PedidosvendaprodantModel();
			return $boa->fetchAll("ID_PEDIDO = ".$ped);				
		}
		
		 
		
		//--Lista produtos por grupo de venda---------------------------
		 function buscaGruposvendprodscli($idcli){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_pedidos','*'), array('p.CODIGO','p.id_gruposprodsub','sum(pd.qt) as quant'))
			        ->join(array('pd'=>'tb_pedidos_prod'),'t.id = pd.id_ped')
			        ->join(array('p'=>'produtos'),'pd.id_prod = p.ID')
			        ->where("t.sit = 0 and t.id_parceiro = ".$idcli)
			        ->group('p.ID');
			  		//->group('pd.id_prod');
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
	
		//--- VendasBO::atualizaRelatorio() ------------------------------
		function listaProdutospedidos($id,$data){
		 	
			$data = ' and t.data_vend like "'.$data.'%"';
												
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			
			$select->from(array('t'=>'tb_pedidos'), array('sum(pd.qt) as quant'))
			        ->join(array('pd'=>'tb_pedidos_prod'),'t.id = pd.id_ped')
			        ->where("t.status = 'ped' and t.sit = 0 and  pd.id_prod = ".$id.$data);
			        
			$stmt = $db->query($select);
			return $stmt->fetchAll();	
		}
		
		//--- VendasBO::atualizaRelatorio() ------------------------------
		function buscaPendencias($id,$data){
			
		    $data = ' and p.dt_pend like "'.$data.'%"';
		    
		    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			
			$select->from(array('p'=>'tb_pedidos_pend','*'), array('sum(p.qt) as qut',))
		        ->where("p.id_prod = ".$id.$data);
				        
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		function listaRelatoriovendas(){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			
			$select->from(array('p'=>'produtos','*'),
			        array('p.ID as idproduto'))
			        ->join(array('s'=>'tb_gruposprodsub'), 's.id = p.id_gruposprodsub')
			        ->join(array('g'=>'tb_gruposprod'), 'g.id = s.id_gruposprod');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();	
		}
				
		function atualizaRelatorio(){
			$bo		= new PedidosvendaModel();
			$bor	= new RelatoriosvendasModel();			
			
			$dia = date('d');
			$mes = date('m') - 1;
			$ano = date('Y');
			$data = mktime(0,0,0,$mes,$dia,$ano);
			
			//$data = mktime(0,0,0,"12","01","2013");
			
			//---- Busco todos os produtos ---------------------------------------------
			foreach (ProdutosBO::listaallProdutos() as $lista):
				
				foreach (VendaBO::listaProdutospedidos($lista->ID,date('Y-m',$data)) as $listprod);
				foreach (VendaBO::buscaPendencias($lista->ID,date('Y-m',$data)) as $lispend);
				
				$array = array(
					'id_prod'	=> $lista->ID,
					'data'  	=> date('Y-m-d',$data),
					'qtvend'	=> $listprod->quant,
					'qtped' 	=> $listprod->quant+$lispend->qut
				);
				
				$bor->insert($array);			
			endforeach;
			
			$assunto 	= "Relatório de venda";
			$texto 		= "Relatorio de venda atualizado com sucesso";
			$resp		= "Cleiton";
			$email		= "cleiton@ztlbrasil.com.br";
			//VendaBO::enviaMail($assunto, $texto, $resp, $email);
			
		}
		
		
		function corrigeRelatorio(){
			$bo		= new PedidosvendaModel();
			$bor	= new RelatoriosvendasModel();
				
			//---- Busco todos os produtos ---------------------------------------------
			foreach (ProdutosBO::listaallProdutos() as $lista):
				$mes = 7;
				for($i = 1; $i <= $mes; $i++):
					$historico = "";
					foreach ($bor->fetchAll("data like '2012-0".$i."%' and id_prod = ".$lista->ID) as $historico);
					
					if(count($bor->fetchAll("data like '2012-0".$i."%' and id_prod = ".$lista->ID))<=0):
						
						$data = "2012-0".$i;
						if(count(VendaBO::buscaPendencias($lista->ID,$data))>0):
							foreach (VendaBO::buscaPendencias($lista->ID,$data) as $lispend);
							
							echo $i." - ".$lista->ID." - ".$lispend->qut;
							echo "<br />";
							
							$data2 = $data."-02";
							
							$array = array(
								'id_prod'	=> $lista->ID,
								'data'  	=> $data2,
								'qtvend'	=> 0,
								'qtped' 	=> $lispend->qut
							);
							
							$bor->insert($array);
						endif;
					endif;
				endfor;
				
				
				
				/* $array['id_prod']	= $lista->ID;
				$array['data']		= date('Y-m-d',$data);
			
				foreach (VendaBO::listaProdutospedidos($lista->ID,date('Y-m',$data)) as $listprod);
				$array['qtvend']	= $listprod->quant;
					
				foreach (VendaBO::buscaPendencias($lista-ID,date('Y-m',$data)) as $lispend);
				$array['qtped'] = $listprod->quant+$lispend->qut;
			
				$bor->insert($array );*/
			endforeach;
				
			$assunto 	= "Relatório de venda";
			$texto 		= "Relatorio de venda atualizado com sucesso";
			$resp		= "Cleiton";
			$email		= "cleiton@ztlbrasil.com.br";
			//VendaBO::enviaMail($assunto, $texto, $resp, $email);
				
		}
		
		
		
		
		//---- Verifica pendencias financeiras com clientes na venda --------------------------
		function buscaPendfinanceiras($params){
		    $bo = new PedidosvendaModel();		    
		    
		    if(count(UsuarioBO::buscaUsuario($params))>0){
			    foreach (UsuarioBO::buscaUsuario($params) as $usuario);
			    
			    if(($usuario->idperfil == 1) || ($usuario->idperfil == 28) || ($usuario->idperfil == 29)):			    	
			    	$array = array('id_liberacaofin' => $usuario->iduser);
		    		$bo->update($array, "id = ".$params['ped']);
			    	echo "Sucesso";
			    else:
			    	echo "Você não tem permissão";
			    endif;			    
				
			}else{
				echo "Usuário/Senha incorreto";
			}
		    
		}
		
		//----- relatorio de vendas ----------------------------
		function buscaVendasprodutos($var){
			
			if(!empty($var['dataini']) || !empty($var['datafim'])):
				if(!empty($var['dataini']) and !empty($var['datafim'])):
					$dataini = substr($var['dataini'],6,4).'-'.substr($var['dataini'],3,2).'-'.substr($var['dataini'],0,2);
					$datafim = substr($var['datafim'],6,4).'-'.substr($var['datafim'],3,2).'-'.substr($var['datafim'],0,2);			
					$where = ' and p.data_vend BETWEEN "'.$dataini.'" and "'.$datafim.'  23:59:59"';
				elseif (!empty($var['dataini'])):
					$dataini = substr($var['dataini'],6,4).'-'.substr($var['dataini'],3,2).'-'.substr($var['dataini'],0,2);
					$where = ' and p.data_vend >= "'.$dataini.' 23:59:59"';
				elseif (!empty($var['datafim'])):
					$datafim = substr($var['datafim'],6,4).'-'.substr($var['datafim'],3,2).'-'.substr($var['datafim'],0,2);
					$where = ' and p.data_vend <= "'.$datafim.' 23:59:59"';
				endif;
			else:
				$dataini	 = date('Y-m-d');
				$datafim	 = date("Y-m-d",strtotime("+24 hours"));
				$where = ' and p.data_vend BETWEEN "'.$dataini.'" and "'.$datafim.'"';
			endif;
						
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			
			$select->from(array('p'=>'tb_pedidos','*'), array('sum(qt*preco_unit) as precototal'))
			        ->join(array('pd'=>'tb_pedidos_prod'), 'pd.id_ped = p.id')
			        ->where('p.status = "ped" and p.sit = 0'.$where);
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();	
		}
		
		//----- relatorio de pendencias ----------------------------
	    function buscaPendenciasprodutos($var){
	    	if(!empty($var['dataini']) || !empty($var['datafim'])):
				if(!empty($var['dataini']) and !empty($var['datafim'])):
					$dataini = substr($var['dataini'],6,4).'-'.substr($var['dataini'],3,2).'-'.substr($var['dataini'],0,2);
					$datafim = substr($var['datafim'],6,4).'-'.substr($var['datafim'],3,2).'-'.substr($var['datafim'],0,2);			
					$where = ' and p.data_vend BETWEEN "'.$dataini.'" and "'.$datafim.'  23:59:59"';
				elseif (!empty($var['dataini'])):
					$dataini = substr($var['dataini'],6,4).'-'.substr($var['dataini'],3,2).'-'.substr($var['dataini'],0,2);
					$where = ' and p.data_vend >= "'.$dataini.' 23:59:59"';
				elseif (!empty($var['datafim'])):
					$datafim = substr($var['datafim'],6,4).'-'.substr($var['datafim'],3,2).'-'.substr($var['datafim'],0,2);
					$where = ' and p.data_vend <= "'.$datafim.' 23:59:59"';
				endif;
			else:
				$dataini	 = date('Y-m-d');
				$datafim	 = date("Y-m-d",strtotime("+24 hours"));
				$where = ' and p.data_vend BETWEEN "'.$dataini.'" and "'.$datafim.'"';
			endif;
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			
			$select->from(array('p'=>'tb_pedidos','*'), array('sum(qt*valor) as precopend'))
			        ->join(array('pd'=>'tb_pedidos_pend'), 'pd.id_ped = p.id')
			        ->where('p.status = "ped" and p.sit = 0'.$where);
				        
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		
		function enviaMail($assunto, $texto, $resp,$email){
			
			try {				
			
				$mailTransport = new Zend_Mail_Transport_Smtp("smtp.ztlbrasil.com.br", Zend_Registry::get('mailSmtp'));
				
				$mail = new Zend_Mail('utf-8');
				$mail->setFrom("info@ztlbrasil.com.br");
				$mail->addTo($email,$resp);
				$mail->setBodyHtml($texto);
				$mail->setSubject($assunto);
				$mail->send($mailTransport);
			
				echo "Email enviado com SUCESSSO: ".$email;
			} catch (Exception $e){
				echo ($e->getMessage());
			}
		
		}

		
		function corrigeEstoque($params=""){
		    $bo	= new EstoqueModel();
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
		
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
			$select = $db->select();
			$select->from(array('p'=>'tb_pedidos','*'), array('p.id as idped','p.*'))
				->joinLeft(array('e'=>'tb_estoqueztl'),'p.id = e.id_atualizacao and e.tipo like "%venda%"')
				->where('p.status = "ped" and e.id is NULL and p.id >= 4936')
				->order('p.id desc');
				
			$stmt = $db->query($select);
			$objpedidos = $stmt->fetchAll();
			
			foreach ($objpedidos as $pedidos){
			    echo $pedidos->idped;
			    echo " - ";
			    echo $pedidos->data_vend;
			    

			    
			    foreach ($bo->fetchAll("dt_atualizacao like '.substr($pedidos->data_vend,0,15).'") as $estoque){
			        echo $estoque->id;
			        echo "<br />";
			    }
			    
			    echo "<br />";
			    
			    
			}
		}
		
		function enviarlistaPrecos($var){
			try{
				if(ClientesBO::listaEmailsUp($var['selparceiro'], 1)){
					foreach (ClientesBO::listaEmailsUp($var['selparceiro'], 1) as $email);
					$emailcli = $email->EMAIL;
					
					$usuario 	= Zend_Auth::getInstance()->getIdentity();
						
					$texto = '<html>
						<head>
							<title>SisZTL Mobile 1.0</title>
						    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
						    
						    <style>
						    .tbtitulo{
								color: #fff;
								background-color: #808080;
								text-align: center;
								font-weight: bold;
								font-size: 20px;
								padding: 2.5% 0 2.5% 0;
							}
							
							body{
								margin: 0;
								padding: 0;
								font-family: tahoma, arial;
							}
							
							.tbaplicacaomedio{
								background-color: #dce4e7;
								font-size: 18px;
								padding: 10px;
								border: 0px;
								color: #000;
							}
							
							.tbaplicacao2medio{
								font-size: 18px;
								padding: 10px;
								border: 0px;
								color: #000;
							}
						    </style>
						    
						</head>
						<body>
							<div style="width: 80%; margin: 0 auto; padding: 20px 0px 20px 0px">
								<a href="http://www.ztlbrasil.com.br" target="_blank"><font size="6" color="#1b999a" face="Arial, Helvetica, sans-serif">ztlbrasil.com.br</font></a><br /><br />
								Presado cliente, segue abaixo tabela com nossa lista de preços. Caso tenha alguma dúvida, 
								favor entrar em contato com seu representante, ou ligue <font color="#1b999a">(61) 34337777</font>.
							</div>
						';	
					
					$count = 0;
					$class = 'tbaplicacao2medio';
					foreach (@ProdutosBO::buscaProdutoscatalogo() as $produtos){
						if($produtos->situacao == 0){
							if($class == 'tbaplicacao2medio') $class = 'tbaplicacaomedio'; else $class = 'tbaplicacao2medio';
							$count++;
							
							if(empty($produtos->PRECO_UNITARIO)) $preco = 0;
							else $preco = $produtos->PRECO_UNITARIO;
							
							if($count == 1):
								$coluna1 .= "<tr><td class=".$class." style='text-align: center'>".$produtos->CODIGO."</td><td style='text-align: right;' class=".$class.">".number_format($preco,2,",",".")."</td></tr>";
							elseif($count == 2):
								$coluna2 .= "<tr><td class=".$class." style='text-align: center'>".$produtos->CODIGO."</td><td style='text-align: right;' class=".$class.">".number_format($preco,2,",",".")."</td></tr>";
							elseif($count == 3):
								$coluna3 .= "<tr><td class=".$class." style='text-align: center'>".$produtos->CODIGO."</td><td style='text-align: right;' class=".$class.">".number_format($preco,2,",",".")."</td></tr>";
								$count = 0;
							endif;
							
							
							//$texto .= '<tr><td class="'.$class.'">'.$produtos->CODIGO.'</td><td class="'.$class.'">'.number_format($preco,2,",",".").'</td></tr>';
						}						
					}
					
					$texto .= '<table style="width: 80%; margin: 0 auto;"><tr>';
					
					$texto .= '<td style="padding: 10px">';
					$texto .= '<table style="width: 100%; margin-right: 3%"><thead><tr><td class="tbtitulo">Código</td><td class="tbtitulo">Preço</td></tr></thead><tbody>';
					$texto .= $coluna1;
					$texto .= '</tbody></table></td>';
					
					$texto .= '<td style="padding: 10px">';
					$texto .= '<table style="width: 100%; margin-right: 3%"><thead><tr><td class="tbtitulo">Código</td><td class="tbtitulo">Preço</td></tr></thead><tbody>';
					$texto .= $coluna2;
					$texto .= '</tbody></table></td>';
					
					$texto .= '<td style="padding: 10px; vertical-align: top">';
					$texto .= '<table style="width: 100%; margin-right: 3%"><thead><tr><td class="tbtitulo">Código</td><td class="tbtitulo">Preço</td></tr></thead><tbody>';
					$texto .= $coluna3;
					$texto .= '</tbody></table></td>';
					
					$texto .= '</tr></table></body></html>';					
					
					$assunto = "Lista de preços ZTL do Brasil";
					$resp	 = $usuario->nome;
					
					
					$emailcli = "cleiton@ztlbrasil.com.br";
					DiversosBO::enviaMail($assunto, $texto, $resp, $emailcli);
					
					echo "Lista enviada com sucesso para o email <b>".$emailcli."</b>";
				}else{
					echo $erro = "Email institucional não cadastrado para esse cliente";
				}
				
			}catch (Zend_Exception $e){
		    	$boerro	= new ErrosModel();
		    	$dataerro = array('descricao' => $e->getMessage(), 'pagina' => 'VendaBO::enviarlistaPrecos('.$var['selparceiro'].')');
		    	$boerro->insert($dataerro);
		    }
		}
		
	}
?>