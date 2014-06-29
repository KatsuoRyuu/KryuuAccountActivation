<?php

namespace KryuuAccountActivation;

return array(
    __NAMESPACE__ => array(
        'user_entity'    => 'KryuuAccount\Entity\User',
        'activate_entity'=> 'KryuuAccountActivation\Entity\Activate',
        'login_event_success' => array(
            'class_name' => 'ZfcUser\Authentication\Adapter\AdapterChain',
            'event_name' => 'authenticate.success'
        ),
        'temporary_activation'=>array(
            'active' => true,
            'time'   => 518400,// in seconds (518400 = 6 days)
        ),
        'resistration_activation_state' => false,
        'no_identity_policy' => true,
        'block_policy' => 'role', // "role" -> change user role, "reroute" ->
        'activation_block_reroute' => 'KryuuAccountActivation\error',
        'activation_block_reroute_accepted' => array(
            // list of invokable controllers that is accepted
            'KryuuAccountActivation\error',
            'Application\Controller\Index',
        ),
        'activation_block_role' => 'user_inactive',
        'account_editor_service' => 'kryuu_account_editor'
    ),
    
    'controllers' => array(
        'invokables' => array(
            'KryuuAccountActivation'         => 'KryuuAccountActivation\Controller\ActivationController',
            'KryuuAccountActivation\error'   => 'KryuuAccountActivation\Controller\ErrorController',
            'KryuuAccountActivation\status'   => 'KryuuAccountActivation\Controller\StatusController',
        ),
    ),

    /*
     * Routing Example
     */

    
    'router' => array(
        'routes' => array(
            'KryuuAccountActivation' => array(
                'type'    => 'literal',
                'options' => array(
                    'route' => '/activate',
                    'defaults' => array(
                        'controller'    => 'KryuuAccountActivation',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'error' => array(
                        'type' => 'literal',
                        'options' => array(
                            'route' => '/error',
                            'defaults' => array(
                                'controller' => 'KryuuAccountActivation\error',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    'activate' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/a[/:id][/:activate_id][/:hash]',
                            'constraints' => array(
                                'id'            => '[0-9]+',
                                'activate_id'   => '[0-9]+',
                                'hash'          => '[a-zA-Z0-9]+',
                            ),
                            'defaults' => array(
                                'controller' => 'KryuuAccountActivation',
                                'action' => 'activate',
                            ),
                        ),
                    ),
                    'status' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/status[/:msg]',
                            'constraints' => array(
                                'msg' => '[a-zA-Z0-9_]+',
                            ),
                            'defaults' => array(
                                'controller' => 'KryuuAccountActivation\status',
                                'action' => 'status',
                            ),
                        ),
                    ),
                    'lost_activation' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/lost',
                            'constraints' => array(
                                'msg' => '[0-9]+',
                            ),
                            'defaults' => array(
                                'controller' => 'KryuuAccountActivation',
                                'action' => 'lost',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    

    'doctrine'=> array(
        'driver' => array(
            __NAMESPACE__ . '_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../src/' . __NAMESPACE__ . '/Entity'),
            ),
            'orm_default' => array(
                'drivers' => array(
                    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver',
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'KryuuAccountActivation\Config'                   => 'KryuuAccountActivation\Service\ConfigServiceFactory',
            'KryuuAccountActivation\ActivateHandler'          => 'KryuuAccountActivation\Service\ActivateHandlerServiceFactory',
            'KryuuAccountActivation\PolicyService'            => 'KryuuAccountActivation\Service\PolicyServiceFactory',
            'KryuuAccountActivation\ActivationService'        => 'KryuuAccountActivation\Service\ActivateEditorServiceFactory',
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'kryuuaccountactivation' => __DIR__ . '/../view',
        ),
    ),
);