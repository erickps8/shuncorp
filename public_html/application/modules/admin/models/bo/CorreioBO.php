<?php
	class CorreioBO{		
	    
	    protected $senha;
	    
	    function __construct() {
	        $usuario = Zend_Auth::getInstance()->getIdentity();
	        $bou	= new UsuarioModel();
	        
	        foreach ($bou->fetchAll("id = ".$usuario->id) as $user);
	        $this->senha = $user->senhamail;
	        	        
	    }
	    
		function listaEmail($params=""){
		    
		    $usuario = Zend_Auth::getInstance()->getIdentity();
		    
		    try{
			    $mail = new Zend_Mail_Storage_Imap(array(
					'host' 		=> 'pop.ztlbrasil.com.br',
					'user'		=> $usuario->email,
					'password' 	=> $this->senha,
					'ssl'		=> 'SSL',
					'port'		=> '993')
				); 
			    
			    if(isset($params['tp'])):
				    if($params['tp'] == 'enviadas'):
				    	$mail->selectFolder("Sent");
				    elseif($params['tp'] == 'rascunhos'):
				    	$mail->selectFolder("Drafts");
				    elseif($params['tp'] == 'lixeira'):
				    	$mail->selectFolder("Trash");
				    else:
				    	$mail->selectFolder("Inbox");
				    endif;
				endif; 
			    
			    foreach ($mail as $message) {
			    	$array[] = $message;
			    }
			    
			    if($array!=NULL): 
			    	usort($array, 'CorreioBO::verificadorOrdem');
			    	return $array;
			    else:
			    	return false;
			    endif;
			    
		    }catch (Zend_Exception $e){
		        $array = array('erro' => $e->getMessage());
		        return $array;
		    }
		    
		}		
		
		function verificadorOrdem($obj1, $obj2) {
		    
		    $dataobj1 = strtotime($obj1->date);
		    $dataobj2 = strtotime($obj2->date);
		    
			if ($dataobj1 < $dataobj2) {
				return +1;
			} elseif ($dataobj1 > $dataobj2) {
				return -1;
			}
			return 0;
		}
		
		function buscarEmail($params=""){
		
			$usuario = Zend_Auth::getInstance()->getIdentity();
		    $mail = new Zend_Mail_Storage_Imap(array(
				'host' 		=> 'pop.ztlbrasil.com.br',
				'user'		=> $usuario->email,
				'password' 	=> $this->senha,
				'ssl'		=> 'SSL',
				'port'		=> '993')
			);
				
			$message = $mail->getMessage($params['email']);
			
			$mail->setFlags($params['email'], array(Zend_Mail_Storage::FLAG_SEEN));
			
			if($message->isMultipart()):
				$foundPart = 0;
				foreach (new RecursiveIteratorIterator($message) as $part) {
				    
				    if ((strtok($part->contentType, '/') == 'image')) {
					    
					    foreach ($part->getHeaders() as $name => $value) {
							if($name == "content-id"){
							 	$fileName = str_replace("<", "", $value);
							    $fileName = str_replace(">", "", $fileName);
							   
							    $attachment = base64_decode($part->getContent());
							    $fh = fopen("/aplic/ztlbrasil.com.br/public/sistema/email/".$fileName, 'w');
							    fwrite($fh, $attachment);
							    fclose($fh);							    
							}
						}
				    
			        	$foundPart++;
				    }
				}
				
			endif;
			
			return $mail->getMessage($params['email']);
		
		}
		
		function contarEmail(){
		    $usuario = Zend_Auth::getInstance()->getIdentity();
		    
		    try{
			    $mail = new Zend_Mail_Storage_Imap(array(
					'host' 		=> 'pop.ztlbrasil.com.br',
					'user'		=> $usuario->email,
					'password' 	=> $this->senha,
					'ssl'		=> 'SSL',
					'port'		=> '993')
				);
			    
			    return $mail->countMessages();
		    }catch (Zend_Exception $e){
		    	$array = array('erro' => $e->getMessage());
		    	return $array;
		    }
		}
		
		
		function baixarAnexo($params=""){
		
			$usuario = Zend_Auth::getInstance()->getIdentity();
		    $mail = new Zend_Mail_Storage_Imap(array(
				'host' 		=> 'pop.ztlbrasil.com.br',
				'user'		=> $usuario->email,
				'password' 	=> $this->senha,
				'ssl'		=> 'SSL',
				'port'		=> '993')
			);

			
			$message = $mail->getMessage($params['email']);
			
			if($message->isMultipart()):
				$part = $message->getPart($params['parte']);
				
				if ((strtok($part->contentType, '/') == 'application') || (strtok($part->contentType, '/') == 'image')) :
			
				    //-- busco nome do arquivo ----------------------
				    $fileName = explode(";", $part->contentType);
				    $fileName = explode('=', $fileName[1]);
				    $fileName = str_replace('"', '', $fileName[1]);
				    	
				    	
				    // Get the attachement and decode
				    $attachment = base64_decode($part->getContent());
				    	
				    // Save the attachment
				    $fh = fopen("/aplic/ztlbrasil.com.br/public/sistema/email/".$fileName, 'w');
				    	
				    fwrite($fh, $attachment);
				    	
				    fclose($fh);
				    
				    
				    // Configuramos os headers que serão enviados para o browser
				    header('Content-Description: File Transfer');
				    header('Content-Disposition: attachment; filename="'.$fileName.'"');
				    header('Content-Type: application/octet-stream');
				    header('Content-Transfer-Encoding: binary');
				    header('Content-Length: ' . filesize("/aplic/ztlbrasil.com.br/public/sistema/email/".$fileName));
				    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				    header('Pragma: public');
				    header('Expires: 0');
				    
				    // Envia o arquivo para o cliente
				    readfile("/aplic/ztlbrasil.com.br/public/sistema/email/".$fileName);
				    
				endif;
			endif;
			
			
		}
		
		function enviaEmail($params){
		    date_default_timezone_set('America/Sao_Paulo');
		    $upload = new Zend_File_Transfer_Adapter_Http();
		    $usuario = Zend_Auth::getInstance()->getIdentity();
		    
		    if(filter_var(trim($usuario->email), FILTER_VALIDATE_EMAIL)):
		    	$emailrem 	= $usuario->email;
		    	$senha		= $this->senha;
		    else:
		    	$emailrem 	= "info@ztlbrasil.com.br";
		    	$senha		= "010203";
		    endif;
		    
			try {			    			    
				$mailTransport = new Zend_Mail_Transport_Smtp("smtp.ztlbrasil.com.br", array (
				    'ssl' => 'tls', 
			        'auth' => 'login', 
			        'username' => $emailrem, 
			        'password' => $senha, 
			        'port' => '25')
		        );

				$mail = new Zend_Mail('utf-8');
				$mail->setFrom($emailrem,$usuario->nome);
				
				if($params['tipoenvio'] == 1):
					foreach (ContatosBO::listarContatosmatriz($params) as $listemp):
						$mail->addTo($listemp->EMAIL,$listemp->contato);
						$dest .= $listemp->contato."; ";
					endforeach;
				else:
					$destinatarios = explode(",", $params['destinatarios']);
					foreach ($destinatarios as $email):
						$mail->addTo($email);				
					endforeach;
				endif;
				
				$mail->setBodyHtml($params['mensagem']);
				$mail->setSubject($params['assunto']);
				
				$files = $upload->getFileInfo();
		        
				if($files):
					foreach ($files as $file => $info):
						$ext = end(explode('.', $info['name']));
				
						if($info['tmp_name']):
					    	$mail->createAttachment(file_get_contents($info['tmp_name']), $ext, Zend_Mime::DISPOSITION_INLINE, Zend_Mime::ENCODING_BASE64, $info['name']);
						endif;
				    endforeach;
				endif; 				
				
				$mail->send($mailTransport);	

				/* Tipos:
				 * 1 - Email
				 * 2 - Outras interacoes
				 * */
				
				if($params['tipoenvio'] == 1):
					$bo 	= new ContatosModel();
					$boi	= new ContatosempinteracaoModel();
					
					$datain = array(
						'texto' 			=> $dest."<br />".$params['mensagem'],
					    'data' 				=> date("Y-m-d H:i:s"),
					    'tipo' 				=> 1,
					    'sit' 				=> true,
					    'id_contatosemp' 	=> $params['emp'],
					    'id_usuarios' 		=> $usuario->id,
					    'titulo'			=> $params['assunto']
					);
					
					$boi->insert($datain);
				endif;
				
				return true;
												
			} catch (Exception $e){
				return false;
				Zend_Debug::dump($e);				
			}
		}
		
		function removeEmail($params){
		    $usuario = Zend_Auth::getInstance()->getIdentity();
		    $mail = new Zend_Mail_Storage_Imap(array(
				'host' 		=> 'pop.ztlbrasil.com.br',
				'user'		=> $usuario->email,
				'password' 	=> $this->senha,
				'ssl'		=> 'SSL',
				'port'		=> '993')
			);
		    		    	
		    $mail->moveMessage($params['email'],'Trash');
		    
		}
	}
?>
