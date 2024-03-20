<?php
class TaicomprasModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_tai_compra';
	protected $_primary = 'id_tai_compra';
}

class TaicomprasprodModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_tai_comprasprod';
	protected $_primary = 'id';
}

class EntregaprodModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_taiprodent';
	protected $_primary = 'id';
}

class PreordemModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_preordem';
	protected $_primary = 'id';
}

class PreordemprodModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_preordem_prod';
	protected $_primary = 'id';
}

class PreordemprodkitModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_preordem_prod_kit';
	protected $_primary = 'id';
}