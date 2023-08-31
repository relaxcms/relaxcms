(function(){
 //Section 1 : 按下自定义按钮时执行的代码
 var a= {
  exec:function(editor){
   alert("This a custome button!");
  }
 },
 //Section 2 : 创建自定义按钮、绑定方法
 b='myplug'; //注意myplug名字
 CKEDITOR.plugins.add(b,{
  init:function(editor){
   CKEDITOR.dialog.add('myplugDialog', this.path + 'dialogs/myplug.js'); //注意myplug名字
   editor.addCommand('myplug', new CKEDITOR.dialogCommand('myplugDialog')); //注意myplug名字
   //注意myplug名字 和 图片路径
   editor.ui.addButton('myplug',{
    label:'打开我的插件',
    icon: this.path + 'myplug.png',
    command:b
   });
  }
 });
})();