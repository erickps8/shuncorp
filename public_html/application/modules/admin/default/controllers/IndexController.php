<?php

class IndexController extends Zend_Controller_Action
{

    public function init(){
        $params = $this->_getAllParams();
    	$usuario = Zend_Auth::getInstance()->getIdentity();
		if(!empty($usuario->ID)){
			//echo "<script> location.href = 'admin/painel'</script>";
		}
		
		if(!empty($params['idioma'])):
			$sessao = new Zend_Session_Namespace('Idiomas');
		    $sessao->idioma = $params['idioma'];
		    
		    $url = str_replace("?".$_SERVER["QUERY_STRING"],"",str_replace("/public/","", $_SERVER['REQUEST_URI']));
		    $this->_redirect($url);
		endif;		
    }

    public function indexAction(){
    	$this->_helper->layout->setLayout('sitepadrao');
        $this->view->translate	=	Zend_Registry::get('translate');
        
        if($this->_request->isPost()):
        	$params = $this->_getAllParams();
        	if($params['email']):
        		$this->view->objConf = ContatoBO::cadNewslatter($this->_getAllParams());
        	endif;
        endif;
    	
    }
   
	public function recuperasenhaAction(){
        $this->_helper->layout->setLayout('sitepadrao');
        $this->view->translate	=	Zend_Registry::get('translate');
        if($this->_request->isPost()):
        	$params = $this->_getAllParams();
        	if($params['cpf_cnpj']):
				$this->view->objRec = ContatoBO::recuperarSenha($this->_getAllParams());
        	endif;
        endif;
    }
    
	public function trocarsenhaAction(){
        $this->_helper->layout->setLayout('sitepadrao');
        $this->view->translate	=	Zend_Registry::get('translate');
        $this->view->objUser	= 	ContatoBO::trocaSenhadados($this->_getAllParams());
        
        if($this->_request->isPost()):
        	ContatoBO::trocaSenha($this->_getAllParams());
        	$this->view->objConf = "sucesso"; 
        endif;
    }
    

    
    public function crontabAction(){
    	$this->_helper->layout->disableLayout();
    	
    	exit();
    }    
    
    function sincronizacaoAction(){
        $this->_helper->layout->disableLayout();
         
        $dbhost = 'mysql01.hbr3.hospedagemdesites.ws';
        $dbuser = 'hbr3';
        $dbpass = 'BdMySql2008';
        $dbname = 'hbr3';
        
        $dbhost2 = 'mysql01.shuncorp.hospedagemdesites.ws';
        $dbuser2 = 'shuncorp';
        $dbpass2 = 'BdMySql2008';
        $dbname2 = 'shuncorp';
        
        //-- fabricantes ----------------------------------------------------------------------
        $conexao = array (
            'host'              => 'mysql01.hbr3.hospedagemdesites.ws',
            'username'          => 'hbr3',
            'password'          => 'BdMySql2008',
            'dbname'            => 'hbr3',
            'driver_options'    => array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8;')
        );
        
        //--- fabricantes --------------------------------------------------------------------
        $bor = new RefcruzadaModel();
        $bof = new FabricasModel();
        
        $db = Zend_Db::factory('PDO_MYSQL', $conexao);
        $db->setFetchMode(Zend_Db::FETCH_OBJ);
        $select = $db->select();
        
        $select->from(array('f'=>'tb_fabricante','*'), array('*'));
        
        $stmt = $db->query($select);
        $objFabricantes = $stmt->fetchAll();
        
        foreach ($objFabricantes as $fabricante){
            
            echo $fabricante->id;
            echo "<br>";
            
            $fabriLocal = $bof->fetchRow("id = '".$fabricante->id."'");  // veririco se o fabricante ja existe
            if(count($fabriLocal)>0){ // Se existe, atualizo
                $bof->update(array('no_fabricante' => $fabricante->no_fabricante), "id = '".$fabricante->id."'");
            }else{ // se nao existe, insiro ----
                $bof->insert(array('id' => $fabricante->id, 'no_fabricante' => $fabricante->no_fabricante, 'sit' => $fabricante->sit));
            }      
        }
        
        //--- produtos --------------------------------------------------------------------
        
        //-- Realizo backup das tabelas relacionadas com produtos -----------------------------------------
        $backupfile = '/home/shuncorp/public_html/public/sistema/temp/backprodutos.sql';
        
        echo system("mysqldump -h $dbhost -u $dbuser -p$dbpass --lock-tables $dbname tb_montadora tb_veiculo tb_gruposprod tb_gruposprodsub tb_produtosncm > $backupfile");
        echo system("mysql -h $dbhost2 -u $dbuser2 -p$dbpass2 $dbname2 < $backupfile");        
        
        $bop = new ProdutosModel();
        
        $db = Zend_Db::factory('PDO_MYSQL', $conexao);
        $db->setFetchMode(Zend_Db::FETCH_OBJ);
        $select = $db->select();
        
        $select->from(array('p'=>'produtos','*'), array('*'));
        
        $stmt = $db->query($select);
        $objProdutos = $stmt->fetchAll();
        
        foreach ($objProdutos as $produtos){
        
            echo $produtos->ID;
            echo "<br>";
            
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
            
            $prodLocal = $bop->fetchRow("ID = '".$produtos->ID."'");  // veririco se o fabricante ja existe
            
            if(count($prodLocal)>0){ // Se existe, atualizo
                $bop->update($data, "ID = '".$produtos->ID."'");
            }else{ // se nao existe, insiro ----
                $bop->insert($data);
            }
        }
        
        //-- Realizo backup das tabelas relacionadas com produtos -----------------------------------------
        $backupfile = '/home/shuncorp/public_html/public/sistema/temp/backprodutos.sql';
        
        echo system("mysqldump -h $dbhost -u $dbuser -p$dbpass --lock-tables $dbname tb_produto_veiculo tb_produtosmedidas tb_crossprodutos tb_crossreference tb_kits > $backupfile");
        echo system("mysql -h $dbhost2 -u $dbuser2 -p$dbpass2 $dbname2 < $backupfile");
                
        exit();
    }
    
    function atualizaprodutosAction(){
        $this->_helper->layout->disableLayout();
    
        $boprod	        = new ProdutosModel();
        
        $kit            = new KitsModel();
        $historico      = new HistoricopccomprachinaModel();
        $medidas        = new ProdutosmediasModel();
        
        $kc             = new KangcomprasModel();
        $kcompras       = new KangcomprasprodModel();
        $kentrage       = new KangcomprasentregaModel();
        
        $k              = new KangvendasModel();
        $kvenda         = new VendasprodModel();
        $kconinvoice    = new KanginvoiceprodModel();
        $kpacklist      = new PacklistprodModel();
        $ktmp           = new KangorcamentosprodModel();
        
        $t              = new TaicomprasModel();
        $tcompraprod    = new TaicomprasprodModel();
        $tentrega       = new EntregaprodModel();
        $tpreordemprod  = new PreordemprodModel();
        $tpreordemkit   = new PreordemprodkitModel();
        
        $conexao = array (
            'host'              => 'mysql01.hbr3.hospedagemdesites.ws',
            'username'          => 'hbr3',
            'password'          => 'BdMySql2008',
            'dbname'            => 'hbr3',
            'driver_options'    => array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8;')
        );
    
        $db = Zend_Db::factory('PDO_MYSQL', $conexao);
        $db->setFetchMode(Zend_Db::FETCH_OBJ);
    
        $select = $db->select();
        $select->from(array('p'=>'produtos','*'), array('*'));
    
        $stmt = $db->query($select);
        $objProdutos = $stmt->fetchAll();
    
        try{
            
             foreach ($objProdutos as $produtos){
                                
                $prodlocal = "";                
                $prodlocal = $boprod->fetchRow("CODIGO = '".$produtos->CODIGO."'");
    
                if(count($prodlocal) > 0){
                    if($produtos->ID != $prodlocal->ID){                    
                        echo $produtos->CODIGO;
                        echo " - ";
                        echo $produtos->ID;
                        echo " - "; 
                        echo $prodlocal->ID;
                        echo " - ";
                        
                        echo $idprod = $prodlocal->ID + 10000;
                        
                        $dataProd = array(
                            'ID'                                => $idprod,
                            'CODIGO'                            => $produtos->CODIGO,
                            'id_cliente_fornecedor_shuntai'     => $prodlocal->id_cliente_fornecedor_shuntai,
                            'supplier_code'                     => $prodlocal->supplier_code,
                            'tipo_moeda_shuntai'                => $prodlocal->tipo_moeda_shuntai,
                            'custo_valor_shuntai'               => $prodlocal->custo_valor_shuntai,
                            'id_cliente_shuntai'                => $prodlocal->id_cliente_shuntai,
                            'cod_shuntai'                       => $prodlocal->cod_shuntai,
                            'moeda_shuntai'                     => $prodlocal->moeda_shuntai,
                            'custo_shuntai'                     => $prodlocal->custo_shuntai,
                            'Purchasing_group'                  => $prodlocal->Purchasing_group,
                            'pl_prod_desc'                      => $prodlocal->pl_prod_desc,
                            'pl_prod_desc_pt'                   => $prodlocal->pl_prod_desc_pt,
                            'purchasing_det'                    => $prodlocal->purchasing_det,
                            'id_hscode'                         => $prodlocal->id_hscode,
                            'id_produtosmaterial'               => $prodlocal->id_produtosmaterial,
                        );
                        
                        $boprod->insert($dataProd);   
                                             
                        $kit->update(array('id_prod' => $idprod), "id_prod = '".$prodlocal->ID."'");
                        $kit->update(array('id_prodkit' => $idprod), "id_prodkit = '".$prodlocal->ID."'");                        
                        $historico->update(array('id_produtos' => $idprod), "id_produtos = '".$prodlocal->ID."'");
                        $medidas->update(array('id_prod' => $idprod), "id_prod = '".$prodlocal->ID."'");                                            
                        $kcompras->update(array('id_prod' => $idprod), "id_prod = '".$prodlocal->ID."'");
                        $kentrage->update(array('id_prod' => $idprod), "id_prod = '".$prodlocal->ID."'");                                            
                        $kvenda->update(array('ID_PRODUTO' => $idprod), "ID_PRODUTO = '".$prodlocal->ID."'");
                        $kconinvoice->update(array('id_prod' => $idprod), "id_prod = '".$prodlocal->ID."'");
                        $kpacklist->update(array('id_prod' => $idprod), "id_prod = '".$prodlocal->ID."'");
                        $ktmp->update(array('id_prod' => $idprod), "id_prod = '".$prodlocal->ID."'");                        
                        $tcompraprod->update(array('id_prod' => $idprod), "id_prod = '".$prodlocal->ID."'");
                        $tentrega->update(array('id_prod' => $idprod), "id_prod = '".$prodlocal->ID."'");
                        $tpreordemprod->update(array('id_produtos' => $idprod), "id_produtos = '".$prodlocal->ID."'");
                        $tpreordemkit->update(array('id_prod' => $idprod), "id_prod = '".$prodlocal->ID."'");
                        
                        $boprod->delete("ID = '".$prodlocal->ID."'");
                        
                        echo "<br>";                        
                    }   
                }                
            }
             
            echo "<br><br><br><br>--------------------------------------------------------<br><br><br><br>";

            foreach ($objProdutos as $produtos){
            
                $prodlocal = "";
                $prodlocal = $boprod->fetchRow("CODIGO = '".$produtos->CODIGO."'");
            
                if(count($prodlocal) > 0){
                    if($produtos->ID != $prodlocal->ID){
                        echo $produtos->CODIGO;
                        echo " - ";
                        echo $produtos->ID;
                        echo " - ";
                        echo $prodlocal->ID;
                        echo " - ";
            
                        echo $idprod = $produtos->ID;
                        
                        $dataProd = array(
                            'ID'                                => $idprod,
                            'CODIGO'                            => $produtos->CODIGO,
                            'id_cliente_fornecedor_shuntai'     => $prodlocal->id_cliente_fornecedor_shuntai,
                            'supplier_code'                     => $prodlocal->supplier_code,
                            'tipo_moeda_shuntai'                => $prodlocal->tipo_moeda_shuntai,
                            'custo_valor_shuntai'               => $prodlocal->custo_valor_shuntai,
                            'id_cliente_shuntai'                => $prodlocal->id_cliente_shuntai,
                            'cod_shuntai'                       => $prodlocal->cod_shuntai,
                            'moeda_shuntai'                     => $prodlocal->moeda_shuntai,
                            'custo_shuntai'                     => $prodlocal->custo_shuntai,
                            'Purchasing_group'                  => $prodlocal->Purchasing_group,
                            'pl_prod_desc'                      => $prodlocal->pl_prod_desc,
                            'pl_prod_desc_pt'                   => $prodlocal->pl_prod_desc_pt,
                            'purchasing_det'                    => $prodlocal->purchasing_det,
                            'id_hscode'                         => $prodlocal->id_hscode,
                            'id_produtosmaterial'               => $prodlocal->id_produtosmaterial,
                        );                        
                        
                        if($produtos->ID != 26){
                            $boprod->insert($dataProd);
                        }
                        
                        $kit->update(array('id_prod' => $idprod), "id_prod = '".$prodlocal->ID."'");
                        $kit->update(array('id_prodkit' => $idprod), "id_prodkit = '".$prodlocal->ID."'");
                        $historico->update(array('id_produtos' => $idprod), "id_produtos = '".$prodlocal->ID."'");
                        $medidas->update(array('id_prod' => $idprod), "id_prod = '".$prodlocal->ID."'");
                        $kcompras->update(array('id_prod' => $idprod), "id_prod = '".$prodlocal->ID."'");
                        $kentrage->update(array('id_prod' => $idprod), "id_prod = '".$prodlocal->ID."'");
                        $kvenda->update(array('ID_PRODUTO' => $idprod), "ID_PRODUTO = '".$prodlocal->ID."'");
                        $kconinvoice->update(array('id_prod' => $idprod), "id_prod = '".$prodlocal->ID."'");
                        $kpacklist->update(array('id_prod' => $idprod), "id_prod = '".$prodlocal->ID."'");
                        $ktmp->update(array('id_prod' => $idprod), "id_prod = '".$prodlocal->ID."'");
                        $tcompraprod->update(array('id_prod' => $idprod), "id_prod = '".$prodlocal->ID."'");
                        $tentrega->update(array('id_prod' => $idprod), "id_prod = '".$prodlocal->ID."'");
                        $tpreordemprod->update(array('id_produtos' => $idprod), "id_produtos = '".$prodlocal->ID."'");
                        $tpreordemkit->update(array('id_prod' => $idprod), "id_prod = '".$prodlocal->ID."'");
            
                        $boprod->delete("ID = '".$prodlocal->ID."'");
            
                        echo "<br>";
                    }
                }
            }
    
        }catch (Zend_Exception $e){
            echo $e->getMessage();
        }
    
        exit();
    }
}


