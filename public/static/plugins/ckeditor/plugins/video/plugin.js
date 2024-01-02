(function(){
 //Section 1 : 按下自定义按钮时执行的代码
 var a= {
  exec:function(editor){
   alert("This a custome button!");
  }
 },
 //Section 2 : 创建自定义按钮、绑定方法
 b='video'; //注意myplug名字
 CKEDITOR.plugins.add(b,{
  init:function(editor){
   CKEDITOR.dialog.add('videoDialog', this.path + 'dialogs/video.js'); //注意myplug名字
   editor.addCommand('video', new CKEDITOR.dialogCommand('videoDialog')); //注意myplug名字
   //注意myplug名字 和 图片路径
   editor.ui.addButton('video',{
    label:'插件视频',
    icon: this.path + 'icons/video.png',
    command:b
   });
  }
 });
})();