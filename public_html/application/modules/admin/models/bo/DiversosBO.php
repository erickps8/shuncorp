<?php
class DiversosBO{
	
	function pogremoveAcentos($var, $enc = 'UTF-8'){
		$acentos = array(
			'A' => '/À|Á|Â|Ã|Ä|Å|&Agrave;|&Aacute;|&Acirc;|&Atilde;|&Auml;|&Aring;/',
			'a' => '/à|á|â|ã|ä|å|&agrave;|&aacute;|&acirc;|&atilde;|&auml;|&aring;/',
			'C' => '/Ç|&Ccedil;/',
			'c' => '/ç|&ccedil;/',
			'E' => '/È|É|Ê|Ë|&Egrave;|&Eacute;|&Ecirc;|&Euml;/',
			'e' => '/è|é|ê|ë|&egrave;|&eacute;|&ecirc;|&euml;|&amp;|&;/',
			'I' => '/Ì|Í|Î|Ï|&Igrave;|&Iacute;|&Icirc;|&Iuml;/',
			'i' => '/ì|í|î|ï|&igrave;|&iacute;|&icirc;|&iuml;/',
			'N' => '/Ñ|&Ntilde;/',
			'n' => '/ñ|&ntilde;/',
			'O' => '/Ò|Ó|Ô|Õ|Ö|&Ograve;|&Oacute;|&Ocirc;|&Otilde;|&Ouml;/',
			'o' => '/ò|ó|ô|õ|ö|&ograve;|&oacute;|&ocirc;|&otilde;|&ouml;/',
			'U' => '/Ù|Ú|Û|Ü|&Ugrave;|&Uacute;|&Ucirc;|&Uuml;/',
			'u' => '/ù|ú|û|ü|&ugrave;|&uacute;|&ucirc;|&uuml;/',
			'Y' => '/Ý|&Yacute;/',
			'y' => '/ý|ÿ|&yacute;|&yuml;/',
			'a.' => '/ª|&ordf;/',
			'o.' => '/º|&ordm;/',
		    ''	=> '/&lsquo;/'
		);
		$var = preg_replace($acentos, array_keys($acentos), $var);
		
		return $var;
	}
	
	function enviaMail($assunto, $texto, $resp, $email){
				
		$mailTransport = new Zend_Mail_Transport_Smtp("smtp.ztlbrasil.com.br", Zend_Registry::get('mailSmtp'));

		$mail = new Zend_Mail('utf-8');
		$mail->setFrom("info@ztlbrasil.com.br");
		$mail->addTo($email,$resp);
		$mail->setBodyHtml($texto);
		$mail->setSubject($assunto);
		if(!$mail->send($mailTransport)){
		    throw new Exception("Erro ao enviar email");
		    return false;
		}else{
			return true;
		}		
	}
	
	/**
	 * criarDiretorio cria pastas e/ou altera permissoes de pastas dentro da aplicacao
	 * @name criarDiretorio
     * @author Cleitonsb
     * @param    string  $pasta  string contendo o endereco completo da pasta
     * @return   boolean  
	 */
	public function criarDiretorio($pasta){ 
		if($pasta!=""){
		    
			if (!(is_dir($pasta))){
				if(!(mkdir($pasta, 0777))){
				    throw new Exception("Pasta de upload nao existe, e nao pode ser criada");
				    return false;
				}				
			}
			
			if(!(is_writable($pasta))){
			    //throw new Exception("Pasta sem permissao de escrita");
			    return false;
			}
						
			return true;
			
		}else{
			throw new Exception("Variavel diretorio vazia.");
			return false;
		}
	
		
	}
	
	
	function diffDate($d1, $d2, $type='', $sep='-'){
		$d1 = explode($sep, $d1);
		$d2 = explode($sep, $d2);
		switch ($type){
			case 'A':
				$X = 31536000;
				break;
			case 'M':
				$X = 2592000;
				break;
			case 'D':
				$X = 86400;
				break;
			case 'H':
				$X = 3600;
				break;
			case 'MI':
				$X = 60;
				break;
			default:
				$X = 1;
		}
		
		return floor( ( ( mktime(0, 0, 0, $d2[1], $d2[2], $d2[0]) - mktime(0, 0, 0, $d1[1], $d1[2], $d1[0] ) ) / $X ) );
	}
	
	function gravarReporte($params){
	    try{
		    $usuario = Zend_Auth::getInstance()->getIdentity();
		    $bo = new ReporteModel();
		    
		    $data = array(
		    	'descricao' 	=> $params['reporte'],
		        'pagina'		=> $params['pagina'],
		        'id_usuarios'	=> (count($usuario)>0) ? $usuario->id : null,
		        'data'			=> date("Y-m-d H:i:s")
		    );
		    
		    $bo->insert($data);
	    }catch (Zend_Exception $e){
	    	$boerro	= new ErrosModel();
	    	$dataerro = array('descricao' => $e->getMessage(), 'pagina' => "DiversosBO::gravarReporte()");
	    	$boerro->insert($dataerro);
	    	return 'erro';
	    }
	}
	
	function importaArquivo($arq){
	    $handle = fopen($arq,"r");
	    $conteudo = @file_get_contents($handle);
	    $bot 	= new TributosModel();
	    $bo 	= new NcmModel();
	    
	    while ($linha = fgets($handle)){
			
			$empresa = array();
			$empresa = explode(";", $linha);
			
			if($empresa['2'] == 0){
			
			    foreach ($bo->fetchAll() as $ncm){
			    	if(str_replace(".", "", $ncm->ncm) == $empresa['0']){
			    	    
			    	    echo $linha;
			    	    echo "<br />";
			    	    echo $ncm->ncm;
			    	    echo "<br />";
						$data = array(
							'ibpt_aliqnac'		=> trim(str_replace(",", ".", $empresa[3])),
							'ibpt_aliqimp' 		=> trim(str_replace(",", ".", $empresa[4]))
							
						);
						
						$bo->update($data, "id = ".$ncm->id);
						
			    	}
				
			    }
				
			}
			
	    }
	    
	    
	}
	
	
	function corrigeErros(){
	    $bov		= new PedidosvendaModel();
	    $bop		= new PedidosvendaprodModel();
	    $bopr		= new ProdutosModel();
	    $boc		= new ProdutoscmvModel();
	    
	    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
	    $db->setFetchMode(Zend_Db::FETCH_OBJ);
	    
	    $select = $db->select();
	    $select->from(array('p'=>'tb_pedidos_prod','*'), array('p.*'))
	    	->where("p.custocompra > p.preco_unit and p.id_prod != 99");
	    
	    $stmt = $db->query($select);
	    $objpedidos = $stmt->fetchAll();
	    	
	    foreach ($objpedidos as $pedidos){
	        
	        $cmv = $boc->fetchRow("id = (select max(v.id) from tb_produtoscmv v where v.id_produtos = ".$pedidos->id_prod.")");
	        
	        echo $pedidos->id_ped;
	        echo " - ";
	        echo $pedidos->id_prod;
	        echo " - ";
	        echo $pedidos->custocompra;
	        echo " - ";
	        echo $pedidos->preco_unit;
	        echo " - ";
	        echo $cmv->valor;
	        echo "<br />";
	        
	        if($cmv->valor){
	        	$bop->update(array('custocompra' => $cmv->valor), "id = ".$pedidos->id);
	        }
	        
	    }
	}	
	
	
	function buscaVendas(){
	    $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
	    $db->setFetchMode(Zend_Db::FETCH_OBJ);
	     
	    $select = $db->select();
	    $select->from(array('p'=>'tb_pedidos','*'), array('pr.*', 'v.valor as valorcmv','p.ID as idpedido'))
	    	->join(array('pr'=>'tb_pedidos_prod'), 'pr.id_ped = p.id')
	    	->join(array('prod'=>'produtos'),'pr.id_prod = prod.ID')
	    	->joinLeft(array('v'=>'tb_produtoscmv'),'pr.id_prod = v.id_produtos and v.id = (select max(v.id) from tb_produtoscmv v where v.id_produtos = pr.id_prod)')
	    	->joinLeft(array('pc'=>'tb_produtosclasses'),'pc.id = prod.id_produtosclasses')
	    	->where("p.data_vend between '2014-03-01' and '2014-04-01' ");
	     
	    $stmt = $db->query($select);
	    $objpedidos = $stmt->fetchAll();
	    
	    $totalvenda = $totalvendapret = $totalvendamin = $totalcmv = 0;
	    
	    foreach ($objpedidos as $pedidos){
	        $totalvenda += $pedidos->qt*$pedidos->preco_unit;
	        
	        /* echo $pedidos->idpedido;
	        echo " - ";
	        echo $pedidos->preco_unit;
	        echo " - ";
			echo $pedidos->valorcmv;
			echo " - ";
			echo $pedidos->markup;
			echo " - ";
			echo $pedidos->valorcmv + (($pedidos->valorcmv * $pedidos->markup)/100);
			
			echo "<br />";  */
	         
	        $totalvendapret += (!empty($pedidos->markup)) ? $pedidos->qt * ($pedidos->valorcmv + (($pedidos->valorcmv * $pedidos->markup)/100)) : $pedidos->qt * $pedidos->preco_unit;
	        $totalvendamin += (!empty($pedidos->markupmin)) ? $pedidos->qt * ($pedidos->valorcmv + (($pedidos->valorcmv * $pedidos->markupmin)/100)) : $pedidos->qt * $pedidos->preco_unit;
	        $totalcmv += $pedidos->qt*$pedidos->valorcmv;
	        
	        echo (empty($pedidos->markup)) ? $pedidos->CODIGO."<br>" : "";
	       
	        
	    }
	    
	    echo $totalvenda;
	    echo "<br />";
		echo $totalvendapret;
		echo "<br />";
		echo $totalvendamin;
		echo "<br />";
		echo $totalcmv;
		echo "<br />";
		
		$select = $db->select();
		$select->from(array('p'=>'tb_pedidos','*'), array('pr.*', 'v.valor as valorcmv'))
		->join(array('pr'=>'tb_pedidos_prod'), 'pr.id_ped = p.id')
		->join(array('prod'=>'produtos'),'pr.id_prod = prod.ID')
		->joinLeft(array('v'=>'tb_produtoscmv'),'pr.id_prod = v.id_produtos and v.id = (select max(v.id) from tb_produtoscmv v where v.id_produtos = pr.id_prod)')
		->joinLeft(array('pc'=>'tb_produtosclasses'),'pc.id = prod.id_produtosclasses')
		->where("p.data_vend between '2014-04-01' and '2014-05-01' ");
		
		$stmt = $db->query($select);
		$objpedidos = $stmt->fetchAll();
		 
		$totalvenda = $totalvendapret = $totalvendamin = $totalcmv = 0;
	    
	    foreach ($objpedidos as $pedidos){
	        $totalvenda += $pedidos->qt*$pedidos->preco_unit;
	        
	        /* echo $pedidos->idpedido;
	        echo " - ";
	        echo $pedidos->preco_unit;
	        echo " - ";
			echo $pedidos->valorcmv;
			echo " - ";
			echo $pedidos->markup;
			echo " - ";
			echo $pedidos->valorcmv + (($pedidos->valorcmv * $pedidos->markup)/100);
			
			echo "<br />";  */
	         
	        $totalvendapret += (!empty($pedidos->markup)) ? $pedidos->qt * ($pedidos->valorcmv + (($pedidos->valorcmv * $pedidos->markup)/100)) : $pedidos->qt * $pedidos->preco_unit;
	        $totalvendamin += (!empty($pedidos->markupmin)) ? $pedidos->qt * ($pedidos->valorcmv + (($pedidos->valorcmv * $pedidos->markupmin)/100)) : $pedidos->qt * $pedidos->preco_unit;
	        $totalcmv += $pedidos->qt*$pedidos->valorcmv;
	        
	        echo (empty($pedidos->markup)) ? $pedidos->CODIGO."<br>" : "";
	    }
	    
	    echo $totalvenda;
	    echo "<br />";
		echo $totalvendapret;
		echo "<br />";
		echo $totalvendamin;
		echo "<br />";
		echo $totalcmv;
		echo "<br />";
		
		$select = $db->select();
		$select->from(array('p'=>'tb_pedidos','*'), array('pr.*', 'v.valor as valorcmv'))
		->join(array('pr'=>'tb_pedidos_prod'), 'pr.id_ped = p.id')
		->join(array('prod'=>'produtos'),'pr.id_prod = prod.ID')
		->joinLeft(array('v'=>'tb_produtoscmv'),'pr.id_prod = v.id_produtos and v.id = (select max(v.id) from tb_produtoscmv v where v.id_produtos = pr.id_prod)')
		->joinLeft(array('pc'=>'tb_produtosclasses'),'pc.id = prod.id_produtosclasses')
		->where("p.data_vend between '2014-05-01' and '2014-06-01' ");
		
		$stmt = $db->query($select);
		$objpedidos = $stmt->fetchAll();
		 
		$totalvenda = $totalvendapret = $totalvendamin = $totalcmv = 0;
	    
	    foreach ($objpedidos as $pedidos){
	        $totalvenda += $pedidos->qt*$pedidos->preco_unit;
	        
	        /* echo $pedidos->idpedido;
	        echo " - ";
	        echo $pedidos->preco_unit;
	        echo " - ";
			echo $pedidos->valorcmv;
			echo " - ";
			echo $pedidos->markup;
			echo " - ";
			echo $pedidos->valorcmv + (($pedidos->valorcmv * $pedidos->markup)/100);
			
			echo "<br />";  */
	         
	        $totalvendapret += (!empty($pedidos->markup)) ? $pedidos->qt * ($pedidos->valorcmv + (($pedidos->valorcmv * $pedidos->markup)/100)) : $pedidos->qt * $pedidos->preco_unit;
	        $totalvendamin += (!empty($pedidos->markupmin)) ? $pedidos->qt * ($pedidos->valorcmv + (($pedidos->valorcmv * $pedidos->markupmin)/100)) : $pedidos->qt * $pedidos->preco_unit;
	        $totalcmv += $pedidos->qt*$pedidos->valorcmv;
	        
	        echo (empty($pedidos->markup)) ? $pedidos->CODIGO."<br>" : "";
	    }
	    
	    echo $totalvenda;
	    echo "<br />";
		echo $totalvendapret;
		echo "<br />";
		echo $totalvendamin;
		echo "<br />";
		echo $totalcmv;
		echo "<br />";
	    
	}	
}
?>