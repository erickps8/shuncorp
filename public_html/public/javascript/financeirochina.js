
function tipoPesquisa(valor){

	if(valor==1){
		document.getElementById("buscid").style.display = "block";
		document.getElementById("buscfor").style.display = "none";
		document.getElementById("buscvalor").style.display = "none";
		document.getElementById("buscpurchase").style.display = "none";
		document.getElementById("buscplcontas").style.display = "none";
		document.getElementById("buscaplcontas").value = "0";
		document.getElementById("buscafor").value = "0";
		document.getElementById("buscavalor").value = "";
		document.getElementById("buscapurc").value = "";
	}else if(valor==2){
		document.getElementById("buscid").style.display = "none";
		document.getElementById("buscfor").style.display = "block";
		document.getElementById("buscvalor").style.display = "none";
		document.getElementById("buscpurchase").style.display = "none";
		document.getElementById("buscplcontas").style.display = "none";
		document.getElementById("buscaplcontas").value = "0";
		document.getElementById("buscaid").value = "";
		document.getElementById("buscavalor").value = "";
		document.getElementById("buscapurc").value = "";
	}else if(valor==3){
		document.getElementById("buscid").style.display = "none";
		document.getElementById("buscfor").style.display = "none";
		document.getElementById("buscvalor").style.display = "block";
		document.getElementById("buscpurchase").style.display = "none";
		document.getElementById("buscplcontas").style.display = "none";
		document.getElementById("buscaplcontas").value = "0";
		document.getElementById("buscafor").value = "0";
		document.getElementById("buscaid").value = "";
		document.getElementById("buscapurc").value = "";
	}else if(valor==4){
		document.getElementById("buscid").style.display = "none";
		document.getElementById("buscfor").style.display = "none";
		document.getElementById("buscvalor").style.display = "none";
		document.getElementById("buscplcontas").style.display = "none";
		document.getElementById("buscpurchase").style.display = "block";
		document.getElementById("buscafor").value = "0";
		document.getElementById("buscaplcontas").value = "0";
		document.getElementById("buscaid").value = "";
		document.getElementById("buscavalor").value = "";
	}else if(valor==5){
		document.getElementById("buscid").style.display = "none";
		document.getElementById("buscfor").style.display = "none";
		document.getElementById("buscvalor").style.display = "none";
		document.getElementById("buscpurchase").style.display = "none";
		document.getElementById("buscplcontas").style.display = "block";
		document.getElementById("buscafor").value = "0";
		document.getElementById("buscaid").value = "";
		document.getElementById("buscavalor").value = "";
		document.getElementById("buscapurc").value = "";
	}
}

function tipoPesquisarec(valor){

	if(valor==1){
		document.getElementById("buscidrec").style.display = "block";
		document.getElementById("buscforrec").style.display = "none";
		document.getElementById("buscvalorrec").style.display = "none";
		document.getElementById("buscplcontas").style.display = "none";
		document.getElementById("buscaforrec").value = "0";
		document.getElementById("buscavalorrec").value = "";
	}else if(valor==2){
		document.getElementById("buscidrec").style.display = "none";
		document.getElementById("buscforrec").style.display = "block";
		document.getElementById("buscvalorrec").style.display = "none";
		document.getElementById("buscplcontas").style.display = "none";
		document.getElementById("buscaidrec").value = "";
		document.getElementById("buscavalorrec").value = "";
	}else if(valor==3){
		document.getElementById("buscidrec").style.display = "none";
		document.getElementById("buscforrec").style.display = "none";
		document.getElementById("buscvalorrec").style.display = "block";
		document.getElementById("buscplcontas").style.display = "none";
		document.getElementById("buscaforrec").value = "0";
		document.getElementById("buscaidrec").value = "";
	}else if(valor==4){
		document.getElementById("buscidrec").style.display = "none";
		document.getElementById("buscforrec").style.display = "none";
		document.getElementById("buscvalorrec").style.display = "none";
		document.getElementById("buscplcontas").style.display = "block";
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

var alertaPadrao = function(titulo, msg, tipo, altura, largura) {
	$('body').append('<a href="#" id="alerta-padrao"></a>');
	$('#alerta-padrao').m2brDialog({
			draggable: true,
			texto: msg,
			tipo: tipo,
			titulo: titulo,
			altura: altura,
			largura: largura,
			botoes: {
				1: {
					label: 'Fechar',
					tipo: 'fechar'
				}
			}									   
	});
	$('#alerta-padrao')
		.click()
		.remove();
}; // fim alertaPadrao

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
		alert("A data não pode ficar em branco!");
		//alertaPadrao('Erro!', 'O Código do produto não pode ficar em branco!', 'erro', 110, 300);
		document.formnovaconc.dataconc.focus();
		return false; 
	}else if(document.formnovaconc.valorconc.value==""){
		alert("O valor não pode ficar em branco!");
		//alertaPadrao('Erro!', 'O Código do produto não pode ficar em branco!', 'erro', 110, 300);
		document.formnovaconc.valorconc.focus();
		return false; 
	}else if((document.formnovaconc.contasval.value=="0") && (document.formnovaconc.taxcambio.value!="")){
		alert("Selecione um banco!");
		//alertaPadrao('Erro!', 'O Código do produto não pode ficar em branco!', 'erro', 110, 300);
		document.formnovaconc.valorconc.focus();
		return false; 
	}else if((document.formnovaconc.contasval.value!="0") && (document.formnovaconc.taxcambio.value=="")){
		alert("A taxa de cambio não pode ficar em branco!");
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
		alert("A emissão não pode ficar em branco!");
		document.formpagamento.emissaopag.focus();
		return false; 
	}else if(document.formpagamento.vencimentopag.value==""){
		alert("O vencimento não pode ficar em branco!");
		document.formpagamento.vencimentopag.focus();
		return false; 
	}else if(document.formpagamento.valorpag.value==""){
		alert("O valor não pode ficar em branco!");
		document.formpagamento.valorpag.focus();
		return false; 
	}else if(document.formpagamento.fornpag.value=="0"){
		alert("O fornecedor não pode ficar em branco!");
		document.formpagamento.fornpag.focus();
		return false; 
	}else if((document.formpagamento.fornpag.value=="out") && (document.formpagamento.outfornpag.value=="")){
		alert("O fornecedor não pode ficar em branco!");
		document.formpagamento.outfornpag.focus();
		return false; 
	}else if(document.formpagamento.faturapag.value==""){
		alert("A fatura não pode ficar em branco!");
		document.formpagamento.faturapag.focus();
		return false; 
	}else if(document.formpagamento.parcpag.value==""){
		alert("O periodo não pode ficar em branco!");
		document.formpagamento.parcpag.focus();
		return false; 
	}else{
		var data1 = document.formpagamento.emissaopag.value;
		var data2 = document.formpagamento.vencimentopag.value;

		if ( parseInt( data2.split( "/" )[2].toString() + data2.split( "/" )[1].toString() + data2.split( "/" )[0].toString() ) < parseInt( data1.split( "/" )[2].toString() + data1.split( "/" )[1].toString() + data1.split( "/" )[0].toString() ) )
		{
		  if(confirm('Vencimento anterior a emissão. Deseja continuar?')){
			 document.formpagamento.submit();
		  }		  
		}else{
			document.formpagamento.submit();
		}
	}
	return false;
}

function baixapag(){
	if(document.formpagamento.datapagamentopag.value==""){
		alert("A data do pagamento não pode ficar em branco!");
		document.formpagamento.datapagamentopag.focus();
		return false; 
	}else if(document.formpagamento.valorpagamentopag.value==""){
		alert("O valor do pagamento não pode ficar em branco!");
		document.formpagamento.valorpagamentopag.focus();
		return false; 
	}else if(document.formpagamento.bancopagamentopag.value=="0"){
		alert("O banco não pode ficar em branco!");
		document.formpagamento.bancopagamentopag.focus();
		return false; 
	}else{
		if(confirm('Baixar a conta?')){
			document.formpagamento.baixarpag.value=1;
			document.formpagamento.submit();
		}
	}
	return false;
}

function validaliberacaopag(){
	if(document.formpagamento.emissaopag.value==""){
		alert("A emissão não pode ficar em branco!");
		document.formpagamento.emissaopag.focus();
		return false; 
	}else if(document.formpagamento.vencimentopag.value==""){
		alert("O vencimento não pode ficar em branco!");
		document.formpagamento.vencimentopag.focus();
		return false; 
	}else if(document.formpagamento.valorpag.value==""){
		alert("O valor não pode ficar em branco!");
		document.formpagamento.valorpag.focus();
		return false; 
	}else if(document.formpagamento.fornpag.value=="0"){
		alert("O fornecedor não pode ficar em branco!");
		document.formpagamento.fornpag.focus();
		return false; 
	}else if((document.formpagamento.fornpag.value=="out") && (document.formpagamento.outfornpag.value=="")){
		alert("O fornecedor não pode ficar em branco!");
		document.formpagamento.outfornpag.focus();
		return false; 
	}else if(document.formpagamento.faturapag.value==""){
		alert("A fatura não pode ficar em branco!");
		document.formpagamento.faturapag.focus();
		return false; 
	}else if(document.formpagamento.parcpag.value==""){
		alert("O periodo não pode ficar em branco!");
		document.formpagamento.parcpag.focus();
		return false; 
	}else{
		var data1 = document.formpagamento.emissaopag.value;
		var data2 = document.formpagamento.vencimentopag.value;

		if ( parseInt( data2.split( "/" )[2].toString() + data2.split( "/" )[1].toString() + data2.split( "/" )[0].toString() ) < parseInt( data1.split( "/" )[2].toString() + data1.split( "/" )[1].toString() + data1.split( "/" )[0].toString() ) )
		{
		  if(confirm('Vencimento anterior a emissão. Deseja continuar?')){
			  	if(confirm('Liberar para pagamento?')){
					document.formpagamento.liberarpag.value=1;
					document.formpagamento.submit();
				}
		  }		  
		}else{
			if(confirm('Liberar para pagamento?')){
				document.formpagamento.liberarpag.value=1;
				document.formpagamento.submit();
			}
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
	
	var qtan = document.formpagamento.intanex.value;
	var i = 0;
	for(i=1;i<=qtan;i++){
		document.getElementById("anp"+i).style.display = "none";
	}
	
} 

function desabilitaContabaixa(tipo) {
    document.formpagamento.datapagamentopag.disabled = true;
	document.formpagamento.valorpagamentopag.disabled = true;
	document.formpagamento.bancopagamentopag.disabled = true;
	document.getElementById("aanex").style.display = "none";
	document.getElementById("add").style.display = "none";
}

function validaformrec(){
	if(document.formrecebimento.emissaorec.value==""){
		alert("A emissão não pode ficar em branco!");
		document.formrecebimento.emissaorec.focus();
		return false; 
	}else if(document.formrecebimento.vencimentorec.value==""){
		alert("O vencimento não pode ficar em branco!");
		document.formrecebimento.vencimentorec.focus();
		return false; 
	}else if(document.formrecebimento.valorrec.value==""){
		alert("O valor não pode ficar em branco!");
		document.formrecebimento.valorrec.focus();
		return false; 
	}else if(document.formrecebimento.fornrec.value=="0"){
		alert("O fornecedor não pode ficar em branco!");
		document.formrecebimento.fornrec.focus();
		return false; 
	}else if((document.formrecebimento.fornrec.value=="out") && (document.formrecebimento.outfornrec.value=="")){
		alert("O fornecedor não pode ficar em branco!");
		document.formrecebimento.outfornrec.focus();
		return false; 
	}else if(document.formrecebimento.faturarec.value==""){
		alert("A fatura não pode ficar em branco!");
		document.formrecebimento.faturarec.focus();
		return false; 
	}else if(document.formrecebimento.parcrec.value==""){
		alert("O periodo não pode ficar em branco!");
		document.formrecebimento.parcrec.focus();
		return false; 
	}else{
		
		var data1 = document.formrecebimento.emissaorec.value;
		var data2 = document.formrecebimento.vencimentorec.value;

		if ( parseInt( data2.split( "/" )[2].toString() + data2.split( "/" )[1].toString() + data2.split( "/" )[0].toString() ) < parseInt( data1.split( "/" )[2].toString() + data1.split( "/" )[1].toString() + data1.split( "/" )[0].toString() ) )
		{
		  if(confirm('Vencimento anterior a emissão. Deseja continuar?')){
			document.formrecebimento.submit();				
		  }		  
		}else{
			document.formrecebimento.submit();
		}
	}
	return false;
}

function baixarrec(){
	if(document.formrecebimento.emissaorec.value==""){
		alert("A emissão não pode ficar em branco!");
		document.formrecebimento.emissaorec.focus();
		return false; 
	}else if(document.formrecebimento.vencimentorec.value==""){
		alert("O vencimento não pode ficar em branco!");
		document.formrecebimento.vencimentorec.focus();
		return false; 
	}else if(document.formrecebimento.valorrec.value==""){
		alert("O valor não pode ficar em branco!");
		document.formrecebimento.valorrec.focus();
		return false; 
	}else if(document.formrecebimento.fornrec.value=="0"){
		alert("O fornecedor não pode ficar em branco!");
		document.formrecebimento.fornrec.focus();
		return false; 
	}else if((document.formrecebimento.fornrec.value=="out") && (document.formrecebimento.outfornrec.value=="")){
		alert("O fornecedor não pode ficar em branco!");
		document.formrecebimento.outfornrec.focus();
		return false; 
	}else if(document.formrecebimento.faturarec.value==""){
		alert("A fatura não pode ficar em branco!");
		document.formrecebimento.faturarec.focus();
		return false; 
	}else if(document.formrecebimento.parcrec.value==""){
		alert("O periodo não pode ficar em branco!");
		document.formrecebimento.parcrec.focus();
		return false; 
	}else if(document.formrecebimento.datapagamentorec.value==""){
		alert("A data do recebimento não pode ficar em branco!");
		document.formrecebimento.datapagamentorec.focus();
		return false; 
	}else if(document.formrecebimento.valorpagamentorec.value==""){
		alert("O valor do recebimento não pode ficar em branco!");
		document.formrecebimento.valorpagamentorec.focus();
		return false; 
	}else if(document.formrecebimento.bancopagamentorec.value=="0"){
		alert("O banco não pode ficar em branco!");
		document.formrecebimento.bancopagamentorec.focus();
		return false; 
	}else{
		var data1 = document.formrecebimento.emissaorec.value;
		var data2 = document.formrecebimento.vencimentorec.value;

		if ( parseInt( data2.split( "/" )[2].toString() + data2.split( "/" )[1].toString() + data2.split( "/" )[0].toString() ) < parseInt( data1.split( "/" )[2].toString() + data1.split( "/" )[1].toString() + data1.split( "/" )[0].toString() ) )
		{
		  if(confirm('Vencimento anterior a emissão. Deseja continuar?')){
			if(confirm('Baixar recebimento?')){
				document.formrecebimento.baixarec.value=1;
				document.formrecebimento.submit();
			}	
		  }		  
		}else{
			if(confirm('Baixar recebimento?')){
				document.formrecebimento.baixarec.value=1;
				document.formrecebimento.submit();
			}
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
	document.getElementById("aanexrec").style.display = "none";
	
	var qtan = document.formrecebimento.intanexrec.value;
	var i = 0;
	for(i=1;i<=qtan;i++){
		document.getElementById("anprec"+i).src = "";
	}
	
} 

function selecionaMoeda(moeda){
	if(moeda=="USD"){
		document.formpagamento.bancopagamentopag.disabled = true;
	}else{
		document.formpagamento.bancopagamentopag.disabled = false;
	}
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
					z.innerHTML='<input type="text" name="vlcompra_'+form[i].name+'" value="" size="10" onkeyup="mascara_num(this);" >';
					e.align = "center";
					e.setAttribute("style","width:10px;");
					e.innerHTML='<a href="#" onclick="deleteRow(this.parentNode.parentNode.rowIndex,'+form[i].name+')"><img src="http://www.ztlbrasil.com.br/images/window-close.png" width="12" heigth="12" border="0"></a>';
				}else{
					y.align = "left";
					y.setAttribute("style","width:100px;");
					y.innerHTML='<a href="http://www.ztlbrasil.com.br/admin/shuntaicompras/gerarpedido/ped/'+form[i].id+'" target="_blank" >'+form[i].value+'</a>';
					z.setAttribute("style","width:140px;");
					z.innerHTML='<input type="text" name="vlcompra_'+form[i].name+'" value="" size="10" onkeyup="mascara_num(this);" >';
					e.align = "center";
					e.setAttribute("style","width:10px;");
					e.innerHTML='<a href="#" onclick="deleteRow(this.parentNode.parentNode.rowIndex,'+form[i].name+')"><img src="http://www.ztlbrasil.com.br/images/window-close.png" width="12" heigth="12" border="0"></a>';
				}
				cont++;            						
			}        
			    					      						
		}
		document.getElementById("tipopurch").value = tipo;
	}
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
			z.innerHTML='<input type="text" name="vlinvoice_'+form[i].name+'" value="" size="10" onkeyup="mascara_num(this);" >';
			e.align = "center";
			e.setAttribute("style","width:10px;");
			e.innerHTML='<a href="#" onclick="deleteRow(this.parentNode.parentNode.rowIndex,'+form[i].name+')"><img src="/public/images/window-close.png" width="12" heigth="12" border="0"></a>';
			
			cont++;            						
		}        
		    					      						
	}
	
}
