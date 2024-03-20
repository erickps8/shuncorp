MensageiroDadosBotoes = function(conf){

	var config = conf;

	this.abreAjuda = function(controllerAction, idForm){
		var elemento 	= (idForm ? $(idForm) : null );
		elemento.target = "_blank"; 
		elemento.action = controllerAction;
		elemento.method = "post";
		elemento.submit();
	}.bind(this)

	this.abrirModalAjuda = function(controller){
		new MochaUI.Window({
			id: 'slideshare',
			title: 'Ajuda',
			loadMethod: 'xhr',
			contentURL: controller,
			width: 800,
			height: 400,
			resizeLimit:  {'x': [330, 2500], 'y': [250, 2000]}	//674 405	
		});		
	
	}.bind(this)


	this.enviarDadosFormulario = function(controllerAction, idForm){

		new ModalCarregando();
		var elemento = (idForm ? $(idForm) : null );
		new Request.HTML({
								url: controllerAction,
								onSuccess: function(){
									MochaUI.closeWindow($('CarregandoModal'));
									new ModalDadosSalvos();
									//alert("Processamento salvo com sucesso!!! colocar janela -- TODO");
								}.bind(this)
							}
						).post(elemento);
	}.bind(this)

	this.enviarDadosFormularioAtualiza = function(controller,idForm){
			new ModalCarregando();
			var elemento = (idForm ? $(idForm) : null );
			new Request.HTML({
									url: controller,
									update: $('mainPanel'),
									onSuccess: function(){

/*										var scroll = new Fx.Scroll($('mainPanel'), {
											duration: 0,
											offset: {'x': -100000, 'y': -100000}
										});*/
										
										MochaUI.closeWindow($('CarregandoModal'));
										//scroll.toElement(elemento);

									}.bind(this)
								}
							).post(elemento);
	}.bind(this)
	
	this.enviarDadosFormularioSubmit = function(idForm){
		var elemento = (idForm ? $(idForm) : null );
		elemento.submit();
	}.bind(this)
	
	this.enviarDadosFormularioModal = function(controller,idForm){
		
				new ModalCarregando();
				var elemento = (idForm ? $(idForm) : null );
				new Request.HTML({
										url: controller,
										onSuccess: function(){
											MochaUI.closeWindow($('CarregandoModal'));
											MochaUI.closeWindow($('slideshare'));
										}.bind(this)
									}
								).post(elemento);
		

	}.bind(this)

	enviarDadosFormularioModalSemUpdate = function(controller,idForm){
		
				new ModalCarregando();
				var elemento = (idForm ? $(idForm) : null );
				new Request.HTML({
										url: controller,
										update: $('mainPanel'),
										onSuccess: function(){
											MochaUI.closeWindow($('CarregandoModal'));
											MochaUI.closeWindow($('slideshare'));
										}.bind(this)
									}
								).post(elemento);
		

	}.bind(this)
	
	
	
this.abrirModal = function(controller){
		new MochaUI.Window({
			id: 'slideshare',
			title: config.telaNome,
			loadMethod: 'xhr',
			contentURL: controller,
			width: 674,
			height: 405,
			resizeLimit:  {'x': [330, 2500], 'y': [250, 2000]}	//674 405	
		});		
	
	}.bind(this)


this.abrirModalParams = function(controller,w,h){
		new MochaUI.Window({
		id: 'slideshare',
		title: config.telaNome,
		loadMethod: 'xhr',
		contentURL: controller,
		width: w,
		height: h,
		resizeLimit: {'x': [330, 2500], 'y': [250, 2000]}
		});
	}.bind(this)

	

	
this.habilitaBotaoSalvar = function(){
				if($('barraCadastroSalvarUnico')){
						$('barraCadastroSalvarUnico').removeEvent('click');
						$('barraCadastroSalvarUnico').addEvent('click', function(){
								var elemento = (config.idFormularioHTML ? $(config.idFormularioHTML) : null );
								var HTMLRequest = new Request.JSON(
																	{
																		url: config.urlValidarInformacoes,
																		onComplete: function(obj){
																			if (obj.tipo == 0){
																				new ModalValidacao(obj.mensagem);
																				return false;
																			}else{
																				this.enviarDadosFormularioModal(config.urlGravarInformacoes, config.idFormularioHTML);
																				//MochaUI.notification("<h3>" + obj.mensagem + "</h3>");
																				return true;
																			}
																		}.bind(this)
																	}
																).post(elemento);
								HTMLRequest = null;
						}.bind(this));
				}		
}.bind(this)


this.habilitaBotaoSalvarSemUpdate = function(){
				if($('barraCadastroSalvarUnico')){
						$('barraCadastroSalvarUnico').removeEvent('click');
						$('barraCadastroSalvarUnico').addEvent('click', function(){
								var elemento = (config.idFormularioHTML ? $(config.idFormularioHTML) : null );
								var HTMLRequest = new Request.JSON(
																	{
																		url: config.urlValidarInformacoes,
																		onComplete: function(obj){
																			if (obj.tipo == 0){
																				new ModalValidacao(obj.mensagem);
																				return false;
																			}else{
																				enviarDadosFormularioModalSemUpdate(config.urlGravarInformacoes, config.idFormularioHTML);
																				//MochaUI.notification("<h3>" + obj.mensagem + "</h3>");
																				return true;
																			}
																		}.bind(this)
																	}
																).post(elemento);
								HTMLRequest = null;
						}.bind(this));
				 }		
}.bind(this)

	  
this.habilitaBotaoPesquisa = function(){
	if($('botaoPesquisa')){
		$('botaoPesquisa').removeEvent('click');
		$('botaoPesquisa').addEvent('click', function(){
			this.enviarDadosFormularioAtualiza(config.urlGravarInformacoes,config.idFormularioHTML);
		}.bind(this));
	}	
}.bind(this)

this.habilitaBotaoNovo = function(){
	if($('botaoNovo')){
		$('botaoNovo').removeEvent('click');
		$('botaoNovo').addEvent('click', function(){
			this.abrirModal(config.urlModal);
		}.bind(this));
	}	
}.bind(this)

this.habilitaBarraCadastroUpdate = function(){
		new Request.HTML({
					url: config.urlPainelInferior,
					update: $(config.idPainelPrincipal),
					onSuccess: function(){
						if($('barraCadastroSalvar')){
								$('barraCadastroSalvar').removeEvent('click');
								$('barraCadastroSalvar').addEvent('click', function(){
										
										var elemento = (config.idFormularioHTML ? $(config.idFormularioHTML) : null );
										var HTMLRequest = new Request.JSON(
																			{
																				url: config.urlValidarInformacoes,
																				onComplete: function(obj){
																					if (obj.tipo == 0){
																						new ModalValidacao(obj.mensagem);
																						return false;
																					}else{
																						this.enviarDadosFormularioAtualiza(config.urlGravarInformacoes, config.idFormularioHTML);
																						//MochaUI.notification("<h3>" + obj.mensagem + "</h3>");
																						return true;
																					}
																				}.bind(this)
																			}
																		).post(elemento);
										HTMLRequest = null;
									
								}.bind(this));
							}	
							if($('barraCadastroCancelar')){
										$('barraCadastroCancelar').removeEvent('click');
										$('barraCadastroCancelar').addEvent('click', function(){
											new ModalConfirmacao(config.urlPainelPrincipalAnterior, config.urlPainelInferiorAnterior);
										}.bind(this));
							}			
		
					}.bind(this)
				  }).send();
}.bind(this)

//Comentario
this.habilitaBarraCadastro = function(){
		new Request.HTML({
					url: config.urlPainelInferior,
					update: $(config.idPainelPrincipal),
					onSuccess: function(){
						if($('barraCadastroSalvar')){
								$('barraCadastroSalvar').removeEvent('click');
								$('barraCadastroSalvar').addEvent('click', function(){
									
										var elemento = (config.idFormularioHTML ? $(config.idFormularioHTML) : null );
										var HTMLRequest = new Request.JSON(
																			{
																				url: config.urlValidarInformacoes,
																				onComplete: function(obj){
																					if (obj.tipo == 0){
																						new ModalValidacao(obj.mensagem);
																						return false;
																					}else{
																						this.enviarDadosFormulario(config.urlGravarInformacoes, config.idFormularioHTML);
																						//MochaUI.notification("<h3>" + obj.mensagem + "</h3>");
																						return true;
																					}
																				}.bind(this)
																			}
																		).post(elemento);
										HTMLRequest = null;
									
									
								}.bind(this));
						}
						if($('barraCadastroCancelar')){
								$('barraCadastroCancelar').removeEvent('click');
								$('barraCadastroCancelar').addEvent('click', function(){
									new ModalConfirmacao(config.urlPainelPrincipalAnterior, config.urlPainelInferiorAnterior);
								}.bind(this));
						}		
		
					}.bind(this)
				  }).send();
}.bind(this)	
				  
				  

this.habilitaBarraCadastroSubmit = function(){
		new Request.HTML({
					url: config.urlPainelInferior,
					update: $(config.idPainelPrincipal),
					onSuccess: function(){
					 if($('barraCadastroSalvar')){
							$('barraCadastroSalvar').removeEvent('click');
							$('barraCadastroSalvar').addEvent('click', function(){
								this.enviarDadosFormularioSubmit(config.idFormularioHTML);
							}.bind(this));
					  }
					  if($('barraCadastroCancelar')){	 
							$('barraCadastroCancelar').removeEvent('click');
							$('barraCadastroCancelar').addEvent('click', function(){
								new ModalConfirmacao(config.urlPainelPrincipalAnterior, config.urlPainelInferiorAnterior);
							}.bind(this));
					   }	
					}.bind(this)
				  }).send();
}.bind(this)



this.habilitaBarraCadastroPCP = function(){
		new Request.HTML({
					url: config.urlPainelInferior,
					update: $(config.idPainelPrincipal),
					onSuccess: function(){
					if($('barraCadastroSalvar')){
						$('barraCadastroSalvar').removeEvent('click');
						$('barraCadastroSalvar').addEvent('click', function(){
								var elemento = (config.idFormularioHTML ? $(config.idFormularioHTML) : null );
								var HTMLRequest = new Request.JSON(
																	{
																		url: config.urlValidarInformacoes,
																		onComplete: function(obj){
																			if (obj.tipo == 0){
																				new ModalValidacao(obj.mensagem);
																				return false;
																			}else{
																				this.enviarDadosFormulario(config.urlGravarInformacoes, config.idFormularioHTML);
																				//MochaUI.notification("<h3>" + obj.mensagem + "</h3>");
																				return true;
																			}
																		}.bind(this)
																	}
																).post(elemento);
								HTMLRequest = null;
							
						}.bind(this));
					}
					if($('barraCadastroSalvarSituacao')){
						$('barraCadastroSalvarSituacao').removeEvent('click');
						$('barraCadastroSalvarSituacao').addEvent('click', function(){
							var elemento = (config.idFormularioHTML ? $(config.idFormularioHTML) : null );
								var HTMLRequest = new Request.JSON(
																	{
																		url: config.urlValidarInformacoes,
																		onComplete: function(obj){
																			if (obj.tipo == 0){
																				new ModalValidacao(obj.mensagem);
																				return false;
																			}else{
																				this.enviarDadosFormularioAtualiza(config.urlGravarInformacoesSituacao, config.idFormularioHTML);
																				//MochaUI.notification("<h3>" + obj.mensagem + "</h3>");
																				return true;
																			}
																		}.bind(this)
																	}
																).post(elemento);
								HTMLRequest = null;
							
						}.bind(this));
					}
					if($('barraCadastroCancelar')){	
						$('barraCadastroCancelar').removeEvent('click');
						$('barraCadastroCancelar').addEvent('click', function(){
							new ModalConfirmacao(config.urlPainelPrincipalAnterior, config.urlPainelInferiorAnterior);
						}.bind(this));
					}
					}.bind(this)
				  }).send();
}.bind(this)


this.habilitaBarraCadastroPrograma = function(){
		new Request.HTML({
					url: config.urlPainelInferior,
					update: $(config.idPainelPrincipal),
					onSuccess: function(){
					if($('barraCadastroSalvar')){
						$('barraCadastroSalvar').removeEvent('click');
						$('barraCadastroSalvar').addEvent('click', function(){
								var elemento = (config.idFormularioHTML ? $(config.idFormularioHTML) : null );
								var HTMLRequest = new Request.JSON(
																	{
																		url: config.urlValidarInformacoes,
																		onComplete: function(obj){
																			if (obj.tipo == 0){
																				new ModalValidacao(obj.mensagem);
																				return false;
																			}else{
																				this.enviarDadosFormulario(config.urlGravarInformacoes, config.idFormularioHTML);
																				//MochaUI.notification("<h3>" + obj.mensagem + "</h3>");
																				return true;
																			}
																		}.bind(this)
																	}
																).post(elemento);
								HTMLRequest = null;
							
						}.bind(this));
					}
					
					if($('barraCadastroSalvarSituacao')){
						$('barraCadastroSalvarSituacao').removeEvent('click');
						$('barraCadastroSalvarSituacao').addEvent('click', function(){
							var elemento = (config.idFormularioHTML ? $(config.idFormularioHTML) : null );
								var HTMLRequest = new Request.JSON(
																	{
																		url: config.urlValidarInformacoes,
																		onComplete: function(obj){
																			if (obj.tipo == 0){
																				new ModalValidacao(obj.mensagem);
																				return false;
																			}else{
																				this.enviarDadosFormularioAtualiza(config.urlGravarInformacoesSituacao, config.idFormularioHTML);
																				//MochaUI.notification("<h3>" + obj.mensagem + "</h3>");
																				return true;
																			}
																		}.bind(this)
																	}
																).post(elemento);
								HTMLRequest = null;
							
						}.bind(this));
					}
					if($('barraCadastroCancelar')){	
						$('barraCadastroCancelar').removeEvent('click');
						$('barraCadastroCancelar').addEvent('click', function(){
							new ModalConfirmacao(config.urlPainelPrincipalAnterior, config.urlPainelInferiorAnterior);
						}.bind(this));
					}
					}.bind(this)
				  }).send();
}.bind(this)



this.habilitaBarraNada = function(){
		new Request.HTML({
					url: config.urlPainelInferior,
					update: $(config.idPainelPrincipal),
					onSuccess: function(){
					}.bind(this)
				  }).send();
}.bind(this)


this.habilitaBarraCadastroUpdateConfirmacao = function(){
	new Request.HTML({
				url: config.urlPainelInferior,
				update: $(config.idPainelPrincipal),
				onSuccess: function(){
					if($('barraCadastroSalvar')){
							$('barraCadastroSalvar').removeEvent('click');
							$('barraCadastroSalvar').addEvent('click', function(){
							
								var controller 	= config.urlGravarInformacoes;
								var idForm 		= config.idFormularioHTML;
								var msg			= config.msg;
								
								new ModalConfirmacaoAcao(msg, controller, idForm);
								
							}.bind(this));
						}	
						if($('barraCadastroCancelar')){
									$('barraCadastroCancelar').removeEvent('click');
									$('barraCadastroCancelar').addEvent('click', function(){
										new ModalConfirmacao(config.urlPainelPrincipalAnterior, config.urlPainelInferiorAnterior);
									}.bind(this));
						}			
	
				}.bind(this)
			  }).send();
}.bind(this)





}