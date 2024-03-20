<?php
	class NfeBO{
		function emitirNota($idnfe){
			require_once("Nfephp/libs/ToolsNFePHP.class.php");
			$nfe 	= new ToolsNFePHP();
			$bo 	= new NfeModel();
			
			$data['data']	= date("Y-m-j");
			//$idnfe	=	$bo->insert($data);
			foreach ($bo->fetchAll("id = ".$idnfe) as $nfedados);
			
			$dataSaiEnt = date("Y-m-j");
			
			echo "-- Gera o ID da nfe ------------------------";
			$aChave = NfeBO::gerarIdnfe($idnfe);			
			
			$aChave['dataSaiEnt']	= $nfedados->data;
			$aChave['idnfe']		= $idnfe;
			$aChave['idcliente']	= $nfedados->id_cliente;
			
			echo "<br />";
			echo $aChave['chave'];
						
			echo "<br /><br />-- Gera o XML ------------------------";
			NfeBO::gerarXml($aChave);	 
			
			echo "<br /><br />-- Assinar o XML ------------------------";
			
			//carrega nfe para assinar em uma strig
			$filename = $nfe->entDir.$aChave['chave']."-nfe.xml";

			//mantenha desse jeito mesmo com um unico =
			//a atribuição da variavel e o teste são simultâneos
			if ( $nfefile = file_get_contents($filename) ) {
				//assinador usando somente o PHP da classe classNFe
			    //mantenha desse jeito mesmo com um unico =
			    //a atribuição da variavel e o teste são simultâneos
			    if ( $signn = $nfe->signXML($nfefile, 'infNFe') ) {
			    	//xml retornado gravar
			        $file = $nfe->assDir.$aChave['chave']."-nfe.xml";
			        if ( !file_put_contents($file, $signn) ) {
			        	//$nfe->errStatus = true;
			        	//return false;
			        	echo "<br />Erro na gravação da assinatura";
			        	
			        } else {
			        	unlink($filename);
			        	echo "<br />Nota assinada corretamente";
			        	
					} //fim do teste de gravação
				} //fim do teste da assinatura
			}else{
				echo "<br />Nota nao encontrada para assinar";
			}           
			
			//--- 3 passo: Validar XML -----------------
			echo "<br /><br />-- Validar o XML ------------------------";
			
			$filename = $nfe->assDir.$aChave['chave']."-nfe.xml";
			if ( $nfefile = file_get_contents($filename) ) {
				//validar
			    $filexsd = "/aplic/ztlbrasil.com.br/public/nfe/schemes/PL_006g/nfe_v2.00.xsd";
			    
			    $aRet = $nfe->validXML($nfefile,$filexsd);
			                    
			    if ( $aRet['status'] ) {
			    	// validado => transferir para pasta validados
			        $file = $nfe->valDir.$aChave['chave']."-nfe.xml";
			        if ( !file_put_contents($file, $nfefile) ) {
			        	$nfe->errStatus = false;
			                            
					} else {
			        	unlink($filename);
			            echo "<br />Nota validada corretamente";
			        }
			    } else {
			    	//NFe com erros transferir de pasta rejeitadas
			        $file = $nfe->rejDir.$aChave['chave']."-nfe.xml";
			                       	
			        echo $nota.' ... '.$aRet['error']."\n";
			        echo "<br />";
			        if (!file_put_contents($file, $nfefile) ) {
			        	$nfe->errStatus = true;
			            echo "<br />Nota rejeitada<br />Erro na gravação na pasta rejeitadas<br />";
			        } else {
						unlink($filename);
			
			            echo "<br />Nota rejeitada<br />Gravada na pasta rejeitadas<br/>";
			        } //fim teste de gravação
			        return false;
				} //fim validação
			}else{
				echo "<br />Nota não encontrada na pasta assindadas";
			}
			
			
			//---4 passo: Enviar XML a sefaz -----------------
			
			echo "<br /><br />-- Enviar o XML a sefaz ------------------------";
			
			//estabelece condição inicial do retorno
			$recibo = '';
			       
			$filename = $nfe->valDir.$aChave['chave']."-nfe.xml";
			$nfefile = file_get_contents($filename);
			$aNFE[] = $nfefile;
			
			//obter o numero do ultimo lote enviado
			
			//enviar as notas
			if ($ret = $nfe->sendLot($aNFE,2,$nfe->modSOAP)){
				//incrementa o numero do lote no controle
			    
			    //['bStat'=>false,'cStat'=>'','xMotivo'=>'','dhRecbto'=>'','nRec'=>'']                    
			    //verificar o status do envio
			
					if ($ret['bStat']){
			    	//pegar o numero do recibo da SEFAZ
			        $recibo = $ret['nRec'];
			        //mover as notas do lote para o diretorio de enviadas
			        //para cada em $aNames[] mover para $nfe->envDir
			
			        $dir1 = $nfe->valDir.$aChave['chave']."-nfe.xml"; 
			        $dir2 = $nfe->envDir.$aChave['chave']."-nfe.xml";
			        		
			        if( !rename($dir1,$dir2) ){
			        	echo "Falha na movimentacao da NFe das validadas para enviadas !!\n";
			        }
			        
			    } else {
			    	echo "Erro no resultado da sefaz";
			    }
			} else {
				echo "Erro no envio do lote de NFe!!\n";    
			}    
			
			echo "<br />Recibo: ".$recibo;
						
			//---5 passo: Veriricar se autorizada, rejeitada ou reprovada -----------------
			echo "<br /><br />-- Verificar o resultado do XML na sefaz ------------------------";
			//condição inicial da variável de retorno
			$aRetorno = array(0=>array('cStat'=>'','xMotivo'=>'','nfepath'=>''));
			$n = 0;
			
			echo "<br />";
			echo $idNFe = $aChave['chave'];
			$nfeFile = $nfe->envDir.$aChave['chave']."-nfe.xml";
			
			//primeiro verificar se o protocolo existe na pasta temporarias
			
			$protFile = $nfe->temDir.$idNFe.'-prot.xml';			
			
			if (file_exists($protFile)){
				$docxml = file_get_contents($protFile);
			    $dom = new DOMDocument(); //cria objeto DOM
			    $dom->formatOutput = false;
			    $dom->preserveWhiteSpace = false;
			    $dom->loadXML($docxml);
			    //pagar o cStat e xMotivo do protocolo
			    $aRet['cStat'] = $dom->getElementsByTagName('cStat')->item(0)->nodeValue;
			    $aRet['xMotivo'] = $dom->getElementsByTagName('xMotivo')->item(0)->nodeValue; 	
			    $aRet['bStat'] = true;
			} else {
				//caso não exista então buscar pela chave da NFe
					
				$aRet = $nfe->getProtocol('',$idNFe,2,$nfe->modSOAP);
			}    
			
			if ( $aRet['cStat'] == 100) {
				//NFe aprovada
			    $pasta = $nfe->aprDir;
			}//endif
			if ( $aRet['cStat'] == 110) {
				//NFe denegada
			    $pasta = $nfe->denDir;
			}//endif
			if ( $aRet['cStat'] > 199 ) {
				//NFe reprovada
			    $pasta = $nfe->repDir;
				//mover a NFe para a pasta repovadas
			    rename($nfeFile, $pasta.$idNFe.'-nfe.xml');
			}//endif
			
			if ( $aRet['bStat'] ) {
				//montar a NFe com o protocolo
				if ( is_file($protFile) && is_file($nfeFile) ) {
			    	//se aprovada ou denegada adicionar o protocolo e mover o arquivo
			        if ($aRet['cStat'] == 100 || $aRet['cStat'] == 110 ) {
			        	$procnfe = $nfe->addProt($nfeFile,$protFile);
			            //salvar a NFe com o protocolo na pasta
			            if ( file_put_contents($pasta.$idNFe.'-nfe.xml', $procnfe) ) {
			                //se o arquivo foi gravado na pasta destino com sucesso
			                //remover os arquivos das outras pastas
			            	unlink($nfeFile);
			            } //endif
			    	}//endif cStat   
				} //endif is_file
			} //endif bStat
			$aRetorno[$n]['cStat'] = $aRet['cStat'];
			$aRetorno[$n]['xMotivo'] = $aRet['xMotivo'];
			$aRetorno[$n]['nfepath'] = $pasta.$idNFe.'-nfe.xml';
			
			echo "<br />";
			echo $aRet['cStat'];
			echo "<br />";
			echo $aRet['xMotivo'];
			echo "<br />";
			echo $pasta.$idNFe.'-nfe.xml';
						
			
			
			
		}
		
		//--- Gera chave da nfe ---------------------------------
		function gerarIdnfe($nf){
			$bo 	= new NfeModel();
			
			//$aChave = array('chave'=>'','cn'=>'','dv'=>'','dataSaiEnt'=>'','idnfe'=>'');
			$aChave = array('chave'=>'','cn'=>'','dv'=>'');
			
			$cUF = '53';//Código da UF [02]
			$aamm = date("ym");//AAMM da emissão [4]
			$cnpj = '07555737000110';//CNPJ do Emitente [14]
			$mod ='55';//Modelo [02]
			$serie ='001';//Série [03]
			$NumeroDaNf = $nf;
			$tpEmis='1';//forma de emissão da NF-e [01] 1 – Normal – emissão normal; 2 – Contingência FS; 3 – Contingência SCAN;
									
			//variaveis que monta a chave
			$cn='';
			$dv='';
			
			//$num = str_pad($NumeroDaNf, 9, '0',STR_PAD_LEFT);
			$num = str_pad($NumeroDaNf, 9, '0',STR_PAD_LEFT);
			$cn = NfeBO::geraCN(8);
			$chave = $cUF . $aamm . $cnpj . $mod . $serie . $num . $tpEmis . $cn;
			$chave .= NfeBO::calculaDV($chave);
						
			$aChave['chave']	= $chave;
			$aChave['cn']		= $cn;
			$aChave['dv']		= NfeBO::calculaDV($chave);
			$aChave['etapa']	= 4;
			
			
			try {
				$bo->update($aChave, "id = ".$nf);
				echo "sucessochave";
				return $aChave;				
			}catch (Zend_Exception $e){
			    NfeBO::gravarErronfe($e->getMessage(), $nf, "NfeBO::gerarIdnfe(".$nf.")");
				return false;
			}
			
		}
		
		function geraCN($length=8){
		    $numero = '';
		    for ($x=0;$x<$length;$x++){
		        $numero .= rand(0,9);
		    }
		    return $numero;
		
		}
		
		function calculaDV($chave43) {
		    $soma_ponderada = 0;
		    $multiplicadores = array(2,3,4,5,6,7,8,9);
		    $i = 42;
		    while ($i >= 0) {
		        for ($m=0; $m<count($multiplicadores) && $i>=0; $m++) {
		            $soma_ponderada+= $chave43[$i] * $multiplicadores[$m];
		            $i--;
		        }
		    }
		    $resto = $soma_ponderada % 11;
		    if ($resto == '0' || $resto == '1') {
		        return 0;
		    } else {
		        return (11 - $resto);
			}
		}
				
		function gerarXml($idnfe){
		    try{
		    
		    require_once("Nfephp/libs/ToolsNFePHP.class.php");
		    $toolsnfe	= new ToolsNFePHP();
			$bo 		= new NfeModel();		
			$boprod 	= new NfeprodModel();			
			
			foreach ($bo->fetchAll("id = ".$idnfe) as $nfedados);		
			
			//versao do encoding xml
			$dom = new DOMDocument("1.0", "UTF-8");
			//retirar os espacos em branco
			$dom->preserveWhiteSpace = false;
			//gerar o codigo
			$dom->formatOutput = true;
			
			//criando o nó principal (nfeProc)			
			/* $nfeProc = $dom->createElement("nfeProc");
			$nfeProc->setAttribute("versao", "2.00");
			$nfeProc->setAttribute("xmlns", "http://www.portalfiscal.inf.br/nfe"); */
						
			//--nó filho nfeProc/NFe-------------------------
			$NFe = $dom->createElement("NFe");
			$NFe->setAttribute("xmlns", "http://www.portalfiscal.inf.br/nfe");
			
			//--nó filho nfeProc/NFe/infNFe-------------------------
			$infNFe = $dom->createElement("infNFe");
			$infNFe->setAttribute("versao", "2.00");
			$infNFe->setAttribute("Id", "NFe".$nfedados->chave);

			//--nó filho nfeProc/NFe/infNFe/ide-------------------------
			$ide = $dom->createElement("ide");
			
			$ide->appendChild($dom->createElement("cUF", 53));
			$ide->appendChild($dom->createElement("cNF", $nfedados->cn));
			$ide->appendChild($dom->createElement("natOp", DiversosBO::pogremoveAcentos(trim(substr($nfedados->naturezaop,0,58)))));
			$ide->appendChild($dom->createElement("indPag", 1));
			$ide->appendChild($dom->createElement("mod", 55));
			$ide->appendChild($dom->createElement("serie", 1));
			$ide->appendChild($dom->createElement("nNF", $idnfe));
			$ide->appendChild($dom->createElement("dEmi", $nfedados->data));
			
			if(!empty($nfedados->data_saida)):
				$ide->appendChild($dom->createElement("dSaiEnt", substr($nfedados->data_saida,0,10)));
				$ide->appendChild($dom->createElement("hSaiEnt", substr($nfedados->data_saida,11,8)));
			endif;
			
			$ide->appendChild($dom->createElement("tpNF", $nfedados->tipo));
			$ide->appendChild($dom->createElement("cMunFG", 5300108));
			
			if($nfedados->finalidade == 2):
				$NFref = $dom->createElement("NFref");
				$NFref->appendChild($dom->createElement("refNFe", $nfedados->chavecomplementa));
				$ide->appendChild($NFref);
			endif;
			
			$ide->appendChild($dom->createElement("tpImp", 1));
			$ide->appendChild($dom->createElement("tpEmis", 1));
			$ide->appendChild($dom->createElement("cDV", $nfedados->dv));
			$ide->appendChild($dom->createElement("tpAmb", $toolsnfe->tpAmb));
			
			if($nfedados->finalidade == 2):
				$ide->appendChild($dom->createElement("finNFe", 2));
			else:
				$ide->appendChild($dom->createElement("finNFe", 1));
			endif;
			
			$ide->appendChild($dom->createElement("procEmi", 0));
			$ide->appendChild($dom->createElement("verProc", "SisZTL 2.0 Alpha"));
			
			$infNFe->appendChild($ide);
			
			//--nó filho nfeProc/NFe/infNFe/emit-------------------------
			$emit = $dom->createElement("emit");
			
			//--nó filho nfeProc/NFe/infNFe/emit/enderEmit-------------------------
			$enderEmit = $dom->createElement("enderEmit");
			$enderEmit->appendChild($dom->createElement("xLgr", "QI 08 LOTE 45/48 TAG. NORTE"));
			$enderEmit->appendChild($dom->createElement("nro", "S/N"));
			$enderEmit->appendChild($dom->createElement("xBairro", "TAGUATINGA"));
			$enderEmit->appendChild($dom->createElement("cMun", "5300108"));
			$enderEmit->appendChild($dom->createElement("xMun", "TAGUATINGA"));
			$enderEmit->appendChild($dom->createElement("UF", "DF"));
			$enderEmit->appendChild($dom->createElement("CEP", "72135080"));
			$enderEmit->appendChild($dom->createElement("cPais", "1058"));
			$enderEmit->appendChild($dom->createElement("xPais", "Brasil"));
			$enderEmit->appendChild($dom->createElement("fone", "6134337777"));
			
			$emit->appendChild($dom->createElement("CNPJ", "07555737000110"));
			$emit->appendChild($dom->createElement("xNome", "ZTL DO BRASIL IMPORTACAO EXPORTACAO E COM. LTDA"));
			$emit->appendChild($dom->createElement("xFant", "ZTL DO BRASIL"));
			$emit->appendChild($enderEmit);			
			$emit->appendChild($dom->createElement("IE", "0747014000173"));
			$emit->appendChild($dom->createElement("CRT", 3));
			
			$infNFe->appendChild($emit);
			
			//--nó filho nfeProc/NFe/infNFe/dest-------------------------
			$dest = $dom->createElement("dest");
			
			//--nó filho nfeProc/NFe/infNFe/dest/enderDest-------------------------
			$enderDest = $dom->createElement("enderDest");
			$enderDest->appendChild($dom->createElement("xLgr", trim($nfedados->endereco)));
			$enderDest->appendChild($dom->createElement("nro", trim($nfedados->numero)));
			$enderDest->appendChild($dom->createElement("xBairro", trim($nfedados->bairro)));
			$enderDest->appendChild($dom->createElement("cMun", $nfedados->codcidade));
			$enderDest->appendChild($dom->createElement("xMun", trim($nfedados->cidade)));
			$enderDest->appendChild($dom->createElement("UF", $nfedados->uf));
			
			if(!empty($nfedados->cep)){
				$enderDest->appendChild($dom->createElement("CEP", trim(str_replace("-","", str_replace(".","", $nfedados->cep)))));
			}
			
			if(empty($nfedados->pais) || empty($nfedados->codpais)):
			    $pais 		= "Brasil";
			    $codpais 	= 1058;
			else:
				$pais 		= $nfedados->pais;
				$codpais 	= $nfedados->codpais;
			endif;
			
			$enderDest->appendChild($dom->createElement("cPais", $codpais));
			$enderDest->appendChild($dom->createElement("xPais", trim($pais)));
			
			if(!empty($nfedados->fone)){
				$enderDest->appendChild($dom->createElement("fone", trim($nfedados->fone)));
			}
			
			if($toolsnfe->tpAmb==1):			
				if((empty($nfedados->pais) || empty($nfedados->codpais)) || ($nfedados->codpais == 1058)):
					if(strlen($nfedados->cnpj) == 14):
						$dest->appendChild($dom->createElement("CNPJ", $nfedados->cnpj)); //$nfedados->cnpj
					elseif(strlen($nfedados->cnpj) == 11):
						$dest->appendChild($dom->createElement("CPF", $nfedados->cnpj));
					endif;
				else:
					$dest->appendChild($dom->createElement("CNPJ", ""));
				endif;
				
				$dest->appendChild($dom->createElement("xNome", trim($nfedados->empresa)));				
				$dest->appendChild($enderDest);			
				
				if((empty($nfedados->pais) || empty($nfedados->codpais)) || ($nfedados->codpais == 1058)):
					if(strlen($nfedados->cnpj) == 11):
						$dest->appendChild($dom->createElement("IE", "")); //$nfedados->inscricao
					else:
						$destinatario = str_replace("-", "", str_replace("&", "", $nfedados->inscricao));
						$dest->appendChild($dom->createElement("IE", $destinatario)); //$nfedados->inscricao
					endif;
				else:
					$dest->appendChild($dom->createElement("IE", "")); //$nfedados->inscricao
				endif;
			else:
				if((empty($nfedados->pais) || empty($nfedados->codpais)) || ($nfedados->codpais == 1058)):
					$dest->appendChild($dom->createElement("CNPJ", "99999999000191"));
				else:
					$dest->appendChild($dom->createElement("CNPJ", ""));
				endif;
				
				$dest->appendChild($dom->createElement("xNome", "NF-E EMITIDA EM AMBIENTE DE HOMOLOGACAO - SEM VALOR FISCAL"));				
				$dest->appendChild($enderDest);				
				$dest->appendChild($dom->createElement("IE", "")); //$nfedados->inscricao				
			endif;
			
			//$dest->appendChild($dom->createElement("IE", "")); //$nfedados->inscricao
						
			$infNFe->appendChild($dest);
			
			//--Produtos-------------------------
			$contitem = 0;
			foreach ($boprod->fetchAll("id_nfe = ".$idnfe) as $nfeprodutos):
				
				//--nó filho nfeProc/NFe/infNFe/det-------------------------
				$contitem++;
				$det = $dom->createElement("det");
				$det->setAttribute("nItem", $contitem);
				
				//--nó filho nfeProc/NFe/infNFe/det/prod-------------------------
				$prod = $dom->createElement("prod");
				$prod->appendChild($dom->createElement("cProd", trim($nfeprodutos->codigo)));
				$prod->appendChild($dom->createElement("cEAN", trim($nfeprodutos->codean)));
				$prod->appendChild($dom->createElement("xProd", trim($nfeprodutos->descricao)));
				$prod->appendChild($dom->createElement("NCM", trim($nfeprodutos->ncm)));
				
				if(!empty($nfeprodutos->ncmex)): 
					$prod->appendChild($dom->createElement("EXTIPI", trim(substr($nfeprodutos->ncmex,2))));
				endif;
				
				$prod->appendChild($dom->createElement("CFOP", $nfedados->cfop));
				$prod->appendChild($dom->createElement("uCom", $nfeprodutos->unidade));
				$prod->appendChild($dom->createElement("qCom", number_format($nfeprodutos->qt,4,".","")));
				$prod->appendChild($dom->createElement("vUnCom", number_format($nfeprodutos->preco,4,".","")));
				$prod->appendChild($dom->createElement("vProd", number_format($nfeprodutos->preco*$nfeprodutos->qt,2,".","")));
				$prod->appendChild($dom->createElement("cEANTrib", trim($nfeprodutos->codean)));
				$prod->appendChild($dom->createElement("uTrib", $nfeprodutos->unidade));
				$prod->appendChild($dom->createElement("qTrib", number_format($nfeprodutos->qt,4,".","")));
				$prod->appendChild($dom->createElement("vUnTrib", number_format($nfeprodutos->preco,4,".","")));
								
				if(!empty($nfeprodutos->frete)):
					$prod->appendChild($dom->createElement("vFrete", number_format($nfeprodutos->frete,2,".","")));
				endif;
				
				if(!empty($nfeprodutos->desconto)):
					$prod->appendChild($dom->createElement("vDesc", number_format($nfeprodutos->desconto,2,".","")));
				endif;
				
				if(!empty($nfeprodutos->outrasdesp)):
					$prod->appendChild($dom->createElement("vOutro", number_format($nfeprodutos->outrasdesp,2,".","")));
				endif;

				$prod->appendChild($dom->createElement("indTot", 1));
				
				//---- Dados da importacao --------------------------------------------------------------
				if(!empty($nfedados->di)):
					$di = $dom->createElement("DI");
					$di->appendChild($dom->createElement("nDI", trim($nfedados->di)));
					$di->appendChild($dom->createElement("dDI", trim($nfedados->datadi)));
					$di->appendChild($dom->createElement("xLocDesemb", trim($nfedados->localdesembarque)));
					$di->appendChild($dom->createElement("UFDesemb", trim($nfedados->ufdesembarque)));
					$di->appendChild($dom->createElement("dDesemb", trim($nfedados->datadesembarque)));
					$di->appendChild($dom->createElement("cExportador", trim($nfedados->codexportador)));
				
					$adi = $dom->createElement("adi");
					$adi->appendChild($dom->createElement("nAdicao", trim($nfeprodutos->dinumadicao)));
					$adi->appendChild($dom->createElement("nSeqAdic", trim($nfeprodutos->dinumseq)));
					$adi->appendChild($dom->createElement("cFabricante", trim($nfeprodutos->dicodfab)));
					
					$di->appendChild($adi);
					$prod->appendChild($di);
				endif;
				
				$det->appendChild($prod);
							
				//--nó filho nfeProc/NFe/infNFe/det/imposto-------------------------
				$imposto = $dom->createElement("imposto");
				
				if(strlen($nfedados->cnpj) == 11){
					$imposto->appendChild($dom->createElement("vTotTrib", number_format($nfeprodutos->valortotaltrib,2,".",""))); 
				}
				
				//--nó filho nfeProc/NFe/infNFe/det/imposto/ICMS-------------------------
				$ICMS = $dom->createElement("ICMS");
							
				//--nó filho nfeProc/NFe/infNFe/det/imposto/ICMS/ICMS00-------------------------
				if(($nfeprodutos->csticms==40)||($nfeprodutos->csticms==41)||($nfeprodutos->csticms==50)):
					$ICMS00 = $dom->createElement("ICMS40");
				else:
					$ICMS00 = $dom->createElement("ICMS".str_pad($nfeprodutos->csticms,2,'0',STR_PAD_LEFT));
				endif;
				$ICMS00->appendChild($dom->createElement("orig", $nfeprodutos->origem));
				$ICMS00->appendChild($dom->createElement("CST", str_pad($nfeprodutos->csticms,2,'0',STR_PAD_LEFT)));				
				
				if($nfeprodutos->csticms==00):
					$ICMS00->appendChild($dom->createElement("modBC", "3"));
					$ICMS00->appendChild($dom->createElement("vBC", number_format($nfeprodutos->baseicms,2,".","")));
					$ICMS00->appendChild($dom->createElement("pICMS", number_format($nfeprodutos->alicms,2,".","")));
					$ICMS00->appendChild($dom->createElement("vICMS", number_format($nfeprodutos->vlicms,2,".","")));					
				elseif($nfeprodutos->csticms==10):
					$ICMS00->appendChild($dom->createElement("modBC", "3"));
					$ICMS00->appendChild($dom->createElement("vBC", number_format($nfeprodutos->baseicms,2,".","")));
					$ICMS00->appendChild($dom->createElement("pICMS", number_format($nfeprodutos->alicms,2,".","")));
					$ICMS00->appendChild($dom->createElement("vICMS", number_format($nfeprodutos->vlicms,2,".","")));
					$ICMS00->appendChild($dom->createElement("modBCST", "4"));
					if($nfeprodutos->mvast!=0):
						$ICMS00->appendChild($dom->createElement("pMVAST", number_format($nfeprodutos->mvast,2,".","")));
					endif;
					//$ICMS00->appendChild($dom->createElement("pRedBCST", "0"));
					$ICMS00->appendChild($dom->createElement("vBCST", number_format($nfeprodutos->basest,2,".","")));
					$ICMS00->appendChild($dom->createElement("pICMSST", number_format($nfeprodutos->icmsst,2,".","")));
					$ICMS00->appendChild($dom->createElement("vICMSST", number_format($nfeprodutos->vlicmsst,2,".","")));
				elseif($nfeprodutos->csticms==20):
					$ICMS00->appendChild($dom->createElement("modBC", "3"));
					$ICMS00->appendChild($dom->createElement("pRedBC", number_format(str_pad($nfedados->descontoperc,2,'0',STR_PAD_LEFT),2,".","")));
					$ICMS00->appendChild($dom->createElement("vBC", number_format($nfeprodutos->baseicms,2,".","")));
					$ICMS00->appendChild($dom->createElement("pICMS", number_format($nfeprodutos->alicms,2,".","")));
					$ICMS00->appendChild($dom->createElement("vICMS", number_format($nfeprodutos->vlicms,2,".","")));				
				elseif($nfeprodutos->csticms==30):
					$ICMS00->appendChild($dom->createElement("modBCST", "4"));
					$ICMS00->appendChild($dom->createElement("pMVAST", number_format($nfeprodutos->mvast,2,".","")));
					if($nfedados->descontoperc > 0) $ICMS00->appendChild($dom->createElement("pRedBCST", number_format(str_pad($nfedados->descontoperc,2,'0',STR_PAD_LEFT),2,".","")));
					$ICMS00->appendChild($dom->createElement("vBCST", number_format($nfeprodutos->basest,2,".","")));
					$ICMS00->appendChild($dom->createElement("pICMSST", number_format($nfeprodutos->icmsst,2,".","")));
					$ICMS00->appendChild($dom->createElement("vICMSST", number_format($nfeprodutos->vlicmsst,2,".","")));				
				elseif($nfeprodutos->csticms==51):
					$ICMS00->appendChild($dom->createElement("modBC", "3"));
					$ICMS00->appendChild($dom->createElement("pRedBC", number_format(str_pad($nfedados->descontoperc,2,'0',STR_PAD_LEFT),2,".","")));
					$ICMS00->appendChild($dom->createElement("vBC", number_format($nfeprodutos->baseicms,2,".","")));
					$ICMS00->appendChild($dom->createElement("pICMS", $nfeprodutos->alicms));
					$ICMS00->appendChild($dom->createElement("vICMS", number_format($nfeprodutos->vlicms,2,".","")));
				elseif($nfeprodutos->csticms==60):
					$ICMS00->appendChild($dom->createElement("vBCST", number_format($nfeprodutos->basest,2,".","")));
					$ICMS00->appendChild($dom->createElement("vICMSST", number_format($nfeprodutos->vlicmsst,2,".","")));
				elseif($nfeprodutos->csticms==70):
					$ICMS00->appendChild($dom->createElement("modBC", "3"));
					$ICMS00->appendChild($dom->createElement("pRedBC", number_format(str_pad($nfedados->descontoperc,2,'0',STR_PAD_LEFT),2,".","")));
					$ICMS00->appendChild($dom->createElement("vBC", number_format($nfeprodutos->baseicms,2,".","")));
					$ICMS00->appendChild($dom->createElement("pICMS", number_format($nfeprodutos->alicms,2,".","")));
					$ICMS00->appendChild($dom->createElement("vICMS", number_format($nfeprodutos->vlicms,2,".","")));
					$ICMS00->appendChild($dom->createElement("modBCST", "4"));
					$ICMS00->appendChild($dom->createElement("pMVAST", number_format($nfeprodutos->mvast,2,".","")));
					$ICMS00->appendChild($dom->createElement("pRedBCST", number_format(str_pad($nfedados->descontoperc,2,'0',STR_PAD_LEFT),2,".","")));
					$ICMS00->appendChild($dom->createElement("vBCST", number_format($nfeprodutos->basest,2,".","")));
					$ICMS00->appendChild($dom->createElement("pICMSST", number_format($nfeprodutos->icmsst,2,".","")));
					$ICMS00->appendChild($dom->createElement("vICMSST", number_format($nfeprodutos->vlicmsst,2,".","")));
				elseif($nfeprodutos->csticms==90):
					$ICMS00->appendChild($dom->createElement("modBC", "3"));
					$ICMS00->appendChild($dom->createElement("pRedBC", number_format(str_pad($nfedados->descontoperc,2,'0',STR_PAD_LEFT),2,".","")));
					$ICMS00->appendChild($dom->createElement("vBC", number_format($nfeprodutos->baseicms,2,".","")));
					$ICMS00->appendChild($dom->createElement("pICMS", number_format($nfeprodutos->alicms,2,".","")));
					$ICMS00->appendChild($dom->createElement("vICMS", number_format($nfeprodutos->vlicms,2,".","")));
					$ICMS00->appendChild($dom->createElement("modBCST", "4"));
					$ICMS00->appendChild($dom->createElement("pMVAST", number_format($nfeprodutos->mvast,2,".","")));
					$ICMS00->appendChild($dom->createElement("pRedBCST", number_format(str_pad($nfedados->descontoperc,2,'0',STR_PAD_LEFT),2,".","")));
					$ICMS00->appendChild($dom->createElement("vBCST", number_format($nfeprodutos->basest,2,".","")));
					$ICMS00->appendChild($dom->createElement("pICMSST", number_format($nfeprodutos->icmsst,2,".","")));
					$ICMS00->appendChild($dom->createElement("vICMSST", number_format($nfeprodutos->vlicmsst,2,".","")));	
				endif;
				
				$ICMS->appendChild($ICMS00);
				$imposto->appendChild($ICMS);

				if($nfeprodutos->csttpipi=="Trib"):
					//--nó filho nfeProc/NFe/infNFe/det/imposto/IPI-------------------------
					$IPI = $dom->createElement("IPI");
					$IPI->appendChild($dom->createElement("cEnq", "999"));			
					
					//--nó filho nfeProc/NFe/infNFe/det/imposto/IPI/IPITrib-------------------------
					$IPITrib = $dom->createElement("IPI".$nfeprodutos->csttpipi);
					$IPITrib->appendChild($dom->createElement("CST", $nfeprodutos->cstipi));
				
					$IPITrib->appendChild($dom->createElement("vBC", number_format($nfeprodutos->preco*$nfeprodutos->qt,2,".","")));
					$IPITrib->appendChild($dom->createElement("pIPI", number_format($nfeprodutos->alipi,2,".","")));
					$IPITrib->appendChild($dom->createElement("vIPI", number_format($nfeprodutos->vlipi,2,".","")));
					
					$IPI->appendChild($IPITrib);
					$imposto->appendChild($IPI);
				elseif($nfeprodutos->csttpipi=="NT"): 
					$IPI = $dom->createElement("IPI");
					$IPI->appendChild($dom->createElement("cEnq", "999"));
					
					$IPITrib = $dom->createElement("IPI".$nfeprodutos->csttpipi);
					$IPITrib->appendChild($dom->createElement("CST", $nfeprodutos->cstipi));
					
					$IPI->appendChild($IPITrib);
					$imposto->appendChild($IPI);
				endif;
				
				//--nó filho nfeProc/NFe/infNFe/det/imposto/II-------------------------
				if(!empty($nfedados->di)):
					$II = $dom->createElement("II");
					$II->appendChild($dom->createElement("vBC", number_format($nfeprodutos->baseii,2,".","")));
					$II->appendChild($dom->createElement("vDespAdu", number_format($nfeprodutos->vladuaneiro,2,".","")));
					$II->appendChild($dom->createElement("vII", number_format($nfeprodutos->vlii,2,".","")));
					$II->appendChild($dom->createElement("vIOF", 0));
					$imposto->appendChild($II);
				endif;
								
				//--nó filho nfeProc/NFe/infNFe/det/imposto/PIS-------------------------
				$PIS = $dom->createElement("PIS");
							
				//--nó filho nfeProc/NFe/infNFe/det/imposto/PIS/PISAliq-------------------------
				$PISAliq = $dom->createElement("PIS".$nfeprodutos->csttppis);
				$PISAliq->appendChild($dom->createElement("CST", $nfeprodutos->cstpis));
				if($nfeprodutos->csttppis=="Aliq"):
					$PISAliq->appendChild($dom->createElement("vBC", number_format($nfeprodutos->baseicms,2,".","")));
					$PISAliq->appendChild($dom->createElement("pPIS", number_format($nfeprodutos->alpis,2,".","")));
					$PISAliq->appendChild($dom->createElement("vPIS", number_format($nfeprodutos->vlpis,2,".","")));
				endif;
				
				/*-- Verificar situacoes do PIS e COFINS para PISQtde e PISOutr ------------------------------------*/
				
				$PIS->appendChild($PISAliq);
				$imposto->appendChild($PIS);
				
				//--nó filho nfeProc/NFe/infNFe/det/imposto/COFINS-------------------------
				$COFINS = $dom->createElement("COFINS");
							
				//--nó filho nfeProc/NFe/infNFe/det/imposto/COFINS/COFINSAliq-------------------------
				$COFINSAliq = $dom->createElement("COFINS".$nfeprodutos->csttpcofins);
				$COFINSAliq->appendChild($dom->createElement("CST", $nfeprodutos->cstcofins));
				if($nfeprodutos->csttpcofins=="Aliq"):
					$COFINSAliq->appendChild($dom->createElement("vBC", number_format($nfeprodutos->baseicms,2,".","")));
					$COFINSAliq->appendChild($dom->createElement("pCOFINS", number_format($nfeprodutos->alcofins,2,".","")));
					$COFINSAliq->appendChild($dom->createElement("vCOFINS", number_format($nfeprodutos->vlcofins,2,".","")));
				endif;
				
				$COFINS->appendChild($COFINSAliq);
				$imposto->appendChild($COFINS);
							
				$det->appendChild($imposto);
				
				$infNFe->appendChild($det);
			
			endforeach;
			
			//-- Total ---------------
			//--nó filho nfeProc/NFe/infNFe/total-------------------------
			$total = $dom->createElement("total");
						
			//--nó filho nfeProc/NFe/infNFe/total/ICMSTot-------------------------
			$ICMSTot = $dom->createElement("ICMSTot");
			$ICMSTot->appendChild($dom->createElement("vBC", number_format($nfedados->baseicms,2,".","")));
			$ICMSTot->appendChild($dom->createElement("vICMS", number_format($nfedados->vlicms,2,".","")));
			$ICMSTot->appendChild($dom->createElement("vBCST", number_format($nfedados->basest,2,".","")));
			$ICMSTot->appendChild($dom->createElement("vST", number_format($nfedados->vlst,2,".","")));
			$ICMSTot->appendChild($dom->createElement("vProd", number_format($nfedados->totalprodutos,2,".","")));
			$ICMSTot->appendChild($dom->createElement("vFrete", number_format($nfedados->frete,2,".","")));
			$ICMSTot->appendChild($dom->createElement("vSeg", number_format($nfedados->seguro,2,".","")));
			$ICMSTot->appendChild($dom->createElement("vDesc", number_format($nfedados->desconto,2,".","")));
			$ICMSTot->appendChild($dom->createElement("vII", number_format($nfedados->totalii,2,".","")));
			$ICMSTot->appendChild($dom->createElement("vIPI", number_format($nfedados->totalipi,2,".","")));
			$ICMSTot->appendChild($dom->createElement("vPIS", number_format($nfedados->totalpis,2,".","")));
			$ICMSTot->appendChild($dom->createElement("vCOFINS", number_format($nfedados->totalcofins,2,".","")));
			$ICMSTot->appendChild($dom->createElement("vOutro", number_format($nfedados->outrasdesp,2,".","")));
			$ICMSTot->appendChild($dom->createElement("vNF", number_format($nfedados->totalnota,2,".","")));
			
			if(strlen($nfedados->cnpj) == 11){
				$ICMSTot->appendChild($dom->createElement("vTotTrib", number_format($nfedados->valortotaltrib,2,".","")));
			}
						
			$total->appendChild($ICMSTot);
			$infNFe->appendChild($total);
			
			//-- Transporte ---------------
			//--nó filho nfeProc/NFe/infNFe/transp-------------------------
			$transp = $dom->createElement("transp");
			$transp->appendChild($dom->createElement("modFrete", $nfedados->tipofrete));
			
			if($nfedados->tipofrete != 9):
				//--nó filho nfeProc/NFe/infNFe/transp/transporta--------------
				$transporta = $dom->createElement("transporta");
				
				if(strlen($nfedados->transcnpj) == 14):
					$transporta->appendChild($dom->createElement("CNPJ", trim($nfedados->transcnpj)));
				elseif(strlen($nfedados->transcnpj) == 11):
					$transporta->appendChild($dom->createElement("CPF", trim($nfedados->transcnpj)));
				endif;
				
				$transporta->appendChild($dom->createElement("xNome", trim($nfedados->transportadora)));
				
				if(strlen($nfedados->transcnpj) == 11):
					$transporta->appendChild($dom->createElement("IE", ""));
				else:
					$transporta->appendChild($dom->createElement("IE", trim($nfedados->transie)));
				endif;
				
				$transporta->appendChild($dom->createElement("xEnder", trim($nfedados->transendereco)));
				$transporta->appendChild($dom->createElement("xMun", trim($nfedados->transcidade)));
				$transporta->appendChild($dom->createElement("UF", $nfedados->transuf));
							
				$transp->appendChild($transporta);
				
				//--nó filho nfeProc/NFe/infNFe/transp/veicTransp--------------
				if(!empty($nfedados->transplaca)):
					$veicTransp = $dom->createElement("veicTransp");
					$veicTransp->appendChild($dom->createElement("placa", trim($nfedados->transplaca)));
					$veicTransp->appendChild($dom->createElement("UF", $nfedados->transufplaca));
					$transp->appendChild($veicTransp);
				endif;
				
				//--nó filho nfeProc/NFe/infNFe/transp/vol--------------
				$vol = $dom->createElement("vol");
				$vol->appendChild($dom->createElement("qVol", $nfedados->quantidade));
				$vol->appendChild($dom->createElement("esp", $nfedados->especie));
				$vol->appendChild($dom->createElement("marca", $nfedados->marca));
				$vol->appendChild($dom->createElement("pesoL", number_format($nfedados->pesoliquido,3,".","")));
				$vol->appendChild($dom->createElement("pesoB", number_format($nfedados->pesobruto,3,".","")));
							
				$transp->appendChild($vol);
			endif;
									
			$infNFe->appendChild($transp);

			$objFinanceiro = FinanceiroBO::buscarFinanceironfe($idnfe);
			
			if(!empty($objFinanceiro)):
				
			    //-- Cobrancas ---------------
			    //--nó filho nfeProc/NFe/infNFe/cobr-------------------------
			    $cobr = $dom->createElement("cobr");
			
				//--nó filho nfeProc/NFe/infNFe/cobr/fat--------------
				$fat = $dom->createElement("fat");
				$fat->appendChild($dom->createElement("nFat", substr("000000".$idnfe, -6, 6)));
				$fat->appendChild($dom->createElement("vOrig", number_format($nfedados->totalnota,2,".","")));
				$fat->appendChild($dom->createElement("vLiq", number_format($nfedados->totalnota,2,".","")));
							
				$cobr->appendChild($fat);
				
				$contparc=0;
				foreach (FinanceiroBO::buscarFinanceironfe($idnfe) as $parcfin):
					$contparc++;
					//--nó filho nfeProc/NFe/infNFe/cobr/dup--------------
					$dup = $dom->createElement("dup");
					$dup->appendChild($dom->createElement("nDup", "NE".substr("000000".$idnfe, -6, 6)."/".$contparc));
					$dup->appendChild($dom->createElement("dVenc", $parcfin->vencimento));
					$dup->appendChild($dom->createElement("vDup", number_format($parcfin->valor_apagar,2,".","")));
								
					$cobr->appendChild($dup);
				endforeach;
				
				$infNFe->appendChild($cobr);
			endif;
			
			$infAdic = $dom->createElement("infAdic");
			
			$obs = trim($nfedados->obs);
			
			if(!empty($obs)):
				$infAdic->appendChild($dom->createElement("infCpl", trim($nfedados->obs)));
			endif;
			$infNFe->appendChild($infAdic);
			
			if(!empty($nfedados->ufexporta)):
				$exporta = $dom->createElement("exporta");
				$exporta->appendChild($dom->createElement("UFEmbarq", trim($nfedados->ufexporta)));
				$exporta->appendChild($dom->createElement("xLocEmbarq", trim($nfedados->localexporta)));
				$infNFe->appendChild($exporta);
			endif;
			
			//-----------------
			
			$NFe->appendChild($infNFe);
			$dom->appendChild($NFe);
			
		    if(@$dom->save(@$toolsnfe->entDir.$nfedados->chave."-nfe.xml")){
			    $etapa = array('etapa' => 5);
				$bo->update($etapa, "id = ".$idnfe);
				echo "sucessoxml";
			}else{
				echo "Erro ao gravar XML da pasta";
			}
			
			}catch (Zend_Exception $e){
				NfeBO::gravarErronfe($e->getMessage(), $idnfe, "NfeBO::gerarXml(".$idnfe.")");
				echo $e->getMessage();
				return false;
			}
						
		}
		
		//--- Usado em VendasBO::gerarFinanceirovenda --------------------
		function buscarNfevenda($params){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();			
			$select->from(array('p'=>'tb_pedidos','*'), array('*'))
					->join(array('n'=>'tb_nfe'),'n.id = p.id_nfe')
					->where("md5(p.id) = '".$params['ped']."'");
			
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		function assinaXmlnfe($idnfe){
			require_once("Nfephp/libs/ToolsNFePHP.class.php");
			$nfe 		= new ToolsNFePHP();
			$bo 		= new NfeModel();
								
			foreach ($bo->fetchAll("id = ".$idnfe) as $nfedados);
			
			//carrega nfe para assinar em uma strig
			$filename = $nfe->entDir.$nfedados->chave."-nfe.xml";
			
			//mantenha desse jeito mesmo com um unico =
			//a atribuição da variavel e o teste são simultâneos
			if ( $nfefile = file_get_contents($filename) ) {
				//assinador usando somente o PHP da classe classNFe
				//mantenha desse jeito mesmo com um unico =
				//a atribuição da variavel e o teste são simultâneos
				if ( $signn = $nfe->signXML($nfefile, 'infNFe') ) {
					//xml retornado gravar
					$file = $nfe->assDir.$nfedados->chave."-nfe.xml";
					if ( !file_put_contents($file, $signn) ) {
						//$nfe->errStatus = true;
						//return false;
						echo $erro = "ErroAssina:Erro na gravação da assinatura";
						NfeBO::gravarErronfe($erro, $idnfe);
			
					} else {
						unlink($filename);
						
						$etapa = array('etapa' => 6);
						$bo->update($etapa, "id = ".$idnfe);
						
						echo "sucessoassina";
			
					} //fim do teste de gravação
				} //fim do teste da assinatura
				
			}else{
				echo $erro = "Nota nao encontrada para assinar";
				NfeBO::gravarErronfe($erro, $idnfe, "NfeBO::assinaXmlnfe(".$idnfe.")");
				return false;				
			}
			
		}
		
		function validarXmlnfe($idnfe){
			require_once("Nfephp/libs/ToolsNFePHP.class.php");
			$nfe 		= new ToolsNFePHP();
			$bo 		= new NfeModel();	
			
			foreach ($bo->fetchAll("id = ".$idnfe) as $nfedados);
			
			
			@$filename = $nfe->assDir.$nfedados->chave."-nfe.xml";
			if ( $nfefile = @file_get_contents($filename) ) {
				//validar
				
				$filexsd = $nfe->xsdDir . $nfe->schemeVer . "/nfe_v2.00.xsd";
				
				$aErr = array();
				$bRet = $nfe->validXML($nfefile,$filexsd,$aErr);
								
				if ( $bRet ) {
					// validado => transferir para pasta validados
					$file = $nfe->valDir . $nfedados->chave."-nfe.xml";
					if ( !file_put_contents($file, $nfefile) ) {
						$nfe->errStatus = false;
					} else {
						unlink($filename);
						
						$etapa = array('etapa' => 7);
						$bo->update($etapa, "id = ".$idnfe);
						
						echo "sucessovalida";
					}
				}else {
                    $sErr = '';
                    foreach ($aErr as $e){
                    	$sErr .= $e . "\n";
                    }
                    //NFe com erros transferir de pasta rejeitadas
                    $file = $nfe->rejDir.$nfedados->chave."-nfe.xml";
                    $nfe->errStatus = true;
                    $nfe->errMsg .= $nfedados->chave.'-nfe.xml'.' ... '.$sErr."\n";
                    
                    if ( !file_put_contents($file, $nfefile) ) {
                    	$nfe->errStatus = true;
                    	$erro = "ErroValida:Nota rejeitada - Erro na gravação na pasta rejeitadas";
                    } else {
                    	unlink($filename);
                    	$erro = "ErroValida:Nota rejeitada - Gravada na pasta rejeitadas";
                    } //fim teste de gravação
                    
                    $erro .= $nfe->errMsg;
                   	//echo $erro;
                    echo $nfe->errMsg;
                   	 
                } //fim validação
				
                
			}else{
				echo $erro = "Nota n&atilde;o encontrada na pasta assindadas";
			}
			
			if($erro != ""):	
				NfeBO::gravarErronfe($erro, $idnfe, "NfeBO::validarXmlnfe(".$idnfe.")");
				return false;
			endif;
		}
		
		function enviaNFesefaz($idnfe){
			require_once("Nfephp/libs/ToolsNFePHP.class.php");
			$nfe 		= new ToolsNFePHP();
			$bo 		= new NfeModel();
			
			//-- marco envio para sefaz ------------------------------------------------
			$etapa = array('etapa' => 8);
			$bo->update($etapa, "id = ".$idnfe);
			
			//estabelece condição inicial do retorno
			$recibo = '';
			
			foreach ($bo->fetchAll("id = ".$idnfe) as $nfedados);
			
			$filename = $nfe->valDir.$nfedados->chave."-nfe.xml";
			$nfefile = file_get_contents($filename);
			$aNFE[] = $nfefile;
			
			$num = NfeBO::getNumLot();
			//incrementa o numero
			$num++;
			
			//enviar as notas
			
			if ($ret = $nfe->sendLot($aNFE,$num,$nfe->modSOAP)){
				//incrementa o numero do lote no controle
				    
				if (!NfeBO::putNumlot($num)){
					$errotp2 = "ErroSefaz:Falha na Gravação do numero do lote de envio!!";
					echo $errotp2;
					NfeBO::gravarErronfe($errotp2, $idnfe);
					return false;
				}
				
				//['bStat'=>false,'cStat'=>'','xMotivo'=>'','dhRecbto'=>'','nRec'=>'']
				//verificar o status do envio
			
				if ($ret['bStat']){
					//pegar o numero do recibo da SEFAZ
					$recibo = $ret['nRec'];
					//mover as notas do lote para o diretorio de enviadas
					//para cada em $aNames[] mover para $nfe->envDir
					
					$dir1 = $nfe->valDir.$nfedados->chave."-nfe.xml";
					$dir2 = $nfe->envDir.$nfedados->chave."-nfe.xml";
			
					if( !rename($dir1,$dir2) ){
						$errotp2 = "ErroSefaz:Falha na movimentacao da NFe das validadas para enviadas !!";
						echo $errotp2;
						NfeBO::gravarErronfe($errotp2, $idnfe, '', 2);
					}else{
						echo "sucessosefaz";
					}			
				} else {
					echo $errotp2 = "ErroSefaz:Erro no resultado da sefaz".$nfe->errMsg;		
					NfeBO::gravarErronfe($errotp2, $idnfe, '', 1);
				} 
			} else {
				echo $erro = "Erro no envio do lote de NFe:".$nfe->errMsg;
				NfeBO::gravarErronfe($erro, $idnfe, '', 0);
			}						
		}
		
		function getNumLot(){
			require_once("Nfephp/libs/ToolsNFePHP.class.php");
			$nfe 		= new ToolsNFePHP();
			
			$lotfile = $nfe->raizDir . 'config/numloteenvio.xml';
			$domLot = new DomDocument;
			$domLot->load($lotfile);
			$num = $domLot->getElementsByTagName('num')->item(0)->nodeValue;
			if( is_numeric($num) ){
				return $num;
			} else {
				//arquivo não existe, então suponho que o numero seja 1
				return 1;
			}
		}
		
		function putNumlot($num){
			require_once("Nfephp/libs/ToolsNFePHP.class.php");
			$nfe = new ToolsNFePHP();
			
			if ( is_numeric($num) ){
				$lotfile = $nfe->raizDir . 'config/numloteenvio.xml';
				$numLot = '<?xml version="1.0" encoding="UTF-8"?><root><num>' . $num . '</num></root>';
				if (!file_put_contents($lotfile,$numLot)) {
					//em caso de falha retorna falso
					return false;
				} else {
					//em caso de sucesso retorna true
					return true;
				}
			}
		}		
		
		function verificaRetornonfesefaz($idnfe){
			require_once("Nfephp/libs/ToolsNFePHP.class.php");
			$nfe 		= new ToolsNFePHP();
			$bo 		= new NfeModel();
			$bom		= new NferecusaModel();
			
			//estabelece condição inicial do retorno
			$recibo = '';			
			foreach ($bo->fetchAll("id = ".$idnfe) as $nfedados);			
						
			//---5 passo: Veriricar se autorizada, rejeitada ou reprovada -----------------
			//condição inicial da variável de retorno
			$aRetorno = array(0=>array('cStat'=>'','xMotivo'=>'','nfepath'=>''));
			$n = 0;
			
			$nfeFile = $nfe->envDir.$nfedados->chave."-nfe.xml";
			
			//primeiro verificar se o protocolo existe na pasta temporarias
			
			$protFile = $nfe->temDir.$nfedados->chave.'-prot.xml';
			
			if (file_exists($protFile)){
				$docxml = file_get_contents($protFile);
				$dom = new DOMDocument(); //cria objeto DOM
				$dom->formatOutput = false;
				$dom->preserveWhiteSpace = false;
				$dom->loadXML($docxml);
				//pagar o cStat e xMotivo do protocolo
				$aRet['cStat'] = $dom->getElementsByTagName('cStat')->item(0)->nodeValue;
				$aRet['xMotivo'] = $dom->getElementsByTagName('xMotivo')->item(0)->nodeValue;
				$aRet['bStat'] = true;
				
			
			} else {
				//caso não exista então buscar pela chave da NFe			
				$aRet = $nfe->getProtocol('',$nfedados->chave,$nfe->tpAmb,$nfe->modSOAP);
			}
			
			if ( $aRet['cStat'] == 100) {
				//NFe aprovada
				$pasta = $nfe->aprDir;
				
				//--- Marca NFe como aprovada --------------------------------
				$retorno = $aRet['aProt'];
				
				$rep = array(
					'status' => true,
				    'autorizacao' => $retorno['nProt']
				);
				$bo->update($rep, 'id = '.$idnfe);		
				
				echo "sucessoaprovada";
				
				$etapa = array('etapa' => 9);
				$bo->update($etapa, "id = ".$idnfe);
				
			}//endif
			if ( $aRet['cStat'] == 110) {
				//NFe denegada
				$pasta = $nfe->denDir;
				$erro = "NFe denegada";
				
				echo "NFe denegada: ".$aRet['xMotivo'];
				NfeBO::gravarErronfe($erro.": ".$aRet['xMotivo'], $idnfe, '', 1);
				return false;
				
			}//endif
			
			if ( $aRet['cStat'] > 199 && $aRet['cStat'] != 301 && $aRet['cStat'] != 302) {
				//NFe reprovada
				$pasta = $nfe->repDir;
				/* //mover a NFe para a pasta reprovadas
				@rename($nfeFile, $pasta.$nfedados->chave.'-nfe.xml');
				$erro = "NFe reprovada"; */
				
				if($aRet['cStat'] != 217){
				    //mover a NFe para a pasta reprovadas
				    @rename($nfeFile, $pasta.$nfedados->chave.'-nfe.xml');
				    $erro = "NFe reprovada";
					NfeBO::gravarErronfe($erro.": ".$aRet['xMotivo'], $idnfe, '', 0);
				}
				
				try{
					if(count($bom->fetchAll("cstat = '".$aRet['cStat']."'"))>0){
					    foreach ($bom->fetchAll("cstat = '".$aRet['cStat']."'") as $motivorecusa);
					    echo $aRet['cStat']."|".$motivorecusa->descricao;				    
					}else{
					    echo $aRet['cStat']."|".$aRet['xMotivo'];
					}
				}catch(Zend_Exception $e){
					$boerro	= new ErrosModel();
					$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "NfeBO::verificaRetornonfesefaz(nfe=".$idnfe.")");
					$boerro->insert($dataerro);
				
					echo $aRet['cStat']."|".$aRet['xMotivo'];
				}
				
				return false;
			}//endif
			
			if ( $aRet['bStat'] ) {
				//montar a NFe com o protocolo
				if ( is_file($protFile) && is_file($nfeFile) ) {
					//se aprovada ou denegada adicionar o protocolo e mover o arquivo
					if ($aRet['cStat'] == 100 || $aRet['cStat'] == 110 ) {
						$procnfe = $nfe->addProt($nfeFile,$protFile);
						
						//salvar a NFe com o protocolo na pasta
						if ( file_put_contents($pasta.$nfedados->chave.'-nfe.xml', $procnfe) ) {
							//se o arquivo foi gravado na pasta destino com sucesso
							//remover os arquivos das outras pastas
							unlink($nfeFile);
						}
								
						//endif
					}//endif cStat
				} //endif is_file
			} //endif bStat
			
			$aRetorno[$n]['cStat'] = $aRet['cStat'];
			$aRetorno[$n]['xMotivo'] = $aRet['xMotivo'];
			$aRetorno[$n]['nfepath'] = $pasta.$nfedados->chave.'-nfe.xml';			
		}
		
		function gerarDanfenfe($idnfe){
			require_once("Nfephp/libs/ToolsNFePHP.class.php");
			require_once("Nfephp/libs/DanfeNFePHP.class.php");
			$nfe 		= new ToolsNFePHP();
			$danfe		= new DanfeNFePHP();			
			$bo 		= new NfeModel();
			
			foreach ($bo->fetchAll("id = ".$idnfe) as $nfedados);
			//$filename = $nfe->valDir.$nfedados->chave."-nfe.xml";
			
			/* if($nfedados->tipo == 0):
				$piscofins = 1;
			else:
				$piscofins = 1;
			endif; */
			
			//$anomes = '20'.substr($nfedados->chave,2,4);
			if($docxml = file_get_contents($nfe->aprDir."/".$nfedados->chave."-nfe.xml")):
				$danfe 		= new DanfeNFePHP($docxml,$nfe->danfepaper,$nfe->danfeform,$nfe->danfelogopath,'I','',$nfe->danfefont,1);
				
				$id = (string) $danfe->montaDANFE();
				$pdfName = $id.'.pdf';
				//carrega a DANFE como uma string para gravação no diretorio escolhido
				$pdfFile = (string) $danfe->printDANFE($nfe->pdfDir.$pdfName,'S');
				if ($pdfFile != ''){
					//grava a DANFE como pdf no diretorio
					if (!file_put_contents($nfe->pdfDir.$pdfName,$pdfFile)){
						//houve falha na gravação
						$nfe->errMsg = "Falha na gravação do pdf";
						$nfe->errStatus = true;
					} else {
						//em caso de sucesso, verificar se foi definida a printer se sim imprimir
						//este comando de impressão funciona tanto em linux como em wndows se o
						//ambiente estiver corretaente preparado
						echo "sucessodanfe";
						
						$etapa = array('etapa' => 10);
						$bo->update($etapa, "id = ".$idnfe);
						
						if ( $nfe->danfeprinter != '' ) {
							$command = "lpr -P $nfe->danfeprinter $nfe->pdfDir$pdfName";
							system($command);
						}
					}
				} else {
					//houve falha na geração da DANFE
					$nfe->errMsg = "Falha na geração da DANFE";
					$nfe->errStatus = true;
				}
				
				echo $erro = $nfe->errMsg;
			else:
				echo $erro = "Não foi encontrado o xml para gerar a DANFE";
			endif;
			
			
			if($erro != ""):
				echo $erro;
				NfeBO::gravarErronfe($erro, $idnfe, '', 2);
				return false;
			endif;			
		}
		
		function gerarEmailnfe($idnfe){
			require_once("Nfephp/libs/ToolsNFePHP.class.php");
			$nfe 		= new ToolsNFePHP();
			$bo 		= new NfeModel();
			
			foreach ($bo->fetchAll("id = ".$idnfe) as $nfedados);			
			foreach (ClientesBO::listaEmailsUp($nfedados->id_cliente,3) as $emails);
			
			if($emails->EMAIL!=""):
			
				$anomes = '20'.substr($nfedados->chave,2,4);
				//$anomes."/".
				$docxml = file_get_contents($nfe->aprDir.$nfedados->chave."-nfe.xml");
				$docpdf = file_get_contents($nfe->pdfDir.$nfedados->chave.".pdf");
				
				
				$message = '<table width="750" align="center" border="0" cellpadding="0" cellspacing="0"><tr><td width="100%"><a href="http://www.ztlbrasil.com.br" target="_blank">
				<font size="6" color="#1b999a" face="Arial, Helvetica, sans-serif">ztlbrasil.com.br</font></a></td></tr><tr><td>&nbsp;</td></tr><tr><td valign="top"><font size="2" color="#333333" face="Arial, Helvetica, sans-serif">
				Olá <strong>'.$emails->NOME_CONTATO.',</strong></font></td></tr><tr><td valign="top">&nbsp;</td></tr>
				<tr><td valign="top" style="text-align: justify;"><font size="2" color="#333333" face="Arial, Helvetica, sans-serif" >
				
				Você está recebendo a Nota Fiscal Eletrônica número '.$nfedados->id.', série '.$nfedados->serie.' da ZTL DO BRASIL IMPORTACAO EXPORTACAO E COMÉRCIO LTDA., no valor de R$ '.number_format($nfedados->totalnota,2,",",".").'. Além disso, junto com a mercadoria seguirá o DANFE (Documento Auxiliar da Nota Fiscal Eletrônica), impresso em papel que acompanha o transporte das mesmas.<br /><br />
				Anexo à este e-mail você está recebendo também o arquivo XML da Nota Fiscal Eletrônica. Este arquivo deve ser armazenado eletronicamente por sua empresa pelo prazo de 05 (cinco) anos, conforme previsto na legislação tributária (Art. 173 do Código Tributário Nacional e § 4º da Lei 5.172 de 25/10/1966).<br /><br />
				O DANFE em papel pode ser arquivado para apresentação ao fisco quando solicitado. Todavia, se sua empresa também for emitente de NF-e, o arquivamento eletrônico do XML de seus fornecedores é obrigatório, sendo passível de fiscalização.<br /><br />
				Para se certificar que esta NF-e é válida, queira por favor consultar sua autenticidade no site nacional do projeto NF-e (www.nfe.fazenda.gov.br), utilizando a chave de acesso contida no DANFE.<br /><br />
				O arquivo XML anexo a este e-mail, é a própria Nota Fiscal Eletrônica, que deve ser guardada pelo período de 05 anos. Ele contém todas as informações do emitente, destinatário, produtos, tributação, etc. Esse arquivo, além de guardado, deve ser enviado para o setor de contabilidade.</font> </td></tr><tr><td valign="top">&nbsp;</td></tr><tr><td valign="top" width="100%"><font size="2" color="#333333" face="Arial, Helvetica, sans-serif"> 
				';
				
				//Segue também, os seus boletos refente a esta compra, que pode(m) ser impresso(s) acessando:  <br> <br>;
				
				/* $contparc=0;
				foreach (FinanceiroBO::buscarFinanceironfe($idnfe) as $parcfin):
					$contparc++;
					$message .= 'Fatura <b>NE'.substr("000000".$idnfe, -6, 6)."/".$contparc.'</b> Vencimento: <b>'.substr($parcfin->vencimento,8,2).'/'.substr($parcfin->vencimento,5,2).'/'.substr($parcfin->vencimento,0,4).'</b> Valor: <b>'.number_format($parcfin->valor_apagar,2,".","").'</b>   
					<a href="http://www.ztlbrasil.com.br/servicos/financeiro/boleto/'.md5('boletocli'.$parcfin->id).'" title="Imprimir boleto" target="_blank">
					<img src="http://www.ztlbrasil.com.br/public/images/imprimir_orc.gif" alt="Imprimir"  width="18"/></a><br />';				
				endforeach; */
				
				$message .= '<br /></font></td></tr><tr><td valign="top"><font size="2" color="#333333" face="Arial, Helvetica, sans-serif">			
				Em caso de dúvidas, entre em contato com nosso Serviço de Atendimento ao Cliente, enviando e-mail para faturamento@ztlbrasil.com.br
				</font> </td></tr><tr><td valign="top">&nbsp;</td></tr><tr><td valign="top"><font size="2" color="#333333" face="Arial, Helvetica, sans-serif">
				Atenciosamente,<br />
				<b>Departamento de vendas ZTL Brasil</b><br />
				</font> </td></tr></table>';			
								
				$assunto 	= "Nota Fiscal Eletrônica nº ".$nfedados->id." da ZTL Brasil";
				$resp 		= $emails->NOME_CONTATO;
				$email		= $emails->EMAIL;
				
				try{
					if($nfe->tpAmb==1):
						NfeBO::enviaMailnfe($assunto, $message, $resp, $email, $idnfe);
					else:
						NfeBO::enviaMailnfe($assunto, $message, $resp, "cleiton@ztlbrasil.com.br", $idnfe);
					endif;
					
					$etapa = array('etapa' => 12);
					$bo->update($etapa, "id = ".$idnfe);
					
				}catch(Zend_Exception $e){
				    echo "Erro ao enviar e-mail ao cliente...";
				    return 0;
				}
				
				/* $email		= "faturamento@ztlbrasil.com.br";
				NfeBO::enviaMailnfe($assunto, $message, $resp, $email, $idnfe); */
				
			else:
				echo $erro = "Email NFE não cadastrado";
				NfeBO::gravarErronfe($erro, $idnfe, '', 2);
			endif;
		}
		
				
		function enviaMailnfe($assunto, $texto, $resp, $email, $idnfe){
			require_once("Nfephp/libs/ToolsNFePHP.class.php");
			$nfe 		= new ToolsNFePHP();
			$bo 		= new NfeModel();
			
			foreach ($bo->fetchAll("id = ".$idnfe) as $nfedados);
			
			try {
				$mailTransport = new Zend_Mail_Transport_Smtp("smtp.ztlbrasil.com.br", Zend_Registry::get('mailSmtp'));
						
				//$anomes = '20'.substr($nfedados->chave,2,4);
				$docxml = file_get_contents($nfe->aprDir.$nfedados->chave."-nfe.xml");
				$docpdf = file_get_contents($nfe->pdfDir.$nfedados->chave.".pdf");
				
				$mail = new Zend_Mail('utf-8');
				$mail->setFrom("info@ztlbrasil.com.br");
				$mail->addTo($email,$resp);
				$mail->addCc('faturamento@ztlbrasil.com.br', 'Faturamento ZTL');
				$mail->setBodyHtml($texto);
				$mail->setSubject($assunto);
				$mail->createAttachment($docpdf, "pdf", Zend_Mime::DISPOSITION_INLINE, Zend_Mime::ENCODING_BASE64, "Danfe.pdf");
				$mail->createAttachment($docxml, "xml", Zend_Mime::DISPOSITION_INLINE, Zend_Mime::ENCODING_BASE64, "XML.xml");
				$mail->send($mailTransport);
		
				if(($email != "faturamento@ztlbrasil.com.br") || ($nfe->tpAmb == 2)):
					echo "sucessoemail";
				endif;
								
			} catch (Exception $e){
				$erro = "Erro ao enviar Email NFE";
				echo $erro.$e->getMessage();
				NfeBO::gravarErronfe($erro, $idnfe, '', 2);
			}
		}
		
		//-- Grava erros da nfe ---------------------------------------------------
		function gravarErronfe($msg, $nf, $ped="", $tp = 0){
			try{
		    $bonfe		= new NfeModel();
			$bonfeprod	= new NfeprodModel();
			$botmp		= new NfetmpModel();
			$bov		= new PedidosvendaModel();
			$bo 		= new FinanceiroModel();
			$bop		= new FinanceiroreceberModel();
			$boparc 	= new FinanceiroreceberparcModel();
			$boerro		= new ErrosModel();
			$bog		= new GarantiaModel();
			$bogp		= new GarantiaproddetModel();
			
			$dataerro = array('descricao' => $msg, 'pagina' => $ped);
			$boerro->insert($dataerro);
			
			/*--
			 * Se $tp == 0 apaga tudo sobre a nfe ----
			 * Se $tp == 1 apaga chave estrangeira da nfe e o financeiro ---------- 
			 */
			
			if(($tp==0)||($tp==1)):
				//--- remove chave estrangeira em pedidos venda ----------------
				$nfeped = array('id_nfe' => NULL);
				$bov->update($nfeped, 'id_nfe = '.$nf);
				
				//--- remove chave estrangeira das garantias--------------------
				foreach ($bonfeprod->fetchAll("id_nfe = ".$nf) as $nfeprod):
					$nfeped = array('id_nfeprod' => NULL);
					$bogp->update($nfeped, 'id_nfeprod = '.$nfeprod->id);
				endforeach;
				
				//--- remove chave estrangeira das NFe avulsas--------------------
				$nfeav = array('id_nfe' => NULL);
				$botmp->update($nfeav, 'id_nfe = '.$nf);
				
				//--- Remove Financeiro ----------------------------------------
				foreach ($bop->fetchAll('id_nfe = '.$nf) as $financeiro);
				if(!empty($financeiro)):
					$boparc->delete('id_financeirorec = '.$financeiro->id);
					$bop->delete('id = '.$financeiro->id);
				endif;
				
				//--- Marca NFe como reprovada --------------------------------
				$rep = array('status' => false);
				$bonfe->update($rep, 'id = '.$nf);
				
			endif;
			
			if($tp==0){
				//--- remove nfe -----------------------------------------------
				$bonfeprod->delete('id_nfe = '.$nf);
				$bonfe->delete('id = '.$nf);
				
				if(!empty($nf)){
					$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
					$db->setFetchMode(Zend_Db::FETCH_OBJ);
					$db->query('ALTER TABLE `ztlrolamentos`.`tb_nfe` AUTO_INCREMENT = '.$nf);
				}
			}
			
			}catch(Zend_Exception $e){
			    $boerro	= new ErrosModel();
			    $dataerro = array('descricao' => $e->getMessage(), 'pagina' => "NfeBO::gravarErronfe(nfe=".$nf.")");
			    $boerro->insert($dataerro);
			     
			    return 0;
			}
		}
		
		//--- Listagem de NFe -------------------------------------------------
		/* Usado em 
		 * venda/garantiasnfe ---------------
		 * 
		 * */
		
		function buscaNfe($params){
			$bo		= new NfeModel();
			return $bo->fetchAll("id = ".$params);
		}
		
		function buscaProdutosnfe($params){
			$bo		= new NfeModel();
			$bop	= new NfeprodModel();
			return $bop->fetchAll("id_nfe = ".$params);
		}
		
		function listaNfe($params="",$tipo=""){
		    try{
			    $bo		= new NfeModel();
			    if(isset($params['buscacli']) and $params['buscacli'] != 0):
			    	$where = " and id_cliente = ".$params['buscacli'];
			    endif;
			    
			   	if(($tipo == "garantia")):
			    	$where .= " and cfop in (5916,6916) and status = true";
			    endif;	
	
			    //-- busca por nfe --------------------------
			    if(!empty($params['nfe'])){
			        $where = " and id = '".ereg_replace("[^0-9]", " ", $params['nfe'])."'";
			    }
			    
			    if(!empty($params['fil'])){
					if($params['fil '] == 'entrada'){
			        	$where = " and tipo = 0";
			        }elseif($params['fil'] == 'saida'){
			        	$where = " and tipo = 1";
			        }elseif($params['fil'] == 'canceladas'){
			        	$where = " and status = 3";
			        }elseif($params['fil'] == 'inutilizadas'){
			        	$where = " and status = 2";
			        }  
			    }
			    
			    return $bo->fetchAll("id > 0 ".$where,"id desc");
		    }catch (Zend_Exception $e){
		    	$boerro	= new ErrosModel();
		    	$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "NfeBO::listaNfe()");
		    	$boerro->insert($dataerro);
		    }
		}
		
		//--- Busca de NFe -------------------------------------------------
		function buscaNfemd5($params){
			$bo		= new NfeModel();
			return $bo->fetchAll("md5(id) = '".$params['nfe']."'");
		}

		//--- Busca de NFe -------------------------------------------------
		function qtNfe(){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
			$select = $db->select()->from(array('t'=>'tb_nfe','*'), array('count(*) as qt'));
			$stmt = $db->query($select);
			
			return $stmt->fetchAll();
		}
		
		function buscaNfeprodmd5($params){
			$bo		= new NfeModel();
			$bop	= new NfeprodModel();
			return $bop->fetchAll("md5(id_nfe) = '".$params['nfe']."'");
		}
		
		function buscaCcemd5($params){
			$bo		= new NfeModel();
			$bocce	= new NfecceModel();
			return $bocce->fetchAll("status = 1 and md5(id_nfe) = '".$params['nfe']."'",'id desc',1);
		}
		
		
		//-- Nova nfe avulsa ----------------------------------------------------
		//------- NFe ---------------------------------------
		function gravarDadosnfetmp($params){
			$bonfe			= new NfeModel();
			$bonfetmp		= new NfetmpModel();
			
			if(empty($params['parceiro'])):
				//--- Busca CFOP da compra ----------------------------------------------
				$cfopid['idcfop'] = md5($params['cfop']);
				foreach (TributosBO::buscaCfop($cfopid) as $cfop);
			
				//-- Enderecos ----------------------------------------------------------
				$buscapais['idpais'] = md5($params['pais']);
				foreach (EstadosBO::buscaPaises($buscapais) as $pais);
				
				$cidade = EstadosBO::buscaCidadesid(array('cidade' => $params['cidade']));
				
				//-- Dados da NFe ------------------------------------
				$datanfe = array(
					'serie'					=> 1,
					'data'					=> date('Y-m-d'),
					'data_saida'			=> date('Y-m-d H:i:s'),
					'cfop'					=> $cfop->cfop,
					'naturezaop'			=> $cfop->descricao,
					'tipo'					=> $params['movimento'],
					'cnpj'					=> $params['cnpj'],
					'inscricao'				=> $params['inscricao'],
					'empresa'				=> DiversosBO::pogremoveAcentos($params['empresa']),
					'endereco'				=> DiversosBO::pogremoveAcentos($params['logradouro'].$params['complemento']),
					'numero'				=> $params['n'],
					'bairro'				=> DiversosBO::pogremoveAcentos($params['bairro']),
					'cep'					=> $params['cep'],
					'codcidade'				=> $cidade->codigo,
					'cidade' 				=> DiversosBO::pogremoveAcentos($cidade->nome),
					'codpais'				=> $pais->codigo,
					'pais' 					=> DiversosBO::pogremoveAcentos($pais->nome),
					'uf'					=> $cidade->uf,
					'fone'					=> $params['ddd'].$params['telefone'],
					'transportadora'		=> $params['transnome'],
					'transcnpj'				=> $params['transcnpj'],
					'transie'				=> $params['transinscricao'],
					'transendereco'			=> DiversosBO::pogremoveAcentos($params['translogradouro']),
					'transcidade' 			=> DiversosBO::pogremoveAcentos($params['transcidade']),
					'transuf'				=> $params['transuf'],
					'movestoque'			=> 0,
					'email'					=> $params['email'],
					'contato'				=> $params['contatoemail'],
					'nivel'					=> 1,
					'status'				=> 0
				);
			else:
			
				//--- Parceiro ------------------------------------------------------------------------
				$busca['idparceiro']		= $params['parceiro'];
				foreach (ClientesBO::buscaParceiros("",$busca) as $listaempresa);
					
				foreach (ClientesBO::listaEnderecocomp($params['parceiro'],1) as $endempresa);
				foreach (TributosBO::buscaDespesasperfil($listaempresa->id_despesasfiscais) as $depempresa);
				foreach (ClientesBO::listaTelefonesUp($params['parceiro'], "telefone1") as $telempresa);
				foreach (ClientesBO::listaEmailsUp($params['parceiro'], 3) as $empresamail);
				
				//--- Busca CFOP da compra ----------------------------------------------
				$cfopid['idcfop'] = md5($params['cfop']);
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
					'tipo'					=> $params['movimento'],
				    'id_clientes'			=> $listaempresa->ID,
					'cnpj'					=> $listaempresa->CPF_CNPJ,
					'inscricao'				=> $listaempresa->RG_INSC,
					'empresa'				=> DiversosBO::pogremoveAcentos($listaempresa->RAZAO_SOCIAL),
					'endereco'				=> DiversosBO::pogremoveAcentos($endempresa->LOGRADOURO),
					'numero'				=> $endempresa->numero,
					'bairro'				=> DiversosBO::pogremoveAcentos($endempresa->BAIRRO),
					'cep'					=> $endempresa->CEP,
					'codcidade'				=> $endempresa->codcidade,
					'cidade' 				=> DiversosBO::pogremoveAcentos($endempresa->ncidade),
				    'codpais'				=> $endempresa->codpais,
				    'pais' 					=> DiversosBO::pogremoveAcentos($endempresa->npais),
					'uf'					=> $endempresa->nuf,
					'fone'					=> $telempresa->DDD.$telempresa->NUMERO,
					'movestoque'			=> $params['estoque'],
					'email'					=> $params['email'],
					'contato'				=> $params['contatoemail'],
					'nivel'					=> 1,
					'status'				=> 0
				);

				
			endif;
		
			try {
				$idnfe = $bonfetmp->insert($datanfe);				
				//Zend_Debug::dump($datanfe);
			}catch (Zend_Exception $e){
				/* $boerro	= new ErrosModel();
				$dataerro = array('descr' => $e->getMessage(), 'erro' => $params[ped]);
				$boerro->insert($dataerro); */
			    echo $e->getMessage();
			}
			
			
			//--- Busca transportadora com enderecos ----------------
			$busca['idparceiro'] = $listaempresa->id_transportadoras;			
			foreach (ClientesBO::buscaParceiros("",$busca) as $transportadora);
			
			//--- Transportadora ---------------------------------------
			if($listaempresa->tptransp == 1):
				$datanfe = array(
					'transportadora'		=> "Nosso Carro",
					'transcnpj'				=> "07555737000110",
					'transie'				=> "0747014000173",
					'transendereco'			=> "Taguatinga Norte QI 08 Lote 45",
					'transcidade' 			=> "Brasilia",
					'transuf'				=> "DF"
				);
				
			elseif($listaempresa->tptransp == 2):
				$datanfe = array(
					'transportadora'		=> "Cliente Retira",
					'transcnpj'				=> $listaempresa->CPF_CNPJ,
					'transie'				=> $listaempresa->RG_INSC,
					'transendereco'			=> DiversosBO::pogremoveAcentos($endempresa->BAIRRO." ".$endempresa->LOGRADOURO." ".$endempresa->numero),
					'transcidade' 			=> DiversosBO::pogremoveAcentos($endempresa->ncidade),
					'transuf'				=> $endempresa->nuf,
				);
			
			elseif(!empty($transportadora->ID)):
				foreach (ClientesBO::listaEnderecocomp($listaempresa->id_transportadoras, 1) as $endtransportadora);
				$datanfe = array(
					'id_transportadoras'	=> $transportadora->ID,
					'transportadora'		=> $transportadora->RAZAO_SOCIAL,
					'transcnpj'				=> $transportadora->CPF_CNPJ,
					'transie'				=> $transportadora->RG_INSC,
					'transendereco'			=> DiversosBO::pogremoveAcentos($endtransportadora->BAIRRO." ".$endtransportadora->LOGRADOURO." ".$endtransportadora->numero),
					'transcidade' 			=> DiversosBO::pogremoveAcentos($endtransportadora->ncidade),
					'transuf'				=> $endtransportadora->nuf,
				);
			
			endif;
			
			try {
				$bonfetmp->update($datanfe,"id = ".$idnfe);
			}catch (Zend_Exception $e){
				$boerro	= new ErrosModel();
				$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "NfeBO::gravarDadosnfetmp(ped=".$params[ped].")");
				$boerro->insert($dataerro);
			}
			
			return $idnfe;
		}
		
		function listaNfetmp(){
		    $bon	= new NfeModel();
			$bo		= new NfetmpModel();
			return $bo->fetchAll("sit = 1","id desc");
		}
		
		function removeNfetmp($params){
		    try{
				$bon	= new NfeModel();
				$bo		= new NfetmpModel();
				return $bo->update(array("sit" => "0"),"id = ".$params['nfe']);
				
			}catch(Zend_Exception $e){
				$boerro	= new ErrosModel();
				$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "NfeBO::removeNfetmp(nfe=".$params['nfe'].")");
				$boerro->insert($dataerro);			
			}
		}
		
		//--- Busca de NFe -------------------------------------------------
		function buscaNfetmpmd5($params){
			$bo		= new NfeModel();
			$bot	= new NfetmpModel();
			return $bot->fetchAll("md5(id) = '".$params['nfe']."'");
		}
		
		function buscaNfeprodtmpmd5($params){
			/* $bo		= new NfeModel();
			$bop	= new NfeprodtmpModel();
			return $bop->fetchAll("md5(id_nfe) = '".$params['nfe']."'"); */
						
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
			$select = $db->select();
				
			$select->from(array('t'=>'tb_nfeprodtmp','*'), array('t.*', 'e.qt_atual','t.id as idnfeprod','t.id_prod'))
				->joinLeft(array('e'=>'tb_estoqueztl'),'t.id_prod = e.id_prod and e.id = (SELECT max(id) from tb_estoqueztl e where t.id_prod = e.id_prod)')
				->where("md5(t.id_nfe) = '".$params['nfe']."'");
				
			$stmt = $db->query($select);
			return $stmt->fetchAll();
			
		}
		
		function removeProdnfetmp($params){
			$bonfe			= new NfeModel();
			$bonfetmp		= new NfetmpModel();
			$bonfeprodtmp   = new NfeprodtmpModel();
				
			$bonfeprodtmp->delete("md5(id) = '".$params['prod']."'");
		}
		
		function gravaDadosnfeprodn1($params){
		    $bonfe			= new NfeModel();
		    $bonfetmp		= new NfetmpModel();
		    $bonfeprodtmp   = new NfeprodtmpModel();
		    
		    foreach (ProdutosBO::listaProdutos($params['produto']) as $listProd);
		    
		    $datancm = array('ncm' => md5($listProd->id_ncm));
		    foreach (TributosBO::buscaNcm($datancm) as $listaNcm);
		    
		    $dataprod = array(
	    		'id_nfe'		=> $params['nfetmp'],
	    		'id_prod'		=> $params['produto'],
	    		'codigo'		=> $listProd->CODIGO,
	    		'descricao'		=> $listProd->DESCRICAO,
	    		'ncm'			=> str_replace(".", "", $listaNcm->ncm),
	    		'ncmex'			=> $listaNcm->ncmex,
	    		'qt'			=> $params['qt'],
	    		'preco'			=> str_replace(",", ".", str_replace(".", "", $params['preco'])),
	    		'unidade'		=> $listProd->unidade,
	    		'codean'		=> $listProd->codigo_ean
		    );
		    
		    $bonfeprodtmp->insert($dataprod);
		}

		function gravaDadosnfeprodn1out($params){
			$bonfe			= new NfeModel();
			$bonfetmp		= new NfetmpModel();
			$bonfeprodtmp   = new NfeprodtmpModel();
		
			$datancm = array('ncm' => md5($params['ncm']));
			foreach (TributosBO::buscaNcm($datancm) as $listaNcm);
		
			$dataprod = array(
				'id_nfe'		=> $params['nfetmp'],
				'codigo'		=> DiversosBO::pogremoveAcentos($params['codigo']),
				'descricao'		=> DiversosBO::pogremoveAcentos($params['descricao']),
				'ncm'			=> str_replace(".", "", $listaNcm->ncm),
				'ncmex'			=> $listaNcm->ncmex,
				'qt'			=> $params['qt'],
				'preco'			=> str_replace(",", ".", str_replace(".", "", $params['preco'])),
				'unidade'		=> $params['unidade'],
				'codean'		=> $params['codean']
			);
		
		
			$bonfeprodtmp->insert($dataprod);
		}
		
		
		function gravaDadosnfeprodn2($params){
			$bonfe			= new NfeModel();
			$bonfetmp		= new NfetmpModel();
			$bonfeprodtmp   = new NfeprodtmpModel();
		
			$busca['idcfop'] = md5($params['cfop']);
			foreach (TributosBO::buscaCfop($busca) as $busca);
			
			$cfop = array('cfop' => $busca->cfop, 'naturezaop' => $busca->descricao);
			$bonfetmp->update($cfop, "id = '".$params['nfetmp']."'");
			
			foreach (NfeBO::buscaNfetmpmd5($params) as $nfe);

			$total_pedido = 0;
			foreach(NfeBO::buscaNfeprodtmpmd5($params) as $nfeprod):
				
				$buscaipi = array('idcst' => md5($params['cstipi_'.$nfeprod->idnfeprod]));
				foreach(TributosBO::buscaCstipi($buscaipi) as $cstipi);
				
				if($cstipi->tipo == 1):
					$tpipi = "Trib";
				elseif($listProd->tipoipi == 2):
					$tpipi = "NT";
				else:
					$erro =  "Tipoipi";
				endif;
				
				$buscapis = array('idcst' => md5($params['cstpis_'.$nfeprod->idnfeprod]));
				foreach(TributosBO::buscaCstpis($buscapis) as $cstpis);
				
				if($cstpis->tipo == 1):
					$tppis = "Aliq";
				elseif($cstpis->tipo == 2):
					$tppis = "Qtde";
				elseif($cstpis->tipo == 3):
					$tppis = "NT";
				elseif($cstpis->tipo == 4):
					$tppis = "Outr";
				else:
					$erro =  "Tipopis";
				endif;
				
				$buscacofins = array('idcst' => md5($params['cstcofins_'.$nfeprod->idnfeprod]));
				foreach(TributosBO::buscaCstcofins($buscacofins) as $cstcofins);
				
				if($cstcofins->tipo == 1):
					$tpcofins = "Aliq";
				elseif($cstcofins->tipo == 2):
					$tpcofins = "Qtde";
				elseif($cstcofins->tipo == 3):
					$tpcofins = "NT";
				elseif($cstcofins->tipo == 4):
					$tpcofins = "Outr";
				else:
					$erro =  "Tipocofins";
				endif;
			
				$dataprod = array(
			        'alii'			=> str_replace(",", ".", str_replace(".", "", $params['alii_'.$nfeprod->idnfeprod])),
			        'vlii'			=> str_replace(",", ".", str_replace(".", "", $params['prodii_'.$nfeprod->idnfeprod])),
			        'baseii'		=> str_replace(",", ".", str_replace(".", "", $params['baseprodii_'.$nfeprod->idnfeprod])),
				        
					'alicms'		=> str_replace(",", ".", str_replace(".", "", $params['alicms_'.$nfeprod->idnfeprod])),
					'baseicms'		=> str_replace(",", ".", str_replace(".", "", $params['baseprodicms_'.$nfeprod->idnfeprod])),
					'vlicms'		=> str_replace(",", ".", str_replace(".", "", $params['prodicms_'.$nfeprod->idnfeprod])),
					'csticms'		=> str_pad($params['csticms_'.$nfeprod->idnfeprod], 2, '0',STR_PAD_LEFT),
				    'origem'		=> $params['origem_'.$nfeprod->idnfeprod],
			        
				    'basest'		=> str_replace(",", ".", str_replace(".", "", $params['baseprodicmsst_'.$nfeprod->idnfeprod])),
			        'mvast'			=> $nfe->mva,
			        'icmsst'		=> $params['alicmsst_'.$nfeprod->idnfeprod],
			        'vlicmsst'		=> str_replace(",", ".", str_replace(".", "", $params['prodicmsst_'.$nfeprod->idnfeprod])),
				        
					'alipi'			=> str_replace(",", ".", str_replace(".", "", $params['alipi_'.$nfeprod->idnfeprod])),
					'vlipi'			=> str_replace(",", ".", str_replace(".", "", $params['prodipi_'.$nfeprod->idnfeprod])),
					'cstipi'		=> str_pad($cstipi->cstipi, 2, '0',STR_PAD_LEFT),
				    
				    'cstpis'		=> str_pad($cstpis->cstpis, 2, '0',STR_PAD_LEFT),
					'alpis'			=> str_replace(",", ".", str_replace(".", "", $params['alpis_'.$nfeprod->idnfeprod])),
					'vlpis'			=> str_replace(",", ".", str_replace(".", "", $params['prodpis_'.$nfeprod->idnfeprod])),
				        
					'cstcofins'		=> str_pad($cstcofins->cstcofins, 2, '0',STR_PAD_LEFT),
					'alcofins'		=> str_replace(",", ".", str_replace(".", "", $params['alcofins_'.$nfeprod->idnfeprod])),
					'vlcofins'		=> str_replace(",", ".", str_replace(".", "", $params['prodcofins_'.$nfeprod->idnfeprod])),
				        
					'csttpipi'		=> $tpipi,
					'csttppis'		=> $tppis,
					'csttpcofins'	=> $tpcofins
				);
				
				$bonfeprodtmp->update($dataprod, "id = ".$nfeprod->idnfeprod);
				
				$total_pedido 	+= $nfeprod->preco * $nfeprod->qt;
			endforeach;
			
			$desc = str_replace(",",".",str_replace(".","",$params['desconto']));
			$perc = str_replace(",",".",str_replace(".","",$params['descontoperc']));
			if(!empty($params['desconto'])):
				$perc = $desc * 100 / $total_pedido;
			elseif($params['descontoperc']):
				$desc = ($perc * $total_pedido) / 100;
			endif;
				
			$frete = str_replace(",",".",str_replace(".","",$params['frete']));
			if(!empty($params['frete'])):
				$freteperc = $frete * 100 / $total_pedido;
			endif;
			
			//-- Dados da NFe ------------------------------------
			$datanfe = array(
				'pesoliquido'	=> str_replace(",", ".", str_replace(".", "", $params['pesoliquido'])),
			    'pesobruto'		=> str_replace(",", ".", str_replace(".", "", $params['pesobruto'])),
		        'obs'			=> $params['obsnfe'],
		        'frete'			=> str_replace(",", ".", str_replace(".", "", $params['frete'])),
		        'freteperc'		=> $freteperc,
		        'seguro'		=> str_replace(",", ".", str_replace(".", "", $params['seguro'])),
		        'desconto'		=> $desc,
		        'descontoperc'	=> $perc,
		        'outrasdesp'	=> str_replace(",", ".", str_replace(".", "", $params['outdespesas'])),
		        'quantidade'	=> $params['qtpacote'],
		        'especie'		=> $params['especie'],
		        'marca'			=> 'ZTL',
		        'tipofrete'		=> $params['tipofrete'],
		        'transantt'		=> $params['antt'],
		        'transplaca'	=> $params['placa'],
		        'transufplaca'	=> $params['ufplaca'],
		        'nivel'			=> 2
			);
			
			$bonfetmp->update($datanfe, "id = ".$params['nfetmp']);
			
		}
		
		function gravarDadosnfe($params){
			$bonfe			= new NfeModel();
			$bonfeprod		= new NfeprodModel();
			$bonfetmp		= new NfetmpModel();
			$bonfeprodtmp   = new NfeprodtmpModel();
			
			foreach (NfeBO::buscaNfetmpmd5($params) as $nfe);
				
			/* $total_pedido = 0;
			foreach(NfeBO::buscaNfeprodtmpmd5($params) as $nfeprod):
			 */		
			$total_pedido = $ipi = 0;
			$total_pedido_liquido = 0;
				
			//-- Dados da NFe ------------------------------------
			$datanfe = array(
				'serie'					=> 1,
				'data'					=> date('Y-m-d'),
				'data_saida'			=> date('Y-m-d H:i:s'),
				'cfop'					=> $nfe->cfop,
				'naturezaop'			=> trim($nfe->naturezaop),
				'tipo'					=> $nfe->tipo,
				'cnpj'					=> $nfe->cnpj,
				'inscricao'				=> $nfe->inscricao,
				'empresa'				=> $nfe->empresa,
				'endereco'				=> $nfe->endereco,
				'numero'				=> $nfe->numero,
				'bairro'				=> $nfe->bairro,
				'cep'					=> $nfe->cep,
				'codcidade'				=> $nfe->codcidade,
				'cidade' 				=> $nfe->cidade,
			    'codpais'				=> $nfe->codpais,
			    'pais' 					=> $nfe->pais,
				'uf'					=> $nfe->uf,
				'fone'					=> $nfe->fone,
				'transportadora'		=> $nfe->transportadora,
				'tipofrete'				=> $nfe->tipofrete,
				'transantt'				=> $nfe->transantt,
				'transplaca'			=> $nfe->transplaca,
				'transufplaca'			=> $nfe->transufplaca,
				'transcnpj'				=> $nfe->transcnpj,
				'transie'				=> $nfe->transie,
				'transendereco'			=> $nfe->transendereco,
				'transcidade' 			=> $nfe->transcidade,
				'transuf'				=> $nfe->transuf,
				'obs'					=> $nfe->obs,
				'frete'					=> $nfe->frete,
				'freteperc'				=> $nfe->freteperc,
				'seguro'				=> $nfe->seguro,
				'desconto'				=> $nfe->desconto,
				'descontoperc'			=> $nfe->descontoperc,
				'outrasdesp'			=> $nfe->outrasdesp,
				'quantidade'			=> $nfe->quantidade,
				'especie'				=> $nfe->especie,
				'pesobruto'				=> $nfe->pesobruto,
				'pesoliquido'			=> $nfe->pesoliquido,
				'marca'					=> 'ZTL',
				'di'					=> $nfe->di,
				'datadi'				=> $nfe->datadi,
				'localdesembarque'		=> $nfe->localdesembarque,
				'ufdesembarque'			=> $nfe->ufdesembarque,
				'datadesembarque'		=> $nfe->datadesembarque,
				'codexportador'			=> '662',
				'ufexporta'				=> $nfe->ufexporta,
				'localexporta'			=> $nfe->localexporta
			);		
			
			try {
				$idnfe = $bonfe->insert($datanfe);
				
				if(!empty($nfe->id_clientes)):
					$datanfe = array(
						'id_cliente'			=> $nfe->id_clientes,
						'id_transportadoras'	=> $nfe->id_transportadoras,
					);
					
					$bonfe->update($datanfe, "id = ".$idnfe);
				endif;
				
			}catch (Zend_Exception $e){
				
				$boerro	= new ErrosModel();
				$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "NfeBO::gravarDadosnfe(ped=".$params[ped].")");
				$boerro->insert($dataerro);
			}
				
			$total_pedido = $precototal = 0;
			$totalii = $total_prod = $ipi = $bicms = $vicms = $bst = $vst = $totalpis = $totalcofins = 0;
			foreach(NfeBO::buscaNfeprodtmpmd5($params) as $nfeprod):
				$precototal = $nfeprod->qt*$nfeprod->preco;
					
				/*-- Calcula o desconto -----------------------------------------
				 * -- O desconto nao se aplica ao IPI -------
				*/
				$desconto 	= ($precototal*$nfe->descontoperc)/100;
				
				/*-- Calcula o frete ----------------------------------------- */
				$frete 			= ($precototal*$nfe->freteperc)/100;
						
				$dataprod = array(
					'id_nfe'		=> $idnfe,
					'codigo'		=> $nfeprod->codigo,
					'descricao'		=> $nfeprod->descricao,
					'ncm'			=> $nfeprod->ncm,
					'ncmex'			=> $nfeprod->ncmex,
					'cfop'			=> $nfe->cfop,
					'qt'			=> $nfeprod->qt,
					'preco'			=> $nfeprod->preco,
					'alicms'		=> $nfeprod->alicms,
					'baseicms'		=> $nfeprod->baseicms,
					'vlicms'		=> $nfeprod->vlicms,
					'csticms'		=> $nfeprod->csticms,
					'alipi'			=> $nfeprod->alipi,
					'vlipi'			=> $nfeprod->vlipi,
					'cstipi'		=> $nfeprod->cstipi,
					'origem'		=> $nfeprod->origem,
					'unidade'		=> $nfeprod->unidade,
					'codean'		=> $nfeprod->codean,
					'basest'		=> $nfeprod->basest,
					'mvast'			=> $nfeprod->mvast,
					'icmsst'		=> $nfeprod->icmsst,
					'vlicmsst'		=> $nfeprod->vlicmsst,
					'desconto'		=> $desconto,
			        'alii'			=> $nfeprod->alii,
			        'baseii'		=> $nfeprod->baseii,
			        'vlii'			=> $nfeprod->vlii,
					'frete'			=> $frete,
					'cstpis'		=> $nfeprod->cstpis,
					'alpis'			=> $nfeprod->alpis,
					'vlpis'			=> $nfeprod->vlpis,
					'cstcofins'		=> $nfeprod->cstcofins,
					'alcofins'		=> $nfeprod->alcofins,
					'vlcofins'		=> $nfeprod->vlcofins,
					'csttpipi'		=> $nfeprod->csttpipi,
					'csttppis'		=> $nfeprod->csttppis,
					'csttpcofins'	=> $nfeprod->csttpcofins,
			        'dinumadicao'	=> $nfeprod->dinumadicao,
			        'dinumseq'		=> $nfeprod->dinumseq,
			        'dicodfab'		=> $nfeprod->dicodfab,
			        'vladuaneiro'	=> $nfeprod->vladuaneiro
				);
		
				$idprod = $bonfeprod->insert($dataprod);
				
				if(!empty($nfeprod->id_prod)):
					$dataprod = array(						
						'id_prod'		=> $nfeprod->id_prod,
					);						
					$bonfeprod->update($dataprod, "id = ".$idprod);
				endif;
				
				$totalii 		+= (isset($nfeprod->vlii)) ? $nfeprod->vlii : 0;
				$total_prod 	+= $precototal;
				$ipi 			+= (isset($nfeprod->vlipi)) ? $nfeprod->vlipi : 0;
				$bicms			+= (isset($nfeprod->baseicms)) ? $nfeprod->baseicms : 0;
				$vicms			+= (isset($nfeprod->vlicms)) ? $nfeprod->vlicms : 0;
				$bst			+= (isset($nfeprod->basest)) ? $nfeprod->basest : 0;
				$vst			+= (isset($nfeprod->vlicmsst)) ? $nfeprod->vlicmsst : 0;
				$totalpis		+= (isset($nfeprod->vlpis)) ? $nfeprod->vlpis : 0;
				$totalcofins	+= (isset($nfeprod->vlcofins)) ? $nfeprod->vlcofins : 0;
			endforeach;
		
			$vltotalnota = $ipi + $vst + $total_prod;
			$vltotalnota += ($nfe->frete + $nfe->outrasdesp + $nfe->seguro) - $nfe->desconto ;
			
			$datanfe = array(
				'baseicms'		=> number_format($bicms,2,".",""),
				'vlicms'		=> number_format($vicms,2,".",""),
				'basest'		=> number_format($bst,2,".",""),
				'vlst'			=> number_format($vst,2,".",""),
				'totalipi'		=> number_format($ipi,2,".",""),
				'totalpis'		=> number_format($totalpis,2,".",""),
			    'totalii'		=> number_format($totalii,2,".",""),
				'totalcofins'	=> number_format($totalcofins,2,".",""),
				'totalprodutos'	=> number_format($total_prod,2,".",""),
				'totalnota'		=> number_format($vltotalnota,2,".","")				
			);
				
			try {
				$bonfe->update($datanfe,'id = '.$idnfe);
				$nfeped = array('id_nfe' => $idnfe);
				$bonfetmp->update($nfeped, "md5(id) = '".$params['nfe']."'");
				//--- esse echo garente a validacao na nfevenda.js --------------------------
				echo "idnfe:".$idnfe;
			}catch (Zend_Exception $e){
				
				$boerro	= new ErrosModel();
				$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "NfeBO::gravarDadosnfe(ped=".$params[ped].")");
				$boerro->insert($dataerro);
			}
		}
		
		function gravarMovimentacaoestoque($var){
		    $boe		= new EntradaestoqueModel();
		    $boest		= new EstoqueModel();
		    $bonfe		= new NfeModel();
			$bonfetmp	= new NfetmpModel();
		    
			$usuario = Zend_Auth::getInstance()->getIdentity();
				
			//--Grava produtos no estoque-------------------------------
			
			$buscaprod['nfe']	= $var['baixarestoque'];
			foreach (NfeBO::buscaNfetmpmd5($buscaprod) as $nfe);
			
			if($nfe->movestoque == 2){
				foreach(NfeBO::buscaNfeprodtmpmd5($buscaprod) as $lista):
				
					$qt_atual = "";
					foreach ($boest->fetchAll('id_prod = '.$lista->id_prod,"id desc",1) as $qt_atual);
					$qtatual = 0;
					if(!empty($qt_atual->qt_atual)):
						if($nfe->tipo == 0):
							$qtatual = $qt_atual->qt_atual+$lista->qt;
						else:
							$qtatual = $qt_atual->qt_atual-$lista->qt;
						endif;
					else:
						if($nfe->tipo == 0):
							$qtatual = $lista->qt;
						else:
							$qtatual = -($lista->qt);
						endif;
					endif;
					
					//---- Insiro os produtos no estoque -------------------------------------------------------
					$arraye['id_prod'] 			= $lista->id_prod;
					$arraye['qt_atual'] 		= $qtatual;
					$arraye['qt_atualizacao'] 	= $lista->qt;
					$arraye['id_atualizacao'] 	= $lista->id_nfe;
					$arraye['dt_atualizacao'] 	= date("Y-m-d H:i:s");					
					$arraye['tipo']	 			= "NFe Avulsa";					
					$arraye['id_user'] 			= $usuario->id;
					
					$boest->insert($arraye);
				endforeach;
			}	

			//---- Marco NFetmp ID NFe ------------------------
				
			$erro = "sucessobaixa"; 
			if(!empty($var['idnfe'])):
				$arrayent = array ('id_nfe' 	=> $var['idnfe']);			
			
				try {
					$arrayent = array ('id_nfe' 	=> $var['idnfe']);			
					$bonfetmp->update($arrayent, "md5(id) = '".$var['baixarestoque']."'");
				}catch (Zend_Exception $e){
					$boerro	= new ErrosModel();
					$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "NfeBO::gravarMovimentacaoestoque(idnfe=".$var['idnfe'].")");
					$boerro->insert($dataerro);
					
					echo $erro = "Erro ao baixar estoque!";
				}	
				
			endif;

			echo $erro;
			
		}
		
		
		
		//---- Carta de Correcao Eletronica -------------------------------------
		
		
		function geraDacce($params){
		    require_once("html2pdf/html2pdf.class.php");
		    require_once("Nfephp/libs/ToolsNFePHP.class.php");
		    
		    $nfe 	= new ToolsNFePHP();
		    $bo		= new NfeModel();
		    $bocce	= new NfecceModel();
		    
		    foreach ($bo->fetchAll("id = ".$params['idnfe']) as $nfeoriginal);
		    foreach ($bocce->fetchAll("id = ".$params['gerardacce']) as $cce);
		    
		    $xml = simplexml_load_file($nfe->cccDir.$nfeoriginal->chave."-".$cce->seqevento."-procCCe.xml");
		    
		    $eventos = array();
		    $i = 0;
		    foreach ($xml as $evento):
		    	foreach ($evento->infEvento as $infEvento);
		    	$eventos[$i]['Id'] 			= $infEvento['Id'];
		    	$eventos[$i]['dhEvento'] 	= $infEvento->dhEvento;
		    	$eventos[$i]['nProt'] 		= $infEvento->nProt;
		    	
		    	$i++;
		    endforeach;
		    
		    $chave = substr($eventos[0]['Id'],2);
		    $chave1 = substr($chave, 0,4)." ".substr($chave, 4,4)." ".substr($chave, 8,4)." ".substr($chave, 12,4)." ".substr($chave, 16,4)." ".substr($chave, 20,4)." ".substr($chave, 24,4)." ".substr($chave, 28,4);
		    $chave2 = substr($chave, 32,4)." ".substr($chave, 36,4)." ".substr($chave, 40,4)." ".substr($chave, 44,4)." ".substr($chave, 48,4);
		    
		    $html = '<style>
					table#table100
					{
						width:100%;
						border-collapse: collapse;
					} 
					</style>
					<table id="table100"  >
						<tr>
							<td rowspan="3" style="border: 1px solid; text-align: center; width: 45%">
								<img alt="Logo" src="http://ztlbrasil.com.br/public/imagens/sistema/nfe/logop.jpg" >
							</td>
							<td rowspan="3" style="border: 1px solid; text-align: center; width: 20%">
								DACCE<br />
								DOCUMENTO<br />AUXILIDAR DA<br />CARTA DE<br />CORRE&Ccedil;&Atilde;O<br />ELETR&Ocirc;NICA<br />
								<b>N&#176; '.$nfeoriginal->id.'<br />
								S&Eacute;RIE 1<br />
								SEQ '.$cce->seqevento.'</b>
							</td>
							<td style="border: 1px solid; padding-left: 5px; width: 35%">
								<span style="font-size: 8px">PROTOCOLO DE AUTORIZA&Ccedil;&Atilde;O DE USO</span><br />
								'.$eventos[1]['nProt'].'
							</td>
						</tr>
						<tr>
							<td  style="border-right: 1px solid; border-bottom: 1px solid; padding-left: 5px; ">
								<span style="font-size: 8px">DATA/HORA DE REGISTRO DO EVENTO</span><br />
								'.$eventos[0]['dhEvento'].'
							</td>
						</tr>
						<tr>
							<td  style="border-right: 1px solid; border-bottom: 1px solid; padding-left: 5px; ">
								<span style="font-size: 8px">CHAVE DE ACESSO</span><br />
								'.$chave1.'<br />'.$chave2.'
							</td>
						</tr>
						<tr>
							<td  colspan="2" style="border: 1px solid; padding-left: 5px;">
								<span style="font-size: 8px">CNPJ</span><br />
								07555737000110
							</td>
							<td  style="border: 1px solid; padding-left: 5px;">
								<span style="font-size: 8px">INSCRI&Ccedil;&Atilde;O ESTADUAL</span><br />
								0747014000173
							</td>
						</tr>
					</table>
					<span style="font-size: 8px"><b>DESTINAT&Aacute;RIO</b></span>
					<table id="table100"  >
						<tr>
							<td style="border: 1px solid; padding-left: 5px; width: 70%"  colspan="2">
								<span style="font-size: 8px">NOME/RAZAO SOCIAL</span><br />
								'.$nfeoriginal->empresa.'
							</td>
							<td style="border: 1px solid; padding-left: 5px; width: 15%" >
								<span style="font-size: 8px">CNPJ/CPF</span><br />
								'.$nfeoriginal->cnpj.' 
							</td>
							<td style="border: 1px solid; padding-left: 5px; width: 15%" >
								<span style="font-size: 8px">DATA DA EMISS&Atilde;O DA NF-e</span><br />
								'.substr($nfeoriginal->data_saida, 8,2).'/'.substr($nfeoriginal->data_saida, 5,2).'/'.substr($nfeoriginal->data_saida, 0,4).' 
							</td>
						</tr>
						<tr>
							<td style="border: 1px solid; padding-left: 5px" >
								<span style="font-size: 8px">ENDERE&Ccedil;O</span><br />
								'.$nfeoriginal->endereco.', '.$nfeoriginal->numero.' 
							</td>
							<td style="border: 1px solid; padding-left: 5px" colspan="2">
								<span style="font-size: 8px">BAIRRO/DISTRITO</span><br />
								'.$nfeoriginal->bairro.' 
							</td>
							<td style="border: 1px solid; padding-left: 5px" >
								<span style="font-size: 8px">CEP</span><br />
								'.$nfeoriginal->cep.'  
							</td>
						</tr>
						<tr>
							<td style="border: 1px solid; padding-left: 5px" >
								<span style="font-size: 8px">MUNIC&Iacute;PIO</span><br />
								'.$nfeoriginal->cidade.' 
							</td>
							<td style="border: 1px solid; padding-left: 5px" >
								<span style="font-size: 8px">FONE/FAX</span><br />
								'.$nfeoriginal->fone.' 
							</td>
							<td style="border: 1px solid; padding-left: 5px" >
								<span style="font-size: 8px">UF</span><br />
								'.$nfeoriginal->uf.' 
							</td>
							<td style="border: 1px solid; padding-left: 5px" >
								<span style="font-size: 8px">INSCRI&Ccedil;&Atilde; ESTADUAL</span><br />
								'.$nfeoriginal->inscricao.' 
							</td>
						</tr>
					</table>
					<span style="font-size: 8px"><b>CORRE&Ccedil;&Atilde;O A SER CONSIDERADA</b></span>
					<table id="table100"  >
						<tr>
							<td style="height: 520px; vertical-align: top; border: 1px solid; padding-left: 5px; width: 100%">
								'.$cce->correcao.' 
							</td>
						</tr>
					</table>
					
					<span style="font-size: 8px"><b>CONDI&Ccedil;&Atilde;O DE USO</b></span>
					<table id="table100"  >
						<tr>
							<td style="height: 200px; vertical-align: top; border: 1px solid; padding-left: 5px; width: 100%">
								A Carta de Correcao e disciplinada pelo paragrafo 1o-A do art. 7o do Convenio S/N, de 15 de dezembro de 1970 e pode ser utilizada para regularizacao de erro ocorrido na emissao de documento fiscal, desde que o erro nao esteja relacionado com: I - as variaveis que determinam o valor do imposto tais como: base de calculo, aliquota, diferenca de preco, quantidade, valor da operacao ou da prestacao; II - a correcao de dados cadastrais que implique mudanca do remetente ou do destinatario; III - a data de emissao ou de saida.
							</td>
						</tr>
					</table>
					<span style="font-size: 8px">DACCE gerada pelo SisZTL ver. 1.2</span>';
		    
		    
		    
		    $output = $nfe->cccDir."pdf/".$nfeoriginal->chave."-".$cce->seqevento."-DACCE.pdf";
		    $dest = "F";
		    
		    $pdf = new HTML2PDF('P', 'A4', 'pt', false, 'ISO-8859-15', 2);
			$pdf->WriteHTML(utf8_decode($html));
			$pdf->Output($output, $dest); 			
			
			echo "sucessoDacce";
		}
		
		
		function gravaCce($params){
		    $bo		= new NfeModel();
		    $bocce	= new NfecceModel();
		    
		    $seq = 0;
		    $seq = count($bocce->fetchAll("status = true and id_nfe = ".$params['idnfe']));
		    $seq += 1;
		    
		    $data = array(
		    	'correcao'		=> $params['correcao'],
		        'status'		=> 0,
	            'id_nfe'		=> $params['idnfe'],
	            'seqevento'		=> $seq,
		    );
		    
		    try {
		    	$id = $bocce->insert($data);
		    	echo "idcce:".$id;		    
		    } catch (Exception $e){
		    	echo $erro = "Erro ao gravar CCE".$e->getMessage();
		    	NfeBO::gravarErrocce($erro, $params['idnfe']);
		    }		    
		}
		
		function geraCce($params){
			require_once("Nfephp/libs/ToolsNFePHP.class.php");
			$nfe 	= new ToolsNFePHP();
			$bo		= new NfeModel();
		    $bocce	= new NfecceModel();
				
		    foreach ($bo->fetchAll("id = ".$params['idnfe']) as $nfeoriginal);
		    foreach ($bocce->fetchAll("id = ".$params['idcce']) as $cce);
			
			$nfe->envCCe(
				$chNFe=$nfeoriginal->chave,
				$xCorrecao=$cce->correcao,
				$nSeqEvento=$cce->seqevento
			);
			
			if($nfe->errStatus == true):
				$boerro	= new ErrosModel();
				$dataerro = array('descricao' => $nfe->errMsg, 'pagina' => "NfeBO::geraCce(idnfe=".$params['idnfe'].")");
				$boerro->insert($dataerro);
			
				echo $nfe->errMsg;
			else:
				$data = array('status' => 1);
				$bocce->update($data,"id = ".$params['idcce']);
				echo "sucessoCcesefaz";
			endif;
		}
				
		//-- Grava erros da nfe ---------------------------------------------------
		function gravarErrocce($msg, $nf){
			$boerro	= new ErrosModel();
			$dataerro = array('descricao' => $msg, 'pagina' => "NfeBO::geraCce(".$var['idnfe'].")");
			$boerro->insert($dataerro);
				
			
		}
		
		function gerarEmailcce($params){
			require_once("Nfephp/libs/ToolsNFePHP.class.php");
			$nfe 		= new ToolsNFePHP();
			$bo 		= new NfeModel();
			$bocce		= new NfecceModel();
			
			foreach ($bo->fetchAll("id = ".$params['idnfe']) as $nfedados);
			foreach (ClientesBO::listaEmailsUp($nfedados->id_cliente,3) as $emails);
				
			foreach ($bocce->fetchAll("id = ".$params['enviaemail']) as $cce);
			
			if($emails->EMAIL!=""):
				
				$message = '
			        <table width="750" align="center" border="0" cellpadding="0" cellspacing="0"><tr><td width="100%"><a href="http://www.ztlbrasil.com.br" target="_blank">
					<font size="6" color="#1b999a" face="Arial, Helvetica, sans-serif">ztlbrasil.com.br</font></a></td></tr><tr><td>&nbsp;</td></tr><tr><td valign="top"><font size="2" color="#333333" face="Arial, Helvetica, sans-serif">
					Olá <strong>'.$emails->NOME_CONTATO.',</strong></font></td></tr><tr><td valign="top">&nbsp;</td></tr>
					<tr><td valign="top" style="text-align: justify;"><font size="2" color="#333333" face="Arial, Helvetica, sans-serif" >
				
					Você está recebendo a Carta de Correção Eletrônica número da NF-e nº '.$nfedados->id.', série '.$nfedados->serie.' da ZTL DO BRASIL IMPORTACAO EXPORTACAO E 
					COMÉRCIO LTDA.<br /><br />
					Anexo à este e-mail você está recebendo também o arquivo XML da CC-e. Este arquivo deve ser armazenado eletronicamente por sua empresa pelo prazo de 
					05 (cinco) anos, conforme previsto na legislação tributária (Art. 173 do Código Tributário Nacional e § 4º da Lei 5.172 de 25/10/1966).<br /><br />
					O DACCE em papel pode ser arquivado para apresentação ao fisco quando solicitado. Todavia, se sua empresa também for emitente de NF-e, 
					o arquivamento eletrônico do XML de seus fornecedores é obrigatório, sendo passível de fiscalização.<br /><br />
					Para se certificar que esta CC-e é válida, queira por favor consultar sua autenticidade no site nacional do projeto NF-e (www.nfe.fazenda.gov.br), 
					utilizando a chave de acesso contida no DACCE.<br /><br />
					
					Em caso de dúvidas, entre em contato com nosso Serviço de Atendimento ao Cliente, enviando e-mail para faturamento@ztlbrasil.com.br
					</font> </td></tr><tr><td valign="top">&nbsp;</td></tr><tr><td valign="top"><font size="2" color="#333333" face="Arial, Helvetica, sans-serif">
					Atenciosamente,<br />
					<b>Departamento de vendas ZTL Brasil</b><br />
					</font> </td></tr></table>';
			
				$assunto 	= "Carta de Correção Eletrônica da NF-e nº ".$nfedados->id." da ZTL Brasil";
				$resp 		= $emails->NOME_CONTATO;
				$email		= $emails->EMAIL;
				
				//$email		= "cleitonsbarbosa@gmail.com";
				NfeBO::enviaMailcce($assunto, $message, $resp, $email, $params['idnfe'], $params['enviaemail']);
			
				/* $email		= "faturamento@ztlbrasil.com.br";
				NfeBO::enviaMailcce($assunto, $message, $resp, $email, $params['idnfe'], $params['enviaemail']); */
			else:
				echo $erro = "Email NFE não cadastrado";
				NfeBO::gravarErrocce($erro, $params['idnfe']);
			endif;
		}
		
		
		function enviaMailcce($assunto, $texto, $resp, $email, $idnfe, $idcce){
			require_once("Nfephp/libs/ToolsNFePHP.class.php");
			$nfe 		= new ToolsNFePHP();
			$bo 		= new NfeModel();
			$bocce	= new NfecceModel();
			
			foreach ($bocce->fetchAll("id = ".$idcce) as $cce);
			foreach ($bo->fetchAll("id = ".$idnfe) as $nfedados);
			
			try {
				$mailTransport = new Zend_Mail_Transport_Smtp("smtp.ztlbrasil.com.br", Zend_Registry::get('mailSmtp'));
		
				//$anomes = '20'.substr($nfedados->chave,2,4);
				$docxml = file_get_contents($nfe->cccDir.$nfedados->chave."-".$cce->seqevento."-procCCe.xml");
				$docpdf = file_get_contents($nfe->cccDir."pdf/".$nfedados->chave."-".$cce->seqevento."-DACCE.pdf");
		
				$mail = new Zend_Mail('utf-8');
				$mail->setFrom("info@ztlbrasil.com.br");
				$mail->addTo($email,$resp);
				$mail->addCc('faturamento@ztlbrasil.com.br', 'Faturamento ZTL');
				$mail->setBodyHtml($texto);
				$mail->setSubject($assunto);
				$mail->createAttachment($docpdf, "pdf", Zend_Mime::DISPOSITION_INLINE, Zend_Mime::ENCODING_BASE64, "Dacce.pdf");
				$mail->createAttachment($docxml, "xml", Zend_Mime::DISPOSITION_INLINE, Zend_Mime::ENCODING_BASE64, "XML.xml");
				$mail->send($mailTransport);
		
				echo "sucessoemail";
		
			} catch (Exception $e){
				echo $erro = "Erro ao enviar Email CCE";
				NfeBO::gravarErrocce($erro, $idnfe);
			}
		}
		
		function correcaoNfe(){
		    require_once("Nfephp/libs/ToolsNFePHP.class.php");
		    $nfe 		= new ToolsNFePHP();
			$bo 		= new NfeModel();		
			$boprod 	= new NfeprodModel();			
				
		}
		
		function gravaMotivocancela($params){
		    $bonfe 		= new NfeModel();
		    $data = array('motivocanc' => $params['obscancela']);
		    
			try {
				$bonfe->update($data, "id = ".$params['nfe']);
				echo "sucessogravamotivo";	
			}catch (Zend_Exception $e){
				echo "errogravamotivo";				
			}

		    
		}
		
		function cancelarNfe($idnfe){
			require_once("Nfephp/libs/ToolsNFePHP.class.php");
			$nfe 		= new ToolsNFePHP();
			$bonfe 		= new NfeModel();
		
			foreach ($bonfe->fetchAll("id = ".$idnfe) as $notafiscal);		
		
			$id 		= $notafiscal->chave;
			$protId 	= $notafiscal->autorizacao;
			$xJust 		= $notafiscal->motivocanc;
		
			//$retorno = $nfe->cancelNF($id, $protId, $xJust);
			$retorno = $nfe->cancelEvent($id, $protId, $xJust);
						
			if($nfe->errStatus == true):
				echo $nfe->errMsg;
			else:
				$data = array('status' => 3);
				$bonfe->update($data, "id = ".$idnfe);
				
				echo "sucessocancela";
				
				/*if($retorno['bStat'] == true):
					$data = array('status' => 3);
					$bonfe->update($data, "id = ".$idnfe);
					
					echo "sucessocancela";
				else:
					echo $retorno['xMotivo'];
				endif;
				*/
			endif;
		
		} 
		
		
		function cancelarNfee(){
		    require_once("Nfephp/libs/ToolsNFePHP.class.php");
		    $nfe 		= new ToolsNFePHP();
		    		    
		    $id = "53120807555737000110550010000036981483247717"; //97
		    $protId = "353120019114714";
		    $xJust = "NFe emitida em duplicidade";
		    
		    $aary = $nfe->cancelNF($id, $protId, $xJust); 
		    
		    Zend_Debug::dump($aary);
		    
		    
		}
		
		function consultaNfe($var = ""){
			require_once("Nfephp/libs/ToolsNFePHP.class.php");
			$nfe 		= new ToolsNFePHP();
			$bonfe 		= new NfeModel();
			$bonfeprod	= new NfeprodModel();
		
			
			$chave = $var['chave']; //"53120907555737000110550010000038091957357377";
			$recibo = $var['recibo']; //53120807555737000110550010000036951421372559 -----------
			
			if(!empty($chave)):
				echo "Pelo recibo:<br />";
				$ret = $nfe->getProtocol(null,$chave,1); //consulta por chave
				print_r($ret);
			endif;
			
			echo "<br /><br />";
			
			if(!empty($recibo)):
				echo "Pela chave: <br />";
				$ret = $nfe->getProtocol($recibo, null,1); //consulta por recibo
				print_r($ret);
			endif;
		
		
		}		
		
		//--- Inutilizar nfe -----------------------------------------------------
		function inutilizarNfe($params){
		    require_once("Nfephp/libs/ToolsNFePHP.class.php");
		    $nfe 		= new ToolsNFePHP;
		    $bonfe 		= new NfeModel();
		    $aRetorno = $nfe->inutNF($params['ano'],'1',$params['ini'],$params['fim'],$params['justificativa']);
		    
		    if($aRetorno){
		        for ($i=$params['ini'];$i<=$params['fim'];$i++){
			        
			        if(count($bonfe->fetchAll("id = ".$i))>0){
			        	foreach ($bonfe->fetchAll("id = ".$i) as $notafe);
				        $array = array('status' => 2,'motivocanc' => $params['justificativa']);
				        $bonfe->update($array, "id = ".$i);
			        }else{
			        	$array = array(
			        		'id' 			=> $i,
			        		'status' 		=> 2,
			        		'motivocanc' 	=> $params['justificativa']
			        	);
			         
			        	$bonfe->insert($array);
			        }
		        }
		        
		        echo "NFe inutilizada com sucesso!";
		        
		    }else{
		        echo  $nfe->errMsg;
		    }
		    
		    Zend_Debug::dump($aRetorno);
		    echo "<br />".$nfe->errMsg;
		    
		    
		    /* if($aRetorno['cStat'] == 102):		    
			    for ($i=$params['ini'];$i<=$params['fim'];$i++):
			    	echo $i;
			    	echo "<br />"; 		    
			    	
			    	if(count($bonfe->fetchAll("id = ".$i))>0):
			    		foreach ($bonfe->fetchAll("id = ".$i) as $nfe);
			    		$array = array('status' => 2,'motivocanc' => $params['justificativa']);
			    		$bonfe->update($array, "id = ".$i);
			    	else:
				    	$array = array(
				    		'id' 			=> $i,
				    	    'status' 		=> 2,
				    	    'motivocanc' 	=> $params['justificativa']
				    	);
				    	
				    	$bonfe->insert($array);
			    	endif;
			    	
			    	
			    endfor;
			endif; */
		    
		}
		
		//--- Remessas NFe -------------------------------------------------------
		
		function remessasContabil(){
		    
		    $mes = date("m", strtotime("-1 month"));
		    $ano = date("Y", strtotime("-1 month"));
		    $ultimo_dia = date("t", mktime(0,0,0,$mes,'01',$ano));
		    
		}
		
		
		
		function listaRemessasnfe(){
		    $bo		= new NfeModel();
		    $bor	= new NferemessaModel();
		    
		    return $bor->fetchAll("id > 0","id desc");
		}
		
		function buscaRemessasnfe($params){
			$bo		= new NfeModel();
			$bor	= new NferemessaModel();
		
			return $bor->fetchAll("md5(id) = '".$params."'");
		}
				
		function gerarRemessanfe(){
		    
		    $params = array();
		    
		    //--- confeciono as datas -------------------------
		    $mes = date("m", strtotime("-1 month"));
		    $ano = date("Y", strtotime("-1 month"));
		    $ultimo_dia = date("t", mktime(0,0,0,$mes,'01',$ano));

		    $params['dataini'] = "01"."/".$mes."/".$ano;
		    $params['datafim'] = $ultimo_dia."/".$mes."/".$ano;
		    
		    $dataini = $ano."-".$mes."-"."01";
		    $datafin = $ano."-".$mes."-".$ultimo_dia;
		    
		    $mes = $mes."/".$ano;
		    
		    $bo		= new NfeModel();
		    $bor	= new NferemessaModel();
			
			if(!empty($params['dataini']) and !empty($params['datafim'])):
				
				$data = array(
					'dt_inicial' 	=> $dataini,
					'dt_final'		=> $datafin,
					'dt_geracao'	=> date("Y-m-d"),
				    'sit'			=> 0,
				    'email'			=> 'cleiton@ztlbrasil.com.br;'
				);
				
				$idremessa = $bor->insert($data);
								
				$pastaremessa 	= Zend_Registry::get('pastaPadrao')."public/nfe/remessas";
				$pastaxml 		= Zend_Registry::get('pastaPadrao')."public/nfe/nfe/producao/enviadas/aprovadas";
				$pastapdf 		= Zend_Registry::get('pastaPadrao')."public/nfe/nfe/producao/pdf";
				
				$zip = new ZipArchive();
				
				//- Entradas -----------------------------------------------------------------------------
				if(count($bo->fetchAll("status = 1 and tipo = 0 and data between '".$dataini."' and '".$datafin."'"))>0):
					//--- XML -----------------------------------------
					if ($zip->open($pastaremessa.'/Remessa_'.$idremessa.'_Nfe.zip', ZipArchive::CREATE)):
					    foreach ($bo->fetchAll("status = 1 and tipo = 0 and data between '".$dataini."' and '".$datafin."'") as $nfe):
					    	if(file_exists($pastaxml."/".$nfe->chave."-nfe.xml")):
								$zip->addFile($pastaxml."/".$nfe->chave."-nfe.xml","Entrada/xml/".$nfe->chave."-nfe.xml");
							else:
								echo "Erro ao buscar XML de entrada<br />";
							endif;					
						endforeach;					    
					else:
						echo "Erro";
					endif;

					//--- PDF -----------------------------------------
					if ($zip->open($pastaremessa.'/Remessa_'.$idremessa.'_Nfe.zip', ZipArchive::CREATE )):
						foreach ($bo->fetchAll("status = 1 and tipo = 0 and data between '".$dataini."' and '".$datafin."'") as $nfe):
							if(file_exists($pastapdf."/".$nfe->chave.".pdf")):
								$zip->addFile($pastapdf."/".$nfe->chave.".pdf","Entrada/pdf/".$nfe->chave.".pdf");
							else:
								echo "Erro ao buscar PDF de entrada<br />";
							endif;
						endforeach;
						
					else:
						echo "Erro";
					endif;
					
				else:
					echo "Sem notas de entrada emitidas<br />";
				endif;				
				
				//- Saida -----------------------------------------------------------------------------
				if(count($bo->fetchAll("status = 1 and tipo = 1 and data between '".$dataini."' and '".$datafin."'"))>0):
					//--- XML -----------------------------------------
					if ($zip->open($pastaremessa.'/Remessa_'.$idremessa.'_Nfe.zip', ZipArchive::CREATE )):
						foreach ($bo->fetchAll("status = 1 and tipo = 1  and data between '".$dataini."' and '".$datafin."'") as $nfe):
							if(file_exists($pastaxml."/".$nfe->chave."-nfe.xml")):
								$zip->addFile($pastaxml."/".$nfe->chave."-nfe.xml","Saida/xml/".$nfe->chave."-nfe.xml");
							else:
								echo "Erro ao buscar XML de saida";
							endif;
						endforeach;
						$zip->close();
					else:
						echo "Erro";
					endif;
					
					//--- PDF -----------------------------------------
					if ($zip->open($pastaremessa.'/Remessa_'.$idremessa.'_Nfe.zip', ZipArchive::CREATE )):
						foreach ($bo->fetchAll("status = 1 and tipo = 1 and data between '".$dataini."' and '".$datafin."'") as $nfe):
							if(file_exists($pastapdf."/".$nfe->chave.".pdf")):
								$zip->addFile($pastapdf."/".$nfe->chave.".pdf","Saida/pdf/".$nfe->chave.".pdf");
							else:
								echo "Erro ao buscar PDF de saida";
							endif;
						endforeach;
						$zip->close();
					else:
						echo "Erro";
					endif;
				
				else:
					echo "Sem notas de garantias emitidas";
				endif;
				
				//- Compacta notas canceladas -------------------------------------------------------------------------------
				if(count($bo->fetchAll("status = 3 and data between '".$dataini."' and '".$datafin."'"))>0):
					//--- XML --------------------------------------------------
					if ($zip->open($pastaremessa.'/Remessa_'.$idremessa.'_Nfe.zip', ZipArchive::CREATE )):
						foreach ($bo->fetchAll("status = 3 and data between '".$dataini."' and '".$datafin."'") as $nfe):
							if(file_exists($pastaxml."/".$nfe->chave."-nfe.xml")):
								$zip->addFile($pastaxml."/".$nfe->chave."-nfe.xml","Canceladas/xml/".$nfe->chave."-nfe.xml");
							else:
								echo "Erro ao buscar XML cancelado";
							endif;
						endforeach;
						$zip->close();
					else:
						echo "Erro";
					endif;
					
					//--- PDF -----------------------------------------
					if ($zip->open($pastaremessa.'/Remessa_'.$idremessa.'_Nfe.zip', ZipArchive::CREATE )):
						foreach ($bo->fetchAll("status = 3 and data between '".$dataini."' and '".$datafin."'") as $nfe):
							if(file_exists($pastapdf."/".$nfe->chave.".pdf")):
								$zip->addFile($pastapdf."/".$nfe->chave.".pdf","Canceladas/pdf/".$nfe->chave.".pdf");
							else:
								echo "Erro ao buscar PDF de saida";
							endif;
						endforeach;
						$zip->close();
					else:
						echo "Erro";
					endif;
					
				else:
					echo "Sem notas emitidas";
				endif;
				
				//- Gera MSG ------------------------------------------------------------------------------------------------
				$msg = NfeBO::gerarMsgremessanfe($params,$idremessa);	
				$data = array(
					'msg' 				=> $msg['msg'],
					'icmsapuracao' 		=> $msg['icms'],
					'ipiapuracao' 		=> $msg['ipi'],
				    'pisapuracao' 		=> $msg['pis'],
				    'cofinsapuracao' 	=> $msg['cofins'],
				    'icmsstapuracao' 	=> $msg['icmsst']
				);
				
				$bor->update($data, "id = ".$idremessa);
				
				return $idremessa;
			else:
				echo "Datas incorretas";			
			endif;
		}
		
		function gerarMsgremessanfe($params,$idremessa){
		    
		    $bo		= new NfeModel();
		    $bor	= new NferemessaModel();		    
		    
		    $this->objIcms			= FaturamentoBO::buscaImpostos("icms", $params, 1);
		    $this->objSt			= FaturamentoBO::buscaImpostos("st", $params, 1);
		    $this->objIpi			= FaturamentoBO::buscaImpostos("ipi", $params, 1);
		    $this->objPis			= FaturamentoBO::buscaImpostos("pis", $params, 1);
		    $this->objCofins		= FaturamentoBO::buscaImpostos("cofins", $params, 1);
		    	
		    $this->objIcmsent		= FaturamentoBO::buscaImpostos("icms", $params, 0);
		    $this->objStent			= FaturamentoBO::buscaImpostos("st", $params, 0);
		    $this->objIpient		= FaturamentoBO::buscaImpostos("ipi", $params, 0);
		    $this->objPisent		= FaturamentoBO::buscaImpostos("pis", $params, 0);
		    $this->objCofinsent		= FaturamentoBO::buscaImpostos("cofins", $params, 0);
		    	
		    if(!empty($params['dataini']) || !empty($params['datafim'])):
			    if(!empty($params['dataini']) and !empty($params['datafim'])):
			    	$busca = 'Período '.$params['dataini'].' à '.$params['datafim'];
			    elseif (!empty($params['dataini'])):
			   	 	$busca = 'Período '.$params['dataini'].' à '.date('d/m/Y');
			    elseif (!empty($params['datafim'])):
			    	$busca = 'Período até '.$params['datafim'];
			    endif;
		    else:
		    	$busca = 'Período mês '.date('m/Y');
		    endif;
		    
		    $msg = '
		    	<div style="font-family: Verdana">
			    	<div style="margin: 10px 0px 10px 0px; font-weight: bold; font-size: 14px">
		    		'.$busca.'
		    		</div>		    		
		    		<div style="margin: 10px 0px 10px 0px; font-weight: bold; font-size: 14px">
		    		Entrada
		    		</div>		    		
		    		<div>
		    			<div style="width: 48%;  float: left;">
		    				<div >
		    					ICMS
		    					<table style="border: 1px solid #d5d5d5; width: 100%;">
		    						<tr>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">CFOP</td>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">Alq</td>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">Fat</td>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">Valor</td>
		    						</tr>';
		    						
		    						if(count($this->objIcmsent)>0):
		    							$totalicmsent = 0;
		    							foreach ($this->objIcmsent as $icms):
		    								if($icms->alicms != 0):
		    								$msg .= '
			    								<tr>
			    									<td style="text-align: center;">'.$icms->cfopnfe.'</td>
			    									<td style="text-align: center;">'.number_format($icms->alicms,2,",",".").'</td>
			    									<td style="text-align: right;">'.number_format($icms->tbaseicms,2,",",".").'</td>
			    									<td style="text-align: right;">'.number_format($icms->tvlicms,2,",",".").'</td>
			    								</tr>';		    								
		    								$totalicmsent += $icms->tvlicms;
		    								endif;
		    							endforeach;
		    						endif;
		    						
		    						$msg .= '
		    						<tr>
		    							<td colspan="4" style="text-align: right; background-color: #ccc; font-weight: bold;">
		    							Total: '.number_format($totalicmsent,2,",",".").'</td>
		    						</tr>
		    					</table>
		    				</div>
		    				<div style="padding-top: 10px">
		    					PIS
		    					<table style="border: 1px solid #d5d5d5; width: 100%;">
		    						<tr>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">CFOP</td>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">Alq</td>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">Fat</td>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">Valor</td>
		    						</tr>';
		    						
		    						if(count($this->objPisent)>0):
		    							$totalpisent = 0;
		    							foreach ($this->objPisent as $pis):
		    								if($pis->alpis != 0):
		    								$msg .= '
		    								<tr>
		    									<td style="text-align: center;">'.$pis->cfopnfe.'</td>
		    									<td style="text-align: center;">'.number_format($pis->alpis,2,",",".").'</td>
		    									<td style="text-align: right;">'.number_format($pis->tbaseicms,2,",",".").'</td>
		    									<td style="text-align: right;">'.number_format($pis->tvlpis,2,",",".").'</td>
		    								</tr>';
		    								
		    								$totalpisent += $pis->tvlpis;
		    								endif;
		    							endforeach;
		    						endif;
		    						
		    						$msg .= '
		    						<tr>
		    							<td colspan="4" style="text-align: right; background-color: #ccc; font-weight: bold;">
		    							Total: '.number_format($totalpisent,2,",",".").'</td>
		    						</tr>
		    					</table>
		    				</div>
		    				
		    				<div style="padding-top: 10px">
		    					COFINS
		    					<table style="border: 1px solid #d5d5d5; width: 100%;">
		    						<tr>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">CFOP</td>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">Alq</td>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">Fat</td>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">Valor</td>
		    						</tr>';
		    						
		    						if(count($this->objCofinsent)>0):
		    							$totalcofinsent = 0;
		    							foreach ($this->objCofinsent as $cofins):
		    								if($cofins->alcofins != 0):
		    								$msg .= '
		    								<tr>
		    									<td style="text-align: center;">'.$cofins->cfopnfe.'</td>
		    									<td style="text-align: center;">'.number_format($cofins->alcofins,2,",",".").'</td>
		    									<td style="text-align: right;">'.number_format($cofins->tbaseicms,2,",",".").'</td>
		    									<td style="text-align: right;">'.number_format($cofins->tvlcofins,2,",",".").'</td>
		    								</tr>';
		    								
		    								$totalcofinsent += $cofins->tvlcofins;
		    								endif;
		    							endforeach;
		    						endif;
		    						
		    						$msg .= '
		    						<tr>
		    							<td colspan="4" style="text-align: right; background-color: #ccc; font-weight: bold;">
		    							Total: '.number_format($totalcofinsent,2,",",".").'</td>
		    						</tr>
		    					</table>
		    				</div>
		    				
		    			</div>
		    			<div style="width: 48%; float: right;" >
		    				<div >
		    					ICMS ST
		    					<table style="border: 1px solid #d5d5d5; width: 100%;">
		    						<tr>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">CFOP</td>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">Alq</td>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">Fat</td>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">Valor</td>
		    						</tr>';
		    						
		    						if(count($this->objStent)>0):
		    							$totalstent = 0;
		    							foreach ($this->objStent as $st):
		    								if($st->icmsst != 0):
		    								$msg .= '
		    								<tr>
		    									<td style="text-align: center;">'.$st->cfopnfe.'</td>
		    									<td style="text-align: center;">'.number_format($st->icmsst,2,",",".").'</td>
		    									<td style="text-align: right;">'.number_format($st->tbasest,2,",",".").'</td>
		    									<td style="text-align: right;">'.number_format($st->tvlicmsst,2,",",".").'</td>
		    								</tr>';
		    								
		    								$totalstent += $st->tvlicmsst;
		    								endif;
		    							endforeach;
		    						endif;
		    						$msg .= '
		    						<tr>
		    							<td colspan="4" style="text-align: right; background-color: #ccc; font-weight: bold;">
		    							Total: '.number_format($totalstent,2,",",".").'</td>
		    						</tr>
		    					</table>
		    				</div>
		    				<div style="padding-top: 10px">
		    					IPI
		    					<table style="border: 1px solid #d5d5d5; width: 100%;">
		    						<tr>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">CFOP</td>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">Alq</td>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">Fat</td>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">Valor</td>
		    						</tr>';
		    						
		    						if(count($this->objIpient)>0):
		    							$totalipient = 0;
		    							foreach ($this->objIpient as $ipi):
		    								if($ipi->alipi != 0):
		    								$msg .= '
		    								<tr>
		    									<td style="text-align: center;">'.$ipi->cfopnfe.'</td>
		    									<td style="text-align: center;">'.number_format($ipi->alipi,2,",",".").'</td>
		    									<td style="text-align: right;">'.number_format($ipi->tbaseipi,2,",",".").'</td>
		    									<td style="text-align: right;">'.number_format($ipi->tvlipi,2,",",".").'</td>
		    								</tr>';
		    								
		    								$totalipient += $ipi->tvlipi;
		    								endif;
		    							endforeach;
		    						endif;
		    						
		    						$msg .= '
		    						<tr>
		    							<td colspan="4" style="text-align: right; background-color: #ccc; font-weight: bold;">
		    							Total: '.number_format($totalipient,2,",",".").'</td>
		    						</tr>
		    					</table>
		    				</div>
		    				
		    			</div>
		    		</div>
		    		
		    		<div style="clear: both; margin: 10px 0px 10px 0px; font-weight: bold; font-size: 14px; padding-top: 30px">
		    		Saída
		    		</div>
		    		
		    		<div style="">
		    			<div style="width: 48%;  float: left;">
		    				<div >
		    					ICMS
		    					<table style="border: 1px solid #d5d5d5; width: 100%;">
		    						<tr>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">CFOP</td>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">Alq</td>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">Fat</td>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">Valor</td>
		    						</tr>';
		    						 
		    						if(count($this->objIcms)>0):
		    							$totalicms = 0;
		    							foreach ($this->objIcms as $icms):
		    								if($icms->alicms != 0):
		    								$msg .= '
		    								<tr>
		    									<td style="text-align: center;">'.$icms->cfopnfe.'</td>
		    									<td style="text-align: center;">'.number_format($icms->alicms,2,",",".").'</td>
		    									<td style="text-align: right;">'.number_format($icms->tbaseicms,2,",",".").'</td>
		    									<td style="text-align: right;">'.number_format($icms->tvlicms,2,",",".").'</td>
		    								</tr>';
		    								
		    								$totalicms += $icms->tvlicms;
		    								endif;
		    							endforeach;
		    						endif;
		    						
		    						$msg .= '
		    						<tr>
		    							<td colspan="4" style="text-align: right; background-color: #ccc; font-weight: bold;">
		    							Total: '.number_format($totalicms,2,",",".").'</td>
		    						</tr>
		    					</table>
		    				</div>
		    				
		    				<div style="padding-top: 10px">
		    					PIS
		    					<table style="border: 1px solid #d5d5d5; width: 100%;">
		    						<tr>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">CFOP</td>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">Alq</td>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">Fat</td>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">Valor</td>
		    						</tr>';
		    						
		    						if(count($this->objPis)>0):
		    							$totalpis = 0;
		    							foreach ($this->objPis as $pis):
		    								if($pis->alpis != 0):
		    								$msg .= '
		    								<tr>
		    									<td style="text-align: center;">'.$pis->cfopnfe.'</td>
		    									<td style="text-align: center;">'.number_format($pis->alpis,2,",",".").'</td>
		    									<td style="text-align: right;">'.number_format($pis->tbaseicms,2,",",".").'</td>
		    									<td style="text-align: right;">'.number_format($pis->tvlpis,2,",",".").'</td>
		    								</tr>';
		    								
		    								$totalpis += $pis->tvlpis;
		    								endif;
		    							endforeach;
		    						endif;
		    						
		    						$msg .= '
		    						<tr>
		    							<td colspan="4" style="text-align: right; background-color: #ccc; font-weight: bold;">
		    							Total: '.number_format($totalpis,2,",",".").'</td>
		    						</tr>
		    					</table>
		    				</div>
		    				
		    				<div style="padding-top: 10px">
		    					COFINS
		    					<table style="border: 1px solid #d5d5d5; width: 100%;">
		    						<tr>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">CFOP</td>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">Alq</td>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">Fat</td>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">Valor</td>
		    						</tr>';
		    						
		    						if(count($this->objCofins)>0):
		    							$totalcofins = 0;
		    							foreach ($this->objCofins as $cofins):
		    								if($cofins->alcofins != 0):
		    								$msg .= '
		    								<tr>
		    									<td style="text-align: center;">'.$cofins->cfopnfe.'</td>
		    									<td style="text-align: center;">'.number_format($cofins->alcofins,2,",",".").'</td>
		    									<td style="text-align: right;">'.number_format($cofins->tbaseicms,2,",",".").'</td>
		    									<td style="text-align: right;">'.number_format($cofins->tvlcofins,2,",",".").'</td>
		    								</tr>';
		    								
		    								$totalcofins += $cofins->tvlcofins;
		    								endif;
		    							endforeach;
		    						endif;
		    						
		    						$msg .= '
		    						<tr>
		    							<td colspan="4" style="text-align: right; background-color: #ccc; font-weight: bold;">
		    							Total: '.number_format($totalcofins,2,",",".").'</td>
		    						</tr>
		    					</table>
		    				</div>				
		    			</div>
		    			<div style="width: 48%; float: right;" >
		    				<div >
		    					ICMS ST
		    					<table style="border: 1px solid #d5d5d5; width: 100%;">
		    						<tr>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">CFOP</td>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">Alq</td>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">Fat</td>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">Valor</td>
		    						</tr>';
		    						
		    						if(count($this->objSt)>0):
		    							$totalst = 0;
		    							foreach ($this->objSt as $st):
		    								if($st->icmsst != 0):
		    								$msg .= '
		    								<tr>
		    									<td style="text-align: center;">'.$st->cfopnfe.'</td>
		    									<td style="text-align: center;">'.number_format($st->icmsst,2,",",".").'</td>
		    									<td style="text-align: right;">'.number_format($st->tbasest,2,",",".").'</td>
		    									<td style="text-align: right;">'.number_format($st->tvlicmsst,2,",",".").'</td>
		    								</tr>';
		    								
		    								$totalst += $st->tvlicmsst;
		    								endif;
		    							endforeach;
		    						endif;
		    						
		    						$msg .= '
		    						<tr>
		    							<td colspan="4" style="text-align: right; background-color: #ccc; font-weight: bold;">
		    							Total: '.number_format($totalst,2,",",".").'</td>
		    						</tr>
		    					</table>
		    				</div>
		    			
		    				<div style="padding-top: 10px">
		    					IPI
		    					<table style="border: 1px solid #d5d5d5; width: 100%;">
		    						<tr>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">CFOP</td>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">Alq</td>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">Fat</td>
		    							<td style="text-align: center; background-color: #ccc; font-weight: bold;">Valor</td>
		    						</tr>';
		    						
		    						if(count($this->objIpi)>0):
		    							$totalipi = 0;
		    							foreach ($this->objIpi as $ipi):
		    								if($ipi->alipi != 0):
		    								
		    								$msg .= '
		    								<tr>
		    									<td style="text-align: center;">'.$ipi->cfopnfe.'</td>
		    									<td style="text-align: center;">'.number_format($ipi->alipi,2,",",".").'</td>
		    									<td style="text-align: right;">'.number_format($ipi->tbaseipi,2,",",".").'</td>
		    									<td style="text-align: right;">'.number_format($ipi->tvlipi,2,",",".").'</td>
		    								</tr>';
		    								
		    								$totalipi += $ipi->tvlipi;
		    								endif;
		    							endforeach;
		    						endif;
		    						
		    						$msg .= '
		    						<tr>
		    							<td colspan="4" style="text-align: right; background-color: #ccc; font-weight: bold;">
		    							Total: '.number_format($totalipi,2,",",".").'</td>
		    						</tr>
		    					</table>
		    				</div>		
		    			</div>		
		    		</div>				
		    	</div>';


    			$mes = date("Y-m", strtotime("-1 month"));
    			foreach ($bor->fetchAll("dt_inicial like '".$mes."%'") as $remessaant);
		    	
		    	if($remessaant->icmsapuracao > 0) $creditoicms = $remessaant->icmsapuracao;
		    	else $creditoicms = 0;
		    	
		    	if($remessaant->ipiapuracao > 0) $creditoipi = $remessaant->ipiapuracao;
		    	else $creditoipi = 0;
		    	
		    	$totalicms 		= $totalicms*(-1);
		    	$totalipi  		= $totalipi*(-1);
		    	
		    	$icmsapurado 	= $totalicms + ($totalicmsent+$creditoicms);
		    	$ipiapurado 	= $totalipi + ($totalipient+$creditoipi);
		    	$pisapurado 	= ($totalpis+$totalpisent)*(-1);
		    	$cofinsapurado 	= ($totalcofins+$totalcofinsent)*(-1);
		    	$icmsstapurado 	= ($totalstent+$totalst)*(-1); 
		    	
		    	$msg .= '
		    	    <div class="clear"></div>
    				<div style="border: 1px solid #d5d5d5; padding: 10px; margin-top: 10px">
    					<div style="width: 24%; float: left; margin-right: 2%">
    						<table style="width: 100%; border: 1px solid #d5d5d5;">
    						<tr><td colspan="2" style="background-color: #ccc">ICMS</td></tr>
    						<tr><td >Saída</td><td style="text-align: right;">'.number_format($totalicms,2,",",".").'</td></tr>
    						<tr><td >Entrada</td><td style="text-align: right;">'.number_format($totalicmsent,2,",",".").'</td></tr>
    						<tr><td >Crédito</td><td style="text-align: right;">'.number_format($creditoicms,2,",",".").'</td></tr>
    						<tr><td style="background-color: #ccc">Total</td><td style="text-align: right; background-color: #ccc">'.number_format($totalicms+($totalicmsent+$creditoicms),2,",",".").'</td></tr>
    						</table>
    					</div>
    					<div style="width: 24%; float: left; margin-right: 2%">
    						<table style="width: 100%; border: 1px solid #d5d5d5; ">
    						<tr><td colspan="2" style="background-color: #ccc">IPI</td></tr>
    						<tr><td >Saída</td><td style="text-align: right;">'.number_format($totalipi,2,",",".").'</td></tr>
    						<tr><td >Entrada</td><td style="text-align: right;">'.number_format($totalipient,2,",",".").'</td></tr>
    						<tr><td >Crédito</td><td style="text-align: right;">'.number_format($creditoipi,2,",",".").'</td></tr>
    						<tr><td style="background-color: #ccc">Total</td><td style="text-align: right; background-color: #ccc">'.number_format($totalipi+($totalipient+$creditoipi),2,",",".").'</td></tr>
    					</table>
    					</div>
    						<div style="width: 23%; float: left;">
    						<table style="width: 100%; border: 1px solid #d5d5d5;">
    						<tr><td colspan="2" style="background-color: #ccc">PIS</td></tr>
    						<tr><td >Saída</td><td style="text-align: right;">-'.number_format($totalpis,2,",",".").'</td></tr>
    						<tr><td >Entrada</td><td style="text-align: right;">-'.number_format($totalpisent,2,",",".").'</td></tr>
    						<tr><td style="background-color: #ccc">Total</td><td style="text-align: right; background-color: #ccc">-'.number_format($totalpis+$totalpisent,2,",",".").'</td></tr>
    						</table>
    					</div>
    					<div style="width: 23%; float: right;">
    						<table style="width: 100%; border: 1px solid #d5d5d5;">
    						<tr><td colspan="2" style="background-color: #ccc">COFINS</td></tr>
    						<tr><td >Saída</td><td style="text-align: right;">-'.number_format($totalcofins,2,",",".").'</td></tr>
    						<tr><td >Entrada</td><td style="text-align: right;">-'.number_format($totalcofinsent,2,",",".").'</td></tr>
    						<tr><td style="background-color: #ccc">Total</td><td style="text-align: right; background-color: #ccc">-'.number_format($totalcofins+$totalcofinsent,2,",",".").'</td></tr>
    						</table>
    					</div>
    						
    					<div class="clear"></div>
    						
    				</div>
		    	';	
		    	
		    	/* $pesq['dtini'] = $params['dataini'];
		    	$pesq['dtfim'] = $params['datafim'];
		    	
		    	//-- Estoque --------------------------------------------------------------------
		    	$cmvestoque = EstoqueBO::calcularValorcmv($pesq);
		    		
		    	$cmvestoque = $cmvestoque*(-1);
		    	
		    	$msg .= '
		    	<div class="clear"></div>
		    	<div style="border: 1px solid #d5d5d5; padding: 10px; margin-top: 10px">
		    		<b>Estoque inicial + Compras - Estoque final</b><br>R$ '.number_format($cmvestoque,2,",",".").
		    	'</div>'; */

		    	
		    	/* //-- Duplicatas descontadas -----------------------------------------------------
		    	$msg .= '
		    	<div class="clear"></div>
		    	<div style="border: 1px solid #d5d5d5; padding: 10px; margin-top: 10px">'.FinanceiroBO::buscaDuplicatasremessa($pesq).'</div>';
		    	 */
    			$msg .= '					
    				<div class="clear"></div>
    				<div style="clear: both; padding-top: 10px;">
    					<div style="clear: both; border: 1px solid #ccc; padding: 10px; margin-top: 10px">
    					<a href="http://ztlbrasil.com.br/public/nfe/remessas/Remessa_'.$idremessa.'_Nfe.zip">Anexo da Remessa '.$idremessa.'</a><br />
    					<a href="http://ztlbrasil.com.br/index/listanfeexcel/remessa/'.md5($idremessa).'">Relação das NFe da Remessa '.$idremessa.'</a>
    				</div>	
    				</div>';
    			
    			
			return array('msg' => $msg, 'icms' => $icmsapurado, 'ipi' => $ipiapurado, 'pis' => $pisapurado, 'cofins' => $cofinsapurado, 'icmsst' => $icmsstapurado);
		}
		
		function mailRemessanfe($params){
		    $bo		= new NfeModel();
		    $bor	= new NferemessaModel();
		    
		    $data = array(
		    	'email' => $params['email'],
		        'sit'	=> 1	
		    );
		    
		    $bor->update($data, "id = ".$params['idremessa']);
		    
		    foreach ($bor->fetchAll('id = '.$params['idremessa']) as $remessa);
		    
		    $email = explode(";", $remessa->email);
		    
		    $assunto 	= "Remessa de XML NFe";
		    $texto   	= $remessa->msg;
		    $resp		= "Escricontal";
		    for ($i=0;$i<count($email);$i++):
		    	$emailenv = str_replace(";", "", $email[$i]); 
		    	$ret = DiversosBO::enviaMail($assunto, $texto, $resp, $emailenv);
		    endfor;
		    
		}
		
		function gerarlistanfeExcel($remessa){
		    $bo	= new NfeModel();
		    		    
		    ?>
		    <table >
		    	<tr>
		    		<td>&nbsp;</td>
		    		<td colspan="8" align="center">AUTORIZADAS</td>
		    	</tr>
		    	<tr>
		    		<td>&nbsp;</td>
		    		<td align="center">NFE</td>
		    		<td align="center">DATA</td>
		    		<td align="center">TIPO</td>
		    		<td align="center">ICMS</td>
		    		<td align="center">ICMSST</td>
		    		<td align="center">IPI</td>
		    		<td align="center">PIS</td>
		    		<td align="center">COFINS</td>
		    	</tr>
			    <?php 
			    $where = "data between '".$remessa->dt_inicial."' and '".$remessa->dt_final."' and status = 1";
			    
			    foreach ($bo->fetchAll($where) as $nfe):
			    	?>
			    	<tr>
			    		<td>&nbsp;</td>
			    		<td style="border: 1px solid;">NFe<?=substr("00000".$nfe->id,-6,6)?></td>
			    		<td><?=$nfe->data?></td>
			    		<td><?php if($nfe->tipo == 0) echo "ENTRADA"; else echo "SAIDA"?></td>
			    		<td><?=number_format($nfe->vlicms,2,",",".")?></td>
			    		<td><?=number_format($nfe->vlst,2,",",".")?></td>
			    		<td><?=number_format($nfe->totalipi,2,",",".")?></td>
			    		<td><?=number_format($nfe->totalpis,2,",",".")?></td>
			    		<td><?=number_format($nfe->totalcofins,2,",",".")?></td>		    		
			    	</tr>
			    	<?php		    	
			    endforeach;
			    ?>
			    <tr>
			    	<td>&nbsp;</td>
		    		<td colspan="8">&nbsp;</td>
		    	</tr>
		    	<tr>
		    		<td>&nbsp;</td>
		    		<td colspan="8" align="center">CANCELADAS</td>
		    	</tr>
		    	<?php 
		    	$where = "data between '".$remessa->dt_inicial."' and '".$remessa->dt_final."' and status = 3";
		    	
			    foreach ($bo->fetchAll($where) as $nfe):
			    	?>
			    	<tr>
			    		<td>&nbsp;</td>
			    		<td >NFe<?=substr("00000".$nfe->id,-6,6)?></td>
			    		<td ><?=$nfe->data?></td>
			    		<td ><?php if($nfe->tipo == 0) echo "ENTRADA"; else echo "SAIDA"?></td>
			    		<td ><?=number_format($nfe->vlicms,2,",",".")?></td>
			    		<td ><?=number_format($nfe->vlst,2,",",".")?></td>
			    		<td ><?=number_format($nfe->totalipi,2,",",".")?></td>
			    		<td ><?=number_format($nfe->totalpis,2,",",".")?></td>
			    		<td ><?=number_format($nfe->totalcofins,2,",",".")?></td>		    		
			    	</tr>
			    	<?php		    	
			    endforeach;
			    ?>
			</table>
			<?php 
		}
		
		function gravarDadosnfecomplementar($params){
			$bonfe		= new NfeModel();
			$bonfeprod	= new NfeprodModel();
				
			foreach ($bonfe->fetchAll("id = ".$params['nfe']) as $nfe);
				
			$icms 		= str_replace(",", ".", str_replace(".", ",",$params['icms']));
			$cofins		= str_replace(",", ".", str_replace(".", ",",$params['cofins']));
			$pis		= str_replace(",", ".", str_replace(".", ",",$params['pis']));
			$icmsst  	= str_replace(",", ".", str_replace(".", ",",$params['icmsst']));
			$ipi		= str_replace(",", ".", str_replace(".", ",",$params['ipi'])); 	
			
			$totalnfe = $icmsst;
			
			if($icmsst != ""):
				$csticms = "10";
			else:
				$csticms = "00";
			endif;
			
			/* $natop	= $nfe->naturezaop;
			
			if($icms!="") 	$natop = "Complemento de ICMS";
			if($icmsst!="") $natop = "Complemento de ICMS ST";
			if($pis!="") 	$natop = "Complemento de PIS";
			if($cofins!="") $natop = "Complemento de COFINS";
			if($ipi!="") 	$natop = "Complemento de IPI"; */
			
			//-- Dados da NFe ------------------------------------
			$datanfe = array(
				'serie'					=> 1,
				'data'					=> date('Y-m-d'),
				'data_saida'			=> date('Y-m-d H:i:s'),
				'cfop'					=> $nfe->cfop,
				'naturezaop'			=> $params['descricao'], 
				'tipo'					=> $nfe->tipo,
				'finalidade'			=> 2,
				'id_cliente'			=> $nfe->id_cliente,
				'cnpj'					=> $nfe->cnpj,
				'inscricao'				=> $nfe->inscricao,
				'empresa'				=> $nfe->empresa,
				'endereco'				=> $nfe->endereco,
				'numero'				=> $nfe->numero,
				'bairro'				=> $nfe->bairro,
				'cep'					=> $nfe->cep,
				'codcidade'				=> $nfe->codcidade,
				'cidade' 				=> $nfe->cidade,
				'uf'					=> $nfe->uf,
				'fone'					=> $nfe->fone,
				'tipofrete'				=> 9,
				'obs'					=> $params['obs'],
				'frete'					=> 0,
				'freteperc'				=> 0,
				'seguro'				=> 0,
				'desconto'				=> 0,
				'descontoperc'			=> 0,
				'outrasdesp'			=> 0,
				'baseicms'				=> 0,
				'vlicms'				=> $icms,
				'basest'				=> 0,
				'vlst'					=> $icmsst,
				'totalipi'				=> $ipi,
				'totalpis'				=> $pis,
				'totalcofins'			=> $cofins,
				'totalprodutos'			=> 0,
				'totalnota'				=> $totalnfe,
				'id_nfecomplementa'		=> $params['nfe'],
				'chavecomplementa'		=> $nfe->chave				
			);
				
			try {
				$idnfe = $bonfe->insert($datanfe);
			}catch (Zend_Exception $e){
				$boerro	= new ErrosModel();
				$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "NfeBO::gravarDadosnfecomplementar(ped=".$params[ped].")");
				$boerro->insert($dataerro);
			}
			
			if($ipi != "") $csttpipi = "Trip";
			
			foreach ($bonfeprod->fetchAll("id_nfe = ".$params['nfe']) as $listProd);
			
			$dataprod = array(
				'id_nfe'		=> $idnfe,
				'codigo'		=> "0001",
				'descricao'		=> $params['descricao'],
				'ncm'			=> $listProd->ncm,
				'ncmex'			=> $listProd->ncmex,
				'qt'			=> 0,
				'preco'			=> 0,
				'alicms'		=> 0,
				'baseicms'		=> 0,
				'vlicms'		=> $icms,
				'csticms'		=> $csticms,
				'alipi'			=> 0,
				'vlipi'			=> $ipi,
				'cstipi'		=> "50",
				'origem'		=> $listProd->origem,
				'unidade'		=> $listProd->unidade,
				'codean'		=> $listProd->codean,
				'basest'		=> 0,
				'mvast'			=> 0,
				'icmsst'		=> 0,
				'vlicmsst'		=> $icmsst,
				'desconto'		=> 0,
				'frete'			=> 0,
				'cstpis'		=> "01",
				'alpis'			=> 0,
				'vlpis'			=> $pis,
				'cstcofins'		=> "01",
				'alcofins'		=> 0,
				'vlcofins'		=> $cofins,
				'csttpipi'		=> $csttpipi,
				'csttppis'		=> "Aliq",
				'csttpcofins'	=> "Aliq",
			);
		
			
			$bonfeprod->insert($dataprod);			
		
			
			echo "sucessodados:".$idnfe;
			
		}
		
		function gerarnfeavulsaEntrada($params){
			try{
				$bonfe			= new NfeModel();
				$bonfeprd		= new NfeprodModel();
				$bonfetmp		= new NfetmpModel();
				$botmpprod		= new NfeprodtmpModel();
					
				foreach ($bonfe->fetchAll("id = ".$params['nfe']) as $nfe);			
				
				//-- Dados da NFe ------------------------------------
				$datanfe = array(
					'serie'					=> $nfe->serie,
					'data'					=> date('Y-m-d'),
					'data_saida'			=> date('Y-m-d H:i:s'),
					'cfop'					=> $nfe->cfop,
					'naturezaop'			=> $nfe->naturezaop,
					'tipo'					=> 0,
					'cnpj'					=> $nfe->cnpj,
					'inscricao'				=> $nfe->inscricao,
					'empresa'				=> $nfe->empresa,
					'endereco'				=> $nfe->endereco,
					'numero'				=> $nfe->numero,
					'bairro'				=> $nfe->bairro,
					'cep'					=> $nfe->cep,
					'codcidade'				=> $nfe->codcidade,
					'cidade' 				=> $nfe->cidade,
					'codpais'				=> 1058,
					'pais' 					=> 'Brasil',
					'uf'					=> $nfe->uf,
					'fone'					=> $nfe->fone,
					'baseicms'				=> $nfe->pesoliquido,
					'vlicms'				=> $nfe->vlicms,
					'basest'				=> $nfe->basest,
					'vlst'					=> $nfe->vlst,
					'frete'					=> $nfe->frete,
					'freteperc'				=> $nfe->freteperc,
					'seguro'				=> $nfe->seguro,
					'desconto'				=> $nfe->desconto,
					'descontoperc'			=> $nfe->descontoperc,
					'outrasdesp'			=> $nfe->outrasdesp,
					'totalipi'				=> $nfe->totalipi,
					'totalnota'				=> $nfe->totalnota,
					'transportadora'		=> $nfe->transportadora,
					'tipofrete'				=> $nfe->tipofrete,
					'transantt'				=> $nfe->transantt,
					'transplaca'			=> $nfe->transplaca,
					'transufplaca'			=> $nfe->transufplaca,
					'transcnpj'				=> $nfe->transcnpj,
					'transendereco'			=> $nfe->transendereco,
					'transcidade' 			=> $nfe->transcidade,
					'transie'				=> $nfe->transie,
					'transuf'				=> $nfe->transuf,
					'quantidade'			=> $nfe->quantidade,
					'especie'				=> $nfe->especie,
					'pesobruto'				=> $nfe->pesobruto,
					'pesoliquido'			=> $nfe->pesoliquido,
					'marca'					=> 'ZTL',				
					'email'					=> 'nfe@ztlbrasil.com.br',
					'contato'				=> 'ZTL do Brasil',
					'nivel'					=> 1,
					'status'				=> 0,
					'totalpis'				=> $nfe->totalpis,
					'totalii'				=> $nfe->totalii,
					'totalcofins'			=> $nfe->totalcofins,
					'totalprodutos'			=> $nfe->totalprodutos,
					'obs'					=> "Devolucao da NFe".substr("000000".$params['nfe'],-6,6),
					'movestoque'			=> 2
					
				);

				$idnfe = $bonfetmp->insert($datanfe);
				
				foreach ($bonfeprd->fetchAll("id_nfe = ".$params['nfe']) as $nfeprod){
					$dataprod = array(
						'id_nfe'		=> $idnfe,
						'codigo'		=> $nfeprod->codigo,
						'id_prod'		=> $nfeprod->id_prod,
						'descricao'		=> $nfeprod->descricao,
						'ncm'			=> $nfeprod->ncm,
						'ncmex'			=> $nfeprod->ncmex,
						'cfop'			=> $nfeprod->cfop,
						'qt'			=> $nfeprod->qt,
						'preco'			=> $nfeprod->preco,
						'alicms'		=> $nfeprod->alicms,
						'baseicms'		=> $nfeprod->baseicms,
						'vlicms'		=> $nfeprod->vlicms,
						'csticms'		=> $nfeprod->csticms,
						'alipi'			=> $nfeprod->alipi,
						'vlipi'			=> $nfeprod->vlipi,
						'cstipi'		=> $nfeprod->cstipi,
						'origem'		=> $nfeprod->origem,
						'unidade'		=> $nfeprod->unidade,
						'codean'		=> $nfeprod->codean,
						'basest'		=> $nfeprod->basest,
						'mvast'			=> $nfeprod->mvast,
						'icmsst'		=> $nfeprod->icmsst,
						'vlicmsst'		=> $nfeprod->vlicmsst,
						'desconto'		=> $nfeprod->desconto,
						'alii'			=> $nfeprod->alii,
						'baseii'		=> $nfeprod->baseii,
						'vlii'			=> $nfeprod->vlii,
						'frete'			=> $nfeprod->frete,
						'cstpis'		=> $nfeprod->cstpis,
						'alpis'			=> $nfeprod->alpis,
						'vlpis'			=> $nfeprod->vlpis,
						'cstcofins'		=> $nfeprod->cstcofins,
						'alcofins'		=> $nfeprod->alcofins,
						'vlcofins'		=> $nfeprod->vlcofins,
						'csttpipi'		=> $nfeprod->csttpipi,
						'csttppis'		=> $nfeprod->csttppis,
						'csttpcofins'	=> $nfeprod->csttpcofins,
						'dinumadicao'	=> $nfeprod->dinumadicao,
						'dinumseq'		=> $nfeprod->dinumseq,
						'dicodfab'		=> $nfeprod->dicodfab,
						'vladuaneiro'	=> $nfeprod->vladuaneiro
					);
				
					$botmpprod->insert($dataprod);

				}

				return $idnfe;
			}catch (Zend_Exception $e){
				$boerro	= new ErrosModel();
				$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "NfeBO::gerarnfeavulsaEntrada(nfe=".$params[nfe].")");
				$boerro->insert($dataerro);
			}			
		}
		
		
		function verificaRetornonfesefazmultiplo(){
			require_once("Nfephp/libs/ToolsNFePHP.class.php");
			$nfe 		= new ToolsNFePHP();
			$bo 		= new NfeModel();
				
			//estabelece condição inicial do retorno
			$recibo = '';
			foreach ($bo->fetchAll("status != 1") as $nfedados){
		
				//---5 passo: Veriricar se autorizada, rejeitada ou reprovada -----------------
				//condição inicial da variável de retorno
				$aRetorno = array(0=>array('cStat'=>'','xMotivo'=>'','nfepath'=>''));
				$n = 0;
				
				if(empty($nfedados->chave)){
					//caso não exista então buscar pela chave da NFe
					//$aRet = $nfe->getProtocol('',$nfedados->chave,1,$nfe->modSOAP);
						
					echo $nfedados->id."<br />";
					//Zend_Debug::dump($aRet);
				}
			}
		}
		
	}
?>
                               