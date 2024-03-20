
$(function(){   
	
	$('input[name=empresa]').keydown(function(e) {
		if (e.keyCode == '13') {
			buscarRegistros(1);
			return false;
		}
	});
	
	//-- Contatos --------------------------------------------------
	$('.btnContatos').live('click', function() {
		var emp = $(this).attr('rel');
		
		if( $('#contatos_'+emp).is(':visible') ) {
			$('#contatos_'+emp).hide();
		}else{
			$('#contatos_'+emp).show();
			$('#contatos_'+emp).html('<td colspan="5"><img src="/public/sistema/imagens/loaders/loader6.gif" alt="Carregando"> <i>Buscando contatos...</i></td>');
			
			$.post('/admin/cadastro/buscarcontatos', { idemp: emp },
		    function(resposta) {
		    	if(resposta == 'erro'){
		    		jAlert('Erro ao buscar os contatos! Tente novamente.','Erro!');
		    	}else{
		    		$('#contatos_'+emp).html(resposta);
		    	}
		    });	
		}
	});
	
	//-- Ppaginacao ------------------------------------------------
	$('.btnPaginator').live('click', function() {
		var rel = $(this).attr('rel');
		if(rel=='ant'){
			rel = parseInt($('#pag').val())-1;
		}else if(rel=='pos'){
			rel = parseInt($('#pag').val())+1;
		}else{
			rel = $(this).text();
		}
		buscarRegistros(rel);
	});
	
	//-- grava dados do contatos -----------------------------------
	$('#btnbusca').click(function(){
		buscarRegistros(1);
	});
	
	function buscarRegistros(pag){	
		
		$('#resultado').html('<div style="border: 1px solid #d5d5d5; padding: 20px"><img src="/public/sistema/imagens/loaders/loader6.gif" alt="Carregando"> <i>Aguarde, buscando contatos...</i></div>');
		
		var url;
		var tipopesq = $("input[name='tipopesq']:checked").val();
		
		if(tipopesq == "0"){
			url = '/admin/cadastro/buscarcontatosemp';
		}else{
			url = '/admin/cadastro/buscarcontatos/paginacao/1';
		}
		
		
	    $.post(url, {
	    	tipopesq: 		$("input[name='tipopesq']:checked").val(),
	    	parceiros: 		$("input[name='parceiros']:checked").val(),
	    	alvos: 			$("input[name='alvos']:checked").val(),
	    	quarentena: 	$("input[name='quarentena']:checked").val(),
	    	mercado: 		$("input[name='mercado']:checked").val(),
	    	empresa:		$("input[name=empresa]").val(),
	    	ginteresse:		$("select[name=ginteresse] option:selected").val(),
	    	uf:				$("select[name=uf] option:selected").val(),
	    	buscaregioes:	$("select[name=buscaregioes] option:selected").val(),
	    	televenda:		$("select[name=televenda] option:selected").val(),
	    	page:			pag	    	
	    },
	    function(resposta) {
	    	if(resposta == 'erro'){
	    		jAlert('Erro ao buscar os contatos!','Erro!');
	    		$('#resultado').html("");
	    	}else{
	    		$('#resultado').html(resposta);
	    	}
	    });	
	}
	
	$('.btnFiliais').live('click', function() {
		var emp = $(this).attr('rel');
		
		if( $('#filiais_'+emp).is(':visible') ) {
			$('#filiais_'+emp).hide();
		}else{
			$('#filiais_'+emp).show();
			$('#filiais_'+emp).html('<td colspan="5"><img src="/public/sistema/imagens/loaders/loader6.gif" alt="Carregando"> <i>Buscando filiais...</i></td>');
			
			$.post('/admin/cadastro/buscarcontatosfil', { idmatriz: emp },
		    function(resposta) {
		    	if(resposta == 'erro'){
		    		jAlert('Erro ao buscar os filias! Tente novamente.','Erro!');
		    	}else{
		    		$('#filiais_'+emp).html(resposta);
		    	}
		    });	
		}
	});
		
	$('#gravaempresa').find(':input').attr('disabled', 'disabled');
	$('input[type=button]').removeAttr('disabled');
	$('input[type=submit]').removeAttr('disabled');
	$('input[name=idempresa]').removeAttr('disabled');
	$('input[name=editparc]').removeAttr('disabled');
	$("select[name=clientes]").removeAttr('disabled');
	
	if($('#editparc').val() == 1){
		$('select[name=ginteresse]').removeAttr('disabled');
		$('input[name=dtabertura]').removeAttr('disabled');
		$('textarea').removeAttr('disabled');
		$('input[name=meta]').removeAttr('disabled');
		
	}else if($('#editar').val() == 1){
		$('#gravaempresa').find(':input').removeAttr('disabled');
	}
	
	if($('input[name=nivel]').val() == 1){
		$('select[name=regiao]').attr('disabled', 'disabled');
	}
	
	$('.btnDesgregar').live('click', function() {
		jConfirm('Deseja remover o vínuculo deste contato com o parceiro? Esta ação não poderá ser desfeita.', 'Confirme', function(r) {
			if(r==true){
				window.location='';
			}
		});
	});	
	
	$('#btnAgregar').live('click', function() {
		jModal($('#buscaEmpresa').html(), "Agregar ao parceiro", 300, 100);
		$('#buscaEmpresa').html("");
	});
	
	$('#btnSagregar').live('click', function() {
		window.location='/admin/cadastro/agregaparceiro/idempresa/'+$('input[name=idempresa]').val()+'/novaempresa/'+$('#clientes option:selected').val();		
	});
	
	$('#btnFiliar').live('click', function() {
		
		$.post('/admin/cadastro/buscamatrizcontatos',
	    function(resposta) {
	    	if(resposta == 'erro'){
	    		jAlert('Erro ao buscar as empresas! Tente novamente.','Erro!');
	    	}else{
	    		jModal(resposta, "Filiar a matriz", 300, 100);
	    	}
	    });	
	});
		
	$('#btnSfilial').live('click', function() {
		window.location='/admin/cadastro/defineempresa/idempresa/'+$('input[name=idempresa]').val()+'/idmatriz/'+$('#matrizes option:selected').val();		
	});
	
	$('#btnDesfiliar').live('click', function() {
		jConfirm('Deseja editar esta empresa como Matriz?', 'Confirme', function(r) {
			if(r==true){
				window.location='/admin/cadastro/defineempresa/idempresa/'+$('input[name=idempresa]').val();
			}
		});				
	});
		
	$('#btnSalvar').live('click', function() {
		if($("input[name=empresa]").val() == ""){ 
			jAlert('O nome da empresa é obrigatorio!', 'Erro!'); 
		}else{
			$("#gravaempresa").submit();
		}
	});
		
	$('.btnNovofilial').live('click', function() {
		window.location='/admin/cadastro/contatosempcad/idmatriz/'+$(this).attr('rel');		
	});
	
	//--- busca contato ----------------------------------------------------------------
	$('.btnNovocontato').live('click', function() {
	
		$.post('/admin/cadastro/buscacontatos', {
	    	empresa: 	$(this).attr('rel')},
	    	
	    	function(resposta) {
				if(resposta == "erro"){	
					jAlert('Erro ao buscar dados! Tente novamente.', 'Erro!');
				}else{
					jModal(resposta, "Contatos", 300, 100);
		        }
	    });	
		
	});

	//--- busca contato ----------------------------------------------------------------
	$('.btnContato').live('click', function() {	
		$.post('/admin/cadastro/buscacontatos', {
			contato: 	$(this).attr('rel')},
	    	
	    	function(resposta) {
				if(resposta == "erro"){	
					jAlert('Erro ao buscar dados! Tente novamente.', 'Erro!');
				}else{
					jModal(resposta, "Contatos", 300, 100);
		        }
			}
		);			
	});

	/*--------- grava dados do contatos ------------------*/
	$('#btnSalvarcontato').live('click', function(){	
		
		var emp = $("input[name=idempresa]").val();
		
	    $.post('/admin/cadastro/gravacontatos', {
	    	idcontato: 	$("input[name=idcontato]").val(),
	    	idempresa: 	$("input[name=idempresa]").val(),
	    	nome: 		$("input[name=nome]").val(),
	    	dtnasc: 	$("input[name=dtnasc]").val(), 
	    	email: 		$("input[name=email]").val(), 
	    	skype: 		$("input[name=skype]").val(),
	    	ddi1: 		$("input[name=ddi1]").val(),
	    	ddd1: 		$("input[name=ddd1]").val(),
	    	numero1: 	$("input[name=numero1]").val(),
	    	ddi2: 		$("input[name=ddi2]").val(),
	    	ddd2: 		$("input[name=ddd2]").val(),
	    	numero2: 	$("input[name=numero2]").val(),
	    	nextel: 	$("input[name=nextel]").val(),
	    	rua: 		$("input[name=rua]").val(),
	    	bairro: 	$("input[name=bairro]").val(),
	    	cep: 		$("input[name=cep]").val(),
	    	grupo: 		$("select[name=grupo] option:selected").val(),
	    	uf: 		$("select[name=uf] option:selected").val(), 
	    	cidade: 	$("select[name=cidade] option:selected").val(),
	    	endempresa: $("input[name='endempresa']").val(),
	    	obs: 		$("#obs").val()},
	    	
	    function(resposta) {
			texto = resposta.replace(/^\s+|\s+$/g,"");
			texto = unescape(texto);
			
			if(texto == "erro"){	
				jAlert('Contato não foi cadastrado! Tente novamente.', 'Erro!');
			}else{
				jAlert('Contato cadastrado com sucesso!', 'Sucesso!', function(){
					
					if($("input[name='tipopesq']:checked").val() == 1){
						buscarRegistros($('#pag').val());
					}else{
						$('#contatos_'+emp).html('<td colspan="5"><img src="/public/sistema/imagens/loaders/loader6.gif" alt="Carregando"> <i>Buscando contatos...</i></td>');
						
						$.post('/admin/cadastro/buscarcontatos', { idemp: emp },
					    function(resposta) {
					    	if(resposta == 'erro'){
					    		jAlert('Erro ao buscar os contatos! Tente novamente.','Erro!');
					    	}else{
					    		$('#contatos_'+emp).html(resposta);
					    	}
					    });
					}
				});				
	        }
	    });	
	});
	
	//-- valida contatos/empresas ----------------------------------------------------------
	$('#btnValida').live('click', function() {
		var contato = $(this).attr('rel').split("|");
			
		$('#btnValida').html('<img src="/public/sistema/imagens/loaders/loader6.gif" alt="Validando">');
		
		$.post('/admin/cadastro/contatosvalida', { contato: contato[0], tipo: contato[1]},
	    	function(resposta) {
				if(resposta == "erro"){	
					$('#btnValida').html('Erro ao validar!');
				}else{
					$('#btnValida').html('Validado');
					if(contato[1]==3){
						buscarRegistros($('#pag').val());
					}
				}
		});		
	});
	
	
	//--- exclui contato ----------------------------------------------------------------
	$('.btnContatoexcluir').live('click', function() {
		
		var contatoArray = $(this).attr('rel').split("|");
		
		jConfirm('Deseja remover o contato ID '+contatoArray[0]+'?', 'Confirme', function(r) {
			if(r==true){
		
				var emp = contatoArray[1];
				
				$.post('/admin/cadastro/removecontato', {
					contato: 	contatoArray[0]},
			    	
			    	function(resposta) {
						if(resposta == "erro"){	
							jAlert('Erro ao remover contato! Tente novamente.', 'Erro!');
						}else{
							jAlert('Contato removido com sucesso!.','Sucesso!', function(){
								
								if(emp="0"){
									buscarRegistros($('#pag').val());
								}else{
									
									$('#contatos_'+emp).html('<td colspan="5"><img src="/public/sistema/imagens/loaders/loader6.gif" alt="Carregando"> <i>Buscando contatos...</i></td>');
									
									$.post('/admin/cadastro/buscarcontatos', { idemp: emp },
								    function(resposta) {
								    	if(resposta == 'erro'){
								    		jAlert('Erro ao buscar os contatos! Tente novamente.','Erro!');
								    	}else{
								    		$('#contatos_'+emp).html(resposta);
								    	}
								    });
								}
							});
				        }
					}
				);	
			}
		});
	});
	
	//--- exclui matriz ----------------------------------------------------------------
	$('.btnExcluimatriz').live('click', function() {
		
		var emp = $(this).attr('rel');
		
		jConfirm('Deseja remover a empresa ID '+emp+'?', 'Confirme', function(r) {
			if(r==true){
				$.post('/admin/cadastro/removematriz', {
					empresa: emp},			    	
			    	function(resposta) {
						if(resposta == "erro"){	
							jAlert('Erro ao remover empresa! Tente novamente.', 'Erro!');
						}else{
							jAlert('Empresa removidaa com sucesso!.','Sucesso!', function(){
								buscarRegistros(1);								
							});
				        }
					}
				);	
			}
		});
	});
	
	//--- exclui filial ----------------------------------------------------------------
	$('.btnExcluifilial').live('click', function() {
		
		var filialArray = $(this).attr('rel').split("|");
		
		jConfirm('Deseja remover o contato ID '+filialArray[0]+'?', 'Confirme', function(r) {
			if(r==true){
		
				var emp = filialArray[1];
				
				$.post('/admin/cadastro/removeempfilial', {
					empresa: filialArray[0]},
			    	
			    	function(resposta) {
						if(resposta == "erro"){	
							jAlert('Erro ao remover filial! Tente novamente.', 'Erro!');
						}else{
							jAlert('Filial removida com sucesso!.','Sucesso!', function(){
								
								$('#filiais_'+emp).html('<td colspan="5"><img src="/public/sistema/imagens/loaders/loader6.gif" alt="Carregando"> <i>Buscando filiais...</i></td>');
								
								$.post('/admin/cadastro/buscarcontatosfil', { idmatriz: emp },
							    function(resposta) {
							    	if(resposta == 'erro'){
							    		jAlert('Erro ao buscar os filias! Tente novamente.','Erro!');
							    	}else{
							    		$('#filiais_'+emp).html(resposta);
							    	}
							    });
								
							});
				        }
					}
				);	
			}
		});
	});
	
	//-- busca compras da empresa -----------------------------------------------------
	
	$('#compras').click(function(){
		$('#btnCompras').trigger( "click" );
	});
	
	$('#btnCompras').live('click', function(){
		
		$('#placeholder').html('<img src="/public/sistema/imagens/loaders/loader6.gif" alt="Carregando"> <i>Buscando compras...</i>');
		
		$.post('/admin/relatorios/buscacontatoscompras', { 
			cliente: 	$('input[name=idcliente]').val(),
			idempresa: 	$('input[name=idempresa]').val(),
			vendasfil: 	$("input[name='vendasfiliais']:checked").val(),
			dataini:   	$('input[name=dataini]').val(),
			datafim:   	$('input[name=datafim]').val()
			},
	    	function(resposta) {
				
				if(resposta == "erro data"){
					jAlert('Data final maior que data inicial! Tente novamente.', 'Erro!');
				}else if(resposta == "erro"){	
					jAlert('Erro ao buscar dados! Tente novamente.', 'Erro!');
				}else{
					var sin = [];
					var tick = [];
					var sin2 = [];
					
					var arrayRes = resposta.split("|");
					
					for ( var i = 0 ; i < arrayRes.length ; i++ ) {
					   
						var subArray = arrayRes[i].split('-');
						
						sin.push([i, subArray[1]]);
						tick.push([i, subArray[0]]);
						sin2.push([i, subArray[2]]);
					}
					
					var plot = $.plot("#placeholder", [
						{ data: sin, label: "Vendas" },
						{ data: sin2, label: "Com pendências"}
					], {
						series: {
							lines: { show: true },
							points: { show: true }
						},
						grid: {
							hoverable: true
						},						
						xaxis:
						{
							ticks: tick 
						},
						
					});
				
					$("<div id='tooltip'></div>").css({
						position: "absolute",
						display: "none",
						border: "1px solid #fdd",
						padding: "2px",
						"background-color": "#fee",
						opacity: 0.80
					}).appendTo("body");
				
					$("#placeholder").bind("plothover", function (event, pos, item) {
						if (item) {
							var x = item.datapoint[0].toFixed(2),
								y = item.datapoint[1].toFixed(2);
			
							$("#tooltip").html(y)
								.css({top: item.pageY+5, left: item.pageX+5})
								.fadeIn(200);
						} else {
							$("#tooltip").hide();
						}
						
					});
								
					$('#divprodutos').html('<img src="/public/sistema/imagens/loaders/loader6.gif" alt="Carregando"> <i>Buscando produtos...</i>');
					$('#divprodutos').show();
					
					//-- produtos vendidos ------------------------------------------------------------------
					$.post('/admin/venda/buscaprodutosvendidos', { 
						idempresa: 	$('input[name=idempresa]').val(),
						cliente: 	$('input[name=idcliente]').val(),
						vendasfil: 	$("input[name='vendasfiliais']:checked").val(),
						dataini:   	$('input[name=dataini]').val(),
						datafim:   	$('input[name=datafim]').val()
						},
				    	function(resposta) {
							if(resposta == "erro"){	
								jAlert('Erro ao buscar dados! Tente novamente.', 'Erro!');
							}else{
								$('#divprodutos').html(resposta);
							}
						}
					);
					
					$('#divrentabilidade').html('<img src="/public/sistema/imagens/loaders/loader6.gif" alt="Carregando"> <i>Buscando rentabilidade...</i>');
					$('#divrentabilidade').show();
					
					//-- rentabilidade -----------------------------------------------------------------------
					$.post('/admin/relatorios/buscavendascustocontatosemp', { 
						idempresa	: $('input[name=idempresa]').val(),
						cliente		: $('input[name=idcliente]').val(),
						vendasfil	: $("input[name='vendasfiliais']:checked").val(),
						dtini		: $('input[name=dataini]').val(),
				    	dtfim		: $('input[name=datafim]').val()
						},
				    	function(resposta) {
							if(resposta == "erro"){	
								jAlert('Erro ao buscar dados! Tente novamente.', 'Erro!');
							}else{
								$('#divrentabilidade').html(resposta);
							}
						}
					);
					
				}				
			}
			
		);	
	});
	
	//-- busca pendencias da empresa -----------------------------------------------------	
	$('#pendencias').click(function(){
		$('#btnPendencias').trigger( "click" );
	});
	
	$('#btnPendencias').live('click', function(){
		
		$('#resultadopend').html('<img src="/public/sistema/imagens/loaders/loader6.gif" alt="Carregando"> <i>Buscando pendências...</i>');
		
		$.post('/admin/venda/buscapendencias', { 
			cliente: 	$('input[name=idcliente]').val(),
			pendfil: 	$("input[name='pendenciasfiliais']:checked").val(),	
			idempresa: 	$('input[name=idempresa]').val()
			},
	    	function(resposta) {				
				if(resposta == "erro data"){
					jAlert('Data final maior que data inicial! Tente novamente.', 'Erro!');
				}else if(resposta == "erro"){	
					jAlert('Erro ao buscar dados! Tente novamente.', 'Erro!');
				}else{
					$('#resultadopend').html(resposta);
				}
			}
		);
	});
	
	
	$('#btnCancpend').live('click', function(){
		
		var data = $('form#pendendencias').serialize();
				
		$.ajax({
			url:'/admin/venda/baixapendempresa?'+data,
		    success: function(retorno){
		    	if(retorno == 'erro'){
		    		jAlert('Erro ao baixar pendência! Tente novamente.', 'Erro!');
		    	}else{
		    				    		
		    		$.post('/admin/venda/buscapendencias', { 
		    			cliente: 	$('input[name=idcliente]').val(),
		    			pendfil: 	$("input[name='pendenciasfiliais']:checked").val(),		
		    			idempresa: 	$('input[name=idempresa]').val()
		    			},
		    	    	function(resposta) {				
		    				if(resposta == "erro data"){
		    					jAlert('Data final maior que data inicial! Tente novamente.', 'Erro!');
		    				}else if(resposta == "erro"){	
		    					jAlert('Erro ao buscar dados! Tente novamente.', 'Erro!');
		    				}else{
		    					$('#resultadopend').html(resposta);
		    				}
		    			}
		    		);
		    	}
		    }		 		

		});
	});
	
	//-- interacoes --------------------------------------------------------------
	$('#interacao').click(function(){
		
		var emp = $('input[name=idempresa]').val();
		
		if(emp != ""){
			tp =  'int';
		}else{
			tp =  'intcompleto';
		}
		
		$.post('/admin/cadastro/buscacontatosemp', { 
			empresa:	$('input[name=idempresa]').val(),
			tp:			tp
			},
	    	function(resposta) {
				if(resposta == "erro"){	
					jAlert('Erro ao buscar dados! Tente novamente.', 'Erro!');
				}else{
					$('#divinteracao').html(resposta);
				}
			}
		);
	});
	
	$('#buscaInteracao').click(function(){
		buscaInteracoes();
	});
	
	//-- comentarios --------------------------------------------------------------
	$('.interacao').live('click', function(){
				
		var int = $(this).attr('id');
		
		if( $('#inter_'+int).is(':visible') ) {
			$('#inter_'+int).hide();
		}else{
			$('#inter_'+int).show();
				
			$.post('/admin/cadastro/buscacontatosemp', { 
				tp:			'com',
				interacao:  int
				},
		    	function(resposta) {
					if(resposta == "erro"){	
						jAlert('Erro ao buscar dados! Tente novamente.', 'Erro!');
					}else{
						$('#inter_'+int).html(resposta);
					}
				}
			);
		}
	});
	
	
	$('#btnInteracao').click(function(){
		$.post('/admin/cadastro/gravarinteracao', { 
			empresa: $("input[name=idempresa]").val(), 
			idcliente: $("input[name=idcliente]").val(), 
			textointeracao: $("#textonovainteracao").val(),
			dataalarme: $("input[name=dataalarme]").val()}, 
			function(resposta) {
				if(resposta == "erro"){	
					jAlert('Erro ao gravar interação! Tente novamente.', 'Erro!');
				}else{
					jAlert('Interação cadastrada com sucesso!', 'Sucesso!', function(){
						$('#interacao').trigger( "click" );
						$("#textonovainteracao").val("");
						$("input[name=dataalarme]").val("");
					});
				}				
			}
		);	
	});
	
	
	$('.btncomentar').live('click', function(){
		$('#divcomentar_'+$(this).attr('id')).toggle('slow');
	});
	
	$('.btnGcoment').live('click', function(){
		var inter = $(this).attr('id');
		
		$.post('/admin/cadastro/gravarcomentario', { 
			interacao: inter, 
			comentario: $("#novocomentario_"+inter).val()}, 
			function(resposta) {
				$('#inter_'+inter).show();
				
				$.post('/admin/cadastro/buscacontatosemp', { 
					tp:			'com',
					interacao:  inter
					},
			    	function(resposta) {
						if(resposta == "erro"){	
							jAlert('Erro ao buscar dados! Tente novamente.', 'Erro!');
						}else{
							$('#inter_'+inter).html(resposta);
						}
					}
				);
			}
		);
	});
	
	$('#movimentacao').click(function(){
		
		$.post('/admin/cadastro/buscacontatosemp', { 
			tp: 		'mov', 
			cliente: 	$("input[name=idcliente]").val()}, 
			function(resposta) {				
				if(resposta == "erro"){	
					jAlert('Erro ao buscar dados! Tente novamente.', 'Erro!');
				}else{
					$('#divmoviment').html(resposta);
				}
			}
		);
	});
		
	$('#financeiro').click(function(){
		
		$.post('/admin/cadastro/buscacontatosemp', { 
			tp: 		'fin', 
			cliente: 	$("input[name=idcliente]").val()}, 
			function(resposta) {				
				if(resposta == "erro"){	
					jAlert('Erro ao buscar dados! Tente novamente.', 'Erro!');
				}else{
					$('#divfinanceiro').html(resposta);
				}
			}
		);
	});
	
	$('#linhaprodutos').click(function(){
		
		$.post('/admin/cadastro/buscaproduto', { 
			idcli: 	$("input[name=idcliente]").val()}, 
			function(resposta) {				
				if(resposta == "erro"){	
					jAlert('Erro ao buscar dados! Tente novamente.', 'Erro!');
				}else{
					$('#divreccomp').html(resposta);
				}
			}
		);
	});
	
	/*----- Relatorio ---------------------------------
	 * Grava contatos selecionados na busca do relatorio
	 *  
	 * */

	function gravaContatosrel(opcao){
		
		var contatossel = "";
		$("input[type=checkbox][name='contatosrel[]']:checked").each(function(){
			contatossel = contatossel+($(this).val())+",";
		});
		
		$.post('/admin/cadastro/gravacontatosrel', {
			contatosids: 	$('#idscontatos').val(),
		    contsel:		contatossel,
		    id:				opcao
		},
		function(resposta) {				
			if(resposta == "erro"){	
				jAlert('Erro ao gravar a lista! Tente novamente.', 'Erro!');
			}else{
				jAlert('Lista salva com sucesso!', 'Sucesso!', function(){
					//window.location = window.location;
				});
			}
		});
		
	}
	
	$(".bSalvar").click( function() {
		gravaContatosrel();		
		
		if($('#idcampanha').val()!=""){
			jPrompt('Nome da campanha:', '', 'Salvar', function(r) {
				if(r==true){
					window.location = '/admin/cadastro/salvarcontatosrel/nome/'+r;
				}
			});
		}else{
			gravaContatosrel($('#idcampanha').val());	
		}
	});
	
	$(".bSalvarcomo").click( function() {
		gravaContatosrel();		
		jPrompt('Nome da campanha:', '', 'Salvar', function(r) {
			if(r==true){
				window.location = '<?php echo $this->baseUrl()?>/admin/cadastro/salvarcontatosrel/nome/'+r;
			}
		});
	});
	
});

function buscaInteracoes(){
	$('#resultado').html('<div style="border: 1px solid #d5d5d5; padding: 20px; margin-top: 5px" ><img src="/public/sistema/imagens/loaders/loader6.gif" alt="Carregando"> <i>Aguarde, buscando interações...</i></div>');
	$.post('/admin/cadastro/buscacontatosinteracoes', { 
		empresa: 			$('input[name=idempresa]').val(),
		pendentes: 			$("input[name='pendentes']:checked").val(),
		todos: 				$("input[name='todos']:checked").val(),
		buscaregioes:		$("select[name=buscaregioes] option:selected").val(),
		regioestelevendas:	$("select[name=regioestelevendas] option:selected").val(),
		dataini:   			$('input[name=dataini]').val(),
		datafim:   			$('input[name=datafim]').val()
		},
    	function(resposta) {
			if(resposta == "erro"){	
				jAlert('Erro ao buscar dados! Tente novamente.', 'Erro!');
			}else{
				$('#resultado').html(resposta);
			}
		}
	);
}

var xmlHttp;
function buscarParceiro(id){
	
	xmlHttp=GetXmlHttpObject();
	
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	var url="/admin/cadastro/buscaparceiros/idcliente/"+id;
	url=url+"/id/"+user;
	url=url+"/sid="+Math.random();
	xmlHttp.onreadystatechange=stateChangedsol;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function stateChangedsol(){
	if (xmlHttp.readyState==4){
		//document.novogar.id_or.value,
		document.getElementById('divsol').innerHTML=xmlHttp.responseText;
	}
}