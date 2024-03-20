<?php
	class TributosBO{
				
		//---NCM-------------------------------------------------------------------
		/*--Utilizado em produtoscad
		 */
		function listaNcm(){
			$ob 	= new TributosModel();
			$obj 	= new NcmModel();
			return $obj->fetchAll("sit = true","ncm asc");			
		}
		
		function buscaNcm($pesq){
						
		    if(isset($pesq['ncm']) || isset($pesq['ncmdesc'])){
		        
		        $where = (isset($pesq['ncm'])) ? "md5(p.id) = '".$pesq['ncm']."'" : "";
		        $where = (isset($pesq['ncmdesc'])) ? "p.ncm = '".$pesq['ncmdesc']."'" : $where;
		        
		        $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
				$db->setFetchMode(Zend_Db::FETCH_OBJ);
				$select = $db->select();
							
				$select->from(array('p'=>'tb_produtosncm','*'), array('p.*','p.id as idncm'))
				 	->where($where);
				  		
				$stmt = $db->query($select);
				return $stmt->fetchAll(); 	
		    }
		}
		
		function gravarNcm($params){
		    try{
				$ob 	= new TributosModel();
				$obj 	= new NcmModel();
	
				
				$icms = $ii = $ipi = $pis = $cofins = NULL;
				
				if($params['ii'] != "")			   $ii 	    = str_replace(",", ".", $params['ii']);
				if($params['ipi'] != "") 		   $ipi 	= str_replace(",", ".", $params['ipi']);
				if($params['pis'] != "")		   $pis	    = str_replace(",", ".", $params['pis']);
				if($params['cofins'] != "")		   $cofins  = str_replace(",", ".", $params['cofins']);
				if($params['icms'] != "") 		   $icms	= str_replace(",", ".", $params['icms']);
				if($params['icmsoriginal'] != "")  $icmsor	= str_replace(",", ".", $params['icmsoriginal']);
				
				$array = array(
					'ncm'					=> $params['ncm'],
					'ncmex'					=> $params['ncmex'],
					'sit'					=> true,			
					'ii'					=> $ii,
					'ipi'					=> $ipi,
					'pis'					=> $pis,
					'cofins'				=> $cofins,
				    'icms'					=> $icms,
				    'icmsoriginal'			=> $icmsor,
					'id_tributocsticms'		=> $params['csticms'],
					'id_tributocstipi'		=> $params['cstipi'],
					'id_tributocstpis'		=> $params['cstpis'],
					'id_tributocstcofins'	=> $params['cstcofins']
				);
				
				if(empty($params['idncm'])):
					 $idncm = $obj->insert($array);				 			 
				else:
					 $obj->update($array,"id = ".$params['idncm']);
				endif; 	
				return true;
			}catch (Zend_Exception $e){
				$boerro	= new ErrosModel();
				$dataerro = array('descricao' => $e->getMessage(), 'pagina' => 'TributosBO::gravarNcm()');
				$boerro->insert($dataerro);
				return false;
			}		
		}
		
		//-- NCM dos perfils de clientes ------------------------------------------------
		/*-- Utilizado em despesasfiscaisncm ------*/
		function buscaNcmclientes($pesq){
			$ob 	= new TributosModel();
			$obt	= new NcmclientesModel();
			return $obt->fetchAll("sit = true and md5(id) = '".$pesq['ncmcli']."'");
		}
		
		function listaNcmclientes($pesq){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
		
			$select->from(array('p'=>'tb_produtosncm','*'), array('p.*','p.id as idncm', 't.id as idncmcli'))
				->join(array('t' => 'tb_tributosncm'), 't.id_produtosncm = p.id')
				->where("p.sit = true and t.sit = true and md5(id_tributosfiscais) = '".$pesq['desp']."'")
				->order('ncm asc');
				
			$stmt = $db->query($select);
						
			return $stmt->fetchAll();
		}
						
		function buscaNcmlivresclientes($pesq){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
		
			$select->from(array('p'=>'tb_produtosncm','*'), array('p.*','p.id as idncm','t.id as id_tributosncm'))
				->joinLeft(array('t' => 'tb_tributosncm'), "t.id_produtosncm = p.id and t.sit = true and md5(t.id_tributosfiscais) = '".$pesq['desp']."'")
				->where("p.sit = true")
				->order('p.ncm asc');
		
			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}
		
		function gravarNcmclientes($params){
			$ob 	= new TributosModel();
			$obj 	= new NcmclientesModel();
			
			$array = array(
				'id_produtosncm'		=> $params['produtosncm'],
				'id_tributosfiscais'	=> $params['tributofiscal'],
				'sit'					=> true,
				'ii'					=> str_replace(",", ".", $params['ii']),
				'ipi'					=> str_replace(",", ".", $params['ipi']),
				'pis'					=> str_replace(",", ".", $params['pis']),
				'cofins'				=> str_replace(",", ".", $params['cofins']),
				'id_tributocsticms'		=> $params['csticms'],
				'id_tributocstipi'		=> $params['cstipi'],
				'id_tributocstpis'		=> $params['cstpis'],
				'id_tributocstcofins'	=> $params['cstcofins']
			);
								
			if(empty($params['tributoncm'])):
				$idncm = $obj->insert($array);
			else:
				$obj->update($array,"id = ".$params['tributoncm']);
			endif;
			
			if(!empty($params['todosaliquota'])):
				$arrayal = array(
					'ii'					=> str_replace(",", ".", $params['ii']),
					'ipi'					=> str_replace(",", ".", $params['ipi']),
					'pis'					=> str_replace(",", ".", $params['pis']),
					'cofins'				=> str_replace(",", ".", $params['cofins'])					
				);
				
				$obj->update($arrayal,"id_tributosfiscais = ".$params['tributofiscal']);
			endif;
			
			if(!empty($params['todoscst'])):
				$arraycst = array(
					'id_tributocsticms'		=> $params['csticms'],
					'id_tributocstipi'		=> $params['cstipi'],
					'id_tributocstpis'		=> $params['cstpis'],
					'id_tributocstcofins'	=> $params['cstcofins']
				);
			
				$obj->update($arraycst,"id_tributosfiscais = ".$params['tributofiscal']);
			endif;
			
			return $params['tributofiscal'];
					
		}
		
		//---CFOP-------------------------------------------------------------------
		function listaCfop(){
			$ob 	= new TributosModel();
			$obj 	= new CfopModel();
			return $obj->fetchAll("sit = true","cfop asc");			
		}
		
		function buscaCfop($id){
			$ob 	= new TributosModel();
			$obj 	= new CfopModel();
			if(isset($id['idcfop'])) return $obj->fetchAll("md5(id) = '".$id['idcfop']."'");			
		}
				
		function gravarCfop($params){
			$ob 	= new TributosModel();
			$obj 	= new CfopModel();

			$array = array(
				'cfop' 					=> $params['cfop'],
				'sit'					=> true,			
				'descricao'				=> $params['descricao'],
				'venda'					=> $params['venda'],
				'garantia'				=> $params['garantia'],
				'compra'				=> $params['compra'],
				'devolucao'				=> $params['devolucao']
			);			
			
			if(empty($params['idcfop'])):
				 $idncm = $obj->insert($array);				 			 
			else:
				 $obj->update($array,"id = ".$params['idcfop']);
			endif;
			
		}
		
		//---CST-------------------------------------------------------------------
		function listaCst(){
			$ob 	= new TributosModel();
			$obj 	= new CstModel();
			return $obj->fetchAll("sit = true","csticms asc");			
		}
		
		function buscaCst($id){
			$ob 	= new TributosModel();
			$obj 	= new CstModel();
			if(isset($id['idcst'])) return $obj->fetchAll("md5(id) = '".$id['idcst']."'");			
		}
		
		function removeCst($id){
			$ob 	= new TributosModel();
			$obj 	= new CstModel();
			$data = array('sit' => 0);
			$obj->update($data, "md5(id) = '".$id['idcst']."'");
		}
				
		function gravarCst($params){
			$ob 	= new TributosModel();
			$obj 	= new CstModel();
						
			$array['csticms'] 			= $params['cst'];
			$array['sit']			= true;			
			$array['descicms']			= $params['descricao'];
			
			if(empty($params['idcst'])):
				 $idncm = $obj->insert($array);				 			 
			else:
				 $obj->update($array,"id = ".$params['idcst']);
			endif;
			
		}
		
		//---CST IPI-------------------------------------------------------------------
		function listaCstipi(){
			$ob 	= new TributosModel();
			$obj 	= new CstipiModel();
			return $obj->fetchAll("sit = true","cstipi asc");
		}
		
		function buscaCstipi($id){
			$ob 	= new TributosModel();
			$obj 	= new CstipiModel();
			if(isset($id['idcst'])) return $obj->fetchAll("md5(id) = '".$id['idcst']."'");
		}
		
		function removeCstipi($id){
			$ob 	= new TributosModel();
			$obj 	= new CstipiModel();
			$data = array('sit' => 0);
			$obj->update($data, "md5(id) = '".$id['idcst']."'");
		}
		
		function gravarCstipi($params){
			$ob 	= new TributosModel();
			$obj 	= new CstipiModel();
		
			$array['cstipi'] 		= $params['cst'];
			$array['sit']			= true;
			$array['descipi']		= $params['descricao'];
			$array['tipo']			= $params['tipo'];
		
			if(empty($params['idcst'])):
			$idncm = $obj->insert($array);
			else:
			$obj->update($array,"id = ".$params['idcst']);
			endif;
		
		}
		
		//---CST PIS/PASEP-------------------------------------------------------------------
		function listaCstpis(){
			$ob 	= new TributosModel();
			$obj 	= new CstpisModel();
			return $obj->fetchAll("sit = true","cstpis asc");
		}
		
		function buscaCstpis($id){
			$ob 	= new TributosModel();
			$obj 	= new CstpisModel();
			if(isset($id['idcst'])) return $obj->fetchAll("md5(id) = '".$id['idcst']."'");
		}
		
		function removeCstpis($id){
			$ob 	= new TributosModel();
			$obj 	= new CstpisModel();
			$data = array('sit' => 0);
			$obj->update($data, "md5(id) = '".$id['idcst']."'");
		}
		
		function gravarCstpis($params){
			$ob 	= new TributosModel();
			$obj 	= new CstpisModel();
		
			$array['cstpis'] 		= $params['cst'];
			$array['sit']			= true;
			$array['descpis']		= $params['descricao'];
			$array['tipo']			= $params['tipo'];
			
			if(empty($params['idcst'])):
			$idncm = $obj->insert($array);
			else:
			$obj->update($array,"id = ".$params['idcst']);
			endif;
		
		}
		
		//---COFINS-------------------------------------------------------------------
		function listaCstcofins(){
			$ob 	= new TributosModel();
			$obj 	= new CstcofinsModel();
			return $obj->fetchAll("sit = true","cstcofins asc");
		}
		
		function buscaCstcofins($id){
			$ob 	= new TributosModel();
			$obj 	= new CstcofinsModel();
			if(isset($id['idcst'])) return $obj->fetchAll("md5(id) = '".$id['idcst']."'");
		}
		
		function removeCstcofins($id){
			$ob 	= new TributosModel();
			$obj 	= new CstcofinsModel();
			$data = array('sit' => 0);
			$obj->update($data, "md5(id) = '".$id['idcst']."'");
		}
		
		function gravarCstcofins($params){
			$ob 	= new TributosModel();
			$obj 	= new CstcofinsModel();
		
			$array['cstcofins'] 	= $params['cst'];
			$array['sit']			= true;
			$array['desccofins']	= $params['descricao'];
			$array['tipo']			= $params['tipo'];
			
			if(empty($params['idcst'])):
				$idncm = $obj->insert($array);
			else:
				$obj->update($array,"id = ".$params['idcst']);
			endif;
		
		}
		
		//---Despesas fiscais-------------------------------------------------------------------
		function listaDespesas(){
			$ob 	= new TributosModel();
			return $ob->fetchAll("sit = true");			
		}
		
		/*--
		 * Usado em despesasfiscaisufAction-------		 * 
		 * */
		function buscaDespesas($id){
			$ob 	= new TributosModel();
			if(isset($id['iduf'])) return $ob->fetchAll("md5(id_estados) = '".$id['iduf']."'");			
		}
		
		/*--
		 * Usado em parceirosAction-------		 * 
		 * */
		function buscaDespesaspar($id){
			$ob 	= new TributosModel();
			if(isset($id['iduf'])) return $ob->fetchAll("sit = 1 and md5(id_estados) = '".$id['iduf']."'");			
		}
		
		/**
		 * Usado em 
		 * pedidoseditAction-------------
		 * despesasfiscaiscadAction------		 
		 * despesasfiscaisncm ----------- 
		 * */
		
		function buscaDespesasid($id){
			$ob 	= new TributosModel();
			if(!empty($id)):
				return $ob->fetchAll("md5(id) = '".$id."'");
			endif;			
		}
		
		/**
		 * @param int $id - Id do estado
		 * @return Zend_Db_Table_Rowset_Abstract
		 */
		function buscaDespesaspadrao($id){
			$ob = new TributosModel();
			if(!empty($id)) return $ob->fetchAll("md5(id_estados) = '".$id."' and descricao = 'Padrao'");
		}
		
		/**
		 * buscaDespesasperfil
		 * @param unknown $idperfil
		 * @param array $tipo = 
		 * 1 - venda 
		 * 2 - garantia 
		 * 3 - retorno industrializacao 
		 * 4 - cobranca industrializacao 
		 * 5 - compras 
		 * 6 - devolucao 
		 * 7 - entrada industrializacao
		 * @return Ambigous <multitype:, multitype:mixed Ambigous <string, boolean, mixed> >
		 */
		
		function buscaDespesasperfil($idperfil, $tipo=1){
			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$select = $db->select();
			
			if($tipo == 1) $coluna = "t.id_cfopvenda";
			if($tipo == 2) $coluna = "t.id_cfopgarantia";
			if($tipo == 3) $coluna = "t.id_cfopretornoindustrializacao";
			if($tipo == 4) $coluna = "t.id_cfopcobrancaindustrializacao";
			if($tipo == 5) $coluna = "t.id_cfopcompra";
			if($tipo == 6) $coluna = "t.id_cfodevolucao";
			if($tipo == 7) $coluna = "t.id_cfopentradaindustrializacao";
			
			$select->from(array('t'=>'tb_tributosfiscais'), array('*','t.descricao as descperfil','c.descricao as desccfop'))
				->joinLeft(array('c'=>'tb_tributocfop'),'c.id = '.$coluna)
				->where("t.id = ".$idperfil);
			
			$stmt = $db->query($select);
			return $stmt->fetchAll();
			
			/*
			 	->joinLeft(array('tc'=>'tb_tributocsticms'),'c.id_tributocsticms = tc.id')
				->joinLeft(array('ti'=>'tb_tributocstipi'),'c.id_tributocstipi = ti.id')
				->joinLeft(array('tp'=>'tb_tributocstpis'),'c.id_tributocstpis = tp.id')
				->joinLeft(array('tf'=>'tb_tributocstcofins'),'c.id_tributocstcofins = tf.id')
			 */
		}

		function removeDespesas($id){
			$ob 	= new TributosModel();
			$array['sit'] = false;
			$ob->update($array, "md5(id) = '".$id."'");
		}		
		
		function gravarDespesas($params){
			$ob 	= new TributosModel();
									
			$array['id_estados'] 			= $params['iduf'];
			$array['sit']					= true;			
			$array['descricao']				= $params['descricao'];
			$array['compraicms']			= str_replace(",", ".", $params['compraicms']);
			$array['compraicmsdest']		= str_replace(",", ".", $params['compraicmsdest']);
			$array['comprast']				= str_replace(",", ".", $params['comprast']);
			$array['id_cfopcompra']			= $params['cfopcompra'];
			$array['vendaicms']				= str_replace(",", ".", $params['vendaicms']);
			$array['vendaicmsdest']			= str_replace(",", ".", $params['vendaicmsdest']);
			$array['vendast']				= str_replace(",", ".", $params['vendast']);
			$array['id_cfopvenda']			= $params['cfopvenda'];
			$array['garantiaicms']			= str_replace(",", ".", $params['garantiaicms']);
			$array['garantiaicmsdest']		= str_replace(",", ".", $params['garantiaicmsdest']);
			$array['garantiast']			= str_replace(",", ".", $params['garantiast']);
			$array['id_cfopgarantia']		= $params['cfopgarantia'];
			$array['devolucaoicms']			= str_replace(",", ".", $params['devolucaoicms']);
			$array['devolucaoicmsdest']		= str_replace(",", ".", $params['devolucaoicmsdest']);
			$array['devolucaost']			= str_replace(",", ".", $params['devolucaost']);
			$array['id_cfopdevolucao']		= $params['cfopdevolucao'];
			
			$array['semipi']				= $params['semipi'];
			$array['usecompraicms']			= $params['usecompraicms'];
			$array['usecompraicmsdest']		= $params['usecompraicmsdest'];
			$array['usevendaicms']			= $params['usevendaicms'];
			$array['usevendaicmsdest']		= $params['usevendaicmsdest'];
			$array['usegarantiaicms']		= $params['usegarantiaicms'];
			$array['usegarantiaicmsdest']	= $params['usegarantiaicmsdest'];
			$array['usedevolucaoicms']		= $params['usedevolucaoicms'];
			$array['usedevolucaoicmsdest']	= $params['usedevolucaoicmsdest'];
			$array['ctm']					= str_replace(",", ".", $params['ctm']);	
			$array['usectm']				= $params['usectm'];
			
			$array['id_cfopentradaindustrializacao'] = $params['cfopindent'];
			$array['indenticms']			         = str_replace(",", ".", $params['indenticms']);
			$array['indentst']			             = str_replace(",", ".", $params['indentst']);
			$array['indenticmsdest']			     = str_replace(",", ".", $params['indenticmsdest']);
			$array['useindenticms']	                 = $params['useindenticms'];
			$array['useindenticmsdest']	             = $params['useindenticmsdest'];
			
			$array['id_cfopretornoindustrializacao']  = $params['cfopindret'];
			$array['indreticms']			          = str_replace(",", ".", $params['indreticms']);
			$array['indretst']			              = str_replace(",", ".", $params['indretst']);
			$array['indreticmsdest']			      = str_replace(",", ".", $params['indreticmsdest']);
			$array['useindreticms']	                  = $params['useindreticms'];
			$array['useindreticmsdest']	              = $params['useindreticmsdest'];
			
			$array['id_cfopcobrancaindustrializacao'] = $params['cfopindcob'];
			$array['indcobicms']			          = str_replace(",", ".", $params['indcobicms']);
			$array['indcobst']			              = str_replace(",", ".", $params['indcobst']);
			$array['indcobicmsdest']			      = str_replace(",", ".", $params['indcobicmsdest']);
			$array['useindcobicms']	                  = $params['useindcobicms'];
			$array['useindcobicmsdest']	              = $params['useindcobicmsdest'];
					
			if(empty($params['edituf'])):
				 $id = $ob->insert($array);				 			 
			else:
				 $ob->update($array,"id = ".$params['edituf']);
				 $id = $params['edituf'];
			endif;
			
			return $id;
		}
		
		function buscaDespesasempresa($id){
			$ob 	= new TributosModel();
			return $ob->fetchAll("id = ".$id);			
		}
		
		
		//--- Gambeta para correcao ---------------------------------------------
		function corrigeNcmclientes(){
			$ob 	= new TributosModel();
			$obj 	= new NcmclientesModel();
			
			
			foreach ($ob->fetchAll('descricao like "Suframa%"') as $tributos): //-- tb_tributosfiscais ------
				$obj->delete("id_produtosncm != 2 and id_tributosfiscais = ".$tributos->id); //-- remove --------
				foreach ($obj->fetchAll("id_produtosncm = 2 and id_tributosfiscais = ".$tributos->id) as $tribprod); //--- tb_tributosncm ---------
				
				foreach (TributosBO::listaNcm() as $ncm):
					$array = array(
						'id_produtosncm'		=> $ncm->id,
						'id_tributosfiscais'	=> $tributos->id,
						'sit'					=> true,
						'ii'					=> $tribprod->ii,
						'ipi'					=> $tribprod->ipi,
						'pis'					=> $tribprod->pis,
						'cofins'				=> $tribprod->cofins,
						'id_tributocsticms'		=> $tribprod->id_tributocsticms,
						'id_tributocstipi'		=> $tribprod->id_tributocstipi,
						'id_tributocstpis'		=> $tribprod->id_tributocstpis,
						'id_tributocstcofins'	=> $tribprod->id_tributocstcofins
					);
					
					if($ncm->id != 2):
						$obj->insert($array);
					endif;
				endforeach;
				
			endforeach;
		
		}
		
		
		//---HSCODE-------------------------------------------------------------------
		/*--Utilizado em produtoscad
		 */
		function listaHscode(){
			$ob 	= new TributosModel();
			$obj 	= new HscodeModel();
			return $obj->fetchAll("sit = true","ncm asc");
		}
		
		function buscaHscode($pesq){
		
		    $ob 	= new TributosModel();
		    $obj 	= new HscodeModel();
		    return $obj->fetchAll("md5(id) = '".$pesq['ncm']."'");
		    
		}
		
		function gravarHscode($params){
			$ob 	= new TributosModel();
			$obj 	= new HscodeModel();
		
			$array = array(
				'ncm'					=> $params['ncm'],
			    'descricao'				=> $params['descricao'],
			    'descricaochines'		=> $params['descricaochines'],
				'sit'					=> true,
				'retorno'				=> str_replace(",", ".", str_replace(".", "", $params['retorno']))
			);
									
			if(empty($params['idncm'])):
				 $idncm = $obj->insert($array);
			else:
				$obj->update($array,"id = ".$params['idncm']);
			endif;
			
		}
		
		
	}
?>