<?php
class UsuarioModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_usuarios';
	protected $_primary = 'id';
}

class UsuarioanexoModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_usuariosanexos';
	protected $_primary = 'id';
}

class UsuariomsgModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_usuariosmsg';
	protected $_primary = 'id';
}

class UsuarioausenciasModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_usuariosausencia';
	protected $_primary = 'id';
}