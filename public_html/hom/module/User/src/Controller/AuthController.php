<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use User\Form\LoginForm;
use Zend\Authentication\AuthenticationServiceInterface;


class AuthController extends AbstractActionController
{
    private $form;
    private $authService;
    public function __construct(LoginForm $form, AuthenticationServiceInterface $authService)
    {
        $this->form         = $form;
        $this->authService  = $authService;
    }
    
    public function loginAction()
    {
        
        if($this->authService->hasIdentity()){
            return $this->redirect()->toRoute('nfe'); 
        }
        
        $form = $this->form;
                
        $request = $this->getRequest();
        
        if(!$request->isPost()){
            return new ViewModel([
                'form' => $form,
            ]);
        }
                
        $data = $this->getRequest()->getPost();
        $form->setData($data);
        
        if($form->isValid()){
            
            $formData = $form->getData();
            
            $authAdapter = $this->authService->getAdapter();
            $authAdapter->setIdentity($formData['email']);
            $authAdapter->setCredential($formData['senha']);
            
            $result = $this->authService->authenticate();
            
            if($result->isValid()){
                return $this->redirect()->toRoute('nfe'); 
            }else{
                $messageError = "Login inválido";
            }
        }else{
            echo "dados não validos";
        } 
        
        return new ViewModel([
            'form'          => $form,
            'messageError'  => $messageError,
        ]);        
    }
    
    public function logoutAction()
    {
        $this->authService->clearIdentity(); 
        return $this->redirect()->toRoute('login'); 
    }
     
    
}
