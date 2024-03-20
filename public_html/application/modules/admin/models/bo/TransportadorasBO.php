<?php
	class TransportadorasBO{		
		 function listarEstados(){
			$obj = new EstadosModel();
			return $obj->fetchAll();			
		}
		
		function cadastraTransportadoras($params){
			$bo = new TransportadorasModel();
						
			$array['dt_cadastro']  		= date("Y-m-d H:i:s");
			$array['nome']        		= $params['emp'];
			$array['endereco']     		= $params['end'];
			$array['cidade']    		= $params['cid'];
			$array['uf']    			= $params['uf'];
			$array['cep'] 				= $params['cep'];
			$array['contato'] 			= $params['contato'];
			$array['telefone'] 			= $params['tel'];
			$array['fax'] 				= $params['fax'];
	        $array['sit']	  			= true;
			
	        if(empty($params['id'])){
				$idcli = $bo->insert($array);
				return $idcli;
	        }else{
				$bo->update($array,'id_transacoes='.$params['id_transacoes']);
				return $params['id_transacoes'];
	        }
		}
		
		 function cadastraRotaTransportadoras($params){
			$bo = new RotaModel();
						
			$array['id_transportadoras']  	= $params['emp'];
			$array['tipo']        			= $params['cidtipo'];
			$array['uf']        			= $params['uf'];
			$array['percentual']     		= str_replace(",",".",str_replace(".","",$params['perc']));
			$array['frete_minimo']    		= str_replace(",",".",str_replace(".","",$params['minimo']));
			$array['gatilho']    			= str_replace(",",".",str_replace(".","",$params['gatilho']));
			$array['sit']	  				= true;
			
	        if(empty($params['id'])){
				$bo->insert($array);
				return $params['emp'];
	        }else{
				$bo->update($array,'id_transacoes='.$params['id_transacoes']);
				return $params['id_transacoes'];
	        }
		}
		
		 function listarTransportadoras($params){
			$obj = new TransportadorasModel();
			if(empty($params)){
				return $obj->fetchAll();
			}else{
				return $obj->fetchAll("id = ".$params);
			}
					
		}
		
		 function listarRotas($params){
			$obj = new RotaModel();
			
			if(empty($params)){
				return $obj->fetchAll();
			}else{
				return $obj->fetchAll("id_transportadoras = ".$params." and sit = true ");
			}	
		}
		
		 function remRotas($params){
			$bo 			= new RotaModel();
			$array['sit'] 	= false;
			
			$bo->update($array,'id = '.$params);
								
		}
		
		 function calculaRotas($pesq){
			$params = array (
			    'host'     => '127.0.0.1',
			    'username' => 'ztlrolamentos',
			    'password' => 'BdMySql2008',
			    'dbname'   => 'ztlrolamentos'
			);
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_transportadoras','*'),
			        array('t.nome','r.tipo','r.percentual','r.frete_minimo','r.gatilho','r.uf as estado'))
			        ->join(array('r'=>'tb_transportadoras_rota'),
			        't.id = r.id_transportadoras')
			        ->where("t.sit = true and r.sit = true and r.uf = '".$pesq[uf]."' and r.tipo = '".$pesq[cidtipo]."' ")
			        ->order('t.nome','asc');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
								
		}
		
		
		
		//------ Garantias -----------------------------------------------------------
		
		
		//--Grava Garantia-------
		 function gravaGarantia($params){
			$bo 		= New GarantiaModel();
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
        							 
			if(empty($params["cliente"])) $id_cliente = $usuario->ID;
			else $id_cliente = $params["cliente"];
			
			$array['id']				= $params["numgarantia"];
			$array['data_atualizacao'] 	= date("Y-m-d H:i:s");
			$array['sit']				= true;
			$array['obs']				= $params["obs"];
			$array['id_user']			= $usuario->ID;
			$array['id_clientes']		= $id_cliente;
			$array['nota_fiscal']		= $params["ntfiscal"];
			$array['data_nf']			= substr($params["dt_exp"],6,4).'-'.substr($params["dt_exp"],3,2).'-'.substr($params["dt_exp"],0,2);
			$array['cfop'	]			= $params["cfop"];
			$array['peso_nf']			= str_replace(",",".",$params["peso"]);
			$array['volumes']			= $params["volumes"];
			$array['status']			= "REMETIDO A ZTL";
			$array['tipoenvio']			= $params["tipoenvio"];
			$array['data_valpac']		= substr($params["dt_val"],6,4).'-'.substr($params["dt_val"],3,2).'-'.substr($params["dt_val"],0,2);
			
			if($params["tipoenvio"]==1) $array['valorenvio']	= $params["pac"];
			else $array['valorenvio']	= $params["transportadora"];
			
			if(empty($params[idgarantia])):
				//d$array['data_cad']			= date("Y-m-d H:i:s");
				$array['data_cad']			= substr($params["dt_cad"],6,4).'-'.substr($params["dt_cad"],3,2).'-'.substr($params["dt_cad"],0,2); 
				$idcli = $bo->insert($array);
			else: 
				$bo->update($array,"id = ".$params[idgarantia]);
				$idcli = $params[idgarantia];
			endif;
			
		 	$transferencia = new Zend_File_Transfer_Adapter_Http();
					  	
			$name = $transferencia->getFileInfo();
		  	
		  	if($name):
		    	foreach ($name as $val){
			        $fname=$val['name'];
		    	}
			    $exts = split("[/\\.]", $fname) ;
			    $n = count($exts)-1;
			    $exts = $exts[$n];
		    
			  	$transferencia->addFilter('Rename', array('target' => 'nfgarantias/'.$idcli.'.'.$exts,
	                     'overwrite' => true));
				$transferencia->receive();
				
				if(!empty($exts)):
				  	$array_up[anexo]	= $exts;
				  	$bo->update($array_up,"id = ".$idcli);
				endif;
		  	endif;
			
		  	$bohis 		= new GarantiahistoricoModel();
			$arrayu['data']				= date("Y-m-d H:i:s");
			$arrayu['status']			= "REMETIDO A ZTL";
			$arrayu['id_garantiaztl']	= $idcli;
			$arrayu['id_user']			= $usuario->ID;
						
			$bohis->insert($arrayu);
			
			LogBO::cadastraLog("Estoque/Garantia",2,$usuario->ID,$idcli,"GARANTIA G".substr("000000".$idcli,-6,6));
			
			foreach (ClientesBO::listaEmailsUp($id_cliente,4) as $listMail);
			$email 	= $listMail->EMAIL;
			$resp	= $listMail->NOME_CONTATO;
			
			if($params["tipoenvio"]==1):
				$texto_ztl = '<style>
						body {
							margin-left: 0px;
							margin-top: 0px;
							margin-right: 0px;
							margin-bottom: 0px;
							color: #666666;
							font: 11px Arial, Helvetica, sans-serif;
						}
						
						</style>
						
						<body>
						<table width="600px" height="200" style="border: 1px solid #000; margin-top: 10px; " cellpadding="4px" align="center" >
						<tr>
							<td align="center" colspan="4">
								<b>Solicita&ccedil;&atilde;o N&ordm; G'.substr("000000".$idcli,-6,6).'</b><br>
							</td>
						</tr>
						<tr>
							<td align="center" colspan="4">
								Autoriza&ccedil;&atilde;o de envio para an&aacute;lise de garantia
							</td>
						</tr>
						<tr>
							<td align="left" colspan="4">
								
							Boa Tarde, <br> Segue abaixo o c&oacute;digo de autoriza&ccedil;&atilde;o de Postagem por PAC 
							referente a Nota Fiscal: <b>'.$params["ntfiscal"].'</b><br><br>
							
							
							C&oacute;digo da Autoriza&ccedil;&atilde;o de Postagem: <b>'.$params["pac"].'</b><br>
							Data de Validade: <b>'.$params["dt_val"].'</b><br>
							Servi&ccedil;o de Encomenda: <b>PAC Reverso</b><br>
							Remetente autorizado: <b>'.$resp.'</b> <br><br>
							
							Destinat&aacute;rio:<br>
							<b>ZTL DO BRASIL</b><br>
							QI 8 45/48 , Setor Industrial - Taguatinga - DF <br>
							CEP: 72135-080<br>
							</td>
							</tr>
							<tr>
							<td align="center">
								
								Acompanhe sua garantia <a target="_blanc" href="http://www.ztlbrasil.com.br/admin/venda/garantiasview/garantia/'.md5($idcli).'"> clicando aqui </a>
								<br>(&Eacute; necess&aacute;rio estar logado no sistema)
							</td>
						</tr>
						<tr>
							<td align="center" colspan="4">
								<a href="http://www.ztlbrasil.com.br" >www.ztlbrasil.com.br</a> 
							</td>
						</tr>
						
						</table>
						</body>';
			
				else:
				
					$texto_ztl = '<style>
						body {
							margin-left: 0px;
							margin-top: 0px;
							margin-right: 0px;
							margin-bottom: 0px;
							color: #666666;
							font: 11px Arial, Helvetica, sans-serif;
							
						}
						
						</style>
						
						<body>
						<table width="600px" height="200" style="border: 1px solid #000; margin-top: 10px; " cellpadding="4px" align="center" >
						<tr>
							<td align="center" colspan="4">
								<b>Solicita&ccedil;&atilde;o N&ordm; G'.substr("000000".$idcli,-6,6).'</b><br>
							</td>
						</tr>
						<tr>
							<td align="center" colspan="4">
								Autoriza&ccedil;&atilde;o de envio para an&aacute;lise de garantia
							</td>
						</tr>
						<tr>
							<td align="left" colspan="4">
								
							Boa Tarde, <br> Informamos que ser&aacute; realizado a coleta dos produtos a serem enviados para a an&aacute;lise de garantia,
							referente a Nota Fiscal <b>3418</b>, pela transportadora <b>'.$params["transportadora"].'</b>.<br><br>
							
							</td>
							</tr>
							<tr>
							<td align="center">
								
								Acompanhe sua garantia <a target="_blanc" href="http://www.ztlbrasil.com.br/admin/venda/garantiasview/garantia/'.md5($idcli).'"> clicando aqui </a>
								<br>(&Eacute; necess&aacute;rio estar logado no sistema)
							</td>
						</tr>
						<tr>
							<td align="center" colspan="4">
								<a href="http://www.ztlbrasil.com.br" >www.ztlbrasil.com.br</a> 
							</td>
						</tr>
						
						</table>
						</body>';
				
				endif;
				
				if(!empty($email)):
					try {
						$mail = new Zend_Mail();
						$mail->setBodyHtml($texto_ztl);
						$mail->setFrom('garantias@ztlbrasil.com.br', 'Departamento de garantias ZTL');
						$mail->addTo($email, $resp);
						$mail->setSubject('Garantias ZTL');
						$mail->send();
					} catch (Exception $e){
						echo ($e->getMessage());
					}
					
				endif;		 				
				
		}
		
		//--Grava Garantia-------
		 function gravaGarantiacli($params){
			$bo 		= New GarantiaModel();
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
        							 
			if(empty($params["cliente"])) $id_cliente = $usuario->ID;
			else $id_cliente = $params["cliente"];
			
			foreach (ClientesBO::listaEmailsUp($id_cliente,4) as $listMail);
			$email = $listMail->EMAIL;
			
			$array['data_atualizacao'] 	= date("Y-m-d H:i:s");
			$array['sit']				= true;
			$array['obs']				= $params["obs"];
			$array['id_user']			= $usuario->ID;
			$array['id_clientes']		= $id_cliente;
			$array['nota_fiscal']		= $params["ntfiscal"];
			$array['data_nf']			= substr($params["dt_exp"],6,4).'-'.substr($params["dt_exp"],3,2).'-'.substr($params["dt_exp"],0,2);
			$array['cfop'	]			= $params["cfop"];
			$array['peso_nf']			= str_replace(",",".",$params["peso"]);
			$array['volumes']			= $params["volumes"];
			$array['status']			= "AGUARDANDO AUTORIZAÇÃO PARA ENVIO";
			
			if(empty($params[idgarantia])):
				$array['data_cad']			= date("Y-m-d H:i:s");
				$idcli = $bo->insert($array);
			else: 
				$bo->update($array,"id = ".$params[idgarantia]);
				$idcli = $params[idgarantia];
			endif;
			
		 	$transferencia = new Zend_File_Transfer_Adapter_Http();
					  	
			$name = $transferencia->getFileInfo();
		  	
		  	if($name):
		    	foreach ($name as $val){
			        $fname=$val['name'];
		    	}
			    $exts = split("[/\\.]", $fname) ;
			    $n = count($exts)-1;
			    $exts = $exts[$n];
		    
			  	$transferencia->addFilter('Rename', array('target' => 'nfgarantias/'.$idcli.'.'.$exts,
	                     'overwrite' => true));
				$transferencia->receive();
				
				if(!empty($exts)):
				  	$array_up[anexo]	= $exts;
				  	$bo->update($array_up,"id = ".$idcli);
				endif;
		  	endif;
			
		  	$bohis 		= new GarantiahistoricoModel();
			$arrayu['data']				= date("Y-m-d H:i:s");
			$arrayu['status']			= "AGUARDANDO AUTORIZAÇÃO PARA ENVIO";
			$arrayu['id_garantiaztl']	= $idcli;
			$arrayu['id_user']			= $usuario->ID;
						
			$bohis->insert($arrayu);
			
			LogBO::cadastraLog("Área cliente/Garantia",4,$usuario->ID,$idcli,"GARANTIA G".substr("000000".$idcli,-6,6));
			
			$texto_cli = '<style>
						body {
							margin-left: 0px;
							margin-top: 0px;
							margin-right: 0px;
							margin-bottom: 0px;
							color: #666666;
							font: 11px Arial, Helvetica, sans-serif;
							
						}
						
						</style>
						
						<body>
						<table width="600px" height="200" style="border: 1px solid #000; margin-top: 10px; " cellpadding="4px" align="center" >
						<tr>
							<td align="left" colspan="4">
								<b>Solicita&ccedil;&atilde;o N&ordm; G'.substr("000000".$idcli,-6,6).'</b><br>
							</td>
						</tr>
						<tr>
							<td align="center" colspan="4">
								Solicita&ccedil;&atilde;o de an&aacute;lise de garantia cadastrado com sucesso!
							<br /><br />
								Em breve enviaremos a sua autoriza&ccedil;&atilde;o para o envio dos produtos
								<br /><br /><br />
								Acompanhe sua solicita&ccedil;&atilde;o <a target="_blanc" href="http://www.ztlbrasil.com.br/admin/venda/garantiasviewcli/garantia/'.md5($idcli).'"> clicando aqui </a>
								<br>(&Eacute; necess&aacute;rio estar logado no sistema)
							</td>
						</tr>
						<tr>
							<td align="center" colspan="4">
								www.ztlbrasil.com.br 
							</td>
						</tr>
						
						</table>
						</body>';
			
			if(!empty($email)):
				try{
					$mail = new Zend_Mail();
					$mail->setBodyHtml($texto_cli);
					$mail->setFrom('garantias@ztlbrasil.com.br', 'Departamento de garantias ZTL');
					$mail->addTo($email, $usuario->EMPRESA);
					$mail->setSubject('Garantias ZTL');
					$mail->send();
				} catch (Exception $e){
					echo ($e->getMessage());
				}
			endif;
			
			$texto_ztl = '<style>
						body {
							margin-left: 0px;
							margin-top: 0px;
							margin-right: 0px;
							margin-bottom: 0px;
							color: #666666;
							font: 11px Arial, Helvetica, sans-serif;
							
						}
						
						</style>
						
						<body>
						<table width="600px" height="200" style="border: 1px solid #000; margin-top: 10px; " cellpadding="4px" align="center" >
						<tr>
							<td align="left" colspan="4">
								<b>Solicita&ccedil;&atilde;o N&ordm; G'.substr("000000".$idcli,-6,6).'</b><br>
							</td>
						</tr>
						<tr>
							<td align="center" colspan="4">
								Solicita&ccedil;&atilde;o de an&aacute;lise de garantia cadastrada!
							<br /><br />
								Favor gerar autoriza&ccedil;&atilde;o para o envio dos produtos
								<br /><br /><br />
								Acesse <a target="_blanc" href="http://www.ztlbrasil.com.br/admin/venda/garantiasview/garantia/'.md5($idcli).'"> clicando aqui </a>
								<br>(&Eacute; necess&aacute;rio estar logado no sistema)
							</td>
						</tr>
						<tr>
							<td align="center" colspan="4">
								www.ztlbrasil.com.br
							</td>
						</tr>
						
						</table>
						</body>';
			
				try {
					$mail = new Zend_Mail();
					$mail->setBodyHtml($texto_ztl);
					$mail->setFrom('garantias@ztlbrasil.com.br', 'Departamento de garantias ZTL');
					$mail->addTo('ztl@ztlbrasil.com.br', 'Engenharia');
					$mail->setSubject('Garantias ZTL');
					$mail->send();
				} catch (Exception $e){
					echo ($e->getMessage());
				}
		}
	
		//--Aceita Garantia-------
		 function aceitaGarantia($params){
			$bo 		= New GarantiaModel();
			
			$array['obs']				= $params["obs"];
			$array['nota_fiscal']		= $params["ntfiscal"];
			$array['data_nf']			= substr($params["dt_exp"],6,4).'-'.substr($params["dt_exp"],3,2).'-'.substr($params["dt_exp"],0,2);
			$array['peso_nf']			= str_replace(",",".",$params["peso"]);
			$array['volumes']			= $params["volumes"];
			$array['data_atualizacao'] 	= date("Y-m-d H:i:s");
			$array['tipoenvio']			= $params["tipoenvio"];
			$array['data_valpac']		= substr($params["dt_val"],6,4).'-'.substr($params["dt_val"],3,2).'-'.substr($params["dt_val"],0,2);
			
			if($params["tipoenvio"]==1) $array['valorenvio']	= $params["pac"];
			else $array['valorenvio']	= $params["transportadora"];
			
			$array['status']		= "REMETIDO A ZTL";
						
			$bo->update($array,"id = ".$params[idgarantia]);
			
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			$bohis 		= new GarantiahistoricoModel();
			$arrayu['data']				= date("Y-m-d H:i:s");
			$arrayu['status']			= "REMETIDO A ZTL";
			$arrayu['id_garantiaztl']	= $params[idgarantia];
			$arrayu['id_user']			= $usuario->ID;
						
			$bohis->insert($arrayu);
			
			foreach (VendaBO::listarGarantiascliente($params[idgarantia]) as $listcli);
			
			$id_cliente = $listcli->id_clientes;
			
			foreach (ClientesBO::listaEmailsUp($id_cliente,4) as $listMail);
			$email 	= $listMail->EMAIL;
			$resp	= $listMail->NOME_CONTATO;
			
			if($params["tipoenvio"]==1):
				$texto_ztl = '<style>
						body {
							margin-left: 0px;
							margin-top: 0px;
							margin-right: 0px;
							margin-bottom: 0px;
							color: #666666;
							font: 11px Arial, Helvetica, sans-serif;
						}
						
						</style>
						
						<body>
						<table width="600px" height="200" style="border: 1px solid #000; margin-top: 10px; " cellpadding="4px" align="center" >
						<tr>
							<td align="center" colspan="4">
								<b>Solicita&ccedil;&atilde;o N&ordm; G'.substr("000000".$params[idgarantia],-6,6).'</b><br>
							</td>
						</tr>
						<tr>
							<td align="center" colspan="4">
								Autoriza&ccedil;&atilde;o de envio para an&aacute;lise de garantia
							</td>
						</tr>
						<tr>
							<td align="left" colspan="4">
								
							Boa Tarde, <br> Segue abaixo o c&oacute;digo de autoriza&ccedil;&atilde;o de Postagem por PAC 
							referente a Nota Fiscal: '.$params["ntfiscal"].'<br><br>
							
							
							C&oacute;digo da Autoriza&ccedil;&atilde;o de Postagem: <b>'.$params["pac"].'</b><br>
							Data de Validade: <b>'.$params["dt_val"].'</b><br>
							Servi&ccedil;o de Encomenda: <b>PAC Reverso</b><br>
							Remetente autorizado: <b>'.$resp.'</b> <br><br>
							
							Destinat&aacute;rio:<br>
							<b>ZTL DO BRASIL</b><br>
							QI 8 45/48 , Setor Industrial - Taguatinga - DF <br>
							CEP: 72135-080<br>
							</td>
							</tr>
							<tr>
							<td align="center">
								
								Acompanhe sua garantia <a target="_blanc" href="http://www.ztlbrasil.com.br/admin/venda/garantiasview/garantia/'.md5($params[idgarantia]).'"> clicando aqui </a>
								<br>(&Eacute; necess&aacute;rio estar logado no sistema)
							</td>
						</tr>
						<tr>
							<td align="center" colspan="4">
								<a href="http://www.ztlbrasil.com.br" >www.ztlbrasil.com.br</a> 
							</td>
						</tr>
						
						</table>
						</body>';
			
				else:
				
					$texto_ztl = '<style>
						body {
							margin-left: 0px;
							margin-top: 0px;
							margin-right: 0px;
							margin-bottom: 0px;
							color: #666666;
							font: 11px Arial, Helvetica, sans-serif;
							
						}
						
						</style>
						
						<body>
						<table width="600px" height="200" style="border: 1px solid #000; margin-top: 10px; " cellpadding="4px" align="center" >
						<tr>
							<td align="center" colspan="4">
								<b>Solicita&ccedil;&atilde;o N&ordm; G'.substr("000000".$params[idgarantia],-6,6).'</b><br>
							</td>
						</tr>
						<tr>
							<td align="center" colspan="4">
								Autoriza&ccedil;&atilde;o de envio para an&aacute;lise de garantia
							</td>
						</tr>
						<tr>
							<td align="left" colspan="4">
								
							Boa Tarde, <br> Informamos que ser&aacute; realizado a coleta dos produtos a serem enviados para a an&aacute;lise de garantia,
							referente a Nota Fiscal <b>3418</b>, pela transportadora <b>'.$params["transportadora"].'</b>.<br><br>
							
							</td>
							</tr>
							<tr>
							<td align="center">
								
								Acompanhe sua garantia <a target="_blanc" href="http://www.ztlbrasil.com.br/admin/venda/garantiasview/garantia/'.md5($params[idgarantia]).'"> clicando aqui </a>
								<br>(&Eacute; necess&aacute;rio estar logado no sistema)
							</td>
						</tr>
						<tr>
							<td align="center" colspan="4">
								<a href="http://www.ztlbrasil.com.br" >www.ztlbrasil.com.br</a> 
							</td>
						</tr>
						
						</table>
						</body>';
				
				endif;
			
			
			if(!empty($email)):
				try {
					$mail = new Zend_Mail();
					$mail->setBodyHtml($texto_ztl);
					$mail->setFrom('garantias@ztlbrasil.com.br', 'Departamento de garantias ZTL');
					$mail->addTo($email, $resp);
					$mail->setSubject('Garantias ZTL');
					$mail->send();
				} catch (Exception $e){
					echo ($e->getMessage());
				}
			endif;
			
		}
		
		//--Lista garantias---------------------------
		 function listaGarantias($val){
			
			$sessaoFin = new Zend_Session_Namespace('Default');
			
			$dataini = substr($val['dataini'],6,4).'-'.substr($val['dataini'],3,2).'-'.substr($val['dataini'],0,2);
			$datafin = substr($val['datafin'],6,4).'-'.substr($val['datafin'],3,2).'-'.substr($val['datafin'],0,2);
			
			if(($val['tipo']==1) and (!empty($val['buscaid']))):
				$where = " and t.id = ".substr($val['buscaid'],1);
							
			elseif(($val['tipo']==3) and (!empty($val['buscacli']))):
				$where = " and t.id_clientes = ".$val['buscacli'];
				
			elseif($val['tipo']==4):
				$where = " and t.status like '%".$val['tipostatus']."%'";
							
			endif;
			
			if((!empty($val['dataini'])) and (!empty($val['datafin']))):
				$where .= " and t.data_cad between '".$dataini."' and '".$datafin."'";
			elseif((!empty($val['dataini'])) and (empty($val['datafin']))):
				$where .= " and t.data_cad >= '".$dataini."'";
			elseif((empty($val['dataini'])) and (!empty($val['datafin']))):
				$where .= " and t.data_cad <= '".$datafin."'";
			endif;
			
			if(($where=="") and (!empty($val['page']))):
				$where	= $sessaoFin->where;
			else:
				$sessaoFin->where = $where;
			endif;
			
			$params = array ('host'     => '127.0.0.1', 'username' => 'ztlrolamentos', 'password' => 'BdMySql2008', 'dbname'   => 'ztlrolamentos');
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_garantiaztl','*'),
			        array('t.id','DATE_FORMAT(t.data_chegada,"%d/%m/%Y") as chegada','DATE_FORMAT(t.data_atualizacao,"%d/%m/%Y") as data','t.obs','t.status','c.EMPRESA','t.id_clientes','t.anexo'))
			        ->join(array('c'=>'clientes'),'t.id_clientes = c.ID')
			        ->join(array('ce'=>'clientes_endereco'),'t.id_clientes = ce.ID_CLIENTE')
			        ->where("ce.TIPO = 1 and t.sit = true  ".$where)
			        ->order('t.id desc','');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
	    function listaGarantiasrelatorio($pesq){
			
	    	if(!empty($pesq['prod'])):
				$where = " and p.CODIGO = '".$pesq['prod']."'";
			elseif($pesq['buscagruposub']!=0):
				$where = " and p.id_gruposprodsub = ".$pesq['buscagruposub'];
			elseif($pesq['grupo']!=0):
				foreach (GruposprodBO::listaGruposprodutossub($pesq['grupo']) as $listsubg):
					$idsg .= $listsubg->id.",";
				endforeach;
				$where = " and p.id_gruposprodsub in (".substr($idsg, 0,-1).") ";
			endif;
	    	
			if(!empty($pesq['clientes'])):
				$where .= " and t.id_clientes = ".$pesq['clientes'];
				
			elseif($pesq['uf']!=0):
				$boc	= new ClientesModel();
				$bocl	= new ClientesEnderecoModel();
				
				foreach ($bocl->fetchAll('ESTADO = '.$pesq['uf']) as $listacliuf):
					$idclis .= $listacliuf->ID_CLIENTE.',';
				endforeach;
				
				if($idclis!=""):
					$where .= " and t.id_clientes in (".substr($idclis,0,-1).")";
				endif;		
			endif;
						
			$params = array ('host'     => '127.0.0.1', 'username' => 'ztlrolamentos', 'password' => 'BdMySql2008', 'dbname'   => 'ztlrolamentos');
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_garantiaztl','*'),array('sum(gp.qt) as qtgar'))
					->join(array('gp'=>'tb_garantiaztl_prod'),'t.id = gp.id_garantiaztl')
					->join(array('p'=>'produtos'),'p.ID = gp.id_prod')
			        ->where("t.sit = true and TO_DAYS(NOW()) - TO_DAYS(t.data_cad) <= 365 ".$where);
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
			
		 function listarGarantiascliente($params,$perfil){
			$obj = new GarantiaModel();
			
			$usuario = Zend_Auth::getInstance()->getIdentity();
			
			if($perfil!=10):
				if(empty($params)):
					$where = " and id_clientes = ".$usuario->ID;
				else:
					$where = "and t.id = ".$params;
				endif;
			endif;	
			
			$params = array (
			    'host'     => '127.0.0.1',
			    'username' => 'ztlrolamentos',
			    'password' => 'BdMySql2008',
			    'dbname'   => 'ztlrolamentos'
			);
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_garantiaztl','*'),
			        array('t.id as idcli','DATE_FORMAT(t.data_chegada,"%d/%m/%Y") as chegada','DATE_FORMAT(t.data_atualizacao,"%d/%m/%Y") as data',
			        't.obs','t.status','c.EMPRESA','t.id_clientes','t.nota_fiscal','t.peso_nf','t.volumes','t.tipoenvio','t.valorenvio',
			        't.motivorec','t.data_nf','t.anexo','DATE_FORMAT(t.data_valpac,"%d/%m/%Y") as data_validadepac','t.obsanalise'))
			        ->join(array('c'=>'clientes'),'t.id_clientes = c.ID')
			        ->where("t.sit = true ".$where)
			        ->order('t.id desc','');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();	
			
			
		}
		
		 function listarGarantiasregiao(){
			$obj 	= new GarantiaModel();
			$bo		= new RegioesModel();
			$bor	= new RegioesclientesModel();
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
		
			foreach (PerfilBO::listarPerfil($usuario->tb_perfil_id) as $list);
			if($list->nivel == 1):
				foreach ($bor->fetchAll("id_clientes = ".$usuario->ID) as $regioes):
					$reg .= $regioes->id_regioes.",";
				endforeach;				
				$where = " and ID_REGIOES in (".substr($reg,0,-1).")";
			endif;
			
			$params = array ('host'     => '127.0.0.1', 'username' => 'ztlrolamentos', 'password' => 'BdMySql2008', 'dbname'   => 'ztlrolamentos');
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_garantiaztl','*'),
			        array('t.id','DATE_FORMAT(t.data_chegada,"%d/%m/%Y") as chegada','DATE_FORMAT(t.data_atualizacao,"%d/%m/%Y") as data','t.obs','t.status','c.EMPRESA','t.id_clientes','t.nota_fiscal','t.peso_nf','t.volumes','t.tipoenvio','t.valorenvio','t.motivorec','t.data_nf','t.anexo'))
			        ->join(array('c'=>'clientes'),'t.id_clientes = c.ID')
			        ->where("t.sit = true ".$where)
			        ->order('t.id desc','');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();	
			
		}
		
		 function listarHistorico($var){
			$bo	 = new GarantiaModel();
			$obj = new GarantiahistoricoModel();
															
			$params = array ('host' => '127.0.0.1','username' => 'ztlrolamentos','password' => 'BdMySql2008','dbname'   => 'ztlrolamentos');
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_garantiahistorico','*'),
			        array('t.id','t.data','t.status','c.EMPRESA'))
			        ->join(array('c'=>'clientes'),'t.id_user = c.ID')
			        ->where("t.id_garantiaztl = ".$var)
			        ->order("t.id desc")
			        ->group("t.status");
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();	
			
		}
		
		//--Exclui garantia--------------------------------
		 function removeGarantia($params){
			$aj 		= new GarantiaModel();
			$array['sit'] 				= false;
			$array['data_atualizacao'] 	= date("Y-m-d H:i:s");
			$aj->update($array,'id = '.$params);
			
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			$bohis 		= new GarantiahistoricoModel();
			$arrayu['data']				= date("Y-m-d H:i:s");
			$arrayu['status']			= "EXCLUSÃO";
			$arrayu['id_garantiaztl']	= $params;
			$arrayu['id_user']			= $usuario->ID;
						
			$bohis->insert($arrayu);
			
		}
		//--Recusa garantia--------------------------------
		 function recusarGarantia($params){
			$aj 		= new GarantiaModel();
			$array['status'] 	= "ENVIO RECUSADO";
			$array['motivorec'] = $params[textrecusa];
			$array['data_atualizacao'] = date("Y-m-d H:i:s");
			$aj->update($array,'id = '.$params[idgarantia]);
			
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			$bohis 		= new GarantiahistoricoModel();
			$arrayu['data']				= date("Y-m-d H:i:s");
			$arrayu['status']			= "ENVIO RECUSADO";
			$arrayu['id_garantiaztl']	= $params["idgarantia"];
			$arrayu['id_user']			= $usuario->ID;
						
			$bohis->insert($arrayu);
			
		}
		
		//--Receber produtos Garantia-------
		 function recprodutosGarantia($params){
			$bo			= new GarantiaModel();
			$bohis 		= new GarantiahistoricoModel();
			$boprod 	= new GarantiaprodModel();
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			if($params[enviaAn]==1):
				$array['data']				= date("Y-m-d H:i:s");
				$array['status']			= "RECEBIDO - EM ANÁLISE";
				$array['id_garantiaztl']	= $params["idgarantia"];
				$array['id_user']			= $usuario->ID;
				$bohis->insert($array); 
				
				$arrayg['status']			= "RECEBIDO - EM ANÁLISE";
			else:
				if($params[edicao]!=1):
					$array['data']				= date("Y-m-d H:i:s");
					$array['status']			= "RECEBIDO";
					$array['id_garantiaztl']	= $params["idgarantia"];
					$array['id_user']			= $usuario->ID;
								
					$bohis->insert($array);
				endif;
				$arrayg['status']			= "RECEBIDO";				
			endif;
			
			$arrayg['data_atualizacao'] = date("Y-m-d H:i:s");//
			$arrayg['data_chegada'] 	= substr($params['dt_chegada'],6,4).'-'.substr($params['dt_chegada'],3,2).'-'.substr($params['dt_chegada'],0,2);
			$bo->update($arrayg,"id = ".$params["idgarantia"]);
			
			$boprod->delete("id_garantiaztl = ".$params["idgarantia"]);
			
			//--Grava produtos cadastrados ----------------
			$contador=0;			
			foreach(ProdutosBO::listaallProdutos() as $listprod):
				if(!empty($params[$listprod->ID])):
					$arrayprod['id_garantiaztl']	= $params["idgarantia"];
					$arrayprod['id_prod']			= $listprod->ID;
					$arrayprod['qt']				= $params[$listprod->ID];
					$arrayprod['preco_nf']			= $params["valor_".$listprod->ID];
					$arrayprod['ipi']				= str_replace(",",".",$params["ipi_".$listprod->ID]);
					$arrayprod['icms']				= str_replace(",",".",$params["icms_".$listprod->ID]);
					$arrayprod['track_code']		= $params["tc_".$listprod->ID];
					$boprod->insert($arrayprod);
				endif;
			endforeach;
			
			//LogBO::cadastraLog("Estoque/Ajuste",2,$usuario->ID,$id,"AJUSTE A".substr("000000".$idcli,-6,6));
			
		}
		
		//--listar produtos garantias---------------------------
		 function listaProdutosgarantia($var){
			$params = array (
			    'host'     => '127.0.0.1',
			    'username' => 'ztlrolamentos',
			    'password' => 'BdMySql2008',
			    'dbname'   => 'ztlrolamentos'
			);
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('g'=>'tb_garantiaztl_prod','*'),
			        array('id','qt','preco_nf','p.CODIGO','p.DESCRICAO','id_prod','ipi','icms'))
			        ->join(array('p'=>'produtos'),
			        'g.id_prod = p.ID')
			        ->where("g.id_garantiaztl = ".$var)
			        ->order('g.id','asc');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();				
		}
		
		//--Salva produtos analizados-------
		 function salvarprodutosAnalise($params){
			$bo			= new GarantiaModel();
			$bohis 		= new GarantiahistoricoModel();
			$boprod 	= new GarantiaprodModel();
			$bog		= new GarantiaanaliseModel();
			$bogprod	= new GarantiaanaliseprodModel();
			$boproddet	= new GarantiaproddetModel();
			$boimg		= new GarantiaimgModel();
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			//--Atualizo garantia------------
			$arrayg['data_atualizacao'] = date("Y-m-d H:i:s");
			$arrayg['obsanalise'] 		= stripslashes($params["obs"]);
			
			$bo->update($arrayg,"id = ".$params["idgarantia"]);
						

			//--remove analises para posterior gravacao----------------------
			foreach ($boproddet->fetchAll("id_garantiaztl = ".$params["idgarantia"]) as $listdetg):
				$bogprod->delete("id_proddet = ".$listdetg->id);			
			endforeach;
			
			$boproddet->delete("id_garantiaztl = ".$params["idgarantia"]);
			
			//--Grava produtos cadastrados ----------------
			
			$cont=0;
			$tcont = $params['tcont'];
			
			for ($cont=1;$cont<=$tcont;$cont++):
				$analise = explode(";",$params["analises_".$cont]);
				$arraydet['id_garantiaztl']	=	$params["idgarantia"];
				$arraydet['n_ord']			=	$cont;
				$arraydet['id_prod']		=	$params['id_prod_'.$cont];
				$arraydet['substituir']		=	$params['sub_'.$cont];
				
				$iddet = $boproddet ->insert($arraydet);
				
				$i=0;
				while($analise[$i]!=''):
				 	$arrayprod = array();
					if($analise[$i]=='out'):
						$arrayprod['id_proddet']			= $iddet;
						$arrayprod['desc_outros']			= $params["out_".$cont];
						$bogprod->insert($arrayprod);
						$arrayprod['desc_outros']			= "";
					else:
						$arrayprod['id_analisegar'] 		= $analise[$i];  
						$arrayprod['id_proddet']			= $iddet;
						$bogprod->insert($arrayprod);
						$arrayprod['id_analisegar'] 		= "";
					endif;
					
					$i++;			
				endwhile;
			endfor;
					
			$produtos = explode(";",$params['produtos']);
			
			$i=0;
			while($produtos[$i]!=''):
				$proddet = explode(":",$produtos[$i]);
				
				$arraydet['id_garantiaztl']	=	$params["idgarantia"];
				$arraydet['n_ord']			=	$proddet[1];
				$arraydet['substituir']     = 	false;
				
				foreach (ProdutosBO::buscaProdutoscodigo($proddet[0]) as $lprod);
				$arraydet['id_prod']		=	$lprod->ID;
				
				$iddet = $boproddet->insert($arraydet);
				
				$arrayprod['id_analisegar'] 		= 3;  
				$arrayprod['id_proddet']			= $iddet;
				$bogprod->insert($arrayprod);
				$i++;			
			endwhile;
			
			//--------------------------------------------------------
			
			$upload_adapter = new Zend_File_Transfer_Adapter_Http();
	        $upload_adapter->setDestination(Zend_Registry::get('pastaPadrao')."public/imganalises");
	        //$upload_adapter->setDestination("/aplic/sites/ztlrolamentos.com.br/admin/imganalises");
	        //$pasta = Zend_Registry::get('pastaPadrao')."public/financeirochina/pagar/";
	        
	        $files = $upload_adapter->getFileInfo();
	        $fields = array_keys($files); 
	        $i = 0; 
	        
			foreach ($files as $info) {
								
				//echo "<br>".$fields[$i];
				
				if($upload_adapter->receive($info['name'])){
					$upload_adapter->getMessages();
					//$array[] = $info['name'];
					
					foreach (VendaBO::listaProdutosnord($params["idgarantia"]) as $listdet):
						if(stripos($fields[$i],"arquivo_".$listdet->n_ord)!==false):
							$arrayimg['nome']			=	$info['name'];
							$arrayimg['id_detprod']		=	$listdet->id;
							$arrayimg['id_garantiaztl']	=	$params["idgarantia"];
							$boimg->insert($arrayimg);
						endif;
					endforeach;
				}
				$i++; 
			}
			
			
			
		}
		
		//--Gravar produtos analizados-------
		 function gravarprodutosAnalise($params){
			$bo			= new GarantiaModel();
			$bohis 		= new GarantiahistoricoModel();
			$boprod 	= new GarantiaprodModel();
			$bog		= new GarantiaanaliseModel();
			$bogprod	= new GarantiaanaliseprodModel();
			$boproddet	= new GarantiaproddetModel();
			$boimg		= new GarantiaimgModel();
			$usuario 	= Zend_Auth::getInstance()->getIdentity();

			//--Atualizo historico da garantia-------------------------
			$array['data']				= date("Y-m-d H:i:s");
			$array['status']			= "ANÁLISE CONCLUIDA";
			$array['id_garantiaztl']	= $params["idgarantia"];
			$array['id_user']			= $usuario->ID;
			$bohis->insert($array);
			
			//--Atualizo garantia------------
			$arrayg['status']			= "ANÁLISE CONCLUIDA";	
			$arrayg['data_atualizacao'] = date("Y-m-d H:i:s");
			$arrayg['obsanalise'] 		= stripslashes($params["obs"]);
			
			$bo->update($arrayg,"id = ".$params["idgarantia"]);
			
			//--remove analises para posterior gravacao----------------------
			foreach ($boproddet->fetchAll("id_garantiaztl = ".$params["idgarantia"]) as $listdetg):
				$bogprod->delete("id_proddet = ".$listdetg->id);			
			endforeach;
			
			$boproddet->delete("id_garantiaztl = ".$params["idgarantia"]);			
			
			//--Grava produtos cadastrados ----------------
			
			$cont=0;
			$tcont = $params['tcont'];
			
			
			for ($cont=1;$cont<=$tcont;$cont++):
				$analise = explode(";",$params["analises_".$cont]);
				$arraydet['id_garantiaztl']	=	$params["idgarantia"];
				$arraydet['n_ord']			=	$cont;
				$arraydet['id_prod']		=	$params['id_prod_'.$cont];
				$arraydet['substituir']		=	$params['sub_'.$cont];
				
				$iddet = $boproddet ->insert($arraydet);
				
				$i=0;
				while($analise[$i]!=''):
				 	$arrayprod = array();
					if($analise[$i]=='out'):
						$arrayprod['id_proddet']			= $iddet;
						$arrayprod['desc_outros']			= $params["out_".$cont];
						$bogprod->insert($arrayprod);
						$arrayprod['desc_outros']			= "";
					else:
						$arrayprod['id_analisegar'] 		= $analise[$i];  
						$arrayprod['id_proddet']			= $iddet;
						$bogprod->insert($arrayprod);
						$arrayprod['id_analisegar'] 		= "";
					endif;
					
					$i++;			
				endwhile;
			endfor;
					
			$produtos = explode(";",$params['produtos']);
			
			$i=0;
			while($produtos[$i]!=''):
				$proddet = explode(":",$produtos[$i]);
				
				$arraydet['id_garantiaztl']	=	$params["idgarantia"];
				$arraydet['n_ord']			=	$proddet[1];
				$arraydet['substituir']     = 	false;
				
				foreach (ProdutosBO::buscaProdutoscodigo($proddet[0]) as $lprod);
				$arraydet['id_prod']		=	$lprod->ID;
				
				$iddet = $boproddet->insert($arraydet);
				
				$arrayprod['id_analisegar'] 		= 3;  
				$arrayprod['id_proddet']			= $iddet;
				$bogprod->insert($arrayprod);
				$i++;			
			endwhile;
			
			//--------------------------------------------------------
			
			$upload_adapter = new Zend_File_Transfer_Adapter_Http();
	        //$upload_adapter->setDestination("/var/www/homologacao/admin/imganalises");
	        //$upload_adapter->setDestination("/aplic/sites/ztlrolamentos.com.br/admin/imganalises");
	        $upload_adapter->setDestination(Zend_Registry::get('pastaPadrao')."public/imganalises");
			
	        $files = $upload_adapter->getFileInfo();
	        $fields = array_keys($files); 
	        $i = 0; 
	        
			foreach ($files as $info) {
								
				//echo "<br>".$fields[$i];
				
				if($upload_adapter->receive($info['name'])){
					$upload_adapter->getMessages();
					//$array[] = $info['name'];
					
					foreach (VendaBO::listaProdutosnord($params["idgarantia"]) as $listdet):
						if(stripos($fields[$i],"arquivo_".$listdet->n_ord)!==false):
							$arrayimg['nome']			=	$info['name'];
							$arrayimg['id_detprod']		=	$listdet->id;
							$arrayimg['id_garantiaztl']	=	$params["idgarantia"];
							$boimg->insert($arrayimg);
						endif;
					endforeach;
				}
				$i++; 
			}
			
			
			//--------------------------------------------------------
			
			foreach (VendaBO::listarGarantiascliente($params[idgarantia]) as $listcli);
			
			$id_cliente = $listcli->id_clientes;
			
			foreach (ClientesBO::listaEmailsUp($id_cliente,4) as $listMail);
			$email 	= $listMail->EMAIL;
			$resp	= $listMail->NOME_CONTATO;
			
			$texto_ztl = '<style>
						body {
							margin-left: 0px;
							margin-top: 0px;
							margin-right: 0px;
							margin-bottom: 0px;
							color: #666666;
							font: 11px Arial, Helvetica, sans-serif;
							
						}
						
						</style>
						
						<body>
						<table width="600px" height="200" style="border: 1px solid #000; margin-top: 10px; " cellpadding="4px" align="center" >
						<tr>
							<td align="center" colspan="4">
								<b>Solicita&ccedil;&atilde;o N&ordm; G'.substr("000000".$params[idgarantia],-6,6).'</b><br>
							</td>
						</tr>
						<tr>
							<td align="center" colspan="4">
								Resultado da An&aacute;lise de Garantia
							</td>
						</tr>
						<tr>
							<td align="left" colspan="4">
							Boa Tarde, <br> Informamos que a an&aacute;lise da sua solicita&ccedil;&atilde;o garantia foi concluida.
							</td>
						</tr>
						<tr>
							<td align="center">
								
								Para verificar o resultado <a target="_blanc" href="http://www.ztlbrasil.com.br/admin/venda/garantiaspagcli/garantia/'.md5($params[idgarantia]).'"> clique aqui </a>
								<br>(&Eacute; necess&aacute;rio estar logado no sistema)
							</td>
						</tr>
						<tr>
							<td align="center" colspan="4">
								<a href="http://www.ztlbrasil.com.br" >www.ztlbrasil.com.br</a> 
							</td>
						</tr>
						
						</table>
						</body>';
				
			if(!empty($email)):
				try {
					$mail = new Zend_Mail();
					$mail->setBodyHtml($texto_ztl);
					$mail->setFrom('garantias@ztlbrasil.com.br', 'Departamento de garantias ZTL');
					$mail->addTo($email, $resp);
					$mail->setSubject('Garantias ZTL');
					$mail->send();
				} catch (Exception $e){
					echo ($e->getMessage());
				}
			endif;
			
		}
		
		//---Dicas----------------------------------------------------------
		//---Cadastro de dicas ------------------------------------------------
		 function gravdicasAnalise($params){
			$bo 	= new GarantiaanaliseModel();
			$bod	= new GarantiadicasModel();
			$boa	= new GarantiadicasanaliseModel();
						
			$array['descricao']    		= $params['diag'];
			$array['sit']	  			= true;
	        $array['infotecnica']	  	= stripslashes($params['editor1']);
	           
	        if(empty($params['iddica'])){
				$idcli = $bod->insert($array);
	        }else{
				$bod->update($array,'id ='.$params['iddica']);
				$idcli = $params['iddica'];
	        }
	        
	        $boa->delete("id_dicas = ".$idcli);
	        $arrayb[buscatipo] = 2;
	        foreach (VendaBO::listardiagAnalise($arrayb) as $list):
				if(!empty($params[$list->id])):
					$arrayd['id_dicas'] 			= $idcli;
					$arrayd['id_analise']			= $list->id;
			        $boa->insert($arrayd);
			    endif;       
		    endforeach;
	        
	        
	        $usuario 	= Zend_Auth::getInstance()->getIdentity();
			//LogBO::cadastraLog("Venda/Análise Garantia",2,$usuario->ID,$idcli,"GARANTIA G".substr("000000".$idcli,-6,6));
		}
		
		 function listarDicas($params){
			$bo 	= new GarantiaanaliseModel();
			$bod	= new GarantiadicasModel();
						
			if(!empty($params[id_dicas])){
				return $bod->fetchAll("id = ".$params[id_dicas],"id asc");
				
			}elseif(!empty($params[buscatipo])){
				return $bod->fetchAll("sit = true and tipo = ".$params[buscatipo],"id asc");
			}else{
				return $bod->fetchAll("sit = true", "id asc");
			}	
		}
		
		 function listardicasAnalise($val){
						
			$params = array (
			    'host'     => '127.0.0.1',
			    'username' => 'ztlrolamentos',
			    'password' => 'BdMySql2008',
			    'dbname'   => 'ztlrolamentos'
			);
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('c'=>'tb_gardicas','*'),
			        array('c.id as iddicas','c.descricao as desc', 'c.infotecnica as info', 'a.id as idanalise', 'a.descricao'))
			        ->joinLeft(array('ga'=>'tb_gardicasanalise'),'c.id = ga.id_dicas')
			        ->joinLeft(array('a'=>'tb_garanalise'),'ga.id_analise = a.id')
			        ->where("c.id = ".$val);
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();	
			
		}
		
		//---Cadastro de analises ------------------------------------------------
		 function gravdiagAnalise($params){
			$bo = new GarantiaanaliseModel();
						
			$array['descricao']    		= $params['diag'];
			$array['tipo']	     		= $params['tipo'];
	        $array['sit']	  			= true;
	        $array['infotecnica']	  	= stripslashes($params['editor1']);
	        $array['detalhadesc']	  	= stripslashes($params['descdiag']);
	        
	        
			
	        if(empty($params['analise'])){
				$idcli = $bo->insert($array);
	        }else{
				$bo->update($array,'id ='.$params['analise']);
	        }
	        
	        $usuario 	= Zend_Auth::getInstance()->getIdentity();
			LogBO::cadastraLog("Estoque/Análise Garantia",2,$usuario->ID,$idcli,"ANALISE ".$idcli);
		}
		
		 function listardiagAnalise($params){
			$obj = new GarantiaanaliseModel();
			
			if(!empty($params[id_analisegar])){
				return $obj->fetchAll("id = ".$params[id_analisegar],"id asc");
				
			}elseif(!empty($params[buscatipo])){
				return $obj->fetchAll("sit = true and tipo = ".$params[buscatipo],"id asc");
			}else{
				return $obj->fetchAll("sit = true", "id asc");
			}	
		}
		
	 	 function buscaAnalise($params){
			$obj = new GarantiaanaliseModel();
			if(!empty($params)){
				return $obj->fetchAll("id = ".$params);
			}
		}
		
		
		//---Cadastro de dicas ------------------------------------------------
		 function gravdiagDicas($params){
			$bo  = new GarantiaanaliseModel();
			$obj = new GarantiadicasModel();
						
			$array['descricao']    		= $params['diag'];
			$array['sit']	  			= true;
	        $array['infotecnica']	  	= stripslashes($params['editor1']);
	        	        
	        			
	        if(empty($params['dicas'])){
				$idcli = $bo->insert($array);
	        }else{
				$bo->update($array,'id ='.$params['dicas']);
	        }
	        
	        $usuario 	= Zend_Auth::getInstance()->getIdentity();
			//LogBO::cadastraLog("Venda/Análise Garantia",2,$usuario->ID,$idcli,"GARANTIA G".substr("000000".$idcli,-6,6));
		}
		
		 function listardiagDicas($params){
			$ob  = new GarantiaanaliseModel();
			$obj = new GarantiadicasModel();
			
			if(!empty($params[id_dicas])){
				return $obj->fetchAll("id = ".$params[id_dicas],"id asc");
				
			}else{
				return $obj->fetchAll("sit = true", "id asc");
			}	
		}
		
	 	 function buscaDicas($params){
			$ob  = new GarantiaanaliseModel();
			$obj = new GarantiadicasModel();
			if(!empty($params)){
				return $obj->fetchAll("id = ".$params);
			}
		}
		
		
		//---lista produtos analisados-----------------------------------
		 function listaProdutosanalise($val){
			
			$params = array (
			    'host'     => '127.0.0.1',
			    'username' => 'ztlrolamentos',
			    'password' => 'BdMySql2008',
			    'dbname'   => 'ztlrolamentos'
			);
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_garantiaztl_proddet','*'),
			        array('t.id as idt','t.n_ord', 'g.id_analisegar', 'g.desc_outros', 'a.descricao', 'p.CODIGO','a.tipo','t.substituir'))
			        ->join(array('p'=>'produtos'),'p.ID = t.id_prod')
			        ->join(array('g'=>'tb_garanaliseprod'),'t.id = g.id_proddet')
			        ->joinLeft(array('a'=>'tb_garanalise'),'a.id = g.id_analisegar')
			        
			        ->where("t.id_garantiaztl = ".$val)
			        ->order('t.n_ord asc','');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}

		//---lista produtos analisados por ordem-----------------------------------
		 function listaProdutosnord($val){
			$bo 	= new GarantiaModel();
			$boi	= new GarantiaproddetModel();

			return $boi->fetchAll("id_garantiaztl = ".$val);
		}

		//---lista imagens produtos -----------------------------------
		 function listaProdutosimg($val){
			$bo 	= new GarantiaModel();
			$boi	= new GarantiaimgModel();

			return $boi->fetchAll("id_garantiaztl = ".$val);
		}
		
		 function listaProdutosanaimg($val){
			$params = array (
			    'host'     => '127.0.0.1',
			    'username' => 'ztlrolamentos',
			    'password' => 'BdMySql2008',
			    'dbname'   => 'ztlrolamentos'
			);
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_garantiaztl_proddet','*'),
			        array('t.id as idt','t.n_ord','a.nome'))
			        ->join(array('a'=>'tb_garanaliseimg'),'a.id_detprod = t.id')
			        ->where("t.id_garantiaztl = ".$val)
			        ->order('t.n_ord asc','');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		
		//---lista produtos analisados para o pagamento da garantia-----------------------------------
		 function listaProdutospagar($val){
			
			$params = array (
			    'host'     => '127.0.0.1',
			    'username' => 'ztlrolamentos',
			    'password' => 'BdMySql2008',
			    'dbname'   => 'ztlrolamentos'
			);
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_garantiaztl_proddet','*'),
			        array('count(t.id_prod) qtent','t.id as idt','t.n_ord', 'p.CODIGO','t.id_prod','(select sum(qt) from tb_garantiaentrega where id_garantiaztl_prod = g.id) as qte'))
			        ->join(array('p'=>'produtos'),'p.ID = t.id_prod')
			        ->join(array('g'=>'tb_garantiaztl_prod'),'g.id_garantiaztl = t.id_garantiaztl and g.id_prod = t.id_prod ')
			        ->where(" t.substituir = true  and t.id_garantiaztl = ".$val)
			        ->group('t.id_prod');
			        			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}

		 function listaProdutoanalise($val){
			$params = array (
			    'host'     => '127.0.0.1',
			    'username' => 'ztlrolamentos',
			    'password' => 'BdMySql2008',
			    'dbname'   => 'ztlrolamentos'
			);
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_garantiaztl_proddet','*'),
			        array('g.id_analisegar', 'g.desc_outros', 'a.descricao', 'p.CODIGO','a.tipo','t.substituir','a.infotecnica','a.detalhadesc','DATE_FORMAT(z.data,"%d/%m/%Y") as dtconcluido'))
			        ->join(array('p'=>'produtos'),'p.ID = t.id_prod')
			        ->join(array('g'=>'tb_garanaliseprod'),'t.id = g.id_proddet')
			        ->joinLeft(array('a'=>'tb_garanalise'),'a.id = g.id_analisegar')
			        ->joinLeft(array('z'=>'tb_garantiahistorico'),'z.id_garantiaztl = t.id_garantiaztl and z.status like "%CONCLU%"')
			        ->where("md5(t.id) = '".$val."'");
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		//---lista produtos analisados para o nao pagamento da garantia -----------------------------------
		 function listaProdutosnpagar($val){
			
			$params = array (
			    'host'     => '127.0.0.1',
			    'username' => 'ztlrolamentos',
			    'password' => 'BdMySql2008',
			    'dbname'   => 'ztlrolamentos'
			);
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_garantiaztl_proddet','*'),
			        array('count(t.id_prod) as qtent','t.id as idt','t.n_ord', 'p.CODIGO','t.id_prod','t.nt_fiscal','DATE_FORMAT(t.data_entrega,"%d/%m/%Y") as dtnt'))
			        ->join(array('p'=>'produtos'),'p.ID = t.id_prod')
			        /*->join(array('a'=>'tb_garanaliseprod'),'a.id_proddet = t.id')*/
			        ->where(" t.substituir = false  and t.id_garantiaztl = ".$val)
			        ->group('t.id_prod');
			        			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}	
		
		//--- Cadastro de entrega ------------------------------------------------
		 function gravaentregaGarantia($params){
			$bog 		= new GarantiaModel();
			$bo 		= new GarantiaprodModel();
			$boent		= new GarantiaentregaModel();
			$bohis 		= new GarantiahistoricoModel();
			$bodet		= new GarantiaproddetModel();
			$boprod 	= new EstoqueModel();
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			if((!empty($params['dqtd'])) and (!empty($params['ddtnt']))):
				$arraydet['nt_fiscal']		= $params['dqtd'];
				$arraydet['data_entrega']	= substr($params['ddtnt'],6,4).'-'.substr($params['ddtnt'],3,2).'-'.substr($params['ddtnt'],0,2);
				$bodet->update($arraydet,"substituir = false and id_garantiaztl = ".$params['idgarantia']);
			
			endif;
			
			foreach (VendaBO::listaProdutosgarantia($params['idgarantia']) as $list):
				if((!empty($params['qt_'.$list->id_prod])) && (!empty($params['nt_'.$list->id_prod])) && (!empty($params['dtnt_'.$list->id_prod]))):
					$array['qt']    				= $params['qt_'.$list->id_prod];
					$array['nt_fiscal']				= $params['nt_'.$list->id_prod];
			        $array['dt_ntfiscal']  			= substr($params['dtnt_'.$list->id_prod],6,4).'-'.substr($params['dtnt_'.$list->id_prod],3,2).'-'.substr($params['dtnt_'.$list->id_prod],0,2);
			        $array['id_garantiaztl_prod']   = $list->id;
			        
			        $boent->insert($array);

			        foreach ($boprod->fetchAll('id_prod = '.$list->id_prod,"id desc",1) as $qt_atual);
					
					$arrayestq = array();
					$arrayestq['id_prod'] 			= $list->id_prod;
					$arrayestq['qt_atual'] 			= $qt_atual->qt_atual-($params['qt_'.$list->id_prod]);
					$arrayestq['qt_atualizacao'] 	= $params['qt_'.$list->id_prod];
					$arrayestq['id_atualizacao'] 	= $params['idgarantia'];
					$arrayestq['dt_atualizacao'] 	= date("Y-m-d H:i:s");
					$arrayestq['tipo'] 				= "GARANTIA";
					$arrayestq['id_user'] 			= $usuario->ID;
					
					$boprod->insert($arrayestq);
			        
			    endif;       
		    endforeach;
		    
			$fin = 0;
			foreach (VendaBO::listaProdutospagar($params["idgarantia"]) as $listProd):
       			if(($listProd->qtent-$listProd->qte)>0):
	       			$fin = 1;       			
	       		endif;
	       	endforeach;
	       	
	       	foreach ($bodet->fetchAll("id_garantiaztl = ".$params['idgarantia']." and substituir = false") as $listDet):
	       		if(empty($listDet->nt_fiscal)):
	       			$fin = 1;
	       		endif;	       		
	       	endforeach;
	       	
	       	if($fin==0):
		       	//--Atualizo historico da garantia-------------------------
				$arrayh['data']				= date("Y-m-d H:i:s");
				$arrayh['status']			= "FINALIZADO";
				$arrayh['id_garantiaztl']	= $params["idgarantia"];
				$arrayh['id_user']			= $usuario->ID;
				$bohis->insert($arrayh);
				
		       	//--Atualiza garantia------------
				$arrayg['status']			= "FINALIZADO";
				$arrayg['data_atualizacao'] = date("Y-m-d H:i:s");
				$bog->update($arrayg,"id = ".$params["idgarantia"]);
			else:
				foreach (VendaBO::listarGarantiascliente($params["idgarantia"]) as $lista);
				if(strripos($lista->status, " PARCIAL")===false):
					//--Atualiza historico da garantia-------------------------
					$arrayh['data']				= date("Y-m-d H:i:s");
					$arrayh['status']			= "FINALIZAÇÃO PARCIAL";
					$arrayh['id_garantiaztl']	= $params["idgarantia"];
					$arrayh['id_user']			= $usuario->ID;
					$bohis->insert($arrayh);
					
					//--Atualiza garantia------------
					$arrayg['status']			= "FINALIZAÇÃO PARCIAL";
					$arrayg['data_atualizacao'] = date("Y-m-d H:i:s");
					$bog->update($arrayg,"id = ".$params["idgarantia"]);
				endif;				
			endif;
						
		}
		
		//--listar produtos garantias pagos---------------------------
		 function listaprodutospagosGarantia($var){
			$params = array (
			    'host'     => '127.0.0.1',
			    'username' => 'ztlrolamentos',
			    'password' => 'BdMySql2008',
			    'dbname'   => 'ztlrolamentos'
			);
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('e'=>'tb_garantiaentrega','*'),
			        array('p.CODIGO','e.qt as qte','e.nt_fiscal as ntf','DATE_FORMAT(e.dt_ntfiscal,"%d/%m/%Y") as dtn'))
			        ->join(array('g'=>'tb_garantiaztl_prod'),'e.id_garantiaztl_prod = g.id')
			        ->join(array('p'=>'produtos'),'g.id_prod = p.ID')
			        ->where("g.id_garantiaztl = ".$var);
			  
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();				
		}
		
		//--listar detalhes analises garantias---------------------------
		 function listadetalhesAnalise($var){
			$params = array (
			    'host'     => '127.0.0.1',
			    'username' => 'ztlrolamentos',
			    'password' => 'BdMySql2008',
			    'dbname'   => 'ztlrolamentos'
			);
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_garantiaztl_proddet','*'),
			        array('a.id','a.detalhadesc','a.infotecnica'))
			        ->join(array('p'=>'tb_garanaliseprod'),'t.id = p.id_proddet')
			        ->join(array('a'=>'tb_garanalise'),'a.id = p.id_analisegar')			        
			        ->where("t.id_garantiaztl =  ".$var)
			        ->group('a.id','asc');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();				
		}
		
		
		 function gravaImagensanalise(){
			    					
	        // Instancia o adaptador do Zend para tranferência de arquivos via
	        // protocolo Http e definine o destino do arquivo
	        $upload_adapter = new Zend_File_Transfer_Adapter_Http();
	        //$upload_adapter->setDestination("/var/www/homologacao/admin/imganalises");
	        $upload_adapter->setDestination("/aplic/sites/ztlrolamentos.com.br/admin/imganalises");
	        
			
	        $files = $upload_adapter->getFileInfo();
	        $fields = array_keys($files); 
	        $i = 0; 
	        
			foreach ($files as $info) {
								
				//echo "<br>".$fields[$i];
				
				if($upload_adapter->receive($info['name'])){
					$upload_adapter->getMessages();
					$array[] = $info['name'];
					//return;
				}
				$i++; 
			}

	        
	        
	        /*if( $upload_adapter->receive() )
	            echo 'Upload efetuado com sucesso';
	        else
	            echo 'Ops! Ocorreu um erro ao enviar o arquivo';*/
			
			return $array;
			
		}
		
		//---- Relatorios garantias ---------------------------------------
		 function relatorioCompras($pesq){
			
			if(!empty($pesq['prod'])):
				$where = " and p.CODIGO = '".$pesq['prod']."'";
			elseif ($pesq['buscagruposub']!=0):
				$where = " and p.id_gruposprodsub = ".$pesq['buscagruposub'];
			elseif ($pesq['grupo']!=0):
				foreach (GruposprodBO::listaGruposprodutossub($pesq['grupo']) as $listsubg):
					$idsg .= $listsubg->id.",";
				endforeach;
				$where = " and p.id_gruposprodsub in (".substr($idsg, 0,-1).") ";
			endif;
			
			$params = array (
			    'host'     => '127.0.0.1',
			    'username' => 'ztlrolamentos',
			    'password' => 'BdMySql2008',
			    'dbname'   => 'ztlrolamentos'
			);
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('pd'=>'tb_pedidos','*'),
			        array('pp.id_prod','sum(pp.qt) as qttotal','p.CODIGO'))
			        ->join(array('pp'=>'tb_pedidos_prod'),'pp.id_ped = pd.id')
			        ->join(array('p'=>'produtos'),'pp.id_prod = p.ID')
			        ->join(array('g'=>'tb_garantiaztl'),'g.id_clientes = pd.id_parceiro')
			        ->join(array('pg'=>'tb_garantiaztl_prod'),'pg.id_garantiaztl = g.id')
			        ->where("pg.id_prod = pp.id_prod and id_parceiro = ".$usuario->ID." and TO_DAYS(NOW()) - TO_DAYS(pd.data_cad) <= 365 ".$where)
			        ->order('p.codigo_mask','asc')
			        ->group("pp.id_prod");
			  
			$stmt = $db->query($select);
			
			
			return $stmt->fetchAll();		

			/*select pp.id_prod, sum(pp.qt), pd.data_cad 

			from tb_pedidos pd, tb_pedidos_prod pp, produtos p
			
			where id_parceiro = 173 and pp.id_ped = pd.id and TO_DAYS(NOW()) - TO_DAYS(data_cad) <= 365 and pp.id_prod = p.ID
			
			group by pp.id_prod
			;*/
						
		}
		
		//---- Relatorios garantias ---------------------------------------
		 function relatorioGarantia($pesq){
			
			if(!empty($pesq['prod'])):
				$where = " and pp.CODIGO = '".$pesq['prod']."'";
			elseif (!empty($pesq['grupo'])):
				foreach (GruposprodBO::listaGruposprodutossub($pesq['grupo']) as $listag):
					$idsg	.= $listag->id.",";
				endforeach;
				$where = " and pp.id_gruposprodsub in (".substr($idsg,0,-1).")";
			endif;
			
			$params = array (
			    'host'     => '127.0.0.1',
			    'username' => 'ztlrolamentos',
			    'password' => 'BdMySql2008',
			    'dbname'   => 'ztlrolamentos'
			);
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('g'=>'tb_garantiaztl','*'),
			        array('p.id_prod','sum(p.qt) as qttotal'))
			        ->join(array('p'=>'tb_garantiaztl_prod'),'p.id_garantiaztl = g.id')
			        ->join(array('pp'=>'produtos'),'p.id_prod = pp.ID')
			        ->where("id_clientes = ".$usuario->ID." and g.sit = true and TO_DAYS(NOW()) - TO_DAYS(data_cad) <= 365 ".$where)
			        ->group("p.id_prod");
			  
			$stmt = $db->query($select);
						
			return $stmt->fetchAll();		

			/* 
			 *  SELECT sum(p.qt), p.id_prod, p.qt
				FROM tb_garantiaztl g, tb_garantiaztl_prod p 
				where id_clientes = 173 and p.id_garantiaztl = g.id and g.sit = true and TO_DAYS(NOW()) - TO_DAYS(data_cad) <= 365
				group by p.id_prod
				;
				
				
			;*/
						
		}
		
		//---- Relatorios garantias ---------------------------------------
		 function relatorioGarantiapag($pesq){
			
			
			if(!empty($pesq['prod'])):
				$where = " and pp.CODIGO = '".$pesq['prod']."'";
			elseif ($pesq['buscagruposub']!=0):
				$where = " and pp.id_gruposprodsub = ".$pesq['buscagruposub'];
			elseif ($pesq['grupo']!=0):
				foreach (GruposprodBO::listaGruposprodutossub($pesq['grupo']) as $listsubg):
					$idsg .= $listsubg->id.",";
				endforeach;
				$where = " and pp.id_gruposprodsub in (".substr($idsg, 0,-1).") ";
			endif;
			
			$params = array (
			    'host'     => '127.0.0.1',
			    'username' => 'ztlrolamentos',
			    'password' => 'BdMySql2008',
			    'dbname'   => 'ztlrolamentos'
			);
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('g'=>'tb_garantiaztl','*'),
			        array('p.id_prod','count(p.id_prod) as qttotal'))
			        ->join(array('p'=>'tb_garantiaztl_proddet'),'p.id_garantiaztl = g.id')
			        ->join(array('pp'=>'produtos'),'p.id_prod = pp.ID')
			        ->where("id_clientes = ".$usuario->ID." and g.sit = true and p.substituir = true and TO_DAYS(NOW()) - TO_DAYS(data_cad) <= 365 ".$where)
			        ->group("p.id_prod");
			  
			$stmt = $db->query($select);
						
			return $stmt->fetchAll();		

			/* 
			 *  				
				SELECT count(p.id_prod), p.id_prod, substituir
				FROM tb_garantiaztl g, tb_garantiaztl_proddet p 
				where id_clientes = 173 and p.id_garantiaztl = g.id and TO_DAYS(NOW()) - TO_DAYS(data_cad) <= 365 and p.substituir = true
				group by p.id_prod
				;

			;*/
						
		}
		
	//---- Relatorios garantias ---------------------------------------
		 function relatorioComprasgeral($pesq){
			//---- Quantidade de produtos comprados ------------------
			if(!empty($pesq['prod'])):
				$where = " and p.CODIGO = '".$pesq['prod']."'";
			elseif ($pesq['buscagruposub']!=0):
				$where = " and p.id_gruposprodsub = ".$pesq['buscagruposub'];
			elseif ($pesq['grupo']!=0):
				foreach (GruposprodBO::listaGruposprodutossub($pesq['grupo']) as $listsubg):
					$idsg .= $listsubg->id.",";
				endforeach;
				$where = " and p.id_gruposprodsub in (".substr($idsg, 0,-1).") ";
			endif;		
			
			/*if(!empty($pesq['clientes'])):
				$where .= " and p.id_parceiro = ".$pesq['clientes'];
				
			elseif($pesq['uf']!=0):
				$boc	= new ClientesModel();
				$bocl	= new ClientesEnderecoModel();
				
				foreach ($bocl->fetchAll('ESTADO = '.$pesq['uf']) as $listacliuf):
					$idclis .= $listacliuf->ID_CLIENTE.',';
				endforeach;
				
				if($idclis!=""):
					$where .= " and p.id_parceiro in (".substr($idclis,0,-1).")";
				endif;		
			endif;*/
			
			
			$params = array ('host'     => '127.0.0.1', 'username' => 'ztlrolamentos', 'password' => 'BdMySql2008', 'dbname'   => 'ztlrolamentos');
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('pd'=>'tb_pedidos','*'),
			        array('sum(pp.qt) as qtcomp','p.CODIGO','pp.id_prod'))
			        ->join(array('pp'=>'tb_pedidos_prod'),'pp.id_ped = pd.id')
			        ->join(array('p'=>'produtos'),'pp.id_prod = p.ID')
			        ->where("pd.status = 'ped' and pd.sit = 0 ".$where)
			        ->group("pp.id_prod");
			  
			$stmt = $db->query($select);			
			
			/*and TO_DAYS(NOW()) - TO_DAYS(pd.data_vend) <= 365*/
			
			return $stmt->fetchAll();		
						
		}
		
		//---- Relatorios garantias ---------------------------------------
		 function relatorioGarantiageral($pesq){
			
			if(!empty($pesq['prod'])):
				$where = " and pp.CODIGO = '".$pesq['prod']."'";
			elseif (!empty($pesq['grupo'])):
				foreach (GruposprodBO::listaGruposprodutossub($pesq['grupo']) as $listag):
					$idsg	.= $listag->id.",";
				endforeach;
				$where = " and pp.id_gruposprodsub in (".substr($idsg,0,-1).")";
			endif;
			
			/*if(!empty($pesq['clientes'])):
				$where .= " and p.id_parceiro = ".$pesq['clientes'];
			elseif($pesq['uf']!=0):
				$boc	= new ClientesModel();
				$bocl	= new ClientesEnderecoModel();
				
				foreach ($bocl->fetchAll('ESTADO = '.$pesq['uf']) as $listacliuf):
					$idclis .= $listacliuf->ID_CLIENTE.',';
				endforeach;
				
				if($idclis!=""):
					$where .= " and p.id_parceiro in (".substr($idclis,0,-1).")";
				endif;		
			endif;*/
			
			$params = array ('host'     => '127.0.0.1', 'username' => 'ztlrolamentos', 'password' => 'BdMySql2008', 'dbname'   => 'ztlrolamentos');
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('g'=>'tb_garantiaztl','*'),
			        array('p.id_prod','sum(p.qt) as qttotal'))
			        ->join(array('p'=>'tb_garantiaztl_prod'),'p.id_garantiaztl = g.id')
			        ->join(array('pp'=>'produtos'),'p.id_prod = pp.ID')
			        ->where("g.sit = true ".$where)
			        ->group("p.id_prod");
			  
			$stmt = $db->query($select);
						
			return $stmt->fetchAll();		

			/* 
			 *  SELECT sum(p.qt), p.id_prod, p.qt
				FROM tb_garantiaztl g, tb_garantiaztl_prod p 
				where id_clientes = 173 and p.id_garantiaztl = g.id and g.sit = true and TO_DAYS(NOW()) - TO_DAYS(data_cad) <= 365
				group by p.id_prod
				;
				
				
			;*/
						
		}
		
		//---- Relatorios garantias ---------------------------------------
		 function relatorioGarantiapaggeral($pesq){
			
			if(!empty($pesq['prod'])):
				$where = " and pp.CODIGO = '".$pesq['prod']."'";
			elseif (!empty($pesq['grupo'])):
				foreach (GruposprodBO::listaGruposprodutossub($pesq['grupo']) as $listag):
					$idsg	.= $listag->id.",";
				endforeach;
				$where = " and pp.id_gruposprodsub in (".substr($idsg,0,-1).")";
			endif;
			
			/*if(!empty($pesq['clientes'])):
				$where .= " and p.id_parceiro = ".$pesq['clientes'];				
			elseif($pesq['uf']!=0):
				$boc	= new ClientesModel();
				$bocl	= new ClientesEnderecoModel();
				
				foreach ($bocl->fetchAll('ESTADO = '.$pesq['uf']) as $listacliuf):
					$idclis .= $listacliuf->ID_CLIENTE.',';
				endforeach;
				
				if($idclis!=""):
					$where .= " and p.id_parceiro in (".substr($idclis,0,-1).")";
				endif;		
			endif;*/
			
			$params = array ('host'     => '127.0.0.1', 'username' => 'ztlrolamentos', 'password' => 'BdMySql2008', 'dbname'   => 'ztlrolamentos');
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('g'=>'tb_garantiaztl','*'),
			        array('count(p.id_prod) as qttotal','p.id_prod'))
			        ->join(array('p'=>'tb_garantiaztl_proddet'),'p.id_garantiaztl = g.id')
			        ->join(array('pp'=>'produtos'),'p.id_prod = pp.ID')
			        ->where("g.sit = true and p.substituir = true ".$where)
			        ->group("p.id_prod");
			  
			$stmt = $db->query($select);
						
			return $stmt->fetchAll();		

			/* 
			 *  				
				SELECT count(p.id_prod), p.id_prod, substituir
				FROM tb_garantiaztl g, tb_garantiaztl_proddet p 
				where id_clientes = 173 and p.id_garantiaztl = g.id and TO_DAYS(NOW()) - TO_DAYS(data_cad) <= 365 and p.substituir = true
				group by p.id_prod
				;

			;*/
						
		}
		
		//---- Relatorios garantias negadas ---------------------------------------
		 function relatorioGarantiapagnegcli($pesq){
			
			if(!empty($pesq['prod'])):
				$where = " and pp.CODIGO = '".$pesq['prod']."'";
			elseif ($pesq['buscagruposub']!=0):
				$where = " and pp.id_gruposprodsub = ".$pesq['buscagruposub'];
			elseif ($pesq['grupo']!=0):
				foreach (GruposprodBO::listaGruposprodutossub($pesq['grupo']) as $listsubg):
					$idsg .= $listsubg->id.",";
				endforeach;
				$where = " and pp.id_gruposprodsub in (".substr($idsg, 0,-1).") ";
			endif;
			
			$params = array (
			    'host'     => '127.0.0.1',
			    'username' => 'ztlrolamentos',
			    'password' => 'BdMySql2008',
			    'dbname'   => 'ztlrolamentos'
			);
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('g'=>'tb_garantiaztl','*'),
			        array('gp.id_analisegar','count(gp.id_analisegar) as qtanalise','count(gp.desc_outros) as qtanaliseout','a.descricao'))
			        ->join(array('p'=>'tb_garantiaztl_proddet'),'p.id_garantiaztl = g.id')
			        ->join(array('pp'=>'produtos'),'p.id_prod = pp.ID')
			        ->join(array('gp'=>'tb_garanaliseprod'),'gp.id_proddet = p.id')
			        ->joinLeft(array('a'=>'tb_garanalise'),'a.id = gp.id_analisegar')
			        ->where("g.id_clientes = ".$usuario->ID." and g.sit = true and p.substituir = false and TO_DAYS(NOW()) - TO_DAYS(g.data_cad) <= 365  ".$where)
			        ->order("gp.id_analisegar desc")
			        ->group("gp.id_analisegar");
			  
			$stmt = $db->query($select);
			
			return $stmt->fetchAll();		
						
		}

		 function relatorioGarantiapagnegclidesc($pesq){
			
			if(!empty($pesq['prod'])):
				$where = " and pp.CODIGO = '".$pesq['prod']."'";
			elseif (!empty($pesq['grupo'])):
				$where = " and pp.ID_GRUPO = ".$pesq['grupo'];
			endif;
			
			$params = array (
			    'host'     => '127.0.0.1',
			    'username' => 'ztlrolamentos',
			    'password' => 'BdMySql2008',
			    'dbname'   => 'ztlrolamentos'
			);
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('g'=>'tb_garantiaztl','*'),
			        array('gp.id_analisegar','count(gp.id) as qttotal','gp.desc_outros','a.descricao','a.id as idanalise'))
			        ->join(array('p'=>'tb_garantiaztl_proddet'),'p.id_garantiaztl = g.id')
			        ->join(array('pp'=>'produtos'),'p.id_prod = pp.ID')
			        ->join(array('gp'=>'tb_garanaliseprod'),'gp.id_proddet = p.id')
			        ->joinLeft(array('a'=>'tb_garanalise'),'a.id = gp.id_analisegar')
			        ->where("g.id_clientes = ".$usuario->ID." and g.sit = true and p.substituir = false and TO_DAYS(NOW()) - TO_DAYS(g.data_cad) <= 365  ".$where)
			        ->order("gp.id_analisegar asc")
			        ->group("gp.id_analisegar");
			  
			$stmt = $db->query($select);
						
			return $stmt->fetchAll();	
		}
		
		///////--------Relatorio ZTl garantias------------------------------
		//---- Relatorios garantias ---------------------------------------
		 function relatorioComprasztl($pesq){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			$bo		= new RegioesModel();
			$bor	= new RegioesclientesModel();
			
			if(!empty($pesq['prod'])):
				$where = " and p.CODIGO = '".$pesq['prod']."'";
			elseif($pesq['buscagruposub']!=0):
				$where = " and p.id_gruposprodsub = ".$pesq['buscagruposub'];
			elseif($pesq['grupo']!=0):
				foreach (GruposprodBO::listaGruposprodutossub($pesq['grupo']) as $listsubg):
					$idsg .= $listsubg->id.",";
				endforeach;
				$where = " and p.id_gruposprodsub in (".substr($idsg, 0,-1).") ";
			endif;			
			
			if(!empty($pesq['clientes'])):
				$where .= " and pd.id_parceiro = ".$pesq['clientes'];
				
			elseif($pesq['uf']!=0):
				$boc	= new ClientesModel();
				$bocl	= new ClientesEnderecoModel();
				
				foreach ($bocl->fetchAll('ESTADO = '.$pesq['uf']) as $listacliuf):
					$idclis .= $listacliuf->ID_CLIENTE.',';
				endforeach;
				
				if($idclis!=""):
					$where .= " and pd.id_parceiro in (".substr($idclis,0,-1).")";
				endif;		
			endif;
			
			foreach (PerfilBO::listarPerfil($usuario->tb_perfil_id) as $list);
			if($list->nivel == 1):
				foreach ($bor->fetchAll("id_clientes = ".$usuario->ID) as $regioes):
					$reg .= $regioes->id_regioes.",";
				endforeach;				
				$where .= " and ID_REGIOES in (".substr($reg,0,-1).")";				
			endif;			
			
			$params = array ('host'     => '127.0.0.1', 'username' => 'ztlrolamentos', 'password' => 'BdMySql2008', 'dbname'   => 'ztlrolamentos');
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('pd'=>'tb_pedidos','*'),
			        array('pp.id_prod','sum(pp.qt) as qttotal','p.CODIGO'))
			        ->join(array('pp'=>'tb_pedidos_prod'),'pp.id_ped = pd.id')
			        ->join(array('p'=>'produtos'),'pp.id_prod = p.ID')
			        ->where("pd.sit = 0 and pd.status = 'ped' ".$where)
			        ->order('p.codigo_mask','asc')
			        ->group("pp.id_prod");

			        
			        
			$stmt = $db->query($select);
			
			return $stmt->fetchAll();	
						
		}
		
		//---- Relatorios garantias ---------------------------------------
		 function relatorioGarantiaztl($pesq){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			if(!empty($pesq['prod'])):
				$where = " and pp.CODIGO = '".$pesq['prod']."'";
			elseif ($pesq['buscagruposub']!=0):
				$where = " and pp.id_gruposprodsub = ".$pesq['buscagruposub'];
			elseif ($pesq['grupo']!=0):
				foreach (GruposprodBO::listaGruposprodutossub($pesq['grupo']) as $listsubg):
					$idsg .= $listsubg->id.",";
				endforeach;
				$where = " and pp.id_gruposprodsub in (".substr($idsg, 0,-1).") ";
			endif;
			
			if(!empty($pesq['clientes'])):
				$where .= " and g.id_clientes = ".$pesq['clientes'];				
			elseif($pesq['uf']!=0):
				$boc	= new ClientesModel();
				$bocl	= new ClientesEnderecoModel();
				
				foreach ($bocl->fetchAll('ESTADO = '.$pesq['uf']) as $listacliuf):
					$idclis .= $listacliuf->ID_CLIENTE.',';
				endforeach;
				
				if($idclis!=""):
					$where .= " and g.id_clientes in (".substr($idclis,0,-1).")";
				endif;		
			endif;
			
			/*foreach (PerfilBO::listarPerfil($usuario->tb_perfil_id) as $list);
			if($list->nivel == 1):
				foreach ($bor->fetchAll("id_clientes = ".$usuario->ID) as $regioes):
					$reg .= $regioes->id_regioes.",";
				endforeach;				
				$where .= " and ID_REGIOES in (".substr($reg,0,-1).")";				
			endif;*/
			
			
			$params = array ('host'     => '127.0.0.1', 'username' => 'ztlrolamentos', 'password' => 'BdMySql2008', 'dbname'   => 'ztlrolamentos');
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('g'=>'tb_garantiaztl','*'),
			        array('p.id_prod','sum(p.qt) as qttotal'))
			        ->join(array('p'=>'tb_garantiaztl_prod'),'p.id_garantiaztl = g.id')
			        ->join(array('pp'=>'produtos'),'p.id_prod = pp.ID')
			        ->where("g.sit = true and TO_DAYS(NOW()) - TO_DAYS(data_cad) <= 365 ".$where)
			        ->group("p.id_prod");
			  
			$stmt = $db->query($select);
						
			return $stmt->fetchAll();		
						
		}
		
		//---- Relatorios garantias ---------------------------------------
		 function relatorioGarantiapagztl($pesq){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			if(!empty($pesq['prod'])):
				$where = " and pp.CODIGO = '".$pesq['prod']."'";
			elseif ($pesq['buscagruposub']!=0):
				$where = " and pp.id_gruposprodsub = ".$pesq['buscagruposub'];
			elseif ($pesq['grupo']!=0):
				foreach (GruposprodBO::listaGruposprodutossub($pesq['grupo']) as $listsubg):
					$idsg .= $listsubg->id.",";
				endforeach;
				$where = " and pp.id_gruposprodsub in (".substr($idsg, 0,-1).") ";
			endif;
			
			if(!empty($pesq['clientes'])):
				$where .= " and g.id_clientes = ".$pesq['clientes'];				
			elseif($pesq['uf']!=0):
				$boc	= new ClientesModel();
				$bocl	= new ClientesEnderecoModel();
				
				foreach ($bocl->fetchAll('ESTADO = '.$pesq['uf']) as $listacliuf):
					$idclis .= $listacliuf->ID_CLIENTE.',';
				endforeach;
				
				if($idclis!=""):
					$where .= " and g.id_clientes in (".substr($idclis,0,-1).")";
				endif;		
			endif;
			
			/*foreach (PerfilBO::listarPerfil($usuario->tb_perfil_id) as $list);
			if($list->nivel == 1):
				foreach ($bor->fetchAll("id_clientes = ".$usuario->ID) as $regioes):
					$reg .= $regioes->id_regioes.",";
				endforeach;				
				$where .= " and ID_REGIOES in (".substr($reg,0,-1).")";				
			endif;*/
			
			
			$params = array ('host' => '127.0.0.1', 'username' => 'ztlrolamentos', 'password' => 'BdMySql2008', 'dbname' => 'ztlrolamentos');
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('g'=>'tb_garantiaztl','*'),
			        array('p.id_prod','count(p.id_prod) as qttotal'))
			        ->join(array('p'=>'tb_garantiaztl_proddet'),'p.id_garantiaztl = g.id')
			        ->join(array('pp'=>'produtos'),'p.id_prod = pp.ID')
			        ->where("g.sit = true and p.substituir = true ".$where)
			        ->group("p.id_prod");
			  
			$stmt = $db->query($select);
						
			return $stmt->fetchAll();		
		
		}
		
		//---- Relatorios garantias negadas ZTL---------------------------------------
		 function relatorioGarantiapagnegztl($pesq){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			if(!empty($pesq['prod'])):
				$where = " and pp.CODIGO = '".$pesq['prod']."'";
			elseif ($pesq['buscagruposub']!=0):
				$where = " and pp.id_gruposprodsub = ".$pesq['buscagruposub'];
			elseif ($pesq['grupo']!=0):
				foreach (GruposprodBO::listaGruposprodutossub($pesq['grupo']) as $listsubg):
					$idsg .= $listsubg->id.",";
				endforeach;
				$where = " and pp.id_gruposprodsub in (".substr($idsg, 0,-1).") ";
			endif;
			
			
			if($pesq['clientes']!=0):
				$where .= " and g.id_clientes = '".$pesq['clientes']."'";
			elseif (!empty($pesq['uf'])):
				$where .= " and c.ESTADO = '".$pesq['uf']."'";
			else:
				$where .= " and cl.ID_REGIOES = '".$usuario->ID_REGIOES."'";
			endif;
			
			$params = array (
			    'host'     => '127.0.0.1',
			    'username' => 'ztlrolamentos',
			    'password' => 'BdMySql2008',
			    'dbname'   => 'ztlrolamentos'
			);
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			/*$select->from(array('g'=>'tb_garantiaztl','*'),
			        array('gp.id_analisegar','count(gp.id) as qttotal','gp.desc_outros','a.descricao','a.id as idanalise'))
			        ->join(array('p'=>'tb_garantiaztl_proddet'),'p.id_garantiaztl = g.id')
			        ->join(array('pp'=>'produtos'),'p.id_prod = pp.ID')
			        ->join(array('gp'=>'tb_garanaliseprod'),'gp.id_proddet = p.id')
			        ->join(array('c'=>'clientes_endereco'),'g.id_clientes = c.ID_CLIENTE')
			        ->join(array('cl'=>'clientes'),'g.id_clientes = cl.ID')
			        ->joinLeft(array('a'=>'tb_garanalise'),'a.id = gp.id_analisegar')
			        ->where("c.TIPO = 1 and g.sit = true and p.substituir = false and TO_DAYS(NOW()) - TO_DAYS(g.data_cad) <= 365  ".$where)
			        ->order("gp.id_analisegar desc")
			        ->group("gp.id_analisegar");*/
			
			$select->from(array('g'=>'tb_garantiaztl','*'),
			        array('gp.id_analisegar','count(gp.id_analisegar) as qtanalise','count(gp.desc_outros) as qtanaliseout','a.descricao'))
			        ->join(array('p'=>'tb_garantiaztl_proddet'),'p.id_garantiaztl = g.id')
			        ->join(array('pp'=>'produtos'),'p.id_prod = pp.ID')
			        ->join(array('gp'=>'tb_garanaliseprod'),'gp.id_proddet = p.id')
			        ->joinLeft(array('a'=>'tb_garanalise'),'a.id = gp.id_analisegar')
			        ->join(array('c'=>'clientes_endereco'),'g.id_clientes = c.ID_CLIENTE')
			        ->join(array('cl'=>'clientes'),'g.id_clientes = cl.ID')
			        ->where("c.TIPO = 1 and g.sit = true and p.substituir = false and TO_DAYS(NOW()) - TO_DAYS(g.data_cad) <= 365  ".$where)
			        ->order("count(gp.id_analisegar) desc")
			        ->group("gp.id_analisegar");
			  
			$stmt = $db->query($select);
						
			return $stmt->fetchAll();		

			
			/* 
			 *  				
				select ga.id_analisegar, count(ga.id_analisegar), count(ga.desc_outros) , e.descricao 
				from 
				tb_garantiaztl_proddet gd, 
				produtos p, 
				tb_garanaliseprod ga left join tb_garanalise e on (e.id = ga.id_analisegar),
				tb_garantiaztl g,
				clientes c,
				clientes_endereco ce
								
				where gd.substituir = false 
				and gd.id_prod = p.ID
				and ga.id_proddet = gd.id
				and g.id = gd.id_garantiaztl
				and g.id_clientes = c.ID
				and ce.ID_CLIENTE = c.ID 
				and ce.TIPO = 1
				and ce.ESTADO = 26
				group by (ga.id_analisegar)
				;
			;*/
						
		}

		 function relatorioGarantiapagnegztldesc($pesq){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			if(!empty($pesq['prod'])):
				$where = " and pp.CODIGO = '".$pesq['prod']."'";
			elseif (!empty($pesq['grupo'])):
				//$where = " and pp.ID_GRUPO = ".$pesq['grupo'];
				foreach (GruposprodBO::listaGruposprodutossub($pesq['grupo']) as $listag):
					$idsg	.= $listag->id.",";
				endforeach;
				$where = " and pp.id_gruposprodsub in (".substr($idsg,0,-1).")";
			endif;
			
			if($pesq['clientes']!=0):
				$where .= " and g.id_clientes = '".$pesq['clientes']."'";
			elseif (!empty($pesq['uf'])):
				$where .= " and c.ESTADO = '".$pesq['uf']."'";
			else:
				$where .= " and cl.ID_REGIOES = '".$usuario->ID_REGIOES."'";
			endif;
			
			$params = array (
			    'host'     => '127.0.0.1',
			    'username' => 'ztlrolamentos',
			    'password' => 'BdMySql2008',
			    'dbname'   => 'ztlrolamentos'
			);
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('g'=>'tb_garantiaztl','*'),
			        array('gp.id_analisegar','count(gp.id) as qttotal','gp.desc_outros','a.descricao','a.id as idanalise'))
			        ->join(array('p'=>'tb_garantiaztl_proddet'),'p.id_garantiaztl = g.id')
			        ->join(array('pp'=>'produtos'),'p.id_prod = pp.ID')
			        ->join(array('gp'=>'tb_garanaliseprod'),'gp.id_proddet = p.id')
			        ->join(array('c'=>'clientes_endereco'),'g.id_clientes = c.ID_CLIENTE')
			        ->join(array('cl'=>'clientes'),'g.id_clientes = cl.ID')
			        ->joinLeft(array('a'=>'tb_garanalise'),'a.id = gp.id_analisegar')
			        ->where("c.TIPO = 1 and g.sit = true and p.substituir = false and TO_DAYS(NOW()) - TO_DAYS(g.data_cad) <= 365  ".$where)
			        ->order("gp.id_analisegar asc")
			        ->group("gp.id_analisegar");
			  
			$stmt = $db->query($select);
						
			return $stmt->fetchAll();	
		}
		
		
		
		 function listaGarautoriza(){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			//$where = " and cl.ID_REGIOES = '".$usuario->ID_REGIOES."'";
			
			$params = array (
			    'host'     => '127.0.0.1',
			    'username' => 'ztlrolamentos',
			    'password' => 'BdMySql2008',
			    'dbname'   => 'ztlrolamentos'
			);
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('g'=>'tb_garantiaztl','*'),
			        array('g.id','g.status'))
			        ->join(array('cl'=>'clientes'),'g.id_clientes = cl.ID')
			        ->where("g.sit = true and g.status like 'AGUARDANDO%' ".$where)
			        ->order("g.id asc");
			        
			  
			$stmt = $db->query($select);
						
			return $stmt->fetchAll();	
			
		}
		
		 function listaGaranalise(){
			$bo			= new RegioesModel();
			$bor		= new RegioesclientesModel();
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
		
			foreach (PerfilBO::listarPerfil($usuario->tb_perfil_id) as $list);
			if($list->nivel == 1):
				foreach ($bor->fetchAll("id_clientes = ".$usuario->ID) as $regioes):
					$reg .= $regioes->id_regioes.",";
				endforeach;				
				$where = " and cl.ID_REGIOES in (".substr($reg,0,-1).")";
			endif;
			
			$params = array (
			    'host'     => '127.0.0.1',
			    'username' => 'ztlrolamentos',
			    'password' => 'BdMySql2008',
			    'dbname'   => 'ztlrolamentos'
			);
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('g'=>'tb_garantiaztl','*'),
			        array('g.id as idg','g.status','cl.EMPRESA','g.data_chegada','sum(gp.qt) as qtt'))
			        ->join(array('cl'=>'clientes'),'g.id_clientes = cl.ID')
			        ->join(array('gp'=>'tb_garantiaztl_prod'),'gp.id_garantiaztl = g.id')
			        ->where("g.sit = true and g.status like 'RECEBIDO%' ".$where)
			        ->order("g.id asc")
			        ->group("g.id");
			        
			  
			$stmt = $db->query($select);
						
			return $stmt->fetchAll();	
			
		}
		
		 function listaGaranalisecliente(){
			$bo			= new RegioesModel();
			$bor		= new RegioesclientesModel();
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
		
			foreach (PerfilBO::listarPerfil($usuario->tb_perfil_id) as $list);
			if($list->nivel == 1):
				foreach ($bor->fetchAll("id_clientes = ".$usuario->ID) as $regioes):
					$reg .= $regioes->id_regioes.",";
				endforeach;				
				$where = " and cl.ID_REGIOES in (".substr($reg,0,-1).")";
			elseif($list->nivel == 0):
				$where = " and cl.ID = ".$usuario->ID;
			endif;
			
			
			$params = array (
			    'host'     => '127.0.0.1',
			    'username' => 'ztlrolamentos',
			    'password' => 'BdMySql2008',
			    'dbname'   => 'ztlrolamentos'
			);
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('g'=>'tb_garantiaztl','*'),
			        array('g.id as idg','g.status','cl.EMPRESA','DATE_FORMAT(g.data_atualizacao,"%d/%m/%Y" ) as dtat','g.nota_fiscal'))
			        ->join(array('cl'=>'clientes'),'g.id_clientes = cl.ID')
			        ->where("g.sit = true and g.status like '%CONCLUIDA' ".$where)
			        ->order("g.data_atualizacao desc");
			        
			$stmt = $db->query($select);
						
			return $stmt->fetchAll();	
			
		}		
		
		//------- Pedidos vendas ---------------------------------
		//--Lista Pedidos de venda---------------------------
		 function listaPedidosvenda($val){
			$bo		= new RegioesModel();
			$bor	= new RegioesclientesModel();
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			$dataini = substr($val['dataini'],6,4).'-'.substr($val['dataini'],3,2).'-'.substr($val['dataini'],0,2);
			$datafin = substr($val['datafin'],6,4).'-'.substr($val['datafin'],3,2).'-'.substr($val['datafin'],0,2);
			
			if(!empty($val['buscaid'])):
				$where = " and t.id = ".substr($val['buscaid'],1);
			elseif((!empty($val['dataini'])) and (!empty($val['datafin']))):
				$where = " and t.data_vend between '".$dataini."' and '".$datafin."'";
			elseif((!empty($val['dataini'])) and (empty($val['datafin']))):
				$where = " and t.data_vend >= '".$dataini."'";
			elseif((empty($val['dataini'])) and (!empty($val['datafin']))):
				$where = " and t.data_vend <= '".$datafin."'";
			elseif(!empty($val['buscacli'])):
				$where = " and t.id_parceiro = ".$val['buscacli'];
			elseif(!empty($val['buscauf'])):
				$where = " and ce.ESTADO = '".$val['buscauf']."'";
			elseif(!empty($val['buscareg'])):
				$where = " and c.ID_REGIOES = '".$val['buscareg']."'";
			endif;
			   
		   	/*$sessaobusca = new Zend_Session_Namespace('Default');
		   	
		   	if(!empty($where)):
		   		//$sessaobusca->where = $where;
		   	elseif(isset($sessaobusca->where)):
		   		//$where = $sessaobusca->where;
		   	endif;*/
		  
			$sql = "";
			foreach (PerfilBO::listarPerfil($usuario->tb_perfil_id) as $list);
			if($list->nivel == 1):
				foreach ($bor->fetchAll("id_clientes = ".$usuario->ID) as $regioes):
					$reg .= $regioes->id_regioes.",";
				endforeach;				
				$where .= " and ID_REGIOES in (".substr($reg,0,-1).")";
			endif;
			
			
			$params = array (
			    'host'     => '127.0.0.1',
			    'username' => 'ztlrolamentos',
			    'password' => 'BdMySql2008',
			    'dbname'   => 'ztlrolamentos'
			);
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_pedidos','*'),
			        array('t.id','DATE_FORMAT(t.data_vend,"%d/%m/%Y %H:%i" ) as dtvenda','c.EMPRESA','(select EMPRESA from clientes cl where cl.ID = t.id_user) as vendedor'))
			        ->join(array('c'=>'clientes'),'t.id_parceiro = c.id')
			        ->where("t.status = 'ped' and t.sit = 0  ".$where)
			        ->order('t.id desc','');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		//--Lista Pedidos de venda clientes---------------------------
		 function listaPedidosvendacli($val){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			$dataini = substr($val['dataini'],6,4).'-'.substr($val['dataini'],3,2).'-'.substr($val['dataini'],0,2);
			$datafin = substr($val['datafin'],6,4).'-'.substr($val['datafin'],3,2).'-'.substr($val['datafin'],0,2);
			
			if(!empty($val['buscaid'])):
				$where = " and t.id = ".substr($val['buscaid'],1);
			elseif((!empty($val['dataini'])) and (!empty($val['datafin']))):
				$where = " and t.data_cad between '".$dataini."' and '".$datafin."'";
			elseif((!empty($val['dataini'])) and (empty($val['datafin']))):
				$where = " and t.data_cad >= '".$dataini."'";
			elseif((empty($val['dataini'])) and (!empty($val['datafin']))):
				$where = " and t.data_cad <= '".$datafin."'";
			endif;

			$where .= " and c.ID = ".$usuario->ID;
		   	
			$params = array (
			    'host'     => '127.0.0.1',
			    'username' => 'ztlrolamentos',
			    'password' => 'BdMySql2008',
			    'dbname'   => 'ztlrolamentos'
			);
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_pedidos','*'),
			        array('t.id','DATE_FORMAT(t.data_cad, "%d/%m/%Y") as dtvenda','c.EMPRESA','(select EMPRESA from clientes cl where cl.ID = t.id_user) as vendedor','t.status'))
			        ->join(array('c'=>'clientes'),'t.id_parceiro = c.id')
			        ->where("t.sit = 0  ".$where)
			        ->order('t.id desc','');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		//--Lista pedidos-------------------------------------------------------------------------
		 function listaPedidospendentes($var){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			$bo		= new RegioesModel();
			$bor	= new RegioesclientesModel();

			//--- Busca memorizada ------------------------------------------
			$sessaobusca = new Zend_Session_Namespace('pedidosvenda');
		    if(isset($sessaobusca->where)):
		   		$where = $sessaobusca->where;
		   	endif;
		   	
		   	if(isset($sessaobusca->wheredata)):
		   		$wheredata = $sessaobusca->wheredata;
		   	endif;
			
		   	//--- Nova busca ------------------------------------------
		   	if(($var['tipo']==1) and ($var['id']!="")):
				$where = " and t.id = ".$var['id'];				
			elseif($var['tipo']==2):				
				$where = " and t.id_representante = ".$var['representante'];
			elseif($var['tipo']==3):
				$where = " and t.id_user = ".$var['vendedor'];
				
			elseif($var['tipo']==4):
				$where = " and t.id_parceiro = ".$var['cliente'];
				
			elseif($var['tipo']==5):
				if($var['situacao']=="1"):
					$where = " and t.status = 'orc' and t.sit = 0";
				elseif($var['situacao']=="2"):
					$where = " and t.status = 'ped' and t.sit = 0";
				elseif($var['situacao']=="3"):
					$where = " and t.sit = 1";
				endif;				
			endif;
			
			//--- Data ----------------------------------------------
			if($var['tpdata']==2):
				if(!empty($var['dataini']) || !empty($var['datafim'])):
					if(!empty($var['dataini']) and !empty($var['datafim'])):
						$dataini = substr($var['dataini'],6,4).'-'.substr($var['dataini'],3,2).'-'.substr($var['dataini'],0,2);
						$datafim = substr($var['datafim'],6,4).'-'.substr($var['datafim'],3,2).'-'.substr($var['datafim'],0,2);			
						$wheredata = ' and t.data_vend BETWEEN "'.$dataini.'" and "'.$datafim.'  23:59:59"';
					elseif (!empty($var['dataini'])):
						$dataini = substr($var['dataini'],6,4).'-'.substr($var['dataini'],3,2).'-'.substr($var['dataini'],0,2);
						$wheredata = ' and t.data_vend >= "'.$dataini.' 23:59:59"';
					elseif (!empty($var['datafim'])):
						$datafim = substr($var['datafim'],6,4).'-'.substr($var['datafim'],3,2).'-'.substr($var['datafim'],0,2);
						$wheredata = ' and t.data_vend <= "'.$datafim.' 23:59:59"';
					endif;
				endif;
			elseif($var['tpdata']==1):	
				if(!empty($var['dataini']) || !empty($var['datafim'])):
					if(!empty($var['dataini']) and !empty($var['datafim'])):
						$dataini = substr($var['dataini'],6,4).'-'.substr($var['dataini'],3,2).'-'.substr($var['dataini'],0,2);
						$datafim = substr($var['datafim'],6,4).'-'.substr($var['datafim'],3,2).'-'.substr($var['datafim'],0,2);			
						$wheredata = ' and t.data_cad BETWEEN "'.$dataini.'" and "'.$datafim.'  23:59:59"';
					elseif (!empty($var['dataini'])):
						$dataini = substr($var['dataini'],6,4).'-'.substr($var['dataini'],3,2).'-'.substr($var['dataini'],0,2);
						$wheredata = ' and t.data_cad >= "'.$dataini.' 23:59:59"';
					elseif (!empty($var['datafim'])):
						$datafim = substr($var['datafim'],6,4).'-'.substr($var['datafim'],3,2).'-'.substr($var['datafim'],0,2);
						$wheredata = ' and t.data_cad <= "'.$datafim.' 23:59:59"';
					endif;
				endif;
			endif;
			
			//--- Controle de perfil ------------------------------------------
			foreach (PerfilBO::listarPerfil($usuario->tb_perfil_id) as $list);
			if($list->nivel==1):
				$sql 	= "";				
				foreach (PerfilBO::listarPerfil($usuario->tb_perfil_id) as $list);
				if($list->nivel == 1):
					foreach ($bor->fetchAll("id_clientes = ".$usuario->ID) as $regioes):
						$reg .= $regioes->id_regioes.",";
					endforeach;				
					$where .= " and c.ID_REGIOES in (".substr($reg,0,-1).")";
				endif;				
			elseif($list->nivel==0):
				$where .= " and c.ID = ".$usuario->ID;
			endif;
			
			if(!empty($where)):
		   		$sessaobusca->where = $where;
		   	endif;
		   	
			if(!empty($wheredata)):
		   		$sessaobusca->wheredata = $wheredata;
		   	endif;		   	
		   	
			$params = array ('host'     => '127.0.0.1', 'username' => 'ztlrolamentos', 'password' => 'BdMySql2008', 'dbname'   => 'ztlrolamentos');
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_pedidos','*'),
			        array('t.id','DATE_FORMAT(t.data_cad,"%d/%m/%Y %H:%i") as dtcad','DATE_FORMAT(t.data_vend,"%d/%m/%Y %H:%i") as dtvenda',
			        'c.EMPRESA as parceiro','(select EMPRESA from clientes cl where cl.ID = t.id_user) as vendedor','c.ID as idcli',
			        't.status as statusped','t.sit as sitped', 'cr.EMPRESA as representante'))
			        ->join(array('c'=>'clientes'),'t.id_parceiro = c.id')
			        ->joinLeft(array('cr'=>'clientes'),'t.id_representante = cr.id')
			        ->where("t.id is not NULL ".$where.$wheredata)
			        ->order('t.id desc','');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		//--Lista pedidos exportar ----------------------------------------------------------
		 function listaPedidosexportar($var){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			$bo		= new RegioesModel();
			$bor	= new RegioesclientesModel();
			
			
			//--- Busca memorizada ------------------------------------------
			$sessaobusca = new Zend_Session_Namespace('pedidosvenda');
		    if(isset($sessaobusca->where)):
		   		$where = $sessaobusca->where;
		   	endif;
		   	
		   	if(isset($sessaobusca->wheredata)):
		   		$wheredata = $sessaobusca->wheredata;
		   	endif;
			
			//--- Controle de perfil ------------------------------------------
			foreach (PerfilBO::listarPerfil($usuario->tb_perfil_id) as $list);
			if($list->nivel==1):
				$sql 	= "";				
				foreach (PerfilBO::listarPerfil($usuario->tb_perfil_id) as $list);
				if($list->nivel == 1):
					foreach ($bor->fetchAll("id_clientes = ".$usuario->ID) as $regioes):
						$reg .= $regioes->id_regioes.",";
					endforeach;				
					$where .= " and c.ID_REGIOES in (".substr($reg,0,-1).")";
				endif;				
			elseif($list->nivel==0):
				$where .= " and c.ID = ".$usuario->ID;
			endif;
				   	
		   	$pdoParams = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8;');
			$params = array ('host'     => '127.0.0.1', 'username' => 'ztlrolamentos', 'password' => 'BdMySql2008', 'dbname'   => 'ztlrolamentos',  'driver_options' => $pdoParams);
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_pedidos','*'),
			        array('t.id','DATE_FORMAT(t.data_cad,"%d/%m/%Y %H:%i") as dtcad','DATE_FORMAT(t.data_vend,"%d/%m/%Y %H:%i") as dtvenda','c.EMPRESA','(select EMPRESA from clientes cl where cl.ID = t.id_user) as vendedor','c.ID as idcli','t.status as statusped','t.sit as sitped','sum(tp.qt*tp.preco_unit) as precototalped'))
			        ->join(array('c'=>'clientes'),'t.id_parceiro = c.id')
			        ->join(array('tp'=>'tb_pedidos_prod'),'t.id = tp.id_ped')
			        ->where("t.id is not NULL ".$where.$wheredata)
			        ->order('t.id desc','')
			        ->group("t.id");
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
	//--Lista pedidos PAINEL-------------------------------------------------------------------------
		 function listaPedidospainel(){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			$bo		= new RegioesModel();
			$bor	= new RegioesclientesModel();

			//--- Controle de perfil ------------------------------------------
			foreach (PerfilBO::listarPerfil($usuario->tb_perfil_id) as $list);
			if($list->nivel==1):
				$sql 	= "";				
				foreach (PerfilBO::listarPerfil($usuario->tb_perfil_id) as $list);
				if($list->nivel == 1):
					foreach ($bor->fetchAll("id_clientes = ".$usuario->ID) as $regioes):
						$reg .= $regioes->id_regioes.",";
					endforeach;				
					$where .= " and c.ID_REGIOES in (".substr($reg,0,-1).")";
				endif;				
			elseif($list->nivel==0):
				$where .= " and c.ID = ".$usuario->ID;
			endif;
		   	
			$pdoParams = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8;');
			$params = array ('host' => '127.0.0.1', 'username' => 'ztlrolamentos', 'password' => 'BdMySql2008', 'dbname' => 'ztlrolamentos', 'driver_options' => $pdoParams);
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_pedidos','*'),
			        array('t.id','DATE_FORMAT(t.data_cad,"%d/%m/%Y %H:%i") as dtcad','DATE_FORMAT(t.data_vend,"%d/%m/%Y %H:%i") as dtvenda','c.EMPRESA','(select EMPRESA from clientes cl where cl.ID = t.id_user) as vendedor','c.ID as idcli','t.status as statusped','t.sit as sitped'))
			        ->join(array('c'=>'clientes'),'t.id_parceiro = c.id')
			        ->where("t.status = 'orc' and sit = 0")
			        ->order('t.id desc','');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		//--Lista produtos pedidos---------------------------
		 function listaPedidosprod($var){
			
			$pdoParams = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8;');
			$params = array ('host' => '127.0.0.1', 'username' => 'ztlrolamentos', 'password' => 'BdMySql2008', 'dbname' => 'ztlrolamentos', 'driver_options' => $pdoParams);
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_pedidos_prod','*'),
			        array('t.qt', 't.promo', 't.preco_alt', 't.preco_unit', 't.preco_tabela', 'p.ID', 'p.CODIGO', 'p.PRECO_UNITARIO', 
			        'p.DESCRICAO', 'p.APLICACAO', 'p.valor_promo', 'p.valor_desc','e.qt_atual','p.PESO as pesoliquido'))
			        ->join(array('p'=>'produtos'),'t.id_prod = p.ID')
			        ->joinLeft(array('e'=>'tb_estoqueztl'),'t.id_prod = e.id_prod and e.id = (SELECT max(id) from tb_estoqueztl e where t.id_prod = e.id_prod)')
			        ->where("t.id_ped = ".$var);
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		function buscaLocalprodutos($var){
			
			$pdoParams = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8;');
			$params = array ('host' => '127.0.0.1', 'username' => 'ztlrolamentos', 'password' => 'BdMySql2008', 'dbname' => 'ztlrolamentos', 'driver_options' => $pdoParams);
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_pedidos_prod','*'), array('*'))
			        ->join(array('p'=>'produtos_localizacao'),'t.id_prod = p.id_prod')
			        ->where("t.id_ped = ".$var);
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		
		
		function buscaPedido($var){
			$bo		= new PedidosvendaModel();
			return $bo->fetchAll("md5(id) = '".$var['ped']."'");
		}
		
		function gravaPedido($params){
			$bov	= new PedidosvendaModel();
			$bop	= new PedidosvendaprodModel();
			
			if(!empty($params[qt])):			
				foreach (ClientesBO::listaDesc($params[pedcli]) as $listDes);
				
				foreach (ProdutosBO::buscaProdutoscodigo($params[codigo]) as $lista);
				
				$array[id_ped]			= $params[ped];
				$array[id_prod]			= $lista->ID;
				$array[preco_tabela]	= $lista->PRECO_UNITARIO;
				$array[qt]				= $params[qt];
				
				if(!empty($params[promo])): 
					$array[promo] = 1;
					
	                if(($lista->valor_promo!='') and ($lista->valor_promo!=0)): 
	                	$valor_unitario = $lista->valor_promo;
	                	
	                elseif(($lista->valor_desc!='') and ($lista->valor_desc!=0)):
	                    $valor_unitario = $lista->PRECO_UNITARIO;
	                    
	                    $valor_unitario = $valor_unitario - (($listDes->desc1 / 100)*$valor_unitario);
	                    $valor_unitario = $valor_unitario - (($listDes->desc2 / 100)*$valor_unitario);
	                    $valor_unitario = $valor_unitario - (($listDes->desc3 / 100)*$valor_unitario);
	                    $valor_unitario = $valor_unitario - (($listDes->desc4 / 100)*$valor_unitario);
	                    $valor_unitario = $valor_unitario - (($listDes->desc5 / 100)*$valor_unitario);
	                
	                    $valor_unitario = $valor_unitario - (($listDes->valor_desc / 100)*$valor_unitario);
					endif;
	            else:
	                $valor_unitario = $lista->PRECO_UNITARIO;
	                $valor_unitario = $valor_unitario - (($listDes->desc1 / 100)*$valor_unitario);
	            	$valor_unitario = $valor_unitario - (($listDes->desc2 / 100)*$valor_unitario);
	                $valor_unitario = $valor_unitario - (($listDes->desc3 / 100)*$valor_unitario);
	                $valor_unitario = $valor_unitario - (($listDes->desc4 / 100)*$valor_unitario);
	                $valor_unitario = $valor_unitario - (($listDes->desc5 / 100)*$valor_unitario);
	               
				endif;
				
				$valor_unitario = round($valor_unitario * 100) / 100; 
	    		$array[preco_unit] = $valor_unitario;
				
				$bop->insert($array);
			endif;			
		}
		
		
		//----Orcamentos--------------------------------------------------
		 function gravaProdorcamentos($params){
			$bov	= new OrcamentosvendaModel();
			$bop	= new OrcamentosvendaprodModel();
			
			if(!empty($params[qt])):			
				foreach (ProdutosBO::buscaProdutoscodigo($params[codigo]) as $lista);
				
				$array[id_pedido_tmp]	= $params[ped];
				$array[id_prod]			= $lista->ID;
				$array[qt]				= $params[qt];
				$array[promo]			= $params[promo];
				
				$bop->insert($array);
			endif;			
		}
		
		 function rmProdorcamentos($params){
			$bov	= new OrcamentosvendaModel();
			$bop	= new OrcamentosvendaprodModel();
			
			$bop->delete("id = ".$params);						
		}
		
		function removePendorcamentos($params){
			$bo = new PendenciasModel();
			
			$data['id_peddes']	= $params['ped'];
			$bo->update($data,"id = ".$params['id']);						
		}
		
	 	function rmOrcamentos($params){
			$bov	= new OrcamentosvendaModel();
			
			$data['status']	= 0;
			$bov->update($data, "md5(id) = '".$params['orcamento']."'");						
		}
		
		function rmPedidosdevenda($params){
			$bov	= new PedidosvendaModel();
			
			$data['sit']	= 1;
			$bov->update($data, "md5(id) = '".$params['pedido']."'");						
		}		
		
		function removePendorcamentosall($params){
			$bo = new PendenciasModel();
			
			$data['id_peddes']	= $params['ped'];
			$bo->update($data,"status = 0 and id_cliente = ".$params['idcli']);						
		}
		
		//--Lista produtos orcamento---------------------------
		 function listaPedidosorc($ped){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			$pdoParams = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8;');
			$params = array ('host' => '127.0.0.1', 'username' => 'ztlrolamentos', 'password' => 'BdMySql2008', 'dbname' => 'ztlrolamentos', 'driver_options' => $pdoParams);
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_pedidos_tmp_prod','*'),
			        array('t.id as idprorc','t.qt','p.ID as id_prod', 'p.CODIGO', 'p.PRECO_UNITARIO','p.DESCRICAO', 'p.APLICACAO', 'p.valor_promo', 'p.valor_desc','e.qt_atual','t.promo'))
			        ->join(array('p'=>'produtos'),'t.id_prod = p.ID')
			        ->join(array('e'=>'tb_estoqueztl'),'t.id_prod = e.id_prod and e.id = (SELECT max(id) from tb_estoqueztl e where t.id_prod = e.id_prod)')
			        ->where("t.id_pedido_tmp = ".$ped);
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		 function editaProdpedidos($params){
			$bov	= new PedidosvendaModel();
			$bop	= new PedidosvendaprodModel();
			$bo 	= new PendenciasModel();
			$usuario = Zend_Auth::getInstance()->getIdentity();
			
			foreach (VendaBO::listaPedidosprod($params[ped]) as $listProd):
				if(!empty($params['editqt_'.$listProd->ID])):
					$array_up[qt]	= $params['editqt_'.$listProd->ID];
					if($listProd->qt > $params['editqt_'.$listProd->ID]):
						$array[id_ped]		= $params[ped];
						$array[id_cliente]	= $params[pedcli];
						$array[id_user]		= $usuario->ID;
						$array[dt_pend]		= date("Y-m-d H:i:s");
						$array[id_prod]		= $listProd->ID;
						$array[qt]			= $listProd->qt-$params['editqt_'.$listProd->ID];
						$array[valor]		= $params['pendvl_'.$listProd->ID];
						$bo->insert($array);
					endif;
				endif;
				if(!empty($params['editvl_'.$listProd->ID])):
					$array_up[preco_alt]	= str_replace(",",".",str_replace(".","",$params['editvl_'.$listProd->ID]));
					$array_up[preco_unit]	= str_replace(",",".",str_replace(".","",$params['editvl_'.$listProd->ID]));
				endif;
				$bop->update($array_up,"id_ped = ".$params[ped]." and id_prod = ".$listProd->ID);
			endforeach;
		}
		
		 function removeProdpedidos($params){
			$bov	= new PedidosvendaModel();
			$bop	= new PedidosvendaprodModel();
			$bo 	= new PendenciasModel();
			$usuario = Zend_Auth::getInstance()->getIdentity();
						
			$array[id_ped]		= $params[ped];
			$array[id_cliente]	= $params[pedcli];
			$array[id_user]		= $usuario->ID;
			$array[dt_pend]		= date("Y-m-d H:i:s");
			$array[id_prod]		= $params[idprod];
			$array[qt]			= $params[qt];
			$array[valor]		= $params[valor];
			$bo->insert($array);
			
			$bop->delete("id_ped = ".$params[ped]." and id_prod = ".$params[idprod]);
			
			
		}
		
		 function gerarVenda($params){
			$bov	= new PedidosvendaModel();
			$bop	= new PedidosvendaprodModel();
			$bopr	= new ProdutosModel();
			$boc	= new ProdutoscmvModel();
			$bo 	= new EstoqueModel();
			$usuario = Zend_Auth::getInstance()->getIdentity();
			
			foreach (RegioesBO::buscaRegioesporcliente($params[pedcli]) as $regiao);
			$array["id_user"]			= $usuario->ID;
						
			$array["prazo1"] 		= $params[prazo1];
			$array["prazo2"] 		= $params[prazo2];
			$array["prazo3"] 		= $params[prazo3];
			$array["prazo4"] 		= $params[prazo4];
			$array["prazo5"] 		= $params[prazo5];
			$array["data_vend"]		= date("Y-m-d H:i:s");
			$array["status"]	 	= "ped";
			$array["obs"] 			= $params[obs];
			$bov->update($array,"id = ".$params[ped]);
			
			$usuario = Zend_Auth::getInstance()->getIdentity();
			
			foreach (VendaBO::listaPedidosprod($params[ped]) as $listProd):
				foreach ($boc->fetchAll("id = (select max(v.id) from tb_produtoscmv v where v.id_produtos = ".$listProd->ID.")") as $listacmv);

				if(count($listacmv)>0):
					$array_up['custocompra']	= $listacmv->valor;			
					$bop->update($array_up,"id_ped = ".$params[ped]." and id_prod = ".$listProd->ID);
				endif;

				foreach (EstoqueBO::buscaEstoque($listProd->ID) as $estoque);
				if(count($estoque)>0):
					$qt_atual	= $estoque->qt_atual;
				endif;
				
				$arrayestq = array();
				$arrayestq['id_prod'] 			= $listProd->ID;
				$arrayestq['qt_atual'] 			= $qt_atual-$listProd->qt;
				$arrayestq['qt_atualizacao'] 	= $listProd->qt;
				$arrayestq['id_atualizacao'] 	= $params[ped];
				$arrayestq['dt_atualizacao'] 	= date("Y-m-d H:i:s");
				$arrayestq['tipo'] 				= "VENDA";
				$arrayestq['id_user'] 			= $usuario->ID;
				$bo->insert($arrayestq);
				
			endforeach;
			
			LogBO::cadastraLog("Vendas/Pedidos",4,$usuario->ID,$params[ped],"VENDA ".$params[ped]);
			
		}
				
		function gerarPedido($params){
			$bov		= new PedidosvendaModel();
			$bop		= new PedidosvendaprodModel();
			$boo		= new OrcamentosvendaModel();
			$bopend		= new PendenciasModel();
			$usuario	= Zend_Auth::getInstance()->getIdentity();			
			
			foreach (RegioesBO::buscaRegioesporcliente($params[pedcli]) as $regiao);
			
			$arrayped["prazo1"] 			= $params[prazo1];
			$arrayped["prazo2"] 			= $params[prazo2];
			$arrayped["prazo3"] 			= $params[prazo3];
			$arrayped["prazo4"] 			= $params[prazo4];
			$arrayped["prazo5"] 			= $params[prazo5];
			$arrayped["id_parceiro"]		= $params[pedcli];
			$arrayped["id_user"]			= $usuario->ID;
			$arrayped["id_representante"]	= $regiao->id_clientes;
			$arrayped["data_cad"]			= date("Y-m-d H:i:s");
			$arrayped["status"]	 			= "orc";
			$arrayped["obs"] 				= $params[obs];
			$id = $bov->insert($arrayped);

			
			foreach (ProdutosBO::listaallProdutos() as $lista):
				if(!empty($params[$lista->ID])):			
					foreach (ClientesBO::listaDesc($params[pedcli]) as $listDes);
				
					$array[id_ped]			= $id;
					$array[id_prod]			= $lista->ID;
					$array[preco_tabela]	= $lista->PRECO_UNITARIO;
					$array[qt]				= $params[$lista->ID];
				
					if(!empty($params[promo])): 
						$array[promo] = 1;
						
		                if(($lista->valor_promo!='') and ($lista->valor_promo!=0)): 
		                	$valor_unitario = $lista->valor_promo;
		                	
		                elseif(($lista->valor_desc!='') and ($lista->valor_desc!=0)):
		                    $valor_unitario = $lista->PRECO_UNITARIO;
		                    
		                    $valor_unitario = $valor_unitario - (($listDes->desc1 / 100)*$valor_unitario);
		                    $valor_unitario = $valor_unitario - (($listDes->desc2 / 100)*$valor_unitario);
		                    $valor_unitario = $valor_unitario - (($listDes->desc3 / 100)*$valor_unitario);
		                    $valor_unitario = $valor_unitario - (($listDes->desc4 / 100)*$valor_unitario);
		                    $valor_unitario = $valor_unitario - (($listDes->desc5 / 100)*$valor_unitario);
		                
		                    $valor_unitario = $valor_unitario - (($listDes->valor_desc / 100)*$valor_unitario);
						endif;
		            else:
		                $valor_unitario = $lista->PRECO_UNITARIO;
		                $valor_unitario = $valor_unitario - (($listDes->desc1 / 100)*$valor_unitario);
		            	$valor_unitario = $valor_unitario - (($listDes->desc2 / 100)*$valor_unitario);
		                $valor_unitario = $valor_unitario - (($listDes->desc3 / 100)*$valor_unitario);
		                $valor_unitario = $valor_unitario - (($listDes->desc4 / 100)*$valor_unitario);
		                $valor_unitario = $valor_unitario - (($listDes->desc5 / 100)*$valor_unitario);
		               
					endif;
					
					$valor_unitario = round($valor_unitario * 100) / 100; 
		    		$array[preco_unit] = $valor_unitario;
					
					$bop->insert($array);
					
					$arraypend['status']		= 1;
					$arraypend['id_ped_fat']	= $id;
					$arraypend['dt_fat']		= date("Y-m-d H:i:s");
					$bopend->update($arraypend, "id_prod = ".$lista->ID." and id_cliente = ".$params[pedcli]);					
					
				endif;	
			endforeach;
			
			$dataor['status']	 = 0;
			$boo->update($dataor,"id = ".$params['ped']);
		}
		
		function gerarOrcamento($params){
			$bo	= new OrcamentosvendaModel();
			$usuario = Zend_Auth::getInstance()->getIdentity();
			
			$array["id_user"] 			= $usuario->ID;
			$array["id_cliente"] 		= $params;
			$array["status"] 			= 1;
			$array["dt_atualizacao"] 	= date("Y-m-d H:i:s");
			
			return $bo->insert($array);	
			
		}
		
		//--Lista Orcamentos-------------------------------------------------------------------------
		function listaOrcamentos(){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();			
			$params = array ( 'host'     => '127.0.0.1',
			    'username' => 'ztlrolamentos',
			    'password' => 'BdMySql2008',
			    'dbname'   => 'ztlrolamentos'
			);
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_pedidos_tmp','*'),
			        array('t.id','DATE_FORMAT(t.dt_atualizacao,"%d/%m/%Y %H:%i") as dtvenda','c.EMPRESA','c.ID as idcli'))
			        ->join(array('c'=>'clientes'),'t.id_cliente = c.ID')
			        ->where("t.status != '0' and t.id_user = ".$usuario->ID)
			        ->order('t.id desc','');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		//--Lista produtos pedidos---------------------------
		function listaOrcamentosprod($ped){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			$params = array (
			    'host'     => '127.0.0.1',
			    'username' => 'ztlrolamentos',
			    'password' => 'BdMySql2008',
			    'dbname'   => 'ztlrolamentos'
			);
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_pedidos_prod','*'),
			        array('t.qt', 't.promo', 't.preco_alt', 't.preco_unit', 't.preco_tabela', 'p.ID', 'p.CODIGO', 'p.PRECO_UNITARIO', 
			        'p.DESCRICAO', 'p.APLICACAO', 'p.valor_promo', 'p.valor_desc','e.qt_atual'))
			        ->join(array('p'=>'produtos'),'t.id_prod = p.ID')
			        ->join(array('e'=>'tb_estoqueztl'),'t.id_prod = e.id_prod and e.id = (SELECT max(id) from tb_estoqueztl e where t.id_prod = e.id_prod)')
			        ->where("t.id_ped = ".$ped)
			        ->order('p.codigo_mask');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		//---Busca orcamentos--------------------
		 function buscaOrcamentos($var){
			
			if(!empty($var['ped'])):
				$where = "and md5(t.id) = '".$var['ped']."'";
			else:
				$where = "and t.id > 0";
			endif;
			
			$pdoParams = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8;');
			$params = array ('host' => '127.0.0.1', 'username' => 'ztlrolamentos', 'password' => 'BdMySql2008', 'dbname' => 'ztlrolamentos', 'driver_options' => $pdoParams);
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_pedidos_tmp','*'),
			        array('t.id as idped','DATE_FORMAT(t.dt_atualizacao,"%d/%m/%Y %H:%i") as dtvenda','c.EMPRESA','c.ID as idcli'))
			        ->join(array('c'=>'clientes'),'t.id_cliente = c.ID')
			        ->join(array('cd'=>'clientes_desc'),'cd.id_cliente = c.ID')
			        ->join(array('ce'=>'clientes_endereco'),'ce.ID_CLIENTE = c.ID')
			        ->join(array('e'=>'tb_estados'),'e.id = ce.ESTADO')
			        ->where("ce.tipo = 1 ".$where);
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		
	//--Lista produtos pedidos---------------------------
		 function buscaProdutosvendcli($idcli){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			$params = array (
			    'host'     => '127.0.0.1',
			    'username' => 'ztlrolamentos',
			    'password' => 'BdMySql2008',
			    'dbname'   => 'ztlrolamentos'
			);
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_pedidos','*'),
			        array('sum(pd.qt) as qtt', 'pd.preco_unit', 'p.ID as idproduto', 'p.CODIGO','t.id','t.data_vend as dtv'))
			        ->join(array('pd'=>'tb_pedidos_prod'),'t.id = pd.id_ped')
			        ->join(array('p'=>'produtos'),'pd.id_prod = p.ID')
			        ->where("t.id_parceiro = ".$idcli)
			        ->order('t.data_vend asc')
			        ->group('pd.id_prod');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		 function buscaProdutosvendcliantigo($idcli){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			$params = array (
			    'host'     => '127.0.0.1',
			    'username' => 'ztlrolamentos',
			    'password' => 'BdMySql2008',
			    'dbname'   => 'ztlrolamentos'
			);
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'pedidos','*'),
			        array('sum(pd.QUANTIDADE) as qtt', 'p.ID as idproduto', 'p.CODIGO','t.ID_INT'))
			        ->join(array('pd'=>'produtos_pedidos'),'t.ID_INT = pd.ID_PEDIDO')
			        ->join(array('p'=>'produtos'),'pd.ID_PRODUTO = p.ID')
			        ->where("t.STATUS = 'FATURADO' and t.ID_CLIENTE = ".$idcli)
			        ->group('pd.ID_PRODUTO');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		//--Lista produtos por grupo pedidos---------------------------
		 function buscaGruposvendcli($idcli){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			$params = array (
			    'host'     => '127.0.0.1',
			    'username' => 'ztlrolamentos',
			    'password' => 'BdMySql2008',
			    'dbname'   => 'ztlrolamentos'
			);
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_pedidos','*'),
			        array('count(p.ID) as count', 'pd.preco_unit', 'p.ID as idproduto', 'p.CODIGO','t.id','t.data_vend as dtv','g.NOME','p.ID_GRUPO'))
			        ->join(array('pd'=>'tb_pedidos_prod'),'t.id = pd.id_ped')
			        ->join(array('p'=>'produtos'),'pd.id_prod = p.ID')
			        ->join(array('g'=>'grupos'),'p.ID_GRUPO = g.ID')
			        ->where("t.id_parceiro = ".$idcli)
			        ->order('t.data_vend asc')
			        ->group('p.ID_GRUPO');
			  		//->group('pd.id_prod');
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		 function buscaGruposvendcliantigo($idcli){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			$params = array (
			    'host'     => '127.0.0.1',
			    'username' => 'ztlrolamentos',
			    'password' => 'BdMySql2008',
			    'dbname'   => 'ztlrolamentos'
			);
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'pedidos','*'),
			        array('count(p.ID) as count', 'p.ID as idproduto', 'p.CODIGO','t.ID_INT','g.NOME','p.ID_GRUPO'))
			        ->join(array('pd'=>'produtos_pedidos'),'t.ID_INT = pd.ID_PEDIDO')
			        ->join(array('p'=>'produtos'),'pd.ID_PRODUTO = p.ID')
			        ->join(array('g'=>'grupos'),'p.ID_GRUPO = g.ID')
			        ->where("t.STATUS = 'FATURADO' and t.ID_CLIENTE = ".$idcli)
			        ->group('p.ID_GRUPO');
			        //->group('pd.ID_PRODUTO');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
		 function listpedidosant(){
			$bo = new PedidosvendaModel();
			$boa = new PedidosvendaantModel();
			return $boa->fetchAll("STATUS = 'FATURADO' and ID <= 2945");				
		}
		
		 function listpedidosantprod($ped){
			$bo = new PedidosvendaModel();
			$boa = new PedidosvendaprodantModel();
			return $boa->fetchAll("ID_PEDIDO = ".$ped);				
		}
		
		 
		
		//--Lista produtos por grupo de venda---------------------------
		 function buscaGruposvendprodscli($idcli){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			$params = array (
			    'host'     => '127.0.0.1',
			    'username' => 'ztlrolamentos',
			    'password' => 'BdMySql2008',
			    'dbname'   => 'ztlrolamentos'
			);
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_pedidos','*'),
			        array('p.CODIGO','p.ID_GRUPO','sum(pd.qt) as quant'))
			        ->join(array('pd'=>'tb_pedidos_prod'),'t.id = pd.id_ped')
			        ->join(array('p'=>'produtos'),'pd.id_prod = p.ID')
			        ->where("t.sit = 0 and t.id_parceiro = ".$idcli)
			        ->group('p.ID');
			  		//->group('pd.id_prod');
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
	
		 function listaProdutospedidos($id){
			$params = array ('host'     => '127.0.0.1', 'username' => 'ztlrolamentos', 'password' => 'BdMySql2008', 'dbname'   => 'ztlrolamentos');
			$db1 = Zend_Db::factory('PDO_MYSQL', $params);
			$db1->setFetchMode(Zend_Db::FETCH_OBJ);
			$select1 = $db1->select();
			
			$select1->from(array('r'=>'tb_relatoriodevendas','*'), array('DATE_FORMAT(max(data),"%Y-%m") as dt'))
			        ->where('id_prod = '.$id);
			
			$stmt1 = $db1->query($select1);
			foreach ($stmt1->fetchAll() as $listdata);	
			        
			if(!empty($listdata->dt)):
				$data = ' and t.data_vend > "'.$listdata->dt.'-31"';
			endif;
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			
			$select->from(array('t'=>'tb_pedidos','*'),
			        array('sum(pd.qt) as quant','EXTRACT(YEAR from t.data_vend) as ano','EXTRACT(MONTH from t.data_vend) as mes'))
			        ->join(array('pd'=>'tb_pedidos_prod'),'t.id = pd.id_ped')
			        ->where("t.status = 'ped' and t.sit = 0 and  pd.id_prod = ".$id.$data)
			        ->group("ano")
			        ->group("mes");
			        
			$stmt = $db->query($select);
			return $stmt->fetchAll();	
		}
		
		function buscaPendencias($ano,$mes,$prod){
			$params = array (
			    'host'     => '127.0.0.1',
			    'username' => 'ztlrolamentos',
			    'password' => 'BdMySql2008',
			    'dbname'   => 'ztlrolamentos'
			);
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			
			$select->from(array('p'=>'tb_pedidos_pend','*'),
		        array('sum(p.qt) as qut',))
		        ->where("EXTRACT(YEAR from dt_pend) = '".$ano."' and EXTRACT(MONTH from dt_pend) = '".$mes."' and id_prod = ".$prod);
				        
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		function listaRelatoriovendas(){
			$params = array (
			    'host'     => '127.0.0.1',
			    'username' => 'ztlrolamentos',
			    'password' => 'BdMySql2008',
			    'dbname'   => 'ztlrolamentos'
			);
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			
			$select->from(array('p'=>'produtos','*'),
			        array('p.ID as idproduto'))
			        ->join(array('s'=>'tb_gruposprodsub'), 's.id = p.id_gruposprodsub')
			        ->join(array('g'=>'tb_gruposprod'), 'g.id = s.id_gruposprod');
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();	
		}
				
		function atualizaRelatorio(){
			$bo		= new PedidosvendaModel();
			$bor	= new RelatoriosvendasModel();			
			
			foreach (ProdutosBO::listaallProdutos() as $lista):				
				foreach (VendaBO::listaProdutospedidos($lista->ID) as $listprod):
					$array['qtvend']	= $listprod->quant;
					$array['id_prod']	= $lista->ID;
					$array['data']		= $listprod->ano."-".$listprod->mes."-02";
					
					foreach (VendaBO::buscaPendencias($listprod->ano, $listprod->mes, $lista->ID) as $lispend);
					if(!empty($lispend->qut)):
						$array['qtped'] = $listprod->quant+$lispend->qut;
					else:
						$array['qtped'] = 0;
					endif;
					
					$bor->insert($array);
					
				endforeach;
				
			endforeach;
		}
		
		//----- relatorio de vendas ----------------------------
		function buscaVendasprodutos($var){
			
			if(!empty($var['dataini']) || !empty($var['datafim'])):
				if(!empty($var['dataini']) and !empty($var['datafim'])):
					$dataini = substr($var['dataini'],6,4).'-'.substr($var['dataini'],3,2).'-'.substr($var['dataini'],0,2);
					$datafim = substr($var['datafim'],6,4).'-'.substr($var['datafim'],3,2).'-'.substr($var['datafim'],0,2);			
					$where = ' and p.data_vend BETWEEN "'.$dataini.'" and "'.$datafim.'  23:59:59"';
				elseif (!empty($var['dataini'])):
					$dataini = substr($var['dataini'],6,4).'-'.substr($var['dataini'],3,2).'-'.substr($var['dataini'],0,2);
					$where = ' and p.data_vend >= "'.$dataini.' 23:59:59"';
				elseif (!empty($var['datafim'])):
					$datafim = substr($var['datafim'],6,4).'-'.substr($var['datafim'],3,2).'-'.substr($var['datafim'],0,2);
					$where = ' and p.data_vend <= "'.$datafim.' 23:59:59"';
				endif;
			else:
				$dataini	 = date('Y-m-d');
				$datafim	 = date("Y-m-d",strtotime("+24 hours"));
				$where = ' and p.data_vend BETWEEN "'.$dataini.'" and "'.$datafim.'"';
			endif;
						
			$params = array ('host'     => '127.0.0.1', 'username' => 'ztlrolamentos', 'password' => 'BdMySql2008', 'dbname'   => 'ztlrolamentos');
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			
			$select->from(array('p'=>'tb_pedidos','*'), array('sum(qt*preco_unit) as precototal'))
			        ->join(array('pd'=>'tb_pedidos_prod'), 'pd.id_ped = p.id')
			        ->where('p.status = "ped" and p.sit = 0'.$where);
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();	
		}
		
		//----- relatorio de pendencias ----------------------------
	    function buscaPendenciasprodutos($var){
	    	if(!empty($var['dataini']) || !empty($var['datafim'])):
				if(!empty($var['dataini']) and !empty($var['datafim'])):
					$dataini = substr($var['dataini'],6,4).'-'.substr($var['dataini'],3,2).'-'.substr($var['dataini'],0,2);
					$datafim = substr($var['datafim'],6,4).'-'.substr($var['datafim'],3,2).'-'.substr($var['datafim'],0,2);			
					$where = ' and p.data_vend BETWEEN "'.$dataini.'" and "'.$datafim.'  23:59:59"';
				elseif (!empty($var['dataini'])):
					$dataini = substr($var['dataini'],6,4).'-'.substr($var['dataini'],3,2).'-'.substr($var['dataini'],0,2);
					$where = ' and p.data_vend >= "'.$dataini.' 23:59:59"';
				elseif (!empty($var['datafim'])):
					$datafim = substr($var['datafim'],6,4).'-'.substr($var['datafim'],3,2).'-'.substr($var['datafim'],0,2);
					$where = ' and p.data_vend <= "'.$datafim.' 23:59:59"';
				endif;
			else:
				$dataini	 = date('Y-m-d');
				$datafim	 = date("Y-m-d",strtotime("+24 hours"));
				$where = ' and p.data_vend BETWEEN "'.$dataini.'" and "'.$datafim.'"';
			endif;
			
			$pdoParams = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8;');
			$params = array ('host' => '127.0.0.1', 'username' => 'ztlrolamentos', 'password' => 'BdMySql2008', 'dbname' => 'ztlrolamentos', 'driver_options' => $pdoParams);
			
			$db = Zend_Db::factory('PDO_MYSQL', $params);
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			
			$select->from(array('p'=>'tb_pedidos','*'), array('sum(qt*valor) as precopend'))
			        ->join(array('pd'=>'tb_pedidos_pend'), 'pd.id_ped = p.id')
			        ->where('p.status = "ped" and p.sit = 0'.$where);
				        
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		
		function enviaMail(){
			$smtp = "smtp.ztlbrasil.com.br";
			$conta = "info@ztlbrasil.com.br";
			$senha = "010203";
			$de = "info@ztlbrasil.com.br";
			$assunto = "Informativo ZTL";
			
			$texto_ztl 	= "Mail crontab teste";
			$resp 		= "Eu mesmo";
			$email		= "cleiton@ztlbrasil.com.br";
			
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
				$mail->setBodyHtml($texto_ztl);
				$mail->setSubject($assunto);
				$mail->send($mailTransport);
			
				echo "Email enviado com SUCESSSO: ".$email."<br>";
			} catch (Exception $e){
				echo ($e->getMessage());
				echo "<br>";
			}
		
		}
		
		
		
		
	}
?>