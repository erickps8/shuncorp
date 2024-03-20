<?php

class Admin_RelatoriosController extends Zend_Controller_Action {
	
	public function init()
	{
	    date_default_timezone_set('America/Sao_Paulo');
		if ( !Zend_Auth::getInstance()->hasIdentity() ) {
	    	$this->_redirect('/');
		}
	}
		
	public function topoAction($ativo=''){		
		$params = $this->_getAllParams();
		if(!empty($params['idioma'])):
			$sessao = new Zend_Session_Namespace('Idiomas');
		    $sessao->idioma = $params['idioma'];
		    
		    $url = str_replace("?".$_SERVER["QUERY_STRING"],"",$_SERVER['REQUEST_URI']);
		    $this->_redirect($url);
		endif;
		
		$usuario = Zend_Auth::getInstance()->getIdentity();
		$this->view->usuario 	= $usuario;
		$this->view->translate	= Zend_Registry::get('translate');
		$this->view->objMenu 	= MenuBO::listarMenu();
		$this->view->objMeumenu	= MenuBO::buscaMenuusuario();
		$this->view->ativo 		= $ativo;
		
		//--- Controle de perfil ------------------------------------------
		$this->view->objPerfilusuario	= PerfilBO::listarPerfil($usuario->id_perfil);
	}

	public function historicoprecosAction(){
	    $this->topoAction('kang');
	    $usuario = Zend_Auth::getInstance()->getIdentity();
	    foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 44) as $list);
	
	    if($list->visualizar==1){
    	    $this->view->translate	= Zend_Registry::get('translate');
    	    $this->view->objFornec	= ClientesBO::buscaParceiros("fornecedorchines","","A");
	    	
	    }else{
	       $this->_redirect("/admin/kang/erro");
	    }
	
	    LogBO::cadastraLog("Kang/Historico precos",1,$usuario->id,"","");
	}
	
	public function buscahistoricoprecoAction(){
		    
        $this->_helper->layout->disableLayout();
        $translate	= Zend_Registry::get('translate');
        
        $params = $this->_getAllParams();
        
        $kangcompraBO = new KangcomprasBO();
        
        $objProdutos = $kangcompraBO->buscaHistoriopreco($params['fornecedor'], $params['codigo']);
        
        if(count($objProdutos)>0){
            ?>
    		<div class="widget">
     			<table style="width: 100%" class="tableStatic">
                	<thead>
                    	<tr>
                        	<td ><?php echo $translate->_("Ord")?></td>
                            <td ><?php echo $translate->_("Código")?></td>
                            <td ><?php echo $translate->_("Fornecedor")?></td>
                            <td ><?php echo $translate->_("Data")?></td>
                            <td ><?php echo $translate->_("Preço")?></td>                        
                        </tr>
                  	</thead>
              		<tbody>
              		<?php 
              		$cont = 0;
        			foreach($objProdutos as $lista){
        			    $cont++;
            	    	?>
            			<tr >
                            <td  style="text-align: center;" >
                               <?php echo $cont?>
                            </td>
                            <td  style="text-align: center;" >
                            	<?php echo $lista->CODIGO?>
                            </td>
                            <td  align="left" >
                            	<?php echo $lista->EMPRESA?>
                            </td>
                            <td  style="text-align: center;" > 
                   	            <?php echo $lista->datacompra?>
                            </td>
                            <td  style="text-align: right;" > 
                            	<?php echo number_format($lista->preco,3,",",".")?>
                            </td>		                
                        </tr>		            
            			<?php  
        			} 
        			?>
                    </tbody>        
    			</table>
    			
    		</div>
    		<?php 
        }else{
            ?><center><br>Nenhum registro encontrado</center><?php 
        }
        	
        exit();
	}
	
	public function historicoprecosimpAction(){
	
	    $this->_helper->layout->disableLayout();
	    $translate	= Zend_Registry::get('translate');
	
	    $params = $this->_getAllParams();
	
	    $kangcompraBO = new KangcomprasBO();
	
	    $objProdutos = $kangcompraBO->buscaHistoriopreco($params['fornecedor'], $params['codigo']);
	
	    if(count($objProdutos)>0){
	        ?>
	        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
            	<title>SisCorp 2.0 Alpha - shuncorp.com</title>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            	            	
            	<link href="/public/sistema/css/mainprint.css" rel="stylesheet" type="text/css" />
            </head>
            <body>
            <div class="content" style="width: 800px; margin: 0 auto;">
                <div class="widgets">
                    <div class="widget">
                		<div class="head" ><h5 class="iFrames" style="margin-top: 0px; text-align: center; ">Histórico de preços</h5></div>		
                		<table class="tableStatic" style="width: 100%">
        		        	<thead>
                            	<tr>
                                	<td ><?php echo $translate->_("Ord")?></td>
                                    <td ><?php echo $translate->_("Código")?></td>
                                    <td ><?php echo $translate->_("Fornecedor")?></td>
                                    <td ><?php echo $translate->_("Data")?></td>
                                    <td ><?php echo $translate->_("Preço")?></td>                        
                                </tr>
                          	</thead>
                      		<tbody>
                      		<?php 
                      		$cont = 0;
                			foreach($objProdutos as $lista){
                			    $cont++;
                    	    	?>
                    			<tr >
                                    <td  style="text-align: center;" >
                                       <?php echo $cont?>
                                    </td>
                                    <td  style="text-align: center;" >
                                    	<?php echo $lista->CODIGO?>
                                    </td>
                                    <td  align="left" >
                                    	<?php echo $lista->EMPRESA?>
                                    </td>
                                    <td  style="text-align: center;" > 
                                       	<?php echo $lista->datacompra?>             
                                    </td>
                                    <td  style="text-align: right;" > 
                                    	<?php echo number_format($lista->preco,3,",",".")?>
                                    </td>		                
                                </tr>		            
                    			<?php  
                			} 
                			?>
                            </tbody>        
            			</table>
            			
            		</div>
        		</div>
        	</div>
    		</body>
    		</html>
    		<?php 
        }else{
            ?><center><br>Nenhum registro encontrado</center><?php 
        }
        	
        exit();
	}
	
}