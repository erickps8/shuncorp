<?php
	class KangfreteBO extends BaseBO {

		function buscar($params){
			$select = $this->db->select();
			
			$select->from(['k' => 'tb_kang_cominvoice'],  [
                'k.id as idCominvoice',
                'k.st_fob',
                'k.sit as sitInvoice',
                'DATE_FORMAT(k.data,"%d/%m/%y") as dtInvoice',
                'DATE_FORMAT(k.dt_embarque,"%d/%m/%y") as dtEmbarque',
                'c.EMPRESA',
                'p.id as idPag',
                'DATE_FORMAT(p.vencimento,"%d/%m/%y") as dtPag',
                'p.valor_apagar as despesas',
                'r.id as idRec',
                'DATE_FORMAT(r.vencimento,"%d/%m/%y") as dtRec',
                'r.valor_apagar as receitas',
            ])
    			->join(array('c'=>'clientes'),'c.ID = k.id_cliente')
    			->joinLeft(array('r'=>'tb_fin_contasareceber'), 'r.id = k.id_freterec')
    			->joinLeft(array('p'=>'tb_fin_contasapagar'), 'p.id = k.id_fretepag')
    			->joinLeft(array('kp'=>'tb_kang_cominvoiceprod'), 'kp.id_cominvoice = k.id')
    			->joinLeft(array('kc'=>'tb_kang_compra'), 'kc.id_kang_compra = kp.id_kang_compra')
    			->where(self::getWhere($params))
			    ->order('k.id desc')
                ->group('k.id');

			$stmt = $this->db->query($select);
			return $stmt->fetchAll();
		}

        private static function getWhere($params)
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

            if (isset($params['buscacli']) && $params['buscacli'] != 0) {
                $where .= ' and k.id_cliente = "'. $params['buscacli'] .'"';
            }

            if (isset($params['buscafor']) && $params['buscafor'] != 0) {
                $where .= ' and kc.id_for = "'. $params['buscafor'] .'"';
            }

            if (isset($params['buscasit']) && $params['buscasit'] != "") {
                $sit = ($params['buscasit'] != 5) ? $params['buscasit'] : 0;
                $where .= ' and k.sit = "'. $sit .'"';
            }

            return $where;
        }

				
	}

