<?php namespace App\Http\Controllers;

	use App\Models\Item;
    use App\Services\APIService;
    use App\Services\InternalAPIService;
    use Session;
	use Request;
	use DB;
	use CRUDBooster;

	class AdminTItemController extends \crocodicstudio\crudbooster\controllers\CBController {

        // 状态
        const STATUS_NO_STARTING = 1;
        const STATUS_TEST_SUCCESS = 2;
        const STATUS_TEST_FAIL = 3;
        const STATUS_START_UP = 4;
        const STATUS_STOP = 5;

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
			$this->button_delete = true;
			$this->button_detail = true;
			$this->button_show = true;
			$this->button_filter = true;
			$this->button_import = false;
			$this->button_export = false;
			$this->table = "t_item";
			# END CONFIGURATION DO NOT REMOVE THIS LINE

			# START COLUMNS DO NOT REMOVE THIS LINE
			$this->col = [];
			$this->col[] = ["label"=>"ID","name"=>"id"];
			$this->col[] = ["label"=>"任务名称","name"=>"name","width"=>'200'];
            $this->col[] = ["label"=>"资源地址","name"=>"resource_url",'width'=>'200',"callback"=>function ($row) {
                return '<a href="' . $row->resource_url . '" target="_brank" style="width:200px;overflow: hidden; display: -webkit-box;text-overflow: ellipsis; word-break: break-all;-webkit-box-orient: vertical;-webkit-line-clamp: 1;">'. $row->resource_url .'</a>';
            }];
            $this->col[] = ["label"=>"数据类型","name"=>"data_type","callback"=>function ($row) {
                if ( $row->data_type == 1) {
                    return 'html';
                } else if( $row->data_type == 2) {
                    return 'json';
                } else {
                    return '截图';
                }
            }];

            $this->col[] = ["label"=>"内容类型","name"=>"content_type","callback"=>function ($row) {
                if ( $row->content_type == 1) {
                    return '短内容';
                } else {
                    return '长内容';
                }
            }];

            $this->col[] = ["label"=>"执行频次","name"=>"cron_type","callback"=>function ($row) {
                if ( $row->cron_type == 1) {
                    return '持续执行';
                } else if( $row->cron_type == 2) {
                    return '每分钟执行一次';
                } else if( $row->cron_type == 3) {
                    return '每小时执行一次';
                } else if ($row->cron_type == 4) {
                    return '每天执行一次';
                } else if ($row->cron_type == 5) {
                    return '执行一次';
                }
            }];
            $this->col[] = ["label"=>"是否翻墙","name"=>"is_proxy","callback"=>function ($row) {
                if ( $row->is_proxy == 1) {
                    return '是';
                } else {
                    return '否';
                }
            }];

			$this->col[] = ["label"=>"最后执行时间","name"=>"last_job_at"];
            $this->col[] = ["label"=>"状态","name"=>"status","callback"=>function ($row) {
                if ( $row->status == 1) {
                    return '未启动';
                } else if( $row->status == 2) {
                    return '测试中';
                } else if( $row->status == 3) {
                    return '测试成功';
                } else if( $row->status == 4) {
                    return '测试失败';
                } else if( $row->status == 5) {
                    return '运行中';
                } else if( $row->status == 6) {
                    return '已停止';
                }
            }];
			# END COLUMNS DO NOT REMOVE THIS LINE

			# START FORM DO NOT REMOVE THIS LINE
			$this->form = [];
			$this->form[] = ['label'=>'任务名称','name'=>'name','type'=>'text','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'数据类型','name'=>'data_type','type'=>'radio','validation'=>'required|integer|between:1,2','width'=>'col-sm-10','dataenum'=>'1|html;2|json;3|截图','value'=>'1'];
			$this->form[] = ['label'=>'内容类型','name'=>'content_type','type'=>'radio','validation'=>'required|integer|between:1,2','width'=>'col-sm-10','dataenum'=>'1|短内容;2|长内容','value'=>'1'];
			$this->form[] = ['label'=>'Type','name'=>'type','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|快讯','value'=>'1'];
            $this->form[] = ['label'=>'Cron Type','name'=>'cron_type','type'=>'radio','validation'=>'required|integer|in:1,2,3,4','width'=>'col-sm-10','dataenum'=>'1|持续执行;2|每分钟;3|每小时;4|每天;5|执行一次','value'=>'1'];
            $this->form[] = ['label'=>'是否翻墙','name'=>'is_proxy','type'=>'radio','validation'=>'required|integer|between:1,2','width'=>'col-sm-10','dataenum'=>'1|是;2|否','value'=>'2'];
            $this->form[] = ['label'=>'Status','name'=>'status','type'=>'hidden','width'=>'col-sm-10'];

			$this->form[] = ['label'=>'资源URL','name'=>'resource_url','type'=>'text','validation'=>'required|string','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'短内容选择器','name'=>'short_content_selector','type'=>'textarea','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'长内容选择器','name'=>'long_content_selector','type'=>'textarea','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'图片配置','name'=>'capture_config','type'=>'textarea','width'=>'col-sm-10'];
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
            $this->addaction[] = ['label'=>'测试', 'url'=>CRUDBooster::mainpath('test/[id]'),'color'=>'info', 'icon'=>'fa fa-play'];
            $this->addaction[] = ['label'=>'测试结果', 'url'=>CRUDBooster::mainpath('test-result/[id]'),'color'=>'info', 'icon'=>'fa fa-play'];

            $this->addaction[] = ['label'=>'启动', 'url'=>CRUDBooster::mainpath('start-up/[id]'),'color'=>'success', 'icon'=>'fa fa-play', 'showIf'=>'[status] == ' . self::STATUS_TEST_SUCCESS . '|| [status] == ' . self::STATUS_STOP];

            $this->addaction[] = ['label'=>'停止', 'url'=>CRUDBooster::mainpath('stop-down/[id]/' . self::STATUS_STOP),'color'=>'warning', 'icon'=>'fa fa-stop', 'showIf'=>'[status] == ' . self::STATUS_START_UP];

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
	        $this->post_index_html = '';



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
            //调用创建接口创建任务

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



	    //By the way, you can still create your own method in here... :)

        public function getTest($id)
        {
            $uri = '/v1/item/test';
            $params['id'] = intval($id);
            $result = APIService::openPost($uri, $params);
            if (empty($result))
            {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "info");
            }
            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "测试提交成功，请稍后查看测试结果", "info");

        }

        public function getTestResult($id)
        {

        }

        public function getStartUp($id)
        {
            $uri = '/v1/item/start';
            $params['id'] = intval($id);
            $result = APIService::openPost($uri, $params);

            if (empty($result))
            {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "info");
            }
            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "启动成功", "info");
        }

        public function getStopDown($id, $status)
        {
            $uri = '/v1/item/stop';
            $params['id'] = intval($id);
            $result = APIService::openPost($uri, $params);
            if (empty($result))
            {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "info");
            }
            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "停止成功", "info");
        }
	}