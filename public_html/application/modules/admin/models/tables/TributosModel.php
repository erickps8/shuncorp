<?php
class TributosModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_tributosfiscais';
	protected $_primary = 'id';
}

class NcmModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_produtosncm';
	protected $_primary = 'id';
}


class HscodeModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_produtoshscode';
	protected $_primary = 'id';
}
