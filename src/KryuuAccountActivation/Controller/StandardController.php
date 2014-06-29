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
 * and shall be written in one of the following ways: 竜技術, Ryuu Technology 
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

 * @version 20140614 
 * @link https://github.com/KatsuoRyuu/
 */

use KryuuAccountActivation\Controller\EntityUsingController;

class StandardController extends EntityUsingController{
    
    const ROUTE_ACTIVATED = "KryuuAccountActivation/status";
    const ROUTE_MAIL_SEND = "KryuuAccountActivation/lost_activation";
    const ROUTE_LOGIN  = "zfcuser/login";
    const ROUTE_STATUS = "KryuuAccountActivation/status";
    
    const CONFIG_SERVICE = "KryuuAccountActivation\Config";
    
    const EVENT_PREFIX = 'kryuu.account.activation.';
    
    const STATUS_ACTIVATION_SUCCESS="activation_success";
    const STATUS_ACTIVATION_FAILED="activation_failed";
    
    const STATUS_DEACTIVATION_SUCCESS="deactivation_success";
    const STATUS_DEACTIVATION_FAILED="deactivation_failed";
    
    const STATUS_MAIL_SEND_SUCCESS='mailsend_success';
    
    const STATUS_USER_NOT_FOUND='user_not_found';
    const STATUS_ACTIVATION_ENTITY_NOT_FOUND='activation_entity_not_found';
    
}
