<?php namespace App\Http\Controllers;

use Session;
use Request;
use DB;
use CRUDBooster;
use App\Models\V2\WechatServer;
use App\Services\InternalAPIV2Service;

	class AdminTWechatServerController extends \crocodicstudio\crudbooster\controllers\CBController {

	    public function cbInit() {

			# START CONFIGURATION DO NOT REMOVE THIS LINE
			$this->title_field = "wechat_name";
			$this->limit = "20";
			$this->orderby = "id,desc";
			$this->global_privilege = false;
			$this->button_table_action = true;
			$this->button_bulk_action = true;
			$this->button_action_style = "button_icon";
			$this->button_add = true;
			$this->button_edit = true;
			$this->button_delete = true;
			$this->button_detail = true;
			$this->button_show = true;
			$this->button_filter = true;
			$this->button_import = false;
			$this->button_export = false;
			$this->table = "t_wechat_server";
			# END CONFIGURATION DO NOT REMOVE THIS LINE

			# START COLUMNS DO NOT REMOVE THIS LINE
			$this->col = [];
			$this->col[] = ["label"=>"管理微信名称","name"=>"wechat_name"];
			$this->col[] = ["label"=>"群名称","name"=>"room_name"];
			$this->col[] = ["label"=>"邮箱","name"=>"email"];
            $this->col[] = ["label"=>"监听类型","name"=>"listen_type","callback"=>function ($row) {
                if ( $row->listen_type == WechatServer::LISTEN_TYPE_ROOM) {
                    return '监听群';
                } else {
                    return '监听服务号';
                }
            }];
            $this->col[] = ["label"=>"状态","name"=>"status","callback"=>function ($row) {
                if ( $row->status == WechatServer::STATUS_INIT) {
                    return '待启动';
                } elseif ( $row->status == WechatServer::STATUS_START ){
                    return '启动中';
                } else {
                    return '已停止';
                }
            }];
            $this->col[] = ["label"=>"启动时间","name"=>"start_at"];
			$this->col[] = ["label"=>"停止时间","name"=>"stop_at"];
			# END COLUMNS DO NOT REMOVE THIS LINE

			# START FORM DO NOT REMOVE THIS LINE
			$this->form = [];
			$this->form[] = ['label'=>'管理微信名称','name'=>'wechat_name','type'=>'text','validation'=>'required|min:1|max:100','width'=>'col-sm-4'];
            $this->form[] = ['label'=>'群名称','name'=>'room_name','type'=>'text','validation'=>'nullable|min:1|max:100','width'=>'col-sm-4'];
            $this->form[] = ['label'=>'通知邮箱','name'=>'email','type'=>'email','validation'=>'nullable|email','width'=>'col-sm-4'];
			$this->form[] = ['label'=>'监听类型','name'=>'listen_type','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-6','dataenum'=>'1|监听群;2|监听公众号','value'=>'1'];
			# END FORM DO NOT REMOVE THIS LINE

			/*
	        | ----------------------------------------------------------------------
	        | Sub Module
	        | ----------------------------------------------------------------------
			| @label          = Label of action
			| @path           = Path of sub module
			| @foreign_key 	  = foreign key of sub table/module
			| @button_color   = Bootstrap Class (primary,success,warning,danger)
			| @button_icon    = Font Awesome Class
			| @parent_columns = Sparate with comma, e.g : name,created_at
	        |
	        */
	        $this->sub_module = array();


	        /*
	        | ----------------------------------------------------------------------
	        | Add More Action Button / Menu
	        | ----------------------------------------------------------------------
	        | @label       = Label of action
	        | @url         = Target URL, you can use field alias. e.g : [id], [name], [title], etc
	        | @icon        = Font awesome class icon. e.g : fa fa-bars
	        | @color 	   = Default is primary. (primary, warning, succecss, info)
	        | @showIf 	   = If condition when action show. Use field alias. e.g : [id] == 1
	        |
	        */
	        $this->addaction = array();
            $this->addaction[] = ['label'=>'启动', 'url'=>CRUDBooster::mainpath('start/[id]'),'color'=>'info', 'icon'=>'glyphicon glyphicon-play', 'showIf'=>'[status] != ' . WechatServer::STATUS_START];
            $this->addaction[] = ['label'=>'停止', 'url'=>CRUDBooster::mainpath('stop/[id]'),'color'=>'warning', 'icon'=>'glyphicon glyphicon-stop', 'showIf'=>'[status] == ' . WechatServer::STATUS_START];



	        /*
	        | ----------------------------------------------------------------------
	        | Add More Button Selected
	        | ----------------------------------------------------------------------
	        | @label       = Label of action
	        | @icon 	   = Icon from fontawesome
	        | @name 	   = Name of button
	        | Then about the action, you should code at actionButtonSelected method
	        |
	        */
	        $this->button_selected = array();


	        /*
	        | ----------------------------------------------------------------------
	        | Add alert message to this module at overheader
	        | ----------------------------------------------------------------------
	        | @message = Text of message
	        | @type    = warning,success,danger,info
	        |
	        */
	        $this->alert        = array();



	        /*
	        | ----------------------------------------------------------------------
	        | Add more button to header button
	        | ----------------------------------------------------------------------
	        | @label = Name of button
	        | @url   = URL Target
	        | @icon  = Icon from Awesome.
	        |
	        */
	        $this->index_button = array();



	        /*
	        | ----------------------------------------------------------------------
	        | Customize Table Row Color
	        | ----------------------------------------------------------------------
	        | @condition = If condition. You may use field alias. E.g : [id] == 1
	        | @color = Default is none. You can use bootstrap success,info,warning,danger,primary.
	        |
	        */
	        $this->table_row_color = array();


	        /*
	        | ----------------------------------------------------------------------
	        | You may use this bellow array to add statistic at dashboard
	        | ----------------------------------------------------------------------
	        | @label, @count, @icon, @color
	        |
	        */
	        $this->index_statistic = array();



	        /*
	        | ----------------------------------------------------------------------
	        | Add javascript at body
	        | ----------------------------------------------------------------------
	        | javascript code in the variable
	        | $this->script_js = "function() { ... }";
	        |
	        */


	        $this->script_js = "
                $(function(){
                    var listen_type = $(\"input[name='listen_type']:checked\").val()

                    if (listen_type == 1) {
                       $(\"#form-group-email\").css(\"display\", \"none\")
                       $(\"#form-group-room_name\").css(\"display\", \"\")
                    } else if (listen_type == 2) {
                       $(\"#form-group-email\").css(\"display\", \"\")
                       $(\"#form-group-room_name\").css(\"display\", \"none\")
                    }
                   
                    $(\"input[name='listen_type']\").change(function(){
                        var listen_type = $(\"input[name='listen_type']:checked\").val()
                        if (listen_type == 1) {
                           $(\"#form-group-email\").css(\"display\", \"none\")
                           $(\"#form-group-room_name\").css(\"display\", \"\")
                        } else if (listen_type == 2) {
                           $(\"#form-group-email\").css(\"display\", \"\")
                           $(\"#form-group-room_name\").css(\"display\", \"none\")
                        }
                    });
                })
	        ";


            /*
	        | ----------------------------------------------------------------------
	        | Include HTML Code before index table
	        | ----------------------------------------------------------------------
	        | html code to display it before index table
	        | $this->pre_index_html = "<p>test</p>";
	        |
	        */
	        $this->pre_index_html = null;



	        /*
	        | ----------------------------------------------------------------------
	        | Include HTML Code after index table
	        | ----------------------------------------------------------------------
	        | html code to display it after index table
	        | $this->post_index_html = "<p>test</p>";
	        |
	        */
	        $this->post_index_html = null;



	        /*
	        | ----------------------------------------------------------------------
	        | Include Javascript File
	        | ----------------------------------------------------------------------
	        | URL of your javascript each array
	        | $this->load_js[] = asset("myfile.js");
	        |
	        */
	        $this->load_js = array();



	        /*
	        | ----------------------------------------------------------------------
	        | Add css style at body
	        | ----------------------------------------------------------------------
	        | css code in the variable
	        | $this->style_css = ".style{....}";
	        |
	        */
	        $this->style_css = NULL;



	        /*
	        | ----------------------------------------------------------------------
	        | Include css File
	        | ----------------------------------------------------------------------
	        | URL of your css each array
	        | $this->load_css[] = asset("myfile.css");
	        |
	        */
	        $this->load_css = array();


	    }


	    /*
	    | ----------------------------------------------------------------------
	    | Hook for button selected
	    | ----------------------------------------------------------------------
	    | @id_selected = the id selected
	    | @button_name = the name of button
	    |
	    */
	    public function actionButtonSelected($id_selected,$button_name) {
	        //Your code here

	    }


	    /*
	    | ----------------------------------------------------------------------
	    | Hook for manipulate query of index result
	    | ----------------------------------------------------------------------
	    | @query = current sql query
	    |
	    */
	    public function hook_query_index(&$query) {
	        //Your code here

	    }

	    /*
	    | ----------------------------------------------------------------------
	    | Hook for manipulate row of index table html
	    | ----------------------------------------------------------------------
	    |
	    */
	    public function hook_row_index($column_index,&$column_value) {
	    	//Your code here
	    }

	    /*
	    | ----------------------------------------------------------------------
	    | Hook for manipulate data input before add data is execute
	    | ----------------------------------------------------------------------
	    | @arr
	    |
	    */
	    public function hook_before_add(&$postdata) {}

	    /*
	    | ----------------------------------------------------------------------
	    | Hook for execute command after add public static function called
	    | ----------------------------------------------------------------------
	    | @id = last insert id
	    |
	    */
	    public function hook_after_add($id) {
	        //Your code here

	    }

	    /*
	    | ----------------------------------------------------------------------
	    | Hook for manipulate data input before update data is execute
	    | ----------------------------------------------------------------------
	    | @postdata = input post data
	    | @id       = current id
	    |
	    */
	    public function hook_before_edit(&$postdata,$id) {
	        //Your code here

	    }

	    /*
	    | ----------------------------------------------------------------------
	    | Hook for execute command after edit public static function called
	    | ----------------------------------------------------------------------
	    | @id       = current id
	    |
	    */
	    public function hook_after_edit($id) {
	        //Your code here

	    }

	    /*
	    | ----------------------------------------------------------------------
	    | Hook for execute command before delete public static function called
	    | ----------------------------------------------------------------------
	    | @id       = current id
	    |
	    */
	    public function hook_before_delete($id) {
	        //Your code here

	    }

	    /*
	    | ----------------------------------------------------------------------
	    | Hook for execute command after delete public static function called
	    | ----------------------------------------------------------------------
	    | @id       = current id
	    |
	    */
	    public function hook_after_delete($id) {
	        //Your code here

	    }

        public function postAddSave() {
            $this->cbLoader();
            if(!CRUDBooster::isCreate() && $this->global_privilege==FALSE) {
                CRUDBooster::insertLog(trans('crudbooster.log_try_add_save',['name'=>Request::input($this->title_field),'module'=>CRUDBooster::getCurrentModule()->name ]));
                CRUDBooster::redirect(CRUDBooster::adminPath(),trans("crudbooster.denied_access"));
            }

            $this->validation();
            $this->input_assignment();

            $formParams = $this->arr;
            if ($formParams['listen_type'] == WechatServer::LISTEN_TYPE_OFFICIAL && empty($formParams['email'])) {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "监听公众号时,邮箱必填", "error");
            }

            if ($formParams['listen_type'] == WechatServer::LISTEN_TYPE_ROOM && empty($formParams['room_name'])) {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "监听群时,群名称必填", "error");
            }

            try {
                InternalAPIV2Service::post('/wechat_server', $formParams);
            } catch (\Dingo\Api\Exception\ResourceException $e) {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "error");
            }

            CRUDBooster::redirect($_SERVER['HTTP_ORIGIN'] . "/admin/t_wechat_server", "创建成功", "success");
        }

        public function postEditSave($id) {
            $this->cbLoader();
            $row = DB::table($this->table)->where($this->primary_key,$id)->first();

            if(!CRUDBooster::isUpdate() && $this->global_privilege==FALSE) {
                CRUDBooster::insertLog(trans("crudbooster.log_try_add",['name'=>$row->{$this->title_field},'module'=>CRUDBooster::getCurrentModule()->name]));
                CRUDBooster::redirect(CRUDBooster::adminPath(),trans('crudbooster.denied_access'));
            }

            $this->validation($id);
            $this->input_assignment($id);

            $formParams = $this->arr;
            if ($formParams['listen_type'] == WechatServer::LISTEN_TYPE_OFFICIAL && empty($formParams['email'])) {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "监听公众号时,邮箱必填", "error");
            }

            if ($formParams['listen_type'] == WechatServer::LISTEN_TYPE_ROOM && empty($formParams['room_name'])) {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "监听群时,群名称必填", "error");
            }


            $formParams['id'] = $id;

            try {
                InternalAPIV2Service::post('/wechat_server/update', $formParams);
            } catch (\Dingo\Api\Exception\ResourceException $e) {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "error");
            }

            CRUDBooster::redirect($_SERVER['HTTP_ORIGIN'] . "/admin/t_wechat_server", "修改成功", "success");
        }


	    //By the way, you can still create your own method in here... :)
        // 启动服务
        public function getStart($id)
        {
            try {
                $res = InternalAPIV2Service::post('/wechat_server/start', ['id' => $id]);
            } catch (\Dingo\Api\Exception\ResourceException $e) {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "error");
            }
            $res['log'] = [];

            CRUDBooster::redirect('/admin/t_wechat_server/detail/' . $id . '?last_time=' . $res['start_at'], "启动成功", "success");
        }

        public function getDetail($id)
        {
            try {
                $res = InternalAPIV2Service::get('/wechat_server/' . $id);
            } catch (\Dingo\Api\Exception\ResourceException $e) {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "error");
            }


            $wechatServer = $res;
            $wechatServer['log'] = [];
            if (!empty($res['start_at'])) {
                $log = InternalAPIV2Service::get('/wechat_server_log/wechat_server_id/' . $id, ['last_time' => $res['start_at']]);
            }
            $this->cbView('admin.wechat_server_log', $wechatServer);
        }

        public function getLog($id)
        {
            $last_time = Request::get('last_time');
            $asc = Request::get('asc');
            $log_id = Request::get('log_id');
            if (empty($last_time)) {
                $last_time = date("Y-m-d H:i:s");
            }
            $log = InternalAPIV2Service::get('/wechat_server_log/wechat_server_id/' . $id, ['last_time' => $last_time, 'asc' => $asc, 'log_id' => $log_id]);
            echo json_encode($log);
        }

        // 停止服务
        public function getStop($id)
        {
            try {
                $res = InternalAPIV2Service::post('/wechat_server/stop', ['id' => $id]);
            } catch (\Dingo\Api\Exception\ResourceException $e) {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "error");
            }
            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "启动成功", "success");
        }

	}