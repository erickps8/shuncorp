$(function(){   
	
	$('.wysiwyg').wysiwyg({
		iFrameClass: "wysiwyg-input",
		controls: {
			bold          : { visible : true },
			italic        : { visible : true },
			underline     : { visible : true },
			strikeThrough : { visible : false },
			
			justifyLeft   : { visible : true },
			justifyCenter : { visible : true },
			justifyRight  : { visible : true },
			justifyFull   : { visible : true },
			
			indent  : { visible : true },
			outdent : { visible : true },
			
			subscript   : { visible : false },
			superscript : { visible : false },
			
			undo : { visible : true },
			redo : { visible : true },
			
			insertOrderedList    : { visible : true },
			insertUnorderedList  : { visible : true },
			insertHorizontalRule : { visible : false },
			
			h1: {
			visible: true,
			className: 'h1',
			command: ($.browser.msie || $.browser.safari) ? 'formatBlock' : 'heading',
			arguments: ($.browser.msie || $.browser.safari) ? '<h1>' : 'h1',
			tags: ['h1'],
			tooltip: 'Header 1'
			},
			h2: {
			visible: true,
			className: 'h2',
			command: ($.browser.msie || $.browser.safari) ? 'formatBlock' : 'heading',
			arguments: ($.browser.msie || $.browser.safari) ? '<h2>' : 'h2',
			tags: ['h2'],
			tooltip: 'Header 2'
			},
			h3: {
			visible: true,
			className: 'h3',
			command: ($.browser.msie || $.browser.safari) ? 'formatBlock' : 'heading',
			arguments: ($.browser.msie || $.browser.safari) ? '<h3>' : 'h3',
			tags: ['h3'],
			tooltip: 'Header 3'
			},
			h4: {
			visible: true,
			className: 'h4',
			command: ($.browser.msie || $.browser.safari) ? 'formatBlock' : 'heading',
			arguments: ($.browser.msie || $.browser.safari) ? '<h4>' : 'h4',
			tags: ['h4'],
			tooltip: 'Header 4'
			},
			h5: {
			visible: true,
			className: 'h5',
			command: ($.browser.msie || $.browser.safari) ? 'formatBlock' : 'heading',
			arguments: ($.browser.msie || $.browser.safari) ? '<h5>' : 'h5',
			tags: ['h5'],
			tooltip: 'Header 5'
			},
			h6: {
			visible: true,
			className: 'h6',
			command: ($.browser.msie || $.browser.safari) ? 'formatBlock' : 'heading',
			arguments: ($.browser.msie || $.browser.safari) ? '<h6>' : 'h6',
			tags: ['h6'],
			tooltip: 'Header 6'
			},
			
			cut   : { visible : true },
			copy  : { visible : true },
			paste : { visible : true },
			html  : { visible: true },
			increaseFontSize : { visible : true },
			decreaseFontSize : { visible : true },
			},
		events: {
			click: function(event) {
				if ($("#click-inform:checked").length > 0) {
					event.preventDefault();
					alert("You have clicked jWysiwyg content!");
				}
			}
		}
	});
	
    $("#btnenviar").click(function(){
    	
    	if($("input[name=assunto]").val() == ""){
			jAlert("O assunto não pode ficar em branco!","Erro!");
		}else if($("#mensagem").val() == ""){
			jAlert("O texto não pode ficar em branco!","Erro!");
		}else{
			
			$("#divform").hide();
			
			$("#loading").html('<img src="/public/sistema/imagens/loaders/loader6.gif" alt="Carregando"> <i>Aguarde, cadastrando o boletim eletrônico...</i>');
    		$("#loading").show();
    		
    		var inputs = $("#formcorreio :input");
        	
        	var string = "";
        	for (var i in inputs){
        		if(inputs[i].checked){
        			string = string+inputs[i].name+"="+inputs[i].checked+"&";
        		}
        	}
			
			$.post('/admin/correio/enviarmailing?'+string, {
				assunto			: $("input[name=assunto]").val(),
				mensagem		: $("#mensagem").val()		    	
		    },
		    function(resposta) {
				
				if(resposta == 'erro'){	
					jAlert('Erro ao gravar o boletim! Tente novamente.', 'Erro!');
					$("#divform").show();
				}else if(resposta == 'teste'){
					jAlert('Envio de teste realizado com sucesso.', 'Sucesso!');
					$("#divform").show();
				}else{
					$("#loading").html('Boletim cadastrado.<br /><img src="/public/sistema/imagens/loaders/loader6.gif" alt="Carregando"> <i>Aguarde, enviando o boletim eletrônico... </i>');
					
					$.ajax({
				    	url: '/admin/correio/disparamailing/idm/'+resposta,
				    	success: function(data) {
				    		$("#loading").html('Boletim cadastrado.<br />Boletim eletrônico enviado com sucesso!'+data);
				    	}
				    });
				}      
		    });	
		}
	});
    
    
    $("#todos").change(function(){
    	if(todos.checked){
    		$(".funcionarios").attr("checked",true);
    		$(".boletim").attr("checked",true);
    		$(".ginteresse").attr("checked",true);
    		$(".agentes").attr("checked",true);
    		$(".contatos").attr("checked",true);
    	    $(".jqTransformCheckbox").addClass('jqTransformChecked');
    	}else{
    		$(".funcionarios").attr("checked",false);
    		$(".boletim").attr("checked",false);
    		$(".ginteresse").attr("checked",false);
    		$(".agentes").attr("checked",false);
    		$(".contatos").attr("checked",false);
    		$(".jqTransformCheckbox").removeClass('jqTransformChecked');
    	}
    });
    
    $("#func").change(function(){
    	if(func.checked){
    		//$("#divfuncionarios").css("display","block");
    		$(".funcionarios").attr("checked",true);
    		$("#divfuncionarios a.jqTransformCheckbox").addClass('jqTransformChecked');
    	}else{
    		//$("#divfuncionarios").css("display","none");
    		$(".funcionarios").attr("checked",false);
    	    $("#divfuncionarios a.jqTransformCheckbox").removeClass('jqTransformChecked');
    	}
    });

    $("#contatos").change(function(){
    	if(contatos.checked){
    		//$("#divginteresse").css("display","block");
    		//$("#divagentes").css("display","block");
    		$(".ginteresse").attr("checked",true);
    		$(".agentes").attr("checked",true);
    		$("#divginteresse a.jqTransformCheckbox").addClass('jqTransformChecked');
    		$("#divagentes a.jqTransformCheckbox").addClass('jqTransformChecked');
    	}else{
    		//$("#divginteresse").css("display","none");
    		//$("#divagentes").css("display","none");
    		$(".ginteresse").attr("checked",false);
    		$(".agentes").attr("checked",false);
    	    $("#divginteresse a.jqTransformCheckbox").removeClass('jqTransformChecked');
    	    $("#divagentes a.jqTransformCheckbox").removeClass('jqTransformChecked');
    	}
    })
    
    
});



var xmlHttp;
var span = 0;

function enviaMail(){
	
	xmlHttp=GetXmlHttpObject();
	
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	var url="/admin/marketing/enviarmensagem";
	xmlHttp.onreadystatechange=estadoResposta;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function estadoResposta(){
	if (xmlHttp.readyState==4){
		document.getElementById('recHint').innerHTML=xmlHttp.responseText;
	}
}