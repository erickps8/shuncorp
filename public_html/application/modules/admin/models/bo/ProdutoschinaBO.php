<?php
	class ProdutoschinaBO{		
		public function listaProdutos($params){
			$obj = new ProdutosModel();
			return $obj->fetchAll('ID = '.$params);			
		}

		public function listaProdutoscodigo($params){
			$obj = new ProdutosModel();
			return $obj->fetchAll('CODIGO = "'.$params['q'].'"');			
		}
		
		public function listaallProdutos(){
			$obj = new ProdutosModel();
			return $obj->fetchAll("ID is not NULL","codigo_mask");			
		}
		
		function buscaProdutoscodigo($cod){
			/*---Lista produtos  codido--------------------
			 * Usado em pedidosentorcAction;
			 */
			$obj = new ProdutosModel();
			return $obj->fetchAll('CODIGO = "'.$cod.'"');			
		}
		
		public function veiculosProdutos($pesq){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_produto_veiculo','*'),
			        array('t.id', 't.id_produto', 't.id_veiculo', 't.ano_ini', 'v.no_modelo', 'v.id_montadora', 'm.nome', 't.ano_fim'))
			        ->join(array('v'=>'tb_veiculo'), 't.id_veiculo = v.id')
			        ->join(array('m'=>'tb_montadora'), 'v.id_montadora = m.id')
			        ->where("t.id_produto = ".$pesq);
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
								
		}
		
		public function listaGrupos(){
			$obj = new GruposprodModel();
			return $obj->fetchAll("ID is not NULL","NOME asc");			
		}
		
		public function buscaProduto($pesq){
		    try{
			    $where = "";
			    if($pesq['tipo']==1):
				    //---- busca por descricao do produto  -----------------------------------------
				    $where = 'p.DESCRICAO like  "%'.$pesq['buscadesc'].'%"';
			    
			    elseif($pesq['tipo']==2):
			    		//--- busca por ID em MD5 -------------------------------------------
				    $where = 'md5(p.ID) = "'.$pesq['produto'].'"';
			    
			    elseif($pesq['tipo']==3):
			    	//--- busca por codigo exato -------------------------------------------
			    	$where = 'p.CODIGO = "'.$pesq['codigo'].'"';
			    
			    endif;
			    	
			   
			    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			    $db->setFetchMode(Zend_Db::FETCH_OBJ);
			    	
			    $select = $db->select();
			    	
			    $select->from(array('p'=>'produtos','*'), array('p.*','p.ID', 'p.CODIGO','g.descricao as NOME','p.DESCRICAO','p.situacao','g.descricao as descgrupo','s.descricao as descsubgrupo','s.id as idsubg','s.id_gruposprod as grupoprod'))
				    ->join(array('s'=>'tb_gruposprodsub'), 's.id = p.id_gruposprodsub')
				    ->join(array('g'=>'tb_gruposprod'), 'g.id = s.id_gruposprod')
				    ->joinLeft(array('n'=>'tb_produtosncm'), 'n.id = p.id_ncm')
				    ->joinLeft(array('m'=>'tb_produtosmedidas'), 'm.id_prod = p.ID')
				    ->where($where);
			    	
			    $stmt = $db->query($select);
			    return $stmt->fetchAll();
		    }catch (Zend_Exception $e){
		        $boerro	= new ErrosModel();
		        $dataerro = array('descricao' => $e->getMessage(), 'pagina' => "ProdutosBO::buscaProduto()");
		        $boerro->insert($dataerro);
		    }
		}
		
		
		//--Lista produtos com Grupo---------------------------------
		function listaProdutosgrupo($pesq){

			$this->translate	=	Zend_Registry::get('translate');
			
			$sessaobusca = new Zend_Session_Namespace('produtos');
		    if(isset($sessaobusca->where)):
		   		$where = $sessaobusca->where;
		   		$tipo  = $sessaobusca->tipo;
		   		$media = $sessaobusca->buscamedia;
		   		$limit = "";
		   	endif;	
			
			$where = "";
			
		   	//---- busca por subgrupo -----------------------------------------
			$where = (isset($pesq['buscagruposub']) and $pesq['buscagruposub']!=0) ? ' and s.id = '.$pesq['buscagruposub'] : "";
			//---- busca por Grupo -----------------------------------------
			$where .= ($pesq['buscagrupo']!=0) ? ' and g.id = '.$pesq['buscagrupo'] : "";
			//---- busca por descricao do produto  -----------------------------------------
			$where .= (!empty($pesq['buscacod'])) ? ' and (p.DESCRICAO like  "%'.$pesq['buscacod'].'%" || p.CODIGO like  "%'.$pesq['buscacod'].'%")' : "";
			
			//---- busca por medidas -----------------------------------------
			if($pesq['pesqmedida']==1):
				$where .= (!empty($pesq['internorol'])) ? " and p.M_INNER like '".$pesq['internorol']."%'" : "";
				$where .= (!empty($pesq['externorol'])) ? " and p.M_OUTER like '".$pesq['externorol']."%'" : "";
				$where .= (!empty($pesq['alturarol'])) ? " and p.M_HIGH like '".$pesq['alturarol']."%'" : "";				
			elseif($pesq['pesqmedida']==2):
				$where .= (!empty($pesq['estm1'])) ? " and p.estriado_macho_d like '".$pesq['estm1']."%'" : "";
				$where .= (!empty($pesq['estm2'])) ? " and p.estriado_macho_mm like '".$pesq['estm2']."%'" : "";
				$where .= (!empty($pesq['estf1'])) ? " and p.estriado_femea_d like '".$pesq['estf1']."%'" : "";
				$where .= (!empty($pesq['estf2'])) ? " and p.estriado_femea_mm like '".$pesq['estf2']."%'" : "";				
			elseif($pesq['pesqmedida']==3):
				$where .= (!empty($pesq['internoslin'])) ? " and p.medida_inner_desl like '".$pesq['internoslin']."%'" : "";
				$where .= (!empty($pesq['externoslin'])) ? " and p.medida_outer_desl like '".$pesq['externoslin']."%'" : "";
				$where .= (!empty($pesq['alturaslin'])) ? " and p.medida_high_desl like '".$pesq['alturaslin']."%'" : "";
				$where .= (!empty($pesq['dentesslin'])) ? " and p.medida_teeth_desl like '".$pesq['dentesslin']."%'" : "";				
			elseif($pesq['pesqmedida']==4):
				$where .= (!empty($pesq['internocrus'])) ? " and p.medida_inner_cru like '".$pesq['internocrus']."%'" : "";
				$where .= (!empty($pesq['externocrus'])) ? " and p.medida_outer_cru like '".$pesq['externocrus']."%'" : "";
				$where .= (!empty($pesq['alturacrus'])) ? " and p.medida_teeth_cru like '".$pesq['alturacrus']."%'" : "";									
			endif;
			
			
			if(!empty($where)):
		   		$sessaobusca->where = $where;
		   		$sessaobusca->tipo 	= $tipo;
		   		$sessaobusca->buscamedia = $media;
		   		$limit = "";
		   	endif;
		   	
			$limit = (!empty($where)) ? 1000000 : 500;
			
		  	$where = 'p.ID is not NULL'.$where;		   	
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('p'=>'produtos','*'), array('p.*','p.ID', 'p.CODIGO','g.descricao as NOME','p.DESCRICAO','p.situacao'))
		        ->join(array('s'=>'tb_gruposprodsub'), 's.id = p.id_gruposprodsub')
		        ->join(array('g'=>'tb_gruposprod'), 'g.id = s.id_gruposprod')
		        ->joinLeft(array('n'=>'tb_produtosncm'), 'n.id = p.id_ncm')
		        ->where($where)
		        ->order("p.codigo_mask")
		        ->limit($limit);
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();
					
		}
		
		
		//--Lista produtos com Grupo---------------------------------
		function exportalistaProdutos(){
		    $usuario = Zend_Auth::getInstance()->getIdentity();
		    foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 13) as $list);
		    
			$sessaobusca = new Zend_Session_Namespace('produtos');
			if(isset($sessaobusca->where)):
				$where = $sessaobusca->where;
				$tipo  = $sessaobusca->tipo;
				$media = $sessaobusca->buscamedia;
			endif;
			
			$where = 'p.ID is not NULL'.$where;
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
			$select = $db->select();
			$select->from(array('p'=>'produtos','*'), array('p.*','p.ID', 'p.CODIGO','g.descricao as grupo','s.descricao as subgrupo','p.DESCRICAO','p.situacao'))
				->join(array('s'=>'tb_gruposprodsub'), 's.id = p.id_gruposprodsub')
				->join(array('g'=>'tb_gruposprod'), 'g.id = s.id_gruposprod')
				->joinLeft(array('n'=>'tb_produtosncm'), 'n.id = p.id_ncm')
				->where($where)
				->order("p.codigo_mask");
				
			$stmt = $db->query($select);
			$objprod = $stmt->fetchAll();
			
			if(count($objprod)>0){
			    ?>
			    <table>
			    <thead>
			    	<tr>
			    		<td style="text-align: center;">Código</td>
			    		<td style="text-align: center;">Descrição</td>
			    		<td style="text-align: center;">Grupo</td>
			    		<td style="text-align: center;">NCM</td>
			    		<?php if($list->aba3==1){ ?>
                		<td style="text-align: center;">CMV</td>	
			    		<td style="text-align: center;">Estoque</td>
			    		<?php } ?>
			    		<td style="text-align: center;">Valor</td>
			    	</tr>
			    </thead>
			    <tbody>
			    <?php 
				foreach($objprod as $produtos){
					?>
					<tr>
						<td style="text-align: center;"><?php echo $produtos->CODIGO?></td>
						<td><?php echo $produtos->DESCRICAO?></td>
						<td><?php echo $produtos->grupo."/".$produtos->subgrupo?></td>
						<td style="text-align: center;"><?php echo $produtos->ncm." ".$produtos->ncmex?></td>
						<?php if($list->aba3==1){ ?>
						<td style="text-align: right;">
							<?php 
							$objCmv = ProdutosBO::listaCmvproduto($produtos->ID);
							if(count($objCmv)>0){
								foreach($objCmv as $cmv);
								echo number_format($cmv->valor,2,",",".");
							}
							?>
						</td>
						<td style="text-align: center;">
							<?php
							$objEstoque = EstoqueBO::buscaEstoque($produtos->ID);
							if(count($objEstoque)>0){
								foreach($objEstoque as $estoque);
								echo $estoque->qt_atual;
							}
							?>
						</td>
						<?php } ?>
						<td style="text-align: richness;"><?php echo number_format($produtos->PRECO_UNITARIO,2,",",".");?></td>
					</tr>
					<?php 
				}
				?>
				</tbody>
			    </table>
				<?php 
			}
		
		}
		
		//--Lista produtos com Grupo---------------------------------
		function listaProdutoscompleto($pesq){
		
			$sessaobusca = new Zend_Session_Namespace('produtos');
			if(isset($sessaobusca->where)):
				$where = $sessaobusca->where;
				$tipo  = $sessaobusca->tipo;
				$media = $sessaobusca->buscamedia;
				$limit = "";
			endif;
				
			//---- busca por subgrupo -----------------------------------------
			if($pesq['buscagruposub']!=0 and $pesq['tipo']==1):
				$where = "";
				$where = ' and s.id = '.$pesq['buscagruposub'];
		
			elseif($pesq['tipo']==1):
				//---- busca por Grupo -----------------------------------------
				$where = "";
				if($pesq['buscagrupo']!=0):
				$where = ' and g.id = '.$pesq['buscagrupo'];
				endif;
		
			elseif($pesq['tipo']==3):
				//---- busca por descricao do produto  -----------------------------------------
				$where = "";
				$where = ' and p.DESCRICAO like  "%'.$pesq['buscadesc'].'%"';
		
			elseif($pesq['tipo']==2):
				//---- busca por trecho de codigo -----------------------------------------
				$where = "";
				$where = ' and p.CODIGO like  "%'.$pesq['buscacod'].'%"';
		
			elseif($pesq['tipo']==4):
				//---- busca por medidas -----------------------------------------
				$where = "";
				$tipo 	= 4;
				$media 	= $pesq['buscamedia'];
				$limit = 300;
				if($pesq['buscamedia']==1):
				if(!empty($pesq['internorol'])):
				$where .= " and p.M_INNER like '".$pesq['internorol']."%'";
				endif;
				if(!empty($pesq['externorol'])):
				$where .= " and p.M_OUTER like '".$pesq['externorol']."%'";
				endif;
				if(!empty($pesq['alturarol'])):
				$where .= " and p.M_HIGH like '".$pesq['alturarol']."%'";
				endif;
				elseif($pesq['buscamedia']==2):
				if(!empty($pesq['estm1'])):
				$where .= " and p.estriado_macho_d like '".$pesq['estm1']."%'";
				endif;
				if(!empty($pesq['estm2'])):
				$where .= " and p.estriado_macho_mm like '".$pesq['estm2']."%'";
				endif;
				if(!empty($pesq['estf1'])):
				$where .= " and p.estriado_femea_d like '".$pesq['estf1']."%'";
				endif;
				if(!empty($pesq['estf2'])):
				$where .= " and p.estriado_femea_mm like '".$pesq['estf2']."%'";
				endif;
				elseif($pesq['buscamedia']==3):
				if(!empty($pesq['internoslin'])):
				$where .= " and p.medida_inner_desl like '".$pesq['internoslin']."%'";
				endif;
				if(!empty($pesq['externoslin'])):
				$where .= " and p.medida_outer_desl like '".$pesq['externoslin']."%'";
				endif;
				if(!empty($pesq['alturaslin'])):
				$where .= " and p.medida_high_desl like '".$pesq['alturaslin']."%'";
				endif;
				if(!empty($pesq['dentesslin'])):
				$where .= " and p.medida_teeth_desl like '".$pesq['dentesslin']."%'";
				endif;
				elseif($pesq['buscamedia']==4):
				if(!empty($pesq['internocrus'])):
				$where .= " and p.medida_inner_cru like '".$pesq['internocrus']."%'";
				endif;
				if(!empty($pesq['externocrus'])):
				$where .= " and p.medida_outer_cru like '".$pesq['externocrus']."%'";
				endif;
				if(!empty($pesq['alturacrus'])):
				$where .= " and p.medida_teeth_cru like '".$pesq['alturacrus']."%'";
				endif;
				endif;
			elseif($pesq['tipo']==5):
				//---- busca por fornecedor -----------------------------------------
				$where = "";
				$where = ' and p.id_cliente_fornecedor_shuntai = '.$pesq['buscafor'];
			elseif($pesq['tipo']==6):
				//---- busca por hscode -----------------------------------------
				$where = "";
				$where = ' and p.id_hscode = '.$pesq['buscahscode'];
			elseif($pesq['tipo']==7):
				//--- busca por ID em MD5 -------------------------------------------
				$where = "";
				$where = ' and md5(p.ID) = "'.$pesq['produto'].'"';
			elseif($pesq['tipo']==8):
				//--- busca por codigo exato -------------------------------------------
				$where = "";
				$where = ' and p.CODIGO = "'.$pesq['codigo'].'"';
			endif;
				
			if(!empty($where)):
				$sessaobusca->where = $where;
				$sessaobusca->tipo 	= $tipo;
				$sessaobusca->buscamedia = $media;
				$limit = "";
			endif;
		
			$where = 'p.ID is not NULL'.$where;
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
			$select = $db->select();
				
			$select->from(array('p'=>'produtos','*'), array('p.*','p.ID', 'p.CODIGO','g.descricao as grupo','s.descricao as subgrupo','p.DESCRICAO','p.situacao','c.EMPRESA'))
				->join(array('s'=>'tb_gruposprodsub'), 's.id = p.id_gruposprodsub')
				->join(array('g'=>'tb_gruposprod'), 'g.id = s.id_gruposprod')
				->joinLeft(array('c'=>'clientes'), 'c.id = p.id_cliente_fornecedor_shuntai')
				->joinLeft(array('n'=>'tb_produtoshscode'), 'n.id = p.id_hscode')
				->where($where)
				->order("p.codigo_mask");
				
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		
		}
		
		//--Lista produtos com Grupo de compra---------------------------------
		public function listaProdutosgrupocompra($pesq){
						
			if($pesq['buscagrupo']!=0):
				$where = 'g.id = '.$pesq['buscagrupo'];
			else:
				$where = 'p.ID is not NULL';
			endif;
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('p'=>'produtos','*'), array('p.ID', 'p.CODIGO','g.purchasing as NOME','p.DESCRICAO','p.situacao'))
			        ->join(array('g'=>'tb_purchasing'), 'g.id = p.Purchasing_group')
			        ->where($where)
			        ->order("g.purchasing")
			        ->order("p.codigo_mask");
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();									
		}
		
		//--Busca produtos por Grupo---------------------------------
		public function buscaProdutosgrupo($pesq){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			
			$select->from(array('p'=>'produtos','*'), array('p.ID as idproduto'))
			        ->join(array('s'=>'tb_gruposprodsub'), 's.id = p.id_gruposprodsub')
			        ->join(array('g'=>'tb_gruposprod'), 'g.id = s.id_gruposprod')
			        ->where('g.id = '.$pesq);
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();										
		}
		
		/*--Busca produtos por Grupo----------------------------------
		 */
		public function buscaProdrelatoriovenda($pesq){
		    try{
				//---Busca por grupo - subgrupo---------------------------
				if ($pesq['buscagruposub']!=0):
					$where = " and p.id_gruposprodsub = ".$pesq['buscagruposub'];
				elseif ($pesq['grupovenda']!=0):
					foreach (GruposprodBO::listaGruposprodutossub($pesq['grupovenda']) as $listsubg):
						$idsg .= $listsubg->id.",";
					endforeach;
					$where = " and p.id_gruposprodsub in (".substr($idsg, 0,-1).") ";
				endif;
				
				//---Busca por grupo de compra-----------------------------
				if($pesq['buscagrupo']!=0):
					$where = 'and p.Purchasing_group = '.$pesq['buscagrupo'];
				endif;
				
				//---Busca por fornecedor----------------------------------
				if($pesq['buscafor']!=0):
					$where .= ' and p.ID_CLIENTE_FORNECEDOR = '.$pesq['buscafor'];
				endif;
				
				//---Busca por periodo-------------------------------------
				$periodo2	= date("Y")."-".(date("m"))."-01";
				
				if(date("m") > $pesq['periodo']):
					$periodo = date("Y")."-".(date("m")-$pesq['periodo'])."-01";
				else:
					$periodo = (date("Y")-1)."-".((date("m")+12)-$pesq['periodo'])."-01";
				endif;			
				
				//-- Ordenacao --------------------------------------------
				if($pesq['ord']=='1'):
					$order1 = 'qtvenda desc';
					$order2 = 'p.codigo_mask';
				else:
					$order1 = 'p.codigo_mask';
					$order2 = 'qtvenda desc';
				endif;
				
				$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
				$db->setFetchMode(Zend_Db::FETCH_OBJ);
				$select = $db->select();
				
				$select->from(array('p'=>'produtos','*'), array('p.ID as idproduto','p.*','e.qt_atual',
			        '(select sum(pc.QUANTIDADE) from produtos_pedidos_compra pc, pedidos_compra pco where pco.STATUS !="FINALIZADO" and pco.sit = 1 and pco.ID = pc.ID_PEDIDO_COMPRA and pc.sit = false and pc.ID_PRODUTO = p.ID) as qtcomp',
			        '(select sum(e.qt) from produtos_pedidos_compra pc, tb_entradaztl_prod e, tb_entradaztl en where en.id = e.id_entradaztl and e.id_prodped = pc.ID and en.sit = 1 and pc.ID_PRODUTO = p.ID and pc.sit = false) as qtent',
			        '(select sum(r.qtped) from tb_relatoriodevendas r where r.id_prod = p.ID and r.data > "'.$periodo.'" and r.data < "'.$periodo2.'") as qtvenda'))
			        ->joinLeft(array('e'=>'tb_estoqueztl'), 'p.ID = e.id_prod and e.id = (SELECT max(id) from tb_estoqueztl t where t.id_prod = e.id_prod) ')
			        ->join(array('g'=>'tb_gruposprodsub'), 'g.id = p.id_gruposprodsub ') // and g.id_gruposprod not in (4,5)
			        ->where("p.situacao != 2 and p.ID_CLIENTE_FORNECEDOR !='' and p.ID_CLIENTE_FORNECEDOR is not null and p.ID_CLIENTE_FORNECEDOR !='0' ".$where)
			        ->group('p.ID')
			        ->order($order1)
			        ->order($order2);
				
				$stmt = $db->query($select);
				
				return $stmt->fetchAll();
			}catch (Zend_Exception $e){
			    echo $e->getMessage();
			}										
		}
		
		
		/*--Busca produtos por Grupo agrupados ----------------------------------
		 * Essa busca é somente para a geracao da curca ABC dos produtos
		 * usado em relatoriosvendascurva
		 */
		public function buscaProdcurvavenda($pesq){
			//---Busca por grupo - subgrupo---------------------------
			if ($pesq['buscagruposub']!=0):
				$where = " and p.id_gruposprodsub = ".$pesq['buscagruposub'];
			elseif ($pesq['grupovenda']!=0):
				foreach (GruposprodBO::listaGruposprodutossub($pesq['grupovenda']) as $listsubg):
					$idsg .= $listsubg->id.",";
				endforeach;
				$where = " and p.id_gruposprodsub in (".substr($idsg, 0,-1).") ";
			endif;
			
			//---Busca por grupo de compra-----------------------------
			if($pesq['buscagrupo']!=0):
				$where = 'and p.Purchasing_group = '.$pesq['buscagrupo'];
			endif;
			
			//---Busca por fornecedor----------------------------------
			if($pesq['buscafor']!=0):
				$where .= ' and p.ID_CLIENTE_FORNECEDOR = '.$pesq['buscafor'];
			endif;
			
			//---Busca por periodo-------------------------------------
			$periodo2	= date("Y")."-".(date("m"))."-01";
			
			if(date("m") > $pesq['periodo']):
				$periodo = date("Y")."-".(date("m")-$pesq['periodo'])."-01";
			else:
				$periodo = (date("Y")-1)."-".((date("m")+12)-$pesq['periodo'])."-01";
			endif;			
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			
			$select->from(array('p'=>'produtos'), array('p.ID as idproduto','p.CODIGO', 'EXTRACT(MONTH FROM r.data) as mes', 'sum(r.qtped) as qtvenda','sum(r.qtvend) as qtv'))
		        ->join(array('g'=>'tb_gruposprodsub'), 'g.id = p.id_gruposprodsub')
		        ->joinLeft(array('r'=>'tb_relatoriodevendas'), 'r.id_prod = p.ID and r.data > "'.$periodo.'" and r.data < "'.$periodo2.'"')
		        ->where("p.situacao != 2 and p.ID_CLIENTE_FORNECEDOR !='' and p.ID_CLIENTE_FORNECEDOR is not null and p.ID_CLIENTE_FORNECEDOR !='0' ".$where)
		        ->group('p.ID')
		        ->group('r.data')
		        ->order('qtvenda desc')
		        ->order('p.codigo_mask');
			
			$stmt = $db->query($select);
			return $stmt->fetchAll();										
		}
		
		/*-- Busca produtos agrupados para gera Curva de venda ----------------------------------
		 * Essa busca é somente para a geracao da curca ABC dos produtos
		 * usado em relatoriosvendascurva
		 */
		public function buscaProdcurvaagrupados($pesq){
			//---Busca por grupo - subgrupo---------------------------
			if ($pesq['buscagruposub']!=0):
				$where = " and p.id_gruposprodsub = ".$pesq['buscagruposub'];
			elseif ($pesq['grupovenda']!=0):
				foreach (GruposprodBO::listaGruposprodutossub($pesq['grupovenda']) as $listsubg):
					$idsg .= $listsubg->id.",";
				endforeach;
				$where = " and p.id_gruposprodsub in (".substr($idsg, 0,-1).") ";
			endif;
			
			//---Busca por grupo de compra-----------------------------
			if($pesq['buscagrupo']!=0):
				$where = 'and p.Purchasing_group = '.$pesq['buscagrupo'];
			endif;
			
			//---Busca por fornecedor----------------------------------
			if($pesq['buscafor']!=0):
				$where .= ' and p.ID_CLIENTE_FORNECEDOR = '.$pesq['buscafor'];
			endif;
			
			//---Busca por periodo-------------------------------------
			$periodo2	= date("Y")."-".(date("m"))."-01";
			
			if(date("m") > $pesq['periodo']):
				$periodo = date("Y")."-".(date("m")-$pesq['periodo'])."-01";
			else:
				$periodo = (date("Y")-1)."-".((date("m")+12)-$pesq['periodo'])."-01";
			endif;			
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			
			$select->from(array('p'=>'produtos'), array('p.ID','p.CODIGO', 'sum(r.qtped) as qtvenda'))
		        ->join(array('g'=>'tb_gruposprodsub'), 'g.id = p.id_gruposprodsub')
		        ->joinLeft(array('r'=>'tb_relatoriodevendas'), 'r.id_prod = p.ID and r.data > "'.$periodo.'" and r.data < "'.$periodo2.'"')
		        ->where("p.situacao != 2 and p.ID_CLIENTE_FORNECEDOR !='' and p.ID_CLIENTE_FORNECEDOR is not null and p.ID_CLIENTE_FORNECEDOR !='0' ".$where)
		        ->group('p.ID')
		        ->order('qtvenda desc')
		        ->order('p.codigo_mask')
				->limit(20);
			
			$stmt = $db->query($select);
			return $stmt->fetchAll();										
		}
		
		
		//--Busca media vendas produtos---------------------------------
		public function buscaMediaprodvendas($pesq){
			$bo 	= new PedidosvendaModel();
			$bor	= new RelatoriosvendasModel();
			
			if(date("m") > $pesq['dt']):
				$periodo = date("Y")."-".(date("m")-$pesq['dt'])."-01";
			else:
				$periodo = (date("Y")-1)."-".((date("m")+12)-$pesq['dt'])."-01";
			endif;
			
			return $bor->fetchAll('data > "'.$periodo.'" and id_prod = '.$pesq["idprod"]);
											
		}
		
		//--Busca compra produtos---------------------------------
		/* Usado em VendaController::buscamediacompraAction */
		public function buscaCompraprod($pesq){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('pd'=>'pedidos_compra','*'),
			        array('pd.ID as IDPED','DATE_FORMAT(pd.DATA_HORA,"%d/%m/%Y") as dataped', 'pc.QUANTIDADE as qtped','(select sum(e.qt) from tb_entradaztl_prod e, tb_entradaztl en where en.id = e.id_entradaztl and e.id_prodped = pc.ID and en.sit =1) as qtent'))
			        ->join(array('pc'=>'produtos_pedidos_compra'), 'pc.ID_PEDIDO_COMPRA = pd.ID')
			        //->joinLeft(array('e'=>'tb_entradaztl_prod'), 'e.id_prodped = pc.ID')
			        //->join(array('en'=>'tb_entradaztl'), 'en.id = e.id_entradaztl and en.sit = 1')
			        ->where("pc.sit = false and pd.STATUS != 'FINALIZADO' and pd.sit = 1 and pc.ID_PRODUTO = ".$pesq['idprod'])
			        ->group('pd.ID');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();	
											
		}
		
		//--Lista produtos Com Fornecedor---------------------------------
		public function listaProdutosfornecedor($pesq){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('p'=>'produtos','*'),
			        array('p.ID as IDPROD', 'p.CODIGO','p.MOEDA','p.DESCRICAO','p.situacao','p.CUSTO_VALOR','c.EMPRESA'))
			        ->join(array('c'=>'clientes'), 'c.ID = p.ID_CLIENTE_FORNECEDOR')
			        ->where("CODIGO = '".$pesq['q']."'");
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
								
		}
		
		//--Lista produtos Com Fornecedor, se houver---------------------------------
		public function listaProdutosfornecedorleft($pesq){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('p'=>'produtos','*'),
			        array('p.ID as IDPROD', 'p.CODIGO','p.MOEDA','p.DESCRICAO','p.situacao','p.CUSTO_VALOR','c.EMPRESA'))
			        ->joinLeft(array('c'=>'clientes'), 'c.ID = p.ID_CLIENTE_FORNECEDOR')
			        ->where("CODIGO = '".$pesq['q']."'");
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
								
		}
		
		public function listaMontadoras(){
			$bo = new MontadoraModel();
			return $bo->fetchAll("ID is not NULL","NOME asc");
		}
		
		public function listaVeiculos($params){
			$bo = new VeiculosModel();
			return $bo->fetchAll("id_montadora = ".$params, "no_modelo asc");
		}
		
		public function listaAllveiculos(){
			$bo = new VeiculosModel();
			return $bo->fetchAll();
		}
		
		public function listaHistoricopcvenda($params){
			$boprod	= new ProdutosModel();
			$bohisv	= new HistoricopcvendaModel();
			if(!empty($params)):
				return $bohisv->fetchAll("id_produtos = ".$params,"id desc");
			endif;
		}
		
		public function listaHistoricopccompra($params){
			$boprod	= new ProdutosModel();
			$bohisc	= new HistoricopccompraModel();
			if(!empty($params)):
				return $bohisc->fetchAll("id_produtos = ".$params,"id desc");
			endif;
		}
		
		public function listaHistoricopcvendaultimo($params,$moeda){
			$boprod	= new ProdutosModel();
			$bohisv	= new HistoricopcvendaModel();
			if(!empty($params)):
				return $bohisv->fetchAll("id_produtos = ".$params." and moeda = '".$moeda."'","id desc","1");
			endif;
		}
		
		public function listaHistoricopccompraultimo($params){
			$boprod	= new ProdutosModel();
			$bohisc	= new HistoricopccompraModel();
			if(!empty($params)):
				return $bohisc->fetchAll("id_produtos = ".$params,"id desc","1");
			endif;
		}
		
		public function cadastraProdutos($params){
			date_default_timezone_set('America/Sao_Paulo');
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			try{
				$boprod	= new ProdutosModel();
				$bokit	= new KitsModel();
				$bovei	= new VeiculosprodModel();
				$bohisv	= new HistoricopcvendaModel();
				$bohisc	= new HistoricopccompraModel();
				$boarq	= new ProdarquivosModel();
				$boest	= new EstoqueModel();
				$bocat	= new ProdutoscatModel();
				$bomed	= new ProdutosmediasModel();
				$bocref	= new RefcruzadaModel();
				$bocros = new CodigoscrossModel();
							
				if(empty($params['id_produto'])):
					foreach ($boprod->fetchAll("CODIGO = '".$params['codigo']."'") as $listaCod);
					if(!empty($listaCod)):
						$arrayerro = array('sit' 	=> false, 'texto' 	=> "Código já cadastrado!");
						return $arrayerro;						
					endif;
				endif;			
				
				$array['CODIGO']  				= strtoupper($params['codigo']);
				$array['codigo_ean']			= $params['ean'];
				$array['DESCRICAO']   			= $params['descricao'];
				$array['APLICACAO']   			= $params['detproduto'];
				$array['COMPONENTE']	   		= $params['tipoprod'];			
				$array['PRECO_UNITARIO']   		= str_replace(",",".",str_replace(".","",$params['preco_brl']));
				$array['PREVISAO']				= $params['previsao'];
				$array['PRECO_UNITARIO_USD']	= str_replace(",",".",str_replace(".","",$params['preco_usd']));
				$array['PRECO_UNITARIO_RMB']	= str_replace(",",".",str_replace(".","",$params['preco_rmb']));
				$array['PRECO_UNITARIO_EUR']	= str_replace(",",".",str_replace(".","",$params['preco_eur']));
				$array['ID_CLIENTE_FORNECEDOR']	= $params['fornecedor'];
				$array['MOEDA']					= $params['moeda'];
				$array['CUSTO_VALOR']			= str_replace(",",".",str_replace(".","",$params['preco_compra']));
				
				$array['PESO']					= $params['peso'];
				$array['id_ncm']				= $params['classfiscal'];
				
				$array['M_INNER']				= $params['altura'];
				$array['M_OUTER']				= $params['largura'];
				$array['M_HIGH']				= $params['profundidade'];
				$array['ball']					= $params['roletes'];
				$array['Purchasing_group']		= $params['grupocompra'];
				$array['codigo_mask']			= $params['mascara'];
				$array['estriado_macho_d']		= $params['machod_homo'];
				$array['estriado_macho_mm']		= $params['machomm_homo'];
				$array['estriado_femea_d']		= $params['femead_homo'];
				$array['estriado_femea_mm']		= $params['femeamm_homo'];
				$array['ins_aperto_homo']		= $params['instaperto_homo'];
				$array['diametro_homo']			= $params['diametro_homo'];
				$array['raio_porca_homo']		= $params['raio_homo'];
				$array['aperto_homo']			= $params['aperto_homo'];
				$array['medida_inner_desl']		= $params['junta_interno'];
				$array['medida_outer_desl']		= $params['junta_externo'];
				$array['medida_high_desl']		= $params['junta_altura'];
				$array['medida_teeth_desl']		= $params['junta_dentes'];
				$array['medida_inner_cru']		= $params['cru_interno'];
				$array['medida_high_cru']		= $params['cru_externo'];
				$array['medida_teeth_cru']		= $params['cru_dentes'];
				$array['valor_promo']			= str_replace(",",".",str_replace(".","",$params['preco_promo']));
				$array['valor_desc']			= $params['preco_desc'];
				$array['id_gruposprodsub']		= $params['buscagruposub'];
				$array['pl_prod_desc']			= $params['pl_desc'];
				$array['situacao']				= $params['sit'];
				$array['observacao']			= $params['observacaoprod'];
				$array['purchasing_det']		= $params['detcompra'];
				$array['unidade']				= $params['unidade'];
				$array['origem']				= $params['origem'];
				$array['participapromo']		= $params['participapromo'];
				$array['descpromo']				= $params['descpromo'];
				$array['id_produtosclasses']	= (empty($params['classes'])) ? NULL : $params['classes'];
				
							
				if(!empty($params['precoajuste']) || !empty($params['ajusteperc'])):			
					$array['precoajuste']			= str_replace(",",".",str_replace(".","",$params['precoajuste']));
					$array['percentajuste']			= str_replace(",",".",str_replace(".","",$params['ajusteperc']));
					$array['dt_ajuste'] 			= substr($params["dataajuste"],6,4).'-'.substr($params["dataajuste"],3,2).'-'.substr($params["dataajuste"],0,2);
					$array['id_userajuste']			= $usuario->id;
					$array['dt_cadajuste'] 			= date("Y-m-d H:i:s");
				endif;
			
				if(!empty($params['id_produto'])):
					$boprod->update($array,"ID = ".$params['id_produto']);
					$idprod = $params['id_produto'];
					
					$arraycros = array(
						'codigo'		=> strtoupper($params['codigo']),
						'codigo_mask'	=> $params['mascara']
					);
					
					$bocros->update($arraycros,"id_prod = ".$idprod);
					
				else:
					$array['dt_cadastro']  			= date("Y-m-d H:i:s");
					$idprod = $boprod->insert($array);
					
					$arraycros = array(
						'codigo'		=> strtoupper($params['codigo']),
					    'sit'			=> true,
					    'id_fabricante'	=> 1,
					    'codigo_mask'	=> $params['mascara'],
					    'id_prod'		=> $idprod
					);
					
					$bocros->insert($arraycros);
					
					$arrayestq['id_prod'] 			= $idprod;
					$arrayestq['qt_atual'] 			= 0;
					$arrayestq['qt_atualizacao'] 	= 0;
					$arrayestq['id_atualizacao'] 	= 0;
					$arrayestq['dt_atualizacao'] 	= date("Y-m-d H:i:s");
					$arrayestq['tipo'] 				= "NOVO";
					$arrayestq['id_user'] 			= $usuario->id;
					
					$boest->insert($arrayestq);
					
				endif;
			
				try{
					$bokit->delete("id_prod = ".$idprod);
					foreach (ProdutosBO::listaallProdutos() as $listprods):
					if(!empty($params['kit_'.$listprods->ID])):
					$arraykit['id_prod']	=	$idprod;
					$arraykit['id_prodkit']	=	$listprods->ID;
					$arraykit['qt']			=	str_replace(",",".",$params['kit_'.$listprods->ID]);
					$bokit->insert($arraykit);
					endif;
					endforeach;
				}catch (Zend_Exception $e){
					$arrayerro = array('sit' 	=> false, 'texto' 	=> "Erro ao cadastrar os componentes!");
					return $arrayerro;
				}
					
					
				try{
					$bovei->delete("id_produto = ".$idprod);
					foreach (ProdutosBO::listaAllveiculos() as $listveic):
					if(!empty($params['veiculo_'.$listveic->id])):
					$arrayvei = array();
					$arrayvei['id_produto']	=	$idprod;
					$arrayvei['id_veiculo']	=	$listveic->id;
					$arrayvei['ano_ini']	=	$params['anoini_'.$listveic->id];
					$arrayvei['ano_fim']	=	$params['anofin_'.$listveic->id];
					$bovei->insert($arrayvei);
					endif;
					endforeach;
				}catch (Zend_Exception $e){
					$arrayerro = array('sit' 	=> false, 'texto' 	=> "Erro ao cadastrar a aplicação!");
					return $arrayerro;
				}
					
				try{
					//---Historico venda moeda BRL---------------------------------------------------
					if(!empty($params['preco_brl']) and ($params['atprecobrl']==1)):
					$arrayhistv['data'] 		= date("Y-m-d H:i:s");
					$arrayhistv['moeda'] 		= "BRL";
					$arrayhistv['valor'] 		= str_replace(",",".",str_replace(".","",$params['preco_brl']));
					$arrayhistv['id_produtos'] 	= $idprod;
					$arrayhistv['id_func'] 		= $usuario->id;
					$bohisv->insert($arrayhistv);
					endif;
				
					//---Historico venda moeda USD---------------------------------------------------
					if(!empty($params['preco_usd']) and ($params['atprecousd']==1)):
					$arrayhistv['data'] 		= date("Y-m-d H:i:s");
					$arrayhistv['moeda'] 		= "USD";
					$arrayhistv['valor'] 		= str_replace(",",".",str_replace(".","",$params['preco_usd']));
					$arrayhistv['id_produtos'] 	= $idprod;
					$arrayhistv['id_func'] 		= $usuario->id;
					$bohisv->insert($arrayhistv);
					endif;
				
					//---Historico venda moeda RMB---------------------------------------------------
					if(!empty($params['preco_rmb']) and ($params['atprecormb']==1)):
					$arrayhistv['data'] 		= date("Y-m-d H:i:s");
					$arrayhistv['moeda'] 		= "RMB";
					$arrayhistv['valor'] 		= str_replace(",",".",str_replace(".","",$params['preco_rmb']));
					$arrayhistv['id_produtos'] 	= $idprod;
					$arrayhistv['id_func'] 		= $usuario->id;
					$bohisv->insert($arrayhistv);
					endif;
				
					//---Historico venda moeda EUR---------------------------------------------------
					if(!empty($params['preco_eur']) and ($params['atprecoeur']==1)):
					$arrayhistv['data'] 		= date("Y-m-d H:i:s");
					$arrayhistv['moeda'] 		= "EUR";
					$arrayhistv['valor'] 		= str_replace(",",".",str_replace(".","",$params['preco_eur']));
					$arrayhistv['id_produtos'] 	= $idprod;
					$arrayhistv['id_func'] 		= $usuario->id;
					$bohisv->insert($arrayhistv);
					endif;
				
					//---Historico Compra---------------------------------------------------
					if(!empty($params['preco_compra']) and ($params['pchist']==1)):
					$arrayhistc['data'] 		= date("Y-m-d H:i:s");
					$arrayhistc['moeda'] 		= $params['moeda'];
					$arrayhistc['valor'] 		= str_replace(",",".",str_replace(".","",$params['preco_compra']));
					$arrayhistc['id_produtos'] 	= $idprod;
					$arrayhistc['id_func'] 		= $usuario->id;
					$arrayhistc['id_fornecedor']= $params['fornecedor'];
					$bohisc->insert($arrayhistc);
					endif;
				
				}catch (Zend_Exception $e){
					$arrayerro = array('sit' 	=> false, 'texto' 	=> "Erro ao cadastrar os históricos!");
					return $arrayerro;
				}
					
				try{
					$bomed->delete("id_prod = ".$idprod);
				
					$arraymed['cubo_eixo']				= str_replace(",",".",str_replace(".","",$params['cuboeixo']));
					$arraymed['cubo_denteshomo']		= $params['cubodenteshomo'];
					$arraymed['cubo_tipoparafuso']		= $params['cuboprisioneiros'];
					$arraymed['cubo_qtfuroparafuso']	= $params['cuboqtprisioneiros'];
					$arraymed['cubo_tipoabs']			= $params['cuboabs'];
					$arraymed['cubo_dentescoroaabs']	= $params['cubodentescoroaabs'];
					$arraymed['cubo_alturacoroaabs']	= str_replace(",",".",str_replace(".","",$params['cuboaltcoroaabs']));
					$arraymed['cubo_altconjrolante']	= str_replace(",",".",str_replace(".","",$params['cuboaltconjrol']));
					$arraymed['cubo_altura']			= str_replace(",",".",str_replace(".","",$params['cuboaltura']));
					$arraymed['cubo_externo']			= str_replace(",",".",str_replace(".","",$params['cuboexterno']));
					$arraymed['cubo_geracao']			= $params['cubogeracao'];
					$arraymed['cubo_construcao']		= $params['cubocontrucao'];
					$arraymed['id_prod']				= $idprod;
				
					$bomed->insert($arraymed);
				}catch (Zend_Exception $e){
					$arrayerro = array('sit' 	=> false, 'texto' 	=> "Erro ao cadastrar as medidas!");
					return $arrayerro;
				}
					
					
				try{
					//---Imagem 1-------------------------------
					$arquivo1 = isset($_FILES['imagen1']) ? $_FILES['imagen1'] : FALSE;
					$ext1 = end(explode(".",$_FILES['imagen1']['name']));
						
					if(($ext1=="jpg")||($ext1=="JPG")||($ext1=="jpeg")||($ext1=="JEPG")):
						
					$pasta = Zend_Registry::get('pastaPadrao')."public/images/imgprodutos/".$idprod."/";
						
					if (!(is_dir($pasta))){
						if(!(mkdir($pasta, 0777))){
							echo ("Alerta: pasta de upload nao existe, e nao pode ser criada");
							echo $pasta;
							return $this;
						}
					}
					 
					if(!(is_writable($pasta))){
						echo ("Alerta: pasta sem permissao de escrita");
						return $this;
					}
				
					$img = imagecreatefromjpeg($arquivo1['tmp_name']);
					$x   = imagesx($img);
					$y   = imagesy($img);
					$altura = (400 * $y)/$x;
					$nova = imagecreatetruecolor(400, $altura);
					imagecopyresampled($nova, $img, 0, 0, 0, 0, 400, $altura, $x, $y);
						
					imagejpeg($nova,$pasta."imagem1.jpg");
					endif;
				
					//---Imagem 2-------------------------------
					$arquivo2 = isset($_FILES['imagen2']) ? $_FILES['imagen2'] : FALSE;
					$ext2 = end(explode(".",$_FILES['imagen2']['name']));
					 
					if(($ext2=="jpg")||($ext2=="JPG")||($ext2=="jpeg")||($ext2=="JEPG")):
				
					$pasta = Zend_Registry::get('pastaPadrao')."public/images/imgprodutos/".$idprod."/";
						
					if (!(is_dir($pasta))){
						if(!(mkdir($pasta, 0777))){
							echo ("Alerta: pasta de upload nao existe, e nao pode ser criada");
							echo ("<br />");
							echo ($pasta);
							return $this;
						}
					}
					 
					if(!(is_writable($pasta))){
						echo ("Alerta: pasta das imagens sem permissao de escrita");
						return $this;
					}
				
					$img = imagecreatefromjpeg($arquivo2['tmp_name']);
					$x   = imagesx($img);
					$y   = imagesy($img);
					$altura = (400 * $y)/$x;
					$nova = imagecreatetruecolor(400, $altura);
					imagecopyresampled($nova, $img, 0, 0, 0, 0, 400, $altura, $x, $y);
						
					imagejpeg($nova,$pasta."imagem1_".$idprod.".jpg");
					 
					 
					endif;
				}catch (Zend_Exception $e){
					$arrayerro = array('sit' 	=> false, 'texto' 	=> "Erro ao salvar as imagens!");
					return $arrayerro;
				}
				
				try{
					//---Arquivos-------------------------------
					for($i=1;$i<=$params["qtarquivo"];$i++):
				
					$arquivo4 = isset($_FILES['arquivo_'.$i]) ? $_FILES['arquivo_'.$i] : FALSE;
					$pasta = Zend_Registry::get('pastaPadrao')."public/projetoprod/".$idprod."/";
				
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
				
					 
					if(is_uploaded_file($arquivo4['tmp_name'])){
						if (move_uploaded_file($arquivo4["tmp_name"], $pasta . $arquivo4['name'])) {
							//print "Upload executado com sucesso!!!<br />";
							//Zend_Debug::dump($arquivo1);
							$arrayarq['arquivo'] = $arquivo4['name'];
							$arrayarq['id_prod'] = $idprod;
							$boarq->insert($arrayarq);
						} else {
							echo ("Alerta: Nao foi possivel fazer o upload para $pasta");
							return $this;
						}
					}else{
						//echo "erro ao carregar imagem";
					}
				
					endfor;
				}catch (Zend_Exception $e){
					$arrayerro = array('sit' 	=> false, 'texto' 	=> "Erro ao salvar os anexos!");
					return $arrayerro;
				}
				
				
				try{
					//--- Exibicao no catalogo especial -----------------------------------------
					$bocat->delete("id_prod = ".$idprod);
					if(!empty($params['pesado'])):
					$arraypes['id_prod']		= $idprod;
					$arraypes['cod_cat']		= 1;
					$bocat->insert($arraypes);
					endif;
				
					if(!empty($params['6000'])):
					$arrayseis['id_prod']		= $idprod;
					$arrayseis['cod_cat']		= 2;
					$bocat->insert($arrayseis);
					endif;
				
					if(!empty($params['transmissao'])):
					$arraytrans['id_prod']		= $idprod;
					$arraytrans['cod_cat']		= 3;
					$bocat->insert($arraytrans);
					endif;
				}catch (Zend_Exception $e){
					$arrayerro = array('sit' 	=> false, 'texto' 	=> "Erro ao cadastrar o produto no catalogo especial!");
					return $arrayerro;
				}
				 
				try{
					//--- Log de produtos --------------------------
					if(!empty($params['id_produto'])):
					$tp = 4;
					else:
					$tp = 2;
					endif;
					 
					ProdutosBO::cadatraLogalteraprodutos($idprod,$tp);
				}catch (Zend_Exception $e){
					$arrayerro = array('sit' 	=> false, 'texto' 	=> "Erro ao salvar o log de altereções!");
					return $arrayerro;
				}
				
				
			}catch (Zend_Exception $e){
			    $arrayerro = array('sit' 	=> false, 'texto' 	=> "Erro ao cadastrar o produto!");
				return $arrayerro;
			}	
			
			$arrayerro = array(
				'sit' 		=> true, 
			    'texto' 	=> "Produtos salvo com sucesso!",
			    'idproduto'	=> $idprod
			);
			
			return $arrayerro;       
		}
		
		//--- Guarda informacoes que foram alteracas no cadastro do produto ------
		/*-- usado em ProdutosBO::cadastraProdutos*/
		function cadatraLogalteraprodutos($idproduto,$tp){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			foreach (ProdutosBO::listaProdutos($idproduto) as $prod);
			
			$array = array();
			$array['CODIGO']			= $prod->CODIGO;
			
			if(!empty($prod->id_gruposprodsub)):
				foreach (GruposprodBO::buscaSubgrupo($prod->id_gruposprodsub) as $listsubg);
				foreach (GruposprodBO::listaGruposprodutos() as $list):
					if($listsubg->id_gruposprod == $list->id) $array['GRUPO'] = strtoupper($list->descricao);
				endforeach;
			endif;
			
			if(!empty($prod->id_gruposprodsub)):
				foreach (GruposprodBO::buscaSubgrupo($prod->id_gruposprodsub) as $list);
				$array['SUBGRUPO'] = $list->descricao;				
			endif;
			
			$array['DESCRICAO'] = stripcslashes($prod->DESCRICAO);
			$array['DETALHES'] 	= stripcslashes($prod->APLICACAO);
			
			$veiculos = "";
			
			foreach (ProdutosBO::listarVeiculosprod($idproduto) as $listveic):
				$veiculos .=  'MONTADORA: '.$listveic->NOME;
				$veiculos .=  ' - VEICULO: '.$listveic->no_modelo;
				
				if(($listveic->ano_ini == "") && ($listveic->ano_fim != "")){
					$ano = "< ".$listveic->ano_fim;
				}else if(($listveic->ano_fim == "") && ($listveic->ano_ini != "")){
					$ano = $listveic->ano_ini." > ";
				}else if(($listveic->ano_ini == "") && ($listveic->ano_fim == "")){
					$ano = "Todos";
				}else if($listveic->ano_ini < $listveic->ano_fim){
					$ano = $listveic->ano_ini." > ".$listveic->ano_fim;
				}else if($listveic->ano_ini == $listveic->ano_fim){
					$ano = $listveic->ano_ini;
				}
				
				$veiculos .=  ' - ANO: '.$ano."<br />";
			endforeach;	
					
			if($veiculos!=""):
				$array['APLICACAO'] = $veiculos;
			endif;
			
			foreach (ProdutosBO::listaExibircatalogo($idproduto) as $catalogo):
				if($catalogo->cod_cat==1):
					$array['EXIBIR NO CATALOGO'] 		= "Linha pesada";
				elseif($catalogo->cod_cat==2):
					$array['EXIBIR NO CATALOGO'] 		= "Linha 6000";
				endif;
			endforeach;		
			
			
			if($prod->M_INNER!="") $array['ROL INTERNO'] 			= $prod->M_INNER;
			if($prod->M_OUTER!="") $array['ROL EXTERNO'] 			= $prod->M_OUTER;
			if($prod->M_HIGH!="") $array['ROL ALTURA'] 				= $prod->M_HIGH;
			if($prod->ball!="") $array['ROL ESF/ROL'] 				= $prod->ball;
			
			if($prod->estriado_macho_d!="") $array['HOMO ESTRIADO MACHO D'] = $prod->estriado_macho_d;
			if($prod->estriado_macho_mm!="") $array['HOMO ESTRIADO MACHO MM'] = $prod->estriado_macho_mm;
			if($prod->estriado_femea_d!="") $array['HOMO ESTRIADO FEMEA D'] = $prod->estriado_femea_d;
			if($prod->estriado_femea_mm!="") $array['HOMO ESTRIADO FEMEA MM'] = $prod->estriado_femea_mm;
			if($prod->diametro_homo!="") $array['HOMO DIAMETRO'] 			= $prod->diametro_homo;
			if($prod->raio_porca_homo!="") $array['HOMO RAIO'] 				= $prod->raio_porca_homo;
			if($prod->aperto_homo!="") $array['HOMO APERTO'] 				= $prod->aperto_homo;
			if($prod->ins_aperto_homo!="") $array['HOMO INS APERTO']		= $prod->ins_aperto_homo;
			
			if($prod->medida_inner_desl!="") $array['DESL INTERNO']			= $prod->medida_inner_desl;
			if($prod->medida_outer_desl!="") $array['DESL EXTERNA']			= $prod->medida_outer_desl;
			if($prod->medida_high_desl!="") $array['DESL ALTURA']			= $prod->medida_high_desl;
			if($prod->medida_teeth_desl!="") $array['DESL DENTES']			= $prod->medida_teeth_desl;
			
			if($prod->medida_inner_cru!="") $array['CRU INTERNO']			= $prod->medida_inner_cru;
			if($prod->medida_high_cru!="") $array['CRU ALTURA']				= $prod->medida_high_cru;
			if($prod->medida_teeth_cru!="") $array['CRU DENTES']			= $prod->medida_teeth_cru;
			
			
			if(count(ProdutosBO::buscaMedidasprod($idproduto))>0):
				foreach (ProdutosBO::buscaMedidasprod($idproduto) as $medidas);
			endif;			
			
			if(!empty($medidas->cubo_eixo)) $array['CUBO EIXO ROLAMENTO']	= $medidas->cubo_eixo;
			if(!empty($medidas->cubo_altura)) $array['CUBO ALTURA']			= $medidas->cubo_altura;
			if(!empty($medidas->cubo_externo)) $array['CUBO EXTERNO']			= $medidas->cubo_externo;
			if(!empty($medidas->cubo_denteshomo)) $array['CUBO DENTES HOMO']		= $medidas->cubo_denteshomo;
			if(!empty($medidas->cubo_geracao)) $array['CUBO GERACAO']			= $medidas->cubo_geracao;
			if(!empty($medidas->cubo_construcao)) $array['CUBO CONSTRUCAO']			= $medidas->cubo_construcao;
			if(!empty($medidas->cubo_altconjrolante)) $array['CUBO ALT/CONJ/ROLANTE'] = $medidas->cubo_altconjrolante;			
			
			if($medidas->cubo_tipoabs==1) $array['CUBO TIPO ABS']			= "Magnético";
			if($medidas->cubo_tipoabs==2) $array['CUBO TIPO ABS']			= "Cabo conector";
			if($medidas->cubo_tipoabs==3) $array['CUBO TIPO ABS']			= "Coroa";
			
			if(!empty($medidas->cubo_alturacoroaabs)) $array['CUBO ALT COROA ABS'] 	= $medidas->cubo_alturacoroaabs;
			if(!empty($medidas->cubo_dentescoroaabs)) $array['CUBO DEN COROA ABS'] 	= $medidas->cubo_dentescoroaabs;
			
			if($medidas->cubo_tipoparafuso==1) $array['CUBO PRISIONEIROS'] = "Furos";
			if($medidas->cubo_tipoparafuso==2) $array['CUBO PRISIONEIROS'] = "Parafusos";
			
			if(!empty($medidas->cubo_qtfuroparafuso)) $array['CUBO QT FUROS'] 		= $medidas->cubo_qtfuroparafuso;			
			
			LogBO::cadastraLog("Cadastro/Produtos",$tp,$usuario->id,$idproduto,"COD ".$prod->CODIGO,$array);
			
		}
		
		function cadastraProdutoschina($params){
			date_default_timezone_set('America/Sao_Paulo');
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			$boprod	= new ProdutosModel();
			$bohisc = new HistoricopccomprachinaModel();
						
			
			$array['id_cliente_fornecedor_shuntai']		= $params['fornecedorkang'];
			$array['supplier_code']						= $params['codekang'];
			$array['tipo_moeda_shuntai']				= $params['moedakang'];
			$array['custo_valor_shuntai']				= str_replace(",",".",str_replace(".","",$params['precokang']));
			
			$array['id_cliente_shuntai']		= $params['fornecedortai'];
			$array['cod_shuntai']				= $params['codetai'];
			$array['moeda_shuntai']				= $params['moedatai'];
			$array['custo_shuntai']				= str_replace(",",".",str_replace(".","",$params['precotai']));
			
			if($params['hscode']!=0):
				$array['id_hscode']					= $params['hscode'];
			endif;			
			
			if($params['material']!=0):
				$array['id_produtosmaterial']					= $params['material'];
			endif;
			
			if(!empty($params['id_produto'])):
				$boprod->update($array,"ID = ".$params['id_produto']);
				$idprod = $params['id_produto'];
			endif;
			
			//---Historico Compra---------------------------------------------------
			
			if($params['precoshunkang']== 1):
				$arrayhistc['data'] 		= date("Y-m-d H:i:s");
				$arrayhistc['moeda'] 		= $params['moedakang'];
				$arrayhistc['valor'] 		= str_replace(",",".",str_replace(".","",$params['precokang']));
				$arrayhistc['id_produtos'] 	= $idprod;
				$arrayhistc['id_func'] 		= $usuario->id;
				$arrayhistc['id_fornecedor']= $params['fornecedorkang'];
				$bohisc->insert($arrayhistc);
			endif;
			
			if($params['precoshuntai']== 1):
				$arrayhistc['data'] 		= date("Y-m-d H:i:s");
				$arrayhistc['moeda'] 		= $params['moedatai'];
				$arrayhistc['valor'] 		= str_replace(",",".",str_replace(".","",$params['precotai']));
				$arrayhistc['id_produtos'] 	= $idprod;
				$arrayhistc['id_func'] 		= $usuario->id;
				$arrayhistc['id_fornecedor']= $params['fornecedortai'];
				$bohisc->insert($arrayhistc);
			endif;
			
			return $idprod;
	         
		}
				
		function listarKitprod($prod){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('p'=>'produtos','*'),
			        array('p.ID as IDPROD', 'p.CODIGO','p.MOEDA','p.DESCRICAO','p.situacao','p.CUSTO_VALOR','c.EMPRESA','k.qt','p.id_gruposprodsub'))
			        ->join(array('k'=>'tb_kits'), 'p.ID = k.id_prodkit')
			        ->join(array('c'=>'clientes'), 'c.ID = p.ID_CLIENTE_FORNECEDOR')
			        ->where('k.id_prod = '.$prod);
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		function listarVeiculosprod($prod){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('v'=>'tb_produto_veiculo','*'),
			        array('tv.id as idveiculo','v.ano_ini', 'v.ano_fim','tv.no_modelo','m.NOME'))
			        ->join(array('tv'=>'tb_veiculo'), 'tv.id = v.id_veiculo')
			        ->join(array('m'=>'tb_montadora'), 'tv.id_montadora = m.ID')
			        ->where('v.id_produto = '.$prod)
			        ->order("m.NOME asc")
			        ->order("tv.no_modelo asc");
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		function buscaHistoricopreco($prod){
			$bo 	= new ProdutosModel();
			$boh	= new HistoricopcvendaModel();
			return $boh->fetchAll("id_produtos = ".$prod,"id desc",5);
		}
		
		function listarHistoricovenda($prod){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('p'=>'tb_pedidos_prod','*'),
			        array('pd.id as idpedido','p.qt','pd.data_vend','c.EMPRESA'))
			        ->join(array('pd'=>'tb_pedidos'), 'pd.id = p.id_ped')
			        ->join(array('c'=>'clientes'), 'c.ID = pd.id_parceiro')
			        ->where('pd.status = "ped" and p.id_prod = '.$prod)
			        ->order("pd.id desc")
			        ->limit(5);
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		function listarHistoricoforn($prod){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('h'=>'tb_historicopccompra','*'),
			        array('h.data','h.moeda as moed','h.valor','c.EMPRESA','h.balls','h.id as idhistc'))
			        ->join(array('c'=>'clientes'), 'c.ID = h.id_fornecedor')
			        ->where('h.id_produtos = '.$prod)
			        ->order("h.id desc")
			        ->limit(5);
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		function listarHistoricofornchina($prod){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('h'=>'tb_historicopccomprachina','*'),
			        array('h.data','h.moeda as moed','h.valor','c.EMPRESA','h.balls','h.id as idhistc'))
			        ->join(array('c'=>'clientes'), 'c.ID = h.id_fornecedor')
			        ->where('h.id_produtos = '.$prod)
			        ->order("h.id desc")
			        ->limit(5);
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		function listarHistoricocompra($prod){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('p'=>'produtos_pedidos_compra','*'),
			        array('pd.ID as idped','pd.DATA_HORA','pd.STATUS','pd.data_entrega','p.QUANTIDADE','p.PRECO_UNITARIO_USD','c.EMPRESA'))
			        ->joinLeft(array('c'=>'clientes'), 'c.ID = p.SUPPLIER')
			        ->join(array('pd'=>'pedidos_compra'), 'pd.ID = p.ID_PEDIDO_COMPRA')
			        ->where('pd.empresa_pedido != "S" and pd.sit = true and p.ID_PRODUTO = '.$prod)
			        ->order("pd.ID desc")
			        ->limit(5);
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		function excluiImagem($idproduto,$posicao){
			if($posicao==1):
		 		//@unlink("/var/www/homologacao/imgs_produtos/$idproduto/imagem".$posicao.".jpg");
		 		//@unlink("/aplic/sites/ztlrolamentos.com.br/imgs_produtos/$idproduto/imagem".$posicao.".jpg");
		 		
		 		@unlink(Zend_Registry::get('pastaPadrao')."public/images/imgprodutos/$idproduto/imagem".$posicao.".jpg");
		 		
		 		
		 	else:
		 		//@unlink("/var/www/homologacao/imgs_produtos/$idproduto/imagem1_".$idproduto.".jpg");
		 		@unlink(Zend_Registry::get('pastaPadrao')."public/images/imgprodutos/$idproduto/imagem1_".$idproduto.".jpg");
		 	endif;
		}
		
		function excluiProjeto($idproduto){
			foreach (ProdutosBO::listaProdutos($idproduto) as $list);
		 	@unlink(Zend_Registry::get('pastaPadrao')."public/projetoprod/projfonte_".$idproduto.".".$list->projeto_fonte);
		 	//@unlink("/var/www/homologacao/admin/projetoprod/projfonte_".$idproduto.".".$list->projeto_fonte);
		}
		
		function excluiProjetopdf($idproduto){
		 	@unlink(Zend_Registry::get('pastaPadrao')."public/projetoprod/projleitura_".$idproduto.".pdf");
		 	//@unlink("/var/www/homologacao/admin/projetoprod/projleitura_".$idproduto.".pdf");
		}
		
		function gravarHistoricoforn($params){
			$bo  = new ProdutosModel();
			$boh = new HistoricopccompraModel();
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			$arrayhistc['data'] 			= substr($params["data_hist"],6,4).'-'.substr($params["data_hist"],3,2).'-'.substr($params["data_hist"],0,2);
			$arrayhistc['moeda'] 			= $params['moedahist'];
			$arrayhistc['valor'] 			= str_replace(",",".",str_replace(".","",$params['preco_hist']));
			$arrayhistc['id_produtos'] 		= $params['id_produto'];
			$arrayhistc['id_func'] 			= $usuario->id;
			$arrayhistc['id_fornecedor']	= $params['fornecedor_hist'];
			$arrayhistc['balls']			= $params['balls'];
			$boh->insert($arrayhistc);
			
		}
		
		function gravarHistoricofornchina($params){
			$bo  = new ProdutosModel();
			$boh = new HistoricopccomprachinaModel();
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			$arrayhistc['data'] 			= substr($params["data_hist"],6,4).'-'.substr($params["data_hist"],3,2).'-'.substr($params["data_hist"],0,2);
			$arrayhistc['moeda'] 			= $params['moedahist'];
			$arrayhistc['valor'] 			= str_replace(",",".",str_replace(".","",$params['preco_hist']));
			$arrayhistc['id_produtos'] 		= $params['id_produto'];
			$arrayhistc['id_func'] 			= $usuario->id;
			$arrayhistc['id_fornecedor']	= $params['fornecedor_hist'];
			$arrayhistc['balls']			= $params['balls'];
			$boh->insert($arrayhistc);
			
			
		}
		
		function removeHistoricofonr($params){
			$bo  = new ProdutosModel();
			$boh = new HistoricopccompraModel();
			$boh->delete("id = ".$params);
		}
		
		function removeHistoricofonrchina($params){
			$bo  = new ProdutosModel();
			$boh = new HistoricopccomprachinaModel();
			$boh->delete("id = ".$params);
		}
		
		
		/*function correcaoProdutoskit(){
			$bo  	= new ProdutosModel();
			$bok	= new KitsModel();
			foreach (ProdutosBO::listaallProdutos() as $listprod):
				if(!empty($listprod->MONTAR_KIT)):
					$kit = explode(";",$listprod->MONTAR_KIT);
					
					for($i=0;$i<sizeof($kit);$i++):
                    	$kiti = explode(':',$kit[$i]);
                    	foreach (ProdutosBO::buscaProdutoscodigo($kiti[0]) as $lisprodcod);
                    	if(!empty($lisprodcod->ID)):
                    		$arrayk['id_prod'] 		= $listprod->ID;
                    		$arrayk['id_prodkit'] 	= $lisprodcod->ID;
                    		$arrayk['qt'] 			= $kiti[1];
                    		$bok->insert($arrayk);
                    		echo "<br>".$lisprod->CODIGO;
                    	endif;                    	
                    endfor;
				endif;
        	endforeach;
		}*/
		
		function listaHiscompraantigo() {
			$bo 	= new ProdutosModel();
			$obh 	= new HistoricopccompraantigoModel();
			return $obh->fetchAll();
		}
		
		
		
		function listaArquivos($idprod){
			$bo		= new ProdutosModel();
			$boa	= new ProdarquivosModel();
			return $boa->fetchAll("id_prod = ".$idprod);
		}
		
		function removeArquivos($id){
			$bo		= new ProdutosModel();
			$boa	= new ProdarquivosModel();
			$boa->delete("id = ".$id);
		}
		
		function listarRelacionamentos($prod){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('p'=>'produtos','*'),
			        array('p.ID as IDPROD', 'p.CODIGO','p.MOEDA','p.DESCRICAO','p.situacao','p.CUSTO_VALOR','c.EMPRESA','k.qt','p.id_gruposprodsub'))
			        ->join(array('k'=>'tb_kits'), 'p.ID = k.id_prod')
			        ->join(array('c'=>'clientes'), 'c.ID = p.ID_CLIENTE_FORNECEDOR')
			        ->where('k.id_prodkit = '.$prod);
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		//---China-----------------------------------
		function listarKitprodchina($prod){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('p'=>'produtos','*'),
			        array('p.ID as IDPROD', 'p.CODIGO','p.DESCRICAO','p.situacao','p.custo_valor_shuntai as custokang','p.tipo_moeda_shuntai as moedakang',
			        'p.custo_shuntai as custotai','p.moeda_shuntai as moedatai','c.EMPRESA as empshukang','cl.EMPRESA as empshuntai','k.qt',
			        'p.id_gruposprodsub','c.ID as idforshunkang','cl.ID as idforshuntai'))
			        ->joinLeft(array('c'=>'clientes'), 'c.ID = p.id_cliente_fornecedor_shuntai')
			        ->joinLeft(array('cl'=>'clientes'), 'cl.ID = p.id_cliente_shuntai')
			        ->join(array('k'=>'tb_kits'), 'p.ID = k.id_prodkit')
			        ->where('k.id_prod = '.$prod);
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		function corrigeProdutospreco(){
			$bo  	= new ProdutosModel();
			$boh	= new HistoricopcvendaModel();
			
			foreach (ProdutosBO::listaallProdutos() as $listprod):
				if($listprod->PRECO_UNITARIO != '0'):
					$array['data']			= '0000-00-00 00:00:00';
					$array['moeda']			= 'BRL';
					$array['valor']			= $listprod->PRECO_UNITARIO;
					$array['id_produtos']	= $listprod->ID;
					$array['id_func']		= 613;
					$boh->insert($array);
					
					$arrayalt['PRECO_UNITARIO'] = $listprod->PRECO_UNITARIO * 1.06;
					$bo->update($arrayalt, 'ID = '.$listprod->ID);
					
					$array['data']			= date("Y-m-d H:i:s");
					$array['moeda']			= 'BRL';
					$array['valor']			= $listprod->PRECO_UNITARIO * 1.06;
					$array['id_produtos']	= $listprod->ID;
					$array['id_func']		= 613;
					$boh->insert($array);
				endif;
			endforeach;			
		}
		
		public function listaProdutosajusteprecos($var){
			/*$obj = new ProdutosModel();
			return $obj->fetchAll("precoajuste != '' || percentajuste !='' ","codigo_mask");*/
			$where = "";
			$ids = "";
			foreach (ProdutosBO::listaallProdutos() as $list):
				if(!empty($var[$list->ID])) $ids .= $list->ID.",";
			endforeach;
			
			//--- Busca na montagem da tabela -------------------
			if($ids!="") $where = " and p.ID in (".substr($ids,0,-1).")";
			
			//--- Busca na hora do envio -------------------
			if(!empty($var['produtos'])) $where = " and p.ID in (".substr($var['produtos'],0,-1).")";
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
						
			$select->from(array('p'=>'produtos','*'), array('p.*','p.ID as idprod','c.EMPRESA'))
			        ->join(array('c'=>'clientes'), 'c.ID = p.id_userajuste')
			        ->where('(p.percentajuste !="" || precoajuste !="") '.$where);
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		public function listaProdutosajusteprod($var){
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
						
			$select->from(array('p'=>'produtos','*'),
			        array('p.id_gruposprodsub','p.CODIGO','c.EMPRESA','a.precoatual','a.dt_ajuste','a.preconovo','a.percent','a.dt_cadajuste'))
			        ->join(array('a'=>'tb_ajusteprecosprod'), 'a.id_produtos = p.ID')
			        ->join(array('c'=>'clientes'), 'c.ID = a.id_userajuste')
			        ->where('md5(a.id_ajusteprecos) = "'.$var['ajuste'].'"');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		public function ajusteprecosGrupo($var){
			$bo	= new ProdutosModel();
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			if(!empty($var['ajusteperc']) and (!empty($var['dataajuste']))):
				if(($var['buscagruposub']!=0) and ($var['buscagrupo']!="all")):
					foreach ($bo->fetchAll("id_gruposprodsub = ".$var['buscagruposub']) as $listsg):
						$array['percentajuste']		= str_replace(",",".",str_replace(".","",$var['ajusteperc']));
						$array['dt_ajuste'] 		= substr($var["dataajuste"],6,4).'-'.substr($var["dataajuste"],3,2).'-'.substr($var["dataajuste"],0,2);
						$array['id_userajuste']		= $usuario->id;
						$array['dt_cadajuste']		= date("Y-m-d H:i:s");
						$bo->update($array,"ID = ".$listsg->ID);
					endforeach;
				elseif(($var['buscagrupo']!=0) and ($var['buscagrupo']!="all")):
					foreach (ProdutosBO::buscaProdutosgrupo($var['buscagrupo']) as $listsg):
						$array['percentajuste']		= str_replace(",",".",str_replace(".","",$var['ajusteperc']));
						$array['dt_ajuste'] 		= substr($var["dataajuste"],6,4).'-'.substr($var["dataajuste"],3,2).'-'.substr($var["dataajuste"],0,2);
						$array['id_userajuste']		= $usuario->id;
						$array['dt_cadajuste']		= date("Y-m-d H:i:s");
						$bo->update($array,"ID = ".$listsg->idproduto);
					endforeach;
				elseif($var['buscagrupo']=="all"):
					foreach (ProdutosBO::listaallProdutos() as $listsg):
						$array['percentajuste']		= str_replace(",",".",str_replace(".","",$var['ajusteperc']));
						$array['dt_ajuste'] 		= substr($var["dataajuste"],6,4).'-'.substr($var["dataajuste"],3,2).'-'.substr($var["dataajuste"],0,2);
						$array['id_userajuste']		= $usuario->id;
						$array['dt_cadajuste']		= date("Y-m-d H:i:s");
						$bo->update($array,"ID = ".$listsg->ID);
					endforeach;
				endif;
			endif;
		}
		
		public function disparaListaprecos($var){
			date_default_timezone_set('America/Sao_Paulo');
			$usuario 	= Zend_Auth::getInstance()->getIdentity();	
			$array['id_user']			= $usuario->id;
			$array['status']			= "Enviado";
			$array['dt_envio']			= date("Y-m-d H:i:s");
			$array['representantes']	= $var['rep'];
			$array['funcionarios']		= $var['func'];
			$array['gerente']			= $var['ger'];
			$array['clientes']			= $var['cli'];
			
			$boa		= new AjusteprecosModel();
			$boae		= new AjusteprecosprodModel();
			$idenvio	= $boa->insert($array);
			
			$message = '<table cellpadding="0" cellspacing="0" style="margin-top: 6px"><tr><td>';
			
			foreach(GruposprodBO::listaSubgrupos("") as $listGrupos):
				$verg = 0; 
				foreach(ProdutosBO::listaProdutosajusteprecos($var) as $lista):
					if($lista->id_gruposprodsub==$listGrupos->idsub):
						$verg 		= 1;
						$grupo		= $listGrupos->grupo;
						$subgrupo	= $listGrupos->subgrupo;
					endif;
				endforeach;
			
	        	if ($verg==1) :
						        	
					$message .=	'
					
					<table width="100%" cellpadding="0" cellspacing="0" >
									<tr>
				              			<td class="borda_tcadastro">
				              			<br>
										<b>'.$grupo." / ".$subgrupo.'</b>
				              			</td>
				              		</tr>
					            </table>
							</td>
						</tr>
						<tr>
							<td>
								<table align="left" class="mytable" border="1px"   width="554" cellspacing="0" style="margin-top: 5px">
							         <tr>
							            <th  class="th_canto_orc" align="center" width="30%">
							                CÓDIGO
							            </th>
							            <th  class="th_orc" align="center" width="20%">
							                PREÇO ATUAL
							            </th>
							            <th  class="th_orc" align="center" width="20%">
							               NOVO PREÇO
							            </th>
							            <th  class="th_orc" align="center" width="30%">
							                DATA AJUSTE
							            </th>
							        </tr>';
					
					$cor=0;
			    	foreach(ProdutosBO::listaProdutosajusteprecos($var) as $lista):
			    	if($lista->id_gruposprodsub==$listGrupos->idsub):
			    		$cor++;
			            if(($cor%2)==0) $class = 'td_orc_par';
			            else $class = 'td_orc';
			            
			            if(!empty($lista->precoajuste)):
			            	$novopreco 	= number_format($lista->precoajuste,"2",",",".");
			            	$percent	= $lista->precoajuste - $lista->PRECO_UNITARIO;
			            	$percent	= number_format(($percent*100)/$lista->PRECO_UNITARIO,"2",",",".");
			            elseif(!empty($lista->percentajuste)):
			            	$novopreco 	= number_format($lista->PRECO_UNITARIO+($lista->PRECO_UNITARIO*($lista->percentajuste/100)),"2",",",".");
			            	$percent	= number_format($lista->percentajuste,"2",",",".");
			            endif;
			            
			            $arrayp['id_ajusteprecos']	= $idenvio;	
			            $arrayp['id_produtos']		= $lista->idprod;
			            $arrayp['dt_ajuste']		= $lista->dt_ajuste;
			            $arrayp['dt_cadajuste']		= $lista->dt_cadajuste;
			            $arrayp['precoatual']		= $lista->PRECO_UNITARIO;
			            $arrayp['preconovo']		= $lista->precoajuste;
			            $arrayp['id_userajuste']	= $lista->id_userajuste;
			            $arrayp['percent']			= $lista->percentajuste;
			            $boae->insert($arrayp);
			            
						$message .=	'<tr >
						                <td  class="'.$class.'" align="center" >
						                   '.$lista->CODIGO.'
						                </td>
						                <td  class="'.$class.'" align="right" style="text-transform: uppercase;">
						                  '.number_format($lista->PRECO_UNITARIO,"2",",",".").'
						                </td>
						                <td  class="'.$class.'" align="right" >
						                  '.$novopreco.'
						                </td>                
						                <td  class="'.$class.'" align="center" >
						                   '.substr($lista->dt_ajuste,8,2)."/".substr($lista->dt_ajuste,5,2)."/".substr($lista->dt_ajuste,0,4).'
						                </td>  				                                
						            </tr>';
								
						endif;
					endforeach;
					
					$message .=	'</table>';
							     	
				endif;
			endforeach;
			
			$message.=	'</td></tr></table>';
			
			$smtp = "smtp.ztlbrasil.com.br";
			$conta = "info@ztlbrasil.com.br";
			$senha = "010203";
			$de = "info@ztlbrasil.com.br";
			$assunto = "Tabela de Ajuste de preços";
			
			$array['representantes']	= $var['rep'];
			$array['funcionarios']		= $var['func'];
			$array['gerente']			= $var['ger'];
			$array['clientes']			= $var['cli'];
			
			
			if($var['ger']==1):
				$arrayb['ger']	=	1;
				foreach (ClientesBO::listaemailsAllclientes($arrayb) as $listfunc):
					$email	=	$listfunc->EMAIL;
					$resp 	=   $listfunc->NOME_CONTATO;
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
						$mail->setBodyHtml($message);
						$mail->setSubject($assunto);
						$mail->send($mailTransport);
					
						echo "Email enviado com SUCESSSO: ".$email."<br>";
					} catch (Exception $e){
						echo ($e->getMessage());
						echo "<br>";
					}
					$total +=1;		
				endforeach;
			endif;
			
			if($var['func']==1):
				$arrayb['func']	=	1;
				foreach (ClientesBO::listaemailsAllclientes($arrayb) as $listfunc):
					$email	=	$listfunc->EMAIL;
					$resp 	=   $listfunc->NOME_CONTATO;
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
						$mail->setBodyHtml($message);
						$mail->setSubject($assunto);
						$mail->send($mailTransport);
											
					} catch (Exception $e){
						echo ($e->getMessage());
						echo "<br>";
					}
					$total +=1;	;			
				endforeach;
			endif;
			
			if($var['rep']==1):
				$arrayb['rep']	=	1;
				foreach (ClientesBO::listaemailsAllclientes($arrayb) as $listfunc):
					$email	=	$listfunc->EMAIL;
					$resp 	=   $listfunc->NOME_CONTATO;
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
						$mail->setBodyHtml($message);
						$mail->setSubject($assunto);
						$mail->send($mailTransport);
					
						echo "Email enviado com SUCESSSO: ".$email."<br>";
					} catch (Exception $e){
						echo ($e->getMessage());
						echo "<br>";
					}
					$total +=1;				
				endforeach;
			endif;
			
			if($var['cli']==1):
				$arrayb['cliente']	=	1;
				foreach (ClientesBO::listaemailsAllclientes($arrayb) as $listfunc):
					$email	=	$listfunc->EMAIL;
					$resp 	=   $listfunc->NOME_CONTATO;
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
						$mail->setBodyHtml($message);
						$mail->setSubject($assunto);
						$mail->send($mailTransport);
					
						echo "Email enviado com SUCESSSO: ".$email."<br>";
					} catch (Exception $e){
						echo ($e->getMessage());
						echo "<br>";
					}
					$total +=1;				
				endforeach;
			endif;
			
			//--Dispara emails-----------
			/*$arrayb['funcionario']	=	1;
			$cont = 0;
			//foreach (ClientesBO::listaemailsAllclientes($arrayb) as $listfunc):
							
				$email	=	$listfunc->EMAIL;
				$resp 	=   $listfunc->NOME_CONTATO;
				
				$resp 	=  "Cleiton";
				$email  = "cleiton@ztlbrasil.com.br";
				try {
					$config = array (
					'auth' => 'login',
					'username' => $conta,
					'password' => $senha,
					'port' => '25'
					);
				
					$mailTransport = new Zend_Mail_Transport_Smtp($smtp, $config);
					
					$mail = new Zend_Mail('utf-8');
					$mail->setFrom($de);
					$mail->addTo($email,$resp);
					$mail->setBodyHtml($message);
					$mail->setSubject($assunto);
					$mail->send($mailTransport);
				
					echo "Email enviado com SUCESSSO: ".$email."<br>";
				} catch (Exception $e){
					echo ($e->getMessage());
					echo "<br>";
				}
				$total +=1;
				
			endforeach;
			
			echo $total;*/
			
		}
		
		public function listaAjusteprecos(){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
						
			$select->from(array('a'=>'tb_ajusteprecos','*'),
			        array('a.*','c.EMPRESA'))
			        ->join(array('c'=>'clientes'), 'c.ID = a.id_user')
			        ->order('a.id desc');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();			
		}
		
		public function buscaAjusteprecos($var){
			$bo		= new AjusteprecosModel();
			return $bo->fetchAll("md5(id) = '".$var['ajuste']."'");			
		}
		
		public function executarAjusteprecos(){
			date_default_timezone_set('America/Sao_Paulo');
			$bo		= new ProdutosModel();
			$bohisv	= new HistoricopcvendaModel();			
			
			foreach ($bo->fetchAll('(precoajuste != "" || percentajuste != "") and dt_ajuste = "'.date("Y-m-d").'"') as $lista):
				
				if(($lista->precoajuste != "") and ($lista->precoajuste != 0)): 
					$novopreco = $lista->precoajuste;
				elseif (($lista->percentajuste != "") and ($lista->percentajuste != 0)):
					$novopreco = $lista->PRECO_UNITARIO+($lista->PRECO_UNITARIO*($lista->percentajuste/100));
				endif;
				
				$arrayhistv['valor']		= $novopreco;
				$arrayhistv['data'] 		= date("Y-m-d H:i:s");
				$arrayhistv['moeda'] 		= "BRL";
				$arrayhistv['id_produtos'] 	= $lista->ID;
				$arrayhistv['id_func'] 		= $lista->id_userajuste;
				$bohisv->insert($arrayhistv);
				
				$arrayp['PRECO_UNITARIO']   = $novopreco;
				$arrayp['precoajuste']   	= "";
				$arrayp['percentajuste']   	= "";
				$arrayp['dt_ajuste']   		= "";
				$bo->update($arrayp, "ID = ".$lista->ID);
								
			endforeach;
						
		}
		
		//----Referencia cruzada---------------------------------
		
		/*function corrigeCodcross(){
			$bo 	= new RefcruzadaModel();
			$bon	= new CodantigocrossModel();
			$boc	= new CodigoscrossModel(); 
			$cont = 0;
			foreach ($bon->fetchAll() as $lista):
				$array['codigo']		= strtoupper($lista->codigo);
				$array['id_fabricante']	= $lista->id_fabricante;
				$array['vl_bruto']		= str_replace(",",".",$lista->valor_bruto);
				$array['vl_liquido']	= str_replace(",",".",$lista->valor_liquido);
				$array['vl_desc']		= str_replace(",",".",$lista->valor_desconto);
				$array['sit']			= true;
				$boc->insert($array);	
				$cont++;			
			endforeach;
			
			echo $cont;
		}*/
		
		function listaFabricas(){
			$bo 	= new RefcruzadaModel();	
			$bof	= new FabricasModel();
			return $bof->fetchAll("id is not NULL","no_fabricante asc");
		}
		
		function listaCodigoscross($var){
			$sessaobusca = new Zend_Session_Namespace('Default');
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			
			if(isset($sessaobusca->wherecr)):
				$where = $sessaobusca->wherecr;
				$limit	= 1000;
				$order	=	'f.no_fabricante';
			else:
				$limit	= 10;
				$order	=	'p.id desc';
			endif;			

			
			$usuario = Zend_Auth::getInstance()->getIdentity();
			foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 9) as $listPer);
			if($listPer->aba2 == false):
				$where .= " and p.visualizar = true";
			endif;
			
			$select->from(array('p'=>'tb_crossprodutos','*'), array('p.principal', 'p.visualizar','p.id as idcodigo','p.codigo','f.no_fabricante','f.id as idfabricante'))
		        ->join(array('f'=>'tb_fabricante'), 'f.id = p.id_fabricante')
		        ->where('p.sit = true'.$where)
		        ->order($order)
		        ->limit($limit);
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		function listaCodigoscrossbusca($var){
			$sessaobusca = new Zend_Session_Namespace('Default');
									
			if($var['buscafabrica']!=0) $where = " and id_fabricante  = ".$var['buscafabrica'];
			if(!empty($var['buscacod'])) $where .= " and codigo like '%".$var['buscacod']."%'";
			
			$usuario = Zend_Auth::getInstance()->getIdentity();
			foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 9) as $listPer);
			if($listPer->aba2 == false):
				$where .= " and p.visualizar = true";
			endif;			
			
			$order	=	'f.no_fabricante';
			$sessaobusca->wherecr 	= $where;
			$sessaobusca->tr		= $var['tr'];
			
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			
			$select->from(array('p'=>'tb_crossprodutos','*'), array('p.principal', 'p.visualizar','p.id as idcodigo','p.codigo','f.no_fabricante','f.id as idfabricante'))
		        ->join(array('f'=>'tb_fabricante'), 'f.id = p.id_fabricante')
		        ->where('p.sit = true'.$where)
		        ->order($order);
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		function gravarCodigosrefcruzada($params){
			$bo 		= new RefcruzadaModel();	
			$bof		= new CodigoscrossModel();
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			$array['codigo']		= strtoupper($params['codigo']);
			$array['codigo_mask']	= strtoupper($params['codigo_mask']);
			$array['principal']		= $params['principal'];
			$array['visualizar']	= $params['visualizar'];
			$array['sit']			= true;
			
			if(!empty($params['id_prod'])):
				$array['id_prod'] 		= $params['id_prod'];
			endif;
			
			if(!empty($params['idcodigo'])):
				$bof->update($array, "id = ".$params['idcodigo']);
				LogBO::cadastraLog("Adm/Ref Cruzada",4,$usuario->id,$params['codigo'],"PROD ".$params['codigo']);
			else:
				$array['id_fabricante']	= $params['fabrica'];
				LogBO::cadastraLog("Adm/Ref Cruzada",2,$usuario->id,$params['codigo'],"PROD ".$params['codigo']);
				$bof->insert($array);
			endif;
		}
		
		function removeCodigosrefcruzada($id){
			$bo 	= new RefcruzadaModel();	
			$bof	= new CodigoscrossModel();
			$array['sit']	= false;
			$bof->update($array, "id = ".$id);
		}
	
		function buscaCross($var){
			date_default_timezone_set('America/Sao_Paulo'); 
			$usuario = Zend_Auth::getInstance()->getIdentity();
			$bo		= new RefcruzadaModel();
			$boc	= new CodigoscrossModel();
			$boh	= new CrosshistoricoModel();
			$where 	= "";
			$codigos= array();
			
			if(!empty($var['fabricamd'])):
				foreach ($boc->fetchAll("sit = true and codigo = '".strtoupper(str_replace(" ","",$var['buscacod']))."' and md5(id_fabricante) = '".$var['fabricamd']."'") as $codigos);
			else:
				foreach ($boc->fetchAll("sit = true and codigo = '".strtoupper(str_replace(" ","",$var['buscacod']))."' and id_fabricante = ".$var['fabrica']) as $codigos);	
			endif;
			
			if(count($codigos) > 0){
				
				/* $arrayhist['id_crossprodutos']	= $list->id;
				$arrayhist['dt_atualizacao']	= date("Y-m-d H:i:s");
				$arrayhist['id_clientes']		= $usuario->id;
			
				$boh->insert($arrayhist); */
								
				foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 9) as $listPer);
				if($listPer->aba2 == false):
					$where = " and p.visualizar = true"; 
				endif;
				
				$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
				$db->setFetchMode(Zend_Db::FETCH_OBJ);
				$select = $db->select();
							
				$select->from(array('r'=>'tb_crossreference','*'), array('p.id as idcodigo','p.codigo','f.no_fabricante','f.id as idfabricante'))
			        ->join(array('p'=>'tb_crossprodutos'), 'p.id = r.id_crossprodutos')
			        ->join(array('f'=>'tb_fabricante'), 'f.id = p.id_fabricante')
			        ->where('r.sit = true and p.sit = true and r.id_codprod = '.$codigos->id.$where)
			        ->order('f.no_fabricante');
				        			  
				$stmt = $db->query($select);
				return $stmt->fetchAll();
			}else{
				return "erro";
			}		
		}
		
		function listaHistoricosbusca(){
			$bo		= new RefcruzadaModel();
			$boh	= new CrosshistoricoModel();
						
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
						
			$select->from(array('h'=>'tb_crosshistorico','*'),
			        array('p.id as idcodigo','p.codigo','f.no_fabricante','f.id as idfabricante','DATE_FORMAT(h.dt_atualizacao,"%d/%m/%Y %H:%i") as databusca','u.nome'))
			        ->join(array('p'=>'tb_crossprodutos'), 'p.id = h.id_crossprodutos')
			        ->join(array('f'=>'tb_fabricante'), 'f.id = p.id_fabricante')
			        ->joinLeft(array('u'=>'tb_usuarios'), 'u.id = h.id_clientes')
			        ->where('p.sit = true')
			        ->order('h.id desc')
			        ->limit(5);
			        			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();				
		}
		
		function buscaCodigocross($var){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
						
			$select->from(array('p'=>'tb_crossprodutos','*'),
			        array('p.id as idcodigo','p.codigo','f.no_fabricante'))
			        ->join(array('f'=>'tb_fabricante'), 'f.id = p.id_fabricante')
			        ->where('p.sit = true and p.codigo = "'.str_replace(" ","",$var['buscacod']).'" and p.id_fabricante = '.$var['fabrica']);
			        			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		function buscaFabricas($var){
			$bo 	= new RefcruzadaModel();	
			$bof	= new FabricasModel();
			if(!empty($var['fabricamd'])):
				return $bof->fetchAll("md5(id) = '".$var['fabricamd']."'","no_fabricante asc");
			else:
				return $bof->fetchAll("id = ".$var['fabrica'],"no_fabricante asc");			
			endif;			
		}
		
		function gravaReferencias($params){
			$bo		= new RefcruzadaModel();
			$usuario = Zend_Auth::getInstance()->getIdentity();
			
			$codcross	= explode(";", $params['arrcodigos'],-1);
			//--- Veririca existencia codigo, se nao existe, faz a gravacao ------------------
			for($i=0;$i<count($codcross);$i++):
				$codcad	= explode(":", $codcross[$i]);
				
				$var['buscacod']		= $codcad[0];
				$var['fabrica']			= $codcad[1];				
				//---verifica se existe--------------------------
				$listcad='';
				foreach (ProdutosBO::buscaCodigocross($var) as $listcad);
				//---grava--------------------------cadref
				
				if(empty($listcad)):
					$arraycad['codigo_mask'] 	= NULL;
					$arraycad['id_prod'] 		= NULL;
					if($codcad[1]==1): 
						foreach (ProdutosBO::buscaProdutoscodigo($codcad[0]) as $listcod);
						if(!empty($listcod)):
							$arraycad['codigo_mask'] 	= $listcod->codigo_mask;
							$arraycad['id_prod'] 		= $listcod->ID;
						endif;
					endif;
				
					$prin = 0;
					if($codcad[2] == "true") $prin = 1;
					
					$vis = 0;
					if($codcad[3] == "true") $vis = 1;
					
					$arraycad['codigo']			= $codcad[0];
					$arraycad['fabrica']		= $codcad[1];
					$arraycad['principal']		= $prin;
					$arraycad['visualizar']		= $vis;
						
					ProdutosBO::gravarCodigosrefcruzada($arraycad);
				endif;												
			endfor;
			
			//--- Remove o cruzamento, para posterior gravacao -----------------------------
			$codrem	= explode(";", $params['arrcodigosrem'],-1);
			for($i=0;$i<count($codrem);$i++):
				$codcad	= explode(":", $codrem[$i]);
				//---busca o id do produto--------------------------
				$var['buscacod']	= $codcad[0];
				$var['fabrica']		= $codcad[1]; 
				$listcad='';
				foreach (ProdutosBO::buscaCodigocross($var) as $listcad);
				$idrem	= ""; 
				$idrem	=	$listcad->idcodigo;
				
				//-- Remove todos os cruzamentos principal --------------------------------
				for($j=0;$j<count($codcross);$j++):
					$codr	= explode(":", $codcross[$j]);
					
					$var['buscacod']	= $codr[0];
					$var['fabrica']		= $codr[1]; 
					//---verifica se existe--------------------------
					$listcad='';
					foreach (ProdutosBO::buscaCodigocross($var) as $listcad);

					$arrayrem['sit']			= false;
					echo "<br>";
					echo "(id_codprod = ".$listcad->idcodigo." and id_crossprodutos = ".$idrem.") || (id_codprod = ".$idrem." and id_crossprodutos = ".$listcad->idcodigo.")";
					$bo->update($arrayrem, "(id_codprod = '".$listcad->idcodigo."' and id_crossprodutos = '".$idrem."' and sit = true) || (id_codprod = '".$idrem."' and id_crossprodutos = '".$listcad->idcodigo."' and sit = true)");				
				endfor;

				//-- Remove todos os cruzamentos segundarios --------------------------------
				for($j=0;$j<count($codrem);$j++):
					$codr	= explode(":", $codrem[$j]);
					
					$var['buscacod']	= $codr[0];
					$var['fabrica']		= $codr[1]; 
					//---verifica se existe--------------------------
					$listcad='';
					foreach (ProdutosBO::buscaCodigocross($var) as $listcad);

					$arrayrem['sit']			= false;
					echo "<br>";
					echo "(id_codprod = ".$listcad->idcodigo." and id_crossprodutos = ".$idrem.") || (id_codprod = ".$idrem." and id_crossprodutos = ".$listcad->idcodigo.")";
					$bo->update($arrayrem, "(id_codprod = '".$listcad->idcodigo."' and id_crossprodutos = '".$idrem."' and sit = true) || (id_codprod = '".$idrem."' and id_crossprodutos = '".$listcad->idcodigo."' and sit = true)");				
				endfor;												
				
			endfor;
			
			//--- Grava o cruzamento -----------------------------
			$codbusc	= explode(";",$params['arrcodigos'],-1);
			for($i=0;$i<count($codcross);$i++):
				$codcad	= explode(":", $codcross[$i]);
				//---busca o id do produto--------------------------
				$var['buscacod']	= $codcad[0];
				$var['fabrica']		= $codcad[1]; 
				$listcad='';
				foreach (ProdutosBO::buscaCodigocross($var) as $listcad);
				$idprod = "";
				$idprod = $listcad->idcodigo;
				
				//-- loop de gravacao -------------------------
				for($j=0;$j<count($codbusc);$j++):
					$codbusccad	= explode(":", $codbusc[$j]);
					//--- busca id dos codigos para gravar no cross ----------------------------
					$var['buscacod']	= $codbusccad[0];
					$var['fabrica']		= $codbusccad[1]; 
					$listbusccad='';
					foreach (ProdutosBO::buscaCodigocross($var) as $listbusccad);
					$idprodbusc = "";
					$idprodbusc	= $listbusccad->idcodigo;
					
					if($idprodbusc != $idprod):
						$list = "";
						foreach ($bo->fetchAll("sit = true and id_codprod = ".$idprod." and id_crossprodutos = ".$idprodbusc) as $list);
						
						//--- grava ---------------------------------------
						if(empty($list)):
							$arrayins['id_codprod']			= $idprod;
							$arrayins['id_crossprodutos']	= $listbusccad->idcodigo;
							$arrayins['dt_cadastro']		= date("Y-m-d H:i:s");
							$arrayins['sit']				= true;
							$bo->insert($arrayins);						
						endif;
						
					endif;
				endfor;
				
				LogBO::cadastraLog("Adm/Ref Cruzada",4,$usuario->id,$codcad[0],"CROSS ".$codcad[0]);
				
			endfor;
			
			return true;
			
		}
		
		function relatorioCodigoscross($var){
			$sessaobusca = new Zend_Session_Namespace('Default');
			
			$usuario = Zend_Auth::getInstance()->getIdentity();
			foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 9) as $listPer);
			if($listPer->aba2 == false):
				$where = " and p.visualizar = true and pd.visualizar = true";
			endif;
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
						
			$select->from(array('c'=>'tb_crossreference','*'),
			        array('p.codigo as codigo1','f.no_fabricante as fabrica1','pd.codigo as codigo2','fd.no_fabricante as fabrica2','f.id as idfabrica1','fd.id as idfabrica2'))
			        ->join(array('p'=>'tb_crossprodutos'), 'c.id_crossprodutos = p.id')
			        ->join(array('f'=>'tb_fabricante'), 'f.id = p.id_fabricante')
			        ->join(array('pd'=>'tb_crossprodutos'), 'c.id_codprod = pd.id')
			        ->join(array('fd'=>'tb_fabricante'), 'fd.id = pd.id_fabricante')
			        ->where('c.sit = true and p.sit = true and f.id = '.$var['buscafabrica'].' and fd.id = '.$var['buscafabrica2'].$where)
			        ->order('p.codigo_mask')
			        ->order('p.codigo');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		
		
		/* function restauraPrecos(){
			$boprod	= new ProdutosModel();
			$bokit	= new KitsModel();
						
			//--- busca produtos com erro ----------------
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('a'=>'tb_logacesso','*'), array('*'))
			        ->where('clientes_id = 1017 and acao = 4 and id > 49681')
			        ->group("ident_desc");
			  
			$stmt = $db->query($select);
			
			
			
			//---executa correcao ----------------
			foreach ($stmt->fetchAll() as $lista):
			
				$codigo =  substr($lista->ident_desc,4);
				echo $codigo." ";
				//--- busca produto por codigo --------------------
				$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
				$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
				$select2 = $db2->select();
				
				$select2->from(array('p'=>'produtos','*'), array('*'))
				        ->where('p.CODIGO = "'.$codigo.'"');
				  
				$stmt2 = $db2->query($select2);
				
				foreach ($stmt2->fetchAll() as $prod);
				
				echo "Codigo = ".$prod->CODIGO."<br>";
				
				$array['PRECO_UNITARIO']   		= $prod->PRECO_UNITARIO;
				$array['PREVISAO']				= $prod->PREVISAO;
				$array['PRECO_UNITARIO_USD']	= $prod->PRECO_UNITARIO_USD;
				$array['PRECO_UNITARIO_RMB']	= $prod->PRECO_UNITARIO_RMB;
				$array['PRECO_UNITARIO_EUR']	= $prod->PRECO_UNITARIO_EUR;
				$array['ID_CLIENTE_FORNECEDOR']	= $prod->ID_CLIENTE_FORNECEDOR;
				$array['MOEDA']					= $prod->MOEDA;
				$array['CUSTO_VALOR']			= $prod->CUSTO_VALOR;
				
				$array['Purchasing_group']		= $prod->Purchasing_group;
				
				$array['valor_promo']			= $prod->valor_promo;
				$array['valor_desc']			= $prod->valor_desc;
				
				$array['pl_prod_desc']			= $prod->pl_prod_desc;
				$array['observacao']			= $prod->observacao;
				$array['purchasing_det']		= $prod->purchasing_det;
				
				$boprod->update($array, "ID = ".$prod->ID);
				
				
				
			endforeach;			
		} */
				
		public function listaallProdutosmigra(){
			$obj = new ProdutosModel();
			return $obj->fetchAll();			
		}
		
		
		function migraNcm(){
			$bo = new NcmModel();
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('p'=>'produtos','*'), array('*'))
					->group("CLASSIFICACAO_FISCAL")
					->group('classificacao_ex')
					->order('CLASSIFICACAO_FISCAL');
								  
			$stmt = $db->query($select);
			
			$i = 0;
			foreach ($stmt->fetchAll() as $lista):
				$array[$i][0] = $lista->CLASSIFICACAO_FISCAL;
				$array[$i][1] = $lista->classificacao_ex;
				$i++;
			endforeach;
			
			for($i=0;$i<sizeof($array);$i++):
				
				if(($array[$i][0]!=$array[$i+1][0]) || (($array[$i][0]==$array[$i+1][0]) and ($array[$i][1]!=$array[$i+1][1])) ):
					echo $array[$i][0]." ".$array[$i][1]."<br>";
					$arrayin['ncm'] 		= $array[$i][0];
					$arrayin['ncmex'] 		= $array[$i][1];
					TributosBO::gravarNcm($arrayin);
				endif;
				
			endfor;
			
		}
		
		function migraprodutosNcm(){
			$bo = new NcmModel();
			$bop	= new ProdutosModel();
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('p'=>'produtos','*'), array('*'));
								  
			$stmt = $db->query($select);
			
			$i = 0;
			foreach ($stmt->fetchAll() as $lista):
				foreach (TributosBO::listaNcm() as $listncm):
					if(($lista->CLASSIFICACAO_FISCAL==$listncm->ncm) and ($lista->classificacao_ex==$listncm->ncmex)):	
						$array['id_ncm']	= $listncm->id;
						$bop->update($array, "ID = ".$lista->ID);
					endif;
				endforeach;
			endforeach;
						
		}
		
		//--- CMV -----------------------------------------------------
		
		function listaCmvprodutosent($var){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('c'=>'produtos_pedidos_compra'), array('sum(e.qt) as qta', 'v.valor', 'v.id_entradaztl'))
			        ->join(array('e'=>'tb_entradaztl_prod'),'e.id_prodped = c.ID')
			        ->join(array('v'=>'tb_produtosentcmv'),'v.id_entradaztl = e.id_entradaztl')		        
			        ->where("c.ID_PRODUTO = ".$var)
			        ->group('e.id_entradaztl')
			        ->order('e.id_entradaztl desc');
			  
			$stmt = $db->query($select);
			return  $stmt->fetchAll();
		}
		
		function listaProdutosent($var){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			  
			$select->from(array('c'=>'produtos_pedidos_compra'), array('*','sum(e.qt) as qta'))
			        ->join(array('e'=>'tb_entradaztl_prod'),'e.id_prodped = c.ID')	 
			        ->join(array('ent'=>'tb_entradaztl'),'ent.id = e.id_entradaztl')
			        ->where("ent.sit = 1 and c.ID_PRODUTO = ".$var)
			        ->group('e.id_entradaztl')
			        ->order('e.id_entradaztl desc');
			
			$stmt = $db->query($select);
			return  $stmt->fetchAll();
		}
		
		//--- lista qt de produtos de ajustes com entradas ----------------------------
		function listaAjusteprodutosent($var){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('a'=>'tb_ajustestoqueztl'), array('a.id as idajuste', 'p.qt as qtajuste','a.id_entradaztl'))
					->join(array('p'=>'tb_ajustestoqueztl_prod'),'p.id_ajuste = a.id') 
			        ->where("p.id_prod = ".$var)
			        ->order("a.id desc");
			  
			$stmt = $db->query($select);
			return  $stmt->fetchAll();
		}
		
		function listaCmventproduto($var){
			$boe	= new EntradaestoqueModel();
			$bo		= new EntradaestoquecmvModel();	
			
			return $bo->fetchAll("id_produtos = ".$var);
		}
		
		function listaCmvproduto($var){
			$bo 	= new ProdutosModel();
			$boc 	= new ProdutoscmvModel();
			
			return $boc->fetchAll("id = (select max(id) from tb_produtoscmv where id_produtos = ".$var.")");
		}
		
		//----Listar entrega-----------------------------
		function buscaProdutosentregues($var){
			$boc	= new ZtlcomprasModel();
			$bocp	= new ZtlcomprasprodModel();
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('pc'=>'pedidos_compra'), array('e.qt as qtent','DATE_FORMAT(en.data,"%d/%m/%Y") as dtent','en.id as docent','pd.PRECO_UNITARIO_USD as precocomp','pc.ID as idped','pd.perc'))
					->join(array('pd'=>'produtos_pedidos_compra'),'pd.ID_PEDIDO_COMPRA = pc.ID')        
					->join(array('e'=>'tb_entradaztl_prod'),'e.id_prodped = pd.ID')
					->join(array('en'=>'tb_entradaztl'),'e.id_entradaztl = en.id')
			        ->where("pd.ID_PRODUTO = ".$var)
			        ->order('en.id','asc');
			  
			$stmt = $db->query($select);
			return  $stmt->fetchAll();
		}
		

		function corrigePrecos(){
			foreach (ProdutosBO::listaallProdutos() as $listp):
				$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
				$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
				$select = $db->select();
				
				$select->from(array('t'=>'tb_historicopcvenda','*'),array('*'))
				        ->where("t.moeda = 'BRL' and t.id_produtos = ".$listp->ID);
				  
				$stmt = $db->query($select);
				
				$id = 0;
				$valor="";
				foreach ($stmt->fetchAll() as $lisvl):
					if(($lisvl->id > $id)):
						$valor = $lisvl->valor;
					endif;
					$id = $lisvl->id;
				endforeach;
				
				if(($valor!=$listp->PRECO_UNITARIO) and ($valor != "")):
					echo "CODIGO: ".$listp->CODIGO." &nbsp; PRECO: ".$listp->PRECO_UNITARIO." &nbsp; HIST: ".$valor."<br>";
				endif;
				
			endforeach;	
							
		}
		
		function buscaProdutosveiculoscatalogo($pesq = ""){
			$sessaobusca = new Zend_Session_Namespace('catalogo');
		    if($sessaobusca->where!=""):
		    	$where = " and ".$sessaobusca->where;
		    endif;		
			
		    if($pesq['exportar'] == 1):
			    $idexp = "";
			    foreach (ProdutosBO::listaallProdutos() as $produtos):
				    if(!empty($pesq['prod_'.$produtos->ID])):
				    	$idexp .= $produtos->ID.",";
				    endif;
			    endforeach;
			    $idexp = substr($idexp, 0,-1);
			     
			    if($idexp!=""):
			    	$where .= " and p.ID in (".$idexp.")";
			    endif;
		     
		    endif;
		    
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('p'=>'produtos','*'), array('p.CODIGO','pv.*','v.*'))
			        ->join(array('s'=>'tb_gruposprodsub'), 's.id = p.id_gruposprodsub and s.tipo = 1')
			        ->join(array('g'=>'tb_gruposprod'), 'g.id = s.id_gruposprod and g.tipo = 1')
			        ->join(array('pv'=>'tb_produto_veiculo'), 'pv.id_produto = p.ID')
			        ->join(array('v'=>'tb_veiculo'), 'v.id = pv.id_veiculo')
			        ->join(array('m'=>'tb_montadora'), 'm.ID = v.id_montadora')
			        ->joinLeft(array('c'=>'prod_cat'), 'c.id_prod = p.ID')
			        ->where("p.situacao != 2".$where)
			        ->order("m.NOME")
			        ->order("v.no_modelo");
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();	
								
		}
		
		function buscaProdutoscatalogo($pesq=""){
			
		    $sessaobusca = new Zend_Session_Namespace('catalogo');
		    if($sessaobusca->where!=""):
		    	$where = " and ".$sessaobusca->where;
		    endif;			 
		    
		    if($pesq['exportar'] == 1):
			    $idexp = "";
			    foreach (ProdutosBO::listaallProdutos() as $produtos):
				    if(!empty($pesq['prod_'.$produtos->ID])):
				    	$idexp .= $produtos->ID.",";
				    endif;
			    endforeach;
			    $idexp = substr($idexp, 0,-1);
			    
			    if($idexp!=""):
			    	$where .= " and p.ID in (".$idexp.")";
			    endif;
		    	
		    endif;
		    
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('p'=>'produtos','*'), array('p.ID as idprod','p.*','g.descricao as NOME','s.id_gruposprod as grupoprod','ncm.ncm','ncm.ncmex'))
			        ->join(array('s'=>'tb_gruposprodsub'), 's.id = p.id_gruposprodsub  and s.tipo = 1')
			        ->join(array('g'=>'tb_gruposprod'), 'g.id = s.id_gruposprod  and g.tipo = 1')
			        ->joinLeft(array('c'=>'prod_cat'), 'c.id_prod = p.ID')
			        ->joinLeft(array('m'=>'tb_produtosmedidas'), 'm.id_prod = p.ID')
			        ->joinLeft(array('ncm'=>'tb_produtosncm'), 'ncm.id = p.id_ncm')
			        ->where("s.tipo = 1 ".$where)
			        ->order("p.codigo_mask")
			        ->group('p.ID');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
								
		}
		
		function buscaGruposcatalogo($pesq){
		    $where = 'p.ID is not NULL ';
		    
		    $sessaobusca = new Zend_Session_Namespace('catalogo');
		    
			if(!empty($pesq['codigo'])):
				$where .= " and p.CODIGO like '%".$pesq['codigo']."%'";
			endif;
			
			if(!empty($pesq['descricao'])):
				$where .= " and p.DESCRICAO like '%".$pesq['descricao']."%'";
			endif;
			
			if(!empty($pesq['montadora'])):
			$ids = "";
				foreach (ProdutosBO::buscaProdutospormontadora($pesq['montadora']) as $codmont):
					$ids .= $codmont->idprod.","; 	
				endforeach;
				
				if($ids!=""):
					$where .= " and p.ID in (".substr($ids,0,-1).")";
				endif;
			endif;
			
			if($pesq['buscagruposub']!=0):
				$where .= ' and p.id_gruposprodsub = '.$pesq['buscagruposub'];
			elseif($pesq['buscagrupo']!=0):
				$where .= ' and g.id = '.$pesq['buscagrupo'];
			endif;
			
			if(!empty($pesq['pesado'])):
				$linha = "1,";
			endif;
			
			if(!empty($pesq['6000'])):
				$linha .= "2,";
			endif;
						
			if(!empty($pesq['pesado']) || !empty($pesq['6000']) || !empty($pesq['transmissao'])):
				$where .= " and c.cod_cat in ('".substr($linha, 0, -1)."')";
			endif;
			
			if(!empty($where)):
				$sessaobusca->where = $where;
			elseif($sessaobusca->where!=""):
				$where = $sessaobusca->where;
			endif;			
			
			if($pesq['exportar'] == 1):
				$idexp = "";
				foreach (ProdutosBO::listaallProdutos() as $produtos):
					if(!empty($pesq['prod_'.$produtos->ID])):
						$idexp .= $produtos->ID.",";
					endif;
				endforeach;
				$idexp = substr($idexp, 0,-1);
				
				if($idexp!=""):
					$where .= " and p.ID in (".$idexp.")";
				endif;
			
			endif;
						
			$usuario = Zend_Auth::getInstance()->getIdentity();
			
			if(!empty($usuario)):
				foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
				if($list->nivel==2):
					$where .= " and p.situacao != 2";
				else:
					$where .= " and p.situacao = 0";
				endif;
			else:
				$where .= " and p.situacao = 0";
			endif;
						
			if(!empty($pesq['sugestcomp'])):
				$idsubgrupos = $idpbusca = "";
			
				$busca['idparceiro'] = $listaempresa->id_transportadoras;
				foreach (ClientesBO::buscaParceiros("",$busca) as $transportadora);
			
				$busca['idparceiro'] = $pesq['sugestcomp'];			
				foreach (ClientesBO::buscaParceiros("",$busca) as $cliente);
			    if(count($cliente)>0):
					foreach (VendaBO::buscaGruposvendprodscli($cliente->ID) as $produtos):
						$idpbusca .= $produtos->ID.",";
					endforeach;
				endif;
				
				$idpbusca = substr($idpbusca, 0,-1);
			
				if($idpbusca!=""):
					foreach (ClientesBO::listaGruposcli($cliente->ID) as $subgrupos):
						$idsubgrupos .= $subgrupos->idsub.",";
					endforeach;
				
					$idsubgrupos = substr($idsubgrupos, 0,-1);
					
					$where = "p.ID not in (".$idpbusca.")";

					if($idsubgrupos!=""):
						$where .= " and p.id_gruposprodsub in (".$idsubgrupos.")";
					endif;
					
					$where .= " and p.situacao = 0";
					
					$sessaobusca->where = $where;
				endif;
			
			endif;
						
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('p'=>'produtos','*'), array('g.descricao as descgrupo','s.descricao as descsubgrupo','s.id as idsubg'))
			        ->join(array('s'=>'tb_gruposprodsub'), 's.id = p.id_gruposprodsub and s.tipo = 1')
			        ->join(array('g'=>'tb_gruposprod'), 'g.id = s.id_gruposprod and g.tipo = 1')
			        ->joinLeft(array('c'=>'prod_cat'), 'c.id_prod = p.ID')
			        ->where($where)
			        ->group("g.descricao")
			        ->group("s.descricao")
			        ->order("g.descricao")
			        ->order("s.descricao");			        
			     
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
								
		}
		
		function buscaProdutospormontadora($pesq){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('p'=>'produtos'), array('p.ID as idprod'))
			        ->join(array('pv'=>'tb_produto_veiculo'), 'pv.id_produto = p.ID')
			        ->join(array('v'=>'tb_veiculo'), 'v.id = pv.id_veiculo')
			        ->where("v.id_montadora = ".$pesq);
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		public function buscaProdutosvenda($params){

			if($params['tp']==2):
				$situacao = "p.situacao != 2";
			else:
				$situacao = "p.situacao = 0";
			endif;			
			
			$cod = explode(" ", $params['busca']);			
			for ($i=0;$i<=sizeof($cod);$i++):
				if(strlen($cod[$i])>=3):
					$where .= " and (p.CODIGO like '%".$cod[$i]."%'";
					$where .= " || ".$situacao." and v.no_modelo like '%".$cod[$i]."%'";
					$where .= " || ".$situacao." and p.DESCRICAO like '%".$cod[$i]."%')";
				endif;
			endfor;
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			$select->from(array('p'=>'produtos'), array('p.*'))
			        ->joinLeft(array('pv'=>'tb_produto_veiculo'), 'pv.id_produto = p.ID')
			        ->joinLeft(array('v'=>'tb_veiculo'), 'v.id = pv.id_veiculo')
			        ->where($situacao.$where)
			        ->group('p.ID');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();				
		}
		
		
		function listarKitsprodutos($prod){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('p'=>'produtos','*'), array('*'))
			        ->join(array('k'=>'tb_kits'), 'p.ID = k.id_prodkit')
			        ->where('k.id_prod = '.$prod);
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		
		function listaExibircatalogo($params){
			$obj 	= new ProdutosModel();
			$bo		= new ProdutoscatModel();
			
			return $bo->fetchAll('id_prod = '.$params,"cod_cat asc");			
		}
		
		function buscaMedidasprod($idprod){
			$bo		= new ProdutosModel();
			$bomed	= new ProdutosmediasModel();
			
			return $bomed->fetchAll('id_prod = '.$idprod);		
		}
		
		//-- Cadastro material produtos chines -----------------------------------------
		//--Listar materiais --------------------------
		function listaMaterial(){
			$obj = new ProdutosModel();
			$bo	 = new ProdutosmaterialModel();
			return $bo->fetchAll('sit = true','descricao');
		}
		
		//--Grava Material -------
		function gravaMaterial($params){
			$obj = new ProdutosModel();
			$bo	 = new ProdutosmaterialModel();
			$usuario = Zend_Auth::getInstance()->getIdentity();
			
			$array['descricao']			= $params['desc'];
			$array['descricaochines']	= $params['descchines'];
			$array['sit']				= true;
			
			if(empty($params['iddesc'])):			
				$id = $bo->insert($array);
				LogBO::cadastraLog("Produtos chines/Materiais",2,$usuario->id,$id,'Material ID '.$id);
			else:
				$id = $bo->update($array,'id = '.$params['iddesc']);
				LogBO::cadastraLog("Produtos chines/Materiais",4,$usuario->id,$id,'Material ID '.$id);
			endif;			
		}
		
		//--Remove Material -------
		function removeMaterial($params){
			$obj = new ProdutosModel();
			$bo	 = new ProdutosmaterialModel();
			$usuario = Zend_Auth::getInstance()->getIdentity();
				
			$array['sit']				= false;
			$bo->update($array,'md5(id) = "'.$params['iddesc'].'"');
			LogBO::cadastraLog("Produtos chines/Materiais",3,$usuario->id,$params['iddesc'],'Material ID '.$params['iddesc']);
			
		}
		
		function listaFornecedoresprodcross($params=""){
		    
		    if((!empty($params['forn'])) and (!empty($params['idprod']))):
			    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			    $db->setFetchMode(Zend_Db::FETCH_OBJ);
			    $select = $db->select();
				$select->from(array('f'=>'tb_fabricante'), array('p.codigo as codigocross'))
				    ->join(array('p'=>'tb_crossprodutos'), 'f.id = p.id_fabricante')
				    ->join(array('c'=>'tb_crossreference'), 'c.id_codprod = p.id')
				    ->join(array('pp'=>'tb_crossprodutos'), 'c.id_crossprodutos = pp.id')
				    ->where("p.principal = true and f.id_parceiro = ".$params['forn']." and pp.id_prod = ".$params['idprod']);
			    	
			    $stmt = $db->query($select);
			    
			    return $stmt->fetchAll();			    
			endif;		    
		    
		}
		
		
		//--- Localizacao de produtos -----------------------------------------------
		function buscaLocalizacao($cod=""){
			$obj 	= new ProdutosModel();
			$bo 	= new ProdutoslocalizacaoModel();
			
			if(!empty($cod)){
				return $bo->fetchAll('id_prod = "'.$cod.'"');
			}
		}
		
		function gravaLocalizacao($params=""){
			$obj 	= new ProdutosModel();
			$bo 	= new ProdutoslocalizacaoModel();
				
			try{
				if(!empty($params)){
					$array = array(
						'id_prod'	=> $params['busca'],
						'loca1'		=> $params['loca1']
					);
	
					$bo->insert($array);
				}
				return 1;
			}catch (Zend_Exeption $e){
			    return 0;
			}
		}
		
		function removeLocalizacao($params=""){
			$obj 	= new ProdutosModel();
			$bo 	= new ProdutoslocalizacaoModel();
		
			try{
				if(!empty($params)){
					$bo->delete("id = '".$params['idlocalizacao']."'");
				}
				return 1;
			}catch (Zend_Exeption $e){
				return 0;
			}
		}
		
		function listaClasses(){
			$bo	= new ProdutosclassesModel();
			return $bo->fetchAll();
		}		
		
		function gravarClasses($data){
			$bo	= new ProdutosclassesModel();
			
			$classe = array('letra' => strtoupper($data['letra']), 'markup' => str_replace(",", ".", str_replace(".", "", $data['markup'])));
			
			if(isset($data['id']) and $data['id'] != ""){
				$bo->update($classe,'id = "'.$data['id'].'"');
			}else{
				$bo->insert($classe);
			}
		}
	}
?>