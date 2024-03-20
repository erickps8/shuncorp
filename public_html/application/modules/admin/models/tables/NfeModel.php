<?php
class NfeModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_nfe';
	protected $_primary = 'id';
}

class NfeprodModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_nfeprod';
	protected $_primary = 'id';
}

class NfetmpModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_nfetmp';
	protected $_primary = 'id';
}

class NfeprodtmpModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_nfeprodtmp';
	protected $_primary = 'id';
}

class NfecceModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_nfecorrecao';
	protected $_primary = 'id';
}

class NferemessaModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_nferemessas';
	protected $_primary = 'id';
}

class NferecusaModel extends Zend_Db_Table_Abstract {
    protected $_name = 'tb_nfemotivorecusa';
    protected $_primary = 'id';
}