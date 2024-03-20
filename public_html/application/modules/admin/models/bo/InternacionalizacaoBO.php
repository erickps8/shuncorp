<?php
	class InternacionalizacaoBO{				
		
		function gerarTraducao(){
			$bo		= new InternacionalizacaoModel();
			
			$abre = fopen(Zend_Registry::get('pastaPadrao')."/public/traduzir/en.po", "r");
			$abre2 = fopen(Zend_Registry::get('pastaPadrao')."/public/traduzir/zh.po", "r");
			$abre3 = fopen(Zend_Registry::get('pastaPadrao')."/public/traduzir/es.po", "r");
		
			$le = fread($abre,filesize(Zend_Registry::get('pastaPadrao')."/public/traduzir/en.po"));
			$le2 = fread($abre2,filesize(Zend_Registry::get('pastaPadrao')."/public/traduzir/zh.po"));
			$le3 = fread($abre3,filesize(Zend_Registry::get('pastaPadrao')."/public/traduzir/es.po"));
		
			$dado = explode("msgid ",$le);
			$dado2 = explode("msgid ",$le2);
			$dado3 = explode("msgid ",$le3);
		
			for($i=2; $i<count($dado);$i++):
				$msg = explode("msgstr ",$dado[$i]);
				$msg2 = explode("#",$msg[1]);
			
				$msgzh = explode("msgstr ",$dado2[$i]);
				$msgzn2 = explode("#",$msgzh[1]);
			
				$msges = explode("msgstr ",$dado3[$i]);
				$msges2 = explode("#",$msges[1]);
			
				$var = substr($msg[0],1);
				$var = str_replace('"', '', substr($var,0,-1));
			
				$var1 = substr($msg2[0],1);
				$var1 = str_replace('"', '', substr($var1,0,-1));
			
				$var2 = substr($msgzn2[0],1);
				$var2 = str_replace('"', '', substr($var2,0,-1));
			
				$var3 = substr($msges2[0],1);
				$var3 = str_replace('"', '', substr($var3,0,-1));
				
				$data	= array(
					'portugues'	=> $var,
					'ingles'	=> $var1,
					'chines'	=> $var2,
					'espanhol'	=> $var3
				);
				
				$bo->insert($data);
				
			endfor;
		}
		
		function listaTraducoes(){
			$bo	= new InternacionalizacaoModel();
			return $bo->fetchAll("id>0",'ingles asc');			
		}
		
		function gravarTraducoes($params){
			$bo	= new InternacionalizacaoModel();
			foreach (InternacionalizacaoBO::listaTraducoes() as $traducoes):
				if($params[$traducoes->id] != ""):
					$data	= array(
						'ingles'	=> $params[$traducoes->id."_en"],
						'chines'	=> $params[$traducoes->id."_zh"],
						'espanhol'	=> $params[$traducoes->id."_es"]
					);
			
					$bo->update($data, "id = ".$traducoes->id);
				endif;
			endforeach;
		}
				
		function gerarPO(){
			$abre = fopen(Zend_Registry::get('pastaPadrao')."/public/traduzir/en.po", "w+");
			$abre2 = fopen(Zend_Registry::get('pastaPadrao')."/public/traduzir/zh.po", "w+");
			$abre3 = fopen(Zend_Registry::get('pastaPadrao')."/public/traduzir/es.po", "w+");
			$abre4 = fopen(Zend_Registry::get('pastaPadrao')."/public/traduzir/pt_BR.po", "w+");
			
			
			$texto1 = 'msgid ""
			msgstr ""
			"Project-Id-Version: ZTL EN\n"
			"Report-Msgid-Bugs-To: \n"
			"POT-Creation-Date: 2011-02-21 16:31-0300\n"
			"PO-Revision-Date: \n"
			"Last-Translator: Cleiton Silva Barbosa <cleiton@ztlbrasil.com.br>\n"
			"Language-Team: Cleiton Silva Barbosa <cleiton@ztlbrasil.com.br>\n"
			"Language: \n"
			"MIME-Version: 1.0\n"
			"Content-Type: text/plain; charset=UTF-8\n"
			"Content-Transfer-Encoding: 8bit\n"
			"X-Poedit-Language: en\n"
			"X-Poedit-Country: UNITED STATES\n"
			"X-Poedit-SourceCharset: utf-8\n"
			"X-Poedit-Basepath: /aplic/ztlbrasil.com.br/application/\n"
			"X-Poedit-SearchPath-0: modules/admin\n"
			"X-Poedit-SearchPath-1: modules/default\n"
			"X-Poedit-SearchPath-2: layouts\n"
			
			';
			
			$texto2 = 'msgid ""
			msgstr ""
			"Project-Id-Version: ZTL ZH\n"
			"Report-Msgid-Bugs-To: \n"
			"POT-Creation-Date: 2011-02-23 13:59-0300\n"
			"PO-Revision-Date: \n"
			"Last-Translator: Cleiton Silva Barbosa <cleiton@ztlbrasil.com.br>\n"
			"Language-Team: Cleiton Silva Barbosa <cleiton@ztlbrasil.com.br>\n"
			"Language: \n"
			"MIME-Version: 1.0\n"
			"Content-Type: text/plain; charset=UTF-8\n"
			"Content-Transfer-Encoding: 8bit\n"
			"X-Poedit-Language: zh\n"
			"X-Poedit-Country: CHINA\n"
			"X-Poedit-SourceCharset: utf-8\n"
			"X-Poedit-Basepath: /aplic/ztlbrasil.com.br/application/\n"
			"X-Poedit-SearchPath-0: modules/admin\n"
			"X-Poedit-SearchPath-1: modules/default\n"
			"X-Poedit-SearchPath-2: layouts\n"
			
			';
			
			$texto3 = 'msgid ""
			msgstr ""
			"Project-Id-Version: ZTL ES\n"
			"Report-Msgid-Bugs-To: \n"
			"POT-Creation-Date: 2011-02-23 13:59-0300\n"
			"PO-Revision-Date: \n"
			"Last-Translator: Cleiton Silva Barbosa <cleiton@ztlbrasil.com.br>\n"
			"Language-Team: Cleiton Silva Barbosa <cleiton@ztlbrasil.com.br>\n"
			"Language: \n"
			"MIME-Version: 1.0\n"
			"Content-Type: text/plain; charset=UTF-8\n"
			"Content-Transfer-Encoding: 8bit\n"
			"X-Poedit-Language: es\n"
			"X-Poedit-Country: ESPANHA\n"
			"X-Poedit-SourceCharset: utf-8\n"
			"X-Poedit-Basepath: /aplic/ztlbrasil.com.br/application/\n"
			"X-Poedit-SearchPath-0: modules/admin\n"
			"X-Poedit-SearchPath-1: modules/default\n"
			"X-Poedit-SearchPath-2: layouts\n"
			
			';
			
			$texto4 = 'msgid ""
			msgstr ""
			"Project-Id-Version: ZTL PT_BR\n"
			"Report-Msgid-Bugs-To: \n"
			"POT-Creation-Date: 2011-02-23 13:59-0300\n"
			"PO-Revision-Date: \n"
			"Last-Translator: Cleiton Silva Barbosa <cleiton@ztlbrasil.com.br>\n"
			"Language-Team: Cleiton Silva Barbosa <cleiton@ztlbrasil.com.br>\n"
			"Language: \n"
			"MIME-Version: 1.0\n"
			"Content-Type: text/plain; charset=UTF-8\n"
			"Content-Transfer-Encoding: 8bit\n"
			"X-Poedit-Language: es\n"
			"X-Poedit-Country: ESPANHA\n"
			"X-Poedit-SourceCharset: utf-8\n"
			"X-Poedit-Basepath: /aplic/ztlbrasil.com.br/application/\n"
			"X-Poedit-SearchPath-0: modules/admin\n"
			"X-Poedit-SearchPath-1: modules/default\n"
			"X-Poedit-SearchPath-2: layouts\n"
			
			';
			
			fwrite($abre, $texto1);
			fclose($abre);
			
			fwrite($abre2, $texto2);
			fclose($abre2);
			
			fwrite($abre3, $texto3);
			fclose($abre3);
			
			fwrite($abre4, $texto4);
			fclose($abre4);
			
			$bo	= new InternacionalizacaoModel();
			
			foreach ($bo->fetchAll() as $traducao):
				$abre = fopen(Zend_Registry::get('pastaPadrao')."/public/traduzir/en.po", "a+");
				$texto1 = '
				
				msgid "'.$traducao->portugues.'"
				msgstr "'.$traducao->ingles.'"';
				
				fwrite($abre, $texto1);
				fclose($abre);
				$abre2 = fopen(Zend_Registry::get('pastaPadrao')."/public/traduzir/zh.po", "a+");
				$texto2 = '
				
				msgid "'.$traducao->portugues.'"
				msgstr "'.$traducao->chines.'"';
				
				fwrite($abre2, $texto2);
				fclose($abre2);
				$abre3 = fopen(Zend_Registry::get('pastaPadrao')."/public/traduzir/es.po", "a+");
				$texto3 = '
				
				msgid "'.$traducao->portugues.'"
				msgstr "'.$traducao->espanhol.'"';
				
				fwrite($abre3, $texto3);
				fclose($abre3);
				$abre4 = fopen(Zend_Registry::get('pastaPadrao')."/public/traduzir/pt_BR.po", "a+");
				$texto4 = '
				
				msgid "'.$traducao->portugues.'"
				msgstr ""';
				
				fwrite($abre4, $texto4);
				fclose($abre4);
				
			endforeach;
			
		}
		
		
		
	}
?>