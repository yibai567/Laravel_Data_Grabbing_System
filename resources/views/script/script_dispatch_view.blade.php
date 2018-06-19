@extends('crudbooster::admin_template')

@section('content')


<div class="panel panel-default" >
    <div class="panel-heading">
        <strong>
            <i class='{{CRUDBooster::getCurrentModule()->icon}}'></i>项目分发
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

                    <div class='form-group header-group-0 ' id='form-group-name'>
                        <label class='control-label col-sm-2'>脚本ID
                            <span class='text-danger' title='This field is required'>*</span>
                        </label>
                        <div class="col-xs-5">
                            <label class='control-label col-sm-2'>{{$id}}</label>
                            <input type="hidden" name="id" value="{{$id}}">
                        </div>
                    </div>

                    <div class='form-group header-group-0 ' id='form-group-name'>
                        <label class='control-label col-sm-2'>分发项目</label>
                        <div class="col-xs-5">
                            <div class="checkbox">
                                @if (!empty($dispatch))
                                    @foreach($dispatch as $key=>$value)
                                        @if (!empty($projects))
                                            <?php $newProjects = json_decode($projects, true)?>
                                            <?php $checked = '' ?>
                                            @if(in_array($value['id'], $newProjects))
                                                <?php $checked = 'checked' ?>
                                            @endif
                                        @endif
                                        <label>
                                            <input type="checkbox" name="projects[]" {{$checked}} value="{{$value['id']}}">
                                            {{$value['name']}}
                                        </label>
                                    @endforeach
                                @endif
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
