<?php
	class VideosBO{		
		function listaVideos(){
			$bo	= new VideosModel();
			return $bo->fetchAll("id is not NULL", "id desc");
		}
	}
?>