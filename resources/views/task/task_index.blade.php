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
        @if($button_bulk_action && ( ($button_delete && CRUDBooster::isDelete()) || $button_selected) )
        <div class="pull-{{ trans('crudbooster.left') }}">
          <div class="selected-action" style="display:inline-block;position:relative;">
            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class='fa fa-check-square-o'></i> {{trans("crudbooster.button_selected_action")}}
              <span class="fa fa-caret-down"></span></button>
              <ul class="dropdown-menu">
                @if($button_delete && CRUDBooster::isDelete())
                  <li><a href="javascript:void(0)" data-name='delete' title='{{trans('crudbooster.action_delete_selected')}}'><i class="fa fa-trash"></i> {{trans('crudbooster.action_delete_selected')}}</a></li>
                @endif

                @if($button_selected)
                  @foreach($button_selected as $button)
                    <li><a href="javascript:void(0)" data-name='{{$button["name"]}}' title='{{$button["label"]}}'><i class="fa fa-{{$button['icon']}}"></i> {{$button['label']}}</a></li>
                  @endforeach
                @endif
              </ul><!--end-dropdown-menu-->
          </div><!--end-selected-action-->
        </div><!--end-pull-left-->
        @endif
        <div class="box-tools pull-{{ trans('crudbooster.right') }}" style="position: relative;margin-top: -5px;margin-right: -10px">

              @if($button_filter)
              <a style="margin-top:-23px" onclick = "showFilter()" id='btn_advanced_filter' data-url-parameter='{{$build_query}}' title='{{trans('crudbooster.filter_dialog_title')}}' class="btn btn-sm btn-default {{(Request::get('filter_column'))?'active':''}}">
                <i class="fa fa-filter"></i> {{trans("crudbooster.button_filter")}}
              </a>
              @endif
              <form method='get' style="display:inline-block;width: 260px;" action='{{Request::url()}}'>
                <div class="input-group">
                  <input type="text" name="q" value="{{ Request::get('q') }}" class="form-control input-sm pull-{{ trans('crudbooster.right') }}" placeholder="{{trans('crudbooster.filter_search')}}"/>
                    {!! CRUDBooster::getUrlParameters(['q']) !!}
                      <div class="input-group-btn">
                              @if(Request::get('q'))
                                  <?php
                                  $parameters = Request::all();
                                  unset($parameters['q']);
                                  $build_query = urldecode(http_build_query($parameters));
                                  $build_query = ($build_query)?"?".$build_query:"";
                                  $build_query = (Request::all())?$build_query:"";
                                  ?>
                                  <button type='button' onclick='location.href="{{ CRUDBooster::mainpath().$build_query}}"' title="{{trans('crudbooster.button_reset')}}" class='btn btn-sm btn-warning'><i class='fa fa-ban'></i></button>
                              @endif
                              <button type='submit' class="btn btn-sm btn-default"><i class="fa fa-search"></i></button>
                          </div>
                      </div>
                  </form>
                  <form method='get' id='form-limit-paging' style="display:inline-block" action='{{Request::url()}}'>
                      {!! CRUDBooster::getUrlParameters(['limit']) !!}
                      <div class="input-group">
                          <select onchange="$('#form-limit-paging').submit()" name='limit' style="width: 56px;"  class='form-control input-sm'>
                              <option {{($limit==5)?'selected':''}} value='5'>5</option>
                              <option {{($limit==10)?'selected':''}} value='10'>10</option>
                              <option {{($limit==20)?'selected':''}} value='20'>20</option>
                              <option {{($limit==25)?'selected':''}} value='25'>25</option>
                              <option {{($limit==50)?'selected':''}} value='50'>50</option>
                              <option {{($limit==100)?'selected':''}} value='100'>100</option>
                              <option {{($limit==200)?'selected':''}} value='200'>200</option>
                          </select>
                      </div>
                  </form>
        </div>

        <br style="clear:both"/>

      </div>
<table id='table_dashboard' class='table table-striped table-bordered'>
  <thead>
      <tr>
        @if($button_bulk_action)
          <th width='3%'><input type='checkbox' id='checkall'/></th>
        @endif
        <th>ID</th>
        <th>需求ID</th>
        <th>脚本ID</th>
        <th>任务名称</th>
        <th>测试地址</th>
        <th>是否翻墙</th>
        <th>执行规则</th>
        <th>数据类型</th>
        <th>语言类型</th>
        <th>状态</th>
        <th style="float: right;">操作</th>
       </tr>
  </thead>
  <tbody>
    @foreach($result as $row)
      <tr>
        @if($button_bulk_action)
          <th width='3%'><input type='checkbox' id='checkall'/></th>
        @endif
        <td>{{$row->id}}</td>
        <td>{{$row->requirement_pool_id}}</td>
        <td>{{$row->script_id}}</td>
        <td>{{$row->name}}</td>
        <td><span style="width:150px;overflow: hidden; display: -webkit-box;text-overflow: ellipsis; word-break: break-all;-webkit-box-orient: vertical;-webkit-line-clamp: 1;">{{$row->test_url}}</span></td>
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
        @if ($row->language_type == 1)
            <td>英文</td>
        @elseif ($row->language_type == 2)
            <td>中文</td>
        @else
            <td>未知</td>
        @endif
        @if ($row->status == 1)
            <td><a class='btn btn-xs btn-warning'><i></i>初始化</a></td>
        @elseif ($row->status == 2)
            <td><a class='btn btn-xs btn-success'><i></i>已启动</a></td>
        @endif
        <td style="float: right;">
          @if($row->status == 1 && $row->cron_type !== 4)
            <a class='btn btn-xs btn-success' title='启动' href='{{CRUDBooster::mainpath("start-up/$row->id")}}'>
            <i class='fa fa-play'></i> 启动</a>
          @endif

          @if($row->status == 2 && $row->cron_type !== 4)
            <a class='btn btn-xs btn-warning' title='停止' href="{{CRUDBooster::mainpath("stop-down/$row->id")}}">
            <i class='fa fa-stop'></i>停止</a>
          @endif
          <a class='btn btn-xs btn-primary' title='测试' onclick="showTestUrlForm({{$row->id}})">
          <i class='fa fa-play'></i> 测试</a>
          <a class="btn btn-xs btn-success" title="结果列表" onclick='location.href="/admin/t_data?parent_table=t_task&parent_columns=id&parent_columns_alias=&parent_id={{$row->id}}&return_url=http://{{$_SERVER['HTTP_HOST']}}/admin/t_task_detail_list&foreign_key=task_id&label=任务详情列表"'>
          <i class="fa fa-bars"></i> 结果列表</a>
          <a class='btn btn-xs btn-primary btn-detail' title='详情' href='{{CRUDBooster::mainpath("detail/$row->id")}}'>
            <i class='fa fa-eye'></i>
          </a>
          @if(CRUDBooster::isDelete())
            <a class='btn btn-xs btn-warning btn-delete' href='javascript:void(0)' onclick="swal({
              title: '确认删除吗 ?',
              text: '删除之后将无法恢复!',
              type: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#ff0000',
              confirmButtonText: '是!',
              cancelButtonText: '否',
              closeOnConfirm: false },
              function(){  location.href='{{CRUDBooster::mainpath("delete/$row->id")}}' });">
              <i class="fa fa-trash"></i>
            </a>
          @endif
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

                $("#showtest").hide()
            }
        });
    }
    function showFilter() {
        $('#btn_advanced_filter').click(function() {
            $('#advanced_filter_modal').modal('show');
        })
        $(".filter-combo").change(function() {
            var n = $(this).val();
            var p = $(this).parents('.row-filter-combo');
            var type_data = $(this).attr('data-type');
            var filter_value = p.find('.filter-value');

            p.find('.between-group').hide();
            p.find('.between-group').find('input').prop('disabled',true);
            filter_value.val('').show().focus();
            switch(n) {
                default:
                    filter_value.removeAttr('placeholder').val('').prop('disabled',true);
                    p.find('.between-group').find('input').prop('disabled',true);
                    break;
                case 'like':
                case 'not like':
                    filter_value.attr('placeholder','{{trans("crudbooster.filter_eg")}} : {{trans("crudbooster.filter_lorem_ipsum")}}').prop('disabled',false);
                    break;
                case 'asc':
                    filter_value.prop('disabled',true).attr('placeholder','{{trans("crudbooster.filter_sort_ascending")}}');
                    break;
                case 'desc':
                    filter_value.prop('disabled',true).attr('placeholder','{{trans("crudbooster.filter_sort_descending")}}');
                    break;
                case '=':
                    filter_value.prop('disabled',false).attr('placeholder','{{trans("crudbooster.filter_eg")}} : {{trans("crudbooster.filter_lorem_ipsum")}}');
                    break;
                case '>=':
                    filter_value.prop('disabled',false).attr('placeholder','{{trans("crudbooster.filter_eg")}} : 1000');
                    break;
                case '<=':
                    filter_value.prop('disabled',false).attr('placeholder','{{trans("crudbooster.filter_eg")}} : 1000');
                    break;
                case '>':
                    filter_value.prop('disabled',false).attr('placeholder','{{trans("crudbooster.filter_eg")}} : 1000');
                    break;
                case '<':
                    filter_value.prop('disabled',false).attr('placeholder','{{trans("crudbooster.filter_eg")}} : 1000');
                    break;
                case '!=':
                    filter_value.prop('disabled',false).attr('placeholder','{{trans("crudbooster.filter_eg")}} : {{trans("crudbooster.filter_lorem_ipsum")}}');
                    break;
                case 'in':
                    filter_value.prop('disabled',false).attr('placeholder','{{trans("crudbooster.filter_eg")}} : {{trans("crudbooster.filter_lorem_ipsum_dolor_sit")}}');
                    break;
                case 'not in':
                    filter_value.prop('disabled',false).attr('placeholder','{{trans("crudbooster.filter_eg")}} : {{trans("crudbooster.filter_lorem_ipsum_dolor_sit")}}');
                    break;
                case 'between':
                    filter_value.val('').hide();
                    p.find('.between-group input').prop('disabled',false);
                    p.find('.between-group').show().focus();
                    p.find('.filter-value-between').prop('disabled',false);
                    break;
            }
        })
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
