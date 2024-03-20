<?php

class Admin_VendaController extends Zend_Controller_Action {
	
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
	
	
	function buscavendasfinanceiroAction(){
	    $this->_helper->layout->disableLayout();
	    
	    $params = $this->_getAllParams();
	    
	    $bov = new KangvendasModel();
	    $bo  = new KanginvoiceModel();
	    
	    if($params['tp'] == 1){
            $objVendas = $bo->fetchAll('sit != 0 and (id_fin_contasapagar = "'.$params['financeiro'].'" || id_fin_contasapagar is null || id_fin_contasapagar = 0)', 'id desc');
	    }else{
	        $objVendas = $bo->fetchAll('sit != 0 and (id_fin_contasareceber = "'.$params['financeiro'].'" || id_fin_contasareceber is null || id_fin_contasareceber = 0)', 'id desc');
	    }
	    
	    if(count($objVendas)>0){
	    ?>
    	<form method="post" name="contas" id="contas" class="mainForm">
        	<input type="hidden" name="financeiro" value="<?php echo $params['financeiro']?>">
        	<input type="hidden" name="tp" value="<?php echo $params['tp']?>">
        	
        	<div class="widget" style="width: 100%; overflow: scroll; height: 280px; border-top: 1px solid #d4d4d4;" >	
            	<table class="tableStatic" style="width: 100%">
            	<thead>	
            		<tr>
            			<td style="text-align: center;">Id</td>
            			<td style="text-align: center;">Invoice</td>
            			<td>&nbsp;</td>
            		</tr>
            	</thead>
            	<tbody>
            		<?php
            		$cont=0; 
            		foreach ($objVendas as $invoices){
            			$cont++;
            			?>
            			<tr style="font: 11px Arial, Helvetica, sans-serif;">
            				<td class="td_orc_min" style="text-align: center;"><?php echo $cont?></td>
            				<td class="td_orc_min" style="text-align: center;">S<?php echo substr("000000".$invoices->id,-6,6)?></td>
            				<td class="td_orc_min" style="text-align: center;">
            				    <?php            				    
            				    $check = null;
            				    
            				    if($params['tp'] == 1){ 
            				        $check = ($params['financeiro'] == $invoices->id_fin_contasapagar and $params['financeiro'] != "") ? 'checked="checked"' : '';
            				    }else{ 
            				        $check = ($params['financeiro'] == $invoices->id_fin_contasareceber and $params['financeiro'] != "") ? 'checked="checked"' : '';
            				    }
            				    ?>            				                				
            					<input type="radio" class="retorno" id="<?php echo md5($invoices->id)?>" name="radioInvoice" value="<?php echo $invoices->id?>" <?php echo $check?> >
            				</td>
            			</tr>
            		<?php								
            		}
            		?>
            		</tbody>
            	</table>
        	</div>
        	<div style="text-align: left; padding-top: 10px">
        		<input type="button" id="btnSalvarinvoice" name="salvar" value="Salvar" class="basicBtn">
        	</div>
    		
    	</form>
	    <?php 
	    }
	    
	    exit();
	}

    function buscaVendasFreteAction(){
        $this->_helper->layout->disableLayout();

        $params = $this->_getAllParams();

        $bov = new KangvendasModel();
        $bo  = new KanginvoiceModel();

        if($params['tp'] == 1){
            $objVendas = $bo->fetchAll('sit != 0 and (id_fretepag = "'.$params['financeiro'].'" || id_fretepag is null || id_fretepag = 0)', 'id desc');
        }else{
            $objVendas = $bo->fetchAll('sit != 0 and (id_freterec = "'.$params['financeiro'].'" || id_freterec is null || id_freterec = 0)', 'id desc');
        }

        if(count($objVendas)>0){
            ?>
            <form method="post" name="frete" id="frete" class="mainForm">
                <input type="hidden" name="financeiro" value="<?php echo $params['financeiro']?>">
                <input type="hidden" name="tp" value="<?php echo $params['tp']?>">

                <div class="widget" style="width: 100%; overflow: scroll; height: 280px; border-top: 1px solid #d4d4d4;" >
                    <table class="tableStatic" style="width: 100%">
                        <thead>
                        <tr>
                            <td style="text-align: center;">Id</td>
                            <td style="text-align: center;">Invoice</td>
                            <td>&nbsp;</td>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $cont=0;
                        foreach ($objVendas as $invoices){
                            $cont++;
                            ?>
                            <tr style="font: 11px Arial, Helvetica, sans-serif;">
                                <td class="td_orc_min" style="text-align: center;"><?php echo $cont?></td>
                                <td class="td_orc_min" style="text-align: center;">S<?php echo substr("000000".$invoices->id,-6,6)?></td>
                                <td class="td_orc_min" style="text-align: center;">
                                    <?php
                                    $check = null;

                                    if($params['tp'] == 1){
                                        $check = ($params['financeiro'] == $invoices->id_fin_contasapagar and $params['financeiro'] != "") ? 'checked="checked"' : '';
                                    }else{
                                        $check = ($params['financeiro'] == $invoices->id_fin_contasareceber and $params['financeiro'] != "") ? 'checked="checked"' : '';
                                    }

                                    ?>
                                    <input type="radio" class="retorno" name="radioFrete" value="<?php echo $invoices->id?>" <?php echo $check?> >

                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
                <?php if($params['tp'] == 1){ ?>
                <div style="text-align: left; padding: 10px 0px 30px 0px">
                     <label><input type="checkbox" name="checkFob" id="checkFob" value="1" > FOB FREIGHT </label>
                </div>
                <?php } ?>
                <div style="text-align: left; padding-top: 10px">
                    <input type="button" id="btnSalvarFrete" name="salvar" value="Salvar" class="basicBtn">
                </div>

            </form>
            <?php
        }

        exit();
    }
	
	public function gravacontasAction(){
	    $this->_helper->layout->disableLayout();
	    
	    $params = $this->_getAllParams();
	     
	    $bov = new KangvendasModel();
	    $bo  = new KanginvoiceModel();
	    
	    if($params['tp'] == 1){
    	    $objVendas = $bo->fetchAll('id_fin_contasapagar = "'.$params['financeiro'].'" || id_fin_contasapagar is null || id_fin_contasapagar = 0', 'id desc');
    	    
    	    //-- removo marcaçoes antigas --
    	    $bo->update(array('id_fin_contasapagar' => null), 'id_fin_contasapagar = "'.$params['financeiro'].'"');
    	    
    	    foreach ($objVendas as $invoices){
    	        if($params[$invoices->id]){
                    $bo->update(array('id_fin_contasapagar' => $params['financeiro']), 'id = "'.$invoices->id.'"');
    	        }
    	    }
	    }else{
	        $objVendas = $bo->fetchAll('id_fin_contasareceber = "'.$params['financeiro'].'" || id_fin_contasareceber is null || id_fin_contasareceber = 0', 'id desc');
	        	
	        //-- removo marcaçoes antigas --
	        $bo->update(array('id_fin_contasareceber' => null), 'id_fin_contasareceber = "'.$params['financeiro'].'"');
	        	
	        foreach ($objVendas as $invoices){
	            if($params[$invoices->id]){
	                $bo->update(array('id_fin_contasareceber' => $params['financeiro']), 'id = "'.$invoices->id.'"');
	            }
	        }
	    }
	    
	    exit();
	}
}  
