(function () {
  
  function attachDialog(editor) {
  return {
   title: '选择附件', //窗口标题
   minWidth: 400,
   minHeight: 100,
   buttons: [
   CKEDITOR.dialog.okButton, //对话框底部的确定按钮
   CKEDITOR.dialog.cancelButton], //对话框底部的取消按钮
   contents:      //每一个contents在对话框中都是一个tab页
   [ {
     id: 'attachContent',   //contents的id
     label: 'attach',
     title: 'attach',
     elements:    //定义contents中的内容，我们这里放一个文本框，id是name
     [{ type: 'vbox', 
        padding: 0, 
        children: [ {
          type: 'hbox',
          widths: [ '280px', '110px' ],
          align: 'right',
          children: [ {
              id: 'ids',
              type: 'text',
              label: '附件',
            },
            {
             id: 'browse',
             type: 'button',
             label: '浏览服务器',
             style: 'display:inline-block;margin-top:14px;',
             onClick:function(e) {
              var dialog = this.getDialog();
                  console.log("onClick..., filebrowserVideoUrl="+editor.config.filebrowserAttachUrl);
                  var _self = this;
                  var txtUrl = $("#info:txtUrl");

                        layer.open({
                            type: 2,
                            title: '选择附件',
                            shadeClose: false,
                            shade: 0.2,
                            shift:10,
                            area: ['55%', '70%'],
                            content: editor.config.filebrowserAttachUrl,
                            btn:['确定', '关闭'],
                            yes: function(index, layero) {
                                //console.log('in yes...');
                                var iframeWin = window["layui-layer-iframe" + index];
                                var rows = iframeWin.rows;
                                console.log(rows);
                                var row = rows[0];
                                //console.log(row.previewUrl);
                                //console.log(row.downloadUrl);

                                //editor.insertHtml( row.previewUrl );
                                var el = dialog.getContentElement( 'attachContent', 'ids' );
                                el.setValue(row.id );                                
                                layer.close(index);
                                //console.log('out yes'); 
                            }
                        });
             }

            }]
        }]
      }]
    }],    
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
    var id = this.getValueOf( 'attachContent', 'ids' );
    //从界面上取值的方法，getValueOf( 'contents的id', '控件的id' )
    var text = "[attach]"+id+"[/attach]";;
    if (text) {
      editor.insertHtml(text+' ');
      //将内容放入到editor
      this.commitContent();
    }
   },
   onCancel: function () {
    //alert('onCancel');
   },
   resizable: CKEDITOR.DIALOG_RESIZE_HEIGHT
  };
 }
 CKEDITOR.dialog.add('attachDialog', function (editor) {
  return attachDialog(editor);
 });
})();