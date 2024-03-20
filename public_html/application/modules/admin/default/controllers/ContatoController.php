<?php
class ContatoController extends Zend_Controller_Action
{
    public function init()
    {
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
    	$this->_redirect('contato/faleconosco');	            
    }
    
	public function faleconoscoAction(){
       	$this->_helper->layout->setLayout('sitepadrao');
    	$this->view->translate	=	Zend_Registry::get('translate');
    	$params	= $this->_getAllParams();
    	$this->view->objSetor 		= $params['setor'];
    	$this->view->erroCaptcha	= $params['captha'];
    	$this->view->msgSucesso		= $params['envio'];    		            
    }
    
	public function representantesAction(){
       	$this->_helper->layout->setLayout('sitepadrao');
       	$this->view->translate	=	Zend_Registry::get('translate');
    }
    
    public function enviamensagemAction(){
    	$params = $this->_getAllParams();
    	
    	if($_SESSION['session_textoCaptcha']==$params['validarhum']):
    		ContatoBO::enviarMensagem($params);    	
    		$this->_redirect('contato/faleconosco/envio/sucesso');
    	else:
    		$this->_redirect('contato/faleconosco/captha/erro');
    	endif;
    }

	public function localizacaoAction(){
       	$this->_helper->layout->setLayout('sitepadrao');
       	$this->view->translate	=	Zend_Registry::get('translate');
    }
    
	public function trabalheconoscoAction(){
       	$this->_helper->layout->setLayout('sitepadrao');
       	$this->view->translate	=	Zend_Registry::get('translate');
    }
    
    public function buscarepresentantesAction(){
		$this->_helper->layout->disableLayout();
		date_default_timezone_set('America/Sao_Paulo');
		$params	= $this->_getAllParams();
		$this->view->translate	=	Zend_Registry::get('translate');
		$this->view->objRep = ContatoBO::buscaRepresentantes($params['regiao']);	
	}
	
	public function precadastroAction(){
		$this->_helper->layout->setLayout('sitepadrao');
		$this->view->translate	=	Zend_Registry::get('translate');
		$this->view->objUf 				= EstadosBO::buscaEstados(1);
		
		if($this->_request->isPost()){
		    $this->view->objRet = ClientesBO::gravaPrecadastro($this->_getAllParams());		    
		}
	}

    public function buscaufAction(){
    	$this->_helper->layout->disableLayout();
    	$params = $this->_getAllParams();
    	$this->view->objParams  = $params;
    
    	if($params['tipo'] == 1):
    		$this->view->objList		= EstadosBO::buscaEstados($params['id']);
    	else:
    		$this->view->objList		= EstadosBO::buscaCidadesidestado($params['id']);
    	endif;
    
    }
}

