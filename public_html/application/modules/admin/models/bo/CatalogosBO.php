<?php
	class CatalogosBO{		

		function montarCatalogo($params){
		    $bov = new VeiculosModel();
		    $usuario = Zend_Auth::getInstance()->getIdentity();
		    
		    if((($usuario->id_perfil == 1) || ($usuario->id_perfil == 28) || ($usuario->id_perfil == 29) || ($usuario->id_perfil == 16))){
		        $situacao = 'p.situacao != 2 and ';
			}else{
		    	$situacao = 'p.situacao = 0 and ';
		   	}
		    
		    //-- busca produtos cross ---------------------------------
		    $bo		= new RefcruzadaModel();
		    $boc	= new CodigoscrossModel();
		     
		    if(count($boc->fetchAll("visualizar = true and sit = true and codigo = '".strtoupper($params['buscageral'])."'")) > 0){
		        		        
		        foreach ($boc->fetchAll("visualizar = true and sit = true and codigo = '".strtoupper($params['buscageral'])."'") as $list){
		        
			    	$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			    	$db->setFetchMode(Zend_Db::FETCH_OBJ);
			    	$select = $db->select();
			    
			    	$select->from(array('r'=>'tb_crossreference','*'), array('p.id_prod as idcodigo','p.codigo','f.no_fabricante','f.id as idfabricante'))
			    		->join(array('p'=>'tb_crossprodutos'), 'p.id = r.id_crossprodutos and p.id_fabricante = 1')
				    	->join(array('f'=>'tb_fabricante'), 'f.id = p.id_fabricante ')
				    	->where('r.sit = true and p.sit = true and r.id_codprod = '.$list->id);
			    
			    	$stmt = $db->query($select);
			    	
			    	foreach ($stmt->fetchAll() as $cross){
			    	    $produtos .= $cross->idcodigo.",";
			    	}
		    	}		    	
		    }
		    
		    //-- busca generica por veiculo -------------------------------------------
		    $busca = explode(" ", $params['buscageral']);
		    
		    if((empty($params['buscageral']) || $params['buscageral'] == "Código ZTL, código original ou descrição")){
		        $wheregenerica = "p.id > 0";
		    }elseif(count($busca) == 1){
		        
		        $busca = $busca[0];		        
		        //-- verifica se tem algum carro na busca ----------------------------
		        if(count($bov->fetchAll("no_modelo like '%".$busca."%'"))>0){
		        	$whereveiculo = "v.no_modelo like '%".$busca."%'";
		        }else{
		            if(substr($busca,-2) == "es") $busca = substr($busca,0,-2);
		            elseif(substr($busca,-1) == "s") $busca = substr($busca,0,-1);
		            $wheregenerica = "p.CODIGO like '%".$busca."%' || p.DESCRICAO like '%".$busca."%'";
		        }
		    }else{
			    foreach ($busca as $row => $valor){
			        if(strlen($valor) > 2){			            
			            //-- verifica se tem algum carro na busca ----------------------------
				        if(count($bov->fetchAll("no_modelo like '%".$valor."%'"))>0){
				            $whereveiculo = " and  (v.no_modelo like '%".$valor."%')";
				        }else{
				            if(substr($valor,-2) == "es") $valor = substr($valor,0,-2);
				            elseif(substr($valor,-1) == "s") $valor = substr($valor,0,-1);
				             
				            $whered .= "p.DESCRICAO like '%".$valor."%' and ";
				        }
			        }
			    }

			    $wheregenerica = "(".substr($whered, 0,-4).")";			    
		    }
		    
		    if((strlen($wheregenerica)>0) || (strlen($whereveiculo)>0)){
			   	$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			    $db->setFetchMode(Zend_Db::FETCH_OBJ);
			     
			    $select = $db->select();
			     
			    $select->from(array('p'=>'produtos','*'), array('p.ID as idproduto'))
		    		->joinLeft(array('pv'=>'tb_produto_veiculo'), 'pv.id_produto = p.ID')
		    		->joinLeft(array('v'=>'tb_veiculo'), 'v.id = pv.id_veiculo')
		    		->where($situacao."(".$wheregenerica.$whereveiculo.")")
		    		->group('p.ID');
			     
			    $stmt = $db->query($select);
			    
			    foreach ($stmt->fetchAll() as $cross){
			    	$produtos .= $cross->idproduto.",";
			    }			    			    
		    }
		    
		    //--- busca avancada ------------------------------------------------------------
		    if(!empty($params['codigoztl']) || (!empty($params['buscagruposub']) and $params['buscagruposub'] != 0) || (!empty($params['buscagrupo']) and $params['buscagrupo'] != 0)){ 
			    if(!empty($params['codigoztl'])) $whereout = " and p.CODIGO like '".$params['codigoztl']."%'";
			    if(!empty($params['buscagruposub']) and $params['buscagruposub'] != 0) $whereout .= " and p.id_gruposprodsub = ".$params['buscagruposub'];
			    if(!empty($params['buscagrupo']) and $params['buscagrupo'] != 0) $whereout .= " and s.id_gruposprod = ".$params['buscagrupo'];
			    
			    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			    $db->setFetchMode(Zend_Db::FETCH_OBJ);
			    	
			    $select = $db->select();
			    	
			    $select->from(array('p'=>'produtos','*'), array('p.ID as idproduto'))
				    ->join(array('s'=>'tb_gruposprodsub'), 's.id = p.id_gruposprodsub')
				    ->where($situacao."s.tipo = 1 ".$whereout)
				    ->group('p.ID');
			    	
			    $stmt = $db->query($select);
			    
			    foreach ($stmt->fetchAll() as $cross){
			    	$produtosav .= $cross->idproduto.",";
			    }

			    
		    	//--- verifico busca generica com busca avancada ------------------------------------------------------
			    if(!empty($produtos)){
			    	$arrayprod 		= explode(",", substr($produtos,0,-1));
			    	$arrayprod2		= explode(",", substr($produtosav,0,-1));
			    	
			    	foreach ($arrayprod as $linha => $valor){
			    		if(in_array($valor, $arrayprod2)){
			    			$novoprod .= $valor.",";
			    		}			    		
			    	}
			    	$produtos = $novoprod;
			    }			    
		    }
		    
		    if(!empty($params['montadora']) and $params['montadora'] != 0){
		        
		        $produtosav = "";
		        $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			    $db->setFetchMode(Zend_Db::FETCH_OBJ);
			    	
			    $select = $db->select();
			    	
			    $select->from(array('p'=>'produtos'), array('p.ID as idproduto'))
					->join(array('pv'=>'tb_produto_veiculo'), 'pv.id_produto = p.ID')
				    ->join(array('v'=>'tb_veiculo'), 'v.id = pv.id_veiculo')
				    ->where($situacao."v.id_montadora = '".$params['montadora']."'")
			    	->group('p.ID');
			    	
			    $stmt = $db->query($select);
			    foreach ($stmt->fetchAll() as $cross){
			    	$produtosav .= $cross->idproduto.",";
			    }
			    
			    //--- verifico busca generica com busca avancada ------------------------------------------------------
			    if(!empty($produtos)){
			    	$arrayprod 		= explode(",", substr($produtos,0,-1));
			    	$arrayprod2		= explode(",", substr($produtosav,0,-1));
			    	
			    	$novoprod = "";
			    	foreach ($arrayprod as $linha => $valor){
			    		if(in_array($valor, $arrayprod2)){
			    			$novoprod .= $valor.",";
			    		}
			    		
			    	}
			    	$produtos = $novoprod;
			    }
			    
		    }
		    
		    if(!empty($produtos)){
		    $listprod = $produtos;
		    
		    //-- busca os grupos conforme dados da pesquisa -----------------------------------------------------------------
		    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		    $db->setFetchMode(Zend_Db::FETCH_OBJ);
		    	
		    $select = $db->select();
		    	
		    $select->from(array('p'=>'produtos','*'), array('g.descricao as descgrupo','s.descricao as descsubgrupo','s.id as idsubg','s.id_gruposprod as grupoprod'))
		    		->join(array('s'=>'tb_gruposprodsub'), 's.id = p.id_gruposprodsub and s.tipo = 1')
		    		->join(array('g'=>'tb_gruposprod'), 'g.id = s.id_gruposprod and g.tipo = 1')
		    		->where($situacao.'p.ID in ('.substr($listprod, 0,-1).')')
		    		->group("g.descricao")
		    		->group("s.descricao")
		    		->order("g.descricao")
		    		->order("s.descricao");
		    
		    $stmt = $db->query($select);
		    $objgrupos = $stmt->fetchAll();
		    
		    if(count($objgrupos)>0){
		    
		    foreach ($objgrupos as $grupos){
		    	?>
    			<div class="divgrupos">  
    			<?=($grupos->descgrupo." - ".$grupos->descsubgrupo)?>
    			</div>
		    			  
    			<?php 
    			// --- busca produtos ----------------------------------------------------------------
    			
    			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
    			$db->setFetchMode(Zend_Db::FETCH_OBJ);
    				
    			$select = $db->select();
    				
    			$select->from(array('p'=>'produtos','*'), array('p.ID as idprod','p.*','ncm.ncm','ncm.ncmex'))
	    			->joinLeft(array('m'=>'tb_produtosmedidas'), 'm.id_prod = p.ID')
	    			->joinLeft(array('ncm'=>'tb_produtosncm'), 'ncm.id = p.id_ncm')
	    			->where($situacao.'p.ID in ('.substr($listprod, 0,-1).') and p.id_gruposprodsub = "'.$grupos->idsubg.'"')
	    			->order('p.codigo_mask')
	    			->group('p.ID');
    				
    			$stmt = $db->query($select);
    			
    			$objprodutos = $produtos = "";
    			$objprodutos = $stmt->fetchAll();
    			
    			foreach ($objprodutos as $produtos){
    				?>	
    				<table class="tbproduto">
    					<thead>
		    				<tr>
    							<td rowspan="2" class="tdcodigo">
    								<?=$produtos->CODIGO?>
    							</td>
    							<td class="tbdescricao" colspan="3">
    								<?=($produtos->DESCRICAO)?> | <?=($produtos->APLICACAO)?>
    							</td>
    						</tr>
    						<tr>
    							<td class="tbtitulo" style="width: 26%">
    							Montadora
    							</td>
    							<td class="tbtitulo" style="width: 26%">
    							Modelo
    							</td>
    							<td class="tbtitulo" style="width: 23%; border-right: 0px">
    							Ano
    							</td>
    						</tr>
    					</thead>
    					<tbody>
    						<tr>
    							<td rowspan="2" style="text-align: center; vertical-align: top;">
    							<?php 
    					        if(file_exists(BaseImg.'imgprodutos/'.$produtos->idprod.'/imagem1.jpg')):
    					        ?>
    					        	<img align="top" src="/public/images/imgprodutos/<?=$produtos->idprod?>/imagem1.jpg" width="150" border="0" />
    					        <?php else: ?>
    					        	<img align="top" src="/public/images/hotsites/catalogo/lancamento.jpg"  width="150" />
    					        <?php endif; ?>
    							</td>
    							<td colspan="3" style="vertical-align: top">
    							<?php 
    							
    							$objveiculos = "";
    							$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
    							$db->setFetchMode(Zend_Db::FETCH_OBJ);
    								
    							$select = $db->select();
    								
    							$select->from(array('pv'=>'tb_produto_veiculo','*'), array('pv.*','v.*'))
	    							->join(array('v'=>'tb_veiculo'), 'v.id = pv.id_veiculo')
	    							->join(array('m'=>'tb_montadora'), 'm.ID = v.id_montadora')
	    							->where('pv.id_produto = "'.$produtos->idprod.'"')
    								->order("m.NOME")
    								->order("v.no_modelo");
    								
    							$stmt = $db->query($select);
    							$objveiculos =  $stmt->fetchAll();
    							
    							
    							if(count($objveiculos)>0){
    								$contclass = "tbaplicacao";
    								?><table style="width: 100%; border-collapse: collapse;" ><?php 
    						        foreach ($objveiculos as $veiculos){
    									if($contclass == "tbaplicacao") $contclass = "tbaplicacao2"; else $contclass = "tbaplicacao";
    									?>
    					        		<tr>
    					        			<td align="left" style="width: 35%" class="<?php echo $contclass?>">
    					        				<?=utf8_encode($veiculos->NOME)?>
    					        			</td>
    					        			<td align="left" style="width: 35%" class="<?php echo $contclass?>">
    					        				<?=($veiculos->no_modelo)?>
    					        			</td>
    					        			<td align="left" style="width: 30%" class="<?php echo $contclass?>">
    					        				<?
    					        				if(!empty($veiculos->ano_ini) and empty($veiculos->ano_fim)):
    					        					echo $veiculos->ano_ini." > ";
    					        				elseif(empty($veiculos->ano_ini) and !empty($veiculos->ano_fim)):
    					        					echo "< ".$veiculos->ano_fim;
    					        				elseif(!empty($veiculos->ano_ini) and !empty($veiculos->ano_fim)):
    					        					echo $veiculos->ano_ini." > ".$veiculos->ano_fim;	        				
    					        				else:
    					        					echo "Todos";
    					        				endif;
    					        				?>
    					        			</td>
    					        		</tr>
    					        		<?php 
    				        		}    				        		
    				        		?></table><?php
    				        	}	
		    
    				        	if($contclass == "tbaplicacao") $contclass = "tbaplicacao2"; else $contclass = "tbaplicacao";
    							?>
    							
    							</td>
		    						</tr>
		    						<tr>
		    							<td colspan="3" class="<?php echo $contclass?>" style="height: 5%">
		    							<?php 
		    						        if(($grupos->grupoprod==8)):
		    						        	//-- Cruzetas e trizetas 
		    						        	?>
		    						        	Interno: <?=$produtos->medida_inner_cru?> mm &nbsp; 
		    									Altura: <?=$produtos->medida_high_cru?> mm &nbsp; 
		    									Dentes:	<?=$produtos->medida_teeth_cru?> mm &nbsp; 				        	
		    						        	<?php 
		    						        elseif(($grupos->grupoprod==1)||($produtos->grupoprod==6)):
		    						        	//--Restantes -----------------
		    						        	?>
		    						        	Interno: <?=$produtos->M_INNER?> mm &nbsp; 
		    									Externo: <?=$produtos->M_OUTER?> mm &nbsp; 
		    									Altura: <?=$produtos->M_HIGH?> mm 
		    						        	<?php
		    						        elseif(($grupos->grupoprod==2)):
		    						        	$medida = "";
		    						        	if($produtos->cubo_eixo > 0):
		    						        		$medida = "Eixo rolamento: ".number_format($produtos->cubo_eixo,2,",","");
		    						        	endif;
		    						        	
		    						        	if($produtos->cubo_altura > 0):
		    						        		$medida .= " &nbsp; Altura: ".number_format($produtos->cubo_altura,2,",","");
		    						        	endif;
		    						        	
		    						        	if($produtos->cubo_externo > 0):
		    						        		$medida .= " &nbsp; Externo: ".number_format($produtos->cubo_externo,2,",","");
		    						        	endif;
		    						        	
		    						        	//-Dentes homocinética
		    						        	if($produtos->cubo_denteshomo > 0):
		    						        		$medida .= " &nbsp; Dentes homocinética: ".$produtos->cubo_denteshomo;
		    						        	endif;
		    						        	
		    						        	//-Geração
		    						        	if($produtos->cubo_geracao > 0):
		    						        		$medida .= " &nbsp; Geração: ".$produtos->cubo_geracao."ª";
		    						        	endif;
		    						        	
		    						        	//-Construção
		    						        	if($produtos->cubo_construcao > 0):
		    						        		$medida .= " &nbsp; Contrução: "; if($produtos->cubo_construcao==1) $medida .= "Esfera"; else $medida .= "Roletes";
		    						        	endif;
		    						        	
		    						        	//- Alt/Conj/Rolante:
		    						        	if($produtos->cubo_altconjrolante > 0):
		    						        		$medida .= " &nbsp; Alt/Conj/Rolante: ".number_format($produtos->cubo_altconjrolante,2,",","");
		    						        	endif;
		    						        	
		    						        	//-  Tipo ABS
		    						        	if($produtos->cubo_tipoabs > 0):
		    						        		$medida .= " Tipo ABS: "; if($produtos->cubo_tipoabs==1) $medida .= "Magnético"; elseif($produtos->cubo_tipoabs==2) $medida .= "Cabo conector"; elseif($produtos->cubo_tipoabs==3) $medida .= "Coroa"; elseif($produtos->cubo_tipoabs==4) $medida .= "Sem ABS";
		    						        	endif;
		    						        	
		    						        	//-  Altura coroa ABS
		    						        	if($produtos->cubo_alturacoroaabs > 0):
		    						        		$medida .= " &nbsp; Altura coroa ABS: ".number_format($produtos->cubo_alturacoroaabs,2,",","");
		    						        	endif;
		    						        	
		    						        	//-  Dentes coroa ABS
		    						        	if($produtos->cubo_dentescoroaabs > 0):
		    						        		$medida .= " &nbsp; Dentes coroa ABS: ".$produtos->cubo_dentescoroaabs;
		    						        	endif;
		    						        	
		    						        	//-- Qt furos
		    						        	if($produtos->cubo_qtfuroparafuso > 0):
		    						        		if($produtos->cubo_tipoparafuso == 1):
		    						        	    	$medida .= " &nbsp; Qt furos: ".$produtos->cubo_qtfuroparafuso;
		    						        		else:
		    						        			$medida .= " &nbsp; Qt pafarusos: ".$produtos->cubo_qtfuroparafuso;
		    						        		endif;
		    						        	endif;
		    						        endif;
		    				
		    						        echo $medida;
		    						        
		    						    ?>
		    							</td>
		    						</tr>
		    					</tbody>				
		    				</table>
		    				  	
		    				     
	    				  <?php 
	    				  }
					}
				}
		    		  
		    }else{
				?>
				<div id="naolocalizado">
				Produto não localizado!
				</div>
				<?php 
			}
		}
		
		function montarCatalogomobile($params){
			$bov = new VeiculosModel();
			$usuario = Zend_Auth::getInstance()->getIdentity();
		
			if((($usuario->id_perfil == 1) || ($usuario->id_perfil == 28) || ($usuario->id_perfil == 29) || ($usuario->id_perfil == 16))){
				$situacao = 'p.situacao != 2 and ';
			}else{
				$situacao = 'p.situacao = 0 and ';
			}
		
			//-- busca produtos cross ---------------------------------
			$bo		= new RefcruzadaModel();
			$boc	= new CodigoscrossModel();
			 
			if(count($boc->fetchAll("visualizar = true and sit = true and codigo = '".strtoupper($params['buscageral'])."'")) > 0){
		
				foreach ($boc->fetchAll("visualizar = true and sit = true and codigo = '".strtoupper($params['buscageral'])."'") as $list){
		
					$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
					$db->setFetchMode(Zend_Db::FETCH_OBJ);
					$select = $db->select();
					 
					$select->from(array('r'=>'tb_crossreference','*'), array('p.id_prod as idcodigo','p.codigo','f.no_fabricante','f.id as idfabricante'))
					->join(array('p'=>'tb_crossprodutos'), 'p.id = r.id_crossprodutos and p.id_fabricante = 1')
					->join(array('f'=>'tb_fabricante'), 'f.id = p.id_fabricante ')
					->where('r.sit = true and p.sit = true and r.id_codprod = '.$list->id);
					 
					$stmt = $db->query($select);
		
					foreach ($stmt->fetchAll() as $cross){
						$produtos .= $cross->idcodigo.",";
					}
				}
			}
		
			//-- busca generica por veiculo -------------------------------------------
			$busca = explode(" ", $params['buscageral']);
		
			if((empty($params['buscageral']) || $params['buscageral'] == "Código ZTL, código original ou descrição")){
				$wheregenerica = "p.id > 0";
			}elseif(count($busca) == 1){
		
				$busca = $busca[0];
				//-- verifica se tem algum carro na busca ----------------------------
				if(count($bov->fetchAll("no_modelo like '%".$busca."%'"))>0){
					$whereveiculo = "v.no_modelo like '%".$busca."%'";
				}else{
					if(substr($busca,-2) == "es") $busca = substr($busca,0,-2);
					elseif(substr($busca,-1) == "s") $busca = substr($busca,0,-1);
					$wheregenerica = "p.CODIGO like '%".$busca."%' || p.DESCRICAO like '%".$busca."%'";
				}
			}else{
				foreach ($busca as $row => $valor){
					if(strlen($valor) > 2){
						//-- verifica se tem algum carro na busca ----------------------------
						if(count($bov->fetchAll("no_modelo like '%".$valor."%'"))>0){
							$whereveiculo = " and  (v.no_modelo like '%".$valor."%')";
						}else{
							if(substr($valor,-2) == "es") $valor = substr($valor,0,-2);
							elseif(substr($valor,-1) == "s") $valor = substr($valor,0,-1);
							 
							$whered .= "p.DESCRICAO like '%".$valor."%' and ";
						}
					}
				}
		
				$wheregenerica = "(".substr($whered, 0,-4).")";
			}
		
			if((strlen($wheregenerica)>0) || (strlen($whereveiculo)>0)){
				$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
				$db->setFetchMode(Zend_Db::FETCH_OBJ);
		
				$select = $db->select();
		
				$select->from(array('p'=>'produtos','*'), array('p.ID as idproduto'))
				->join(array('pv'=>'tb_produto_veiculo'), 'pv.id_produto = p.ID')
				->join(array('v'=>'tb_veiculo'), 'v.id = pv.id_veiculo')
				->where($situacao."(".$wheregenerica.$whereveiculo.")")
				->group('p.ID');
		
				$stmt = $db->query($select);
				 
				foreach ($stmt->fetchAll() as $cross){
					$produtos .= $cross->idproduto.",";
				}
			}
		
			//--- busca avancada ------------------------------------------------------------
			if(!empty($params['codigoztl']) || (!empty($params['buscagruposub']) and $params['buscagruposub'] != 0) || (!empty($params['buscagrupo']) and $params['buscagrupo'] != 0)){
				if(!empty($params['codigoztl'])) $whereout = " and p.CODIGO like '".$params['codigoztl']."%'";
				if(!empty($params['buscagruposub']) and $params['buscagruposub'] != 0) $whereout .= " and p.id_gruposprodsub = ".$params['buscagruposub'];
				if(!empty($params['buscagrupo']) and $params['buscagrupo'] != 0) $whereout .= " and s.id_gruposprod = ".$params['buscagrupo'];
				 
				$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
				$db->setFetchMode(Zend_Db::FETCH_OBJ);
		
				$select = $db->select();
		
				$select->from(array('p'=>'produtos','*'), array('p.ID as idproduto'))
				->join(array('s'=>'tb_gruposprodsub'), 's.id = p.id_gruposprodsub')
				->where($situacao."s.tipo = 1 ".$whereout)
				->group('p.ID');
		
				$stmt = $db->query($select);
				 
				foreach ($stmt->fetchAll() as $cross){
					$produtosav .= $cross->idproduto.",";
				}
		
				 
				//--- verifico busca generica com busca avancada ------------------------------------------------------
				if(!empty($produtos)){
					$arrayprod 		= explode(",", substr($produtos,0,-1));
					$arrayprod2		= explode(",", substr($produtosav,0,-1));
		
					foreach ($arrayprod as $linha => $valor){
						if(in_array($valor, $arrayprod2)){
							$novoprod .= $valor.",";
						}
					}
					$produtos = $novoprod;
				}
			}
		
			if(!empty($params['montadora']) and $params['montadora'] != 0){
		
				$produtosav = "";
				$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
				$db->setFetchMode(Zend_Db::FETCH_OBJ);
		
				$select = $db->select();
		
				$select->from(array('p'=>'produtos'), array('p.ID as idproduto'))
				->join(array('pv'=>'tb_produto_veiculo'), 'pv.id_produto = p.ID')
				->join(array('v'=>'tb_veiculo'), 'v.id = pv.id_veiculo')
				->where($situacao."v.id_montadora = '".$params['montadora']."'")
				->group('p.ID');
		
				$stmt = $db->query($select);
				foreach ($stmt->fetchAll() as $cross){
					$produtosav .= $cross->idproduto.",";
				}
				 
				//--- verifico busca generica com busca avancada ------------------------------------------------------
				if(!empty($produtos)){
					$arrayprod 		= explode(",", substr($produtos,0,-1));
					$arrayprod2		= explode(",", substr($produtosav,0,-1));
		
					$novoprod = "";
					foreach ($arrayprod as $linha => $valor){
						if(in_array($valor, $arrayprod2)){
							$novoprod .= $valor.",";
						}
					  
					}
					$produtos = $novoprod;
				}
				 
			}
		
			if(!empty($produtos)){
				$listprod = $produtos;
		
				//-- busca os grupos conforme dados da pesquisa -----------------------------------------------------------------
				$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
				$db->setFetchMode(Zend_Db::FETCH_OBJ);
				 
				$select = $db->select();
				 
				$select->from(array('p'=>'produtos','*'), array('g.descricao as descgrupo','s.descricao as descsubgrupo','s.id as idsubg','s.id_gruposprod as grupoprod'))
				->join(array('s'=>'tb_gruposprodsub'), 's.id = p.id_gruposprodsub and s.tipo = 1')
				->join(array('g'=>'tb_gruposprod'), 'g.id = s.id_gruposprod and g.tipo = 1')
				->where($situacao.'p.ID in ('.substr($listprod, 0,-1).')')
				->group("g.descricao")
				->group("s.descricao")
				->order("g.descricao")
				->order("s.descricao");
		
				$stmt = $db->query($select);
				$objgrupos = $stmt->fetchAll();
		
				if(count($objgrupos)>0){
		
					foreach ($objgrupos as $grupos){
						
		    			// --- busca produtos ----------------------------------------------------------------
		    			
		    			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		    			$db->setFetchMode(Zend_Db::FETCH_OBJ);
		    				
		    			$select = $db->select();
		    				
		    			$select->from(array('p'=>'produtos','*'), array('p.ID as idprod','p.*','ncm.ncm','ncm.ncmex'))
			    			->joinLeft(array('m'=>'tb_produtosmedidas'), 'm.id_prod = p.ID')
			    			->joinLeft(array('ncm'=>'tb_produtosncm'), 'ncm.id = p.id_ncm')
			    			->where($situacao.'p.ID in ('.substr($listprod, 0,-1).') and p.id_gruposprodsub = "'.$grupos->idsubg.'"')
			    			->order('p.codigo_mask')
			    			->group('p.ID');
		    				
		    			$stmt = $db->query($select);
		    			
		    			$objprodutos = $produtos = "";
		    			$objprodutos = $stmt->fetchAll();
		    			
		    			foreach ($objprodutos as $produtos){
		    				?>	
		    				<table class="tbproduto">
		    					<thead>
				    				<tr>
		    							<td class="tdcodigo" colspan="2">
		    								<div style="width: 25%; float: left; padding-top: 3px">&nbsp;
			    								<?	if(file_exists(BaseImg."imgprodutos/".$produtos->idprod."/imagem1.jpg")){ ?>
			    								<div style="float: left;  padding-left: 5px" onclick='buscaImg("<?php echo $produtos->idprod?>",1)' >
													<img src="/public/sistema/imagens/mobile/foto.svg" style="width: 40px">
												</div>
												<?php } ?>
												
												<?	if(file_exists(BaseImg."imgprodutos/".$produtos->idprod."/imagem1_".$produtos->idprod.".jpg")){ ?>
			    								<div style="float: left; padding-left: 5px" onclick='buscaImg("<?php echo $produtos->idprod?>",2);' >
													<img src="/public/sistema/imagens/mobile/projeto.svg" style="width: 40px">
												</div>
												<?php } ?>												
											</div>
											
											<div style="width: 55%; float:left;">
		    									<?=$produtos->CODIGO?>
		    								</div>
		    								
		    								<div style="width: 10%; float: right;" onclick="exibeLinha2('<?php echo $produtos->idprod?>');">
		    									<img id="img_<?php echo $produtos->idprod?>" style="width: 100%" alt="Aplicação" src="/public/sistema/imagens/mobile/abrir_catalogo.svg">		    									
		    								</div>
		    							</td>
		    						</tr>
		    						<tr>
		    							<td class="tbdescricao" colspan="2">
		    								<?=($produtos->DESCRICAO)?> | <?=($produtos->APLICACAO)?>
		    							</td>
		    						</tr>
		    					</thead>
		    					<tbody>
		    						<tr onclick="exibeLinha2('<?php echo $produtos->idprod?>');">
		    							<td class="tbtitulo"  style="display: none">
			    							<img id="img_<?php echo $produtos->idprod?>" style="width: 30%" alt="Aplicação" src="/public/sistema/imagens/mobile/aplicacao.svg">
			    						</td>
		    						</tr>
		    						<tr style="display: none" id="<?php echo $produtos->idprod?>">
		    							<td  style="border: 0px solid">
		    								<table style="border-collapse: collapse; width: 100%;">
		    								<tbody >
		    								<tr >
				    							<td class="tbtitulo" style="border: 0px solid; width: 50%">
				    								Modelo
				    							</td>
				    							<td class="tbtitulo" style="border: 0px solid">
				    							Ano
				    							</td>
				    						</tr>
				    							<?php 
				    							
				    							$objveiculos = "";
				    							$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
				    							$db->setFetchMode(Zend_Db::FETCH_OBJ);
				    								
				    							$select = $db->select();
				    								
				    							$select->from(array('pv'=>'tb_produto_veiculo','*'), array('pv.*','v.*'))
					    							->join(array('v'=>'tb_veiculo'), 'v.id = pv.id_veiculo')
					    							->join(array('m'=>'tb_montadora'), 'm.ID = v.id_montadora')
					    							->where('pv.id_produto = "'.$produtos->idprod.'"')
				    								->order("m.NOME")
				    								->order("v.no_modelo");
				    								
				    							$stmt = $db->query($select);
				    							$objveiculos =  $stmt->fetchAll();
				    							
				    							
				    							if(count($objveiculos)>0){
				    								$contclass = "tbaplicacao";
				    								foreach ($objveiculos as $veiculos){
				    									if($contclass == "tbaplicacao") $contclass = "tbaplicacao2"; else $contclass = "tbaplicacao";
				    									?>
				    					        		<tr>
				    					        			<td align="left" class="<?php echo $contclass?> tbaplicacaopadding">
				    					        				<?=($veiculos->no_modelo)?>
				    					        			</td>
				    					        			<td align="left" class="<?php echo $contclass?>" style="text-align: center;">
				    					        				<?
				    					        				if(!empty($veiculos->ano_ini) and empty($veiculos->ano_fim)):
				    					        					echo $veiculos->ano_ini." > ";
				    					        				elseif(empty($veiculos->ano_ini) and !empty($veiculos->ano_fim)):
				    					        					echo "< ".$veiculos->ano_fim;
				    					        				elseif(!empty($veiculos->ano_ini) and !empty($veiculos->ano_fim)):
				    					        					echo $veiculos->ano_ini." > ".$veiculos->ano_fim;	        				
				    					        				else:
				    					        					echo "Todos";
				    					        				endif;
				    					        				?>
				    					        			</td>
				    					        		</tr>
				    					        		<?php 
				    				        		}
				    				        	}	
						    
				    				        	if($contclass == "tbaplicacao") $contclass = "tbaplicacao2"; else $contclass = "tbaplicacao";
				    							?>
				    								<tr>
						    							<td colspan="3" class="<?php echo $contclass?>" style="height: 5%; text-align: left;">
						    							<?php 
						    						        if(($grupos->grupoprod==8)):
						    						        	//-- Cruzetas e trizetas 
						    						        	?>
						    						        	Interno: <?=$produtos->medida_inner_cru?> mm &nbsp; 
						    									Altura: <?=$produtos->medida_high_cru?> mm &nbsp; 
						    									Dentes:	<?=$produtos->medida_teeth_cru?> mm &nbsp; 				        	
						    						        	<?php 
						    						        elseif(($grupos->grupoprod==1)||($produtos->grupoprod==6)):
						    						        	//--Restantes -----------------
						    						        	?>
						    						        	Interno: <?=$produtos->M_INNER?> mm &nbsp; 
						    									Externo: <?=$produtos->M_OUTER?> mm &nbsp; 
						    									Altura: <?=$produtos->M_HIGH?> mm 
						    						        	<?php
						    						        elseif(($grupos->grupoprod==2)):
						    						        	$medida = "";
						    						        	if($produtos->cubo_eixo > 0):
						    						        		$medida = "Eixo rolamento: ".number_format($produtos->cubo_eixo,2,",","");
						    						        	endif;
						    						        	
						    						        	if($produtos->cubo_altura > 0):
						    						        		$medida .= " &nbsp; Altura: ".number_format($produtos->cubo_altura,2,",","");
						    						        	endif;
						    						        	
						    						        	if($produtos->cubo_externo > 0):
						    						        		$medida .= " &nbsp; Externo: ".number_format($produtos->cubo_externo,2,",","");
						    						        	endif;
						    						        	
						    						        	//-Dentes homocinética
						    						        	if($produtos->cubo_denteshomo > 0):
						    						        		$medida .= " &nbsp; Dentes homocinética: ".$produtos->cubo_denteshomo;
						    						        	endif;
						    						        	
						    						        	//-Geração
						    						        	if($produtos->cubo_geracao > 0):
						    						        		$medida .= " &nbsp; Geração: ".$produtos->cubo_geracao."ª";
						    						        	endif;
						    						        	
						    						        	//-Construção
						    						        	if($produtos->cubo_construcao > 0):
						    						        		$medida .= " &nbsp; Contrução: "; if($produtos->cubo_construcao==1) $medida .= "Esfera"; else $medida .= "Roletes";
						    						        	endif;
						    						        	
						    						        	//- Alt/Conj/Rolante:
						    						        	if($produtos->cubo_altconjrolante > 0):
						    						        		$medida .= " &nbsp; Alt/Conj/Rolante: ".number_format($produtos->cubo_altconjrolante,2,",","");
						    						        	endif;
						    						        	
						    						        	//-  Tipo ABS
						    						        	if($produtos->cubo_tipoabs > 0):
						    						        		$medida .= " Tipo ABS: "; if($produtos->cubo_tipoabs==1) $medida .= "Magnético"; elseif($produtos->cubo_tipoabs==2) $medida .= "Cabo conector"; elseif($produtos->cubo_tipoabs==3) $medida .= "Coroa"; elseif($produtos->cubo_tipoabs==4) $medida .= "Sem ABS";
						    						        	endif;
						    						        	
						    						        	//-  Altura coroa ABS
						    						        	if($produtos->cubo_alturacoroaabs > 0):
						    						        		$medida .= " &nbsp; Altura coroa ABS: ".number_format($produtos->cubo_alturacoroaabs,2,",","");
						    						        	endif;
						    						        	
						    						        	//-  Dentes coroa ABS
						    						        	if($produtos->cubo_dentescoroaabs > 0):
						    						        		$medida .= " &nbsp; Dentes coroa ABS: ".$produtos->cubo_dentescoroaabs;
						    						        	endif;
						    						        	
						    						        	//-- Qt furos
						    						        	if($produtos->cubo_qtfuroparafuso > 0):
						    						        		if($produtos->cubo_tipoparafuso == 1):
						    						        	    	$medida .= " &nbsp; Qt furos: ".$produtos->cubo_qtfuroparafuso;
						    						        		else:
						    						        			$medida .= " &nbsp; Qt pafarusos: ".$produtos->cubo_qtfuroparafuso;
						    						        		endif;
						    						        	endif;
						    						        endif;
						    				
						    						        echo $medida;
						    						        
						    						    ?>
						    							</td>
						    						</tr>
						    					</tbody>	
		    								</table>
		    							</td>
		    						</tr>
		    					</tbody>		    					
		    				</table>
				    			  <?php 
			    				  }
			    			?><div style="height: 50px">&nbsp;</div><?php 
							}
						}
				    		  
				    }else{
						?>
						<div id="naolocalizado">
						Produto não localizado!
						</div>
						<?php 
					}
				}
	}
?>

