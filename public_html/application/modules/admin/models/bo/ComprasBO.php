<?php
	class ComprasBO{		
		
		//----- Novos pedidos de compra ------------------------------------
		
		//--Lista pedidos-------------------------------------------------------------------------
		function listaPedidos($pesq){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();	

			if($pesq['tipo']==1):
				if(!empty($pesq['buscaid'])):
					$where = " and p.ID = ".$pesq['buscaid'];
				endif;
			elseif ($pesq['tipo']==2):
				if($pesq['buscacli']!=0):
					$where = " and p.id_for = ".$pesq['buscacli'];
				endif;
			elseif ($pesq['tipo']==3):
				if (($pesq['tipostatus']=='FINALIZADO')||($pesq['tipostatus']=='ENTREGUE')):
					$where = " and p.STATUS = '".$pesq['tipostatus']."'";
				elseif($pesq['tipostatus']=='ANDAMENTO'):
					$where = " and p.data_entrega > NOW() and p.STATUS != 'FINALIZADO' and p.STATUS != 'CANCELADO'";
				elseif($pesq['tipostatus']=='ATRASO'):
					$where = " and data_entrega <= NOW() and p.STATUS != 'FINALIZADO' and p.STATUS != 'CANCELADO'";
				elseif($pesq['tipostatus']=='ABERTO'):
					$where = " and p.STATUS != 'FINALIZADO' and p.STATUS != 'CANCELADO'";
				endif;
			endif;
			
			if((!empty($pesq['dtini'])) || (!empty($pesq[dtfim]))):
				if(!empty($pesq['dtini'])) $di	= substr($pesq['dtini'],6,4).'-'.substr($pesq['dtini'],3,2).'-'.substr($pesq['dtini'],0,2);
				if(!empty($pesq[dtfim])) $df	= substr($pesq[dtfim],6,4).'-'.substr($pesq[dtfim],3,2).'-'.substr($pesq[dtfim],0,2);
			
				if((!empty($di)) and (!empty($df))): 
					$where .= ' and data_entrega between "'.$di.'" and "'.$df.'"';
				elseif((!empty($di)) and (empty($df))): 
					$where .= ' and data_entrega >= "'.$di.'"';
				elseif((empty($di)) and (!empty($df))): 
					$where .= ' and data_entrega <= "'.$df.'"';
				endif;
			endif;
			
			$sessaobusca = new Zend_Session_Namespace('Compras');			    
			
			if(!empty($where)):
				$where = "p.sit = 1".$where;
				$sessaobusca->where = $where;
			else:
				if(isset($sessaobusca->where)):
			   		$where = $sessaobusca->where;
			   	else:
			   		$where = "p.sit = 1";
			   	endif;			   		
			endif;
			
			//--- limita a pesquisa ------------------------------------
			if(!empty($pesq['limite'])):
				$limite =  $pesq['limite'];
			else:
				$limite =  10000000000;
			endif;
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('p'=>'pedidos_compra'),array('*','p.ID as idped','c.EMPRESA','c.ID as idcli','e.id_entradaztl'))
					->join(array('c'=>'clientes'), 'c.ID = p.id_for')
					->join(array('pc'=>'produtos_pedidos_compra'),'pc.ID_PEDIDO_COMPRA = p.ID')
			        ->joinLeft(array('e'=>'tb_entradaztl_prod'),'e.id_prodped = pc.ID')
			        ->where($where)
			        ->order('p.ID desc')
			        ->group('p.ID')
					->limit($limite);	
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		function buscaPedidos($busca){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('p'=>'pedidos_compra','*'),
			        array('p.*','p.ID as idped','c.EMPRESA','c.ID as idcli','DATE_FORMAT(p.data_entrega,"%d/%m/%Y") as dtchegada','c.moeda'))
			        ->join(array('c'=>'clientes'),'c.ID = p.id_for')
			        ->where("md5(p.ID) = '".$busca."'");
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		function buscaProdutospedidos($pesq){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('c'=>'pedidos_compra'),
			        array('c.ID as idped','pc.ID as idp','pc.QUANTIDADE','pc.QT_ENT','p.CODIGO','p.ID as idprod','pc.PRECO_UNITARIO_USD as preco','pc.moeda as moedap','c.*','DATE_FORMAT(c.data_entrega,"%d/%m/%Y") as chegada','n.ncm','n.ncmex'))
					->join(array('pc'=>'produtos_pedidos_compra'),'pc.ID_PEDIDO_COMPRA = c.ID')
			        ->join(array('p'=>'produtos'),'p.ID = pc.ID_PRODUTO')
			        ->joinLeft(array('n'=>'tb_produtosncm'),'p.id_ncm = n.id')
			        ->where("md5(c.ID) = '".$pesq."'")
			        ->order('p.codigo_mask','asc');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();				
		}
					
		function listaProdutospedidos($var){
			
			if(!empty($var['buscacod'])):
				$where = " and p.CODIGO like '%".$var['buscacod']."%'";
			endif;
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('c'=>'pedidos_compra','*'),
			        array('c.ID as ped','pc.QUANTIDADE','pc.QT_ENT','p.CODIGO','p.ID as ID_PROD','DATE_FORMAT(c.data_entrega,"%d/%m/%Y") as dt_entrega'))
					->join(array('pc'=>'produtos_pedidos_compra'),'pc.ID_PEDIDO_COMPRA = c.ID')
			        ->join(array('p'=>'produtos'),'p.ID = pc.ID_PRODUTO')
			        ->where("c.sit = 1 and c.STATUS != 'FINALIZADO' and pc.sit = 0 ".$where)
			        ->order('p.codigo_mask','asc');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();				
		}
		
		function gerarPedido($pesq){
			$bo 	= new ZtlcomprasModel();
			$bop	= new ZtlcomprasprodModel();
			$boprod = new ProdutosModel();
			
			$usuario = Zend_Auth::getInstance()->getIdentity();
			
			$for = explode(";",substr($pesq['fornecedores'],0,-1));
			for ($i=0;$i<sizeof($for);$i++):
				$arrayped['DATA_HORA']			= date("Y-m-d H:i:s");
				$arrayped['id_for']				= $for[$i];
				$arrayped['id_user']			= $usuario->id;
				$arrayped['OBSERVACOES']		= $pesq['obs_'.$for[$i]];		
				$arrayped['data_entrega']		= substr($pesq['data_entrega_'.$for[$i]],6,4).'-'.substr($pesq['data_entrega_'.$for[$i]],3,2).'-'.substr($pesq['data_entrega_'.$for[$i]],0,2); 
				$arrayped['sit']				= 1;
				$arrayped['partial_shipment']	= $pesq['acc_'.$for[$i]];
			
				$idped = $bo->insert($arrayped);
				
				foreach ($boprod->fetchAll("ID_CLIENTE_FORNECEDOR = ".$for[$i]) as $listprod):
					if(!empty($pesq['prod_'.$listprod->ID])):
						$arrayprod['ID_PEDIDO_COMPRA']	= $idped;
						$arrayprod['ID_PRODUTO']		= $listprod->ID;
						$arrayprod['QUANTIDADE']		= $pesq['prod_'.$listprod->ID];
						$arrayprod['PRECO_UNITARIO_USD']= $listprod->CUSTO_VALOR;
						$arrayprod['moeda']				= $listprod->MOEDA;
						$bop->insert($arrayprod);									
					endif;
				endforeach;
			
			endfor;
			
			return $idped;
		}
		
		function cancelarPedidocompra($id){
			$bo 	= new ZtlcomprasModel();
			$bop	= new ZtlcomprasprodModel();
			
			$array['STATUS'] = 'CANCELADO';
			$bo->update($array, "md5(id) = '".$id."'");
			
			$arrayprod['sit'] = 1;
			$bop->update($arrayprod, "md5(ID_PEDIDO_COMPRA) = '".$id."'");		
		}
		
		function buscaProdutosfornecedor($pesq){
			
		    
		    
		    try{
				$bo		= new ProdutosModel();
				
				$array['buscafor']		= $pesq['fornecedor'];
				$array['buscagrupo']	= $pesq['grupocompra'];
				$array['grupovenda']	= $pesq['grupovenda'];
				$array['buscagruposub']	= $pesq['subgrupo'];
				$array['periodo']		= 1;
				
				foreach (ProdutosBO::buscaProdrelatoriovenda($array) as $lista):
				
					if(!empty($pesq['prod_'.$lista->idproduto])):
						$idsp .= $lista->idproduto.",";
					endif;
				endforeach;
				
				return $bo->fetchAll("ID in (".substr($idsp,0,-1).")");
		     }catch (Zend_Exception $e){
		        $boerro	= new ErrosModel();
		        $dataerro = array('descricao' => $e->getMessage(), 'pagina' => "ComprasBO::buscaProdutosfornecedor()");
		        $boerro->insert($dataerro);
		    }
			
		}
		
		function buscafornecedorprodutos($pesq){
			
			$bo		= new ClientesModel();
			
			$array['buscafor']		= $pesq['fornecedor'];
			$array['buscagrupo']	= $pesq['grupocompra'];
			$array['grupovenda']	= $pesq['grupovenda'];
			$array['buscagruposub']	= $pesq['subgrupo'];
			$array['periodo']		= 1;
			
			foreach (ProdutosBO::buscaProdrelatoriovenda($array) as $lista):
				if(!empty($pesq['prod_'.$lista->idproduto])):
					$idsp .= $lista->ID_CLIENTE_FORNECEDOR.",";				
				endif;
			endforeach;
			
			return $bo->fetchAll("ID in (".substr($idsp,0,-1).")");
			
		}
		
		function listaExtratoproduto($val){
						
			$dataini = substr($val['dataini'],6,4).'-'.substr($val['dataini'],3,2).'-'.substr($val['dataini'],0,2);
			$datafin = substr($val['datafin'],6,4).'-'.substr($val['datafin'],3,2).'-'.substr($val['datafin'],0,2);
			
			$where = "";
			$limit = 500;
			if((!empty($val['dataini'])) and (!empty($val['datafin']))):
				$where = " and e.dt_atualizacao between '".$dataini."' and '".$datafin."'";
				$limit = 10000;
			elseif((!empty($val['dataini'])) and (empty($val['datafin']))):
				$where = " and e.dt_atualizacao >= '".$dataini."'";
				$limit = 10000;
			elseif((empty($val['dataini'])) and (!empty($val['datafin']))):
				$where = " and e.dt_atualizacao <= '".$datafin."'";
				$limit = 10000;
			else:
				//$where = " and e.dt_atualizacao between '".(date("Y"))."-".(date("m")-1)."-".date("d")."' and NOW()";
			endif;
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('e'=>'tb_estoqueztl','*'),
		        array('e.qt_atual','e.qt_atualizacao','e.id_atualizacao','DATE_FORMAT(e.dt_atualizacao,"%d/%m/%Y") as dt_at','e.tipo','p.CODIGO', 
		        't.nome','e.obs as obsest','c.EMPRESA as clienteped','cg.EMPRESA as clientegar'))
				->join(array('p'=>'produtos'),'p.ID = e.id_prod')
				->joinLeft(array('t'=>'tb_usuarios'),'t.id = e.id_user')
				->joinLeft(array('pd'=>'tb_pedidos'),'pd.id = e.id_atualizacao and e.TIPO like "VENDA%"')
				->joinLeft(array('c'=>'clientes'),'c.ID = pd.id_parceiro')
				->joinLeft(array('g'=>'tb_garantiaztl'),'g.id = e.id_atualizacao and e.TIPO like "GARANTIA%"')
				->joinLeft(array('cg'=>'clientes'),'cg.ID = g.id_clientes')
				->where("p.CODIGO = '".$val[codproduto]."' ".$where)
		        ->order('e.id desc','')
		        ->limit($limit);			        
			        			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();				
		}
		
		function buscaQtatualproduto($val){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('e'=>'tb_estoqueztl','*'), array('e.qt_atual'))
			        ->where('e.id = (select max(id) from tb_estoqueztl where id_prod = '.$val.')');			        
			        			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();				
		}
		
		function gerarXmlcompra($val){	
			/*$this->view->objList 	= ComprasBO::buscaProdutospedidos($params['ped']);
			$this->view->objPed		= ComprasBO::buscaPedidos($params['ped']);
			$this->view->objFin		= FinanceiroBO::buscaContaspedidocompra($params['ped']);
			$this->view->objEnt		= ComprasBO::listaEntregaped($params);*/
			
			$dom = new DOMDocument("1.0", "ISO-8859-1");
			$dom->preserveWhiteSpace = false;
			$dom->formatOutput = true;
			
			//criando o nó principal (root)
			$root = $dom->createElement("pedido");
			
			foreach (ComprasBO::buscaPedidos($val) as $listd);
			$root->appendChild($dom->createElement("pedidocli", $listd->idped));
			$root->appendChild($dom->createElement("dataentrega", $listd->data_entrega));
			$root->appendChild($dom->createElement("parcial", $listd->partial_shipment));
									
			//nó filho (produtos)
			$produtos = $dom->createElement("produtos");
			
			$cont = 0;
			foreach (ComprasBO::buscaProdutospedidos($val) as $listp):
				
				$codigos = $dom->createElement("produto");
				$codigos->appendChild($dom->createElement("idprod", $listp->idprod));
				$codigos->appendChild($dom->createElement("cod", $listp->CODIGO));
				$codigos->appendChild($dom->createElement("qt", $listp->QUANTIDADE));
				$codigos->appendChild($dom->createElement("preco", $listp->preco));
				$codigos->appendChild($dom->createElement("moeda", $listp->moedap));
				
				$produtos->appendChild($codigos);				
			endforeach;
						
			$produtos->appendChild($codigos);
			$root->appendChild($produtos);
			
			$dom->appendChild($root);
			return $dom;			
		}		
		
		function fechaPedidocompra($ped){
			$bo		= new ZtlcomprasModel();
			$bop	= new ZtlcomprasprodModel();
			$bof 	= new FinanceiroModel();
			$bofp 	= new FinanceiropagarModel();
			$boparc = new FinanceiropagarparcModel();
			$boe	= new EntradaestoqueModel();
						
			$array['STATUS'] = "FINALIZADO";
			$bo->update($array, "md5(ID) = '".$ped."'");
			
			$var['ped'] = $ped;
			foreach (ComprasBO::listaEntregapedgroup($var) as $listap):
				$arrayp['sit']		= true;
				$bop->update($arrayp, "ID = ".$listap->idprodped);
			endforeach;
			
			foreach ($bofp->fetchAll("md5(id_pedcompra) = '".$ped."'") as $listf);			
			$idf	= $listf->id;
			
			if($idf!=""):
				$arrayf['pedfechado'] 	= 1;
				$boparc->update($arrayf,"id_financeiropag = ".$idf);	
			endif;
			
			$var['ped'] = $ped;
			foreach (ComprasBO::listaEntregaped($var) as $listproent):
				echo $listproent->docent;
				$arrayent['bloq']	= true;
				$boe->update($arrayent, "id = ".$listproent->docent); 
			endforeach;
			
		}
		
		function listaEstoquegrupo($pesq){
			
			$sessaobusca = new Zend_Session_Namespace('Estoqueprod');
		    if(isset($sessaobusca->where)):
		   		$where = $sessaobusca->where;
		   	endif;			
		   	
		   	if(!empty($pesq['codproduto'])){
		   	    $where = " and p.CODIGO like '%".$pesq['codproduto']."%'";
		   	
		  	//---Busca por grupo - subgrupo---------------------------
		   	}elseif ($pesq['buscagruposub']!=0){
				$where = " and p.id_gruposprodsub = ".$pesq['buscagruposub'];
		   	}elseif ($pesq['grupovenda']!=0){
				foreach (GruposprodBO::listaGruposprodutossub($pesq['grupovenda']) as $listsubg):
					$idsg .= $listsubg->id.",";
				endforeach;
				$where = " and p.id_gruposprodsub in (".substr($idsg, 0,-1).") ";
		   	};
			
			//---Busca por grupo de compra-----------------------------
			if($pesq['buscagrupo']!=0):
				$where = 'and p.Purchasing_group = '.$pesq['buscagrupo'];
			endif;		  	
			
			$usuario = Zend_Auth::getInstance()->getIdentity();			
			foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $listr);
			if($listr->nivel != 2){
			    $idsg = "";
			    foreach (GruposprodBO::listaGruposprodutossub($pesq['grupovenda']) as $listsubg):
			    	if($listsubg->tipo == 1){
			    		$idsg .= $listsubg->id.",";
			    	}
			    endforeach;
			    $where .= " and p.id_gruposprodsub in (".substr($idsg, 0,-1).") ";
			}
			
			if(!empty($pesq['comestoque'])){
			    $where .= " and e.qt_atual > 0";
			}
			
			//----- Periodo da busca --------------------------------------------------------
			
			if(isset($sessaobusca->wheremax)):
				$wheremax = $sessaobusca->wheremax;
			endif;
			
			if(!empty($pesq['periodo'])){
				$wheremax = ' and ee.dt_atualizacao <= "'.$pesq['periodo'].'-31"';
			}
			
			// -------------------------------------------------------------------------------
			
			
			if(!empty($where)):
		   		$sessaobusca->where = $where;
		   	endif;
		   	
		   	if(!empty($wheremax)):
		   		$sessaobusca->wheremax = $wheremax;
		   	endif;
			
		   			   	
		  	$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
												
			$select->from(array('p'=>'produtos','*'), array('p.CODIGO','p.DESCRICAO', 'e.qt_atual','e.id','e.id_prod','p.ID as idprod','DATE_FORMAT(p.dateverestoque,"%d/%m/%Y") as dataver', 'c.valor as custoproduto','c.data as datacustoproduto'))
			        ->joinLeft(array('e'=>'tb_estoqueztl'),'p.id = e.id_prod and e.id = (SELECT max(id) from tb_estoqueztl ee where e.id_prod = ee.id_prod '.$wheremax.')')
			        ->joinLeft(array('c'=>'tb_produtoscmv'),'c.id_produtos = p.ID and c.id = (SELECT max(id) from tb_produtoscmv cc where cc.id_produtos = c.id_produtos)')
					->where('p.situacao != 2  '.$where)
			        ->order('p.codigo_mask asc');
			
			$stmt = $db->query($select);
			return $stmt->fetchAll();				
		}
		
		
		//-- Lista estoque com custo e pedidos de venda ---------------------------------------------
		function listaEstoquecurva($pesq){
				
			$sessaobusca = new Zend_Session_Namespace('Estoqueprod');
			if(isset($sessaobusca->where)):
			$where = $sessaobusca->where;
			endif;
		
			if(!empty($pesq['codproduto'])){
				$where = " and p.CODIGO like '%".$pesq['codproduto']."%'";
		
				//---Busca por grupo - subgrupo---------------------------
			}elseif ($pesq['buscagruposub']!=0){
				$where = " and p.id_gruposprodsub = ".$pesq['buscagruposub'];
			}elseif ($pesq['grupovenda']!=0){
				foreach (GruposprodBO::listaGruposprodutossub($pesq['grupovenda']) as $listsubg):
				$idsg .= $listsubg->id.",";
				endforeach;
				$where = " and p.id_gruposprodsub in (".substr($idsg, 0,-1).") ";
			};
				
			//---Busca por grupo de compra-----------------------------
			if($pesq['buscagrupo']!=0):
			$where = 'and p.Purchasing_group = '.$pesq['buscagrupo'];
			endif;
				
			$usuario = Zend_Auth::getInstance()->getIdentity();
			foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $listr);
			if($listr->nivel != 2){
				$idsg = "";
				foreach (GruposprodBO::listaGruposprodutossub($pesq['grupovenda']) as $listsubg):
				if($listsubg->tipo == 1){
					$idsg .= $listsubg->id.",";
				}
				endforeach;
				$where .= " and p.id_gruposprodsub in (".substr($idsg, 0,-1).") ";
			}
				
			if(!empty($pesq['comestoque'])){
				$where .= " and e.qt_atual > 0";
			}
				
			//----- Periodo da busca --------------------------------------------------------
				
			if(isset($sessaobusca->wheremax)):
			$wheremax = $sessaobusca->wheremax;
			endif;
				
			if(!empty($pesq['periodo'])){
				$wheremax = ' and ee.dt_atualizacao <= "'.$pesq['periodo'].'-31"';
			}
				
			// -------------------------------------------------------------------------------
				
				
			if(!empty($where)):
			$sessaobusca->where = $where;
			endif;
		
			if(!empty($wheremax)):
			$sessaobusca->wheremax = $wheremax;
			endif;
				
		
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
			$select = $db->select();
		
			$select->from(array('p'=>'produtos'), array('p.CODIGO','p.DESCRICAO', 'e.qt_atual','e.id','e.id_prod','p.ID as idprod', 'p.PRECO_UNITARIO',
			        'DATE_FORMAT(p.dateverestoque,"%d/%m/%Y") as dataver', 'c.valor as custoproduto','c.data as datacustoproduto',
			        'sum(pp.qt) as pendencia' ,'g.descricao as descgrupo',
					'(select sum(qt) from tb_pedidos_prod pd, tb_pedidos ped where pd.id_prod = p.ID and ped.id = pd.id_ped and ped.data_vend > date_add(now(), interval -36 month)) as venda'
			        ))
				->joinLeft(array('e'=>'tb_estoqueztl'),'p.id = e.id_prod and e.id = (SELECT max(id) from tb_estoqueztl ee where e.id_prod = ee.id_prod '.$wheremax.')')
				->joinLeft(array('c'=>'tb_produtoscmv'),'c.id_produtos = p.ID and c.id = (SELECT max(id) from tb_produtoscmv cc where cc.id_produtos = c.id_produtos)')
				->joinLeft(array('pp'=>'tb_pedidos_pend'),'pp.id_prod = p.ID and pp.dt_pend > date_add(now(), interval -12 month) and pp.status = 0')
				->joinLeft(array('s'=>'tb_gruposprodsub'), 's.id = p.id_gruposprodsub')
				->joinLeft(array('g'=>'tb_gruposprod'), 'g.id = s.id_gruposprod')
				
				->where('p.situacao != 2 '.$where)
				->group('p.ID')
				->order('p.codigo_mask asc');
				
			
			$stmt = $db->query($select);
			
			return $stmt->fetchAll();
		}
		
		
		
		/*SELECT  e.*, p.CODIGO from tb_estoqueztl e, produtos p where e.id_prod = p .ID
		and qt_atual < 0 and p.id = e.id_prod and e.id = (SELECT max(id) from tb_estoqueztl e where p.id = e.id_prod)*/
		
		//--Entrada------------------------------------------------------
		//--Lista entrada estoque---------------------------
		  function listaEntrada($pesq){
			
			$limit=200;
			
			if(isset($pesq['tipo']) and $pesq['tipo']==1):
				if(!empty($pesq['buscaid'])):
					$where = " and t.id = ".substr($pesq['buscaid'],1);
					$limit=1000;
				endif;
			elseif (isset($pesq['tipo']) and $pesq['tipo']==2):
				if(!empty($pesq['buscadoc'])):
					$where = " and t.fornecimento like '%".$pesq['buscadoc']."%'";
					$limit=1000;
				endif;
			elseif(!empty($pesq['entrada'])):
				$where = " and md5(t.id) = '".$pesq['entrada']."'";
				$limit=1000;				
			endif;
			
			if((!empty($pesq['dtini'])) || (!empty($pesq['dtini']))):
				if(!empty($pesq['dtini'])) $di	= substr($pesq['dtini'],6,4).'-'.substr($pesq['dtini'],3,2).'-'.substr($pesq['dtini'],0,2);
				if(!empty($pesq['dtini'])) $df	= substr($pesq['dtini'],6,4).'-'.substr($pesq['dtini'],3,2).'-'.substr($pesq['dtini'],0,2);
			
				if((!empty($di)) and (!empty($df))): 
					$where .= ' and data between "'.$di.'" and "'.$df.'"';
					$limit=1000;
				elseif((!empty($di)) and (empty($df))): 
					$where .= ' and data >= "'.$di.'"';
					$limit=1000;
				elseif((empty($di)) and (!empty($df))): 
					$where .= ' and data <= "'.$df.'"';
					$limit=1000;
				endif;
			endif;
		  	
						
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_entradaztl','*'),
			        array('t.*','t.id_nfe','t.id as identrada','t.fornecimento','DATE_FORMAT(t.data,"%d/%m/%Y") as data','c.EMPRESA','t.obs','c.id_despesasfiscais','t.id_tributocfop','t.data as datadi','cl.EMPRESA as fornecedor'))
			        ->join(array('c'=>'clientes'),'t.id_user = c.ID')
			        ->joinLeft(array('cl'=>'clientes'),'t.id_fornecedor = cl.ID')
			        ->where("t.sit = true ".$where)
			        ->order('t.id desc','');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		function buscaEntrada($params){
			$obj = new EntradaestoqueModel();
			return $obj->fetchAll('id = '.$params);			
		}	
		
		function buscaUltimaentrada(){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_entradaztl','*'), array('max(id) as identrada'))
				   ->where("status = 2");
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		
		
		
		
		//-- Gerar entradas---------------------------------------------------------------------------
		/*---- Lista fornecedores com pedidos em aberto -----------------------
		 * Usado em entradaestAction ------------------------
		 * */		
		function listaFornecedorescompra(){
		    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		    $db->setFetchMode(Zend_Db::FETCH_OBJ);
		    	
		    $select = $db->select();		    	
		    $select->from(array('pc'=>'pedidos_compra','*'), array('*'))
			    ->join(array('c'=>'clientes'),'c.ID = pc.id_for')
			    ->group('c.ID')
			    ->order('c.EMPRESA');
		    	
		    $stmt = $db->query($select);
		    return  $stmt->fetchAll();
		}
		
		function gravaEntradatmp($var){
			$boe	= new EntradaestoqueModel();
			$bo		= new EntradaestoquetmpModel();
			$boc	= new ZtlcomprasModel();
			$bocp	= new ZtlcomprasprodModel();
			
			if(!empty($var['atualizaqts'])):
				foreach (ComprasBO::listaProdutosenttmp() as $listprod):
					if(!empty($var["qt_".$listprod->ident])):
						$array['qt']	= $var["qt_".$listprod->ident];
						$bo->update($array,"id = ".$listprod->ident);
					endif;
				endforeach;
				$bo->delete("qt is NULL");
			elseif(!empty($var['atualiza'])):
				$array['qt'] = $var["qt_".$var['atualiza']];
				$bo->update($array,"id = ".$var['atualiza']);
			else:
				//--Verifico quantos produtos pedidos existem-----------------------------
				if(count($bocp->fetchAll("sit = false and ID_PRODUTO = ".$var["prodped"],"ID asc")) > 1):
				//--Se for varios produtos pedidos -----------------------------
					foreach ($bocp->fetchAll("sit = false and ID_PRODUTO = ".$var["prodped"],"ID asc") as $listprod):
						$array['id_prodped']	= $listprod->ID;
						$array['preco']			= $listprod->PRECO_UNITARIO_USD;
						$array['cst']			= $var['cst'];
						$array['cfop']			= $var['cfop'];
						$array['pesounit']		= str_replace(",",".",str_replace(".","",$var['peso']));
						$bo->insert($array);				
					endforeach;				
					$erro = 3;	
				
				elseif(count($bocp->fetchAll("sit = false and ID_PRODUTO = ".$var["prodped"],"ID asc")) == 1):
				//--Se for apenas 1 produto pedido -----------------------------
					$params = array ('host' => '127.0.0.1',	'username' => 'ztlrolamentos', 'password' => 'BdMySql2008', 'dbname'   => 'ztlrolamentos');
					
					$db = Zend_Db::factory('PDO_MYSQL', $params);
					$db->setFetchMode(Zend_Db::FETCH_OBJ);
					
					$select = $db->select();
					
					$select->from(array('c'=>'produtos_pedidos_compra','*'), array('*','c.ID as idprodped'))
					        ->joinLeft(array('e'=>'tb_entradaztl_prod'),'e.id_prodped = c.ID')
					        ->where("c.ID_PRODUTO = ".$var["prodped"]." and c.sit = false")
					        ->order('c.id asc','asc');
					  
					$stmt = $db->query($select);
						
					$qtped = $qtent = 0;
					foreach ($stmt->fetchAll() as $listprod):
						$qtent += $listprod->qt;
						$qtped = $listprod->QUANTIDADE;
					endforeach;
					
					if(($qtped-$qtent)>=($var["qt"])):
					//--Se qt entrada for menor ou igual a pedida -----------------------------
						$array['id_prodped']	= $listprod->idprodped;
						$array['preco']			= $listprod->PRECO_UNITARIO_USD;
						$array['qt']			= $var["qt"];
						$array['cst']			= $var['cst'];
						$array['cfop']			= $var['cfop'];
						$array['pesounit']		= str_replace(",",".",str_replace(".","",$var['peso']));
						$ident = $bo->insert($array);
						
					else:
					//--Se qt entrada for maior que pedida -----------------------------
						$array['id_prodped']	= $listprod->idprodped;
						$array['preco']			= $listprod->PRECO_UNITARIO_USD;
						$array['cst']			= $var['cst'];
						$array['cfop']			= $var['cfop'];
						$array['pesounit']		= str_replace(",",".",str_replace(".","",$var['peso']));
						$ident = $bo->insert($array);
						
						$erro = 2;				
					endif;				
				else:
					$erro = 1;
				endif;
			endif;
			
			return $erro;
			
			/*
			 * Erro 1 = Produto nao pedido
			 * Erro 2 = Quantidade superior a pedida
			 * Erro 3 = Produto em mais de 1 pedido			 
			 *  
			 * */
			
		}
		
		function removeEntradatmp($var){
			$boe	= new EntradaestoqueModel();
			$bo		= new EntradaestoquetmpModel();			
						
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('c'=>'produtos_pedidos_compra','*'), array('*','c.ID as idprodped','e.id as ident'))
			        ->join(array('e'=>'tb_entradaprodtmp'),'e.id_prodped = c.ID')
			        ->join(array('p'=>'produtos'),'p.ID = c.ID_PRODUTO')
			        ->where("md5(p.ID) = '".$var['ident']."'");
			  
			$stmt = $db->query($select);
			foreach ($stmt->fetchAll() as $lista):
				$bo->delete("id = ".$lista->ident);				
			endforeach;
			
		}
		
		/*-- Usado em entradaestoqimportAction ---------- */
		function listaProdutosenttmp(){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('c'=>'produtos_pedidos_compra','*'), array('*','c.ID as idprodped','e.id as ident'))
					->join(array('pc'=>'pedidos_compra'),'pc.ID = c.ID_PEDIDO_COMPRA')
			        ->join(array('e'=>'tb_entradaprodtmp'),'e.id_prodped = c.ID')
			        ->join(array('p'=>'produtos'),'p.ID = c.ID_PRODUTO')
			        ->where("c.sit = false and pc.sit = true and pc.STATUS = ''")
			        ->order('e.id asc','asc')
			        ->group("c.ID");
			  
			$stmt = $db->query($select);
			return  $stmt->fetchAll();
		}		
		
		
		/* --- Listas produtos agrupados para selecao na montagem da entrada ----------
		-- Usado em entradaestoqimportAction ------ */
		
		function listaProdutosenttmpgroup(){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('c'=>'produtos_pedidos_compra','*'), array('*','c.ID as idprodped','e.id as ident', 
					'sum(e.qt) as qta', 'e.preco as precounit', 'e.pesounit','e.ncm as ncmcad', 'e.ipi as ipicad'))
					->join(array('pc'=>'pedidos_compra'),'pc.ID = c.ID_PEDIDO_COMPRA')
			        ->join(array('e'=>'tb_entradaprodtmp'),'e.id_prodped = c.ID')
			        ->join(array('p'=>'produtos'),'p.ID = c.ID_PRODUTO')
			        ->joinLeft(array('n'=>'tb_produtosncm'),'p.id_ncm = n.id')
			        ->where("pc.sit = true and pc.STATUS = ''")
			        ->group("c.ID")
			        ->order('p.codigo_mask','asc');
			  
			$stmt = $db->query($select);
			return  $stmt->fetchAll();
		}
		
		function listaProdtmpgroupidproped(){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('c'=>'produtos_pedidos_compra','*'), array('*','c.ID as idprodped','e.id as ident', 
					'sum(e.qt) as qta', 'e.preco as precounit', 'e.pesounit','e.ncm as ncmcad','n.ncmex','n.ncm', 'e.ipi as ipicad'))
					->join(array('pc'=>'pedidos_compra'),'pc.ID = c.ID_PEDIDO_COMPRA')
			        ->join(array('e'=>'tb_entradaprodtmp'),'e.id_prodped = c.ID')
			        ->join(array('p'=>'produtos'),'p.ID = c.ID_PRODUTO')
			        ->joinLeft(array('n'=>'tb_produtosncm'),'p.id_ncm = n.id')
			        ->where("pc.sit = true and pc.STATUS = ''")
			        ->group("c.ID")
			        ->order('e.id asc','asc');
			  
			$stmt = $db->query($select);
			return  $stmt->fetchAll();
		}
		
		function listaProdutosenttmpgroupncm(){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('c'=>'produtos_pedidos_compra','*'), array('*','c.ID as idprodped','e.id as ident'))
			        ->join(array('e'=>'tb_entradaprodtmp'),'e.id_prodped = c.ID')
			        ->join(array('p'=>'produtos'),'p.ID = c.ID_PRODUTO')
			        ->joinLeft(array('n'=>'tb_produtosncm'),'p.id_ncm = n.id')
			        ->group("n.id")
			        ->order('n.id asc','asc');
			  
			$stmt = $db->query($select);
			return  $stmt->fetchAll();
		}
		
		function registraEmpresaentrada($var){
			$boe	= new EntradaestoqueModel();
			$bo		= new EntradaestoqueempresatmpModel();
			$boet	= new EntradaestoquetmpModel();
			
			$bo->delete();
			$boet->delete();
			$array['id_fornecedor'] 	= $var['fornecedor'];
			$bo->insert($array);
		}
		
		
		//----- Entrada nacional -----------------------------------------------
		/* -- Lista produtos pedidos do fornedor selecionado na entrada dos produtos --
		 * Usado em entradaestoq ---
		 * */		
		function listaProdutosporfornecedor(){
		    $boe	= new EntradaestoqueModel();
		    $bo		= new EntradaestoqueempresatmpModel();
		    
		    foreach ($bo->fetchAll() as $forn);
		    
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
			$select = $db->select();
				
			$select->from(array('c'=>'produtos_pedidos_compra','*'), array('*','c.ID as id_prodped','c.PRECO_UNITARIO_USD as precocompra','c.ID as idpedprod'))
				->join(array('p'=>'produtos'),'p.ID = c.ID_PRODUTO')
				->join(array('pc'=>'pedidos_compra'),'pc.ID = c.ID_PEDIDO_COMPRA')
				->where('pc.id_for = '.$forn->id_fornecedor)
				->order('p.codigo_mask asc')
				->order('pc.ID asc');
				
			$stmt = $db->query($select);
			return  $stmt->fetchAll();
		}
		
		/*--- Usado em entradaestoqAction --------------------
		*/
		function gerarEntradacompranac($var){
			$boe	= new EntradaestoqueModel();
			$bop	= new EntradaestoqueprodModel();
						
			$usuario = Zend_Auth::getInstance()->getIdentity();
			
			//--Grava a entrada--------------------------------------
			$array['id_user']		= $usuario->id;
			$array['id_fornecedor']	= $var['empresa'];
			$array['data']			= date("Y-m-d H:i:s");
			$array['obs']			= $var['obs'];
			$array['sit']			= true;
			$array['status']		= 1;
			$array['tipo']			= 1;	
			$array['data']			= substr($var['datadoc'],6,4).'-'.substr($var['datadoc'],3,2).'-'.substr($var['datadoc'],0,2);
			$array['fornecimento']	= strtoupper($var['documento']);
			
			$ident = $boe->insert($array);
			
			foreach (ComprasBO::listaProdutosporfornecedor() as $listp):
				
					$arrayp['qt']				= $var['qt_'.$listp->id_prodped];
					$arrayp['preco']			= str_replace(",",".",str_replace(".","", $var['preco_'.$listp->id_prodped]));
					$arrayp['id_entradaztl']	= $ident;
					$arrayp['id_prodped']		= $listp->id_prodped;
					$bop->insert($arrayp);
				
			endforeach;
			
		  	//--- Upload de arquivos ------------------------------------------------------------------------
		  	
			$arquivo = isset($_FILES['anexo']) ? $_FILES['anexo'] : FALSE;
		  	$pasta = Zend_Registry::get('pastaPadrao')."public/compras/anexoentradas/";
		  		
		  	if (!(is_dir($pasta))){
		  		if(!(mkdir($pasta, 0777))){
		  			echo ("Alerta: pasta de upload nao existe, e nao pode ser criada");
		  			return $this;
		  		}
		  	}
		  	
		  	if(!(is_writable($pasta))){
		  		echo ("Alerta: pasta dos projetos sem permissao de escrita");
		  		return $this;
		  	}
		  		
		  	
		  	if(is_uploaded_file($arquivo['tmp_name'])){
		  	    $exts = split("[/\\.]", $arquivo['name']) ;
		  	    $n = count($exts)-1;
		  	    $exts = $exts[$n];
		  	   
		  		if (move_uploaded_file($arquivo["tmp_name"], $pasta . $ident.".".$exts)) {
		  			$array['anexo']	= $exts;
		  		    $boe->update($array,"id = ".$ident);		  		    
		  		} else {
		  			echo ("Alerta: Nao foi possivel fazer o upload para $pasta");
		  			return $this;
		  		}
		  	}else{
		  		echo "erro ao carregar imagem";
		  	}
		  	
		  	
			return $ident;
		}
		
		function gerarCmventradanac($var){
			$bo = new CmvModel();		
			$params['entrada'] = md5($var);
        	
        	$qtentreg = $qtpedid = $total_pedido = $totalicms = $vlicms = $cor = 0;
        	foreach (ComprasBO::listaProdutosentgroup($params) as $listaprod):
        		$cor++;	            
	            //--- somar produtos iguais de pedidos diferentes ---------------------------
	            $pedidos = "";
	            $qtentreg = $qtpedid = 0;
	            foreach (ComprasBO::listaProdutosent($params) as $list):
	        		if(($list->ID_PRODUTO == $listaprod->ID_PRODUTO) and (!empty($list->qt))):
	        			$qtpedid  += $list->QUANTIDADE;
	        			$qtentreg += $list->qt;
	        		endif;
	        	endforeach;
	                      
	          	$qtentreg; 
	          	$qttotal += $qtentreg;
	          
	          	$totalprod = $listaprod->preco*$qtentreg;
                $vtotal += $totalprod; 	                   

                $ipi	= ($totalprod*$listaprod->ipi)/100;
	            $baseicms = $ipi+$totalprod+($totalprod*$listaprod->iva)/100;
	            $tbaseicms += $baseicms;
	            $vipi += $ipi;
	            
	            $icmsst = (($totalprod*$listaprod->icmsfor)/100);
	            $icms = (($baseicms*$listaprod->icms)/100) - $icmsst;
	            $vlicms	+= (($baseicms*$listaprod->icms)/100) - $icmsst;
	            $vlicmsst += $icmsst;
	            
	            $array['valor']			= ($totalprod+$icms+$ipi)/$qtentreg;
	           	$array['data']			= date("Y-m-d H:i:s");
	           	$array['id_entradaztl']	= $var;
	           	$array['id_prod']		= $listaprod->ID_PRODUTO;
	           	$bo->insert($array);
	           	
        	endforeach;
        	
		}
		
		//--- Gera entrada nacional -------------------------------------------------------------------
		/* function gerarEntradacompranacional($var){
			$boe	= new EntradaestoqueModel();
			$bo		= new EntradaestoquetmpModel();
			$boncm	= new EntradaestoquencmModel();
			$bop	= new EntradaestoqueprodModel();
			$boemp	= new EntradaestoqueempresatmpModel();
			$boest	= new EstoqueModel();
			$boz	= new ZtlcomprasModel();
			$bozp	= new ZtlcomprasprodModel();
		
			$usuario = Zend_Auth::getInstance()->getIdentity();
				
			foreach ($boemp->fetchAll() as $empresa);
				
			//--Grava a entrada--------------------------------------
			$array['id_user']		= $empresa->id_fornecedor;
			$array['data']			= substr($var['datadoc'],6,4).'-'.substr($var['datadoc'],3,2).'-'.substr($var['datadoc'],0,2);
			$array['obs']			= $var['obs'];
			$array['frete']			= str_replace(",",".",str_replace(".","",$var['frete']));
			$array['capatazia']		= str_replace(",",".",str_replace(".","",$var['capatazia']));
			$array['siscomex']		= str_replace(",",".",str_replace(".","",$var['siscomex']));
			$array['fornecimento']	= strtoupper($var['documento']);
			$array['sit']			= true;
			$array['status']		= 1;
			$array['docdi']			= strtoupper($var['docdi']);
				
			$ident = $boe->insert($array);
		
			foreach (ComprasBO::listaProdutosporfornecedor() as $listp):
		
			$arrayp['qt']				= $listp->qt;
			$arrayp['preco']			= $listp->preco;
			$arrayp['id_entradaztl']	= $ident;
			$arrayp['id_prodped']		= $listp->id_prodped;
			$arrayp['cst']				= $var['cst'];
			$arrayp['cfop']				= $var['cfop'];
			$arrayp['txcambio']			= str_replace(",",".",str_replace(".","",$var['cambio']));
			$arrayp['icms']				= str_replace(",",".",str_replace(".","",$var['icms']));
				
			$arrayp['ii']				= "";
			$arrayp['ipi']				= "";
			$arrayp['pis']				= "";
			$arrayp['cofins']			= "";
			$arrayp['ncm']				= "";
				
			//---busca impostos---------------------------------------
			//--- listaProdutosenttmpgroup
			foreach (ComprasBO::listaProdtmpgroupidproped() as $prod):
			if($prod->idprodped==$listp->id_prodped):
			$arrayp['ii']		= $prod->ii;
			$arrayp['ipi']		= $prod->ipi;
			$arrayp['pis']		= $prod->pis;
			$arrayp['cofins']	= $prod->cofins;
			$arrayp['ncm']		= $prod->ncm;
			$arrayp['aduaneiro']		= str_replace(",",".",str_replace(".","",$var['vl_'.$prod->id_ncm]));
			endif;
			endforeach;
				
			$bop->insert($arrayp);
				
			
			$arrayent['sit'] 			= false;
			if($listp->QUANTIDADE <= $listp->qt):
			$arrayent['sit']		= true;
			$bozp->update($arrayent, "ID = ".$listp->id_prodped);
			ComprasBO::finalizaEntrega($listp->ID_PEDIDO_COMPRA);
			endif;
				
			endforeach;
				
			$bo->delete();
			return $ident;
		} */
		
		//-- Gera entrada importada ---------------------------------------------------------------
		function gerarEntradacompra($var){
			$boe	= new EntradaestoqueModel();
			$bo		= new EntradaestoquetmpModel();
			$boncm	= new EntradaestoquencmModel();
			$bop	= new EntradaestoqueprodModel();
			$boemp	= new EntradaestoqueempresatmpModel();
			$boest	= new EstoqueModel();
			$boz	= new ZtlcomprasModel();
			$bozp	= new ZtlcomprasprodModel();
						
			$usuario = Zend_Auth::getInstance()->getIdentity();
			
			foreach ($boemp->fetchAll() as $empresa);
			
			//--Grava a entrada--------------------------------------
			$array['id_user']			= $usuario->id;
			$array['id_fornecedor']		= $empresa->id_fornecedor;
			$array['data']				= substr($var['datadoc'],6,4).'-'.substr($var['datadoc'],3,2).'-'.substr($var['datadoc'],0,2);
			$array['obs']				= $var['obs'];
			$array['frete']				= str_replace(",",".",str_replace(".","",$var['frete']));
			$array['capatazia']			= str_replace(",",".",str_replace(".","",$var['capatazia']));
			$array['siscomex']			= str_replace(",",".",str_replace(".","",$var['siscomex']));
			$array['fornecimento']		= strtoupper($var['documento']);
			$array['sit']				= true;
			$array['status']			= 1;			
			$array['docdi']				= strtoupper($var['docdi']);		
			$array['id_tributocfop']	= $var['cfop'];
			$array['tipo']				= 2;
			
			$array["qtpacotes"]			= $var['qtpacote'];
			$array["especie"] 			= $var['especie'];
			$array["pesobruto"]			= str_replace(",",".",str_replace(".","",$var['pesobruto']));
			$array["pesoliquido"]		= str_replace(",",".",str_replace(".","",$var['pesoliquido']));
			
			$array["localdesembarque"]	= $var['localdesembarque'];
			$array["ufdesembarque"]		= $var['ufdesembarque'];
			$array["datadesembarque"]	= substr($var['datadesembarque'],6,4).'-'.substr($var['datadesembarque'],3,2).'-'.substr($var['datadesembarque'],0,2);
			
			
			$ident = $boe->insert($array);

			$totalentrada = 0;
			foreach (ComprasBO::listaProdtmpgroupidproped() as $totalprod):
				$totalentrada += $totalprod->preco * $totalprod->qta;
			endforeach;
			
			$ncmtmp = "";
			$numseq = 0;
			$ncm2 = "";
			foreach (ComprasBO::listaProdutosenttmp() as $listp):
				
				$arrayp['qt']				= $listp->qt;
				$arrayp['preco']			= $listp->preco;
				$arrayp['id_entradaztl']	= $ident;
				$arrayp['id_prodped']		= $listp->id_prodped;
				$arrayp['txcambio']			= str_replace(",",".",str_replace(".","",$var['cambio']));
				
				//---busca impostos---------------------------------------
				//--- listaProdutosenttmpgroup
				$ncm = "";
				foreach (ComprasBO::listaProdtmpgroupidproped() as $prod):
					if($prod->idprodped==$listp->id_prodped):		
					
						if($prod->ncmex != "") $ncm = $prod->ncm." ".$prod->ncmex;
						else $ncm = $prod->ncm;
						
						$arrayp['ncm']				= $ncm;
						$arrayp['aduaneiro']		= str_replace(",",".",str_replace(".","",$var['vl_'.$prod->id_ncm]));
						$arrayp['numadicao']		= $var['adicao_'.$prod->id_ncm];
						
						$contseq = count($bop->fetchAll("id_entradaztl = ".$ident." and numadicao = ".$var['adicao_'.$prod->id_ncm]));
						$arrayp['numsequencia']		= $contseq+1;
					endif;										
				endforeach;
				
				$bop->insert($arrayp);	
					
				/*-- Finalizo produtos recebidos junto com pedidos de compra ------------------------------------------- */					
				$arrayent['sit'] 			= false;
				if($listp->QUANTIDADE <= $listp->qt):
					$arrayent['sit']		= true;
					$bozp->update($arrayent, "ID = ".$listp->id_prodped);
					ComprasBO::finalizaEntrega($listp->ID_PEDIDO_COMPRA);
				endif;									
					
			endforeach;
			
			$bo->delete("id > 0");
			return $ident;
		}

		function finalizaEntrega($var){
			$boz	= new ZtlcomprasModel();
			$bozp	= new ZtlcomprasprodModel();
			
			foreach ($bozp->fetchAll("sit = false and ID_PEDIDO_COMPRA = ".$var) as $prod);
			
			if(count($prod)<=0):
				$array['STATUS'] = "Entregue";
				$boz->update($array, "ID = ".$var);			
			endif;
		}
		
		function calculaAduaneiro($ident){
		    $boe	= new EntradaestoqueModel();
		    $bop	= new EntradaestoqueprodModel();
		    
		    $params['entrada'] = md5($ident);
		    $objProdutos		= ComprasBO::listaProdutosentgroup($params);
		    $objProdncm			= ComprasBO::listaProdutosentncm($params);
		    $objProddet			= ComprasBO::listaProdutosent($params);
		    foreach ($objProdncm as $prodncm):
		    	foreach ($objProdutos as $prodgroup):
				    if($prodncm->ncm == $prodgroup->encm):
				    	
				    	$somaprod = 0;
				    	$somaprod = $prodgroup->preco * $prodgroup->qta;
				    	$perc = ($somaprod * 100)/$prodncm->total;
				    	$totalperc = ($prodncm->aduaneiro * $perc) /100;

				    	$data = array('prodaduaneiro' => $totalperc);
				    	
				    	foreach ($objProddet as $proddet):
				    		if($proddet->ID_PRODUTO == $prodgroup->ID_PRODUTO):
				    			$bop->update($data, "id = ".$proddet->ident);
				    		endif;
				    	endforeach;
				    endif; 
			    endforeach;
			endforeach;		    
		}

		function removeEntrada($params){
		    $boe	= new EntradaestoqueModel();
		    $boep	= new EntradaestoqueprodModel();
		    $boz	= new ZtlcomprasModel();
		    $bozp	= new ZtlcomprasprodModel();
		    
		    
		    $usuario = Zend_Auth::getInstance()->getIdentity();
		    	
		    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		    $db->setFetchMode(Zend_Db::FETCH_OBJ);
		    
		    $select = $db->select();
		    
		    $select->from(array('c'=>'produtos_pedidos_compra'), array('*'))
			    ->join(array('p'=>'tb_entradaztl_prod'),'p.id_prodped = c.ID')
			    ->where('md5(p.id_entradaztl) = "'.$params['entrada'].'"');
		    
		    $stmt = $db->query($select);
		    
		    foreach ($stmt->fetchAll() as $produtos):
			    $arrayent = array('sit' => false);
			    $bozp->update($arrayent, "ID = ".$produtos->id_prodped);
			    
			    $array['STATUS'] = "";
			    $boz->update($array, "ID = ".$produtos->ID_PEDIDO_COMPRA);
		    endforeach;

		    $dataent = array('sit' => false);		    
		    $boe->update($dataent, "md5(id) = '".$params['entrada']."'" );
		    
		   // $boep->delete("md5(id_entradaztl) = '".$params['entrada']."'");
		    
		}
		
		
		function gerarCmventrada($var){
			$bo = new CmvModel();		
			$params['entrada'] = md5($var);        	
			 
        	$qtprod = 0;
        	$idprod = -1;
        	
        	$cor=0;
        	$idverqt="";
        	$auxqt=0;
        	$total_pecas = 0;
        	
        	foreach (ComprasBO::listaEntrada($params) as $listent);
        	
        	//-- percentual frete ------------------
        	foreach (ComprasBO::listaProdutosentgroup($params) as $listaprod):
        		$qtentreg = $qtpedid = 0;
	            foreach (ComprasBO::listaProdutosent($params) as $list):
	        		if(($list->ID_PRODUTO == $listaprod->ID_PRODUTO) and (!empty($list->qt))):
	        			$qtpedid  += $list->QUANTIDADE;
	        			$qtentreg += $list->qt;
	        		endif;
	        	endforeach;
	            $total_pedido += $listaprod->preco*$qtentreg;
        	endforeach;
	        	
        	$aduaneiro = $total_pedido+$listent->frete+$listent->capatazia;
	        	
        	$qtentreg = $qtpedid = $total_pedido = $totalcofins = $totalpis = $totalii = 0;
        	foreach (ComprasBO::listaProdutosentgroup($params) as $listaprod):
        		
	            $qtentreg = $qtpedid = 0;
	            foreach (ComprasBO::listaProdutosent($params) as $list):
	        		if(($list->ID_PRODUTO == $listaprod->ID_PRODUTO) and (!empty($list->qt))):
	        			
	        			$qtpedid  += $list->QUANTIDADE;
	        			$qtentreg += $list->qt;
	        		endif;
	        	endforeach;
	               
                foreach (ComprasBO::listaProdutosentncm($params) as $pncm):
                	if($pncm->ncm == $listaprod->ncm):
                		$totalncm = $pncm->total;
                	endif;
                endforeach;
	                
                $coeficiente = ($listaprod->aduaneiro/(round($totalncm,2)*$listaprod->txcambio));
	                
                $totalunitario = $qtentreg*$listaprod->preco*$coeficiente*$listaprod->txcambio;
				
                
                $ii = ($listaprod->ii*$totalunitario)/100;
                
                $unitario = ($totalunitario+$ii)/$qtentreg;
                
                $ipi = (((($listaprod->ii*$totalunitario)/100)+$totalunitario)*$listaprod->ipi)/100;
                
                $aicms 		= $listaprod->icms/100;
                $aii 		= $listaprod->ii/100;
                $aipi 		= $listaprod->ipi/100;
                $apis 		= $listaprod->pis/100;
                $acofins 	= $listaprod->cofins/100;
                      
                $pis = $apis*($totalunitario*(1+$aicms*($aii+$aipi*(1+$aii))))/((1-$apis-$acofins)*(1-$aicms));
                
                $cofins = $acofins*($totalunitario*(1+$aicms*($aii+$aipi*(1+$aii))))/((1-$apis-$acofins)*(1-$aicms));

                $totalii 		+= $ii; 
                $totalpis 		+= $pis;
                $totalcofins 	+= $cofins;
                $totalipi       += $ipi;
                $total_pedido 	+= $totalunitario+$ii;
				
        	endforeach;
	        	
        	$coedicms = ($totalpis+$totalcofins+$listent->siscomex)/($total_pedido+$totalipi);      
        	
        	$qtentreg = $qtpedid = $total_pedido = $totalicms = 0;
        	foreach (ComprasBO::listaProdutosentgroup($params) as $listaprod):
        		$cor++;
	            if(($cor%2)==0) $class = 'td_orc_par';
	            else $class = 'td_orc';
	            $idprodutos .= $listaprod->ID_PRODUTO.";";
	            
	            //--- somar produtos iguais de pedidos diferentes ---------------------------
	            $pedidos = "";
	            $qtentreg = $qtpedid = 0;
	            foreach (ComprasBO::listaProdutosent($params) as $list):
	        		if(($list->ID_PRODUTO == $listaprod->ID_PRODUTO) and (!empty($list->qt))):
	        			$qtpedid  += $list->QUANTIDADE;
	        			$qtentreg += $list->qt;
	        		endif;
	        	endforeach;
	            
	            
				$listaprod->CODIGO; 
				
				$qtentreg;
			    
				$listaprod->ncm;
	               
                foreach (ComprasBO::listaProdutosentncm($params) as $pncm):
                	if($pncm->ncm == $listaprod->ncm):
                		$totalncm = $pncm->total;
                	endif;
                endforeach;
                
                $coeficiente = ($listaprod->aduaneiro/(round($totalncm,2)*$listaprod->txcambio));
                
                $totalunitario = $qtentreg*$listaprod->preco*$coeficiente*$listaprod->txcambio;
				
                echo "<br> Em dolar: ";
                echo ($qtentreg*$listaprod->preco*$coeficiente)/$qtentreg;
                
                $ii = ($listaprod->ii*$totalunitario)/100;
                
                $unitario = ($totalunitario+$ii)/$qtentreg;
				echo " - ";
                echo number_format($unitario,4,",",".");     
                
                $ipi = (((($listaprod->ii*$totalunitario)/100)+$totalunitario)*$listaprod->ipi)/100;
	                
                $aicms 		= $listaprod->icms/100;
                $aii 		= $listaprod->ii/100;
                $aipi 		= $listaprod->ipi/100;
                $apis 		= $listaprod->pis/100;
                $acofins 	= $listaprod->cofins/100;
                      
                $pis = $apis*($totalunitario*(1+$aicms*($aii+$aipi*(1+$aii))))/((1-$apis-$acofins)*(1-$aicms));
                $cofins = $acofins*($totalunitario*(1+$aicms*($aii+$aipi*(1+$aii))))/((1-$apis-$acofins)*(1-$aicms));
	           
                echo " - ";
                echo number_format($totalunitario+$ii,2,",",".");                   
	              
                $baseicms = 0;
                $baseicms = (($totalunitario+$ii)+$ipi+(($totalunitario+$ii)+$ipi)* $coedicms) / (1-$aicms);
                echo " - ";	                   
                echo number_format($baseicms,2,",",".");
                   
                $totalicms += $aicms*$baseicms; 
	            number_format($aicms*$baseicms,2);
	            number_format($ipi,2,",",".");  
	        	
				$total_pedido += $totalunitario+$ii;
				$total_pecas  += $qtentreg;
				
        	endforeach;    	
		}
		
		/* function gerarNcmentrada($var){
			$boe	= new EntradaestoqueModel();
			$bo		= new EntradaestoquetmpModel();
						
			$usuario = Zend_Auth::getInstance()->getIdentity();
				
			foreach (ComprasBO::listaProdutosenttmp() as $listp):
				if(!empty($var['qt_'.$listp->ident])):
					$arrayp['qt']				= $var['qt_'.$listp->ident];
					$arrayp['preco']			= $listp->preco;
					$arrayp['id_prodped']		= $listp->id_prodped;
					
					$bo->update($arrayp,'id = '.$listp->ident);
				endif;								
			endforeach;
			
			$bo->delete("qt is NULL");
			
		} */
				
		//----Listar entrega-----------------------------
		/* Usado em comprascontroller::pedidoscompraprod()*/
		function listaEntregaped($var){
			$boc	= new ZtlcomprasModel();
			$bocp	= new ZtlcomprasprodModel();
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('pc'=>'pedidos_compra'), array('e.qt as qtent','p.CODIGO','DATE_FORMAT(en.data,"%d/%m/%Y") as dtent','en.id as docent','p.ID as idprod','e.preco as precocomp','pd.ID as idprodcomp'))
					->join(array('pd'=>'produtos_pedidos_compra'),'pd.ID_PEDIDO_COMPRA = pc.ID')        
					->join(array('e'=>'tb_entradaztl_prod'),'e.id_prodped = pd.ID')
					->join(array('en'=>'tb_entradaztl'),'e.id_entradaztl = en.id')
			        ->join(array('p'=>'produtos'),'p.ID = pd.ID_PRODUTO')
			        ->where("en.sit = true and md5(pc.ID) = '".$var['ped']."'")
			        ->order('p.codigo_mask','asc');
			  
			$stmt = $db->query($select);
			return  $stmt->fetchAll();
		}
		
		//----Listar entrega agrupada-----------------------------
		function listaEntregapedgroup($var){
			$boc	= new ZtlcomprasModel();
			$bocp	= new ZtlcomprasprodModel();
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('pc'=>'pedidos_compra'), array('sum(e.qt) as qtent','p.CODIGO','DATE_FORMAT(en.data,"%d/%m/%Y") as dtent','en.id as docent','p.ID as idprod','pd.PRECO_UNITARIO_USD as precocomp','pd.ID as idprodped'))
					->join(array('pd'=>'produtos_pedidos_compra'),'pd.ID_PEDIDO_COMPRA = pc.ID')        
					->join(array('e'=>'tb_entradaztl_prod'),'e.id_prodped = pd.ID')
					->join(array('en'=>'tb_entradaztl'),'e.id_entradaztl = en.id')
			        ->join(array('p'=>'produtos'),'p.ID = pd.ID_PRODUTO')
			        ->where("md5(pc.ID) = '".$var['ped']."'")
			        ->group("p.ID");
			  
			$stmt = $db->query($select);
			return  $stmt->fetchAll();
		}
		
		//-- Listar entradas-----------------------------------		
		function listaProdutosent($var){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('c'=>'produtos_pedidos_compra','*'), array('*','c.ID as idprodped','e.id as ident','e.numsequencia','e.numadicao'))
			        ->join(array('e'=>'tb_entradaztl_prod'),'e.id_prodped = c.ID')
			        ->join(array('p'=>'produtos'),'p.ID = c.ID_PRODUTO')
			        ->where("md5(e.id_entradaztl) = '".$var['entrada']."'")
			        ->order('e.id asc','asc');
			  
			$stmt = $db->query($select);
			return  $stmt->fetchAll();
		}
		
		function listaProdutosentgroup($var){
		    if(!empty($var['adicao'])):
		    	$where = " and e.numadicao = ".$var['adicao'];
		    endif;
		    
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('c'=>'produtos_pedidos_compra','*'), array('*','c.ID as idprodped','e.id as ident', 'sum(e.qt) as qta', 'e.preco as precoimp',
		        'n.ncm as pncm','n.ncmex as pncmex','n.ii as pii','n.icms as picms','n.ipi as pipi','n.pis as ppis','n.cofins as pcofins','e.numsequencia', 'e.ncm as encm',
			    'cipi.tipo as tipoipi','cpis.tipo as tipopis','cc.tipo as tipocofins','p.id_ncm'))
		        ->join(array('e'=>'tb_entradaztl_prod'),'e.id_prodped = c.ID')
		        ->join(array('p'=>'produtos'),'p.ID = c.ID_PRODUTO')
		        
		        ->joinLeft(array('n'=>'tb_produtosncm'),'p.id_ncm = n.id')
		        
		        ->joinLeft(array('ci'=>'tb_tributocsticms'),'n.id_tributocsticms = ci.id')
		        ->joinLeft(array('cipi'=>'tb_tributocstipi'),'n.id_tributocstipi = cipi.id')
		        ->joinLeft(array('cpis'=>'tb_tributocstpis'),'n.id_tributocstpis = cpis.id')
		        ->joinLeft(array('cc'=>'tb_tributocstcofins'),'n.id_tributocstcofins = cc.id')
		         
		        ->where("md5(e.id_entradaztl) = '".$var['entrada']."'".$where)
		        ->order('e.numsequencia')
		        ->order('e.id asc','asc')
				//->order('n.id asc')
		        ->group('p.ID');
			  
			$stmt = $db->query($select);
			return  $stmt->fetchAll();
		}
		
		
		function gerarCmventradaprod($var){
		    $qtprod = 0;
		    $idprod = -1;
		    
		    $cor=0;
		    $idverqt="";
		    $auxqt=0;
		    $total_pecas = 0;
		    
		    $this->objProdutos		= ComprasBO::listaProdutosentgroup($var);
		    $this->objProddet		= ComprasBO::listaProdutosent($var);
		    $this->objEntrada		= ComprasBO::listaEntrada($var);

		    foreach ($this->objEntrada as $listent);
		    //-- percentual frete ------------------
		    foreach ($this->objProdutos as $listaprod):
			    $qtentreg = $qtpedid = 0;
			    foreach ($this->objProddet as $list):
				    if(($list->ID_PRODUTO == $listaprod->ID_PRODUTO) and (!empty($list->qt))):
				    $qtpedid  += $list->QUANTIDADE;
				    $qtentreg += $list->qt;
				    endif;
			    endforeach;
		    
			    $total_pedido += $listaprod->precoimp*$qtentreg;
		    endforeach;
		    
		    $aduaneiro = $total_pedido+$listent->frete+$listent->capatazia;
		    
		    $qtentreg = $qtpedid = $total_pedido = $totalcofins = $totalpis = $totalii = 0;
		    foreach ($this->objProdutos as $listaprod):
		     
			    $qtentreg = $qtpedid = 0;
			    foreach ($this->objProddet as $list):
				    if(($list->ID_PRODUTO == $listaprod->ID_PRODUTO) and (!empty($list->qt))):
					    //$pedidos.= '<a href="'.$this->baseUrl().'/admin/compras/pedidoscompraprod/ped/'.md5($list->ID_PEDIDO_COMPRA).'" target="_blank"><b>'.$list->ID_PEDIDO_COMPRA.'</b></a>/';
					     
					    $qtpedid  += $list->QUANTIDADE;
					    $qtentreg += $list->qt;
				    endif;
		    	endforeach;
		     
		    	$totalncm = 0;
			    foreach ($this->objProdutos as $pncm):
				    if(($pncm->id_ncm == $listaprod->id_ncm)):
				    	$totalncm += $pncm->qta*$pncm->precoimp;
				    endif;
			    endforeach;
		    
		    	$coeficiente = ($listaprod->aduaneiro/(round($totalncm,2)*$listaprod->txcambio));
		     
			    //-- Valores unitarios -------------------------------------------------------------------------
			    $unitario = $listaprod->precoimp*$coeficiente*$listaprod->txcambio;
			    $unitario = round($unitario,4);
			     
			    $iiunitatio = ($listaprod->pii*$unitario)/100;
			     
			    $unitarioii = ($listaprod->precoimp*$coeficiente*$listaprod->txcambio)+$iiunitatio;
			    $unitarioii = round($unitarioii,4);
			    //($totalunitario+$ii)/$qtentreg;
			     
			    //-- Total unitario --------------------------------------------------------------------
			    $totalunitario = $qtentreg*$unitario;
			    $totalunitario = round($totalunitario,4);
			    
			    $ii = ($listaprod->pii*$totalunitario)/100;
			    
			    $ipi = (((($listaprod->pii*$totalunitario)/100)+$totalunitario)*$listaprod->pipi)/100;
			     
			    $aicms 		= $listaprod->icms/100;
			    $aii 		= $listaprod->pii/100;
			    $aipi 		= $listaprod->pipi/100;
			    $apis 		= $listaprod->ppis/100;
			    $acofins 	= $listaprod->pcofins/100;
			    
			     
			    $pis = $apis*($totalunitario*(1+$aicms*($aii+$aipi*(1+$aii))))/((1-$apis-$acofins)*(1-$aicms));
			    $cofins = $acofins*($totalunitario*(1+$aicms*($aii+$aipi*(1+$aii))))/((1-$apis-$acofins)*(1-$aicms));
		     
			    $totalii 		+= $ii;
			    $totalpis 		+= $pis;
			    $totalcofins 	+= $cofins;
			    $totalipi       += $ipi;
			    $total_pedido 	+= $unitarioii*$qtentreg;
			    $total2			+= $totalunitario+$ii;
		    endforeach;
		    
		    echo "Coef = ".$coedicms = ($totalpis+$totalcofins+$listent->siscomex+$listent->outros)/($total_pedido+$totalipi);
		    echo " S: ".$listent->siscomex." O: ".$listent->outros;
		    echo "<br />";
		    
		    $qtentreg = $qtpedid = $total_pedido = $totalicms = 0;
		    foreach ($this->objProdutos as $listaprod):
			    $idprodutos .= $listaprod->ID_PRODUTO.";";
		    
			    //--- somar produtos iguais de pedidos diferentes ---------------------------
			    $pedidos = "";
			    $qtentreg = $qtpedid = 0;
			    foreach ($this->objProddet as $list):
				    if(($list->ID_PRODUTO == $listaprod->ID_PRODUTO) and (!empty($list->qt))):
				    	//$pedidos.= '<a href="'.$this->baseUrl().'/admin/compras/pedidoscompraprod/ped/'.md5($list->ID_PEDIDO_COMPRA).'" target="_blank"><b>'.$list->ID_PEDIDO_COMPRA.'</b></a>/';
				     
					    $qtpedid  += $list->QUANTIDADE;
					    $qtentreg += $list->qt;
				    endif;
			    endforeach;
		    
			    //--- Calculo dos impostos que sao exibidos ----------------------------------------------
			    $totalncm = 0;
			    foreach ($this->objProdutos as $pncm):
				    if(($pncm->id_ncm == $listaprod->id_ncm)):
				    	$totalncm += $pncm->qta*$pncm->precoimp;
				    endif;
			    endforeach;
		    
			    $coeficiente = ($listaprod->aduaneiro/(round($totalncm,2)*$listaprod->txcambio));
			    
			    //-- Valores unitarios -------------------------------------------------------------------------
			    $unitario = $listaprod->precoimp*$coeficiente*$listaprod->txcambio;
			    $unitario = round($unitario,4);
			     
			    $iiunitatio = ($listaprod->pii*$unitario)/100;
			     
			    $unitarioii = ($listaprod->precoimp*$coeficiente*$listaprod->txcambio)+$iiunitatio;
			    $unitarioii = round($unitarioii,4);
			    //($totalunitario+$ii)/$qtentreg;
			     
			    //-- Total unitario --------------------------------------------------------------------
			    $totalunitario = $qtentreg*$unitario;
			    $totalunitario = round($totalunitario,4);
			    
			    $ii = ($listaprod->pii*$totalunitario)/100;
			     
			     
			    $ipi = (((($listaprod->pii*$totalunitario)/100)+$totalunitario)*$listaprod->pipi)/100;
			     
			    $aicms 		= $listaprod->icms/100;
			    $aii 		= $listaprod->pii/100;
			    $aipi 		= $listaprod->pipi/100;
			    $apis 		= $listaprod->ppis/100;
			    $acofins 	= $listaprod->cofins/100;
			     
			    $pis = $apis*($totalunitario*(1+$aicms*($aii+$aipi*(1+$aii))))/((1-$apis-$acofins)*(1-$aicms));
			    $cofins = $acofins*($totalunitario*(1+$aicms*($aii+$aipi*(1+$aii))))/((1-$apis-$acofins)*(1-$aicms));
		    	
		    	echo $listaprod->CODIGO;
		    	echo "<br />";
    							
		    	$baseicms = 0;
    		    echo $baseicms = (($totalunitario+$ii)+$ipi+(($totalunitario+$ii)+$ipi) * $coedicms) / (1-$aicms);
    		    echo "<br />";
    		        		    
    		    $icms = ($aicms*$baseicms)/$qtentreg;
    		    echo "ICMS: ".$listaprod->icms." = ".$icms;
    		    echo "<br />";
    		    
    		    
    		    
    		    echo "ICMS Rec: ";
    		    echo $icms * 0.384;
    		    echo "<br />";
    		    
    			echo "II: ".$listaprod->pii." = ".$ii/$qtentreg;
    			echo "<br />";
    			echo "IPI: ".$listaprod->pipi." = ".$ipi/$qtentreg;
    			echo "<br />";
    			echo "PIS: ".$listaprod->ppis." = ".$pis/$qtentreg;
    			echo "<br />";
    			echo "COFINS: ".$listaprod->pcofins." = ".$cofins/$qtentreg;
    			echo "<br />";

    			echo "Unit: "; echo $unitario = $unitario - ($listaprod->preco*$listaprod->txcambio);
    			echo "<br />";
    			
    			echo "Total: ".(($ii/$qtentreg)+($icms * 0.384)+($ipi/$qtentreg)+($cofins/$qtentreg)+$unitario);
    			echo "<br />";
    			
    			
    			echo "<br />";
    			    					
    		endforeach;
		    
		}
		
		
		
		function geraProdutoscmv($var){
			$boe	= new EntradaestoqueModel();
			$bo		= new EntradaestoquecmvModel();
			/* Aqui, vou gerar os cmvs temporarios dos produtos */
			//-- listo produto a produto ---
			
			/* foreach (ComprasBO::listaProdutosentgroup($var) as $listentrada):
				$txcambio	= $listentrada->txcambio;
				$cont = 0;
				$precot = 0;
				 //--- Custo do produto --------------------------------------------------------------------
				//--- com esse loop verifico se existe o mesmo produto de pedidos diferentes --------------
				foreach (ComprasBO::listaProdutosent($var) as $listprod):
					//--- Produtos iguais -----------------------------------------------------------------
					if($listprod->ID_PRODUTO == $listentrada->ID_PRODUTO):
						$cont++;
						//-- Busco o valor total do pedido ------------------
						$total = $totalprod = 0;
						$ped['ped']	= md5($listprod->ID_PEDIDO_COMPRA);
						foreach (ComprasBO::listaEntregaped($ped) as $listqtent):
							$total += $listqtent->qtent*$listqtent->precocomp;
							if($listqtent->idprodcomp==$listprod->idprodped):
								$totalprod = $listqtent->qtent*$listqtent->precocomp;
							endif;
						endforeach;
						
						//-- Busco a porcentagem de cada pagamento -----------
						//-- e o quanto foi pago neste pedido ----------------
						 foreach (FinanceiroBO::listarParcelasapagarporpedido($listprod->ID_PEDIDO_COMPRA) as $fin):
							//-- Se parcela ja foi paga ------------------------------------
							if(!empty($fin->valor_pago)):
								$perc = ($fin->valor_pago*100)/$total;
								if(!empty($fin->txcambio)):
									$precot += (($perc*$totalprod)/100)*$fin->txcambio;
								else:
									$precot += (($perc*$totalprod)/100)*$txcambio;
								endif;						
							else:
							// -- se nao foi para ------------------------------------------
								$perc = ($fin->valor_apagar*100)/$total;
								$precot += (($perc*$totalprod)/100)*$txcambio;						
							endif;
							
						endforeach; 
										
					endif;
				endforeach;
				
				if(count($bo->fetchAll("id_produtos = ".$listentrada->ID_PRODUTO." and id_entradaztl = ".$listentrada->id_entradaztl))>0):
					foreach ($bo->fetchAll("id_produtos = ".$listentrada->ID_PRODUTO." and id_entradaztl = ".$listentrada->id_entradaztl) as $listcmvtmp);
					if(empty($listcmvtmp->data)):
						//--- Removo e gravo se o cmv for termporario ------------------------------------
						$bo->delete("id_produtos = ".$listentrada->ID_PRODUTO." and id_entradaztl = ".$listentrada->id_entradaztl);
						
						$array['valor']			= $precot/$listentrada->qta;
						$array['id_entradaztl']	= $listentrada->id_entradaztl;
						$array['id_produtos']	= $listentrada->ID_PRODUTO;
						
						$bo->insert($array);
					endif;
				else:
					//-- grava entrada cmv temporario --------------------------
					$array['valor']			= $precot/$listentrada->qta;
					$array['id_entradaztl']	= $listentrada->id_entradaztl;
					$array['id_produtos']	= $listentrada->ID_PRODUTO;
					
					$bo->insert($array);
				endif;
			endforeach;  */
			
			
			//----------- Impostos ------------------------------------------------
			foreach (ComprasBO::listaEntrada($var) as $listent);
			
        	$qtprod = 0;
        	$idprod = -1;
        	
        	$cor=0;
        	$idverqt="";
        	$auxqt=0;
        	$total_pecas = 0;
        	
        	//-- percentual frete para calculo do aduaneiro ------------------
        	foreach (ComprasBO::listaProdutosentgroup($var) as $listaprod):
        		$qtentreg = $qtpedid = 0;
	            foreach (ComprasBO::listaProdutosent($var) as $list):
	        		if(($list->ID_PRODUTO == $listaprod->ID_PRODUTO) and (!empty($list->qt))):
	        			$qtpedid  += $list->QUANTIDADE;
	        			$qtentreg += $list->qt;
	        		endif;
	        	endforeach;
	            $total_pedido += $listaprod->preco*$qtentreg;
        	endforeach;
        	
        	$aduaneiro = $total_pedido+$listent->frete+$listent->capatazia;
        	
        	$qtentreg = $qtpedid = $total_pedido = $totalcofins = $totalpis = $totalii = 0;
        	foreach (ComprasBO::listaProdutosentgroup($var) as $listaprod):        		
	            $qtentreg = $qtpedid = 0;
	            foreach (ComprasBO::listaProdutosent($var) as $list):
	        		if(($list->ID_PRODUTO == $listaprod->ID_PRODUTO) and (!empty($list->qt))):
	        			$qtpedid  += $list->QUANTIDADE;
	        			$qtentreg += $list->qt;
	        		endif;
	        	endforeach;
	               
                foreach (ComprasBO::listaProdutosentncm($var) as $pncm):
                	if($pncm->ncm == $listaprod->ncm):
                		$totalncm = $pncm->total;
                	endif;
                endforeach;
	                
                $coeficiente = ($listaprod->aduaneiro/(round($totalncm,2)*$listaprod->txcambio));
	                
                $totalunitario = $qtentreg*$listaprod->preco*$coeficiente*$listaprod->txcambio;
				
                $ii = ($listaprod->ii*$totalunitario)/100;
                
                $unitario = ($totalunitario+$ii)/$qtentreg;
                
                $ipi = (((($listaprod->ii*$totalunitario)/100)+$totalunitario)*$listaprod->ipi)/100;
                
                $aicms 		= $listaprod->icms/100;
                $aii 		= $listaprod->ii/100;
                $aipi 		= $listaprod->ipi/100;
                $apis 		= $listaprod->pis/100;
                $acofins 	= $listaprod->cofins/100;
                      
                $pis = $apis*($totalunitario*(1+$aicms*($aii+$aipi*(1+$aii))))/((1-$apis-$acofins)*(1-$aicms));
                
                $cofins = $acofins*($totalunitario*(1+$aicms*($aii+$aipi*(1+$aii))))/((1-$apis-$acofins)*(1-$aicms));

                $totalii 		+= $ii; 
                $totalpis 		+= $pis;
                $totalcofins 	+= $cofins;
                $totalipi       += $ipi;
                $total_pedido 	+= $totalunitario+$ii;
				
        	endforeach;
        	
        	$coedicms = ($totalpis+$totalcofins+$listent->siscomex)/($total_pedido+$totalipi);

        	
        	echo "<br />";
        	echo "<br />";
        	
        	$qtentreg = $qtpedid = $total_pedido = $totalicms = 0;
        	foreach (ComprasBO::listaProdutosentgroup($var) as $listaprod):
        		$cor++;
	            if(($cor%2)==0) $class = 'td_orc_par';
	            else $class = 'td_orc';
	            $idprodutos .= $listaprod->ID_PRODUTO.";";
	            
	            //--- somar produtos iguais de pedidos diferentes ---------------------------
	            $pedidos = "";
	            $qtentreg = $qtpedid = 0;
	            foreach (ComprasBO::listaProdutosent($var) as $list):
	        		if(($list->ID_PRODUTO == $listaprod->ID_PRODUTO) and (!empty($list->qt))):
	        			$qtpedid  += $list->QUANTIDADE;
	        			$qtentreg += $list->qt;
	        		endif;
	        	endforeach;
	                   
                foreach (ComprasBO::listaProdutosentncm($var) as $pncm):
                	if($pncm->ncm == $listaprod->ncm):
                		$totalncm = $pncm->total;
                	endif;
                endforeach;
                
                $coeficiente = ($listaprod->aduaneiro/(round($totalncm,2)*$listaprod->txcambio));
                
                echo $listaprod->CODIGO;
              //  echo "<br />Preco dolar:".$listaprod->preco;
                                
                
                $totalunitario = $qtentreg*$listaprod->preco*$coeficiente*$listaprod->txcambio;
				
                $ii = ($listaprod->ii*$totalunitario)/100;
                
               // echo "<br />Preco Unit:".$listaprod->preco*$listaprod->txcambio;
                
                $precotxcambio = 0;
                $precotxcambio = $listaprod->preco*$listaprod->txcambio;
                
                $valorcoeficiente = 0;
                $valorcoeficiente = (($listaprod->preco*$coeficiente)*$listaprod->txcambio) - ($listaprod->preco*$listaprod->txcambio);
                
               // echo "<br />Valor Coeficiente: "; echo $valorcoeficiente;
                
                
                $unitario = ($totalunitario+$ii)/$qtentreg;
                
               /*  echo "<br />Coeficiente:".($listaprod->preco*$coeficiente)*$listaprod->txcambio;
                echo "<br />Coef Puro:".$coeficiente;
                
                echo "<br />II:".$ii/$qtentreg;
                
                echo "<br />"; */
                
                $ipi = (((($listaprod->ii*$totalunitario)/100)+$totalunitario)*$listaprod->ipi)/100;
                
                $aicms 		= $listaprod->icms/100;
                $aii 		= $listaprod->ii/100;
                $aipi 		= $listaprod->ipi/100;
                $apis 		= $listaprod->pis/100;
                $acofins 	= $listaprod->cofins/100;
                      
                $pis = $apis*($totalunitario*(1+$aicms*($aii+$aipi*(1+$aii))))/((1-$apis-$acofins)*(1-$aicms));
                $cofins = $acofins*($totalunitario*(1+$aicms*($aii+$aipi*(1+$aii))))/((1-$apis-$acofins)*(1-$aicms));
	            
                $baseicms = 0;
                $baseicms = (($totalunitario+$ii)+$ipi+(($totalunitario+$ii)+$ipi)* $coedicms) / (1-$aicms);	                   
                   
                $totalicms += $aicms*$baseicms; 
	            
                $icmsuni	= (($aicms*$baseicms)*40)/100;
	                
	            $total_pedido += $totalunitario+$ii;
				$total_pecas  += $qtentreg;
								
				//echo "<br>".$valorunitario = ($totalunitario+$ii+$ipi+$cofins+$pis+$icmsuni)/$qtentreg;
				$bo->delete("id_entradaztl	= ".$listaprod->id_entradaztl." and id_produtos = ".$listaprod->ID_PRODUTO);
				
				if(count($bo->fetchAll("id_produtos = ".$listaprod->ID_PRODUTO." and id_entradaztl = ".$listaprod->id_entradaztl))>0):
					foreach ($bo->fetchAll("id_produtos = ".$listaprod->ID_PRODUTO." and id_entradaztl = ".$listaprod->id_entradaztl) as $listcmvtmp);
					if(empty($listcmvtmp->data)):
						//--- Removo e gravo se o cmv for termporario ------------------------------------
						//$bo->delete("id_produtos = ".$listentrada->ID_PRODUTO." and id_entradaztl = ".$listentrada->id_entradaztl);
							
						$valorunitario 		= (($listcmvtmp->valor*$qtentreg*$coeficiente)+$ii+$ipi+$cofins+$pis+$icmsuni)/$qtentreg;
						$arraytmp['valor']		= $valorunitario;
						
						$bo->update($arraytmp, "id = ".$listcmvtmp->id);
					endif;
				else:
					//-- grava entrada cmv temporario --------------------------
					$valor = ($totalunitario+$ii+$ipi+$cofins+$pis+$icmsuni)/$qtentreg;
					echo " - ".$valor." - ".$precotxcambio."<br />";
				
					$valor = $valor - $precotxcambio;
				
					
					
					
					$array['valor']			= $valor;
					//$array['valor']			= (($ii+$ipi+$cofins+$pis+$icmsuni)/$qtentreg) + $valorcoeficiente;
					$array['id_entradaztl']	= $listaprod->id_entradaztl;
					$array['id_produtos']	= $listaprod->ID_PRODUTO;					
					$bo->insert($array);
				endif;

				
				
				//echo "TUNIT: ".$totalunitario." - II:".$ii." - IPI:".$ipi." - COF:".$cofins." - PIS:".$pis." - ICMS:".$icmsuni." - QT:".$qtentreg."<br />";
				
        	endforeach;
			
		}		
		
		function listaProdutosentcmv($var){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('c'=>'produtos_pedidos_compra'), array('e.id_entradaztl as identrada','e.id as ident', 'sum(e.qt) as qta','p.CODIGO','p.ID as idprod','v.valor','c.ID_PEDIDO_COMPRA'))
			        ->join(array('p'=>'produtos'),'p.ID = c.ID_PRODUTO')
			        ->join(array('e'=>'tb_entradaztl_prod'),'e.id_prodped = c.ID')
			        ->joinLeft(array('v'=>'tb_produtosentcmv'),'v.id_produtos = c.ID_PRODUTO and v.id_entradaztl= e.id_entradaztl')			        
			        ->where("md5(e.id_entradaztl) = '".$var['entrada']."'")
			        ->order('e.id asc','asc')
					//->order('p.id_ncm asc','asc')
			        ->group('p.ID');
			  
			$stmt = $db->query($select);
			return  $stmt->fetchAll();
		}
		
		function gravarCmventrada($var){
			$boe	= new EntradaestoqueModel();
			$bo		= new EntradaestoquecmvModel();	
			
			foreach (ComprasBO::listaProdutosentcmv($var) as $list):
				if(!empty($var['cmv_'.$list->idprod])):
					$bo->delete("id_produtos = ".$list->idprod." and id_entradaztl = ".$list->identrada);
					
					//-- grava entrada cmv --------------------------
					$precoprod = str_replace(",",".", str_replace(".","",$var['cmv_'.$list->idprod]));
					$array['valor']			= $precoprod;
					$array['data']			= date("Y-m-d H:i:s");
					$array['id_entradaztl']	= $list->identrada;
					$array['id_produtos']	= $list->idprod;
					$array['sit']			= 0;
					
					$bo->insert($array);
					
				endif;
			endforeach;
		}
		
		function geraCmvestoque($var){
			$boe	= new EntradaestoqueModel();
			$bo		= new EntradaestoquecmvModel();	
			$bop	= new ProdutosModel();
			$boc	= new ProdutoscmvModel();
			$boa	= new AjustestoqueModel();
			$boap	= new AjustestoqueprodModel();
			$boqt	= new EntradaestoqueqtatualModel();
			
			foreach (ComprasBO::listaProdutosentcmv($var) as $list):
							
				if(!empty($var['cmv_'.$list->idprod])):
					//-- grava entrada cmv --------------------------
					$bo->delete("id_produtos = ".$list->idprod." and id_entradaztl = ".$list->identrada);
					
					$precoprod = str_replace(",",".", str_replace(".","",$var['cmv_'.$list->idprod]));
					$array['valor']			= $precoprod;
					$array['data']			= date("Y-m-d H:i:s");
					$array['id_entradaztl']	= $list->identrada;
					$array['id_produtos']	= $list->idprod;
					$bo->insert($array);
					
					//-- calcula entrada cmv no produto --------------------------
					//-- busca ultimo cmv cadastrado -----------------------------
					
					$vlcmv = 0;
					$qtatual = 0;
					$qtatualest = 0;
					
					if(count($boc->fetchAll("id = (select max(v.id) from tb_produtoscmv v where v.id_produtos = ".$list->idprod.")"))>0):
						foreach ($boc->fetchAll("id = (select max(v.id) from tb_produtoscmv v where v.id_produtos = ".$list->idprod.")") as $listacmv);
						
						$vlcmv	= $listacmv->valor;
						
						foreach ($boqt->fetchAll("id_entradaztl = ".$list->identrada." and id_produtos = ".$list->idprod) as $qtprod);
						if($qtprod->qtatual>0):	
							$qtatual 	= $qtprod->qtatual;
							$qtatualest = $qtprod->qtatual;
						else:
							$qtatual = 0;
						endif;		
						$qtatualest = $qtprod->qtatual;
						
					endif;
					
					if(($vlcmv==0) || (($vlcmv!=0) and ($qtatual<=0))):	
						$totalvl = $precoprod;						
					else:
						$totalvl = (($list->qta*$precoprod) + ($vlcmv*$qtatual)) / ($qtatual+$list->qta);					
					endif;
										
					$arraycmv['valor'] 			= $totalvl;
					$arraycmv['data'] 			= date("Y-m-d H:i:s");
					$arraycmv['id_produtos'] 	= $list->idprod;
					$arraycmv['valorant'] 		= $vlcmv;
					$arraycmv['qtant'] 			= $qtatualest;
					
					$boc->insert($arraycmv);
					
				endif;
			endforeach;
			
			foreach ($boe->fetchAll("md5(id) = '".$var['entrada']."'") as $entrada);
			foreach ($bo->fetchAll('data is NULL and md5(id_entradaztl) = "'.$var['entrada'].'"') as $listentrada);
			
			if(empty($listentrada) and $entrada->status==2):
				$arrayent['status']	= 3;
			elseif(empty($listentrada) and $entrada->status==1):
				$arrayent['status']	= 4;
			endif;
			
			$arrayent['bloq']	= 1;
			$boe->update($arrayent, "md5(id) = '".$var['entrada']."'");
		}
		
		function buscaAjusteprodentrada($prod,$ent){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('a'=>'tb_ajustestoqueztl'), array('*'))
			        ->join(array('ap'=>'tb_ajustestoqueztl_prod'),'ap.id_ajuste = a.id')
			        ->where("a.id_entradaztl = ".$ent." and ap.id_prod = ".$prod);
			  
			$stmt = $db->query($select);
			return  $stmt->fetchAll();
		}
		
		
		function listaProdentcmvout($var,$ent){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('c'=>'produtos_pedidos_compra'), array('sum(e.qt) as qta'))
			        ->join(array('e'=>'tb_entradaztl_prod'),'e.id_prodped = c.ID')			        		        
			        ->join(array('ez'=>'tb_entradaztl'),'ez.id = e.id_entradaztl')
			        ->join(array('p'=>'tb_produtosentcmv'),'ez.id = p.id_entradaztl and p.id_produtos = c.ID_PRODUTO')
			        ->where("p.data is not NULL and ez.status = 1 and c.ID_PRODUTO = ".$var." and e.id_entradaztl != ".$ent)
			        ->group('e.id_entradaztl')
			        ->order('e.id_entradaztl desc');
			  
			$stmt = $db->query($select);
			return  $stmt->fetchAll();
		}		
		
		function listaProdentcmv($var,$ent){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('c'=>'produtos_pedidos_compra'), array('sum(e.qt) as qta'))
			        ->join(array('e'=>'tb_entradaztl_prod'),'e.id_prodped = c.ID')			        		        
			        ->join(array('ez'=>'tb_entradaztl'),'ez.id = e.id_entradaztl')
			        ->join(array('p'=>'tb_produtosentcmv'),'ez.id = p.id_entradaztl and p.id_produtos = c.ID_PRODUTO')
			        ->where("ez.status = 1 and c.ID_PRODUTO = ".$var." and e.id_entradaztl = ".$ent)
			        ->group('e.id_entradaztl')
			        ->order('e.id_entradaztl desc');
			  
			$stmt = $db->query($select);
			return  $stmt->fetchAll();
		}
		
		//-- Lista produtos das entradas agrupados por NCM -------------------------------------------
		/*-- Usado em entradaprodAction -------------------------*/
		
		/* function listaProdutosentncm($var){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('e'=>'tb_entradaztl_prod','*'), array('e.ncm','sum(e.qt * e.preco) as total'))
				
		        ->where("md5(e.id_entradaztl) = '".$var['entrada']."'")
		        ->group('e.ncm');
			  
			$stmt = $db->query($select);
			return  $stmt->fetchAll();
		} */
		
		
		function listaProdutosentncm($var){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
			$select = $db->select();
				
			$select->from(array('e'=>'tb_entradaztl_prod','*'), array('e.ncm','sum(e.qt * e.preco) as total','e.numadicao','e.aduaneiro'))
				->where("md5(e.id_entradaztl) = '".$var['entrada']."'")
				->group('e.ncm')
				->order('e.numadicao');
				
			$stmt = $db->query($select);
			return  $stmt->fetchAll(); 
	    			    
		} 
		
		
		function atualizaOrdemprodutos($params){
		    $bo		= new EntradaestoqueModel();
		    $bop	= new EntradaestoqueprodModel();
		    
			$updateRecordsArray 	= $params['recordsArray'];
				
			$listingCounter = 1;
			foreach ($updateRecordsArray as $recordIDValue) {
				$data = array('numsequencia'	=> 	$listingCounter);
		
				foreach (ComprasBO::listaProdutosent($params) as $produtos):
					if($produtos->ID_PRODUTO == $recordIDValue):										
						$bop->update($data, "id = ".$produtos->ident);
					endif;
				endforeach;
				$listingCounter = $listingCounter + 1;
			}
		}
		
		//-- Importar ped importado -----------------------------------------------
		function importacaoPedido($var){
			$boe	= new EntradaestoqueModel();
			$bo		= new EntradaestoquetmpModel();
			$boc	= new ZtlcomprasModel();
			$bocp	= new ZtlcomprasprodModel();
			
         	$arquivo = isset($_FILES['arquivo']) ? $_FILES['arquivo'] : FALSE;
	        $pasta = Zend_Registry::get('pastaPadrao')."public/sistema/upload/compras/";
			 				 
			if (!(is_dir($pasta))){
				if(!(mkdir($pasta, 0777))){
                   	echo ("Alerta: pasta de upload nao existe, e nao pode ser criada");
                	return $this;                           
                 }
            }
                   
            if(!(is_writable($pasta))){
             	echo ("Alerta: pasta sem permissao de escrita");
                return $this;                   
            }
			 				 
			if(is_uploaded_file($arquivo['tmp_name'])){                                
                  if (move_uploaded_file($arquivo["tmp_name"], $pasta . "pedtmp.xml")) {
                  		
                  } else {
                        echo ("Alerta: Nao foi possivel fazer o upload para $pasta");
                        return $this;                                           
                  }                               
            }else{
	             //echo "erro ao carregar arquivo";
            }
	         
            
			$xml = simplexml_load_file($pasta . "/pedtmp.xml");
			
			$bo->delete();
			
			$verprod = 0;
			foreach ($xml as $listimp):
				foreach($listimp->produto as $lp):										
					
					if(count($bocp->fetchAll("ID_PEDIDO_COMPRA = '".$lp->pedido."' and ID_PRODUTO = '".$lp->idprod."'")) > 0):
						Zend_Debug::dump($lp);
						foreach ($bocp->fetchAll("ID_PEDIDO_COMPRA = '".$lp->pedido."' and ID_PRODUTO = '".$lp->idprod."'") as $produtos);
						$array['id_prodped']	= $produtos->ID;
						$array['preco']			= $lp->preco;
						$array['qt']			= $lp->qt;						
						$bo->insert($array);
					else:
						$verprod = 1;
					endif; 
					
		   		endforeach;  
		   		
		    endforeach;	
		    
		    return $verprod;
			 
		}
		
		function lerimportacaoPed(){
			$xml = simplexml_load_file(Zend_Registry::get('pastaPadrao')."public/sistema/upload/compras/pedtmp.xml");
			return $xml;
		}
		
		
		//-- Importar ped nacional ---------------------------------
		/* function importacaoPedidonac($var){
			$boe	= new EntradaestoqueModel();
			$bo		= new EntradaestoquetmpModel();
			$bof	= new EntradaestoqueempresatmpModel();
			$boc	= new ZtlcomprasModel();
			$bocp	= new ZtlcomprasprodModel();
			
			$bo->delete("");
			
			foreach ($bof->fetchAll() as $empresa);
			
			
         	 $arquivo = isset($_FILES['arquivo']) ? $_FILES['arquivo'] : FALSE;
	         $pasta = Zend_Registry::get('pastaPadrao')."/public/importped/";

	         	         
			 if (!(is_dir($pasta))){
				if(!(mkdir($pasta, 0777))){
                   	echo ("Alerta: pasta de upload nao existe, e nao pode ser criada");
                	return $this;                           
                 }
             }
                   
             if(!(is_writable($pasta))){
             	echo ("Alerta: pasta sem permissao de escrita");
                return $this;                   
             }
			 				 
			 if(is_uploaded_file($arquivo['tmp_name'])){                                
                  if (move_uploaded_file($arquivo["tmp_name"], $pasta . "pedtmp.xml")) {
                  		
                  } else {
                        echo ("Alerta: Nao foi possivel fazer o upload para $pasta");
                        return $this;                                           
                  }                               
             }else{
	             //echo "erro ao carregar arquivo";
             }
	         
            
			$xml = simplexml_load_file(Zend_Registry::get('pastaPadrao')."/public/importped/pedtmp.xml");
			
			
			
			foreach ($xml as $listnfe):
				foreach ($listnfe->infNFe as $listinfo):
					foreach ($listinfo->det as $listprod):
						foreach ($listprod->prod as $produtos);
							foreach (ProdutosBO::buscaProdutoscodigo($produtos->cProd) as $produtosbusc);
														
							$arraypesq['forn']		= $empresa->id_fornecedor;
							$arraypesq['idprod']	= $produtosbusc->ID;
							
							$array['ncm']			= substr($produtos->NCM,0,4).".".substr($produtos->NCM,4);
							$array['cfop']			= $produtos->CFOP;
							
							foreach ($listprod->imposto as $produtosimp):
		                		foreach ($produtosimp->ICMS as $icms):
		                			foreach ($icms->ICMS10 as $icms00):	
		                				$array['cst'] 	= $icms00->orig.$icms00->CST;
		                				$array['icms'] 	= $icms00->pICMS;
		                			endforeach;
		                		endforeach;
		                		foreach ($produtosimp->IPI as $ipi):
		                			foreach ($ipi->IPITrib as $ipitrib):	
		                				$array['ipi'] = $ipitrib->pIPI; 
		                			endforeach;
		                		endforeach;
		                	endforeach;
		               	
							if(count(ComprasBO::buscaProdutospedidosfor($arraypesq)) > 1):
								//--Se for varios produtos pedidos -----------------------------
								foreach (ComprasBO::buscaProdutospedidosfor($arraypesq) as $listprod):
									$array['id_prodped']	= $listprod->idp;
									$array['preco']			= $produtos->vUnCom;									
									$bo->insert($array);													
								endforeach;				
								$erro = 3;	
							
							elseif(count(ComprasBO::buscaProdutospedidosfor($arraypesq)) == 1):
								//--Se for apenas 1 produto pedido -----------------------------
								$params = array ('host' => '127.0.0.1',	'username' => 'ztlrolamentos', 'password' => 'BdMySql2008', 'dbname'   => 'ztlrolamentos');
								$db = Zend_Db::factory('PDO_MYSQL', $params);
								$db->setFetchMode(Zend_Db::FETCH_OBJ);
								$select = $db->select();
								
								$select->from(array('c'=>'produtos_pedidos_compra','*'), array('*','c.ID as idprodped'))
								        ->joinLeft(array('e'=>'tb_entradaztl_prod'),'e.id_prodped = c.ID')
								        ->where("c.ID_PRODUTO = ".$produtosbusc->ID." and c.sit = false")
								        ->order('c.id asc','asc');
								  
								$stmt = $db->query($select);
								
								foreach ($stmt->fetchAll() as $listprod):
									$qtent += $listprod->qt;
									$qtped = $listprod->QUANTIDADE;
								endforeach;
								
								$array['id_prodped']	= $listprod->idprodped;
								$array['preco']			= $produtos->vUnCom;
								$ident = $bo->insert($array);					
								
							else:
								$erro = 1;
							endif;							
							
						//endforeach;
						
					endforeach;
				endforeach;
			endforeach;			
			
			return $erro;
			 
		}
		
		//-- ler ncm da importacao de xml nacional -------------------------------------
		function lerimportacaoNcmpednac(){
			$bot 	= new TributosModel();
			$bo		= new NcmModel();
			
			$xml = simplexml_load_file(Zend_Registry::get('pastaPadrao')."/public/importped/pedtmp.xml");
			foreach ($xml as $listnfe):
				foreach ($listnfe->infNFe as $listinfo):
					foreach ($listinfo->det as $listprod):
						foreach ($listprod->prod as $produtos):
							$ncm .= substr($produtos->NCM,0,4).".".substr($produtos->NCM,4).",";
						endforeach;
					endforeach;
				endforeach;
			endforeach;
			
			$ncm = substr($ncm, 0,-1);
			
			return $bo->fetchAll("ncm in (".$ncm.")");
			
		} */
		
		/*function importCmv(){
			$bop	= new ProdutosModel();
			$bo		= new ProdutoscmvModel();	
			
			$params = array ('host' => '127.0.0.1',	'username' => 'ztlrolamentos', 'password' => 'BdMySql2008', 'dbname'   => 'ztlrolamentos');
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			
			$select->from(array('s'=>'Custo_estoque','*'), array('*'))
			        ->join(array('p'=>'produtos'),'p.CODIGO = s.A');
			  
			$stmt = $db->query($select);
			
			foreach ($stmt->fetchAll() as $listprod):
				echo $listprod->ID." ".$listprod->CODIGO." ".$listprod->B."<br>";
				$array['valor']			= $listprod->B;
				$array['data']			= date("Y-m-d");
				$array['id_produtos']	= $listprod->ID;
				$array['valorant']		= 0;
				$array['qtant']			= 0;
				$bo->insert($array);				
			endforeach;
			
		}*/
		
		/*function procurarCmv(){
			$bop	= new ProdutosModel();
			$bo		= new ProdutoscmvModel();	
			
			$params = array ('host' => '127.0.0.1',	'username' => 'ztlrolamentos', 'password' => 'BdMySql2008', 'dbname'   => 'ztlrolamentos');
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			
			$select->from(array('p'=>'produtos','*'), array('*'))
			        ->join(array('z'=>'tb_estoqueztl'),'p.ID = z.id_prod and z.id = (select max(id) from tb_estoqueztl zv where z.id_prod = zv.id_prod)')
			        ->where("z.qt_atual > 0");
			  
			$stmt = $db->query($select);
			
			foreach ($stmt->fetchAll() as $listprod):
				foreach ($bo->fetchAll("id_produtos = ".$listprod->ID) as $prod);
				if(!empty($prod)):
					if($listprod->ID==1089): 
						echo "1089<br>";
						Zend_Debug::dump($prod);
					endif;
				else:
					
					echo $listprod->CODIGO;
					echo "<br>";
					Zend_Debug::dump($prod);
				endif;
			endforeach;
			
		}*/
		
		function gerarEntradancm(){
			$boe = new EntradaestoqueModel();
			$bo  = new EntradaestoquetmpModel();
			
			//-- listo todos os produtos agrupados por Produto -------------------------------------
			$j=0;
			foreach (ComprasBO::listaProdutosenttmpgroup() as $produtos):
				echo "<br>".$produtos->CODIGO." - "; 			
				//-- listo a importacao dos produtos -------------------------------------
				foreach (ComprasBO::lerimportacaoPed() as $import):
					$packt = "";
					//-- busco os packs dos Produtos -------------------------------------
			   		foreach($import->packsprod as $packsprod): 
			   			if($packsprod->idprod==$produtos->ID_PRODUTO): 
							$packt .= $packsprod->idpack.";";
			   			endif; 
			   		endforeach;
			   		
			   		//-- busco pallets com mais de um produto distinto ----------------------------
			   		$pack   	= explode(";", substr($packt,0,-1));
			   		$idpackrem 	= $packt;
			   		$idpacks = "";
			   		for($i=0; $i<sizeof($pack); $i++): 
			   			foreach($import->packsprod as $packsprod):
			   				if(($packsprod->idpack==$pack[$i]) and ($packsprod->idprod!=$produtos->ID_PRODUTO)):
			   					$idpackrem = str_replace($packsprod->idpack.";", "", $idpackrem);
			   					$idpacks 	.=  $packsprod->idpack.";";															   					
			   				endif;
			   			endforeach;
			   		endfor;
					
			   	endforeach;	
				
			   	//-- Gravo qt de pallets com produtos unicos -------------------------------------
				$idpackrem 	= array_unique(explode(";", substr($idpackrem,0,-1)));
				
				if(!empty($idpackrem[0])): 
					$pesoliquido = 0;
					foreach (ComprasBO::lerimportacaoPed() as $importQt):
						foreach($importQt->packsprod as $lprodpack):
				   			if($lprodpack->idpack==$idpackrem[0]):
								
								foreach (ComprasBO::lerimportacaoPed() as $importQtpeso):
									foreach($importQtpeso->packs as $lpacks):		
						   				if($idpackrem[0]==$lpacks->idpack):
							   				$pesoliquido = $lpacks->pliquido;				   				
							   			endif;
							   		endforeach;					   		
								endforeach;
								
								$arraype['pesounit'] = $pesoliquido/$lprodpack->qt; 
				
								foreach (ComprasBO::listaProdutostmpgeral() as $lpeso):
									if($lpeso->ID_PRODUTO == $produtos->ID_PRODUTO):
										$bo->update($arraype, "id = ".$lpeso->ident);
									endif;
								endforeach;
								
							endif;
				   		endforeach; 
					endforeach;
				else:
					foreach (ComprasBO::buscaProdutostmp($produtos->ID_PRODUTO) as $listpeso);	
									
					if(empty($listpeso->pesounit)):				
						$idpacks 	= array_unique(explode(";", substr($idpacks,0,-1)));
						
						//-- pego valor total dos pesos do pallet --------------------------
						$qtbrutoGer = $qtliquidoGer = 0;
						
						foreach (ComprasBO::lerimportacaoPed() as $importQt):
							foreach($importQt->packs as $lpacks):							   				
								if($idpacks[0]==$lpacks->idpack):
					   				$qtbrutoGer   = $lpacks->pbruto; 
					   				$qtliquidoGer = $lpacks->pliquido;		
					   			endif;
					   		endforeach;					   		
						endforeach;						
						
						//-- verifico se algum produto ja tem peso definido ---------------------
						
						echo "<br>";
						
						foreach($importQt->packsprod as $lprodpack):
							$precototal = $precopunico = $qtunico = $qtliquidoGert = 0;
							if($lprodpack->idpack==$idpacks[0]):	
								foreach (ComprasBO::lerimportacaoPed() as $importQtt):
									foreach($importQtt->packs as $lpacks):							   				
										if($idpacks[0]==$lpacks->idpack):
							   				$qtbrutoGer   = $lpacks->pbruto; 
							   				$qtliquidoGer = $lpacks->pliquido;		
							   			endif;
							   		endforeach;					   		
								endforeach;	
															
								foreach (ComprasBO::lerimportacaoPed() as $importQttotal):
									foreach($importQttotal->packsprod as $lprodtotal):
										if($lprodtotal->idpack==$idpacks[0]):
											foreach (ComprasBO::buscaProdutostmp($lprodtotal->idprod) as $listpeso);					
											
											if(empty($listpeso->pesounit)):
												$precototal += $lprodtotal->qt*$listpeso->preco;
											else:
												$qtliquidoGer = $qtliquidoGer-($listpeso->pesounit*$lprodtotal->qt);												
											endif;											
										
										endif;										
									endforeach;
								endforeach;
								
				   				foreach (ComprasBO::buscaProdutostmp($lprodpack->idprod) as $listpeso);
				   				if(empty($listpeso->pesounit)):
									$precopunico = $lprodpack->qt*$listpeso->preco;
									$qtunico	= $lprodpack->qt;
									
									$qtperc = 0;
									$qtperc = ($qtliquidoGer*(($precopunico*100)/$precototal))/100;
									
									$arraype['pesounit'] = "";
									$arraype['pesounit'] = $qtperc/$qtunico;
									
									$bo->update($arraype, "id = ".$listpeso->ident);												
																												
								endif;										
				   			endif;
						endforeach;
						
					endif;			
		   		endif;
				
			endforeach;		
		
		}
		
		function listaProdutostmpgeral(){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('c'=>'produtos_pedidos_compra','*'), array('*','c.ID as idprodped','e.id as ident'))
			        ->join(array('e'=>'tb_entradaprodtmp'),'e.id_prodped = c.ID')
			        ->where("c.sit = false");
			  
			$stmt = $db->query($select);
			return  $stmt->fetchAll();
		}
		
		function buscaProdutostmp($var){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('c'=>'produtos_pedidos_compra','*'), array('c.*','e.*','e.id as ident','p.CODIGO'))
			        ->join(array('e'=>'tb_entradaprodtmp'),'e.id_prodped = c.ID')
			        ->join(array('p'=>'produtos'),'c.ID_PRODUTO = p.ID')
			        ->where("c.sit = false and c.ID_PRODUTO = ".$var);
			  
			$stmt = $db->query($select);
			return  $stmt->fetchAll();
		}
		
		function buscaEmpresatmp(){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('e'=>'tb_entradatmp','*'), array('t.*'))
			        ->join(array('c'=>'clientes'),'c.ID = e.id_fornecedor')
			        ->join(array('t'=>'tb_tributosfiscais'),'t.id = c.id_despesasfiscais');
			  
			$stmt = $db->query($select);
			return  $stmt->fetchAll();
		}
		
	
		function buscaProdutospedidosfor($pesq){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('c'=>'pedidos_compra'),
			        array('c.ID as idped','pc.ID as idp','pc.QUANTIDADE','pc.PRECO_UNITARIO_USD as preco','pc.moeda as moedap'))
					->join(array('pc'=>'produtos_pedidos_compra'),'pc.ID_PEDIDO_COMPRA = c.ID and pc.sit = 0')
			        ->where("c.id_for = ".$pesq['forn']." and pc.ID_PRODUTO = ".$pesq['idprod']);
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();				
		}
	
		function gravarEntradaestoque($var){
			$boe	= new EntradaestoqueModel();
			$boest	= new EstoqueModel();
			$bocmv	= new EntradaestoquecmvModel();
			$boqt	= new EntradaestoqueqtatualModel();
			
			$usuario = Zend_Auth::getInstance()->getIdentity();
			
			//--Grava produtos no estoque-------------------------------			
			foreach (ComprasBO::listaProdutosentgroup($var)as $lista):
				$qt_atual = "";
				foreach ($boest->fetchAll('id_prod = '.$lista->ID_PRODUTO,"id desc",1) as $qt_atual);
				$qtatual = 0;
				if(!empty($qt_atual->qt_atual)):
					$qtatual = $qt_atual->qt_atual+$lista->qta;
				else:
					$qtatual = $lista->qta;
				endif;

				//---- Registro o estoque antes da insercao dos novos produtos -----------------------------
				$arrayqt['qtatual']			=  $qt_atual->qt_atual;
				$arrayqt['id_entradaztl']	=  $lista->id_entradaztl;
				$arrayqt['id_produtos']		=  $lista->ID_PRODUTO;
				$boqt->insert($arrayqt);
				
				//---- Insiro os produtos no estoque -------------------------------------------------------
				$arraye['id_prod'] 			= $lista->ID_PRODUTO;
				$arraye['qt_atual'] 		= $qtatual;
				$arraye['qt_atualizacao'] 	= $lista->qta;
				$arraye['id_atualizacao'] 	= $lista->id_entradaztl;
				$arraye['dt_atualizacao'] 	= date("Y-m-d H:i:s");
				$arraye['tipo'] 			= "COMPRA";
				$arraye['id_user'] 			= $usuario->id;
				
				$boest->insert($arraye);
			endforeach;
			
			//---- Marco entrada com  Produtos Entregues ------------------------
			
			if(!empty($var['nfe'])):
				$arrayent = array (
					'status'	=> 2,
				    'id_nfe'	=> $var['nfe']
				);
			else:
				$arrayent = array (
					'status'	=> 2
				);
			endif;
			
			/* foreach ($bocmv->fetchAll('data is NULL and md5(id_entradaztl) = "'.$var['entrada'].'"') as $listentrada);
			if(count($listentrada)<=0):
				$arrayent['status']	= 3;
			endif; */
			
			$boe->update($arrayent, "md5(id) = '".$var['entrada']."'");
			
			echo "sucessobaixa";
		}

		
		function gravarEntradasemestoque($var){
			$boe	= new EntradaestoqueModel();
			
			//---- Marco entrada com  Produtos Entregues ------------------------
				
			if(!empty($var['nfe'])):
				$arrayent = array (
					'status'	=> 1,
					'id_nfe'	=> $var['nfe']
				);
			else:
				$arrayent = array (
					'status'	=> 1
				);
			endif;
								
			$boe->update($arrayent, "md5(id) = '".$var['entrada']."'");
				
			echo "sucessobaixa";
		}
		
		//--- Aviso pedidos atrasados ---------------------------------
		function alertaPedidosatrasados(){
			date_default_timezone_set('America/Sao_Paulo');
			
			$var['tipostatus'] 	= "ATRASO";
			$var['tipo']		= 3;
			
			$var2['tipostatus'] = "ANDAMENTO";
			$var2['tipo']		= 3;
			$var2['dtini'] 		= date("d-m-Y");
			$var2['dtfim'] 		= date("d-m-Y",strtotime("+30 days"));
			
			if((count(ComprasBO::listaPedidos($var)) > 0) || (count(ComprasBO::listaPedidos($var2)) > 0)):
			
			$texto = '			
			<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
			<title>ZTL Brasil - www.ztlbrasil.com.br </title>
			<link href="http://www.ztlbrasil.com.br/public/sistema/imagens/ztl.ico" rel="shortcut icon" />
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			</head>
			<body>
			<table align="center" width="600px" cellspacing="0" cellpadding="0" style="border-collapse: collapse; border: 1px solid #333; font: 11px Arial, Helvetica, sans-serif;" >';
		     	
			if(count(ComprasBO::listaPedidos($var)) > 0) :
				$texto .= '				
		        <tr>
		            <td align="left" style="padding: 6px; color: #FF0000">
		                <b>PEDIDOS EM ATRAZO</b>
		            </td>
		    	</tr>
		    	<tr>
		    		<td>		    		
						<table align="center" width="600px" cellspacing="0" cellpadding="2" >
					        <tr>
					            <th  align="center" width="10%" style="border-top: 1px solid #333;" >
					                ID
					            </th>
					            <th align="center" width="50%" style="border-left: 1px solid #333; border-top: 1px solid #333;" >
					                FORNECEDOR
					            </th>
					            <th align="center" width="20%" style="border-left: 1px solid #333; border-top: 1px solid #333;" >
					            	DATA ENTREGA 
					            </th>
					            <th align="center" width="10%" style="border-left: 1px solid #333; border-top: 1px solid #333;" >
					                VISUALIZAR
					            </th>
					        </tr>';	
							
				
							foreach (ComprasBO::listaPedidos($var) as $lista):
								$texto .= '			
								<tr >
					                <td align="center" style="border-top: 1px solid #333;" >
					                   '.$lista->idped.'
					                </td>
					                <td align="left" style="border-left: 1px solid #333; border-top: 1px solid #333;" >
					                	'.$lista->EMPRESA.'
					                </td>
					                <td align="center" style="border-left: 1px solid #333; border-top: 1px solid #333;" >
					                	'.substr($lista->data_entrega,8,2).'/'.substr($lista->data_entrega,5,2).'/'.substr($lista->data_entrega,0,4).'
					                </td>
					                <td align="center" style="border-left: 1px solid #333; border-top: 1px solid #333;" >
					                	<a target="_blank" href="http://www.ztlbrasil.com.br/admin/compras/pedidoscompraprod/ped/'.md5($lista->idped).'>" ><img src="http://www.ztlbrasil.com.br/admin/images/visualizar_orc.gif" width="17" height="13" border="0" title="Visualizar"></a>
					                </td>                
					            </tr>';
				            
				            endforeach;
				            
				            $texto .= '		     
						</table>
					</td>
				</tr>';
			 
			 endif;
			 if(count(ComprasBO::listaPedidos($var2)) > 0) :
				$texto .= '		
				<tr>
		            <td align="left" valign="middle" style="padding: 6px; border-top: 1px solid #333;" >
		               <b> ENTREGAS PREVISTAS PARA OS PRÓXIMOS 30 DIAS </b>
		            </td>
		    	</tr>
		    	<tr>
		    		<td>		    		
						<table align="center" width="600px" cellspacing="0" cellpadding="2" >
					        <tr>
					            <th  align="center" width="10%" style="border-top: 1px solid #333;" >
					                ID
					            </th>
					            <th align="center" width="50%" style="border-left: 1px solid #333; border-top: 1px solid #333;" >
					                FORNECEDOR
					            </th>
					            <th align="center" width="20%" style="border-left: 1px solid #333; border-top: 1px solid #333;" >
					            	DATA ENTREGA 
					            </th>
					            <th align="center" width="10%" style="border-left: 1px solid #333; border-top: 1px solid #333;" >
					                VISUALIZAR
					            </th>
					        </tr>';	
							
							foreach (ComprasBO::listaPedidos($var2) as $lista):
								$texto .= '		
								<tr >
					                <td align="center" style="border-top: 1px solid #333;" >
					                   '.$lista->idped.'
					                </td>
					                <td align="left" style="border-left: 1px solid #333; border-top: 1px solid #333;" >
					                	'.$lista->EMPRESA.'
					                </td>
					                <td align="center" style="border-left: 1px solid #333; border-top: 1px solid #333;" >
					                	'.substr($lista->data_entrega,8,2).'/'.substr($lista->data_entrega,5,2).'/'.substr($lista->data_entrega,0,4).'
					                </td>
					                <td align="center" style="border-left: 1px solid #333; border-top: 1px solid #333;" >
					                	<a target="_blank" href="http://www.ztlbrasil.com.br/admin/compras/pedidoscompraprod/ped/'.md5($lista->idped).'" ><img src="http://www.ztlbrasil.com.br/admin/images/visualizar_orc.gif" width="17" height="13" border="0" title="Visualizar"></a>
					                </td>                
					            </tr>';
				            
				            endforeach;
				            
				            $texto .= '   
						</table>
					</td>
				</tr>';
			 
			 endif;
			 $texto .= '	
		</table>
		</body>
		</html>';
		
		
		$resp 	= "Helio";
		$email  = "helio@ztlbrasil.com.br";
				
		try {
			DiversosBO::enviaMail($assunto, $texto, $resp, $email);
			
		} catch (Exception $e){
			echo $e;
		}
		 
		
		$resp 	= "Bruno";
		$email  = "edson.lima@ztlbrasil.com.br";
				
		try {
			DiversosBO::enviaMail($assunto, $texto, $resp, $email);
			
		} catch (Exception $e){
			echo $e;
		}
		 
		endif; 
		
		}
		
		//--- Aviso pedidos atrasados ---------------------------------
		function alertaProdutossemestoque(){
			date_default_timezone_set('America/Sao_Paulo');
			
			$var['periodo']		= 6;			
			if(count(ProdutosBO::buscaProdrelatoriovenda($var)) > 0):
			
			$texto = '			
			<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
			<title>ZTL Brasil - www.ztlbrasil.com.br </title>
			<link href="http://www.ztlbrasil.com.br/public/sistema/imagens/ztl.ico" rel="shortcut icon" />
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			</head>
			<body>
			<table align="center" width="500px" cellspacing="0" cellpadding="0" style="border-collapse: collapse; border: 1px solid #333; font: 11px Arial, Helvetica, sans-serif;" >';
		     	
			if(count(ProdutosBO::buscaProdrelatoriovenda($var)) > 0) :
				$texto .= '				
		        <tr>
		            <td align="left" style="padding: 6px; color: #FF0000">
		                <b>SUGESTÃO DE PRODUTOS PARA COMPRA</b>
		            </td>
		    	</tr>
		    	<tr>
		    		<td>		    		
						<table align="center" width="600px" cellspacing="0" cellpadding="2" >
					        <tr>
					            <th  align="center" width="10%" style="border-top: 1px solid #333;" >
					                CODIGO
					            </th>
					            <th align="center" width="50%" style="border-left: 1px solid #333; border-top: 1px solid #333;" >
					                VENDAS
					            </th>
					            <th align="center" width="20%" style="border-left: 1px solid #333; border-top: 1px solid #333;" >
					            	FÍSICO 
					            </th>
					            <th align="center" width="10%" style="border-left: 1px solid #333; border-top: 1px solid #333;" >
					                VIRTUAL
					            </th>
					            <th align="center" width="10%" style="border-left: 1px solid #333; border-top: 1px solid #333;" >
					                SUGESTÃO
					            </th>
					        </tr>';	
							
				
							foreach (ProdutosBO::buscaProdrelatoriovenda($var) as $lista):
							
								if ((($lista->qtvenda/6)*6)-($lista->qt_atual+($lista->qtcomp - $lista->qtent)) >= 0):
									if(!empty($lista->qtvenda)): 
										$qtven = $lista->qtvenda; 
									else: 
										$qtven = 0; 
									endif;
									
									if(!empty($lista->qt_atual)): 
										$qtatual =  $lista->qt_atual; 
									else: 
										$qtatual = 0;
									endif;
									
									if(!empty($lista->qtcomp)): 
										$qtvir = $lista->qtcomp - $lista->qtent; 
									else: 
										$qtvir = 0; 
									endif;
									
									$texto .= '			
									<tr >
						                <td align="center" style="border-top: 1px solid #333;" >
						                   '.$lista->CODIGO.'
						                </td>
						                <td align="center" style="border-left: 1px solid #333; border-top: 1px solid #333;" >
						                	'.$qtven.'
						                </td>
						                <td align="center" style="border-left: 1px solid #333; border-top: 1px solid #333;" >
						                	'.$qtatual.'
						                </td>
						                <td align="center" style="border-left: 1px solid #333; border-top: 1px solid #333;" >
						                	'.$qtvir.'
						                </td>                
						                <td align="center" style="border-left: 1px solid #333; border-top: 1px solid #333;" >
						                	'.round((($lista->qtvenda/$this->objQtmes)*6)-($lista->qt_atual+($lista->qtcomp - $lista->qtent))).'
						                </td>
						            </tr>';
				            	endif;
				            endforeach;
				            
				            $texto .= '		     
						</table>
					</td>
				</tr>
				</table>
			</body>
			</html>';
			 
			endif;
		
			$smtp = "smtp.ztlbrasil.com.br";
			$conta = "info@ztlbrasil.com.br";
			$senha = "010203";
			$de = "info@ztlbrasil.com.br";
			$assunto = "SUGESTÃO DE PRODUTOS PARA COMPRA";

			$resp 	= "Cleiton";
			$email  = "cleiton@ztlbrasil.com.br";
					
			try {
				$config = array (
				'ssl' => 'tls',
				'auth' => 'login',
				'username' => $conta,
				'password' => $senha,
				'port' => '25'
				);
			
				$mailTransport = new Zend_Mail_Transport_Smtp($smtp, $config);
				
				$mail = new Zend_Mail('utf-8');
				$mail->setFrom($de);
				$mail->addTo($email,$resp);
				$mail->setBodyHtml($texto);
				$mail->setSubject($assunto);
				$mail->send($mailTransport);
				
			} catch (Exception $e){
				echo $e;
			}
			
			$resp 	= "Ranieri";
			$email  = "rani@ztlbrasil.com.br";
					
			try {
				$config = array (
				'ssl' => 'tls',
				'auth' => 'login',
				'username' => $conta,
				'password' => $senha,
				'port' => '25'
				);
			
				$mailTransport = new Zend_Mail_Transport_Smtp($smtp, $config);
				
				$mail = new Zend_Mail('utf-8');
				$mail->setFrom($de);
				$mail->addTo($email,$resp);
				$mail->setBodyHtml($texto);
				$mail->setSubject($assunto);
				$mail->send($mailTransport);
				
			} catch (Exception $e){
				echo $e;
			}
			
			$resp 	= "Helio";
			$email  = "helio@ztlbrasil.com.br";
					
			try {
				$config = array (
				'ssl' => 'tls',
				'auth' => 'login',
				'username' => $conta,
				'password' => $senha,
				'port' => '25'
				);
			
				$mailTransport = new Zend_Mail_Transport_Smtp($smtp, $config);
				
				$mail = new Zend_Mail('utf-8');
				$mail->setFrom($de);
				$mail->addTo($email,$resp);
				$mail->setBodyHtml($texto);
				$mail->setSubject($assunto);
				$mail->send($mailTransport);
				
			} catch (Exception $e){
				echo $e;
			}
			 
			
			$resp 	= "Bruno";
			$email  = "blirio@ztlbrasil.com.br";
					
			try {
				$config = array (
				'ssl' => 'tls',
				'auth' => 'login',
				'username' => $conta,
				'password' => $senha,
				'port' => '25'
				);
			
				$mailTransport = new Zend_Mail_Transport_Smtp($smtp, $config);
				
				$mail = new Zend_Mail('utf-8');
				$mail->setFrom($de);
				$mail->addTo($email,$resp);
				$mail->setBodyHtml($texto);
				$mail->setSubject($assunto);
				$mail->send($mailTransport);
				
			} catch (Exception $e){
				echo $e;
			}
		 
		endif; 
		
		}
		
		
		function corrigeEntrada(){
			$bo		= new EntradaestoqueModel();
			$bop	= new EntradaestoqueprodModel();
			$boe		= new EstoqueModel();
						
			foreach ($boe->fetchAll("id_atualizacao = 16 and tipo = 'COMPRA'") as $estoque):
				 foreach ($boe->fetchAll("id_prod = ".$estoque->id_prod." and id > ".$estoque->id) as $estqcorrige):
					$data = array(
						'qt_atual'	=> $estqcorrige->qt_atual - $estoque->qt_atualizacao
					);
			
					$boe->update($data, "id = ".$estqcorrige->id);
					
					echo $estqcorrige->id_prod;
					echo " - ";
					echo $estqcorrige->qt_atual;
					echo "<br />";
						
				endforeach;
				
				$boe->delete("id = ".$estoque->id);
			endforeach;
			
			
			
			
			/* foreach ($bop->fetchAll("id_entradaztl = 16") as $prodent):
				
				
			endforeach; */
		}
		
		
		//------- NFe ---------------------------------------
		function gravarDadosnfe($params){
			$bonfe		= new NfeModel();
			$bonfeprod	= new NfeprodModel();
			$boe		= new EntradaestoqueModel();
						
			
			if($params['entestoque'] == 'true'):
				$arrayent['entestoque']	= true;
			else:
				$arrayent['entestoque']	= false;
			endif;
			
			$boe->update($arrayent, "id = ".$params['gerarentrada']);
			
			$ent['entrada']	= md5($params['gerarentrada']);
			//--- Busca empresa e transportadora com enderecos ----------------
			foreach (ComprasBO::listaEntrada($ent) as $entrada);
			
			//--- Busca CFOP da compra ----------------------------------------------
			$cfopid['idcfop'] = md5($entrada->id_tributocfop);
			foreach (TributosBO::buscaCfop($cfopid) as $cfop);
					
			$total_pedido = $ipi = 0;
			$total_pedido_liquido = 0;
				
			//-- Dados da NFe ------------------------------------
			$datanfe = array(
				'serie'					=> 1,
				'data'					=> date('Y-m-d'),
				'data_saida'			=> date('Y-m-d H:i:s'),
				'cfop'					=> $cfop->cfop,
				'naturezaop'			=> $cfop->descricao,
				'tipo'					=> 0,
				'id_cliente'			=> 662,
				'cnpj'					=> "",
				'inscricao'				=> "",
				'empresa'				=> "ZTL DO BRASIL IMPORTACAO EXPORTACAO E COM. LTDA",
				'endereco'				=> "QI 08 LOTE 45/48 TAG. NORTE",
				'numero'				=> "S/N",
				'bairro'				=> "TAGUATINGA",
				'cep'					=> "72135080",
				'codcidade'				=> "9999999",
				'cidade' 				=> "EXTERIOR",
				'uf'					=> "EX",
			    'pais'					=> "CHINA",
			    'codpais'				=> "1600",
				'fone'					=> "6134337777",
				'id_transportadoras'	=> 662,
				'transportadora'		=> "NOSSO CARRO",
				'tipofrete'				=> 0,
				'transantt'				=> "",
				'transplaca'			=> "",
				'transufplaca'			=> "",
				'transcnpj'				=> "07555737000110",
				'transie'				=> "0747014000173",
				'transendereco'			=> "QI 08 LOTE 45/48 TAG. NORTE",
				'transcidade' 			=> "TAGUATINGA",
				'transuf'				=> "DF",
				'frete'					=> 0,
				'freteperc'				=> 0,
				'seguro'				=> 0,
				'desconto'				=> 0,
				'descontoperc'			=> 0,
				'quantidade'			=> $entrada->qtpacotes,
				'especie'				=> $entrada->especie,
				'pesobruto'				=> $entrada->pesobruto,
				'pesoliquido'			=> $entrada->pesoliquido,
				'marca'					=> 'ZTL',
				'di'					=> $entrada->docdi,
				'datadi'				=> $entrada->datadi,
				'localdesembarque'		=> $entrada->localdesembarque,
				'ufdesembarque'			=> $entrada->ufdesembarque,
				'datadesembarque'		=> $entrada->datadesembarque,
				'codexportador'			=> '662'
			);
		
				
			try {
				$idnfe = $bonfe->insert($datanfe);
				//Zend_Debug::dump($datanfe);
			}catch (Zend_Exception $e){
				$boerro	= new ErrosModel();
				$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "NfeBO::gravarDadosnfe(ped=".$params[ped].")");
				$boerro->insert($dataerro);
			}
			
			//---- Calculo do coeficiente ---------------------------------------------------------------------------------------
			$qtprod = 0;
			$idprod = -1;
			
			$cor=0;
			$idverqt="";
			$auxqt=0;
			$total_pecas = 0;			
			
			$objProdutos		= ComprasBO::listaProdutosentgroup($ent);
			$objProddet			= ComprasBO::listaProdutosent($ent);
									
			//-- percentual frete ------------------------------------------------------------------
			foreach ($objProdutos as $listaprod):
				$qtentreg = $qtpedid = 0;
				foreach ($objProddet as $list):
					if(($list->ID_PRODUTO == $listaprod->ID_PRODUTO) and (!empty($list->qt))):
						$qtpedid  += $list->QUANTIDADE;
						$qtentreg += $list->qt;
					endif;
				endforeach;
				$total_pedido += $listaprod->precoimp*$qtentreg;
			endforeach;
			
			$aduaneiro = $total_pedido + $entrada->frete + $entrada->capatazia;
			
			$qtentreg = $qtpedid = $total_pedido = $totalcofins = $totalpis = $totalii = $totalunitgeral = 0;
			foreach ($objProdutos as $listaprod):
				 
				$qtentreg = $qtpedid = 0;
				foreach ($objProddet as $list):
					if(($list->ID_PRODUTO == $listaprod->ID_PRODUTO) and (!empty($list->qt))):
						$qtpedid  += $list->QUANTIDADE;
						$qtentreg += $list->qt;
					endif;
				endforeach;
				
				$totalncm = 0;
				foreach ($objProdutos as $pncm):
					if(($pncm->id_ncm == $listaprod->id_ncm)):
						$totalncm += $pncm->qta*$pncm->precoimp;
					endif;
				endforeach;
				
				$coeficiente = ($listaprod->aduaneiro/($totalncm*$listaprod->txcambio));
				
				//-- Valores unitarios -------------------------------------------------------------------------
                $unitario = $listaprod->precoimp*$coeficiente*$listaprod->txcambio;
                $iiunitatio = ($listaprod->pii*$unitario)/100;                
                $unitarioii = ($listaprod->precoimp*$coeficiente*$listaprod->txcambio)+$iiunitatio;
                
                //-- Total unitario --------------------------------------------------------------------
                $totalunitario = $qtentreg*$unitario;
                 
                $ii = ($listaprod->pii*$totalunitario)/100;	  
								
				$ipi = (((($listaprod->pii*$totalunitario)/100)+$totalunitario)*$listaprod->pipi)/100;
				 
				$aicms 		= $listaprod->picms/100;
				$aii 		= $listaprod->pii/100;
				$aipi 		= $listaprod->pipi/100;
				$apis 		= $listaprod->ppis/100;
				$acofins 	= $listaprod->pcofins/100;
				 
				$pis = ($totalunitario*$apis);
		        $cofins = ($totalunitario*$acofins);
				
				$totalpis 		+= $pis;
				$totalcofins 	+= $cofins;
				$totalipi       += $ipi;
				$total_pedido 	+= $totalunitario+$ii;
				$totalunitgeral += $totalunitario;
				
			endforeach;
			
			$coedicms = ($totalpis+$totalcofins+$entrada->siscomex+$entrada->outros)/($total_pedido+$totalipi);
			$totalcalcoutros = 0;
			
			//---- Grava produtos --------------------------------------------------------------------
			$qtentreg = $qtpedid = $total_pedido = $totalicms = $totalbase = $totalipi = 0;
			$totalii = $totalpis = $totalcofins = $qtentreg = 0;
        	foreach ($objProdutos as $listaprod):
        		$idprodutos .= $listaprod->ID_PRODUTO.";";
	            
	            //--- somar produtos iguais de pedidos diferentes ---------------------------
	            $pedidos = "";
	            $qtentreg = $qtpedid = 0;
	            foreach ($objProddet as $list):
	        		if(($list->ID_PRODUTO == $listaprod->ID_PRODUTO) and (!empty($list->qt))):
	        			$qtpedid  += $list->QUANTIDADE;
	        			$qtentreg += $list->qt;
	        		endif;
	        	endforeach;
	            
	        	//--- Calculo dos impostos que sao exibidos ----------------------------------------------
	        	$totalncm = 0;
				foreach ($objProdutos as $pncm):
					if(($pncm->id_ncm == $listaprod->id_ncm)):
						$totalncm += $pncm->qta*$pncm->precoimp;
					endif;
				endforeach;
	        			        	
	        	//$coeficiente = ($listaprod->aduaneiro/(round($totalncm,2)*$listaprod->txcambio));
	        	$coeficiente = ($listaprod->aduaneiro/($totalncm*$listaprod->txcambio));
	        	
	        	//-- Valores unitarios -------------------------------------------------------------------------
                $unitario = $listaprod->precoimp*$coeficiente*$listaprod->txcambio;
                $iiunitatio = ($listaprod->pii*$unitario)/100;
                $unitarioii = ($listaprod->precoimp*$coeficiente*$listaprod->txcambio)+$iiunitatio;
                
                //-- Total unitario --------------------------------------------------------------------
                $totalunitario = $qtentreg*$unitario;
                 
                $ii = ($listaprod->pii*$totalunitario)/100;	  
	        		        		        	
	        	$ipi = (((($listaprod->pii*$totalunitario)/100)+$totalunitario)*$listaprod->pipi)/100;
	        	
	        	$aicms 		= $listaprod->picms/100;
	        	$aii 		= $listaprod->pii/100;
	        	$aipi 		= $listaprod->pipi/100;
	        	$apis 		= $listaprod->ppis/100;
	        	$acofins 	= $listaprod->cofins/100;
	        	
	        	$pis = ($totalunitario*$apis);
		        $cofins = ($totalunitario*$acofins);
					        			        
				//--- CST ------------------------------------------------------
				$tpipi = "";
				if($listaprod->tipoipi == 1):
					$tpipi = "Trib";
				elseif($listaprod->tipoipi == 2):
					$tpipi = "NT";
				else:
					$erro =  "Tipoipi";
					$tpipi = $erro;
				endif;
			
				if($listaprod->tipopis == 1):
					$tppis = "Aliq";
				elseif($listaprod->tipopis == 2):
					$tppis = "Qtde";
				elseif($listaprod->tipopis == 3):
					$tppis = "NT";
				elseif($listaprod->tipopis == 4):
					$tppis = "Outr";
				else:
					$erro =  "Tipopis";
				endif;
			
				if($listaprod->tipocofins == 1):
					$tpcofins = "Aliq";
				elseif($listaprod->tipocofins == 2):
					$tpcofins = "Qtde";
				elseif($listaprod->tipocofins == 3):
					$tpcofins = "NT";
				elseif($listaprod->tipocofins == 4):
					$tpcofins = "Outr";
				else:
					$erro =  "Tipocofins";
				endif;
								
				$baseicms = $icms = 0;
				$baseicms = (($totalunitario+$ii)+$ipi+(($totalunitario+$ii)+$ipi)* $coedicms) / (1-$aicms);
				$icms = $aicms*$baseicms;
				
				/*-- Calcula o frete ----------------------------------------- */
				
				$outrosdesp = $percout = 0;
				$percout	= ($totalunitario*100)/$totalunitgeral;
				$outrosdesp = (($entrada->siscomex+$entrada->outros)*$percout)/100;
				
				$dataprod = array(
					'id_nfe'		=> $idnfe,
					'id_prod'		=> $listaprod->ID,
					'codigo'		=> $listaprod->CODIGO,
					'descricao'		=> $listaprod->DESCRICAO,
					'ncm'			=> str_replace(".", "", $listaprod->ncm),
					'ncmex'			=> $listaprod->ncmex,
					'cfop'			=> $tribcfop->cfop,
					'qt'			=> $qtentreg,
					'preco'			=> round($unitarioii,4),
					'alicms'		=> $listaprod->picms,
					'baseicms'		=> $baseicms,
					'vlicms'		=> $icms,
					'csticms'		=> str_pad($listaprod->csticms, 2, '0',STR_PAD_LEFT),
					'alipi'			=> $listaprod->pipi,
					'vlipi'			=> $ipi,
					'cstipi'		=> str_pad($listaprod->cstipi, 2, '0',STR_PAD_LEFT),
					'origem'		=> $listaprod->origem,
					'unidade'		=> $listaprod->unidade,
					'codean'		=> $listaprod->codigo_ean,
					'basest'		=> 0,
					'mvast'			=> 0,
					'icmsst'		=> 0,
					'vlicmsst'		=> 0,
				    'alii'			=> $listaprod->pii,
				    'baseii'		=> $totalunitario,
				    'vlii'			=> $ii,
					'desconto'		=> 0,
					'frete'			=> 0,
				    'outrasdesp'	=> $outrosdesp,
					'cstpis'		=> str_pad($listaprod->cstpis, 2, '0',STR_PAD_LEFT),
					'alpis'			=> $listaprod->ppis,
					'vlpis'			=> $pis,
					'cstcofins'		=> str_pad($listaprod->cstcofins, 2, '0',STR_PAD_LEFT),
					'alcofins'		=> $listaprod->pcofins,
					'vlcofins'		=> $cofins,
					'csttpipi'		=> $tpipi,
					'csttppis'		=> $tppis,
					'csttpcofins'	=> $tpcofins,
				    'dinumadicao'	=> $listaprod->numadicao,
				    'dinumseq'		=> $listaprod->numsequencia,
				    'dicodfab'		=> $listaprod->ID,
				    'vladuaneiro'	=> $listaprod->prodaduaneiro
				);
			
				$totalii 		+= $ii;
				$totalpis 		+= $pis;
				$totalcofins 	+= $cofins;
				$totalipi       += $ipi;											
				$total_pedido   += round(round($unitarioii,4)*$qtentreg,2);
				$total_pecas    += $qtentreg;
				$totalicms 		+= $icms;
				$totalbase 		+= $baseicms;
				
				$bonfeprod->insert($dataprod);
			endforeach;
			
			$observacao = "DI=".$entrada->docdi." INVOICE=".$entrada->fornecimento." TAXA=".number_format($listaprod->txcambio,4,",",".").";PIS=".number_format($totalpis,2,",",".")." Cofins=".number_format($totalcofins,2,",",".").";";
			$observacao.= "FORNECEDOR: CIXI SHUNKANG IMPORT AND EXPORT CO.,LTD ".$entrada->obs;
						
			//--- Totais da nota -------------------------------------------
			$datanfe = array(
				'baseicms'		=> number_format($totalbase,2,".",""),
				'vlicms'		=> number_format($totalicms,2,".",""),
				'basest'		=> number_format(0,2,".",""),
				'vlst'			=> number_format(0,2,".",""),
				'totalipi'		=> number_format($totalipi,2,".",""),
				'totalpis'		=> number_format($totalpis,2,".",""),
				'totalcofins'	=> number_format($totalcofins,2,".",""),
			    'totalii'		=> number_format($totalii,2,".",""),
				'totalprodutos'	=> number_format($total_pedido,2,".",""),
				'totalnota'		=> number_format($total_pedido+$totalipi+($totalpis+$totalcofins+$entrada->siscomex+$entrada->outros),2,".",""),
				'outrasdesp'	=> number_format($entrada->siscomex+$entrada->outros,2,".",""),
			    'obs'			=> $observacao
			);
				
			try {
				$bonfe->update($datanfe,'id = '.$idnfe);
				//$nfeped = array('id_nfe' => $idnfe);
				//$bov->update($nfeped, "id = ".$params['ped']);
				//--- esse echo garente a validacao na nfevenda.js --------------------------
				echo "idnfe:".$idnfe;
			}catch (Zend_Exception $e){
				$boerro	= new ErrosModel();
				$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "NfeBO::gravarDadosnfe(ped=".$params[ped].")");
				$boerro->insert($dataerro);
			}
		}
		
		
		function buscaEntradasimulacoes($params=""){
		    $bo  = new EntradaestoqueModel();
		    $bos = new EntradasimulacaoModel();
		    
		    if(!empty($params['simulacao'])){
		        $where = " and md5(id) = '".$params['simulacao']."'";
		    }
		    		    
		    if(!empty($params['buscadoc'])){
		    	$where = " and id = ".substr($params['buscadoc'],1);
		    }
		    
		    if((!empty($params['dataini'])) and (!empty($params['datafin']))):
			    $dataini = substr($params['dataini'],6,4).'-'.substr($params['dataini'],3,2).'-'.substr($params['dataini'],0,2);
			    $datafin = substr($params['datafin'],6,4).'-'.substr($params['datafin'],3,2).'-'.substr($params['datafin'],0,2);
		    	$where .= " and data between '".$dataini."' and '".$datafin."'";
		    elseif((!empty($params['dataini'])) and (empty($params['datafin']))):
			    $dataini = substr($params['dataini'],6,4).'-'.substr($params['dataini'],3,2).'-'.substr($params['dataini'],0,2);
			    $where .= " and data >= '".$dataini."'";
		    elseif((empty($params['dataini'])) and (!empty($params['datafin']))):
		    	$datafin = substr($params['datafin'],6,4).'-'.substr($params['datafin'],3,2).'-'.substr($params['datafin'],0,2);
		    	$where .= " and data <= '".$datafin."'";
		    endif;
		    
			return $bos->fetchAll("sit != 0".$where,"id desc");		    
		}
		
		function buscaEntradasimulacoesprod($params=""){
			$bo  = new EntradaestoqueModel();
			$bos = new EntradasimulacaoModel();
			$boprod = new EntradasimulacaoprodModel();
		
			if(!empty($params['simulacao'])){
				
				$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
				$db->setFetchMode(Zend_Db::FETCH_OBJ);
					
				$select = $db->select();
					
				$select->from(array('e'=>'tb_entradasimulacaoprod'), array('e.*','p.CODIGO'))
					->join(array('p'=>'produtos'),'p.ID = e.id_produtos')
					->where("md5(e.id_entradasimulacao) = '".$params['simulacao']."'");
					
				$stmt = $db->query($select);
				return  $stmt->fetchAll();
			}			
		}
		
		function gravaSimulacao($params){
		    try{
			    $bo  	= new EntradaestoqueModel();
			    $bos 	= new EntradasimulacaoModel();
			    $boprod = new EntradasimulacaoprodModel();
			    $boad	= new EntradasimulacaoadicoesModel();
			    $usuario = Zend_Auth::getInstance()->getIdentity();
			    
			    if(empty($params['idsimulacao'])){
			        $data = array(
			        	'data' 			=> date("Y-m-d"),
			            'sit'			=> 1,
			            'id_usuarios'	=> $usuario->id
			        );
			        		        
			      	$id =	$bos->insert($data); 
			    }else{
			        $id = $params['idsimulacao'];
			    }
			    
			    
			    $params['tipo'] = 3;
			    foreach (ProdutosBO::buscaProduto($params) as $prod);
			    
				$dataprod = array(
					'qt'					=> $params['qt'],
			        'id_produtos'			=> $params['codigo'],
				    'id_entradasimulacao'	=> $id,
				    'id_produtos'			=> $prod->ID,
				    'ncm'					=> $prod->ncm." ".$prod->ncmex,
				    'preco'					=> str_replace(",", ".", str_replace(".", "", $params['preco']))
				);
	
				$boprod->insert($dataprod);
				
				LogBO::cadastraLog("Entrada/Simulação",2,$usuario->id,$id,"S".substr("000000".$id,-6,6));
				
				return $id;
		    }catch (Zend_Exception $e){
		        $boerro	= new ErrosModel();
		        $dataerro = array('descricao' => $e->getMessage(), 'pagina' => "ComprasBO::gravaSimulacao()");
		        $boerro->insert($dataerro);
		        
		        return false;
		    }
		}
				
		function gravaSimulacaoadcao($params){
			try{
				$bo  	= new EntradaestoqueModel();
				$bos	= new EntradasimulacaoModel();
				$boad	= new EntradasimulacaoadicoesModel();
				
				$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
				$db->setFetchMode(Zend_Db::FETCH_OBJ);
					
				$select = $db->select();
					
				$select->from(array('e'=>'tb_entradasimulacaoprod'), array('e.*'))
					->join(array('p'=>'produtos'), 'p.ID = e.id_produtos')
					->joinLeft(array('n'=>'tb_produtosncm'), 'n.id = p.id_ncm')
					->where("md5(e.id_entradasimulacao) = '".$params['simulacao']."'")
					->group("e.ncm");
					
				$stmt = $db->query($select);

				//--- verifico as adicoes. se nao existir, um novo cadastro eh criado ------------------------------
				foreach ($stmt->fetchAll() as $produtos){
				    $verad = 0;
				    foreach (ComprasBO::buscaEntradasimulacoesadcoes($params) as $adicoes){
				        if((trim($produtos->ncm)) == (trim($adicoes->ncm))){
				            $verad = 1;
				        }
				    }
				    
				    if($verad == 0){
					    $data = array(
				            'ncm' => $produtos->ncm, 
				            'id_entradasimulacao' 	=> $produtos->id_entradasimulacao,
				            'ipi'					=> $produtos->ipi,
				            'pis'					=> $produtos->pis,
				            'cofins'				=> $produtos->cofins,
				            'ii'					=> $produtos->ii
					    );
					    
					    $boad->insert($data);
				    }
				}
				
				//--- verifico alguma adicao sem produto cadastrado. Isso ocorre caso algum produto seja removido ---------------------------
				foreach (ComprasBO::buscaEntradasimulacoesadcoes($params) as $adicoes){
				    $verad = 0;
				    
				    $select = $db->select();
				    	
				    $select->from(array('e'=>'tb_entradasimulacaoprod'), array('e.*'))
				    ->join(array('p'=>'produtos'), 'p.ID = e.id_produtos')
				    ->joinLeft(array('n'=>'tb_produtosncm'), 'n.id = p.id_ncm')
				    ->where("md5(e.id_entradasimulacao) = '".$params['simulacao']."'")
				    ->group("e.ncm");
				    	
				    $stmt = $db->query($select);
				    
				    foreach ($stmt->fetchAll() as $produtos){
						if((trim($produtos->ncm)) == (trim($adicoes->ncm))){
							$verad = 1;
						}
				    }

				    if($verad == 0) $boad->delete("id = '".$adicoes->id."'");
				}
								
				$datasim = array('sit' => 2);				
				$bos->update($datasim,"md5(id) = '".$params['simulacao']."'");
				
				return true;
			}catch (Zend_Exception $e){
				$boerro	= new ErrosModel();
				$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "ComprasBO::gravaSimulacaoadcao()");
				$boerro->insert($dataerro);
		
				return false;
			}
		}
		
		function buscaEntradasimulacoesadcoes($params=""){
			$bo  = new EntradaestoqueModel();
			$boad	= new EntradasimulacaoadicoesModel();
		
			if(!empty($params['simulacao'])){
				return $boad->fetchAll("md5(id_entradasimulacao) = '".$params['simulacao']."'");
			}			
		}
		
		function atualizaEntradasimulacaoadcoes($params){
		    $bo  	= new EntradaestoqueModel();
		    $bos	= new EntradasimulacaoModel();
		    $boad	= new EntradasimulacaoadicoesModel();
		    
		    foreach (ComprasBO::buscaEntradasimulacoesadcoes($params) as $ncm){
		        if((!empty($params['ncm_'.$ncm->id])) and (!empty($params['peso_'.$ncm->id]))) {
		        	$data = array(
		        	 	'valor' 	=> str_replace(",",".",str_replace(".","",$params['ncm_'.$ncm->id])),
		        	    'peso' 		=> str_replace(",",".",str_replace(".","",$params['peso_'.$ncm->id])),
	        	        'ipi'		=> str_replace(",",".",str_replace(".","",$params['ipi_'.$ncm->id])),
	        	        'pis'		=> str_replace(",",".",str_replace(".","",$params['pis_'.$ncm->id])),
	        	        'cofins'	=> str_replace(",",".",str_replace(".","",$params['cofins_'.$ncm->id])),
	        	        'ii'		=> str_replace(",",".",str_replace(".","",$params['ii_'.$ncm->id]))
		        	);
		        	
		        	$boad->update($data,"id = ".$ncm->id);		        	
		        }
		    }
		    
		    $datasim = array(
	    		'frete' 	=> str_replace(",",".",str_replace(".","",$params['frete'])),
	    		'capatazia' => str_replace(",",".",str_replace(".","",$params['capatazia'])),
	    		'cambio' 	=> str_replace(",",".",str_replace(".","",$params['cambio'])),
	    		'icms' 		=> str_replace(",",".",str_replace(".","",$params['icms'])),
	    		'siscomex'	=> str_replace(",",".",str_replace(".","",$params['siscomex'])),
	    		'sit'		=> 3
		    );
		    
		    $bos->update($datasim,"md5(id) = '".$params['simulacao']."'");
		}
		
		function removeSimulacao($params){
			try{
				$bo  	= new EntradaestoqueModel();
				$bos 	= new EntradasimulacaoModel();
				$usuario = Zend_Auth::getInstance()->getIdentity();
				 
				$data = array('sit' => 0);
				$id = $bos->update($data,"md5(id) = '".$params['simulacao']."'");
				
				LogBO::cadastraLog("Entrada/Simulação",3,$usuario->id,$id,"S".substr("000000".$id,-6,6));
				
				return true;
			}catch (Zend_Exception $e){
				$boerro	= new ErrosModel();
				$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "ComprasBO::removeSimulacao()");
				$boerro->insert($dataerro);
		
				return false;
			}
		}
		
		function calculaImpostosentrada($idncm,$valor){
		    
		    try{
		        if(!empty($idncm) and !empty($valor)){
				    $bo 	= new TributosModel();
				    $bon	= new NcmModel();
				    
				    foreach ($bon->fetchAll("id = ".$idncm) as $produtosncm);
				    
				    $vlii = ($valor * ($produtosncm->ii/100));
				    $vlipi = (($valor+$vlii) * ($produtosncm->ipi/100));
				    
				    $aicms 		= 12/100;
				    $aii 		= $produtosncm->ii/100;
				    $aipi 		= $produtosncm->ipi/100;
				    $apis 		= $produtosncm->pis/100;
				    $acofins 	= $produtosncm->cofins/100;
				    
				    $vlpis 		= $apis*($valor*(1+$aicms*($aii+$aipi*(1+$aii))))/((1-$apis-$acofins)*(1-$aicms));
				    $vlcofins 	= $acofins*($valor*(1+$aicms*($aii+$aipi*(1+$aii))))/((1-$apis-$acofins)*(1-$aicms));
				    $coeficms 	= 1 - $aicms;
				    $vlicms 	=  (($valor + $vlii + $vlipi + $vlpis + $vlcofins)/$coeficms)*$aicms;
				    
				    
				    $custoicms 		= $vlicms * 0.384;
				    
				    echo number_format((($custoicms+$vlcofins+$vlpis+$vlipi+$vlii)),2,",",".")."%";
		        }		   
			    
		    }catch (Zend_Exception $e){
		        echo $e->getMessage();
		    }
		    
		}
		
	}
?>
                               