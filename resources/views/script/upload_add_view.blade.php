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
                    <div class='form-group header-group-0  id='form-group-description'>
                        <label class='control-label col-sm-2'>url
                            <span class='text-danger' title='This field is required'>*</span>
                        </label>
                        <div class="col-sm-10" id="test">
                           <input type='text' title="url" required maxlength='255' class='form-control' name="url"/>

                            <div class="text-danger"></div>
                            <p class='help-block'></p>
                        </div>
                    </div>
                    <div class='form-group header-group-0  id='form-group-description'>
                        <label class='control-label col-sm-2'>脚本内容
                            <span class='text-danger' title='This field is required'>*</span>
                        </label>
                        <div class="col-sm-10">

                            <textarea name="description" id="description" maxlength=5000 required minlength='10' class='form-control' style="height:400px;display: block;color: #428bca;"></textarea>
                            <div class="text-danger"></div>
                            <p class='help-block'></p>
                        </div>
                    </div>
                    <div class='form-group header-group-0 ' id='form-group-load_images'>
                        <label class='control-label col-sm-2'>脚本类型
                        </label>
                        <div>
                            <div class="col-sm-10">
                                <label>
                                    <input type="radio" name="type" value="1" checked>&nbsp;&nbsp;php&nbsp;&nbsp;
                                    <input type="radio" name="type" value="2">&nbsp;&nbsp;js
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
