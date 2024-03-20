$(document).ready(function(){
	$('input[name=codigo]').focus();
	
	$('input[name=codigo]').click(function(){
		$('#resultado').html('');
	});
	
	$("input[name=codigo]").keypress(function (evt) {
		var charCode = evt.charCode || evt.keyCode;
		if (charCode  == 13) {
			$("input[name=codigo]").focus();
			return false;
		}
	});
	
	$("input[name=qt]").keypress(function (evt) {
		var charCode = evt.charCode || evt.keyCode;
		if (charCode  == 13) { return false; }
	});
	
	$("input[name=qt]").blur(function(){
		if($("input[name=codigo]").val() !="" && $("input[name=codigo]").val() !="") {
			if($("input[name=promo]").val()=="1"){
				if(confirm('Produto em promoção. Deseja utilizar o preço da promoção?')){
					$("#prepedido").submit();		
				}else{
					$("input[name=promo]").val('');
					$("#prepedido").submit();		
				}
			}else{
				$("#prepedido").submit();		
			}
			$("input[name=promo]").val('');
		}
	});
	
	$("input[name=qt]").focusin(function(){
		if($("input[name=codigo]").val()!=''){
			$.ajax({
		    	url: '/admin/venda/buscaprodutovenda/q/'+$("input[name=codigo]").val(),
		    	success: function(data) {
		    		
		    		texto = data.split("|");
		    		
		    		var arrId = $('#arrId').val();
		    		narrId = arrId.split(";");
		    		
		    		erro = 0;
		    		for(i=0;i < narrId.length; i++){
		    			if(texto[0]==narrId[i]){
		    				erro = 3;
		    			}
		    		}
		    		
		    		if(erro==3){
		    			$('#resultado').html('Este produto já está na lista!');
		    			$("input[name=codigo]").val('');
		    			$("input[name=codigo]").focus();
		    		    return false;		    
		    		}else if(texto[0]=="erro1"){
		    			$('#resultado').html('Codigo incorreto!');
		    			$("input[name=codigo]").val('');
		    			$("input[name=codigo]").focus();
		    		}else if(texto[0]=="erro4"){
		    			$('#resultado').html('Produto não disponível para venda!');
		    			$("input[name=codigo]").val('');
		    			$("input[name=codigo]").focus();
		    		}else{
		    			document.getElementById('arrId').value += texto[0]+";";
		    			
		    			if((texto[2]!='') && (texto[2]!='0')){
		    				$("#promo").val(1);
		    			}			
		    			$("#qtprod").val(texto[3]);
		    		}
		    		
		    	}
		    });
	   	}
	});
	
	$('#salvarorc').click(function(){
		$("#prepedido").attr("action","/admin/venda/pedidosentorc/gerarvenda/true");
		$("#prepedido").submit();		
	});
	
	$('#salvarorcb').click(function(){
		jAlert('Orçamento com valores abaixo do permitido. Revise os preços dos produtos!','Erro!');		
	});
	
	//-- edita qt-----------------------------
	$('.edicaoqt').blur(function(){		
		$("#prepedido").attr("action","/admin/venda/pedidosentorc/editprod/true");
		$("#prepedido").submit();
		
	});
	
	$('.qtprod').click(function(){	
		var id = $(this).attr('rel');
		$('input[name=editqt_'+id+']').show();
		$(this).hide();
	});
	
	//-- edita vl ---------------------------
	$('.edicaovl').blur(function(){		
		var id = $(this).attr('id');
		$("#prepedido").attr("action","/admin/venda/pedidosentorc/editprod/true/idprodedit/"+id);
		$("#prepedido").submit();
		
	});	
	
	$('.vlprod').click(function(){	
		var id = $(this).attr('rel');
		$('input[name=editvl_'+id+']').show();
		$(this).hide();
	});
	
	
	
	$('.remProduto').click(function(){
		var id = $(this).attr('rel');
		var ped 	= $('input[name=ped]').val();
		
		jConfirm('Você deseja remover este produto?', 'Confirme', function(r) {
			if(r==true){
				window.location='/admin/venda/removeprodorc/ped/'+ped+'/id/'+id;
			}
		});
		
	});
	
	$('.remProdpend').click(function(){
		var id = $(this).attr('rel');
		var ped 	= $('input[name=ped]').val();
		
		jConfirm('Remover pendência deste produto?', 'Confirme', function(r) {
			if(r==true){
				window.location='/admin/venda/removependorc/ped/'+ped+'/id/'+id;
			}
		});
		
	});	
		
	$('#confPendencias').click(function(){		
		jConfirm('Deseja remover todos produtos pendentes?', 'Confirme', function(r) {
			if(r==true){
				var ped 	= $('input[name=ped]').val();
				var pedcli 	= $('input[name=pedcli]').val();
				window.location='/admin/venda/removependenciasorc/ped/'+ped+'/idcli/'+pedcli;
			}
		});
		
	});	
	
	
	
});



var xmlHttp;

function MostraOpcao(str){
	if (str.length==0){
		document.getElementById("txtHint").innerHTML="";
		return;
	}
	
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	var url="/admin/venda/buscaprodutovenda/q/"+str;
	xmlHttp.onreadystatechange=stateChanged;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
	
}

function stateChanged(){
	if (xmlHttp.readyState==4){
		var texto = xmlHttp.responseText;
		texto = texto.replace(/\+/g," ");
		texto = unescape(texto);
		texto = texto.split("|");
		
		var arrId = document.getElementById('arrId').value;
		
		narrId = arrId.split(";");
		erro = 0;
		for(i=0;i < narrId.length; i++){
			if(texto[0]==narrId[i]){
				erro = 3;
			}
		}
		
		if(erro==3){
			document.getElementById('resultado').innerHTML = 'Este produto já está na lista!';
			document.getElementById('codigo').value = "";
			document.getElementById('codigo').focus();
		    return false;		    
		}else if(texto[0]=="erro1"){
			document.getElementById('resultado').innerHTML = 'Codigo incorreto!';
			document.getElementById('codigo').value = "";
			document.getElementById('codigo').focus();
		}else if(texto[0]=="erro4"){
			document.getElementById('resultado').innerHTML = 'Produto não disponível para venda!';
			document.getElementById('codigo').value = "";
			document.getElementById('codigo').focus();
		}else{
			document.getElementById('arrId').value += texto[0]+";";
			if((texto[2]!='') && (texto[2]!='0')){
				document.getElementById('promo').value = 1;
			}			
			document.getElementById('qtprod').value = texto[3];			
		}
	
	}
	
}

function buscaProdutosvenda(str,tp){
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	document.getElementById('resultadobusca').style.display='block';
	
	var url="/admin/venda/buscaprodutovendalista/busca/"+str+"/tp/"+tp;
	xmlHttp.onreadystatechange=stateChangedbusca;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
	
}

function stateChangedbusca(){
	if (xmlHttp.readyState==4){
		document.getElementById('resultadobusca').innerHTML=xmlHttp.responseText;		
	}	
}
