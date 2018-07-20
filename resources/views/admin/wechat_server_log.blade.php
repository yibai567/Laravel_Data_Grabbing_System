<!-- First, extends to the CRUDBooster Layout -->
@extends('crudbooster::admin_template')
@section('content')
  <!-- Your html goes here -->
  <div class='panel panel-default'>
    <div class='panel-heading'>微信服务日志</div>
    <div class='panel-body'>
        <div class='form-horizontal'>
            <label class="col-sm-2 control-label">监听类型</label>
            <div class="col-sm-10">
                <p class="form-control-static">
                @if($listen_type == 1)
                    监听群服务
                @else
                    监听公众号服务
                @endif
                </p>
            </div>
            @if($listen_type == 1)
                <label class="col-sm-2 control-label">监听群名称</label>
                <div class="col-sm-10">
                    <p class="form-control-static">{{$room_name}}</p>
                </div>
                <label class="col-sm-2 control-label">管理微信名称</label>
                <div class="col-sm-10">
                    <p class="form-control-static">{{$wechat_name}}</p>
                </div>
            @endif
            <label class="col-sm-2 control-label">输出日志</label>
            <div class="col-sm-10" style="height: 800px; overflow:auto">
                <table id = "log" class="table">
                <caption onclick="test({{$id}})">5s 后自动加载，可点击手动加载 (只显示最新 500 条记录)</caption>
                <thead>
                    <tr>
                        <th width="150">时间</th>
                        <th>内容</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($log as $value)
                    <tr>
                        <td data="{{$value['id']}}">{{$value['created_at']}}</td>
                        <td>{!! $value['content'] !!}</td>
                    </tr>
                    @endforeach
                </tbody>
                </table>
            </div>
        </div>
      </form>
    </div>
  </div>
@endsection

<script type="text/javascript">

    function test(id){
        var last_time = '<?php echo $_GET['last_time'];?>'
        var log_id = ''
        if ($("#log tr:eq(1) td:eq(0)").text()) {
            last_time = $("#log tr:eq(1) td:eq(0)").text()
            log_id = $("#log tr:eq(1) td:eq(0)").attr('data')
        }

        console.log('/admin/t_wechat_server/log/' + id + '?last_time=' + last_time)
        $.get('/admin/t_wechat_server/log/' + id + '?last_time=' + last_time + '&asc=1&log_id=' + log_id, function(data,status){
          if (status != 'success') {
            console.log('系统错误')
            return false;
          }
          console.log(data)
          res = JSON.parse(data);
          res.forEach(function (value) {
            var tr = '<tr><td data="' + value.id + '">' + value.created_at + '</td><td>' +  value.content +'</td></tr>'
            $("#log tr:eq(0)").after(tr)
          })
        })
    }
    setInterval('test({{$id}})', 5000)
</script>