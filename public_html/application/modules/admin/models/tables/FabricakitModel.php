<?php
class FabricakitModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_fabricakits';
	protected $_primary = 'id';
}

class FabricakitprodModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_fabricakits_prod';
	protected $_primary = 'id';
}

class FabricakitprodqtModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_fabricakits_qtprod';
	protected $_primary = 'id';
}