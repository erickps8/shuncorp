$(function(){   

	$('.parcela').mask('0/0');
	
	$("select[name=moedapag]").change(function(){
		
		var moeda = $('select[name=moedapag] option:selected').val();
		
		$('select[name=bancopagamentopag] option').each(function(){
			classe = $(this).attr('class');
			
			if(classe != moeda){
				$(this).attr('disabled', true);				
			}else{
				$(this).attr('disabled', false);
			}
		});		
	});
	
	
	/**
	 * busca as vendas nao marcadas 
	 */
	
	$("#btnAddvenda").click(function(){
		
		var id = $(this).attr('data-rel');
		var tp = $(this).attr('data-tp');
				
		$.ajax({
		  url: "/admin/venda/buscavendasfinanceiro/financeiro/"+id+"/tp/"+tp,
		  success: function(data) {
			  jModal(data, "Selecione a invoice", 350, 300);
		  }
		});
	});

	/**
	 * busca as vendas nao marcadas
	 */

	$("#btnAddFrete").click(function(){

		var id = $(this).attr('data-rel');
		var tp = $(this).attr('data-tp');

		$.ajax({
			url: "/admin/venda/busca-vendas-frete/financeiro/"+id+"/tp/"+tp,
			success: function(data) {
				jModal(data, "Selecione a invoice", 350, 300);
			}
		});
	});
	
	/**
	 * Inclui as invoices no financeiro
	 */
	$("#btnSalvarinvoice").live('click', function(){
		
		var ret;
		
		$(".retorno").each(function(){
			if($(this).attr('checked') == true){
				
				ret = ret+'<tr>'+
							'<td align="left" style="padding: 5px">'+
								'<a href="/admin/kang/vendasdet/ped/'+$(this).attr('id')+'" target="_blank" >S'+("000000"+$(this).val()).substr(-6, 6)+'</a>'+
								'<input type="hidden" name="retornoimposto[]" value="'+$(this).val()+'">'+
							'</td>'+
							'<td align="right" style="padding: 5px;">'+
								'<a href="javascript:" onclick="$(this).closest(\'tr\').remove()"><img src="/public/sistema/imagens/icons/middlenav/close.png" width="12" border="0"></a>'+
							'</td>'+	
						'</tr>'			
				
			}
		});
		
		$("#tableRetorno").html(ret);
		$.alerts._hide();
		
	});

	/**
	 * Inclui as invoices de frete
	 */
	$("#btnSalvarFrete").live('click', function(){

		var ret;
		var fob = $("#checkFob:checked").val();

		var strFob = (fob == undefined) ? '': ' (FOB FREIGHT)';

		$(".retorno").each(function(){
			if($(this).attr('checked') == true){

				ret = ret+'<tr>'+
					'<td align="left" style="padding: 5px">'+
					'<a href="/admin/kang/vendasdet/ped/'+$(this).attr('id')+'" target="_blank" >S'+("000000"+$(this).val()).substr(-6, 6)+'</a>'+
					strFob +
					'<input type="hidden" name="frete[]" value="'+$(this).val()+'">'+
					'<input type="hidden" name="stFob" value="'+fob+'">'+
					'</td>'+
					'<td align="right" style="padding: 5px;">'+
					'<a href="javascript:" onclick="$(this).closest(\'tr\').remove()"><img src="/public/sistema/imagens/icons/middlenav/close.png" width="12" border="0"></a>'+
					'</td>'+
					'</tr>'

			}
		});

		$("#tableFrete").html(ret);
		$.alerts._hide();

	});
	
});

function buscaContasconcilia(tipo,id,conta,tpant,bancobusca){
	$.ajax({
	  url: "/admin/administracao/buscacontasfin/tipo/"+tipo+"/idc/"+id+"/conta/"+conta+"/tpant/"+tpant+"/bancobusca/"+bancobusca,
	  success: function(data) {
		  jModal(data, "Selecione as contas", 500, 300);
	  }
	});
	
}

function buscaPedidoscompra(id, tipo, fornecedor, npurch){
		
	$.ajax({
	  url: "/admin/administracao/buscacontasfinped/conta/"+id+"/tipo/"+tipo+"/forn/"+fornecedor+"/npurc/"+npurch,
	  success: function(data) {
		  jModal(data, "Purchase", 500, 300);
	  }
	});
	
}

function buscaInvoice(id, fornecedor){
	
	$.ajax({
	  url: "/admin/administracao/buscainvoice/conta/"+id+"/buscacli/"+fornecedor+"/financeiro/1",
	  success: function(data) {
		  jModal(data, "Invoice", 500, 300);
	  }
	});		
}


function tipoPesquisa(valor){

	if(valor==1){
		$("#buscid").show();
		$("#buscfor, #buscvalor, #buscpurchase, #buscplcontas").hide();
		$("#buscaplcontas, #buscafor").val("0");
		$("#buscavalor, #buscapurc, #buscaid").val("");
		
	}else if(valor==2){		
		$("#buscfor").show();
		$("#buscid, #buscvalor, #buscpurchase, #buscplcontas").hide();
		$("#buscaplcontas, #buscafor").val("0");
		$("#buscavalor, #buscapurc, #buscaid").val("");
		
	}else if(valor==3){
		$("#buscvalor").show();
		$("#buscid, #buscfor, #buscpurchase, #buscplcontas").hide();
		$("#buscaplcontas, #buscafor").val("0");
		$("#buscavalor, #buscapurc, #buscaid").val("");
		
	}else if(valor==4){
		$("#buscpurchase").show();
		$("#buscid, #buscfor, #buscvalor, #buscplcontas").hide();
		$("#buscaplcontas, #buscafor").val("0");
		$("#buscavalor, #buscapurc, #buscaid").val("");
		
	}else if(valor==5){
		$("#buscplcontas").show();
		$("#buscid, #buscfor, #buscvalor, #buscpurchase").hide();
		$("#buscaplcontas, #buscafor").val("0");
		$("#buscavalor, #buscapurc, #buscaid").val("");		
	}
}

function tipoPesquisarec(valor){

	if(valor==1){
		document.getElementById("buscidrec").style.display = "block";
		document.getElementById("buscforrec").style.display = "none";
		document.getElementById("buscvalorrec").style.display = "none";
		document.getElementById("buscplcontas").style.display = "none";
		document.getElementById("buscinvoice").style.display = "none";
		document.getElementById("buscainvoice").value = "";
		document.getElementById("buscaforrec").value = "0";
		document.getElementById("buscavalorrec").value = "";
		document.getElementById("buscapurc").value = "";
	}else if(valor==2){
		document.getElementById("buscidrec").style.display = "none";
		document.getElementById("buscforrec").style.display = "block";
		document.getElementById("buscvalorrec").style.display = "none";
		document.getElementById("buscplcontas").style.display = "none";
		document.getElementById("buscinvoice").style.display = "none";
		document.getElementById("buscainvoice").value = "";
		document.getElementById("buscaidrec").value = "";
		document.getElementById("buscavalorrec").value = "";
		document.getElementById("buscapurc").value = "";
	}else if(valor==3){
		document.getElementById("buscidrec").style.display = "none";
		document.getElementById("buscforrec").style.display = "none";
		document.getElementById("buscvalorrec").style.display = "block";
		document.getElementById("buscplcontas").style.display = "none";
		document.getElementById("buscinvoice").style.display = "none";
		document.getElementById("buscainvoice").value = "";
		document.getElementById("buscaforrec").value = "0";
		document.getElementById("buscaidrec").value = "";
		document.getElementById("buscapurc").value = "";
	}else if(valor==4){
		document.getElementById("buscidrec").style.display = "none";
		document.getElementById("buscforrec").style.display = "none";
		document.getElementById("buscvalorrec").style.display = "none";
		document.getElementById("buscplcontas").style.display = "block";
		document.getElementById("buscinvoice").style.display = "none";
		document.getElementById("buscainvoice").value = "";
		document.getElementById("buscaforrec").value = "0";
		document.getElementById("buscaidrec").value = "";
		document.getElementById("buscavalorrec").value = "";
	}else if(valor==5){
		document.getElementById("buscidrec").style.display = "none";
		document.getElementById("buscforrec").style.display = "none";
		document.getElementById("buscvalorrec").style.display = "none";
		document.getElementById("buscplcontas").style.display = "none";
		document.getElementById("buscinvoice").style.display = "block";
		document.getElementById("buscapurc").value = "";
		document.getElementById("buscaforrec").value = "0";
		document.getElementById("buscaidrec").value = "";
		document.getElementById("buscavalorrec").value = "";
	}
}

function tipoPesquisaconc(valor){

	if(valor==1){
		document.getElementById("buscidconcbuscidconc").style.display = "block";
		document.getElementById("buscvalorconc").style.display = "none";
		document.getElementById("buscavlfimconc").value = "0";
		document.getElementById("buscavliniconc").value = "";
	}else if(valor==2){
		document.getElementById("buscidconc").style.display = "none";
		document.getElementById("buscvalorconc").style.display = "block";
		document.getElementById("buscaidconc").value = "";
	}
}

function editaConciliacao(data,valor,banco,tax,id){
	document.formnovaconc.dataconc.value=data;
	document.formnovaconc.valorconc.value=valor;
	document.formnovaconc.contasval.value=banco;
	document.formnovaconc.idconci.value=id;
	document.formnovaconc.taxcambio.value=tax;
}

function validaformconcilia(){
	var banco = document.getElementById("contasval").value;
	var valor = document.formnovaconc.valorconc.value;
	valor = valor.replace(".","");
	valor = valor.replace(",",".");
		
	if(document.formnovaconc.dataconc.value==""){
		jAlert("A data não pode ficar em branco!","Erro!");
		//alertaPadrao('Erro!', 'O Código do produto não pode ficar em branco!', 'erro', 110, 300);
		document.formnovaconc.dataconc.focus();
		return false; 
	}else if(document.formnovaconc.valorconc.value==""){
		jAlert("O valor não pode ficar em branco!","Erro!");
		//alertaPadrao('Erro!', 'O Código do produto não pode ficar em branco!', 'erro', 110, 300);
		document.formnovaconc.valorconc.focus();
		return false; 
	}else if((document.formnovaconc.contasval.value=="0") && (document.formnovaconc.taxcambio.value!="")){
		jAlert("Selecione um banco!","Erro!");
		//alertaPadrao('Erro!', 'O Código do produto não pode ficar em branco!', 'erro', 110, 300);
		document.formnovaconc.valorconc.focus();
		return false; 
	}else if((document.formnovaconc.contasval.value!="0" && document.formnovaconc.contasval.value!="") && (document.formnovaconc.taxcambio.value=="")){
		jAlert("A taxa de cambio não pode ficar em branco!","Erro!");
		//alertaPadrao('Erro!', 'O Código do produto não pode ficar em branco!', 'erro', 110, 300);
		document.formnovaconc.taxcambio.focus();
		return false; 
	}else{
		return true;
	}
	return false;
}

function validaformpag(){
	if(document.formpagamento.emissaopag.value==""){
		jAlert("A emissão não pode ficar em branco!","Erro!");
		document.formpagamento.emissaopag.focus();
		return false; 
	}else if(document.formpagamento.vencimentopag.value==""){
		jAlert("O vencimento não pode ficar em branco!","Erro!");
		document.formpagamento.vencimentopag.focus();
		return false; 
	}else if(document.formpagamento.valorpag.value=="" || document.formpagamento.valorpag.value=="0,00"){
		jAlert("O valor não pode ficar em branco!","Erro!");
		document.formpagamento.valorpag.focus();
		return false; 
	}else if(document.formpagamento.fornpag.value=="0"){
		jAlert("O fornecedor não pode ficar em branco!","Erro!");
		document.formpagamento.fornpag.focus();
		return false; 
	}else if((document.formpagamento.fornpag.value=="out") && (document.formpagamento.outfornpag.value=="")){
		jAlert("O fornecedor não pode ficar em branco!","Erro!");
		document.formpagamento.outfornpag.focus();
		return false; 
	}else{
		var data1 = document.formpagamento.emissaopag.value;
		var data2 = document.formpagamento.vencimentopag.value;

		if ( parseInt( data2.split( "/" )[2].toString() + data2.split( "/" )[1].toString() + data2.split( "/" )[0].toString() ) < parseInt( data1.split( "/" )[2].toString() + data1.split( "/" )[1].toString() + data1.split( "/" )[0].toString() ) )
		{
			jConfirm('Vencimento anterior a emissão. Deseja continuar?', 'Confirme', function(r) {
				if(r==true){
					gravarFinanceiropag();
				}
			});
		}else{
			gravarFinanceiropag();
		}
	}	
}

/**
 * Grava recebimento
 */
function gravarFinanceiropag(){
	
	$('form[name=formpagamento]').ajaxForm({
		beforeSubmit:  function(){
			jLoader("Aguarde", "Salvando....", 600, 150);		
		}, 
    	success: function(data) {
    		console.log(data);
    		data = data.split("|");
    		
    		data[0] = data[0].replace(/^\s+|\s+$/g,"");
    		
    		if(data[0] == "erro"){
    			jAlert(data[1], "Erro!");	
    		}else{
    			jAlert("Registro salvo com sucesso!", "Sucesso!", function(){
    				window.location = '/admin/administracao/financeirochinapagcad/pay/'+data[0].replace(/^\s+|\s+$/g,"");
    			});
    		}    		   		 		    		
        },  
        error: function(data) {  
        	jAlert("Erro ao salvar o registro: "+data.statusText, "Erro!"); 
        	console.log(data.status);
        	console.log(data.statusText);
        } 
    }).submit();
		
	$.alerts._hide();	
}


function baixapag(){
	if(document.formpagamento.datapagamentopag.value==""){
		jAlert("A data do pagamento não pode ficar em branco!","Erro!");
		document.formpagamento.datapagamentopag.focus();
		return false; 
	}else if(document.formpagamento.valorpagamentopag.value==""){
		jAlert("O valor do pagamento não pode ficar em branco!","Erro!");
		document.formpagamento.valorpagamentopag.focus();
		return false; 
	}else if(document.formpagamento.bancopagamentopag.value=="0"){
		jAlert("O banco não pode ficar em branco!","Erro!");
		document.formpagamento.bancopagamentopag.focus();
		return false; 
	}else{
		jConfirm('Baixar esta conta?', 'Confirme', function(r) {
			if(r==true){
				document.formpagamento.baixarpag.value=1;
				gravarFinanceiropag();
			}
		});
	}
	return false;
}

function validaliberacaopag(){
	if(document.formpagamento.emissaopag.value==""){
		jAlert("A emissão não pode ficar em branco!","Erro!");
		document.formpagamento.emissaopag.focus();
		return false; 
	}else if(document.formpagamento.vencimentopag.value==""){
		jAlert("O vencimento não pode ficar em branco!","Erro!");
		document.formpagamento.vencimentopag.focus();
		return false; 
	}else if(document.formpagamento.valorpag.value==""){
		jAlert("O valor não pode ficar em branco!","Erro!");
		document.formpagamento.valorpag.focus();
		return false; 
	}else if(document.formpagamento.fornpag.value=="0"){
		jAlert("O fornecedor não pode ficar em branco!","Erro!");
		document.formpagamento.fornpag.focus();
		return false; 
	}else if((document.formpagamento.fornpag.value=="out") && (document.formpagamento.outfornpag.value=="")){
		jAlert("O fornecedor não pode ficar em branco!","Erro!");
		document.formpagamento.outfornpag.focus();
		return false; 
	}else{
		var data1 = document.formpagamento.emissaopag.value;
		var data2 = document.formpagamento.vencimentopag.value;

		if ( parseInt( data2.split( "/" )[2].toString() + data2.split( "/" )[1].toString() + data2.split( "/" )[0].toString() ) < parseInt( data1.split( "/" )[2].toString() + data1.split( "/" )[1].toString() + data1.split( "/" )[0].toString() ) )
		{
			
			jConfirm('Vencimento anterior a emissão. Deseja continuar?', 'Confirme', function(r) {
				if(r==true){
					jConfirm('Liberar para pagamento?', 'Confirme', function(r) {
						if(r==true){
							document.formpagamento.liberarpag.value=1;
							gravarFinanceiropag();
						}
					});
				}
			});
			  
		}else{
			jConfirm('Liberar para pagamento?', 'Confirme', function(r) {
				if(r==true){
					document.formpagamento.liberarpag.value=1;
					gravarFinanceiropag();
				}
			});
		}
	}
	return false;
}

function desabilitaConta(tipo) {
    document.formpagamento.emissaopag.disabled = true;
	document.formpagamento.vencimentopag.disabled = true;
	document.formpagamento.planocontapag.disabled = true;
	document.formpagamento.moedapag.disabled = true;
	document.formpagamento.valorpag.disabled = true;
	document.formpagamento.fornpag.disabled = true;
	document.formpagamento.outfornpag.disabled = true;
	document.formpagamento.faturapag.disabled = true;
	document.formpagamento.parcpag.disabled = true;
	
	document.formpagamento.obspag.disabled = true;
	//document.getElementById("aanex").style.display = "none";
	
	/*var qtan = document.formpagamento.intanex.value;
	var i = 0;
	for(i=1;i<=qtan;i++){
		document.getElementById("anp"+i).style.display = "none";
	}*/
	
} 

function desabilitaContabaixa(tipo) {
    document.formpagamento.datapagamentopag.disabled = true;
	document.formpagamento.valorpagamentopag.disabled = true;
	document.formpagamento.bancopagamentopag.disabled = true;
	//document.getElementById("aanex").style.display = "none";
	//document.getElementById("add").style.display = "none";
}

function validaformrec(){
	if(document.formrecebimento.emissaorec.value==""){
		jAlert("A emissão não pode ficar em branco!","Erro!");
		document.formrecebimento.emissaorec.focus();
		return false; 
	}else if(document.formrecebimento.vencimentorec.value==""){
		jAlert("O vencimento não pode ficar em branco!","Erro!");
		document.formrecebimento.vencimentorec.focus();
		return false; 
	}else if(document.formrecebimento.valorrec.value=="" || document.formrecebimento.valorrec.value== '0,00'){
		jAlert("O valor não pode ficar em branco!","Erro!");
		document.formrecebimento.valorrec.focus();
		return false; 
	}else if(document.formrecebimento.fornrec.value=="0"){
		jAlert("O fornecedor não pode ficar em branco!","Erro!");
		document.formrecebimento.fornrec.focus();
		return false; 
	}else if((document.formrecebimento.fornrec.value=="out") && (document.formrecebimento.outfornrec.value=="")){
		jAlert("O fornecedor não pode ficar em branco!","Erro!");
		document.formrecebimento.outfornrec.focus();
		return false; 
	}else{
		
		var data1 = document.formrecebimento.emissaorec.value;
		var data2 = document.formrecebimento.vencimentorec.value;

		if ( parseInt( data2.split( "/" )[2].toString() + data2.split( "/" )[1].toString() + data2.split( "/" )[0].toString() ) < parseInt( data1.split( "/" )[2].toString() + data1.split( "/" )[1].toString() + data1.split( "/" )[0].toString() ) )
		{
			jConfirm('Vencimento anterior a emissão. Deseja continuar?', 'Confirme', function(r) {
				if(r==true){
					gravarFinanceirorec()				
				}
			});
		}else{
			gravarFinanceirorec()
		}
	}
	return false;
}

/**
 * Grava recebimento
 */
function gravarFinanceirorec(){
	
	$('form[name=formrecebimento]').ajaxForm({
		beforeSubmit:  function(){
			jLoader("Aguarde", "Salvando....", 600, 150);		
		}, 
    	success: function(data) {
    		console.log(data);
    		data = data.split("|");
    		
    		data[0] = data[0].replace(/^\s+|\s+$/g,"");
    		
    		if(data[0] == "erro"){
    			jAlert(data[1], "Erro!");	
    		}else{
    			jAlert("Registro salvo com sucesso!", "Sucesso!", function(){
    				window.location = '/admin/administracao/financeirochinareccad/rec/'+data[0].replace(/^\s+|\s+$/g,"");
    			});
    		}    		   		 		    		
        },  
        error: function(data) {  
        	jAlert("Erro ao salvar o registro: "+data.statusText, "Erro!"); 
        	console.log(data.status);
        	console.log(data.statusText);
        } 
    }).submit();
		
	$.alerts._hide();	
}


function baixarrec(){
	if(document.formrecebimento.emissaorec.value==""){
		jAlert("A emissão não pode ficar em branco!","Erro!");
		document.formrecebimento.emissaorec.focus();
		return false; 
	}else if(document.formrecebimento.vencimentorec.value==""){
		jAlert("O vencimento não pode ficar em branco!","Erro!");
		document.formrecebimento.vencimentorec.focus();
		return false; 
	}else if(document.formrecebimento.valorrec.value=="" || document.formrecebimento.valorrec.value== '0,00'){
		jAlert("O valor não pode ficar em branco!","Erro!");
		document.formrecebimento.valorrec.focus();
		return false; 
	}else if(document.formrecebimento.fornrec.value=="0"){
		jAlert("O fornecedor não pode ficar em branco!","Erro!");
		document.formrecebimento.fornrec.focus();
		return false; 
	}else if((document.formrecebimento.fornrec.value=="out") && (document.formrecebimento.outfornrec.value=="")){
		jAlert("O fornecedor não pode ficar em branco!","Erro!");
		document.formrecebimento.outfornrec.focus();
		return false; 
	}else if(document.formrecebimento.datapagamentorec.value==""){
		jAlert("A data do recebimento não pode ficar em branco!","Erro!");
		document.formrecebimento.datapagamentorec.focus();
		return false; 
	}else if(document.formrecebimento.valorpagamentorec.value==""){
		jAlert("O valor do recebimento não pode ficar em branco!","Erro!");
		document.formrecebimento.valorpagamentorec.focus();
		return false; 
	}else if(document.formrecebimento.bancopagamentorec.value=="0"){
		jAlert("O banco não pode ficar em branco!","Erro!");
		document.formrecebimento.bancopagamentorec.focus();
		return false; 
	}else{
		var data1 = document.formrecebimento.emissaorec.value;
		var data2 = document.formrecebimento.vencimentorec.value;

		if ( parseInt( data2.split( "/" )[2].toString() + data2.split( "/" )[1].toString() + data2.split( "/" )[0].toString() ) < parseInt( data1.split( "/" )[2].toString() + data1.split( "/" )[1].toString() + data1.split( "/" )[0].toString() ) )
		{
			jConfirm('Vencimento anterior a emissão. Deseja continuar?', 'Confirme', function(r) {
				if(r==true){
					jConfirm('Baixar recebimento?', 'Confirme', function(r) {
						if(r==true){
							document.formrecebimento.baixarec.value=1;
							gravarFinanceirorec();
						}
					});
				}
			});
		}else{
			jConfirm('Baixar recebimento?', 'Confirme', function(r) {
				if(r==true){
					document.formrecebimento.baixarec.value = 1;
					gravarFinanceirorec();
				}
			});
		}
		
	}
	return false;
}

function desabilitaContarec() {
    
	document.formrecebimento.emissaorec.disabled = true;
	document.formrecebimento.vencimentorec.disabled = true;
	document.formrecebimento.planocontarec.disabled = true;
	document.formrecebimento.moedarec.disabled = true;
	document.formrecebimento.valorrec.disabled = true;
	document.formrecebimento.fornrec.disabled = true;
	document.formrecebimento.outfornrec.disabled = true;
	document.formrecebimento.faturarec.disabled = true;
	document.formrecebimento.parcrec.disabled = true;
	document.formrecebimento.datapagamentorec.disabled = true;
	document.formrecebimento.valorpagamentorec.disabled = true;
	document.formrecebimento.bancopagamentorec.disabled = true;
	document.formrecebimento.obsrec.disabled = true;
}

function selecionaMoedarec(moeda){
	if(moeda=="USD"){
		document.formrecebimento.bancopagamentorec.value = 9;
		document.formrecebimento.bancopagamentorec.disabled = true;
	}else{
		document.formrecebimento.bancopagamentorec.disabled = false;
	}
}

function deleteRow(i,id){
    document.getElementById('tpurch').deleteRow(i);

    var arrId = document.getElementById("idpurch").value;
    document.getElementById('idpurch').value = arrId.replace(id+",","");
}

function addPurchase(idconta){
	
	var tipo;
	var ver = 0;
	if(document.getElementById("tipo1").checked){
		tipo = 1;				
	}else if(document.getElementById("tipo2").checked){
		tipo = 2;
    }
	
	if((idconta==2 && tipo==1) || (idconta==1) && (tipo==2)){
		if(!confirm("Ao selecionar esse comprador, as compras já cadastradas serão perdidas. Deseja continuar?")){
			ver = 1;
		};
	}	
	
	if(ver==0){
		
		var t = document.getElementById("tpurch");
		while( t.rows.length ){
			t.deleteRow(0);
		}
		
		document.getElementById("tpurch").style.display="block";
		document.getElementById("idpurch").value="";
		
		form = document.contas;
		
		var cont=0;
		for (i=0;i<form.length;i++){
			
			if(form[i].checked==true){
				
				var idv   = document.getElementById("idpurch");				
				var tbCod = document.getElementById("tpurch").insertRow(cont);
				var y= tbCod.insertCell(0);
				var z= tbCod.insertCell(1);
				var e= tbCod.insertCell(2);
				idv.value = idv.value+form[i].name+",";
				
				if(tipo==1){
					y.align = "left";
					y.setAttribute("style","width:100px;");
					y.innerHTML='<a href="http://www.ztlbrasil.com.br/admin/kang/pedidoscompraped/ped/'+form[i].id+'" target="_blank" >'+form[i].value+'</a>';
					z.setAttribute("style","width:140px;");
					z.innerHTML='<input type="text" name="vlcompra_'+form[i].name+'" value=""  style="width: 70px" class="moeda" >';
					e.align = "center";
					e.setAttribute("style","width:10px;");
					e.innerHTML='<a href="#" onclick="deleteRow(this.parentNode.parentNode.rowIndex,'+form[i].name+')"><img src="/public/sistema/imagens/icons/middlenav/close.png" width="12" border="0"></a>';
				}else{
					y.align = "left";
					y.setAttribute("style","width:100px;");
					y.innerHTML='<a href="http://www.ztlbrasil.com.br/admin/shuntaicompras/gerarpedido/ped/'+form[i].id+'" target="_blank" >'+form[i].value+'</a>';
					z.setAttribute("style","width:140px;");
					z.innerHTML='<input type="text" name="vlcompra_'+form[i].name+'" value="" style="width: 70px"  class="moeda" >';
					e.align = "center";
					e.setAttribute("style","width:10px;");
					e.innerHTML='<a href="#" onclick="deleteRow(this.parentNode.parentNode.rowIndex,'+form[i].name+')"><img src="/public/sistema/imagens/icons/middlenav/close.png" width="12" border="0"></a>';
				}
				cont++;            						
			}        
			    					      						
		}
		document.getElementById("tipopurch").value = tipo;
		$('.moeda').mask('000.000.000.000.000,00', {reverse: true});
	}
	
	$.alerts._hide();
}



function addInvoice(idconta){
	
	var tipo;
	var ver = 0;
		
	var t = document.getElementById("tpurch");
	while( t.rows.length ){
		t.deleteRow(0);
	}
	
	document.getElementById("tpurch").style.display="block";
	document.getElementById("idpurch").value="";
	
	form = document.contas;
	var cont=0;
	for (i=0;i<form.length;i++){
		if(form[i].checked==true){
			
			var idv   = document.getElementById("idpurch");
			
			var tbCod = document.getElementById("tpurch").insertRow(cont);
			var y= tbCod.insertCell(0);
			var z= tbCod.insertCell(1);
			var e= tbCod.insertCell(2);
			idv.value = idv.value+form[i].name+",";
			
			
			y.align = "left";
			y.setAttribute("style","width:120px;");
			y.innerHTML='<a href="/admin/kang/vendasdet/ped/'+form[i].id+'" target="_blank" >'+form[i].value+'</a>';
			z.setAttribute("style","width:120px;");
			z.innerHTML='<input type="text" name="vlinvoice_'+form[i].name+'" value="" style="width: 70px"  class="moeda" >';
			e.align = "center";
			e.setAttribute("style","width:10px;");
			e.innerHTML='<a href="#" onclick="deleteRow(this.parentNode.parentNode.rowIndex,'+form[i].name+')"><img src="/public/sistema/imagens/icons/middlenav/close.png" width="12" border="0"></a>';
			
			cont++;            						
		}        
		    					      						
	}
	
	$('.moeda').mask('000.000.000.000.000,00', {reverse: true});
	
	$.alerts._hide();
}
