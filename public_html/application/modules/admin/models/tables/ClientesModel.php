<?php
class ClientesModel extends Zend_Db_Table_Abstract {
	protected $_name = 'clientes';
	protected $_primary = 'ID';
}

class ClientesEmailModel extends Zend_Db_Table_Abstract {
	protected $_name = 'clientes_emails';
	protected $_primary = 'ID';
}

class ClientesEnderecoModel extends Zend_Db_Table_Abstract {
	protected $_name = 'clientes_endereco';
	protected $_primary = 'ID';
}

class ClientesEnderecoChinesModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_clientes_endereco';
	protected $_primary = 'ID';
}

class ClientesTelefoneModel extends Zend_Db_Table_Abstract {
	protected $_name = 'clientes_telefone';
	protected $_primary = 'ID';
}

class ClientesDescModel extends Zend_Db_Table_Abstract {
	protected $_name = 'clientes_desc';
	protected $_primary = 'id';
}

class ClientesClichinesModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_clientechina';
	protected $_primary = 'id';
}

class ClientesInfoKangModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_clientes_infokang';
	protected $_primary = 'id';
}

class ClientesanexoModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_clientesanexos';
	protected $_primary = 'id';
}

class ClientesgruposModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_clientesgrupos';
	protected $_primary = 'id';
}

class ClientesgruposprodModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_clientesgruposprod';
	protected $_primary = 'id';
}

class ClientesprecadastroModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_precadastroparceiro';
	protected $_primary = 'id';
}

class ClientesprecadastroanexosModel extends Zend_Db_Table_Abstract {
	protected $_name = 'tb_precadastroparceiroanexos';
	protected $_primary = 'id';
}

class ClientesconsigneeModel extends Zend_Db_Table_Abstract {
    protected $_name = 'tb_clienteconsignee';
    protected $_primary = 'id';
}