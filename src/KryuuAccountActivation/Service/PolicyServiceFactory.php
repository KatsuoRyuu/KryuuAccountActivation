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

/**
 * 
 */
class PolicyServiceFactory implements FactoryInterface
{
    
    private $serviceLocator;
    
    private $eventManager;
    
    private $sharedEventManager;
    
    /**
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    private $identity;
    
    private $active;
    
    /**
     * {@inheritDoc}
     *
     * @return array
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        $this->config = $serviceLocator->get('KryuuAccountActivation\Config')->get();
        $this->activationHandler = $serviceLocator->get('KryuuAccountActivation\ActivateHandler');
        $this->time = $this->activationHandler->timeLeft();
        $this->active = $this->activationHandler->isActive();
        $this->temporary = $this->activationHandler->isTemporary();
        
        return $this;
        
    }
    
    
    public function hasAccess(){
        if (!$this->isOpenRequest()){
            return false;
        }
        return $this->isActive();
    }
    
    public function isActive(){
        return $this->active ? true : $this->isTemporary();
    }
    
    public function isTemporary(){
        return $this->temporary() ? $this->hasTimeLeft() : FALSE ;
    }
    
    public function hasTimeLeft(){
        return $this->time > 0 ? TRUE : FALSE ;
    }
    
    private function isOpenRequest(){
        // Get the route maching object for checking the route
        $routeMatch = $this->serviceLocator->get('Application')->getMvcEvent()->getRouteMatch();
        if ($this->config['block_policy'] == 'reroute' || $this->config['block_policy'] == '') {
            foreach($this->config['activation_block_reroute_accepted'] as $route) {
                if ($routeMatch->getParam('controller') == $route){
                    return true;
                }
            }
        } else if ($this->config['block_policy'] == 'role') {
            $accountEditor= $this->serviceLocator->get($this->config['account_editor_service']);
            return $accountEditor->changeUserRole($this->config['activation_block_role']);
        }

        return false;
    }
}