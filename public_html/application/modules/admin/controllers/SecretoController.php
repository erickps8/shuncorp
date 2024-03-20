<?php

class Admin_SecretoController extends Zend_Controller_Action {
	
	public function init()
	{
	        if ( !Zend_Auth::getInstance()->hasIdentity() ) {
	               // $this->_redirect('http://www.ztlbrasil.com.br');
	        }
	}

	public function indexAction(){
	    $this->_helper->layout->disableLayout();
	    
	   	$array = array(
	    	"1"=>"Cleiton",
	        "2"=>"Tiago",
			"3"=>"Tayane",
	        "4"=>"Helio",
	        "5"=>"Rani",
	        "6"=>"Jarley",
	        "7"=>"Bruno",
	        "8"=>"Italo",
	        "9"=>"Tereza",
	        "10"=>"Tais",
	        "11"=>"Ota",
	        "12"=>"Lucas",
	        "13"=>"João",
	   	);         
	   
	    $arraysorteados = array();
	   	for($i = 1; $i <= 13; $i++){
	        
	   	    $id = randnum();
	   	    
		}
		
		function randnum(){
		    
		    foreach ($arraysorteados as $arr => $chave){
		    	if($arraysorteados[$chave] == $id) rand();
		    }
		    
			return rand(1, 13);
		}
		
		exit();
	}
	
	
	
	public function loginAction(){
		$this->_helper->layout->disableLayout();
		
	}
	
	public function listaAction(){
		$this->_helper->layout->disableLayout();
		
		$params = $this->_getAllParams();
		
		if($params['login']):
			foreach (SecretoBO::listarUsuario($params) as $listuser);
			if(!$listuser->login): 
				$this->_redirect("/admin/secreto/erro");	
			endif;
			
			foreach (SecretoBO::buscaUsuariosel($listuser->id) as $listu);
			
			if($listu->idescolha): 
				$this->view->objUser	= SecretoBO::buscaUsuarioall($listu->id);				
			else:
				$this->view->objlista 	= SecretoBO::listarUsuarioall();
				$this->view->objUs 		= $listuser->id;
			
			endif;
			
		elseif($params['sel']): 
			$this->view->objUser	= SecretoBO::buscaUsuarioall($params['sel']);
			$array['iduser'] 	= $params['iduser'];
			$array['idescolha'] = $params['sel'];
			SecretoBO::atualizaUser($array);
		else:
			$this->_redirect("/admin/secreto/erro");
		endif;
		
	}
	
	public function erroAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_getAllParams();
		$this->view->erro = $params[e]; 
	}
	
		
}

?>
