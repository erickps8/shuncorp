<?php
	class AtividadesBO{		
	    
	    function formAtividade($params=""){
	        ?>
	        
	        <div id="loading">
        		<img src="/public/sistema/imagens/loaders/loader6.gif"> <i>Aguarde...</i>
        	</div>
        	
	        <div id="divform" style="display: none">
				<script type="text/javascript" src="/public/sistema/js/jquery-ui.min.js"></script> 
				<script type="text/javascript" src="/public/sistema/js/custom.min.js"></script>
				<script type="text/javascript" src="/public/sistema/js/buscaajax/administracaoAtividadesaux.js"></script>
		        
		        
	        	<input type="hidden" name="idatividade" value=""/>
        		<div style="width: 100%; text-align: left;">
        			<form class="mainForm">
        			<div >
        				Título:<br />
        				<input type="text" name="titulo" style="width: 250px">
        			</div>
        			<div >
        				Descrição:<br /><textarea rows="" cols="" name="atividade" id="atividade" style="width: 500px"></textarea>
        				<input type="hidden" name="previsao" id="previsao" value="<?php echo $params['previsao']?>">
        			</div>
        			</form>
        			
        			<form class="mainForm" id="funcionarios">
        			<div style="width: 100%" >
        				Responsáveis:
        				<div class="widget" style="border-top: 1px solid #d5d5d5; width: 100%; margin-top: -2px" >
	                		<table style="width: 100%" class="tableStatic">
	                			<tbody>
			                		<?php 
			                		$cont = 0;
			                		foreach (UsuarioBO::buscaUsuario("","funcionario") as $funcionarios){ 
			                			if($cont == 0){ ?><tr><?php }
					                	?>
					                	<td style="padding-left: 5px">
					                		<input type="checkbox" name="func_<?=$funcionarios->iduser?>" > <label><?=$funcionarios->nomeusuario?></label>
					                	</td>
					                	<?php 
					                	$cont++;
					                	if($cont == 3){ echo "</tr>"; $cont = 0; }							                	
					                } ?>
	                			</tbody>
	                		</table>
                		</div>
        			</div>
        			</form>
        		</div>
        		
        		<div style="text-align: left; width: 100%">
        			<input type="button" style="margin-top: 5px;" class="basicBtn" value="Salvar" id="btngravaativ" />
        		</div>	        		
        	</div>
        	
        	<?php
    	}
    	
		public function cadastraAtividades($params){
		    
		    try{
				$bo 		= new AtividadesModel();
				$bou 		= new AtividadesuserModel();
				$usuario 	= Zend_Auth::getInstance()->getIdentity();
	        			
				$array['dt_cad']	  		= date("Y-m-d H:i:s");
				$array['descricao']       	= $params['atividade'];
				$array['titulo']       		= $params['titulo'];
				$array['dt_previsao']     	= $params["previsao"];
				$array['id_solicitante']    = $usuario->id;
				$array['sit']	 			= 1;
				
				/*	0 - Excluído
					1 - Cadastrado 
					2 - Executando
					3 - Executado
					4 - Concluído
				*/
				
		        if(empty($params['id'])){
					$id = $bo->insert($array);
		        }else{
					$bo->update($array,'id ='.$params['id']);
					$id = $params['id'];
		        }
		        
		        foreach (UsuarioBO::buscaUsuario("","funcionario") as $lista){
		        	if(isset($params["func_".$lista->iduser])){
		        		$arrayuser['id_atividades']		= $id;
		        		$arrayuser['id_usuarios'] 		= $lista->iduser;
		        		
		        		if($lista->iduser == $usuario->id) $arrayuser['leitura'] = true;
		        		
		        		$bou->insert($arrayuser);		        		
		        	}
		        }
		        
		        return true;
	        }catch (Zend_Exception $e){
	        	$boerro	= new ErrosModel();
	        	$dataerro = array('descricao' => $e->getMessage(), 'pagina' => 'AtividadesBO::cadastraAtividades()');
	        	$boerro->insert($dataerro);
	        	return false;
	        }
		}
    	
		public function cadastraComentarios($params){
		
			try{
				$bo 		= new AtividadesModel();
				$bou 		= new AtividadesuserModel();
				$boc 		= new AtividadesinteracaoModel();
				
				$usuario 	= Zend_Auth::getInstance()->getIdentity();
		
				$array['data']	  		= date("Y-m-d H:i:s");
				$array['descricao']     = $params['comentario'];
				$array['id_atividades']	= $params['idatividade'];
				$array['id_usuarios']   = $usuario->id;
		
				$id = $boc->insert($array);
				
				//-- marca como nao lida se nao for o solicitante ----------------------------------------------------
				foreach ($bo->fetchAll("id = '".$params['idatividade']."'") as $atividade) 
				if($usuario->id != $atividade->id_solicitante) $bo->update(array('leitura' => false), "id = '".$params['idatividade']."'");
				
				//-- marco com nao lido para os responsaveis ---------------------------------------------------------
				$bou->update(array('leitura' => false), "id_atividades = '".$params['idatividade']."'");
				
				//-- marco com lido para os usuario do comentario -----------------------------------------------------
				$bou->update(array('leitura' => true), "id_atividades = '".$params['idatividade']."' and id_usuarios = '".$usuario->id."'");
				
				return true;
			}catch (Zend_Exception $e){
				$boerro	= new ErrosModel();
				$dataerro = array('descricao' => $e->getMessage(), 'pagina' => 'AtividadesBO::cadastraComentarios()');
				$boerro->insert($dataerro);
				return false;
			}
		}
		
		public function iniciarAtividade($params){
		
			try{
				$bo 		= new AtividadesModel();
				$array['dt_inicio']		= date("Y-m-d");
				$array['dt_previsao']	= substr($params['dtprevisao'],6,4).'-'.substr($params['dtprevisao'],3,2).'-'.substr($params['dtprevisao'],0,2);
				$array['sit']			= 2;
				
				$bo->update($array,"id = '".$params['idatividade']."'");
				return true;
			}catch (Zend_Exception $e){
				$boerro	= new ErrosModel();
				$dataerro = array('descricao' => $e->getMessage(), 'pagina' => 'AtividadesBO::iniciarAtividade()');
				$boerro->insert($dataerro);
				return false;
			}
		}
		
		public function fechaAtividade($params){
		
			try{
				$bo = new AtividadesModel();
				$array['dt_concluido']	= date("Y-m-d");
				$array['sit']			= 3;
		
				$bo->update($array,"id = '".$params['idatividade']."'");
				return true;
			}catch (Zend_Exception $e){
				$boerro	= new ErrosModel();
				$dataerro = array('descricao' => $e->getMessage(), 'pagina' => 'AtividadesBO::fechaAtividade()');
				$boerro->insert($dataerro);
				return false;
			}
		}
		
		public function encerraAtividade($params){
		
			try{
				$bo = new AtividadesModel();
				$array['sit'] = 4;
				$bo->update($array,"id = '".$params['idatividade']."'");
				return true;
			}catch (Zend_Exception $e){
				$boerro	= new ErrosModel();
				$dataerro = array('descricao' => $e->getMessage(), 'pagina' => 'AtividadesBO::encerraAtividade()');
				$boerro->insert($dataerro);
				return false;
			}
		}
		
		public function reabrirAtividade($params){
		
			try{
				$bo = new AtividadesModel();
				$array['sit'] = 1;
				$bo->update($array,"id = '".$params['idatividade']."'");
				return true;
			}catch (Zend_Exception $e){
				$boerro	= new ErrosModel();
				$dataerro = array('descricao' => $e->getMessage(), 'pagina' => 'AtividadesBO::encerraAtividade()');
				$boerro->insert($dataerro);
				return false;
			}
		}
		
		public function listarAtividades($var=""){
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			
			$select->from(array('a'=>'tb_atividades','*'), array('*','a.id as idatividade','a.leitura as leituraativ','s.leitura as leiturauser'))
		    	->joinLeft(array('s'=>'tb_atividadesusuarios'), 's.id_atividades = a.id')
		        ->where('a.sit != 0 and (a.id_solicitante = '.$usuario->id.' || s.id_usuarios = '.$usuario->id.')')
				->group('a.id');
			  
			$stmt = $db->query($select);
			$objAtiv = $stmt->fetchAll();	
			
			if(count($objAtiv)>0){
			    $retorno = $tipo = "";
				foreach ($objAtiv as $atividades){
				    
				    if($usuario->id == $atividades->id_solicitante){
				        if($atividades->sit == 1) $tipo = 1;
				        if($atividades->sit == 2) $tipo = 2;
				        if($atividades->sit == 3) $tipo = 3;
				        if($atividades->sit == 4) $tipo = 3;
				        
				        if($atividades->leituraativ == false) $tipo = 0;
				        
				    }else{ 
				        if($atividades->sit == 1) $tipo = 4;
				        if($atividades->sit == 2) $tipo = 5;
				        if($atividades->sit == 3) $tipo = 6;
				        if($atividades->sit == 4) $tipo = 6;
				        
				        if($atividades->leiturauser == false) $tipo = 0;
				    }
				    
					$retorno .= 
					$atividades->dt_previsao.";".
					$atividades->titulo.";".
					$tipo.";".
					$atividades->idatividade.";".
					$atividades->dt_inicio."|";
				}
				
				echo substr($retorno, 0,-1);
			}
		}
		
		function buscaAtividade($params=""){
			$bo 		= new AtividadesModel();
			$bou 		= new AtividadesuserModel();
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			if(count($bo->fetchAll("id = '".$params['id']."'"))>0){
				$select = $db->select();
					
				$select->from(array('a'=>'tb_atividades','*'), array('*','a.id as idatividade','u.nome','a.sit as sitatividade'))
					->join(array('u'=>'tb_usuarios'), 'u.id = a.id_solicitante')
		        	->where('a.id = "'.$params['id'].'"');
					
				$stmt = $db->query($select);
				
				foreach ($stmt->fetchAll() as $atividade);
				
				//-- marca como lida se for o solicitante ----------------------------------------------------
				$arrleitura = array('leitura' => true);
				if($usuario->id == $atividade->id_solicitante) $bo->update($arrleitura, "id = '".$params['id']."'");
				
				?>
				<style>
        			.comentarios{
        				text-align: left; 
        				width: 100%; 
        				margin-top: 10px; 
        				width: 90%;
        				padding: 10px;
        				border-radius: 5 5 5;
        				background-color: #EDEDED;
        			}
        		</style>
				<div id="loading">
	        		<img src="/public/sistema/imagens/loaders/loader6.gif"> <i>Aguarde...</i>
	        	</div>
	        	
		        <div id="divform" style="display: none">
					<script type="text/javascript" src="/public/sistema/js/jquery-ui.min.js"></script> 
					<script type="text/javascript" src="/public/sistema/js/custom.min.js"></script>
					<script type="text/javascript" src="/public/sistema/js/buscaajax/administracaoAtividadesaux.js"></script>
			        
			        
		        	<input type="hidden" name="idatividade" value=""/>
	        		<div style="width: 100%; text-align: left;">
	        			<div style="width: 75%; float: left;">
	        				<div>
		        				Situação:<br />
		        				<b><?php 
		        				if($atividade->sitatividade == 1) echo "Cadastrada"; 
		        				if($atividade->sitatividade == 2) echo "Em execução";
		        				if($atividade->sitatividade == 3) echo "Concluida";
		        				if($atividade->sitatividade == 4) echo "Fechada";
		        				?></b>
		        			</div>
		        			<div>
		        				Título:<br />
		        				<b><?php echo $atividade->titulo?></b>
		        			</div>
		        			<div >
		        				Descrição:<br />
		        				<b><?php echo $atividade->descricao?></b>	        				
		        			</div>
	        			</div>
	        			
	        			<div style="width: 20%; float: right; border: 1px solid #d5d5d5; padding: 5px" >
	        				Solicitante:<br />
	        				<b><?php 
		        				$sol = explode(" ", $atividade->nome);
								echo $sol[0];
							?></b><br />
	        				Responsáveis:<br/><b>
	        				<?php 
	        				
	        				$select = $db->select();
	        				$select->from(array('s'=>'tb_atividadesusuarios','*'), array('u.nome','s.id_usuarios'))
		        				->join(array('u'=>'tb_usuarios'), 'u.id = s.id_usuarios')
		        				->where('s.id_atividades = "'.$params['id'].'"');
	        					
	        				$stmt = $db->query($select);
	        				$objAtiv = $stmt->fetchAll();
	        				
	        				$res = 0;
	        				foreach ($objAtiv as $useratividades){
								$nome = explode(" ", $useratividades->nome);
								echo $nome[0]."<br />";
								
								if($usuario->id == $useratividades->id_usuarios){ 
									$res = 1;
									$bou->update($arrleitura, "id_atividades = '".$params['id']."' and id_usuarios = '".$usuario->id."'");
								}								
							}
	        				?></b>
	        			</div>
	        			
	        		</div>
	        		
	        		<div class="fix"></div>
	        		<?php 
        				$select = $db->select();
        				$select->from(array('s'=>'tb_atividadesinteracao','*'), array('s.descricao','u.nome','s.id_usuarios','DATE_FORMAT(s.data,"%d/%m/%Y %H:%i") as datacometario'))
	        				->join(array('u'=>'tb_usuarios'), 'u.id = s.id_usuarios')
	        				->where('s.id_atividades = "'.$params['id'].'"')
        					->order('s.id asc');
        					
        				$stmt = $db->query($select);
        				$objAtiv = $stmt->fetchAll();
        				
        				if(count($objAtiv)>0){
	        				foreach ($objAtiv as $comentarios){
								$nome = explode(" ", $comentarios->nome);
								
								if($atividade->id_solicitante == $comentarios->id_usuarios){ 
									?>
									<div style="float: left;" class="comentarios">
										<?php echo $comentarios->descricao?><br />
										<b><?php echo $nome[0]." em ".$comentarios->datacometario?></b>
									</div>
									<?php
								}else{
									?>
									<div style="float: right;" class="comentarios">
										<?php echo $comentarios->descricao?><br />
										<b><?php echo $nome[0]." em ".$comentarios->datacometario?></b>
									</div>
									<?php
								}							
							}
						}
        				?>
	        		
	        		<div class="fix"></div>
	        		
	        		<div style="text-align: left; width: 100%; margin-top: 10px">
	        		
	        		<?php if($atividade->sitatividade != 3 and $atividade->sitatividade != 4){ ?>
	        			<?php if($res == 1 and $atividade->sitatividade == 1){ ?>
	        			<input type="button" style="margin-top: 5px;" class="basicBtn" value="Iniciar" id="btniniciaativ" />
	        			<?php } ?>
	        			<input type="button" style="margin-top: 5px;" class="basicBtn" value="Comentar" id="btncomentativ" />
	        			<input type="button" style="margin-top: 5px;" class="greenBtn" value="Concluir" id="btnconcativ" />
	        			
	        			<form class="mainForm">
	        				<input type="hidden" name="idatividade" id="idatividade" value="<?php echo $atividade->idatividade?>">
		        			<div style="margin-top: 10px; border: 1px solid #d5d5d5; padding: 10px; display: none" id="divprevisao">
		        				Data previsão do término: 
		        				<input type="text" name="dtprevisao" id="dtprevisao" class="data datepicker"> 
		        				<input type="button" style="margin-top: 5px;" class="basicBtn" value="Salvar" id="btnsalvainiciaativ" />
		        			</div>
		        			
		        			<div style="margin-top: 10px; display: none" id="divcomentario">
		        				<textarea rows="" cols="" style="width: 500px" name="comentario" id="comentario"></textarea> 
		        				<div class="fix"></div>
		        				<input type="button" style="margin-top: 5px;" class="basicBtn" value="Salvar" id="btnsalvacomentaativ" />
		        			</div>
		        			<div style="margin-top: 10px; display: none" id="divfechar">
		        				Deseja marcar essa atividade como <b>Concluída?</b> 
		        				<input type="button" style="margin-top: 5px;" class="basicBtn" value="Sim" id="btnConcconfativ" />
		        			</div>
	        			</form>
	        			<?php }elseif($atividade->sitatividade != 4){ 
		        			if($atividade->id_solicitante == $usuario->id){
		        		    ?>
		        		    <input type="button" style="margin-top: 5px;" class="blueBtn" value="Reabrir" id="btnAbrirativ" />
		        		    <input type="button" style="margin-top: 5px;" class="greenBtn" value="Encerrar" id="btnFecharativ" />
	        			
		        			<form class="mainForm">
		        				<input type="hidden" name="idatividade" id="idatividade" value="<?php echo $atividade->idatividade?>">
			        			<div style="margin-top: 10px; display: none" id="divfechar">
			        				Deseja <b>encerrar</b> a atividade? 
			        				<input type="button" style="margin-top: 5px;" class="basicBtn" value="Sim" id="btnFecharconfativ" />
			        			</div>
		        			</form>
		        			<?php 
							}
						} ?>
	        		</div>	        		
	        	</div>
	        	<?php
	        }else{
	            ?>
            	<div style="text-align: center; padding: 10px">
            		Atividade não encontrada!
            	</div>
            	<?php
	        }
    	}
		
    	
    	public function qtAtividades($var=""){
			$bo = new AtividadesModel();
    		$usuario 	= Zend_Auth::getInstance()->getIdentity();
    		
    		$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
    		$db->setFetchMode(Zend_Db::FETCH_OBJ);
    		$select = $db->select();
    			
    		$select->from(array('a'=>'tb_atividades','*'), array('*'))
	    		->joinLeft(array('s'=>'tb_atividadesusuarios'), 's.id_atividades = a.id')
	    		->where('(a.sit != 0 and a.sit != 3) and s.id_usuarios = "'.$usuario->id.'"');
    			
    		$stmt = $db->query($select);
    		$objAtiv = $stmt->fetchAll();

    		echo count($bo->fetchAll("(sit != 0 and sit != 3) and id_solicitante = '".$usuario->id."'"));
    		echo "|";    		
    		echo count($objAtiv);
    	}

    	public function listarAtividadesuser($pesq=array()){
    		$usuario 	= Zend_Auth::getInstance()->getIdentity();
    			
    		$where = "";
    		if($pesq['tppesq'] == 0){
    			$where =  ' and a.id_solicitante = "'.$usuario->id.'"';
    		}else{
				$where =  ' and s.id_usuarios = "'.$usuario->id.'"';
			} 
			
			if(!empty($pesq['dtini'])) $di	= substr($pesq['dtini'],6,4).'-'.substr($pesq['dtini'],3,2).'-'.substr($pesq['dtini'],0,2);
			if(!empty($pesq['dtfim'])) $df	= substr($pesq['dtfim'],6,4).'-'.substr($pesq['dtfim'],3,2).'-'.substr($pesq['dtfim'],0,2);
			
			if((!empty($pesq['dtini'])) || (!empty($pesq['dtfim']))){
				if((!empty($di)) and (!empty($df))) 	$where .= ' and a.dt_cad between "'.$di.'" and "'.$df.'"';
				elseif((!empty($di)) and (empty($df))) 	$where .= ' and a.dt_cad >= "'.$di.'"';
				elseif((empty($di)) and (!empty($df))) 	$where .= ' and a.dt_cad <= "'.$df.'"';
			
				$limite = "";
			}

			if($pesq['buscasit'] != 0) $where .= " and a.sit = '".$pesq['buscasit']."'";
			else $where .= " and a.sit in (1,2)";
    		
    		$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
    		$db->setFetchMode(Zend_Db::FETCH_OBJ);
    		$select = $db->select();
    			
    		$select->from(array('a'=>'tb_atividades','*'), array('*','a.id as idatividade','a.leitura as leituraativ','s.leitura as leiturauser','a.sit as sitatividade',
				'DATE_FORMAT(a.dt_cad,"%d/%m/%Y") as dtcad',
				'DATE_FORMAT(a.dt_inicio,"%d/%m/%Y") as dtinicio',
				'DATE_FORMAT(a.dt_previsao,"%d/%m/%Y") as dtprevisao',
				'DATE_FORMAT(a.dt_concluido,"%d/%m/%Y") as dtconcluido'
				))
    		->joinLeft(array('s'=>'tb_atividadesusuarios'), 's.id_atividades = a.id')
    		->where('a.sit != 0 '.$where)
    		->group('a.id');
    			
    		$stmt = $db->query($select);
    		$objAtiv = $stmt->fetchAll();
	
    		?>
    		<div class="widget">
    			<?php 
    			if(count($objAtiv)>0){
    				?>
    					<table style="width: 100%;" class="tableStatic" >
    						<thead>
    							<tr>
	    						    <td >Titulo</td>
	    						    <td >Cadastro</td>
	    						    <td >Previsão</td>
	    						    <td >Conclusão</td>
	    						    <td >Situação</td>
	    						    <td >Opções</td>
								</tr>
    						</thead>
    						<tbody>
    						<?php			
    						foreach ($objAtiv as $listatividades){
    						?>
    							<tr  >
    								<td><?php echo $listatividades->titulo?></td>
    								<td style="text-align: center;"><?php echo $listatividades->dtcad?></td>
    								<td style="text-align: center;"><?php echo $listatividades->dtprevisao?></td>
    								<td style="text-align: center;"><?php echo $listatividades->dtconcluido?></td>
    								<td style="text-align: center;">
    									<?php 
    									if($listatividades->sitatividade == 1) echo "Cadastrada";
    									if($listatividades->sitatividade == 2) echo "Em execução";
    									if($listatividades->sitatividade == 3) echo "Concluida";
    									if($listatividades->sitatividade == 4) echo "Fechada";
    									?>
    								</td>
    								<td style="text-align: center;">
    									<a href="#" class="btnAtividade" rel="<?php echo $listatividades->idatividade?>"><img src="/public/sistema/imagens/icons/middlenav/magnify.png" width="16" border="0" title="Visualizar"></a>
    								</td>			
    							</tr>
    						<?php								
    						}					
    						?>
    						</tbody>
    					</table>
    				<?php 
    			}else{
    				?>
    					<div style="border-top: 1px solid #d5d5d5; padding: 15px">
    						Nenhuma conta encontrada!
    					</div>	
    				<?php 
    			}
    			?>
    			</div>
    		<?php
    		
    	}
    	
	}
?>