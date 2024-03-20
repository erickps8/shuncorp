<?php
	class MailingBO{

		public function promocoesCadastrados($param){
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);	
			
			$select = $db->select();
			
			$select->from(array('m'=>'mailing_enviados','*'),
			        array('m.menssagem'))
			        ->where("m.id = ".$param[id]);		       
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		public function removeMailinglista(){
			$obj = new MailingModel();
			$ob = new  MailingenviadosModel();
			$ob->delete("id > 0");			
		}
		
		public function removeMailinglancamentos($id){
			$obj = new MailingModel();
			$ob = new  MailingenviadosModel();
			$ob->delete("id = ".$id);			
		}
		
		public function listarMailingenviados(){
			$ob = new MailingModel();
			return $ob->fetchAll("id > 0", "data_envio desc");			
		}
		
		public function listarEmailcadastrados(){
			$ob = new MailingModel();
			$obj = new MailingemailsModel();
			return $obj->fetchAll("ATIVO = 'S'");			
		}
		
		
		public function lancamentosCadastrados(){
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);	
			
			$select = $db->select();
			
			$select->from(array('m'=>'mailing_lancamentos','*'),
			        array('p.CODIGO','p.DESCRICAO','p.ID','p.M_INNER','p.M_OUTER','p.M_HIGH','p.PESO','m.id as idlanc'))
			        ->join(array('p'=>'produtos'),'p.ID = m.id_prod')
			        ->order('p.codigo_mask','asc');		       
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
			
		
		public function enviaMailing($params){
			date_default_timezone_set('America/Sao_Paulo');
			$usuario = Zend_Auth::getInstance()->getIdentity();
			
			$bo 	= new MailingModel();
			
			$total = 0;		
			$array['data_envio']  			= date("Y-m-d H:i:s");
			$array['assunto']        		= $params['assunto'];
			$array['menssagem']     		= stripslashes($params['mensagem']);
			$array['sit']					= true;
			$array['respenvio']				= "ZTL Brasil";
			$array['emailenvio'] 			= "info@ztlbrasil.com.br";
			
			try{
				if(empty($params['teste'])):
					$idm = $bo->insert($array);
				endif;
			
			}catch (Zend_Exception $e){
				return ' - Erro ao gravar o boletim eletrônico<br />';
				break;
			}
			
			try{
			
				//--Teste de marketing--
				if($params['teste']){
					$assunto = $params['assunto'];
				
					$message = "
					<table border='0' cellpadding='0' cellspacing='0' width='100%'>
						<tr>
							<td style='font-family: Verdana,Geneva,sans-serif; font-size: 9px; color: rgb(0, 0, 0);' align='center'>
							<center>Caso n&atilde;o consiga visualizar este e-mail,
							<a href='http://www.ztlbrasil.com.br/admin/mailing/promocoes/id/'>acesse este link.</a>
							</td>
						</tr>
					</table>";
					
					$message .= stripslashes($params['mensagem']);
					
					$message .= "<table border='0' cellpadding='0' cellspacing='0' width='100%'>
						<tr>
							<td style='font-family: Verdana,Geneva,sans-serif; font-size: 12px; color: rgb(0, 0, 0);' align='center'>
							<a href='http://www.ztlbrasil.com.br'> www.ztlbrasil.com.br </a>
							</td>
						</tr>
					</table>";
					
					$message .= "<table width='100%' cellspacing='0' cellpadding='0'>
						<tr>
							<td>
							<img src='http://www.ztlbrasil.com.br/admin/mailing/confleituramailing/id/' width='1px' height='1px'>
							</td>
						</tr>
					</table>";
				
					$resp 	= "Cleiton";
					$email  = "cleiton@ztlbrasil.com.br";
					
					ContatosBO::enviaMail($assunto, $message, $resp, $email);
					
					$resp 	= "Wander";
					$email  = "mkt@ztlbrasil.com.br";
					
					ContatosBO::enviaMail($assunto, $message, $resp,$email);
					
					return 'teste';
				}elseif($params['disparo'] == 0){
				    $cont = MailingBO::gerarEmailmarketing($params, $idm);
				    return $idm;
				} 
				
			}catch (Zend_Exception $e){
			    return 'erro';
			}
			
		}
		
		function gerarEmailmarketing($params, $idmailing, $tipo = ""){
		    $bomail = new MailingModel();
		    $bot	= new MailingtmpModel();
		    
			$bot->delete("id > 0");		

			MailingBO::buscaEmailusuarios($params);
			MailingBO::buscaEmailcontatos($params);
			MailingBO::buscaEmailboletim($params);
			
			return count($bot->fetchAll());
			
		
		}
		
		function dispararBoletimeletronico($params, $idmailing){
		    MailingBO::enviarEmailmarketing($params, $idmailing);
		}
		
		
		function buscaEmailboletim($params){
		    
		    //---- mailing --------------------------------------------------------------------
		    try{
		        $bomail = new MailingModel();
		        $bot	= new MailingtmpModel();
		        
			    if($params['boletim']){
			    	
				    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
				    $db->setFetchMode(Zend_Db::FETCH_OBJ);
				    $select = $db->select();
				    $select->from(array('m'=>'mailing','*'), array('*'))->where('m.ATIVO = "S"')->group('m.EMAIL');
				    $stmt = $db->query($select);
				    	
				    foreach ($stmt->fetchAll() as $emailcadastrados){
					    	
					    $email  = trim($emailcadastrados->EMAIL);
					    
					    if(filter_var($email, FILTER_VALIDATE_EMAIL)){
						    $data = array('email' => $email, 'nome' => 'Caro Cliente');
						    $bot->insert($data);
					    }else{
					    	MailingBO::gravaEmailerrados($email);
					    }
				    }
		    	}
		    	
		    }catch (Zend_Exception $e){
		    	echo '- Erro ao enviar boletim para os newslatter<br />';
		    } 
		    
		}
		
		function buscaEmailusuarios($params){
			//-- lista usuarios ---------------------------------------------------
			 
			try{
			    $bomail = new MailingModel();
			    $bot	= new MailingtmpModel();
			    
				foreach (PerfilBO::listarPerfil() as $perfil){
					if($params['func_'.$perfil->id]){
		
						$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
						$db->setFetchMode(Zend_Db::FETCH_OBJ);
						$select = $db->select();
						$select->from(array('u'=>'tb_usuarios','*'), array('*'))
						->join(array('c'=>'clientes'),'c.ID = u.id_cliente')
							->where('u.sit = 1 and u.id_perfil = '.$perfil->id.' and ((c.id_perfil in (2,3)) || (u.id_perfil not in (32,33,34)))')
							->group('u.email');
		
						$stmt = $db->query($select);
		
		
						foreach ($stmt->fetchAll() as $usuarios){
				    
							$resp 	= $usuarios->nome;
							$email  = trim($usuarios->email);
			
							if(filter_var($email, FILTER_VALIDATE_EMAIL)){
								$data = array('email' => $email, 'nome' => $resp);
								$bot->insert($data);
							}else{
								MailingBO::gravaEmailerrados($email);
							}
						} 
					}
				}
		
			}catch (Zend_Exception $e){
				echo '- Erro ao enviar boletim para os funcionários<br />';
			}
		}
		
		function buscaEmailcontatos($params){
		    //-- empresas com grupo de interesse --------------------------------------------------------------
		    $bomail = new MailingModel();
		    $bot	= new MailingtmpModel();
		    
		    $boc	= new ContatosModel();
		    $boe	= new ContatosempModel();
		    
		    foreach (ClientesBO::buscaClientesgrupos() as $gruposcli){
		    	if($params['cont_'.$gruposcli->id]){
		    	    //-- lista empresas -----------------------------------
				    foreach ($boe->fetchAll("id_clientesgrupos = ".$gruposcli->id." and status = 1") as $empresamatriz){
				    	//-- lista perfil contatos com perfil ---------------------------------
					    try{
					    	foreach (ContatosBO::listaGruposinteresse() as $perfilcontatos){
					    
					    	    if($params['agent_'.$perfilcontatos->id]){
					    			//-- lista contatos ------------------------------
					    			$dbcmat = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
					    			$dbcmat->setFetchMode(Zend_Db::FETCH_OBJ);
					    			$selectcmat = $dbcmat->select();
					    			$selectcmat->from(array('c'=>'tb_contatos','*'), array('*'))
						    			->where("c.id_emp =  ".$empresamatriz->id." and c.sit = true and c.id_ginteresse = ".$perfilcontatos->id)
						    			->group('c.email');
					    
					    			$stmtcmat = $dbcmat->query($selectcmat);

					    			foreach ($stmtcmat->fetchAll() as $contatosmatriz){
					    
					    				$resp 	= $contatosmatriz->nome;
					    				$email  = trim($contatosmatriz->email);
					    
					    				if(filter_var($email, FILTER_VALIDATE_EMAIL)){
					    					$data = array('email' => $email, 'nome' => $resp);
					    					$bot->insert($data);
					    				}else{
					    					MailingBO::gravaEmailerrados($email);
					    				}
					    
					    			} 
					    		}
					    	}
					    
					    }catch (Zend_Exception $e){
					    	echo  '- Erro ao enviar boletim para os contatos das empresas com perfil<br />';
					    }
					    
					    //--- contatos sem perfil ---------------------------------------
					    try{
					    	if($params['agent_semgrupo']){
					    		//-- lista contatos ------------------------------
					    		$dbcmat = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
					    		$dbcmat->setFetchMode(Zend_Db::FETCH_OBJ);
					    		$selectcmat = $dbcmat->select();
					    		$selectcmat->from(array('c'=>'tb_contatos','*'), array('*'))
					    		->where("id_emp =  ".$empresamatriz->id." and sit = true and (id_ginteresse is null || id_ginteresse = 0 || id_ginteresse not in (16,15,14))")
					    		->group('c.email');
					    			
					    		$stmtcmat = $dbcmat->query($selectcmat);
					    			
					    		foreach ($stmtcmat->fetchAll() as $contatosmatriz){
					    
					    			$resp 	= $contatosmatriz->nome;
					    			$email  = trim($contatosmatriz->email);
					    
					    			if(filter_var($email, FILTER_VALIDATE_EMAIL)){
					    				$data = array('email' => $email, 'nome' => $resp);
					    				$bot->insert($data);
					    			}else{
					    				MailingBO::gravaEmailerrados($email);
					    			}
					    				
					    		}
					    	}
					    
					    }catch (Zend_Exception $e){
					    	echo '- Erro ao enviar boletim para os contatos das matrizes sem perfil<br />';
					    }
				    
				    }				    				    
				}
			}
		     
		    // --- empresas sem grupos de interesse ------------------------------------------------------------
		    try{
		    	if($params['cont_semgrupo']){
			    	//-- lista empresas -----------------------------------
			    	foreach ($boe->fetchAll("(id_clientesgrupos = 0 || id_clientesgrupos is NULL) and status = 1") as $empresamatriz){
			    		//-- lista perfil contatos com perfil ---------------------------------
			    		foreach (ContatosBO::listaGruposinteresse() as $perfilcontatos){
			    			if($params['agent_'.$perfilcontatos->id]){
			    				//-- lista contatos da empresa ------------------------------
			    
			    				$dbcmat = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			    				$dbcmat->setFetchMode(Zend_Db::FETCH_OBJ);
			    				$selectcmat = $dbcmat->select();
			    				$selectcmat->from(array('c'=>'tb_contatos','*'), array('*'))
			    				->where("id_emp =  ".$empresamatriz->id." and sit = true and id_ginteresse = ".$perfilcontatos->id)
			    				->group('c.email');
			    					
			    				$stmtcmat = $dbcmat->query($selectcmat);
			    				
			    				foreach ($stmtcmat->fetchAll() as $contatosmatriz){
			    
			    					$resp 	= $contatosmatriz->nome;
			    					$email  = trim($contatosmatriz->email);
			    
			    					if(filter_var($email, FILTER_VALIDATE_EMAIL)){
			    						$data = array('email' => $email, 'nome' => $resp);
			    						$bot->insert($data);
			    					}else{
			    						MailingBO::gravaEmailerrados($email);
			    					}
			    						
			    				};
			    			}
			    		}
			    			
			    		//--- contatos sem perfil ---------------------------------------
			    		if($params['agent_semgrupo']){
			    			//-- lista contatos ------------------------------
			    
			    			$dbcmat = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			    			$dbcmat->setFetchMode(Zend_Db::FETCH_OBJ);
			    			$selectcmat = $dbcmat->select();
			    			$selectcmat->from(array('c'=>'tb_contatos','*'), array('*'))
			    			->where("id_emp =  ".$empresamatriz->id." and sit = true and (id_ginteresse is NULL || id_ginteresse = 0 || id_ginteresse not in (16,15,14))")
			    			->group('c.email');
			    
			    			$stmtcmat = $dbcmat->query($selectcmat);
			    			
			    			foreach ($stmtcmat->fetchAll() as $contatosmatriz){
			    					
			    				$resp 	= $contatosmatriz->nome;
			    				$email  = trim($contatosmatriz->email);
			    					
			    				if(filter_var($email, FILTER_VALIDATE_EMAIL)){
			    					$data = array('email' => $email, 'nome' => $resp);
			    					$bot->insert($data);
			    				}else{
			    					MailingBO::gravaEmailerrados($email);
			    				}			    
			    			}
			    		}	
			    	}
			    	
		    	}
		    		
		    }catch (Zend_Exception $e){
		    	echo '- Erro ao enviar boletim para os contatos sem grupo de interesse<br />';
		    } 
			
		}
		
		function enviarEmailmarketing($idmailing, $tipo = ""){
		    date_default_timezone_set('America/Sao_Paulo');
		    ignore_user_abort(1);
		    set_time_limit(0);
		    
		    $bomail = new MailingModel();
		    $bot	= new MailingtmpModel();
		    
		    foreach ($bomail->fetchAll("id = ".$idmailing) as $marketing);
		    
		    $message = "<table border='0' cellpadding='0' cellspacing='0' width='100%'><tr><td style='font-family: Verdana,Geneva,sans-serif; font-size: 9px; color: rgb(0, 0, 0);' align='center'><center>Caso n&atilde;o consiga visualizar este e-mail, <a href='http://ztlbrasil.com.br/admin/mailing/promocoes/id/".$marketing->id."'>acesse este link.</a></td></tr></table>".$marketing->menssagem."<table border='0' cellpadding='0' cellspacing='0' width='100%'><tr><td style='font-family: Verdana,Geneva,sans-serif; font-size: 12px; color: rgb(0, 0, 0);' align='center'><a href='http://www.ztlbrasil.com.br'> www.ztlbrasil.com.br </a></td></tr></table> <table width='100%' cellspacing='0' cellpadding='0'><tr><td><img width='1px' height='1px' src='http://ztlbrasil.com.br/admin/mailing/confleituramailing/id/".$marketing->id."' ></td></tr></table>";
		    $assunto = $marketing->assunto;
		    
		    $total_sucesso = $total_erro = 0;
		    
		    try{		

		        $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		        $db->setFetchMode(Zend_Db::FETCH_OBJ);
		        $select = $db->select();
		        $select->from(array('c'=>'tb_emailtmp'), array('*'))->group('c.email');
		        	
		        $stmt = $db->query($select);
		        
			    foreach ($stmt->fetchAll() as $email){
			        if(ContatosBO::enviaMail($assunto, $message, $email->nome, $email->email, $marketing->respenvio, $marketing->emailenvio)==1):
			        	$total_sucesso++;
			        else:
			        	$total_erro++;
			        	MailingBO::gravaEmailerrados($email);
			        endif;

			        $total_sucesso++;			        
			    }
			    
			    $bot->delete();
			
		    }catch (Zend_Exception $e){
		    	$texto = '- Erro ao enviar boletim<br />'.$e->getMessage();
		    }
		    
		    $bomail->update(array('qt_envio' => ($marketing->qt_envio+$total_sucesso)), "id = ".$idmailing);
		    
		    echo "<br />".$total_sucesso." e-mails envidos com sucesso.";
		    echo "<br />".$total_erro." erros ao tentar enviar e-mails.";
		    
		}
		
		function gravaLeituramailing($var){
			if(!empty($var['id'])):
				$bo = new MailingModel();
				foreach ($bo->fetchAll("id = ".$var['id']) as $lista);
				$qt = $lista->leitura;
				$qt++;
				
				$array2['leitura']		= $qt;
				$bo->update($array2, "id = ".$var['id']);
			endif;
			
		}
		
		function descadastraEmail($var){
			$bocn	= new ContatosModel();
			$bom	= new MailingModel();
			$bonw	= new MailingemailsModel();
			$bodes	= new MailingemailsdescadastraModel();
			
			$array['email']				=	$var['email'];
			$array['dt_descadastro']	=	date("Y-m-d H:i:s");
			
			if($var['motivo']==1):
				$array['naoautoriza']		=	1;
			elseif($var['motivo']==2):
				$array['frequencia']		=	1;
			elseif($var['motivo']==3):
				$array['naotertempo']		=	1;
			elseif($var['motivo']==4):
				$array['naointeressa']		=	1;
			elseif($var['motivo']==5):
				$array['outros']			=	$var['outros'];				
			endif;		
			
			$bodes->insert($array);
			
			
			//--remove email contatos novos-------------------
			$arrayremcn['mailing']	=	false;
			$bocn->update($arrayremcn, "EMAIL = '".$var['email']."'");	
			
			//--remove email newslatter-------------------
			$arrayremnews['ATIVO']	=	"N";
			$bonw->update($arrayremnews, "EMAIL = '".$var['email']."'");
			
		}
		
		function listaEmailsdescadastrados(){
			$bom	= new MailingModel();
			$bodes	= new MailingemailsdescadastraModel();
			return $bodes->fetchAll("id>0","id desc");
		}
		
		function listamotivosEmailsdescadastrados(){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);	
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_mailingdescadastra','*'),
			        array('count(naoautoriza) as autoriza', 'count(frequencia) as frequencia', 'count(naotertempo) as semtempo', 
			        'count(naointeressa) as seminteresse', 'count(outros) as outros')) ;		       
			  
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
			
		function gravaEmailerrados($email){
			$bom	= new MailingModel();
			$boc	= new MailingcorrigeModel();
			
			$data = array(
				'email' 	=> $email
			);
			
			$boc->insert($data);
		}
		
		function listaEmailerrados(){
			$bom	= new MailingModel();
			$boc	= new MailingcorrigeModel();
			return $boc->fetchAll();
		}
		
		function removeEmailerrados($params){
			$bom	= new MailingModel();
			$bomc	= new MailingcorrigeModel();
			$bome	= new MailingemailsModel();			
			$bocn 	= new ContatosModel();
			$bocl	= new ClientesModel();
			$bocle	= new ClientesEmailModel();
			
			foreach ($bomc->fetchAll("md5(id) = '".$params['idmail']."'") as $email);
			
			echo $email->email;
			
			//--- removo email dos contatos -----------------------
			$datacv = array(
				'mailing' => false,
				'EMAIL'	=> ""
			);
			
			$bocn->update($datacv, 'EMAIL = "'.$email->email.'"');
			
			//--- removo email dos newslatter -----------------------
			$datame = array(
				'ATIVO' => "N"
			);
			$bome->update($datame, 'EMAIL = "'.$email->email.'"');
			
			//--- removo email dos clientes -----------------------
			$datacle = array(
				'EMAIL' => ""
			);
			$bocle->update($datacle, 'EMAIL = "'.$email->email.'"');
			
			//--- removo email da lista de emails incorretos -----------------------
			$bomc->delete("md5(id) = '".$params['idmail']."'");
			
		}
		
		function alteraEmailerrados($params){
			$bom	= new MailingModel();
			$bomc	= new MailingcorrigeModel();
			$bome	= new MailingemailsModel();
			$bocn 	= new ContatosModel();
			$bocl	= new ClientesModel();
			$bocle	= new ClientesEmailModel();
			
			foreach ($bomc->fetchAll("id = '".$params['mailid']."'") as $email);
			
			echo $idmail		= $params['mailid'];
			echo $email->email;
			
						
			$datacv = array(
				'EMAIL'	=> $params['email_'.$idmail]
			);		
			
			$bocn->update($datacv, 'EMAIL = "'.$email->email.'"');
		
			$datame = array(
				'EMAIL'	=> $params['email_'.$idmail]
			);
			$bome->update($datame, 'EMAIL = "'.$email->email.'"');
		
			$datacle = array(
				'EMAIL'	=> $params['email_'.$idmail]
			);
			$bocle->update($datacle, 'EMAIL = "'.$email->email.'"');
			
			//--- removo email da lista de emails incorretos -----------------------
			$bomc->delete("id = ".$idmail);
		
		}
		
		function excluiEmailerrados($params){
			$bom	= new MailingModel();
			$bomc	= new MailingcorrigeModel();
						
			//--- removo email da lista de emails incorretos -----------------------
			$bomc->delete("md5(id) = '".$params['idmail']."'");
		
		}
		
		function buscarmailsIncorretos(){
			$bom	= new MailingModel();
			$bome	= new MailingemailsModel();
			$bocn 	= new ContatosModel();
			$bocl	= new ClientesModel();
			$bocle	= new ClientesEmailModel();
			
			
			foreach ($bocn->fetchAll() as $emails):
				if(!filter_var($emails->EMAIL, FILTER_VALIDATE_EMAIL)){
					MailingBO::gravaEmailerrados($emails->EMAIL);
				}
			endforeach;
			
			foreach ($bome->fetchAll() as $emails):
				if(!filter_var($emails->EMAIL, FILTER_VALIDATE_EMAIL)){
					MailingBO::gravaEmailerrados($emails->EMAIL);
				}
			endforeach;
			
			foreach ($bocle->fetchAll() as $emails):
				if(!filter_var($emails->EMAIL, FILTER_VALIDATE_EMAIL)){
					MailingBO::gravaEmailerrados($emails->EMAIL);
				}
			endforeach;
					
		}
		
		
		
	}
?>
