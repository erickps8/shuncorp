<?php
class PedidosvendaModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_pedidos';
	protected $_primary = 'id';
}

class PedidosvendaprodModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_pedidos_prod';
	protected $_primary = 'id';
}

class PedidosvendaantModel extends Zend_Db_Table_Abstract {
	protected $_name = 'pedidos';
	protected $_primary = 'ID';
}

class PedidosvendaprodantModel extends Zend_Db_Table_Abstract {
	protected $_name = 'produtos_pedidos';
	protected $_primary = 'ID';
}

class RelatoriosvendasModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_relatoriodevendas';
	protected $_primary = 'id';
}

class VendacomissaoModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_vendacomissao';
	protected $_primary = 'id';
}

class VendacomissaotelevendaModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_vendacomissaointerno';
	protected $_primary = 'id';
}