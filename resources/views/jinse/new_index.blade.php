<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="{{ asset('css/semantic.min.css')}}">
<script src="http://{{$_SERVER['HTTP_HOST']}}/vendor/crudbooster/assets/adminlte/plugins/jQuery/jQuery-2.1.4.min.js"></script>
<script src="{{ asset('js/semantic.min.js')}}"></script>
</head>
<body>

<div class="ui menu">
  <div class="header item">Jinse Crawl</div>
    @if($data['nav_status'] == 'block_news')
        <a href="http://{{$_SERVER['HTTP_HOST']}}/news" class="item active" >行业最新新闻</a>
    @else
        <a href="http://{{$_SERVER['HTTP_HOST']}}/news" class="item" >行业最新新闻</a>
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
<div class="ui top attached tabular menu">
    <a href="#" class="item active" >全部</a>
    <!-- @if(empty($data['status']))
        <a href="http://{{$_SERVER['HTTP_HOST']}}/wx_message" class="item active" >全部</a>
    @else
        <a href="http://{{$_SERVER['HTTP_HOST']}}/wx_message" class="item" >全部</a>
    @endif
    @if($data['status'] == 1)
        <a href="http://{{$_SERVER['HTTP_HOST']}}/wx_message?status=1" class="item active" >未处理</a>
    @else
        <a href="http://{{$_SERVER['HTTP_HOST']}}/wx_message?status=1" class="item" >未处理</a>
    @endif
    @if($data['status'] == 2)
        <a href="http://{{$_SERVER['HTTP_HOST']}}/wx_message?status=2" class="item active" >已处理</a>
    @else
        <a href="http://{{$_SERVER['HTTP_HOST']}}/wx_message?status=2" class="item" >已处理</a>
    @endif -->
</div>
<div class="ui bottom attached tab segment active" data-tab="first" style="display: none;">
</div>

<div class="ui internally celled grid">
    <div class="row">
    <div class="three wide column">
        <div class="ui divided items">
        <div class="ui relaxed divided list">
            @foreach($data['data'] as $key=>$value)
              <div class="item">
                <a class="ui orange right ribbon label">问题 {{$key+1}}</a>
                <p>{!! $value['content'] !!}</p>
                <div class="meta">
                    <div class="right floated content" onclick="getWxMessage({{$value['id']}}, {{$key+1}}, '{{$value['content']}}')">
                    <a class="ui basic left pointing label"><i class="angle right icon"></i></a>
                    </div>
                    <div class="ui bottom left label">{{$value['created_at']}}</div>
                </div>
              </div>
            @endforeach
            </div>
        </div>
    </div>

    <div class="ten wide column">
        <div class="ui visible message">
          <p id="p"></p>
          <span id='all'><a href='' target="_blank">查看文本</a></span>
        </div>

        <div class="ui link cards" id="card">

        </div>

    </div>
</div>
</div>
</body>
<script type="text/javascript">
    function getWxMessage(id, num, problem='') {
        $.ajax({
            type: "get",
            url: "http://{{$_SERVER['HTTP_HOST']}}/wx/ajax/room/message/"+id,
            data : "",
            crossDomain: true,
            xhrFields: {
                withCredentials: true
            },
            dataType: "json",
            contentType: "application/json; charset=utf-8"
        }).done(function (d) {
            if (d) {
                var name = '';
                var content = '';
                for (var i in d.data) {
                    name +='<div class="card"><div class="content"><a class="ui green left ribbon label">'+i+'</a></div>';
                    for (var res in d.data[i]){
                        if (i == d.data[i][res].contact_name) {
                            name+='<div class="content"><div class="ui relaxed divided list"><div class="item"><p>'+d.data[i][res].content+'</p><div class="meta"><span class="price">'+d.data[i][res].created_at+'</span></div></div></div></div>';
                        }
                    }
                    name +='</div>';
                }
                $("#card").html(name);
                $("#p").html("问题"+num+":     "+problem);
                $("#all a").attr("href", '/wx/down/room/message/' + id);
            }
        })
    }
    var last_group_id = {{$data['data'][0]['id']}}
    var problem = '{{$data['data'][0]['content']}}'
        getWxMessage(last_group_id, 1, problem)

</script>
</html>
