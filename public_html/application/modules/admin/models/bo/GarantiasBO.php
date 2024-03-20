<?php
	class GarantiasBO{		
		 
		//------ Garantias -----------------------------------------------------------
		
		
		//--Grava Garantia-------
		 function gravaGarantia($params){
			$bo 		= New GarantiaModel();
			$boerr		= new ErrosModel();
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
        							 
			if(empty($params["cliente"])): 
				$id_cliente = $usuario->id_cliente;
				$array['status']			= "Aguardando a autorização para envio";
			else: 
				$id_cliente = $params["cliente"];
				$array['status']			= "Remetido a ZTL";
			endif;
			
			$array['data_atualizacao'] 	= date("Y-m-d H:i:s");
			$array['sit']				= true;
			$array['obs']				= $params["obs"];
			$array['id_user']			= $usuario->id;
			$array['id_clientes']		= $id_cliente;
			$array['nota_fiscal']		= $params["ntfiscal"];
			$array['data_nf']			= substr($params["dt_exp"],6,4).'-'.substr($params["dt_exp"],3,2).'-'.substr($params["dt_exp"],0,2);
			$array['cfop'	]			= $params["cfop"];
			$array['peso_nf']			= str_replace(",",".",$params["peso"]);
			$array['volumes']			= $params["volumes"];
			$array['tipoenvio']			= $params["tipoenvio"];
			
			if(!empty($params["dt_val"])) $array['data_valpac']		= substr($params["dt_val"],6,4).'-'.substr($params["dt_val"],3,2).'-'.substr($params["dt_val"],0,2);
			
			if($params["tipoenvio"]==1){
				$array['valorenvio']	= $params["pac"];
			}elseif($params["tipoenvio"]==1){ 
				$array['valorenvio']	= $params["transportadora"];
			}
			
			if(empty($params[idgarantia])):
				$array['data_cad']			= date("Y-m-d H:i:s"); //substr($params["dt_cad"],6,4).'-'.substr($params["dt_cad"],3,2).'-'.substr($params["dt_cad"],0,2); 
				$idcli = $bo->insert($array);
			else: 
				$bo->update($array,"id = ".$params[idgarantia]);
				$idcli = $params[idgarantia];
			endif;
			
			$pasta = Zend_Registry::get('pastaPadrao')."public/sistema/upload/clientes/".$id_cliente;
			
			if (!(is_dir($pasta))){
				if(!(mkdir($pasta, 0777))){
					$dataerro = array('descr' => "Alerta: pasta de upload nao existe, e nao pode ser criada", 'erro' => "Nova garantia");
					$boerr->insert($dataerro);
					return false;
					break;			
				}
			}
			
			if(!(is_writable($pasta))){
			    
				$dataerro = array('descricao' => "Alerta: pasta sem permissao de escrita", 'pagina' => "Nova garantia");
				$boerr->insert($dataerro);
				return false;
				break;
				
			}

			$pasta = Zend_Registry::get('pastaPadrao')."public/sistema/upload/clientes/".$id_cliente."/garantias";
			if (!(is_dir($pasta))){
				if(!(mkdir($pasta, 0777))){
					$dataerro = array('descr' => "Alerta: pasta de upload nao existe, e nao pode ser criada", 'erro' => "Nova garantia");
					$boerr->insert($dataerro);
					return false;
					break;
						
				}
			}
			
			if(!(is_writable($pasta))){
				$dataerro = array('descricao' => "Alerta: pasta sem permissao de escrita", 'pagina' => "Nova garantia");
				$boerr->insert($dataerro);
				return false;
				break;
			}
			
			$transferencia = new Zend_File_Transfer_Adapter_Http();
			$name = $transferencia->getFileInfo();
			
		  	if($name):
		  		foreach ($name as $val){
			        $fname=$val['name'];
		    	}
			    $exts = split("[/\\.]", $fname) ;
			    $n = count($exts)-1;
			    $exts = $exts[$n];
		    
			    echo $name;
			    
			  	$transferencia->addFilter('Rename', array('target' => $pasta.'/'.$idcli.'.'.$exts, 'overwrite' => true));
				$transferencia->receive();
				
				if(!empty($exts)):
				  	$array_up[anexo]	= $exts;
				  	$bo->update($array_up,"id = ".$idcli);
				endif;
		  	endif;
			
		  	
		  	
		  	$bohis 		= new GarantiahistoricoModel();
		  	$arrayu['data']				= date("Y-m-d H:i:s");
		  	$arrayu['id_garantiaztl']	= $idcli;
		  	$arrayu['id_user']			= $usuario->id;
		  	
		  	if(empty($params["cliente"])):
			  	$arrayu['status']			= "Aguardando autorização para envio";			  	
		  	else:
			  	$arrayu['status']			= "Remetido a ZTL";				
			endif;
				
			$bohis->insert($arrayu);
			
			LogBO::cadastraLog("Garantia",2,$usuario->id,$idcli,"GARANTIA G".substr("000000".$idcli,-6,6));
			
			foreach (ClientesBO::listaEmailsUp($id_cliente,4) as $listMail);
			$email 	= $listMail->EMAIL;
			$resp	= $listMail->NOME_CONTATO;
			
			if(empty($params["cliente"])):
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
			
						<body><table width="600px" height="200" style="border: 1px solid #000; margin-top: 10px; " cellpadding="4px" align="center" >						<tr>
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
			
			else:
			
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
								
							Segue abaixo o c&oacute;digo de autoriza&ccedil;&atilde;o de Postagem por PAC 
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
								
							Informamos que ser&aacute; realizado a coleta dos produtos a serem enviados para a an&aacute;lise de garantia,
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
				endif;
				
				if(!empty($email)):
					try {
						DiversosBO::enviaMail("Garantias ZTL", $texto_ztl, 'Departamento de garantias ZTL', 'garantias@ztlbrasil.com.br');						
					} catch (Exception $e){
						echo ($e->getMessage());
					}
					
				endif;	 
				
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
			
			$array['status']		= "Remetido a ZTL";
						
			$bo->update($array,"id = ".$params[idgarantia]);
			
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			$bohis 		= new GarantiahistoricoModel();
			$arrayu['data']				= date("Y-m-d H:i:s");
			$arrayu['status']			= "Remetido a ZTL";
			$arrayu['id_garantiaztl']	= $params[idgarantia];
			$arrayu['id_user']			= $usuario->id;
						
			$bohis->insert($arrayu);
			
			foreach (GarantiasBO::listarGarantiascliente($params[idgarantia]) as $listcli);
			
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
					DiversosBO::enviaMail("Garantias ZTL", $texto_ztl, 'Departamento de garantias ZTL', 'garantias@ztlbrasil.com.br');					
				} catch (Exception $e){
					echo ($e->getMessage());
				}
			endif;
			
		}
		
		//--Lista garantias---------------------------
		 function listaGarantias($val){
		 	$usuario 	= Zend_Auth::getInstance()->getIdentity();
			$sessaoFin = new Zend_Session_Namespace('Default');
			
			$bo		= new RegioesModel();
			$bor	= new RegioesclientesModel();
			$bot	= new RegioestelevendasModel();
			
			$where = "";
			if(isset($val['tipo'])){
				if(($val['tipo']==1) and (!empty($val['buscaid']))):
					$where = " and t.id = ".substr($val['buscaid'],1);								
				elseif(($val['tipo']==3) and (!empty($val['buscacli']))):
					$where = " and t.id_clientes = ".$val['buscacli'];					
				elseif($val['tipo']==4):
					$where = " and t.status like '%".$val['tipostatus']."%'";
				elseif($val['tipo']==5):
					$where = " and md5(t.id) = '".$val['garantia']."'";
				endif;
			}
			
			
			if(isset($val['dataini'])){
				$dataini = substr($val['dataini'],6,4).'-'.substr($val['dataini'],3,2).'-'.substr($val['dataini'],0,2);
			}
			
			if(isset($val['datafin'])){
			    $datafin = substr($val['datafin'],6,4).'-'.substr($val['datafin'],3,2).'-'.substr($val['datafin'],0,2);
			}
				
			
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
			
			//--- Controle de perfil ------------------------------------------
		    foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
		    if($list->nivel==1):
		    	$sql 	= "";
		    	foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
			    if($list->nivel == 1):
			    	if($usuario->id_perfil == 31):
			    		$where .= " and c.id_regioestelevendas in (".RegioesBO::buscaRegioesusuariolog().")";
				    else:
				    	$where .= " and c.ID_REGIOES in (".RegioesBO::buscaRegioesusuariolog().")";
				    endif;
		    	endif;
		    elseif($list->nivel==0):
		    	$where .= " and c.ID = ".$usuario->id_cliente;
		    endif;
						
			//--- limita a pesquisa ------------------------------------
			if(!empty($val['limite'])):
				$limite =  $val['limite'];
			else:
				$limite =  10000000000;
			endif;
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_garantiaztl','*'),
			        array('t.id','DATE_FORMAT(t.data_chegada,"%d/%m/%Y") as chegada','DATE_FORMAT(t.data_atualizacao,"%d/%m/%Y") as data','t.obs','t.status','c.EMPRESA','t.id_clientes','t.anexo'))
			        ->join(array('c'=>'clientes'),'t.id_clientes = c.ID')
			        ->join(array('ce'=>'clientes_endereco'),'t.id_clientes = ce.ID_CLIENTE')
			        ->where("ce.TIPO = 1 and t.sit = true  ".$where)
			        ->order('t.id desc','')
					->limit($limite);
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();		
		}
		
	   
			
		
		/*--------- Relatorio de garantias ----------------------------------------------------------*/
		
		function relatorioGarantias($pesq,$abragencia="",$tipo=""){
				
		    try{
			    if(!empty($pesq['prod'])):
		    		$where = " and p.CODIGO = '".$pesq['prod']."'";
		    	elseif($pesq['buscagruposub']!=0):
		    		$where = " and p.id_gruposprodsub = ".$pesq['buscagruposub'];
		    	elseif($pesq['buscagrupo']!=0):
			    	foreach (GruposprodBO::listaGruposprodutossub($pesq['buscagrupo']) as $listsubg):
			    		$idsg .= $listsubg->id.",";
			    	endforeach;
			    	$where = " and p.id_gruposprodsub in (".substr($idsg, 0,-1).") ";
		    	endif;
		    		
		    	if(!empty($pesq['clientes']) and ($abragencia != "ztl")):
					$where .= " and t.id_clientes = ".$pesq['clientes'];
				endif;
				
				if($tipo == 1):
					$where .= " and gp.substituir = true";
				elseif($tipo == 2):
					$where .= " and gp.substituir = false";
				endif;
				
				$where .= " and t.data_cad >= DATE_SUB(CURRENT_DATE(), INTERVAL ".$pesq['periodo']." month)";
				
		    	$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		    	$db->setFetchMode(Zend_Db::FETCH_OBJ);
		    	$select = $db->select();
		    	
		    	if($tipo == 3):
			    	$select->from(array('t'=>'tb_garantiaztl','*'), array('count(gp.id_prod) as qttotal'))
				    	->join(array('gp'=>'tb_garantiaztl_proddet'),'t.id = gp.id_garantiaztl')
				    	->join(array('ga'=>'tb_garanaliseprod'),'gp.id = ga.id_proddet and ga.id_analisegar = 24')
				    	->join(array('p'=>'produtos'),'p.ID = gp.id_prod')
				    	->where("t.sit = true  ".$where);
		    	else:
			    	$select->from(array('t'=>'tb_garantiaztl','*'), array('count(gp.id_prod) as qttotal'))
				    	->join(array('gp'=>'tb_garantiaztl_proddet'),'t.id = gp.id_garantiaztl')
				    	->join(array('p'=>'produtos'),'p.ID = gp.id_prod')
				    	->where("t.sit = true  ".$where);
				endif;
		    	
		    	$stmt = $db->query($select);
		    	
		    	return $stmt->fetchAll();
		    	
		    }catch(Zend_Exception $e){
		        return "erro";
		    }
		}
		
		
		function relatorioGarantiasgroup($pesq,$abragencia="",$tipo=""){
		
			try{
				if(!empty($pesq['prod'])):
					$where = " and p.CODIGO = '".$pesq['prod']."'";
				elseif($pesq['buscagruposub']!=0):
					$where = " and p.id_gruposprodsub = ".$pesq['buscagruposub'];
				elseif($pesq['buscagrupo']!=0):
					foreach (GruposprodBO::listaGruposprodutossub($pesq['buscagrupo']) as $listsubg):
						$idsg .= $listsubg->id.",";
					endforeach;
					$where = " and p.id_gruposprodsub in (".substr($idsg, 0,-1).") ";
				endif;
		
				if(!empty($pesq['clientes']) and ($abragencia != "ztl")):
					$where .= " and t.id_clientes = ".$pesq['clientes'];
				endif;
		
				if($tipo == 1):
					$where .= " and gp.substituir = true";
				elseif($tipo == 2):
					$where .= " and gp.substituir = false";
				endif;
		
				$where .= " and t.data_cad >= DATE_SUB(CURRENT_DATE(), INTERVAL ".$pesq['periodo']." month)";
		
				$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
				$db->setFetchMode(Zend_Db::FETCH_OBJ);
				$select = $db->select();
				$select->from(array('t'=>'tb_garantiaztl','*'), array('count(gp.id_prod) as qttotal','MONTH(t.data_cad) as mes','YEAR(t.data_cad) as ano'))
					->join(array('gp'=>'tb_garantiaztl_proddet'),'t.id = gp.id_garantiaztl')
					->join(array('p'=>'produtos'),'p.ID = gp.id_prod')
					->where("t.sit = true  ".$where)
					->group("MONTH(t.data_cad)")
					->group("YEAR(t.data_cad)");
		
				$stmt = $db->query($select);
				
				return $stmt->fetchAll();
				 
			}catch(Zend_Exception $e){
				return "erro";
			}
		}
		
		function relatoriolistaComprasgroup($pesq){
		
			try{

			    if(!empty($pesq['prod'])):
			    	$where = " and prod.CODIGO = '".$pesq['prod']."'";
			    elseif($pesq['buscagruposub']!=0):
			    	$where = " and prod.id_gruposprodsub = ".$pesq['buscagruposub'];
			    elseif($pesq['buscagrupo']!=0):
				    foreach (GruposprodBO::listaGruposprodutossub($pesq['buscagrupo']) as $listsubg):
				    	$idsg .= $listsubg->id.",";
				    endforeach;
				    $where = " and prod.id_gruposprodsub in (".substr($idsg, 0,-1).") ";
			    endif;
			    
				$where .= " and p.data_vend >= DATE_SUB(CURRENT_DATE(), INTERVAL ".$pesq['periodo']." month)";
		
				$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
				$db->setFetchMode(Zend_Db::FETCH_OBJ);
				$select = $db->select();
				$select->from(array('p'=>'tb_pedidos','*'), array('sum(pr.qt) as qttotal','MONTH(p.data_vend) as mes','YEAR(p.data_vend) as ano'))
					->join(array('pr'=>'tb_pedidos_prod'),'p.id = pr.id_ped')
					->join(array('prod'=>'produtos'),'pr.id_prod = prod.ID')
					->where("p.sit = 0 and p.status = 'ped' ".$where)
					->group("MONTH(p.data_vend)")
					->group("YEAR(p.data_vend)");
		
				$stmt = $db->query($select);
				return $stmt->fetchAll();
					
			}catch(Zend_Exception $e){
			    echo $e->getMessage();
				return "erro";
			}
		}
		
		
		function relatorioVendas($pesq, $abragencia=""){
				
			if(!empty($pesq['prod'])):
				$where = " and p.CODIGO = '".$pesq['prod']."'";
			elseif($pesq['buscagruposub']!=0):
				$where = " and p.id_gruposprodsub = ".$pesq['buscagruposub'];
			elseif($pesq['buscagrupo']!=0):
				foreach (GruposprodBO::listaGruposprodutossub($pesq['buscagrupo']) as $listsubg):
					$idsg .= $listsubg->id.",";
				endforeach;
				$where = " and p.id_gruposprodsub in (".substr($idsg, 0,-1).") ";
			endif;
				
			if(!empty($pesq['clientes']) and ($abragencia != "ztl")):
				$where .= " and pd.id_parceiro = ".$pesq['clientes'];
			endif;
			
			$where .= " and pd.data_vend >= DATE_SUB(CURRENT_DATE(), INTERVAL ".$pesq['periodo']." month)";
				
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
			$select = $db->select();
				
			$select->from(array('pd'=>'tb_pedidos','*'), array('sum(pp.qt) as qttotal'))
				->join(array('pp'=>'tb_pedidos_prod'),'pp.id_ped = pd.id')
				->join(array('p'=>'produtos'),'pp.id_prod = p.ID')
				->where("pd.sit = 0 and pd.status = 'ped' ".$where);
			 
			$stmt = $db->query($select);
				
			return $stmt->fetchAll();
		
		}
		
		function relatoriolistaGarantias($pesq,$tipo=""){
		
			try{
				if(!empty($pesq['prod'])):
					$where = " and p.CODIGO = '".$pesq['prod']."'";
				elseif($pesq['buscagruposub']!=0):
					$where = " and p.id_gruposprodsub = ".$pesq['buscagruposub'];
				elseif($pesq['buscagrupo']!=0):
					foreach (GruposprodBO::listaGruposprodutossub($pesq['buscagrupo']) as $listsubg):
					$idsg .= $listsubg->id.",";
					endforeach;
					$where = " and p.id_gruposprodsub in (".substr($idsg, 0,-1).") ";
				endif;
		
				if(!empty($pesq['clientes'])):
					$where .= " and t.id_clientes = ".$pesq['clientes'];
				endif;
				
				if($tipo == 1):
					$where .= " and gp.substituir = true";
				endif;
								
				$where .= " and t.data_cad >= DATE_SUB(CURRENT_DATE(), INTERVAL ".$pesq['periodo']." month)";
				 
				$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
				$db->setFetchMode(Zend_Db::FETCH_OBJ);
				$select = $db->select();
				$select->from(array('t'=>'tb_garantiaztl','*'), array('p.CODIGO','p.ID as idproduto','count(gp.id_prod) as qttotal'))
					->join(array('gp'=>'tb_garantiaztl_proddet'),'t.id = gp.id_garantiaztl')
					->join(array('p'=>'produtos'),'p.ID = gp.id_prod')
					->where("t.sit = true ".$where)
					->group('p.ID');
		
				$stmt = $db->query($select);
				
				return $stmt->fetchAll();
				
			}catch(Zend_Exception $e){
				return "erro";
			}
		}
		
		
		function relatoriolistaCompras($pesq){
		    
			try{			    
			    
			    if(!empty($pesq['prod'])):
			    	$where = " and prod.CODIGO = '".$pesq['prod']."'";
			    elseif($pesq['buscagruposub']!=0):
			   		$where = " and prod.id_gruposprodsub = ".$pesq['buscagruposub'];
			    elseif($pesq['buscagrupo']!=0):
				    foreach (GruposprodBO::listaGruposprodutossub($pesq['buscagrupo']) as $listsubg):
				    	$idsg .= $listsubg->id.",";
				    endforeach;
				    $where = " and prod.id_gruposprodsub in (".substr($idsg, 0,-1).") ";
			    endif;
			    
				if(!empty($pesq['clientes'])):
					$where .= " and p.id_parceiro = ".$pesq['clientes'];
				endif;
				
				$where .= " and p.data_vend >= DATE_SUB(CURRENT_DATE(), INTERVAL ".$pesq['periodo']." month)";
				
				
				$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
				$db->setFetchMode(Zend_Db::FETCH_OBJ);
				$select = $db->select();
				$select->from(array('p'=>'tb_pedidos','*'), array('sum(pr.qt) as qttotal','pr.id_prod as idproduto','prod.CODIGO'))
					->join(array('pr'=>'tb_pedidos_prod'),'p.id = pr.id_ped')
					->join(array('prod'=>'produtos'),'pr.id_prod = prod.ID')
					->where("p.sit = 0 and p.status = 'ped' ".$where)
					->group('pr.id_prod')
					->order("prod.codigo_mask");
		
				$stmt = $db->query($select);
				return $stmt->fetchAll();
					
			}catch(Zend_Exception $e){
				return "erro";
			}
		}
		
		
		
		/*--- Busca dados dos cliente e transportadora para garantias -----------------
		 * Usado em garantiaspagAction ------------------------
		* Usado em garantiasgernfeAction ----------------------
		* Usado em garantiascliAction -------------------------
		* garantiasnovocliAction ------------------------------
		* garantiasviewAction ---------------------------------
		* garantiasviewcliAction ------------------------------
		* garantiasaceitAction --------------------------------
		* garantiasaceitcliAction -----------------------------
		* */
		
		function listarGarantiascliente($params,$perfil = ""){
			$obj = new GarantiaModel();
			
			$usuario = Zend_Auth::getInstance()->getIdentity();
			
			if($perfil!=10):
				if(empty($params)):
					$where = " and id_clientes = ".$usuario->id_cliente;
				else:
					$where = "and t.id = ".$params;
				endif;
			endif;	
			
			//--- Controle de perfil ------------------------------------------
		    foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
		    if($list->nivel==1):
		    	$sql 	= "";
		    	foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
			    if($list->nivel == 1):
			    	if($usuario->id_perfil == 31):
			    		$where .= " and c.id_regioestelevendas in (".RegioesBO::buscaRegioesusuariolog().")";
				    else:
				    	$where .= " and c.ID_REGIOES in (".RegioesBO::buscaRegioesusuariolog().")";
				    endif;
		    	endif;
		    elseif($list->nivel==0):
		    	$where .= " and c.ID = ".$usuario->id_cliente;
		    endif;			
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_garantiaztl'),
			        array('t.id as idcli','DATE_FORMAT(t.data_chegada,"%d/%m/%Y") as chegada','DATE_FORMAT(t.data_atualizacao,"%d/%m/%Y") as data',
			        't.obs','t.status','c.EMPRESA','t.id_clientes','t.nota_fiscal','t.peso_nf','t.volumes','t.tipoenvio','t.valorenvio',
			        't.motivorec','t.data_nf','t.anexo','DATE_FORMAT(t.data_valpac,"%d/%m/%Y") as data_validadepac','t.obsanalise','t.obscancela','t.anexocancela'))
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
		
			foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
			if($list->nivel == 1):
				foreach ($bor->fetchAll("id_clientes = ".$usuario->id) as $regioes):
					$reg .= $regioes->id_regioes.",";
				endforeach;				
				$where = " and ID_REGIOES in (".substr($reg,0,-1).")";
			endif;
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
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
															
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
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
			$arrayu['status']			= "Exclusão";
			$arrayu['id_garantiaztl']	= $params;
			$arrayu['id_user']			= $usuario->id;
						
			$bohis->insert($arrayu);
			
		}
		//--Recusa garantia--------------------------------
		 function recusarGarantia($params){
			$aj 		= new GarantiaModel();
			$array['status'] 	= "Envio recusado";
			$array['motivorec'] = $params[textrecusa];
			$array['data_atualizacao'] = date("Y-m-d H:i:s");
			$aj->update($array,'id = '.$params[idgarantia]);
			
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			$bohis 		= new GarantiahistoricoModel();
			$arrayu['data']				= date("Y-m-d H:i:s");
			$arrayu['status']			= "Envio recusado";
			$arrayu['id_garantiaztl']	= $params["idgarantia"];
			$arrayu['id_user']			= $usuario->id;
						
			$bohis->insert($arrayu);
			
		}
		
		//--Receber produtos Garantia-------
		 function recprodutosGarantia($params){
			$bo			= new GarantiaModel();
			$bohis 		= new GarantiahistoricoModel();
			$boprod 	= new GarantiaprodModel();
			$bomo		= new GarantiamoModel();
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			if($params['enviaAn']==1){
				$array['data']				= date("Y-m-d H:i:s");
				$array['status']			= "Recebido - em análise";
				$array['id_garantiaztl']	= $params["idgarantia"];
				$array['id_user']			= $usuario->id;
				$bohis->insert($array); 
				
				$arrayg['status']			= "Recebido - em análise";
			}else{
				if($params['edicao']!=1):
					$array['data']				= date("Y-m-d H:i:s");
					$array['status']			= "Recebido";
					$array['id_garantiaztl']	= $params["idgarantia"];
					$array['id_user']			= $usuario->id;
								
					$bohis->insert($array);
				endif;
				$arrayg['status']			= "Recebido";				
			}
			
			$id_cliente = $params['idcliente'];
			
			$arrayg['data_atualizacao'] = date("Y-m-d H:i:s");//
			$arrayg['data_chegada'] 	= substr($params['dt_chegada'],6,4).'-'.substr($params['dt_chegada'],3,2).'-'.substr($params['dt_chegada'],0,2);
			$bo->update($arrayg,"id = ".$params["idgarantia"]);
			
			
			$pasta = Zend_Registry::get('pastaPadrao')."public/sistema/upload/clientes/".$id_cliente;
			
			if (!(is_dir($pasta))){
			    
				if(!(mkdir($pasta, 0777))){
				    $boerro	= new ErrosModel();
					$dataerro = array('descricao' => 'pasta de upload nao existe, e nao pode ser criada', 'pagina' => "GarantiasBO::recprodutosGarantia()");
					$boerro->insert($dataerro);
					return false;
					break;
				}
			}
				
			if(!(is_writable($pasta))){
			    $boerro	= new ErrosModel();
			    $dataerro = array('descricao' => 'pasta sem permissao de escrita', 'pagina' => "GarantiasBO::recprodutosGarantia()");
			    $boerro->insert($dataerro);
			    return false;
			    break;
			    
			}
			
			$pasta = Zend_Registry::get('pastaPadrao')."public/sistema/upload/clientes/".$id_cliente."/garantias/";
			if (!(is_dir($pasta))){
				if(!(mkdir($pasta, 0777))){
					$boerro	= new ErrosModel();
					$dataerro = array('descricao' => 'pasta de upload nao existe, e nao pode ser criada', 'pagina' => "GarantiasBO::recprodutosGarantia()");
					$boerro->insert($dataerro);
					return false;
					break;
				}
			}
				
			if(!(is_writable($pasta))){
				$boerro	= new ErrosModel();
			    $dataerro = array('descricao' => 'pasta sem permissao de escrita', 'pagina' => "GarantiasBO::recprodutosGarantia()");
			    $boerro->insert($dataerro);
			    return false;
			    break;
			}
				
			/* $transferencia = new Zend_File_Transfer_Adapter_Http();
			$name = $transferencia->getFileInfo();
			
			
			if($name['arquivo']){
			
			    $fname = $name['arquivo']['name'];
				
				$exts = split("[/\\.]", $fname) ;
				$n = count($exts)-1;
				$exts = $exts[$n];
			 
				$transferencia->addFilter('Rename', array('target' => $pasta.'/'.$params["idgarantia"].'_maodeobra.'.$exts, 'overwrite' => true));
				$transferencia->receive($fname);
			
			} */
			
			$bomo->delete("id_garantiaztl = '".$params["idgarantia"]."'");
			
			for($i=1;$i<=$params['intarchive'];$i++){
				if($_FILES['arquivo'.$i]['name']){				    
					$ext 	= substr($_FILES['arquivo'.$i]['name'], strrpos($_FILES['arquivo'.$i]['name'], "."), strlen($_FILES['arquivo'.$i]['name']));
					$idmo 	= $bomo->insert(array('nome' => $ext, 'id_garantiaztl' => $params["idgarantia"]));
					
					$arquivo = isset($_FILES['arquivo'.$i]) ? $_FILES['arquivo'.$i] : FALSE;
					$nome = $params["idgarantia"]."_mo_".$idmo.$ext;
					
					if(is_uploaded_file($_FILES['arquivo'.$i]["tmp_name"])){
						if (move_uploaded_file($arquivo["tmp_name"], $pasta . $nome)) {
							echo "sucesso!";
						} else {
							echo ("Alerta: Nao foi possivel fazer o upload para $pasta");
							return $this;
						}
					}else{
						echo "erro ao carregar imagem";
					} 
				}
			}
						
			
			$boprod->delete("id_garantiaztl = ".$params["idgarantia"]);
			
			//--Grava produtos cadastrados ----------------
			$contador=0;			
			foreach(ProdutosBO::listaallProdutos() as $listprod):
				if(!empty($params[$listprod->ID])):
					$arrayprod['id_garantiaztl']	= $params["idgarantia"];
					$arrayprod['id_prod']			= $listprod->ID;
					$arrayprod['qt']				= $params[$listprod->ID];
					$arrayprod['preco_nf']			= $params["valor_".$listprod->ID];
					$boprod->insert($arrayprod);
				endif;
			endforeach;
			
			LogBO::cadastraLog("Garantias",2,$usuario->id,$params["idgarantia"],"G".substr("000000".$params["idgarantia"],-6,6));
			
		}
		
		function listarAnexosmobra($var){
			$bo			= new GarantiaModel();
			$bomo		= new GarantiamoModel();
				
			return $bomo->fetchAll('md5(id_garantiaztl) = "'.$var['garantia'].'"');
		}
		
		//--listar produtos garantias---------------------------
		function listaProdutosgarantia($var){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('g'=>'tb_garantiaztl_prod'), array('id','qt','preco_nf','p.CODIGO','p.DESCRICAO','id_prod','ipi','icms'))
			        ->join(array('p'=>'produtos'), 'g.id_prod = p.ID')
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
	        	        
	        $files = $upload_adapter->getFileInfo();
	        $fields = array_keys($files); 
	        $i = 0; 
	        
			foreach ($files as $info) {
								
				//echo "<br>".$fields[$i];
				
				if($upload_adapter->receive($info['name'])){
					$upload_adapter->getMessages();
					//$array[] = $info['name'];
					
					foreach (GarantiasBO::listaProdutosnord($params["idgarantia"]) as $listdet):
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
		/* function gambiarraAnalise(){
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
			$array['status']			= "Análise concluída";
			$array['id_garantiaztl']	= 1189;
			$array['id_user']			= 613;
			$bohis->insert($array);
				
			//--Atualizo garantia------------
			$arrayg['status']			= "Análise concluída";
			$arrayg['data_atualizacao'] = date("Y-m-d H:i:s");
							
			$bo->update($arrayg,"id = 1189");
				
			//--Grava produtos cadastrados ----------------
			
			for ($cont=1;$cont<=1001;$cont++){
				
				$arraydet['id_garantiaztl']	=	1189;
				$arraydet['n_ord']			=	$cont;
				$arraydet['id_prod']		=	1234;
				$arraydet['substituir']		=	($cont>135) ? 1 : 0;
		
				$iddet = $boproddet ->insert($arraydet);
		
				$arrayprod['id_analisegar'] 	= ($cont>135) ? 24 : 26;
				$arrayprod['id_proddet']		= $iddet;
				$bogprod->insert($arrayprod);
				
			}
				
		} */
		
		
		
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
			$array['status']			= "Análise concluída";
			$array['id_garantiaztl']	= $params["idgarantia"];
			$array['id_user']			= $usuario->id;
			$bohis->insert($array);
			
			//--Atualizo garantia------------
			$arrayg['status']			= "Análise concluída";	
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
			
	        $files = $upload_adapter->getFileInfo();
	        $fields = array_keys($files); 
	        $i = 0; 
	        
			foreach ($files as $info) {
								
				//echo "<br>".$fields[$i];
				
				if($upload_adapter->receive($info['name'])){
					$upload_adapter->getMessages();
					//$array[] = $info['name'];
					
					foreach (GarantiasBO::listaProdutosnord($params["idgarantia"]) as $listdet):
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
			
			foreach (GarantiasBO::listarGarantiascliente($params[idgarantia]) as $listcli);
			
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
					
					DiversosBO::enviaMail("Garantias ZTL", $texto_ztl, 'Departamento de garantias ZTL', 'garantias@ztlbrasil.com.br');
					
				} catch (Exception $e){
					echo ($e->getMessage());
				}
			endif;
			
		}
		
		function cancelaGarantia($params){
			$bo			= new GarantiaModel();
			
			$upload_adapter = new Zend_File_Transfer_Adapter_Http();
			$upload_adapter->setDestination(Zend_Registry::get('pastaPadrao')."public/garantias/cancelamentos");
			$files = $upload_adapter->getFileInfo();
									
			foreach ($files as $info) {
				$ext = pathinfo($info['name']);
				$filter = array('target' => Zend_Registry::get('pastaPadrao')."public/garantias/cancelamentos/".$params['idgarantia'].".".$ext['extension'], 'overwrite' => true);
				$upload_adapter->addFilter("Rename", $filter); 
				
				if($upload_adapter->receive($info['name'])){
					$upload_adapter->getMessages();					
				}

				$array['anexocancela']	 = $ext['extension'];				
			}
			
			$array['obscancela']	 = $params['obscancela'];
			$array['status']		 = 'Cancelado';
			$bo->update($array, "id = ".$params['idgarantia']);
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
	        foreach (GarantiasBO::listardiagAnalise($arrayb) as $list):
				if(!empty($params[$list->id])):
					$arrayd['id_dicas'] 			= $idcli;
					$arrayd['id_analise']			= $list->id;
			        $boa->insert($arrayd);
			    endif;       
		    endforeach;
	        
	        
	        $usuario 	= Zend_Auth::getInstance()->getIdentity();
			//LogBO::cadastraLog("Venda/Análise Garantia",2,$usuario->id,$idcli,"GARANTIA G".substr("000000".$idcli,-6,6));
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
						
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
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
			LogBO::cadastraLog("Estoque/Análise Garantia",2,$usuario->id,$idcli,"ANALISE ".$idcli);
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
			//LogBO::cadastraLog("Venda/Análise Garantia",2,$usuario->id,$idcli,"GARANTIA G".substr("000000".$idcli,-6,6));
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
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
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

		function listaProdgardetalhado($val){
				
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
			$select = $db->select();
				
			$select->from(array('t'=>'tb_garantiaztl_proddet','*'), array('t.id as idt','t.n_ord', 'p.CODIGO', 't.substituir','t.id_nfeprod','n.id_nfe as nfe','t.nt_fiscal','p.ID as idproduto','t.id_creditos'))
					->join(array('p'=>'produtos'),'p.ID = t.id_prod')
					->joinLeft(array('n'=>'tb_nfeprod'),'n.id = t.id_nfeprod')
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
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
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
		/* Usado em garantiaspagAction ---------*/
		 function listaProdutospagar($val){
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
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
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
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
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
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
		
		//--- Baixa no estoque garantias ------------------------------------------------
		function gravaentregaGarantia($val){
			$bog 		= new GarantiaModel();
			$bohis 		= new GarantiahistoricoModel();
			$bodet		= new GarantiaproddetModel();
			$boprod 	= new EstoqueModel();
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			foreach (GarantiasBO::listaProdutospornfe($val) as $produtos):
				foreach ($boprod->fetchAll('id_prod = '.$produtos->id_prod,"id desc",1) as $qt_atual);
					
				$arrayestq = array(
					'id_prod' 			=> $produtos->id_prod,
					'qt_atual' 			=> $qt_atual->qt_atual-($produtos->qtent),
					'qt_atualizacao' 	=> -($produtos->qtent),
					'id_atualizacao' 	=> $produtos->id_garantiaztl,
					'dt_atualizacao' 	=> date("Y-m-d H:i:s"),
					'tipo' 				=> "GARANTIA",
					'id_user' 			=> $usuario->id
				);
					
				$boprod->insert($arrayestq);			
			
				$verbaixa = 0;
				foreach ($bodet->fetchAll("id_garantiaztl = ".$produtos->id_garantiaztl) as $garantia):
					if(empty($garantia->id_nfeprod) and empty($garantia->id_creditos)):
						$verbaixa = 1;
					endif;
				endforeach;
				
				if($verbaixa==0):
					//--Atualizo historico da garantia-------------------------
					$arrayh['data']				= date("Y-m-d H:i:s");
					$arrayh['status']			= "Finalizado";
					$arrayh['id_garantiaztl']	= $produtos->id_garantiaztl;
					$arrayh['id_user']			= $usuario->id;
					$bohis->insert($arrayh);
					
					//--Atualiza garantia------------
					$arrayg['status']			= "Finalizado";
					$arrayg['data_atualizacao'] = date("Y-m-d H:i:s");
					$bog->update($arrayg,"id = ".$produtos->id_garantiaztl);
				else:
					foreach (GarantiasBO::listarGarantiascliente($produtos->id_garantiaztl) as $lista);
					if(strripos($lista->status, " PARCIAL")===false):
						//--Atualiza historico da garantia-------------------------
						$arrayh['data']				= date("Y-m-d H:i:s");
						$arrayh['status']			= "Finalização parcial";
						$arrayh['id_garantiaztl']	= $produtos->id_garantiaztl;
						$arrayh['id_user']			= $usuario->id;
						$bohis->insert($arrayh);
							
						//--Atualiza garantia------------
						$arrayg['status']			= "Finalização parcial";
						$arrayg['data_atualizacao'] = date("Y-m-d H:i:s");
						$bog->update($arrayg,"id = ".$produtos->id_garantiaztl);
					endif;
				endif;				
				
			endforeach;
			
			LogBO::cadastraLog("Vendas/Garantias",4,$usuario->id,$val,"Garantia NFe ".$val);
			
			echo "sucessobaixa";
						
		}

		
		//--listar produtos garantias pagos---------------------------
		 function listaprodutospagosGarantia($var){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('e'=>'tb_garantiaentrega','*'),
			        array('p.CODIGO','e.qt as qte','e.nt_fiscal as ntf','DATE_FORMAT(e.dt_ntfiscal,"%d/%m/%Y") as dtn'))
			        ->join(array('g'=>'tb_garantiaztl_prod'),'e.id_garantiaztl_prod = g.id')
			        ->join(array('p'=>'produtos'),'g.id_prod = p.ID')
			        ->where("g.id_garantiaztl = ".$var)
			        ->group("p.ID");
			  
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();				
		}
		
		//--listar detalhes analises garantias---------------------------
		 function listadetalhesAnalise($var){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_garantiaztl_proddet','*'), array('t.id as idt','a.id','a.detalhadesc','a.infotecnica'))
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
	        $upload_adapter->setDestination(Zend_Registry::get('pastaPadrao')."admin/imganalises");
	       			
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
			
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('pd'=>'tb_pedidos','*'),
			        array('pp.id_prod','sum(pp.qt) as qttotal','p.CODIGO'))
			        ->join(array('pp'=>'tb_pedidos_prod'),'pp.id_ped = pd.id')
			        ->join(array('p'=>'produtos'),'pp.id_prod = p.ID')
			        ->join(array('g'=>'tb_garantiaztl'),'g.id_clientes = pd.id_parceiro')
			        ->join(array('pg'=>'tb_garantiaztl_prod'),'pg.id_garantiaztl = g.id')
			        ->where("pg.id_prod = pp.id_prod and id_parceiro = ".$usuario->id." and TO_DAYS(NOW()) - TO_DAYS(pd.data_cad) <= 365 ".$where)
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
			
			
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('g'=>'tb_garantiaztl','*'),
			        array('p.id_prod','sum(p.qt) as qttotal'))
			        ->join(array('p'=>'tb_garantiaztl_prod'),'p.id_garantiaztl = g.id')
			        ->join(array('pp'=>'produtos'),'p.id_prod = pp.ID')
			        ->where("id_clientes = ".$usuario->id." and g.sit = true and TO_DAYS(NOW()) - TO_DAYS(data_cad) <= 365 ".$where)
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
		 /* function relatorioGarantiapag($pesq){
			
			
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
			
			
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('g'=>'tb_garantiaztl','*'),
			        array('p.id_prod','count(p.id_prod) as qttotal'))
			        ->join(array('p'=>'tb_garantiaztl_proddet'),'p.id_garantiaztl = g.id')
			        ->join(array('pp'=>'produtos'),'p.id_prod = pp.ID')
			        ->where("id_clientes = ".$usuario->id." and g.sit = true and p.substituir = true and TO_DAYS(NOW()) - TO_DAYS(data_cad) <= 365 ".$where)
			        ->group("p.id_prod");
			  
			$stmt = $db->query($select);
						
			return $stmt->fetchAll();		

			 				
				SELECT count(p.id_prod), p.id_prod, substituir
				FROM tb_garantiaztl g, tb_garantiaztl_proddet p 
				where id_clientes = 173 and p.id_garantiaztl = g.id and TO_DAYS(NOW()) - TO_DAYS(data_cad) <= 365 and p.substituir = true
				group by p.id_prod
				;

			;
						
		} */
		
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
			
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
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
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
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
			
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
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
			
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('g'=>'tb_garantiaztl','*'),
			        array('gp.id_analisegar','count(gp.id_analisegar) as qtanalise','count(gp.desc_outros) as qtanaliseout','a.descricao'))
			        ->join(array('p'=>'tb_garantiaztl_proddet'),'p.id_garantiaztl = g.id')
			        ->join(array('pp'=>'produtos'),'p.id_prod = pp.ID')
			        ->join(array('gp'=>'tb_garanaliseprod'),'gp.id_proddet = p.id')
			        ->joinLeft(array('a'=>'tb_garanalise'),'a.id = gp.id_analisegar')
			        ->where("g.id_clientes = ".$usuario->id." and g.sit = true and p.substituir = false and TO_DAYS(NOW()) - TO_DAYS(g.data_cad) <= 365  ".$where)
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
			
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('g'=>'tb_garantiaztl','*'),
			        array('gp.id_analisegar','count(gp.id) as qttotal','gp.desc_outros','a.descricao','a.id as idanalise'))
			        ->join(array('p'=>'tb_garantiaztl_proddet'),'p.id_garantiaztl = g.id')
			        ->join(array('pp'=>'produtos'),'p.id_prod = pp.ID')
			        ->join(array('gp'=>'tb_garanaliseprod'),'gp.id_proddet = p.id')
			        ->joinLeft(array('a'=>'tb_garanalise'),'a.id = gp.id_analisegar')
			        ->where("g.id_clientes = ".$usuario->id." and g.sit = true and p.substituir = false and TO_DAYS(NOW()) - TO_DAYS(g.data_cad) <= 365  ".$where)
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
					
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('pd'=>'tb_pedidos','*'), array('pp.id_prod','sum(pp.qt) as qttotal','p.CODIGO'))
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
			
			/*foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
			if($list->nivel == 1):
				foreach ($bor->fetchAll("id_clientes = ".$usuario->id) as $regioes):
					$reg .= $regioes->id_regioes.",";
				endforeach;				
				$where .= " and ID_REGIOES in (".substr($reg,0,-1).")";				
			endif;*/
			
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
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
			
			/*foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
			if($list->nivel == 1):
				foreach ($bor->fetchAll("id_clientes = ".$usuario->id) as $regioes):
					$reg .= $regioes->id_regioes.",";
				endforeach;				
				$where .= " and ID_REGIOES in (".substr($reg,0,-1).")";				
			endif;*/
			
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
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
				$where .= " and cl.ID_REGIOES = '".$usuario->id_REGIOES."'";
			endif;
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
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
				$where .= " and cl.ID_REGIOES = '".$usuario->id_REGIOES."'";
			endif;
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
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
			//$where = " and cl.ID_REGIOES = '".$usuario->id_REGIOES."'";
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
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
		
			foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
			if($list->nivel == 1):
				foreach ($bor->fetchAll("id_clientes = ".$usuario->id) as $regioes):
					$reg .= $regioes->id_regioes.",";
				endforeach;				
				$where = " and cl.ID_REGIOES in (".substr($reg,0,-1).")";
			endif;
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
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
		
			foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
			if($list->nivel == 1):
				foreach ($bor->fetchAll("id_clientes = ".$usuario->id) as $regioes):
					$reg .= $regioes->id_regioes.",";
				endforeach;				
				$where = " and cl.ID_REGIOES in (".substr($reg,0,-1).")";
			elseif($list->nivel == 0):
				$where = " and cl.ID = ".$usuario->id;
			endif;
						
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
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

		function buscaGarantiacliente($params){
			$bo		= new GarantiaModel();
			return $bo->fetchAll("sit = true and status like '%CONCLUIDA' and id_clientes = ".$params);
		}
		
		//-- NFe garantias --------------------------------------
		/*-- Usado em garantiaspaggnfeAction -------------------------*/
		function listaGarantiasapagar(){
				
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
			$select = $db->select();
			
			$select->from(array('t'=>'tb_garantiaztl_proddet','*'), array('*'))
				->join(array('g'=>'tb_garantiaztl'),'g.id = t.id_garantiaztl')
				->join(array('gp'=>'tb_garantiaztl_prod'),'gp.id_prod = t.id_prod and gp.id_garantiaztl = t.id_garantiaztl')
				->join(array('c'=>'clientes'),'c.ID = g.id_clientes')
				->where("g.sit = true and (t.id_nfeprod is NULL || t.id_nfeprod = '') and (t.nt_fiscal is NULL || t.nt_fiscal = '') and (t.id_creditos is NULL || t.id_creditos = '')")
				->group('c.ID')
				->order('c.EMPRESA'); 
			
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		} 
			
		/*-- Usado em garantiaspaggnfeAction -------------------------*/
		function listaProdutosapagar($val){
		    		
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
		
			$select = $db->select();
					
			$select->from(array('t'=>'tb_garantiaztl_proddet','*'), array('count(t.id_prod) qtent','t.id as idt','t.n_ord', 'p.CODIGO','t.id_prod','e.qt_atual','t.substituir'))
				->join(array('p'=>'produtos'),'p.ID = t.id_prod')
				->join(array('g'=>'tb_garantiaztl'),'g.id = t.id_garantiaztl')
				->join(array('gp'=>'tb_garantiaztl_prod'),'gp.id_prod = t.id_prod and gp.id_garantiaztl = t.id_garantiaztl') //-- garante exibir somente produtos com nota ---------------------
				->joinLeft(array('n'=>'tb_produtosncm'),'p.id_ncm = n.id')
				->joinLeft(array('e'=>'tb_estoqueztl'),'t.id_prod = e.id_prod and e.id = (SELECT max(id) from tb_estoqueztl e where t.id_prod = e.id_prod)')
				->where("(t.id_nfeprod is NULL || t.id_nfeprod = '') and (t.nt_fiscal is NULL || t.nt_fiscal = '') and (t.id_creditos is NULL || t.id_creditos = '') and g.id_clientes = ".$val['selparceiro'])
				->group('t.id_prod')
				->group('t.substituir')
				->order("p.codigo_mask");
			
		
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		//--- Lista produtos a pagar com numeros da garantia ---------------------------------
		function listaProdutosegarantiasapagar($val){
		
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
		
			$select = $db->select();
				
			$select->from(array('t'=>'tb_garantiaztl_proddet','*'), array('*','DATE_FORMAT(g.data_nf,"%d/%m/%Y") as datanf'))
				->join(array('g'=>'tb_garantiaztl'),'g.id = t.id_garantiaztl')
				->where("(t.id_creditos is NULL || t.id_creditos = '') and (t.id_nfeprod is NULL || t.id_nfeprod = '') and (t.nt_fiscal is NULL || t.nt_fiscal = '') and g.id_clientes = ".$val['selparceiro'])
				->group("t.id_prod")
				->group("g.id");				
		
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		//--- usado em GarantiasBO::gravadadosnfe ----------------------
		function listaProdutosapagargroup($val){
		
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
		
			$select = $db->select();
				
			$select->from(array('t'=>'tb_garantiaztl_proddet','*'), array('count(t.id_prod) qtent','t.id as idt','t.n_ord', 'p.CODIGO','t.id_prod','t.substituir'))
				->join(array('p'=>'produtos'),'p.ID = t.id_prod')
				->join(array('g'=>'tb_garantiaztl'),'g.id = t.id_garantiaztl')
				->join(array('gp'=>'tb_garantiaztl_prod'),'gp.id_prod = t.id_prod and gp.id_garantiaztl = t.id_garantiaztl')
				->joinLeft(array('n'=>'tb_produtosncm'),'p.id_ncm = n.id')
				->where("(t.id_nfeprod is NULL || t.id_nfeprod = '') and (t.nt_fiscal is NULL || t.nt_fiscal = '') and g.id_clientes = ".$val['selparceiro'])
				->group('p.ID')
				->order("p.codigo_mask");
				
		
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		
		function listaProdutosapagardetalhado($val){
		
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
		
			$select = $db->select();
				
			$select->from(array('t'=>'tb_garantiaztl_proddet','*'), array('t.id as iddet','t.substituir','t.id_garantiaztl'))
				->join(array('g'=>'tb_garantiaztl'),'g.id = t.id_garantiaztl')				
				->where("(t.id_nfeprod is NULL || t.id_nfeprod = '') and (t.nt_fiscal is NULL || t.nt_fiscal = '') and g.id_clientes = ".$val['selparceiro']." and t.id_prod = ".$val['idprod'])
				->order("t.id");
		
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		function listaProdutospornfe($val){
		
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
		
			$select = $db->select();
		
			$select->from(array('t'=>'tb_garantiaztl_proddet','*'), array('sum(t.substituir) as qtent','t.id_prod','t.id_garantiaztl'))
				->join(array('n'=>'tb_nfeprod'),'n.id = t.id_nfeprod')
				->where("t.substituir = true and n.id_nfe = ".$val)
				->group('t.id_garantiaztl')
				->group('t.id_prod');
		
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		
		function gravarDadosnfe($params){
		    		    
			$bonfe		= new NfeModel();
			$bonfeprod	= new NfeprodModel();
			$bog		= new GarantiaModel();
			$bogprod	= new GarantiaproddetModel();
			$bohis 		= new GarantiahistoricoModel();
			$bodet		= new GarantiaproddetModel();
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			//--- Busca empresa com enderecos ----------------
			
			$busca['idparceiro']		= $params['clientegar'];
			
			foreach (ClientesBO::buscaParceiros("",$busca) as $listaempresa);
			foreach (ClientesBO::listaEnderecocomp($params['clientegar'], 1) as $endempresa);
			foreach (ClientesBO::listaEmailsUp($params['clientegar'], 3) as $emailemproesa);
			foreach (ClientesBO::listaTelefonesUp($params['clientegar'], "telefone1") as $telempresa);
			
			//--- Busca telefones da empresa -----------------------------
					
			$total_pedido = $ipi = 0;
			$total_pedido_liquido = 0;
				
			if($endempresa->nuf!="DF"):
	           $cfop = 6916;
	           $nop = "Retorno de mercadoria ou bem recebido para conserto"; 
	        else:
		        $cfop = 5916;
		        $nop = "Retorno de mercadoria ou bem recebido para conserto";
	        endif;			
	        
	        $frete = str_replace(",",".",str_replace(".","",$params['frete']));
	        if(!empty($params['frete'])):
	        	$freteperc = $frete * 100 / $params['totalnota'];
	        endif;
	        
			//-- Dados da NFe ------------------------------------
			$datanfe = array(
				'serie'					=> 1,
				'data'					=> date('Y-m-d'),
				'data_saida'			=> date('Y-m-d H:i:s'),
				'cfop'					=> $cfop,
				'naturezaop'			=> $nop,
				'tipo'					=> 1,
				'id_cliente'			=> $listaempresa->ID,
				'cnpj'					=> $listaempresa->CPF_CNPJ,
				'inscricao'				=> $listaempresa->RG_INSC,
				'empresa'				=> DiversosBO::pogremoveAcentos($listaempresa->RAZAO_SOCIAL),
				'endereco'				=> DiversosBO::pogremoveAcentos($endempresa->LOGRADOURO),
				'numero'				=> $endempresa->numero,
				'bairro'				=> DiversosBO::pogremoveAcentos($endempresa->BAIRRO),
				'cep'					=> $endempresa->CEP,
				'codcidade'				=> $endempresa->codcidade,
				'cidade' 				=> DiversosBO::pogremoveAcentos($endempresa->ncidade),
				'uf'					=> $endempresa->nuf,
				'fone'					=> $telempresa->DDD.$telempresa->NUMERO,
				'tipofrete'				=> $params['tipofrete'],
				'transantt'				=> $params['antt'],
				'transplaca'			=> $params['placa'],
				'transufplaca'			=> $params['ufplaca'],
				'obs'					=> $params['obsnfe'],
				'frete'					=> $frete,
				'freteperc'				=> $freteperc,
				'seguro'				=> 0,
				'desconto'				=> 0,
				'descontoperc'			=> 0,
				'outrasdesp'			=> 0,
				'quantidade'			=> $params['qtpacote'],
				'especie'				=> $params['especie'],
				'pesobruto'				=> str_replace(",",".",str_replace(".","", $params['pesobruto'])),
				'marca'					=> 'ZTL'
			);		
				
			try {
				$idnfe = $bonfe->insert($datanfe);							    
			}catch (Zend_Exception $e){
			    $boerro	= new ErrosModel();
				$dataerro = array('descr' => $e->getMessage(), 'erro' => $params[ped]);
				$boerro->insert($dataerro);					
			}
				
			//--- Busca transportadora com enderecos ----------------
			$busca['idparceiro']		= $listaempresa->id_transportadoras;
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
				
				
				
			else:
				echo "- Cadastre uma transportadora para o cliente;";
			endif;
				
			try {
				$bonfe->update($datanfe,"id = ".$idnfe);
			}catch (Zend_Exception $e){
				$boerro	= new ErrosModel();
				$dataerro = array('descr' => $e->getMessage(), 'erro' => $params[ped]);
				$boerro->insert($dataerro);
			}
			
			
			
			$val['selparceiro'] = $params['clientegar'];
			$validaprod = 0;
						
			//---- Produtos a pagar ----------------------------------------------
			// ---- string com produtos --------------------------
			
			//--- Junto todos os produtos sem repeti-los ------------------------------
			$todosprods 	= "|".$params['prodsel'];
			
			if(empty($todosprods)):
				$todosprods = $params['prodselneg'];
			elseif(!empty($params['prodselneg'])):
				$produtosver 	= explode("|", $params['prodselneg']);
				for($p = 0; $p < count($produtosver); $p++):
					if(!empty($produtosver[$p])):
						$prodv = "";
						$prodv =  explode(":", $produtosver[$p]);
						//echo $prodv[0]."\n";
						if(strpos($todosprods, "|".$prodv[0].":")===false):
							$todosprods .= $produtosver[$p]."|";
						endif;
					endif;
				endfor;				
			endif;

			
			$produtos = explode("|", substr($todosprods,1));
			$produtospag = explode("|", $params['prodsel']);
			$produtosver = explode("|", $params['prodselneg']);
			
			for($i = 0; $i < count($produtos); $i++):
				$proddet	= explode(":", $produtos[$i]);
			
				foreach (GarantiasBO::listaProdutosapagargroup($val) as $listProd):
				
					if(($proddet[0] == $listProd->ID)):
						$qtprod = $qtproddev = $qtprodpagar = 0;
												
						//-- busco qt total para o produto, somando a pagar com a devolver ---------------------------
						for($l = 0; $l < count($produtospag); $l++):
							$proddetpg	= explode(":", $produtospag[$l]);
							if($proddetpg[0] == $proddet[0]):
								$qtprodpagar 	= $proddetpg[1];
							endif;
						endfor;
						
						for($j = 0; $j < count($produtosver); $j++):
							$proddetver	= explode(":", $produtosver[$j]);
							if($proddetver[0] == $proddet[0]):
								$qtproddev 	= $proddetver[1];
							endif;
						endfor;
				
						$qtprod = $qtprodpagar + $qtproddev;
						
						$precototal = $frete = $desconto = $baseicms = 0;
						$precototal = $qtprod*$listProd->preco_nf;
							
						/*-- Calcula o frete ----------------------------------------- */
						$frete 			= ($precototal*$freteperc)/100;
						$total_frete	+= $frete;
						
						$tpipi = "NT";
						$tppis = "NT";
						$tpcofins = "NT";
						
						$dataprod = array(
							'id_nfe'		=> $idnfe,
							'id_prod'		=> $listProd->ID,
							'codigo'		=> $listProd->CODIGO,
							'descricao'		=> $listProd->DESCRICAO,
							'ncm'			=> str_replace(".", "", $listProd->ncm),
							'ncmex'			=> $listProd->ncmex,
							'cfop'			=> $cfop,
							'qt'			=> $qtprod,
							'preco'			=> $listProd->preco_nf,
							'alicms'		=> 0,
							'baseicms'		=> 0,
							'vlicms'		=> 0,
							'csticms'		=> "40",
							'alipi'			=> 0,
							'vlipi'			=> 0,
							'cstipi'		=> "53",
							'origem'		=> $listProd->origem,
							'unidade'		=> $listProd->unidade,
							'codean'		=> $listProd->codigo_ean,
							'basest'		=> 0,
							'mvast'			=> 0,
							'icmsst'		=> 0,
							'vlicmsst'		=> 0,
							'desconto'		=> 0,
							'frete'			=> $frete,
							'cstpis'		=> "08",
							'alpis'			=> 0,
							'vlpis'			=> 0,
							'cstcofins'		=> "08",
							'alcofins'		=> 0,
							'vlcofins'		=> 0,
							'csttpipi'		=> $tpipi,
							'csttppis'		=> $tppis,
							'csttpcofins'	=> $tpcofins
						);
					
						$total_prod 			+= $precototal;
						$total_pedido_liquido 	+= $precototal+$frete;
						$peso = 0;
						$peso = str_replace(",", ".", $listProd->PESO);
						$pesoliquido += $qtprod*$peso;
						
						try {
							$idprod['id_nfeprod'] = $bonfeprod->insert($dataprod);
						}catch (Zend_Exception $e){
							NfeBO::gravarErronfe($e->getMessage(), $idnfe, "", 0);
							echo "Erro ao gravar produto";
						}
												
						$val['idprod'] = $listProd->ID;
						
						/*-- Listo os produtos do cliente para a baixa -------------
						 * Baixo somente a quantidade enviada, ordenado pelo ID det mais antigo ------
						*/
						
						//Zend_Debug::dump(GarantiasBO::listaProdutosapagardetalhado($val));
						//------- Baixa produtos garantia -------------------------------------------
						/* -- verifico a garantia se existe produtos a baixar.
						 * Se nao, baixo a garantia, se sim, finalizo parcialmente --------------------- */
						$contbaixa = 0;
						foreach (GarantiasBO::listaProdutosapagardetalhado($val) as $prodbaixa):
							if(($qtprodpagar > $contbaixa) and ($prodbaixa->substituir == true)):
								$contbaixa ++;
								$bogprod->update($idprod, "id = ".$prodbaixa->iddet);	
								
								$verbaixa = 0;
								foreach ($bodet->fetchAll("id_garantiaztl = ".$prodbaixa->id_garantiaztl) as $garantia):
									if((empty($garantia->id_nfeprod)) and (empty($garantia->nt_fiscal))):
										$verbaixa = 1;
									endif;
								endforeach;
								
								if($verbaixa==0):
									//--Atualizo historico da garantia-------------------------
									$arrayh['data']				= date("Y-m-d H:i:s");
									$arrayh['status']			= "FINALIZADO";
									$arrayh['id_garantiaztl']	= $prodbaixa->id_garantiaztl;
									$arrayh['id_user']			= $usuario->id;
									$bohis->insert($arrayh);
										
									//--Atualiza garantia------------
									$arrayg['status']			= "FINALIZADO";
									$arrayg['data_atualizacao'] = date("Y-m-d H:i:s");
									$bog->update($arrayg,"id = ".$prodbaixa->id_garantiaztl);
								else:
									//--Atualiza historico da garantia-------------------------
									$arrayh['data']				= date("Y-m-d H:i:s");
									$arrayh['status']			= "FINALIZAÇÃO PARCIAL";
									$arrayh['id_garantiaztl']	= $prodbaixa->id_garantiaztl;
									$arrayh['id_user']			= $usuario->id;
									$bohis->insert($arrayh);
									
									//--Atualiza garantia------------
									$arrayg['status']			= "FINALIZAÇÃO PARCIAL";
									$arrayg['data_atualizacao'] = date("Y-m-d H:i:s");
									$bog->update($arrayg,"id = ".$prodbaixa->id_garantiaztl);
									
								endif;
							endif;
						endforeach;
						
						$contbaixa = 0;
						foreach (GarantiasBO::listaProdutosapagardetalhado($val) as $prodbaixa):
							if(($qtproddev > $contbaixa) and ($prodbaixa->substituir == false)):
								$contbaixa ++;
								$bogprod->update($idprod, "id = ".$prodbaixa->iddet);
								
								$verbaixa = 0;
								foreach ($bodet->fetchAll("id_garantiaztl = ".$prodbaixa->id_garantiaztl) as $garantia):
									if((empty($garantia->id_nfeprod)) and (empty($garantia->nt_fiscal))):
										$verbaixa = 1;
									endif;
								endforeach;
								
								if($verbaixa==0):
									//--Atualizo historico da garantia-------------------------
									$arrayh['data']				= date("Y-m-d H:i:s");
									$arrayh['status']			= "FINALIZADO";
									$arrayh['id_garantiaztl']	= $prodbaixa->id_garantiaztl;
									$arrayh['id_user']			= $usuario->id;
									$bohis->insert($arrayh);
									
									//--Atualiza garantia------------
									$arrayg['status']			= "FINALIZADO";
									$arrayg['data_atualizacao'] = date("Y-m-d H:i:s");
									$bog->update($arrayg,"id = ".$prodbaixa->id_garantiaztl);
								else:
									//--Atualiza historico da garantia-------------------------
									$arrayh['data']				= date("Y-m-d H:i:s");
									$arrayh['status']			= "FINALIZAÇÃO PARCIAL";
									$arrayh['id_garantiaztl']	= $prodbaixa->id_garantiaztl;
									$arrayh['id_user']			= $usuario->id;
									$bohis->insert($arrayh);
										
									//--Atualiza garantia------------
									$arrayg['status']			= "FINALIZAÇÃO PARCIAL";
									$arrayg['data_atualizacao'] = date("Y-m-d H:i:s");
									$bog->update($arrayg,"id = ".$prodbaixa->id_garantiaztl);
									
								endif;
									
								
							endif;
						endforeach;   
						
						
					endif;
				endforeach;
			endfor;
			
			/*-- calculo total dos impostos -----------------------------
			 * Base ICMS-ST
			* -- IPI + total liquido + (IPI * total liquido * porcentagem do st)
			*/
			
			$datanfe = array(
				'baseicms'		=> 0,
				'vlicms'		=> 0,
				'basest'		=> 0,
				'vlst'			=> 0,
				'totalipi'		=> 0,
				'totalprodutos'	=> number_format($total_prod,2,".",""),
				'totalnota'		=> number_format($total_pedido_liquido,2,".",""),
				'pesoliquido'	=> $pesoliquido
			);
							
			try {
				$bonfe->update($datanfe,'id = '.$idnfe);
				//--- esse echo garente a validacao na nfegarantia.js --------------------------
				echo "idnfe:".$idnfe;
			}catch (Zend_Exception $e){
				NfeBO::gravarErronfe($e->getMessage(), $idnfe, "", 0);
			}
		}
	
		function removeNfegarantia($params){
		    $bog 		= new GarantiaModel();
		    $bogd		= new GarantiaproddetModel();
		    $bogh		= new GarantiahistoricoModel();
		    $usuario 	= Zend_Auth::getInstance()->getIdentity();
		    $boe		= new EstoqueModel();
		    
		    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		    $db->setFetchMode(Zend_Db::FETCH_OBJ);
		    
		    $select = $db->select();
		    
		    $select->from(array('t'=>'tb_garantiaztl_proddet','*'), array('t.*, t.id_prod, t.id_garantiaztl, sum(t.substituir) as qt_substituir'))
			    ->join(array('n'=>'tb_nfeprod'),'n.id = t.id_nfeprod')
			    ->where("substituir = 1 and id_nfe = ".$params['garantianfe'])
			    ->group("t.id_garantiaztl")
			    ->group("t.id_prod");
		    
		    
		    $stmt = $db->query($select);
		    foreach($stmt->fetchAll() as $garantiasprod):
		    	
		    	foreach (EstoqueBO::buscaEstoque($garantiasprod->id_prod) as $estoque);
		    	
		    	$arrayestq = array(
		    		'id_prod'			=> $garantiasprod->id_prod,
		    		'qt_atual'			=> $estoque->qt_atual+$garantiasprod->qt_substituir,
		    		'qt_atualizacao'	=> $garantiasprod->qt_substituir,
		    		'id_atualizacao'	=> $garantiasprod->id_garantiaztl,
		    		'dt_atualizacao'	=> date("Y-m-d H:i:s"),
		    		'tipo'				=> "GARANTIA CANCELADA",
		    		'id_user' 			=> $usuario->id
		    	);
		    	
		    	$boe->insert($arrayestq);
		    	
		    endforeach;
		    
		    //--- Desmarca as garantias -----------------------------------------------------------------------------
		    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		    $db->setFetchMode(Zend_Db::FETCH_OBJ);
		    
		    $select = $db->select();
		    
		    $select->from(array('t'=>'tb_garantiaztl_proddet','*'), array('t.id as idgardet','t.id_garantiaztl'))
			    ->join(array('n'=>'tb_nfeprod'),'n.id = t.id_nfeprod')
			    ->where("id_nfe = ".$params['garantianfe']);		    
		    
		    $stmt = $db->query($select);
		    foreach($stmt->fetchAll() as $garantiasprod):
			    $array = array('id_nfeprod' => NULL);
			    $bogd->update($array, "id = ".$garantiasprod->idgardet);
			     
			    $datagar = array(
		    		'status' 			=> 'FINAZAÇÃO PARCIAL',
		    		'data' 				=> date('Y-m-d H:i:s'),
		    		'id_garantiaztl' 	=> $garantiasprod->id_garantiaztl,
		    		'id_user' 			=> $usuario->id
			    );
			     
			    $bogh->insert($datagar);			     
		    endforeach;
		    
		    LogBO::cadastraLog("Vendas/Pedidos",3,$usuario->id,$params['venda'],"VENDA CANCELADA ".$params['venda']);
		    echo "sucessobaixa";
		}
		
		function buscaprodutosVenda($var){
			try{
			    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			    $db->setFetchMode(Zend_Db::FETCH_OBJ);
			    
			    $select = $db->select();
			    
			    $select->from(array('p'=>'tb_pedidos','*'), array('*'))
				    ->join(array('pp'=>'tb_pedidos_prod'),'pp.id_ped = p.id')
				    ->where("p.id_parceiro = '".$var['cliente']."' and pp.id_prod = '".$var['prod']."'")
			    	->order('p.id desc')
			    	->limit(10);
			    		    
			    $stmt = $db->query($select);
			    $lisp = $stmt->fetchAll();
			    
			    if(count($lisp)>0){
				    $preco = 0;
				    foreach($lisp as $produtos){
				        if($produtos->preco_unit > $preco) $preco = $produtos->preco_unit;
				    }
				    
				    $preco = ($preco*1.3);
				    return $preco;
				    
			    }else{
			    	return "erro3";
			    	//-- cliente nao comprou este produto ---------------------------------------
			    } 
			    
			 }catch (Zend_Exception $e){
		    	$boerro	= new ErrosModel();
		    	$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "GarantiasBO::buscaprodutosVenda()");
		    	$boerro->insert($dataerro);
				return false;
		    }
		}
		
	}
?>
