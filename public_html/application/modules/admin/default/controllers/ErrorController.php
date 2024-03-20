<?php

class ErrorController extends Zend_Controller_Action
{

    public function errorAction()
    {
       	$this->_helper->layout->setLayout('sitepadrao');
        $this->view->translate	=	Zend_Registry::get('translate');
        
    	$errors = $this->_getParam('error_handler');
        
        switch ($errors->type) { 
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
        
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = 'Page not found';
                $this->view->tipo = 1;
                break;
            default:
                // application error 
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->message = 'Application error';
                $this->view->tipo = 2;
                break;
        }
        
        $this->view->exception = $errors->exception;
        $this->view->request   = $errors->request;
        
        
        $erro = $errors->exception->getMessage()
        		."<br />Stack trace:".$errors->exception->getTraceAsString()
        		."Request Parameters:".var_export($errors->request->getParams(), true);
        
        $array = array(
        		'reporte' => $erro,
        		'pagina'  => str_replace("?".$_SERVER["QUERY_STRING"],"",$_SERVER['REQUEST_URI'])
        );
        
        DiversosBO::gravarReporte($array);
    }


}

