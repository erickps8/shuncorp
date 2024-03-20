<?php 
namespace Nfe\InputFilter;

use Zend\InputFilter\InputFilter;
use Zend\Filter\StringTrim;
use Zend\Filter\StripTags;
use Zend\Filter\Digits;
use Zend\Validator\NotEmpty;

class NfeInputFilter extends InputFilter
{
    public function __construct()
    {
        $this->add([
            'name'          => 'cfop',
            'required'      => true,
            'filters'       => [
                ['name' => Digits::class],
            ],
            'validators'    => [
                [
                    'name'      => NotEmpty::class,
                    'options'   => [
                        'messages' => [
                            NotEmpty::IS_EMPTY => 'O campo não pode ficar em branco',
                            NotEmpty::INVALID  => 'Valor inválido',
                        ]
                    ]
                ]  
            ],
        ]);
        
        $this->add([
            'name'          => 'naturezaop',
            'required'      => true,
            'filters'       => [
                ['name' => StringTrim::class],
                ['name' => StripTags::class],
            ],
        ]);
    }
}