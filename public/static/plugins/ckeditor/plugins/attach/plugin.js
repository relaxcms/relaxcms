(function(){
 name='attach';
 CKEDITOR.plugins.add(name, {
  init:function(editor){
  CKEDITOR.dialog.add('attachDialog', this.path + 'dialogs/attach.js');
  editor.addCommand(name, new CKEDITOR.dialogCommand('attachDialog')); //注意myplug名字
   editor.ui.addButton(name, {
    label:'插入附件',
    icon: this.path + 'icons/attach.png',
    command: name
   });
  }
 });
})();