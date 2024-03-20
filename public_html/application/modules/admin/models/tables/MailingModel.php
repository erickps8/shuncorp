<?php
class MailingModel extends Zend_Db_Table_Abstract {
	protected $_name = 'mailing_enviados';
	protected $_primary = 'id';
}

class MailingenviadosModel extends Zend_Db_Table_Abstract {
	protected $_name = 'mailing_lancamentos';
	protected $_primary = 'id';
}

class MailingemailsModel extends Zend_Db_Table_Abstract {
	protected $_name = 'mailing';
	protected $_primary = 'ID';
}

class MailingemailsdescadastraModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_mailingdescadastra';
	protected $_primary = 'id';
}

class MailingcorrigeModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_mailingcorrige';
	protected $_primary = 'id';
}

class MailingtmpModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_emailtmp';
	protected $_primary = 'id';
}