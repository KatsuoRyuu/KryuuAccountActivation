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

class StatusController extends StandardController
{
    const NO_MSG = "No message found";
    
	const MSG_ACTIVATION_SUCCESS='The account has been activated.';
    const MSG_ACTIVATION_FAILED='The account could not be activated';
    
    const MSG_DEACTIVATION_SUCCESS='The account has been deactivated.';
    const MSG_DEACTIVATION_FAILED='The acocunt could not be deactivated';
    
    const MSG_NEW_ACTIVATION_MAIL='New activation mail has been send';
    
    const MSG_USER_NOT_FOUND="The user was not found";
    const MSG_ACTIVATION_ENTITY_NOT_FOUND="Users activation entity was not found";
    
	protected $message;
    protected $layout;
	
    public function statusAction()
    {
        $view = new ViewModel();
        
        $this->getEventManager()->trigger(static::EVENT_PREFIX.'status.message.pre', $this, $this->params());
        
        if ($this->params('msg') ){
            $this->getStatusMessage($this->params('msg'));
        }
        
        $this->getEventManager()->trigger(static::EVENT_PREFIX.'status.message.post', $this, $this->params());
        
        if ($this->layout != null){
            $view->layout($this->layout);
        }
        
        $this->getEventManager()->trigger(static::EVENT_PREFIX.'status.layout.post', $this, $this->params());
        
        $view->setVariables(array(
            'message' => $this->message,
            ));
        
        $this->getEventManager()->trigger(static::EVENT_PREFIX.'status.variables.post', $this, $view);
        
        $view->setVariables(array(
            'message' => $this->message,
            ));
        return $view;
    }   
    
    private function getStatusMessage($msg){
        
        switch($msg):
            case static::STATUS_ACTIVATION_SUCCESS :
                $this->message = static::MSG_ACTIVATION_SUCCESS;
                break;
            case static::STATUS_ACTIVATION_FAILED :
                $this->message = static::MSG_ACTIVATION_FAILED;
                break;
            case static::STATUS_DEACTIVATION_SUCCESS :
                $this->message = static::MSG_DEACTIVATION_SUCCESS;
                break;
            case static::STATUS_DEACTIVATION_FAILED :
                $this->message = static::MSG_DEACTIVATION_FAILED;
                break;
            case static::STATUS_MAIL_SEND_SUCCESS :
                $this->message = static::MSG_NEW_ACTIVATION_MAIL;
                break;
            case static::STATUS_USER_NOT_FOUND :
                $this->message = static::MSG_USER_NOT_FOUND;
                break;
            case static::STATUS_ACTIVATION_ENTITY_NOT_FOUND :
                $this->message = static::MSG_ACTIVATION_ENTITY_NOT_FOUND;
                break;
            default:
                $this->message = static::NO_MSG;
                break;
        endswitch;
        
    }
    
}
