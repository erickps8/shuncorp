<?php
class ProdutosModel extends Zend_Db_Table_Abstract {
	protected $_name = 'produtos';
	protected $_primary = 'ID';
}

class KitsModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_kits';
	protected $_primary = 'id';
}

class VeiculosprodModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_produto_veiculo';
	protected $_primary = 'id';
}

class HistoricopcvendaModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_historicopcvenda';
	protected $_primary = 'id';
}

class HistoricopccompraModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_historicopccompra';
	protected $_primary = 'id';
}

class HistoricopccomprachinaModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_historicopccomprachina';
	protected $_primary = 'id';
}

class ProdutosmediasModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_produtosmedidas';
	protected $_primary = 'id';
}

class ProdutosmaterialModel extends Zend_Db_Table_Abstract {
    protected $_name = 'tb_produtosmaterial';
    protected $_primary = 'id';
}
