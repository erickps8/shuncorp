
function tipoPesquisa(valor){

	if(valor==1){
		document.getElementById("buscid").style.display = "block";
		document.getElementById("buscfor").style.display = "none";
		document.getElementById("buscvalor").style.display = "none";
		document.getElementById("buscpcontas").style.display = "none";
		document.getElementById("buscsituacao").style.display = "none";
		document.getElementById("buscfatura").style.display = "none";
		document.getElementById("buscafor").value = "0";
		document.getElementById("buscavalor1").value = "";
		document.getElementById("buscavalor2").value = "";
	}else if(valor==2){
		document.getElementById("buscid").style.display = "none";
		document.getElementById("buscfor").style.display = "block";
		document.getElementById("buscvalor").style.display = "none";
		document.getElementById("buscpcontas").style.display = "none";
		document.getElementById("buscsituacao").style.display = "none";
		document.getElementById("buscfatura").style.display = "none";
		document.getElementById("buscaid").value = "";
		document.getElementById("buscavalor1").value = "";
		document.getElementById("buscavalor2").value = "";
	}else if(valor==3){
		document.getElementById("buscid").style.display = "none";
		document.getElementById("buscfor").style.display = "none";
		document.getElementById("buscvalor").style.display = "block";
		document.getElementById("buscpcontas").style.display = "none";
		document.getElementById("buscsituacao").style.display = "none";
		document.getElementById("buscfatura").style.display = "none";
		document.getElementById("buscafor").value = "0";
		document.getElementById("buscaid").value = "";		
	}else if(valor==4){
		document.getElementById("buscid").style.display = "none";
		document.getElementById("buscfor").style.display = "none";
		document.getElementById("buscvalor").style.display = "none";
		document.getElementById("buscpcontas").style.display = "block";
		document.getElementById("buscsituacao").style.display = "none";
		document.getElementById("buscfatura").style.display = "none";
		document.getElementById("buscafor").value = "0";
		document.getElementById("buscaid").value = "";
		document.getElementById("buscavalor1").value = "";
		document.getElementById("buscavalor2").value = "";
	}else if(valor==5){
		document.getElementById("buscid").style.display = "none";
		document.getElementById("buscfor").style.display = "none";
		document.getElementById("buscvalor").style.display = "none";
		document.getElementById("buscpcontas").style.display = "none";
		document.getElementById("buscfatura").style.display = "none";
		document.getElementById("buscsituacao").style.display = "block";
		document.getElementById("buscafor").value = "0";
		document.getElementById("buscaid").value = "";
		document.getElementById("buscavalor1").value = "";
		document.getElementById("buscavalor2").value = "";
	}else if(valor==6){
		document.getElementById("buscid").style.display = "none";
		document.getElementById("buscfor").style.display = "none";
		document.getElementById("buscvalor").style.display = "none";
		document.getElementById("buscpcontas").style.display = "none";
		document.getElementById("buscsituacao").style.display = "none";
		document.getElementById("buscfatura").style.display = "block";
		document.getElementById("buscafor").value = "0";
		document.getElementById("buscaid").value = "";
		document.getElementById("buscavalor1").value = "";
		document.getElementById("buscavalor2").value = "";
	}
}

function tipoPesquisarec(valor){

	if(valor==1){
		document.getElementById("buscidrec").style.display = "block";
		document.getElementById("buscforrec").style.display = "none";
		document.getElementById("buscvalorrec").style.display = "none";
		document.getElementById("buscplano").style.display = "none";
		document.getElementById("buscsituacao").style.display = "none";
		document.getElementById("buscaplano").value = "0";
		document.getElementById("buscaforrec").value = "0";
		document.getElementById("buscavalorrec").value = "";
	}else if(valor==2){
		document.getElementById("buscidrec").style.display = "none";
		document.getElementById("buscforrec").style.display = "block";
		document.getElementById("buscvalorrec").style.display = "none";
		document.getElementById("buscplano").style.display = "none";
		document.getElementById("buscsituacao").style.display = "none";
		document.getElementById("buscaplano").value = "0";
		document.getElementById("buscaidrec").value = "";
		document.getElementById("buscavalorrec").value = "";
	}else if(valor==3){
		document.getElementById("buscidrec").style.display = "none";
		document.getElementById("buscforrec").style.display = "none";
		document.getElementById("buscvalorrec").style.display = "block";
		document.getElementById("buscplano").style.display = "none";
		document.getElementById("buscsituacao").style.display = "none";
		document.getElementById("buscaplano").value = "0";
		document.getElementById("buscaforrec").value = "0";
		document.getElementById("buscaidrec").value = "";
	}else if(valor==4){
		document.getElementById("buscidrec").style.display = "none";
		document.getElementById("buscforrec").style.display = "none";
		document.getElementById("buscvalorrec").style.display = "none";
		document.getElementById("buscplano").style.display = "block";
		document.getElementById("buscsituacao").style.display = "none";
		document.getElementById("buscaforrec").value = "0";
		document.getElementById("buscaidrec").value = "";
		document.getElementById("buscavalorrec").value = "";
	}else if(valor==5){
		document.getElementById("buscidrec").style.display = "none";
		document.getElementById("buscforrec").style.display = "none";
		document.getElementById("buscvalorrec").style.display = "none";
		document.getElementById("buscplano").style.display = "none";
		document.getElementById("buscsituacao").style.display = "block";
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

function editaConciliacao(data,valor,id){
	document.formnovaconc.dataconc.value=data;
	document.formnovaconc.valorconc.value=valor;
	document.formnovaconc.idconci.value=id;			
}

function validaformpag(){
	if(document.formpagamento.emissaopag.value==""){
		alert("A emissão não pode ficar em branco!");
		document.formpagamento.emissaopag.focus();
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
	}else{
		document.formpagamento.submit();		
	}
	return false;
}

function baixapag(){
	if(document.formpagamento.emissaopag.value==""){
		alert("A emissão não pode ficar em branco!");
		document.formpagamento.emissaopag.focus();
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
	document.formpagamento.txcambio.disabled = true;
	
	document.formpagamento.obspag.disabled = true;
	//document.getElementById("aanex").style.display = "none";
	
	var qtan = document.formpagamento.intanex.value;
	var i = 0;
	for(i=1;i<=qtan;i++){
		document.getElementById("anp"+i).src = "";
	}	
} 

function desabilitaContabaixa() {
	document.getElementById("formparcela").style.display = "none";
}

function validaformrec(){
	if(document.formrecebimento.emissaorec.value==""){
		alert("A emissão não pode ficar em branco!");
		document.formrecebimento.emissaorec.focus();
		return false; 
	}else if(document.formrecebimento.valortotalrec.value==""){
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
	}else{
		document.formrecebimento.submit();		
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

function selecionaMoeda(moeda,id){
	if(moeda=="BRL"){
		document.getElementById('tdcambio_'+id).style.display = "none";
	}else{
		document.getElementById('tdcambio_'+id).style.display = "block";
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

    var arrId = document.getElementById("idnfe").value;
    document.getElementById('idnfe').value = arrId.replace(id+",","");

}

function addPurchase(){
	
	var t = document.getElementById("tpurchnfe");
	while( t.rows.length ){
		t.deleteRow(0);
	}

	document.getElementById("tpurchnfe").style.display="block";
	document.getElementById("idnfe").value="";
	
	
	form = document.contas;
	var cont=0;
	for (i=0;i<form.length;i++){
		if(form[i].checked==true){
			
			var idv   = document.getElementById("idnfe");
			
			var tbCod = document.getElementById("tpurchnfe").insertRow(cont);
			var y= tbCod.insertCell(0);
			var z= tbCod.insertCell(1);
			var e= tbCod.insertCell(2);
			idv.value = idv.value+form[i].name+",";
			
			y.align = "left";
			y.setAttribute("style","width:80px;");
			y.innerHTML='<a href="/admin/nfe/visualizarnfe/nfe/'+form[i].id+'" target="_blank" >'+form[i].value+'</a>';
			z.setAttribute("style","width:80px;");
			z.innerHTML='<input type="text" name="valor_'+form[i].name+'" value="" style="width: 70px" onkeyup="moedaNegativo(this,event,\'###.###.###,##\',true);">';
			e.align = "center";
			e.innerHTML='<a href="javascript:void(0)" onclick="deleteRow(this.parentNode.parentNode.rowIndex,'+form[i].name+')"><img src="/public/sistema/imagens/icons/middlenav/close.png" width="12" border="0"></a>';
			
			cont++;            						
		}        
		    					      						
	}
	
	$("#popup_ok").trigger('click');
	
}
