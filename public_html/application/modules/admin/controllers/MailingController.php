<?php

class Admin_MailingController extends Zend_Controller_Action {
		
	public function init()
	{
		
	}
	
	public function promocoesAction(){
		$this->_helper->layout->disableLayout();
		$this->view->obj = MailingBO::promocoesCadastrados($this->_getAllParams());
	}

	public function removemailinglancamentoAction(){
		$params = $this->_getAllParams();
		MailingBO::removeMailinglancamentos($params['id']);
		$this->_redirect("/admin/administracao/lancamentos");
	}
	
	public function descadastraAction(){
		$this->_helper->layout->disableLayout();
		
		if($this->_request->isPost()):
        	MailingBO::descadastraEmail($this->_getAllParams());
        	$this->view->obj = 1;
        endif;	
	}
	
	public function confleituramailingAction(){
		$this->view->obj = MailingBO::gravaLeituramailing($this->_getAllParams());
	}
	
	
	
}

