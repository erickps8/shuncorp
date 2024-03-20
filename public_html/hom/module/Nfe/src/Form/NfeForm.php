<?php


namespace Nfe\Form;


use Zend\Form\Form;

class NfeForm extends Form
{

    public function __construct($name=null)
    {
        parent::__construct('nfe');

        $this->add([
           'name' => 'id',
            'type' => 'hidden'
        ]);

        $this->add([
            'name' => 'cfop',
            'type' => 'text',
            'options' => [
                'label'=> 'CFOP'
            ]
        ]);

        $this->add([
            'name' => 'naturezaop',
            'type' => 'text',
            'options' => [
                'label'=> 'Natureza OP'
            ]
        ]);

        $this->add([
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => [
                'value'=> 'Salvar',
                'id'=>'submitbutton'
            ]
        ]);
    }

}