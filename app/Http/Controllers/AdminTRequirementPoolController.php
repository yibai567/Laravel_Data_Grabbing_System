<?php namespace App\Http\Controllers;

	use App\Models\V2\AlarmResult;
    use App\Models\V2\Requirement;
    use Session;
	use Request;
	use DB;
	use CRUDBooster;
    use Config;
    use App\Services\InternalAPIV2Service;
    use Illuminate\Support\Facades\Route;
    use Log;

	class AdminTRequirementPoolController extends \crocodicstudio\crudbooster\controllers\CBController {

	    public function cbInit() {

			# START CONFIGURATION DO NOT REMOVE THIS LINE
			$this->title_field = "收集";
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
			$this->table = "t_requirement_pool";
			# END CONFIGURATION DO NOT REMOVE THIS LINE

			# START COLUMNS DO NOT REMOVE THIS LINE
			$this->col = [];
            $this->col[] = ["label"=>"任务ID","name"=>"id"];
			$this->col[] = ["label"=>"任务名称","name"=>"name"];
            $this->col[] = ["label"=>"任务地址","name"=>"list_url",'width'=>'200',"callback"=>function ($row) {
                return '<a href="' . $row->list_url . '" target="_brank" style="width:200px;overflow: hidden; display: -webkit-box;text-overflow: ellipsis; word-break: break-all;-webkit-box-orient: vertical;-webkit-line-clamp: 1;">'. $row->list_url .'</a>';
            }];
            $this->col[] = ["label"=>"公司名称","name"=>"company_id","callback"=>function ($row){
                $company = DB::table('t_company')->where('id', $row->company_id)->first();
                return $company->cn_name;
            }];
            $this->col[] = ["label"=>"分类","name"=>"category","callback"=>function ($row){
                if ( $row->category == 1) {
                    return '新闻';
                } else if($row->category == 2) {
                    return '历史数据';
                } else if($row->category == 3){
                   return '订阅';
                } else {
                   return '行业快讯';
                }
            }];
			$this->col[] = ["label"=>"订阅类型","name"=>"subscription_type","callback"=>function ($row){
                if ( $row->subscription_type == Requirement::SUBSCRIPTION_TYPE_LIST) {
                    return '列表';
                } else {
                    return '详情';
                }
            }];
			$this->col[] = ["label"=>"截图","name"=>"is_capture","callback"=>function ($row) {
                if ( $row->is_capture == Requirement::IS_CAPTURE_TRUE) {
                    return '需要';
                } else {
                    return '不需要';
                }
            }];

			$this->col[] = ["label"=>"图片资源","name"=>"is_download_img","callback"=>function ($row) {
                if ( $row->is_download_img == Requirement::IS_DOWNLOAD_TRUE) {
                    return '需要';
                } else {
                    return '不需要';
                }
            }];
            $this->col[] = ["label"=>"状态","name"=>"status","callback"=>function ($row) {
                if ($row->status == Requirement::STATUS_TRUE) {
                    return "<a class='btn btn-xs btn-warning'><i></i>未处理</a>";
                } else {
                    return "<a class='btn btn-xs btn-success'><i></i>已处理</a>";
                }
            }];
            $this->col[] = ["label"=>"全部数据","name"=>"status_identity","callback"=>function ($row) {
                if ($row->status_identity == 1) {
                    return "<a class='btn btn-xs btn-warning'><i></i>未获取</a>";
                } else {
                    return "<a class='btn btn-xs btn-success'><i></i>已获取</a>";
                }
            }];
            $this->col[] = ["label"=>"操作人","name"=>"operate_by","callback"=>function ($row) {
                 return $this->__getUser($row->operate_by);
            }];

            $this->col[] = ["label"=>"创建时间","name"=>"created_at"];

			# END COLUMNS DO NOT REMOVE THIS LINE

			# START FORM DO NOT REMOVE THIS LINE
			$this->form = [];
			$this->form[] = ['label'=>'任务名称','name'=>'name','type'=>'text','validation'=>'nullable|string|max:100','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'任务地址','name'=>'list_url','type'=>'text','validation'=>'required|string|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'描述','name'=>'description','type'=>'textarea','validation'=>'nullable|max:20000','width'=>'col-sm-10'];
            $this->form[] = ['label'=>'公司列表','name'=>'company_id','type'=>'select2','datatable'=>'t_company,cn_name'];
            $this->form[] = ['label'=>'公司中文名','name'=>'cn_name','type'=>'text','validation'=>'nullable|string|max:255','width'=>'col-sm-10','placeholder'=>'公司列表中未找到时可填写'];
            $this->form[] = ['label'=>'公司英文名','name'=>'en_name','type'=>'text','validation'=>'nullable|string|max:255','width'=>'col-sm-10','placeholder'=>'公司列表中未找到时可填写'];

			$this->form[] = ['label'=>'图片描述','name'=>'img_description','type'=>'upload','width'=>'col-sm-10',"callback"=>function ($row) {
                $newIp=$_SERVER['HTTP_HOST']."/".$row->img_description;
                $domain = strstr($newIp, '/uploads');
                if($domain){
                    return $row->img_description;
                }else{
                    $string="<div class='col-sm-10' style='margin-left:208px;margin-bottom:15px'><a data-lightbox='roadtrip' href=' ".$row->img_description."'><img style='max-width:160px'  src=".$row->img_description."></a><div class='text-danger'></div></div>";
                    echo $string;
                }
            }];
            $this->form[] = ['label'=>'分类','name'=>'category','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|新闻;2|历史数据;3|订阅;4|行业快讯','value'=>1];
			$this->form[] = ['label'=>'订阅类型','name'=>'subscription_type','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|列表;2|详情','value'=>1];
			$this->form[] = ['label'=>'截图','name'=>'is_capture','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|需要;2|不需要','value'=>2];
			$this->form[] = ['label'=>'图片资源','name'=>'is_download_img','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|需要;2|不需要','value'=>2];
            $this->form[] = ['label'=>'需求类型','name'=>'requirement_type','type'=>'radio','validation'=>'nullable|integer','width'=>'col-sm-10','dataenum'=>'1|快讯;2|公告','value'=>'1'];
            $this->form[] = ['label'=>'执行规则','name'=>'cron_type','type'=>'radio','validation'=>'nullable|integer','width'=>'col-sm-10','dataenum'=>'1|每分钟执行一次;2|每五分钟执行一次;3|每十五分钟执行一次;4|只执行一次','value'=>'1'];
			$this->form[] = ['label'=>'创建人','name'=>'create_by','type'=>'select','validation'=>'required','width'=>'col-sm-9','dataenum'=>'1|liqi1@jinse.com;2|huangxingxing@jinse.com;3|wangbo@jinse.com',];
			# END FORM DO NOT REMOVE THIS LINE

			# OLD START FORM
			//$this->form = [];
			//$this->form[] = ['label'=>'任务名称','name'=>'name','type'=>'text','validation'=>'nullable|string|max:100','width'=>'col-sm-10'];
			//$this->form[] = ['label'=>'抓取url','name'=>'list_url','type'=>'text','validation'=>'required|string|max:255','width'=>'col-sm-10'];
			//$this->form[] = ['label'=>'描述','name'=>'description','type'=>'textarea','validation'=>'nullable|min:10|max:255','width'=>'col-sm-10'];
			//$this->form[] = ['label'=>'图片描述','name'=>'img_description','type'=>'upload','validation'=>'required|max:255','width'=>'col-sm-10'];
			//$this->form[] = ['label'=>'订阅类型','name'=>'subscription_type','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|列表;2|详情', 'value'=>1];
			//$this->form[] = ['label'=>'是否截图','name'=>'is_capture','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|是;2|否', 'value'=>1];
			//$this->form[] = ['label'=>'是否下载图片','name'=>'is_download_img','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|是;2|否', 'value'=>1];
			# OLD END FORM

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
            $this->addaction[] = ['label'=>'done', 'url'=>CRUDBooster::mainpath('modify-state/[id]/' . Requirement::STATUS_FALSE),'color'=>'warning', 'icon'=>'ion-arrow-right-c', 'showIf'=>'[status] == ' . Requirement::STATUS_TRUE];
            $this->addaction[] = ['label'=>'restart', 'url'=>CRUDBooster::mainpath('modify-state/[id]/' . Requirement::STATUS_TRUE),'color'=>'info', 'icon'=>'ion-arrow-right-c', 'showIf'=>'[status] == ' . Requirement::STATUS_FALSE];

            $this->addaction[] = ['label'=>'获取全部数据', 'url'=>CRUDBooster::mainpath('save-status-identity/[id]/2'),'color'=>'info', 'icon'=>'ion-arrow-right-c', 'showIf'=>'[status_identity] == 1'];

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
            $this->index_statistic[] = ['label'=>'新闻','count'=>Requirement::where('category', 1)->count(),'color'=>'btn btn-xs btn-success'];
            $this->index_statistic[] = ['label'=>'历史数据','count'=>Requirement::where('category', 2)->count(),'color'=>'btn btn-xs btn-warning'];
            $this->index_statistic[] = ['label'=>'订阅','count'=>Requirement::where('category', 3)->count(),'color'=>'btn btn-xs btn-info'];
            $this->index_statistic[] = ['label'=>'行业快讯','count'=>Requirement::where('category', 4)->count(),'color'=>'btn btn-xs btn-info'];
	        /*
	        | ----------------------------------------------------------------------
	        | Add javascript at body
	        | ----------------------------------------------------------------------
	        | javascript code in the variable
	        | $this->script_js = "function() { ... }";
	        |
	        */
	        $this->script_js = NULL;


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
	    public function hook_before_add(&$postdata) {

	        //Your code here

            $this->__create($postdata);

	    }

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

	        $this->__update($postdata, $id);
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


        public function getModifyState($id,$status) {

            $params['id'] = $id;
            $params['status'] = $status;
            $params['user_id'] = CRUDBooster::myId();

            try {
                $result = InternalAPIV2Service::post('/quirement/update_status', $params);
            } catch (\Dingo\Api\Exception\ResourceException $e) {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "error");
            }
            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "状态修改成功", "info");
        }

        public function getSaveStatusIdentity($id,$statusIdentity) {
            try {
                $requirement = Requirement::find($id);
                $requirement->status_identity = $statusIdentity;
                $requirement->operate_by = CRUDBooster::myId();
                $requirement->save();
            } catch (\Dingo\Api\Exception\ResourceException $e) {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "error");
            }
           CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "状态修改成功", "info");
        }

        private function __getUser($id) {
            $res=DB::table('cms_users')->select('name')->where('id', $id)->first();

            return $res->name;
        }


        private function __create($params)
        {
            $newData = [
                "name" => $params['name'],
                "list_url" => $params['list_url'],
                "company_id" => $params['company_id'],
                "img_description" => $params['img_description'],
                "category" => $params['category'],
                "subscription_type" => $params['subscription_type'],
                "is_capture" => $params['is_capture'],
                "is_download_img" => $params['is_download_img'],
                "create_by" => $params['create_by'],
                "created_at" => $params['created_at'],
                "operate_by" => CRUDBooster::myId(),
            ];
            try {
                if (empty($params['company_id'])) {
                    $params['company_id'] = $this->__getCompanyId($params);
                }

                $params['operate_by'] = CRUDBooster::myId();
                $result = InternalAPIV2Service::post('/quirement', $params);

                if ($result) {
                    $data = [
                        'type' => AlarmResult::TYPE_WEWORK,
                        'content' => '有新的需求，请及时处理！',
                        'wework' => config('alarm.alarm_recipient')
                    ];

                    $alarmResult = InternalAPIV2Service::post('/alarm_result', $data);
                    if (!$alarmResult) {
                        Log::debug('[Requirement Create] create alarm_result is failed');
                    }
                }

            } catch (\Dingo\Api\Exception\ResourceException $e) {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "error");
            }
            CRUDBooster::redirect($_SERVER['HTTP_ORIGIN'] . "/admin/t_requirement_pool", "创建成功", "success");
        }

        private function __update($params, $id)
        {

            $params['id'] = (int)$id;
            try {
                InternalAPIV2Service::post('/quirement/update', $params);

            } catch (\Dingo\Api\Exception\ResourceException $e) {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "error");
            }


            CRUDBooster::redirect($_SERVER['HTTP_ORIGIN'] . "/admin/t_requirement_pool", "修改成功", "success");
        }
        public function getDetail($id)
        {
            $this->cbLoader();
            $row = Requirement::where('id', $id)->first();

            if ($row->subscription_type == Requirement::SUBSCRIPTION_TYPE_LIST) {
                $row->subscription_type = '列表';
            } else {
                $row->subscription_type = '详情';
            }
            if ($row->is_capture == Requirement::IS_CAPTURE_TRUE) {
                $row->is_capture = '是';
            } else {
                $row->is_capture = '否';
            }

            if ($row->is_download_img == IS_DOWNLOAD_TRUE) {
                $row->is_download_img = '是';
            } else {
                $row->is_download_img = '否';
            }
            $createBy = config('user');

            $row->create_by=$createBy[$row->create_by];

            if(!CRUDBooster::isRead() && $this->global_privilege==FALSE || $this->button_detail==FALSE) {
                    CRUDBooster::insertLog(trans("crudbooster.log_try_view",['name'=>$row->{$this->title_field},'module'=>CRUDBooster::getCurrentModule()->name]));
                    CRUDBooster::redirect(CRUDBooster::adminPath(),trans('crudbooster.denied_access'));
                }
                $page_menu  = Route::getCurrentRoute()->getActionName();
                $page_title = trans("crudbooster.detail_data_page_title",['module'=>$module->name,'name'=>$row->{$this->title_field}]);
                $command    = 'detail';
                Session::put('current_row_id',$id);
                return view('crudbooster::default.form',compact('row','page_menu','page_title','command','id'));

        }

        private function __getCompanyId($params)
        {
            $validator = validator($params, [
                'cn_name' => 'required|string|max:255',
                'en_name' => 'required|string|max:255'
            ]);

            if ($validator->fails()) {
                $message = $validator->getMessageBag()->all();
                return CRUDBooster::redirect($_SERVER['HTTP_REFERER'], trans('crudbooster.alert_validation_error',['error'=>implode(', ',$message)]));
            }

            $enName = preg_replace('/ +/', '_', $params['en_name']);

            $id = DB::table('t_company')->where('en_name', $enName)->value('id');
            //没有找到数据就创建新数据
            if (empty($id)) {
                $id = DB::table('t_company')->insertGetId(['cn_name'=>$params['cn_name'],'en_name'=>$enName]);
            }
            return $id;
        }
	    //By the way, you can still create your own method in here... :)


	}