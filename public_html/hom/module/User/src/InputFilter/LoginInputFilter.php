<?php 
namespace User\InputFilter;

use Zend\InputFilter\InputFilter;
use Zend\Filter\StringTrim;
use Zend\Validator\EmailAddress;
use Zend\Validator\NotEmpty;

class LoginInputFilter extends InputFilter
{
    public function __construct()
    {
        $this->add([
            'name'          => 'email',
            'required'      => true,
            'filters'       => [
                ['name' => StringTrim::class],
            ],
            'validators'    => [
                [
                    'name'      => NotEmpty::class,
                    'options'   => [
                        'messages'  => [
                            NotEmpty::IS_EMPTY  => 'Campo obrigatório',
                        ],
                    ]
                ],
                [
                    'name'      => EmailAddress::class,
                    'options'   => [
                        'messages' => [
                            EmailAddress::INVALID            => "Formato do email inválido",
                            EmailAddress::INVALID_FORMAT     => "Emai com formato inválido. O padrão do formato é email@dominio",
                            EmailAddress::INVALID_HOSTNAME   => "'%hostname%' não é um endereço válido",
                            EmailAddress::INVALID_MX_RECORD  => "'%hostname%' não foi encontrado",
                            EmailAddress::INVALID_SEGMENT    => "'%hostname%' não é uma rota de rede válida.",
                            EmailAddress::DOT_ATOM           => "'%localPart%' formato não compatível com dot-atom",
                            EmailAddress::QUOTED_STRING      => "'%localPart%' não pode ser combinado com o formato da string citada",
                            EmailAddress::INVALID_LOCAL_PART => "'%localPart%' não é uma nome válido para o endereço de e-mail",
                            EmailAddress::LENGTH_EXCEEDED    => "Tamanho máximo do campo excedido",
                        ]
                    ]
                ],
            ],
        ]);
        
        $this->add([
            'name'          => 'senha',
            'required'      => true,
            'allow_empty'    => true,            
        ]);
    }
}

