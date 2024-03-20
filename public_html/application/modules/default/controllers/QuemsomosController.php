<?php
class QuemsomosController extends Zend_Controller_Action
{

    public function init()    {
       $params = $this->_getAllParams();
    	$usuario = Zend_Auth::getInstance()->getIdentity();
		if(!empty($usuario->ID)){
			echo "<script> location.href = '/admin/painel'</script>";
		}
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
    	$this->_redirect('quemsomos/oquefazemos');	            
    }
    
	public function oquefazemosAction(){
       	$this->_helper->layout->setLayout('sitepadrao');
    	$this->view->translate	=	Zend_Registry::get('translate');    		            
    }
    
	public function nossahistoriaAction(){
       	$this->_helper->layout->setLayout('sitepadrao');
    	$this->view->translate	=	Zend_Registry::get('translate');    		            
    }
    
	public function equipeztlAction(){
       	$this->_helper->layout->setLayout('sitepadrao');
    	$this->view->translate	=	Zend_Registry::get('translate');    
    			            
    }

}

