<?php
class GruposprodModel extends Zend_Db_Table_Abstract {
	protected $_name = 'grupos';
	protected $_primary = 'ID';
}

class GruposcompraModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_purchasing';
	protected $_primary = 'id';
}

class GruposprodutosModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_gruposprod';
	protected $_primary = 'id';
}

class GruposprodutossubModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_gruposprodsub';
	protected $_primary = 'id';
}