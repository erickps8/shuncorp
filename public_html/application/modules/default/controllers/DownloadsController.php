<?php
class DownloadsController extends Zend_Controller_Action
{

    public function init()
    {
        $params = $this->_getAllParams();
    	if(!empty($params['idioma'])):
			$sessao = new Zend_Session_Namespace('Idiomas');
		    $sessao->idioma = $params['idioma'];
		    
		    $url = str_replace("?".$_SERVER["QUERY_STRING"],"",str_replace("/public/","", $_SERVER['REQUEST_URI']));
		    $this->_redirect($url);
		endif;	
    }

    public function indexAction()
    {
       	$this->_helper->layout->setLayout('sitepadrao');
    	$this->view->translate	=	Zend_Registry::get('translate');
    }
    
    public function catalogosAction()
    {
    	$this->_helper->layout->setLayout('sitepadrao');
    	$this->view->translate	=	Zend_Registry::get('translate');
    }

    public function dicasAction()
    {
    	$this->_helper->layout->setLayout('sitepadrao');
    	$this->view->translate	=	Zend_Registry::get('translate');
    }
}

