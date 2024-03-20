<?php
class RefcruzadaModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_crossreference';
	protected $_primary = 'id';
}

class CodigoscrossModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_crossprodutos';
	protected $_primary = 'id';
}

class FabricasModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_fabricante';
	protected $_primary = 'id';
}

class CodantigocrossModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_fabricante_novo';
	protected $_primary = 'id';
}

class CrossantigocrossModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_cross_reference_novo';
	protected $_primary = 'id';
}

class CrosshistoricoModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_crosshistorico';
	protected $_primary = 'id';
}


