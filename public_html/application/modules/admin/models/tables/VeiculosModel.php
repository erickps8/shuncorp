<?php
class VeiculosModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_veiculo';
	protected $_primary = 'id';
}

class FabricantesModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_fabricante';
	protected $_primary = 'id';
}
