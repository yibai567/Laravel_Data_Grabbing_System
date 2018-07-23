@extends('script.script_admin_template')
@section('content')
@if(CRUDBooster::getCurrentMethod() != 'getProfile' && $button_cancel)
    @if(g('return_url'))
    <p>
        <a title='Return' href='{{g("return_url")}}'>
            <i class='fa fa-chevron-circle-left '></i> &nbsp; {{trans("crudbooster.form_back_to_list",['module'=>CRUDBooster::getCurrentModule()->name])}}
        </a>
    </p>
    @else
    <p>
        <a title='Main Module' href='{{CRUDBooster::mainpath()}}'>
            <i class='fa fa-chevron-circle-left '></i> &nbsp; {{trans("crudbooster.form_back_to_list",['module'=>CRUDBooster::getCurrentModule()->name])}}
        </a>
    </p>
    @endif
@endif

<div class="panel panel-default">
    <div class="panel-heading">
        <strong>
            <i class='{{CRUDBooster::getCurrentModule()->icon}}'></i> 第一步、配置脚本信息
        </strong>
    </div>
    <?php
        $action = (@$row)?CRUDBooster::mainpath("edit-save/$row[id]"):CRUDBooster::mainpath("add-save");
        $return_url = ($return_url)?:g('return_url');
    ?>
    <form class='form-horizontal' method='post' id="form" enctype="multipart/form-data" action='{{$action}}'>
        <div class="panel-body" style="padding:20px 0px 0px 0px; width: 80%;">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type='hidden' name='return_url' value='{{ @$return_url }}'/>
            <input type='hidden' name='ref_mainpath' value='{{ CRUDBooster::mainpath() }}'/>
            <input type='hidden' name='ref_parameter' value='{{urldecode(http_build_query(@$_GET))}}'/>
            <input type='hidden' name='data_type' value='{{$row[data_type]}}'/>
            @if($hide_form)
                <input type="hidden" name="hide_form" value='{!! serialize($hide_form) !!}'>
            @endif
            <div class="box-body" id="parent-form-area">
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
                        <textarea name="list_url" id="description" maxlength=5000 class='form-control' rows='2'>{{$row[list_url]}}</textarea>

                        <div class="text-danger"></div>
                        <p class='help-block'></p>
                    </div>
                </div>
                @if ($row['data_type'] == 1 && empty($row['content']))
                <div class="table-bordered">
                    <div class='form-group'>
                        <label class='control-label col-md-2'>casper配置</label>
                        <label class='control-label col-md-3'>是否加载图片&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp
                            <?php $checked = "" ?>
                            @if ($row['casper_config']['load_images'] == 1)
                            <?php $checked = "checked" ?>
                            @endif
                            <input type="checkbox" {{$checked}} name="load_images" value="1">
                        </label>
                        <label class='control-label col-md-3'>是否加载插件&nbsp&nbsp&nbsp&nbsp
                            <?php $checked = "" ?>
                            @if ($row['casper_config']['load_plugins'] == 1)
                            <?php $checked = "checked" ?>
                            @endif
                            <input type="checkbox" {{$checked}} name="load_plugins" value="1">
                        </label>
                        <label class='control-label col-md-3'>verbose&nbsp&nbsp&nbsp&nbsp
                            <?php $checked = "" ?>
                            @if ($row['casper_config']['verbose'] == 1)
                            <?php $checked = "checked" ?>
                            @endif
                            <input type="checkbox" {{$checked}} name="verbose" value="1">
                        </label>
                    </div>
                    <div class='form-group'>
                        <label class='control-label col-md-2'></label>
                        <?php $checked = "" ?>
                        @if ($row['casper_config']['log_level'] == 'debug')
                            <?php $checked = "checked" ?>
                        @elseif ($row['casper_config']['log_level'] == 'info')
                            <?php $checked = "checked" ?>
                        @else
                            <?php $checked = "checked" ?>
                        @endif
                        <label class='control-label col-md-3'>日志级别 debug&nbsp&nbsp&nbsp&nbsp
                            <input type="radio" name="log_level" {{$checked}} value="debug">
                        </label>
                        <label class='control-label col-md-3'>info&nbsp&nbsp&nbsp&nbsp
                            <input type="radio" name="log_level" {{$checked}} value="info">
                        </label>
                        <label class='control-label col-md-3'>error&nbsp&nbsp&nbsp&nbsp
                            <input type="radio" name="log_level" {{$checked}} value="error">
                        </label>
                    </div>
                    <div class='form-group'>
                        <label class='control-label col-md-2'></label>
                        <label class='control-label col-md-1'>width:</label>
                        <label class='control-label col-md-2'>
                            <input type='text' class='form-control' name="width" value="{{$row['casper_config']['width']}}"/>
                        </label>
                        <label class='control-label col-md-2'>height:</label>
                        <label class='control-label col-md-2'>
                            <input type='text' class='form-control' name="height" value="{{$row['casper_config']['height']}}"/>
                        </label>
                    </div>
                </div>
                @endif
                @if (empty($row['content']))
                    <div class='form-group header-group-0 ' id='form-group-modules'>
                    <label class='control-label col-sm-2'>步骤
                        <span class='text-danger' title='This field is required'>*</span>
                    </label>
                    <div class="col-sm-10">
                        <div data-force="18" class="layer-block">
                            <p class='help-block'>请将右侧代码块拖拽至下方虚框内</p>
                            <ul id="bar" class="block__list block__list_tags">
                                @if (!empty($row['modules']))
                                    @foreach($row['modules'] as $key => $value)
                                    <li>
                                        <div class="text-danger" style="font-size: 20px;font-weight: bold;">{{$row[script_model_list][$key][name]}}</div>
                                        <div class="params">
                                            @foreach($value as $modulesKey => $modulesValue)
                                                <?php $parameters = $row['script_model_params'][$key][$modulesKey]?>
                                                <div class='form-group' id='form-group-name'>
                                                    @if (!empty($parameters->name))
                                                        <label class='control-label col-sm-2' style="text-align: left;">
                                                        {{$parameters->name}}:
                                                        @if ($parameters->requires == 'true')
                                                            <?php $requires = 'required' ?>
                                                            <span class='text-danger' title='必填'>*</span>
                                                        @else
                                                            <?php $requires = '' ?>
                                                        @endif
                                                        </label>
                                                    @else
                                                        <input type="hidden" class="form-control" name="script_model_params[{{$key}}][]" value="">
                                                    @endif
                                                    @if ($parameters->type == 'string')
                                                        <div class="col-xs-10">
                                                            <input type="type" {{$requires}} class="form-control" name="script_model_params[{{$key}}][]" value="{{$modulesValue}}">
                                                        </div>
                                                    @elseif ($parameters->type == 'json')
                                                        <div class="col-xs-10">
                                                            <textarea {{$requires}} class="form-control" name="script_model_params[{{$key}}][]" maxlength=5000 rows="3">{{$modulesValue}}</textarea>
                                                        </div>
                                                    @elseif ($parameters->type == 'boole')
                                                        <div class="col-sm-10">
                                                            <label class='radio-inline'>
                                                                <input type="radio" {{($parameters->default == 'true') ? 'checked' : ''}} name="script_model_params[{{$key}}][]" value="1">true
                                                            </label>
                                                            <label class='radio-inline'>
                                                                <input type="radio"  {{($parameters->default == 'false') ? 'checked' : ''}} name="script_model_params[{{$key}}][]" value="2"> false
                                                            </label>
                                                        </div>
                                                    @elseif ($parameters->type == 'text')
                                                        <div class="col-sm-10">
                                                            <textarea {{$requires}} name="script_model_params[{{$key}}][]" maxlength='5000' rows="3" class="form-control">{{$modulesValue}}</textarea>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </li>
                                    @endforeach
                                @else
                                    <li class="text-center hidden"></li>
                                @endif
                            </ul>
                        </div>
                    </div>
                    <div style="clear: both;"></div>
                </div>
                @else
                    <div class='form-group header-group-0  id='form-group-content'>
                        <label class='control-label col-sm-2'>脚本内容
                            <span class='text-danger' title='This field is required'>*</span>
                        </label>
                        <div class="col-sm-10">

                            <textarea name="content" id="content" maxlength=10000 required class='form-control' style="height:400px;display: block;color: #428bca;">{{$row['content']}}</textarea>
                            <div class="text-danger"></div>
                            <p class='help-block'></p>
                        </div>
                    </div>

                @endif

                <div class='form-group header-group-0 ' id='form-group-cron_type'>
                    <label class='control-label col-sm-2'>是否翻墙
                    </label>
                    <div>
                        <div class="col-sm-10">
                            <div class="checkbox">
                                @if ($row[is_proxy] == 1)
                                    <label>
                                        <input type="checkbox" checked name="is_proxy" value="1">
                                    </label>
                                @else
                                    <label>
                                        <input type="checkbox" name="is_proxy" value="1">
                                    </label>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class='form-group header-group-0 ' id='form-group-name'>
                    <label class='control-label col-sm-2'>需求池ID
                    </label>
                    <div class="col-xs-5">
                        <input type='text' title="需求池ID" maxlength='70' class='form-control' name="requirement_pool_id" id="requirement_pool_id" value='{{$row[requirement_pool_id]}}'/>
                        <div class="text-danger"></div>
                        <p class='help-block'></p>
                    </div>
                </div>

                <div class='form-group header-group-0 ' id='form-group-cron_type'>
                    <label class='control-label col-sm-2'>cron_type
                        <span class='text-danger' title='This field is required'>*</span>
                    </label>
                    <div class="col-sm-10">
                        @if ($row[cron_type] == 1)
                            <label class='radio'>
                                <input type="radio" checked name="cron_type" value="1"> 每分钟执行一次
                            </label>
                        @else
                            <label class='radio'>
                                <input type="radio" name="cron_type" value="1"> 每分钟执行一次
                            </label>
                        @endif
                        @if ($row[cron_type] == 2)
                            <label class='radio'>
                                <input type="radio" checked name="cron_type" value="2"> 每五分钟执行一次
                            </label>
                        @else
                            <label class='radio'>
                                <input type="radio" name="cron_type" value="2"> 每五分钟执行一次
                            </label>
                        @endif

                        @if ($row[cron_type] == 3)
                            <label class='radio'>
                                <input type="radio" checked name="cron_type" value="3"> 每十分钟执行一次
                            </label>
                        @else
                            <label class='radio'>
                                <input type="radio" name="cron_type" value="3"> 每十分钟执行一次
                            </label>
                        @endif

                        @if ($row[cron_type] == 4)
                            <label class='radio'>
                                <input type="radio" checked name="cron_type" value="4"> 只执行一次
                            </label>
                        @else
                            <label class='radio'>
                                <input type="radio" name="cron_type" value="4"> 只执行一次
                            </label>
                        @endif
                    </div>
                </div>
            </div>
        </div><!-- /.boxbody- -->
        <div class="box-footer" style="background: #F5F5F5">
            <div class="form-group">
                <label class="control-label col-sm-2"></label>
                <div class="col-sm-10">
                    @if($button_cancel && CRUDBooster::getCurrentMethod() != 'getDetail')
                        @if(g('return_url'))
                            <a href='{{g("return_url")}}' class='btn btn-default'><i class='fa fa-chevron-circle-left'></i> {{trans("crudbooster.button_back")}}</a>
                        @else
                            <a href='{{CRUDBooster::mainpath("?".http_build_query(@$_GET)) }}' class='btn btn-default'><i class='fa fa-chevron-circle-left'></i> {{trans("crudbooster.button_back")}}</a>
                        @endif
                    @endif
                    @if(CRUDBooster::isCreate() || CRUDBooster::isUpdate())
                        @if(CRUDBooster::isCreate() && $button_addmore==TRUE && $command == 'add')
                            <input type="submit" name="submit" value='{{trans("crudbooster.button_save_more")}}' class='btn btn-success'>
                        @endif
                        @if($button_save && $command != 'detail')
                            <input type="submit" name="submit" value='{{trans("crudbooster.button_save")}}' class='btn btn-success'>
                        @endif
                    @endif
                </div>
            </div>
        </div><!-- /.box-footer-->
    </form>
    @if (empty($row['content']))
        <aside class="control-sidebar control-sidebar-dark" style="width: 330px; right:0; height: 100%">
        <ul id="foo" class="block__list block__list_words">
            @if (!empty($script_model))
                @foreach($script_model as $key=>$value)
                <li>
                <p class="hidden">模块id:{{$value->id}}</p>
                <div class="text-danger" style="font-size: 20px;font-weight: bold;">{{$value->name}}</div>
                <blockquote class="text-muted blockquote_description">{!!$value->description!!}</blockquote>
                <div class="params">
                @if (empty(json_decode($value->parameters, true)))
                    <span><input type="hidden" {{$requires}} name="script_model_params[{{$value->id}}][]" value=""></span>
                @else
                    @foreach(json_decode($value->parameters, true) as $parametersValue)
                    <div class='form-group' id='form-group-name'>
                        <label class='control-label col-sm-2' style="text-align: left;">{{$parametersValue[name]}}:
                        @if ($parametersValue['requires'] == 'true')
                            <?php $requires = 'required' ?>
                            <span class='text-danger' title='必填'>*</span>
                        @else
                            <?php $requires = '' ?>
                        @endif
                        </label>
                        @if ($parametersValue[type] == 'string')
                            <div class="col-xs-10">
                                <input type="type" {{$requires}} class="form-control" name="script_model_params[{{$value->id}}][]" value="{{$parametersValue['default']}}">
                            </div>
                        @elseif ($parametersValue[type] == 'json')
                            <div class="col-xs-10">
                                <textarea {{$requires}} class="form-control" name="script_model_params[{{$value->id}}][]" maxlength=5000 rows="3">{{$parametersValue['default']}}</textarea>
                            </div>
                        @elseif ($parametersValue[type] == 'boole')
                            <div class="col-sm-10">
                                <label class='radio-inline'>
                                    <input type="radio" {{($parametersValue['default'] == 'true') ? 'checked' : ''}} name="script_model_params[{{$value->id}}][]" value="1">true
                                </label>
                                <label class='radio-inline'>
                                    <input type="radio"  {{($parametersValue['default'] == 'false') ? 'checked' : ''}} name="script_model_params[{{$value->id}}][]" value="2"> false
                                </label>
                            </div>
                        @elseif ($parametersValue[type] == 'text')
                        <div class="col-sm-10">
                            <textarea {{$requires}} name="script_model_params[{{$value->id}}][]" maxlength='5000' rows="3" class="form-control">{{$parametersValue['default']}}</textarea>
                        </div>
                        @endif
                    </div>
                    @endforeach
                @endif
                </div>
                </li>
                @endforeach
            @else
                <li></li>
            @endif
        </ul>
    </aside>
    @endif
</div>
<script src="http://rubaxa.github.io/Sortable/Sortable.js"></script>
<script>
Sortable.create(document.getElementById('foo'), {
    group: {
        name:"words",
        pull: 'clone',
        put: true
    },
    animation: 150, //动画参数
    sort: false,
});

    Sortable.create(document.getElementById('bar'), {
    group: {
        name:"words",
        pull: true,
        put: true
    },
    animation: 150, //动画参数
    onRemove: function (evt){ //删除拖拽节点的时候促发该事件
       evt.item.parentNode.removeChild(evt.item);
      },
    });
</script>
@endsection
