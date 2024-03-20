$(document).ready(function(){
	
	$('#gerarVenda').click(function(){
		
		if($('input[name=vervalida]').val() == 1){
			jConfirm('Já verificou se existe preço combinado?<br />Não será possivel edição após a venda gerada.', 'Confirme', function(r) {
				if(r==true){
					document.getElementById('divnfe').style.display = "block";
					$("#resultadoprogresso").css({color:'#000'}).html("Salvando dados da venda...");
					gravarDadosnfevenda();
				}
			});
		};
	});
	
});

function removeProduto(codigo, idprod, ped, cli, qt, valor){
	
	jConfirm('Você deseja remover o '+codigo+'?', 'Confirme', function(r) {
		if(r==true){
			window.location='/admin/venda/pedidosremprod/ped/'+ped+'/idprod/'+idprod+'/pedcli/'+cli+'/qt/'+qt+'/valor/'+valor;
		}
	});
}

function validaDados(){
	var texto 		= "";
	var pesobruto 	= 0;
	var vlprazo1 	= 0;
	var vlprazo2 	= 0;
	var vlprazo3 	= 0;
	var vlprazo4 	= 0;
	var vlprazo5 	= 0;
	var vltotalnota = document.prepedido.vltotalnota.value;
	
	if(document.prepedido.pesobruto.value!=""){
		var pesobruto 	= parseFloat(document.prepedido.pesobruto.value.replace(",", "."));
	}
	
	var pesoliquido = parseFloat(document.prepedido.pesoliquido.value.replace(",", "."));	
		
	if((document.prepedido.qtpacote.value=="")||(document.prepedido.qtpacote.value==0)){
		texto = "- Quantidade de pacotes deve ser maior que zero;<br />";
	}

	if($("#trans option:selected").val()==0){
		texto = "- Selecione uma transportadora;<br />";
	}		

	if(pesobruto <= pesoliquido){	
		texto = texto + "- Peso bruto tem que ser maior que peso líquido;<br />";			
	}

	if(document.prepedido.vlprazo1.value!=""){			
		vlprazo1 	= document.prepedido.vlprazo1.value.replace(".", "");	
		vlprazo1 	= parseFloat(vlprazo1.replace(",", "."));					
	}

	if(document.prepedido.vlprazo2.value!=""){			
		vlprazo2 	= document.prepedido.vlprazo2.value.replace(".", "");	
		vlprazo2 	= parseFloat(vlprazo2.replace(",", "."));				
	}

	if(document.prepedido.vlprazo3.value!=""){			
		vlprazo3 	= document.prepedido.vlprazo3.value.replace(".", "");	
		vlprazo3 	= parseFloat(vlprazo3.replace(",", "."));			
	}

	if(document.prepedido.vlprazo4.value!=""){	
		vlprazo4 	= document.prepedido.vlprazo4.value.replace(".", "");	
		vlprazo4 	= parseFloat(vlprazo4.replace(",", "."));			
	}

	if(document.prepedido.vlprazo5.value!=""){			
		vlprazo5 	= document.prepedido.vlprazo5.value.replace(".", "");	
		vlprazo5 	= parseFloat(vlprazo5.replace(",", "."));				
	}

	if((vlprazo1+vlprazo2+vlprazo3+vlprazo4+vlprazo5).toFixed(2) != vltotalnota){
		texto = texto + "- Valor da soma das parcelas diferente do valor total;<br />";				
	}

	if(texto==""){
		document.prepedido.gerarVenda.disabled = false;
	}else{
		document.prepedido.gerarVenda.disabled = true;
	}
	document.getElementById('textovalida').innerHTML = texto;	

}

function buscarModalfinanceiro(ped,cli){
	
    $.ajax({
    	url: '/admin/venda/buscafinatrazado/ped/'+ped+'/cli/'+cli,
    	success: function(data) {
    		jModal(data, "Financeiro", 300, 300);
    	}
    });
}

function buscaAdministrador(ped){
	
	var cpf 	= document.buscaadmin.cpf.value;
	var senha 	= document.buscaadmin.senha.value;
	
	if (cpf.length==0){
		$('#resultadoadmin').html("Digite o seu CPF!");
		return;
	}
	
	$.post('/admin/venda/buscaadmin', {
		cpf			: cpf,
		senha     	: senha,
		ped			: ped		    	
    },
    function(resposta) {
    	
    	$('#resultadoadmin').html(resposta);
		
		if(resposta=='Sucesso'){
			$("#popup_ok").trigger('click');
		} 
               
    });	
	
}

function buscaprod(cod){
 	  if(cod!=''){
     	  MostraOpcao(cod);
 	  }else{
		//alert("Digite o código!");
		//document.getElementById('codigo').focus();
 	  }
}

function enviaProduto(){
	if((document.prepedido.codigo.value!="") && (document.prepedido.qt.value!="")) {
		var qtprod = parseInt(document.getElementById('qtprod').value);
		if(qtprod<document.prepedido.qt.value){
			alert("Produto com estoque inferior a solicitada! Estoque atual: "+qtprod);
			document.prepedido.qt.focus();	
		}else{
			if(document.prepedido.promo.value=="1"){
				if(confirm('Produto em promoção. Deseja utilizar o preço da promoção?')){
					document.prepedido.submit();
				}else{
					document.prepedido.promo.value="";
					document.prepedido.submit();
				}
			}else{
				document.prepedido.submit();
			}
		
		}
		document.prepedido.promo.value="";
	}
}

function gravaDados(tp){
	document.prepedido.action = "/admin/venda/pedidosentdados";
	document.prepedido.parcela.value = tp;
	document.prepedido.submit();	
}

function editaProduto(id){
	document.prepedido.action = "/admin/venda/pedidosent/editprod/true/idprodedit/"+id;  
	document.prepedido.submit();
}

function editaProdutoqt(){
	document.prepedido.action = "/admin/venda/pedidosent/editprod/true";  
	document.prepedido.submit();
}