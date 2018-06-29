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
    @if($data['nav_status'] == 'wx_message')
        <a href="http://{{$_SERVER['HTTP_HOST']}}/wx/room/message/old" class="item active" >相对论信息</a>
    @else
        <a href="http://{{$_SERVER['HTTP_HOST']}}/wx/room/message/old" class="item" >相对论信息</a>
    @endif
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
    @if(empty($data['status']))
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
    @endif
</div>
<div class="ui bottom attached tab segment active" data-tab="first" style="display: none;">
</div>
<div class="ui bottom attached tab segment" data-tab="two" style="display: none;">
</div>
<div class="ui bottom attached tab segment" data-tab="three" style="display: none;">
</div>

<div class="ui internally celled grid">
    <div class="row">
    <div class="three wide column">
        <div class="ui divided items">
        <div class="ui relaxed divided list">
            @foreach($data['data'] as $value)
              <div class="item" id="status_{{$value['id']}}">

                @if ($value['status'] == 1)
                    <a class="ui teal right ribbon label" onclick="updateGroupStatus({{$value['id']}})">关闭</a>
                @else
                    <a class="ui orange right ribbon label">已处理</a>
                @endif
                <p>{{$value['last_message']}}</p>
                <div class="meta">
                    <div class="right floated content" onclick="getWxMessage({{$value['id']}}, 1)">
                    <a class="ui basic left pointing label"><i class="angle right icon"></i></a>
                    </div>
                    <div class="ui bottom left label">{{$value['month']}}-{{$value['day']}} {{$value['hour']}}:{{$value['times']}}</div>
                </div>
              </div>
            @endforeach
            </div>
        </div>
    </div>
    <div class="ten wide column">
        <div class="ui items">
        <div class="ui visible message">
          <p id="p">微信消息列表</p>
          <p id="p1"></p>
        </div>
        <div id="div">
        </div>
    </div>
  </div>
</div>
</div>
</body>
<script type="text/javascript">
    function getWxMessage(id, status='') {

        if (id == undefined) {
            return false;
        }
        var colorArr = ['yellow','green','blue','red'];

        $.ajax({
            type: "get",
            url: "http://{{$_SERVER['HTTP_HOST']}}/ajax/wx/message?group_wx_message_id="+id+ "&status="+status,
            data : "",
            crossDomain: true,
            xhrFields: {
                withCredentials: true
            },
            dataType: "json",
            contentType: "application/json; charset=utf-8"
        }).done(function (d) {
            if (d) {
                var content = ""
                var room_name= '';
                for (var i = 0, len = d.data.wx_message.length; i < len; i++) {
                    var n = Math.floor(Math.random() * colorArr.length + 1)-1;
                    status = '';
                    if (d.data.wx_message[i].status == 1) {
                        status = '<div class="floating ui red label" onclick="updateStatus('+d.data.wx_message[i].id+')">隐藏</div>';
                    }
                    content += '<div class="item" id="update_message_status_'+d.data.wx_message[i].id+'"><div class="content"><p><div class="header">'+d.data.wx_message[i].contact_name+':</div><div class="ui '+colorArr[n]+' message"><p><span>'+d.data.wx_message[i].content+'</span>'+status+'</p></div></p></div></div>';
                    room_name = d.data.wx_message[i].room_name;
                }
            }
            $('#div').html(content);

            desc = '当前组ID：'+id+'&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp'+'当前时间段：'+d.data.group_wx_message.times+'&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp'+'当前微信群名称：'+room_name+'&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp <br><br>(<span style="color:red;">注</span>：点击"全部"按钮显示当前组内全部信息; 点击"隐藏"按钮隐藏当前信息)';
            $('#p').html(desc);
            $('#p1').html('<div class="floating ui red label" onclick="getWxMessage('+id+',0)">全部</div>');
        })
    }
    function updateStatus(id)
    {
    $.ajax({
        type: "get",
        url: "http://{{$_SERVER['HTTP_HOST']}}/ajax_update_status?id="+id,
        data : "",
        crossDomain: true,
        xhrFields: {
            withCredentials: true
        },
        dataType: "json",
        contentType: "application/json; charset=utf-8"
    }).done(function (d) {
        if (d && d.data.status == 2) {
            $('#update_message_status_'+id).remove();
        }
        })
    }
    function updateGroupStatus(id)
    {
    $.ajax({
        type: "get",
        url: "http://{{$_SERVER['HTTP_HOST']}}/ajax_update_group_status?id="+id,
        data : "",
        crossDomain: true,
        xhrFields: {
            withCredentials: true
        },
        dataType: "json",
        contentType: "application/json; charset=utf-8"
    }).done(function (d) {
        if (d && d.data.status == 2) {
            $('#status_'+id).remove();
        }
        })
    }
    var last_group_id = {{$data['data'][0]['id']}}
        getWxMessage(last_group_id, 1)
</script>
</html>