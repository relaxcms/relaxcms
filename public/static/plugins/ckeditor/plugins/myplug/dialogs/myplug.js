(function () {
  function myplugDialog(editor) {
  return {
   title: 'Who does you want to say hello?', //窗口标题
   minWidth: 300,
   minHeight: 80,
   buttons: [{
    type: 'button',
    id: 'someButtonID',
    label: 'Button',
    onClick: function () {
     alert('This is a custome button');
    }
    //对话框底部的自定义按钮
   },
   CKEDITOR.dialog.okButton, //对话框底部的确定按钮
   CKEDITOR.dialog.cancelButton], //对话框底部的取消按钮
   contents:      //每一个contents在对话框中都是一个tab页
   [
    {
     id: 'user',   //contents的id
     label: 'You name',
     title: 'You name',
     elements:    //定义contents中的内容，我们这里放一个文本框，id是name
     [
      {
       id: 'name',
       type: 'text',
       style: 'width: 50%;',
       label: 'You name',
      }
     ]
    }
   ],
   onLoad: function () {
    //alert('onLoad');
   },
   onShow: function () {
    //alert('onShow');
   },
   onHide: function () {
    //alert('onHide');
   },
   onOk: function () {
    //点击 确定 按钮之后触发的事件
    var name = this.getValueOf( 'user', 'name' );
    //从界面上取值的方法，getValueOf( 'contents的id', '控件的id' )
    editor.insertHtml('<p>' + name + ' : Hello world!' + '</p>');
    //将内容放入到editor
    this.commitContent();
   },
   onCancel: function () {
    //alert('onCancel');
   },
   resizable: CKEDITOR.DIALOG_RESIZE_HEIGHT
  };
 }
 CKEDITOR.dialog.add('myplugDialog', function (editor) {
  return myplugDialog(editor);
 });
})();