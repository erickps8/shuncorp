<?php
class LogModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_logacesso';
	protected $_primary = 'id';
}

class LogloginModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_logacessoent';
	protected $_primary = 'id';
}

class LogfalecomModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_log_falecom';
	protected $_primary = 'id_log_falecom';
}

class LogalteracoesModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_logacessoalteracoes';
	protected $_primary = 'id';
}