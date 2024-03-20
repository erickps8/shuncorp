<?php
/**
 * Este arquivo é parte do projeto SisZtl.
 * 
 * @package   Admin
 * @name      UsuarioBO
 * @version   1.0
 * @copyright 2012-2013 &copy; ZTL do Brasil importacao, exportacao e comercio LTDA
 * @link      http://www.ztlbrasil.com.br/
 * @author    Cleiton S. Barbosa <cleitonsbarbosa at gmail dot com>
 */

class UsuarioBO{

    function cadastraMenu($params){
        $usuario 	= Zend_Auth::getInstance()->getIdentity();
    }
		
    /**
     * buscaUsuario()
     *
     * @author programador
     * @data 10/06/2014
     * @tags @param unknown_type $params
     * @tags @param var $tipo A
     * @tags @return Ambigous <multitype:, multitype:mixed Ambigous <string, boolean, mixed> >
     */
    
	function buscaUsuario($params="",$tipo=""){
		$obj = new UsuarioModel();
		$usuario 	= Zend_Auth::getInstance()->getIdentity();
		
		$sit = "";
		if(!empty($tipo)){
		    $sit = " and u.sit = true ";		    
		}
		$where = "";
		if(isset($params['usermd']) and $params['usermd']):
			$where = (' and md5(u.id) = "'.$params['usermd'].'"');
		elseif(isset($params['usuario']) and $params['usuario']):
			$where = (' and u.id = '.$params['usuario']);
		elseif(isset($params['nome']) and $params['nome']):
			$where = (' and u.nome like "%'.$params['nome'].'%"');
		elseif(isset($params['email']) and $params['email']):
			$where = (' and u.email = "'.$params['email'].'"');
		elseif(isset($params['cpf']) and $params['senha']):
			$cpf = substr($params['cpf'], 0,3).".".substr($params['cpf'], 3,3).".".substr($params['cpf'], 6,3)."-".substr($params['cpf'], 9,2);
			$where = (' and (u.cpf = "'.$params['cpf'].'" || u.cpf = "'.$cpf.'") and u.senha = "'.md5($params['senha']).'"');
		endif;
		
		if(isset($params['buscageral'])):
			$where .= ' and (u.nome like "%'.$params['buscageral'].'%" || u.email like "%'.$params['buscageral'].'%") || u.ID = "'.ereg_replace("[^0-9]", " ", $params['buscageral']).'"';
		endif;
		
		if($tipo == 'funcionario'):
			//$where = ' and u.id_perfil in (1,4,5,6,7,8,9,10,11,12,13,14,15,16,17,20,31,28,30,29)';
			$where = ' and u.id_cliente in (662,627,395)';
		elseif($tipo == 'vendedores'):
			$where = ' and u.id_perfil in (1,8,3,4,5,10,29,28,31)';
		endif;
		
		if(isset($params['perfil'])) $where  .= ' and u.id_perfil in ('.$params['perfil'].')';
		
		if(isset($params['empresa']) and $params['empresa']):
			$where  .= ' and u.id_cliente = '.$params['empresa'];
		endif;
		
				
		//--- Controle de perfil ------------------------------------------
		foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
		if(($list->nivel!=2)):
			$where .= " and u.id_cliente = ".$usuario->id_cliente;
		endif;
		
		$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$select = $db->select();
			
		$select->from(array('u'=>'tb_usuarios','*'), array('u.*','u.id as iduser','u.nome as nomeusuario','c.EMPRESA', 'p.descricao as perfil','DATE_FORMAT(u.dt_nascimento,"%d/%m/%Y") as dtnasc',
		        'ec.id_estados','ee.id_paises','u.id_perfil as idperfil','u.id_cliente','u.sit as situsuario','DATE_FORMAT(u.dt_admissao,"%d/%m/%Y") as dtadmin','DATE_FORMAT(u.dt_desligamento,"%d/%m/%Y") as dtdesl'))
				->join(array('c'=>'clientes'),'c.ID = u.id_cliente')
				->join(array('p'=>'tb_perfil'),'p.id = u.id_perfil')
				->joinLeft(array('ec'=>'tb_cidades'),'ec.id = u.id_cidades')
				->joinLeft(array('ee'=>'tb_estados'),'ee.id = ec.id_estados')
				->joinLeft(array('ep'=>'tb_paises'),'ep.id = ee.id_paises')
				->where("u.bloq = false ".$sit.$where)
				->order('u.nome asc');
			
		$stmt = $db->query($select);
		return $stmt->fetchAll(); 
		
	}
	
	function buscaEmail($email, $idusuario){
	    $bou	= new UsuarioModel();
	    if(count($bou->fetchRow("email = '".$email."' and id != '".$idusuario."'"))>0){
	        return true;
	    }else return false;
	}	
	
	/** 
    * @version 1.0
    * @package Admin/Cadastro
    * @author  Cleiton S. Barbosa <cleitonsbarbosa at gmail dot com>
    * @param   array $params obrigatorio dados do novo cadastro
    * @return  boolean true sucesso false Erro
    */
	function cadastraUsuario($params){
	    $usuario 	= Zend_Auth::getInstance()->getIdentity();
	    
	    $bou	= new UsuarioModel();
	    $boua	= new UsuarioanexoModel();
	    $bo		= new RegioesModel();
	    $bor	= new RegioesclientesModel();
	    $bot	= new RegioesclientestelevendasModel();
	    $bof	= new UsuarioausenciasModel();
	    
	    $perfil = explode('|', $params['perfil']);
	    
	    //--- Controle de perfil ------------------------------------------
	    foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
	    if(($list->nivel!=2)){
	    	$cliente = $usuario->id_cliente;
	    }else{
	    	$cliente = $params['empresa'];
	    }
	    
	    $dt_nascimento = $dt_admissao = $dt_desligamento = "";
	    if(isset($params["dtnascimento"])) 		$dt_nascimento 		= substr($params["dtnascimento"],6,4).'-'.substr($params["dtnascimento"],3,2).'-'.substr($params["dtnascimento"],0,2);
	    if(isset($params["dtadmissao"])) 		$dt_admissao		= substr($params["dtadmissao"],6,4).'-'.substr($params["dtadmissao"],3,2).'-'.substr($params["dtadmissao"],0,2);
	    if(isset($params["dtdesligamento"]))	$dt_desligamento	= substr($params["dtdesligamento"],6,4).'-'.substr($params["dtdesligamento"],3,2).'-'.substr($params["dtdesligamento"],0,2);
	    
	    if($params['cidade'] == 0) $idcidade = NULL;
	    else $idcidade = $params['cidade'];
	    
	    $data = array(
	  		'cpf'				=> $params['cpf'],
            'rg'				=> $params['rg'],
            'nome'				=> $params['nome'],
            'dt_nascimento'		=> $dt_nascimento,
	        'dt_admissao'		=> $dt_admissao,
	        'dt_desligamento'	=> $dt_desligamento,
            'id_perfil'			=> $perfil[0],
            'telefoneddi'		=> $params['ddi1'],
            'telefoneddd'		=> $params['ddd1'],
            'telefone'			=> $params['fone1'],
            'telefone2ddi'		=> $params['ddi2'],
            'telefone2ddd'		=> $params['ddd2'],
            'telefone2'			=> $params['fone2'],
            'email'				=> $params['email'],
            'nextel'			=> $params['nextel'],
            'logradouro'		=> $params['logradouro'],
            'numero'			=> $params['numero'],
            'complemento'		=> $params['complemento'],
	        'bairro'			=> $params['bairro'],
            'cep'				=> $params['cep'],
            'id_cidades'		=> $idcidade,
            'id_cliente'		=> $cliente,
            'sit'				=> $params['situacao'],
	        'obs'				=> $params['obsusuario']
	                        
	    );
	    
	    if(isset($params['idusuario']) and (!empty($params['idusuario']))): //-- Atualiza usuarios ---------------------------
	    	$bou->update($data, 'id = "'.$params['idusuario'].'"');
        	$idusuario = $params['idusuario'];
        	LogBO::cadastraLog("Cadastro/Usuarios",4,$usuario->id,$params['idusuario'],"Usuario ".$params['idusuario']);
	        
	    else: //-- Cadatra usuarios ------------------------------------------------------
		    
        	$senha					= substr(md5(date("Y-m-d H:i:s")),0,8);
        	$data['senha'] 			= md5($senha);
        	$data['dt_cadastro'] 	= date("Y-m-d H:i:s");
	    	$idusuario 				= $bou->insert($data);
		    	
	    	$message = '<table width="750" align="center" border="0" cellpadding="0" cellspacing="0"><tr><td width="100%"><a href="http://www.ztlbrasil.com.br" target="_blank">
			<font size="6" color="#1b999a" face="Arial, Helvetica, sans-serif">ztlbrasil.com.br</font></a></td></tr><tr><td>&nbsp;</td></tr><tr><td valign="top"><font size="2" color="#333333" face="Arial, Helvetica, sans-serif">
			Ol&aacute; <strong>'.$params['nome'].',</strong></font></td></tr><tr><td valign="top">&nbsp;</td></tr>
			<tr><td valign="top" style="text-align: justify;"><font size="2" color="#333333" face="Arial, Helvetica, sans-serif" >
	    	
			Voc&ecirc; est&aacute; recebendo este e-mail como confirma&ccedil;&atilde;o do seu cadastro para acesso ao sistema ZTL Brasil.
			Logo abaixo Voc&ecirc; recebe seu usu&aacute;rio de login e senha, o qual podem ser alterados acessando o menu do sistema <b>"Cofigura&ccedil;&otilde;es"</b>. <br /><br /><br />
			<b>
			Usu&aacute;rio: '.$params['email'].'<br />
			Senha: '.$senha.' </b>
			<br /><br /><br /></font></td></tr><tr><td valign="top"><font size="2" color="#333333" face="Arial, Helvetica, sans-serif">
			Em caso de d&uacute;vidas, entre em contato com nosso Servi&ccedil;o de Atendimento ao Cliente, enviando e-mail para admin@ztlbrasil.com.br
			</font> </td></tr><tr><td valign="top">&nbsp;</td></tr><tr><td valign="top"><font size="2" color="#333333" face="Arial, Helvetica, sans-serif">
			Atenciosamente,<br />
			<b>Equipe ZTL Brasil</b><br />
			</font> </td></tr></table>';
		    	
		    LogBO::cadastraLog("Cadastro/Usuarios",2,$usuario->id,$idusuario,"Usuario ".$idusuario);
		    	
		    if($params['email']!=""){
			    $email  = trim($params['email']);
			    	
			    if(filter_var($email, FILTER_VALIDATE_EMAIL)){
			    	DiversosBO::enviaMail('Cadastro realizado com sucesso!', $message, $params['nome'], $params['email']);
			    }
		    }
		    
	    endif;
	     
	    if($idusuario!=""){
		    $bor->delete("id_usuarios = ".$idusuario);
		    $bot->delete("id_usuarios = ".$idusuario);
		    
		    if($perfil[0] != 31){
			    //--- Regioes representantes---------------
			    $bor->delete("id_usuarios = ".$idusuario);
			    foreach (RegioesBO::listaRegioesclientes() as $reg){
				    if(!empty($params['reg_'.$reg->idreg])){
					    $arrayreg = array(
					    	'id_usuarios' 	=> $idusuario,
					    	'id_regioes'	=> $reg->idreg
					    );
					    $bor->insert($arrayreg);
					    
				    }
			    }
			}
			
			if(($perfil[0] == 31) || ($perfil[0] == 4) || ($perfil[0] == 5)){
			    //--- Regioes televendas---------------
			    foreach (RegioesBO::buscaRegioestelevendas() as $reg){
				    if(!empty($params['regtel_'.$reg->idreg])){
					    $arrayreg = array(
					    	'id_usuarios' 	=> $idusuario,
					        'id_regioes'	=> $reg->idreg
					    );
					    $bot->insert($arrayreg);
				    }
			    }
			}   
	    } 

	    echo $idusuario;
	}
	
	function uploadArquivos($params){
	    //---Arquivos-------------------------------
	    $bou	= new UsuarioModel();
	    $boua	= new UsuarioanexoModel();
	    
	    $idusuario = $params['idusuario'];
	    
	    $pasta = Zend_Registry::get('pastaPadrao')."public/sistema/upload/usuarios/".$idusuario;
	    DiversosBO::criarDiretorio($pasta);
	     
	    $pasta = Zend_Registry::get('pastaPadrao')."public/sistema/upload/usuarios/".$idusuario."/cadastro";
	    DiversosBO::criarDiretorio($pasta);
	     
	    $upload = new Zend_File_Transfer_Adapter_Http();
	    $upload->setDestination($pasta);
	    $files = $upload->getFileInfo();
	     
	    if($files){
	    	foreach ($files as $file => $info):
	    		
	    	$num = str_replace('anexo', '', $file);
	    
	    	if ($upload->isValid($file)) {
	    		$upload->receive($file);
	    	}
	    	 
	    	if($info['tmp_name']):
	    
	    	$dataanexo = array(
    			'nomearquivo'	=> $params['nomearquivo'.$num],
    			'nome'			=> $info['name'],
    			'id_usuarios'	=> $idusuario
	    	);
	    	 
	    	$boua->insert($dataanexo);
	    	endif;
	    	 
	    	endforeach;
	    }else{
	        throw "Nenhum arquivo para anexar";
	    }	    
	}
	

	function gravaFerias($params){
	    try{
		    $bou	= new UsuarioModel();
		    $bof	= new UsuarioausenciasModel();
		    
		    //--- ferias ------------------------
		    if(!empty($params['datainiferias']) and !empty($params['datafimferias'])){
		    	 
		    	$ferias = array(
		    		'id_usuarios'		=> $params['idusuario'],
		    		'tipo'				=> 1,
		    		'obs'				=> $params['obsferias'],
		    		'dtini'				=> substr($params["datainiferias"],6,4).'-'.substr($params["datainiferias"],3,2).'-'.substr($params["datainiferias"],0,2),
		    		'dtfim'				=> substr($params["datafimferias"],6,4).'-'.substr($params["datafimferias"],3,2).'-'.substr($params["datafimferias"],0,2)
		    	);
		    	 
		    	$bof->insert($ferias);		    	 
		    }
		    
		    return '1';
	    } catch (Zend_Exception $e) {
	    	$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "UsuarioBO:gravaFerias(".$params['idusuario'].")");
	    	$boerr->insert($dataerro);
	    	return '0';
	    }
	}
	
	function gravaFaltas($params){
		try{
			$bou	= new UsuarioModel();
			$bof	= new UsuarioausenciasModel();
	
			//--- faltas ------------------------
		    
		    if(!empty($params['dataini']) and !empty($params['datafim'])){ 
		        
		        $faltas = array(
		        	'id_usuarios'		=> $params['idusuario'],
			    	'tipo'				=> 0,
			    	'justificado'		=> $params['justificado'],
			    	'obs'				=> $params['obsjust'],
			    	'dtini'				=> substr($params["dataini"],6,4).'-'.substr($params["dataini"],3,2).'-'.substr($params["dataini"],0,2)." ".$params['horaini'],
			    	'dtfim'				=> substr($params["datafim"],6,4).'-'.substr($params["datafim"],3,2).'-'.substr($params["datafim"],0,2)." ".$params['horafim'],
		            'tpfreq'			=> $params['tpfreq'],
			    );
		        
			    $bof->insert($faltas);
			    		    
		    }
	
			return '1';
		} catch (Zend_Exception $e) {
		    $boerr	= new ErrosModel();
			$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "UsuarioBO:gravaFerias(".$params['idusuario'].")");
			$boerr->insert($dataerro);
			return '0';
		}
	}
	
	function cadastraDadospessoais($params){
		 
		$bou	= new UsuarioModel();
		$boua	= new UsuarioanexoModel();
		$bo		= new RegioesModel();
		$bor	= new RegioesclientesModel();
		$bot	= new RegioesclientestelevendasModel();
		$boerr	= new ErrosModel();
		
		$data = array(
			'cpf'			=> $params['cpf'],
			'rg'			=> $params['rg'],
			'nome'			=> $params['nome'],
			'dt_nascimento'	=> substr($params["dtnascimento"],6,4).'-'.substr($params["dtnascimento"],3,2).'-'.substr($params["dtnascimento"],0,2),
			'telefoneddi'	=> $params['ddi1'],
			'telefoneddd'	=> $params['ddd1'],
			'telefone'		=> $params['fone1'],
			'telefone2ddi'	=> $params['ddi2'],
			'telefone2ddd'	=> $params['ddd2'],
			'telefone2'		=> $params['fone2'],
			'email'			=> $params['email'],
			'skype'			=> $params['skype'],
			'msn'			=> $params['msn'],
			'nextel'		=> $params['nextel'],
			'logradouro'	=> $params['logradouro'],
			'numero'		=> $params['numero'],
			'complemento'	=> $params['complemento'],
			'cep'			=> $params['cep'],
			'id_cidades'	=> $params['cidade'],
			'sit'			=> true
		);
		 
		try {
			return $bou->update($data, 'id = "'.$params['idusuario'].'"');			
		} catch (Zend_Exception $e) {
			$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "UsuarioBO:cadastraDadospessoais(".$params['idusuario'].")");
			$boerr->insert($dataerro);
			return false;
		}
	}
	
	function listaRegioestelevendas($params){
		$bo		= new RegioesModel();
		$bor	= new RegioesclientestelevendasModel();
		return $bor->fetchAll("md5(id_usuarios) = '".$params['usermd']."'");
	}
	
	function listaArquivosuser($params){
		$bou	= new UsuarioModel();
	    $boua	= new UsuarioanexoModel();
		return $boua->fetchAll("md5(id_usuarios) = '".$params['usermd']."'");
	}
		
	function removeAnexo($params){
	    $bou	= new UsuarioModel();
	    $boua	= new UsuarioanexoModel();
	    foreach ($boua->fetchAll("md5(id) = '".$params['anexo']."'") as $user);
	    $boua->delete("md5(id) = '".$params['anexo']."'");
	    return $user->id_usuarios;
	}	
	
	function listaAusencias($params){
	    $bou	= new UsuarioModel();
	    $bof	= new UsuarioausenciasModel();
	    $boerr	= new ErrosModel();
	    
	    try{
	        
	        $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
	        $db->setFetchMode(Zend_Db::FETCH_OBJ);
	        $select = $db->select();
	        	
	        $select->from(array('u'=>'tb_usuariosausencia','*'), array('u.*','TIMEDIFF(dtfim,dtini) as horas','DATEDIFF(dtfim,dtini) as dias', 'timestampdiff(minute,dtini,dtfim) as minutos' ))
	        		->where("sit = true and md5(id_usuarios) = '".$params['usermd']."' and tipo = '".$params['tp']."'")
	        		->order('u.id asc');
	        	
	        $stmt = $db->query($select);
	        return $stmt->fetchAll();
	        
	    }catch (Zend_Exception $e){
	        $dataerro = array('descricao' => $e->getMessage(), 'pagina' => "listaAusencias(".$params['usermd'].")");
	        $boerr->insert($dataerro);
	        return false;
	    }
	}
	
	function removeAusencias($params){
	    $bou	= new UsuarioModel();
	    $bof	= new UsuarioausenciasModel();
	    $boerr	= new ErrosModel();
	    
	    try{
	    	$data = array('sit' => false);
	       	$bof->update($data, "md5(id) = '".$params['id']."'");
	        
	    }catch (Zend_Exception $e){
	        $dataerro = array('descricao' => $e->getMessage(), 'pagina' => "removeAusencias(".$params['id'].")");
	        $boerr->insert($dataerro);
	        return false;
	    }
	}
	
	function trocarSenha($params){
	    $bou	= new UsuarioModel();
	    $boerr	= new ErrosModel();
	    $usuario = Zend_Auth::getInstance()->getIdentity();
	    
	    
	    foreach ($bou->fetchAll('id = '.$usuario->id) as $user);
	    
	    if(md5($params['senhaatual']) == $user->senha):
		  	if($params['novasenha1'] != ''):	    
		    	if($params['novasenha1'] == $params['novasenha2']):
		    		$data = array('senha' => md5($params['novasenha1']));
			    	try {
			    		$bou->update($data, 'id = '.$usuario->id);
			    		return 'sucesso';
			    	} catch (Zend_Exception $e) {
			    		$dataerro = array('descr' => $e->getMessage(), 'erro' => "Usuario: ".$usuario->id);
			    		$boerr->insert($dataerro);
			    		return 'erro:4';
			    		break;
			    	}
			    else:
			    	return 'erro:3';
			    endif;
	    	else:
	    		return 'erro:2';
	    	endif;
	    else:
	    	return 'erro:1';
	    endif;
	    
	}
	
	function atualizaCredenciaisemail($params){
	    $usuario = Zend_Auth::getInstance()->getIdentity();
		$bou	= new UsuarioModel();
		$boerr	= new ErrosModel();
		
		$data = array(
			'email'			=> $params['email'],
			'senhamail'		=> $params['senhamail']			
		);
			
		try {
			return $bou->update($data, 'id = '.$usuario->id);
		} catch (Zend_Exception $e) {
			$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "UsuarioBO::atualizaCredenciaisemail(".$usuario->id.")");
			$boerr->insert($dataerro);
			return false;
			break;
		}
	}
	
	function atualizaSessao($iduser, $logado){
	    $bou	= new UsuarioModel();
	    
	    $data = array('logado' => $logado);
	    	
	    try {
	    	$bou->update($data, 'id = "'.$iduser.'"');
	    } catch (Zend_Exception $e) {
	        $boerr	= new ErrosModel();
	    	$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "UsuarioBO::atualizaSessao()");
	    	$boerr->insert($dataerro);
	    	return false;
	    	break;
	    }
	}
	
	//-- Desempenho funcionario -----------------------------------------------------
	
	//----- relatorio de vendas ----------------------------
	function buscaValorvendas($val=array()){
	
		$usuario = Zend_Auth::getInstance()->getIdentity();		
		
		if((!empty($val['dtini'])) || (!empty($val['dtfim']))){
			$dataini = substr($val['dtini'],6,4).'-'.substr($val['dtini'],3,2).'-'.substr($val['dtini'],0,2);;
			$datafim = substr($val['dtfim'],6,4).'-'.substr($val['dtfim'],3,2).'-'.substr($val['dtfim'],0,2);;;
	
			if((!empty($val['dtini'])) and (!empty($val['dtfim']))){
				$where 		= " and p.data_vend between '".$dataini."' and '".$datafim." 23:59:59'";
				$wheredesp  = " and f.emissao between '".$dataini."' and '".$datafim." 23:59:59'";
				$wherenf	= " and n.data between '".$dataini."' and '".$datafim." 23:59:59'";
			}elseif((!empty($val['dtini'])) and (empty($val['dtfim']))){
				$where 		= " and p.data_vend >= '".$dataini."'";
				$wheredesp 	= " and f.emissao >= '".$dataini."'";
				$wherenf 	= " and n.data >= '".$dataini."'";
			}elseif((empty($val['dtini'])) and (!empty($val['dtfim']))){
				$where 		= " and p.data_vend <= '".$datafim."'";
				$wheredesp 	= " and f.emissao <= '".$datafim."'";
				$wherenf 	= " and n.data <= '".$datafim."'";
			}
		}else{
			$where = " and p.data_vend like '".date('Y-m')."%'";
			$wheredesp = " and f.emissao like '".date('Y-m')."%'";
			$wherenf = " and n.data like '".date('Y-m')."%'";
		}
			
		$where 		.= " and p.id_televenda = '".$val['idusuario']."'";
		$wheredesp 	.= " and f.id_usuarios = '".$val['idusuario']."'";
		$wherenf    .= " and p.id_televenda = '".$val['idusuario']."'";
		
		//--- Busca vendas faturadas --------------------------------------------------------------------
		$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$select = $db->select();
	
		$select->from(array('p'=>'tb_pedidos','*'), array('sum(pd.qt*pd.preco_unit) as precototal'))
		->join(array('pd'=>'tb_pedidos_prod'), 'pd.id_ped = p.id')
		->where('p.status = "ped" and p.sit = 0'.$where);
	
		$stmt = $db->query($select);
		$obj = $stmt->fetchAll();
			
		if(count($obj)>0){
			foreach ($obj as $vendas);
			$vendas = $vendas->precototal;
		}
			
		//--- Busca descontos  ---------------------------------------------------------------------------
		$select = $db->select();
		$select->from(array('p'=>'tb_pedidos','*'), array('sum(p.desconto) as descontototal'))
		->where('p.status = "ped" and p.sit = 0'.$where);
			
		$stmt = $db->query($select);
		$obj = $stmt->fetchAll();
	
		if(count($obj)>0){
			foreach ($obj as $desconto);
			$desconto = $desconto->descontototal;
		}
			
		$vendas = $vendas-$desconto;
			
		//--- Busca custo dos produtos --------------------------------------------------------------------
		$select = $db->select();
		$select->from(array('p'=>'tb_pedidos','*'), array('sum(pd.qt*pd.custocompra) as custototal'))
		->join(array('pd'=>'tb_pedidos_prod'), 'pd.id_ped = p.id')
		->where('p.status = "ped" and p.sit = 0'.$where);
			
		$stmt = $db->query($select);
		$obj = $stmt->fetchAll();
	
		if(count($obj)>0){
			foreach ($obj as $custo);
			$custo = $custo->custototal;
		}
			
		//3.1.2.02 campanhas
		
		
		//--- Busca despesas ------------------------------------------------------------------------------
		$bof	= new FinanceiroModel();
				
		$admin = 0;
		$wheredesp .= " and p.id_financeiroplcontas in (164,167,176,178,179,180,181,183,184,185,186,187,188,201,209,210)";
		 
		$select = $db->select();
		$select->from(array('f'=>'tb_financeiropag','*'), array('sum(p.valor_apagar) as valortotal'))
		->join(array('p'=>'tb_financeiropagparc'),'f.id = p.id_financeiropag')
		->where("f.sit = true and p.sit = true ".$wheredesp);

		$stmt = $db->query($select);
		$obj = $stmt->fetchAll();
			
		if(count($obj)>0){
			foreach ($obj as $despesas);
			$admin = $despesas->valortotal;
		}
		
		
		//-- busca impostos pela emissao da nota ---------------------------------------------------------
		//--icms -----------------------------------------------------------------------------------------
		$icms = $pis = $cofins = $ipi = 0;
		$select = $db->select();
		$select->from(array('n'=>'tb_nfe','*'), array('sum(n.vlicms) as valortotal'))
			->join(array('p'=>'tb_pedidos'), 'p.id_nfe = n.id')
			->where("n.status = 1 and n.cfop not in (5916,6911,6915,6916,6501,6901,6949,7949) and n.tipo = 1 ".$wherenf);
	
		$stmt = $db->query($select);
		$obj = $stmt->fetchAll();
		 
		if(count($obj)>0){
			foreach ($obj as $despesas);
			$icms = $despesas->valortotal;
		}
	
		$select = $db->select();
		$select->from(array('n'=>'tb_nfe','*'), array('sum(n.vlicms) as valortotal'))
			->join(array('p'=>'tb_pedidos'), 'p.id_nfe = n.id')
			->where("n.status = 1 and n.cfop in (3102) and n.tipo = 0 ".$wherenf);
	
		$stmt = $db->query($select);
		$obj = $stmt->fetchAll();
		 
		if(count($obj)>0){
			foreach ($obj as $despesas);
			$icms -= $despesas->valortotal;
		}
	
		//-- ipi ----------------------------------------------------------------------------------------
		$select = $db->select();
		$select->from(array('n'=>'tb_nfe','*'), array('sum(n.totalipi) as valortotal'))
			->join(array('p'=>'tb_pedidos'), 'p.id_nfe = n.id')
			->where("n.status = 1 and n.cfop not in (5916,6911,6915,6916,6501,6901,6949,7949) and n.tipo = 1 ".$wherenf);
	
		$stmt = $db->query($select);
		$obj = $stmt->fetchAll();
		 
		if(count($obj)>0){
			foreach ($obj as $despesas);
			$ipi = $despesas->valortotal;
		}
	
		$select = $db->select();
		$select->from(array('n'=>'tb_nfe','*'), array('sum(n.totalipi) as valortotal'))
			->join(array('p'=>'tb_pedidos'), 'p.id_nfe = n.id')
			->where("n.status = 1 and n.cfop in (3102) and n.tipo = 0 ".$wherenf);
	
		$stmt = $db->query($select);
		$obj = $stmt->fetchAll();
		 
		if(count($obj)>0){
			foreach ($obj as $despesas);
			$ipi -= $despesas->valortotal;
		}
	
		//-- cofins ----------------------------------------------------------------------------------------
		$select = $db->select();
		$select->from(array('n'=>'tb_nfe','*'), array('sum(n.totalcofins) as valortotal'))
			->join(array('p'=>'tb_pedidos'), 'p.id_nfe = n.id')
			->where("n.status = 1 and n.cfop not in (5916,6911,6915,6916,6501,6901,6949,7949) and n.tipo = 1 ".$wherenf);
	
		$stmt = $db->query($select);
		$obj = $stmt->fetchAll();
		 
		if(count($obj)>0){
			foreach ($obj as $despesas);
			$cofins = $despesas->valortotal;
		}
	
		$select = $db->select();
		$select->from(array('n'=>'tb_nfe','*'), array('sum(n.totalcofins) as valortotal'))
			->join(array('p'=>'tb_pedidos'), 'p.id_nfe = n.id')
			->where("n.status = 1 and n.cfop in (3102) and n.tipo = 0 ".$wherenf);
	
		$stmt = $db->query($select);
		$obj = $stmt->fetchAll();
		 
		if(count($obj)>0){
			foreach ($obj as $despesas);
			$cofins += $despesas->valortotal;
		}
	
		//-- pis ----------------------------------------------------------------------------------------
		$select = $db->select();
		$select->from(array('n'=>'tb_nfe','*'), array('sum(n.totalpis) as valortotal'))
			->join(array('p'=>'tb_pedidos'), 'p.id_nfe = n.id')
			->where("n.status = 1 and n.cfop not in (5916,6911,6915,6916,6501,6901,6949,7949) and n.tipo = 1 ".$wherenf);
	
		$stmt = $db->query($select);
		$obj = $stmt->fetchAll();
		 
		if(count($obj)>0){
			foreach ($obj as $despesas);
			$pis = $despesas->valortotal;
		}
	
		$select = $db->select();
		$select->from(array('n'=>'tb_nfe','*'), array('sum(n.totalpis) as valortotal'))
			->join(array('p'=>'tb_pedidos'), 'p.id_nfe = n.id')
			->where("n.status = 1 and n.cfop in (3102) and n.tipo = 0 ".$wherenf);
	
		$stmt = $db->query($select);
		$obj = $stmt->fetchAll();
		 
		if(count($obj)>0){
			foreach ($obj as $despesas);
			$pis += $despesas->valortotal;
		}
	
		//-- irpj e csll ----------------------------------------------------------------------------------------------
		$totalfaturamento = 0;
		$select = $db->select();
		$select->from(array('n'=>'tb_nfe','*'), array('sum(n.totalnota) as valortotal'))
			->join(array('p'=>'tb_pedidos'), 'p.id_nfe = n.id')
			->where("n.status = 1 and n.cfop not in (5916,6911,6915,6916,6501,6901,6949,7949) and n.tipo = 1 ".$wherenf);
	
		$stmt = $db->query($select);
		$obj = $stmt->fetchAll();
		 
		if(count($obj)>0){
			foreach ($obj as $faturado);
			$totalfaturamento = $faturado->valortotal;
		}
	
	
		$lucroirpj = ($totalfaturamento*8)/100;
		$lucroirpj = ($lucroirpj*15)/100;
		 
		$lucroclss = ($totalfaturamento*12)/100;
		$lucroclss = ($lucroclss*9)/100;
	
	
		$tributaria = $icms+$ipi+$pis+$cofins+$lucroclss+$lucroirpj;
			
		?>
	    <style>
	    			    	
	    	.red{
	    		color: #f00;
	    		text-align: right;
	    	}
	    	
			.total{
	    		text-align: right;
	    		font-weight: bold;
	    		font-size: 14px;
	    	}
	    	
	    	.cinza{
	    		background-color: #d5d5d5;
	    	}
	    	
	    	.text-right{
	    		text-align: right;
	    	}
	    </style>
		    
		    
			<div class="widget">
				<div class="head"><h5 class="iMoney">Resultado no período</h5></div>
		    	<table class="tableStatic" style="width: 100%">
		        	<tbody>
	                	<tr>
                        	<td>Faturamento Líquido</td>
                            <td class="text-right"><?php echo number_format($vendas,2,",",".")?></td>
	                  	</tr>
                        <tr>
                        	<td>Custo dos produtos</td>
                            <td class="red"><?php echo number_format($custo,2,",",".")." (".number_format(($custo*100)/$vendas,2,",",".").")"?>%</td>
                      	</tr>
                        <tr>
                        	<td>Despesas</td>
                            <td class="red"><?php echo number_format($admin+$tributaria,2,",",".")." (".number_format((($admin+$tributaria)*100)/$vendas,2,",",".").")"?>%</td>
                      	</tr>
                        <tr>
                        	<td>Resultado</td>
                            <td class="total"><?php echo number_format($vendas-($admin+$tributaria+$custo),2,",",".")." (".number_format((($vendas-($admin+$tributaria+$custo))*100)/$vendas,2,",",".").")"?>%</td>
                    	</tr>
                  	</tbody>
            	</table>  
			</div>
			            
			    <div class="widget">
            		<div class="head"><h5 class="iMoney">Percentuais</h5></div>
            		<table class="tableStatic" style="width: 100%">
                        <tbody>
                            <tr>
                                <td>Markup</td>
                                <td class="text-right"><?php echo number_format((($vendas-$custo)/$custo)*100,2,",",".")?>%</td>
                            </tr>
                            <tr>
                                <td>Margem</td>
                                <td class="text-right"><?php echo number_format((($vendas-$custo)/$vendas)*100,2,",",".")."% (R$ ".number_format($vendas-$custo,2,",",".").")"; ?></td>
                            </tr>
                        </tbody>
                    </table>  
			  	</div>
			            
	            <div class="widget">
	            	<div class="head"><h5 class="iMoney">Despesas</h5></div>
	            	<table class="tableStatic" style="width: 100%">
                        <tbody>
                            <tr>
                                <td>Desp admin</td>
                                <td class="text-right"><?php echo number_format($admin,2,",",".")." (".number_format(($admin*100)/$vendas,2,",",".").")"?>%</td>
                            </tr>
                            <tr>
                                <td>Desp tributária</td>
                                <td class="text-right"><?php echo number_format($tributaria,2,",",".")." (".number_format(($tributaria*100)/$vendas,2,",",".").")"?>%</td>
                            </tr>
                        </tbody>
                    </table>  
		     	</div>
			          
		    <div class="clear"></div>
		<?php	
	}	
	
	function buscaInteracoesempresa($val=array()){
		$bo 	= new ContatosModel();
		$boe	= new ContatosempModel();
		$bor	= new RegioesModel();
		$bort	= new RegioestelevendasModel();
		$bou	= new UsuarioModel();
		$boc	= new ClientesModel();
		
		$reg = "";
		foreach ($bort->fetchAll("id_usuarios = '".$val['idusuario']."'") as $regioes){
			$reg .= $regioes->id.",";
		}
		
		if($reg!="") $where = " and c.id_regioestelevendas in (".substr($reg, 0,-1).")";
		else $where = " and c.id < 0";
		
		$whereint = " and id_usuarios = '".$val['idusuario']."'";
		$whereped = " and p.id_televenda = '".$val['idusuario']."'";
		
		?>
		
		<div class="widget">
        	<div class="head"><h5 class="iUsers">Contatos</h5></div>
        	<div style="padding: 10px; font-size: 16px">
            <?php 
	            $contatosemp = 0;
	            
	            $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
	            $db->setFetchMode(Zend_Db::FETCH_OBJ);
	            $select = $db->select();
	            
	            $select->from(array('c'=>'tb_contatosemp'), array('count(*) as totalcontatos'))->where('c.status = 1 '.$where);
	            
	            $stmt = $db->query($select);
	            $obj = $stmt->fetchAll();
	            	
	            if(count($obj)>0){
	            	foreach ($obj as $resultado);
	            	$contatosemp =  $resultado->totalcontatos;
	            }
	            
	            //-- interacoes ----------------------------------------------------------
	            $select = $db->select();
	             
	            $select->from(array('c'=>'tb_contatosempinteracao'), array('count(*) as totalcontatos'))
	            ->where('c.sit = 1 and data >= CURDATE() - INTERVAL 90 DAY '.$whereint);
	             
	            $stmt = $db->query($select);
	            $obj = $stmt->fetchAll();
	            
	            if(count($obj)>0){
	            	foreach ($obj as $resultado);
	            	$contatosint =  $resultado->totalcontatos;
	            }
	            
	            $select = $db->select();
	            $select->from(array('c'=>'tb_contatosempcomentarios'), array('count(*) as totalcontatos'))
	            ->where('data >= CURDATE() - INTERVAL 90 DAY '.$whereint);
	            
	            $stmt = $db->query($select);
	            $obj = $stmt->fetchAll();
	             
	            if(count($obj)>0){
	            	foreach ($obj as $resultado);
	            	$contatoscom =  $resultado->totalcontatos;
	            }
            
	            //-- todos que compraram ----------------------------------------------------
	            $select = $db->select();
	            $select->from(array('c'=>'tb_contatosemp'), array('*'))
		            ->join(array('p'=>'tb_pedidos'), 'p.id_parceiro = c.id_clientes and p.sit = 0')
		            ->where('p.data_cad >= CURDATE() - INTERVAL 90 DAY'.$whereped)
		            ->group('p.id')
		            ->group('c.id_clientes');
	            
	            $stmt = $db->query($select);
	            $objped = $stmt->fetchAll();
	            
	            
	            //-- compra com interacao -----------------------------------------------------
	            $select = $db->select();
	            $select->from(array('i'=>'tb_contatosempinteracao'), array('*'))
	            	->join(array('c'=>'tb_contatosemp'), 'c.id = i.id_contatosemp')
	            	->join(array('p'=>'tb_pedidos'), 'p.id_parceiro = c.id_clientes and p.sit = 0')
	            	->where('i.sit = 1 and i.data >= CURDATE() - INTERVAL 90 DAY and p.data_cad >= CURDATE() - INTERVAL 90 DAY'.$whereint)
	            	->group('p.id')
	            	->group('c.id_clientes');
	             
	            $stmt = $db->query($select);
	            
	            $objint = $stmt->fetchAll();

	            $usuario = $bou->fetchRow("id = '".$val['idusuario']."'");
	            $nome = explode(" ", $usuario->nome);
	            
            ?>
            	<p>Existem <b><?php echo $contatosemp?></b> empresas cadastrada na região do <b><?php echo $nome[0]?></b></p>
            	<p>O <b><?php echo $nome[0]?></b> realizou <b><?php echo $contatosint?></b> Interações e 
            	<b><?php echo $contatoscom?></b> comentários nos últimos 90 dias</p>
            	<p><b><?php echo count($objped)?></b> empresas realizaram pedidos</p>
            	<p><b><?php echo count($objint)?></b> empresas que realizaram pedidos tiveram interação</p>
            </div>
		</div>
		
		<style>
			.divlinha{
				border-left: 3px solid #d5d5d5;
				margin: 20px 0px 10px 10px;				
			}
			
			.marcador{
				padding: 5px;
			}
			
			.marcador img{
				width: 12px;
				margin-left: -12px;
				margin-right: 10px;
			}
		</style>
		
		<div class="widget">
        	<div class="head"><h5 class="iDayCalendar">Linha do tempo</h5></div>
        	<div class="divlinha">
        		<?php 
        		//-- Logs -----------------------------------------------------
        		$select = $db->select();
        		$select->from(array('l'=>'tb_logacesso'), array('*','DATE_FORMAT(l.data,"%d/%m/%Y %H:%i") as datahora'))
	        		->where('l.acao != 1 and (modulo like "Cadastro/Contatos" || modulo like "Cadastro/Empresa Contatos" || modulo like "Contatos/Interação" || 
					modulo like "Cadastro/Empresa Contatos" || modulo like "Vendas/Orçamentos" || modulo like "Vendas/Pedidos") '.$whereint)
					->order('l.id desc')
					->limit(20);

        		$stmt = $db->query($select);
        		 
        		foreach ($stmt->fetchAll() as $logs){
					?>        	
	        		<div class="marcador">
	        			<img alt="" src="/public/sistema/imagens/icons/dark/circle.png">
	        			<?php echo $logs->datahora?> - 
	        			<?php 
	        			
	        			if($logs->modulo == "Login de acesso") echo "Entrou no sistema";
	        			
	        			if($logs->modulo == "Cadastro/Empresa Contatos"){
							if($logs->acao == 2) echo "Cadastrou a empresa ";
							if($logs->acao == 3) echo "Excluiu a empresa ";
							if($logs->acao == 4) echo "Editou a empresa ";
							
							$empresa = $boe->fetchRow("id = '".$logs->identificador."'");
							?><a target="_blank" href="/admin/cadastro/contatosempcad/empresa/<?php echo md5($logs->identificador)?>"><?php echo $empresa->empresa; ?></a><?php 
						}
	        			
						if($logs->modulo == "Cadastro/Contatos"){
							if($logs->acao == 2) echo "Cadastrou o contato ";
							if($logs->acao == 3) echo "Excluiu o contato ";
							if($logs->acao == 4) echo "Editou o contato ";
								
							$empresa = $bo->fetchRow("id = '".$logs->identificador."'");
							echo $empresa->nome;
						}
						
						if($logs->modulo == "Contatos/Interação"){
							echo "Realizou uma interação com a empresa ";
							$empresa = $boe->fetchRow("id = '".$logs->identificador."'");
							?><a target="_blank" href="/admin/cadastro/contatosempcad/empresa/<?php echo md5($logs->identificador)?>"><?php echo $empresa->empresa; ?></a><?php
						}
						
						if($logs->modulo == "Vendas/Orçamentos"){
							echo "Gerou um orçamento para a empresa ";
							$empresa = $boc->fetchRow("ID = '".$logs->identificador."'");
							echo $empresa->EMPRESA;
						}
						
						if($logs->modulo == "Vendas/Pedidos"){
							echo "Gerou um pedido para a empresa ";
							$bov	= new PedidosvendaModel();
							$pedido = $bov->fetchRow("id = '".$logs->identificador."'");
							if(count($pedido)>0){ 
								$empresa = $boc->fetchRow("ID = '".$pedido->id_parceiro."'");
								?><a target="_blank" href="/admin/venda/pedidosedit/ped/<?=md5($pedido->id)?>" ><?php echo $empresa->EMPRESA; ?></a><?php 
							}
						}
	        			?>
	        		</div>    
	        		<?php 
	        		}
        		?>
        	</div>
        </div>
		
		<?php
	}	
}
?>
