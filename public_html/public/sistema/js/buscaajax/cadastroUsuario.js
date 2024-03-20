$(function(){
	
	$('#btnSalvar').click(function(){
		
		if($("input[name=nome]").val() == ""){
			jAlert('O nome não pode ficar em branco', 'Erro!');
		}else if($("select[name=empresa] option:selected").val() == "0"){
			jAlert('Selecione a empresa!', 'Erro!');
		}else if($("select[name=perfil] option:selected").val() == "0"){
			jAlert('Selecione um perfil!', 'Erro!');
		}else{
			var data = $('#form').serialize();
			
			$.post('/admin/cadastro/gravausuario?'+data,
		    function(resposta) {
				
				if(resposta == "erro2"){	
					jAlert('Este email já está em uso! Utilize outro email.', 'Erro!');
				}else if(resposta == "erro"){	
					jAlert('Usuário não cadastrado! Tente novamente.', 'Erro!');
				}else{
					jAlert('Usuário cadastrado com sucesso!', 'Sucesso!', function(){
						window.location="/admin/cadastro/usuarioscad/usuario/"+resposta;
					});
		        }		               
		    });						
		}
	});
	
	$("#btnBuscadesempenho").click(function() {	
		$('#resultadoDesempenho').html('<div style="padding: 10px; border: 1px solid #d5d5d5;"><img src="/public/sistema/imagens/loaders/loader6.gif"> <i>Aguarde...</i></div>');
		$('#resultadoDesempenho').show();
		
	    $.post('/admin/cadastro/buscadesempenho', {
	    	dtini     	: $("input[name=dtini]").val(),
	    	dtfim  		: $("input[name=dtfim]").val(),
	    	idusuario	: $("input[name=idusuario]").val()	    	
	    },
	    function(resposta) {
			
			if(resposta == "erro"){	
				jAlert('Erro ao gerar o relatório! Tente novamente.', 'Erro!');
			}else{
				$('#resultadoDesempenho').html(resposta);
	        }
	               
	    });	
	});
	
	//-- busca contatos ------------------------------------------------
		
	$("#abaDesempenho").click(function(){
		$('#resultadoContatos').html('<div style="padding: 10px; border: 1px solid #d5d5d5;"><img src="/public/sistema/imagens/loaders/loader6.gif"> <i>Aguarde...</i></div>');
		$('#resultadoContatos').show();
		
	    $.post('/admin/cadastro/buscacontatosuser', {
	    	idusuario	: $("input[name=idusuario]").val()	    	
	    },
	    function(resposta) {
			
			if(resposta == "erro"){	
				jAlert('Erro ao gerar o relatório! Tente novamente.', 'Erro!');
			}else{
				$('#resultadoContatos').html(resposta);
	        }
	               
	    });	
	    
	    $("#btnBuscadesempenho").trigger('click');
	    
	});
		
	//-- Frequencia ---------------------------------------------------------------------------------
	$("#btnFaltas").click(function(){
		
		if(($("#dataini").val() == "") || ($("#datafim").val() == "")){
			jAlert("Favor digitar a data inicial e a data final","Erro!");
		}else{
		
			$dt1 = $("#dataini").val().replace("/","-").replace("/","-");
			$dt2 = $("#datafim").val().replace("/","-").replace("/","-");
			
		
		    $.post('/admin/cadastro/gravarfaltas', {
		    	idusuario	: $("input[name=idusuario]").val(),
		    	dataini		: $dt1,
				horaini		: $("#horaini").val(),
				datafim		: $dt2,
				horafim		: $("#horafim").val(),
				justificado : $("#justificado option:selected").val(),
				obsjust		: $("#obsjust").val(),
				tpfreq		: $("input[name='tpfreq']:checked").val()				
		    },
		    function(resposta) {
				
				if(resposta == "erro"){	
					jAlert("Erro ao gravar as faltas!","Erro!");
				}else{
					$('#divfaltas').show(); 
					buscaGenerica('/admin/cadastro/buscaausencias/tp/0/div/divfaltas/usermd/'+$("#idusuariomd5").val(), 'divfaltas');
		        }
		               
		    });	    
		}
	    
	});
	
	
});

var xmlHttp;
var div;
function gravaFerias(tp){
	
	div = iddiv;
	
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null){
		jAlert ("Seu browser não suporta AJAX!","Erro!");
		return;
	}
		
	if(tp == '0'){
		if(($("#dataini").val() == "") || ($("#datafim").val() == "")){
			jAlert("Favor digitar a data inicial e a data final","Erro!");
		}else{
		
			$dt1 = $("#dataini").val().replace("/","-").replace("/","-");
			$dt2 = $("#datafim").val().replace("/","-").replace("/","-");
			
			var url="/admin/cadastro/gravarfaltas"+
			"/idusuario/"+$("#idusuario").val()+
			"/dataini/"+$dt1+
			"/horaini/"+$("#horaini").val()+
			"/datafim/"+$dt2+
			"/horafim/"+$("#horafim").val()+
			"/justificado/"+$("#justificado option:selected").val()+
			"/obsjust/"+$("#obsjust").val();
			
			xmlHttp.onreadystatechange=resgravaFaltas;
			xmlHttp.open("GET",url,true);
			xmlHttp.send(null);
		}
		
	}else{
	
		if(($("#datainiferias").val() == "") || ($("#datafimferias").val() == "")){
			jAlert("Favor digitar a data inicial e a data final","Erro!");
		}else{
		
			$dt1 = $("#datainiferias").val().replace("/","-").replace("/","-");
			$dt2 = $("#datafimferias").val().replace("/","-").replace("/","-");
			
			var url="/admin/cadastro/gravarferias/idusuario/"+$("#idusuario").val()+"/datainiferias/"+$dt1+"/datafimferias/"+$dt2;
			xmlHttp.onreadystatechange=resgravaFerias;
			xmlHttp.open("GET",url,true);
			xmlHttp.send(null);
		}
	}
}

function resgravaFerias(){	
	if (xmlHttp.readyState==4){
		
		if(xmlHttp.responseText == '0'){
			jAlert("Erro ao gravar as férias!","Erro!");
			alert(xmlHttp.responseText);
		}else{
			$('#divferias').css({display:'block'}); 
			buscaGenerica('/admin/cadastro/buscaausencias/tp/1/div/divferias/usermd/'+$("#idusuariomd5").val(), 'divferias');
		}
	}
}


function exibeRegioes(nivel){
	var nivel = nivel.split('|');
	if(nivel[1]==1){
		document.getElementById('regregional').style.display='block';

		if((nivel[0] == 4)||(nivel[0] == 5)){
			document.getElementById('representante').style.display='block';
			document.getElementById('televendas').style.display='block';													
		}else if(nivel[0] != 31){
			document.getElementById('representante').style.display='block';
			document.getElementById('televendas').style.display='none';
		}else{
			document.getElementById('televendas').style.display='block';
			document.getElementById('representante').style.display='none';
		}
	}else{
		document.getElementById('regregional').style.display='none';
	}									
}

function confirmRemoveanexo(idanexo){
	jConfirm('Você deseja remover este anexo?', 'Confirme', function(r) {
		if(r==true){
			window.location='/admin/cadastro/usuariosremanexo/anexo/'+idanexo;
		}
	});
}

function removeCadastro(id, tipo, div, user){

	tp 		= tipo;
	iddiv 	= div;
	jConfirm('Tem certeza que deseja remover esta falta?', 'Confirme', function(r) {
		if(r==true){
			
			$.ajax({
			  url: '/admin/cadastro/removefalta/id/'+id,
			  success: function(data) {
				  $.ajax({
					  url: '/admin/cadastro/buscaausencias/tp/'+tp+'/div/'+iddiv+'/usermd/'+user,
					  success: function(data) {
						  $("#"+iddiv).html(data);
					  }
				  });
			  }
			});
		}
	});
}
