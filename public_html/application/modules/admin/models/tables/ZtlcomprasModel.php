<?php
class ZtlcomprasModel extends Zend_Db_Table_Abstract {
	protected $_name = 'pedidos_compra';
	protected $_primary = 'ID';
}

class ZtlcomprasprodModel extends Zend_Db_Table_Abstract {
	protected $_name = 'produtos_pedidos_compra';
	protected $_primary = 'ID';
}

