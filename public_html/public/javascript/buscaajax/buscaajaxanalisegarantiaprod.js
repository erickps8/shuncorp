var xmlHttp

function MostraOpcaoprod(str){
	if (str.length==0)	{
		document.getElementById("txtHint").innerHTML="";
		return;
     }
	
	xmlHttp=GetXmlHttpObject()
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	var url="/admin/venda/buscaproduto";
	//var url="http://localhost/homologacao/admin/venda/buscaproduto";
	url=url+"?q="+str;
	url=url+"&sid="+Math.random();
	xmlHttp.onreadystatechange=stateChangedprod;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function stateChangedprod(){
	if (xmlHttp.readyState==4){
		
		var texto = xmlHttp.responseText;
		texto = texto.replace(/\+/g," ");
		texto = unescape(texto);
		texto = texto.split("|");
				
		if(texto[0]=="erro1"){
			document.getElementById('resultado').innerHTML = 'Codigo incorreto';
			document.getElementById("codigo").focus();
		}else{
			
			var cont = document.getElementById("cont").value;
			document.getElementById("cont").value=parseInt(cont)+1;
			var tbCod = document.getElementById("txtHint").insertRow(cont);
			
			var y= tbCod.insertCell(0);
			var z= tbCod.insertCell(1);
			var b= tbCod.insertCell(2);
			var c= tbCod.insertCell(3);
			var d= tbCod.insertCell(4);
			
			var strin = "000"+cont;
			y.align = "center";
			y.setAttribute("class","td_orc");
			y.innerHTML=strin.substr(-3)+'<input  type="hidden" name="'+cont+'" id="'+cont+'" value="'+cont+'"  >';
			z.align = "center";
			z.setAttribute("class","td_orc");
			z.innerHTML=texto[1]+'<input type="hidden" name="'+texto[1]+'" value="'+texto[1]+'">';
			b.align = "left";
			b.setAttribute("class","td_orc");
			b.innerHTML="- Produto não consta na nota fiscal";
			c.align = "center";
			c.setAttribute("class","td_orc");
			c.innerHTML="NAO";
			d.align = "center";
			d.setAttribute("class","td_orc");
			d.innerHTML='<a href="#" onclick="deleteRow(this.parentNode.parentNode.rowIndex)"><img src="http://www.ztlbrasil.com.br/public/images/window-close.png" width="15" heigth="15" border="0"></a>';
	
			document.getElementById("codigo").value='';
			document.getElementById("codigo").focus();
			document.getElementById("produtos").value=document.getElementById("produtos").value+texto[1]+":"+cont+";";
		}
//document.getElementById("txtHint").innerHTML=texto[0];

	}
}
