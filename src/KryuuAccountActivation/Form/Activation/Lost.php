<?php
namespace KryuuAccountActivation\Form\Activation;

use Zend\Form\Form;

class Lost extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('Account');
        $this->setAttribute('method', 'post');
		
        $this->add(array(
            'name' => 'email',
            'type' => 'Text',
            'options' => array(
                'label' => 'email:',
            ),
        ));
		
        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Go',
                'id' => 'submitbutton',
            ),
        ));
    }
}