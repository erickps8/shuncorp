
    /*Função  Pai de Mascaras*/
    function Mascara(o,f){
        v_obj=o
        v_fun=f
        setTimeout("execmascara()",1)
    }
    
    /*Função que Executa os objetos*/
    function execmascara(){
        v_obj.value=v_fun(v_obj.value)
    }
    
    /*Função que Determina as expressões regulares dos objetos*/
    function leech(v){
        v=v.replace(/o/gi,"0")
        v=v.replace(/i/gi,"1")
        v=v.replace(/z/gi,"2")
        v=v.replace(/e/gi,"3")
        v=v.replace(/a/gi,"4")
        v=v.replace(/s/gi,"5")
        v=v.replace(/t/gi,"7")
        return v
    }
    
    /*Função que permite apenas numeros*/
    function Integer(v){
        return v.replace(/\D/g,"")
    }
    
    /*Função que padroniza telefone (11) 4184-1241*/
    function Telefone(v){
        v=v.replace(/\D/g,"")                 
        v=v.replace(/^(\d\d)(\d)/g,"($1) $2") 
        v=v.replace(/(\d{4})(\d)/,"$1-$2")    
        return v
    }
    
    /*Função que padroniza telefone (11) 41841241*/
    function TelefoneCall(v){
        v=v.replace(/\D/g,"")                 
        v=v.replace(/^(\d\d)(\d)/g,"($1) $2")    
        return v
    }
    
    /*Função que padroniza telefone (11) 4184-1241*/
    function TelefoneInt(v){
        v=v.replace(/\D/g,"")                
        v=v.replace(/^(\d\d)(\d\d)(\d)/g,"+$1 ($2) $3")
        //v=v.replace(/^(\d\d)(\d)/g,"($1) $2") 
       // v=v.replace(/(\d{4})(\d)/,"$1-$2")    
        return v
    }
    
    /*Função que padroniza CPF*/
    function Cpf(v){
    	v=v.replace(/\D/g,"")                    
        v=v.replace(/(\d{3})(\d)/,"$1.$2")       
        v=v.replace(/(\d{3})(\d)/,"$1.$2")       
                                                 
        v=v.replace(/(\d{3})(\d{1,2})$/,"$1-$2") 
        return v
    }
    
    /*Função que padroniza CEP*/
    function Cep(v){
        v=v.replace(/\D/g,"")                
        v=v.replace(/^(\d{5})(\d)/,"$1-$2") 
        return v
    }
    
    /*Função que padroniza CNPJ*/
    function Cnpj(v){
        v=v.replace(/\D/g,"")                   
        v=v.replace(/^(\d{2})(\d)/,"$1.$2")     
        v=v.replace(/^(\d{2})\.(\d{3})(\d)/,"$1.$2.$3") 
        v=v.replace(/\.(\d{3})(\d)/,".$1/$2")           
        v=v.replace(/(\d{4})(\d)/,"$1-$2")              
        return v
    }
    
    function Codigodebarras(v){
        v=v.replace(/\D/g,"")                   
        v=v.replace(/^(\d{5})(\d)/,"$1.$2")     
        v=v.replace(/^(\d{5})\.(\d{5})(\d)/,"$1.$2 $3")    
        v=v.replace(/(\d{5})(\d)/,"$1.$2")
        v=v.replace(/(\d{5})\.(\d{6})(\d)/,"$1.$2 $3")
        v=v.replace(/(\d{5})(\d)/,"$1.$2")
        
        return v
    }
    
    /*Função que permite apenas numeros Romanos*/
    function Romanos(v){
        v=v.toUpperCase()             
        v=v.replace(/[^IVXLCDM]/g,"") 
        
        while(v.replace(/^M{0,4}(CM|CD|D?C{0,3})(XC|XL|L?X{0,3})(IX|IV|V?I{0,3})$/,"")!="")
            v=v.replace(/.$/,"")
        return v
    }
    
    /*Função que padroniza o Site*/
    function Site(v){
        v=v.replace(/^http:\/\/?/,"")
        dominio=v
        caminho=""
        if(v.indexOf("/")>-1)
            dominio=v.split("/")[0]
            caminho=v.replace(/[^\/]*/,"")
            dominio=dominio.replace(/[^\w\.\+-:@]/g,"")
            caminho=caminho.replace(/[^\w\d\+-@:\?&=%\(\)\.]/g,"")
            caminho=caminho.replace(/([\?&])=/,"$1")
        if(caminho!="")dominio=dominio.replace(/\.+$/,"")
            v="http://"+dominio+caminho
        return v
    }

    /*Função que padroniza DATA*/
    function Data(v){
        v=v.replace(/\D/g,"") 
        v=v.replace(/(\d{2})(\d)/,"$1/$2") 
        v=v.replace(/(\d{2})(\d)/,"$1/$2") 
        return v
    }
    
    /*Função que padroniza Codigo do produto*/
    function Codigo(v){
    	v=v.replace(/[^\w\_]/g,"")
        return v
    }
    
    /*Função que padroniza DATA*/
    function Hora(v){
        v=v.replace(/\D/g,"") 
        v=v.replace(/(\d{2})(\d)/,"$1:$2")  
        return v
    }
    
    /*Função que padroniza valor monétario*/
    function Valor(v){
    	 v=v.replace(/\D/g,"") //Remove tudo o que não é dígito
         v=v.replace(/^([0-9]{3}\.?){3}-[0-9]{2}$/,"$1.$2");
         //v=v.replace(/(\d{3})(\d)/g,"$1,$2")
         v=v.replace(/(\d{1})(\d{6ha n})$/,"$1.$2")  //coloca ponto antes dos últimos 6 digitos
         v=v.replace(/(\d)(\d{3})$/,"$1,$2") //Coloca ponto antes dos 3 últimos digitos
         return v
    }
    
    function Valorc(v){
        v=v.replace(/\D/g,"") //Remove tudo o que não é dígito
        v=v.replace(/^([0-9]{3}\.?){3}-[0-9]{2}$/,"$1.$2");
        //v=v.replace(/(\d{3})(\d)/g,"$1,$2")
        v=v.replace(/(\d{1})(\d{6ha n})$/,"$1.$2")  //coloca ponto antes dos últimos 6 digitos
        v=v.replace(/(\d)(\d{3})$/,"$1,$2") //Coloca ponto antes dos 3 últimos digitos
        return v
    }
    
    function Valorduascasas(v){
        v=v.replace(/\D/g,"") //Remove tudo o que não é dígito
        v=v.replace(/^([0-9]{3}\.?){3}-[0-9]{2}$/,"$1.$2");
        //v=v.replace(/(\d{5})(\d)/g,"$1,$2")
        v=v.replace(/(\d{1})(\d{4ha n})$/,"$1.$2")  //coloca ponto antes dos últimos 6 digitos
        v=v.replace(/(\d)(\d{2})$/,"$1,$2") //Coloca ponto antes dos 2 últimos digitos
        return v
    }
    
    function mreais(v){
        v=v.replace(/\D/g,"")                                           //Remove tudo o que não é dígito
        v=v.replace(/(\d{2})$/,",$1")                   //Coloca a virgula
        v=v.replace(/(\d+)(\d{3},\d{2})$/g,"$1.$2")     //Coloca o primeiro ponto
        return v
    }
    
    function mvalorquatrocasas(v){
        v=v.replace(/\D/g,"")                                           //Remove tudo o que não é dígito
        v=v.replace(/(\d{4})$/,",$1")                   //Coloca a virgula
        v=v.replace(/(\d+)(\d{3},\d{4})$/g,"$1.$2")     //Coloca o primeiro ponto
        return v
    }
    
    function ValorUmacasa(v){
        v=v.replace(/\D/g,"") //Remove tudo o que não é dígito
        v=v.replace(/^([0-9]{3}\.?){3}-[0-9]{2}$/,"$1.$2");
        //v=v.replace(/(\d{3})(\d)/g,"$1,$2")
        //v=v.replace(/(\d{1})(\d{6ha n})$/,"$1.$2")  //coloca ponto antes dos últimos 6 digitos
        v=v.replace(/(\d)(\d{1})$/,"$1,$2") //Coloca ponto antes dos 3 últimos digitos
        return v
    }
    
    function Valorcinco(v){ //5 casas decimais
        v=v.replace(/\D/g,"") //Remove tudo o que não é dígito
        v=v.replace(/^([0-9]{3}\.?){3}-[0-9]{2}$/,"$1.$2");
        //v=v.replace(/(\d{3})(\d)/g,"$1,$2")
        v=v.replace(/(\d{1})(\d{6ha n})$/,"$1.$2")  //coloca ponto antes dos últimos 6 digitos
        v=v.replace(/(\d)(\d{5})$/,"$1,$2") //Coloca ponto antes dos 3 últimos digitos
        return v
    }
    /*Função que padroniza Area*/
    function Area(v){
        v=v.replace(/\D/g,"") 
        v=v.replace(/(\d)(\d{2})$/,"$1.$2") 
        return v
        
    }
    
    /*Função que padroniza peso*/
    function Kilo(v){
        v=v.replace(/\D/g,"") //Remove tudo o que não é dígito
        v=v.replace(/^([0-9]{3}\.?){3}-[0-9]{2}$/,"$1.$2");
        v=v.replace(/(\d)(\d{3})$/,"$1,$2") //Coloca ponto antes dos 2 últimos digitos
        return v
    }
    
    /*Função que padroniza peso*/
    function Ncm(v){
        v=v.replace(/\D/g,"") //Remove tudo o que não é dígito
        v=v.replace(/^([0-9]{3}\.?){3}-[0-9]{2}$/,"$1.$2");
        v=v.replace(/(\d)(\d{4})$/,"$1.$2") //Coloca ponto antes dos 4 últimos digitos
        return v
    }
    
    function Hscode(v){
        v=v.replace(/\D/g,"") //Remove tudo o que não é dígito
        v=v.replace(/^([0-9]{3}\.?){5}-[0-9]{2}$/,"$1.$2");
        v=v.replace(/(\d)(\d{6})$/,"$1.$2") //Coloca ponto antes dos 4 últimos digitos
        return v
    }
    
    
    function Email(){
    	campo = document.cad_cliente.email1;
    	if(campo.value != ''){
			len = campo.value.length;
			arroba = 0;
			ponto =0;
			
				for (var i=0 ; i < len ; i++ )
				{
				str = campo.value.substr(i,1);
					if (str == '@') {arroba++;}
					if (str == '.') {ponto++; }
				}
					if (arroba!=1 ||ponto==0)
					{
					alert ('Campo EMAIL Inválido');
					campo.focus();
					return false;
					}
		}
    	
    	campo = document.cad_cliente.email2;
    	if(campo.value != ''){
			len = campo.value.length;
			arroba = 0;
			ponto =0;
			
				for (var i=0 ; i < len ; i++ )
				{
				str = campo.value.substr(i,1);
					if (str == '@') {arroba++;}
					if (str == '.') {ponto++; }
				}
					if (arroba!=1 ||ponto==0)
					{
					alert ('Campo EMAIL 2 Inválido');
					campo.focus();
					return false;
					}
		}
    	
    	campo = document.cad_cliente.email3;
    	if(campo.value != ''){
			len = campo.value.length;
			arroba = 0;
			ponto =0;
			
				for (var i=0 ; i < len ; i++ )
				{
				str = campo.value.substr(i,1);
					if (str == '@') {arroba++;}
					if (str == '.') {ponto++; }
				}
					if (arroba!=1 ||ponto==0)
					{
					alert ('Campo EMAIL 3 Inválido');
					campo.focus();
					return false;
					}
		}
		return true;
		
	}

    
    function mascara_num(obj){
        valida_num(obj)
        if (obj.value.match("-")){
          mod = "-";
        }else{
          mod = "";
        }
        valor = obj.value.replace("-","");
        valor = valor.replace(",","");
        if (valor.length >= 3){
          valor = poe_ponto_num(valor.substring(0,valor.length-2))+","+valor.substring(valor.length-2, valor.length);
        }
        obj.value = mod+valor;
      }
      
      function poe_ponto_num(valor){
        valor = valor.replace(/\./g,"");
        if (valor.length > 3){
          valores = "";
          while (valor.length > 3){
            valores = "."+valor.substring(valor.length-3,valor.length)+""+valores;
            valor = valor.substring(0,valor.length-3);
          }
          return valor+""+valores;
        }else{
          return valor;
        }
      }
      function valida_num(obj){
        numeros = new RegExp("[0-9]");
        while (!obj.value.charAt(obj.value.length-1).match(numeros)){
          if(obj.value.length == 1 && obj.value == "-"){
            return true;
          }
          if(obj.value.length >= 1){
            obj.value = obj.value.substring(0,obj.value.length-1)
          }else{
            return false;
          }
        }
      }
    
      function formatar(src, mask){
          var i = src.value.length;
          var saida = mask.substring(0,1);
          var texto = mask.substring(i)
          if (texto.substring(0,1) != saida)
          {
            src.value += texto.substring(0,1);
          }
      }
      
  /*  usar no html:
    	
    	<script src="mascara.js"></script>
    	</head>
    	<body>
    	<table width="100%" border="0">
    	    <tr>
    	        <td colspan="2" align="center"><strong>Exemplos de Funções de mascaras em javascript</strong></td>
    	    </tr>
    	    <tr bgcolor="#e1e1e1">
    	        <td width="13%">[Só numeros]</td>
    	        <td width="87%"><input name="int" type="text" id="int" onKeyDown="Mascara(this,Integer);" onKeyPress="Mascara(this,Integer);" onKeyUp="Mascara(this,Integer);"></td>
    	    </tr>
    	    <tr>
    	        <td width="13%">[Telefone]</td>
    	        <td width="87%"><input name="tel" type="text" id="tel" maxlength="14" onKeyDown="Mascara(this,Telefone);" onKeyPress="Mascara(this,Telefone);" onKeyUp="Mascara(this,Telefone);"></td>
    	    </tr>
    	    <tr bgcolor="#e1e1e1">
    	        <td width="13%">[CPF]</td>
    	        <td width="87%"><input name="cpf" type="text" id="cpf" maxlength="14" onKeyDown="Mascara(this,Cpf);" onKeyPress="Mascara(this,Cpf);" onKeyUp="Mascara(this,Cpf);"></td>
    	    </tr>
    	    <tr>
    	        <td width="13%">[Cep]</td>
    	        <td width="87%"><input name="cep" type="text" id="cep" maxlength="9" onKeyDown="Mascara(this,Cep);" onKeyPress="Mascara(this,Cep);" onKeyUp="Mascara(this,Cep);"></td>
    	    </tr>
    	    <tr bgcolor="#e1e1e1">
    	        <td width="13%">[CNPJ]</td>
    	        <td width="87%"><input name="cnpj" type="text" id="cnpj" maxlength="18" onKeyDown="Mascara(this,Cnpj);" onKeyPress="Mascara(this,Cnpj);" onKeyUp="Mascara(this,Cnpj);"></td>
    	    </tr>
    	    <tr>
    	        <td width="13%">[Romanos]</td>
    	        <td width="87%"><input name="rom" type="text" id="rom"  onKeyDown="Mascara(this,Romanos);" onKeyPress="Mascara(this,Romanos);" onKeyUp="Mascara(this,Romanos);"></td>
    	    </tr>
    	    <tr bgcolor="#e1e1e1">
    	        <td width="13%">[Site]</td>
    	        <td width="87%"><input name="sit" type="text" id="sit"  onKeyDown="Mascara(this,Site);" onKeyPress="Mascara(this,Site);" onKeyUp="Mascara(this,Site);"></td>
    	    </tr>
    	    <tr>
    	        <td width="13%">[Data]</td>
    	        <td width="87%"><input name="date" type="text" id="date" maxlength="10" onKeyDown="Mascara(this,Data);" onKeyPress="Mascara(this,Data);" onKeyUp="Mascara(this,Data);"></td>
    	    </tr>
    	    <tr bgcolor="#e1e1e1">
    	        <td width="13%">[Area Valor]</td>
    	        <td width="87%"><input name="arevalo" type="text" id="arevalo"  onKeyDown="Mascara(this,Area);" onKeyPress="Mascara(this,Area);" onKeyUp="Mascara(this,Area);"></td>
    	    </tr>
    	</table>
    	</body>
*/
      
      
      var alertaPadrao = function(titulo, msg, tipo, altura, largura) {
			$('body').append('<a href="#" id="alerta-padrao"></a>');
			$('#alerta-padrao').m2brDialog({
					draggable: true,
					texto: msg,
					tipo: tipo,
					titulo: titulo,
					altura: altura,
					largura: largura,
					botoes: {
						1: {
							label: 'Close',
							tipo: 'fechar'
						}
					}									   
			});
			$('#alerta-padrao')
				.click()
				.remove();
	};