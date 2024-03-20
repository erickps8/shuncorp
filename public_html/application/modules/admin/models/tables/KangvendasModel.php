<?php
class KangvendasModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_kang_vendas';
	protected $_primary = 'ID';
}

class VendasprodModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_kang_vendasprod';
	protected $_primary = 'ID';
}

class KanginvoiceModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_kang_cominvoice';
	protected $_primary = 'id';
}

class KanginvoiceprodModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_kang_cominvoiceprod';
	protected $_primary = 'id';
}

class PacklistModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_kang_packlist';
	protected $_primary = 'id';
}

class PacklistprodModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_kang_packlistprod';
	protected $_primary = 'id';
}


class KangorcamentosModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_kang_pedidos_tmp';
	protected $_primary = 'id';
}

class KangorcamentosprodModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_kang_pedidos_tmp_prod';
	protected $_primary = 'id';
}

class KanginvoicelinksModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_kang_cominvoicelinks';
	protected $_primary = 'id';
}