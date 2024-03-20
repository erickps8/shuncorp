<?php
	class TaicomprasBO{		
				
		function listaProdutosvendas($cat){
								
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('p'=>'produtos','*'), array('p.ID as id_prod','p.CODIGO','p.custo_shuntai','c.EMPRESA','p.DESCRICAO','h.ncm as hscode','h.retorno','m.descricaochines'))
			        ->join(array('c'=>'clientes'),'p.id_cliente_shuntai = c.ID')
			        ->joinLeft(array('h'=>'tb_produtoshscode'),'h.id = p.id_hscode')
			        ->joinLeft(array('m'=>'tb_produtosmaterial'),'m.id = p.id_produtosmaterial')
			        ->where("c.ID = ".$cat['fornecedor'])
			        ->order('p.codigo_mask','');
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
			
			//->join(array('g'=>'grupos'),'p.ID_GRUPO = g.ID')
		}
		
		function listaPedidos($var=array()){
					
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
		    	
		    $where .= (!empty($var['buscaid'])) ? " and p.id_tai_compra = '".ereg_replace("[^0-9]", " ", $var['buscaid'])."'" : "";
		    $where .= (isset($var['buscacli']) and $var['buscacli'] != 0) ? " and p.id_for = '".$var['buscacli']."'" : "";
		    
		    $where .= (isset($var['buscasit']) and $var['buscasit'] == 1) ? " and p.STATUS = 'Received'" : ""; //PENDING
		    $where .= (isset($var['buscasit']) and $var['buscasit'] == 2) ? " and p.STATUS = 'Ordered'" : "";  //GENERATE
		    $where .= (isset($var['buscasit']) and $var['buscasit'] == 3) ? " and p.STATUS = 'Finished'" : ""; //FINALIZED
		    $where .= (isset($var['buscasit']) and $var['buscasit'] == 4) ? " and p.STATUS = 'Canceled'" : ""; //CANCELED
		    
		    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		    $db->setFetchMode(Zend_Db::FETCH_OBJ);
		    	
		    $select = $db->select();
		    
		    $select->from(array('p'=>'tb_tai_compra','*'),
	    		array('p.id_tai_compra','p.status','DATE_FORMAT(p.data, "%d/%m/%Y") as dtcompra','c.EMPRESA','p.track_code','c.ID as idcliente','p.fin'))
	    		->join(array('c'=>'clientes'),'p.id_for = c.ID')
	    		->where("p.sit = true".$where)
	    		->order('p.id_tai_compra desc','');
		    	
		    $stmt = $db->query($select);
		    $objCompras = $stmt->fetchAll();
		    
		    if(!empty($var['codigo']) and count($objCompras)>0){
		    		
		    	$idvend = "";
		    	foreach ($objCompras as $compras){
		    		$idvend .= $compras->id_tai_compra.",";
		    	}
		    		
		    	$whereprod = "(p.CODIGO = '".$var['codigo']."') and v.id_tai_compra in (".substr($idvend, 0,-1).")";
		    		
		    	$select = $db->select();
		    	$select->from(array('pv'=>'tb_tai_comprasprod','*'), array('pv.*','p.CODIGO', 'DATE_FORMAT(v.data,"%d/%m/%Y %H:%i" ) as dtcompra','c.EMPRESA','v.fin','v.sit as sitped'))
		    	->join(array('p'=>'produtos'),'p.ID = pv.id_prod')
		    	->join(array('v'=>'tb_tai_compra'),'pv.id_tai_compra = v.id_tai_compra')
		    	->join(array('c'=>'clientes'),'v.id_for = c.ID')
		    	->where($whereprod)
		    	->order('v.id_tai_compra desc');
		    		
		    	$stmt = $db->query($select);
		    		
		    	return array('objeto' => $stmt->fetchAll(), 'tipo' => 2);
		    }else{
		    	return array('objeto' => $objCompras, 'tipo' => 1);
		    }
		    
		    
		}
		
		//-- Busca compra por ID, com fornecedor --------------------------
		function buscaCompras($id){
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_tai_compra','*'), array('*','DATE_FORMAT(t.data, "%d/%m/%Y") as datacompra'))
			        ->join(array('c'=>'clientes'),'t.id_for = c.ID')
			        ->where("md5(t.id_tai_compra) = '".$id."'");
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();
			
		}
		
		//--Finaliza financeiro--------------------------------
		function finalizaFinance($params){
			$tai 		= new TaicomprasModel();
			$array['fin']			= 1;
			$tai->update($array,'id_tai_compra = "'.$params['fin'].'"');
			
		}
		
		//--Grava pedido-------
		function gravaPedido($params){
			$tai 		= new TaicomprasModel();
			$taiprod 	= new TaicomprasprodModel(); 
			$usuario	= Zend_Auth::getInstance()->getIdentity();
			
			$array['id_for']		= $params['fornecedor'];
			$array['status']		= "Received";
			$array['data']			= date("Y-m-d H:i:s");
			$array['sit']			= true;
			
			$busca['idparceiro']		= $params['fornecedor'];
			$objCli = ClientesBO::buscaParceiros("",$busca);
			
			foreach ($objCli as $list);
			
			$array['track_code']	= $list->track_code;
			$idcli = $tai->insert($array);
			
			foreach(TaicomprasBO::listaProdutosvendas($params) as $listprod):
				if(!empty($params[$listprod->id_prod])):
					$arrayprod = "";
					$arrayprod = array();
				
					$arrayprod['id_tai_compra']		= $idcli;
					$arrayprod['id_prod']			= $listprod->id_prod;
					$arrayprod['qt']				= $params[$listprod->id_prod];
					$arrayprod['preco']				= $listprod->custo_shuntai;
					
					$arrayprod['hs_code']			= $listprod->hscode;
					$arrayprod['retorno']			= $listprod->retorno;
					$arrayprod['material']			= $listprod->descricaochines;
						
					$arraybysca = array('idprod' => $listprod->id_prod, 'forn' => $params['fornecedor']);
					$this->objCodigocross = ProdutosBO::listaFornecedoresprodcross($arraybysca);
												
					if(count($this->objCodigocross)>0):
					foreach ($this->objCodigocross as $codigocross);
						$arrayprod['codfor']		= $codigocross->codigocross;
					else:
						$arrayprod['codfor']		= "";
					endif;
					
					$taiprod->insert($arrayprod);
				endif;
			endforeach;
			
			LogBO::cadastraLog("Shuntai Compras/Pedido",2,$usuario->id,$idcli,'PT'.substr("000000".$idcli,-6,6));

			return $idcli;
			
		}
		
		//--Grava Prazo de entrega e escrita-------
		function geraPedido($params){
			$tai 		= new TaicomprasModel();
			$taiprod 	= new TaicomprasprodModel(); 
			$usuario	= Zend_Auth::getInstance()->getIdentity();
			
			$array['status']		= "Ordered";
			$tai->update($array,"id_tai_compra = ".$params['pedido']);
			
			foreach(TaicomprasBO::listaProdutoscompra($params['pedido']) as $listprod):
				$arrayprod['gravacao']			= $params['grava_'.$listprod->id_prod];
				$arrayprod['prazo']				= substr($params['prazo_'.$listprod->id_prod],6,4).'-'.substr($params['prazo_'.$listprod->id_prod],3,2).'-'.substr($params['prazo_'.$listprod->id_prod],0,2);
				
				$taiprod->update($arrayprod,"id = ".$listprod->idprodped);
				
			endforeach;
			
			LogBO::cadastraLog("Shuntai Compras/Gerar Pedido",4,$usuario->id,$params['pedido'],'PT'.substr("000000".$params['pedido'],-6,6));
		}
		
		//--Fecha pedido-------
		function fecharPedido($params){
			$tai 		= new TaicomprasModel();
			$usuario	= Zend_Auth::getInstance()->getIdentity();
			
			$array['status']		= "Finished";
			$tai->update($array,"id_tai_compra = ".$params['pedido']);
			
			LogBO::cadastraLog("Shuntai Compras/Fechar Pedido",4,$usuario->id,$params['pedido'],'PT'.substr("000000".$params['pedido'],-6,6));
		}
		
		//--Fecha pedido-------
		function removerPedido($params){
			$tai 		= new TaicomprasModel();
			$usuario	= Zend_Auth::getInstance()->getIdentity();
			
			$array['status']		= "Canceled";
			$array['fin']			= 1;
			$tai->update($array,"md5(id_tai_compra) = '".$params['rem']."'");
			
			LogBO::cadastraLog("Shuntai Compras/Fechar Pedido",3,$usuario->id,$params,'PT'.substr("000000".$params,-6,6));
		}
		
		//--Lista produtos pedidos------------------
		function listaProdutoscompra($idfor){
								
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$db->query("SET NAMES UTF8");
			$select = $db->select();
						
			$select->from(array('p'=>'produtos','*'),
			        array('t.id as idprodped','p.ID as id_prod','p.CODIGO','t.qt','t.preco','t.gravacao','DATE_FORMAT(t.prazo, "%d/%m/%Y") as data'))
			        ->join(array('t'=>'tb_tai_comprasprod'),'t.id_prod = p.ID')
			        ->where("md5(t.id_tai_compra) = '".$idfor."'")
			        ->order('p.codigo_mask','');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
								
		}
		
		//--Grava entrega -------
		function gravaEntrega($params){
			$tai 		= new TaicomprasModel();
			$taiprod 	= new EntregaprodModel(); 
			$usuario	= Zend_Auth::getInstance()->getIdentity();
			
			foreach(TaicomprasBO::listaProdutoscompra(md5($params['pedido'])) as $listprod):
				if(!empty($params[$listprod->id_prod])){
    			    $arrayprod = array(
    			        'id_ped'    => $params['pedido'],
    			    	'id_prod'   => $listprod->id_prod,
    			    	'qt'        => $params[$listprod->id_prod],
    			        'dt_ent'    => date("Y-m-d")
    			    );
    			    $taiprod->insert($arrayprod);
			    }
				
			endforeach;
			
			LogBO::cadastraLog("Shuntai Compras/Receber produtos",4,$usuario->id,$params['pedido'],'PT'.substr("000000".$params['pedido'],-6,6));
		}
		
		//--Remove entrega-------
		function removeEntrega($params){
			$tai 		= new TaicomprasModel();
			$taiprod 	= new EntregaprodModel(); 
			$usuario	= Zend_Auth::getInstance()->getIdentity();
			
			$taiprod->update(array('sit' => false), "id = '".$params['rem']."'");
			
			LogBO::cadastraLog("Shuntai Compras/Remover entrega",4,$usuario->id,$params['rem'],'PT'.substr("000000".$params['rem'],-6,6));
		}
		
		//--Lista produtos entregues------------------
		function listaProdutosentregue($idfor){
								
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_taiprodent','*'),
			        array('p.ID as id_prod','p.CODIGO','t.qt as quant','t.id as id_ent','DATE_FORMAT(t.dt_ent, "%d/%m/%Y") as data','c.preco'))
			        ->join(array('p'=>'produtos'),'t.id_prod = p.ID')
			        ->join(array('c'=>'tb_tai_comprasprod'),'c.id_tai_compra = t.id_ped')
			        ->where("md5(t.id_ped) = '".$idfor."' and t.sit = true and c.id_prod = t.id_prod")
			        ->order('t.dt_ent')
					->order('p.codigo_mask','t.id');					
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
								
		}
		
		//--Listar obs de compras--------------------------
		function listaObs(){
			$obj = new KangcomprasobsModel();
			return $obj->fetchAll('sit = true');			
		}
		
		//--Grava Obs -------
		function gravaObs($params){
			$tai 		= new TaicomprasobsModel();
			$tai->delete("id_ped = ".$params['compra']);
			
			try{
				$obj = new TaicomprasobsModel();
				$usuario 	= Zend_Auth::getInstance()->getIdentity();
				 
				$obj->delete("id_ped = ".$params['compra']);
				 
				$arrRegras = explode(";", $params['idregras']);
				 
				foreach ($arrRegras as $regras => $vlregra){
					if(!empty($vlregra)) $obj->insert(array('id_ped' => $params['compra'], 'id_obs' => $vlregra));
				}
				 
				LogBO::cadastraLog("Tai Compras/Regras de compra",4,$usuario->id,$params['compra'],'PT'.substr("000000".$params['compra'],-6,6));
				return true;
				 
			}catch (Zend_Exception $e){
				$boerro	= new ErrosModel();
				$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "KangcomprasBO::gravaObs()");
				$boerro->insert($dataerro);
				return false;
			}
			
		}
		
		//--Listar obs de compras gravadas--------------------------
		function listaObsgravados($params){
			/* $obj = new TaicomprasobsModel();
			return $obj->fetchAll('md5(id_ped) = "'.$params.'"'); */

			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
			$select = $db->select();
			
			$select->from(array('t'=>'tb_tai_obsped','*'), array('*'))
			->join(array('p'=>'tb_kang_pedobs'),'t.id_obs = p.id')
			->where("md5(t.id_ped) = '".$params."'");
			 
			$stmt = $db->query($select);
			return $stmt->fetchAll();
			
		}
		
		//--Grava Obs -------
		function gerarPurchasepreordem($allparams){
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('p'=>'produtos','*'),
			        array('sum(t.qt_pedido) as quant','p.CODIGO','p.ID as id_produto','p.custo_shuntai'))
			        ->join(array('t'=>'tb_preordem_prod_kit'),'p.CODIGO = t.codigo')
			        ->where(" t.id_preordem = ".$allparams['pre']." and p.id_cliente_shuntai = ".$allparams['cli'])
			        ->group("t.codigo");
			  
			$stmt = $db->query($select);
			
			//$stmt->fetchAll();
						
			$tai 		= new TaicomprasModel();
			$taiprod 	= new TaicomprasprodModel(); 
			$array['id_for']		= $allparams['cli'];
			$array['status']		= "Received";
			$array['data']			= date("Y-m-d H:i:s");
			$array['sit']			= true;
			
			$busca['idparceiro']		= $allparams['cli'];
			$objCli = ClientesBO::buscaParceiros("",$busca);
			foreach ($objCli as $list);
			
			$array['track_code']	= $list->track_code;
			$idcli = $tai->insert($array);
			
			foreach($stmt->fetchAll() as $listprod):
				$arrayprod['id_tai_compra']		= $idcli;
				$arrayprod['id_prod']			= $listprod->id_produto;
				$arrayprod['qt']				= $listprod->quant;
				$arrayprod['preco']				= $listprod->custo_shuntai;
				$taiprod->insert($arrayprod);

			endforeach;
			
			LogBO::cadastraLog("Shuntai Compras/Novo Pedido",2,$_SESSION['S_ID'],$idcli,'Pedido N° PT'.substr("000000".$idcli,-6,6));
			
			
		}
		
		//---Financeiro----------------------------------------
		/* Lista todos as compras shukang com financeiro em aberto
		 * Usado em Administracao/buscacontasfinpedAction 
		 */
		function listaComprasabertas($var){
			if(!empty($var['forn']) and $var['forn']!= 0):
				$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
				$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
				$select = $db->select();
				        
				$select->from(array('t'=>'tb_tai_compra'), array('t.id_tai_compra as idcompra','sum(f.valor) as valorpago','(select sum(p.qt*p.preco) from tb_tai_comprasprod p where p.id_tai_compra = t.id_tai_compra) as totalped'))
				        ->joinLeft(array('f'=>'tb_finpurchase'),'f.id_tai_compra = t.id_tai_compra')
				        ->where("t.fin = 0 and t.sit = 1 and id_for = '".$var['forn']."'")
				        ->group('t.id_tai_compra')
				        ->order('t.id_tai_compra asc','');
				  
				$stmt = $db->query($select);
				return $stmt->fetchAll();	
			endif;
		}		
		
		//--Listar produtos pedidos por busca----------------------
		function listaProdutospedidos($val){
			
			if($val['cat'] == "customer") $order = "c.EMPRESA asc";
			elseif($val['cat'] == "code") $order = "pr.codigo_mask asc";
			elseif($val['cat'] == "order") $order = "v.id_tai_compra desc";
			else $order = "v.id_tai_compra desc";
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('p'=>'tb_tai_comprasprod','*'),
			        array('p.id_tai_compra','p.qt','pr.CODIGO','c.EMPRESA'))
			        ->join(array('pr'=>'produtos'),'p.id_prod = pr.ID')
			        ->join(array('v'=>'tb_tai_compra'),'p.id_tai_compra = v.id_tai_compra')
			        ->join(array('c'=>'clientes'),'v.id_for = c.ID')
			        ->where("v.sit = true and v.STATUS != 'Finished' and pr.CODIGO like '%".$val[busca]."%'")
			        ->order($order,'');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		
		//----Pre Ordem-------------------------------------------------
		function listaPreordem($val){
			
			$sessaobusca = new Zend_Session_Namespace('Preordem');
		    if(isset($sessaobusca->where)):
		   		$where = $sessaobusca->where;
		   	endif;
			
			$dataini = substr($val['dataini'],6,4).'-'.substr($val['dataini'],3,2).'-'.substr($val['dataini'],0,2);;
			$datafin = substr($val['datafin'],6,4).'-'.substr($val['datafin'],3,2).'-'.substr($val['datafin'],0,2);;;
			
			if(!empty($val['buscaid'])):
				$where = " and p.id = ".substr($val['buscaid'],2);
			elseif((!empty($val['dataini'])) and (!empty($val['datafin']))):
				$where = " and p.data_cadastro between '".$dataini."' and '".$datafin."'";
			elseif((!empty($val['dataini'])) and (empty($val['datafin']))):
				$where = " and p.data_cadastro >= '".$dataini."'";
			elseif((empty($val['dataini'])) and (!empty($val['datafin']))):
				$where = " and p.data_cadastro <= '".$datafin."'";
			endif;
			
			if(!empty($where)):
		   		$sessaobusca->where = $where;
		   	endif;
		   	
		   	
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('p'=>'tb_preordem','*'), array('p.*','p.id as idpre','DATE_FORMAT(p.data_cadastro,"%d/%m/%Y") as data','c.EMPRESA'))
					->joinLeft(array('k'=>'tb_preordem_prod_kit'),'k.id_preordem = p.id and k.id_for is not NULL')
			        ->join(array('c'=>'clientes'),'p.id_user = c.ID')
			        ->where("p.id > 0 ".$where)
			        ->group('p.id')
			        ->order('p.id desc');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		function listaProdutospreordem($var){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			
			$select->from(array('p'=>'tb_preordem_prod'), array('p.*','pr.CODIGO','po.sit'))
			        ->join(array('pr'=>'produtos'),'p.id_produtos = pr.ID')
			        ->join(array('po'=>'tb_preordem'),'po.id = p.id_preordem')
			        ->where("md5(p.id_preordem) = '".$var['pordem']."'")
			        ->order('pr.CODIGO asc');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();	
		}
		
		function listaProdutoskitpreordem($var){
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			
			$select->from(array('p'=>'tb_preordem_prod_kit'), array('p.*','p.moeda as moedashu','pr.*','c.*','cl.EMPRESA as fornecedorcl'))
			        ->joinLeft(array('cl'=>'clientes'),'p.id_for = cl.ID')
					->join(array('pr'=>'produtos'),'p.id_produtos = pr.ID and id_gruposprodsub not in (14)')
			        ->joinLeft(array('c'=>'clientes'),'pr.id_cliente_shuntai = c.ID')
			        ->where("md5(p.id_preordem) = '".$var['pordem']."'")
			        ->order('pr.CODIGO asc');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();	
		}
		
		
		function listaProdutoskitmoudes($var){
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			
			$select->from(array('p'=>'tb_preordem_prod_kit'), array('p.*','p.moeda as moedashu','pr.*','c.*'))
			        ->join(array('pr'=>'produtos'),'p.id_produtos = pr.ID and id_gruposprodsub in (14)')
			        ->joinLeft(array('c'=>'clientes'),'pr.id_cliente_shuntai = c.ID')
			        ->where("md5(p.id_preordem) = '".$var['pordem']."'")
			        ->order('pr.CODIGO asc');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();	
		}
		
		function listaFornecedoreskitpreordem($var,$tipo){
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			
			$select->from(array('p'=>'tb_preordem_prod_kit'), array('p.*','p.moeda as moedashu','pr.*','c.*','cl.EMPRESA as fornecedorcl'))
					->joinLeft(array('cl'=>'clientes'),'p.id_for = cl.ID')
			        ->join(array('pr'=>'produtos'),'p.id_produtos = pr.ID  and id_gruposprodsub not in (14)')
			        ->joinLeft(array('c'=>'clientes'),'pr.id_cliente_shuntai = c.ID')
			        ->where($tipo." md5(p.id_preordem) = '".$var['pordem']."'")
			        ->group('c.ID')
			        ->group('p.id_tai_compra')
			        ->order('c.EMPRESA asc');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();	
		}
		
		function listaProdutoskitpreordemsum($var,$tipo){
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			
			$select->from(array('p'=>'tb_preordem_prod_kit'), array('p.*','p.id as idprodkit','p.moeda as moedashu','pr.*','c.*','sum(p.qt_pedido*p.qt) as qttotal'))
			        ->join(array('pr'=>'produtos'),'p.id_produtos = pr.ID  and id_gruposprodsub not in (14)')
			        ->joinLeft(array('c'=>'clientes'),'pr.id_cliente_shuntai = c.ID')
			        ->where($tipo." md5(p.id_preordem) = '".$var['pordem']."'")
			        ->group('p.id_produtos')
			        ->order('pr.CODIGO asc');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();	
		}
		
		//--Grava pedido de preordem-------
		function gravaPedidopreordem($params){
			$tai 		= new TaicomprasModel();
			$taiprod 	= new TaicomprasprodModel(); 
			$bop		= new PreordemModel();
			$bopk		= new PreordemprodkitModel();
			
			$array['id_for']		= $params['fornecedor'];
			$array['status']		= "Received";
			$array['data']			= date("Y-m-d H:i:s");
			$array['sit']			= true;
			
			$busca['idparceiro']		= $params['fornecedor'];
			$objCli = ClientesBO::buscaParceiros("",$busca);
			foreach ($objCli as $list);
			
			$array['track_code']	= $list->track_code;
			$idcli = $tai->insert($array);
			
			foreach(TaicomprasBO::listaProdutoskitpreordemsum($params) as $listprod):
				if(($listprod->id_cliente_shuntai==$params['fornecedor']) and ($listprod->id_for =="")):
					$arrayprod['id_tai_compra']		= $idcli;
					$arrayprod['id_prod']			= $listprod->id_produtos;
					$arrayprod['qt']				= $listprod->qttotal;
					$arrayprod['preco']				= $listprod->custo_shuntai;
					$taiprod->insert($arrayprod);
					
					$arraykit['id_for']				= $params['fornecedor'];
					$arraykit['moeda']				= $listprod->moeda_shuntai;
					$arraykit['valor_unit']			= $listprod->custo_shuntai;
					$arraykit['id_tai_compra']		= $idcli;
					$bopk->update($arraykit,"id_produtos = ".$listprod->id_produtos." and id_preordem = ".$listprod->id_preordem);
					
				endif;
			endforeach;
			
			LogBO::cadastraLog("Shuntai Compras/Novo Pedido",2,$_SESSION['S_ID'],$idcli,'Pedido N° PT'.substr("000000".$idcli,-6,6));
						
		}
		
		function gravaPreordem($params){
			$usuario	= Zend_Auth::getInstance()->getIdentity();
			$tai 		= new TaicomprasModel(); 
			$bo			= new PreordemModel();
			$bop		= new PreordemprodModel();
			
			$array['id_user']			= $usuario->ID;
			$array['data_cadastro']		= date("Y-m-d H:i:s");
			$array['sit']				= true;
			
			$id = $bo->insert($array);
			
			foreach(ProdutosBO::listaallProdutos() as $listprod):
				if(!empty($params[$listprod->ID])):
					$arrayprod['id_preordem']		= $id;
					$arrayprod['id_produtos']		= $listprod->ID;
					$arrayprod['qt']				= $params[$listprod->ID];
					$arrayprod['codigo']			= $listprod->CODIGO;
					$bop->insert($arrayprod);
					
				endif;
			endforeach;
		}
		
		function gerarKitpreordem($params){
			$usuario	= Zend_Auth::getInstance()->getIdentity();
			$tai 		= new TaicomprasModel(); 
			$bo			= new PreordemModel();
			$bop		= new PreordemprodModel();
			$bopk		= new PreordemprodkitModel();
			
			$bp			= new ProdutosModel();
			$bpk		= new KitsModel();
			
			foreach ($bop->fetchAll("md5(id_preordem) = '".$params['pordem']."'") as $produtos):
				$bopk->delete("id_prod = ".$produtos->id_produtos." and id_preordem = ".$produtos->id_preordem." and id_for is NULL");
			
				$prodkit = "";
				foreach ($bpk->fetchAll("id_prod = ".$produtos->id_produtos) as $prodkit):
					
					$preprodkit = "";
					foreach ($bopk->fetchAll("id_prod = ".$produtos->id_produtos." and id_produtos = ".$prodkit->id_prodkit." and id_preordem = ".$produtos->id_preordem) as $preprodkit);
										
					if(count($preprodkit->id_produtos)<1):
						
						$arraykit['qt']				= $prodkit->qt;
						$arraykit['id_prod']		= $produtos->id_produtos;
						$arraykit['id_preordem']	= $produtos->id_preordem;
						$arraykit['qt_pedido']		= $produtos->qt;
						$arraykit['id_produtos']	= $prodkit->id_prodkit;
						
						$bopk->insert($arraykit);
					endif;					
					
				endforeach;
			endforeach;
			
		}
		
		function removePreordem($var){
			$tai 		= new TaicomprasModel(); 
			$bop		= new PreordemModel();
			
			$array['sit']	= false;
			$bop->update($array, "md5(id) = '".$var['pordem']."'");			
		}
		
		
		//--- Regras dos pedidos ---------------------------------------
		function buscaRegrascomprasgrupos($params=array()){
			$obj = new TaicomprasobsModel();
			?>
		    <div class="styled-select" style="width: 170px">
    			<select name="contasval" id="contasval" style="width: 192px">
    				<option value="0">Selecione</option>
    				<?php foreach (KangcomprasBO::listaGruposobs() as $grupos): ?>
    				<option value="<?php echo $grupos->id?>"><?php echo $grupos->desc_ingles?></option>
    				<?php endforeach;?>
    			</select>
			</div>
			
			<div id="respostaRegras" style="overflow: auto; overflow-x: hidden;  width: 780px; height: 400px; border-bottom: 1px solid #d5d5d5; border-top: 1px solid #d5d5d5; margin: 10px 0px 10px 0px">
			
			</div>
			
			<div style="text-align: left;">
			  <?php 
			  $obs = "";
			  foreach ($obj->fetchAll("id_ped = '".$params['compra']."'") as $pedobs){
                $obs .= $pedobs->id_obs.";";
              }
			  ?>
			  <input type="hidden" name="idregras" id="idregras" value="<?php echo $obs?>">
			  <input type="hidden" name="compra" id="compra" value="<?php echo $params['compra']?>">
		      <button id="salvarRegras" class="greenBtn">Salvar</button>
			</div>
			
		    <?php
		}
				
		function buscaRegrascompras($params=array()){
            $obj = new TaicomprasobsModel();

            $obs = KangcomprasBO::listaObs($params);

            if (count($obs) > 0) {
                ?>
                <div class="widget" style="width: 770px; margin-top: 0px ">
    	 		<table style="width: 100%; " class="tableStatic" >
    	        	<thead>
    	            	<tr>
    	                	<td width="10%">Id</td>
    	                    <td >Descrição</td>
    	                    <td width="10%">Opções</td>
    	                </tr>
    	          	</thead>
    	      		<tbody>
    			
    				<?php 
    				$cont = 0;
    				foreach($obs as $lista):
    				    $cont++;
    					?>					
    					<tr >
    		                <td  style="text-align: center;" >
    		                   <?php echo $cont?>
    		                </td>
    		                <td  align="left" >
    		                	<?=$lista->desc_ing."<br>".$lista->desc_chines?>
    		                	<input type="hidden" name="ingles_<?php echo $lista->id?>" value="<?=$lista->desc_ing?>">
    		                	<input type="hidden" name="chines_<?php echo $lista->id?>" value="<?=$lista->desc_chines?>">
    		                </td>
    		                <td  style="text-align: center;" >
    		                    <?php 
    		                    $check = (count($obj->fetchRow("id_ped = '".$params['compra']."' and id_obs = ".$lista->id))>0) ? 'checked="checked"' : '';  
    		                    ?>
    		                    <input type="checkbox" value="<?php echo $lista->id?>" class="checkRegra" <?php echo $check?>>        		                
    				        </td>		                
    		            </tr>		            
    				<?php  endforeach; ?>
                	</tbody>        
    			</table>
    			
    			</div>
    	   <?php }
		}
		
	}
?>