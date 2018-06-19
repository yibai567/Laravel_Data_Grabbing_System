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
            <i class='{{CRUDBooster::getCurrentModule()->icon}}'></i>第三步过滤器、Action配置
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
                      <div class="col-md-4 table-bordered">
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
                                                    @if ($newFiltersKey[$key] == $filterValue['id'])
                                                        <?php $checked = 'checked'?>
                                                    @endif
                                                @endif
                                            @endif
                                        @else
                                            <?php $checked = '' ?>
                                        @endif
                                    <li>
                                        <label><input type="checkbox" {{$checked}} name="project_config[filters][{{$value['id']}}][{{$filterValue['id']}}]" value="">{{$filterValue['name']}}</label>
                                        @if (!empty($newFilters[$value['id']][$filterValue['id']]))
                                            @foreach($newFilters[$value['id']][$filterValue['id']] as $key=>$paramsValue)
                                                <label>{{$key}}: <input type="text" name="project_config[filters][{{$value['id']}}][{{$filterValue['id']}}][{{$key}}]" value="{{$paramsValue}}"></label>
                                            @endforeach
                                        @else
                                            @if(!empty($filterValue['params']))
                                                @foreach(json_decode($filterValue['params'], true) as $paramsValue)
                                                    <label>{{$paramsValue}}: <input type="text" name="project_config[filters][{{$value['id']}}][{{$filterValue['id']}}][{{$paramsValue}}]" value=""></label>
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
                                                    @if ($newActionsKey[$key] == $actionValue['id'])
                                                        <?php $checked = 'checked'?>
                                                    @endif
                                                @endif
                                            @endif
                                        @else
                                            <?php $checked = '' ?>
                                        @endif
                                    <li>
                                        <label><input type="checkbox" {{$checked}} name="project_config[actions][{{$value['id']}}][{{$actionValue['id']}}]" value="">{{$actionValue['name']}}</label>
                                        @if (!empty($newActions[$value['id']][$actionValue['id']]))
                                            @foreach($newActions[$value['id']][$actionValue['id']] as $key=>$paramsValue)
                                                <label>{{$key}}: <input type="text" name="project_config[actions][{{$value['id']}}][{{$actionValue['id']}}][{{$key}}]" value="{{$paramsValue}}"></label>
                                            @endforeach
                                        @else
                                            @if(!empty($actionValue['params']))
                                                @foreach(json_decode($actionValue['params'], true) as $paramsValue)
                                                    <label>{{$paramsValue}}: <input type="text" name="project_config[actions][{{$value['id']}}][{{$actionValue['id']}}][{{$paramsValue}}]" value=""></label>
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
    function check(project_id, id, str){
        if (str === undefined) {
            return true;
        }

        strs = str.split(",");
        var input = "";
        for (i=0;i<strs.length ;i++ )
        {
            input += '<div class="form-group"><label class="control-label col-sm-3">'+strs[i]+':</label><div><input type="text" name="project_config[actions]['+project_id+']['+id+']['+strs[i]+']" value=""></div></div>';
        }
        var checkbox = document.getElementById('checkbox_'+project_id+'_'+id);
          if(checkbox.checked){
            document.getElementById('div_'+project_id).innerHTML = input;
          }else{
            document.getElementById('div_'+project_id).innerHTML = "";
          }
    }
</script>

@endsection
