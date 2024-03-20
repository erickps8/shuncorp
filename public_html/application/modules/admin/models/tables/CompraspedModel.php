<?php
class CompraspedModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_kang_cominvoice';
	protected $_primary = 'id';
}

class ComprasprodModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_kang_cominvoiceprod';
	protected $_primary = 'id';
}
