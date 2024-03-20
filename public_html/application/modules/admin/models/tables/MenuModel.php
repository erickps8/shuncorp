<?php
class MenuModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_menu';
	protected $_primary = 'id';
}

class SubmenuModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_menu_sub';
	protected $_primary = 'id';
}

class MenuusuarioModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_menuusuario';
	protected $_primary = 'id';
}