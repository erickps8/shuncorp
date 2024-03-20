<?php
class Admin_FinanceiroController extends Zend_Controller_Action {
		
	public function init()
	{
        if ( !Zend_Auth::getInstance()->hasIdentity() ) {
        	$this->_redirect('/');
        }
	}
	
	public function topoAction($ativo=''){

	    date_default_timezone_set('America/Sao_Paulo');
	    
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
	
	//--Relatorio de pendencias-------------------------
	public function relatoriosAction(){
        try {
            $this->topoAction('admin');

            $this->view->objClientes = ClientesBO::buscaParceiros("clienteschines", "", "A");
            $this->view->objFornec = ClientesBO::buscaParceiros("fornecedorchines");

            if ($this->_request->isPost()) {
                $this->view->params = $this->_getAllParams();

                if ($this->view->params['tpRelatorio'] == 1) {
                    $kangretornoBO = new KangretornoBO();
                    $this->view->objRetono = $kangretornoBO->getDataRebate($this->_getAllParams());
                } else if ($this->view->params['tpRelatorio'] == 2) {
                    $kangfreteBO = new KangfreteBO();
                    $this->view->objRetono = $kangfreteBO->buscar($this->_getAllParams());
                }
            }
        }catch (Exception $e) {
            echo "Erro desconhecido: " . $e->getMessage();
        }
	}

    public function rebateAction(){}

    public function freteAction(){}

    public function rebateimpAction(){
        $this->_helper->layout->disableLayout();

        $kangretornoBO = new KangretornoBO();

        $this->view->params = $this->_getAllParams();
        $this->view->objRetono = $kangretornoBO->getDataRebate($this->_getAllParams());

    }

    public function freteimpAction(){
        $this->_helper->layout->disableLayout();

        $kangfreteBO = new KangfreteBO();
        $this->view->params = $this->_getAllParams();
        $this->view->objRetono = $kangfreteBO->buscar($this->_getAllParams());

    }
}