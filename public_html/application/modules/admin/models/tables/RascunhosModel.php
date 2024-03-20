<?php
class RascunhosModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_rascunho';
	protected $_primary = 'id_racunho';
}

class RascunhosprodModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_rascunhoprod';
	protected $_primary = 'id';
}
