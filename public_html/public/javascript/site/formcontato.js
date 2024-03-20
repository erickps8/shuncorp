function validarCampos(){
	telefone = document.contato.fone;
	nome = document.contato.nome;
	email_contato = document.contato.email;
    mensagem = document.contato.mensagem;
	
	if(nome.value == ""){
		alert("O campo Nome e obrigatorio!");
		nome.focus();	
		return false;
	}else if(telefone.value == ""){
		alert("O campo Telefone e obrigatorio!");
		telefone.focus();
		return false;
	}else if(mensagem.value == ""){
		alert("O campo Mensagem e obrigatorio!");
		mensagem.focus();
		return false;
	}else if(email_contato.value == ""){
		alert("O campo Email e obrigatório!");
		email_contato.focus();	
		return false;
	}else{
    	document.contato.submit();
    }
	
}