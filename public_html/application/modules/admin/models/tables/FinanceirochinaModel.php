<?php
class FinanceirochinaModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_fin_contasapagar';
	protected $_primary = 'id';
}

class FinanceirochinapagarModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_fin_contasapagar';
	protected $_primary = 'id';
}

class FinanceirochinareceberModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_fin_contasareceber';
	protected $_primary = 'id';
}

class FinanceirochinaanexopagModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_fin_anexopagar';
	protected $_primary = 'id';
}

class FinanceirochinaanexorecModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_fin_anexoreceber';
	protected $_primary = 'id';
}

class FinanceirochinapurchaseModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_finpurchase';
	protected $_primary = 'id';
}

class FinanceirochinainvoiceModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_fininvoice';
	protected $_primary = 'id';
}