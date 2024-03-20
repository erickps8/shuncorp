<?php
class GarantiaanaliseModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_garanalise';
	protected $_primary = 'id';
}

class GarantiaanaliseprodModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_garanaliseprod';
	protected $_primary = 'id';
}

class GarantiadicasModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_gardicas';
	protected $_primary = 'id';
}

class GarantiadicasanaliseModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_gardicasanalise';
	protected $_primary = 'id';
}
