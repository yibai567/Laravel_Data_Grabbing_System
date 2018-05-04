<!DOCTYPE html>
<html lang="zh-CN">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>区块链新闻</title>
    <!-- Bootstrap -->
    <!-- 最新版本的 Bootstrap 核心 CSS 文件 -->
    <link rel="stylesheet" href="https://cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <!-- 可选的 Bootstrap 主题文件（一般不用引入） -->
    <link rel="stylesheet" href="https://cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
  <div class="container theme-showcase" role="main">

    <!-- Main jumbotron for a primary marketing message or call to action -->
      <div class="jumbotron" style="height: 100px;">
      <h4 style="margin-top: -30px;" align="center">行业最新内容</h4>
      <p align="center"><font size="2">竞品分析，页面内容平均两分钟更新一次。数据字段描述："数量"(阅读数/点赞)如果是0，表示该内容没有数量展示;"时间"(网站内容展示时间/数据获取时间)</font></p>
    </div>
    <ul class="nav nav-tabs">
        @if (!empty($content_type))
            @foreach($content_type as $key => $value)
                <li role="presentation" @if ($key == $typeDefault) class="active" @endif><a href="http://{{$_SERVER['HTTP_HOST']}}/block_news/{{$key}}">{{$value}}</a></li>
            @endforeach
        @endif

      </ul>
    @if (!empty($data))
    @foreach($data as $key => $value)
    <div class="panel panel-default">
    <table class="table table-hover">
    <!-- <span class="panel-heading" style="position: absolute;left: 0;top: 0;">{{$key}}</span> -->
    <div class="panel-heading" style="text-align: center;font-size: 18px;">{{$key}}</div>
    <thead>
      <tr>
         <th class="col-md-7">标题</th>
         <th class="col-md-1"></th>
         @if ($typeDefault!=3)
            <th class="col-md-1">数量</th>
         @endif
         <th class="col-md-1">时间</th>
      </tr>
    </thead>
    <tbody>
    @foreach($value as $liestValue)
      <tr>
        <td class="col-md-7">
            @if ($liestValue[content_type]==3)
                    @if (empty($liestValue[detail_url]))
                        <span>{{$liestValue[content]}}</span>
                    @endif
                    @if (!empty($liestValue[detail_url]))
                        <a href="{{$liestValue['detail_url']}}" target="_blank" >{{$liestValue[content]}}</a>
                    @endif
            @else
                <a href="{{$liestValue['detail_url']}}" target="_blank">{{$liestValue[title]}}</a>
            @endif
        </td>
         <td class="col-md-1"></td>
         @if ($liestValue[content_type]!=3)
            <td class="col-md-1"><span class="badge">{{$liestValue[read_count]}}</span></td>
         @endif

         <td class="col-md-1">
             @if (empty($liestValue[show_time]))
                <span class="badge">{{$liestValue[start_time]}}</span>
             @endif
             <span class="badge">{{$liestValue[show_time]}}</span>
         </td>
         <td class="col-md-1">
            <span class="glyphicon glyphicon-info-sign" title="详细信息" data-container="body" data-toggle="popover" data-placement="right" data-content="任务抓取开始时间:<br>{{$liestValue[start_time]}} <br> 任务抓取结束时间:<br>{{$liestValue[end_time]}}<br>创建时间<br>{{$liestValue[created_time]}}" aria-hidden="true" data-html="true"></span>
        </td>
      </tr>
    @endforeach
    </tbody>
    </table>
  </div>
    @endforeach
    @endif

  </div> <!-- /container -->
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <!-- 最新的 Bootstrap 核心 JavaScript 文件 -->
    <script src="https://cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
  </body>
</html>
<script>
$(function () {
    $("[data-toggle='popover']").popover();
});
</script>
