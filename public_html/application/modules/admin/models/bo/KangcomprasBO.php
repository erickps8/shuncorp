<?php
	class KangcomprasBO{		
				
		function listaComprasabertas(){
			$obj = new KangcomprasModel();
			return $obj->fetchAll('fin = 0 and sit!=0');					
		}
		
		/*function listaPedidos(){
					
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_tai_compra','*'),
			        array('t.id_tai_compra as id','t.status','DATE_FORMAT(t.data, "%d/%m/%Y") as data','c.EMPRESA','t.sit','c.track_code','c.ID as idcliente','t.fin'))
			        ->join(array('c'=>'clientes'),'t.id_for = c.ID')
			        ->where("t.sit = true")
			        ->order('t.id_tai_compra desc','');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();				
		}
		*/
		
		/* Lista todos as compras shukang com financeiro em aberto
		 * Usado em Administracao/buscacontasfinpedAction 
		 */
		function listaPedidosabertos($var){

			if(!empty($var['forn']) and $var['forn']!= 0):			
				$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
				$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
				$select = $db->select();
				
				$select->from(array('t'=>'tb_kang_compra'), array('t.id_kang_compra as idcompra','sum(f.valor) as valorpago','(select sum(p.qt*p.preco) from tb_kang_comprasprod p where p.id_kang_compra = t.id_kang_compra) as totalped'))
				        ->joinLeft(array('f'=>'tb_finpurchase'),'f.id_kang_compra = t.id_kang_compra')
				        ->where("t.fin = 0 and t.sit = 1 and id_for = '".$var['forn']."'")
				        ->group("t.id_kang_compra")
				        ->order('t.id_kang_compra asc','');
				
				$stmt = $db->query($select);
				return $stmt->fetchAll();
			endif;				
		}
		
		function baixarfinanceiroCompra($var){
			$bo		= new KangcomprasModel();
			$data['fin']		= 1;
			$bo->update($data, "md5(id_kang_compra) = '".$var['ped']."'");
		}
		
		function removeCompra($params){
			$bo		= new KangcomprasModel();
			$bov	= new KangvendasModel();
			$bovp	= new VendasprodModel();
			
			$ped = $bo->fetchRow("id_kang_compra = '".$params['ped']."'");
						
			foreach($this->listaProdutoscompra(md5($params['ped'])) as $listprod){
				$bovp->update(array('comprado' => false),"ID_PRODUTO = '".$listprod->id_prod."' and ID_PEDIDO = '".$ped->id_ped."'");				
			}
			
			$bo->update(array('sit' => 0), "id_kang_compra = '".$params['ped']."'");
			
			return $ped->id_kang_compra;
			
		}
		
		//-- Busca compra por ID, com fornecedor --------------------------
		function buscaCompras($id){
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_kang_compra','*'), array('*','DATE_FORMAT(t.data, "%d/%m/%Y") as datacompra','t.sit as sitped'))
			        ->join(array('c'=>'clientes'),'t.id_for = c.ID')
			        ->where("md5(t.id_kang_compra) = '".$id."'");
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();
			
		}
		
		//--Lista produtos pedidos------------------
		function listaProdutoscompra($id){
								
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('p'=>'produtos','*'),
			        array('p.ID as id_prod','p.CODIGO','t.qt','t.preco','t.gravacao','DATE_FORMAT(t.prazo, "%d/%m/%Y") as data','t.id as idkangprod', 'h.ncm as hscode'))
			        ->join(array('t'=>'tb_kang_comprasprod'),'t.id_prod = p.ID')
			        ->joinLeft(array('h'=>'tb_produtoshscode'),'h.id = p.id_hscode')
			        ->where("md5(t.id_kang_compra) = '".$id."'")
			        ->order('p.codigo_mask','');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
								
		}
				
		/*--Grava entrega -------------------------------------
		 * usado em gravarentregaAction -----------------------
		*/
		function gravaEntrega($params){
			$bo 		= new KangcomprasModel();
			$boe	 	= new KangcomprasentregaModel(); 
			
			foreach(KangcomprasBO::listaProdutoscompra(md5($params['pedido'])) as $listprod):
				$arrayprod['id_ped']				= $params['pedido'];
				$arrayprod['id_prod']				= $listprod->id_prod;
				$arrayprod['qt']					= $params[$listprod->id_prod];
				$arrayprod['dt_ent']				= date("Y-m-d");
				$arrayprod['sit']					= 1;
				$arrayprod['id_kang_comprasprod']	= $listprod->idkangprod;
				
				if(!empty($params[$listprod->id_prod]))	$boe->insert($arrayprod);
				
			endforeach;
			
			LogBO::cadastraLog("Kang Compras/Receber produtos",4,$_SESSION['S_ID'],$params['pedido'],'Pedido N° PK'.substr("000000".$params['pedido'],-6,6));
		}
		
		//--Remove entrega-------
		function removeEntrega($params){
			$bo 		= new KangcomprasModel();
			$boe	 	= new KangcomprasentregaModel(); 
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			$boe->update(array('sit' => false), "id = ".$params['rem']);
			
			LogBO::cadastraLog("Kang Compras/Remover entrega",4,$usuario->id,$params['ped'],'PK'.substr("000000".$params['ped'],-6,6));
		}
		
		//--Lista produtos entregues------------------
		function listaProdutosentregue($id){
								
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
						
			$select->from(array('t'=>'tb_kangprodent','*'), array('p.ID as id_prod','p.CODIGO','t.qt as quant','t.id as id_ent','DATE_FORMAT(t.dt_ent, "%d/%m/%Y") as data','c.preco'))
			        ->join(array('p'=>'produtos'),'t.id_prod = p.ID')
			        ->join(array('c'=>'tb_kang_comprasprod'),'c.id_kang_compra = t.id_ped')
			        ->where("md5(t.id_ped) = '".$id."' and t.sit != 0 and c.id_prod = t.id_prod")
			        ->order('t.dt_ent')
			        ->order('p.codigo_mask','t.id');			        
			        
			$stmt = $db->query($select);
			return $stmt->fetchAll();									
		}
		
		//-- Observacaoes de pedidos de compra -----------------------------------------
		/**
		 * 
		 */
		
		function listaGruposobs(){
		    $obj = new KangcomprasobsModel();
		    $bo  = new KangcomprasgruposobsModel();
		    
		    return $bo->fetchAll('sit = true');
		    
		}
		
		//--Grava Obs -------
		function gravaGruposobs($params){
			$ob  = new KangcomprasobsModel();
			$bo  = new KangcomprasgruposobsModel();
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			$array = array('desc_ingles' => $params['grupo'], 'sit' => true);
				
			if(empty($params['id'])):
    			$id = $bo->insert($array);
    			LogBO::cadastraLog("Kang Compras/Grupos de regras",2,$usuario->id,$id,'Grupo ID '.$id);
			else:
    			$id = $bo->update($array,'id = '.$params['id']);
    			LogBO::cadastraLog("Kang Compras/Grupos de regras",4,$usuario->id,$id,'Grupo ID '.$id);
			endif;
			
		}
		
		//--Remover Grupos de regras de compra -------
		function removerGruposobs($params){
			$ob  = new KangcomprasobsModel();
			$bo  = new KangcomprasgruposobsModel();
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			$bo->update(array('sit' => false),'id = "'.$params['grupo'].'"');
			LogBO::cadastraLog("Kang Compras/Cadastro Regras de compra",3,$usuario->id,$params['grupo'],'Grupo ID '.$params['grupo']);
		}
		
		
		//--Listar obs de compras--------------------------
		function listaObs($var = array()){
			$obj = new KangcomprasobsModel();
			
			$where = " and id_kanggrupospedobs = '".$var['grupo']."'";
			return $obj->fetchAll('sit = true'.$where);			
		}
		
		//--Grava Obs -------
		function gravaObs($params){
		    try{
    			$ob  = new KangcomprasobsModel();
    			$obj = new KangcomprasobspedModel();
    			$usuario 	= Zend_Auth::getInstance()->getIdentity();
    			
    			$obj->delete("id_ped = ".$params['compra']);
    			
    			$arrRegras = explode(";", $params['idregras']);
    			
    			foreach ($arrRegras as $regras => $vlregra){
    			    if(!empty($vlregra)) $obj->insert(array('id_ped' => $params['compra'], 'id_obs' => $vlregra));
    			}
    			
    			LogBO::cadastraLog("Kang Compras/Regras de compra",4,$usuario->id,$params['compra'],'PK'.substr("000000".$params['compra'],-6,6));
    			return true;
    			
    		}catch (Zend_Exception $e){
    		    $boerro	= new ErrosModel();
    		    $dataerro = array('descricao' => $e->getMessage(), 'pagina' => "KangcomprasBO::gravarObs()");
    		    $boerro->insert($dataerro);
    		    return false;
		    }
		}
		
		//--Listar obs de compras gravadas--------------------------
		function listaObsgravados($params){
			$ob  = new KangcomprasobsModel();
			$obj = new KangcomprasobspedModel();
			return $obj->fetchAll('md5(id_ped) = "'.$params['ped'].'"');			
		}
		
		//--Listar obs de compras gravadas com a descricao--------------------------
		function listaObspedidos($idmd5){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
						
			$select->from(array('t'=>'tb_kang_obsped','*'), array('*'))
			        ->join(array('p'=>'tb_kang_pedobs'),'t.id_obs = p.id')
			        ->where("md5(t.id_ped) = '".$idmd5."'");			        
			        
			$stmt = $db->query($select);
			return $stmt->fetchAll();				
		}
		
		
		//--Grava Regras de compra -------
		function gravaRegrascompra($params){
			$ob  = new KangcomprasobsModel();
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			$array['desc_ing']			    = $params['ingles'];
			$array['desc_chines']		    = $params['chines'];
			$array['id_kanggrupospedobs']	= $params['grupo'];
			$array['sit']				    = true;
			
			
			if(empty($params['id'])):			
				$id = $ob->insert($array);
				LogBO::cadastraLog("Kang Compras/Regras de compra",2,$usuario->id,$id,'Regra ID '.$id);
			else:
				$id = $ob->update($array,'id = '.$params['id']);
				LogBO::cadastraLog("Kang Compras/Regras de compra",4,$usuario->id,$id,'Regra ID '.$id);
			endif;			
		}
		
		//--Remover Regras de compra -------
		function removerRegrascompra($params){
			$ob  = new KangcomprasobsModel();
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			$id = $ob->update(array('sit' => false),'id = "'.$params['idregra'].'"');
			LogBO::cadastraLog("Kang Compras/Cadastro Regras de compra",3,$usuario->id,$id,'Regra ID '.$id);						
		}
		
		
		function buscaRegrascomprasgrupos($params=array()){
		    $ob  = new KangcomprasobsModel();
		    $obj = new KangcomprasobspedModel();
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
            $ob  = new KangcomprasobsModel();
            $obj = new KangcomprasobspedModel();

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
		
		
		//--Fecha pedido-------
		function fecharPedido($params){
			$bo 		= new KangcomprasModel();			 
			$array['sit']		= 3;
			$bo->update($array,"(id_kang_compra) = '".$params['pedido']."'");			
			LogBO::cadastraLog("Kang Compras/Fechar compra",4,$_SESSION['S_ID'],$params['pedido'],'Pedido N° PK'.substr("000000".$params['pedido'],-6,6));
		}
				
		/*-- Lista as empresas que compoe o pedido de venda da shunkang, sem pedido de compra definido
		 * Usado em gerarpedidocompra
		 * */
		function listaEmpresaspedidosvenda($val){
						
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_kang_vendasprod','*'), array('*','c.ID as idfor'))
		        ->join(array('p'=>'produtos'),'t.ID_PRODUTO = p.ID')
		        ->joinLeft(array('c'=>'clientes'),'c.ID = p.id_cliente_fornecedor_shuntai')
		        ->joinLeft(array('ch'=>'tb_clientechina'),'ch.id_cliente = c.ID')
		        ->where("t.comprado = 0 and md5(t.ID_PEDIDO) = '".$val['ped']."'")
		        ->group('c.ID')
		        ->order('c.EMPRESA');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();	
		}
				
		/*--- Busco pedidos de compras prod ID da venda da shunkang ------------------------
		 * Usado em gerarpedidocompra
		 */
		function buscaCompraporvenda($var){
					
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_kang_compra','*'), array('t.status','c.EMPRESA','t.sit as sitped','c.ID as idcliente','t.fin','t.id_kang_compra'))
			        ->join(array('c'=>'clientes'),'t.id_for = c.ID')
			        ->joinLeft(array('ch'=>'tb_clientechina'),'ch.id_cliente = c.ID')
			        ->where("t.sit != 0 and md5(t.id_ped) = '".$var['ped']."'")
			        ->order('t.id_kang_compra desc','');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();				
		}
		
		/*--- Busco produtos dos pedidos de compras prod ID da venda da shunkang ------------------------
		 * Usado em gerarpedidocompra
		 */
		function buscaProdutoscompraporvenda($var){
					
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_kang_compra','*'), array('*','DATE_FORMAT(p.prazo, "%d/%m/%Y") as data','p.hs_code as hscode','p.id as idkangprod'))
			        ->join(array('p'=>'tb_kang_comprasprod'),'t.id_kang_compra = p.id_kang_compra')
			        ->join(array('pr'=>'produtos'),'pr.ID = p.id_prod')
			        ->where("t.sit != 0 and md5(t.id_ped) = '".$var['ped']."'")
			        ->order('pr.codigo_mask asc','');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();				
		}

		/*--- Busco produtos entregues dos pedidos de compras prod ID da venda da shunkang ------------------------
		 * Usado em gerarpedidocomprafhghf
		 */
		function listaProdentreguesporvenda($var){
								
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
						
			$select->from(array('tc'=>'tb_kang_compra'), array('p.ID as id_prod','p.CODIGO','t.qt as quant','t.id as id_ent','DATE_FORMAT(t.dt_ent, "%d/%m/%Y") as data','c.preco'))
					->join(array('t'=>'tb_kangprodent'),'t.id_ped = tc.id_kang_compra')        
					->join(array('c'=>'tb_kang_comprasprod'),'c.id_kang_compra = t.id_ped')
					->join(array('p'=>'produtos'),'t.id_prod = p.ID')
			        ->where("md5(tc.id_ped) = '".$var['ped']."' and t.sit != 0 and c.id_prod = t.id_prod and tc.sit != 0")
			        ->order('t.dt_ent')
			        ->order('p.codigo_mask','t.id');
			        
			        
			$stmt = $db->query($select);
			return $stmt->fetchAll();										
		}		
		
		/*--- Gerar compra da Kang apartir da venda -------------------------------
		 * 
		 * */
		
		function gerarCompra($params){
			$idfor	= 0; // -- Uso para marcar o proximo pedido de compra, pelo fornecedor ----			
			$bo		= new KangcomprasModel();
			$bop	= new KangcomprasprodModel();
			$bov	= new KangvendasModel();
			$bovp	= new VendasprodModel();
			$boe 	= new KangcomprasentregaModel(); 

			
			Zend_Debug::dump($params);
			
			//-- Listo primeiros as empresas ------------------
			$arrayped['ped']	= md5($params['pedido']);
			foreach (KangcomprasBO::listaEmpresaspedidosvenda($arrayped) as $empresas):
				    //-- Depois listos os produtos, comparando com o fornecedor de cada prod ---------------
				foreach (KangvendasBO::listaPedidosvendasprod($arrayped) as $produtos):
					/* --- Novos pedidos ---------------------
					-- Verifico se foi preenchido os campos impressao e data de entrega. So grava se estiver preenchidos ------------------
					*/
			
					if(!empty($params['data_'.$produtos->ID_PRODUTO])): // and !empty($params['impressao_'.$produtos->ID_PRODUTO])		
					
						//-- Verifico o fornecedor da peca ------------
						if(($empresas->idfor == $produtos->id_cliente_fornecedor_shuntai) and ($produtos->comprado == false)):
							if($idfor != $empresas->idfor):
								$array['id_ped']		= $params['pedido'];
								$array['id_for']		= $empresas->idfor;	
								$array['status']		= "REGISTERED";
								$array['data']			= date("Y-m-d H:i:s");
								$array['sit']			= 1;
								$array['fin']			= false;
								
								$idkang	= $bo->insert($array);
							endif;
						
							$idfor = $empresas->idfor;						
	
							//--- Gravo produtos nas compras kang -------------------------
							
							$arrayprod = "";
							$arrayprod = array();
							
							$arrayprod['id_kang_compra']	= $idkang;
							$arrayprod['id_prod']			= $produtos->ID_PRODUTO;
							$arrayprod['qt']				= $produtos->QT;
							$arrayprod['preco']				= $produtos->custo_valor_shuntai;
							$arrayprod['prazo']				= substr($params['data_'.$produtos->ID_PRODUTO],6,4).'-'.substr($params['data_'.$produtos->ID_PRODUTO],3,2).'-'.substr($params['data_'.$produtos->ID_PRODUTO],0,2); 
							$arrayprod['gravacao']			= $params['impressao_'.$produtos->ID_PRODUTO];
							$arrayprod['hs_code']			= $produtos->hscode;
							$arrayprod['retorno']			= $produtos->retorno;
							$arrayprod['material']			= $produtos->descricaochines;
							
							$arraybysca = array('idprod' => $produtos->ID_PRODUTO, 'forn' => $idfor);
							$this->objCodigocross = ProdutosBO::listaFornecedoresprodcross($arraybysca);
							
							
							if(count($this->objCodigocross)>0):
								foreach ($this->objCodigocross as $codigocross);
								$arrayprod['codfor']		= $codigocross->codigocross;
							else:
								$arrayprod['codfor']		= "";
							endif;
						
							
							$bop->insert($arrayprod);
					
							//--- Marco produtos como comprado nos produtos das vendas -------------------------
							$arrayven['comprado']	 = true;
							$bovp->update($arrayven,"id = ".$produtos->idprodven);
						endif;						
					endif;				
					
				endforeach;
			endforeach;
			
			/*-- Marcar pedido de venda com compras ja geradas --------------------*/
			$verped = 0;
			foreach (KangvendasBO::listaPedidosvendasprod($arrayped) as $produtos):
				if($produtos->comprado == false):
					$verped = 1;
				endif;
			endforeach;
			
			if($verped == 0):
				$arraypedido['STATUS']		= 'ORDERED';
				$bov->update($arraypedido,"ID = ".$params['pedido']);
			endif;			
		}
		
		/*--- Gravar entregas das compras apartir do pedido de venda -----------------------------*/
		function gravarEntregacompraporvenda($params){
			$bo		= new KangcomprasModel();
			$boe 	= new KangcomprasentregaModel(); 
						
			//-- Listo primeiros as empresas ------------------
			$arrayped['ped']	= md5($params['pedido']);
			foreach (KangcomprasBO::buscaProdutoscompraporvenda($arrayped) as $produtos):
				if(!empty($params['entrega_'.$produtos->id_prod])):
					$arrayprod['id_ped']				= $produtos->id_kang_compra;
					$arrayprod['id_prod']				= $produtos->id_prod;
					$arrayprod['qt']					= $params['entrega_'.$produtos->id_prod];
					$arrayprod['dt_ent']				= date("Y-m-d");
					$arrayprod['sit']					= 1;
				
					$boe->insert($arrayprod);
				endif;
			endforeach;
		}
		
		function fecharPedidoscompraporvenda($var){
			$bo 	= new KangvendasModel();
			
			$arrayped['ped']	= md5($var['pedido']);
			foreach (KangcomprasBO::buscaCompraporvenda($arrayped) as $pedidos):
				$arrayped['pedido']	= $pedidos->id_kang_compra;
				KangcomprasBO::fecharPedido($arrayped);
			endforeach;
			
			$array['STATUS']	= "FINISHED";
			$bo->update($array,"ID = ".$var['pedido']);
			
		}
		
		function uploadqcr($params){
		    
		    $bo   = new KangcomprasModel();
		    $bop  = new KangcomprasprodModel();
		    
		    $pasta = Zend_Registry::get('pastaPadrao')."/public/sistema/upload/qcr";
		    
		    $upload = new Zend_File_Transfer_Adapter_Http();
		    $upload->setDestination($pasta);
		    
		    $files = $upload->getFileInfo();
		    	
		    if($files){		         
		        foreach ($files as $file => $info);
		            
	            $ext = substr(strrchr($info['name'], "."), 1);
	            $nome = $params['idkangprod'].".".$ext;
	            
	            $upload->addFilter('Rename', array('target' => $pasta.'/'.$nome, 'overwrite' => true));
	    
	            if ($upload->isValid($file)) {
	                echo $upload->receive($file);
	                $bop->update(array('anexo' => $ext), "id = '".$params['idkangprod']."'");
	            }	
		    }		    
		}
		
		function editarPedidoscompra($params){
            $bo     = new KangcomprasModel();
            $bop    = new KangcomprasprodModel();
		    
            foreach ($bop->fetchAll("id_kang_compra  = '".$params['pedido']."'") as $produtos){
                $dataArray = array(
                    'preco'         => str_replace(",",".", str_replace('.','', $params['preco'.$produtos->id])),
                    'gravacao'      => $params['gravacao'.$produtos->id],
                    'prazo'         => substr($params['data'.$produtos->id],6,4).'-'.substr($params['data'.$produtos->id],3,2).'-'.substr($params['data'.$produtos->id],0,2), 
                );
                
                $bop->update($dataArray, 'id = "'.$produtos->id.'"');
            }		    
		}
		
		/**
		 * Busca o histórico de preços praticado nas compras
		 * @param string $fornecedor
		 * @param string $produto
		 * @return array objeto
		 */
		function buscaHistoriopreco($fornecedor=null, $produto=null){
		
		    $where = 'p.ID > 0';
		    $where = ($fornecedor != null and $fornecedor != "0") ? $where.' and c.id_for = "'.$fornecedor.'"' : $where;
		    $where = ($produto != null) ? $where.' and p.CODIGO = "'.$produto.'"' : $where;
		    
		    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		    $db->setFetchMode(Zend_Db::FETCH_OBJ);
		
		    $select = $db->select();
		
		    $select->from(array('p'=>'produtos'), array('*','DATE_FORMAT(c.data, "%d/%m/%Y") as datacompra'))
		        ->join(array('t'=>'tb_kang_comprasprod'),'t.id_prod = p.ID')
		        ->join(array('c'=>'tb_kang_compra'),'c.id_kang_compra = t.id_kang_compra')
		        ->join(array('cl'=>'clientes'),'cl.ID = c.id_for')
		        ->where($where)
		        ->order('p.codigo_mask')
		        ->order('c.data desc')
		        ->order('cl.EMPRESA');
			  		
		    $stmt = $db->query($select); 
		    return $stmt->fetchAll();
		}
	}
?>