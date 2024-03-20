<?php
	class VeiculosBO{
		
		function listaFabricantes($pesq=""){
			$ob 	= new VeiculosModel();
			$obj 	= new FabricantesModel();
			
			$where = "sit = true";
			if(!empty($pesq['fabricante'])):
				$where .= " and no_fabricante like '%".$pesq['fabricante']."%'";
			endif;
			
			return $obj->fetchAll($where,"no_fabricante asc");	
		}

		function buscaFabricantes($pesq=""){
			$ob 	= new VeiculosModel();
			$obj 	= new FabricantesModel();
				
			$where = "sit = true";
			if(!empty($pesq['idfab'])):
				$where = "md5(id) = '".$pesq['idfab']."'";
				return $obj->fetchAll($where);
			endif;
		}
		
		
		function gravarFabricante($params){
			$ob 	= new VeiculosModel();
			$obj 	= new FabricantesModel();
			$usuario = Zend_Auth::getInstance()->getIdentity();
			
			//--- Essa chave vincula o fabricante com um parceiro ----------------------------------------
			if(!empty($params['parceiro'])):
				$array['id_parceiro']	= $params['parceiro'];	
			endif;
			
			$obj->update($array,"id = ".$params['idfabricante']);
		}
		
		function listaParceiro($pesq=""){
		    
		    if(count(VeiculosBO::buscaFabricantes($pesq))>0):
		    	foreach (VeiculosBO::buscaFabricantes($pesq) as $fab);
		    	if(!empty($fab->id_parceiro)):
		    		$where = " || (c.ID = ".$fab->id_parceiro.")";
		    	endif;
		    endif;
		    
		    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
		    $db->setFetchMode(Zend_Db::FETCH_OBJ);
		    $select = $db->select();
		    	
		    $select->from(array('c'=>'clientes','*'), array('*'))
	    		->where("(not exists (select * from tb_fabricante f where f.id_parceiro = c.ID) and TIPO not like '%inativo%')".$where)
	    		->order('c.EMPRESA');
		    	
		    $stmt = $db->query($select);
		    return $stmt->fetchAll();
		}
		
		function removeFabricantes($pesq){
		    $ob 	= new VeiculosModel();
		    $obj 	= new FabricantesModel();
		    $usuario = Zend_Auth::getInstance()->getIdentity();
		    
		    $array['sit']	= false;
		    $obj->update($array, "md5(id) = '".$pesq['idfab']."'");
		    
		    LogBO::cadastraLog("Cadastro/Fabricantes",3,$usuario->ID,$pesq['idfab'],"Fabricante ID ".$pesq['idfab']);
		}
		
		
		/**
		 * --Montadoras
		 * 
		 */
		function buscaMontadoras($pesq=array()){
			$obj 	= new MontadoraModel();
			
			$where = "situacao = true";
			$where .= (!empty($pesq['nome'])) ? " and NOME like '%".$pesq['nome']."%'" : (!empty($pesq['montadora'])) ? " and md5(id) = '".$pesq['montadora']."'" : "";
			return $obj->fetchAll($where);			
		}
		
		function listaMontadoras($pesq){
		    $obj 	= new MontadoraModel();
		    	
		    $where = "situacao = true";

		    $where .= (!empty($pesq['montadora'])) ? " and NOME like '%".$pesq['montadora']."%'" : "";
		    
		    $order    = $setamont = "";
		    $setaid   = "&#9660;";
		    $relmont  = $relid = 1;
		    if(isset($pesq['ordem']) and isset($pesq['coluna'])){
		        
		        $setaid   = "";
		        
		        if($pesq['coluna'] == 1 and $pesq['ordem'] == 1){
		            $order    = "id desc";
		            $setaid   = "&#9660;";
		            $relid    = 2;
		        }
		        
		        if($pesq['coluna'] == 1 and $pesq['ordem'] == 2){
		        	$order   = "id asc";
		        	$setaid  = "&#9650";
		        	$relid   = 1;
		        }
		        
		        if($pesq['coluna'] == 2 and $pesq['ordem'] == 1){
		        	$order    = "nome desc";
		        	$setamont = "&#9660;";
		        	$relmont  = 2;		        	
		        }
		        
		        if($pesq['coluna'] == 2 and $pesq['ordem'] == 2){
		        	$order    = "nome asc";
		        	$setamont = "&#9650";
		        	$relmont  = 1;
		        }
		    }
		    		    		    
		    $montadoras =  $obj->fetchAll($where, $order);
		    
		   	if (count($montadoras) > 0):			
    		?>       
            <div class="widget first">
     			<table style="width: 100%" class="tableStatic">
                	<thead>
                    	<tr>
                            <td width=""><a href="javascript:void(0)" id="orderId" rel="<?php echo $relid?>">Id <?php echo $setaid?></a></td>
                            <td width="65%"><a href="javascript:void(0)" id="orderMont" rel="<?php echo $relmont?>">Montadora <?php echo $setamont?></a></td>
                            <td width="15%">Opções</td>
                        </tr>
                    </thead>
                    <tbody>
    	        	<?php 
    	        	
    	        	$pag = (isset($pesq['page']))? $pesq['page'] : 1;
    	        	
    	        	Zend_Paginator::setDefaultScrollingStyle('Sliding');
    	        	Zend_View_Helper_PaginationControl::setDefaultViewPartial('index/paginatorajax.phtml');
    	        	$paginator    = Zend_Paginator::factory($montadoras);
    	        	$currentPage  = $pag;
    	        	$paginator->setCurrentPageNumber($currentPage)
    	        	          ->setItemCountPerPage(10);
    	        	
    				foreach($paginator as $lista):
    				?>			
    				<tr >
    	                <td  style="text-align: center;">
    	                   <?php echo $lista->id ?>
    	                </td>
    	                <td  align="left" >
    	                   <?php echo $lista->nome?>
    	                </td>
    	                <td  style="text-align: center;">
    	                   <a href="/admin/cadastro/veiculos/montadora/<?php echo md5($lista->id)?>"><img src="/public/sistema/imagens/icons/middlenav/magnify.png" width="16" border="0" title="Visualizar"></a>
    	                </td>                  
                	</tr>
                	<?php  endforeach; ?>
                    </tbody>
                </table>            
            </div>
            <div style="margin-top: 20px;">
    			<?php 
    			if ( count($paginator) > 0) :
    				echo $paginator;
    			endif;
    		    ?>
    		</div>
    		
    		<input type="hidden" value="<?php echo $pag?>" id="pag">
    		
    		<?php 
    		else :
    		?>
    		<div class="widget first" style="border: 1px solid #d5d5d5; padding: 20px; text-align: center;">
     			Nenhuma registro encontrado
    		</div>	
    		<?php 
    		endif;
    		 
		    
		    
		}
		
		
		function gravarMontadora($params){
		    
			$obj = new MontadoraModel();
			
			$usuario = Zend_Auth::getInstance()->getIdentity();
				
			$array = array(
				'nome'		=> $params['nome'],
				'situacao'	=> true
			);
				
			if(empty($params['idmontadora'])){
				$id = $obj->insert($array);
				LogBO::cadastraLog("Cadastro/Montadoras",2,$usuario->id,$id,"Montadora ID".$id);
			}else{
			    $id = $params['idmontadora'];
				$obj->update($array,"id = ".$params['idmontadora']);
				LogBO::cadastraLog("Cadastro/Montadoras",4,$usuario->id,$id,"Montadora ID ".$id);
			}
			
			$pasta = Zend_Registry::get('pastaPadrao')."public/sistema/upload/veiculos/montadoras";
			
			$upload = new Zend_File_Transfer_Adapter_Http();
			$upload->setDestination($pasta);
			$files = $upload->getFileInfo();
						
			if($files){
			    
			    foreach ($files as $file => $info){
			    	
			        $ext = substr(strrchr($info['name'], "."), 1);
			        $upload->addFilter('Rename', array('target' => $pasta.'/'.$id.'.'.$ext, 'overwrite' => true));
			        
			    	if ($upload->isValid($file)) {
			    		$upload->receive($file);
			    		$obj->update(array('logo' => $ext),"id = ".$id);
			    	}
			    }
			    
			}else{
				throw "Nenhum arquivo para anexar";
			}
			
				
		}
		
		function removeAnexomontadora($params){
			$obj = new MontadoraModel();
			$obj->update(array('logo' => ''), "id = '".$params['anexo']."'");			
		}
		
		function removeMontadora($params){
			$obj = new MontadoraModel();
			$obj->update(array('situacao' => 'false'), "id = '".$params['montadora']."'");
		}
			
		
		/**
		 * --Veiculos
		 *
		 */
		function buscaVeiculos($pesq=array()){
			$obj 	= new VeiculosModel();
				
			$where = "situacao = true";
			$where .= (!empty($pesq['nome'])) ? " and no_modelo like '%".$pesq['nome']."%'" : (!empty($pesq['veiculo'])) ? " and md5(id) = '".$pesq['veiculo']."'" : "";
			return $obj->fetchAll($where);
		}
		
		function listaVeiculos($pesq){
			$obj 	= new VeiculosModel();
			 
			$where = "situacao = true";		
			$where .= (!empty($pesq['veiculo'])) ? " and no_modelo like '%".$pesq['veiculo']."%'" : "";
			$where .= (!empty($pesq['montadora'])) ? " and id_montadora = '".$pesq['montadora']."'" : "";
		
			$order  = $setamont = "";
			$setaid = "&#9660;";
			$order  = "id desc";
			
			if($pesq['ordem'] != "" and $pesq['coluna'] != ""){
		
				$setaid = "";
		
				if($pesq['coluna'] == 1 and $pesq['ordem'] == 1){
					$order    = "id desc";
					$setaid   = "&#9660;";
				}
		
				if($pesq['coluna'] == 1 and $pesq['ordem'] == 2){
					$order   = "id asc";
					$setaid  = "&#9650";
				}
		
				if($pesq['coluna'] == 2 and $pesq['ordem'] == 1){
					$order    = "no_modelo desc";
					$setamont = "&#9660;";
				}
		
				if($pesq['coluna'] == 2 and $pesq['ordem'] == 2){
					$order    = "no_modelo asc";
					$setamont = "&#9650";
				}
			}
		
			$montadoras =  $obj->fetchAll($where, $order);
		
			if (count($montadoras) > 0):
			?>
            <div class="widget first">
     			<table style="width: 100%" class="tableStatic">
                	<thead>
                    	<tr>
                            <td width=""><a href="javascript:void(0)" id="orderId" >Id <?php echo $setaid?></a></td>
                            <td width="85%"><a href="javascript:void(0)" id="orderVeic" >Veículo <?php echo $setamont?></a></td>
                            
                        </tr>
                    </thead>
                    <tbody>
    	        	<?php 
    	        	
    	        	$pag = (isset($pesq['page']))? $pesq['page'] : 1;
    	        	
    	        	Zend_Paginator::setDefaultScrollingStyle('Sliding');
    	        	Zend_View_Helper_PaginationControl::setDefaultViewPartial('index/paginatorajax.phtml');
    	        	$paginator    = Zend_Paginator::factory($montadoras);
    	        	$currentPage  = $pag;
    	        	$paginator->setCurrentPageNumber($currentPage)
    	        	          ->setItemCountPerPage(10);
    	        	
    				foreach($paginator as $lista):
    				?>			
    				<tr >
    	                <td  style="text-align: center;">
    	                   <?php echo $lista->id ?>
    	                </td>
    	                <td  align="left" >
    	                   <?php echo $lista->no_modelo?>
    	                </td>             
                	</tr>
                	<?php  endforeach; ?>
                    </tbody>
                </table>            
            </div>
            <div style="margin-top: 20px;">
    			<?php 
    			if ( count($paginator) > 0) :
    				echo $paginator;
    			endif;
    		    ?>
    		</div>
    		
    		<input type="hidden" value="<?php echo $pag?>" id="pag">
		    		
    		<?php 
    		else :
    		?>
    		<div class="widget first" style="border: 1px solid #d5d5d5; padding: 20px; text-align: center;">
     			Nenhuma registro encontrado
    		</div>	
    		<?php 
    		endif;
    		 
		    
		    
		}
				
				
		function gravarVeiculo($params){
		    $obj = new VeiculosModel();
			
			$usuario = Zend_Auth::getInstance()->getIdentity();
				
			$array = array(
				'no_modelo'	     => $params['nome'],
                'id_montadora'	 => $params['idmontadora'],
				'situacao'	     => true
			);
				
			if(empty($params['idveiculo'])){
				$id = $obj->insert($array);
				LogBO::cadastraLog("Cadastro/Veículos",2,$usuario->id,$id,"Veículo ID".$id);
			}else{
			    $id = $params['idveiculo'];
				$obj->update($array,"id = ".$params['idveiculo']);
				LogBO::cadastraLog("Cadastro/Veículos",4,$usuario->id,$id,"Veículo ID ".$id);
			}
			
			$pasta = Zend_Registry::get('pastaPadrao')."public/sistema/upload/veiculos/veiculos";
			
			$upload = new Zend_File_Transfer_Adapter_Http();
			$upload->setDestination($pasta);
			$files = $upload->getFileInfo();
						
			if($files){
			    
			    foreach ($files as $file => $info){
			    	
			        $ext = substr(strrchr($info['name'], "."), 1);
			        $upload->addFilter('Rename', array('target' => $pasta.'/'.$id.'.'.$ext, 'overwrite' => true));
			        
			    	if ($upload->isValid($file)) {
			    		$upload->receive($file);
			    		$obj->update(array('img' => $ext),"id = ".$id);
			    	}
			    }
			    
			}else{
				throw "Nenhum arquivo para anexar";
			}	
				
		}
				
		function removeAnexoveiculo($params){
			$obj = new VeiculosModel();
			$obj->update(array('img' => ''), "id = '".$params['anexo']."'");			
		}
		
		function removeVeiculo($params){
			$obj = new VeiculosModel();
			$obj->update(array('situacao' => 'false'), "id = '".$params['veiculo']."'");
		}
		
	}
?>