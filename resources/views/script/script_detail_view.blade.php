@extends('crudbooster::admin_template')
@section('content')
<div>
    <p>
        <a title='Main Module' href='http://{{$_SERVER['HTTP_HOST']}}/admin/t_script'><i class='fa fa-chevron-circle-left '></i> &nbsp; 返回列表
        </a>
    </p>
</div>
<div class='panel panel-default'>
<div class='panel-heading'>
    <strong><i class='fa fa-glass'></i>{{$page_title}}</strong>
</div>
<div class='panel-body'>
<div class='form-group'>
           <div class="panel-body" style="padding:20px 0px 0px 0px">
                <form class='form-horizontal' method='post' id="form" enctype="multipart/form-data" action='http://webmagic.jinse.cn/admin/t_item/edit-save/612'>
                    <input type="hidden" name="_token" value="ZmFF1PiUTP5z7W6hvcTFfxMZfQA4AhSaNCJJwojL">
                    <input type='hidden' name='return_url' value=''/>
                    <input type='hidden' name='ref_mainpath' value='http://webmagic.jinse.cn/admin/t_item'/>
                    <input type='hidden' name='ref_parameter' value=''/>
                    <div class="box-body" id="parent-form-area">
                    <div class='table-responsive'>
                        <table id='table-detail' class='table table-striped'>
                            <tr><td>名称</td><td>{{$row[name]}}</td></tr>
                            <tr><td>描述</td><td><span class='badge'>{{$row[description]}}</span> </td></tr>
                            <tr><td>配置</td><td><pre style='width:1000px;'>{{$row[init]}}</pre></td></tr>
                            <tr><td>步骤</td><td><pre style='width:1000px;'>{{$row[step]}}</pre></td></tr>
                            <tr><td>执行规则</td><td><span class='badge'>{{$row[cron_type]}}</span> </td></tr>
                            <tr><td>最后生成时间</td><td><span class='badge'>{{$row[last_generate_at]}}</span> </td></tr>
                            <tr><td>状态</td><td><span class='badge'>{{$row[status]}}</span> </td></tr>
                            <tr><td>操作人</td><td>{{$row[operate_user]}}</td></tr>
                            <tr><td>创建时间</td><td>{{created_at}}</td></tr>
                        </table>
                    </div>

                </form>
            </div>

<!-- etc .... -->

</form>
</div>
</div>
@endsection