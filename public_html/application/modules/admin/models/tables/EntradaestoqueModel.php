<?php
class EntradaestoqueModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_entradaztl';
	protected $_primary = 'id';
}

class EntradaestoqueprodModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_entradaztl_prod';
	protected $_primary = 'id';
}

class EntradaestoquetmpModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_entradaprodtmp';
	protected $_primary = 'id';
}

class EntradaestoquencmModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_entradaprodncm';
	protected $_primary = 'id';
}

class EntradaestoqueempresatmpModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_entradatmp';
	protected $_primary = 'id';
}

class EntradaestoquecmvModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_produtosentcmv';
	protected $_primary = 'id';
}

class EntradaestoqueqtatualModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_entradaqtatual';
	protected $_primary = 'id';
}

class EntradasimulacaoModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_entradasimulacao';
	protected $_primary = 'id';
}

class EntradasimulacaoprodModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_entradasimulacaoprod';
	protected $_primary = 'id';
}

class EntradasimulacaoadicoesModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_entradasimulacaoadicoes';
	protected $_primary = 'id';
}