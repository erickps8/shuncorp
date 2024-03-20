<?php
class GarantiaModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_garantiaztl';
	protected $_primary = 'id';
}

class GarantiaprodModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_garantiaztl_prod';
	protected $_primary = 'id';
}

class GarantiaproddetModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_garantiaztl_proddet';
	protected $_primary = 'id';
}

class GarantiahistoricoModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_garantiahistorico';
	protected $_primary = 'id';
}

class GarantiaentregaModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_garantiaentrega';
	protected $_primary = 'id';
}

class GarantiaimgModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_garanaliseimg';
	protected $_primary = 'id';
}

class GarantiamoModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_garantiamobra';
	protected $_primary = 'id';
}