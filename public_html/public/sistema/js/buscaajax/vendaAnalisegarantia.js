$(function(){   
	
	$('#procede').change(function(){
		var op = $("#procede option:selected").val();
		$.ajax({
	    	url: '/admin/venda/buscaanalisegarantia/buscatipo/'+op,
	    	success: function(data) {
	    		$('#recHint').html(data);
	    	}
	    });
	});
	
	function buscaProduto(str){
		
		$.ajax({
	    	url: '/admin/venda/buscaproduto/q/'+str,
	    	success: function(data) {
	    		var texto = data;
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
	    	}
	    });
		
	}
	
	$('#abreCancela').click(function(){
		$('#cancelamento').toggle();
	});
	
	$('#confirmaCancelamento').click(function(){
		document.novogar.action = "/admin/venda/cancelagarantia";
		document.novogar.submit();
	});
	
});
