<?php
class AuthController extends Zend_Controller_Action{

    public function init(){
        /* Initialize action controller here */
    }

    public function indexAction(){
        //return $this->_helper->redirector('login');    	
    }

	public function loginAction(){
	   	ini_set("display_errors", 1);
		$this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
	    $this->view->messages = $this->_flashMessenger->getMessages();
	        
	    //Verifica se existem dados de POST
	    if ( $this->getRequest()->isPost() ) {
        	
	        $params = $this->_getAllParams();
	        $login = $params['email'];
            $senha = md5($params['senha']);
            
            $dbAdapter = Zend_Db_Table::getDefaultAdapter();
	            
	   		//Inicia o adaptador Zend_Auth para banco de dados
	        $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);
	        $authAdapter->setTableName('tb_usuarios')
	            		->setIdentityColumn('email')
	                    ->setCredentialColumn('senha')
	                    ->setCredentialTreatment('sit = true');
	                        
			//Define os dados para processar o login
            $authAdapter->setIdentity($login)
            			->setCredential($senha)
            			->setCredentialTreatment('sit = true');
                			
	        //Efetua o login
	        $auth = Zend_Auth::getInstance();
	        $result = $auth->authenticate($authAdapter);	        
	        
	        //Verifica se o login foi efetuado com sucesso
	        if ( $result->isValid() ) {
	        	//Armazena os dados do usuário em sessão, apenas desconsiderando
	            //a senha do usuário
	            $info = $authAdapter->getResultRowObject(null, 'senha');
	            $storage = $auth->getStorage();
	            $storage->write($info);
	            //Redireciona para o Controller protegido

	            $usuario = Zend_Auth::getInstance()->getIdentity();

	            UsuarioBO::atualizaSessao($usuario->id, 1);
	            
				if($usuario->sit == true){		
					//LogBO::cadastraLogin(getenv("REMOTE_ADDR"),"SUCESSO",$usuario->id);
					
				    
				    
					LogBO::cadastraLog("Login de acesso",1,$usuario->id,"",getenv("REMOTE_ADDR"));
					$this->_redirect('../admin');					
				}else{
					LogBO::cadastraLogin(getenv("REMOTE_ADDR"),"FALHA",$usuario->id);
					$auth->clearIdentity();
	        		$this->_redirect('../?val=error');
				}
	        } else {
	            
	            //Define os dados para processar o login
	            $authAdapter->setIdentity($login."@shuncorp.com")->setCredential($senha);
	             
	            //Efetua o login
	            $auth = Zend_Auth::getInstance();
	            $result = $auth->authenticate($authAdapter);
	             	             
	            setcookie('email', $login, (time() + (3 * 24 * 3600)));
	            
	            //Verifica se o login foi efetuado com sucesso
	            if ( $result->isValid() ) {
	            	//Armazena os dados do usuário em sessão, apenas desconsiderando
	            	//a senha do usuário
	            	$info = $authAdapter->getResultRowObject(null, 'senha');
	            	$storage = $auth->getStorage();
	            	$storage->write($info);
	            	//Redireciona para o Controller protegido
	            	 
	            	$usuario = Zend_Auth::getInstance()->getIdentity();
	            	UsuarioBO::atualizaSessao($usuario->id, 1);
	            	
	            	if($usuario->sit == true){
	            		
	            		LogBO::cadastraLog("Login de acesso",1,$usuario->id,"",getenv("REMOTE_ADDR"));
	            		$this->_redirect('../admin');
	            	}else{
	            		LogBO::cadastraLogin(getenv("REMOTE_ADDR"),"FALHA",$usuario->id);
	            		$auth->clearIdentity();
	            		$this->_redirect('../?val=error');
	            	}
	            }else{
	                //Dados inválidos	            
		            LogBO::cadastraLogin(getenv("REMOTE_ADDR"),"FALHA","");
		            $this->_redirect('../?val=error');	   
	            }         
			}	        
	 	}
	 	
	 	exit();
	}

	public function logoutAction(){

	    $usuario = Zend_Auth::getInstance()->getIdentity();
	    UsuarioBO::atualizaSessao($usuario->id, 0);
	    
	    $auth = Zend_Auth::getInstance();
	    $auth->clearIdentity();
	        
	    $this->_redirect('../');
	}


}





