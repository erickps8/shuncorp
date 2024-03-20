<?php
	class KangvendasBO{		

		//--- Orcamentos ------------------------------------
		function gerarOrcamento($var){
			$bov	= new KangvendasModel();
			$bop	= new KangorcamentosModel();
			$usuario	= Zend_Auth::getInstance()->getIdentity();
			
			$array['id_user']			= $usuario->ID;
			$array['id_cliente'] 		= $var['buscacli'];
			$array['dt_atualizacao'] 	= date("Y-m-d");
			$array['status']   			= 1;
			
			$id = $bop->insert($array);
			
			LogBO::cadastraLog("Kang/Orçamento Venda",3,$usuario->ID,$id,$id);
			
			return  $id;
		}
		
		//-- Importar ped -----------------------------------------------
		function importacaoPedido($var){
			$bov	= new KangvendasModel();
			$bop	= new KangorcamentosModel();
			$bopo	= new KangorcamentosprodModel();
			$usuario	= Zend_Auth::getInstance()->getIdentity();
			
         	$arquivo = isset($_FILES['arquivo']) ? $_FILES['arquivo'] : FALSE;
	        $pasta = Zend_Registry::get('pastaPadrao')."/public/sistema/upload/tmp/";

	        DiversosBO::criarDiretorio($pasta);
	        			 				 
			if(is_uploaded_file($arquivo['tmp_name'])){                                
                  if (move_uploaded_file($arquivo["tmp_name"], $pasta . "pedtmp.xml")) {
                  		
                  } else {
                        echo ("Alerta: Nao foi possivel fazer o upload para $pasta");
                        return $this;                                           
                  }                               
            }
	         
            $xml = simplexml_load_file($pasta . "pedtmp.xml");
			
			$array['id_user']			= $usuario->ID;
			$array['id_cliente'] 		= '1671';
			$array['dt_atualizacao'] 	= date("Y-m-d H:m");
			$array['dtentrega'] 		= $xml->dataentrega;
			$array['pedidocli'] 		= $xml->pedidocli;
			$array['parcial']	 		= $xml->parcial;
			$array['status']   			= 1;
			
			$idorc = $bop->insert($array);
			
			foreach ($xml as $listimp):
				foreach($listimp->produto as $lp):		   			
		   			//--Verifico se produtos pedidos existem-----------------------------
					foreach (ProdutosBO::buscaProdutoscodigo($lp->cod) as $produto);
					if(!empty($produto)):
						$arrayprod['id_prod']			= $produto->ID;
						$arrayprod['qt']				= $lp->qt;
						$arrayprod['preco_ut']			= $lp->preco;
						$arrayprod['id_pedido_tmp']		= $idorc;
						$arrayprod['moeda']				= $lp->moeda;

						$bopo->insert($arrayprod);
					else:
					   
					endif;
		   		endforeach;  
		   		
		    endforeach;	

		    
		    LogBO::cadastraLog("Kang/Orçamento Venda",3,$usuario->ID,$idorc,$idorc);
			return $idorc; 
		}
		
		
		//-------- Vendas --------------------------------------------------
		function listaProdutosvendas($val){
			
			if($val['cat'] == "customer") $order = "c.EMPRESA asc";
			elseif($val['cat'] == "code") $order = "pr.codigo_mask asc";
			elseif($val['cat'] == "order") $order = "v.ID desc";
			else $order = "v.ID desc";
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('p'=>'tb_kang_vendasprod','*'),
			        array('p.ID_PEDIDO','p.QT','pr.CODIGO','c.EMPRESA'))
			        ->join(array('pr'=>'produtos'),'p.ID_PRODUTO = pr.ID')
			        ->join(array('v'=>'tb_kang_vendas'),'p.ID_PEDIDO = v.ID')
			        ->join(array('c'=>'clientes'),'v.EMPRESA = c.ID')
			        ->where("v.sit = true and v.STATUS != 'FINISHED' and pr.CODIGO like '%".$val[busca]."%'")
			        ->order($order,'');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
								
		}

		function listaPedidosvendas($var){
			
		    $where = "";
		    
		    if(!empty($var['dataini']) || !empty($var['datafin'])):
			    if(!empty($var['dataini']) and !empty($var['datafin'])):
				    $dataini 	= substr($var['dataini'],6,4).'-'.substr($var['dataini'],3,2).'-'.substr($var['dataini'],0,2);
				    $datafin	= substr($var['datafin'],6,4).'-'.substr($var['datafin'],3,2).'-'.substr($var['datafin'],0,2);
			    	$where 		= ' and t.DATA BETWEEN "'.$dataini.'" and "'.$datafin.'  23:59:59"';
			    elseif (!empty($var['dataini'])):
				    $dataini 	= substr($var['dataini'],6,4).'-'.substr($var['dataini'],3,2).'-'.substr($var['dataini'],0,2);
				    $where	 	= ' and t.data_vend >= "'.$dataini.' 23:59:59"';
			    elseif (!empty($var['datafin'])):
				    $datafin 	= substr($var['datafin'],6,4).'-'.substr($var['datafin'],3,2).'-'.substr($var['datafin'],0,2);
				    $where		= ' and t.data_vend <= "'.$datafin.' 23:59:59"';
			    endif;
		    endif;
		    
		    $where .= (!empty($var['buscaid'])) ? " and t.ID = '".ereg_replace("[^0-9]", " ", $var['buscaid'])."'" : "";
		    $where .= (isset($var['buscacli']) and $var['buscacli'] != 0) ? " and t.EMPRESA = '".$var['buscacli']."'" : "";
		    $where .= (!empty($var['order'])) ? " and t.you_order = '".$var['order']."'" : "";
		    $where .= (isset($var['buscasit']) and $var['buscasit'] == 1) ? " and t.sit = true and t.STATUS = 'Received'" : "";
		    $where .= (isset($var['buscasit']) and $var['buscasit'] == 2) ? " and t.sit = true and t.STATUS = 'Ordered'" : "";
		    $where .= (isset($var['buscasit']) and $var['buscasit'] == 3) ? " and t.sit = true and t.STATUS = 'Finished'" : "";
		    $where .= (isset($var['buscasit']) and $var['buscasit'] == 4) ? " and t.sit = false" : "";

		    $limit = (isset($var['limite'])) ? $var['limite'] : 10000;		    
		    
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
						
			$select->from(array('t'=>'tb_kang_vendas','*'), array('t.ID as idped','DATE_FORMAT(t.DATA,"%d/%m/%Y %H:%i" ) as dtvenda', 'c.ID as idparceiro', 'c.EMPRESA','t.STATUS','t.sit as situacao'))
		        ->join(array('c'=>'clientes'),'t.EMPRESA = c.ID')
		        ->where("t.id > 0 ".$where)
		        ->order('t.id desc','')
			    ->limit($limit);
			  
			$stmt = $db->query($select);
			$objVendas = $stmt->fetchAll();
			
			if(!empty($var['codigo']) and count($objVendas)>0){
			   	
			    $idvend = "";
			    foreach ($objVendas as $vendas){
			        $idvend .= $vendas->idped.",";
			    }
			    
			    $whereprod = "(p.CODIGO = '".$var['codigo']."' || pv.COD_PRODCLI = '".$var['codigo']."')"
			              ." and v.ID in (".substr($idvend, 0,-1).")";
			    
			    $select = $db->select();
			    $select->from(array('pv'=>'tb_kang_vendasprod','*'), array('pv.*','pc.letra','pv.PRECO','p.CODIGO','v.ID as idped','DATE_FORMAT(v.DATA,"%d/%m/%Y %H:%i" ) as dtvenda','c.EMPRESA','v.STATUS as statusped','v.sit as sitped'))
			    	->join(array('p'=>'produtos'),'p.ID = pv.ID_PRODUTO')
			    	->joinLeft(array('pc'=>'tb_produtosclasses'),'pc.id = pv.id_produtosclasse')
			    	->join(array('v'=>'tb_kang_vendas'),'pv.ID_PEDIDO = v.ID')
			    	->join(array('c'=>'clientes'),'v.EMPRESA = c.ID')
			    	->where($whereprod)
			    	->order('v.id desc');
			    
			    $stmt = $db->query($select);
			    
			    return array('objeto' => $stmt->fetchAll(), 'tipo' => 2);
			}else{
			    return array('objeto' => $objVendas, 'tipo' => 1);
			}
								
		}
		
		function listaPedidosvendasdet($val){
						
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_kang_vendas'), array('t.ID as idped','t.*','c.EMPRESA','t.STATUS','DATE_FORMAT(t.DATA,"%d/%m/%Y" ) as dtcominvoice','t.you_order as ordercli', 
			        't.payment as termospagamento', 't.freight as frete', 't.partial_shipment as embarqueparcial','t.frete as fretevalor','t.sit as sitped'))
			        ->join(array('c'=>'clientes'),'t.EMPRESA = c.ID')
			        ->joinLeft(array('ck'=>'tb_clientes_infokang'),'ck.id_cliente = c.ID')
			        ->where("md5(t.ID) = '".$val['ped']."'")
			        ->order('t.id desc','');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
				
		/*-- Lista produtos das vendas kang-----------------------------
		 * Usado em: 
		 * gerarpediocompraAction
		 * pedidosprodAction  
		 * */
		function listaPedidosvendasprod($val){
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_kang_vendasprod','*'), array('t.*','t.MOEDA as moedaprod','p.CODIGO','p.ID','t.id as idprodven','p.ID as idproduto','n.ncm as ncmg','h.ncm as hscode','h.retorno','m.descricaochines','p.unidade as und'))
			        ->join(array('p'=>'produtos'),'t.ID_PRODUTO = p.ID')
			        ->joinLeft(array('n'=>'tb_produtosncm'),'n.id = p.id_ncm')
			        ->joinLeft(array('h'=>'tb_produtoshscode'),'h.id = p.id_hscode')
			        ->joinLeft(array('m'=>'tb_produtosmaterial'),'m.id = p.id_produtosmaterial')
			        ->joinLeft(array('c'=>'tb_produtosclasses'),'c.id = t.id_produtosclasse')
			        ->where("md5(t.ID_PEDIDO) = '".$val['ped']."'")
			        ->order('p.codigo_mask','');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
								
		}		
		
		function listaVendaskang($val=array()){
		
		    $order = 't.id desc';
		    
			$dataini = (isset($val['dataini'])) ? substr($val['dataini'],6,4).'-'.substr($val['dataini'],3,2).'-'.substr($val['dataini'],0,2) : "";
			$datafin = (isset($val['datafin'])) ? substr($val['datafin'],6,4).'-'.substr($val['datafin'],3,2).'-'.substr($val['datafin'],0,2) : "";
			
			if(!empty($val['buscaid'])):
				$where = " and t.id = ".substr($val['buscaid'],1);
			elseif((!empty($val['dataini'])) and (!empty($val['datafin']))):
				$where = " and t.data between '".$dataini."' and '".$datafin."'";
			elseif((!empty($val['dataini'])) and (empty($val['datafin']))):
				$where = " and t.data >= '".$dataini."'";
			elseif((empty($val['dataini'])) and (!empty($val['datafin']))):
				$where = " and t.data <= '".$datafin."'";
			elseif(!empty($val['buscacli'])):
				$where = " and t.id_cliente = ".$val['buscacli'];
				if(!empty($val['financeiro'])):
					$where .= " and t.financeiro = ".$val['financeiro'];
				endif;
				
				$order = 't.id asc';
			endif;
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_kang_cominvoice','*'),
		        array('t.id as idped','DATE_FORMAT(t.data,"%d/%m/%Y %H:%i" ) as dtvenda','c.EMPRESA','t.status','t.statusfin','t.sit as sitcominvoice','t.financeiro'))
		        ->join(array('c'=>'clientes'),'t.id_cliente = c.ID')
		        ->where("t.id > 0".$where)
		        ->order($order);
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
								
		}
		
		function atualizaPedidovenda($params){
			$bo		= new KangvendasModel();
			$bop    = new VendasprodModel(); 
						
			$array = $arrayCheck = array();
			foreach (KangvendasBO::listaPedidosvendasprod(array('ped' => md5($params['ped']))) as $prod){
			    $bop->update(array(
		            'QT'                  => $params['qt_'.$prod->idprodven],
		            'PRECO'               => str_replace(",",".", str_replace(".","", $params['preco_'.$prod->idprodven])),
		            'COD_PRODCLI'         => $params['codcli_'.$prod->idprodven],
		            'MOEDA'               => 'USD',
		            'id_produtosclasse'   => (!empty($params['classe_'.$prod->idprodven]) and $params['classe_'.$prod->idprodven] != 0) ? $params['classe_'.$prod->idprodven] : null,
			    ), "ID = '".$prod->idprodven."'");
			    
			    if(!empty($params[$prod->idprodven])){
			        $array[] = $prod->idprodven;
			    }
			    
			    $arrayCheck[] = $prod->idprodven;
			    			    
			    $i++;
			}
						
			$result = array_diff($arrayCheck, $array);
			foreach ($result as $row => $value){
                $bop->delete("ID = '".$value."'");
			}
						
			$data	= array(
				'dt_entrega'		=> substr($params['dataent'],6,4).'-'.substr($params['dataent'],3,2).'-'.substr($params['dataent'],0,2),
				'you_order'			=> $params['you_order'],
				'defrom'			=> $params['defrom'],
				'para'				=> $params['para'],
				'freight'			=> $params['freight'],
				'payment'			=> $params['payment'],
				'partial_shipment'	=> $params['partial_shipment'],
				'shipment_agent'	=> $params['shipment_agent']
			);
		
			$id	= $bo->update($data, "id = '".$params['ped']."'");
			return $id;
		}
		
		
		function listaCominvoicekang($val){
		
			$dataini = substr($val['dataini'],6,4).'-'.substr($val['dataini'],3,2).'-'.substr($val['dataini'],0,2);
			$datafin = substr($val['datafin'],6,4).'-'.substr($val['datafin'],3,2).'-'.substr($val['datafin'],0,2);
			
			if(!empty($val['buscaid'])):
				$where = " and t.id = ".substr($val['buscaid'],1);
			elseif((!empty($val['dataini'])) and (!empty($val['datafin']))):
				$where = " and t.data between '".$dataini."' and '".$datafin."'";
			elseif((!empty($val['dataini'])) and (empty($val['datafin']))):
				$where = " and t.data >= '".$dataini."'";
			elseif((empty($val['dataini'])) and (!empty($val['datafin']))):
				$where = " and t.data <= '".$datafin."'";
			elseif(!empty($val['buscacli'])):
				$where = " and t.id_cliente = ".$val['buscacli'];
			endif;
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_kang_cominvoice','*'),
			        array('t.id as idped','DATE_FORMAT(t.data,"%d/%m/%Y %H:%i" ) as dtvenda','c.EMPRESA','t.status','t.statusfin'))
			        ->join(array('c'=>'clientes'),'t.id_cliente = c.ID')
			        ->where("t.sit != 0 and status = 'SHIPPED' ".$where)
			        ->order('t.id desc','')
			        ->limit(1000);
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		//---Lista PK das invoices com fornecedor --------------------
		function listaVendaskangforn($val){

			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_kang_cominvoiceprod','*'),
			        array('p.id_kang_compra','DATE_FORMAT(p.data,"%d/%m/%Y %H:%i" ) as dtvenda','c.EMPRESA','t.id_cominvoice','cc.nome'))
			        ->join(array('p'=>'tb_kang_compra'),'t.id_kang_compra = p.id_kang_compra')
			        ->join(array('c'=>'clientes'),'p.id_for = c.ID')
			        ->joinLeft(array('cc'=>'tb_clientechina'),'c.ID = cc.id_cliente')
			        ->where("md5(t.id_cominvoice) = '".$val."'")
			        ->order('t.id_kang_compra asc')
			        ->group("t.id_kang_compra");
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
								
		}
		
		//---Lista produtos das Invoices-------------------
		/*-- Usado em vendasdetAction ------
		 *-- Usado em vendaslistapacotes------
		 *-- Usado em vendaslistapacotesfor------ 
		 *-- Usado em invoicecomimp ------------- */
		
		function listaCominvoiceprod($val,$for=""){
			
		    $where = "";
			if(!empty($for)):
				$where = " and md5(pk.id_for) = '".$for."'"; 
			endif;
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_kang_cominvoiceprod','*'), array('sum(t.qt) as qtprod','sum(t.pack) as qtpack','t.cod_prodcli','p.CODIGO','t.qt','t.preco','sum(t.preco*t.qt) as precoqt','t.id_prod','t.moeda as moedaprod','p.PESO','t.id_kang_compra','p.hs_code','p.ID as idproduto','t.ncm as ncmg'))
		        ->join(array('p'=>'produtos'),'t.id_prod = p.ID')
		        ->joinLeft(array('n'=>'tb_produtosncm'),'n.id = p.id_ncm')
		        ->join(array('pk'=>'tb_kang_compra'),'pk.id_kang_compra = t.id_kang_compra')
		        ->where("md5(t.id_cominvoice) = '".$val."'".$where)
		        ->group('t.id_prod')
		        ->order('p.codigo_mask asc');
		  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		function listaCominvoiceprodclasse($val){
		    
		    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		    $db->setFetchMode(Zend_Db::FETCH_OBJ);
		    
		    $select = $db->select();
		    
		    $select->from(array('t'=>'tb_kang_cominvoiceprod','*'), array('t.*', 't.qt as qtinvoice', 't.preco as precoinvoice', 'p.*', 'c.*'))
    		    ->join(array('p'=>'produtos'),'t.id_prod = p.ID')
    		    ->join(array('kc'=>'tb_kang_compra'),'kc.id_kang_compra = t.id_kang_compra')
    		    ->join(array('kp'=>'tb_kang_comprasprod'),'kp.id_kang_compra = kc.id_kang_compra AND t.id_prod = kp.id_prod')
    		    ->join(array('vp'=>'tb_kang_vendasprod'),'vp.ID_PEDIDO = kc.id_ped AND vp.ID_PRODUTO = kp.id_prod')
    		    ->joinLeft(array('c'=>'tb_produtosclasses'),'c.id = vp.id_produtosclasse')
    		    
    		    ->where("md5(t.id_cominvoice) = '".$val."'")
    		    ->order('p.codigo_mask asc');
		    
		    $stmt = $db->query($select);
		    return $stmt->fetchAll();
		}
		
		//---Lista produtos das Invoices agrupados por hscode -------------------
		/*-- Usado em vendasdeclaracaoexpAction ------ */
		
		function listaCominvoiceprodhscode($val,$for=""){
				
			if(!empty($for)):
				$where = " and md5(pk.id_for) = '".$for."'";
			endif;
				
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
			$select = $db->select();
				
			$select->from(array('t'=>'tb_kang_cominvoiceprod','*'), array('sum(t.qt) as qtprod','t.cod_prodcli','p.CODIGO','sum(t.preco * t.qt) as precounit','t.hs_code as hscode','t.deschscodechines','t.retorno'))
			->join(array('p'=>'produtos'),'t.id_prod = p.ID')
			//->joinLeft(array('n'=>'tb_produtosncm'),'n.id = p.id_ncm')
			//->join(array('pk'=>'tb_kang_compra'),'pk.id_kang_compra = t.id_kang_compra')
			->where("md5(t.id_cominvoice) = '".$val."'".$where)
			->group('t.hs_code')
			->order('p.codigo_mask asc');
				
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		

		/*-- Usado em KangvendaBO::gerarXmlcominvoice------- */
		function listaCominvoiceprodxml($val){
				
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
			$select = $db->select();
				
			$select->from(array('t'=>'tb_kang_cominvoiceprod','*'), array('t.qt','t.cod_prodcli','t.qt','t.preco','t.preco as precoinvoice','t.id_prod','t.moeda','t.id_kang_compra','v.you_order','p.CODIGO'))
				->join(array('v'=>'tb_kang_vendas'), 'v.ID = t.id_kang_vend')
				->join(array('p'=>'produtos'),'t.id_prod = p.ID')
				->where("md5(t.id_cominvoice) = '".$val."'")
				->group('t.id');
				
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		
		/*--- Lista fornecedores com produtos disponiveis para packing list ------------------
		*-- Usado em vendaslistapacotesfor------ */
		function listaCominvoicefornprod($val){
				
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
		
			$select = $db->select();
		
			$select->from(array('t'=>'tb_kang_cominvoiceprod','*'), array('sum(t.qt) as qtprod','sum(t.pack) as qtpack','pk.id_for'))
			->join(array('pk'=>'tb_kang_compra'),'pk.id_kang_compra = t.id_kang_compra')
			->where("md5(t.id_cominvoice) = '".$val."'")
			->group('pk.id_for');
		
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		
		/*-- Lista produtos da Comercial Invoice detalhada por PK (Compra) ---------------- 
		 * -- Usado em vendaspurchaseAction ------
		 * -- Usado em vendalistapacotesforAction --------
		 * */
		function buscaProdutosinvoicecompra($var){

			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_kang_cominvoiceprod','*'),  array('*','t.id as idcomprod','t.qt as qtent','t.preco as qtpreco','(t.qt-t.pack) as qtdisp','DATE_FORMAT(pc.prazo,"%d/%m/%y" ) as dtent'))
				->join(array('pc'=>'tb_kang_comprasprod'),'pc.id_kang_compra = t.id_kang_compra and t.id_prod = pc.id_prod')
		        ->join(array('pd'=>'produtos'),'pd.ID = t.id_prod')
		        ->where("md5(t.id_cominvoice) = '".$var."'");
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();								
		}
		
		/*-- Lista produtos da Comercial Invoice detalhada por PK (Compra) agrupada por HS CODE ----------------
		/*-- Usado em vendasdeclaracaoexpAction ------ */
		
		function buscaProdutosinvoicecomprahscode($var){
		
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
			$select = $db->select();
				
			$select->from(array('t'=>'tb_kang_cominvoiceprod','*'),  array('t.id','sum(t.qt) as qtprod','t.cod_prodcli','sum(t.preco * t.qt) as precounit','t.hs_code as hscode','t.deschscodechines','t.retorno','t.id_kang_compra','DATE_FORMAT(t.dt_retorno,"%d/%m/%Y" ) as dataret','t.invoiceretorno'))
			->where("md5(t.id_cominvoice) = '".$var."'")
			->group('t.id_kang_compra')
			->group('t.hs_code');
				
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		/*--- Lista fornecedor das compras inseridas na invoice -------------------
		 * -- Usado em vendaspurchaseAction --------
		 * -- Usado em vendalistapacotesforAction --------
		 * */
		function listaCominvoicefornecedores($val){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_kang_cominvoiceprod','*'), array('c.ID'))
			        ->join(array('p'=>'tb_kang_compra'),'p.id_kang_compra = t.id_kang_compra')
			        ->join(array('c'=>'clientes'),'c.ID = p.id_for')
			        ->joinLeft(array('ch'=>'tb_clientechina'),'ch.id_cliente = c.ID')
			        ->joinLeft(array('ce'=>'clientes_emails'),'ce.ID_CLIENTE = c.ID and ce.tipo = 1')
			        ->where("md5(t.id_cominvoice) = '".$val."'")
			        ->group('c.ID')
			        ->order('c.EMPRESA asc');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		/*--- Lista compras inseridas na invoice -------------------
		 * -- Usado em vendaspurchaseAction --------
		 * */
		function listaCominvoicepurchase($val){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_kang_cominvoiceprod','*'), array('*'))
			        ->join(array('p'=>'tb_kang_compra'),'p.id_kang_compra = t.id_kang_compra')
			        ->join(array('c'=>'clientes'),'c.ID = p.id_for')
			        ->joinLeft(array('ch'=>'tb_clientechina'),'ch.id_cliente = c.ID')		
			        ->where("md5(t.id_cominvoice) = '".$val."'")
			        ->group('p.id_kang_compra')
			        ->order('c.EMPRESA asc');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		//---Busca Invoice-------------------
		/*-- Usado em vendasdetAction ------
		 *-- Usado em vendalistapacotesAction ------
		 *-- Usado em vendalistapacotesforAction ------
		 */
		function buscaCominvoice($val){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_kang_cominvoice','*'), array('*','t.id as idped','DATE_FORMAT(t.data,"%d/%m/%Y" ) as dtcominvoice', 'DATE_FORMAT(t.dt_previsao,"%d/%m/%Y" ) as dtprevisao',
					't.you_order as kyou_order','t.defrom as kdefrom','t.para as kpara','t.freight as kfreight','t.payment as kpayment',
					't.partial_shipment as kpartial_shipment','t.freight_charge as kfreight_charge','t.shipment_agent as kshipment_agent','t.sit as sitinv'))
			        ->join(array('c'=>'clientes'),'t.id_cliente = c.ID')
			        ->joinLeft(array('ck'=>'tb_clientes_infokang'),'ck.id_cliente = c.ID')
			        ->where("md5(t.id) = '".$val."'");
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}

		function cancelaCominvoice($params){
		    try{
				$bo		= new KangvendasModel();
				$boi	= new KanginvoiceModel();
				$boc	= new KangcomprasModel();
				$boe	= new KangcomprasentregaModel();
				$bof	= new FinanceirochinaModel();
				$bofi	= new FinanceirochinainvoiceModel();
				
				$usuario 	= Zend_Auth::getInstance()->getIdentity();
				
				$data = array(
					'sit'		=> 0,
					'status'	=> "CANCELADO"
				);
				//-- cancela a venda --
				$id = $boi->update($data, "md5(id) = '".$params['invoice']."'");
				
				
				//-- libera produtos para nova venda -----
				$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
				$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
				$select = $db->select();
				
				$select->from(array('t'=>'tb_kang_cominvoiceprod','*'), array('t.*','pk.*'))
			        ->join(array('pk'=>'tb_kang_compra'),'pk.id_kang_compra = t.id_kang_compra')
			        ->where("md5(t.id_cominvoice) = '".$params['invoice']."'")
					->order('t.id_prod');
				  
				$stmt = $db->query($select);
				
				$count = 0;
				foreach($stmt->fetchAll() as $produtos){
				    foreach ($boe->fetchAll("id_ped = ".$produtos->id_kang_compra." and id_prod = ".$produtos->id_prod) as $entrega);
					$boe->update(array('sit' => '1'), 'id = '.$entrega->id);				
				}
				
				//libera financeiro ---------------------
				$bofi->delete("md5(id_kang_cominvoice) = '".$params['invoice']."'");
				
				
				LogBO::cadastraLog("Kang/Cancela Venda",3,$usuario->ID,$id,"S".substr("000000".$id, -6,6));
				
			}catch (Zend_Exception $e){
				$boerro	= new ErrosModel();
				$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "KangvendasBO::cancelaCominvoice()");
				$boerro->insert($dataerro);
				return false;
			}
		}
		
		function fecharFininvoice($params){
			$bo		= new KangvendasModel();
			$boi	= new KanginvoiceModel();
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
				
			$data = array(
				'financeiro'		=> 0
			);
				
			$id = $boi->update($data, "md5(id) = '".$params['invoice']."'");
		
			LogBO::cadastraLog("Kang/Finaliza financeiro Invoice",3,$usuario->ID,$id,"S".substr("000000".$id, -6,6));
		}
		
		function finalizaPlcominvoice($params){
			$bo		= new KangvendasModel();
			$boi	= new KanginvoiceModel();
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
		
			$data = array(
				'sit'		=> 3,
				'status'	=> "AGUARDANDO EMBARQUE"
			);
		
			$id = $boi->update($data, "md5(id) = '".$params['invoice']."'");
		
			LogBO::cadastraLog("Kang/Fin PL Invoice",3,$usuario->ID,$id,"S".substr("000000".$id, -6,6));
		}
		
		
		/*--- Lista pack list cadastrados ---------------
		 * usado em listapacotesAction ----------
		 * usado em vendalistapacotesAction --------------
		 * */
		function listarPacklistcad($params, $for=null){
			
			if(!empty($params['ped'])):
				//-- Busco por venda (S) --------------------
				$where = "sit = 1 and md5(id_cominvoice) = '".$params['ped']."'";
				
				//-- Filtro por fornecedor --------------------
				if(!empty($for)):
					$where .= " and md5(id_for) = '".$for."'";
				endif;			
			else:
				$where = "sit = 1";
			endif;
			
			$bov	= new KangvendasModel();
			$bo 	= new PacklistModel();				
			return $bo->fetchAll($where, array("ordemfinal asc", "ordem asc"));
		}
		
		/*--- Lista produtos do pack list cadastrados ---------------
		 * usado em listapacotesAction ----------
		 * usado em vendalistapacotesAction --------------
		 * */
		function listarPacklistprod($params){
			
			if(!empty($params['ped'])):
				$where = "md5(id_cominvoice) = '".$params['ped']."'";
			else:
				$where = "t.sit = 1";
			endif;
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_kang_packlist','*'), array('*','n.ncm as ncmg','n.ncmex'))
			        ->join(array('p'=>'tb_kang_packlistprod'),'t.id = p.id_pack')
			        ->join(array('pd'=>'produtos'),'pd.ID = p.id_prod')
			        ->joinLeft(array('n'=>'tb_produtosncm'),'n.id = pd.id_ncm')
			        ->where($where);
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();	
		}
		
		
		
		/*--- Lista produtos do pack list ---------------
		 * usado em KangvendasBO::gerarXmlcominvoice ----------
		 * */
		function listarPacklprod($val){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_kang_packlist','*'), array('*'))
			        ->join(array('p'=>'tb_kang_packlistprod'),'t.id = p.id_pack')
			        ->where("md5(t.id_cominvoice) = '".$val."'");
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		function buscadadosDuplicapalete($var = array()){
		    $bos  = new KangvendasModel();
		    $bol  = new PacklistModel();
		    $bop  = new PacklistprodModel();
		    $boc  = new KanginvoiceprodModel();
		    
		    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		    $db->setFetchMode(Zend_Db::FETCH_OBJ);

		    //-- Listo os produtos do pacote ----------------------------------
		    $select = $db->select();
		    $select->from(array('t'=>'tb_kang_packlist','*'), array('*'))->join(array('p'=>'tb_kang_packlistprod'),'t.id = p.id_pack')->where("t.id  = '".$var['pack']."'");
		    	
		    $stmt = $db->query($select);

		    $qtx = 10000;
		    foreach ($stmt->fetchAll() as $packprod){
		        $prodsale = $boc->fetchRow("id_cominvoice = '".$packprod->id_cominvoice."' and id_prod = '".$packprod->id_prod."'");
		        
		        $dis = ($prodsale->qt-$prodsale->pack)  / $packprod->qt;
		        $dis = floor($dis);
		        
		        if($dis < $qtx){
		            $qtx = $dis;
		        }   
		    }
		    
		    ?>
		    Você deseja replicar este pallet? <br /> Quantas vezes? 
		    <input type="text" name="replica" style="width: 35px" class="inteiro" value="1"> (Max: <?php echo $qtx?>)
		    <br /><br />		    
		    
		    <div id="resReplica" class="red"></div>
		    
		    <input type="button" class="basicBtn" value="Cancelar" onclick="$.alerts._hide()">
		    <input type="button" class="basicBtn" value="Salvar" id="replicarPallet">
		    
		    <input type="hidden" name="pack" value="<?php echo $var['pack']?>" >
		    <input type="hidden" name="maxPack" value="<?php echo $qtx?>" >
		    
		    <?php 
		    
		    /* $this->view->objProdpk	= KangvendasBO::buscaProdutosinvoicecompra($params['ped']);
		    
		    $this->view->objPack	= KangvendasBO::listarPacklistcad($params,"");
		    $this->view->objProdpl	= KangvendasBO::listarPacklistprod($params); */
		}
		
		
		
		function gerarXmlcominvoice($val){	
			$boi	= new KangvendasModel();
			$bo 	= new KanginvoiceModel();
			$bop	= new PacklistModel();
			
			//versao do encoding xml
			$dom = new DOMDocument("1.0", "ISO-8859-1");
			
			//retirar os espacos em branco
			$dom->preserveWhiteSpace = false;
			
			//gerar o codigo
			$dom->formatOutput = true;
			
			//criando o nó principal (root)
			$root = $dom->createElement("comercialenvoice");
			
			//nó filho (invoice)
			$invoice = $dom->createElement("invoice");
			
			foreach ($bo->fetchAll("md5(id) = '".$val['idcom']."'") as $listd);
			$invoice->appendChild($dom->createElement("id", $listd->id));
			$invoice->appendChild($dom->createElement("porto", $listd->para));
			
			$root->appendChild($invoice);
									
			//nó filho (produtos)
			$produtos = $dom->createElement("produtos");
			
			$cont = 0;
			foreach (KangvendasBO::listaCominvoiceprodxml($val['idcom']) as $listp):
			    $cont++;
				$codigos = $dom->createElement("produto");
				$codigos->appendChild($dom->createElement("ord", $cont));
				$codigos->appendChild($dom->createElement("idprod", $listp->id_prod));
				$codigos->appendChild($dom->createElement("cod", $listp->CODIGO));
				$codigos->appendChild($dom->createElement("qt", $listp->qt));
				$codigos->appendChild($dom->createElement("preco", $listp->precoinvoice));
				$codigos->appendChild($dom->createElement("moeda", $listp->moeda));
				$codigos->appendChild($dom->createElement("pedido", $listp->you_order));
				
				$produtos->appendChild($codigos);
				
			endforeach;
						
			$produtos->appendChild($codigos);
			$root->appendChild($produtos);
			
			$dom->appendChild($root);
			print $dom->saveXML();
			
			return $listd->id;		
			
		}
		
		//--- Lista pedidos de compra da shunkang ------------------------------------
		function listaPedidoscompra($var){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();	
			
			$where = "";
			
			if(!empty($var['dataini']) || !empty($var['datafin'])):
    			if(!empty($var['dataini']) and !empty($var['datafin'])):
        			$dataini 	= substr($var['dataini'],6,4).'-'.substr($var['dataini'],3,2).'-'.substr($var['dataini'],0,2);
        			$datafin	= substr($var['datafin'],6,4).'-'.substr($var['datafin'],3,2).'-'.substr($var['datafin'],0,2);
        			$where 		= ' and p.data BETWEEN "'.$dataini.'" and "'.$datafin.'  23:59:59"';
    			elseif (!empty($var['dataini'])):
        			$dataini 	= substr($var['dataini'],6,4).'-'.substr($var['dataini'],3,2).'-'.substr($var['dataini'],0,2);
        			$where	 	= ' and p.data >= "'.$dataini.'"';
    			elseif (!empty($var['datafin'])):
        			$datafin 	= substr($var['datafin'],6,4).'-'.substr($var['datafin'],3,2).'-'.substr($var['datafin'],0,2);
        			$where		= ' and p.data <= "'.$datafin.' 23:59:59"';
    			endif;
			endif;
			
			$where .= (!empty($var['buscaid'])) ? " and p.id_kang_compra = '".ereg_replace("[^0-9]", " ", $var['buscaid'])."'" : "";
			$where .= (isset($var['buscacli']) and $var['buscacli'] != 0) ? " and p.id_for = '".$var['buscacli']."'" : "";
			$where .= (!empty($var['order'])) ? " and p.id_ped = '".ereg_replace("[^0-9]", " ", $var['order'])."'" : "";
			$where .= (isset($var['buscasit']) and $var['buscasit'] != 4) ? " and p.sit = '".$var['buscasit']."'" : "";
						
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('p'=>'tb_kang_compra'),array('*','DATE_FORMAT(p.data,"%d/%m/%Y" ) as dtcompra','p.sit as sitped'))
					->join(array('c'=>'clientes'), 'c.ID = p.id_for')
			        ->where("p.id_kang_compra > 0 ".$where)
			        ->order('p.id_kang_compra desc');
			  
			$stmt = $db->query($select);
			$objCompras = $stmt->fetchAll();
				
			if(!empty($var['codigo']) and count($objCompras)>0){
				 
				$idvend = "";
				foreach ($objCompras as $compras){
					$idvend .= $compras->id_kang_compra.",";
				}
				 
				$whereprod = "(p.CODIGO = '".$var['codigo']."') and v.id_kang_compra in (".substr($idvend, 0,-1).")";
				 
				$select = $db->select();
				$select->from(array('pv'=>'tb_kang_comprasprod','*'), array('pv.*','p.CODIGO', 'DATE_FORMAT(v.data,"%d/%m/%Y %H:%i" ) as dtcompra','c.EMPRESA','v.fin','v.sit as sitped'))
				->join(array('p'=>'produtos'),'p.ID = pv.id_prod')
				->join(array('v'=>'tb_kang_compra'),'pv.id_kang_compra = v.id_kang_compra')
				->join(array('c'=>'clientes'),'v.id_for = c.ID')
				->where($whereprod)
				->order('v.id_kang_compra desc');
				 
				$stmt = $db->query($select);
				 
				return array('objeto' => $stmt->fetchAll(), 'tipo' => 2);
			}else{
				return array('objeto' => $objCompras, 'tipo' => 1);
			}			
		}
		
		//--- Gerar Comercial Invoices -------------------------------------------
		/*--- Lista empresas com produtos entregues de compras, prontos para ser embarcados -----------------------------
		 * Usado em geravendaAction-----------------*/
	
		function listaEmpresasgerarinvoice(){

			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('v'=>'tb_kang_vendas','*'), array('c.ID as idcli','c.EMPRESA as empinvoice'))
					->join(array('c'=>'clientes'),'v.EMPRESA = c.ID')
					->join(array('p'=>'tb_kang_compra'),'v.ID = p.id_ped')
			        ->join(array('e'=>'tb_kangprodent'),'e.id_ped = p.id_kang_compra and e.sit = 1')
			        ->group("v.EMPRESA")
			        ->order('c.EMPRESA');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();								
		}
		
		/*--- Lista produtos entregues de compras, prontos para ser embarcados pela empresa apartir do cliente-----------------------------
		 * Usado em geravendaempAction-----------------*/
	
		function buscaProdutosparainvoice($var){

			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('v'=>'tb_kang_vendas','*'), array('*','sum(e.qt) as qtent','DATE_FORMAT(pc.prazo,"%d/%m/%Y" ) as dtent'))
					->join(array('p'=>'tb_kang_compra'),'v.ID = p.id_ped')
			        ->join(array('e'=>'tb_kangprodent'),'e.id_ped = p.id_kang_compra and e.sit = 1')
			        ->join(array('pc'=>'tb_kang_comprasprod'),'pc.id_kang_compra = p.id_kang_compra')
			        ->join(array('pd'=>'produtos'),'pd.ID = e.id_prod')
			        ->where("e.id_prod = pc.id_prod and v.EMPRESA = ".$var['empresa'])
			        ->group('p.id_kang_compra')
			        ->group('e.id_prod');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();								
		}
		
		/*-- Lista compras com entreguas apartir do cliente --------------------------------------*/		
		function buscaComprasparainvoice($var){

			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('v'=>'tb_kang_vendas','*'), array('c.ID as idfor','c.EMPRESA as cliente'))
					->join(array('p'=>'tb_kang_compra'),'v.ID = p.id_ped')
					->join(array('e'=>'tb_kangprodent'),'e.id_ped = p.id_kang_compra and e.sit = 1')
					->join(array('c'=>'clientes'),'c.ID = p.id_for')
					->joinLeft(array('ch'=>'tb_clientechina'),'ch.id_cliente = c.ID')			        
			        ->where("v.EMPRESA = ".$var['empresa'])
			        ->group('p.id_kang_compra');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();								
		}
		
		/*--- Lista produtos entregues de compras, com informacoes do pedido de venda  -----------------------------
		 * Usado em KangvendasBO::gravarComercialinvoice-----------------*/
		function buscaProdutosentreguesped($var){

			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('v'=>'tb_kang_vendas','*'), array('*','sum(e.qt) as qtent','e.id as ident','h.ncm as ncmhscode','h.descricao','h.descricaochines','h.retorno as retornohscode'))
				->join(array('vp'=>'tb_kang_vendasprod'),'v.ID = vp.ID_PEDIDO')
				->join(array('p'=>'tb_kang_compra'),'v.ID = p.id_ped')
				->join(array('pc'=>'tb_kang_comprasprod'),'pc.id_kang_compra = p.id_kang_compra')
		        ->join(array('e'=>'tb_kangprodent'),'e.id_ped = p.id_kang_compra and e.sit = 1')
		        ->join(array('pd'=>'produtos'),'pd.ID = e.id_prod')
		        ->joinLeft(array('h'=>'tb_produtoshscode'),'h.id = pd.id_hscode')
		        ->where("e.id_prod = pc.id_prod and pc.id_prod = vp.ID_PRODUTO and v.EMPRESA = ".$var['empresa'])
		        ->group('p.id_kang_compra')
		        ->group('e.id_prod');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();								
		}
						
		//---- Geracao comercial invoice -------------------------------------------------
		function gravarComercialinvoice($var){
			$bo		= new KangvendasModel();
			$boi	= new KanginvoiceModel();
			$boip	= new KanginvoiceprodModel();
			$boc	= new KangcomprasModel();
			$boe	= new KangcomprasentregaModel();
			$bom	= new KanginvoicelinksModel();
			$usuario	= Zend_Auth::getInstance()->getIdentity();
			
			//-- Gera nova comercial invoice ----------------------------
			
			$arrayin['data']			= date("Y-m-d H:i:s");
			$arrayin['id_cliente']		= $var['empresa'];
			$arrayin['status']			= "AGUARDANDO DOCUMENTOS";
			$arrayin['sit']				= 1;
			
			$id	= $boi->insert($arrayin);

			/*-- Gravacao dos produtos -------------------------- -------*/
			foreach (KangvendasBO::buscaProdutosentreguesped($var) as $produtos):
				if(!empty($var[$produtos->id_kang_compra])):
					$arrayprod = "";
					$arrayprod = array();
				
					$arrayprod['id_cominvoice']			= $id;
					$arrayprod['id_prod']				= $produtos->id_prod;
					$arrayprod['qt']					= $produtos->qtent;
					$arrayprod['preco']					= $produtos->PRECO;
					$arrayprod['moeda']					= $produtos->MOEDA;
					$arrayprod['cod_prodcli']			= $produtos->COD_PRODCLI;
					$arrayprod['id_kang_vend']			= $produtos->ID_PEDIDO;
					$arrayprod['id_kang_compra']		= $produtos->id_kang_compra;
					$arrayprod['hs_code']				= $produtos->ncmhscode;
					$arrayprod['retorno']				= $produtos->retornohscode;
					$arrayprod['deschscode']			= $produtos->descricao;
					$arrayprod['deschscodechines']		= $produtos->descricaochines;
										
					$boip->insert($arrayprod);
					
					$arrayent['sit']	 = 2;
					$boe->update($arrayent, "id_ped = ".$produtos->id_kang_compra." and id_prod = ".$produtos->id_prod);
				endif;
			endforeach;
			
			//--- Listo os fornecedores da cominvoice ------------------------------------------
			foreach (KangvendasBO::listaCominvoicefornecedores(md5($id)) as $fornecedores):
				$dataemail = array(
					'id_cominvoice' 	=> $id,
					'idfor'				=> $fornecedores->id_for,
					'sit'				=> true	 
				);
				
				$bom->insert($dataemail);
				
				//--- Listo as compras dos fornecedores --------------------------------------------------------
				$pks = "";
				foreach (KangvendasBO::listaCominvoicepurchase(md5($id)) as $purchase):
					if($purchase->id_for == $fornecedores->id_for):
						$pks .=  "PK".substr("000000".$purchase->id_kang_compra, -6,6)."/";
					endif;
				endforeach;				
				
				$message = '
				<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><title>SHUNCORP - www.shuncorp.com </title><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta name="keywords" content="rolamentos, cubo de roda, rolamento, embreagem, embreagens, ponta de eixo, pontas de eixo, bomba dagua, bombas dagua, roda, scania, homocinetica, homocineticas, transmissoes" /></head>
				<body>
				<a href="http://www.shuncorp.com" target="_blank"><font size="6" color="#1b999a" face="Arial, Helvetica, sans-serif">Shunkang</font></a><br /><br />
				<font size="1" color="#333333" face="Arial, Helvetica, sans-serif">
				你好, <strong>'.$fornecedores->NOME_CONTATO.' ('.$fornecedores->EMPRESA.' - '.$fornecedores->nome.'),</strong></font><br /><br />
				<font size="3" color="#333333" face="Arial, Helvetica, sans-serif">
				现在你按照慈溪顺康进出口有限公司的合同编号（例如：'.substr($pks, 0,-1).')来完成箱单.
				</font><br />
				<font size="1" color="#333333" face="Arial, Helvetica, sans-serif">
				Now you can make the PACKING LIST of our purchase (s) '.substr($pks, 0,-1).' Cixi ShunKang. 
				</font><br /><br />
				<font size="3" color="#333333" face="Arial, Helvetica, sans-serif">
				直接点击下面的链接，来做这个箱单： 
				</font> <br />
				<font size="1" color="#333333" face="Arial, Helvetica, sans-serif">
				Click on link below to make this PACKING LIST: 
				</font> <br />
				<a href="http://www.ztlbrasil.com.br/admin/publico/gerarpallet/ped/'.md5($id).'/fornmd/'.md5($fornecedores->id_for).'" title="www.ztlbrasil.com.br" target="_blank"> <font size="3" color="#1b999a" face="Arial, Helvetica, sans-serif">
				<strong>http://www.ztlbrasil.com.br/admin/publico/gerarpallet/ped/'.md5($id).'/fornmd/'.md5($fornecedores->id_for).'</strong> </font> </a> <br /><br />
				<font size="3" color="#333333" face="Arial, Helvetica, sans-serif"> 
				切记，你有责任完成这些正确的箱单，当我们货物出口到客户那里后，你将自动收到邮件和你们公司的货运唛头。 
				</font><br />
				<font size="1" color="#333333" face="Arial, Helvetica, sans-serif"> 
				Remember that this information is under your responsibility. As we finish the total export shipment for our client you will receive automatic email with YOUR COMPANY shipping marks. 
				</font><br /><br />
				<font size="3" color="#333333" face="Arial, Helvetica, sans-serif"> 
				如果有什么问题，请联系： 郭燕召  邮箱：shuntaiguo@gmail.com  联系电话：0574-63315331 手机： 13780042250 
				</font><br />
				<font size="1" color="#333333" face="Arial, Helvetica, sans-serif">
				For questions, please contact Mr. Yan Zhao Guo: shuntaiguo@gmail.com / 0574-63315331 / 13780042250
				</font><br /><br />
				<font size="3" color="#333333" face="Arial, Helvetica, sans-serif">
				感谢你的支持！
				</font><br />
				<font size="1" color="#333333" face="Arial, Helvetica, sans-serif">
				Sincerely;
				</font><br /><br />
				<font size="3" color="#333333" face="Arial, Helvetica, sans-serif">
				慈溪顺康进出口有限公司 - 采购部。</font><br />
				<font size="1" color="#333333" face="Arial, Helvetica, sans-serif">
				Cixi ShunKang - Purchasing Department</font>
				</body></html>';
				
				$assunto 	= "托盘发电机 - 慈溪市顺康公司";
				$resp		= $fornecedores->NOME_CONTATO;
				$email		= $fornecedores->EMAIL;
				
				//ContatosBO::enviaMail($assunto, $message, $resp, $email);
				
				/* $email1		= 'blirio@ztlbrasil.com.br';
				ContatosBO::enviaMail($assunto, $message, $resp, $email1);
				
				$email2		= "cleitonsbarbosa@gmail.com";
				ContatosBO::enviaMail($assunto, $message, $resp, $email2);
				
				$email3		= 'shuntaiguo@gmail.com';
				ContatosBO::enviaMail($assunto, $message, $resp, $email3); */
				
			endforeach;
			
			LogBO::cadastraLog("Kang/Gerar Venda",2,$usuario->ID,$id,"S".substr("000000".$id, -6,6));
			
			return $id;
		}
		
		function gravarDadoscominvoice($params){
			$bo		  = new KangvendasModel();
			$boi	  = new KanginvoiceModel();
			$usuario  = Zend_Auth::getInstance()->getIdentity();
			$bol	  = new KanginvoicelinksModel();			
			
			$bocli    = new ClientesModel();
			$boc      = new ClientesconsigneeModel();
			
			$bocidade = new CidadesModel();
			$boestado = new EstadosModel();
			$bopais   = new PaisesModel();
			
			
			$invoice = $boi->fetchRow("id = '".$params['invoice']."'");
			
			$objConsignee = $boc->fetchRow('id_cliente = "'.$invoice->id_cliente.'"');
			
			if($objConsignee->id_cidade != null){
			    $cidadeconsignee = $bocidade->fetchRow("id = '".$objConsignee->id_cidade."'");
			    $estadoconsignee = $boestado->fetchRow("id = '".$cidadeconsignee->id_estados."'");
			    $paisconsignee   = $bopais->fetchRow("id = '".$estadoconsignee->id_paises."'");
			    
			    $data['cidade']          = $cidadeconsignee->nome." - ".$estadoconsignee->nome." - ".$paisconsignee->nome;
			}
			
			$data['empresa']         = $objConsignee->empresa;
			$data['cnpj']            = $objConsignee->cnpj;
			$data['ie']              = $objConsignee->ie;
			$data['logradouro']      = $objConsignee->logradouro;
			$data['bairro']          = $objConsignee->bairro;
			$data['cep']             = $objConsignee->cep;
			$data['fone']            = $objConsignee->fone;
			
			if($params['embarcar'] == 1){			
				$data['dt_embarque']		= substr($params['dt_embarque'],6,4).'-'.substr($params['dt_embarque'],3,2).'-'.substr($params['dt_embarque'],0,2);
				$data['status']				= "EMBARCADO";
				$data['sit']				= 4;
				
				$dataliks = array('sit' => false);
				$bol->update($dataliks, "id_cominvoice = ".$params['invoice']);
				
				LogBO::cadastraLog("Kang/Embarque Venda",4,$usuario->ID,$params['invoice'],"S".substr("000000".$params['invoice'], -6,6));
				
			}else{
				$data['you_order']			= $params['you_order'];
				$data['defrom']				= $params['defrom'];
				$data['para']				= $params['para'];
				$data['freight']			= $params['freight'];
				$data['payment']			= $params['payment'];
				$data['partial_shipment']	= $params['partial_shipment'];
				$data['shipment_agent']		= $params['shipment_agent'];
				$data['dt_previsao']		= substr($params['dt_previsao'],6,4)."-".substr($params['dt_previsao'],3,2)."-".substr($params['dt_previsao'],0,2);
				$data['sit']				= 2;
				$data['freight_charge']     = str_replace(",", ".", str_replace(".", "", $params['frete']));
                $data['sum_freight']		= $params['sum_freight'] == 'on' ? true : false;


				
				LogBO::cadastraLog("Kang/Dados Venda",4,$usuario->ID,$params['invoice'],"S".substr("000000".$params['invoice'], -6,6));
			}

			
			$boi->update($data, "id = '".$params['invoice']."'");			
			
		}
		
		function gravarDadosretornoinvoice($params){
			$bo		= new KangvendasModel();
			$boi	= new KanginvoiceprodModel();
			$usuario = Zend_Auth::getInstance()->getIdentity();
			
			
			Zend_Debug::dump($params);
			
			foreach (KangvendasBO::buscaProdutosinvoicecomprahscode($params['ped']) as $lista):
				if($params['dr_'.str_replace(".", "", $lista->hscode)."_".$lista->id_kang_compra] != ""):
					$data = array(
						'dt_retorno'		=> substr($params['dr_'.str_replace(".", "", $lista->hscode)."_".$lista->id_kang_compra],6,4).'-'.substr($params['dr_'.str_replace(".", "", $lista->hscode)."_".$lista->id_kang_compra],3,2).'-'.substr($params['dr_'.str_replace(".", "", $lista->hscode)."_".$lista->id_kang_compra],0,2),
					    'invoiceretorno'	=> $params['ret_'.str_replace(".", "", $lista->hscode)."_".$lista->id_kang_compra]
					);
					
					$boi->update($data, "id = ".$lista->id);
				endif;
			endforeach;
				
				
		}
		
		
		/*-- Lista produtos das invoice --------------------------------------
		 * Usado em vendalistapacotesAction ------------
		 * */
				
		/*function listaProdutosentregues($var){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();			
			$select->from(array('v'=>'tb_kang_cominvoiceprod','*'), array('*'))
					->join(array('pd'=>'produtos'),'pd.ID = v.id_prod')
					->where('v.qt > v.pack and md5(v.id_cominvoice) = "'.$var['ped'].'"')
					->order('pd.codigo_mask');
					
			$stmt = $db->query($select);
			return $stmt->fetchAll();								
		}*/
		
		/*-- Lista fornecedores de produtos entregues--------------------------------------
		 * Usado em listapacotesAction ------------
		 * */
				
		/*function listaFornecedoreprodentregues(){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('v'=>'tb_kang_cominvoiceprod','*'), array('c.ID as idfor','c.EMPRESA as cliente'))
					->join(array('p'=>'tb_kang_compra'),'p.id_kang_compra = v.id_ped')
					->join(array('c'=>'clientes'),'c.ID = p.id_for')
					->joinLeft(array('ch'=>'tb_clientechina'),'ch.id_cliente = c.ID')
					->where('(v.sit = 1 || v.sit = 2) and p.sit != 0  and v.qt > v.qtpack')
					->group('p.id_for')
					->order('c.EMPRESA');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();								
		}*/
		
		
		/*---- Nota mental -----------------------------
		 * 
		 * 
		 * 
		 * */
		function copiaPacklist($var){
            try{
    			$bop	= new KangvendasModel();
    			$bo		= new PacklistModel();
    			$bopp	= new PacklistprodModel();
    			$boc	= new KanginvoiceprodModel();
    			
    			foreach ($bo->fetchAll("id = ".$var['idpack']) as $pack);
    			
    			//--- busca qt pallets -------------
    			
    			$ordempl = count($bo->fetchAll("sit = 1 and id_cominvoice = '".$pack->id_cominvoice."'"));
    			
    			//foreach ($bo->fetchAll("id_cominvoice = ".$pack->id_cominvoice, "ordem") as $ordem);
    						
    			/* if($ordem->ordem > 0):
    				$ordempl = $ordem->ordem+1;
    			else:
    				$ordempl = 1;
    			endif; */
    			
    			for ($i = 0; $i < $var['replica']; $i++){
    			    $ordempl++;
    			    
        			$array = array(
        				'sit' 			=> 1,
        				'peso_bruto' 	=> $pack->peso_bruto,
        				'peso_liquido'	=> $pack->peso_liquido,
        				'altura' 		=> $pack->altura,
        				'largura' 		=> $pack->largura,
        				'comprimento'	=> $pack->comprimento,
        				'id_cominvoice'	=> $pack->id_cominvoice,
        				'id_for'		=> $pack->id_for,
        				'ordem'			=> $ordempl
        			);
        		
        			$id = $bo->insert($array);
    		      
        			foreach ($bopp->fetchAll("id_pack = '".$var['idpack']."'") as $packprod){
            			$arrayprod['id_prod']			= $packprod->id_prod;
            			$arrayprod['qt']				= $packprod->qt;
            			$arrayprod['peso_bruto']		= $packprod->peso_bruto;
            			$arrayprod['peso_liquido']		= $packprod->peso_liquido;
            			$arrayprod['pkgs']				= $packprod->pkgs;
            			$arrayprod['id_pack']			= $id;
            			$arrayprod['cod_prodcli']		= $packprod->cod_prodcli;
            		
            			$bopp->insert($arrayprod);
            		}	
    			}
    			
    			foreach ($bopp->fetchAll("id_pack = '".$var['idpack']."'") as $packprod){
                    $comprod = $boc->fetchRow("id_cominvoice = '".$pack->id_cominvoice."' and id_prod = ".$packprod->id_prod);
                    $qtPack = $comprod->pack + ($packprod->qt * $var['replica']);
                    $boc->update(array('pack' => $qtPack), 'id = "'.$comprod->id.'"');
                }
                
                return true;
                
			}catch (Zend_Exception $e){
				$boerro	= new ErrosModel();
				$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "KangvendasBO::copiaPacklist()");
				$boerro->insert($dataerro);
				return false;
			}
			
			/* $qtcad	 = $packprod->qt;
			
			foreach ($boc->fetchAll("md5(id_cominvoice) = '".$var['ped']."' and id_prod = ".$packprod->id_prod) as $comprod){
				if($comprod->qt>=($comprod->pack+$qtcad)){
					$arraycom['pack'] = $comprod->pack+$qtcad;
					$qtcad = 0;
					$boc->update($arraycom,"id = ".$comprod->id);
				}else{
					$qtcad -= ($comprod->qt - $comprod->pack);
					$arraycom['pack'] = $comprod->qt;
					$boc->update($arraycom,"id = ".$comprod->id);
				}
			} */
			
		}
		
		function gerarPackinglist($params){
			$bo		= new KangvendasModel();
			$bop 	= new PacklistModel();
			$bopr	= new PacklistprodModel();
			$boe	= new KanginvoiceprodModel();
			
			$bov	= new KangvendasModel();
			$bo 	= new PacklistModel();
			 
			$pack = $bo->fetchRow("sit = 1 and id_cominvoice = '".$params['ped']."'", 'ordemfinal desc');
			if(count($pack)>0){
			    $ordemini = $pack->ordemfinal+1;
			}else{
			    $ordemini = 1;
			}
			
			$ordemfinal = $ordemini + $params['qtpacotes'] - 1;
			
			/* foreach ($bop->fetchAll("id_cominvoice = ".$params['ped'], "ordem") as $ordem);
			
			if($ordem->ordem > 0):
				$ordempl = $ordem->ordem+1;
			else:
				$ordempl = 1;
			endif; */
			
			$datap = array(
				'sit' 			=> 1,
				'peso_bruto' 	=> str_replace(",", ".", str_replace(".", "",$params['gw'])),
				'peso_liquido'	=> str_replace(",", ".", str_replace(".", "",$params['nw'])),
				'altura' 		=> str_replace(",", ".", str_replace(".", "",$params['altura'])),
				'largura' 		=> str_replace(",", ".", str_replace(".", "",$params['largura'])),
				'comprimento'	=> str_replace(",", ".", str_replace(".", "",$params['comprimento'])),
				'id_cominvoice'	=> $params['ped'],
				'id_for'		=> $params['idfor'],
		        'ordeminicial'	=> $ordemini,
		        'ordemfinal'	=> $ordemfinal,
				//'ordem'			=> $ordempl,
			);						
			
			$idpack = $bop->insert($datap);			
			
			//--- Listo os produtos  -------------------
			
			foreach(KangvendasBO::listaCominvoiceprod(md5($params['ped'])) as $lista):
			
    	 		//--- verifico se campos foram preenchidos no formulario -----------------------------------------
    	 		if(($params['qt_'.$lista->id_prod]!="") and ($params['pc_'.$lista->id_prod]!="")):
    	 			$data = array(
    	 				'qt' 			=> $params['qt_'.$lista->id_prod],
			            'peso_bruto' 	=> ($params['gw_'.$lista->id_prod] != "") ? str_replace(",", ".", $params['gw_'.$lista->id_prod]) : 0,
    	 				'peso_liquido' 	=> ($params['nw_'.$lista->id_prod] != "") ? str_replace(",", ".", $params['nw_'.$lista->id_prod]) : 0,
    	 				'pkgs' 			=> $params['pc_'.$lista->id_prod],
	    	 			'id_pack' 		=> $idpack,
	    	 			'id_prod' 		=> $lista->id_prod,
    	 				'cod_prodcli'	=> $lista->cod_prodcli
			        );
			        
			        $idprod = $bopr->insert($data);
			        
			        $qttotal = 0;
			        $qttotal = $params['qt_'.$lista->id_prod] * $params['qtpacotes'];
			       			        
			        foreach (KangvendasBO::buscaProdutosinvoicecompra(md5($params['ped'])) as $entrega):			        	
			        	if(($qttotal > 0) and ($entrega->id_prod==$lista->id_prod)):
			        		if($entrega->qtdisp > $qttotal):
				        		$qtbaixa 	= $entrega->pack+$qttotal;
			        			$qttotal	= 0;
				        	else:
				        		$qtbaixa = $entrega->pack+$entrega->qtdisp;
				        		$qttotal -= $entrega->qtdisp;				        		
				        	endif;					        	
				        	
				        	$datae = array(
					        	'pack' => $qtbaixa,
					        );
				        	
					        $boe->update($datae, "id = ".$entrega->idcomprod);
					        
					    endif;							        
			        endforeach;					        
    	 		endif;
			endforeach;
		}
		
		//--- Cancela pack list gerados, e libera produtos para posterior marcação --------------------
		
		function cancelarPackinglist($params){
			$bo		= new KangvendasModel();
			$bop 	= new PacklistModel();
			$bopr	= new PacklistprodModel();
			$boe	= new KanginvoiceprodModel();
			$qtbaixa = 0;
			
			$pack = $bop->fetchRow("md5(id) = '".$params['pack']."'");
			$multi = $pack->ordemfinal - $pack->ordeminicial + 1;
			
			foreach ($bopr->fetchAll("md5(id_pack) = '".$params['pack']."'") as $prodpack){	
				$qttotal = $prodpack->qt*$multi;		
				foreach (KangvendasBO::buscaProdutosinvoicecompra($params['ped']) as $entrega){			        	
		        	if(($prodpack->id_prod==$entrega->id_prod) and ($qttotal>0)){
						if($qttotal > $entrega->pack){
		        			$qttotal -= $entrega->pack;
		        			$qtbaixa = 0;
						}else{
		        			$qtbaixa = $entrega->pack-$qttotal;
		        			$qttotal = 0;
						}
			        	
				        $boe->update(array('pack' => $qtbaixa), "id = ".$entrega->idcomprod);				        
		        	}							        
				}		
			}
			
			$bop->update(array('sit' => 0), "md5(id) = '".$params['pack']."'");
			
			//-- atualiza ordem --
				
			$array = $params['recordsArray'];
				
			$cont = $posini = $posfim = 0;
			foreach ($bop->fetchAll("sit = 1 and md5(id_cominvoice) = '".$params['ped']."'") as $packs){
			    $cont++;
			     
			    $posini = $posfim+1; //-- posicao inicial
			
			    echo $posfim = $posini + ($packs->ordemfinal - $packs->ordeminicial); //-- posicao final
			    echo "\n";
			
			    $bop->update(
		            array(
	                    'ordem'         => $cont,
	                    'ordeminicial'  => $posini,
	                    'ordemfinal'    => $posfim,
		            ), "id = '".$packs->id."'");
			     
			}
		}
	
		/**
		 * 
		 * @param unknown $params
		 */
		function cancelarTodospackinglist($params){
		    $bo		= new KangvendasModel();
		    $bop 	= new PacklistModel();
		    $boe	= new KanginvoiceprodModel();

		    //-- remove a marcacao de quantidadde nos packs
		    $boe->update(array('pack' => 0), 'id_cominvoice = "'.$params['ped'].'"');
		    $bop->update(array('sit' => 0), 'id_cominvoice = "'.$params['ped'].'"');
		}
		
		function verificaAcessopallet($ped,$for){
			$bo		= new KangvendasModel();
			$bol	= new KanginvoicelinksModel();
			
			return $bol->fetchAll("sit = 1 and md5(id_cominvoice) = '".$ped."' and md5(idfor) = '".$for."'");			
		}
		
		function listaLinkspallet(){
			$bo		= new KangvendasModel();
			$bol	= new KanginvoicelinksModel();
		
			return $bol->fetchAll();
		}
		
		function atualizaOrdempallet($params){
			$bo		= new KangvendasModel();
			$bop 	= new PacklistModel();
			
			$array = $params['recordsArray'];
			
			$cont = $posini = $posfim = 0;			
			foreach ($array as $row => $value){
			    $cont++;
			    
			    $posini = $posfim+1; //-- posicao inicial
			    			    
			    $pack = $bop->fetchRow("id = '".$value."'");

			    echo $posfim = $posini + ($pack->ordemfinal - $pack->ordeminicial); //-- posicao final
			    echo "\n";
        	    			    
			    $bop->update(
                    array(
                        'ordem'         => $cont,
                        'ordeminicial'  => $posini,
                        'ordemfinal'    => $posfim,                            
			        ), "id = '".$value."'");
			    
			}
						
		}
		
		
	}
?>
