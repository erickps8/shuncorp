<?php
class ContatosModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_contatos';
	protected $_primary = 'id';
}

class ContatosempModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_contatosemp';
	protected $_primary = 'id';
}

class ContatosempinteracaoModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_contatosempinteracao';
	protected $_primary = 'id';
}

class ContatosempcomentarioModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_contatosempcomentarios';
	protected $_primary = 'id';
}

class CartoesgruposModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_cartoesgrupos';
	protected $_primary = 'id';
}

class CartoescontatosModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_cartoescontato';
	protected $_primary = 'id';
}

class CartoesfonesModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_cartoesfone';
	protected $_primary = 'id';
}

class CartoesanexoModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_cartoesanexo';
	protected $_primary = 'id';
}

class GruposinteresseModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_contatosginteresse';
	protected $_primary = 'id';
} 

class CampanhasModel extends Zend_Db_Table_Abstract {
    protected $_name = 'tb_contatosrelatorios';
    protected $_primary = 'id';
} 

class ContatosagrupadosModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_contatosagrupados';
	protected $_primary = 'id';
}