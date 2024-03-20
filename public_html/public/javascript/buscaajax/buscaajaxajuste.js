var xmlHttp
var on = 0;
var qtt = 0;
var vl = 0;

function MostraOpcao(qt,str,valor){
	if (str.length==0){
		document.getElementById("txtHint").innerHTML="";
		return;
	}
	xmlHttp=GetXmlHttpObject()
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	var url="buscaproduto";
	url=url+"?q="+str+"&qt="+qt+"&vl="+valor;
	url=url+"&sid="+Math.random();
	xmlHttp.onreadystatechange=stateChanged;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
	qtt = qt;
	vl = valor;
}

function stateChanged(){
	if (xmlHttp.readyState==4){
//document.getElementById("txtHint").innerHTML=xmlHttp.responseText;

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
	document.getElementById('resultado').innerHTML = 'Este código já esta na lista';
    return false;
    
}else if(texto[0]=="erro1"){
	document.getElementById('resultado').innerHTML = 'Codigo incorreto';
	document.getElementById("codigo").focus();
}else{

document.getElementById('arrId').value += texto[0]+";";
var tbCod = document.getElementById("txtHint").insertRow(2);
/*
var tbCod = document.getElementById("txtHint");
var numOfRows = tbCod.rows.length;
var newRow = tbCod.insertRow(numOfRows-1);
*/

var y= tbCod.insertCell(0);
var z= tbCod.insertCell(1);
var b= tbCod.insertCell(2);
var c= tbCod.insertCell(3);
var d= tbCod.insertCell(4);
var e= tbCod.insertCell(5);
//var f= tbCod.insertCell(6);

y.align = "center";
y.setAttribute("class","td_orc");
y.innerHTML=texto[1];
z.align = "center";
z.setAttribute("class","td_orc");
z.innerHTML=' '+qtt+'<input type="hidden" name="'+texto[0]+'" value="'+qtt+'">';
b.align = "left";
b.setAttribute("class","td_orc");
b.innerHTML=texto[3];
c.align = "right";
c.setAttribute("class","td_orc");
c.innerHTML=texto[5]+'<input type="hidden" name="valor_'+texto[0]+'" value="'+texto[2]+'">';
d.align = "right";
d.setAttribute("class","td_orc");
d.innerHTML=texto[6];
e.align = "center";
e.setAttribute("class","td_orc");
e.innerHTML='<a href="#" onclick="deleteRow(this.parentNode.parentNode.rowIndex,'+texto[0]+')"><img src="http://www.ztlrolamentos.com.br/admin/images/window-close.png" width="15" heigth="15" border="0"></a>';

document.getElementById("codigo").value='';
document.getElementById("qt").value='';
document.getElementById("valor").value='';
document.getElementById("codigo").focus();
}
//document.getElementById("txtHint").innerHTML=texto[0];

}
}
