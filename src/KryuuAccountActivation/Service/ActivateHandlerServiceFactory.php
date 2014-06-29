<?php

namespace KryuuAccountActivation\Service;

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

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use KryuuAccountActivation\Entity\Activate;
use KryuuAccountActivation\Mail\Message,
    Zend\Mail,
    Zend\Mime;

/**
 * 
 */
class ActivateHandlerServiceFactory implements FactoryInterface
{
    
    private $serviceLocator;
    
    private $eventManager;
    
    private $sharedEventManager;
    
    private $configService;
    
    /**
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    private $identity;
    
    private $noIdentity=false;
    
    private $activate;
    
    /**
     * {@inheritDoc}
     *
     * @return array
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->setServiceLocator($serviceLocator);
        $this->setEventManager();
        $this->configService = $serviceLocator->get('KryuuAccountActivation\Config');
        $this->config = $this->configService->get();
                        
        if ($this->hasIdentity()){
            $this->activate = $this->getEntityManager()->getRepository('KryuuAccountActivation\Entity\Activate')->findOneBy(array('user' => $this->getIdentity()->getId()));
            if (!$this->activate){
                $this->createActivation();
            }
        } else {
            $this->noIdentityPolicy();
        }
        return $this;
    }
    
    public function isActive(){
        return $this->activate->__get('state');
    }
    
    public function timeLeft(){
        return ($this->activate->__get('time')+$this->config['temporary_activation']['time'])-time();
    }
    
    public function isTemporary(){
        return $this->config['temporary_activation']['active'];
    }
    
    public function getActivate(){
        if ($this->noIdentity == false){
            return $this->activate;
        }
        return false;
    }
    
    public function getNewMailMessage(){
        return new Message();
    }
    
    public function sendMail($message){
        
        $mail = new Mail\Message();
        
        $parts = array();
        
        if (is_array($message->__get('message'))){
            foreach ( $message->__get('message') as $mimetype => $messagepart ){
                
                $bodyMessage = new Mime\Part($messagepart);
                $bodyMessage->type = $mimetype;
                $parts[] = $bodyMessage;
            }
        }  
        
        if ($message->__get("file")->count() > 0){
            foreach ($message->__get("file") as $file) {
                $fileRepo = $this->getServiceLocator()->get('FileRepository');
                $fileContent = fopen($fileRepo->getRoot().'/'.$file->getSavePath(), 'r');
                
                $attachment = new Mime\Part($fileContent);
                $attachment->type = $file->getMimetype();
                $attachment->filename = $file->getName();
                $attachment->encoding = Mime\Mime::ENCODING_BASE64;
                $attachment->disposition = Mime\Mime::DISPOSITION_ATTACHMENT;
                $parts[] = $attachment;
            }

        }

        $bodyPart = new Mime\Message();

        // add the message body and attachment(s) to the MimeMessage
        $bodyPart->setParts($parts);
       
        /*
         * getting the from the sender.
         */
        $from = $message->__get('reply');
        if ($from == null){
            $from = array( $this->configService->get(array('mailTransport','default_sender_name'),true) => $this->configService->get(array('mailTransport','default_sender'),true) );
        }
        $fromName = array_keys($from);
        $fromMail = array_values($from);
        
        foreach($fromName as $index => $name){
            $mail
                ->addFrom($fromMail[$index],$name)
                ->addReplyTo($fromMail[$index],$name)
                ->setSender($fromMail[$index],$name);
        }
        
        /*
         * getting the from the sender.
         */
        $recievers = $message->__get('recievers');
        $recieversMail = array_values($recievers);
        
        foreach($recieversMail as $email){
            $mail->addTo($email);
        }
        
        $mail
            ->setSubject($message->__get('subject'))
            ->setBody($bodyPart)
            ->setEncoding("UTF-8")
            ->setBody($bodyPart);
        // Setup SMTP transport using LOGIN authentication
        
        $this->configService->getMailTransport()->send($mail);
        
    }
    
    public function defaultMail($user){

        $message = $this->getNewMailMessage();
        
        if(!$user){

            throw new \Zend\ServiceManager\Exception('User profile uninitialized');
        }

        $activate = $this->getEntityManager()
            ->getRepository($this->configService->get('activate_entity'))
            ->findOneBy(array('user' => $user->getId()));

        if(!$activate) {

            throw new \Zend\ServiceManager\Exception('Activation object could not be initialized');
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

            return $template;
        }

        throw new \Zend\ServiceManager\Exception('User or Activation object failed to be initialized');
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
    
    /**
     * 
     * From here on all the Private functions will be assigned
     * 
     * 
     */
    
    public function changeUserIdentity(){
        
    }
    
    /**
     * Construct the user activation object for later use.
     * This should only be run if the user does not have a activation object registerd to it yet.
     */
    private function createActivation(){
        $config_tmp = $this->getServiceLocator()->get('config'); $config = $config_tmp['KryuuAccountActivation'];
        
        if ($this->hasIdentity()) {
        
            $this->activate = new Activate();
            $this->activate->__set(time()+$this->config['temporary_activation']['time'],'time'); 
            $this->activate->__set($config['resistration_activation_state'],'state');
            $this->activate->__set($this->getIdentity()->getId(),'user');
            
            $this->getEntityManager()->persist($this->activate);
            $this->getEntityManager()->flush();
        }
        
    }    
    
    /**
     * Construct the user activation object for later use.
     * This should only be run if the user does not have a activation object registerd to it yet.
     */
    private function noIdentityPolicy(){
        $config_tmp = $this->getServiceLocator()->get('config'); $config = $config_tmp['KryuuAccountActivation'];
        $this->activate = new Activate();
        $this->activate->__set(time()+100,'time'); 
        $this->activate->__set($config['no_identity_policy'],'state');
        $this->noIdentity=true;
    }
    
    private function getIdentity(){
        
        if ($this->identity == null){
            $sm = $this->getServiceLocator();
            $auth = $sm->get('zfcuser_auth_service');

            if ($auth->hasIdentity()) {
                $this->identity = $auth->getIdentity();
            }
        }
        return $this->identity;
    }
    
    private function hasIdentity(){
        
        $sm = $this->getServiceLocator();
        $auth = $sm->get('zfcuser_auth_service');

        return $auth->hasIdentity();
    }
    
    /**
     * Private standard functions form here.
     */
    
    /**
     * 
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     */    
    private function setServiceLocator(ServiceLocatorInterface $serviceLocator){
        $this->serviceLocator = $serviceLocator;
    }
    
    /**
     * 
     * @return type
     * @throws Zend\ServiceManager\Exception
     */
    private function getServiceLocator(){
        if ($this->serviceLocator == null){
            throw new Zend\ServiceManager\Exception();
        }
        return $this->serviceLocator;
    }
    
    /**
     * Sets the Eventmanager by using the servicelocator.
     */
    private function setEventManager(){
        $this->eventManager = $this->serviceLocator->get('EventManager');
    }
    
    /**
     * 
     * @return Zend\EventManager\EventManager
     */
    private function getEventManager(){
        if ($this->eventManager == null){
            $this->setEventManager();
        }
        return $this->eventManager;
    }
    
    /**
     * sets the Doctrine ORM Entity Manager by the use of the preset service locator
     */
    private function setEntityManager(){
        $this->entityManager = $this->serviceLocator->get('Doctrine\ORM\EntityManager');
    }
    
    
    /**
     * 
     * @return Doctrine\ORM\EntityManager
     */
    private function getEntityManager(){
        if ($this->entityManager == null){
            $this->setEntityManager();
        }
        return $this->entityManager;
    }
    
}