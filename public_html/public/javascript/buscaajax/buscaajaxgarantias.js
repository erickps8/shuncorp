var xmlHttp
var on 	= 0;
var qtt = 0;
var vl 	= 0;
var imp = 0;
var icm = 0;
var tc = "";

function MostraOpcao(str,qt,valor){
	if (str.length==0){
		document.getElementById("txtHint").innerHTML="";
		return;
	}
	xmlHttp=GetXmlHttpObject()	
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	var url="/admin/venda/buscaproduto";
	//var url="http://www.ztlbrasil.com.br/admin/venda/buscaproduto";
	
	url=url+"?q="+str+"&qt="+qt+"&vl="+valor;
	url=url+"&sid="+Math.random();
	xmlHttp.onreadystatechange=stateChanged;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
	qtt = qt;
	vl 	= valor;
}

function stateChanged()
{
if (xmlHttp.readyState==4)
{
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


var e= tbCod.insertCell(3);

var g= tbCod.insertCell(4);

y.align = "center";
y.setAttribute("class","td_orc");
y.innerHTML=texto[1];
z.align = "center";
z.setAttribute("class","td_orc");
z.innerHTML=' '+qtt+'<input type="hidden" name="'+texto[0]+'" value="'+qtt+'">';
b.align = "left";
b.setAttribute("class","td_orc");
b.innerHTML=texto[4]+'<input type="hidden" name="valor_'+texto[0]+'" value="'+texto[2]+'">';
e.align = "left";
e.setAttribute("class","td_orc");
e.innerHTML=texto[3];
g.align = "center";
g.setAttribute("class","td_orc");
g.innerHTML='<a href="#" onclick="deleteRow(this.parentNode.parentNode.rowIndex,'+texto[0]+')"><img src="/public/sistema/imagens/icons/middlenav/close.png" width="15"  border="0"></a>';

document.getElementById("codigo").value='';
document.getElementById("qt").value='';
document.getElementById("valor").value='';
document.getElementById("codigo").focus();
}
//document.getElementById("txtHint").innerHTML=texto[0];

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
