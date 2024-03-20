<?php
	class ProdutosimportaBO{		
	    
	    //--valida grupos de venda ---------------------------------------------------------------
	    public function validaGrupos($codigo){
	        
	    	$options = array(
    	        'location' => 'http://ztlbrasil.com.br/soap/soap',
    	        'uri' => 'http://ztlbrasil.com.br/soap/'
	    	);
	    	 
	    	$soap = new SoapClient(null, $options);
	    	$idprod = "";
	    	
	    	try{
	    	    
	    	    $produto = $soap->buscaProdutoscodigo($codigo);
	    	    $params = $produto->_data;
	    	    
	    		foreach ($soap->buscaGrupo($params[0]['id_gruposprodsub']) as $grupo);
	    		if(count(GruposprodBO::buscaGruposvenda($grupo->grupo)) >0){ //-- exite grupo ------------------
	    			$gvenda = GruposprodBO::buscaGruposvenda($grupo->grupo);
	    			$gsubgrupo = GruposprodBO::buscaSubgruposvenda($gvenda->id, $grupo->subgrupo);
	    			
	    			if(count($gsubgrupo)<=0){ //-- nao existe subgrupo ---------------------
	    				$ob 	= new GruposprodModel();
	    				$obsg	= new GruposprodutossubModel();
	    	
	    				//-- grava subgrupo -----------------------------------------------
	    				$idsubgrupo = $obsg->insert(array('descricao' => $grupo->subgrupo, 'sit' => true, 'id_gruposprod' => $gvenda->id, 'tipo' => $grupo->stipo));
	    	
	    			}else{
	    			    $idsubgrupo = $gsubgrupo->id;
	    			}
	    		}else{ //-- nao existe grupo ---------------------------
	    	
	    			$ob 	= new GruposprodModel();
	    			$obg	= new GruposprodutosModel();
	    			$obsg	= new GruposprodutossubModel();
	    	
	    			//-- grava grupo ---------------------------------------------------
	    			$idgrupo = $obg->insert(array('descricao' => $grupo->grupo, 'sit' => true, 'tipo' => $grupo->gtipo));
	    	
	    			//-- grava subgrupo -----------------------------------------------
	    			$idsubgrupo = $obsg->insert(array('descricao' => $grupo->subgrupo, 'sit' => true, 'id_gruposprod' => $idgrupo, 'tipo' => $grupo->stipo));
	    		}
	    		
	    		return $idsubgrupo;
	    	
	    	}catch (Exception $e){
	    		throw new Exception($e->getMessage());
	    	}
	    }
	    
	    //--cadastro de produtos -----------------------------------------------------------------
	    public function atualizaProd($codigo){
	    	
	    	$boprod	= new ProdutosModel();
	    	
	    	/* $options = array(
    	        'location' => 'http://ztlbrasil.com.br/soap/soap',
    	        'uri' => 'http://ztlbrasil.com.br/soap/'
	    	); */
	    	
	    	$options = array(
    	        'location' => 'http://ztlbrasil.com.br/soap/soap',
    	        'uri' => 'http://ztlbrasil.com.br/soap/'
	    	);
	    		
	    	$soap = new SoapClient(null, $options);
	    	$idprod = "";
	    		    	
	    	try{
	    	    //-- trata grupo e subgrupo de venda ----------------------------------------------------------
	    		$produto = $soap->buscaProdutoscodigo($codigo);
	    		$params = $produto->_data;
	    		
	    		
	    		$subgrupo = ProdutosimportaBO::validaGrupos($codigo);
	    		
	    		$array['CODIGO']  				= $params[0]['CODIGO'];
	    		$array['DESCRICAO']   			= $params[0]['DESCRICAO'];
	    		$array['APLICACAO']   			= $params[0]['APLICACAO'];
	    		$array['PESO']					= $params[0]['PESO'];
	    		$array['id_ncm']				= $params[0]['id_ncm'];
	    		$array['codigo_mask']			= $params[0]['codigo_mask'];
	    		$array['unidade']				= $params[0]['unidade'];
	    		//$array['situacao']				= $params[0]['situacao'];
	    		$array['ball']					= $params[0]['ball'];
	    		$array['M_INNER']				= $params[0]['M_INNER'];
	    		$array['M_OUTER']				= $params[0]['M_OUTER'];
	    		$array['M_HIGH']				= $params[0]['M_HIGH'];
	    		$array['estriado_macho_d']		= $params[0]['estriado_macho_d'];
	    		$array['estriado_macho_mm']		= $params[0]['estriado_macho_mm'];
	    		$array['estriado_femea_d']		= $params[0]['estriado_femea_d'];
	    		$array['estriado_femea_mm']		= $params[0]['estriado_femea_mm'];
	    		$array['ins_aperto_homo']		= $params[0]['ins_aperto_homo'];
	    		$array['diametro_homo']			= $params[0]['diametro_homo'];
	    		$array['raio_porca_homo']		= $params[0]['raio_porca_homo'];
	    		$array['aperto_homo']			= $params[0]['aperto_homo'];
	    		$array['medida_inner_desl']		= $params[0]['medida_inner_desl'];
	    		$array['medida_outer_desl']		= $params[0]['medida_outer_desl'];
	    		$array['medida_high_desl']		= $params[0]['medida_high_desl'];
	    		$array['medida_teeth_desl']		= $params[0]['medida_teeth_desl'];
	    		$array['medida_inner_cru']		= $params[0]['medida_inner_cru'];
	    		$array['medida_high_cru']		= $params[0]['medida_high_cru'];
	    		$array['medida_teeth_cru']		= $params[0]['medida_teeth_cru'];
	    		$array['observacao']			= $params[0]['observacao'];
	    		$array['pl_prod_desc']			= $params[0]['pl_prod_desc'];
	    		$array['id_gruposprodsub']		= $subgrupo;
	    		$array['dt_cadastro']           = date("Y-m-d H:i:s");
	    		$array['principal']			    = 1;
	    		
	    		$buscProd = ProdutosBO::buscaProdutoscodigo($params[0]['CODIGO']);
	    		
	    		if(count($buscProd)>0){
	    		    $boprod->update($array, "CODIGO = '".$params[0]['CODIGO']."'");
	    		    
	    		    foreach ($buscProd as $prod);
	    		    $idprod = $prod->ID;	    		    
	    			
	    		}else{
	    		    $idprod = $boprod->insert($array);
	    		}
	    		
	    		ProdutosimportaBO::atualizaComponentes($codigo, $idprod);
	    		ProdutosimportaBO::atualizaMedidas($codigo, $idprod);
	    		
	    		return $idprod;
	    
	    	}catch (Exception $e){
	    		throw new Exception(false."|".$e->getMessage());
	    	}
	    		
	    }
	    
	    //--cadastro componentes -----------------------------------------------------------------
	    public function atualizaComponentes($codigo, $idprod){
	    
	    	$boprod	= new ProdutosModel();
	    	$bokit	= new KitsModel();
	    	
	    	$options = array(
    	        'location' => 'http://ztlbrasil.com.br/soap/soap',
    	        'uri' => 'http://ztlbrasil.com.br/soap/'
	    	);
	    	 
	    	$soap = new SoapClient(null, $options);
	    
	    	//-- componentes ------------------------------------------------------------------
	    	try{
	    		if(!empty($idprod)){
	    		    
	    		    //-- remove todos os componentes da base principais -------------------------------
	    		    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
	    		    $db->setFetchMode(Zend_Db::FETCH_OBJ);
	    		    	
	    		    $select = $db->select();
	    		    	
	    		    $select->from(array('k'=>'tb_kits','*'), array('k.id as idkit'))
    	    		    ->join(array('p'=>'produtos'), 'p.ID = k.id_prodkit')
    	    		    ->where("p.principal = 1 and k.id_prod = '".$idprod."'");
	    		    	
	    		    $stmt = $db->query($select);
	    		    
	    		    foreach ($stmt->fetchAll() as $proddelkit){
	    		        $bokit->delete("id = '".$proddelkit->idkit."'");
	    		    }

	    		    //-- busco os componetes no servidor de dados ---------------------------------
	    			$kit = $soap->listarKits($codigo);
	    			
	    			foreach ($kit as $arrkit){
	    			    //-- atualizo o componente como produto -----------------------------------
	    			    $idprodkit = ProdutosimportaBO::atualizaProd($arrkit->CODIGO);
	    			    
	    			    //$bokit->delete("id_prod = '".$idprod."' and id_prodkit = '".$idprodkit."'");
	    			    	
	    			    $arraykit = array(
    			    		'id_prod'	  =>	$idprod,
    			    		'id_prodkit'  =>	$idprodkit,
    			    		'qt'		  =>	$arrkit->qt
	    			    );
	    			    	
	    			    $bokit->insert($arraykit);
	    			}
	    		}
	    		
	    		return true;
	    
	    	}catch (Exception $e){
	    		throw new Exception("Erro ao atualizar componentes: ".$e->getMessage());
	    	}	    		
	    }
	    
	    //--cadastro medidas -----------------------------------------------------------------
	    public function atualizaMedidas($codigo, $idprod){
	    
	    	$boprod	= new ProdutosModel();
	    	$bomed	= new ProdutosmediasModel();
	    	
	    	$options = array(
    	        'location' => 'http://ztlbrasil.com.br/soap/soap',
    	        'uri' => 'http://ztlbrasil.com.br/soap/'
	    	);
	    	 
	    	$soap = new SoapClient(null, $options);
	    	
	    	try{
	    		if(!empty($idprod)){
	    			$bomed->delete("id_prod = '".$idprod."'");
	    			$medidas = $soap->buscaMedidasprod($codigo);
	    			 
	    			foreach ($medidas as $arrMedidas){
	    					
	    				$arraymed['cubo_eixo']				= $arrMedidas->cubo_eixo;
	    				$arraymed['cubo_denteshomo']		= $arrMedidas->cubo_denteshomo;
	    				$arraymed['cubo_tipoparafuso']		= $arrMedidas->cubo_qtfuroparafuso;
	    				$arraymed['cubo_qtfuroparafuso']	= $arrMedidas->cubo_qtfuroparafuso;
	    				$arraymed['cubo_tipoabs']			= $arrMedidas->cubo_tipoabs;
	    				$arraymed['cubo_dentescoroaabs']	= $arrMedidas->cubo_dentescoroaabs;
	    				$arraymed['cubo_alturacoroaabs']	= $arrMedidas->cubo_alturacoroaabs;
	    				$arraymed['cubo_altconjrolante']	= $arrMedidas->cubo_altconjrolante;
	    				$arraymed['cubo_altura']			= $arrMedidas->cubo_altura;
	    				$arraymed['cubo_externo']			= $arrMedidas->cubo_externo;
	    				$arraymed['cubo_geracao']			= $arrMedidas->cubo_geracao;
	    				$arraymed['cubo_construcao']		= $arrMedidas->cubo_construcao;
	    				$arraymed['id_prod']				= $idprod;
	    				 
	    				$bomed->insert($arraymed);
	    			}
	    		}
	    		
	    		return true;
	    		 
	    	}catch (Exception $e){
	    		throw new Exception("Erro ao atualizar medidas: ".$e->getMessage());
	    	}
	    		
	    }
	    
	    //--cadastro de imagens -----------------------------------------------------------------
	    public function atualizaImagens($codigo, $idprod){
	    		
	    	$options = array(
    	        'location' => 'http://ztlbrasil.com.br/soap/soap',
    	        'uri' => 'http://ztlbrasil.com.br/soap/'
	    	);
	    	 
	    	$soap = new SoapClient(null, $options);
	    
	    	try{
	    	    
	    	    $produto = $soap->buscaProdutoscodigo($codigo);
	    	    $params = $produto->_data;
	    	     
	    	    $dirDestino = Zend_Registry::get('pastaPadrao')."public/sistema/upload/produtos/imagens/".$idprod."/";
	    	     
	    	    if(DiversosBO::criarDiretorio($dirDestino)){
	    	    	$pastaRaiz = "http://hbr.ind.br/public/sistema/upload/produtos/imagens/".$params[0]['ID']."/";
	    	    	copy($pastaRaiz."imagem1.jpg", $dirDestino."imagem1.jpg");
	    	    	copy($pastaRaiz."imagem1_".$idprod.".jpg", $dirDestino."imagem1_".$idprod.".jpg");
	    	    }else{
	    	    	throw new Exception("Erro ao atualizar imagens: Pasta sem permissão de escrita.");
	    	    }
	    	    
	    	    return true;
	    
	    	}catch (Exception $e){
	    		throw new Exception(false."|Erro ao atualizar imagens ".$codigo.": ".$e->getMessage());
	    	}
	    	 
	    }
	    
	    
	    public function atualizaImagenscomponentes($idprod){
	    		    
	    	$options = array(
    	        'location' => 'http://ztlbrasil.com.br/soap/soap',
    	        'uri' => 'http://ztlbrasil.com.br/soap/'
	    	);
	    	 
	    	$soap = new SoapClient(null, $options);
	    	
	    	try{
	    	    	    	    
	    	    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
	    	    $db->setFetchMode(Zend_Db::FETCH_OBJ);
	    	     
	    	    $select = $db->select();
	    	     
	    	    $select->from(array('k'=>'tb_kits','*'), array('k.*','p.CODIGO','p.ID'))
            	    ->join(array('p'=>'produtos'), 'p.ID = k.id_prodkit')
            	    ->where("p.principal = 1 and k.id_prod = '".$idprod."'");
	    	     
	    	    $stmt = $db->query($select);
	    	    $prodimg = $stmt->fetchAll();
	    	    
	    	    if(count($prodimg)>0){
    	    		 
	    	        foreach ($prodimg as $prod){
	    	            
	    	            $produto = $soap->buscaProdutoscodigo($prod->CODIGO);
	    	            $params = $produto->_data;
	    	            
	    	            $dirDestino = Zend_Registry::get('pastaPadrao')."public/sistema/upload/produtos/imagens/".$prod->ID."/";
	    	            
	    	            if(DiversosBO::criarDiretorio($dirDestino)){
	    	            	$pastaRaiz = "http://hbr.ind.br/public/sistema/upload/produtos/imagens/".$params[0]['ID']."/";
	    	            	copy($pastaRaiz."imagem1.jpg", $dirDestino."imagem1.jpg");
	    	            	copy($pastaRaiz."imagem1_".$prod->ID.".jpg", $dirDestino."imagem1_".$prod->ID.".jpg");
	    	            }else{
	    	            	throw new Exception("Erro ao atualizar imagens: Pasta sem permissão de escrita.");
	    	            }
	    	            	    	            
	    	            ProdutosimportaBO::atualizaImagenscomponentes($prod->ID);	    	            
	    	        }
	    	    }
	    	    
	    	    return true;
	    	    
	    	}catch (Exception $e){
	    		throw new Exception(false."|Erro ao atualizar imagens: ".$e->getMessage());
	    	}
	    	 
	    }
	    
	    //-- atualiza anexos -----------------------------------------------------------------
	    public function atualizaAnexos($codigo, $idprod){
	    
	    	$boprod	= new ProdutosModel();
	    	$boarq	= new ProdarquivosModel();
	    
	    	$options = array(
    	        'location' => 'http://ztlbrasil.com.br/soap/soap',
    	        'uri' => 'http://ztlbrasil.com.br/soap/'
	    	);
	    	 
	    	$soap = new SoapClient(null, $options);
	    	
	    	try{
	    	    $produto = $soap->buscaProdutoscodigo($codigo);
	    	    $params = $produto->_data;
	    	    
	    		//$boarq->delete("id_prod = '".$idprod."'");
	    		$dirDestino = Zend_Registry::get('pastaPadrao')."public/sistema/upload/produtos/projeto/".$idprod."/";
	    
	    		if(DiversosBO::criarDiretorio($dirDestino)){
	    			$arquivos = $soap->listaArquivos($codigo);
	    
	    			if(count($arquivos)>0){
	    				foreach($arquivos as $arq){
	    					$pastaRaiz = "http://hbr.ind.br/public/sistema/upload/produtos/projeto/".$params[0]['ID']."/";
	    						    					
	    					$nomearquivo = uniqid().".".end(explode(".", $arq->arquivo));
	    					
	    					copy($pastaRaiz.$arq->arquivo, $dirDestino.$nomearquivo);
	    					$boarq->insert(array('arquivo' => $nomearquivo, 'id_prod' => $idprod, 'nome' => $arq->nome));
	    					
	    				}
	    			}
	    
	    		}else{
	    			throw new Exception(false."|Erro ao atualizar projetos: Pasta sem permissão de escrita.");
	    		}
	    		
	    		return true;
	    
	    	}catch (Exception $e){
	    		//throw new Exception(false."|Erro ao atualizar projetos: ".$e->getMessage());
	    		
	    	    echo $e->getMessage();
	    	    
	    	}	    		
	    }
	    
	    public function atualizaAnexoscomponentes($idprod){
	    	 
	    	$boprod	= new ProdutosModel();
	    	$boarq	= new ProdarquivosModel();
	    	 
	    	$options = array(
    	        'location' => 'http://ztlbrasil.com.br/soap/soap',
    	        'uri' => 'http://ztlbrasil.com.br/soap/'
	    	);
	    	 
	    	$soap = new SoapClient(null, $options);
	    
	    	try{
	    	    
	    	    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
	    	    $db->setFetchMode(Zend_Db::FETCH_OBJ);
	    	    	
	    	    $select = $db->select();
	    	    	
	    	    $select->from(array('k'=>'tb_kits','*'), array('k.*','p.CODIGO','p.ID'))
	    	    ->join(array('p'=>'produtos'), 'p.ID = k.id_prodkit')
	    	    ->where("p.principal = 1 and k.id_prod = '".$idprod."'");
	    	    	
	    	    $stmt = $db->query($select);
	    	    $prodarq = $stmt->fetchAll();
	    	    
	    	    if(count($prodarq)>0){
	    	    
	    	    	foreach ($prodarq as $prod){
	    	    		
	    	    	    
	    	    	    
	    	    		$produto = $soap->buscaProdutoscodigo($prod->CODIGO);
	    	    		$params = $produto->_data;
	    	    
	    	    		$dirDestino = Zend_Registry::get('pastaPadrao')."public/sistema/upload/produtos/projeto/".$prod->ID."/";
	    	    
    	    	    	if(DiversosBO::criarDiretorio($dirDestino)){
        	    			$arquivos = $soap->listaArquivos($prod->CODIGO);
        	    			 
        	    			echo count($arquivos);
        	    			
        	    			if(count($arquivos)>0){
        	    				foreach($arquivos as $arq){
        	    					$pastaRaiz = "http://hbr.ind.br/public/sistema/upload/produtos/projeto/".$params[0]['ID']."/";
        	    					
        	    					$nomearquivo = uniqid().".".end(explode(".", $arq->arquivo));
        	    					
        	    					copy($pastaRaiz.$arq->arquivo, $dirDestino.$nomearquivo);
        	    					$boarq->insert(array('arquivo' => $nomearquivo, 'id_prod' => $prod->ID, 'nome' => $arq->nome));
        	    				}
        	    			}
        	    			 
        	    		}else{
        	    			throw new Exception(false."|Erro ao atualizar projetos: Pasta sem permissão de escrita.");
        	    		}
	    	    
        	    		echo " - ".$prod->CODIGO." - ".$prod->ID."<br>";
        	    		
	    	    		ProdutosimportaBO::atualizaAnexoscomponentes($prod->ID);
	    	    	}
	    	    }	  

	    	    return true;
	    		 
	    	}catch (Exception $e){
	    		throw new Exception(false."|Erro ao atualizar projetos: ".$e->getMessage());
	    	}
	    }
	    
	    function buscaProdutosdesatualizados(){
	    	$boprod	= new ProdutosModel();
	    	$options = array(
    			'location' => 'http://hbr.ind.br/soap/soap',
    			'uri' => 'http://hbr.ind.br/soap/'
	    	);
	    	 
	    	$soap = new SoapClient(null, $options);
	    	 
	    	try{
	    		$objProdutos = $soap->listarProdutos();
	    		$params = $objProdutos->_data;
	    
	    		$valida = "Nenhum produto desatualizado";
	    
	    		if(count($params>0)){
	    			?>
                    <div class="widget first">
    	 			<table style="width: 100%" class="tableStatic">
    	               <thead>
    	                   <tr>
    	                        <td width="">Código</td>
    	                        <td width="">Descrição</td>
    	                        <td width="">Dt atualização</td>
    	                        <td width="">Opções</td>    	                        
    	                   </tr>
    	               </thead>
    	               <tbody>
                        <?php 
                        foreach ($params as $produtos){
                            $prodlocal = $boprod->fetchRow("CODIGO = '".$produtos['CODIGO']."'");
                            
                            if(count($prodlocal)>0){
                                if(strtotime($prodlocal->dt_cadastro) < strtotime($produtos['dt_cadastro'])){
                                ?>
                                <tr>
        	                        <td width="20%" align="center"><?php echo $produtos['CODIGO']?></td>
        	                        <td width=""><?php echo $produtos['DESCRICAO']?></td>
        	                        <td width="25%" align="center"><?php echo substr($produtos['dt_cadastro'],8,2)."/".substr($produtos['dt_cadastro'],5,2)."/".substr($produtos['dt_cadastro'],0,4)." ".substr($produtos['dt_cadastro'],10)?></td>
        	                        <td width="10%" align="center"><a href="javascript:" rel="<?php echo $prodlocal->CODIGO?>" class="produtoatualiza"><img src="/public/sistema/imagens/icons/middlenav/download3.png"  width="16" border="0" title="Atualizar"></a></td>    	                        
        	                    </tr>
                                <?php
    
                                $valida = "";
                                
                                }
                            }
                        } 
                        
                        if($valida!=""){
                            ?>
                            <tr>
    	                        <td align="center" colspan="4"><?php echo $valida?></td>    	                            	                        
    	                    </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                    </table>
                    </div>
                    <?php   
                }
            }catch (Exception $e){
	    		throw new Exception(false."|Erro ao buscar produtos: ".$e->getMessage());
	    	}
        }
	}
?>