<?php
    namespace App\Http\Controllers;
	use Session;
	use Request;
	use DB;
	use CRUDBooster;
    use Redirect;
    use GuzzleHttp;
    use App\Models\CrawlTask;
    use App\Services\APIService;
    use Illuminate\Support\Facades\Route;
    use App\Events\TaskPreview;


	class AdminTCrawlTaskController extends \crocodicstudio\crudbooster\controllers\CBController {

        // 状态
        const STATUS_NO_STARTING = 1;
        const STATUS_TEST_SUCCESS = 2;
        const STATUS_TEST_FAIL = 3;
        const STATUS_START_UP = 4;
        const STATUS_STOP = 5;
        const STATUS_ARCHIVED = 6;

        //响应类型 1、API,2、邮件，3、短信，4、企业微信
        const RESPONSE_TYPE_API = 1;
        const RESPONSE_TYPE_EMAIL = 2;
        const RESPONSE_TYPE_SMS = 3;
        const RESPONSE_TYPE_ENTERPRISE_WECHAT = 4;

        //cron类型 1、一分钟，2、一小时，3一天,4持续执行
        const CRON_MINUTE = 2;
        const CRON_HOUR = 3;
        const CRON_DAY = 4;
        const CRON_SUSTAINED_EXECUTE = 1;

        const PROTOCOL_HTTP = 1;
        const PROTOCOL_HTTPS = 2;
        //是否代理支持 1、需要，2、不需要
        const IS_PROXY_YES = 1;
        const IS_PROXY_NO = 2;

	    public function cbInit() {

			# START CONFIGURATION DO NOT REMOVE THIS LINE
			$this->title_field = "name";
			$this->limit = "20";
			$this->orderby = "id,desc";
			$this->global_privilege = false;
			$this->button_table_action = true;
			$this->button_bulk_action = true;
			$this->button_action_style = "button_icon";
			$this->button_add = true;
			$this->button_edit = true;
			$this->button_delete = false;
			$this->button_detail = true;
			$this->button_show = true;
			$this->button_filter = true;
			$this->button_import = false;
			$this->button_export = false;
			$this->table = "t_crawl_task";
			# END CONFIGURATION DO NOT REMOVE THIS LINE

			# START COLUMNS DO NOT REMOVE THIS LINE
			$this->col = [];
			$this->col[] = ["label"=>"ID","name"=>"id"];
            $this->col[] = ["label"=>"资源地址","name"=>"resource_url",'width'=>'200',"callback"=>function ($row) {
                return '<a href="' . $row->resource_url . '" target="_brank" style="width:200px;overflow: hidden; display: -webkit-box;text-overflow: ellipsis; word-break: break-all;-webkit-box-orient: vertical;-webkit-line-clamp: 1;">'. $row->resource_url .'</a>';
            }];
            $this->col[] = ["label"=>"执行频次","name"=>"cron_type","callback"=>function ($row) {
                if ( $row->cron_type == self::CRON_MINUTE) {
                    return '每分钟执行一次';
                } else if( $row->cron_type == self::CRON_HOUR) {
                    return '每小时执行一次';
                } else if( $row->cron_type == self::CRON_DAY) {
                    return '每天执行一次';
                } else if ($row->cron_type == self::CRON_SUSTAINED_EXECUTE) {
                    return '持续执行';
                }
            }];

            $this->col[] = ["label"=>"资源类型","name"=>"resource_type","callback"=>function ($row) {
                if ( $row->resource_type == CrawlTask::RESOURCE_TYPE_HTML) {
                    return 'html';
                } else if( $row->resource_type == CrawlTask::RESOURCE_TYPE_JSON) {
                    return 'json';
                } else {
                    return ' ';
                }
            }];

            $this->col[] = ["label"=>"是否是ajax","name"=>"is_ajax","callback"=>function ($row) {
                if ( $row->is_ajax == CrawlTask::IS_AJAX_TRUE) {
                    return '是';
                } else {
                    return '否';
                }
            }];
            $this->col[] = ["label"=>"是否登陆","name"=>"is_login","callback"=>function ($row) {
                if ( $row->is_login == CrawlTask::IS_LOGIN_TRUE) {
                    return '是';
                } else {
                    return '否';
                }
            }];
            $this->col[] = ["label"=>"是否使用代理","name"=>"is_proxy","callback"=>function ($row) {
                if ( $row->is_proxy == CrawlTask::IS_PROXY_TRUE) {
                    return '是';
                } else {
                    return '否';
                }
            }];
            $this->col[] = ["label"=>"是否翻墙","name"=>"is_wall","callback"=>function ($row) {
                if ( $row->is_wall == CrawlTask::IS_WALL_TRUE) {
                    return '是';
                } else {
                    return '否';
                }
            }];

            $this->col[] = ["label"=>"最后执行时间","name"=>"last_job_at"];
            $this->col[] = ["label"=>"状态","name"=>"status","callback"=>function ($row) {
                if ( $row->status == self::STATUS_NO_STARTING) {
                    return '未启动';
                } else if( $row->status == self::STATUS_TEST_SUCCESS) {
                    return '测试成功';
                } else if( $row->status == self::STATUS_TEST_FAIL) {
                    return '测试失败';
                } else if( $row->status == self::STATUS_START_UP) {
                    return '启动中';
                } else if( $row->status == self::STATUS_STOP) {
                    return '已停止';
                } else if( $row->status == self::STATUS_ARCHIVED) {
                    return '已归档';
                }
            }];
            # END COLUMNS DO NOT REMOVE THIS LINE

            # START FORM DO NOT REMOVE THIS LINE
            $this->form = [];
            $this->form[] = ['label'=>'任务名称','name'=>'name','type'=>'text','validation'=>'string|min:1|max:70','width'=>'col-sm-10'];
            $this->form[] = ['label'=>'任务描述','name'=>'description','type'=>'text','validation'=>'min:1|max:255','width'=>'col-sm-10'];
            $this->form[] = ['label'=>'资源地址','name'=>'resource_url','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
            // $this->form[] = ['label'=>'关键词','name'=>'keywords','type'=>'textarea','width'=>'col-sm-9'];
            $this->form[] = ['label'=>'定时任务类型','name'=>'cron_type','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'2|每分钟执行一次;3|每小时执行一次;4|每天执行一次;1|持续执行','value'=>'1'];
            $this->form[] = ['label'=>'是否使用代理','name'=>'is_proxy','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|是;2|否','value'=>'2'];
            $this->form[] = ['label'=>'是否异步请求','name'=>'is_ajax','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|是;2|否','value'=>'2'];
            $this->form[] = ['label'=>'是否需要翻墙','name'=>'is_wall','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|是;2|否','value'=>'2'];
            $this->form[] = ['label'=>'是否需要登录','name'=>'is_login','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|是;2|否','value'=>'2'];
            $this->form[] = ['label'=>'资源类型','name'=>'resource_type','type'=>'radio','validation'=>'required','width'=>'col-sm-10','dataenum'=>'1|html;2|json','value'=>'1'];
            $this->form[] = ['label'=>'选择器','name'=>'selectors','type'=>'textarea','width'=>'col-sm-10','placeholder'=>'资源类型是html时添加'];
            // $this->form[] = ['label'=>'响应类型','name'=>'response_type','type'=>'hidden','validation'=>'required','width'=>'col-sm-10','value'=>'1'];
            // $this->form[] = ['label'=>'响应地址','name'=>'response_url','type'=>'text','validation'=>'','width'=>'col-sm-10'];
            // $this->form[] = ['label'=>'响应参数','name'=>'response_params','type'=>'textarea','validation'=>'','width'=>'col-sm-10'];
            $this->form[] = ['label'=>'请求头信息','name'=>'header','type'=>'textarea','width'=>'col-sm-10','placeholder'=>'资源类型是json时添加'];
            $this->form[] = ['label'=>'API筛选条件','name'=>'api_fields','type'=>'textarea','width'=>'col-sm-10','placeholder'=>'资源类型是json时添加'];
			$this->form[] = ['label'=>'测试结果','name'=>'test_result','type'=>'text','width'=>'col-sm-9','readonly'=>'false'];
			$this->form[] = ['label'=>'状态','name'=>'status','type'=>'hidden','width'=>'col-sm-9', 'dataenum'=>'1|未启动;2|测试成功;3|测试失败;4|启动中;5|已停止;6|归档;','value'=>'1'];
			# END FORM DO NOT REMOVE THIS LINE

			# OLD START FORM
			//$this->form = [];
			//$this->form[] = ['label'=>'任务名称','name'=>'name','type'=>'text','validation'=>'required|string|min:1|max:70','width'=>'col-sm-10'];
			//$this->form[] = ['label'=>'任务描述','name'=>'description','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			//$this->form[] = ['label'=>'资源URL','name'=>'resource_url','type'=>'text','width'=>'col-sm-10'];
			//$this->form[] = ['label'=>'关键词','name'=>'keywords','type'=>'textarea','width'=>'col-sm-9'];
			//$this->form[] = ['label'=>'Cron类型','name'=>'cron_type','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|一分钟;2|一小时;3|一天','value'=>'1'];
			//$this->form[] = ['label'=>'选择器','name'=>'selectors','type'=>'textarea','width'=>'col-sm-10'];
			//$this->form[] = ['label'=>'响应类型','name'=>'response_type','type'=>'hidden','validation'=>'required','width'=>'col-sm-10'];
			//$this->form[] = ['label'=>'发送地址','name'=>'response_url','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			//$this->form[] = ['label'=>'参数','name'=>'response_params','type'=>'textarea','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			//$this->form[] = ['label'=>'规则模版','name'=>'setting_id','type'=>'select2','validation'=>'required','width'=>'col-sm-10'];
			//$this->form[] = ['label'=>'测试结果','name'=>'test_result','type'=>'text','width'=>'col-sm-9'];
			//$this->form[] = ['label'=>'状态','name'=>'status','type'=>'hidden','width'=>'col-sm-9'];
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

            $this->sub_module[] = ['label'=>'任务结果','path'=>'t_crawl_result_v2','foreign_key'=>'crawl_task_id','button_color'=>'success','button_icon'=>'fa fa-bars', 'parent_columns'=>'id', 'showIf'=>"[resource_type] != 0"];
            $this->sub_module[] = ['label'=>'任务结果','path'=>'t_crawl_result','foreign_key'=>'crawl_task_id','button_color'=>'success','button_icon'=>'fa fa-bars', 'parent_columns'=>'id', 'showIf'=>"[resource_type] == 0"];

            //$this->sub_module[] = ['label'=>'任务结果','path'=>'t_crawl_result','foreign_key'=>'crawl_task_id','button_color'=>'success','button_icon'=>'fa fa-bars', 'parent_columns'=>'id'];
	        /*
	        | ----------------------------------------------------------------------
	        | Add More Action Button / Menu
	        | ----------------------------------------------------------------------
	        | @label       = Label of action
	        | @url         = Target URL, you can use field alias. e.g : [id], [name], [title], etc
	        | @icon        = Font awesome class icon. e.g : fa fa-bars
	        | @color 	   = Default is primary. (primary, warning, success, info)
	        | @showIf 	   = If condition when action show. Use field alias. e.g : [id] == 1
	        |
	        */
	        $this->addaction = array();
            $this->addaction[] = ['label'=>'测试', 'url'=>CRUDBooster::mainpath('test-result/[id]'),'color'=>'info', 'icon'=>'fa fa-play'];

            $this->addaction[] = ['label'=>'启动', 'url'=>CRUDBooster::mainpath('start-up/[id]/' . self::STATUS_START_UP),'color'=>'success', 'icon'=>'fa fa-play', 'showIf'=>'[status] == ' . self::STATUS_TEST_SUCCESS . '|| [status] == ' . self::STATUS_STOP];

            $this->addaction[] = ['label'=>'停止', 'url'=>CRUDBooster::mainpath('stop-up/[id]/' . self::STATUS_STOP),'color'=>'warning', 'icon'=>'fa fa-stop', 'showIf'=>'[status] == ' . self::STATUS_START_UP];
            $this->addaction[] = ['label'=>'归档', 'url'=>CRUDBooster::mainpath('archived/[id]/' . self::STATUS_ARCHIVED),'showIf'=>'[status] == ' . self::STATUS_STOP . '|| [status] == ' . self::STATUS_NO_STARTING . '|| [status] == ' . self::STATUS_TEST_FAIL . '|| [status] == ' . self::STATUS_TEST_SUCCESS ];

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
            $this->index_statistic[] = ['label'=>'任务总数','count'=>DB::table('t_crawl_task')->count(),'icon'=>'fa fa-check','color'=>'success'];
            $this->index_statistic[] = ['label'=>'启动中','count'=>DB::table('t_crawl_task')->where('status', CrawlTask::IS_START_UP)->count(),'icon'=>'fa fa-check','color'=>'success'];
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
            if (substr($postdata['resource_url'], 0, 8) == 'https://') {
                $postdata['protocol'] = CrawlTask::PROTOCOL_HTTPS;
            } else {
                $postdata['protocol'] = CrawlTask::PROTOCOL_HTTP;
            }

            $postdata['md5_params'] = $this->__getMd5Params([], $postdata);
            $taskDetail = $this->__getTaskByMd5Params($postdata);
            if (!empty($taskDetail)) {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "任务已存在", "info");
            }
            $postdata['status'] = 1;

	        //Your code here

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
	    public function hook_before_edit(&$postdata, $id) {
            if ($postdata['status'] == self::STATUS_START_UP) {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "状态启动中不能修改，请返回", "info");
            }

            if (substr($postdata['resource_url'], 0, 8) == 'https://') {
                $postdata['protocol'] = CrawlTask::PROTOCOL_HTTPS;
            } else {
                $postdata['protocol'] = CrawlTask::PROTOCOL_HTTP;
            }

            $postdata['md5_params'] = $this->__getMd5Params([], $postdata);
            $postdata['status'] = 1;
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
        //修改任务状态
        private function getUpdateStatus($id,$status) {
            DB::table('t_crawl_task')->where('id', $id)->update(['status' => $status]);
        }

        public function getLogList()
        {
            $message = 'log日志请去指定的地方查看';
            echo "<script> alert('{$message}') </script>";
            return Redirect::to('admin/t_crawl_task');
        }

        public function getTestResult($id)
        {
            $uri = '/v1/crawl/task/test';
            $params['id'] = $id;
            $result = APIService::openPost($uri, $params);
            if (empty($result))
            {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "info");
            }
            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "测试提交成功，请稍后查看结果", "info");
        }

        public function getStartUp($id, $status)
        {
            $uri = '/v1/crawl/task/start';
            $params['id'] = $id;
            $result = APIService::openPost($uri, $params);
            if (empty($result))
            {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "info");
            }
            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "开启成功", "info");
        }

        public function getStopUp($id, $status)
        {
            $uri = '/v1/crawl/task/stop';
            $params['id'] = $id;
            $result = APIService::openPost($uri, $params);
            if (empty($result))
            {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "info");
            }
            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "停止成功", "info");
        }
        public function getArchived($id, $status)
        {
            $this->getUpdateStatus($id, $status);
            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "归档成功", "info");
        }

	    //By the way, you can still create your own method in here... :)
        public function getDetail($id) {
            $this->cbLoader();
            $row = DB::table('t_crawl_task')->where('id', $id)->first();
            if ( $row->cron_type == self::CRON_MINUTE) {
                    $row->cron_type = '每分钟执行一次';
                } else if( $row->cron_type == self::CRON_HOUR) {
                    $row->cron_type = '每小时执行一次';
                } else if( $row->cron_type == self::CRON_DAY) {
                    $row->cron_type = '每天执行一次';
                } else if ($row->cron_type == self::CRON_SUSTAINED_EXECUTE) {
                    $row->cron_type = '持续执行';
                }
            if ( $row->protocol == self::PROTOCOL_HTTP) {
                    $row->protocol = 'http';
                } else if( $row->cron_type == self::PROTOCOL_HTTPS) {
                    $row->protocol = 'https';
                }
            if ( $row->is_proxy == self::IS_PROXY_YES) {
                    $row->is_proxy = '是';
                } else if( $row->is_proxy == self::IS_PROXY_NO) {
                    $row->is_proxy = '否';
                }


            if ($row->is_ajax == CrawlTask::IS_AJAX_TRUE) {
                $row->is_ajax = '是';
            } else {
                $row->is_ajax = '否';
            }

            if ( $row->is_login == CrawlTask::IS_LOGIN_TRUE) {
                $row->is_login = '是';
            } else {
                $row->is_login = '否';
            }

            if ( $row->is_wall == CrawlTask::IS_WALL_TRUE) {
                $row->is_wall = '是';
            } else {
                $row->is_wall = '否';
            }

            if ( $row->resource_type == CrawlTask::RESOURCE_TYPE_HTML) {
                $row->resource_type = 'html';
            } else if( $row->resource_type == CrawlTask::RESOURCE_TYPE_JSON) {
                $row->resource_type = 'json';
            } else {
                $row->resource_type = ' ';
            }
            if (!empty($row->test_result)) {
                $test_result = decodeUnicode($row->test_result);
                $row->test_result = $test_result;
            }


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

        private function __getTaskByMd5Params($postdata)
        {
            $res = DB::table('t_crawl_task')->where('resource_url', $postdata['resource_url'])
                                            ->where('md5_params', $postdata['md5_params'])
                                            ->get();
            $data = [];
            if (!empty($res)) {
                $data = $res->toArray();
            }
            return $data;
        }

        private function __getMd5Params($task = [], $params = [])
        {
            $item = [
                'resource_url'  => '',
                'cron_type'     => 1,
                'is_ajax'       => 2,
                'is_login'      => 2,
                'is_wall'       => 2,
                'is_proxy'      => 2,
                'protocol'      => 1,
            ];

            if (!empty($task)) {
                foreach ($item as $key => $value) {
                    if (empty($task[$key])) {
                        continue;
                    }
                    $item[$key] = $task[$key];
                }
            }
            if (!empty($params)) {
                foreach ($item as $key => $value) {
                    if (array_key_exists($key, $params)) {
                        $item[$key] = $params[$key];
                    }
                }
            }

            if (empty($task)) {
                $resourceType = $params['resource_type'];
            } else {
                if (empty($params['resource_type'])) {
                    $resourceType = $task['resource_type'];
                } else {
                    $resourceType = $params['resource_type'];
                }
            }
            $item['resource_type'] = $resourceType;

            if ($resourceType == CrawlTask::RESOURCE_TYPE_JSON) {

                if (!empty($task)) {
                    $item['api_fields'] = json_decode($task['api_fields']);
                    $item['header'] = json_decode($task['header']);
                }

                if (!empty($params['api_fields'])) {
                    $item['api_fields'] = $params['api_fields'];
                }

                if (!empty($params['api_fields'])) {
                    $item['header'] = $params['header'];
                }

            } else {

                if (!empty($task)) {
                    $item['selectors'] = json_decode($task['selectors']);
                }

                if (!empty($params['selectors'])) {
                    $item['selectors'] = $params['selectors'];
                }
            }
            return md5(json_encode($item, true));
        }

	}
