
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
