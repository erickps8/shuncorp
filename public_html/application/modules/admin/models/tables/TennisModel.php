<?php
class TennisModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_jogadores';
	protected $_primary = 'id';
}

class FilatennisModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_fila';
	protected $_primary = 'id';
}

class PartidastennisModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_partidas';
	protected $_primary = 'id';
}

class HistoricotennisModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_historico';
	protected $_primary = 'id';
}