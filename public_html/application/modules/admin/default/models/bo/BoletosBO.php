<?php
	class BoletosBO{		
		function geraBoletobb($params){
			
			$bo		 	= new FinanceiroModel();
			$bop		= new FinanceiroreceberparcModel(); 
			$data		= date("d/m/Y");
			$datapa		= date("Y-m-d");
			
			// DADOS DO BOLETO PARA O SEU CLIENTE
			
			foreach (FinanceiroBO::buscarParcelasrecboleto($params['parc']) as $listfin);
			foreach (ClientesBO::listaEnderecocomp($listfin->id_fornecedor, 1) as $listend);

			$array = array('emissaoboleto' => $datapa);
			$bop->update($array, 'id = '.$listfin->idparc);
			
			//$dias_de_prazo_para_pagamento = 5;
			//$taxa_boleto 	= 2.95;
			
			$data_venc 		= substr($listfin->vencimento,8,2)."/".substr($listfin->vencimento,5,2)."/".substr($listfin->vencimento,0,4); //date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006"; 
			$valor_cobrado 	= number_format($listfin->valor_apagar,2,",",""); // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
			///$valor_cobrado = str_replace(",", ".",$valor_cobrado);
			$valor_boleto	= number_format($listfin->valor_apagar,2,",","");//number_format($valor_cobrado+$taxa_boleto, 2, ',', '');
			
			$dadosboleto["nosso_numero"] = str_pad($listfin->idparc,10,'0',STR_PAD_LEFT);
			$dadosboleto["numero_documento"] = "NE".substr("000000".$listfin->id_nfe, -6,6)."/".$listfin->parc;	// Num do pedido ou do documento
			$dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
			$dadosboleto["data_documento"] = $data; // Data de emiss�o do Boleto
			$dadosboleto["data_processamento"] = $data; // Data de processamento do boleto (opcional)
			$dadosboleto["valor_boleto"] = $valor_boleto; 	// Valor do Boleto - REGRA: Com v�rgula e sempre com duas casas depois da virgula
			
			// DADOS DO SEU CLIENTE
			//08.268.582/0001-01
			if(strlen($listfin->CPF_CNPJ)==14):
				$cnpj = " (CNPJ: ".substr($listfin->CPF_CNPJ,0,2).".".substr($listfin->CPF_CNPJ,2,3).".".substr($listfin->CPF_CNPJ,5,3)."/".substr($listfin->CPF_CNPJ,8,4)."-".substr($listfin->CPF_CNPJ,12).")";
			endif;
			
			$dadosboleto["sacado"] = $listfin->RAZAO_SOCIAL.$cnpj;
			$dadosboleto["endereco1"] = $listend->LOGRADOURO." ".$listend->numero." ".$listend->BAIRRO." ".$listend->complemento;
			$dadosboleto["endereco2"] = $listend->ncidade." - ".$listend->nestado." - ".$listend->CEP;
			
			// -- Calcula multa
			$valor_multa = ($listfin->valor_apagar * 0.26666)/100;
			
			// INFORMACOES PARA O CLIENTE
			$dadosboleto["demonstrativo1"] = "- Após vencimento cobrar R$ ".number_format($valor_multa,2,",",".")." ao dia";
			$dadosboleto["demonstrativo2"] = "- Protestar no 5º dia útil após o vencimento";
			$dadosboleto["demonstrativo3"] = "";
			
			// INSTRUCOES PARA O CAIXA
			$dadosboleto["instrucoes1"] = "";
			$dadosboleto["instrucoes2"] = "";
			
			// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
			$dadosboleto["quantidade"] = "";
			$dadosboleto["valor_unitario"] = "";
			$dadosboleto["aceite"] = "N";		
			$dadosboleto["especie"] = "R$";
			$dadosboleto["especie_doc"] = "DM";
			
			
			// ---------------------- DADOS FIXOS DE CONFIGURA��O DO SEU BOLETO --------------- //
			
			
			// DADOS DA SUA CONTA - BANCO DO BRASIL
			$dadosboleto["agencia"] = "00401"; // Num da agencia, sem digito
			$dadosboleto["conta"] = "32428"; 	// Num da conta, sem digito
			
			// DADOS PERSONALIZADOS - BANCO DO BRASIL
			$dadosboleto["convenio"] = "1214004";  // Num do conv�nio - REGRA: 6 ou 7 ou 8 d�gitos
			$dadosboleto["contrato"] = "17631677"; // Num do seu contrato
			$dadosboleto["carteira"] = "17";
			$dadosboleto["variacao_carteira"] = "-019";  // Varia��o da Carteira, com tra�o (opcional)
			
			// TIPO DO BOLETO
			$dadosboleto["formatacao_convenio"] = "7"; // REGRA: 8 p/ Conv�nio c/ 8 d�gitos, 7 p/ Conv�nio c/ 7 d�gitos, ou 6 se Conv�nio c/ 6 d�gitos
			$dadosboleto["formatacao_nosso_numero"] = "2"; // REGRA: Usado apenas p/ Conv�nio c/ 6 d�gitos: informe 1 se for NossoN�mero de at� 5 d�gitos ou 2 para op��o de at� 17 d�gitos
			
			/*
			#################################################
			DESENVOLVIDO PARA CARTEIRA 18
			
			- Carteira 18 com Convenio de 8 digitos
			  Nosso n�mero: pode ser at� 9 d�gitos
			
			- Carteira 18 com Convenio de 7 digitos
			  Nosso n�mero: pode ser at� 10 d�gitos
			
			- Carteira 18 com Convenio de 6 digitos
			  Nosso n�mero:
			  de 1 a 99999 para op��o de at� 5 d�gitos
			  de 1 a 99999999999999999 para op��o de at� 17 d�gitos
			
			#################################################
			*/
			
			
			// SEUS DADOS
			$dadosboleto["identificacao"] = "ZTL Brasil Imp Exp Com Ltda";
			$dadosboleto["cpf_cnpj"] = "07555737/0001-10";
			$dadosboleto["endereco"] = "QI 08 Lotes 45/48 Taguatinga Norte";
			$dadosboleto["cidade_uf"] = "Taguatinga / DF";
			$dadosboleto["cedente"] = "ZTL Brasil Imp Exp Com Ltda";
			$dadosboleto["cep"] 	= "72135-080";
			
			include("boletoPhp/include/funcoes_bb.php");
			include("boletoPhp/include/layout_bb.php");
		}
		
		function geraBoletoitau($params){
				
			$bo		 	= new FinanceiroModel();
			$bop		= new FinanceiroreceberparcModel();
			$data		= date("d/m/Y");
			$datapa		= date("Y-m-d");
				
			// DADOS DO BOLETO PARA O SEU CLIENTE
				
			foreach (FinanceiroBO::buscarParcelasrecboleto($params['parc']) as $listfin);
			foreach (ClientesBO::listaEnderecocomp($listfin->id_fornecedor, 1) as $listend);
		
			$array = array('emissaoboleto' => $datapa);
			$bop->update($array, 'id = '.$listfin->idparc);
				
			//$dias_de_prazo_para_pagamento = 5;
			//$taxa_boleto 	= 2.95;
				
			$data_venc 		= substr($listfin->vencimento,8,2)."/".substr($listfin->vencimento,5,2)."/".substr($listfin->vencimento,0,4); //date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006";
			$valor_cobrado 	= number_format($listfin->valor_apagar,2,",","."); // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
			///$valor_cobrado = str_replace(",", ".",$valor_cobrado);
			$valor_boleto	= number_format($listfin->valor_apagar,2,",",".");//number_format($valor_cobrado+$taxa_boleto, 2, ',', '');
				
			$dadosboleto["nosso_numero"] = "9".str_pad($listfin->id_financeirorec,6,'0',STR_PAD_LEFT).$listfin->parc;
			$dadosboleto["numero_documento"] = "NE".substr("000000".$listfin->id_nfe, -6,6)."/".$listfin->parc;	// Num do pedido ou do documento
			$dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
			$dadosboleto["data_documento"] = $data; // Data de emiss�o do Boleto
			$dadosboleto["data_processamento"] = $data; // Data de processamento do boleto (opcional)
			$dadosboleto["valor_boleto"] = $valor_boleto; 	// Valor do Boleto - REGRA: Com v�rgula e sempre com duas casas depois da virgula
				
			// DADOS DO SEU CLIENTE
			//08.268.582/0001-01
			if(strlen($listfin->CPF_CNPJ)==14):
				$cnpj = substr($listfin->CPF_CNPJ,0,2).".".substr($listfin->CPF_CNPJ,2,3).".".substr($listfin->CPF_CNPJ,5,3)."/".substr($listfin->CPF_CNPJ,8,4)."-".substr($listfin->CPF_CNPJ,12);
			endif;
				
			$dadosboleto["sacado"] = $listfin->RAZAO_SOCIAL;
			$dadosboleto["sacado_cnpj"] = $cnpj;
			$dadosboleto["endereco1"] = $listend->LOGRADOURO." ".$listend->numero." ".$listend->BAIRRO." ".$listend->complemento;
			$dadosboleto["endereco2"] = $listend->ncidade." - ".$listend->nestado." - ".$listend->CEP;
				
			// -- Calcula multa
			$valor_multa = ($listfin->valor_apagar * 0.26666)/100;
				
			// INFORMACOES PARA O CLIENTE
			$dadosboleto["demonstrativo1"] = "- Após ".$data_venc." cobrar R$".number_format($valor_multa,2,",",".")." por dia de atraso;";
			$dadosboleto["demonstrativo2"] = "- Após o 5º dia útil vencido o sacado estará sujeito ao registro em orgãos de proteção ao crédito;";
			$dadosboleto["demonstrativo3"] = "";
				
			// INSTRUCOES PARA O CAIXA
			$dadosboleto["instrucoes1"] = "- Após ".$data_venc." cobrar R$".number_format($valor_multa,2,",",".")." por dia de atraso;";
			$dadosboleto["instrucoes2"] = "- Após o 5º dia útil vencido o sacado estará sujeito ao registro em orgãos de proteção ao crédito;";
				
			// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
			$dadosboleto["quantidade"] = "";
			$dadosboleto["valor_unitario"] = "";
			$dadosboleto["aceite"] = "N";
			$dadosboleto["especie"] = "R$";
			$dadosboleto["especie_doc"] = "DM";
				
			// ---------------------- DADOS FIXOS DE CONFIGURA��O DO SEU BOLETO --------------- //
				
			// DADOS DA SUA CONTA - BANCO DO BRASIL
			$dadosboleto["agencia"] = "0132"; // Num da agencia, sem digito
			$dadosboleto["conta"] = "20165"; 	// Num da conta, sem digito
			$dadosboleto["conta_dv"] = "8"; 	// Digito do Num da conta

			/*C/C: 0132/20165-8 e 0132/34943-2*/
			
			// DADOS PERSONALIZADOS - ITA�
			$dadosboleto["carteira"] = "109";  // C�digo da Carteira: pode ser 175, 174, 104, 109, 178, ou 157
				
			// SEUS DADOS
			$dadosboleto["identificacao"] = "ZTL Brasil Imp Exp Com Ltda";
			$dadosboleto["cpf_cnpj"] = "07555737/0001-10";
			$dadosboleto["endereco"] = "QI 08 Lotes 45/48 Taguatinga Norte";
			$dadosboleto["cidade_uf"] = "Taguatinga / DF";
			$dadosboleto["cedente"] = "ZTL Brasil Imp Exp Com Ltda";
			$dadosboleto["cep"] 	= "72135-080";
				
			include("boletoPhp/include/funcoes_itau.php");
			include("boletoPhp/include/layout_itau.php");
		}
		
		function gerarArquivoremessabb(){
			
			if(file_exists(Zend_Registry::get('pastaPadrao')."/public/sistema/financeiro/remessas/remessabb.REM")){
				unlink(Zend_Registry::get('pastaPadrao')."/public/sistema/financeiro/remessas/remessabb.REM");
			}
			
			$abrir = fopen(Zend_Registry::get('pastaPadrao')."/public/sistema/financeiro/remessas/remessabb.REM", "a+");
			
			//-- Header e Header Lote --------------------------------
			$data_geracao 			= date("dmY");
			$hora_geracao 			= date("His");
			$banco 					= "001";
			$agencia 				= "00401";
			$dv_agencia 			= "4";//Calculo do Módulo11
			$conta					= "000000032428";
			$dv_conta				= "0";
			$cod_convenio 			= "1214004";
			$n_inscricao_empresa 	= "07555737000110";
			$empresa 				= "ZTL DO BRASIL - IMPORTACAO - E";
			$nomebanco				= str_pad(substr('BANCO DO BRASIL S.A.',0,30),30,' ',STR_PAD_RIGHT);
			$codigo_convenio		= str_pad($cod_convenio,9,'0',STR_PAD_LEFT)."001417019".str_pad(' ',2);
			$numeroremessa			= "00000001";
			$numeroseq				= "000001";
			
			$header_arquivo =
				str_pad($banco,3,'0',STR_PAD_LEFT).
				str_pad('',4,'0',STR_PAD_LEFT).
				"0".
				str_pad(' ',9)."2".
				str_pad($n_inscricao_empresa,14,"0",STR_PAD_LEFT).
				str_pad(' ',20).
				$agencia.
				$dv_agencia.
				$conta.
				$dv_conta.
				str_pad(' ',1).
				str_pad($empresa, 30).
				$nomebanco.
				str_pad(' ',10)."1".
				$data_geracao.$hora_geracao.
				$numeroseq."083".
				str_pad('0',5,'0',STR_PAD_LEFT).
				str_pad(' ',20).
				str_pad(' ',20).
				str_pad('',29);
			
			$header_lote =
				str_pad($banco,3,'0',STR_PAD_LEFT).
				"0001"."1"."R"."01".str_pad(' ',2)."042"." "."2".
				str_pad($n_inscricao_empresa,15,"0",STR_PAD_LEFT).
				$codigo_convenio.
				$agencia.
				$dv_agencia.
				$conta.
				$dv_conta.
				str_pad(' ',1).
				str_pad($empresa, 30).
				str_pad('',40).
				str_pad('',40).
				$numeroremessa.
				$data_geracao.
				$data_geracao.
				str_pad(' ',33);
			
			fputs($abrir, $header_arquivo."\r\n" );//Arquivo de Header
			fputs($abrir, $header_lote."\r\n" );//Arquivo de Lote
			
			
			//-- Sequimentos  --------------------------------
			$cod_mov_remessa	= "01";
			/*-- 01 – Entrada de títulos, 02 – Pedido de baixa, 04 – Concessão de Abatimento, 05 – Cancelamento de Abatimento, 06 – Alteração de Vencimento, 07
			 – Concessão de Desconto, 08 – Cancelamento de Desconto, 09 – Protestar, 10 – Cancela/Sustação da
			Instrução de protesto, 30 – Recusa da Alegação do Sacado, 31 – Alteração de Outros Dados, 40 – Alteração
			de Modalidade ------ */
			
			$contseq = 0;
			foreach (FinanceiroBO::listarParcelasareceberremessa() as $finparc):
			
				?>
				<script>
				//window.open('gerarboletobb/parcela/<?php echo $finparc->idparc?>','_blank');
				</script>
				<?php 
			
				echo $finparc->idparc."<br />";
				
				$contseq++;
				
				$nosso_numero 	= $cod_convenio.str_pad($finparc->idparc,10,'0',STR_PAD_LEFT);
				$vencimento 	= $finparc->dtvenc;
				
				$documento 	= substr(preg_replace('@[./-]@','',$finparc->CPF_CNPJ),0,14);
				$cliente 	= str_pad(substr(DiversosBO::pogremoveAcentos($finparc->RAZAO_SOCIAL),0,40),40," ",STR_PAD_RIGHT);
				
				if(mb_strlen($documento, 'utf8') == 14):
					$tipo_de_inscricao = 2;
				else:
					$tipo_de_inscricao = 1;
				endif;
				
				$endereco 		= str_pad(substr(strtoupper(DiversosBO::pogremoveAcentos($finparc->LOGRADOURO).','.$finparc->numero),0,40),40," ",STR_PAD_RIGHT);
				$bairro 		= str_pad(substr(strtoupper(DiversosBO::pogremoveAcentos($finparc->BAIRRO)),0,15),15," ",STR_PAD_RIGHT);
				$cep 			= substr(preg_replace('@[-]@','',$finparc->CEP),0,8);
				$cidade 		= str_pad(substr(strtoupper(DiversosBO::pogremoveAcentos($finparc->ncidade)),0,15),15," ",STR_PAD_RIGHT);
				$estado 		= strtoupper($finparc->nuf);
				$n_documento	= str_pad("NE".substr("000000".$finparc->id_nfe,-6,6)."/".$finparc->parc,15," ",STR_PAD_RIGHT);
				
				$valorexp = explode('.',$finparc->valor_apagar);
				$valor = str_pad($valorexp[0],13,"0",STR_PAD_LEFT);
				$valor .= str_pad($valorexp[1],2,"0");
				
				$data_emissao 	= $finparc->dtemissao;
				$percentual		= 8;
				$perdiario		= 8/30;
				$valor_multa 	= ($finparc->valor_apagar * $perdiario)/100;
				$juros		 	= str_pad(str_replace(".", "", number_format($valor_multa,2)),15,"0",STR_PAD_LEFT);
				$id_titulo		= str_pad($finparc->idparc,25,'0',STR_PAD_LEFT);
				$cod_protesto 	= "2";
				$n_dias_protesto= "05";
				$contrato		= "17631677";
				$cod_moeda		= "09";
				
				$linha_p =
					str_pad($banco,3,'0',STR_PAD_LEFT).
					"0001"."3".str_pad($contseq,5,'0',STR_PAD_LEFT)."P"." ".
					$cod_mov_remessa.
					$agencia.$dv_agencia.$conta.$dv_conta.str_pad(' ',1).
					$nosso_numero.str_pad(' ',3).
					"7"."1"."2"."2"."2".
					$n_documento.
					$vencimento.
					$valor.
					"00000"." "."02"."N".
					$data_emissao.
					"1"."00000000".
					$juros.
					"0"."00000000"."0000000000000"."00"."0000000000000"."00"."0000000000000"."00".
					$id_titulo.
					$cod_protesto.
					$n_dias_protesto.
					"0"."000".
					$cod_moeda.
					str_pad($contrato,10,'0',STR_PAD_LEFT)." ";
				
				$contseq++;
				$linha_q =
					str_pad($banco,3,'0',STR_PAD_LEFT)
					."0001"."3".str_pad($contseq,5,'0',STR_PAD_LEFT)."Q"." ".
					$cod_mov_remessa.
					$tipo_de_inscricao.
					str_pad($documento,15,"0",STR_PAD_LEFT).
					strtoupper($cliente).
					$endereco.$bairro.$cep.$cidade.$estado.
					"0".str_pad("",15,'0',STR_PAD_LEFT).
					str_pad("",40," ")."000".
					str_pad("",20,"0").
					str_pad("",8," ");
				
				$contseq++;
				$linha_r =
					str_pad($banco,3,'0',STR_PAD_LEFT)
					."0001"."3".str_pad($contseq,5,'0',STR_PAD_LEFT)."R"." ".
					$cod_mov_remessa.
					"0".str_pad('',8,'0',STR_PAD_LEFT).str_pad('',13,'0',STR_PAD_LEFT).str_pad('',2,'0',STR_PAD_LEFT).
					"0".str_pad('',8,'0',STR_PAD_LEFT).str_pad('',13,'0',STR_PAD_LEFT).str_pad('',2,'0',STR_PAD_LEFT).
					"0".str_pad('',8,'0',STR_PAD_LEFT).str_pad('',13,'0',STR_PAD_LEFT).str_pad('',2,'0',STR_PAD_LEFT).
					str_pad('',10,'0',STR_PAD_LEFT).
					str_pad('',40,'0',STR_PAD_LEFT).
					str_pad('',40,'0',STR_PAD_LEFT).
					str_pad('',20,' ',STR_PAD_LEFT).
					str_pad('',8,'0',STR_PAD_LEFT).
					str_pad('',3,'0',STR_PAD_LEFT).
					str_pad('',5,'0',STR_PAD_LEFT).
					str_pad('',1,'0',STR_PAD_LEFT).
					str_pad('',12,'0',STR_PAD_LEFT).
					str_pad('',1,'0',STR_PAD_LEFT).
					str_pad('',1,'0',STR_PAD_LEFT).
					str_pad('',1,'0',STR_PAD_LEFT).
					str_pad('',9,' ',STR_PAD_LEFT);
				
				
				fputs($abrir, $linha_p."\r\n" );//Arquivo de Lote
				fputs($abrir, $linha_q."\r\n" );//Arquivo de Lote
				fputs($abrir, $linha_r."\r\n" );//Arquivo de Lote
			
			endforeach;
			
			$trailer_lote =
				str_pad($banco,3,'0',STR_PAD_LEFT)."0001"."5".
				str_pad('',9).
				str_pad(($contseq+2),6,"0",STR_PAD_LEFT).
				str_pad("",217,"0",STR_PAD_LEFT);
			
			$trailer_arquivo =
				str_pad($banco,3,'0',STR_PAD_LEFT).
				"9999"."9".
				str_pad(' ',9)."000001".
				str_pad(($contseq+4),6,"0",STR_PAD_LEFT).
				str_pad(' ',6).
				str_pad(' ',205);
			
			fputs($abrir, $trailer_lote."\r\n" );//Arquivo de Trailer
			fputs($abrir, $trailer_arquivo);//Arquivo de Trailer
			
			fclose($abrir);
			
		}
		
		function gerarArquivoremessaitau(){
				
			if(file_exists(Zend_Registry::get('pastaPadrao')."/public/sistema/financeiro/remessas/remessaitau.REM")){
				unlink(Zend_Registry::get('pastaPadrao')."/public/sistema/financeiro/remessas/remessaitau.REM");
			}
				
			$abrir = fopen(Zend_Registry::get('pastaPadrao')."/public/sistema/financeiro/remessas/remessaitau.REM", "a+");
				
			//-- Header e Header Lote --------------------------------
			$data_geracao 			= date("dmy");
			$hora_geracao 			= date("His");
			$banco 					= "341";
			$agencia 				= "0132";
			$conta					= "20165";
			$dv_conta				= "8";
			
			$n_inscricao_empresa 	= "07555737000110";
			$empresa 				= "ZTL DO BRASIL - IMPORTACAO - E";
			$nomebanco				= str_pad('BANCO ITAU SA',15,' ',STR_PAD_RIGHT);
			$numeroremessa			= "00000001";
			$numeroseq				= str_pad('1',6,'0',STR_PAD_LEFT);
					
			
			/*
			C/C: 0132/20165-8 e 0132/34943-2
			Carteira: 109
			Nosso Número: Faixa livre iniciando em ‘90000000’ e terminando em ‘99999999’.
			*/			
			
			$header_arquivo = "01REMESSA01".str_pad('COBRANCA',15,' ',STR_PAD_RIGHT).$agencia."00".$conta.$dv_conta.str_pad(' ',8).$empresa.$banco.$nomebanco.$data_geracao.str_pad(' ',294).$numeroseq;
			fputs($abrir, $header_arquivo."\r\n" );//Arquivo de Header
			
			$contseq = 1;
			$totalvalor = 0;
			foreach (FinanceiroBO::listarParcelasareceberremessa() as $finparc){

				?>
				<script>
				window.open('gerarboleto/parcela/<?php echo $finparc->idparc?>','_blank');
				</script>
				<?php 
			
				echo $finparc->idparc."<br />";

				$contseq++;
				
				$nosso_numero 	= "9".str_pad($finparc->id_financeirorec,6,'0',STR_PAD_LEFT).$finparc->parc;
				$vencimento 	= $finparc->dtvencitau;
				
				$documento 	= substr(preg_replace('@[./-]@','',$finparc->CPF_CNPJ),0,14);
				$cliente 	= str_pad(substr(DiversosBO::pogremoveAcentos($finparc->RAZAO_SOCIAL),0,30),30," ",STR_PAD_RIGHT);
				
				if(mb_strlen($documento, 'utf8') == 14):
					$tipo_de_inscricao = "02";
				else:
					$tipo_de_inscricao = "01";
				endif;
				
				$endereco 		= str_pad(substr(strtoupper(DiversosBO::pogremoveAcentos($finparc->LOGRADOURO).','.$finparc->numero),0,40),40," ",STR_PAD_RIGHT);
				$bairro 		= str_pad(substr(strtoupper(DiversosBO::pogremoveAcentos($finparc->BAIRRO)),0,12),12," ",STR_PAD_RIGHT);
				$cep 			= substr(preg_replace('@[-]@','',$finparc->CEP),0,8);
				$cidade 		= str_pad(substr(strtoupper(DiversosBO::pogremoveAcentos($finparc->ncidade)),0,15),15," ",STR_PAD_RIGHT);
				$estado 		= strtoupper($finparc->nuf);
				$n_documento	= str_pad("NE".$finparc->id_nfe."/".$finparc->parc,10,"0",STR_PAD_LEFT);
				
				$valorexp = explode('.',$finparc->valor_apagar);
				$valor = str_pad($valorexp[0],11,"0",STR_PAD_LEFT);
				$valor .= str_pad($valorexp[1],2,"0");
				$data_emissao 	= "101013";//$finparc->dtemissaoitau;
				
				
				$perdiario		= 8/30;
				$valor_multa 	= ($finparc->valor_apagar * $perdiario)/100;
				$juros		 	= str_pad(str_replace(".", "", number_format($valor_multa,2)),13,"0",STR_PAD_LEFT);
				
				$registroDetalhe = "102".$n_inscricao_empresa.$agencia."00".$conta.$dv_conta.str_pad(' ',4).str_pad(' ',4).
				str_pad(' ',25).$nosso_numero.str_pad('0',13,'0')."109".str_pad(' ',21)."I"."01".$n_documento.$vencimento.$valor."341".str_pad(' ',5).
				"01A".$data_emissao."0510".$juros.str_pad('0',45,'0').$tipo_de_inscricao.$documento.$cliente.str_pad(' ',10).$endereco.
				$bairro.$cep.$cidade.$estado.str_pad(' ',34).str_pad('0',6,'0').str_pad('0',2,'0')." ".str_pad($contseq,6,'0',STR_PAD_LEFT);
				
				fputs($abrir, $registroDetalhe."\r\n" );//Arquivo de Lote
				
				$totalvalor += $finparc->valor_apagar;
			
			}
			
			/* $registroTrailer = "9201341".str_pad(' ',10).str_pad('0',30,'0').str_pad(' ',10).str_pad('0',30,'0').str_pad(' ',90).
			str_pad(($contseq-1),8,'0',STR_PAD_LEFT).str_pad(str_replace(".","", $totalvalor),14,"0",STR_PAD_LEFT).
			str_pad('0',8,'0').str_pad('0',5,'0').
			str_pad(($contseq-1),8,'0',STR_PAD_LEFT).str_pad(str_replace(".","",$totalvalor),14,"0",STR_PAD_LEFT).
			str_pad(' ',160).str_pad(($contseq+1),6,'0',STR_PAD_LEFT); */
			
			$registroTrailer = "9".str_pad(' ',393).str_pad(($contseq+1),6,'0',STR_PAD_LEFT);
						
			fputs($abrir, $registroTrailer);
			fclose($abrir);
		}
		
	}
?>