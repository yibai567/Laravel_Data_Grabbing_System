<?php namespace App\Http\Controllers;

	use Session;
	use Request;
	use DB;
	use CRUDBooster;
    use Config;
    use App\Models\Requirement;
    use App\Services\InternalAPIService;

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
			$this->col[] = ["label"=>"任务名称","name"=>"name"];
            $this->col[] = ["label"=>"列表URL","name"=>"list_url",'width'=>'200',"callback"=>function ($row) {
                return '<a href="' . $row->list_url . '" target="_brank" style="width:200px;overflow: hidden; display: -webkit-box;text-overflow: ellipsis; word-break: break-all;-webkit-box-orient: vertical;-webkit-line-clamp: 1;">'. $row->list_url .'</a>';
            }];
			$this->col[] = ["label"=>"订阅类型","name"=>"subscription_type","callback"=>function ($row){
                if ( $row->subscription_type == Requirement::SUBSCRIPTION_TYPE_LIST) {
                    return '列表';
                } else {
                    return '详情';
                }
            }];
			$this->col[] = ["label"=>"是否截图","name"=>"is_capture","callback"=>function ($row) {
                if ( $row->is_capture == Requirement::IS_CAPTURE_TRUE) {
                    return '是';
                } else {
                    return '否';
                }
            }];

			$this->col[] = ["label"=>"是否下载图片","name"=>"is_download_img","callback"=>function ($row) {
                if ( $row->is_download_img == Requirement::IS_DOWNLOAD_TRUE) {
                    return '是';
                } else {
                    return '否';
                }
            }];
             $this->col[] = ["label"=>"状态","name"=>"status","callback"=>function ($row) {

                if ($row->status == Requirement::STATUS_TRUE) {
                    return '未处理';
                } else {
                    return '已处理';
                }
            }];
            $this->col[] = ["label"=>"创建人","name"=>"create_by","callback"=>function ($row) {

                $createBy = config('user');

                return $createBy[$row->create_by];



            }];
            $this->col[] = ["label"=>"操作人","name"=>"operate_by","callback"=>function ($row) {

                 return $this->__getUser($row->operate_by);



            }];


            $this->col[] = ["label"=>"创建时间","name"=>"created_at"];
            $this->col[] = ["label"=>"执行时间","name"=>"updated_at"];

			# END COLUMNS DO NOT REMOVE THIS LINE

			# START FORM DO NOT REMOVE THIS LINE
			$this->form = [];
			$this->form[] = ['label'=>'任务名称','name'=>'name','type'=>'text','validation'=>'nullable|string|max:100','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'抓取url','name'=>'list_url','type'=>'text','validation'=>'required|string|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'描述','name'=>'description','type'=>'textarea','validation'=>'nullable|min:10|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'图片描述','name'=>'img_description','type'=>'upload','validation'=>'required|max:255','width'=>'col-sm-10',"callback"=>function ($row) {



                 $newIp=$_SERVER['HTTP_HOST']."/".$row->img_description;
                 $domain = strstr($newIp, '/uploads');
                 $local='/'.$row->img_description;
                 if($domain){
                   $string='<div>';
                   $string='<div><img src="'.$local.'" width="150" height="150" style="margin-left:278px;margin-bottom:15px"><div/>';
                   $string.='<div/>';
                   echo $string;
                 }else{
                   $string='<div>';
                   $string='<div><img src="'.$row->img_description.'" width="150" height="150" style="margin-left:278px;margin-bottom:15px"><div/>';
                   $string.='<div/>';
                   echo $string;
                 }



            }];
			$this->form[] = ['label'=>'订阅类型','name'=>'subscription_type','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|列表;2|详情'];
			$this->form[] = ['label'=>'是否截图','name'=>'is_capture','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|是;2|否'];
			$this->form[] = ['label'=>'是否下载图片','name'=>'is_download_img','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|是;2|否'];
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
            $this->addaction[] = ['label'=>'执行', 'url'=>CRUDBooster::mainpath('modify-state/[id]/' . Requirement::STATUS_FALSE),'color'=>'warning', 'icon'=>'ion-arrow-right-c', 'showIf'=>'[status] == ' . Requirement::STATUS_TRUE];
            $this->addaction[] = ['label'=>'已执行', 'url'=>CRUDBooster::mainpath('modify-state/[id]/' . Requirement::STATUS_TRUE),'color'=>'info', 'icon'=>'ion-arrow-right-c', 'showIf'=>'[status] == ' . Requirement::STATUS_FALSE];
            // $this->addaction[] = ['label'=>'启动', 'url'=>CRUDBooster::mainpath('start-up/[id]'),'color'=>'success', 'icon'=>'fa fa-play', 'showIf'=>'[status] == ' . Item::STATUS_TEST_SUCCESS . '|| [status] == ' . Item::STATUS_STOP];

            // $this->addaction[] = ['label'=>'停止', 'url'=>CRUDBooster::mainpath('stop-down/[id]'),'color'=>'warning', 'icon'=>'fa fa-stop', 'showIf'=>'[status] == ' . Item::STATUS_START];

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


        public function getModifyState($id,$status) {

            $params['id'] = $id;
            $params['status'] = $status;
            $params['user_id'] = CRUDBooster::myId();

            try {
                $result = InternalAPIService::get('/quirement/update_status', $params);
            } catch (\Dingo\Api\Exception\ResourceException $e) {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "error");
            }

           CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "状态修改成功", "info");
        }

        private function __getUser($id) {
            $res=DB::table('cms_users')->select('name')->where('id', $id)->first();

            return $res->name;
        }

	    //By the way, you can still create your own method in here... :)


	}