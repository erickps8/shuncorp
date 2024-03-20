<?php
class KangcomprasModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_kang_compra';
	protected $_primary = 'id_kang_compra';
}

class KangcomprasprodModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_kang_comprasprod';
	protected $_primary = 'id';
}

class KangcomprasentregaModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_kangprodent';
	protected $_primary = 'id';
}