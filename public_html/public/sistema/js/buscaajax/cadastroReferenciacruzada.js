var xmlHttp;

function Buscacodref(cod,emp){
	if (cod.length==0){
		jAlert('Código inválido!','Erro!');
		return;
	}
	
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	var url="/admin/cadastro/buscarefcruzada/buscacod/"+cod+"/fabrica/"+emp;
	xmlHttp.onreadystatechange=stateChangedbuscacodref;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);	
}

function stateChangedbuscacodref(){
	
	if (xmlHttp.readyState==4){
		var arrId 	= document.getElementById('arrcodigos').value;
		var arrrem  = document.getElementById('arrcodigosrem').value;
		
		var texto 	= xmlHttp.responseText;
		texto 		= texto.replace(/\+/g," ");
		texto 		= unescape(texto);
		textocomp 	= texto.split(";");

		
		for(i=0;i < textocomp.length; i++){
			codemp = textocomp[i].split(":");
			codigo 	= codemp[0];
			mont	= codemp[1];
			fabr	= codemp[2];
			
			if((codemp[0]!="erro")&&(codemp[0]!="")){
				document.getElementById('arrcodigos').value += codigo+":"+mont+";";
				document.getElementById('arrcodigosnovos').value += codigo+":"+mont+";";
				document.getElementById('arrcodigosrem').value = arrrem.replace(codigo.toUpperCase()+":"+mont+";","");
				
				var tbtable = document.getElementById("tbcodigos");
				var numOfRows = tbtable.rows.length;
				var tbCod = tbtable.insertRow(numOfRows);
			
				var z= tbCod.insertCell(0);
				var y= tbCod.insertCell(1);
				var h= tbCod.insertCell(2);
				var b= tbCod.insertCell(3);
				
				z.align = "center";
				z.setAttribute("class","td_orc_min");
				z.innerHTML="";	
				y.align = "left";
				y.setAttribute("class","td_orc_min");
				y.innerHTML="<font color='#FF0000'>"+fabr+"</font>";
				h.align = "left";
				h.setAttribute("class","td_orc_min");
				h.innerHTML="<font color='#FF0000'>"+codigo.toUpperCase()+"</font>";
				b.align = "center";
				b.setAttribute("class","td_orc_min");
				b.innerHTML='<a href="javascript:void(0);" onclick="deleteRowcodigo(this.parentNode.parentNode.rowIndex,\''+codigo+':'+mont+'\')"><img src="http://www.ztlbrasil.com.br/public/sistema/imagens/icons/middlenav/close.png" width="15"></a>';
				
			}
		}		
	}
}

function adcionaCodigos(){
	
	var mont 	= document.getElementById('fabricainc').value;
	var codigo 	= document.getElementById('codigoinc').value.toUpperCase();
	var arrId 	= document.getElementById('arrcodigos').value;
	var arrrem  = document.getElementById('arrcodigosrem').value;
	var prin	= document.getElementById('principal').checked;
	var vis    = document.getElementById('visualizar').checked;
	var tipo	= "";
	
	if(mont==0){
		jAlert('Selecione uma fábrica!', 'Erro!');
	}else if(codigo==""){
		jAlert('Digite um código!', 'Erro!');
	}else{		
		narrId = arrId.split(";");
		mont = mont.split("-");
		
		erro = 0;
		
		for(i=0;i < narrId.length; i++){
			codid = narrId[i].split(":");
			if((codid[0]==codigo) && (codid[1]==mont[0])){
				erro = 1;
			}		
		}		
				
		if(erro==1){
			/*alert('Este Veiculo já esta na lista');*/
			jAlert('Este Código já esta na lista!', 'Erro!');
			return false;    
		}
		
		document.getElementById('arrcodigos').value += codigo+":"+mont[0]+":"+prin+":"+vis+";";
		document.getElementById('arrcodigosnovos').value += codigo+":"+mont[0]+":"+prin+":"+vis+";";
		document.getElementById('arrcodigosrem').value = arrrem.replace(codigo+":"+mont[0]+";","");
		
		var tbtable = document.getElementById("tbcodigos");
		var numOfRows = tbtable.rows.length;
		var tbCod = tbtable.insertRow(numOfRows);
	
		var z= tbCod.insertCell(0);
		var y= tbCod.insertCell(1);
		var h= tbCod.insertCell(2);
		var b= tbCod.insertCell(3);
		
		if(prin == true){
			tipo = " C ";
		}
		
		if(vis == true){
			tipo = tipo+" V ";
		}
		
		z.setAttribute("class","td_orc_min");
		z.setAttribute("style","text-align: center");
		z.innerHTML=tipo;		
		y.align = "left";
		y.setAttribute("class","td_orc_min");
		y.innerHTML=mont[1];
		h.align = "left";
		h.setAttribute("class","td_orc_min");
		h.innerHTML=codigo.toUpperCase();
		b.setAttribute("style","text-align: center");
		b.setAttribute("class","td_orc_min");
		b.innerHTML='<a href="javascript:void(0);" onclick="deleteRowcodigo(this.parentNode.parentNode.rowIndex,\''+codigo+':'+mont[0]+'\')"><img src="http://www.ztlbrasil.com.br/public/sistema/imagens/icons/middlenav/close.png" width="15"></a>';
		
		Buscacodref(codigo,mont[0]);
	}
}

function deleteRowcodigo(i,id){
	  document.getElementById('tbcodigos').deleteRow(i);
	  var arrId = document.getElementById('arrcodigos').value;
	  document.getElementById('arrcodigos').value = arrId.replace(id+";","");
	  document.getElementById('arrcodigosrem').value += id+";";
}

function verificaSubmit(){
	var arrId 	= document.getElementById('arrcodigos').value;
	var arrCod 	= document.getElementById('codigonovo').value;
	erro=0;
	codnovo = arrCod.split(":");
	
	if(codnovo[1]==1){
		narrId = arrId.split(";");
		for(i=1;i < narrId.length; i++){
			codid = narrId[i].split(":");
			if(codid[1]==1){
				erro = 1;
			}		
		}
	}
	
	if(erro==1){
		jAlert('Erro!','Não pode ser salvo, por conter referências incorretas!');
		return false;
	}else{
		return true;	
	}
	
	/*narrId = arrId.split(";");
	codid = narrId[i].split(":");
	
	for(i=0;i < narrId.length; i++){
		codid = narrId[i].split(":");
		if((codid[0]==codigo) && (codid[1]==mont[0])){
			erro = 1;
		}		
	}*/
	
	return false;
}
