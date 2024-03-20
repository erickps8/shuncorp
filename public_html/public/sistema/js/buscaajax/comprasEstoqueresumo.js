var xmlHttp;
var intervalo 	= 1;

function buscaResumo(){
	$('#carregando').css({display:'block'});
	$('#tabelaHistorico').css({display:'block'});
	$('#btnBusca').css({display:'none'});
	
	xmlHttp=GetXmlHttpObject();
	
	var url="/admin/compras/buscaestoqueresumo/periodo/"+intervalo;
	xmlHttp.onreadystatechange=stateBuscaresumo;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
	
	intervalo = intervalo + 6; 
	
}

function stateBuscaresumo(){
	
	if (xmlHttp.readyState==4){
		
		var texto = xmlHttp.responseText;
		texto = texto.replace(/\+/g," ");
		texto = unescape(texto);
		texto = texto.split("|");
		
		if(texto[0] == 'erro'){
			$('#carregando').html(texto[0]);
		}else{
			
			for(i=0;i < texto.length; i++){
				dados = texto[i].split(";");
				
				$("#resumo tbody").append(
				"<tr><td style='text-align: center'>"+dados[0]+"</td>"+
				"<td style='text-align: right'>"+dados[1]+"</td>"+
				"<td style='text-align: right'>"+dados[2]+"</td>"+
				"<td style='text-align: right'>"+dados[3]+"</td></tr>");
			}
			
			
			$('#btnBusca').css({display:'block'});
		}
		
		$('#carregando').css({display:'none'});
	}
}

function buscaResumototal(){
	
	xmlHttp=GetXmlHttpObject();
	
	var url="/admin/compras/buscaestoqueresumototal";
	xmlHttp.onreadystatechange=stateBuscaresumototal;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
	
}

function stateBuscaresumototal(){
	
	if (xmlHttp.readyState==4){
		
		var texto = xmlHttp.responseText;
		texto = texto.replace(/\+/g," ");
		texto = unescape(texto);
		texto = texto.split("|");
		
		$('#valorestoque').html(texto[0]);
		$('#quantidade').html(texto[1]);
		$('#compras').html(texto[2]);
		
		//buscaResumo();
	}
}
