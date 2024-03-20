<?php
	class KangretornoBO{
		
	    /**
	     * listaProdutosinvocie()
	     * @param int $val
	     * @return array
	     */
		function listaProdutosinvocie($idinvoice){

			$db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$select = $db->select();
			
			$select->from(array('t'=>'tb_kang_cominvoiceprod','*'),  array('sum(t.qt*pc.preco) as precocompra', 'c.EMPRESA','cc.nome','pc.hs_code as hscode', 'pc.retorno as retornohs','t.deschscode'))
    			->join(array('pc'=>'tb_kang_comprasprod'),'pc.id_kang_compra = t.id_kang_compra and t.id_prod = pc.id_prod')
    			->join(array('p'=>'tb_kang_compra'),'p.id_kang_compra = pc.id_kang_compra')
    			
    			->join(array('c'=>'clientes'),'p.id_for = c.ID')
    			->joinLeft(array('cc'=>'tb_clientechina'),'c.ID = cc.id_cliente')    			
    			
    			->where("md5(t.id_cominvoice) = '".$idinvoice."'")
			    ->group('pc.hs_code')
			    ->group('pc.id_kang_compra')
			    ->order('pc.hs_code');

			//echo $select->__toString()."\n"; die();


			$stmt = $db->query($select);
			return $stmt->fetchAll();
		}

        function getDataRebate($params)
        {
            $where = "1 = 1";

            if($params['idInvoice']) {
                $where .= ' and k.id = "'.$params['idInvoice'].'"';
            }

            if( !empty($params['dtiniconc'])) {
                $di	= substr($params['dtiniconc'],6,4).'-'.substr($params['dtiniconc'],3,2).'-'.substr($params['dtiniconc'],0,2);
                $where .= ' and k.data >= "'.$di.'"';
            }

            if (!empty($params['dtfimconc'])) {
                $df	= substr($params['dtfimconc'],6,4).'-'.substr($params['dtfimconc'],3,2).'-'.substr($params['dtfimconc'],0,2);
                $where .= ' and k.data <= "'.$df.'"';
            }

            $db = Zend_Db::factory('PDO_MYSQL', Zend_Registry::get('conexaoDb'));
            $db->setFetchMode(Zend_Db::FETCH_OBJ);
            $stmt = $db->query('select k.id,
                               DATE_FORMAT(k.data,"%d/%m/%y") as dtInvoice,
                               DATE_FORMAT(k.dt_embarque,"%d/%m/%y") as dtEmbarque ,
                               DATE_FORMAT(r.dt_pagamento,"%d/%m/%y") as dtRebate,
                               (SELECT sum(t.qt * pc.preco)
                                FROM tb_kang_cominvoiceprod AS t
                                         INNER JOIN tb_kang_comprasprod AS pc ON pc.id_kang_compra = t.id_kang_compra and t.id_prod = pc.id_prod
                                WHERE t.id_cominvoice = k.id) AS pagtoTotal,
                               (select sum(fi.valor) from tb_fininvoice fi where fi.id_kang_cominvoice = k.id) as rebateEfetivo,
                               (SELECT sum((t.qt * pc.preco / 1.13) * (pc.retorno/100))
                                    FROM tb_kang_cominvoiceprod AS t
                                         INNER JOIN tb_kang_comprasprod AS pc ON pc.id_kang_compra = t.id_kang_compra and t.id_prod = pc.id_prod
                                    WHERE t.id_cominvoice = k.id) as rebate,
                               r.valor_pago as rebateEfetivo,
                                /* (select sum(fi.valor) from tb_fininvoice fi where fi.id_kang_cominvoice = k.id),  */
                               p.valor_pago as despesas,
                               k.sit as sitInvoice
                        from tb_kang_cominvoice k
                             left join tb_fin_contasareceber r on r.id = k.id_fin_contasareceber
                             left join tb_fin_contasapagar p on p.id = k.id_fin_contasapagar
                        where ' . $where . ' order by k.id desc');

            return $stmt->fetchAll();
        }
				
	}

