var xmlHttp;
var mes;
var ano;
var rep;
var cli;

function buscaRepresentantes(vmes,vano){
	
	mes = vmes;
	ano = vano;
	
	if (mes.length==0){
		return;
	}
	
	xmlHttp=GetXmlHttpObject();
	
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	var url="/admin/relatorios/buscafaturamento/mes/"+mes+"/ano/"+ano;
	xmlHttp.onreadystatechange=retornoRepresentantes;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function retornoRepresentantes(){
	if (xmlHttp.readyState==4){
		document.getElementById('rep_'+mes+'_'+ano).innerHTML=xmlHttp.responseText;
	}
}

function buscaClientes(vmes, vano, vrep){
	
	rep = vrep;
	mes = vmes;
	ano = vano;
	
	if (rep.length==0){
		return;
	}
	
	xmlHttp=GetXmlHttpObject();
	
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	var url="/admin/relatorios/buscafaturamento/mes/"+mes+"/ano/"+ano+"/rep/"+rep;
	xmlHttp.onreadystatechange=retornoClientes;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function retornoClientes(){
	if (xmlHttp.readyState==4){
		document.getElementById('cli_'+mes+'_'+ano+'_'+rep).innerHTML=xmlHttp.responseText;
	}
}

function buscaVendas(vmes, vano, vrep, vcli){
	
	rep = vrep;
	mes = vmes;
	ano = vano;
	cli = vcli;
	
	if (rep.length==0){
		return;
	}
	
	xmlHttp=GetXmlHttpObject();
	
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	var url="/admin/relatorios/buscafaturamento/mes/"+mes+"/ano/"+ano+"/rep/"+rep+"/cli/"+cli;
	xmlHttp.onreadystatechange=retornoVendas;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function retornoVendas(){
	if (xmlHttp.readyState==4){
		document.getElementById('venda_'+mes+'_'+ano+'_'+rep+'_'+cli).innerHTML=xmlHttp.responseText;
	}
}