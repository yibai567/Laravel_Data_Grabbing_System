@extends('crudbooster::admin_template')
@section('content')
    <div>
        <p>
            <a title='Main Module' href='http://{{$_SERVER['HTTP_HOST']}}/admin/t_task'><i class='fa fa-chevron-circle-left '></i> &nbsp; 返回列表
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
                    <input type="hidden" name="_token" value="ZmFF1PiUTP5z7W6hvcTFfxMZfQA4AhSaNCJJwojL">
                    <input type='hidden' name='return_url' value=''/>
                    <input type='hidden' name='ref_mainpath' value='http://webmagic.jinse.cn/admin/t_item'/>
                    <input type='hidden' name='ref_parameter' value=''/>
                    <div class="box-body" id="parent-form-area">
                        <div class='table-responsive'>
                            <table id='table-detail' class='table table-striped'>
                                <tbody>
                                    <tr><th width="80"></th><th></th></tr>
                                    <tr><td>任务ID</td><td>{{$row[id]}}</td></tr>
                                    <tr><td>脚本ID</td><td>{{$row[script_id]}}</td></tr>
                                    <tr><td>任务名称</td><td>{{$row[name]}}</td></tr>
                                    <tr><td>测试地址</td><td>{{$row[test_url]}}</td></tr>
                                    <tr><td>是否翻墙</td><td>{{$row[is_proxy]}}</td></tr>
                                    <tr><td>测试结果</td><td><pre>{{$row[test_result]}}</pre></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- etc .... -->

            </div>
        </div>
@endsection