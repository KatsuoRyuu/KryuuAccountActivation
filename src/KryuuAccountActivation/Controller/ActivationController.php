<?php
namespace KryuuAccountActivation\Controller;


/**
 * @encoding UTF-8
 * @note *
 * @todo *
 * @package PackageName
 * @author Anders Blenstrup-Pedersen - KatsuoRyuu <anders-github@drake-development.org>
 * @license *
 * The Ryuu Technology License
 *
 * Copyright 2014 Ryuu Technology by 
 * KatsuoRyuu <anders-github@drake-development.org>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * Ryuu Technology shall be visible and readable to anyone using the software 
 * and shall be written in one of the following ways: ?????????, Ryuu Technology 
 * or by using the company logo.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *

 * @version 20140506 
 * @link https://github.com/KatsuoRyuu/
 */

use Zend\View\Model\ViewModel;
use KryuuAccountActivation\Controller\StandardController;
use KryuuAccountActivation\Form\Activation;

class ActivationController extends StandardController
{
	
	
    public function indexAction()
    {
        
        return new ViewModel();
    }
    
    public function activateAction(){
        $userEntity = $this->getConfiguration('user_entity');
        $activateEntity = 'KryuuAccountActivation\Entity\Activate';
        
        $user = new $userEntity;
        $activate = new $activateEntity;
        
        if ($this->params('id') > 0){
            $user = $this->entityManager()->getRepository($userEntity)->findOneBy(array('id'=>$this->params('id')));
        }
        if ($this->params('activate_id') > 0){
            $activate = $this->entityManager()->getRepository($activateEntity)->findOneBy(array('id'=>$this->params('activate_id')));
        }
        
        $hash = $this->makeHash($user, $activate);
        
        if($hash == $this->params('hash')){
            $this->serviceLocator()->get('KryuuAccountActivation/ActivationService')->activate($activate);
            return $this->redirect()->toRoute(static::ROUTE_ACTIVATED,array('msg'=>static::STATUS_ACTIVATION_SUCCESS));
        }
        
    }
    
    public function deactivateAction(){
        
    }
    
    public function lostAction(){
       return $this->sendMail();
    }
    
    public function sendMail(){
        
        $viewModel = new ViewModel();
        $activateHandler = $this->getServiceLocator()->get('KryuuAccountActivation\ActivateHandler');
		$message = $activateHandler->getNewMailMessage();
        
        $form = new Activation\Lost();
        
        $this->events()->trigger(__FUNCTION__.'.form', $this, array('form'=>$form));
        
        $form->get('submit')->setValue('Send');
        $form->get('submit')->setLabel('Send');
		        
        $this->events()->trigger(__FUNCTION__.'.form.post', $this, array('form'=>$form));
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            
        	$filter = new Activation\LostFilter();
            $form->setInputFilter($filter->getInputFilter());
            $form->setData((array) $request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();
                $user = $this->entityManager()
                    ->getRepository($this->configuration('user_entity'))
                    ->findOneBy(array('email' => $data['email']));
                
                if(!$user){
                    
                    return $this->redirect()->toRoute(static::ROUTE_STATUS,array('msg'=>static::STATUS_USER_NOT_FOUND));
                }
                
                $activate = $this->entityManager()
                    ->getRepository($this->configuration('activate_entity'))
                    ->findOneBy(array('user' => $user->getId()));
                
				if(!$activate) {
                    
					return $this->redirect()->toRoute(static::ROUTE_STATUS,array('msg'=>static::STATUS_ACTIVATION_ENTITY_NOT_FOUND));
				} 
                
                if ($user != false && $activate !=false) {
                    $message->__set(null,'reply');
                    $message->__set(null,'name');

                    /*
                     *  Starting the template rendering.
                     */ 
                    $template = new ViewModel();
                    $template->setTemplate('mail-template.phtml');
                    $template->setVariables(array(
                        'hash'          => $this->makeHash($user, $activate, true),
                        'email'         => $user->getEmail(),
                        'id'            => $user->getId(),
                    ));

                    $viewRender = $this->getServiceLocator()->get('ViewRenderer');
                    $content = $viewRender->render($template);

                    $message->__set(array('text/html' => $content, ),'message');
                    $message->__set(array($user->getEmail()),'recievers');

                    $activateHandler->sendMail($message);
                    
                    return $this->redirect()->toRoute(static::ROUTE_STATUS,array('msg'=>static::STATUS_MAIL_SEND_SUCCESS));
                }
            } else {
                var_dump('invalid');
            }
        }

        return new ViewModel(array(
            'form' => $form,
            'route'=> static::ROUTE_MAIL_SEND,
        ));
    }
    
    private function makeHash($user,$activate,$reverse=false){
        if ($reverse) {
            return md5( 
                $user->getId().
                $user->getCreatedTime().
                $user->getEmail().
                $user->getCreatedTime().
                $activate->__get('user')
                );
        } else {
            return md5( 
                $activate->__get('user').
                $user->getCreatedTime().
                $user->getEmail().
                $user->getCreatedTime().
                $user->getId()
                );
        }
    }
    
}
