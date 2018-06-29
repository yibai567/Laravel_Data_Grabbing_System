
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>微信信息</title>

    <link href="https://getbootstrap.com/docs/3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://getbootstrap.com/docs/3.3/assets/css/ie10-viewport-bug-workaround.css" rel="stylesheet">
    <link href="https://getbootstrap.com/docs/3.3/examples/dashboard/dashboard.css" rel="stylesheet">
    <script src="https://getbootstrap.com/docs/3.3/assets/js/ie-emulation-modes-warning.js"></script>
</head>
<style type="text/css">
    .navbar {
        background-color: #eee;
        padding-top:6px;
        box-shadow:0 2px 4px -1px rgba(0,0,0,0.25);
        height: 4.2857em;
    }
    #example-navbar-collapse{
        position: relative;
        bottom: 30px;
        text-align: right;
        padding-right: 130px;
    }
</style>
<body>
    <nav class="navbar navbar-inverse navbar-fixed-top">
        <div class="container-fluid">
        <div class="collapse navbar-collapse">
            <img src="https://resource.jinse.com/www/v3/img/logo.svg?v=765">
        </div>
        <div class="collapse navbar-collapse" id="example-navbar-collapse">
        <span class="header-buttons">
            @guest
            <a href="http://{{$_SERVER['HTTP_HOST']}}/login" target="_blank">
            <button class="widget-button btn btn-primary btn-small sign-up-button btn-text">
                <span class="d-button-label">登陆</span>
            </button>
            </a>
            @else
            <button class="widget-button btn btn-primary btn-small sign-up-button btn-text">
                <span class="d-button-label">{{ Auth::user()->name }}</span>
            </button>
            <a href="http://{{$_SERVER['HTTP_HOST']}}/logout">
            <button class="widget-button btn btn-primary btn-small sign-up-button btn-text">
                <span class="d-button-label">退出</span>
            </button>
            </a>
            @endguest
        </span>

        </div>
        </div>
    </nav>
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-3 col-md-2 sidebar">
          <ul class="nav nav-sidebar">
            <div class="btn-group">
                <a href="http://{{$_SERVER['HTTP_HOST']}}/we_message">
                <button type="button" class="btn btn-default">全部</button>
                </a>
                <a href="http://{{$_SERVER['HTTP_HOST']}}/we_message?status=2">
                <button type="button" class="btn btn-default">未标识</button>
                </a>
                <a href="http://{{$_SERVER['HTTP_HOST']}}/we_message?status=1">
                <button type="button" class="btn btn-default">已标识</button>
                </a>
            </div>
            <li class="active">
                <a href="#">时间段<span class="sr-only">(current)</span></a>
            </li>
            @foreach($data['data'] as $value)
            @if ($value['status'] == 0)
            <?php $status = '未标识'?>
            <li><a href="#" onclick="getWxMessage({{$value['id']}}, 2)">{{$value['year']}}-{{$value['month']}}-{{$value['day']}} {{$value['hour']}}:{{$value['minutes']}} <span class="badge" onclick="updateGroupStatus({{$value['id']}})" id="status_{{$value['id']}}">{{$status}}</span></a></li>
            @else
            <li><a href="#" onclick="getWxMessage({{$value['id']}}, 1)">{{$value['year']}}-{{$value['month']}}-{{$value['day']}} {{$value['hour']}}:{{$value['minutes']}}</a></li>
            @endif
            @endforeach
          </ul>
        </div>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
        <div id="button"></div>
        <h2 class="sub-header">消息列表</h2>
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>微信群名</th>
                  <th>微信联系人</th>
                  <th>微信内容</th>
                  <th>状态</th>
                  <th>时间</th>
                </tr>
              </thead>
              <tbody id="tr">
                <tr></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script type="text/javascript">
        function updateStatus(id)
        {
                $.ajax({
                    type: "get",
                    url: "http://{{$_SERVER['HTTP_HOST']}}/ajax/update/status?id="+id,
                    data : "",
                    crossDomain: true,
                    xhrFields: {
                        withCredentials: true
                    },
                    dataType: "json",
                    contentType: "application/json; charset=utf-8"
                }).done(function (d) {
                    if (d && d.data.status == 1) {
                        $('#wx_message_status_'+id).remove();
                    }
                    })

        }

        function updateGroupStatus(id)
        {
                $.ajax({
                    type: "get",
                    url: "http://{{$_SERVER['HTTP_HOST']}}/ajax/update/group/status?id="+id,
                    data : "",
                    crossDomain: true,
                    xhrFields: {
                        withCredentials: true
                    },
                    dataType: "json",
                    contentType: "application/json; charset=utf-8"
                }).done(function (d) {
                    if (d && d.data.status == 1) {
                        $('#status_'+id).remove();
                    }
                    })

        }
        function getWxMessage(id, status='') {
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
                        for (var i = 0, len = d.data.length; i < len; i++) {
                            var status = ''
                            if (d.data[i].status == 0) {
                                status = '未标记'
                                str = '<a class="btn btn-xs btn-warning" onclick="updateStatus('+d.data[i].id+')" >'+status+'</a>';
                            } else {
                                status = '已标记'
                                str = '<a class="btn btn-xs btn-warning">'+status+'</a>';
                            }
                            content += '<tr id="wx_message_status_'+d.data[i].id+'"><td>'+d.data[i].room_name+'</td><td>'+d.data[i].contact_name+'</td><td>'+d.data[i].content+'</td><td>'+str+'</td><td>'+d.data[i].created_at+'</td></tr>'
                        }
                        button = '<a onclick="getWxMessage('+id+')"><button type="button" class="btn btn-default">全部</button></a>';
                        $('#tr').html(content);
                        $('#button').html(button);
                    }
                    })
        }
    </script>
  </body>
</html>
