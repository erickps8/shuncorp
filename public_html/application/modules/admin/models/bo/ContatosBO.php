<?php
	class ContatosBO{		
	    /**
	     * listarCartoes
	     *
	     * @author programador
	     * @data 28/02/2014
	     * @tags @return Zend_Db_Table_Rowset_Abstract
	     */
		function listarCartoesgrupos(){
			$boa	= new ContatosModel();
			$bo		= new CartoesgruposModel();
			return $bo->fetchAll("sit = true","nome asc");
		}
		
		function gravaCartoesgrupos($params){
			$boa	= new ContatosModel();
			$bo		= new CartoesgruposModel();
			$usuario = Zend_Auth::getInstance()->getIdentity();
			
			$array['nome']	= strtoupper($params['nome']);
			
			if(!empty($params['idgrupo'])):
				$bo->update($array, "id = ".$params['idgrupo']);
				$id = $params['idgrupo'];
				LogBO::cadastraLog("OUTROS/Contatos/Grupos Cartões",2,$usuario->id,$id,"Grupo ".$id);
			else:
				$id = $bo->insert($array);
				LogBO::cadastraLog("OUTROS/Contatos/Grupos Cartões",4,$usuario->id,$id,"Grupo ".$id);
			endif;						
		}
		
		function removeCartoesgrupos($params){
			$boa	= new ContatosModel();
			$bo		= new CartoesgruposModel();
			
			$array['sit']	= false;
			
			$bo->update($array, "md5(id) = '".$params['idgrupo']."'");
			
			foreach ($bo->fetchAll("md5(id) = '".$params['idgrupo']."'") as $lista);
			
			$usuario = Zend_Auth::getInstance()->getIdentity();
			LogBO::cadastraLog("OUTROS/Contatos/Grupos Cartões",3,$usuario->id,$lista->id,"Grupo ".$lista->id);
		}		
				
		function listarCartoescontatos($params){
			$boa	= new ContatosModel();
			$bo		= new CartoescontatosModel();
			
			if(!empty($params['buscagrupo'])):
				$where = " and id_cartoesgrupos = ".$params['buscagrupo'];
			elseif(!empty($params['buscacont'])):
				$where = " and contato like '%".$params['buscacont']."%'";
			elseif(!empty($params['buscaempresa'])):	
				$where = " and empresa like '%".$params['buscaempresa']."%'";
			elseif(!empty($params['buscacidade'])):	
				$where = " and cidade like '%".$params['buscacidade']."%'";
			endif;
			
			return $bo->fetchAll("sit = true".$where,"id desc");
		}
		
		function buscaCartoescontatos($param){
			$boa	= new ContatosModel();
			$bo		= new CartoescontatosModel();
			return $bo->fetchAll("md5(id) = '".$param['contato']."'");
		}
		
		function buscaCartoesanexos($param){
			$boa	= new ContatosModel();
			$bo		= new CartoesanexoModel();
			return $bo->fetchAll("md5(id_cartoescontato) = '".$param['contato']."'");
		}
		
		function buscaCartoestelefones($param){
			$boa	= new ContatosModel();
			$bo		= new CartoesfonesModel();
			return $bo->fetchAll("md5(id_cartoescontatos) = '".$param['contato']."'");
		}
		
		function gravaCartoescontatos($params){
			$boa	= new ContatosModel();
			$bo		= new CartoescontatosModel();
			$bof	= new CartoesfonesModel();
			$box	= new CartoesanexoModel();
			
			$array['empresa']			= $params['empresa'];
			$array['contato']			= $params['contato'];
			$array['site']				= $params['site'];
			$array['email']				= $params['email'];
			$array['obs']				= $params['obs'];
			$array['id_cartoesgrupos']	= $params['grupo'];
			$array['cidade']			= $params['cidade'];
			$array['id_paises']			= $params['pais'];
			$array['id_estado']			= $params['uf'];
			$array['sit']				= true;
			
			if(!empty($params['idcontato'])):
				$id = $params['idcontato']; 
				$bo->update($array, "id = ".$params['idcontato']);
				
				$bof->delete("id_cartoescontatos = ".$params['idcontato']);
			else:
				$id = $bo->insert($array);
			endif;
			
			//--telefones-----------------------
			for($i=1;$i<=$params['intarchive'];$i++):
				$arrayfone['ddi']					= $params['ddi_'.$i];
				$arrayfone['ddd']					= $params['ddd_'.$i];
				$arrayfone['telefone']				= $params['numero_'.$i];
				$arrayfone['id_cartoescontatos']	= $id;
				$arrayfone['tipo']					= $params['tipotel_'.$i];
				
				$bof->insert($arrayfone);
			endfor;
			
			//--anexos--------------------------
			$ic = 0;
			foreach ($box->fetchAll('id_cartoescontato = '.$id) as $listanex);
			if(count($listanex)>0):
				$ianex = explode(".",$listanex->nome);
				$ic = substr($ianex[0],-1);
			endif;
			
	        for($i=1;$i<=$params['intarchive2'];$i++):
	        	 $ic++;
	        	
	         	 $arquivo = isset($_FILES['arquivo'.$i]) ? $_FILES['arquivo'.$i] : FALSE;
				 $nome = $id."_".$ic.substr($_FILES['arquivo'.$i]['name'], strrpos($_FILES['arquivo'.$i]['name'], "."), strlen($_FILES['arquivo'.$i]['name']));
				 				
				 $pasta = Zend_Registry::get('pastaPadrao')."public/contatoscartoes/";
				 				 
				 if (!(is_dir($pasta))){
					if(!(mkdir($pasta, 0777))){
	                   	echo ("Alerta: pasta de upload nao existe, e nao pode ser criada");
	                	return $this;                           
	                 }
	             }
	                   
	             if(!(is_writable($pasta))){
	             	echo ("Alerta: pasta sem permissao de escrita");
	                return $this;                   
	             }
				 				 
				 if(is_uploaded_file($_FILES['arquivo'.$i]["tmp_name"])){                                
	                  if (move_uploaded_file($arquivo["tmp_name"], $pasta . $nome)) {
	                  		$arrayarq['nome'] 				= $nome;
	                    	$arrayarq['id_cartoescontato']	= $id;
	                    	$box->insert($arrayarq);
	                  } else {
	                        echo ("Alerta: Nao foi possivel fazer o upload para $pasta");
	                        return $this;                                           
	                  }                               
	             }else{
		             echo "erro ao carregar imagem";
	             }
		    
	         endfor;
			
		}
		
		function remAnexos($params){
			$boa	= new ContatosModel();
			$bo		= new CartoescontatosModel();
			$box	= new CartoesanexoModel();
			
			$usuario = Zend_Auth::getInstance()->getIdentity();			
			
			foreach ($box->fetchAll('id = '.$params['anexo']) as $listanex);
			@unlink(Zend_Registry::get('pastaPadrao')."public/contatoscartoes/".$listanex->nome);
			
			$box->delete('id = '.$params['anexo']);
			LogBO::cadastraLog("OUTROS/Contatos/Grupos Cartões",3,$usuario->id,$listanex->nome,"Remove anexo ".$listanex->nome);
		}
		
		function validaCartoes($params){
			$boa	= new ContatosModel();
			$bo		= new CartoescontatosModel();
			$data['status'] = 1;
			
			$bo->update($data, "md5(id) = '".$params['contato']."'");
		}
		
		
		
		
		/** ----- contatos --------------------------------
		 * 
		 * buscaContatosemp
		 *
		 * @author programador
		 * @data 18/03/2014
		 * @tags @param Array $var
		 * @tags @param bool $tipo
		 * @tags @param var $tpemp
		 * @tags @return Ambigous <multitype:, multitype:mixed Ambigous <string, boolean, mixed> >
		 */
		
		function buscaContatosemp($var, $tipo="", $tpemp=""){
		    $usuario = Zend_Auth::getInstance()->getIdentity();
		    $sessaobusca = new Zend_Session_Namespace('Contatos');
		    
		    $where = "";
		    //--- busca empresas jah validadas -----------------------------------------
		    if(isset($var['quarentena'])) $where = " and ((e.verificado = 0))";
		    //--- busca matrizes ou filiais -----------------------------------------
		    if($tpemp == 'm') $where = " and e.id_matriz is NULL";
		    if($tpemp == 'f') $where = " and e.id_matriz";
		    
		    //--- busca pelo nome da empresa -------------------------------------------
		    if(!empty($var['empresa'])){
		        $where .= " and (e.empresa like '%".$var['empresa']."%' || 
		                e.id = '".preg_replace("/[^0-9]/", "", $var['empresa'])."' ||
		                c.nome like '%".$var['empresa']."%')";
		    }
		    //--- busca pelo ID da empresa ---------------------------------------------
		    if(!empty($var['emp'])) $where .= " and e.id = ".$var['emp'];
		    	
		    if(!empty($var['uf']) and ($var['uf']!="todos")) $where .= " and e.uf = '".$var['uf']."'";
		    	
		    if(!empty($var['parceiros']) || !empty($var['alvos']) || !empty($var['mercado'])){
		    	if(!empty($var['parceiros'])) 	$par = " || e.id_clientes is not NULL";
		    	if(!empty($var['alvos'])) 		$alvo = "Alvo";
		    	if(!empty($var['mercado'])) 	$mer = "Mercado";
		    
		    	$where .= " and (e.tipo_par in ('".$alvo."','".$mer."') ".$par.")";
		    }else{
		        //$where .= " and ((e.tipo_par='' || e.tipo_par='Parceiro')  and e.id_clientes is NULL) ";
		    }
		    
		    if(!empty($var['buscaregioes']) and $var['buscaregioes'] != 0) $where .= " and e.regiao = " .$var['buscaregioes'];
		    if(!empty($var['televenda']) and $var['televenda'] != 0) $where .= " and e.id_regioestelevendas = " .$var['televenda'];
		    	
		    
		    if(isset($var['idmatriz'])) $where = " and e.id_matriz = '".$var['idmatriz']."'";
		    
		    //--- Controle de perfil ------------------------------------------
		    $sql 	= "";
		    foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
		    if($list->nivel==1){
		    	if($usuario->id_perfil == 31){ //-- televendas ----------------------------
		    		$where .= " and e.id_regioestelevendas in (".RegioesBO::listaRegioesusuarios(1).")";
		    	}elseif(($usuario->id_perfil == 4) || ($usuario->id_perfil == 5)){
		    		$where .= " and (e.regiao in (".RegioesBO::listaRegioesusuarios(0).") || e.id_regioestelevendas in (".RegioesBO::listaRegioesusuarios(1)."))";
		    	}else{
		    		$where .= " and e.regiao in (".RegioesBO::listaRegioesusuarios(0).")";
		    	}
		    }elseif($list->nivel==0){
		    	$where .= " and e.id_clientes = ".$usuario->id_cliente;
		    }
		    	
		    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		    $db->setFetchMode(Zend_Db::FETCH_OBJ);
		    
		    $select = $db->select();
		    $select->from(array('e'=>'tb_contatosemp','*'),
	    		array('e.id as idemp','e.empresa','uf.uf as ufemp', 'e.tipo_par', 'e.vercontato as vmcontato','e.verfilial as vmfilial',
	    				'e.verificado as verificadom','e.id_clientes','e.data_interacao','e.data_venda','e.id_matriz'))
	    				->joinLeft(array('c'=>'tb_cidades'), 'c.id = e.cidade')
	    				->joinLeft(array('uf'=>'tb_estados'), 'uf.id = c.id_estados')
	    				->where("e.status = true ".$where)
	    				->order("e.empresa asc");
		    	
		    $stmt = $db->query($select);	     
		    
		    return $objEmp = $stmt->fetchAll();
		}
		
		/**
		 * buscaContatos
		 *
		 * @author programador
		 * @data 18/03/2014
		 * @tags @param unknown_type $var
		 * @tags @param unknown_type $tipo
		 * @tags @param unknown_type $tpemp
		 * @tags @return Ambigous <multitype:, multitype:mixed Ambigous <string, boolean, mixed> >
		 */
		
		function buscaContatos($var){
			$usuario = Zend_Auth::getInstance()->getIdentity();
			$bo		= new ContatosModel();
			$bol	= new CampanhasModel();
			
			$where = "";
			//--- busca contatos nao validados -----------------------------------------
			if(isset($var['quarentena'])) $where = " and ((e.verificado = 0))";
								
			//--- busca pelo nome da empresa -------------------------------------------
			if(!empty($var['empresa'])){ 
			    $where .= " and (ec.nome like '%".$var['empresa']."%' || 
			            e.empresa like '%".$var['empresa']."%' || 
			            e.id = '".preg_replace("/[^0-9]/", "", $var['empresa'])."' ||
			            ec.id = '".preg_replace("/[^0-9]/", "", $var['empresa'])."' ||
			            c.nome like '%".$var['empresa']."%')";
			}
			//--- busca pelo ID da empresa ---------------------------------------------
			if(!empty($var['idemp'])) $where .= " and e.id = ".$var['idemp'];
			 
			if(!empty($var['uf']) and ($var['uf']!="todos")) $where .= " and e.uf = '".$var['uf']."'";
			 
			if(!empty($var['parceiros']) || !empty($var['alvos']) || !empty($var['mercado'])){
				if(!empty($var['parceiros'])) 	$par = "Parceiro";
				if(!empty($var['alvos'])) 		$alvo = "Alvo";
				if(!empty($var['mercado'])) 	$mer = "Mercado";
		
				$where .= " and e.tipo_par in ('".$par."','".$alvo."','".$mer."')";
			}
		
			if(!empty($var['buscaregioes']) and $var['buscaregioes'] != 0) $where .= " and e.regiao = " .$var['buscaregioes'];
			if(!empty($var['televenda']) and $var['televenda'] != 0) $where .= " and e.id_regioestelevendas = " .$var['televenda'];
			 
		
			if(isset($var['idmatriz'])) $where = " and e.id_matriz = '".$var['idmatriz']."'";
		
			//--- Controle de perfil ------------------------------------------
			$sql 	= "";
			foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
			if($list->nivel==1){
				if($usuario->id_perfil == 31){ //-- televendas ----------------------------
					$where .= " and e.id_regioestelevendas in (".RegioesBO::listaRegioesusuarios(1).")";
				}elseif(($usuario->id_perfil == 4) || ($usuario->id_perfil == 5)){
					$where .= " and (e.regiao in (".RegioesBO::listaRegioesusuarios(0).") || e.id_regioestelevendas in (".RegioesBO::listaRegioesusuarios(1)."))";
				}else{
					$where .= " and e.regiao in (".RegioesBO::listaRegioesusuarios(0).")";
				}
			}elseif($list->nivel==0){
				$where .= " and e.id_clientes = ".$usuario->id_cliente;
			}
			
			
			$sessaorel = new Zend_Session_Namespace('Contatosrel');
			if(isset($sessaorel->contatossel) and $sessaorel->contatossel != "0" and $sessaorel->contatossel != ""){
			    $campanha = $bol->fetchRow("id = '".$sessaorel->contatosrelid."'");
			    $where ." and ec.id in (".$campanha->contatos.")";			    			    
			}
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
		
			$select = $db->select();
			$select->from(array('e'=>'tb_contatosemp'),array('ec.nome as nomecontato','ec.id as idcontato','ec.verificado as verificadocontato',
			    'c.uf as ufcontato','ce.uf as ufempresa','c.nome as cidadecontato','ce.nome as cidadeempresa','e.*'))
				->join(array('ec'=>'tb_contatos'), 'ec.id_emp = e.id')
				->joinLeft(array('c'=>'tb_cidades'), 'c.id = e.cidade')
				->joinLeft(array('uf'=>'tb_estados'), 'uf.id = c.id_estados')
				->joinLeft(array('ce'=>'tb_cidades'), 'ce.id = ec.id_cidade')
				->joinLeft(array('ufe'=>'tb_estados'), 'ufe.id = ce.id_estados')
				->where("e.status = true and ec.sit = true ".$where)
				->order("e.id desc");
			 
			$stmt = $db->query($select);
		
			return $objEmp = $stmt->fetchAll();
		}
		
		/**
		* @$var[empresa] 		= nome da empresa
		* @$var[emp] 			= id da empresa
		* @$var[uf] 			= estado
		* @$var[parceiro] 		= filtro de busca por tipo de parceiro
		* @$var[alvo] 			= filtro de busca por tipo de parceiro
		* @$var[mercado] 		= filtro de busca por tipo de parceiro
		* @$var[buscaregioes] 	= filtro por regiao do contato
		* @$tipo = tipo de pesquisa: 1 - Empresas em quarentena; defult - Empresas
		* */
		function listaEmpresas($var,$tipo=""){
			
			$objEmp = ContatosBO::buscaContatosemp($var,$tipo);	
		    
			if(count($objEmp) > 0){
				?>							
				<div class="widget first">
		 			<table style="width: 100%" class="tableStatic" id="listcontatos">
		            	<thead>
		                	<tr>
		                        <td width="15%">Id</td>
		                        <td width="50%">Nome</td>
		                        <td width="">Tipo</td>
		                        <td width="">Estado</td>
		                        <td width="15%">Opções</td>
		                    </tr>
		                </thead>
		                <tbody>
		               
						<?php 
						$cor=0;
						$empresassit = "";
						
						$pag = (isset($var['page']))? $var['page'] : 1;
												
						Zend_Paginator::setDefaultScrollingStyle('Sliding');
						Zend_View_Helper_PaginationControl::setDefaultViewPartial('index/paginatorajax.phtml');
						$paginator = Zend_Paginator::factory($objEmp);
						$currentPage = $pag;
						$paginator	->setCurrentPageNumber($currentPage)
									->setItemCountPerPage(10);
						
						foreach($paginator as $lista){
							$cor++;
					    	$bg = "";
				            if((strtotime($lista->data_venda) < strtotime("-60 days")) and (strtotime($lista->data_interacao) < strtotime("-15 days"))):
				            	$bg = "background-color: #FFE0D7";
				            elseif((strtotime($lista->data_venda) < strtotime("-60 days"))):
				            	$bg = "background-color: #FFF7CF";
				            endif;
				            ?>
							<tr style="<?php echo $bg?>">
				                <td   style="text-align: center;" >
				                   <?php if($lista->id_matriz) echo "F"; else echo "M"?><?php echo $lista->idemp?><?php if($lista->verificadom == 0) echo " (NV)";?>
				                   <?php if($lista->id_matriz){ ?>
				                   		<br /><a target="_blank" href="/admin/cadastro/contatosempcad/empresa/<?php echo md5($lista->id_matriz)?>">M<?php echo $lista->id_matriz?></a>
				                   <?php } ?>				                    
				                </td>
				                <td align="left" >
				                   <span style=" text-transform: uppercase;">
				                   <?php echo ($lista->empresa)?>
				                   </span>
				                   <br>
				                   <span style="font-size: 75%">
				                   <?php if(!$lista->id_matriz){ ?>
				                   <a href="javascript:void(0);" class="btnFiliais" rel="<?php echo $lista->idemp?>" >Filiais</a>
				                   &nbsp;  &nbsp;  &nbsp;
				                   <?php } ?>
				                   <a href="javascript:void(0);" class="btnContatos" rel="<?php echo $lista->idemp?>" >Contatos</a></span>                 
				                </td>
				                <td   style="text-align: center;" >
				                   <?php if($lista->id_clientes) echo "Parceiro"; else echo $lista->tipo_par?>
				                </td>
				                <td   style="text-align: center;" >
				                   <?php echo $lista->ufemp?>
				                </td>
				                <td   style="text-align: center;" >
				                	<a target="_blank" href="/admin/cadastro/contatosempcad/empresa/<?php echo md5($lista->idemp)?>"><img src="/public/sistema/imagens/icons/middlenav/magnify.png" width="16" title="Editar"></a>
					                <?php if($lista->id_clientes == NULL): ?> &nbsp; 
									<a href="javascript:void(0)" rel="<?php echo $lista->idemp?>" class="btnExcluimatriz" ><img src="/public/sistema/imagens/icons/middlenav/close.png" width="15" border="0" title="Remover"></a>
									<?php endif; ?>
				                </td>
				            </tr>
				            <tr id="contatos_<?php echo $lista->idemp?>" style="display: none">
				                <td colspan="5">&nbsp;</td>
				            </tr>   
				            <tr id="filiais_<?php echo $lista->idemp?>" style="display: none">
				                <td colspan="5">&nbsp;</td>
				            </tr>
				            <?php 			
						}
						?>        
					</table>
				</div>
				
				<div style="margin-top: 20px">
					<?php 
					if ( count($paginator) > 0) :
		            	echo $paginator;
		            endif;
				    ?>
				</div>	
				
				<input type="hidden" value="<?php echo $pag?>" id="pag">
							
	        <?php 
			}else{
				?><td colspan="5"><div style="text-align: center; margin-top: 30px">Nenhuma registro encontrado</div></td><?php 
			}
		}
		
		function listaFiliais($var){
				
			$objEmp = ContatosBO::buscaContatosemp($var);
		
			if(count($objEmp) > 0){
				?>
				<td colspan="5">
				<div style="margin-bottom: 5px"><a href="javascript:void(0)" rel="<?php echo $var['idmatriz']?>" class="btnIconLeft btnNovofilial"><img src="/public/sistema/imagens/icons/dark/add.png" alt="" class="icon" /><span>Nova filial</span></a></div>
				
				<div class="widget" style="margin-top: 0px">
		 			<table style="width: 100%" class="tableStatic" >
		            	<thead>
		                	<tr>
		                        <td width="15%">Id</td>
		                        <td width="50%">Nome</td>
		                        <td width="">Tipo</td>
		                        <td width="">Estado</td>
		                        <td width="15%">Opções</td>
		                    </tr>
		                </thead>
		                <tbody>
		               
						<?php 
						$cor=0;
						$empresassit = "";
						
						foreach($objEmp as $lista){
							$cor++;
					    	$bg = "";
				            if((strtotime($lista->data_venda) < strtotime("-60 days")) and (strtotime($lista->data_interacao) < strtotime("-15 days"))):
				            	$bg = "background-color: #FFE0D7";
				            elseif((strtotime($lista->data_venda) < strtotime("-60 days"))):
				            	$bg = "background-color: #FFF7CF";
				            endif;
				            ?>
							<tr style="<?php echo $bg?>">
				                <td   style="text-align: center;" >
				                   F<?php echo $lista->idemp?><?php if($lista->verificadom == 0) echo " (NV)";?>
				                </td>
				                <td align="left" >
				                   <span style=" text-transform: uppercase;">
				                   <?php echo ($lista->empresa)?>
				                   </span>
				                   <br>
				                   <span style="font-size: 75%">
				                   <a href="javascript:void(0);" class="btnContatos" rel="<?php echo $lista->idemp?>" >Contatos</a></span>                 
				                </td>
				                <td   style="text-align: center;" >
				                   <?php if($lista->id_clientes) echo "Parceiro"; else echo $lista->tipo_par?>
				                </td>
				                <td   style="text-align: center;" >
				                   <?php echo $lista->ufemp?>
				                </td>
				                <td   style="text-align: center;" >
				                	<a target="_blank" href="/admin/cadastro/contatosempcad/empresa/<?php echo md5($lista->idemp)?>"><img src="/public/sistema/imagens/icons/middlenav/magnify.png" width="16" title="Editar"></a>
					                <?php if($lista->id_clientes == NULL): ?> &nbsp; 
									<a href="javascript:void(0)" rel="<?php echo $lista->idemp?>|<?php echo $lista->id_matriz?>" class="btnExcluifilial" ><img src="/public/sistema/imagens/icons/middlenav/close.png" width="15" border="0" title="Remover"></a>
									<?php endif; ?>
				                </td>
				            </tr>
				            <tr id="contatos_<?php echo $lista->idemp?>" style="display: none">
				                <td colspan="5">&nbsp;</td>
				            </tr>   
				            
				            <?php 			
						}
						?>        
					</table>
				</div>
				</td>		
	        <?php 
			}else{
				?><td colspan="5">
				<div style="margin-bottom: 5px"><a href="javascript:void(0)" rel="<?php echo $var['idmatriz']?>" class="btnIconLeft btnNovofilial"><img src="/public/sistema/imagens/icons/dark/add.png" alt="" class="icon" /><span>Nova filial</span></a></div>
				<div style="text-align: center; ">Nenhuma registro encontrado</div></td><?php 
			}
		}
		
		function listaContatos($val){
			$bo = new ContatosModel();
		    
			$usuario = Zend_Auth::getInstance()->getIdentity();
			 
			//--- busco perfil do usuario que estah logado -------------------------------------
			foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 11) as $listPer);
			
			$where = "";
			if(isset($val['idemp'])){
				$where = " and id_emp = '".$val['idemp']."'";
			}

			$objcontatos = ContatosBO::buscaContatos($val);
			
			if(isset($val['paginacao'])){
				?>
				<div class="widget first">
	 			<table style="width: 100%" class="tableStatic" >
	            	<thead>
	                	<tr>
	                        <td width="15%">Id</td>
	                        <td width="50%">Nome</td>
	                        <td width="">Grupo</td>
	                        <td width="15%">Opções</td>
	                    </tr>
	                </thead>
					<tbody>
					<?php 
					
					$pag = (isset($val['page']))? $val['page'] : 1;
					
					Zend_Paginator::setDefaultScrollingStyle('Sliding');
					Zend_View_Helper_PaginationControl::setDefaultViewPartial('index/paginatorajax.phtml');
					$paginator = Zend_Paginator::factory($objcontatos);
					$currentPage = $pag;
					$paginator	->setCurrentPageNumber($currentPage)
					->setItemCountPerPage(15);
					
					foreach ($paginator as $listcom){
			    		?>
	    				<tr >
	    					<td style="padding: 0; padding-left: 3px; text-align: center;" >
	    						C<?php echo $listcom->idcontato?><?php if($listcom->verificadocontato == 0) echo " (NV)";?>
							</td>
	    					<td  style="padding: 0; padding-left: 3px;"  align="left" >
	    						<?php echo ($listcom->nomecontato)?>
	    					</td>
	    					<td  style="padding: 0; padding-left: 3px; text-align: center;"  align="left" >
		    					<?php 
		    					foreach (ContatosBO::listaGrupointeresse() as $listg):
		    						if($listcom->id_ginteresse == $listg->id) echo $listg->nome;			    						 
		    					endforeach;
		    					?>		    						
	    					</td> 
							<td  style="padding: 0; text-align: center; padding: 3px"  >
	    						<a href="javascript:void(0)" rel="<?php echo $listcom->idcontato?>" class="btnContato" ><img src="/public/sistema/imagens/icons/middlenav/magnify.png" width="16" title="Visualizar"></a> &nbsp;
	    						<?php if($listPer->editar == 1): ?>
	    						<a href="javascript:void(0)" rel="<?php echo $listcom->idcontato?>|<?php echo $listcom->id_emp?>" class="btnContatoexcluir" ><img src="/public/sistema/imagens/icons/middlenav/close.png" width="15" border="0" title="Remover"></a>
	    						<?php endif; ?>								                
	    					</td>
	    				</tr>
			    		<?php 
			    	}
			    	?>
	    			</tbody>
    			</table>
    			</div>
    			
    			<div style="margin-top: 20px">
					<?php 
					if ( count($paginator) > 0) :
		            	echo $paginator;
		            endif;
				    ?>
				</div>
				
				<input type="hidden" value="<?php echo $pag?>" id="pag">
    			
    			<?php
			}else{
						
				if(count($objcontatos)>0){
					?>
					<td colspan="5">
					<div style="margin-bottom: 5px"><a href="javascript:void(0)" rel="<?php echo $val['idemp']?>" class="btnIconLeft btnNovocontato"><img src="/public/sistema/imagens/icons/dark/add.png" alt="" class="icon" /><span>Novo contato</span></a></div>
					<div class="widget" style="margin-top: 0px">
		 			<table style="width: 100%" class="tableStatic" >
		            	<thead>
		                	<tr>
		                        <td width="15%">Id</td>
		                        <td width="50%">Nome</td>
		                        <td width="">Grupo</td>
		                        <td width="15%">Opções</td>
		                    </tr>
		                </thead>
						<tbody>
						<?php 
				    	foreach ($objcontatos as $listcom){
				    		?>
		    				<tr >
		    					<td style="padding: 0; padding-left: 3px; text-align: center;" >
		    						C<?php echo $listcom->idcontato?><?php if($listcom->verificadocontato == 0) echo " (NV)";?>
								</td>
		    					<td  style="padding: 0; padding-left: 3px;"  align="left" >
		    						<?php echo ($listcom->nomecontato)?>
		    					</td>
		    					<td  style="padding: 0; padding-left: 3px; text-align: center;"  align="left" >
			    					<?php 
			    					foreach (ContatosBO::listaGrupointeresse() as $listg):
			    						if($listcom->id_ginteresse == $listg->id) echo $listg->nome;			    						 
			    					endforeach;
			    					?>		    						
		    					</td> 
								<td  style="padding: 0; text-align: center; padding: 3px"  >
		    						<a href="javascript:void(0)" rel="<?php echo $listcom->idcontato?>" class="btnContato" ><img src="/public/sistema/imagens/icons/middlenav/magnify.png" width="16" title="Visualizar"></a> &nbsp;
		    						<?php if($listPer->editar == 1): ?>
		    						<a href="javascript:void(0)" rel="<?php echo $listcom->idcontato?>|<?php echo $listcom->id_emp?>" class="btnContatoexcluir" ><img src="/public/sistema/imagens/icons/middlenav/close.png" width="15" border="0" title="Remover"></a>
		    						<?php endif; ?>								                
		    					</td>
		    				</tr>
				    		<?php
				    	}
				    	?>
		    			</tbody>
	    			</table>
	    			</div>
	    			</td>
	    			<?php
			    }else{
					?><td colspan="5">
					<div style="margin-bottom: 5px"><a href="javascript:void(0)" rel="<?php echo $val['idemp']?>" class="btnIconLeft btnNovocontato"><img src="/public/sistema/imagens/icons/dark/add.png" alt="" class="icon" /><span>Novo contato</span></a></div>
					<div style="text-align: center; margin-top: 30px">Nenhuma registro encontrado</div></td><?php
			   	}
		   	}
		}
		
	
		function listaMatrizselect(){
	    	?>
	    	<div style="text-align: left;">
			Selecione a matriz:<br/>
			<div class="styled-select" style="width: 300px">
				<select id="matrizes" name="matrizes" style="width: 322px">
				<option value='0'>Selecione</option>
				<?php 
				foreach (ContatosBO::buscaContatosemp("","","m") as $empresas){
					?><option value="<?php echo $empresas->idemp?>" ><?php echo $empresas->empresa?> - M<?php echo $empresas->idemp?></option><?php 
				}
				?>
				</select>							
			</div>
			
			<input type="button" value="Definir filial" class="greenBtn" id="btnSfilial" style="margin-top: 10px">
			</div>
	    	<?php 
		    
		}
		
		
		
		
		
		
		function corrigeContatos(){
		    $bo		= new ContatosModel();
		    $boe 	= new ContatosempModel();
		    $bof	= new ContatosfilialModel();
		    
		    $usuario = Zend_Auth::getInstance()->getIdentity();
		    /* 
		    foreach ($bof->fetchAll("status = 0") as $filiais){
		    	 
		    	$array = array();
		    
		    	$array['empresa']				= $filiais->empresa;
		    	$array['cidade']				= $filiais->cidade;
		    	$array['endereco']				= $filiais->endereco;
		    	$array['bairro']				= $filiais->bairro;
		    	$array['cep']					= $filiais->cep;
		    	$array['status']				= 0;
		    	$array['verificado']			= $filiais->verificado;
		    	$array['regiao']				= $filiais->regiao;
		    	$array['id_matriz']				= $filiais->id_matriz;
		    	$array['id_clientes']			= $filiais->id_clientes;
		    	$array['vercontato']			= $filiais->vercontato;
		    	$array['id_clientesgrupos']		= $filiais->id_clientesgrupos;
		    	$array['ver']					= $filiais->id;
		    	 
		    	try{
		    		$idfil = $boe->insert($array);
		    		$bo->update(array('ID_EMP' => $idfil), "ID_EMP = '".$filiais->id."'");
		    	}catch (Zend_Exception $e){
		    		echo $filiais->id.": ".$e->getMessage();
		    		echo "<br />";
		    	}
		    } */
		    
		    
		    /* foreach ($bo->fetchAll() as $contatos){
		        
		        $sit = ($contatos->sit == false) ? true : false; 
		    	$bo->update(array('sit' => $sit), "id = ".$contatos->id);
		    }	 	
		    
		    
		    foreach ($boe->fetchAll() as $contatosemp){
		    
		    	$sit = ($contatosemp->status == false) ? true : false;
		    	$boe->update(array('status' => $sit), "id = ".$contatosemp->id);
		    } */
		    
		}
		
		
		
		function contatosQuarentena($var){
		     
		    $sessaobusca = new Zend_Session_Namespace('Contatosquar');
		    
		    if(!empty($var['buscaquarentena'])):
		    	if((substr($var['buscaquarentena'], 0,1) == "M")||(substr($var['buscaquarentena'], 0,1) == "m")):
					$where = " || g.id_contatosemp = '".substr($var['buscaquarentena'],1)."'";
		    	elseif((substr($var['buscaquarentena'], 0,1) == "F")||(substr($var['buscaquarentena'], 0,1) == "f")):
					$where = " || g.id_contatosempfilial = '".substr($var['buscaquarentena'],1)."'";
		    	elseif((substr($var['buscaquarentena'], 0,1) == "C")||(substr($var['buscaquarentena'], 0,1) == "c")):
		    		$where = " || g.id_contatos = '".substr($var['buscaquarentena'],1)."'";
		    	endif;
		    	
		    	$where = " and (g.nome like '%".$var['buscaquarentena']."%' ".$where.")";
		    
		    	$sessaobusca->where = $where;
		    else:
		    	$where = $sessaobusca->where;
		    endif;
		    
		    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		    $db->setFetchMode(Zend_Db::FETCH_OBJ);
		    
		    $select = $db->select();
		    	
		    $select->from(array('g'=>'tb_contatosagrupados','*'), array('g.*',
		            'e.empresa as empmatriz','e.tipo_par', 'e.vercontato as vmcontato','e.verfilial as vmfilial','e.verificado as verificadom',
    				'f.empresa as empfilial','f.verificado as verificafildom','f.vercontato as vfcontato','c.nome as nomecontato'))
    			
    			->joinLeft(array('f'=>'tb_contatosempfilial'), 'f.id = g.id_contatosempfilial  and (f.verificado = 0 || f.vercontato = 0)')
    			->joinLeft(array('e'=>'tb_contatosemp'), 'e.id = g.id_contatosemp and (e.verificado = 0 || e.vercontato = 0 || e.verfilial = 0)')
    			->joinLeft(array('c'=>'tb_contatos'), 'c.id = g.id_contatos and c.verificado = 0')
    			
    			->where("((f.id is not null and e.id is null and c.id is null) ||
    			         (f.id is null and e.id is not null and c.id is null) || 
    			         (f.id is null and e.id is null and c.id is not null))   
    			        ".$where)
    			->order("g.nome");
		    	
		    
		    $stmt = $db->query($select);
		    return $stmt->fetchAll();
		    
		}
		
		function copiaContatos(){
		    $bo		= new ContatosModel();
		    $bon	= new ContatosnovoModel();
		    $boe	= new ContatosempModel();
		    $bof	= new ContatosfilialModel();
		    $boa	= new ContatosagrupadosModel();
		    
		    foreach ($boe->fetchAll("id_clientes is null") as $empresas):
		    
			    if(count($bon->fetchAll("TIPO = 0 and ID_EMP = ".$empresas->id))>0):
			    	$vercontato = 0;
			    else:
			    	$vercontato = 1;
			    endif;
		    
			    if(count($bof->fetchAll("id_matriz = ".$empresas->id))>0):
			    	$verfilial = 0;
			    else:
			    	$verfilial = 1;
			    endif;
			    
			    $arraym = array('vercontato' => $vercontato, 'verificado' => 0, 'verfilial' => $verfilial);
			    $boe->update($arraym, "id = ".$empresas->id);
			    
		    	$array = array('nome' => $empresas->empresa, 'id_contatosemp' => $empresas->id);
		    	$boa->insert($array);
		    endforeach;
		    
		    foreach ($bof->fetchAll("id_clientes is null") as $empresas):
		    	
		    	if(count($bon->fetchAll("TIPO = 1 and ID_EMP = ".$empresas->id))>0):
					$vercontato = 0;
		    	else:
		    		$vercontato = 1;
		    	endif;
		    
		    	$arrayf = array('vercontato' => $vercontato, 'verificado' => 0);
		    	$bof->update($arrayf, "id = ".$empresas->id);
		    	
		    	$array = array('nome' => $empresas->empresa, 'id_contatosempfilial' => $empresas->id);
		    	$boa->insert($array);
		    endforeach;
		    
		    foreach ($bon->fetchAll() as $contatos):
			    $array = array('nome' => $contatos->NOME, 'id_contatos' => $contatos->ID);
			    $boa->insert($array);
		    endforeach;
		}
		
		
		function listaMatrizendereco($var){
				
			if(!empty($var['uf']) and ($var['uf']!="todos")):
		   		$where .= " and e.uf = '".$var['uf']."'";
		   	endif;		   	
		   	
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
						
			$select = $db->select();
			
			$select->from(array('e'=>'tb_contatosemp','*'), array('*','c.nome as ncidade','p.nome as npais'))
			        ->joinLeft(array('c'=>'tb_cidades'), 'c.id = e.cidade')
			        ->joinLeft(array('p'=>'tb_paises'), 'p.id = e.pais')
			        ->where("e.status = true ".$where)
			        ->order("e.id desc");
			  
			$stmt = $db->query($select);
						
			return $stmt->fetchAll();	
		}
		
		function gravaEmpresa($params){
			$boa	= new ContatosModel();
			$bo		= new ContatosempModel();
			$bog	= new ContatosagrupadosModel();
			$usuario = Zend_Auth::getInstance()->getIdentity();
						
			$array['verificado']		= true;
			
			if(!isset($params['editparc'])){
				$array['empresa']			= $params['empresa'];
				$array['uf']				= $params['uf'];
				$array['cidade']			= $params['cidade'];
				$array['endereco']			= $params['rua'];
				$array['bairro']			= $params['bairro'];
				$array['cep']				= $params['cep'];
				$array['pais']				= $params['pais'];
				$array['tipo_par']			= $params['tipo'];
				$array['verificado']		= false;
				
				$array['regiao']	       		= ($params['regiao'] != 0) ? $params['regiao'] : null;
				$array['id_regioestelevendas']	= ($params['regioestelevendas'] != 0) ? $params['regioestelevendas'] : null;
			}
			
			$array['id_clientesgrupos']		= ($params['ginteresse'] != 0) ? $params['ginteresse'] : null;
			$array['obsmeta']				= $params['obsmeta'];
			$array['meta']					= str_replace(",", ".", str_replace(".", "", $params['meta']));
			$array['status']				= true;
			$array['data_abertura']			= substr($params["dtabertura"],6,4).'-'.substr($params["dtabertura"],3,2).'-'.substr($params["dtabertura"],0,2);
			
			if($params['idmatriz']!=NULL)  $array['id_matriz'] = $params['idmatriz'];
							
			if(!empty($params['idempresa'])):
				$bo->update($array, "id = ".$params['idempresa']);
				$id = $params['idempresa'];
				
				//-- grava nos contatos agrupados ----------------------------------------------------
				$arrayagrega = array('nome'	=> $params['empresa']);
				$bog->update($arrayagrega,'id_contatosemp = '.$params['idempresa']);
				
				LogBO::cadastraLog("Cadastro/Empresa Contatos",4,$usuario->id,$id,"Empresa ID ".$id);
			else:
				$array['data_cad']			= date("Y-m-d");
				$array['vercontato']		= 1; //-- marca validacao dos contatos, visto q ainda nao existem ------------------------
				$array['verfilial']			= 1; //-- marca validacao das filiais, visto q ainda nao existem ------------------------
				$id = $bo->insert($array);
				
				//-- grava nos contatos agrupados ----------------------------------------------------
				$arrayagrega = array('nome'	=> $params['empresa'], 'id_contatosemp' => $id);
				$bog->insert($arrayagrega);
				
				LogBO::cadastraLog("Cadastro/Empresa Contatos",2,$usuario->id,$id,"Empresa ID ".$id);
			endif;
			
			return $id;
		}
		
		function agregaParceiro($params){
			$boa	= new ContatosModel();
			$bo		= new ContatosempModel();
			$bog	= new ContatosagrupadosModel();
			$usuario = Zend_Auth::getInstance()->getIdentity();
		
			if($params['novaempresa']){
				$busca['idparceiro']		= $params['novaempresa'];
				foreach (ClientesBO::buscaParceiros("",$busca) as $empresa);
				foreach (ClientesBO::listaEnderecocomp($params['novaempresa'], 1) as $endereco);
				
				$array['verificado']			= true;
				$array['empresa']				= $empresa->EMPRESA;
				$array['uf']					= $endereco->nuf;
				$array['cidade']				= $endereco->id_cidades;
				$array['endereco']				= $endereco->LOGRADOURO." ".$endereco->numero." ".$endereco->complemento;
				$array['bairro']				= $endereco->BAIRRO;
				$array['cep']					= $endereco->CEP;
				$array['pais']					= $endereco->PAIS;
				$array['tipo_par']				= "Parceiro";
				$array['regiao']	       		= $empresa->ID_REGIOES;
				$array['id_regioestelevendas']	= $empresa->id_regioestelevendas;
				$array['id_clientesgrupos']		= $empresa->id_clientesgrupos;
				$array['status']				= true;
				$array['data_abertura']			= $empresa->data_abertura;
				$array['id_clientes'] 			= $params['novaempresa'];
				
				if(!empty($params['idempresa'])):
					$bo->update($array, "id = ".$params['idempresa']);
					$id = $params['idempresa'];
				
					//-- grava nos contatos agrupados ----------------------------------------------------
					$arrayagrega = array('nome'	=> $empresa->EMPRESA);
					$bog->update($arrayagrega,'id_contatosemp = '.$params['idempresa']);
				
					LogBO::cadastraLog("Cadastro/Empresa Contatos",4,$usuario->id,$id,"Empresa ID ".$id);
				else:
					$array['data_cad']			= date("Y-m-d");
					$array['vercontato']		= 1; //-- marca validacao dos contatos, visto q ainda nao existem ------------------------
					$array['verfilial']			= 1; //-- marca validacao das filiais, visto q ainda nao existem ------------------------
					$id = $bo->insert($array);
				
					//-- grava nos contatos agrupados ----------------------------------------------------
					$arrayagrega = array('nome'	=> $empresa->EMPRESA, 'id_contatosemp' => $id);
					$bog->insert($arrayagrega);
				
					LogBO::cadastraLog("Cadastro/Empresa Contatos",2,$usuario->id,$id,"Empresa ID ".$id);
				endif;
					
				return $id;
			}else{
				echo "erro";
			}
		}		
		
		function defineTipoempresa($params){
			$boa	= new ContatosModel();
			$bo		= new ContatosempModel();
			$usuario = Zend_Auth::getInstance()->getIdentity();
			if(!empty($params['idempresa'])){
				
				if(isset($params['idmatriz'])){
					$bo->update(array('id_matriz' => $params['idmatriz']), "id = ".$params['idempresa']);
				}else{
				    $bo->update(array('id_matriz' => NULL), "id = ".$params['idempresa']);
				}
				
				LogBO::cadastraLog("Cadastro/Empresa Contatos",4,$usuario->id,$params['idempresa'],"Empresa ID ".$params['idempresa']);
				
				return $params['idempresa'];
			}else{
				throw new Zend_Exception('O idempresa não foi enviado.');   
			}
		}
		
		function buscaEmpresa($idempresa){
			$boa	= new ContatosModel();
			$bo		= new ContatosempModel();
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			
			$select->from(array('e'=>'tb_contatosemp'), array('e.id as idempresa','*'))
					->joinLeft(array('c'=>'tb_cidades'), 'c.id = e.cidade')
					->joinLeft(array('uf'=>'tb_estados'), 'uf.id = c.id_estados')
					->where("md5(e.id) = '".$idempresa."'");
			
			$stmt = $db->query($select);
			return $stmt->fetchAll();			
		}
		
		
		function removeMatriz($var){
			$boa	= new ContatosModel();
			$bo		= new ContatosempModel();
			return $bo->update(array('status' => false), "md5(id) = '".$var."'");	
		}
		
		
		/* function gravaFilial($params){
			$boa	= new ContatosModel();
			$bo		= new ContatosfilialModel();
			$bog	= new ContatosagrupadosModel();
			$boe	= new ContatosempModel();
			
			$usuario = Zend_Auth::getInstance()->getIdentity();
						
			if(!empty($params['cidade'])):
				$cidade = $params['cidade'];
			else:
				$cidade = $params['cidadefil'];
			endif;
						
			$array['empresa']			= $params['empresa'];
			$array['cidade']			= $cidade;
			$array['endereco']			= $params['rua'];
			$array['bairro']			= $params['bairro'];
			$array['cep']				= $params['cep'];
			$array['cadastrado_por']	= $usuario->id;
			$array['data_alt']			= date("Y-m-d");
			$array['status']			= true;
			$array['verificado']		= 0;
			
			if($params['ginteresse'] != 0):
				$array['id_clientesgrupos']			= $params['ginteresse'];
			endif;
			
			if($params['novaempresa']!=""):
				$array['id_clientes'] 	= $params['novaempresa'];
				$array['verificado'] 	= true;
			endif;
			
			try {
				if(!empty($params['idempresa'])):
					$bo->update($array, "id = ".$params['idempresa']);
					
					//-- grava nos contatos agrupados ----------------------------------------------------
					$arrayagrega = array('nome'	=> $params['empresa']);
					$bog->update($arrayagrega,'id_contatosempfilial = '.$params['idempresa']);
				
					//-- marca empresa com contatos nao validado ------------------------------------
					$arrayv = array('verfilial' => 0);
					$boe->update($arrayv, "id = ".$params['idmatriz']);
					
					LogBO::cadastraLog("Cadastro/Filial Contatos",4,$usuario->id,$params['idempresa'],"Filial ID ".$params['idempresa']);
				else:
					$array['data_cad']			= date("Y-m-d");
					$array['id_matriz']			= $params['idmatriz'];
					$array['vercontato']		= 1; //-- marca validacao dos catatos, visto q ainda nao existem ------------------------
					
					$id = $bo->insert($array);
					
					//-- grava nos contatos agrupados ----------------------------------------------------
					$arrayagrega = array('nome'	=> $params['empresa'], 'id_contatosempfilial' => $id);
					$bog->insert($arrayagrega); 
					
					//-- marca empresa com contatos nao validado ------------------------------------
					$arrayv = array('verfilial' => 0);
					$boe->update($arrayv, "id = ".$params['idmatriz']);
					
					LogBO::cadastraLog("Cadastro/Filial Contatos",2,$usuario->id,$id,"Filial ID ".$id);
				endif;
				echo "sucesso";
			}catch (Zend_Exception $e){
				$boerro	= new ErrosModel();
				$dataerro = array('descricao' => $e->getMessage(), 'pagina' => 'ContatosBO::gravaFilial()');
				$boerro->insert($dataerro);
			    echo "erro";
			}
				
		} */
		
		function buscaFiliais($var){
			$bo		= new ContatosModel();
			$boe	= new ContatosempModel();
			return $boe->fetchAll("status = true and md5(id_matriz) = '".$var."'");			
		}
		
		
		
		/*@$var['tipo'] = 1 - Valida empresa, 2 - valida filial  e 3 - valida contato;
		 * 
		 * */
		function validarContatos($var){
			try{
		        $bon	= new ContatosModel();
		        $boe	= new ContatosempModel();
		        
				if($var['tipo'] == 1){
					$array = array('verificado' => 1);
					$boe->update($array, "id = '".$var['contato']."'");
					
				}elseif($var['tipo'] == 2){
					//-- valida filial ------------------------------------
					$array = array('verificado' => 1);
					$boe->update($array, "id = '".$var['contato']."'");
					
					//-- busca dados da filial ----------------------------
					$contato = $boe->fetchRow("id = '".$var['contato']."'");
					    
					//-- Verifica outras filiais da matriz ----------------------
					if(count($boe->fetchAll("id_matriz =".$contato->id_matriz))>0){
						$verfiliais = 1;
						foreach($boe->fetchAll("id_matriz =".$contato->id_matriz) as $filiais):
							//-- verifica se a filial esta validada ----------------------
							if($filiais->verificado == 0):
								$verfiliais = 0;
							else: //-- verifica se os contatos da filial estao validados --------------
								foreach($bon->fetchAll("id_emp =".$var['contato']) as $contatos):
									//-- verifica se o contato nao ta validado -----------------------
									if($contatos->verificado == 0) $verfiliais = 0;
								endforeach;
							endif;
						endforeach;
						
						//-- marca matriz com filiais nao validadas ----------
						$array = array('verfilial' => $verfiliais);
						$boe->update($array, "id = ".$contato->id_matriz);
					}
					
				}elseif($var['tipo'] == 3){
					//-- valida contato ------------------------------------
					$array = array('verificado' => 1);
					$bon->update($array, "id = '".$var['contato']."'");
					
					//-- busca dados do contato ----------------------------
					foreach($bon->fetchAll("id = '".$var['contato']."'") as $contato);
					
					//-- Verifica outros contatos da empresa ----------------------
					$vercontatos = 1;
					foreach($bon->fetchAll("id_emp = ".$contato->id_emp) as $contatos):
						//-- verifica se o contato nao ta validado -----------------------
						if($contatos->verificado == 0):
							$vercontatos = 0;
						endif;
					endforeach;
					
					//-- marca filiais e matriz com contatos nao validadas ----------
					$array = array('vercontato' => $vercontatos);
					$boe->update($array, "id = ".$contatos->id_emp);
										
				}
				
				echo 1;
			}catch (Zend_Exception $e){
			    return 'erro';
			}
			
		}
		
				
		//----- Contatos -----------------------
		/* Por bizonhice do antigo programador, o campo SITUACAO e STATUS das tabelas dos contatos 
		 * ficaram com valores false para declarar registros validos ---------------
		 * 
		 * */
		function gravaContatos($params){
			$bo		= new ContatosModel();
			$bog	= new ContatosagrupadosModel();
			$boe	= new ContatosempModel();
			
			$usuario = Zend_Auth::getInstance()->getIdentity();
			
			$array['nome']					= $params['nome'];
			$array['ddi1']					= $params['ddi1'];
			$array['ddd1']					= $params['ddd1'];
			$array['telefone1']				= $params['numero1'];
			$array['ddi2']					= $params['ddi2'];
			$array['ddd2']					= $params['ddd2'];
			$array['telefone2']				= $params['numero2'];
			$array['email']					= $params['email'];
			$array['id_ginteresse']			= ($params['grupo']!="0") ? $params['grupo'] : NULL;
			$array['nextel']				= $params['nextel'];
			$array['data_nascimento']		= substr($params["dtnasc"],6,4).'-'.substr($params["dtnasc"],3,2).'-'.substr($params["dtnasc"],0,2);
			$array['observacao']			= $params['obs'];
			$array['id_emp']				= $params['idempresa'];
			$array['sit']					= 1;
			$array['skype']					= $params['skype'];
			$array['mailing']				= 1;
			$array['verificado']			= 0;
			$array['tipoend']				= $params['endempresa'];
			$array['id_cidade']				= ($params['cidade']!="0") ? $params['cidade'] : NULL;
			$array['endereco']				= $params['rua'];
			$array['bairro']				= $params['bairro'];
			$array['cep']					= $params['cep'];
						
			try {
				if(!empty($params['idcontato'])):
					$bo->update($array, "id = ".$params['idcontato']);
				
					//-- grava nos contatos agrupados -----------------------------------------------
					$bog->update(array('nome'	=> $params['nome']),"id_contatos = '".$params['idcontato']."'");
					
					//-- marca empresa com contatos nao validado ------------------------------------
					$boe->update(array('vercontato' => 0), "id = ".$params['idempresa']);
					
					LogBO::cadastraLog("Cadastro/Contatos",4,$usuario->id,$params['idcontato'],"Contatos ID ".$params['idcontato']);
				else:
					$id = $bo->insert($array);
					
					//-- grava nos contatos agrupados -----------------------------------------------
					$bog->insert(array('nome'	=> $params['nome'], 'id_contatos' => $id));
					
					//-- marca empresa com contatos nao validado ------------------------------------
					$boe->update(array('vercontato' => 0), "id = ".$params['idempresa']);					
					
					LogBO::cadastraLog("Cadastro/Contatos",2,$usuario->id,$id,"Contatos ID ".$id);
				endif;
				
				echo 'sucesso';
			}catch (Zend_Exception $e){
				$boerro	= new ErrosModel();
				$dataerro = array('descricao' => $e->getMessage(), 'pagina' => 'ContatosBO::gravaContatos()');
				$boerro->insert($dataerro);
				echo 'erro';
			}				
		}
		
		function listaGrupointeresse(){
			$bo		= new ContatosModel();
			$bog	= new GruposinteresseModel();
			return $bog->fetchAll();
		}
				
		function removeContato($var){
			$bo	= new ContatosModel();
			$id = $bo->update(array('sit' => false), "id = '".$var['contato']."'");
		
			$usuario = Zend_Auth::getInstance()->getIdentity();
			LogBO::cadastraLog("Cadastro/Exclui Contatos",4,$usuario->id,$var['contato'],"Contato ID ".$var['contato']);
		}
		
		function contatoFom($params){
		    ini_set( 'display_errors', 0 );
		    $usuario = Zend_Auth::getInstance()->getIdentity();
		    $bo = new ContatosModel();
		    
		    //--- busca a empresa ------------------------------------------------
		    if(isset($params['empresa'])){
		    	foreach (ContatosBO::buscaEmpresa(md5($params['empresa'])) as $empresa);
		    }
		    
		    //--- busca o contato ------------------------------------------------
		    if(isset($params['contato'])){ 
				$objContatos = $bo->fetchRow("id = '".$params['contato']."'");
				foreach (ContatosBO::buscaEmpresa(md5($objContatos->id_emp)) as $empresa);
		    }
		    
		    //--- lista cidades do estado do contato -----------------------------
		    if(isset($objContatos) and $objContatos->id_cidade != NULL and $objContatos->tipoend == 0){
				$cidade 	= EstadosBO::buscaCidadesid(array('cidade' => $objContatos->id_cidade));
		    	$cidades 	= EstadosBO::buscaCidadesidestado($cidade->id_estados);
			}else{
		    	if(!empty($empresa->id_estados)): $cidades = EstadosBO::buscaCidadesidestado($empresa->id_estados); endif;		    	
		    }
		    ?>
		    	
		    <script type="text/javascript" src="/public/sistema/js/buscaajax/cadastroBuscauf.js"></script>
		    <style>table td{ text-align: left; }</style>
				
	    	<form name="gravacontatos" class="mainForm">
	    	<input type="hidden" name="idcontato" value="<?php echo $objContatos->id?>" />
	    	<input type="hidden" name="idempresa" value="<?php echo $empresa->idempresa?>" />
		    	
	    	<table id="tablecontatoedit" >
	    		<tr>
	    			<td style="width: 35%">Nome<br/><input type="text" name="nome" style="width: 250px" value="<?=$objContatos->nome?>"></td>
	    			<td >
	    				Grupo de interesse:<br>
	    				<div class="styled-select" style="width: 210px">
	    				<select name="grupo" id="grupo" style="width: 232px">
	    					<option value="">Selecione</option>
	    					<?php 
	    					foreach (ContatosBO::listaGrupointeresse() as $listg):
	    						?>
	    						<option <?php if($objContatos->id_ginteresse == $listg->id) echo 'selected="selected"'; ?> value="<?=$listg->id?>"><?=$listg->nome?></option>
	    						<?php 
	    					endforeach;
	    					?>
	    				</select>
	    				</div>
	    			</td>
	    			<td align="left" >Dt Nascimento:<br><input value="<?php if(!empty($objContatos->data_nascimento)) echo substr($objContatos->data_nascimento,8,2)."/".substr($objContatos->data_nascimento,5,2)."/".substr($objContatos->data_nascimento,0,4)?>" type="text" name="dtnasc" class="data datepicker" style="width: 100px" ></td>				
	    		</tr>
	    		<tr>
	    			<td >E-mail<br/><input type="text" name="email" value="<?php echo $objContatos->email?>" style="width: 200px"></td>			
	    			<td>Skype:<br><input type="text" name="skype" value="<?php echo $objContatos->skype?>" style="width: 150px"></td>
	    			<td >Nextel:<br><input type="text" name="nextel" value="<?php echo $objContatos->nextel?>" style="width: 150px"></td>
	    		</tr>
	    		
	    		<tr>
	    			<td align="left">
	    				Telefone: <br>
	    				<input type="text" name='ddi1' maxlength="4" value='<?php echo $objContatos->ddi1?>' style="width: 20px" >
	    				<input type="text" name='ddd1' maxlength="2" value='<?php echo $objContatos->ddd1?>' style="width: 20px" >
	    				<input type="text" name='numero1'  value='<?php echo $objContatos->telefone1?>'  style="width: 80px" >	    				
	    			</td>
	    			<td align="left" colspan="2">
	    				Telefone: <br>
	    				<input type="text" name='ddi2' maxlength="4" value='<?php echo $objContatos->ddi2?>' style="width: 20px" >
	    				<input type="text" name='ddd2' maxlength="2" value='<?php echo $objContatos->ddd2?>' style="width: 20px" >
	    				<input type="text" name='numero2'  value='<?php echo $objContatos->telefone2?>'  style="width: 80px" >	    				
	    			</td>
	    		</tr>
	    		<tr>
	    			<td colspan="3" style="padding-top: 10px">
	    				<?php 
	    				$check = "";
	    				if(!isset($objContatos)){
							$check = "checked='checked'";
						}elseif($objContatos->tipoend == 1){ 
		    				$check = "checked='checked'";	
		    			}?>
	    				<input type="checkbox" name="endempresa" value="1" <?php echo $check?>> O mesmo endereço da empresa
	    			</td>
	    		</tr>
	    		<tr>
	    		<td colspan="3">
	    			<table style="width: 100%">
	    				<tr>
	    					<td >Endereço:<br>
	    						<input value="<?php echo $objContatos->endereco?>" type="text" name="rua" style="width: 250px">
	    						<input value="<?php echo $empresa->endereco?>" type="hidden" name="ruaemp">
	    					</td>
	    					<td >Bairro:<br>
	    						<input value="<?php echo $objContatos->bairro?>" type="text" name="bairro" style="width: 150px">
	    						<input value="<?php echo $empresa->bairro?>" type="hidden" name="bairroemp">
	    					</td>  
	    					<td >CEP:<br>
	    						<input value="<?php echo $objContatos->cep?>" type="text" name="cep" style="width: 100px" class="cep">
	    						<input value="<?php echo $empresa->cep?>" type="hidden" name="cepemp" style="width: 100px" class="cep">
	    					</td>
	    				</tr>
	    				<tr>
	    					<td >Estado:<br>
	    						<div class="styled-select" style="width: 180px">
	    							<select id="uf" name="uf" onchange="buscaEstados(this.value,'2','cidade');" style="width: 202px">
	    								<option value="0">Selecione</option>
	    								<?php 
	    								foreach (EstadosBO::buscaEstados(1) as $list):		
	    									?>
	    									<option value='<?php echo $list->id?>' <?php if($cidade->id_estados == $list->id): ?> selected="selected" <?php endif; ?>><?=$list->nome?></option>
	    									<?php 
	    								endforeach;
	    								?>
	    							</select>
	    						</div>		
	    						<input value="<?php echo $empresa->id_estados?>" type="hidden" name="ufemp">				
	    					</td>										
	    					<td >
	    						Cidade:<br>
	    		     			<div id="cidade">
	    		     				<div class="styled-select" style="width: 260px">
	    								<select id="cidade" name="cidade" style="width: 282px">
	    									<option value="0">Selecione</option>
	    									<?php 
	    									foreach ($cidades as $list):		
	    										?>
	    										<option value='<?php echo $list->id?>' <?php if($objContatos->id_cidade == $list->id): ?> selected="selected" <?php endif; ?>><?=$list->nome?></option>
	    										<?php 
	    									endforeach;
	    									?>
	    								</select>
	    							</div>
	    							<input value="<?php echo $empresa->cidade?>" type="hidden" name="cidadeemp">
	    						</div>
	    					</td>	
	    					<td>&nbsp;</td>								 
	    				</tr>
	    			</table>
	    			</td>
	    		</tr>
	    		<tr >
	    			<td colspan="3" >
	    			Observação:<br>
	    			<textarea rows="3" cols="60" name="obs" id="obs"><?php echo $objContatos->observacao?></textarea>			
	    			</td>
	    		</tr>
	    		<tr >
	    			<td style="padding-top: 12px;" colspan="2">
	    				<input type="button" value="Salvar" class="greenBtn" id="btnSalvarcontato">
	    			</td>
	    			<td style="padding-top: 12px; text-align: right;" >
	    				<?php if($usuario->id == 1564 || $usuario->id == 5 || $usuario->id == 613){ ?>
	    				<a href="javascript:void(0)" id="btnValida" rel="<?php echo $objContatos->id?>|3"><img alt="Verificar" src="/public/sistema/imagens/icons/middlenav/check.png"></a>
	    				<?php } ?>
	    			</td>
	    		</tr>		
	    	</table>
	    	
	    </form>
	    
	    <script>
	    $(function(){

	    	function endMatriz(){
		    	if($('input[name=endempresa]').is(':checked')){
					$("input[name=rua]").val($("input[name=ruaemp]").val());	
					$("input[name=cep]").val($("input[name=cepemp]").val());
					$("input[name=bairro]").val($("input[name=bairroemp]").val());
					$("select[name=cidade]").val($("input[name=cidadeemp]").val());
					$("select[name=uf]").val($("input[name=ufemp]").val());
		    	}else{
		    		$("input[name=rua]").val("");	
					$("input[name=cep]").val("");
					$("input[name=bairro]").val("");
					$("select[name=cidade]").val("0");
					$("select[name=uf]").val("0");
		    	}
	    	}
			<?php 
			if($check!=""){
			?>
			endMatriz();			
		    <?php } ?>		

		    $('input[name=endempresa]').live('click', function(){
		    	endMatriz();
		    });
		    	
			$('.data').mask('11/11/1111'); 
			$('.cep').mask('11111-111'); 			
		});	    
	    </script>
		    		    
		<?php 
		
		LogBO::cadastraLog("Cadastro/Contatos",1,$usuario->id,$params['contato'],"Contato ID ".$params['contato']);
		
		}
						
		
		//--- Contagem dos contatos ------------------------------------------------
		/**
		 * buscaQtempresas
		 *
		 * @author programador
		 * @data 16/04/2014
		 * @tags @param unknown_type $var
		 * @tags @param int $tp 1-Mercado, 2-Alvo, 3-Naodefinido, 4-Parceiro
		 * @tags @return Ambigous <multitype:, multitype:mixed Ambigous <string, boolean, mixed> >
		 */
		function buscaQtempresas($var = "",$tp = 1){

		    $usuario 	= Zend_Auth::getInstance()->getIdentity();
		    $where = "";
		    if($var!=""):
		    	$where = ' and c.uf = "'.$var['uf'].'"';
		    endif;
		    
		   	
			$where .= ($tp == 1) ? " and c.tipo_par = 'Mercado'" : "";
			$where .= ($tp == 2) ? " and c.tipo_par = 'Alvo'" : "";
			$where .= ($tp == 3) ? " and (c.tipo_par = '' || c.tipo_par = 'Parceiro') and id_clientes is NULL" : "";
			$where .= ($tp == 4) ? " and c.id_clientes is not NULL" : "";
		    
		    //--- Controle de perfil ------------------------------------------
		    foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
		    if($list->nivel==1){
		    	$sql 	= "";
		    	foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
		    	if($list->nivel == 1){
		    		if($usuario->id_perfil == 31){
		    			$where .= " and c.id_regioestelevendas in (".RegioesBO::listaRegioesusuarios(1).")";
		    		}elseif(($usuario->id_perfil == 4) || ($usuario->id_perfil == 5)){
		    			$where .= " and (c.regiao in (".RegioesBO::listaRegioesusuarios(0).") || c.id_regioestelevendas in (".RegioesBO::listaRegioesusuarios(1)."))";
		    		}else{
		    			$where .= " and c.regiao in (".RegioesBO::listaRegioesusuarios(0).")";
		    		}
		    	}
		    }elseif($list->nivel==0){
		    	$where .= " and c.id_clientes = ".$usuario->id_cliente;
		    }
		    
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
				
			$select = $db->select();
			$select->from(array('c'=>'tb_contatosemp','*'), array('count(*) as qt'))
				->where('c.status = true '.$where);
			
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		function buscaQtcontatos($var="", $tp=""){
		    $usuario 	= Zend_Auth::getInstance()->getIdentity();
		    $whereemp = $where = $wherefil = "";
		    
		    //--- Controle de perfil ------------------------------------------
		    foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
		    if($list->nivel==1){
		    	$sql 	= "";
		    	foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
		    	if($list->nivel == 1){
		    		if($usuario->id_perfil == 31){
		    			$whereemp .= " and e.id_regioestelevendas in (".RegioesBO::listaRegioesusuarios(1).")";
		    		}elseif(($usuario->id_perfil == 4) || ($usuario->id_perfil == 5)){
		    			$whereemp .= " and (e.regiao in (".RegioesBO::listaRegioesusuarios(0).") || e.id_regioestelevendas in (".RegioesBO::listaRegioesusuarios(1)."))";
		    		}else{
		    			$whereemp .= " and e.regiao in (".RegioesBO::listaRegioesusuarios(0).")";
		    		}
		    	}
		    }elseif($list->nivel==0){
		    	$where .= " and c.id = 0 ";
		    }
		    
		    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
		
			$select = $db->select();
		
			
			$select->from(array('c'=>'tb_contatos','*'), array('count(*) as qt'))
				   ->where('(exists (select * from tb_contatosemp e where e.id = c.id_emp and e.status = true '.$whereemp.')) 
				     '.$where.' and c.sit = 1');
			
			
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
				
		function listarPermissoescontatos(){
			$obj 	= new ClientesModel();
			$bo		= new RegioesModel();
			$bor	= new RegioesclientesModel();
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
		   	
			foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
			if($list->nivel == 1):
				foreach ($bor->fetchAll("id_clientes = ".$usuario->id) as $regioes):
					$reg .= $regioes->id_regioes.",";
				endforeach;
			endif;
			
			return substr($reg,0,-1);
		}
				
		
		function enviaMail($assunto, $texto, $resp, $email, $remetente = "", $emailremetente = ""){
		    
		   try {		
			    
			    if($remetente =="") $remetente = "ZTL Brasil";
			    if($emailremetente =="") $emailremetente = "info@ztlbrasil.com.br";
			    
				//$mailTransport = new Zend_Mail_Transport_Smtp("smtp.ztlbrasil.com.br", Zend_Registry::get('mailSmtp'));
			    $mailTransport = new Zend_Mail_Transport_Smtp("smtp.ztlbrasil.com.br");
			    
				$mail = new Zend_Mail('utf-8');
				$mail->setFrom($emailremetente,$remetente);
				$mail->addTo($email,$resp);
				$mail->setBodyHtml($texto);
				$mail->setSubject($assunto);
				$mail->send($mailTransport);
				
				//echo "Email enviado com SUCESSSO: ".$email;
				//echo "<br />";
				
				return true;
			} catch (Exception $e){
				echo ($e->getMessage());
				return false;
			}		
		}
				
		function listaClientesnaocadcontato(){
			$obj 	= new ClientesModel();
			$bo		= new RegioesModel();
			$bor	= new RegioesclientesModel();
			$usuario 	= Zend_Auth::getInstance()->getIdentity();
		
			$sql = "";
			foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
			if($list->nivel == 1):
				$reg = "";
				foreach ($bor->fetchAll("id_clientes = ".$usuario->id) as $regioes):
					$reg .= $regioes->id_regioes.",";
				endforeach;
				
				if($reg!=""):
					$sql = " and c.ID_REGIOES in (".substr($reg,0,-1).") and id_perfil in (2,24,27,28)";
				endif;
			elseif($list->nivel == 2):
				$sql = " and c.id_perfil in (2,24,27,28)";
			else:
				$sql = " and c.ID = 0";
			endif;
			
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			
			$select->from(array('c'=>'clientes'), array('c.*'))
				->where("not exists (select * from tb_contatosemp e where e.id_clientes = c.ID) 
				        and not exists ( select * from tb_contatosempfilial f where f.id_clientes = c.ID) 
				        and c.TIPO not like '%inativo%' ".$sql)
				->order("c.EMPRESA");
			
			 
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		
		//----- Interacoes --------------------------------------------
		function listaInteracoesemp($params){

			
		    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		    $db->setFetchMode(Zend_Db::FETCH_OBJ);
		    $select = $db->select();
		    	
		    $select->from(array('i'=>'tb_contatosempinteracao'), array('*','i.id as idint','DATE_FORMAT(i.data,"%d/%m/%Y %H:%i:%s") as datahora'))
		    	->joinLeft(array('u'=>'tb_usuarios'), 'u.id = i.id_usuarios')
		    	
		    	->where('(id_contatosemp) = "'.$params['empresa'].'" ')
		    	->order("i.id desc")
		    	->group('i.id');
		    	
		    
		    $stmt = $db->query($select);
		    
		    return $stmt->fetchAll();
		}
		
		function listaInteracoespendentes(){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			 
			$select->from(array('i'=>'tb_contatosempinteracao'), array('*','i.id as idint','DATE_FORMAT(i.data,"%d/%m/%Y %H:%i:%s") as datahora'))
			->joinLeft(array('u'=>'tb_usuarios'), 'u.id = i.id_usuarios')
			->where('(id_contatosemp) = "'.$params['empresa'].'"')
			->order("i.id desc");
			 
			
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		function buscaInteracoes($params){
		    $usuario 	= Zend_Auth::getInstance()->getIdentity();
		    
		    $where = "";		    
		    //--- filtra pelo nome da empresa -------------------------------------------
		    if(!empty($params['empresa'])):
		    	$where .= " and e.empresa like '%".$params['empresa']."%'";
		    endif;
		    
		    //--- filtra pela regiao ----------------------------------------------------
		    if(!empty($params['buscaregioes']) and $params['buscaregioes'] != 0):
		    	$where .= " and e.regiao = " .$params['buscaregioes'];
		    endif;
		    
		    //--- filtra pelo vendedor televenda ----------------------------------------
		    if(!empty($params['regioestelevendas']) and $params['regioestelevendas'] != 0):
		    	$where .= " and e.id_regioestelevendas = " .$params['regioestelevendas'];
		    endif;
		    
		    //--- filtra por periodo ---------------------------------------------------
		    if(!empty($params['dataini']) || !empty($params['datafim'])):
			    if(!empty($params['dataini']) and !empty($params['datafim'])):
				    $dataini = substr($params['dataini'],6,4).'-'.substr($params['dataini'],3,2).'-'.substr($params['dataini'],0,2);
				    $datafim = substr($params['datafim'],6,4).'-'.substr($params['datafim'],3,2).'-'.substr($params['datafim'],0,2);
				    $where .= ' and i.data BETWEEN "'.$dataini.'" and "'.$datafim.'  23:59:59"';
			    elseif (!empty($params['dataini'])):
				    $dataini = substr($params['dataini'],6,4).'-'.substr($params['dataini'],3,2).'-'.substr($params['dataini'],0,2);
				    $where .= ' and i.data >= "'.$dataini.' 23:59:59"';
			    elseif (!empty($params['datafim'])):
				    $datafim = substr($params['datafim'],6,4).'-'.substr($params['datafim'],3,2).'-'.substr($params['datafim'],0,2);
				    $where .= ' and i.data <= "'.$datafim.' 23:59:59"';
			    endif;
		    endif;
		   
		    //--- Controle de perfil ------------------------------------------
		    foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
		    if($list->nivel==1){
		    	foreach (PerfilBO::listarPerfil($usuario->id_perfil) as $list);
		    	if($list->nivel == 1){
		    		if($usuario->id_perfil == 31){
		    			$where .= " and e.id_regioestelevendas in (".RegioesBO::listaRegioesusuarios(1).")";
		    		}elseif(($usuario->id_perfil == 4) || ($usuario->id_perfil == 5)){
		    			$where .= " and (e.regiao in (".RegioesBO::listaRegioesusuarios(0).") || e.id_regioestelevendas in (".RegioesBO::listaRegioesusuarios(1)."))";
		    		}else{
		    			$where .= " and e.regiao in (".RegioesBO::listaRegioesusuarios(0).")";
		    		}
		    	}
		    }elseif($list->nivel==0){
		    	$where .= " and e.id_clientes = ".$usuario->id_cliente;
		    }		    
		    		    	    
		    if(empty($params['todos'])) $where .= ' and (i.leitura not like "%;'.$usuario->id.';%")';
		    
		    if(isset($params['pendentes'])){ 
				$where .= " and c.id is NULL and i.dataalarme <= '".date('Y-m-d')."'";
				if($list->nivel != 2 and isset($params['user'])) $where .= " and i.id_usuarios = '".$usuario->id."'";
		    }		    
		    		    
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();			
			
			$select->from(array('i'=>'tb_contatosempinteracao'), array('*','i.id as idint','DATE_FORMAT(i.data,"%d/%m/%Y %H:%i:%s") as datahora'))
				->join(array('e'=>'tb_contatosemp'), 'e.id = i.id_contatosemp')
				->joinLeft(array('u'=>'tb_usuarios'), 'u.id = i.id_usuarios')
				->joinLeft(array('c'=>'tb_contatosempcomentarios'), 'i.id = c.id_contatosempinteracao')
				
				->where("i.id > 0 ".$where)
				->group('i.id')
				->order("i.data");
			 
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		function exibeInteracoes($params){

			$objInter = ContatosBO::buscaInteracoes($params);
			
			?>
			<div class="widget" style="border-top: 1px solid #d5d5d5; padding: 10px" >      
	        <div id="divinteracao" >
	        <?php 
	        	if(count($objInter)>0):
	            	foreach ($objInter as $interacoes):
			    	?>
			    	<div class="widget">    
			    		<div class="head interacao" style="cursor: hand; cursor: pointer" id="<?php echo $interacoes->idint?>">
			        		<h6 <?php if($interacoes->tipo == 1): ?> class="iMail" <?php else: ?> class="iWalkingMan" <?php endif; ?> style="padding: 9px 12px 9px 35px;"><b><?php echo $interacoes->empresa?></b> por <b><?php echo $interacoes->nome?></b> em <?php echo $interacoes->datahora?></h6>
			        	</div>
			        	
			            <div id="inter_<?php echo $interacoes->idint?>" style="display: none">
			            	<img src="/public/sistema/imagens/loaders/loader6.gif" alt="Carregando"> <i>Buscando interações...</i>
			            </div>	
			     	</div>
					<?php 
					endforeach;
				else:
					echo "Nenhuma interação encontrada";
				endif;
			?>
            </div>             	             	                
    	</div> 
			<?php 
		}		
		
		function buscaInteracaoemp($params){
		    $bo		= new ContatosModel();
		    $boi	= new ContatosempinteracaoModel();
		    
		    return $boi->fetchAll('id = '.$params['interacao']);
		}
		
		/*----- Comentarios --------------------------------------------
		 * @$params['interacao']  - id da interacao
		 * @return - Array de objetos com registro das interacoes 
		 * 
		 * Neste metodo marco como interacao lida por usuario ativo --
		 * */
		function buscaComentariosemp($params){
		    $usuario 	= Zend_Auth::getInstance()->getIdentity();
		    $bo		= new ContatosModel();
		    $boi	= new ContatosempinteracaoModel();
		    
		    foreach ($boi->fetchAll("id = ".$params['interacao']) as $interacao);
		    $leitura = str_replace(';'.$usuario->id.';', "", $interacao->leitura).';'.$usuario->id.';';
		    $data = array('leitura'	=> $leitura);

		    $boi->update($data, "id = ".$params['interacao']);
		    		    
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			 
			$select->from(array('i'=>'tb_contatosempcomentarios'), array('*','DATE_FORMAT(i.data,"%d/%m/%Y %H:%i:%s") as datahora'))
				->joinLeft(array('u'=>'tb_usuarios'), 'u.id = i.id_usuarios')
				->where('id_contatosempinteracao = '.$params['interacao'])
				->order("i.id asc");
		
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		/* Gravacao de comentarios -------------------------------
		 * @$params['comentario'] - texto
		 * @$params['interacao'] - id da interacao q pertence o comentaro 
		 * 
		 * 
		 * Neste metodo marco como interacao NAO LIDA pra todos --------------------------
		 * */
		
		function gravarComentarios($params){
		    $bo		= new ContatosModel();
		    $boi	= new ContatosempcomentarioModel();
		    $boint	= new ContatosempinteracaoModel();
		    $usuario 	= Zend_Auth::getInstance()->getIdentity();
		    
		    $datain = array(
	    		'texto' 					=> $params['comentario'],
	    		'data' 						=> date("Y-m-d H:i:s"),
	    		'id_usuarios' 				=> $usuario->id,
		        'id_contatosempinteracao' 	=> $params['interacao']
		    );
		    	
		    $boi->insert($datain);
		    
		    $leitura = ';'.$usuario->id.';';
		    $data = array('leitura'	=> $leitura);
		    
		    $boint->update($data, "id = ".$params['interacao']);		    
		}
		
		function gravarInteracao($params){
		    $bo 	= new ContatosModel();
		    $boi	= new ContatosempinteracaoModel();
		    $usuario 	= Zend_Auth::getInstance()->getIdentity();
		    
		    $alarme = ($params["dataalarme"] == "") ? NULL : substr($params["dataalarme"],6,4).'-'.substr($params["dataalarme"],3,2).'-'.substr($params["dataalarme"],0,2);
		    
		    $datain = array(
	    		'texto' 			=> $params['textointeracao'],
	    		'data' 				=> date("Y-m-d H:i:s"),
	    		'tipo' 				=> 2,
	    		'sit' 				=> true,
	    		'id_contatosemp' 	=> $params['empresa'],
	    		'id_usuarios' 		=> $usuario->id,
	    		'titulo'			=> $params['titulo'],
		        'leitura'			=> ';'.$usuario->id.';',
				'dataalarme'		=> $alarme
		    );
		    	
		    $boi->insert($datain);		    

		    ContatosBO::atualizarSitcontatosemp($params, 2);
		    
		    LogBO::cadastraLog("Contatos/Interação",2,$usuario->id,$params['empresa'],"Empresa ID ".$params['empresa']);
		    
		}
		
		/* Atualiza a situacao da empresa nos contatos --------------------
		 * @params $params['empresa'] - id da empresa nos contatos;
		 * @oarams $tp = 1 - vendas, 2 - Interacao 		  
		 * */
		function atualizarSitcontatosemp($params, $tp){
		    $bo 	= new ContatosModel();
		    $boe	= new ContatosempModel();

		    if($tp == 2):
		    	$data = array('data_interacao' => date("Y-m-d H:i:s"));
		    elseif($tp == 1):
		    	$data = array('data_venda' => date("Y-m-d H:i:s"));
		    endif;
		    
		   // $boe->update($data, "id_clientes = ".$params['idcliente']);
		    $boe->update($data, "id = ".$params['empresa']);
		}
		
		//--------------- campanhas ------------------------------
		function listaCampanhas($var=''){
		    $bo 	= new ContatosModel();
		    $boe	= new CampanhasModel();
		    $where = "";
		    
		    if(!empty($var['idcampanha'])){
		       $where = ' and md5(id) = "'.$var['idcampanha'].'"';
		    }elseif(!empty($var['nome'])){
		       $where = ' and nome like "%'.$var['nome'].'%"';
		        
		    }
		    
		    return $boe->fetchAll('sit = true'.$where);
		    
		}
		
		function removeCampanhas($var){
			$bo 	= new ContatosModel();
			$boe	= new CampanhasModel();
		
			$data = array('sit' => false);
			
			Zend_Debug::dump($data);
			
			$boe->update($data, 'md5(id) = "'.$var['idcampanha'].'"');
		
		}
		
		function salvaCampanha($var=""){
		    $sessaorel 		= new Zend_Session_Namespace('Contatosrel');
		    $sessaobusca 	= new Zend_Session_Namespace('Contatos');
		    
		    $busca = $sessaobusca->wherecont.'|'.$sessaobusca->whereex.'|'.$sessaobusca->whereem;
		    
		    $bo 	= new ContatosModel();
		    $boe	= new CampanhasModel();
		    
		    if(empty($var['id'])):
			    $data = array(
			    	'contatos'	=> substr($sessaorel->contatosrel, 0,-1),
			        'busca'		=> $busca,
			        'nome'		=> $var['nome'],
			        'sit'		=> true,
			        'data'		=> date("Y-m-d H:i:s")
			    );
			    
			    $boe->insert($data);
			else:
				$data = array(
					'contatos'	=> substr($sessaorel->contatosrel, 0,-1),
					'data'		=> date("Y-m-d H:i:s")
				);
			
				$boe->update($data, 'id = '.$var['id']);
				
			endif;
		    			
		    //Zend_Session::namespaceUnset('Contatosrel');
		    //Zend_Session::namespaceUnset('Contatos');
		    
		}
		
		
		function buscaCampanha($var){
			$sessaorel 		= new Zend_Session_Namespace('Contatosrel');
			$sessaobusca 	= new Zend_Session_Namespace('Contatos');
		
			$bo 	= new ContatosModel();
			$boe	= new CampanhasModel();
		
			
			foreach (ContatosBO::listaCampanhas($var) as $campanha);
			
			$busca = explode('|', $campanha->busca);
			$sessaobusca->wherecont = $busca[0];
			$sessaobusca->whereex 	= $busca[1];
			$sessaobusca->whereem  	= $busca[2];
			
			echo $sessaorel->contatosrel 	= $campanha->contatos.',';
			$sessaorel->contatosrelnome = $campanha->nome;
			$sessaorel->contatosrelid	= $campanha->id;
			$sessaorel->contatosreldt	= $campanha->data;
			
		}


		/* Conta quantos contatos estao presentes da lista ------------------
		 * @$var inteiro = id da lista;
		 * @return quantidade de contatos presentes -----------------
		 * */
		function contarContatoscampanha($var){
			$boc 	= new ContatosModel();
			$boe	= new CampanhasModel();
		
			
			foreach ($boe->fetchAll("id = ".$var) as $campanha);
			
			$cont = explode(",", substr($campanha->contatos,0,-1));
			
			$contador = 0;
			foreach ($cont as $contatos => $cont):
				$contativo = "";
				foreach ($boc->fetchAll("id = ".$cont) as $contativo);
				if((count($contativo)>0) and $contativo->sit == 0):
					$contador++;
				endif;
			endforeach;
			
			return $contador;		
		}
		
		
		
		
		//------------------
		function geraListaembreagem($var=""){
			$bo 	= new ContatosModel();
			$bor	= new CampanhasModel();
		
			$ids = "";
			foreach ($bo->fetchAll('sit = true and trabalha_com like "%pesada%"') as $contatos):
				$ids .= $contatos->ID.",";
			endforeach;
		
			$data = array(
				'contatos' 	=> substr($ids,0,-1),
				'sit'		=> 1,
				'data'		=> date('Y-m-d H-i-s'),
				'nome'		=> 'Pesado'
			);
		
			$bor->insert($data);
			echo "Sucesso: ".$var;
		
		}
		
		
		/*--- quarentena ------------------------------------------------
		* @$var['tp'] = 1 para contatos, 2 para filiais ou 3 para empresas -- 
		*/		
		function listaQuarentena($var){
		    $bo 	= new ContatosModel();
		    $boe	= new ContatosempModel();
		    
		    
		    if($var['tp'] == 1):
			    if(!empty($var['busca'])):
			    	$where = " and (NOME like '%".$var['busca']."%' || ID = '".substr($var['busca'],1)."')";
		    	endif;
		    
		    	return $bo->fetchAll("(verificado = 0 and sit = true) ".$where,"data_alt desc");
		    	
		    elseif($var['tp'] == 2):
		      
		    else:
			    if(!empty($var['busca'])):
			    	$where = " and (id_clientes is NULL and empresa like '%".$var['busca']."%' || ID = '".substr($var['busca'],1)."')";
			    endif;
		    	return $boe->fetchAll("id_clientes is NULL and verificado = 0 and status = true ".$where,'data_alt desc');
		    endif;
		    
		}
		
		function listaGruposinteresse(){
		    $boa 	= new ContatosModel();
		    $bo 	= new GruposinteresseModel();
		    
		    return $bo->fetchAll("id > 0","nome");
		}
		
	}
	
?>
