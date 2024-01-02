(function () {
  var createVideoNode = function (url) {
      // video url patterns(youtube, instagram, vimeo, dailymotion, youku, mp4, ogg, webm)
      var ytRegExp = /^(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/;
      var ytMatch = url.match(ytRegExp);

      var igRegExp = /(?:www\.|\/\/)instagram\.com\/p\/(.[a-zA-Z0-9_-]*)/;
      var igMatch = url.match(igRegExp);

      var vRegExp = /\/\/vine\.co\/v\/([a-zA-Z0-9]+)/;
      var vMatch = url.match(vRegExp);

      var vimRegExp = /\/\/(player\.)?vimeo\.com\/([a-z]*\/)*([0-9]{6,11})[?]?.*/;
      var vimMatch = url.match(vimRegExp);

      var dmRegExp = /.+dailymotion.com\/(video|hub)\/([^_]+)[^#]*(#video=([^_&]+))?/;
      var dmMatch = url.match(dmRegExp);

      var youkuRegExp = /\/\/v\.youku\.com\/v_show\/id_(\w+)=*\.html/;
      var youkuMatch = url.match(youkuRegExp);

      var mp4RegExp = /^.+.(mp4|m4v)$/;
      var mp4Match = url.match(mp4RegExp);

      var oggRegExp = /^.+.(ogg|ogv)$/;
      var oggMatch = url.match(oggRegExp);
      
      var hlsRegExp = /^.+.m3u8$/;
      var hlsMatch = url.match(hlsRegExp);

      var webmRegExp = /^.+.(webm)$/;
      var webmMatch = url.match(webmRegExp);
      
      //match ixiqua
      //var ixiguaUrl = "https://www.ixigua.com/6936104216297472524";
      var ixiguaExp = /https:\/\/www.ixigua.com\/(\d+)/;
      var ixiguaMatch = url.match(ixiguaExp);


      var $video;
      if (ytMatch && ytMatch[1].length === 11) {
        var youtubeId = ytMatch[1];
        $video = $('<iframe>')
            .attr('frameborder', 0)
            .attr('src', '//www.youtube.com/embed/' + youtubeId)
            .attr('width', '640').attr('height', '360');
      } else if (igMatch && igMatch[0].length) {
        $video = $('<iframe>')
            .attr('frameborder', 0)
            .attr('src', 'https://instagram.com/p/' + igMatch[1] + '/embed/')
            .attr('width', '612').attr('height', '710')
            .attr('scrolling', 'no')
            .attr('allowtransparency', 'true');
      } else if (vMatch && vMatch[0].length) {
        $video = $('<iframe>')
            .attr('frameborder', 0)
            .attr('src', vMatch[0] + '/embed/simple')
            .attr('width', '600').attr('height', '600')
            .attr('class', 'vine-embed');
      } else if (vimMatch && vimMatch[3].length) {
        $video = $('<iframe webkitallowfullscreen mozallowfullscreen allowfullscreen>')
            .attr('frameborder', 0)
            .attr('src', '//player.vimeo.com/video/' + vimMatch[3])
            .attr('width', '640').attr('height', '360');
      } else if (dmMatch && dmMatch[2].length) {
        $video = $('<iframe>')
            .attr('frameborder', 0)
            .attr('src', '//www.dailymotion.com/embed/video/' + dmMatch[2])
            .attr('width', '640').attr('height', '360');
      } else if (youkuMatch && youkuMatch[1].length) {
        $video = $('<iframe webkitallowfullscreen mozallowfullscreen allowfullscreen>')
            .attr('frameborder', 0)
            .attr('height', '498')
            .attr('width', '510')
            .attr('src', '//player.youku.com/embed/' + youkuMatch[1]);
      } else if (ixiguaMatch && ixiguaMatch[1].length) {
      //for ixiqua
        $video = $('<iframe webkitallowfullscreen mozallowfullscreen allowfullscreen>')
            .attr('frameborder', 0)
            .attr('width', '100%')
            .attr('height', '405')
            .attr('src', 'https://www.ixigua.com/iframe/' + ixiguaMatch[1]+"?autoplay=0&startTime=0");
      } else if (mp4Match || oggMatch || webmMatch || hlsMatch) {
        var type = "video/mp4";
        if (oggMatch) {
          type = "video/oog";
        } else if (webmMatch) {
          type = "video/webm";
        } else if (hlsMatch) {
          type = "application/x-mpegURL";
        }
        $video = $('<video id="my-video" class="myvideoplayer video-js vjs-default-skin vjs-big-play-centered" controls>');            
        $video.append('<source src="'+url+'" type="'+type+'">');
      } else {
      
        // this is not a known video link. Now what, Cat? Now what?
        return false;
      }

      $video.addClass('note-video-clip');
      return $video.prop("outerHTML");
    }

  function videoDialog(editor) {
  return {
   title: '选择视频', //窗口标题
   minWidth: 400,
   minHeight: 100,
   buttons: [
   CKEDITOR.dialog.okButton, //对话框底部的确定按钮
   CKEDITOR.dialog.cancelButton], //对话框底部的取消按钮
   contents:      //每一个contents在对话框中都是一个tab页
   [ {
     id: 'videoContent',   //contents的id
     label: 'videourl',
     title: 'videourl',
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
                  console.log("onClick..., filebrowserVideoUrl="+editor.config.filebrowserVideoUrl);
                  var _self = this;
                  var txtUrl = $("#info:txtUrl");

                        layer.open({
                            type: 2,
                            title: '选择视频',
                            shadeClose: false,
                            shade: 0.2,
                            shift:10,
                            area: ['55%', '70%'],
                            content: editor.config.filebrowserVideoUrl,
                            btn:['确定', '关闭'],
                            yes: function(index, layero) {
                                //console.log('in yes...');
                                var iframeWin = window["layui-layer-iframe" + index];
                                var row = iframeWin.row;
                                //console.log(row.previewUrl);
                                //console.log(row.downloadUrl);

                                //editor.insertHtml( row.previewUrl );
                                var el = dialog.getContentElement( 'videoContent', 'url' );
                                el.setValue(row.playurl );                                
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
    var url = this.getValueOf( 'videoContent', 'url' );
    //从界面上取值的方法，getValueOf( 'contents的id', '控件的id' )
    var text = createVideoNode(url);
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
 CKEDITOR.dialog.add('videoDialog', function (editor) {
  return videoDialog(editor);
 });
})();