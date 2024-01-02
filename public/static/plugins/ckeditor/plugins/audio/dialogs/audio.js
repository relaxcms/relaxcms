(function () {
  var createaudioNode = function (url) {
      var mp3RegExp = /^.+.(mp3)$/;
      var mp3Match = url.match(mp3RegExp);

      var $audio;
      if (mp3Match ) {
        $audio = $('<audio controls>')
            .attr('src', url)
            .attr('width', '640').attr('height', '60');
      } else {
        // this is not a known video link. Now what, Cat? Now what?
        return false;
      }

      $audio.addClass('note-audio-clip');
      return $audio.prop("outerHTML");
    }

  function audioDialog(editor) {
  return {
   title: '选择音频', //窗口标题
   minWidth: 400,
   minHeight: 100,
   buttons: [
   CKEDITOR.dialog.okButton, //对话框底部的确定按钮
   CKEDITOR.dialog.cancelButton], //对话框底部的取消按钮
   contents:      //每一个contents在对话框中都是一个tab页
   [ {
     id: 'audioContent',   //contents的id
     label: 'audioourl',
     title: 'audiourl',
     elements:    //定义contents中的内容，我们这里放一个文本框，id是name
     [{ type: 'vbox', 
        padding: 0, 
        children: [ {
          type: 'hbox',
          widths: [ '280px', '110px' ],
          align: 'right',
          children: [ {
              id: 'url',
              type: 'text',
              label: 'URL',
            },
            {
             id: 'browse',
             type: 'button',
             label: '浏览服务器',
             style: 'display:inline-block;margin-top:14px;',
             onClick:function(e) {
              var dialog = this.getDialog();
                  console.log("onClick..., filebrowserAudioUrl="+editor.config.filebrowserAudioUrl);
                  var _self = this;
                  var txtUrl = $("#info:txtUrl");

                        layer.open({
                            type: 2,
                            title: '选择图片',
                            shadeClose: false,
                            shade: 0.2,
                            shift:10,
                            area: ['55%', '70%'],
                            content: editor.config.filebrowserAudioUrl,
                            btn:['确定', '关闭'],
                            yes: function(index, layero) {
                                //console.log('in yes...');
                                var iframeWin = window["layui-layer-iframe" + index];
                                var row = iframeWin.row;
                                console.log(row.previewUrl);
                                //editor.insertHtml( row.previewUrl );
                                var el = dialog.getContentElement( 'audioContent', 'url' );
                                el.setValue(row.previewUrl );                                
                                layer.close(index);
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
    var url = this.getValueOf( 'audioContent', 'url' );
    //从界面上取值的方法，getValueOf( 'contents的id', '控件的id' )
    var text = createaudioNode(url);

    console.log(text);

    editor.insertHtml(text+' '+url);
    //将内容放入到editor
    this.commitContent();
   },
   onCancel: function () {
    //alert('onCancel');
   },
   resizable: CKEDITOR.DIALOG_RESIZE_HEIGHT
  };
 }
 CKEDITOR.dialog.add('audioDialog', function (editor) {
  return audioDialog(editor);
 });
})();