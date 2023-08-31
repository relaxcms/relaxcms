(function(){
	var name = 'html5audio';
    CKEDITOR.plugins.add( 'html5audio', {
    init: function( editor ) {
    	console.log("in init...");        
        CKEDITOR.dialog.add( 'html5audioDialog', this.path + 'dialogs/html5audio.js' );
        editor.addCommand('html5audio', new CKEDITOR.dialogCommand('html5audioDialog'));
        console.log("init audio plugin........");
        editor.ui.addButton( 'html5audio',{
			label: "Audio",			
			icon: this.path + 'myplug.png',
			command :name
		});
    }
  });
})();