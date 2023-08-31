<?php

class CUIComponent extends CComponent
{
	/**
	 *  模板
	 */
	protected $_tpl;
	
	/**
	 * 模板上级目录
	 *
	 * @var mixed 
	 *
	 */
	protected $_ptdir;
	
	/**
	 * 模板目录
	 *  
	 */
	protected $_tdir;
	
	
	/**
	 * 默认模板目录
	 * 
	 *
	 * @var mixed 
	 *
	 */
	protected $_default_tdir;
	
	protected $_sbt = false;
	
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
		$this->_tpl = $name;		
	}
	
	function CUIComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	protected function _init()
	{
		parent::_init();
				
		$tplname = isset($this->_options['tplname'])?$this->_options['tplname']:'default';
		$compath = dirname($this->_options['classfile']);		
		$ptdir = dirname($compath).DS.'templates';
		//ptdir
		$this->_ptdir = $ptdir;		
		//模板目录
		$this->_tdir = $ptdir.DS.$tplname;
		//默认目录
		$this->_default_tdir = $ptdir.DS."default";
	}
		
	
	/* ===============================================================================
	 * JS/CSS Functions
	 * =============================================================================*/
	
	protected function initJSCSS(&$ioparams=array())
	{
		//$browser = get_browser(null, true);
		
		$langname = $ioparams['_lang'];
		$thename = $ioparams['_thename'];
		$tplname = $ioparams['_tplname'];
		
		$r = Factory::GetRequest();
		$leie9 = $r->isLeIE9();
		
		//css列表
		$jscssdb = array(
				'plugins'=>array(
					'fixedbrowser_leie9'=> array(
						'enable'=>$leie9,
						'css' => array(
							'core'=>'',
							),
						'js' => array(
							'respond'=>'respond.min.js',
							'excanvas'=>'excanvas.min.js',			
							),
						),
					
					'font_awesome'=> array(
						'name'=>'FontAwesome',
						'version'=>'4.4.0',						
						'description'=>'Font Awesome 4.4.0 by @davegandy - http://fontawesome.io - @fontawesome',
						'licensename'=>'SIL&MIT',
						'licenseurl'=>'http://fontawesome.io/license',
						'showlicense'=>true,
						
						'enable'=>false,
						'css' => array(
							'core'=>'font-awesome/css/font-awesome.min.css',
							),
						'js' => array(),
						),
					'simple_line_icons'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'simple-line-icons/simple-line-icons.min.css',
							),
						'js' => array(),
						),
					
					'jquery'=> array(
						'name'=>'jQuery',
						'version'=>'1.12.4',						
						'description'=>'jQuery JavaScript Library',
						'licensename'=>'MIT',
						'licenseurl'=>'https://jquery.org/license/',
						'showlicense'=>true,
						
						'enable'=>false,
						'css' => array(),
						'js' => array(
							'core'=>'jquery.min.js',
							),
						),
					//应用迁移辅助插件（jQuery高级版本兼容低级版本辅助插件）
					'jquery_migrate'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'',
							),
						'js' => array(
							'core'=>'jquery-migrate.min.js',
							),
						),
					
					'jquery_ui'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'',
							),
						'js' => array(
							'core'=>'jquery-ui/jquery-ui.min.js',
							),
						),
					'bootstrap'=> array(
						'name'=>'Bootstrap',
						'version'=>'3.3.7',						
						'description'=>'Bootstrap JavaScript Library',
						'licensename'=>'MIT',
						'licenseurl'=>'https://github.com/twbs/bootstrap/blob/master/LICENSE',
						'showlicense'=>true,
						
						'enable'=>false,
						'css' => array(
							'core'=>'bootstrap/css/bootstrap.min.css',
							),
						'js' => array(
							'core'=>'bootstrap/js/bootstrap.min.js',
							),
						),
					
					
					'jquery_slimscroll'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'',
							),
						'js' => array(
							'core'=>'jquery-slimscroll/jquery.slimscroll.min.js',
							),
						),
					
					'jquery_blockui'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'',
							),
						'js' => array(
							'core'=>'jquery.blockui.min.js',
							),
						),
					
					'jquery_cokie'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'',
							),
						'js' => array(
							'core'=>'jquery.cokie.min.js',
							),
						),/*
										
					'icheck'=> array(
					'enable'=>false,
					'css' => array(
						'core'=>'icheck/skins/all.css',
						),
					'js' => array(
						'core'=>'icheck/icheck.min.js',
						),
					),
					'jquery_uniform'=> array(
					'enable'=>false,
					'css' => array(
						'core'=>'uniform/css/uniform.default.css',
						),
					'js' => array(
						'core'=>'uniform/jquery.uniform.min.js',
						),
					),*/
					
					'jquery_validation'=>array(
						'enable'=>false,
						'css' => array(
							'core'=>'',
							),
						'js' => array(
							'core'=>'jquery-validation/js/jquery.validate.min.js',
							),
						),
					
					'jquery_form'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'',
							),
						'js' => array(
							'core'=>'jquery.form.min.js',
							),
						),
					'jquery_backstretch'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'',
							),
						'js' => array(
							'core'=>'backstretch/jquery.backstretch.min.js',
							),
						),
					
					
					'bootstrap_switch'=> array(
						'enable'=>true,
						'css' => array(
							'core'=>'bootstrap-switch/css/bootstrap-switch.min.css',
							),
						'js' => array(
							'core'=>'bootstrap-switch/js/bootstrap-switch.min.js',
							),
						),
					
					
					'bootstrap_toastr'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'bootstrap-toastr/toastr.min.css',
							),
						'js' => array(
							'core'=>'bootstrap-toastr/toastr.min.js',
							),
						),
					
					'bootstrap_hover_dropdown'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'',
							),
						'js' => array(
							'core'=>'bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js'
							),
						),
					
					'tagsinput'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'bootstrap-tagsinput/bootstrap-tagsinput.css',
							'typeahead'=>'bootstrap-tagsinput/bootstrap-tagsinput-typeahead.css',
							),
						'js' => array(
							'core'=>'bootstrap-tagsinput/bootstrap-tagsinput.min.js',
							'typeahead'=>'typeahead/handlebars.min.js',
							'typeahead_bundle'=>'typeahead/typeahead.bundle.min.js',
							),
						),
					
					
					'typeahead'=> array(
						'enable'=>false,
						'css' => array(
							'typeahead'=>'typeahead/typeahead.css',
							),
						'js' => array(
							'typeahead'=>'typeahead/handlebars.min.js',
							'typeahead_bundle'=>'typeahead/typeahead.bundle.min.js',
							),
						),
					
					
					//<script src="../assets/pages/scripts/components-multi-select.min.js" type="text/javascript"></script>
					'multiselect'=> array(
						'enable' => false,
						'css' => array(
							'core'=>'bootstrap-select/css/bootstrap-select.min.css',
							'jquery-multi-select'=>'jquery-multi-select/css/multi-select.css',
							),
						'js' => array(
							'core'=>'bootstrap-select/js/bootstrap-select.min.js',
							'jquery_multi-select'=>'jquery-multi-select/js/jquery.multi-select.js',
							),
						),
					
					'datepicker'=> array(
						'enable' => false,
						'css' => array(
							'core'=>'bootstrap-datepicker/css/bootstrap-datepicker3.min.css',
							),
						'js' => array(
							'core'=>'bootstrap-datepicker/js/bootstrap-datepicker.min.js',
							'locale-zh-CN'=>'bootstrap-datepicker/locales/bootstrap-datepicker.zh-CN.min.js',
							),
						),
					'datetimepicker'=> array(
						'enable' => false,
						'css' => array(
							'core'=>'bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css',
							),
						'js' => array(
							'core'=>'bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js',
							'locale-zh-CN'=>'bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js',
							),
						),
					'timepicker'=> array(
						'enable' => false,
						'css' => array(
							'core'=>'bootstrap-timepicker/css/bootstrap-timepicker.min.css',
							),
						'js' => array(
							'core'=>'bootstrap-timepicker/js/bootstrap-timepicker.min.js',
							'locale-zh-CN'=>'bootstrap-timepicker/js/locales/bootstrap-timepicker.zh-CN.js',
							),
						),
					
					
					
					'select2'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'select2/select2.css',
							),
						'js' => array(
							'core'=>'select2/select2.min.js',
							),
						),
					
					'bootbox'=> array(
						'enable'=>true,
						'css' => array(
							'core'=>'',
							),
						'js' => array(
							'core'=>'bootbox/bootbox.min.js',
							),
						),
					
					'underscore'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'',
							),
						'js' => array(
							'core'=>'underscore.js',
							),
						),
					
					
					
					'jquery_fileupload'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'jquery-file-upload/css/jquery.fileupload.css',
							'fileupload_ui'=>'jquery-file-upload/css/jquery.fileupload-ui.css',
							'blueimp'=>'jquery-file-upload/blueimp-gallery/blueimp-gallery.min.css',
							'ladda'=>'ladda/ladda-themeless.min.css',
							
							),
						'js' => array(
							'widget'=>'jquery-file-upload/js/vendor/jquery.ui.widget.js',
							'ladda_spin'=>'ladda/spin.min.js',
							'ladda_ladda'=>'ladda/ladda.min.js',
							'transport'=>'jquery-file-upload/js/jquery.iframe-transport.js',
							'fileupload'=>'jquery-file-upload/js/jquery.fileupload.js',
							
							//'tmpl'=>'jquery-file-upload/js/vendor/tmpl.min.js',
							'load-image'=>'jquery-file-upload/js/vendor/load-image.min.js',
							'fileupload-ui'=>'jquery-file-upload/js/jquery.fileupload-ui.js',
							'blueimp'=>'jquery-file-upload/blueimp-gallery/jquery.blueimp-gallery.min.js',
							'fileupload-process'=>'jquery-file-upload/js/jquery.fileupload-process.js',
							'fileupload-image'=>'jquery-file-upload/js/jquery.fileupload-image.js',
							'fileupload-audio'=>'jquery-file-upload/js/jquery.fileupload-audio.js',
							'fileupload-video'=>'jquery-file-upload/js/jquery.fileupload-video.js',
							'fileupload-validate'=>'jquery-file-upload/js/jquery.fileupload-validate.js',
							
							),
						),
					
					'fancybox'=> array(
						'enable'=>false,
						'css' => array(
							'fancybox'=>'fancybox/source/jquery.fancybox.css'),
						'js' => array(
							'mixitup'=>'jquery-mixitup/jquery.mixitup.min.js',
							'fancybox'=>'fancybox/source/jquery.fancybox.pack.js',
							),
						),
					'gtreetable'=> array(
						'enable'=>false,
						'css' => array(
							'gtreetable'=>'bootstrap-gtreetable/bootstrap-gtreetable.min.css'),
						'js' => array(
							'gtreetable'=>'bootstrap-gtreetable/bootstrap-gtreetable.min.js',
							),
						),
					
					'datatables'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'datatables/plugins/bootstrap/dataTables.bootstrap.css',
							),
						'js' => array(
							'core'=>'datatables/media/js/jquery.dataTables.min.js',
							'dt_tableTools'=>'datatables/extensions/TableTools/js/dataTables.tableTools.min.js',
							'dt_colreorder'=>'datatables/extensions/ColReorder/js/dataTables.colReorder.min.js',
							'dt_scroller'=>'datatables/extensions/Scroller/js/dataTables.scroller.min.js',
							'dt_bootstrap'=>'datatables/plugins/bootstrap/dataTables.bootstrap.js',
							),
						),
					/*
					'datatables'=> array(
					'enable'=>false,
					'css' => array(
						'core'=>'datatables/datatables.min.css',
						'datatables_bootstrap'=>'datatables/plugins/bootstrap/datatables.bootstrap.css',
						),
					'js' => array(
						'core'=>'datatables/datatables.all.min.js',
						'datatables_bootstrap'=>'datatables/plugins/bootstrap/datatables.bootstrap.js',
						),
					),*/
					
					'bootstraptable'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'bootstrap-table/bootstrap-table.min.css',
							),
						'js' => array(
							'core'=>'bootstrap-table/bootstrap-table2.js',
							'bootstrap-table-treegrid'=>'bootstrap-table/extensions/treegrid/bootstrap-table-treegrid.min.js',
							),
						),
					'dropzone'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'dropzone/css/dropzone.css',
							),
						'js' => array(
							'core'=>'dropzone/dropzone.min.js',
							),
						),	
					
					
					'treegrid'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'jquery-treegrid/css/jquery.treegrid.css',
							),
						'js' => array(
							'core'=>'jquery-treegrid/js/jquery.treegrid.min.js',
							),
						),	
					
					'ckeditor'=> array(
						'enable'=>false,
						'css' => array(),
						'js' => array(
							'core'=>'ckeditor/ckeditor.js',
							),
						),	
					
					//summernote
					'sneditor'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'bootstrap-summernote/summernote.css',
							),
						
						'js' => array(
							'core'=>'bootstrap-summernote/summernote.min.js',
							),
						),
					
					//videojs
					'videojs'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'videojs/css/videojs.css',
							),
						
						'js' => array(
							'core'=>'videojs/js/videojs.js',
							),
						),
					
					
					'crypto'=> array(
						'enable'=>false,
						'css' => array(),
						'js' => array(
							'core'=>'crypto-js.js',
							),
						),	
					'layer'=> array(
						'name'=>'LayUI',
						'version'=>'3.1.1',						
						'description'=>'Web弹层组件 http://layer.layui.com/',
						'licensename'=>'MIT',
						'licenseurl'=>'https://gitee.com/sentsin/layui/blob/master/LICENSE',
						'showlicense'=>true,
						
						'enable'=>false,
						'css' => array(),
						'js' => array(
							'core'=>'layer/layer.js',
							),
						),
					
					'jcrop'=> array(
						'name'=>'jcrop',
						'version'=>'0.9.12',						
						'description'=>'Query Image Cropping Plugin - released under MIT License',
						'licensename'=>'MIT',
						'licenseurl'=>'http://github.com/tapmodo/Jcrop',
						'showlicense'=>true,
						
						'enable'=>false,
						'css' => array(
							'jcrop'=>'jcrop/css/jquery.Jcrop.min.css'),
						'js' => array(
							'jcrop_color'=>'jcrop/js/jquery.color.js',
							'jcrop'=>'jcrop/js/jquery.Jcrop.min.js',
							),
						),	
					
					
					'bgallery'=> array(
						'name'=>'Gallery',
						'version'=>'3.4.0',						
						'description'=>'blueimp Gallery is a touch-enabled, responsive and customizable image and video
							gallery',
						'licensename'=>'MIT',
						'licenseurl'=>'https://github.com/blueimp/Gallery',
						'showlicense'=>true,
						
						'enable'=>false,
						'css' => array(
							//'gallery'=>'blueimp-gallery/css/blueimp-gallery.min.css'
							),
						'js' => array(
							'gallery'=>'blueimp-gallery/js/blueimp-gallery.min.js',
							//'jquery_gallery'=>'jcrop/js/jquery.blueimp-gallery.min.js',
							),
						),
						
					'bootstrapswitch'=> array(
						'name'=>'Bootstrap-switch',
						'version'=>'3.3.2',						
						'description'=>'Turn checkboxes and radio buttons in toggle switches',
						'licensename'=>'APACHE 2.0',
						'licenseurl'=>'http://www.apache.org/licenses/LICENSE-2.0',
						'showlicense'=>true,						
						'enable'=>false,
						'css' => array(
							'gallery'=>'bootstrap-switch/css/bootstrap-switch.min.css'),
						'js' => array(
							'gallery'=>'bootstrap-switch/js/bootstrap-switch.min.js',
							),
						),
						
					'treenav'=> array(
						'name'=>'treenav',
						'version'=>'0.1.0',						
						'description'=>'treenav',
						'licensename'=>'MIT',
						'licenseurl'=>'http://github.com/relaxcms/treenav',
						'showlicense'=>false,
						
						'enable'=>false,
						'css' => array(
							'treenav'=>'treenav/css/treenav.css'),
						'js' => array(
							'treenav'=>'treenav/js/treenav.js',
							),
						),	
						
						/* amcharts5 https://github.com/amcharts/amcharts5*/						
						'amcharts5'=> array(
							'name'=>'amcharts5',
							'version'=>'5.1.12',						
							'description'=>'amCharts 5 is the fastest, most advanced amCharts data vizualization library, ever.',
							'licensename'=>'amCharts',
							'licenseurl'=>'https://github.com/amcharts/amcharts5',
							'showlicense'=>false,							
							'enable'=>false,
							'css' => array(
								'amcharts5'=>'amcharts5/css/amcharts5.css'),
							'js' => array(
								'amcharts5'=>'amcharts5/index.js',
								'amcharts5_xy'=>'amcharts5/xy.js',
								'amcharts5_Animated'=>'amcharts5/themes/Animated.js',
								'amcharts5_zh_Hans'=>'amcharts5/locales/zh_Hans.js',
								),
						),
						
					//touchspin						
					'touchspin'=> array(
						'name'=>'touchspin',
						'version'=>'3.0.1',						
						'description'=>'Bootstrap TouchSpin',
						'licensename'=>'Apache2.0',
						'licenseurl'=>'http://www.apache.org/licenses/LICENSE-2.0',
						'showlicense'=>true,						
						'enable'=>false,
						'css' => array(
							'touchspin'=>'bootstrap-touchspin/bootstrap.touchspin.min.css'),
						'js' => array(
							'touchspin'=>'bootstrap-touchspin/bootstrap.touchspin.min.js',
							),
						),
						
					),				
				
				
				'global' => array(					
					'encrypt'=> array(
						'enable'=>false,
						'css' => array(),
						'js' => array(
							'core'=>'js/encrypt.min.js',
							),
						),
					
					'datatable'=> array(
						'enable'=>false,
						'css' => array(),
						'js' => array(
							'core'=>'js/datatable.min.js',
							),
						),
					'fileview'=> array(
						'enable'=>false,
						'css' => array(
							'core'=> 'css/fileview.min.css'
							),
						'js' => array(),
						),
					
					'tileupload'=> array(
						'enable'=>false,
						'css' => array(
							//'core'=>'css/tileupload.min.css',
							),
						'js' => array(
							'core'=>'js/tileupload.min.js',
							),
						),
					
					'fileupload'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'css/fileupload.min.css',
							),
						'js' => array(
							'core'=>'js/fileupload.min.js',
							),
						),
					'fileselector'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'css/fileselector.min.css',
							),
						'js' => array(
							'core'=>'js/fileselector.min.js',
							),
						),
					
					'gallery'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'css/gallery.min.css',
							),
						'js' => array(
							'core'=>'js/gallery.js',
							),
						),
						
					'cropimg'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'css/cropimg.min.css',
							),
						'js' => array(
							'core'=>'js/cropimg.min.js',
							),
						),	
					),
				);
		
		
		$this->_jscssdb = $jscssdb;
	}
	
	public function enableJSCSS($jscssmodules=array(), $enable=true)
	{
		if (!is_array($jscssmodules))
			$jscssmodules = explode(',', $jscssmodules);
		
		
		foreach ($jscssmodules as $key => $name) {
			
			$pkey = '';		
			foreach ($this->_jscssdb as $k2 => $v2) {
				
				if (isset($v2[$name])) {
					
					$pkey = $k2;
					break;
				}
			}
			
			if ($pkey)
				$this->_jscssdb[$pkey][$name]['enable'] = $enable;
		}
		
		return true;
	}
	
	protected function getJSCSSDB(&$cssdb, &$jsdb, &$ioparams=array())
	{
		$cssdb = array();
		$jsdb = array();
		
		$_dstroot = $ioparams['_dstroot'];
		$_theroot = $ioparams['_theroot'];
		$basedirs = array(
				'root'=>$_dstroot.'',
				'plugins'=>$_dstroot.'/plugins',
				'global' =>$_dstroot,
				);
		
		
		foreach($this->_jscssdb as $key=>$v)
		{
			//模块
			$bdir = $basedirs[$key];
			foreach ($v as $k2 => $m) {
				if ($m['enable']) {
					
					//css, js
					if (isset($m['css'])) {
						foreach ($m['css'] as $k3=> $v3) {
							if($v3) {
								$_newkey = 'css_'.$k2.'_'.$k3;
								$cssdb[$_newkey] = $bdir.'/'.$v3;
							}
						}						
					}
					
					if (isset($m['js'])) {
						
						foreach ($m['js'] as $k3 => $v3) {
							if($v3) {
								$_newkey = 'js_'.$k2.'_'.$k3;
								$jsdb[$_newkey] = $bdir.'/'.$v3;
							}
						}						
					}
					
				}
			}			
		}
		return true;
	}
	
	/* ===============================================================================
	 * TAB Functions
	 * =============================================================================*/
	
	protected function initActiveTab($nr, $force_active_id=-1, $selector='')
	{
		$tabs = initActiveTab($nr, $force_active_id);
		foreach ($tabs as $key => $v) {
			$this->assign('navtab'.$v['id'], $v);
		}		
		
		if ($selector) {
			$sdb = get_i18n($selector);
			foreach ($tabs as $key => &$v) {
				if (isset($sdb[$v['id']]))
					$v['title'] = $sdb[$v['id']];
			}
		}
		
		$this->assign('navtabs', $tabs);
		
		return $tabs;
	}
	
	protected function setHistory($ioparams)
	{
		$m = Factory::GetModel('history');
		$m->setHistory($ioparams);
		return true;
	}
	
	
	/* ===============================================================================
	 * TASK Functions
	 * =============================================================================*/
	
	protected function show(&$ioparams=array())
	{
		$this->setHistory($ioparams);
		return true;
	}
	
	//nopic/w/h
	protected function nopic(&$ioparams=array())
	{
		$offset = $ioparams['vpath_offset'];
		$width = isset($ioparams['vpath'][$offset])?$ioparams['vpath'][$offset++]:640;
		$height = isset($ioparams['vpath'][$offset])?$ioparams['vpath'][$offset++]:480;
		$img = Factory::GetImage();	
		$img->mknopic("请选择图片(大小:640x480)", $width, $height);	
		exit;
	}	
	
	protected function showimg(&$ioparams=array())
	{
		$offset = $ioparams['vpath_offset'];
		$width = isset($ioparams['vpath'][$offset])?$ioparams['vpath'][$offset++]:640;
		$height = isset($ioparams['vpath'][$offset])?$ioparams['vpath'][$offset++]:480;
		$img = Factory::GetImage();
		$imgfile = isset($ioparams['imgfile'])?$ioparams['imgfile']:false;
		if (!$imgfile || !is_file($imgfile)) {
			$img->mknopic("请选择图片(大小:{$width}x{$height})", $width, $height);	
		} else {
			header("Content-type: image/png");
			$res = readfile($imgfile);	
		}
		exit;
	}
	
	protected function cropimg(&$ioparams=array())
	{
		$this->setTpl("cropimg");
		$this->enableJSCSS('jcrop');
	}
	
	protected function __docropimg(&$ioparams=array())
	{
		$id = $this->_id;
		
		$x = get_int('x');
		$y = get_int('y');
		$w = get_int('w');
		$h = get_int('h');
		
		//$dstimgfile='', $target_w=128, $target_h=128
		
		$dstimgfile = isset($ioparams['dstimgfile'])?$ioparams['dstimgfile']:RPATH_CACHE.DS."cropimg.png";
		$target_w = isset($ioparams['width'])?$ioparams['width']:128;
		$target_h = isset($ioparams['height'])?$ioparams['height']:128;
		
		
		if (!$id) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no id '$id'!");
			return false;
		}
		
		if (!$dstimgfile) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no dstimgfile '$dstimgfile'!");
			return false;
		}
		
		//srcfile
		$file = Factory::GetModel('file');
		$srcfile = $file->getImagePath($id);
		if (!$srcfile) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "get getImagePath failed! id=$id!");
			return false;
		}	
		rlog(RC_LOG_ERROR, __FILE__, __LINE__, "srcfile=$srcfile!");
		$m = Factory::GetImage();
		$res = $m->cropImage($srcfile, $x, $y, $w, $h, $dstimgfile, $target_w, $target_h);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call cropImage failed!");			
		}
		
		return $res;
	}
	
	
	protected function docropimg(&$ioparams=array())
	{
		$res = $this->__docropimg($ioparams);		
		showStatus($res?0:-1);
	}
	
	protected function delcropimg(&$ioparams=array())
	{
		showStatus(-1);
	}
		
		
	protected function selectdir(&$ioparams)
	{
		$this->setTpl("selectdir");
	}
	
	protected function selectfile(&$ioparams=array())
	{
		$ioparams['dlg'] = 1;
		$this->requestInt('type', -1);
		$this->setTpl("selectfile");
	}
	
	protected function fsselect(&$ioparams=array())
	{
		$this->selectfile($ioparams);
	}
	
	protected function selectlink(&$ioparams=array())
	{
		$this->setTpl("selectlink");
		$this->_modname = 'linkcontent';
		$this->show($ioparams);
				
		return true;
	}
	
	
	
	/* ===============================================================================
	 * TPL Functions
	 * =============================================================================*/
	
	
	protected function getTplFile(&$ioparams=array())
	{
		//模版所在目录
		$tdir = $ioparams['tdir'];
				
		//模版
		$tpl = $this->_tpl;
		
		$tpl_filename = $tpl.'.htm';		
		$file = $tdir.DS.$tpl_filename;
		if (!file_exists($file)) {
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "Not found TPL '$file'!");
			$default_tdir = $ioparams['app_tdir'];
			$file = $default_tdir.DS.$tpl_filename;
			if (!file_exists($file)) {
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "Not found TPL '$file'!");		
				$default_tdir = RPATH_TEMPLATE_DEFAULT;
				$file = $default_tdir.DS.$tpl_filename;
				if (!file_exists($file)) {
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "Not found TPL '$file'!");				
					$file = $default_tdir.DS.'blank.htm';
				} else {
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING: use TPL '$file'!");	
				}
			} else {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "use TPL '$file'!");	
			}
		}
		
		if (!file_exists($file)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "Not found TPL '$file'!");
			return false;
		}
		
		//模板编译
		$t = Factory::GetTemplate();		
		$cpl_file = $t->compileTemplate($file, $ioparams, $tpl_filename);
		
		return $cpl_file;
	}
	
	
	/**
	 * 解析RDOC标记
	 *
	 * eg: <rdoc:include file="head.htm" />
	 * 
	 * @param mixed $type This is a description
	 * @param mixed $name This is a description
	 * @param mixed $attrs This is a description
	 * @return mixed This is the return value description
	 *
	 */
	protected function __parse_rdoc_tags($type, $name, $attribs, &$ioparams=array())
	{
		switch($type) {
			case "module":
				$mod = Factory::GetModule($name, $attribs);
				if($mod)
					return $mod->render($ioparams);
				break;
			default:
				break;
		}
		return false;
	}
	
	
	//解析模板
	protected function parseTemplate($data, &$ioparams=array())
	{
		$replace = array();
		$matches = array();
		
		if (($matches = matchModule($data))) {
			$nr = count($matches[1]);
			for($i=0; $i<$nr; $i++) {
				
				$attribs = attr2array( $matches[4][$i] );
				$attribs = array_merge($this->_var, $attribs);
				
				$type  = $matches[2][$i];
				
				$name  = isset($attribs['name']) ? $attribs['name'] : null;
				//$attribs['args'] = $matches[6][$i];				
				
				//合并
				$replace[$i] = $this->__parse_rdoc_tags($type, $name, $attribs, $ioparams);
			}
			
			$data = str_replace($matches[0], $replace, $data);
		}
		
		return $data;
	}
	
	
		
		
	/**
	 * 模板加载
	 *
	 * @param mixed $params This is a description
	 * @return mixed This is the return value description
	 *
	 */
	protected function loadTemplate(&$ioparams = array())
	{
		//全局准备模板所使用变量
		extract($this->_var);
		//当前方法
		extract($ioparams);
		//css and js
		$this->getJSCSSDB($cssdb, $jsdb, $ioparams);
		//$_dstroot/js/$component.js
		//rlog(RPATH_DIST.DS.'js'.DS.$component.'.js');
		$hasComponentJS = file_exists(RPATH_STATIC.DS.'js'.DS.$component.'.js')?true:false;
				
		$i18n = get_i18n();
		//语言
		extract($i18n);
		
		//常用方法名称'edit','add'
		if (isset($i18n['str_'.$this->_task]))
			$str_task = $i18n['str_'.$this->_task];
		else 
			$str_task = '';
		
		//$t and $T variable
		//t
		/*if (isset($i18n['t_'.$this->_name]))
			$t = $i18n['t_'.$this->_name];	
		else
			$t = array();	
			*/
		$t = $ioparams['_i18ndb'];
		
		$T = $i18n;
		
		//page global JS variable 'G'
		$_g = array();
		foreach($ioparams as $k=>$v) {
			if ($k[0] == '_') {
				$_g[$k] = $v; 
			}
		}
		foreach($this->_var as $k=>$v) {
			if ($k[0] == '_') {
				$_g[$k] = $v; 
			}
		}
		//for old
		$_g['webroot'] = $ioparams['_webroot'];
		$_g['base'] = $ioparams['_base'];
		$_g['basename'] = $ioparams['_basename'];
		$_g['libroot'] = $ioparams['_libroot'];
		$_g['lang'] = $ioparams['sys_lang'];
		$_g['name'] = $ioparams['sys_title'];
		$_g['title'] = $ioparams['sys_title'];
		$_g['syserror'] = $sys_error;
		$_g['component'] = $ioparams['component'];
		$_g['task'] = $ioparams['task'];
		
		$_gjson = CJson::encode($_g);		
		$sys_JS_G = "<script language='javascript'> var G = \n$_gjson;\n</script>";
		
		//当前用户
		//$_userinfo = Factory::GetApp()->getUserInfo();
		
		//id 
		$id = $this->_id;
		
		//加载
		$tpl_file = $this->getTplFile($ioparams);
		
		ob_start();		
		if (file_exists($tpl_file)) {
			require $tpl_file;
		}
		
		$contents = ob_get_contents();
		
		//去utf-8 起始符
		$contents = strim_bom($contents);		
		ob_end_clean();	
		
		//解析
		$contents = $this->parseTemplate($contents, $ioparams);
		
		return $contents;	
	}
	
	/* ===============================================================================
	 * Main Functions
	 * =============================================================================*/
	protected function initSbt(&$ioparams=array())
	{
		//检查submit
		$this->_sbt = is_sbt($this->_name);
		//模块使用
		$ioparams['issbt'] = $this->_sbt;
	}
	
	protected function initI18n(&$ioparams=array())
	{
		parent::initI18n($ioparams);
		
		$i18n = get_i18n();
		
		//common
		$_i18ndb = array();
		
		$tkey = 't_i18ndb';
		if (array_key_exists($tkey, $i18n)) 
			$_i18ndb = $i18n[$tkey];
		
		$tkey = 't_i18ndb_'.$this->_name;	
		if (array_key_exists($tkey, $i18n)) 
			$_i18ndb = array_merge($_i18ndb, $i18n[$tkey]);
		
		$key = $this->_task;
		$_i18ndb[$key] = isset($_i18ndb[$key])?$_i18ndb[$key]:$key;
		
		$ioparams['_i18ndb'] = $_i18ndb;
	}
	
	
	/**
	 * 展现之前调用初始化
	 *
	 * @param mixed $ioparams This is a description
	 * @return mixed This is the return value description
	 *
	 */
	protected function init(&$ioparams=array())
	{
		//session_cache_limiter( "private, must-revalidate" ); 
		if (!session_id())
			session_start();
		
		parent::init($ioparams);
		
		if(!isset($ioparams['tdir']))
			$ioparams['tdir'] = $this->_tdir;
		if (!isset($ioparams['default_tdir']))
			$ioparams['default_tdir'] = $this->_default_tdir;
		
		$this->initJSCSS($ioparams);	
		$this->initSbt($ioparams);	
	}
	
	protected function setSbt(&$ioparams=array())
	{
		$ioparams['sbt'] = mk_sbt($this->_name);
	}
	
	protected function fini(&$ioparams=array())
	{
		parent::fini($ioparams);
		$this->setSbt($ioparams);		
	}
	
	
	
	public function render(&$ioparams=array())
	{
		parent::render($ioparams);
		
		$data = $this->loadTemplate($ioparams);
		return $data;
	}
	
	
	protected function sendSecurityCode(&$ioparams=array())
	{
		$type = $this->requestInt('type');
		
		$account = $this->request('account');	
		$m = Factory::GetModel('user');
		$res = $m->sendSecurityCode($account,$type);
		
		showStatus($res?0:-1);
	}
	
	
	public function setMessage($options, $level=null)
	{
		!$options['msg_backurl'] && $options['msg_backurl'] =  $this->_options['_base'];
		foreach($options as $key=>$v) 
			$this->_var[$key] = $v;
		
		$this->setTpl('message');
				
		Factory::GetApplication()->cleanMessage();
	}
	
	
}