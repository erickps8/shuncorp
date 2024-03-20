<?php
class AtividadesModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_atividades';
	protected $_primary = 'id';
}

class AtividadesuserModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_atividadesusuarios';
	protected $_primary = 'id';
}

class AtividadesleituraModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_atividadesintleitura';
	protected $_primary = 'id';
}

class AtividadesinteracaoModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_atividadesinteracao';
	protected $_primary = 'id';
}
