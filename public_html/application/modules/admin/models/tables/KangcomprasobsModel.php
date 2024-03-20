<?php
/* tabela com os OBS --------------------
 */
class KangcomprasobsModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_kang_pedobs';
	protected $_primary = 'id';
}

class KangcomprasgruposobsModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_kanggrupospedobs';
	protected $_primary = 'id';
}

/* Obs dos pedidos de compra Kang --------------------
 */
class KangcomprasobspedModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_kang_obsped';
	protected $_primary = 'id';
}