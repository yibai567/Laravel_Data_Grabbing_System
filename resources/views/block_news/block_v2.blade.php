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
        height:550px;
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
        <a href="#" class="item active" >行业最新新闻</a>
    @else
        <a href="#" class="item" >行业最新新闻</a>
    @endif

    @if($data['nav_status'] == 'fast_news')
        <a href="http://{{$_SERVER['HTTP_HOST']}}/fast_news" class="item active" >行业最新快讯</a>
    @else
        <a href="http://{{$_SERVER['HTTP_HOST']}}/fast_news" class="item" >行业最新快讯</a>
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
    <div class="card">
        <div class="content">
            @if ($value['status'] == 1)
                <a class="ui orange right ribbon label">开发中</a>
            @endif
            <div class="header"><a href="{{$value['list_url']}}" target="_bank" style="letter-spacing: 0.08em;color: #000;">{{$value['company_name']}}</a></div>
                <div style="float:right">条数:{{$value['result']['total'] ?? 0}}</div>
        </div>
        <div class="content div">
            <div class="ui relaxed divided list">
            @if (!empty($value['result']['news']))
                @foreach($value['result']['news'] as $newsValue)
                  <div class="item listitem">
                    <p><a href="{{$newsValue['detail_url']}}" target="_bank" style="letter-spacing: 0.08em;color: #000;">{{$newsValue['title']}}</a></p>
                    <div class="meta" style="color: #1567a5;font-size: 0.8em;">

                    <div class="ui grid">
                        <div class="six wide column">{{$newsValue['show_time']}}</div>
                        <!-- <div class="four wide column">{{$value['company_name']}}</div> -->
                        @if(empty($newsValue['read_count']))
                        <div class="four wide column"><i class="unhide icon"></i> 无</div>
                        @else
                        <div class="six wide column"><i class="unhide icon"></i> {{$newsValue['read_count']}}</div>
                        @endif
                    </div>
                    </div>
                  </div>
                @endforeach
            @else
                <div class="ui" style="text-align: center; margin-top: 200px;">
                  <h3 class="ui grey header">24小时内</h3>
                  <h3 class="ui grey header">暂无数据</h3>
                </div>
            @endif
            </div>
        </div>
    </div>
    @endforeach

  </div>
</body>
</html>