<?php

class Admin_MarketingController extends Zend_Controller_Action {
		
	public function init()
	{
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
	
	public function buscaidiomaAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		
		$sessao = new Zend_Session_Namespace('Idiomas');
	    $sessao->idioma = $params['idioma'];
	    $_SESSION['S_IDIOMA'] 	= $params['idioma'];
	}
	
	public function buscainfoAction(){
		$this->_helper->layout->disableLayout();
		date_default_timezone_set('America/Sao_Paulo');
		$this->view->objAtiv = AtividadesBO::informaAtividades();		
	}
	
	public function testeAction(){
		$this->_helper->layout->disableLayout();
		MailingBO::buscarmailsIncorretos();
	}
	
	public function mailcorrigeAction(){
		$this->topoAction();		
		$this->view->objList = MailingBO::listaEmailerrados();
	}
	
	public function removeemailAction(){
		$params = $this->_getAllParams();
		$this->view->objList = MailingBO::removeEmailerrados($params);
		
		$this->_redirect("/admin/marketing/mailcorrige");
	}
	
	public function alteraemailAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		$this->view->objList = MailingBO::alteraEmailerrados($params);
	
		$this->_redirect("/admin/marketing/mailcorrige");
	}
	
	public function excluimailcorrigeAction(){
		$this->topoAction();
		$params = $this->_getAllParams();
		$this->view->objList = MailingBO::excluiEmailerrados($params);
	
		$this->_redirect("/admin/marketing/mailcorrige");
	}
	
	//--- Mailing -----------------------------------------------------------------
	public function indexAction(){
	    
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 12) as $list);
	
		if($list->visualizar==1):
			$this->topoAction();
				
			Zend_Paginator::setDefaultScrollingStyle('Sliding');
			Zend_View_Helper_PaginationControl::setDefaultViewPartial('correio/paginator.phtml');
			$paginator = Zend_Paginator::factory(MailingBO::listarMailingenviados());
			$currentPage = $this->_getParam('page', 1);
			$paginator
				->setCurrentPageNumber($currentPage)
				->setItemCountPerPage(10);
			
			$this->view->objEmails 	= $paginator;
			
		else:
			$this->_redirect("index/erro");
		endif;
	
		LogBO::cadastraLog("ADM/Mailing",1,$usuario->ID,"","",'');
	}
	
	public function mailingnovoAction(){
	    
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 12) as $list);
		if($list->visualizar==1):
			$this->topoAction();
			
		else:
			$this->_redirect("index/erro");
		endif;
			
	}
	
	public function buscaclientesportipoAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		if(count(ClientesBO::listaemailsAllclientes($params))>0):
		?>
	    	<table>
			<?php 
	    	foreach (ClientesBO::listaemailsAllclientes($params) as $emaills):
	    		?>
	    		<tr>
				    <td><?=$emaills->NOME_CONTATO?></td>
				    <td><?=$emaills->EMAIL?></td>
				  </tr>
		    	<?php 
	    	endforeach;
	    	?>
			</table>
	    	<?php 
	   	endif;
	   	exit();
	}
	
	public function enviarmensagemAction(){
		
		$usuario = Zend_Auth::getInstance()->getIdentity();
		foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 12) as $list);
		if($list->visualizar==1):
			$this->topoAction();
			//MailingBO::enviaMensagem();
		else:
			$this->_redirect("index/erro");
		endif;
			
	}
	
	
	public function enviarmailingAction(){
		$this->topoAction();
		$this->view->obj = MailingBO::enviaMailing($this->_getAllParams());		
	}
	
	public function mailingdescadastradosAction(){
		$usuario = Zend_Auth::getInstance()->getIdentity();
        foreach (PerfilBO::listarPerfildet($usuario->id_perfil, 12) as $list);

        if($list->visualizar==1):
    		$this->topoAction();
			
			Zend_Paginator::setDefaultScrollingStyle('Sliding');
			Zend_View_Helper_PaginationControl::setDefaultViewPartial('index/paginator.phtml');
			$paginator = Zend_Paginator::factory(MailingBO::listaEmailsdescadastrados());
			$currentPage = $this->_getParam('page', 1);
			$paginator
			->setCurrentPageNumber($currentPage)
			->setItemCountPerPage(15);
			
			$this->view->objList 	= $paginator;			
			$this->view->objMot		= MailingBO::listamotivosEmailsdescadastrados();
			
		else:		
			$this->_redirect("venda/erro");
        endif;
        		
		LogBO::cadastraLog("ADM/Emails descadastrados",1,$usuario->ID,"","",'');
	}
	
	public function promocoesAction(){
		$this->_helper->layout->disableLayout();
		$this->view->obj = MailingBO::promocoesCadastrados($this->_getAllParams());
	}
		
}
