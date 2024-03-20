$(document).ready(function(){
	
	$('#fornecedor').change(function(){
		idcliente = $(this).val();
		
		if(idcliente == 0){
			alert('Selecione um cliente');
		}else if(idcliente == 'outro'){
			window.location = '/admin/nfe/nfenova/for/outro';
		}else{
			window.location = '/admin/nfe/nfenova/for/'+idcliente;
		}
	});
		

	$('#salvarNfe').click(function(){
		
		var texto = "";
		
		if($('input[name=email]').val()==""){
			texto = "- O Email da NFe não pode ficar em branco;<br />";
		}else if($('input[name=transportadora]').val()==""){
			texto = "- Cadastre uma trasnportadora para o cliente;<br />";
		}else if($('input[name=empresa]').val()==""){
			texto = "- O campo EMPRESA não pode ficar em branco";
		}else if($('input[name=cnpj]').val()==""){
			texto = "- O campo CNPJ não pode ficar em branco";
		}else if($('input[name=inscricao]').val()==""){
			texto = "- O campo INSCRIÇÃO não pode ficar em branco";
		}else if($('input[name=ddd]').val()==""){
			texto = "- O campo TELEFONE não pode ficar em branco";
		}else if($('input[name=telefone]').val()==""){
			texto = "- O campo TELEFONE não pode ficar em branco";
		}else if($('input[name=ufcob]').val()==""){
			texto = "- O campo ESTADO não pode ficar em branco";
		}else if($('input[name=cidade2]').val()==""){
			texto = "- O campo CIDADE não pode ficar em branco";
		}else if($('input[name=logradouro]').val()==""){
			texto = "- O campo LOGRADOURO não pode ficar em branco";
		}else if($('input[name=cep]').val()==""){
			texto = "- O campo CEP não pode ficar em branco";
		}else if($('input[name=transnome]').val()==""){
			texto = "- O campo NOME da transportadora não pode ficar em branco";
		}else if($('input[name=transcnpj]').val()==""){
			texto = "- O campo CNPJ da transportadora não pode ficar em branco";
		}else if($('input[name=transinscricao]').val()==""){
			texto = "- O campo INSCRIÇÃO da transportadora não pode ficar em branco";
		}else if($('input[name=translogradouro]').val()==""){
			texto = "- O campo LOGRADOURO da transportadora não pode ficar em branco";
		}else if($('input[name=transcidade]').val()==""){
			texto = "- O campo CIDADE da transportadora NFe não pode ficar em branco";
		}else if($('input[name=email]').val()==""){
			texto = "- O campo EMAIL não pode ficar em branco";
		}else{
			email = $('input[name=email]').val();
			er = /^[a-zA-Z0-9][a-zA-Z0-9\._-]+@([a-zA-Z0-9\._-]+\.)[a-zA-Z-0-9]{2}/;
			if(er.exec(email)){
				
			}else{
				texto += "- O formato do email da NFe está incorreto";
			}
		}
		
		if(texto==""){
			$("#prepedido").attr("action","/admin/nfe/gravardadosnfe");
			$('#prepedido').submit();
		}else{
			jAlert(texto,'Erro!');
		};
		
	});
	
	
	
});


function gravaDados(){
	$("#prepedido").attr("action","/admin/nfe/gravardadosnfe");
	$('#prepedido').submit();	
}



function buscaProduto(str){
	
	$.ajax({
		  url: "/admin/venda/buscaprodutocompleto/q/"+str,
		  success: function(data) {
			  
			var texto = data.replace(/\+/g," ");
			texto = unescape(texto);
			texto = texto.split("|");
					
			if(texto[0]!='erro1'){
				$('#codigo').val(texto[1]);
				$('#preco').val(texto[2]);
				$('#unidade').val(texto[7]);
				$('#ncm').val(texto[6]);
				$('#codean').val(texto[5]);
				$('#descricao').val(texto[4]);
			}
		  }
	});
	
}
