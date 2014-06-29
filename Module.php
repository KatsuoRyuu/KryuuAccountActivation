<?php
namespace KryuuAccountActivation;

use Zend\Mvc\MvcEvent;
use KryuuAccountActivation\Controller\Plugin\UserActivation;
use Zend\Authentication\AuthenticationService;


class Module
{ 
    /**
     *
     * @var string
     */
    private $config;
    
    public function onBootstrap(MvcEvent $e){
        $this->config             = $e->getApplication()->getServiceManager()->get('KryuuAccountActivation\Config');
        
        $sm = $e->getApplication()->getServiceManager();
        $auth = $sm->get('zfcuser_auth_service');
        
        if ($auth->hasIdentity()) {
            $this->identity = $auth->getIdentity();
            $e->getApplication()->getServiceManager()->get('KryuuAccountActivation\ActivateHandler');
        }

        $em = $e->getApplication()->getEventManager();
        $em->attach('route', array($this, 'checkAuthenticated'));
        
        $zfcServiceEvents = $e->getApplication()->getServiceManager()->get('zfcuser_user_service')->getEventManager();
        $zfcServiceEvents->attach('register.post', function($e) use ($sm){
        	$user = $e->getParam('user');
            $sm->get('KryuuAccountActivation\ActivateHandler')->defaultMail($user);
        });
    }    
    


    public function checkAuthenticated(MvcEvent $e)
    {
        $sm = $e->getApplication()->getServiceManager();
        $policy = $sm->get('KryuuAccountActivation\PolicyService');
        if ( $policy->hasAccess() ) {
            $e->getRouteMatch()
                ->setParam('controller', $this->config['activation_block_reroute'])
                ->setParam('action', 'index');
        }
    }    
    
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }    
    
    public function getControllerPluginConfig()
    {
        return array(
            'factories' => array(
                'userActivation' => function ($sm) {
                    $serviceLocator = $sm->getServiceLocator();
                    $activeService = $serviceLocator->get('KryuuAccountActivation\ActivateHandler');
                    $userActivation = new UserActivation();
                    $userActivation->setActiveHandlerService($activeService);
                    return $userActivation;
                },
            ),
        );
    }
    
    public function getServiceConfig()
    {
        return array(
            /*
            'invokables' => array(
                'KryuuAccount\Form\Login'                => 'ZfcUser\Form\Login\Login',
                'KryuuAccount\Authentication\Adapter\Db' => 'KryuuAccount\Authentication\Adapter\Db',
            ),*/
            'factories' => array(
                'KryuuAccountActivation\ActivateHandler'          => 'KryuuAccountActivation\Service\ActivateHandlerServiceFactory',/*
                'kryuu-account_login_form' => function ($sm) {
                    $options = $sm->get('zfcuser_module_options');
                    $form = new Form\Login\Login(null, $options);
                    $form->setInputFilter(new Form\Login\LoginFilter($options));
                    return $form;
                },
                // We alias this one because it's ZfcUser's instance of
                // Zend\Authentication\AuthenticationService. We don't want to
                // hog the FQCN service alias for a Zend\* class.
                'zfcuser_auth_service' => function ($sm) {
                    return new \Zend\Authentication\AuthenticationService(
                        $sm->get('ZfcUser\Authentication\Storage\Db'),
                        $sm->get('ZfcUser\Authentication\Adapter\AdapterChain')
                    );
                },
             */
            ),
        );
    }
}