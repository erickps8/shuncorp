<?php
class PerfilModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_perfil';
	protected $_primary = 'id';
}

class PerfilclientesModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_perfilclientes';
	protected $_primary = 'id';
}