<?php
class ContatoBO{		
	function enviarMensagem($var){

	    if(!empty($var[nome]) and !empty($var[email]) and !empty($var[mensagem])){
			$message = '<table style="border: 1px solid;" width="520px" align="center">
			<tr>
				<td align="center" colspan="2">
					<b>Contato do site ZTLBrasil.com.br</b>			
				</td>
			</tr>
			<tr>
				<td align="left">
					Empresa:<br />
					<b>'.$var[empresa].'</b>
				</td>
				<td align="left">
					Nome:<br />
					<b>'.$var[nome].'</b><font>
				</td>
			</tr>
			<tr>
				<td align="left">
					E-mail:<br />
					<b>'.$var[email].'</b>
				</td>
				<td align="left">
					Fone:<br />
					<b>'.$var[fone].'</b>
				</td>
			</tr>
			<tr>
				<td align="left" colspan="2">
					Mensagem:<br />
					<b>'.$var[mensagem].'</b>
				</td>
			</tr>
			
			</table>';
	    	
			
			$smtp = "smtp.ztlbrasil.com.br";
			$conta = "info@ztlbrasil.com.br";
			$senha = "010203";
			$de = "info@ztlbrasil.com.br";
			$assunto = "Contato do site ZTLBrasil.com.br";
				
			$resp 	=   "Cleiton";
			$email  = "cleiton@ztlbrasil.com.br";
			try {
				$config = array ('ssl' => 'tls', 'auth' => 'login', 'username' => $conta, 'password' => $senha, 'port' => '25');
			
				$mailTransport = new Zend_Mail_Transport_Smtp($smtp, $config);
				
				$mail = new Zend_Mail('utf-8');
				$mail->setFrom($de);
				$mail->addTo($email,$resp);
				$mail->setBodyHtml($message);
				$mail->setSubject($assunto);
				$mail->send($mailTransport);
			
				echo "Email enviado com SUCESSSO: ".$email."<br>";
			} catch (Exception $e){
				echo ($e->getMessage());
				echo "<br>";
			}
			
			$resp 	=   "Bruno";
			$email  = "blirio@ztlbrasil.com.br";
			try {
				$config = array ('ssl' => 'tls', 'auth' => 'login', 'username' => $conta, 'password' => $senha, 'port' => '25');
			
				$mailTransport = new Zend_Mail_Transport_Smtp($smtp, $config);
				
				$mail = new Zend_Mail('utf-8');
				$mail->setFrom($de);
				$mail->addTo($email,$resp);
				$mail->setBodyHtml($message);
				$mail->setSubject($assunto);
				$mail->send($mailTransport);
			
				echo "Email enviado com SUCESSSO: ".$email."<br>";
			} catch (Exception $e){
				echo ($e->getMessage());
				echo "<br>";
			}
			
			$resp 	=   "Helio";
			$email  = "helio@ztlbrasil.com.br";
			try {
				$config = array ('ssl' => 'tls', 'auth' => 'login', 'username' => $conta, 'password' => $senha, 'port' => '25');
			
				$mailTransport = new Zend_Mail_Transport_Smtp($smtp, $config);
				
				$mail = new Zend_Mail('utf-8');
				$mail->setFrom($de);
				$mail->addTo($email,$resp);
				$mail->setBodyHtml($message);
				$mail->setSubject($assunto);
				$mail->send($mailTransport);
			
				echo "Email enviado com SUCESSSO: ".$email."<br>";
			} catch (Exception $e){
				echo ($e->getMessage());
				echo "<br>";
			}
		
	    }
	}
	
	function cadNewslatter($var){
		$bo	= new NewslatterModel();
		if(count($bo->fetchAll("email = '".$var['email']."'"))>0):
			foreach ($bo->fetchAll("email = '".$var['email']."'") as $email);
			if($email->ATIVO=='N'):
				$array['ATIVO']	= 'S';
				$bo->update($array, "email = '".$var['email']."'");
				return "E-mail atualizado com sucesso!";
			else:
				return "E-mail já cadastrado em nossa base de dados!";
			endif;
		else:
			$array['ATIVO']	= 'S';
			$array['email']	= $var['email'];
			$bo->insert($array);
			return "E-mail cadastrado com sucesso!";
		endif;
	}
	
	function recuperarSenha($var){
		$bo		= new ClientesdefultModel();
		$bor	= new ClientesrecuperarsenhaModel();
		$obj 	= new UsuarioModel();
		
		foreach ($obj->fetchAll('email = "'.$var['cpf_cnpj'].'"') as $emailuser);
		
		$array['id_cliente']	= $emailuser->id;
		$array['data']			= date("Y-m-d");
		$idr = $bor->insert($array);

		$linkmd5 = md5($idr.$emailuser->id);		
		
		$message = '<table width="550" align="center" border="0" cellpadding="0" cellspacing="0">
						<tr>
							<td width="100%">
								<a href="http://www.ztlbrasil.com.br" target="_blank"><font size="6" color="#1b999a" face="Arial, Helvetica, sans-serif">ztlbrasil.com.br</font></a>
							</td>
						</tr>
						<tr>
								<td>&nbsp;</td>
						</tr>
						<tr>
								<td valign="top"><font size="2" color="#333333" face="Arial, Helvetica, sans-serif">
									Olá, <strong>'.$emailuser->nome.',</strong></font> 
								</td>				
						</tr>
						<tr>
								<td valign="top">&nbsp;</td>
						</tr>
						<tr>
								<td valign="top"><font size="2" color="#333333" face="Arial, Helvetica, sans-serif">Este e-mail é enviado para que você possa recuperar sua senha.</font> </td>
						</tr>
						<tr>
								<td valign="top">&nbsp;</td>
						</tr>
						<tr>
								<td valign="top" width="100%"><font size="2" color="#333333" face="Arial, Helvetica, sans-serif"> Para criar uma nova senha, clique no link abaixo: </font> <br> <br>
								<a href="http://www.ztlbrasil.com.br/index/trocarsenha/autorizacao/'.$linkmd5.'" title="www.ztlbrasil.com.br" target="_blank"> <font size="3" color="#1b999a" face="Arial, Helvetica, sans-serif">
								<strong>http://www.ztlbrasil.com.br/index/trocarsenha/autorizacao/'.$linkmd5.'</strong> </font> </a> </td>
				
						</tr>
						<tr>
								<td valign="top">&nbsp;</td>
						</tr>
						<tr>
								<td valign="top"><font size="2" color="#333333" face="Arial, Helvetica, sans-serif"> Esta solicitação expirará em 48 horas. </font> </td>
						</tr>
						<tr>
								<td valign="top">&nbsp;</td>
						</tr>
						<tr>
								<td valign="top"><font size="2" color="#333333" face="Arial, Helvetica, sans-serif"> Se por acaso você não solicitou uma nova senha, por favor desconsidere este e-mail. </font> </td>
						</tr>	
						<tr>
								<td valign="top">&nbsp;</td>
						</tr>			
						<tr>
								<td valign="top"><font size="2" color="#333333" face="Arial, Helvetica, sans-serif">
								Em caso de dúvidas, entre em contato com nosso Serviço de Atendimento ao Cliente, enviando e-mail para ztl@ztlbrasil.com.br
								</font> </td>
						</tr>
						<tr>
								<td valign="top">&nbsp;</td>
						</tr>
						<tr>
								<td valign="top"><font size="2" color="#333333" face="Arial, Helvetica, sans-serif"> 
								Atenciosamente,<br />
								Serviço de Atendimento ao Cliente ZTL Brasil<br />
								</font> </td>
						</tr>		
					</table>';
		
		$assunto = "Recuperar senha ZTLBrasil.com.br";
		$remetente = "ZTL Brasil";
		$emailremetente = "info@ztlbrasil.com.br";
		
		$resp 	=  $emailuser->nome;
		$email  =  $emailuser->email;
		
		try {
			$mailTransport = new Zend_Mail_Transport_Smtp("smtp.ztlbrasil.com.br", Zend_Registry::get('mailSmtp'));
			
			$mail = new Zend_Mail('utf-8');
			$mail->setFrom($emailremetente,$remetente);
			$mail->addTo($email,$resp);
			$mail->setBodyHtml($message);
			$mail->setSubject($assunto);
			$mail->send($mailTransport);
			
			
			
		} catch (Exception $e){
			echo ($e->getMessage());
			echo "<br>";
		}
		
		return $email;
	}

	function trocaSenhadados($var){
		$bo		= new ClientesdefultModel();
		$bor	= new ClientesrecuperarsenhaModel();
		
		$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$select = $db->select();
		$select->from(array('c'=>'tb_usuarios','*'), array('c.id as iduser','c.nome','c.email','a.*'))
		        ->join(array('a'=>'tb_recuperarsenha'),'a.id_cliente = c.id')		        
		        ->where("md5(concat(a.id,a.id_cliente)) = '".$var['autorizacao']."'");
		$stmt = $db->query($select);
		
		return $stmt->fetchAll();
		
	}
	
	function trocaSenha($var){
		$bo		= new UsuarioModel();
		$array['senha']	= md5($var['novasenha']);
		
		return $bo->update($array, "md5(concat(id,'novaztl')) = '".$var['usuario']."'");
		
	}
	
	function buscaRepresentantes($var){
		$bo		= new ClientesdefultModel();
		$bor	= new ClientesrecuperarsenhaModel();
		
		$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$select = $db->select();
		
		$select->from(array('c'=>'clientes','*'), array('c.ID as idparc','c.EMPRESA','ce.EMAIL','ce.NOME_CONTATO'))
			->join(array('r'=>'tb_clientesregioes'), 'r.id_clientes = c.ID')
			->joinLeft(array('ce'=>'clientes_emails'),'ce.ID_CLIENTE = c.ID and ce.TIPO = 1 and ce.EMAIL != ""')
			->joinLeft(array('e'=>'clientes_telefone'),'e.ID_CLIENTE = c.ID and e.TIPO = "telefone1"')
	    	->where("c.id_perfil = 3 and r.id_regioes in (".$var.")");
		        
		$stmt = $db->query($select);		
		return $stmt->fetchAll();		
	}
	
	
	
}
?>