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

    <div class="box">
      <div class="box-header">

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
        <th>ID</th>
        <th>任务名称</th>
        <th>资源地址</th>
        <th>数据类型</th>
        <th>内容类型</th>
        <th>执行频次</th>
        <th>是否翻墙</th>
        <th>是否截图</th>
        <th>最后执行时间</th>
        <th>状态</th>
        <th>修改时间</th>
        <th>操作</th>
       </tr>
  </thead>
  <tbody>
    @foreach($result as $row)
      <tr>
        <td>{{$row->id}}</td>
        <td>{{$row->name}}</td>
        <td>{{$row->resource_url}}</td>
        <td>{{$row->data_type}}</td>
        <td>{{$row->content_type}}</td>
        <td>{{$row->cron_type}}</td>
        <td>{{$row->is_proxy}}</td>
        <td>{{$row->is_capture_image}}</td>
        <td>{{$row->last_job_at}}</td>
        <td>{{$row->status_name}}</td>
        <td>{{$row->updated_at}}</td>
        <td>
          <!-- To make sure we have read access, wee need to validate the privilege -->
        @if($row->status == $item_status['testing'])
            @if((time()-strtotime($row->updated_at)) > 180)
                 <a class='btn btn-xs btn-warning' href='{{CRUDBooster::mainpath("test-fail/$row->id")}}'>
                        <i class="fa fa-play">停止</i>
                </a>
            @endif
        @endif

        @if($row->status == $item_status['test_success'])
            <a class='btn btn-xs btn-info' href='{{CRUDBooster::mainpath("test/$row->id")}}'>
                    <i class="fa fa-play">测试</i>
            </a>
             <a class='btn btn-xs btn-success' href='{{CRUDBooster::mainpath("start-up/$row->id")}}'>
                    <i class="fa fa-play">启动</i>
            </a>
        @endif
        @if($row->status == $item_status['test_fail'])
            <a class='btn btn-xs btn-info' href='{{CRUDBooster::mainpath("test/$row->id")}}'>
                    <i class="fa fa-play">测试</i>
            </a>
        @endif
        @if($row->status == $item_status['start'])
            <a class='btn btn-xs btn-info' href='{{CRUDBooster::mainpath("test/$row->id")}}'>
                    <i class="fa fa-play">测试</i>
            </a>
             <a class='btn btn-xs btn-warning' href='{{CRUDBooster::mainpath("stop-down/$row->id")}}'>
                    <i class="fa fa-play">停止</i>
            </a>
        @endif

        @if($row->status == $item_status['stop'])
            <a class='btn btn-xs btn-info' href='{{CRUDBooster::mainpath("test/$row->id")}}'>
                    <i class="fa fa-play">测试</i>
            </a>
             <a class='btn btn-xs btn-success' href='{{CRUDBooster::mainpath("start-up/$row->id")}}'>
                    <i class="fa fa-play">启动</i>
            </a>
        @endif

        <a class="btn btn-xs btn-warning" href="javascript:void(0)" onclick="query({{$row->id}})">测试结果</a>
        <a class='btn btn-xs btn-success' onclick='location.href="/admin/t_item_result?parent_table=t_item&parent_columns=id&parent_columns_alias=&parent_id={{$row->id}}&return_url=http://{{$_SERVER['HTTP_HOST']}}/admin/t_item&foreign_key=item_id&label=%E4%BB%BB%E5%8A%A1%E7%BB%93%E6%9E%9C"'>
                <i class="fa fa-bars">任务结果</i>
        </a>

        @if(CRUDBooster::isUpdate() && $button_edit)
            <a class='btn btn-xs btn-success btn-edit' href='{{CRUDBooster::mainpath("edit/$row->id")}}'>
                <i class="fa fa-pencil"></i>
            </a>
        @endif


        @if(CRUDBooster::isView() && $button_edit)
            <a class='btn btn-xs btn-primary btn-detail' href='{{CRUDBooster::mainpath("detail/$row->id")}}'>
                <i class="fa fa-eye"></i>
            </a>
        @endif
        @if(CRUDBooster::isDelete() && $button_edit && $row->status != $item_status['start'])
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
                <!-- 按钮触发模态框 -->
        <!-- <button class="btn btn-xs btn-warning" data-toggle="modal" data-target="#myModal">测试结果</button> -->
        <!-- 模态框（Modal） -->
        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog" style="width: 70%">
                <div class="modal-content">
                    <div class="modal-body" id='modal-body'><div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal -->
        </div>

        </td>
       </tr>
    @endforeach
  </tbody>
  <script type="text/javascript">
    function query(id) {
        var url = "http://{{$_SERVER['HTTP_HOST']}}/admin/t_item/test-result/" + id;
        $.ajax({
            url : url,
            async : true,
            type : "GET",
            data : {
                "type" : "query",
                "id" : id
            },
            // 成功后开启模态框
            success : showQuery,
            error : function() {
                alert("请求失败");
            },
            dataType : "json"
        });
    }
    function showQuery(data) {
        if (data == '') {
            $("#modal-body").html('暂无数据');
        } else {
            $("#modal-body").html('<pre>'+JSON.stringify(data, null, 4)+'</pre>');
        }
        // 显示模态框
        $('#myModal').modal('show');
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
   @if(!is_null($post_index_html) && !empty($post_index_html))
       {!! $post_index_html !!}
   @endif

@endsection
