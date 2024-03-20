<?php
	class FinanceirochinarecBO{		
	    function gravarContasrec($params){
	        $bof	= new FinanceirochinaModel();
	        $bor	= new FinanceirochinareceberModel();
	        	        	
	        try{
	            $array['emissao']			=	substr($params['emissaorec'],6,4).'-'.substr($params['emissaorec'],3,2).'-'.substr($params['emissaorec'],0,2);
	            $array['vencimento']		=	substr($params['vencimentorec'],6,4).'-'.substr($params['vencimentorec'],3,2).'-'.substr($params['vencimentorec'],0,2);
	            $array['moeda']				=	$params['moedarec'];
	            $array['valor_apagar']		=	str_replace(',','.',str_replace('.','',$params['valorrec']));
	            $array['id_fornecedor']		=	$params['fornrec'];
	            $array['out_fornecedor']	=	$params['outfornrec'];
	            $array['n_documento']		=	$params['faturarec'];
	            $array['fatura']			=	$params['parcrec'];
	            $array['obs']				=	$params['obsrec'];
	            $array['id_planoconta']		=	$params['planocontarec'];
	    
	            $array['valor_pago']		=	str_replace(',','.',str_replace('.','',$params['valorpagamentorec']));
	            $array['id_mod_pagamento']	=	$params['bancopagamentorec'];
	    
	            if(!empty($params['datapagamentorec'])){
	               $array['dt_pagamento']	=	substr($params['datapagamentorec'],6,4).'-'.substr($params['datapagamentorec'],3,2).'-'.substr($params['datapagamentorec'],0,2);
	            }
	    
	            if(!empty($params['idcontarec'])){
    	            if(!empty($params['liberarrec'])){
    	               $array['baixa']		= 2;
    	            }elseif(!empty($params['baixarec'])){
    	               $array['baixa']		= 1;
    	            }
	            	
    	            /* baixa = 0 - Excluido
    	             * baixa = 1 - Ativo pago
    	             * baixa = 2 - Ativo salvo
    	             * */
	            	
    	            $bor->update($array, "id = ".$params['idcontarec']);
    	            $id = $params['idcontarec'];
	            }else{
	                $id = $bor->insert($array);
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
	    function gravarInvoice($params, $id){
	        try{
	            $bof	= new FinanceirochinaModel();
	            $bor	= new FinanceirochinareceberModel();
	            $boi	= new FinanceirochinainvoiceModel();
	            
                //--Fixar Invoices--------------------
                $boi->delete("id_fin_contasareceber = ".$id);
                $ids_array = explode(',',$params['idpurch']);
                
                for($i=0 ; $i < sizeof($ids_array); $i++){
                    if(!empty($ids_array[$i])){
                        $arraypur['id_fin_contasareceber']	= $id;
                        $arraypur['id_kang_cominvoice']		= $ids_array[$i];
                        $arraypur['valor']					= str_replace(',','.',str_replace('.','',$params['vlinvoice_'.$ids_array[$i]]));
                        $boi->insert($arraypur);
                    }
                }
                
                return true;
	            
	        }catch (Exception $e){
	            throw new Exception('Lançamento registrado, porém ocorreu um erro ao vincular a Invoice: '.$e->getMessage());
	        }
	    }
	    
	    function gravarArquivos($params, $id){
	        //---Arquivos-------------------------------
	        
	        try{
	            
	            $bof	= new FinanceirochinaModel();
	            $bor	= new FinanceirochinareceberModel();
	            $boa	= new FinanceirochinaanexorecModel();
	            
                $ic = 0;
                foreach ($boa->fetchAll('id_fin_contasareceber = '.$id) as $listanex);
                if(count($listanex)>0){
                    $ianex = explode(".",$listanex->nome);
                    $ic = substr($ianex[0],-1);
                }
	        
                for($i=1;$i<=$params['intarchive'];$i++){
	                $ic++;
	                 
	                $arquivo = isset($_FILES['arquivo'.$i]) ? $_FILES['arquivo'.$i] : FALSE;
	                
	                $extensao = substr($_FILES['arquivo'.$i]['name'], strrpos($_FILES['arquivo'.$i]['name'], "."), strlen($_FILES['arquivo'.$i]['name']));
	                
	                $nome = $id."_".time().uniqid(md5()).$extensao;
	        
	                echo $pasta = Zend_Registry::get('pastaPadrao')."/public/sistema/upload/financeirochina/receber/";
	                 
	                if (!(is_dir($pasta))){
	                    if(!(mkdir($pasta, 0777))){
	                        throw new Exception('Lançamento registrado, porém ocorreu ao subir os arquivos: Pasta de upload nao existe, e nao pode ser criada');
	                    }
	                }
	                 
	                if(!(is_writable($pasta))){
	                    throw new Exception('Lançamento registrado, porém ocorreu ao subir os arquivos: pasta sem permissao de escrita');
	                }
	                 
	                if(is_uploaded_file($_FILES['arquivo'.$i]["tmp_name"])){
	                    if (move_uploaded_file($arquivo["tmp_name"], $pasta . $nome)) {
	                        
	                        $arrayarq['nome'] 				 	= $nome;
	                        $arrayarq['id_fin_contasareceber']	= $id;
	                        $boa->insert($arrayarq);
	                    } else {
	                        throw new Exception('Lançamento registrado, porém ocorreu ao subir os arquivos: Nao foi possivel fazer o upload para '.$pasta);
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
            $bo->update(array('id_fin_contasareceber' => null), 'id_fin_contasareceber = "'.$id.'"');
            
            foreach($params['retornoimposto'] as $row => $valor){
                $bo->update(array('id_fin_contasareceber' => $id), 'id = "'.$valor.'"');
            }             
	    }

		/**
		 * Grava o a invoice do retorno de imposto
		 * @param unknown $params
		 * @param unknown $id
		 */
		function gravaFrete($params, $id){
			$bov = new KangvendasModel();
			$bo  = new KanginvoiceModel();

			//-- removo marcaçoes antigas --
			$bo->update(array('id_freterec' => null), 'id_freterec = "'.$id.'"');

			foreach($params['frete'] as $row => $valor){
				$bo->update(array('id_freterec' => $id), 'id = "'.$valor.'"');
			}
		}
	}
                               