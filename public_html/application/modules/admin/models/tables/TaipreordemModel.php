<?php
class TaipreordemModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_preordem';
	protected $_primary = 'id';
}

class TaipreordemprodModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_preordem_prod';
	protected $_primary = 'id';
}

class TaipreordemkitModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_preordem_prod_kit';
	protected $_primary = 'id';
}

class TaipreordemtmpModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_preordem_tmp';
	protected $_primary = 'id';
}

class TaipreordemtmpprodModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_preordem_tmp_edit';
	protected $_primary = 'id';
}

class TaipreordemauxModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_preordem_aux';
	protected $_primary = 'id';
}