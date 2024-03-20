<?php
class ClientesdefultModel extends Zend_Db_Table_Abstract {
	protected $_name = 'clientes';
	protected $_primary = 'ID';
}

class ClientesEmailModel extends Zend_Db_Table_Abstract {
	protected $_name = 'clientes_emails';
	protected $_primary = 'ID';
}

class ClientesrecuperarsenhaModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_recuperarsenha';
	protected $_primary = 'id';
}