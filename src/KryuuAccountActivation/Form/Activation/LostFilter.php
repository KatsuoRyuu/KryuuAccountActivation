<?php

namespace KryuuAccountActivation\Form\Activation;

use Zend\Filter;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class LostFilter implements InputFilterAwareInterface
{
	
	protected $inputFilter;
	
	public function __construct()
	{
        $filter = new InputFilter();
					
	    $filter->add(array(
	        'name'     => 'email',
	        'required' => true,
	        'filters'  => array(
	        array('name' => 'StripTags'),
	        array('name' => 'StringTrim'),
	    	),
			'validators' => array(
				array(
					'name'    => 'StringLength',
					'options' => array(
						'encoding' => 'UTF-8',
						'min'      => 1,
						'max'      => 3000,
					),
				),
			),
		));
		$this->inputFilter = $filter;
	}
	
	public function getInputFilter()
	{
		return $this->inputFilter;
	}
	
	public function setInputFilter(InputFilterInterface $inputFilter)
	{
		throw new \Exception("Not used");
	}
}
	
  