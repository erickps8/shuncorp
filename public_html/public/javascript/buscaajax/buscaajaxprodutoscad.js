var xmlHttp;
var qtt = 0;

function Mostramontakit(cod,qt){
	if (cod.length==0){
		alert('Código inválido!');
		return;
	}
	
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	//var url="/homologacao/admin/cadastro/buscaprodutokit";
	var url="/admin/cadastro/buscaprodutokit";
	url=url+"/q/"+cod+"/qt/"+qt;
	url=url+"/sid/"+Math.random();
	xmlHttp.onreadystatechange=stateChangedkit;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
	qtt = qt;
	
}

function stateChangedkit(){
	
	if (xmlHttp.readyState==4){
		
		var texto = xmlHttp.responseText;
		texto = texto.replace(/\+/g," ");
		texto = unescape(texto);
		texto = texto.split("|");

		var arrId = document.getElementById('arrId').value;

		narrId = arrId.split(";");
		erro = 0;
		for(i=0;i < narrId.length; i++){
			if(texto[0]==narrId[i]){
				erro = 1;
			}
		}

		if(erro==1){
			/*alert('Este código já esta na lista');*/
			jAlert('Este código já esta na lista!', 'Erro!');
			document.getElementById("codbusca").focus();
		    return false;    
		}else if(texto[0]=="erro1"){
			/*alert('Código incorreto');*/
			jAlert('Código incorreto!', 'Erro!');
			document.getElementById("codbusca").focus();			
		}else{
			document.getElementById('arrId').value += texto[0]+";";
			
			var tbtable = document.getElementById("tbcomp");
			var numOfRows = tbtable.rows.length;
			var tbCod = tbtable.insertRow(numOfRows-2);
		
			var y= tbCod.insertCell(0);
			var z= tbCod.insertCell(1);
			var b= tbCod.insertCell(2);
			var c= tbCod.insertCell(3);
			var h= tbCod.insertCell(4);
			var d= tbCod.insertCell(5);
			var g= tbCod.insertCell(6);
			
			var sit = "";
			if(texto[7]==0){
				sit = "P";
			}else if(texto[7]==1){
				sit = "D";
			}else if(texto[7]==2){
				sit = "I";
			}
			
			y.align = "left";
			y.setAttribute("class","td_orc_min");
			y.innerHTML=texto[1]+"<input type='hidden' name='kit_"+texto[0]+"' value='"+qtt+"' >";
			z.align = "center";
			z.setAttribute("class","td_orc_min");
			z.innerHTML=sit;
			b.align = "left";
			b.setAttribute("class","td_orc_min");
			b.innerHTML=texto[8];
			c.align = "center";
			c.setAttribute("class","td_orc_min");
			c.innerHTML=qtt;
			h.align = "center";
			h.setAttribute("class","td_orc_min");
			h.innerHTML=texto[6];
			d.align = "right";
			d.setAttribute("class","td_orc_min");
			d.innerHTML=texto[5];
			g.align = "center";
			g.setAttribute("class","td_orc_min");
			g.innerHTML='<a href="javascript:void(0);" onclick="deleteRow(this.parentNode.parentNode.rowIndex,'+texto[0]+')"><img src="http://www.ztlbrasil.com.br/admin/images/window-close.png" width="13" heigth="13" border="0"></a>';
			
			document.getElementById("codbusca").focus();
			total = document.getElementById("totalkit").value;
			document.getElementById("idtotal").innerHTML = float2moeda(parseFloat(total)+parseFloat(texto[4]));
			document.getElementById("totalkit").value = parseFloat(total)+parseFloat(texto[4])
		}
		
	}
}

function float2moeda(num) {

	   x = 0;

	   if(num<0) {
	      num = Math.abs(num);
	      x = 1;
	   }
	   if(isNaN(num)) num = "0";
	      cents = Math.floor((num*100+0.5)%100);

	   num = Math.floor((num*100+0.5)/100).toString();

	   if(cents < 10) cents = "0" + cents;
	      for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
	         num = num.substring(0,num.length-(4*i+3))+'.'
	               +num.substring(num.length-(4*i+3));
	      ret = num + ',' + cents;
	      if (x == 1) ret = ' - ' + ret;return ret;

}

function moeda2float(moeda){
   moeda = moeda.replace(".","");
   moeda = moeda.replace(",",".");
   return parseFloat(moeda);

}

function buscaprod(){
	  var cod 	= document.getElementById('codbusca').value;
	  var qt 	= document.getElementById('qtbusca').value;
	  	  
	  if(cod==''){
	 	 /*alert('Digite o código do produto!');*/
		  jAlert('Digite o código do produto!', 'Erro!');
	 	 document.getElementById("codbusca").focus();
	  }else if(qt==''){
		 /*alert('Digite a quantidade!');*/
		  jAlert('Digite a quantidade!', 'Erro!');
	  }else{
		 Mostramontakit(cod,qt);
		  document.getElementById('codbusca').value = "";
	 	  document.getElementById('qtbusca').value = "";
	 	  
	  }
}

function deleteRow(i,id){
  document.getElementById('tbcomp').deleteRow(i);
  var arrId = document.getElementById('arrId').value;
	document.getElementById('arrId').value = arrId.replace(id+";","");

}

function mostarPainel(nTr){
    nTr = document.getElementById(nTr);
    if(nTr.style.display=="none"){
		nTr.style.display = "block";
	}else{
		   nTr.style.display = "none";
	}
}

function Mostraveiculos(id){
	
	xmlHttp=GetXmlHttpObject();
	
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	var url="/admin/cadastro/buscaveiculo/idmont/"+id;
	//var url="http://localhost/homologacao/admin/cadastro/buscaveiculo/idmont/"+id;
	url=url+"/sid="+Math.random();
	xmlHttp.onreadystatechange=stateChangedveiculos;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function stateChangedveiculos(){
	if (xmlHttp.readyState==4){
		document.getElementById('diveiculos').innerHTML=xmlHttp.responseText;
	}
}

function adcionaVeiculo(){
	var ano = 0;
	var mont = document.getElementById('buscamont').value;
	var veic = document.getElementById('buscaveiculo').value;
	var ano1 = document.getElementById('anoini').value;
	var ano2 = document.getElementById('anofin').value;
	
	var arrId = document.getElementById('arrIdveiculo').value;

	narrId = arrId.split(";");
	erro = 0;
	mont = mont.split("-");
	veic = veic.split("-");
	for(i=0;i < narrId.length; i++){
		if(veic[0]==narrId[i]){
			erro = 1;
		}
	}

	if(erro==1){
		/*alert('Este Veiculo já esta na lista');*/
		jAlert('Este Veiculo já esta na lista!', 'Erro!');
		return false;    
	}
	
	document.getElementById('arrIdveiculo').value += veic[0]+";";
	
	ano1p = parseInt(ano1);
	ano2p = parseInt(ano2);
	
	if((ano1 == "") && (ano2 != "")){
		ano = "< "+ano2;
	}else if((ano2 == "") && (ano1 != "")){
		ano = ano1+" > ";
	}else if((ano1 == "") && (ano2 == "")){
		ano = "Todos";
	}else if(ano1p > ano2p){
		/*alert('Ano inicial não pode ser maior que Ano final');*/
		jAlert('Ano inicial não pode ser maior que Ano final!', 'Erro!');
		return false;    
	}else if(ano1p < ano2p){
		ano = ano1+" > "+ano2;
	}else if(ano1p == ano2p){
		ano = ano1;
	}
	
	var tbtable = document.getElementById("tbveiculos");
	var numOfRows = tbtable.rows.length;
	var tbCod = tbtable.insertRow(numOfRows-1);

	var y= tbCod.insertCell(0);
	var h= tbCod.insertCell(1);
	var z= tbCod.insertCell(2);
	var b= tbCod.insertCell(3);
	
	y.align = "left";
	y.setAttribute("class","td_orc_min");
	y.innerHTML=mont[1]+"<input type='hidden' name='veiculo_"+veic[0]+"' value='"+veic[0]+"' >";
	h.align = "left";
	h.setAttribute("class","td_orc_min");
	h.innerHTML=veic[1];
	z.align = "center";
	z.setAttribute("class","td_orc_min");
	z.innerHTML=ano+"<input type='hidden' name='anoini_"+veic[0]+"' value='"+ano1+"' ><input type='hidden' name='anofin_"+veic[0]+"' value='"+ano2+"' >";
	b.align = "center";
	b.setAttribute("class","td_orc_min");
	b.innerHTML='<a href="javascript:void(0);" onclick="deleteRowveiculo(this.parentNode.parentNode.rowIndex,'+veic[0]+')"><img src="http://www.ztlbrasil.com.br/admin/images/window-close.png" width="13" heigth="13" border="0"></a>';
	
}

function deleteRowveiculo(i,id){
	  document.getElementById('tbveiculos').deleteRow(i);
	  var arrId = document.getElementById('arrIdveiculo').value;
	  document.getElementById('arrIdveiculo').value = arrId.replace(id+";","");
}

function validaformproduto(){
	if(document.cad_prod.codigo.value==""){
		/*alert("O Código do produto não pode ficar em branco!");*/
		jAlert('O Código do produto não pode ficar em branco!', 'Erro!');
		document.cad_prod.codigo.focus();
		return false; 
	}else if(document.cad_prod.buscagrupo.value==0){
		/*alert("Selecione o grupo de venda do produto!");*/
		jAlert('Selecione o grupo de venda do produto!', 'Erro!');
		return false; 
	}else if(document.cad_prod.grupocompra.value==0){
		/*alert("Selecione o grupo de compra do produto!");*/
		jAlert('Selecione o grupo de compra do produto!', 'Erro!');
		return false; 
	}else if(document.cad_prod.fornecedor.value==0){
		/*alert("Selecione o fornecedor do produto!");*/
		jAlert('Selecione o fornecedor do produto!', 'Erro!');
		return false; 
	}else if(((document.cad_prod.precoajuste.value!="")||(document.cad_prod.ajusteperc.value!="")) && (document.cad_prod.dataajuste.value=="")){
		jAlert('Preencher a data do ajuste de preço!', 'Erro!');
		return false; 
	}else{
		return true;	
	}
}

function validaformprodutochina(){
	if(document.cad_prod.precoshunkang.checked){
		/*alert("O Código do produto não pode ficar em branco!");*/
		if(document.cad_prod.fornecedorkang.value=='0'){
			jAlert('Selecione o fornecedor Shukang!', 'Erro!');
			return false;
		}else{
			valin = 1;
		}
		
	} 
	
	if(document.cad_prod.precoshuntai.checked){
		/*alert("O Código do produto não pode ficar em branco!");*/
		if(document.cad_prod.fornecedortai.value=='0'){
			jAlert('Selecione o fornecedor Shuntai!', 'Erro!');
			return false;
		}else{
			valin = 1;
		}
		
	}else{
		return true;
	}
	
	if(valin==1){
		return true;
	}
	
	return false;
}



function promocao_preco(){
	document.getElementById('preco_desc').disabled=true;
	document.getElementById('preco_desc').value='';
	document.getElementById('preco_promo').disabled=false;
	document.cad_prod.preco_promo.focus();
}

function promocao_desc(){
	document.getElementById('preco_promo').disabled=true;
	document.getElementById('preco_promo').value='';
	document.getElementById('preco_desc').disabled=false;
	document.cad_prod.preco_desc.focus();
}

function ajute_preco(){
	document.getElementById('ajusteperc').disabled=true;
	document.getElementById('ajusteperc').value='';
	document.getElementById('precoajuste').disabled=false;
	document.cad_prod.precoajuste.focus();
}

function ajuste_perc(){
	document.getElementById('precoajuste').disabled=true;
	document.getElementById('precoajuste').value='';
	document.getElementById('ajusteperc').disabled=false;
	document.cad_prod.ajusteperc.focus();
}

function adcionaHistorico(){
	
	var forn  = document.getElementById('fornecedor_hist').value;
	var moeda = document.getElementById('moedahist').value;
	var prec  = document.getElementById('preco_hist').value;
	var data  = document.getElementById('data_hist').value;
	var balls = document.getElementById('balls').value;
	
	if(forn=="0"){
		jAlert('Selecione o fornecedor!', 'Erro!');
	}else if(prec==""){
		jAlert('O preço não pode ficar em branco!', 'Erro!');
	}else if(data==""){
		jAlert('A data não pode ficar em branco!', 'Erro!');
	}else{
		document.cad_prod.action="/admin/cadastro/gravahistcompra";
		document.cad_prod.submit();
	}
}

function buscaComposicaoprod(){
	
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null){
		jAlert('Seu browser não suporta AJAX!','Erro');
		return;
	}
	
	var cod  = document.getElementById('codcomp').value;
	
	if(cod==""){
		jAlert("Digite o código do produto a ser consultado!");
		return;
	}else{
		var url="/admin/cadastro/buscacomposicao/cod/"+cod;
		url=url+"/sid="+Math.random();
		xmlHttp.onreadystatechange=stateChangedbuscacomp;
		xmlHttp.open("GET",url,true);
		xmlHttp.send(null);
	}
}

function stateChangedbuscacomp(){
	
	if (xmlHttp.readyState==4){
		
		if(xmlHttp.responseText=="erro1"){
			jAlert('Código incorreto!', 'Erro!');
			document.getElementById("codcomp").focus();			
		}else{
			document.getElementById('idcomposicao').innerHTML=xmlHttp.responseText;
		}
		
	}
}

function buscaVeiculosprod(){
	
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null){
		jAlert ("Seu browser não suporta AJAX!","Erro");
		return;
	}
	
	var cod  = document.getElementById('codveicu').value;
	
	if(cod==""){
		jAlert("Digite o código do produto a ser consultado!","Erro");
		return;
	}else{
		var url="/admin/cadastro/buscacompveic/cod/"+cod;
		url=url+"/sid="+Math.random();
		xmlHttp.onreadystatechange=stateChangedbuscaveicprod;
		xmlHttp.open("GET",url,true);
		xmlHttp.send(null);
	}
}

function stateChangedbuscaveicprod(){
	
	if (xmlHttp.readyState==4){
		
		if(xmlHttp.responseText=="erro1"){
			jAlert('Código incorreto!', 'Erro!');
			document.getElementById("codveicu").focus();			
		}else if(xmlHttp.responseText=="erro2"){
			jAlert('Produto não possue aplicação!', 'Erro!');
			document.getElementById("codveicu").focus();			
		}else{
			document.getElementById('idveiculos').innerHTML=xmlHttp.responseText;
		}
		
	}
}

function adcionaHistoricochina(){
	
	var forn  = document.getElementById('fornecedor_hist').value;
	var moeda = document.getElementById('moedahist').value;
	var prec  = document.getElementById('preco_hist').value;
	var data  = document.getElementById('data_hist').value;
	var balls = document.getElementById('balls').value;
	
	if(forn=="0"){
		jAlert('Selecione o fornecedor!', 'Erro!');
	}else if(prec==""){
		jAlert('O preço não pode ficar em branco!', 'Erro!');
	}else if(data==""){
		jAlert('A data não pode ficar em branco!', 'Erro!');
	}else{
		document.cad_prod.action="/admin/cadastro/gravahistcomprachina";
		document.cad_prod.submit();
	}
}