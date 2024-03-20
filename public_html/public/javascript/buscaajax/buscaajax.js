var xmlHttp
var on = 0;
var qtt = 0;
var forn;

function MostraOpcao(qt,str,fornecedor)
{
if (str.length==0)
{
document.getElementById("txtHint").innerHTML="";
return;
}
xmlHttp=GetXmlHttpObject()
if (xmlHttp==null)
{
alert ("Seu browser não suporta AJAX!");
return;
}
var url="buscaproduto";
url=url+"?q="+str+"&fornecedor="+fornecedor+"&qt="+qt;
url=url+"&sid="+Math.random();
xmlHttp.onreadystatechange=stateChanged;
xmlHttp.open("GET",url,true);
xmlHttp.send(null);
qtt = qt;
forn = fornecedor;
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
	document.getElementById('resultado').innerHTML = 'This code is already registered';
    return false;
    
}else if(texto[0]=="erro1"){
	document.getElementById('resultado').innerHTML = 'Incorrect code';
	document.getElementById("codigo").focus();
}else if(texto[4]!=forn){
	document.getElementById('resultado').innerHTML = 'This product is not from this supplier';
	document.getElementById("codigo").focus();

}else{

document.getElementById('arrId').value += texto[0]+";";
var tbCod = document.getElementById("txtHint").insertRow(1);

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
c.innerHTML=texto[5];
d.align = "right";
d.setAttribute("class","td_orc");
d.innerHTML=(texto[6]);
e.align = "center";
e.setAttribute("class","td_orc");
e.innerHTML='<a href="#" onclick="deleteRow(this.parentNode.parentNode.rowIndex,'+texto[0]+')"><img src="/admin/images/window-close.png" width="15" heigth="15" border="0"></a>';

document.getElementById("codigo").value='';
document.getElementById("qt").value='';
document.getElementById("codigo").focus();
}

}
}

function GetXmlHttpObject(){
	var xmlHttp=null;
	try	{
	// Firefox, Opera 8.0+, Safari
	xmlHttp=new XMLHttpRequest();
	}catch (e){
	// Internet Explorer
		try	{
		xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
		}catch (e)	{
			xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
	}
	return xmlHttp;
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
