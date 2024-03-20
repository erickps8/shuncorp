var xmlHttp
var qtt = 0;

function MostraOpcao(str,v2){
	if (str.length==0){
		document.getElementById("txtHint").innerHTML="";
		return;
	}
	
	var per = document.getElementById("permissao").value;
	
	if ((per!=1)&&(v2<0)){
		document.getElementById("resultado").innerHTML="Erro de permissão!  />";
		return;
	}
	
	xmlHttp=GetXmlHttpObject()
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	var url="buscaproduto";
	url=url+"?q="+str+"&v2="+v2;
	url=url+"&sid="+Math.random();
	xmlHttp.onreadystatechange=stateChanged;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
	qtt = v2;
	
}

function stateChanged()
{
if (xmlHttp.readyState==4)
{

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
		document.getElementById('resultado').innerHTML = 'Este código já esta na lista!';
		document.getElementById("codigo").focus(); 
	}else if(texto[0]=="erro1"){
		document.getElementById('resultado').innerHTML = 'Codigo incorreto!';
		document.getElementById("codigo").focus();
	}else{

	document.getElementById('arrId').value += texto[0]+";";
	var tbCod = document.getElementById("txtHint").insertRow(1);
	
	var y= tbCod.insertCell(0);
	var z= tbCod.insertCell(1);
	var c= tbCod.insertCell(2);
	var e= tbCod.insertCell(3);
	
	y.align = "center";
	y.setAttribute("class","td_orc");
	y.innerHTML=texto[1];
	z.align = "center";
	z.setAttribute("class","td_orc");
	z.innerHTML=' '+qtt+'<input type="hidden" name="'+texto[0]+'" value="'+qtt+'">';
	c.align = "left";
	c.setAttribute("class","td_orc");
	c.innerHTML=texto[3];
	e.align = "center";
	e.setAttribute("class","td_orc");
	e.innerHTML='<a href="#" onclick="deleteRow(this.parentNode.parentNode.rowIndex,'+texto[0]+')"><img src="http://www.ztlbrasil.com.br/public/images/window-close.png" width="15"  border="0" title="Remover"></a>';

	document.getElementById("codigo").value='';
	document.getElementById("qt").value='';
	document.getElementById("codigo").focus();
	}
	
	
}
}


function mascara_num(obj){
    valida_num(obj)
    if (obj.value.match("-")){
      mod = "-";
    }else{
      mod = "";
    }
    valor = obj.value.replace("-","");
    valor = valor.replace(",","");
    obj.value = mod+valor;
  }
  