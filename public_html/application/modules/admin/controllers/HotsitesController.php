<?php

class Admin_HotsitesController extends Zend_Controller_Action {
	
	public function init()
	{
	        if ( !Zend_Auth::getInstance()->hasIdentity() ) {
	                //$this->_redirect('http://www.ztlbrasil.com.br');
	        }
	}
		
	public function garantiasAction(){
		$this->_helper->layout->disableLayout();
	}
	
	public function cuboderodaAction(){
		$this->_helper->layout->disableLayout();
	}
	
	public function scaniaserie4Action(){
		$this->_helper->layout->disableLayout();
	}
}

?>
