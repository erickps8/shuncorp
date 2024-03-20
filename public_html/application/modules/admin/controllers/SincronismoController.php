<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class Admin_SincronismoController extends Zend_Controller_Action {

    private static $params;
    private static $paramsHbr;
    private $dump;
    private static $fileBackup;
    private static $conexao;

	public function init(){
        $config = new Zend_Config_Xml(APPLICATION_PATH . '/configs/application.xml', 'production');

	    date_default_timezone_set('America/Sao_Paulo');
		if ( !Zend_Auth::getInstance()->hasIdentity() ) {
			$this->_redirect('/');
		}

		self::$params = $config->resources->db->params;
        self::$paramsHbr = array(
            'dbhost' => 'mysql01.hbr3.hospedagemdesites.ws',
            'dbuser' => 'hbr3',
            'dbpass' => 'BdHbrNew2020',
            'dbname' => 'hbr3'
        );
		self::$fileBackup = basePublic . '/sistema/temp/backprodutos-ext.sql';

        self::$conexao = array (
            'host'              => self::$paramsHbr['dbhost'],
            'username'          => self::$paramsHbr['dbuser'],
            'password'          => self::$paramsHbr['dbpass'],
            'dbname'            => self::$paramsHbr['dbname'],
            'driver_options'    => array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8;')
        );

	}

	private function _executaSql()
    {
        $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
        $db->setFetchMode(Zend_Db::FETCH_OBJ);

        $file = file_get_contents(self::$fileBackup);
        $db->query($file);
    }
	
	function atualizaprodutosAction(){

        set_time_limit(0);
	    $this->_helper->layout->disableLayout();

	    $params = $this->_getAllParams();

	    //-- fabricantes ----------------------------------------------------------------------
	    if($params['etapa'] == 1){
	        
	        try{
        	   
        	    //--- fabricantes --------------------------------------------------------------------
        	    $bor = new RefcruzadaModel();
        	    $bof = new FabricasModel();
        	
        	    $db = Zend_Db::factory('PDO_MYSQL', self::$conexao);
        	    $db->setFetchMode(Zend_Db::FETCH_OBJ);
        	    $select = $db->select();
        	
        	    $select->from(array('f'=>'tb_fabricante','*'), array('*'));
        	
        	    $stmt = $db->query($select);
        	    $objFabricantes = $stmt->fetchAll();
        	
        	    foreach ($objFabricantes as $fabricante){
        	        $fabriLocal = $bof->fetchRow("id = '".$fabricante->id."'");  // veririco se o fabricante ja existe
        	        if(count($fabriLocal)>0){ // Se existe, atualizo
        	            $bof->update(array('no_fabricante' => $fabricante->no_fabricante), "id = '".$fabricante->id."'");
        	        }else{ // se nao existe, insiro ----
        	            $bof->insert(array('id' => $fabricante->id, 'no_fabricante' => $fabricante->no_fabricante, 'sit' => $fabricante->sit));
        	        }
        	    }
        	    
        	    echo true;
	        }catch (Zend_Exception $e){
                echo $e->getMessage();
            }
	    }
	
	    //--- produtos --------------------------------------------------------------------
	
	    //-- Realizo backup das tabelas relacionadas com produtos -----------------------------------------
	    if($params['etapa'] == 2){
    	    try{
                $tables = array(
                    'tb_montadora',
                    'tb_veiculo',
                    'tb_gruposprod',
                    'tb_gruposprodsub',
                    'tb_produtosncm'
                );

                $this->_execDump($tables);
    	        $this->_executaSql();

                echo true;
    	    }catch (Zend_Exception $e){
    	        echo $e->getMessage();
    	    }
	    }
	    
	    //-- backkup produtos ----------------------------------------------------------------------
	    if($params['etapa'] == 3){
	        try{
        	    $bop = new ProdutosModel();
        	
        	    $db = Zend_Db::factory('PDO_MYSQL', self::$conexao);
        	    $db->setFetchMode(Zend_Db::FETCH_OBJ);
        	    $select = $db->select();
        	
        	    $select->from(array('p'=>'produtos','*'), array('*'));
        	
        	    $stmt = $db->query($select);
        	    $objProdutos = $stmt->fetchAll();
        	
        	    foreach ($objProdutos as $produtos){
        	
        	        $data = array(
    	                'ID'                 => $produtos->ID,
    	                'CODIGO'             => $produtos->CODIGO,
    	                'codigo_ean'         => $produtos->codigo_ean,
    	                'DESCRICAO'   	     => $produtos->DESCRICAO,
    	                'APLICACAO'   	     => $produtos->APLICACAO,
    	                'PESO'				 => $produtos->PESO,
    	                'id_ncm'			 => $produtos->id_ncm,
    	                'codigo_mask'		 => $produtos->codigo_mask,
    	                'id_gruposprodsub'	 => $produtos->id_gruposprodsub,
    	                'unidade'			 => $produtos->unidade,
    	                'dt_cadastro'		 => $produtos->dt_cadastro,
    	                'M_INNER'		     => $produtos->M_INNER,
    	                'M_OUTER'		     => $produtos->M_OUTER,
    	                'M_HIGH'		     => $produtos->M_HIGH,
    	                'estriado_macho_d'	 => $produtos->estriado_macho_d,
    	                'estriado_macho_mm'	 => $produtos->estriado_macho_mm,
    	                'estriado_femea_d'	 => $produtos->estriado_femea_d,
    	                'estriado_femea_mm'	 => $produtos->estriado_femea_mm,
    	                'ins_aperto_homo'	 => $produtos->ins_aperto_homo,
    	                'diametro_homo'		 => $produtos->diametro_homo,
    	                'raio_porca_homo'	 => $produtos->raio_porca_homo,
    	                'aperto_homo'		 => $produtos->aperto_homo,
    	                'medida_inner_desl'	 => $produtos->medida_inner_desl,
    	                'medida_outer_desl'	 => $produtos->medida_outer_desl,
    	                'medida_high_desl'	 => $produtos->medida_high_desl,
    	                'medida_teeth_cru'	 => $produtos->medida_teeth_cru,
        	        );
        	
        	        //validacao do codigo
        	       /*  $prodCod = $bop->fetchRow("CODIGO = '".$produtos->CODIGO."'");
        	         
        	        if(count($prodCod)>0){ 
        	            $bop->update(array('CODIGO' => 'UP'), "ID = '".$prodCod->ID."'");
        	        } */
        	        
        	        
        	        $prodLocal = $bop->fetchRow("ID = '".$produtos->ID."'");
        	
        	        if(count($prodLocal)>0){ // Se existe, atualizo
        	            $bop->update($data, "ID = '".$produtos->ID."'");
        	        }else{ // se nao existe, insiro ----
        	            $bop->insert($data);
        	        }
        	    }
        	    
        	    echo true;
        	     
    	    }catch (Zend_Exception $e){
    	        echo $e->getMessage();
    	    }
	    }
	    
	    //-- Realizo backup das tabelas relacionadas com produtos -----------------------------------------
	    if($params['etapa'] == 4){
	        try{

                $tables = array(
                    'tb_produto_veiculo',
                    'tb_produtosmedidas',
                    'tb_crossprodutos',
                    'tb_crossreference',
                    'tb_kits'
                );

                foreach ($tables as $table){
                    $this->_execDump(array($table));
                }

                echo true;

    	    }catch (Zend_Exception $e){
    	        echo $e->getMessage();
    	    }
	    }
	    
	    exit();
	}

	private function _execDump($tables)
    {
        $dumpSettings = array(
            'include-tables' => $tables,
            'add-drop-table' => true,
        );

        include_once(APPLICATION_PATH . '/../library/mysqldump/src/Ifsnop/Mysqldump/Mysqldump.php');

        $dump = new Ifsnop\Mysqldump\Mysqldump(
            'mysql:host='.self::$paramsHbr['dbhost'].';dbname='.self::$paramsHbr['dbname'],
            self::$paramsHbr['dbuser'],
            self::$paramsHbr['dbpass'],
            $dumpSettings
        );

        $dump->start(self::$fileBackup);

        $this->_executaSql();
    }
}
