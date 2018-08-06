@extends('crudbooster::admin_template')

@section('content')

   @if($index_statistic)
      <div id='box-statistic' class='row'>
      @foreach($index_statistic as $stat)
          <div  class="{{ ($stat['width'])?:'col-sm-3' }}">
              <div class="small-box bg-{{ $stat['color']?:'red' }}">
                <div class="inner">
                  <h3>{{ $stat['count'] }}</h3>
                  <p>{{ $stat['label'] }}</p>
                </div>
                <div class="icon">
                  <i class="{{ $stat['icon'] }}"></i>
                </div>
              </div>
          </div>
      @endforeach
      </div>
    @endif

   @if(!is_null($pre_index_html) && !empty($pre_index_html))
       {!! $pre_index_html !!}
   @endif


    @if(g('return_url'))
   <p><a href='{{g("return_url")}}'><i class='fa fa-chevron-circle-{{ trans('crudbooster.left') }}'></i> &nbsp; {{trans('crudbooster.form_back_to_list',['module'=>urldecode(g('label'))])}}</a></p>
    @endif

    @if($parent_table)
    <div class="box box-default">
      <div class="box-body table-responsive no-padding">
        <table class='table table-bordered'>
          <tbody>
            <tr class='active'>
              <td colspan="2"><strong><i class='fa fa-bars'></i> {{ ucwords(urldecode(g('label'))) }}</strong></td>
            </tr>
            @foreach(explode(',',urldecode(g('parent_columns'))) as $c)
            <tr>
              <td width="25%"><strong>
               @if(urldecode(g('parent_columns_alias')))
              {{explode(',',urldecode(g('parent_columns_alias')))[$loop->index]}}
              @else
              {{  ucwords(str_replace('_',' ',$c)) }}
               @endif
              </strong></td><td> {{ $parent_table->$c }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    @endif
     <div class="container kv-main"   id="showtest" style="display: none">

         <?php
            $action = CRUDBooster::mainpath("test-save");
            ?>
         <form enctype="multipart/form-data" action='{{$action}}' method='post' id='form'>

             <br/>
             <div class="input-group">
                 <span class="input-group-addon">测试地址</span>
                 <input type="text" class="form-control" name='test_url' required placeholder="请填写测试地址" >
                 <input type="hidden" class="task-id" name="task_id" >
             </div>
             <br/>
             {{ csrf_field() }}
             <div align="center">
                 <!-- <button type="button" class="btn btn-primary" onclick="submit()">保存</button> -->
                 <input type="submit" name="submit" value='保存' class='btn btn-primary'>
             </div>
         </form>

     </div>

    <div class="box">
      <div class="box-header">
        <div class="box-tools pull-{{ trans('crudbooster.right') }}" style="position: relative;margin-top: -5px;margin-right: -10px">

              @if($button_filter)
              <a style="margin-top:-23px" onclick = "showFilter()" id='btn_advanced_filter' data-url-parameter='{{$build_query}}' title='{{trans('crudbooster.filter_dialog_title')}}' class="btn btn-sm btn-default {{(Request::get('filter_column'))?'active':''}}">
                <i class="fa fa-filter"></i> {{trans("crudbooster.button_filter")}}
              </a>
              @endif

        </div>

        <br style="clear:both"/>

      </div>
<table id='table_dashboard' class='table table-striped table-bordered'>
  <thead>
      <tr>
        <th>ID</th>
        <th>需求ID</th>
        <th>脚本ID</th>
        <th>任务名称</th>
        <th>是否翻墙</th>
        <th>执行规则</th>
        <th>数据类型</th>
        <th>状态</th>
        <th style="float: right;">操作</th>
       </tr>
  </thead>
  <tbody>
    @foreach($result as $row)
      <tr>
        <td>{{$row->id}}</td>
        <td>{{$row->requirement_pool_id}}</td>
        <td>{{$row->script_id}}</td>
        <td>{{$row->name}}</td>
        @if ($row->is_proxy == 1)
            <td>翻墙</td>
        @else
            <td>不翻墙</td>
        @endif
        @if ($row->cron_type == 4)
            <td><span style="color:#f00">只执行一次</span></td>
        @else
            <td>111</td>
        @endif
        @if ($row->data_type == 1)
            <td>casperJs</td>
        @elseif ($row->data_type == 2)
            <td>html</td>
        @elseif ($row->data_type == 3)
            <td>api</td>
        @endif
        @if ($row->status == 1)
            <td><a class='btn btn-xs btn-warning'><i></i>初始化</a></td>
        @elseif ($row->status == 2)
            <td><a class='btn btn-xs btn-success'><i></i>已启动</a></td>
        @endif
        <td style="float: right;">
          @if($row->status == 1)
            <a class='btn btn-xs btn-success' title='启动' href='{{CRUDBooster::mainpath("start-up/$row->id")}}'>
            <i class='fa fa-play'></i> 启动</a>
          @endif

          @if($row->status == 2)
            <a class='btn btn-xs btn-warning' title='停止' href="{{CRUDBooster::mainpath("stop-down/$row->id")}}">
            <i class='fa fa-stop'></i>停止</a>
          @endif
          <a class='btn btn-xs btn-primary' title='测试' onclick="showTestUrlForm({{$row->id}})">
          <i class='fa fa-play'></i> 测试</a>
          <a class="btn btn-xs btn-success" title="结果列表" href="{{CRUDBooster::mainpath("stop-down/$row->id")}}">
          <i class="fa fa-bars"></i> 结果列表</a>
        </td>
       </tr>
    @endforeach
  </tbody>
  <script type="text/javascript">

    function showTestUrlForm(id) {
        $('.task-id').val(id);

        layer.open({
            type: 1,
            title:['测试', 'font-size:14px;'],
            area: ['600px', '160px'],
            shadeClose: true, //点击遮罩关闭
            content: $("#showtest"),
            cancel: function(index, layero){

                $("#showupload").hide()
            }
        });
    }

    function postTest()
    {

    }

  </script>

</table>
<p>{!! urldecode(str_replace("/?","?",$result->appends(Request::all())->render())) !!}</p>

      </div>
    </div>
<!-- MODAL FOR SORTING DATA-->
            <div class="modal fade" tabindex="-1" role="dialog" id='advanced_filter_modal'>
              <div class="modal-dialog modal-lg">
                <div class="modal-content" >
                  <div class="modal-header">
                    <button class="close" aria-label="Close" type="button" data-dismiss="modal">
                    <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title"><i class='fa fa-filter'></i> {{trans("crudbooster.filter_dialog_title")}}</h4>
                  </div>
                  <form method='get' action=''>
                    <div class="modal-body">
                      <?php foreach($columns as $key => $col):?>
                        <?php if( isset($col['image']) || isset($col['download']) || $col['visible']===FALSE) continue;?>

                      <div class='form-group'>

                        <div class='row-filter-combo row'>

                          <div class="col-sm-2">
                            <strong>{{$col['label']}}</strong>
                          </div>

                          <div class='col-sm-3'>
                            <select name='filter_column[{{$col["field_with"]}}][type]' data-type='{{$col["type_data"]}}' class="filter-combo form-control">
                              <option value=''>** {{trans("crudbooster.filter_select_operator_type")}}</option>
                              @if(in_array($col['type_data'],['string','varchar','text','char']))<option {{ (CRUDBooster::getTypeFilter($col["field_with"]) == 'like')?"selected":"" }} value='like'>{{trans("crudbooster.filter_like")}}</option> @endif
                              @if(in_array($col['type_data'],['string','varchar','text','char']))<option {{ (CRUDBooster::getTypeFilter($col["field_with"]) == 'not like')?"selected":"" }} value='not like'>{{trans("crudbooster.filter_not_like")}}</option>@endif

                              <option typeallow='all' {{ (CRUDBooster::getTypeFilter($col["field_with"]) == '=')?"selected":"" }} value='='>{{trans("crudbooster.filter_equal_to")}}</option>
                              @if(in_array($col['type_data'],['int','integer','double','float','decimal','time']))<option {{ (CRUDBooster::getTypeFilter($col["field_with"]) == '>=')?"selected":"" }} value='>='>{{trans("crudbooster.filter_greater_than_or_equal")}}</option>@endif
                              @if(in_array($col['type_data'],['int','integer','double','float','decimal','time']))<option {{ (CRUDBooster::getTypeFilter($col["field_with"]) == '<=')?"selected":"" }} value='<='>{{trans("crudbooster.filter_less_than_or_equal")}}</option>@endif
                              @if(in_array($col['type_data'],['int','integer','double','float','decimal','time']))<option {{ (CRUDBooster::getTypeFilter($col["field_with"]) == '<')?"selected":"" }} value='<'>{{trans("crudbooster.filter_less_than")}}</option>@endif
                              @if(in_array($col['type_data'],['int','integer','double','float','decimal','time']))<option {{ (CRUDBooster::getTypeFilter($col["field_with"]) == '>')?"selected":"" }} value='>'>{{trans("crudbooster.filter_greater_than")}}</option>@endif
                              <option typeallow='all' {{ (CRUDBooster::getTypeFilter($col["field_with"]) == '!=')?"selected":"" }} value='!='>{{trans("crudbooster.filter_not_equal_to")}}</option>
                              <option typeallow='all' {{ (CRUDBooster::getTypeFilter($col["field_with"]) == 'in')?"selected":"" }} value='in'>{{trans("crudbooster.filter_in")}}</option>
                              <option typeallow='all' {{ (CRUDBooster::getTypeFilter($col["field_with"]) == 'not in')?"selected":"" }} value='not in'>{{trans("crudbooster.filter_not_in")}}</option>
                              @if(in_array($col['type_data'],['date','time','datetime','int','integer','double','float','decimal','timestamp']))<option {{ (CRUDBooster::getTypeFilter($col["field_with"]) == 'between')?"selected":"" }} value='between'>{{trans("crudbooster.filter_between")}}</option>@endif
                              <option {{ (CRUDBooster::getTypeFilter($col["field_with"]) == 'empty')?"selected":"" }} value='empty'>Empty ( or Null)</option>
                            </select>
                          </div><!--END COL_SM_4-->



                          <div class='col-sm-5'>
                            <input type='text' class='filter-value form-control' style="{{ (CRUDBooster::getTypeFilter($col["field_with"]) == 'between')?"display:none":"display:block"}}" disabled name='filter_column[{{$col["field_with"]}}][value]' value='{{ (!is_array(CRUDBooster::getValueFilter($col["field_with"])))?CRUDBooster::getValueFilter($col["field_with"]):"" }}'>

                            <div class='row between-group' style="{{ (CRUDBooster::getTypeFilter($col["field_with"]) == 'between')?"display:block":"display:none" }}">
                              <div class='col-sm-6'>
                                <div class='input-group {{ ($col["type_data"] == "time")?"bootstrap-timepicker":"" }}'>
                                  <span class="input-group-addon">{{trans("crudbooster.filter_from")}}:</span>
                                  <input
                                  {{ (CRUDBooster::getTypeFilter($col["field_with"]) != 'between')?"disabled":"" }}
                                  type='text'
                                  class='filter-value-between form-control {{ (in_array($col["type_data"],["date","datetime","timestamp"]))?"datepicker":"timepicker" }}' readonly placeholder='{{$col["label"]}} {{trans("crudbooster.filter_from")}}' name='filter_column[{{$col["field_with"]}}][value][]' value='<?php
                                  $value = CRUDBooster::getValueFilter($col["field_with"]);
                                  echo (CRUDBooster::getTypeFilter($col["field_with"])=='between')?$value[0]:"";
                                  ?>'>
                                </div>
                              </div>
                              <div class='col-sm-6'>
                                <div class='input-group {{ ($col["type_data"] == "time")?"bootstrap-timepicker":"" }}'>
                                  <span class="input-group-addon">{{trans("crudbooster.filter_to")}}:</span>
                                  <input
                                  {{ (CRUDBooster::getTypeFilter($col["field_with"]) != 'between')?"disabled":"" }}
                                  type='text'
                                  class='filter-value-between form-control {{ (in_array($col["type_data"],["date","datetime","timestamp"]))?"datepicker":"timepicker" }}' readonly placeholder='{{$col["label"]}} {{trans("crudbooster.filter_to")}}' name='filter_column[{{$col["field_with"]}}][value][]' value='<?php
                                  $value = CRUDBooster::getValueFilter($col["field_with"]);
                                  echo (CRUDBooster::getTypeFilter($col["field_with"])=='between')?$value[1]:"";
                                  ?>'>
                                </div>
                              </div>
                            </div>
                          </div><!--END COL_SM_6-->


                          <div class='col-sm-2'>
                              <select class='form-control' name='filter_column[{{$col["field_with"]}}][sorting]'>
                                  <option value=''>** Sorting</option>
                                  <option {{ (CRUDBooster::getSortingFilter($col["field_with"]) == 'asc')?"selected":"" }} value='asc'>{{trans("crudbooster.filter_ascending")}}</option>
                                  <option {{ (CRUDBooster::getSortingFilter($col["field_with"]) == 'desc')?"selected":"" }} value='desc'>{{trans("crudbooster.filter_descending")}}</option>
                              </select>
                          </div><!--END_COL_SM_2-->

                        </div>

                      </div>
                      <?php endforeach;?>

                    </div>
                    <div class="modal-footer" align="right">
                      <button class="btn btn-default" type="button" data-dismiss="modal">{{trans("crudbooster.button_close")}}</button>
                      <button class="btn btn-default btn-reset" type="reset" onclick='location.href="{{Request::get("lasturl")}}"' >{{trans("crudbooster.button_reset")}}</button>
                      <button class="btn btn-primary btn-submit" type="submit">{{trans("crudbooster.button_submit")}}</button>
                    </div>
                    {!! CRUDBooster::getUrlParameters(['filter_column','lasturl']) !!}
                    <input type="hidden" name="lasturl" value="{{Request::get('lasturl')?:Request::fullUrl()}}">
                  </form>
                </div>
                <!-- /.modal-content -->
              </div>
            </div>

            <link href="{{URL::asset('/css/fileinput.css')}}" media="all" rel="stylesheet" type="text/css" />

            <script src="{{URL::asset('/js/jquery-1.9.1.min.js')}}"></script>

            <script src="{{URL::asset('/js/fileinput.js')}}" type="text/javascript"></script>

            <script src="{{URL::asset('/js/bootstrap.min.js')}}" type="text/javascript"></script>

            <script src="{{URL::asset('/js/layer-v3.1.1/layer/layer.js')}}"></script>





   @if(!is_null($post_index_html) && !empty($post_index_html))
       {!! $post_index_html !!}
   @endif

@endsection
