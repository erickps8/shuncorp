<?php
	class FinanceirochinapagBO{		
	    function gravarContaspag($params){
	        $bof	= new FinanceirochinaModel();
			$bop	= new FinanceirochinapagarModel();
	        	        	
	        try{
	            $array['emissao']			=	substr($params['emissaopag'],6,4).'-'.substr($params['emissaopag'],3,2).'-'.substr($params['emissaopag'],0,2);
	            $array['vencimento']		=	substr($params['vencimentopag'],6,4).'-'.substr($params['vencimentopag'],3,2).'-'.substr($params['vencimentopag'],0,2);
	            $array['moeda']				=	$params['moedapag'];
	            $array['valor_apagar']		=	str_replace(',','.',str_replace('.','',$params['valorpag']));
	            $array['id_fornecedor']		=	$params['fornpag'];
	            $array['out_fornecedor']	=	$params['outfornpag'];
	            $array['n_documento']		=	$params['faturapag'];
	            $array['fatura']			=	$params['parcpag'];
	            $array['obs']				=	$params['obspag'];
	            $array['id_planoconta']		=	$params['planocontapag'];
	            $array['npurchase']			=	$params['tipopurch'];
	            $array['baixa']				= 	0;
	            	            
	            if(($params['fornpag']!=0) and ($params['fornpag']!='out')){
	               $forn = explode('|', $params['fornpag']);
	            
    	            if($forn[1]==0){
        	            $array['id_fornecedor']		=	$forn[0];
        	            $array['id_usuarios']		=	NULL;
        	            $array['out_fornecedor']	=   NULL;
    	            }else{
        	            $array['id_fornecedor']		=	NULL;
        	            $array['id_usuarios']		=	$forn[0];
        	            $array['out_fornecedor']	=   NULL;
    	            }
	            }elseif(!empty($params['outfornpag'])){
    	            $array['out_fornecedor']	=	$params['outfornpag'];
    	            $array['id_usuarios']		=	NULL;
    	            $array['id_fornecedor']		=   NULL;
	            }
	            
	            if(!empty($params['idcontapag'])){
    	            if(!empty($params['liberarpag'])){
    	               $array['baixa']		= 2;
    	            }elseif(!empty($params['baixarpag'])){
    	               $array['baixa']		= 1;
    	            }
    	            
    	            $bop->update($array, "id = ".$params['idcontapag']);
    	            $id = $params['idcontapag'];
	            	
	            }else{
	               $id = $bop->insert($array);
	            }
	    
	            if(!empty($params['baixarpag'])){
    	            $array['valor_pago']		= str_replace(',','.',str_replace('.','',$params['valorpagamentopag']));
    	            $array['id_mod_pagamento']	= $params['bancopagamentopag'];
    	            $array['baixa']				= 1;
	            	
    	            if(!empty($params['datapagamentopag'])){
    	               $array['dt_pagamento']	=	substr($params['datapagamentopag'],6,4).'-'.substr($params['datapagamentopag'],3,2).'-'.substr($params['datapagamentopag'],0,2);
    	            }
	            	
    	            $bop->update($array, "id = ".$params['idcontapag']);
    	            $id = $params['idcontapag'];
	            	
    	            $usuario = Zend_Auth::getInstance()->getIdentity();
    	            LogBO::cadastraLog("ADM/Fin chines/Pagamentos",4,$usuario->ID,$id,"Baixa ".$id);
	            }
	            
	            
	            return $id;
	    
	        }catch (Exception $e){
	            throw new Exception('Erro ao gravar a conta: '.$e->getMessage());
	        }
	    }
	    
	    /**
	     * Grava a invoice que esta sendo recebido
	     * @param unknown $params
	     * @param unknown $id
	     * @return boolean
	     */
	    function gravarPurchase($params, $id){
	        try{
	            $bof	= new FinanceirochinaModel();
    			$bop	= new FinanceirochinapagarModel();
    			$boa	= new FinanceirochinaanexopagModel();
    			$boh	= new FinanceirochinapurchaseModel();

    			$boh->delete("id_contasapagar = '".$id."'");
    			
	        	//--Fixar pedidos kang/tai--------------------
				$ids_array = explode(',',$params['idpurch']);
				for($i=0 ; $i < sizeof($ids_array); $i++){
					if(($params['tipopurch']==1) and ($ids_array[$i]!="")){
						$arraypur['id_contasapagar']	= $id;
						$arraypur['id_kang_compra']		= $ids_array[$i];
						$arraypur['valor']				= str_replace(',','.',str_replace('.','',$params['vlcompra_'.$ids_array[$i]]));
						$boh->insert($arraypur);					
					}elseif(($params['tipopurch']==2) and ($ids_array[$i]!="")){
						$arraypur['id_contasapagar']	= $id;
						$arraypur['id_tai_compra']		= $ids_array[$i];
						$arraypur['valor']				= str_replace(',','.',str_replace('.','',$params['vlcompra_'.$ids_array[$i]]));
						$boh->insert($arraypur);
					}
				}
			        
                return true;
	            
	        }catch (Exception $e){
	            throw new Exception('Lançamento registrado, porém ocorreu um erro ao vincular a Purchase: '.$e->getMessage());
	        }
	    }
	    
	    function gravarArquivos($params, $id){
	        //---Arquivos-------------------------------
	        
	        try{

	            $bof	= new FinanceirochinaModel();
	            $bop	= new FinanceirochinapagarModel();
	            $boa	= new FinanceirochinaanexopagModel();
	            $boh	= new FinanceirochinapurchaseModel();
	            
	            for($i=1;$i<=$params['intarchive'];$i++){
	            	            
    	            $arquivo = isset($_FILES['arquivo'.$i]) ? $_FILES['arquivo'.$i] : FALSE;
    	            $extensao = substr($_FILES['arquivo'.$i]['name'], strrpos($_FILES['arquivo'.$i]['name'], "."), strlen($_FILES['arquivo'.$i]['name']));
    	             
    	            $nome = $id."_".time().uniqid(md5()).$extensao;
    	            
    	            $pasta = Zend_Registry::get('pastaPadrao')."/public/sistema/upload/financeirochina/pagar/";
	             
	                if (!(is_dir($pasta))){
	                    if(!(mkdir($pasta, 0777))){
	                        throw new Exception('Pasta de upload nao existe, e nao pode ser criada');
	                    }
	                }
	                 
	                if(!(is_writable($pasta))){
	                    throw new Exception('Pasta sem permissao de escrita');
	                }
	            
    	            if(is_uploaded_file($_FILES['arquivo'.$i]["tmp_name"])){
    	                if (move_uploaded_file($arquivo["tmp_name"], $pasta . $nome)) {
    	                    $arrayarq['nome'] 				 	= $nome;
    	                    $arrayarq['id_fin_contasapagar']	= $id;
    	                    $boa->insert($arrayarq);
    	                } else {
    	                    throw new Exception('Não foi possivel fazer o upload para '.$pasta);
    	                    return $this;
    	                }
    	            }
	            
	            }
	            
                return true;
	            
	        }catch (Exception $e){
	            throw new Exception('Lançamento registrado, porém ocorreu ao subir os arquivos: '.$e->getMessage());
	        }	        
	    }
	    
	    /**
	     * Grava o a invoice do retorno de imposto
	     * @param unknown $params
	     * @param unknown $id
	     */
	    function gravaRetorno($params, $id){
	        $bov = new KangvendasModel();
	        $bo  = new KanginvoiceModel();
	     
            //-- removo marcaçoes antigas --
            $bo->update(array('id_fin_contasapagar' => null), 'id_fin_contasapagar = "'.$id.'"');
            
            foreach($params['retornoimposto'] as $row => $valor){
                $bo->update(array('id_fin_contasapagar' => $id), 'id = "'.$valor.'"');
            }	         
	    }

		/**
		 * Grava o a invoice do frete
		 * @param unknown $params
		 * @param unknown $id
		 */
		function gravaFrete($params, $id){
			$bov = new KangvendasModel();
			$bo  = new KanginvoiceModel();

			//-- removo marcaçoes antigas --
			$bo->update(array('id_fretepag' => null), 'id_fretepag = "'.$id.'"');

            $stFob = ($params['stFob'] == 1) ? true : false;

			foreach($params['frete'] as $row => $valor){
				$bo->update(array('id_fretepag' => $id, 'st_fob' => $stFob), 'id = "'.$valor.'"');
			}
		}
	}