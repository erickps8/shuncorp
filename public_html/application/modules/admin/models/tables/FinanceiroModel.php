<?php
class FinanceiroModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_financeirocontas';
	protected $_primary = 'id';
}

class FinanceiropagarModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_financeiropag';
	protected $_primary = 'id';
}

class FinanceiropagarparcModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_financeiropagparc';
	protected $_primary = 'id';
}

class FinanceiroreceberModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_financeirorec';
	protected $_primary = 'id';
}

class FinanceiroreceberparcModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_financeirorecparc';
	protected $_primary = 'id';
}

class FinanceiroplanocontasModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_financeiroplcontas';
	protected $_primary = 'id';
}

class FinanceiroplanocontasoldModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_planocontas';
	protected $_primary = 'id';
}

class FinanceiroplanocontascatModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_planocontascat';
	protected $_primary = 'id';
}

class FinanceiroanexopagarModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_finanexopagar';
	protected $_primary = 'id';
}

class FinanceiroanexoreceberModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_finanexoreceber';
	protected $_primary = 'id';
}

class FinanceiroconciliaModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_financeiroconcilia';
	protected $_primary = 'id';
}

class FinanceirocreditosModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_financeirocredito';
	protected $_primary = 'id';
}