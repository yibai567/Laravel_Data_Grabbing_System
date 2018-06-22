@extends('crudbooster::admin_template')

@section('content')

<style type="text/css">
    ul{
        list-style:none;
    }
    .fillter{
        margin-left:70px;
    }
    .fillter li{
    /*float:left;*/
    }
    .fillter li label{
        display:block;
        margin: 10px 0;
    }
    .fillter ul{
        position:relative;
        bottom:10px;
    }
</style>

<div class="panel panel-default" >
    <div class="panel-heading">
        <strong>
            <i class='{{CRUDBooster::getCurrentModule()->icon}}'></i>第三步、过滤器、Action配置
        </strong>
    </div>
    <?php
       $action = CRUDBooster::mainpath("add-save");

        $return_url = ($return_url)?:g('return_url');
    ?>
    <form class='form-horizontal' method='post' id="form" enctype="multipart/form-data" action='{{$action}}'
        <div class="panel-body" style="padding:79px 0px 75px 0px;width: 80%;">
                <div class="box-body" id="parent-form-area">
                     <input type="hidden" name="_token" value="{{ csrf_token() }}">
                     <input type='hidden' name='return_url' value='{{ @$return_url }}'/>
                     <div class="row container" >
                      @foreach($projects as $value)
                      <div class="col-md-4 table-bordered" style="background-color: #eee;">
                        <div class='form-group'>
                            <label class='control-label col-sm-3'>项目名:</label>
                            <div class="col-xs-5">
                                <label class='control-label'>{{$value['name']}}</label>
                                <input type="hidden" name="id" value="{{$id}}">
                            </div>
                        </div>
                        <div class='form-group'>
                            <label class='control-label col-sm-3'>过滤器:</label>
                            <div class="checkbox fillter">
                            <ul>
                                @if(!empty($filter))
                                    @foreach($filter as $key => $filterValue)
                                        @if (!empty($script))
                                            @if(!empty($script['filters']))
                                                <?php $newFilters = json_decode($script['filters'], true)?>
                                                <?php $checked = '' ?>
                                                @if (!empty($newFilters[$value['id']]))
                                                    <?php $newFiltersKey = array_keys($newFilters[$value['id']])?>
                                                    @if (in_array($filterValue['id'],$newFiltersKey))
                                                        <?php $checked = 'checked'?>
                                                    @endif
                                                @endif
                                            @endif
                                        @else
                                            <?php $checked = '' ?>
                                        @endif
                                    <li>
                                        <label><input type="checkbox" {{$checked}} class="checkbox_{{$value['id']}}_{{$filterValue['id']}}" name="project_config[filters][{{$value['id']}}][{{$filterValue['id']}}]" value="" onclick="filter_checkbox({{$value['id']}},{{$filterValue['id']}},{{$key}})">{{$filterValue['name']}}</label>

                                        @if (!empty($newFilters[$value['id']][$filterValue['id']]))
                                            @foreach($newFilters[$value['id']][$filterValue['id']] as $paramsKey=>$paramsValue)
                                                <label>{{$paramsKey}}: <input type="text" name="project_config[filters][{{$value['id']}}][{{$filterValue['id']}}][{{$paramsKey}}]" value="{{$paramsValue}}" class="filters_params_{{$value['id']}}_{{$key}}"></label>
                                            @endforeach
                                        @else
                                            @if(!empty($filterValue['params']))
                                                @foreach(json_decode($filterValue['params'], true) as $paramsValue)
                                                    <label>{{$paramsValue}}: <input type="text" name="project_config[filters][{{$value['id']}}][{{$filterValue['id']}}][{{$paramsValue}}]" value="" disabled="disabled" class="filters_params_{{$value['id']}}_{{$key}}"></label>
                                                @endforeach
                                            @endif
                                        @endif
                                    @endforeach
                                </li>
                                @else
                                    暂无配置
                                @endif
                            </ul>
                            </div>
                        </div>
                        <div class='form-group'>
                            <label class='control-label col-sm-3'>action:</label>
                            <div class="checkbox fillter">
                            <ul>
                                @if(!empty($action_info))
                                    @foreach($action_info as $key => $actionValue)
                                        @if (!empty($script))
                                            @if(!empty($script['actions']))
                                                <?php $newActions = json_decode($script['actions'], true)?>
                                                <?php $checked = '' ?>
                                                @if (!empty($newActions[$value['id']]))
                                                    <?php $newActionsKey = array_keys($newActions[$value['id']])?>
                                                    @if (in_array($actionValue['id'],$newActionsKey))
                                                        <?php $checked = 'checked'?>
                                                    @endif
                                                @endif
                                            @endif
                                        @else
                                            <?php $checked = '' ?>
                                        @endif
                                    <li>
                                        <label><input type="checkbox" {{$checked}} name="project_config[actions][{{$value['id']}}][{{$actionValue['id']}}]" class="actions_{{$value['id']}}_{{$actionValue['id']}}" value="" onclick="actions_checkbox({{$value['id']}},{{$actionValue['id']}},{{$key}})">{{$actionValue['name']}}</label>
                                        @if (!empty($newActions[$value['id']][$actionValue['id']]))
                                            @foreach($newActions[$value['id']][$actionValue['id']] as $paramsKey=>$paramsValue)
                                                <label>{{$paramsKey}}: <input type="text" name="project_config[actions][{{$value['id']}}][{{$actionValue['id']}}][{{$paramsKey}}]" value="{{$paramsValue}}" class="actions_params_{{$value['id']}}_{{$key}}"></label>
                                            @endforeach
                                        @else
                                            @if(!empty($actionValue['params']))
                                                @foreach(json_decode($actionValue['params'], true) as $paramsValue)
                                                    <label>{{$paramsValue}}: <input type="text" name="project_config[actions][{{$value['id']}}][{{$actionValue['id']}}][{{$paramsValue}}]" value="" disabled="disabled" class="actions_params_{{$value['id']}}_{{$key}}"></label>
                                                @endforeach
                                            @endif
                                        @endif
                                    </li>
                                    @endforeach
                                @else
                                    暂无配置
                                @endif
                            </ul>

                            </div>
                        </div>
                        </div>
                       @endforeach
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

<script type="text/javascript">
    function filter_checkbox(project_id, id, key){
        if ($('.checkbox_' + project_id + '_' + id).is(':checked')) {
            $('.filters_params_' + project_id + '_' + key).each(function(){
                $(this).removeAttr('disabled');
            });
        } else {
            $('.filters_params_' + project_id + '_' + key).each(function(){
                    $(this).attr("disabled",'disabled');
            });
        }
    }
    function actions_checkbox(project_id, id, key){
        if ($('.actions_' + project_id + '_' + id).is(':checked')) {
            $('.actions_params_' + project_id + '_' + key).each(function(){
                $(this).removeAttr('disabled');
            });
        } else {
            $('.actions_params_' + project_id + '_' + key).each(function(){
                    $(this).attr("disabled",'disabled');
            });
        }
    }
</script>

@endsection
