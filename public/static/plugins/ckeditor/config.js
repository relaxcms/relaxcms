/**
 * @license Copyright (c) 2003-2014, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	config.allowedContent=true;
	
	config.filebrowserImageUploadUrl = '/ASHX/ckeditorupload.ashx'; //图片上传固定路径
    config.filebrowserUploadUrl = '/ASHX/ckeditoruploadfile.ashx'; //文件上传固定路径
    config.filebrowserHtml5videoUploadUrl = "/LCShareManage/Videos"; //上传视频的地址
    config.toolbarCanCollapse = true;
    
    //config.extraPlugins = 'attach';
	//config.extraPlugins = 'html5audio';
	//config.extraPlugins = 'html5video';
    config.extraPlugins = "video,audio,attach";
	config.image_previewText = ' ';
	
	config.toolbar = 'Full';
 
	config.toolbar_Full =
	[
		
	    { name: 'document', items : [ 'Source','-','Save','NewPage','DocProps','Preview','Print','-','Templates' ] },
	    { name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
	    { name: 'editing', items : [ 'Find','Replace','-','SelectAll','-','SpellChecker', 'Scayt' ] },
	    { name: 'forms', items : [ 'Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 
	        'HiddenField' ] },
	    '/',
	    { name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat' ] },
	    { name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv',
	    '-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl' ] },
	    { name: 'links', items : [ 'Link','Unlink','Anchor' ] },
	    { name: 'insert', items: ['html5audio', 'Image', 'video', 'audio', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak', 'Iframe', 'attach'] },
	    '/',
	    { name: 'styles', items : [ 'Styles','Format','Font','FontSize' ] },
	    { name: 'colors', items : [ 'TextColor','BGColor' ] },
	    { name: 'tools', items : [ 'Maximize', 'ShowBlocks','-','About' ] }
	];
	 
	config.toolbar_Basic =
	[
	    ['Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink','-','About']
	]; 
	
};
