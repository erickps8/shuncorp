<?php
	class ClientesBO{	
				
		function listaTrackcode(){
			$obj = new ClientesModel();
			return $obj->fetchAll("track_code !=''","track_code");			
		}
		
		function listaEmails($params){
			$ob = new ClientesModel();
			$obj = new ClientesEmailModel();
			return $obj->fetchAll('ID_CLIENTE = '.$params);		
		}
		
		function listaEmailsUp($id, $tipo){
			$ob = new ClientesModel();
			$obj = new ClientesEmailModel();
			return $obj->fetchAll('ID_CLIENTE = '.$id.' and TIPO = '.$tipo);				
		}
		
		//-- Usado em MailingBO::disparaEmailmarketing --------------------------
		function listaemailsAllclientes($var){
						
			if(!empty($var['cliente'])):
				$where = 'and c.id_perfil in (2,24)';
			elseif(!empty($var['funcionario'])):
				$where = 'and c.id_perfil in (1,7,8,9,11,12,16,19)';
			elseif(!empty($var['ger'])):
				$where = 'and c.id_perfil in (4,5,10)';
			elseif(!empty($var['rep'])):
				$where = 'and c.id_perfil in (3)';
			elseif(!empty($var['func'])):
				$where = 'and c.id_perfil in (1,7,8,9,11,12,16,19)';
			elseif(!empty($var['forcavenda'])):
				$where = 'and c.id_perfil in (3,4,5,6,10,30,1,28,29)';
			endif;
						
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);			
			$select = $db->select();
			
			$select->from(array('c'=>'clientes','*'),
			        array('c.ID as idparc','c.EMPRESA','ce.EMAIL','ce.NOME_CONTATO'))
			        ->join(array('ce'=>'clientes_emails'),'ce.ID_CLIENTE = c.ID')
			        ->where("c.TIPO not like '%inativo%' and ce.TIPO = 1 and  ce.EMAIL != ''  ".$where)
			        ->group("EMAIL");			       
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		public function listaEmailcontatos(){
			$ob = new ContatosModel();
			return $ob->fetchAll('migrado = 0 and mailing = true');				
		}
		
		public function listaEmailcontatosnovo(){
			$obj = new ContatosModel();
			return $obj->fetchAll("mailing = true");				
		}
		
		public function listaemailsAllnewslatter(){
			$bo		=	new MailingModel();
			$bon	=	new MailingemailsModel();
			return $bon->fetchAll('ATIVO = "S"');			
		}
		
		public function listaEnderecos($params){
		    $obj = new ClientesModel();
			$obj = new ClientesEnderecoModel();
			return $obj->fetchAll('ID_CLIENTE = '.$params);			
		}
		
		function listaEnderecosUp($id, $tipo){
			$bo	= new ClientesModel();
			$obj = new ClientesEnderecoModel();
			return $obj->fetchAll('ID_CLIENTE = '.$id.' and TIPO = '.$tipo);			
		}
		
		function listaEnderecocomp($id, $tipo){
			/*-- Lista endereco com pais, estado e cidade com cod IBGE --------
			 * Usado em pedidoseditAction;
			 */			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);			
			$select = $db->select();
			
			$select->from(array('c'=>'clientes_endereco'), array('c.*','p.nome as npais','p.codigo as codpais','e.nome as nestado','cd.nome as ncidade','e.uf as nuf','cd.codigo as codcidade'))
			        ->joinLeft(array('p'=>'tb_paises'),'p.id = c.PAIS')
			        ->joinLeft(array('e'=>'tb_estados'),'e.id = c.ESTADO')
			        ->joinLeft(array('cd'=>'tb_cidades'),'cd.id = c.id_cidades')
			        ->where("c.TIPO = ".$tipo." and c.ID_CLIENTE = ".$id);			       
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();			
		}
		
		public function listaEnderecoschines($params){
			$obj = new ClientesEnderecoChinesModel();
			return $obj->fetchAll('ID_CLIENTE = '.$params);			
		}
		
		public function listaTelefones($params){
			$bo		= new ClientesModel();
			$obj = new ClientesTelefoneModel();
			return $obj->fetchAll('ID_CLIENTE = '.$params);			
		}
		
		public function listaTelefonesUp($id, $tipo){
			$bo		= new ClientesModel();
			$obj 	= new ClientesTelefoneModel();
			return $obj->fetchAll('ID_CLIENTE = '.$id.' and TIPO = "'.$tipo.'"');			
		}
		
		public function listaDesc($params){
			$cli = new ClientesModel();
			$obj = new ClientesDescModel();
			return $obj->fetchAll('id_cliente = '.$params);			
		}
		
		public function listaChina($params){
			$cli = new ClientesModel();
			$obj = new ClientesClichinesModel();
			return $obj->fetchAll('id_cliente = '.$params);			
		}
		
		public function listaInfokang($params){
			$obj = new ClientesInfoKangModel();
			return $obj->fetchAll('id_cliente = '.$params);			
		}
		
		function buscaClientes($params){
			$obj 	= new ClientesModel();
			
			$sessaobusca = new Zend_Session_Namespace('Parceiros');
		    if($sessaobusca->where!=""):
		   		$where = $sessaobusca->where;
		   	endif;	
		   	
		   	$usuario 	= Zend_Auth::getInstance()->getIdentity();
		   	$sql = "";
		   	
		   	if(!empty($params['busca']) and ($params['buscaregioes']!=0)):
		   		$where = ' and (EMPRESA like "%'.$params['busca'].'%" '.$sql.' || RAZAO_SOCIAL like "%'.$params['busca'].'%" '.$sql.' || CPF_CNPJ like "%'.$params['busca'].'%" '.$sql.' || ID = "'.$params['busca'].'" '.$sql.') and ID_REGIOES = '.$params['buscaregioes'];
		   	elseif(!empty($params['busca'])):
		   		$where = ' and (EMPRESA like "%'.$params['busca'].'%" '.$sql.' || RAZAO_SOCIAL like "%'.$params['busca'].'%" '.$sql.' || CPF_CNPJ like "%'.$params['busca'].'%" '.$sql.' || ID = "'.$params['busca'].'" '.$sql.') ';
		   	endif;
		   	
		   	$where .= ($params['buscaregioes']!=0) ? ' and ID_REGIOES = "'.$params['buscaregioes'].'"' : "";
		   	$where .= ($params['buscateleregioes']!=0) ? ' and id_regioestelevendas = "'.$params['buscateleregioes'].'"' : "";
		   	
		   	
		   	
			if(!empty($where)):
		   		$sessaobusca->where = $where;
			endif;
			
			//--- Controle de perfil ------------------------------------------
			foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
			if($list->nivel==1){
				foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
				if($list->nivel == 1){
					if($usuario->id_perfil == 31){
						$where .= " and id_regioestelevendas in (".RegioesBO::listaRegioesusuarios(1).")";
					}elseif(($usuario->id_perfil == 4) || ($usuario->id_perfil == 5)){
						$where .= " and (ID_REGIOES in (".RegioesBO::listaRegioesusuarios(0).") || id_regioestelevendas in (".RegioesBO::listaRegioesusuarios(1)."))";
					}else{
						$where .= " and ID_REGIOES in (".RegioesBO::listaRegioesusuarios(0).")";
					}
				}
			}elseif($list->nivel==0){
				$where .= " and ID = ".$usuario->id_cliente;
			}
						
			return $obj->fetchAll('sit = true '.$where,"ID desc");			
		} 
	
		
		/* public function listaClientesreg(){
			$obj 	= new ClientesModel();
			$bo		= new RegioesModel();
			$bor	= new RegioesclientesModel();
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
				
		   	$sql = "";
			foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
			if($list->nivel == 1):
				foreach ($bor->fetchAll("id_clientes = ".$usuario->id) as $regioes):
					$reg .= $regioes->id_regioes.",";
				endforeach;				
				$sql = " and ID_REGIOES in (".substr($reg,0,-1).")";
				return $obj->fetchAll("TIPO not like '%inativo%' and ID_REGIOES in (".substr($reg,0,-1).") and id_perfil in (2,24)","EMPRESA ASC");
			elseif($list->nivel == 2):
				return $obj->fetchAll("TIPO not like '%inativo%' and  id_perfil in (2,24)","EMPRESA ASC");
			else:
				return "";
			endif;						
		} */
		
		
		function buscaEmpresacnpj($params){
			$obj = new ClientesModel();
			
			//--- Controle de perfil ------------------------------------------
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
		    if($list->nivel==1){
		    	foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
		    	if($list->nivel == 1){
		    		if($usuario->id_perfil == 31){
		    			$where .= " and id_regioestelevendas in (".RegioesBO::listaRegioesusuarios(1).")";
		    		}elseif(($usuario->id_perfil == 4) || ($usuario->id_perfil == 5)){
		    			$where .= " and (ID_REGIOES in (".RegioesBO::listaRegioesusuarios(0).") || id_regioestelevendas in (".RegioesBO::listaRegioesusuarios(1)."))";
		    		}else{
		    			$where .= " and ID_REGIOES in (".RegioesBO::listaRegioesusuarios(0).")";
		    		}
		    	}
		    }elseif($list->nivel==0){
		    	$where .= " and ID = ".$usuario->id_cliente;
		    }
			
			return $obj->fetchAll('sit = true and TIPO not like "%inativo%" and CPF_CNPJ = "'.$params.'"'.$where);			
		}

		function cadastraClientes($params){
		    
		    $arrayRet = array();
		    
			$bocli	= new ClientesModel();
			$boend	= new ClientesEnderecoModel();
			$boendC	= new ClientesEnderecoChinesModel();
			$botel	= new ClientesTelefoneModel();
			//$bodesc	= new ClientesDescModel();
			$bomail	= new ClientesEmailModel(); 
			$boinf	= new ClientesInfoKangModel();
			$bochi	= new ClientesClichinesModel();
			$boanex = new ClientesanexoModel();
			
			$bocee  = new ClientesconsigneeModel();
			
			$boerr	= new ErrosModel();
						
			try{
			
				$array['dt_cadastro']  			= date("Y-m-d H:i:s");
				$array['CPF_CNPJ']   			= $params['cpf_cnpj'];
				$array['RG_INSC']   			= $params['rg_insc'];
				$array['EMPRESA']   			= $params['empresa'];
				$array['RAZAO_SOCIAL']			= $params['razao_social'];
				$array['TIPO']					= $params['tipo'];
				$array['OBSERVACAO']			= $params['observacao'];
				$array['obsnfe']				= $params['obsnfe'];
				$array['nu_agente_1']			= $params['agente_1'];
				$array['nu_agente_2']			= $params['agente_2'];
				$array['nu_agente_3']			= $params['agente_3'];
				$array['site']					= $params['txtSite'];
				$array['track_code']			= $params['txtTrackCode'];
				$array['moeda']					= $params['moeda'];
				$array['atividades']			= $params['checkatividade'];
				$array['id_despesasfiscais']	= $params['despesasfiscais'];
				$array['data_abertura']			= substr($params["dtabertura"],6,4).'-'.substr($params["dtabertura"],3,2).'-'.substr($params["dtabertura"],0,2);
				$array['id_perfil']				= $params['perfil'];
				
				$array['meta']					= str_replace(",", ".",str_replace(".", "",$params['meta']));
				$array['obsrel']				= $params['obsrel'];
								
				if($params['id_regioes'] != 0){
					$array['ID_REGIOES']			= $params['id_regioes'];
				}else{
				    $array['ID_REGIOES']			= NULL;
				}
				
				if($params['ginteresse'] != 0):
					$array['id_clientesgrupos']		= $params['ginteresse'];
				else:
					$array['id_clientesgrupos']		= NULL;
				endif;
				
				if($params['regioestelevendas'] != 0):
					$array['id_regioestelevendas']	= $params['regioestelevendas'];
				else:
					$array['id_regioestelevendas']	= NULL;
				endif;
				
				if($params['trans'] == "nc"):
					$array['tptransp']	= 1;
				elseif($params['trans'] == 'cr'):
					$array['tptransp']	= 2;
				else:
					if(($params['trans'])!=0):
						$array['tptransp']	= 0;					
						$array['id_transportadoras']	= $params['trans'];
					endif;
				endif;
				
				if(empty($params["id_cliente"])){
					$idcli = $bocli->insert($array);			
				}else{
					$idcli = $params["id_cliente"];			
					$bocli->update($array,"ID = ".$idcli);
				}
							
			}catch (Zend_Exception $e){
			    $arrayRet['erro1'] = "1";
			    $arrayRet['textoErro'] = $e->getMessage();
			}
			
			try{
				$boend->delete("ID_CLIENTE = ".$idcli);
				
				//--Enderecos --------------------------
				$arrayEnd['ID_CLIENTE']			= $idcli;
				$arrayEnd['LOGRADOURO']			= $params['logradouro1'];
				$arrayEnd['BAIRRO']				= $params['bairro1'];
				$arrayEnd['ESTADO']				= $params['uf'];
				$arrayEnd['PAIS']				= $params['pais'];
				$arrayEnd['CEP']				= $params['cep1'];
				$arrayEnd['TIPO']				= 1;
				$arrayEnd['numero']				= $params['n1'];
				$arrayEnd['complemento']		= $params['complemento1'];
				
				if($params['cidade']!=0):
					$arrayEnd['id_cidades']			= $params['cidade'];
				endif;
				$boend->insert($arrayEnd);
				
				if($params['cpendereco']==1):
					$arrayEndE 			= $arrayEnd;
					$arrayEndE['TIPO']	= 2;
				else:
					$arrayEndE['ID_CLIENTE']		= $idcli;
					$arrayEndE['LOGRADOURO'] 		= $params['logradouro2'];
					$arrayEndE['BAIRRO']			= $params['bairro2'];
					$arrayEndE['ESTADO']			= $params['ufcob'];
					$arrayEndE['PAIS']				= $params['pais2'];
					$arrayEndE['CEP']				= $params['cep2'];
					$arrayEndE['TIPO']				= 2;
					$arrayEndE['numero']			= $params['n2'];
					$arrayEndE['complemento']		= $params['complemento2'];
					
					if($params['cidade2']!=0):
						$arrayEndE['id_cidades']	= $params['cidade2'];
					endif;
					
				endif;
				
				$boend->insert($arrayEndE);
				
				$boendC->delete("ID_CLIENTE = ".$idcli);
				
				$arrayEndc['ID_CLIENTE'] 		= $idcli;
				$arrayEndc['LOGRADOURO']		= $params['logradourochi'];
				$arrayEndc['ESTADO']			= $params['ufchi'];
				$arrayEndc['CEP']				= $params['cepchi'];
				$boendC->insert($arrayEndc);
				
				//--Nome chines---------------------------
				$bochi->delete('id_cliente = '.$idcli);
				$arrayChi['id_cliente'] 		= $idcli;
				$arrayChi['nome']				= $params['nomechines'];
				$bochi->insert($arrayChi);
				
				//--Telefones--------------------------
				$botel->delete("ID_CLIENTE = ".$idcli);
				
				$arrayTel['ID_CLIENTE'] 		= $idcli;
				$arrayTel['NUMERO']				= $params['numero1'];
				$arrayTel['DDD']				= $params['ddd1'];
				$arrayTel['DDI']				= $params['ddi1'];
				$arrayTel['NEXTEL']				= $params['nextel1'];
				$arrayTel['TIPO']				= "telefone1";
				$botel->insert($arrayTel);
				
				$arrayTel2['ID_CLIENTE'] 		= $idcli;
				$arrayTel2['NUMERO']			= $params['numero2'];
				$arrayTel2['DDD']				= $params['ddd2'];
				$arrayTel2['DDI']				= $params['ddi2'];
				$arrayTel2['NEXTEL']			= $params['nextel2'];
				$arrayTel2['TIPO']				= "telefone2";
				$botel->insert($arrayTel2);
					
				$arrayTelf['ID_CLIENTE'] 		= $idcli;
				$arrayTelf['NUMERO']			= $params['numero3'];
				$arrayTelf['DDD']				= $params['ddd3'];
				$arrayTelf['DDI']				= $params['ddi3'];
				$arrayTelf['TIPO']				= "fax";
				$botel->insert($arrayTelf);
					
				//-Emails-------------------------------
				$bomail->delete("ID_CLIENTE = ".$idcli);
						
				$arrayMail['ID_CLIENTE'] 		= $idcli;
				$arrayMail['NOME_CONTATO']		= $params['contato1'];
				$arrayMail['EMAIL']				= $params['email1'];
				$arrayMail['TIPO']				= 1;
				$bomail->insert($arrayMail);
				
				$arrayMail3['ID_CLIENTE'] 		= $idcli;
				$arrayMail3['NOME_CONTATO']		= $params['contato3'];
				$arrayMail3['EMAIL']			= $params['email3'];
				$arrayMail3['TIPO']				= 3;
				$bomail->insert($arrayMail3);
				
				$arrayMail4['ID_CLIENTE'] 		= $idcli;
				$arrayMail4['NOME_CONTATO']		= $params['contato4'];
				$arrayMail4['EMAIL']			= $params['email4'];
				$arrayMail4['TIPO']				= 4;
				$bomail->insert($arrayMail4);
				
				//-Descontos, tranportadora e prazos-----
				/*$bodesc->delete("id_cliente = ".$idcli);
				$arrayDesc['id_cliente'] 		= $idcli;
				$arrayDesc['desc1']				= str_replace(",", ".",str_replace(".", "",$params['desc1']));
				$arrayDesc['desc2']				= str_replace(",", ".",str_replace(".", "",$params['desc2']));
				$arrayDesc['desc3']				= str_replace(",", ".",str_replace(".", "",$params['desc3']));
				$arrayDesc['desc4']				= str_replace(",", ".",str_replace(".", "",$params['desc4']));
				$arrayDesc['desc5']				= str_replace(",", ".",str_replace(".", "",$params['desc5']));
				$arrayDesc['prazo1']			= $params['prazo1'];
				$arrayDesc['prazo2']			= $params['prazo2'];
				$arrayDesc['prazo3']			= $params['prazo3'];
				$arrayDesc['prazo4']			= $params['prazo4'];
				$arrayDesc['prazo5']			= $params['prazo5'];
				$bodesc->insert($arrayDesc); */
				
				$boinf->delete("id_cliente = ".$idcli);
				$arrayInfo['id_cliente'] 		= $idcli;
				$arrayInfo['from_c']			= $params['defrom'];
				$arrayInfo['to_c']				= $params['to'];
				$arrayInfo['freight']			= $params['freight'];
				$arrayInfo['payment']			= $params['terms'];
				$arrayInfo['partial_shipment']	= $params['acc'];
				$arrayInfo['agente_shipment']	= $params['agent'];
				$boinf->insert($arrayInfo);
				
			}catch (Zend_Exception $e){
			    $arrayRet['erro2'] = "1";				
			}
			
			try{
                $arrayConsignee = array(
                    'empresa'       =>  $params['consigneeempresa'],
                    'cnpj'          =>  $params['consigneecnpj'],
                    'ie'            =>  $params['consigneeie'],
                    'logradouro'    =>  $params['consigneelogradouro'],
                    'bairro'        =>  $params['consigneebairro'],
                    'id_cidade'     =>  $params['consigneecidade'],
                    'fone'          =>  $params['consigneefone'],
                    'cep'           =>  $params['consigneecep'],
                    'id_cliente'    =>  $idcli,
                );   
                
                if($params['idconsignee'] != ""){
                    $bocee->update($arrayConsignee, 'id_cliente = "'.$idcli.'"');
                }else{
                    $bocee->insert($arrayConsignee);
                }
                
			}catch (Zend_Exception $e){
			    $arrayRet['erro2'] = "1";
			}
			
			
			try{
				//---Arquivos-------------------------------
				 
				$pasta = Zend_Registry::get('pastaPadrao')."public/sistema/upload/clientes/".$idcli;
				 
				if (!(is_dir($pasta))){
					if(!(mkdir($pasta, 0777))){
						$dataerro = array('descr' => "Alerta: pasta de upload nao existe, e nao pode ser criada", 'erro' => "Novo usuario");
						$boerr->insert($dataerro);
					}
				}
				
				if(!(is_writable($pasta))){
					$dataerro = array('descr' => "Alerta: pasta sem permissao de escrita", 'erro' => "Novo cliente");
					$boerr->insert($dataerro);
				}
				 
				$pasta = Zend_Registry::get('pastaPadrao')."public/sistema/upload/clientes/".$idcli."/cadastro";
				
				if (!(is_dir($pasta))){
					if(!(mkdir($pasta, 0777))){
						$dataerro = array('descr' => "Alerta: pasta de upload nao existe, e nao pode ser criada", 'erro' => "Novo cliente");
						$boerr->insert($dataerro);		
					}
				}
				 
				if(!(is_writable($pasta))){
					$dataerro = array('descr' => "Alerta: pasta sem permissao de escrita", 'erro' => "Novo cliente");
					$boerr->insert($dataerro);
				}
				 
				$upload = new Zend_File_Transfer_Adapter_Http();
				$upload->setDestination($pasta);
				$files = $upload->getFileInfo();
				 
				if($files):
					foreach ($files as $file => $info):
					
						$num = str_replace('anexo', '', $file);
						
						if ($upload->isValid($file)) {
							$upload->receive($file);
						}
					 
						if($info['tmp_name']):				
							$dataanexo = array(
								'nomearquivo'	=> $params['nomearquivo'.$num],
								'nome'			=> $info['name'],
								'id_clientes'	=> $idcli
							);
							 
							$boanex->insert($dataanexo);
						endif;
					endforeach;
				endif;
			
				
			}catch (Zend_Exception $e){
			    $arrayRet['erro3'] = "1";
			}			
			
			
			/*-- Atualizacao nos contatos -----------------------------------------------------------------
			 * Essa atualizacao se dah quando existe o cantato cadastrado como parceiro. Toda atualizacao no parceiro, resulta
			 * em atualizacao nos contatos.
			 */
			/*
			try{
			
				$bocontatos		= new ContatosModel();
				$bomatriz		= new ContatosempModel();
				
				$arraycontato['empresa']		= $params['empresa'];
				$arraycontato['endereco']		= $params['logradouro1']." ".$params['n1']." ".$params['complemento1'];
				$arraycontato['bairro']			= $params['bairro1'];
				$arraycontato['cep']			= $params['cep1'];
				$arraycontato['pais']			= $params['pais1'];
				$arraycontato['status']			= 1;
				$arraycontato['verificado']		= 1;
				
				if($params['ginteresse'] != 0):
					$arraycontato['id_clientesgrupos']			= $params['ginteresse'];
				endif;
				
				
				$nuf = EstadosBO::buscaUFporid($params['uf']);
				$arraycontato['uf']				= $nuf;
				
				if($params['cidade']!=0):
					$arraycontato['cidade']			= $params['cidade'];
				endif;
				
				$arraycontato['regiao']			= $params['id_regioes'];
				$arraycontato['tipo_par']		= "Parceiro";
				$arraycontato['data_abertura']	= substr($params["dtabertura"],6,4).'-'.substr($params["dtabertura"],3,2).'-'.substr($params["dtabertura"],0,2);
				
				if($params['regioestelevendas'] != 0):
					$arraycontato['id_regioestelevendas']	= $params['regioestelevendas'];
				endif;
				
				$bomatriz->update($arraycontato, "id_clientes = ".$idcli);			
				
				$matriz = $bomatriz->fetchRow("id_clientes = '".$idcli."'");
				
				if(count($matriz)>0){
					LogBO::cadastraLog("Cadastro/Empresa Contatos",4,$usuario->id,$matriz->id,"Empresa ID ".$matriz->id);
				}
				
			}catch (Zend_Exception $e){
				$arrayRet['erro4'] = "1";
			} */
				
			$arrayRet['idcliente'] = $idcli;			
			return $arrayRet;	        
		}
		
		function gravaEmail($params){
		    $boc	= new ClientesModel();
		    $boe	= new ClientesEmailModel();
		    	
		    	
		    try {
		    	$arraymail	= array(
	    			'ID_CLIENTE'	=> $params['parceiro'],
	    			'EMAIL'			=> $params['email'],
	    			'NOME_CONTATO'	=> $params['contatoemail'],
	    			'tipo'			=> 3
		    	);
		    		
		    	$boe->delete("ID_CLIENTE = ".$params['parceiro']." and tipo = 3");
		    	$boe->insert($arraymail);
		    }catch (Zend_Exception $e){
		    	$boerro	= new ErrosModel();
		    	$dataerro = array('descricao' => $e->getMessage(), 'pagina' => $params[ped]);
		    	$boerro->insert($dataerro);
		    }
		}
		
		function listaArquivoscliente($params){
			$bou	= new ClientesModel();
			$boua	= new ClientesanexoModel();
			return $boua->fetchAll("md5(id_clientes) = '".$params['idparceiro']."'");
		}
		
		function removeAnexo($params){
			$bou	= new ClientesModel();
			$boua	= new ClientesanexoModel();
			
			foreach ($boua->fetchAll("md5(id) = '".$params['anexo']."'") as $user);
			$boua->delete("md5(id) = '".$params['anexo']."'");
			return $user->id_clientes;
		}
		
		
		/* ----- busca parceiros -------------------------------------------------------------------------------------
		 * @$busca['busca'] = valor para busca ----
		 * @$tipo = cliente, transportadoras, clienteschines, fornecedorchines, fornecedor, representantes
		 * @$sit = A - ativos, I - Inativos, T - todos
		 * 
		 */
		function buscaParceiros($tipo="",$busca="",$sit="T"){
		    $usuario 	= Zend_Auth::getInstance()->getIdentity();
		    
		    $where = "";
		    if($tipo == 'clientes'):
		    	$where = ' and c.id_perfil in (2,24,27,28)';
		    elseif($tipo == 'clientesf'):
		    	$where = ' and c.id_perfil in (2)';
		    elseif($tipo == 'transportadoras'):
		   	 	$where = ' and c.id_perfil in (26)';
		    elseif($tipo == 'clienteschines'):
		    	$where = ' and c.id_perfil in (23,25,27,28)';
		    elseif($tipo == 'fornecedorchines'):
		    	$where = ' and c.id_perfil in (23,18,27)';
		    elseif($tipo == 'fornecedor'):
		    	$where = ' and c.id_perfil in (19,24,27,3)';
		    elseif($tipo == 'representantes'):
		    	$where = ' and c.id_perfil in (3)';
		    endif;
		    		    
		    if(!empty($busca['busca'])):
		    	$where .= ' and (EMPRESA like "%'.$busca['busca'].'%" || RAZAO_SOCIAL like "%'.$busca['busca'].'%" || CPF_CNPJ like "%'.$busca['busca'].'%" || ID = "'.$busca['busca'].'")';
		    endif;
		    
		    if(isset($busca['buscaregioes'])):
		    	if($busca['buscaregioes']!=0 ) $where .= ' and ID_REGIOES = '.$busca['buscaregioes'];
		    endif;
		    
		    if(!empty($busca['idparceiro'])):
		    	$where .= ' and c.ID = '.$busca['idparceiro'];
		    elseif(!empty($busca['idparceiromd5'])):
		    	$where .= ' and md5(c.ID) = "'.$busca['idparceiromd5'].'"';
		    endif;
		    
		    if($sit == "I"):
		    	$where .= " and TIPO like '%inativo%'";
		    elseif($sit == "A"):
		    	$where .= " and TIPO not like '%inativo%'";
		    endif;
		    
		    //--- Controle de perfil ------------------------------------------
		    foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
		    if($list->nivel==1){
		    	foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
		    	if($list->nivel == 1){
		    		if($usuario->id_perfil == 31){
		    			$where .= " and id_regioestelevendas in (".RegioesBO::listaRegioesusuarios(1).")";
		    		}elseif(($usuario->id_perfil == 4) || ($usuario->id_perfil == 5)){
		    			$where .= " and (ID_REGIOES in (".RegioesBO::listaRegioesusuarios(0).") || id_regioestelevendas in (".RegioesBO::listaRegioesusuarios(1)."))";
		    		}else{
		    			$where .= " and ID_REGIOES in (".RegioesBO::listaRegioesusuarios(0).")";
		    		}
		    	}
		    }elseif($list->nivel==0){
		    	$where .= " and ID = ".$usuario->id_cliente;
		    }
		    
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
			$select = $db->select();
		
			$select->from(array('c'=>'clientes','*'), array('*'))
				   ->where('c.sit = true'.$where)
				   ->order("c.EMPRESA asc");
				
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		function buscaClientescnpj($params){
			$obj = new ClientesModel();
			//--- Controle de perfil ------------------------------------------
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
		    if($list->nivel==1){
		    	foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
		    	if($list->nivel == 1){
		    		if($usuario->id_perfil == 31){
		    			$where .= " and id_regioestelevendas in (".RegioesBO::listaRegioesusuarios(1).")";
		    		}elseif(($usuario->id_perfil == 4) || ($usuario->id_perfil == 5)){
		    			$where .= " and (ID_REGIOES in (".RegioesBO::listaRegioesusuarios(0).") || id_regioestelevendas in (".RegioesBO::listaRegioesusuarios(1)."))";
		    		}else{
		    			$where .= " and ID_REGIOES in (".RegioesBO::listaRegioesusuarios(0).")";
		    		}
		    	}
		    }elseif($list->nivel==0){
		    	$where .= " and ID = ".$usuario->id_cliente;
		    }
			
			return $obj->fetchAll('sit = true and CPF_CNPJ = "'.$params.'"'.$where);
		}
		
		//-- grupos de interesse ----------------------------------
		function buscaClientesgrupos($params=""){
		    $boc	= new ClientesModel();
		    $bog	= new ClientesgruposModel();
		    
		    $where = '';
		    if(!empty($params['grupo'])) $where = " and md5(id) = '".$params['grupo']."'";
		    
		    return $bog->fetchAll("sit = 1 ".$where,'nome');		    
		}
		
		function gravaGrupointeresse($params){
		    $boc	= new ClientesModel();
		    $bo 	= new ClientesgruposModel();
		    $bog 	= new ClientesgruposprodModel();
		    
		    try{
		        $array['nome']	= $params['nome'];
		        
		        if(empty($params['idgrupo'])):
				    $id = $bo->insert($array);
			    else:
			    	$bo->update($array,"id = ".$params['idgrupo']);
			    	$id = $params['idgrupo'];
			    endif;
			    
			    //--- Grupos de interesse ----------------------------------------------------------------------
			    if($params['grupoParceiro']!=""):
				    $bog->delete('id_clientesgrupos = '.$id);
				    foreach($params['grupoParceiro'] as $opcao){
				    	$arraygrup['id_clientesgrupos'] 		= $id;
				    	$arraygrup['id_gruposprodsub']	 	= $opcao;
				    	$bog->insert($arraygrup);
				    }
			    endif;
			    
			    return 1;
		    }catch (Zend_Exception $e){
		        return 2;
		    }
		}
		
		function listaGruposinteresse($params){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
			$select = $db->select();
		
			$select->from(array('s'=>'tb_gruposprodsub','*'), array('s.descricao as subgrupo','s.id as idsub','g.descricao as grupo','g.id as idgrupo'))
				->join(array('g'=>'tb_gruposprod'), 'g.id = s.id_gruposprod')
				->join(array('gc'=>'tb_clientesgruposprod'), 'gc.id_gruposprodsub = s.id')
				->where("md5(gc.id_clientesgrupos) = '".$params['grupo']."'")
				->order("g.descricao","s.descricao");
				
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		function removeGruposinteresse($params){
		    try{
			    $boc	= new ClientesModel();
				$bog	= new ClientesgruposModel();
			
				$data['sit'] = false;
				$bog->update($data, "md5(id) = '".$params['grupo']."'");
				
				return true;
		    }catch (Zend_Exception $e){
				return false;
			}
		}
		
		//--- Pre cadastro -------------------------------------------------------------
		function gravaPrecadastro($params){
			try{
				$boc	= new ClientesModel();
				$bo		= new ClientesprecadastroModel();
				$boa	= new ClientesprecadastroanexosModel();
				$boerr 	= new ErrosModel();
				 
				$array['cnpj']				= $params['cnpj'];
				$array['inscricao']			= $params['inscricao'];
				$array['fantasia']			= $params['empresa'];
				$array['razaosocial']		= $params['razao_social'];
				$array['dataabertura']		= substr($params["dtabertura"],6,4).'-'.substr($params["dtabertura"],3,2).'-'.substr($params["dtabertura"],0,2);
				$array['tel1']				= $params['tel1'];
				$array['tel2']				= $params['tel2'];
				$array['nextel']			= $params['nextel'];
				$array['email']				= $params['email'];
				$array['nomecontato']		= $params['contato'];
				$array['emailcompras']		= $params['emailcompras'];
				$array['nomecontatocompras']= $params['contatocompras'];
				$array['emailnfe']			= $params['emailnfe'];
				$array['nomecontatonfe']	= $params['contatonfe'];
				$array['logradouro']		= $params['logradouro'];
				$array['numero']			= $params['numero'];
				$array['bairro']			= $params['bairro'];
				$array['complemento']		= $params['complemento'];
				$array['cep']				= str_replace("-","",$params['cep']);
				$array['id_cidades']		= $params['cidade'];
				$array['ref1']				= $params['empresa1'];
				$array['telref1']			= $params['numero1'];
				$array['ref2']				= $params['empresa2'];
				$array['telref2']			= $params['numero2'];
				$array['ref3']				= $params['empresa3'];
				$array['telref3']			= $params['numero3'];
				$array['ref4']				= $params['empresa4'];
				$array['telref4']			= $params['numero4'];
				$array['ref5']				= $params['empresa5'];
				$array['telref5']			= $params['numero5'];
				$array['obs']				= $params['obs'];
					
				$idcli = $bo->insert($array);
		
				
				try{
					//---Arquivos-------------------------------
						
					$pasta = Zend_Registry::get('pastaPadrao')."public/sistema/upload/precadastro";
						
					if (!(is_dir($pasta))){
						if(!(mkdir($pasta, 0777))){
							$dataerro = array('descr' => "Alerta: pasta de upload nao existe, e nao pode ser criada", 'pagina' => "ClientesBO::gravaPrecadastro()");
							$boerr->insert($dataerro);
						}
					}
				
					if(!(is_writable($pasta))){
						$dataerro = array('descricao' => "Alerta: pasta de upload nao existe, e nao pode ser criada", 'pagina' => "ClientesBO::gravaPrecadastro()");
						$boerr->insert($dataerro);
					}
						
						
					$upload = new Zend_File_Transfer_Adapter_Http();
					$upload->setDestination($pasta);
					$files = $upload->getFileInfo();
						
					if($files){
						foreach ($files as $file => $info){
						    
						    $num = str_replace('anexo', '', $file);
						    
							$exts = split("[/\\.]", $info['name']) ;
							$n = count($exts)-1;
							$exts = $exts[$n];
							
							$cont = count($boa->fetchAll("id_precadastroparceiro = ".$idcli));
							$cont++;
							
							$upload->addFilter('Rename', array('target' => $pasta.'/'.$idcli.'_'.$cont.'.'.$exts, 'overwrite' => true));
							if ($upload->isValid($file)) {
								$upload->receive($file);
							}
							
							if($info['tmp_name']){
								$dataanexo = array(
									'anexo'						=> $exts,
								    'nome'						=> $params['nomearquivo'.$num],
									'id_precadastroparceiro'	=> $idcli
								);
							
								$boa->insert($dataanexo);
							}
						}
					}
						
				
				}catch (Zend_Exception $e){
					$arrayRet['erro3'] = "1";
				}
				
				
				return true;
			}catch (Zend_Exception $e){
				return false;
			}
		}
		
		function listaPrecadastro($params=""){
		    $boc	= new ClientesModel();
		    $bo		= new ClientesprecadastroModel();
		    
		    if(isset($params['cadastro']) and !empty($params['cadastro'])){
		        $where = " and md5(p.id) = '".$params['cadastro']."'";
		    }
		    
		    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		    $db->setFetchMode(Zend_Db::FETCH_OBJ);
		    
		    $select = $db->select();
		    
		    $select->from(array('p'=>'tb_precadastroparceiro','*'), array('p.*','p.id as idcadastro','c.nome as cidade','e.nome as estado','p.sit'))
			    ->join(array('c'=>'tb_cidades'), 'c.id = p.id_cidades')
			    ->join(array('e'=>'tb_estados'), 'e.id = c.id_estados')
			    ->where("p.sit in (1,2) ".$where)
			    ->order("p.id desc");
		    
		    $stmt = $db->query($select);
		    return $stmt->fetchAll();
		    
		} 
		
		function removePrecadastro($params){
			try{
			    $boc	= new ClientesModel();
				$bo		= new ClientesprecadastroModel();
			
				return $bo->update(array('sit' => 0), "md5(id) = '".$params['cadastro']."'");
				return true;
			}catch (Zend_Exception $e){
				$boerro	= new ErrosModel();
		    	$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "ClientesBO::removePrecadastro(".$params['cadastro'].")");
		    	$boerro->insert($dataerro);
				return false;
			}
		}
		
		function inserePrecadastro($params){
		    
		    try{
		        
		        $arrayRet = array();
		        
		        $bocli	= new ClientesModel();
		        $boend	= new ClientesEnderecoModel();
		        $botel	= new ClientesTelefoneModel();
		        $bomail	= new ClientesEmailModel();
		        $bo		= new ClientesprecadastroModel();
		        
		        foreach (ClientesBO::listaPrecadastro($params) as $cadastro);
		        	
		    	$array['dt_cadastro']  			= date("Y-m-d H:i:s");
		    	$array['CPF_CNPJ']   			= $cadastro->cnpj;
		    	$array['RG_INSC']   			= $cadastro->inscricao;
		    	$array['EMPRESA']   			= $cadastro->fantasia;
		    	$array['RAZAO_SOCIAL']			= $cadastro->razaosocial;
		    	$array['TIPO']					= 'ativo';
		    	$array['data_abertura']			= $cadastro->dataabertura;
		    	$array['ID_REGIOES']			= NULL;
		    	$array['id_clientesgrupos']		= NULL;
		    	$array['id_regioestelevendas']	= NULL;
		    	$array['tptransp']	= 1;
		    	
		    	$idcli = $bocli->insert($array);
		    	
	    		//--Enderecos --------------------------
	    		$arrayEnd['ID_CLIENTE']			= $idcli;
	    		$arrayEnd['LOGRADOURO']			= $cadastro->logradouro;
	    		$arrayEnd['BAIRRO']				= $cadastro->bairro;
	    		$arrayEnd['ESTADO']				= $cadastro->id_estados;
	    		$arrayEnd['PAIS']				= 1;
	    		$arrayEnd['CEP']				= $cadastro->cep;
	    		$arrayEnd['TIPO']				= 1;
	    		$arrayEnd['numero']				= $cadastro->numero;
	    		$arrayEnd['complemento']		= $cadastro->complemento;
	    		$arrayEnd['id_cidades']			= $cadastro->id_cidades;
	    		$boend->insert($arrayEnd);
		    	
	    		//--Telefones--------------------------
	    		$arrayTel['ID_CLIENTE'] 		= $idcli;
	    		$arrayTel['NUMERO']				= substr($cadastro->tel1,5);
	    		$arrayTel['DDD']				= substr($cadastro->tel1,1,2);
	    		$arrayTel['DDI']				= '+55';
	    		$arrayTel['NEXTEL']				= $cadastro->nextel;
	    		$arrayTel['TIPO']				= "telefone1";
	    		$botel->insert($arrayTel);
	    		
	    		//--Telefones--------------------------
	    		$arrayTel['ID_CLIENTE'] 		= $idcli;
	    		$arrayTel['NUMERO']				= substr($cadastro->tel2,5);
	    		$arrayTel['DDD']				= substr($cadastro->tel2,1,2);
	    		$arrayTel['DDI']				= '+55';
	    		$arrayTel['TIPO']				= "telefone2";
	    		$botel->insert($arrayTel);
		    	
	    		//-Emails-------------------------------
	    	
	    		$arrayMail['ID_CLIENTE'] 		= $idcli;
	    		$arrayMail['NOME_CONTATO']		= $cadastro->nomecontato;
	    		$arrayMail['EMAIL']				= $cadastro->email;
	    		$arrayMail['TIPO']				= 1;
	    		$bomail->insert($arrayMail);
	    		
	    		$arrayMail['ID_CLIENTE'] 		= $idcli;
	    		$arrayMail['NOME_CONTATO']		= $cadastro->nomecontatocompras;
	    		$arrayMail['EMAIL']				= $cadastro->emailcompras;
	    		$arrayMail['TIPO']				= 4;
	    		$bomail->insert($arrayMail);
	    		
	    		$arrayMail['ID_CLIENTE'] 		= $idcli;
	    		$arrayMail['NOME_CONTATO']		= $cadastro->nomecontatonfe;
	    		$arrayMail['EMAIL']				= $cadastro->emailnfe;
	    		$arrayMail['TIPO']				= 3;
	    		$bomail->insert($arrayMail);
	    		
	    		//--- atualiza precadastro ------------
	    		$bo->update(array('sit' => 2), "md5(id) = '".$params['cadastro']."'");
	    		
	    		return $idcli;
		    		
		    }catch (Zend_Exception $e){
		    	$boerro	= new ErrosModel();
		    	$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "ClientesBO::inserePrecadastro(".$params['cadastro'].")");
		    	$boerro->insert($dataerro);
				return false;
		    }
		}
		
		function listaArquivosprecadastro($params){
			$bou	= new ClientesModel();
			$boua	= new ClientesprecadastroanexosModel();
			return $boua->fetchAll("md5(id_precadastroparceiro) = '".$params['cadastro']."'");
		}
		
		function buscaClientebyid($id){
		    $bo = new ClientesModel();
		    return $bo->fetchRow("ID = 	'".$id."'");
		}
		 
	}
?>
