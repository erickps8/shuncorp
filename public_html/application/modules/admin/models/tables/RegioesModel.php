<?php
class RegioesModel extends Zend_Db_Table_Abstract {
	protected $_name = 'clientes_regioes';
	protected $_primary = 'ID';
}

class RegioesclientesModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_clientesregioes';
	protected $_primary = 'id';
}

class RegioestelevendasModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_regioestelevendas';
	protected $_primary = 'id';
}

class RegioesclientestelevendasModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_clientesregioestelevendas';
	protected $_primary = 'id';
}
