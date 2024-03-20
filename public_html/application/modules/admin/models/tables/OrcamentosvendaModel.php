<?php
class OrcamentosvendaModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_pedidos_tmp';
	protected $_primary = 'id';
}

class OrcamentosvendaprodModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_pedidos_tmp_prod';
	protected $_primary = 'id';
}

