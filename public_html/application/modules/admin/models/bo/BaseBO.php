<?php
	abstract class BaseBO
    {
        public $db;

        public function __construct()
        {
            $this->db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
            $this->db->setFetchMode(Zend_Db::FETCH_OBJ);
        }
    }