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

/**
 * 
 */
class ActivateEditorServiceFactory implements FactoryInterface
{
    /**
     * 
     * @var KryuuAccountActivation\Entity\Activate
     */
    private $activate;
    
    /**
     *
     * @var ServiceLocatorInterface
     */
    private $serviceLocator;
    
    /**
     *
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     *
     * @var KryuuAccountActivation\Service\ActivateHandlerServiceFactory 
     */
    private $activationHandler;
    
    /**
     * {@inheritDoc}
     *
     * @return array
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->setServiceLocator($serviceLocator);
        $this->config = $serviceLocator->get('KryuuAccountActivation\Config');
        $this->setEntityManager();
        $this->setActivationHandler();
        $this->activate = $this->getActivationHander()->getActivate();
        
        
        return $this;
    }
    
    public function activate($activate=null){
        if ($activate != null){
            $activate->setState(true);
            $this->getEntityManager()->persist($this->activate());
            $this->getEntityManager()->flush();
            return true;
        }
        return false;
    }
    
    public function deactivate($activate=null){
        if ($activate != null){
            $activate->setState(false);
            $this->getEntityManager()->persist($this->activate());
            $this->getEntityManager()->flush();
            return true;
        }
        return false;
    }
    
    public function sendNotice(){
        
    }
    
    /**
     * Private standard functions form here.
     */
    
    private function setActivationHandler(){
        $this->activationHandler = $this->serviceLocator->get('KryuuAccountActivation\ActivateHandler');
    }
    
    /**
     * 
     * @return KryuuAccountActivation\ActivateHandler
     * @throws Zend\ServiceManager\Exception
     */
    private function getActivationHander(){
        if($this->activationHandler == null){
            throw new Zend\ServiceManager\Exception();
        }
        return $this->activationHandler;
    }
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
