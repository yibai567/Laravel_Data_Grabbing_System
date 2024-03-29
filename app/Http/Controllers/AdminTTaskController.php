<?php namespace App\Http\Controllers;

	use Session;
	use Request;
	use DB;
	use CRUDBooster;
    use Illuminate\Support\Facades\Route;
    use App\Services\InternalAPIV2Service;
    use App\Models\Task;

	class AdminTTaskController extends \crocodicstudio\crudbooster\controllers\CBController {

	    public function cbInit() {

			# START CONFIGURATION DO NOT REMOVE THIS LINE
			$this->title_field = "name";
			$this->limit = "20";
			$this->orderby = "id,desc";
			$this->global_privilege = false;
			$this->button_table_action = true;
			$this->button_bulk_action = true;
			$this->button_action_style = "button_icon";
			$this->button_add = false;
			$this->button_edit = false;
			$this->button_delete = true;
			$this->button_detail = true;
			$this->button_show = true;
			$this->button_filter = true;
			$this->button_import = false;
			$this->button_export = false;
			$this->table = "t_task";
			# END CONFIGURATION DO NOT REMOVE THIS LINE

			# START COLUMNS DO NOT REMOVE THIS LINE
			$this->col = [];
            $this->col[] = ["label"=>"任务ID","name"=>"id"];
            $this->col[] = ["label"=>"需求ID","name"=>"requirement_pool_id"];
			$this->col[] = ["label"=>"脚本ID","name"=>"script_id"];
			$this->col[] = ["label"=>"任务名称","name"=>"name"];
			$this->col[] = ["label"=>"测试地址","name"=>"test_url",'width'=>'200',"callback"=>function ($row) {
                return '<span style="width:200px;overflow: hidden; display: -webkit-box;text-overflow: ellipsis; word-break: break-all;-webkit-box-orient: vertical;-webkit-line-clamp: 1;">'. $row->test_url .'</span>';
            }];
            // $this->col[] = ["label"=>"最后执行时间","name"=>"name","callback"=>function ($row) {
            //     $taskStatistics = DB::table('t_task_statistics')->where('task_id', $row->id)->first();
            //     return $taskStatistics->last_job_at;
            // }];
            // $this->col[] = ["label"=>"执行次数","name"=>"name","callback"=>function ($row) {
            //     $taskStatistics = DB::table('t_task_statistics')->where('task_id', $row->id)->first();
            //     return $taskStatistics->run_times;
            // }];
            $this->col[] = ["label"=>"是否翻墙","name"=>"is_proxy","callback"=>function ($row) {
                if( $row->is_proxy == 1) {
                    return '翻墙';
                } else{
                    return '不翻墙';
                }
            }];
            $this->col[] = ["label"=>"执行规则","name"=>"cron_type","callback"=>function ($row) {
                if( $row->cron_type == Task::CRON_TYPE_KEEP) {
                    return '每分钟执行';
                } else if ($row->cron_type == Task::CRON_TYPE_EVERY_FIVE_MINUTES) {
                    return '每五分钟执行';
                } else if ($row->cron_type == Task::CRON_TYPE_EVERY_TEN_MINUTES) {
                    return '每十分钟执行';
                } else if ($row->cron_type == Task::CRON_TYPE_KEEP_ONCE){
                    return '<span style="color:#f00">只执行一次</span>';
                }
            }];

            $this->col[] = ["label"=>"数据类型","name"=>"data_type","callback"=>function ($row) {
                if( $row->data_type == Task::DATA_TYPE_CASPERJS) {
                    return 'casperJs';
                } else if ($row->data_type == Task::DATA_TYPE_HTML) {
                    return 'html';
                } else if ($row->data_type == Task::DATA_TYPE_API) {
                    return 'api';
                }
            }];

            $this->col[] = ["label"=>"语言类型","name"=>"language_type","callback"=>function ($row) {
                if( $row->language_type == Task::LANGUAGE_TYPE_ENGLISH) {
                    return '英文';
                } else if ($row->language_type == Task::LANGUAGE_TYPE_CHINESE) {
                    return '中文';
                } else {
                    return '未知';
                }
            }];

            $this->col[] = ["label"=>"状态","name"=>"status","callback"=>function ($row) {
                if( $row->status == Task::STATUS_INIT) {
                    return "<a class='btn btn-xs btn-warning'><i></i>初始化</a>";
                } else if ($row->status == Task::STATUS_START) {
                    return "<a class='btn btn-xs btn-success'><i></i>已启动</a>";
                }
            }];

			# END COLUMNS DO NOT REMOVE THIS LINE

			# START FORM DO NOT REMOVE THIS LINE
			$this->form = [];
			$this->form[] = ['label'=>'Script Id','name'=>'script_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Name','name'=>'name','type'=>'text','validation'=>'required|string|min:3|max:70','width'=>'col-sm-10','placeholder'=>'请输入字母'];
			$this->form[] = ['label'=>'Description','name'=>'description','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
            $this->form[] = ['label'=>'Test Url','name'=>'test_url','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
            $this->form[] = ['label'=>'Test Result','name'=>'test_result','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			# END FORM DO NOT REMOVE THIS LINE

			# OLD START FORM
			//$this->form = [];
			//$this->form[] = ["label"=>"Script Id","name"=>"script_id","type"=>"select2","required"=>TRUE,"validation"=>"required|integer|min:0","datatable"=>"script,id"];
			//$this->form[] = ["label"=>"Name","name"=>"name","type"=>"text","required"=>TRUE,"validation"=>"required|string|min:3|max:70","placeholder"=>"请输入字母"];
			//$this->form[] = ["label"=>"Description","name"=>"description","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Cron Type","name"=>"cron_type","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Languages Type","name"=>"languages_type","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Status","name"=>"status","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
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
            $this->sub_module[] = ['label'=>'结果列表','path'=>'t_data','foreign_key'=>'task_id','button_color'=>'success','button_icon'=>'fa fa-bars', 'parent_columns'=>'id'];

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

            $this->addaction[] = ['label'=>'启动', 'url'=>CRUDBooster::mainpath('start-up/[id]'),'color'=>'success', 'icon'=>'fa fa-play', 'showIf'=>'[status] == 1 && [cron_type] != 4'];

            $this->addaction[] = ['label'=>'停止', 'url'=>CRUDBooster::mainpath('stop-down/[id]'),'color'=>'warning', 'icon'=>'fa fa-stop', 'showIf'=>'[status] == 2'];

            $this->addaction[] = ['label'=>'测试', 'url'=>CRUDBooster::mainpath('test/[id]'), 'icon'=>'fa fa-play'];
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

            $this->index_statistic[] = ['label'=>'casper启动中列表','count'=>Task::where('data_type', 1)->where('cron_type', '!=', 4)->where('status', 2)->where('deleted_at', null)->count(),'icon'=>'glyphicon glyphicon-tasks','color'=>'warning'];
            $this->index_statistic[] = ['label'=>'html启动中列表','count'=>Task::where('data_type', 2)->where('cron_type', '!=', 4)->where('status', 2)->where('deleted_at', null)->count(),'icon'=>'glyphicon glyphicon-tasks','color'=>'info'];
            $this->index_statistic[] = ['label'=>'api启动中列表','count'=>Task::where('data_type', 3)->where('cron_type', '!=', 4)->where('status', 2)->where('deleted_at', null)->count(),'icon'=>'glyphicon glyphicon-tasks','color'=>'success'];
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
            $query->where('cron_type', '!=', Task::CRON_TYPE_KEEP_ONCE);
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

        public function getStartUp($id)
        {
            try {
                $result = InternalAPIV2Service::post('/task/start', ['id' => intval($id)]);
            } catch (\Dingo\Api\Exception\ResourceException $e) {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "error");
            }

            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "启动成功", "success");
        }

        public function getStopDown($id)
        {
            try {
                $result = InternalAPIV2Service::post('/task/stop', ['id' => intval($id)]);
            } catch (\Dingo\Api\Exception\ResourceException $e) {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "error");
            }

            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "停止成功", "success");
        }

        public function getTest($id)
        {
            try {
                $task = InternalAPIV2Service::get('/task', ['id' => intval($id)]);

                //判断task数据是否存在
                if (empty($task)) {
                    CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "task is not found", "error");
                }

                $result = InternalAPIV2Service::post('/task/test', ['task_id' => intval($id), 'test_url' => $task['list_url']]);
            } catch (\Dingo\Api\Exception\ResourceException $e) {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "error");
            }

            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "测试已提交", "success");
        }
	    //By the way, you can still create your own method in here... :)
        public function getDetail($id)	{
            $this->cbLoader();
            $row        = Task::find($id)->toArray();
            $testResult = json_decode($row['test_result'], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);

            if (!is_array($testResult[0])) {
                $row['test_result'] = $testResult[0];
            }

            if(!CRUDBooster::isRead() && $this->global_privilege==true || $this->button_edit==true) {
                CRUDBooster::redirect(CRUDBooster::adminPath(),trans("crudbooster.denied_access"));
            }

            $data = [];
            $data['page_title'] = '任务详情';
            $data['row'] = $row;
            if ($data['row']['is_proxy'] == 1) {
                $data['row']['is_proxy'] = '翻墙';
            } else {
                $data['row']['is_proxy'] = '不翻墙';
            }

            if ($data['row']['language_type'] == Task::LANGUAGE_TYPE_ENGLISH) {
                $data['row']['language_type'] = '英文';
            } elseif ($data['row']['language_type'] == Task::LANGUAGE_TYPE_CHINESE) {
                $data['row']['language_type'] = '中文';
            }

            $this->cbView('task/task_detail_view',$data);

        }

	}