(function(){
 
 var name='audio';
 
 CKEDITOR.plugins.add(name,{
  init:function(editor){
   CKEDITOR.dialog.add('audioDialog', this.path + 'dialogs/audio.js'); 
   editor.addCommand(name, new CKEDITOR.dialogCommand('audioDialog')); 
   editor.ui.addButton(name, {
    label:'插入音频',
    icon: this.path + 'icons/audio.png',
    command:name
   });
  }
 });
})();