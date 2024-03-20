var xmlHttp;

function Buscacodref(cod,emp){
	if (cod.length==0){
		alert('Código inválido!');
		return;
	}
	
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	var url="/admin/cadastro/buscarefcruzada";
	url=url+"/buscacod/"+cod+"/fabrica/"+emp;
	url=url+"/sid/"+Math.random();
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
				b.innerHTML='<a href="javascript:void(0);" onclick="deleteRowcodigo(this.parentNode.parentNode.rowIndex,\''+codigo+':'+mont+'\')"><img src="http://www.ztlbrasil.com.br/admin/images/window-close.png" width="13" heigth="13" border="0"></a>';
				
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
		alertaPadrao('Erro!', 'Selecione uma fábrica!', 'erro', 110, 250);
	}else if(codigo==""){
		alertaPadrao('Erro!', 'Digite um código!', 'erro', 110, 250);
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
			alertaPadrao('Erro!', 'Este Código já esta na lista!', 'erro', 110, 300);
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
			tipo = " P ";
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
		b.innerHTML='<a href="javascript:void(0);" onclick="deleteRowcodigo(this.parentNode.parentNode.rowIndex,\''+codigo+':'+mont[0]+'\')"><img src="/public/images/window-close.png" width="13" heigth="13" border="0"></a>';
		
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
		alertaPadrao('Erro!', 'Não pode ser salvo, por conter referências incorretas!', 'erro', 110, 400);
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
