$(function(){   
    $(".mostrarPainel").click(function(){
    	$("#tnovo").toggle("slow");
    });
     
    
    $("#btnlegenda").click(function(){
    	$("#divlegenda").toggle("slow");
    });
    
    atualizaPainel();
	    
    //-- busca lista atividades --------------------
    $("#btnBusca").click(function(){
    	
    	$('#resultado').html('<img src="/public/sistema/imagens/loaders/loader6.gif"> <i>Aguarde...</i>');
    	
    	$.post('/admin/administracao/buscapainelatividadeslista', { 
    		dtini: 		$('input[name=dtini]').val(), 
    		dtfim: 		$('input[name=dtfim]').val(),
    		tppesq:		$('input[name=tppesq]:checked').val(),
    		buscasit:	$("select[name=buscasit] option:selected").val()
    		},
		    function(resposta) {
		    	if(resposta == 'erro'){
		    		jAlert('Erro ao buscar os atividades! Tente novamente.','Erro!');
		    	}else{
		    		$('#resultado').html(resposta);
		    	}
		    }
    	);	
    });
        
    $(".btnAtividade").live('click', function(){
    	var id = $(this).attr('rel');
    	buscaAtividade(id);    	
    });
    
});


function modalformAtividade(previsao){
    $.ajax({
    	url: '/admin/administracao/montatividades/previsao/'+previsao,
    	success: function(data) {
    		jModal(data, "Cadastro de atividade", 600, 100);
    		$('#calendar').fullCalendar('unselect');
    	}
    });
}

function buscaAtividade(id){
    $.ajax({
    	url: '/admin/administracao/buscaatividades/id/'+id,
    	success: function(data) {
    		jModal(data, "Atividade", 600, 500);
    		atualizaPainel();
    	}
    });
}

function atualizaPainel(){
	
	$('#calendar').html("");
	
	//===== Calendar =====//

	var date = new Date();
	var d = date.getDate();
	var m = date.getMonth();
	var y = date.getFullYear();
	
	var calendario = $('#calendar').fullCalendar({
		header: {
			left: 'prev,next',
			center: 'title'
		},
		monthNames:["Janeiro","Fevereiro","Março","Abril","Maio","Junho","Julio","Agosto","Setembro","Outrubro","Novembro","Dezembro"],
		monthNamesShort:["Jan","Fev","Mar","Abr","Mai","Jun","Jul","Ago","Set","Out","Nov","Dez"],
		dayNames:["Domingo","Segunda","Terça","Quarta","Quinta","Sexta","Sabado"],
		dayNamesShort:["Dom","Seg","Ter","Qua","Qui","Sex","Sab"],
		buttonText:{today:"hoje",month:"mês",week:"semana",day:"dia"},
		titleFormat:{month:"MMMM yyyy",week:"d MMM[ yyyy]{ '&#8212;'d [ MMM] yyyy}",day:"dddd, d MMM yyyy"},
		selectable: true,
		selectHelper: true,
		select: function(start, end, allDay) {
			
			mes = start.getMonth()+1;
			
			previsao =  start.getFullYear()+'-'+mes+'-'+start.getDate();
			modalformAtividade(previsao);
		},
		editable: false,
		eventClick: function(event) {
			buscaAtividade(event.id);
			return false;
		}
	});
	
	$.ajax({
    	url: '/admin/administracao/buscapainelatividades',
    	success: function(data) {
    		var dados = data.split("|");
    		
    		for (var i in dados){
    			var ativ = dados[i].split(";");
    			var adat = ativ[0].split("-");
    			mes = adat[1] - 1;
    			var dat = new Date(adat[0],mes,adat[2]);
    			var inicio = dat;
    			
    			if(ativ[4]!=""){
    				var adat = ativ[4].split("-");
        			mes = adat[1] - 1;
        			inicio = new Date(adat[0],mes,adat[2]);
    			}
    			
    			var cor = "";
    			
    			if(ativ[2] == 0){
    				cor = '#FF803E';
    			}else if(ativ[2] == 1){
    				cor = '#00D0FF';
    			}else if(ativ[2] == 2){
    				cor = '#00A4C9';
    			}else if(ativ[2] == 3){
    				cor = '#007A96';
    			}else if(ativ[2] == 4){
    				cor = '#45E800';
    			}else if(ativ[2] == 5){
    				cor = '#32A800';
    			}else if(ativ[2] == 6){
    				cor = '#007F1B';
    			}
    			
    			/* #099B2D - SA
    			 * #14BA3D - ST
    			 * #B5D31F - SN
    			 * #008282 - SF
    			 * 
    			 * #0D8EBA - UA
    			 * #11A6D8 - UT
    			 * #0A6F91 - UF
    			 * */
    			
    			
    			$('#calendar').fullCalendar('renderEvent',
					{
						id: ativ[3],
    					title: ativ[1],
						start: inicio,
						end: dat,
						color: cor
					},
					true 
				);    			
    		}
    		qtAtividade();
    	}	    	
    });		
}


function qtAtividade(){
    $.ajax({
    	url: '/admin/administracao/qtatividade',
    	success: function(data) {
    		var dados = data.split("|");
    		$('#qtativpend').html(dados[1]);
    		$('#qtsolipend').html(dados[0]);
    	}
    });
}
