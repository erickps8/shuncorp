$(function(){
	/*$(this).text(
	    Date.parse($(this).text()).addYears(18) < Date.today() ?
	    "Under 18" :
	    " Over 18"
	);*/
		
	$('input[name=emissaopag], input[name=vencimentopag], input[name=emissaorec], input[name=vencimentorec]').change(function(){		
	   
		if($(this).val() != "" && $(this).val().length > 8){
			
			var fields = $(this).parents('form:eq(0),body').find('button,input,textarea,select');
			var index = fields.index( this );
			
			var hoje = new Date();
			var parts = $(this).val().split('/');
			var data = new Date(parts[2], parts[1]-1, parts[0]);
			
			data.setDate(data.getDate()+180);
			if(data < hoje){
				jAlert('Data anterior à 6 meses!', 'Atenção!', function(){
					
			        if ( index > -1 && ( index + 1 ) < fields.length ) {
			            fields.eq( index + 1 ).focus();
			        }
			        
				});
			}
			
			data = new Date(parts[2], parts[1]-1, parts[0]);
			
			hoje.setDate(hoje.getDate()+180);
			if(data > hoje){
				jAlert('Data maior que 6 meses!', 'Atenção!', function(){
					if ( index > -1 && ( index + 1 ) < fields.length ) {
			            fields.eq( index + 1 ).focus();
			        }
				});
			};
		}				
	});
	
			
});

function buscaContasconcilia(tipo,id,conta,tpant,bancobusca){
	$.ajax({
	  url: "/admin/administracao/buscacontasfinztl/tipo/"+tipo+"/idc/"+id+"/conta/"+conta+"/tpant/"+tpant+"/bancobusca/"+bancobusca,
	  success: function(data) {
		  jModal(data, "Selecione as contas", 500, 300);
	  }
	});
}

function buscaCreditos(cliente){
	$.ajax({
	  url: "/admin/administracao/buscacreditos/cliente/"+cliente,
	  success: function(data) {
		  jModal(data, "Créditos", 400, 300);
	  }
	});
	
}


var xmlHttp;


function adicionarCreditos(){
		
		document.getElementById("tpurch").style.display="block";
				
		form = document.formcredito;
		var cont=0;
		for (var i=0;i<form.length;i++){
			if(form[i].checked==true){
				
				var idv   = document.getElementById("idpurch");
				var tbCod = document.getElementById("tpurch").insertRow(cont);
				
				var y= tbCod.insertCell(0);
				var z= tbCod.insertCell(1);
				var e= tbCod.insertCell(2);
				
				var creditos = form[i].value.split("|");
				idv.value = idv.value+creditos[1]+",";
								
				y.align = "center";
				y.setAttribute("style","width:100px;");
				y.innerHTML='<a href="/admin/administracao/financeiroztlcreditos/cliente/'+creditos[4]+'" target="_blank" >'+creditos[0]+'</a>';
				z.align = "right";
				z.setAttribute("style","width:140px;");
				z.innerHTML= creditos[2];
				e.align = "center";
				e.setAttribute("style","width:10px;");
				e.innerHTML='<a href="#" onclick="deleteRow(this.parentNode.parentNode.rowIndex,'+creditos[1]+')"><img src="http://www.ztlbrasil.com.br/public/sistema/imagens/icons/middlenav/close.png" width="12" border="0"></a>';
				
				cont++;            						
			}        
			    					      						
		}
		
		$.alerts._hide();
}

function deleteRow(i,id){
    document.getElementById('tpurch').deleteRow(i);

    var arrId = document.getElementById("idpurch").value;
    document.getElementById('idpurch').value = arrId.replace(id+",","");

}


function somaContasconcilia(id){
	var total 	= $("#totalselecionando").val();
	var vlconc 	= parseFloat($("#valorconta").val());
	if($("#"+id).is(":checked")==true){
		total = parseFloat(total) + parseFloat($('#valor_'+id).val());
		$("#totalselecionando").val(total);

		$("#totalsel").html(float2moeda(total));	
		
	}else{
		total = parseFloat(total) - parseFloat($('#valor_'+id).val());
		$("#totalselecionando").val(total);

		$("#totalsel").html(float2moeda(total));
	}
	
	vlconc += 0.1;
	
	if(vlconc >= total){
		$("#btnsalvar").attr("disabled", "");
		$("#btnsalvar").css({ opacity: 1 });
	}else{
		$("#btnsalvar").attr("disabled", "disabled");
		$("#btnsalvar").css({ opacity: 0.3 });
	}
}

function buscarExtrato(){
	$('#resultado').html('<div style="border: 1px solid #d5d5d5; padding: 20px"><img src="/public/sistema/imagens/loaders/loader6.gif" alt="Carregando"> <i>Aguarde, buscando extrato...</i></div>');
	
	if($("select[name=contasval] option:selected").val() == 0){
		jAlert('Selecione uma conta bancária!', 'Erro!');
	}else{
		
		$('#resultado').css({display:'block'});
		
	    $.post('/admin/administracao/buscaconciliacaoztl', {
	    	buscavliniconc     	: $("input[name=buscavliniconc]").val(),
	    	buscavlfimconc  	: $("input[name=buscavlfimconc]").val(),
	    	contasval 			: $("select[name=contasval] option:selected").val(),
	    	dtiniconc			: $("input[name=dtiniconc]").val(),
	    	dtfimconc			: $("input[name=dtfimconc]").val()
	    },
	    function(resposta) {
			
			texto = resposta.replace(/^\s+|\s+$/g,"");
			texto = unescape(texto);
			
			if(texto == "erro"){	
				jAlert('Erro ao buscar o extrato! Tente novamente.', 'Erro!');
			}else{
				$('#resultado').html(resposta);
	        }
	               
	    });	
	}
}

function exportaExtrato(){
		
	if($("select[name=contasval] option:selected").val() == 0){
		jAlert('Selecione uma conta bancária!', 'Erro!');
	}else{
		window.location='/admin/administracao/financeiroztlconcexp'+
	    	'/buscavliniconc/'+$("input[name=buscavliniconc]").val()+
	    	'/buscavlfimconc/'+$("input[name=buscavlfimconc]").val()+
	    	'/contasval/'+$("select[name=contasval] option:selected").val()+
	    	'/dtiniconc/'+$("input[name=dtiniconc]").val().replace("/","-").replace("/","-")+
	    	'/dtfimconc/'+$("input[name=dtfimconc]").val().replace("/","-").replace("/","-");
	}
}

function conciliaContas(){
	$("#divsalvar").html("<i>Aguarde, salvando conciliação...</i>");
	var data = $('#concilia').serialize();
	
	console.log( data);
	
	gerarConciliacao(data);
}

function gerarConciliacao(data){
	
    $.post('/admin/administracao/regconciliaztl?'+data,
    function(resposta) {
		
		texto = resposta.replace(/^\s+|\s+$/g,"");
		texto = unescape(texto);
		
		if(texto == "erro"){	
			jAlert('Erro ao conciliar conta! Tente novamente.', 'Erro!');
		}else{
			$.alerts._hide();
			buscarExtrato();
        }
               
    });
}

function gravaTransacao(){
	
	if($("input[name=dataconc]").val() == ""){
		jAlert('A data não pode ficar em branco!', 'Erro!');
	}else if($("input[name=valorconc]").val() == ""){
		jAlert('O valor não pode ficar em branco!', 'Erro!');
	}else{
		
		$('#resultado').css({display:'block'});
		
	    $.post('/admin/administracao/cadconciliacaoztl', {
	    	valorconc     		: $("input[name=valorconc]").val(),
	    	idconci  			: $("input[name=idconci]").val(),
	    	idcontaconcilha  	: $("select[name=contasval] option:selected").val(),
	    	dataconc			: $("input[name=dataconc]").val()
	    },
	    function(resposta) {
			
			texto = resposta.replace(/^\s+|\s+$/g,"");
			texto = unescape(texto);
			
			if(texto == "erro"){	
				jAlert('Erro ao cadastrar nova transação! Tente novamente.', 'Erro!');
			}else{
				jAlert('Transação inserida com sucesso', 'Sucesso!');
				
				$("input[name=valorconc]").val("");
		    	$("input[name=idconci]").val("");
		    	$("input[name=dataconc]").val("");
				
				buscarExtrato();
	        }
	               
	    });	
	}
}


function novaConctrans(){
	document.getElementById('novaconc').style.display='block';
	document.getElementById('novaconcc').style.display='block';
}

function confirmConcilia(idconc){
	jConfirm('Você deseja validar a conciliação?', 'Confirme', function(r) {
		if(r==true){
			//window.location='/admin/administracao/conciliarcontztl/conc/'+idconc;
			$.ajax({
			  url: '/admin/administracao/conciliarcontztl/conc/'+idconc,
			  success: function(data) {
				  jAlert('Conciliação validada com sucesso!','Sucesso!');
				  buscarExtrato();
			  }
			});
		}
	});
}

function removeConcilia(idconc){
	jConfirm('Você deseja remover a conciliação?', 'Confirme', function(r) {
		if(r==true){
			$.ajax({
			  url: '/admin/administracao/remconciliacaoztl/conc/'+idconc,
			  success: function(data) {
				  jAlert('Lançamento removido com sucesso!','Sucesso!');
				  buscarExtrato();
			  }
			});
		}
	});
}