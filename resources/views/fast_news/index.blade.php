<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="{{ asset('css/semantic.min.css')}}">
<script src="http://{{$_SERVER['HTTP_HOST']}}/vendor/crudbooster/assets/adminlte/plugins/jQuery/jQuery-2.1.4.min.js"></script>
<script src="{{ asset('js/semantic.min.js')}}"></script>
</head>
<body>
<style type="text/css">
    .div{
        overflow-y:auto;
    }
/*            .listitem{
        height:70px;
        position:relative;
    }
    .listitem div{
        position:absolute;
        bottom:0.3rem;
    }
*/
</style>

<div class="ui menu">
  <div class="header item">Jinse Crawl</div>
    @if($data['nav_status'] == 'block_news')
        <a href="http://{{$_SERVER['HTTP_HOST']}}/news" class="item active" >行业最新新闻</a>
    @else
        <a href="http://{{$_SERVER['HTTP_HOST']}}/news" class="item" >行业最新新闻</a>
    @endif

    @if($data['nav_status'] == 'fast_news')
        <a href="#" class="item active" >行业最新快讯</a>
    @else
        <a href="#" class="item" >行业最新快讯</a>
    @endif

    <!--
    @if($data['nav_status'] == 'wx_message')
        <a href="http://{{$_SERVER['HTTP_HOST']}}/wx/room/message/old" class="item active" >相对论信息</a>
    @else
        <a href="http://{{$_SERVER['HTTP_HOST']}}/wx/room/message/old" class="item" >相对论信息</a>
    @endif
    -->
    @if($data['nav_status'] == 'new_wx_message')
        <a href="http://{{$_SERVER['HTTP_HOST']}}/wx/room/message" class="item active" >新版相对论信息</a>
    @else
        <a href="http://{{$_SERVER['HTTP_HOST']}}/wx/room/message" class="item" >新版相对论信息</a>
    @endif
    @guest
    <a class="ui item right" href="http://{{$_SERVER['HTTP_HOST']}}/login">登陆</a>
    @else
    <a class="ui item right">{{ Auth::user()->name }}</a>
    @endguest
</div>
<div class="ui link cards">
    @foreach($data['data'] as $value)
    <div class="card company" id="{{$value['id']}}" status="{{$value['status']}}">
        <div class="content">
            @if ($value['status'] == 1)
                <a class="ui orange right ribbon label">开发中</a>
            @endif
            <div class="header requirement"><a href="{{$value['list_url']}}" target="_bank" style="letter-spacing: 0.08em;color: #000;">{{$value['company_name']}}</a></div>
                <div style="float:right">条数:<p style="float:right" class="count_num"> 0</p></div>
        </div>
        <div class="content div">
            <div class="ui relaxed divided list">
                <div class="ui prompt" style="text-align: center; margin-top: 200px;">
                  <h3 class="ui grey header">加载中...</h3>
                </div>
            </div>
        </div>
    </div>
    @endforeach

  </div>
<script type="text/javascript">

    $(function(){
        var height = $(window).height() * 0.5
        $('.div').height(height)
        $('.company').each(function () {
            if ($(this).attr('status') == 2) {
                var id = $(this).attr('id')
                getNewsByRequirement(id)
            } else {
                $(this).find('.prompt h3').text('敬请期待...')
            }
        })
    })

    function getNewsByRequirement(id) {
        $.ajax({
            type: "get",
            url: "http://webmagic.jinse.cn/fast_news/requirement?requirement_id=" + id + "&offset=0&limit=200",
            data : "",
            crossDomain: true,
            xhrFields: {
                withCredentials: true
            },
            dataType: "json",
            contentType: "application/json; charset=utf-8"
        }).done(function (response) {
            if (response.data.result.length > 0) {
                $('#' + id).find('.ui.prompt').hide()
                var data = response.data
                $('#' + id).find(".count_num").text(data.total)
                var content = ''
                data.result.forEach(function(fastNews){
                    content += '<div class="item listitem"><p>' + fastNews['title'] + '</p><div class="meta" style="color: #1567a5;font-size: 0.8em;"><div class="ui grid"><div class="wide column">' + fastNews['show_time'] + '</div></div></div></div>'
                })
                $('#' + id).find('.list').append(content)
            } else {
                $('#' + id).find('.prompt h3').text('24小时数内')
                $('#' + id).find('.prompt').append('<h3 class="ui grey header">暂无数据</h3>')
            }
        }).fail(function () {
            $('#' + id).find('.prompt h3').text('加载失败...')
        })
    }
</script>
</body>
</html>