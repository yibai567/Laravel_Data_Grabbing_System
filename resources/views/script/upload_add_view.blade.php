@extends('script.script_admin_template')
@section('content')


<div class="panel panel-default" >
    <div class="panel-heading">
        <strong>
            <i class='{{CRUDBooster::getCurrentModule()->icon}}'></i> {!! $page_title or "Page Title" !!}
        </strong>
    </div>
    <?php
       $action = CRUDBooster::mainpath("script-save");

        $return_url = ($return_url)?:g('return_url');
    ?>
    <form class='form-horizontal' method='post' id="form" enctype="multipart/form-data" action='{{$action}}'>
        <div class="panel-body" style="padding:79px 0px 75px 0px;; width: 80%;">
                <div class="box-body" id="parent-form-area">
                     <input type="hidden" name="_token" value="{{ csrf_token() }}">
                     <input type='hidden' name='return_url' value='{{ @$return_url }}'/>
                    <div class='form-group header-group-0 ' id='form-group-name'>
                        <label class='control-label col-sm-2'>脚本名称
                            <span class='text-danger' title='This field is required'>*</span>
                        </label>
                        <div class="col-xs-5">
                            <input type='text' title="脚本名称" required maxlength='70' class='form-control' name="name" id="name" value='{{$row[name]}}'/>
                            <div class="text-danger"></div>
                            <p class='help-block'></p>
                        </div>
                    </div>
                    <div class='form-group header-group-0  id='form-group-description'>
                        <label class='control-label col-sm-2'>脚本描述 </label>
                        <div class="col-sm-10">
                            <textarea name="description" id="description" maxlength=5000 class='form-control' rows='2'>{{$row[description]}}</textarea>
                            <div class="text-danger"></div>
                            <p class='help-block'></p>
                        </div>
                    </div>
                    <div class='form-group header-group-0 ' id='form-group-cron_type'>
                        <label class='control-label col-sm-2'>list_url
                            <span class='text-danger' title='This field is required'>*</span>
                        </label>
                        <div class="col-xs-5">
                            <textarea name="list_url" maxlength=5000 class='form-control' rows='2'></textarea>
                            <div class="text-danger"></div>
                            <p class='help-block'></p>
                        </div>
                    </div>
                    <div class='form-group header-group-0 ' id='form-group-load_images'>
                        <label class='control-label col-sm-2'>data类型
                            <span class='text-danger' title='This field is required'>*</span>
                        </label>
                        <div>
                            <div class="col-sm-10">
                                <label class='radio-inline'>
                                    <input type="radio" name="data_type" value="1" checked> CasperJs
                                </label>
                                <label class='radio-inline'>
                                    <input type="radio" name="data_type" value="2"> Html
                                </label>
                                <label class='radio-inline'>
                                    <input type="radio" name="data_type" value="3"> Api
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class='form-group header-group-0 ' id='form-group-load_images'>
                        <label class='control-label col-sm-2'>脚本类型
                            <span class='text-danger' title='This field is required'>*</span>
                        </label>
                        <div>
                            <div class="col-sm-10">
                                <label class='radio-inline'>
                                    <input type="radio" name="ext" value="1" checked> JS
                                </label>
                                <label class='radio-inline'>
                                    <input type="radio" name="ext" value="2"> PHP
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class='form-group header-group-0  id='form-group-content'>
                        <label class='control-label col-sm-2'>脚本内容
                            <span class='text-danger' title='This field is required'>*</span>
                        </label>
                        <div class="col-sm-10">

                            <textarea name="content" id="content" maxlength=10000 required class='form-control' style="height:400px;display: block;color: #428bca;"></textarea>
                            <div class="text-danger"></div>
                            <p class='help-block'></p>
                        </div>
                    </div>
                    <div class='form-group header-group-0 ' id='form-group-is_proxy'>
                        <label class='control-label col-sm-2'>是否翻墙
                        </label>
                        <div>
                            <div class="col-sm-10">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="is_proxy" value="1">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class='form-group header-group-0 ' id='form-group-requirement_pool_id'>
                        <label class='control-label col-sm-2'>需求池ID
                        </label>
                        <div class="col-xs-5">
                            <input type='text' title="需求池ID" maxlength='70' class='form-control' name="requirement_pool_id" id="requirement_pool_id" value=''/>
                            <div class="text-danger"></div>
                            <p class='help-block'></p>
                        </div>
                    </div>
                    <div class='form-group header-group-0 ' id='form-group-cron_type'>
                        <label class='control-label col-sm-2'>语言类型
                            <span class='text-danger' title='This field is required'>*</span>
                        </label>
                        <div>
                            <div class="col-sm-10">
                                <label class='radio'>
                                    <input type="radio" checked name="language_type" value="1"> 英文
                                </label>
                                <label class='radio'>
                                    <input type="radio" name="language_type" value="2"> 中文
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class='form-group header-group-0 ' id='form-group-cron_type'>
                        <label class='control-label col-sm-2'>cron_type
                            <span class='text-danger' title='This field is required'>*</span>
                        </label>
                        <div>
                            <div class="col-sm-10">
                                <label class='radio'>
                                    <input type="radio" checked name="cron_type" value="1"> 每分钟执行一次
                                </label>
                                <label class='radio'>
                                    <input type="radio" name="cron_type" value="2"> 每五分钟执行一次
                                </label>
                                <label class='radio'>
                                    <input type="radio" name="cron_type" value="3"> 每十分钟执行一次
                                </label>
                                <label class='radio'>
                                    <input type="radio" name="cron_type" value="4"> 只执行一次
                                </label>
                            </div>
                        </div>
                    </div>
            </div>
        </div><!-- /.boxbody- -->

        <div class="box-footer" style="background: #F5F5F5">
            <div class="form-group">
                <label class="control-label col-sm-2"></label>
                <div class="col-sm-10">

                    <a href='{{CRUDBooster::mainpath("?".http_build_query(@$_GET)) }}' class='btn btn-default'><i class='fa fa-chevron-circle-left'></i> {{trans("crudbooster.button_back")}}</a>

                    <input type="submit" name="submit" value='{{trans("crudbooster.button_save")}}' class='btn btn-success'>
                </div>
            </div>
        </div><!-- /.box-footer-->
    </form>

</div>



@endsection
