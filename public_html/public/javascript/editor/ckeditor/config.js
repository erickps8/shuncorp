/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license

*/
CKEDITOR.editorConfig = function( config ){
	config.filebrowserBrowseUrl = '/public/javascript/editor/ckfinder/ckfinder.html',
	config.filebrowserImageBrowseUrl = '/public/javascript/editor/ckfinder/ckfinder.html?type=Images',
	config.filebrowserFlashBrowseUrl = '/public/javascript/editor/ckfinder/ckfinder.html?type=Flash',
	config.filebrowserUploadUrl = '/public/javascript/editor/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
	config.filebrowserImageUploadUrl = '/public/javascript/editor/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images',
	config.filebrowserFlashUploadUrl = '/public/javascript/editor/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash'
};
