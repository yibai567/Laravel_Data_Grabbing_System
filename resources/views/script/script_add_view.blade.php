@extends('crudbooster::admin_template')
@section('content')
@if(CRUDBooster::getCurrentMethod() != 'getProfile' && $button_cancel)
@if(g('return_url'))
<p><a title='Return' href='{{g("return_url")}}'><i class='fa fa-chevron-circle-left '></i> &nbsp; {{trans("crudbooster.form_back_to_list",['module'=>CRUDBooster::getCurrentModule()->name])}}</a></p>
@else
<p><a title='Main Module' href='{{CRUDBooster::mainpath()}}'><i class='fa fa-chevron-circle-left '></i> &nbsp; {{trans("crudbooster.form_back_to_list",['module'=>CRUDBooster::getCurrentModule()->name])}}</a></p>
@endif
@endif

<div class="panel panel-default" style="position: relative;">
<div class="panel-heading">
    <strong>
    <i class='{{CRUDBooster::getCurrentModule()->icon}}'></i> {!! $page_title or "Page Title" !!}
    </strong>
    <strong>
    <a href="#" class='btn-show-sidebar' data-toggle="control-sidebar"><i class='fa fa-bars'></i> Add Widget</a></strong>
</div>

<div class="panel-body" style="padding:20px 0px 0px 0px">
<?php
$action = (@$row)?CRUDBooster::mainpath("edit-save/$row[id]"):CRUDBooster::mainpath("add-save");
$return_url = ($return_url)?:g('return_url');
?>
<form class='form-horizontal' method='post' id="form" enctype="multipart/form-data" action='{{$action}}'>
<input type="hidden" name="_token" value="{{ csrf_token() }}">
<input type='hidden' name='return_url' value='{{ @$return_url }}'/>
<input type='hidden' name='ref_mainpath' value='{{ CRUDBooster::mainpath() }}'/>
<input type='hidden' name='ref_parameter' value='{{urldecode(http_build_query(@$_GET))}}'/>
    @if($hide_form)
    <input type="hidden" name="hide_form" value='{!! serialize($hide_form) !!}'>
    @endif
    <div class="box-body" id="parent-form-area">
    @if($command == 'detail')
    @include("crudbooster::default.form_detail")
    @else
    <div class='form-group header-group-0 ' id='form-group-name' style="">
    <label class='control-label col-sm-2'>脚本名称
    <span class='text-danger' title='This field is required'>*</span>
    </label>

    <div class="col-sm-10">
    <input type='text' title="脚本名称" required maxlength=70 class='form-control' name="name" id="name" value='{{$row[name]}}'/>
    <input type='hidden' maxlength=70 class='form-control' name="languages_type" value='{{$languages_type}}'/>
    <div class="text-danger"></div>
    <p class='help-block'></p>
    </div>
    </div>
    <div class='form-group header-group-0 ' id='form-group-description' style="">
    <label class='control-label col-sm-2'>脚本描述 </label>
    <div class="col-sm-10">
    <textarea name="description" id="description" maxlength=5000 class='form-control' rows='5'>{{$row[description]}}</textarea>
    <div class="text-danger"></div>
    <p class='help-block'></p>
    </div>
    </div>
    @if ($languages_type == 1)
    <div class='form-group header-group-0 ' id='form-group-load_images' style="">
    <label class='control-label col-sm-2'>load_images
    <span class='text-danger' title='This field is required'>*</span>
    </label>
    <div>
    <div class="col-sm-10">
    @if (empty($row[init][load_images]))
        <label class='radio-inline'>
            <input type="radio" name="load_images" value="1"> true
        </label>
        <label class='radio-inline'>
            <input type="radio" checked name="load_images" value="2"> false
        </label>
    @else
        @if ($row[init][load_images] == 1)
            <label class='radio-inline'>
                <input type="radio" checked name="load_images" value="1"> true
            </label>
            <label class='radio-inline'>
                <input type="radio" name="load_images" value="2"> false
            </label>
        @else
            <label class='radio-inline'>
                <input type="radio" name="load_images" value="1"> true
            </label>
            <label class='radio-inline'>
                <input type="radio" checked name="load_images" value="2"> false
            </label>
        @endif
    @endif
    </div>
    </div>
    </div>

    <div class='form-group header-group-0 ' id='form-group-load_plugins' style="">
    <label class='control-label col-sm-2'>load_plugins
    <span class='text-danger' title='This field is required'>*</span>
    </label>
    <div>
    <div class="col-sm-10">
    @if (empty($row[init][load_plugins]))
        <label class='radio-inline'>
            <input type="radio" name="load_plugins" value="1"> true
        </label>
        <label class='radio-inline'>
            <input type="radio" checked name="load_plugins" value="2"> false
        </label>
    @else
        @if ($row[init][load_plugins] == 1)
            <label class='radio-inline'>
                <input type="radio" checked name="load_plugins" value="1"> true
            </label>
            <label class='radio-inline'>
                <input type="radio" name="load_plugins" value="2"> false
            </label>
        @else
            <label class='radio-inline'>
                <input type="radio" name="load_plugins" value="1"> true
            </label>
            <label class='radio-inline'>
                <input type="radio" checked name="load_plugins" value="2"> false
            </label>
        @endif
    @endif
    </div>
    </div>
    </div>

    <div class='form-group header-group-0 ' id='form-group-log_level' style="">
    <label class='control-label col-sm-2'>log_level
    <span class='text-danger' title='This field is required'>*</span>
    </label>
    <div>
    <div class="col-sm-10">
    @if (empty($row[init][log_level]))
        <label class='radio-inline'>
            <input type="radio" name="log_level" value="debug"> debug
        </label>
        <label class='radio-inline'>
            <input type="radio" checked name="log_level" value="error"> error
        </label>
    @else
        @if ($row[init][log_level] == 'debug')
            <label class='radio-inline'>
                <input type="radio" checked name="log_level" value="debug"> debug
            </label>
            <label class='radio-inline'>
                <input type="radio" name="log_level" value="error"> error
            </label>
        @else
            <label class='radio-inline'>
                <input type="radio" name="log_level" value="debug"> debug
            </label>
            <label class='radio-inline'>
                <input type="radio" checked name="log_level" value="error"> error
            </label>
        @endif
    @endif
    </div>
    </div>
    </div>

    <div class='form-group header-group-0 ' id='form-group-verbose' style="">
    <label class='control-label col-sm-2'>verbose
    <span class='text-danger' title='This field is required'>*</span>
    </label>
    <div>
    <div class="col-sm-10">
    @if (empty($row[init][verbose]))
        <label class='radio-inline'>
            <input type="radio" name="verbose" value="1"> true
        </label>
        <label class='radio-inline'>
            <input type="radio" checked name="verbose" value="2"> false
        </label>
    @else
        @if ($row[init][verbose] == 1)
            <label class='radio-inline'>
                <input type="radio" checked name="verbose" value="1"> true
            </label>
            <label class='radio-inline'>
                <input type="radio" name="verbose" value="2"> false
            </label>
        @else
            <label class='radio-inline'>
                <input type="radio" name="verbose" value="1"> true
            </label>
            <label class='radio-inline'>
                <input type="radio" checked name="verbose" value="2"> false
            </label>
        @endif
    @endif
    </div>
    </div>
    </div>
    <div class='form-group header-group-0 ' id='form-group-width' style="">
    <label class='control-label col-sm-2'>width</label>

    <div class="col-sm-10">
    <input type='text' maxlength=70 class='form-control' name="width" id="width" value='{{$row[init][width]}}'/>
    <div class="text-danger"></div>
    <p class='help-block'></p>
    </div>
    </div>
    <div class='form-group header-group-0 ' id='form-group-height' style="">
    <label class='control-label col-sm-2'>height</label>

    <div class="col-sm-10">
    <input type='text' maxlength=70 class='form-control' name="height" id="height" value='{{$row[init][height]}}'/>
    <div class="text-danger"></div>
    <p class='help-block'></p>
    </div>
    </div>
    @endif
    <div class='form-group header-group-0 ' id='form-group-step' style="">
    <label class='control-label col-sm-2'>步骤
    <span class='text-danger' title='This field is required'>*</span>
    </label>
    <div class="col-sm-10">
    <div data-force="18" class="layer-block" style="float: left;width: 50%;border: double">
        <ul id="bar" class="block__list block__list_tags">
        <li></li>
        </ul>
    </div>

    <div style="clear: both;"></div>
    </div>
    </div>

    <div class='form-group header-group-0 ' id='form-group-cron_type' style="">
    <label class='control-label col-sm-2'>cron_type
    <span class='text-danger' title='This field is required'>*</span>
    </label>
    <div>
    <div class="col-sm-10">
    @if (empty($row))
        <label class='radio-inline'>
            <input type="radio" checked name="cron_type" value="1"> 持续执行
        </label>
        <br>
        <label class='radio-inline'>
            <input type="radio" name="cron_type" value="2"> 每分钟执行一次
        </label>
        <br>
        <label class='radio-inline'>
            <input type="radio" name="cron_type" value="3"> 每五分钟执行一次
        </label>
        <br>
        <label class='radio-inline'>
            <input type="radio" name="cron_type" value="4"> 每十五分钟执行一次
        </label>
    @else
        @if ($row[cron_type] == 1)
            <label class='radio-inline'>
                <input type="radio" checked name="cron_type" value="1"> 持续执行
            </label>
            <br>
        @else
            <label class='radio-inline'>
                <input type="radio" name="cron_type" value="1"> 持续执行
            </label>
            <br>
        @endif
        @if ($row[cron_type] == 2)
            <label class='radio-inline'>
                <input type="radio" checked name="cron_type" value="2"> 每分钟执行一次
            </label>
            <br>
        @else
            <label class='radio-inline'>
                <input type="radio" name="cron_type" value="2"> 每分钟执行一次
            </label>
            <br>
        @endif

        @if ($row[cron_type] == 3)
            <label class='radio-inline'>
                <input type="radio" checked name="cron_type" value="3"> 每五分钟执行一次
            </label>
            <br>
        @else
            <label class='radio-inline'>
                <input type="radio" name="cron_type" value="3"> 每五分钟执行一次
            </label>
            <br>
        @endif
        @if ($row[cron_type] == 4)
            <label class='radio-inline'>
                <input type="radio" checked name="cron_type" value="4"> 每十五分钟执行一次
            </label>
            <br>
        @else
            <label class='radio-inline'>
                <input type="radio" name="cron_type" value="4"> 每十五分钟执行一次
            </label>
            <br>
        @endif
    @endif
    </div>
    </div>
    </div>
    </div>
    @endif
</div><!-- /.box-body -->

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
</div>
</div>
<aside class="control-sidebar control-sidebar-dark" style="position: fixed;">
    <ul id="foo" class="block__list block__list_words" style="overflow-y: auto; overflow-x: hidden;">
    @foreach($script_model as $key=>$value)
        <li>
        <p style="display: none;">模块id:{{$value->id}}</p>
        <p>模块名:{{$value->name}}</p>
        <p>模块描述:{{$value->description}}</p>
        <!-- <span>代码结构:{{$value->structure}}</span> -->
        <!-- <span>参数规则:{{$value->parameters}}</span> -->
        <br>
        @if (empty(json_decode($value->parameters, true)))
            <span><input type="hidden" {{$requires}} name="script_model_params[{{$value->id}}][]" value=""></span>
        @else
        @foreach(json_decode($value->parameters, true) as $parametersValue)
            <span>{{$parametersValue[name]}}:</span>
            <br>
        @if ($parametersValue[requires] == 'true')
            <?php $requires = required ?>
            @else
            <?php $requires = '' ?>
        @endif

        @if ($parametersValue[type] == 'string')
            <span><input type="type" {{$requires}} name="script_model_params[{{$value->id}}][]" value="{{$parametersValue['default']}}"></span>
            <br>
        @elseif ($parametersValue[type] == 'json')
            <span>
            <textarea {{$requires}} name="script_model_params[{{$value->id}}][]" maxlength=5000 rows="3" cols="50">{{$parametersValue['default']}}</textarea>
            </span>
            <br>
        @elseif ($parametersValue[type] == 'boole')
            @if ($parametersValue['default'] == 'true')
            <span><input {{$requires}} type="radio" checked name="script_model_params[{{$value->id}}][]" value="1"> true</span>
            <span></span>
            <span><input {{$requires}} type="radio" name="script_model_params[{{$value->id}}][]" value="2"> false</span>
            <br>
            @else
            <span><input {{$requires}} type="radio" name="script_model_params[{{$value->id}}][]" value="1"> true</span>
            <span></span>
            <span><input {{$requires}} type="radio" checked name="script_model_params[{{$value->id}}][]" value="2"> false</span>
            <br>
            @endif
        @elseif ($parametersValue[type] == 'text')
            <span>
            <textarea {{$requires}} name="script_model_params[{{$value->id}}][]" maxlength=5000 rows="3" cols="50">{{$parametersValue['default']}}</textarea>
            </span>
            <br>
        @endif
        @endforeach
        @endif
        </li>
    @endforeach
    </ul>
</aside>
</div><!--END AUTO MARGIN-->

<script src="http://rubaxa.github.io/Sortable/Sortable.js"></script>
<style type="text/css">
    .control-sidebar ul {
        height: 800px;
        padding:0 0 0 0;
        margin:0 0 0 0;
        list-style-type:none;
    }
    .control-sidebar ul li {
        height: 100px;
        text-align: center;
        padding: 10px;
        border: solid;
    }
    .control-sidebar ul li span {
        display: none;
    }

    .control-sidebar ul li:hover {
        background: #555555;
    }
    .layer-block ul {
        padding:0 0 0 0;
        margin:0 0 0 0;
        list-style-type:none;
    }
    .layer-block ul li {
        display: block;
        padding: 10px;
        border: solid;
    }
</style>
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
    });
</script>

@endsection
