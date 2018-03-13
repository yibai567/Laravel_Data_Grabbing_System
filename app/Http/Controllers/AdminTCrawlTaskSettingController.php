<?php namespace App\Http\Controllers;

	use Session;
	use Request;
	use DB;
	use CRUDBooster;
    use Illuminate\Support\Facades\Route;

	class AdminTCrawlTaskSettingController extends \crocodicstudio\crudbooster\controllers\CBController {

        // 状态可用
        const STATUS_SHOW = 1;

        // 状态不可用
        const STATUS_HIDE = 2;

        //数据类型 1、html,2、json,3、xml
        const DATA_TYPE_HTML = 1;
        const DATA_TYPE_JSON = 2;
        const DATA_TYPE_XML = 3;

        //内容类型 1、list,2、content
        const CONTENT_TYPE_LIST = 1;
        const CONTENT_TYPE_CONTENT = 2;

        //是否代理支持 1、需要，2、不需要
        const IS_PROXY_YES = 1;
        const IS_PROXY_NO = 2;

        //响应类型 1、API,2、邮件，3、短信，4、企业微信
        const RESPONSE_TYPE_API = 1;
        const RESPONSE_TYPE_EMAIL = 2;
        const RESPONSE_TYPE_SMS = 3;
        const RESPONSE_TYPE_ENTERPRISE_WECHAT = 4;

        //模版类型 1、通用，2自定义
        const TEMPLATE_GENERAL_PURPOSE = 1;
        const TEMPLATE_CUSTOM = 2;


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
			$this->table = "t_crawl_task_setting";
			# END CONFIGURATION DO NOT REMOVE THIS LINE

			# START COLUMNS DO NOT REMOVE THIS LINE
			$this->col = [];
			$this->col[] = ["label"=>"ID","name"=>"id"];
			$this->col[] = ["label"=>"规则名称","name"=>"name"];
			$this->col[] = ["label"=>"规则描述","name"=>"description"];
			$this->col[] = ["label"=>"资源URl","name"=>"url"];
            $this->col[] = ["label"=>"数据类型","name"=>"data_type","callback"=>function ($row) {
                if ( $row->data_type == self::DATA_TYPE_HTML) {
                    return 'html';
                } else if( $row->data_type == self::DATA_TYPE_JSON) {
                    return 'json';
                }
            }];
            $this->col[] = ["label"=>"内容类型","name"=>"content_type","callback"=>function ($row) {
                if ( $row->content_type == self::CONTENT_TYPE_LIST) {
                    return 'list';
                } else if( $row->content_type == self::CONTENT_TYPE_CONTENT) {
                    return 'content';
                }
            }];
            $this->col[] = ["label"=>"状态","name"=>"status","callback"=>function ($row) {
                if ( $row->status == self::STATUS_SHOW) {
                    return '可用';
                } else if( $row->status == self::STATUS_HIDE) {
                    return '不可用';
                }
            }];
            $this->col[] = ["label"=>"模版类型","name"=>"type","callback"=>function ($row) {
                if ( $row->type == self::TEMPLATE_GENERAL_PURPOSE) {
                    return '通用';
                } else if( $row->type == self::TEMPLATE_CUSTOM) {
                    return '自定义';
                }
            }];
			# END COLUMNS DO NOT REMOVE THIS LINE

			# START FORM DO NOT REMOVE THIS LINE
			$this->form = [];
			$this->form[] = ['label'=>'规则名称','name'=>'name','type'=>'text','validation'=>'required|string|min:1|max:50','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'规则描述','name'=>'description','type'=>'textarea','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'模版类型','name'=>'type','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-9','dataenum'=>'1|通用;2|自定义','value'=>'1'];
			$this->form[] = ['label'=>'资源URL','name'=>'url','type'=>'text','validation'=>'','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'选择器','name'=>'selectors','type'=>'textarea','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'关键词','name'=>'keywords','type'=>'textarea','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'数据类型','name'=>'data_type','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|html;2|json','value'=>'1'];
			$this->form[] = ['label'=>'内容类型','name'=>'content_type','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|list;2|content','value'=>'1'];
			$this->form[] = ['label'=>'是否支持代理','name'=>'is_proxy','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|需要;2|不需要','value'=>'2'];
			$this->form[] = ['label'=>'响应类型','name'=>'response_type','type'=>'hidden','validation'=>'required','width'=>'col-sm-10','value'=>'1'];
			$this->form[] = ['label'=>'状态','name'=>'status','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|可用;2|不可用','value'=>'1'];
			$this->form[] = ['label'=>'脚本代码','name'=>'content','type'=>'textarea','width'=>'col-sm-9'];
			# END FORM DO NOT REMOVE THIS LINE

			# OLD START FORM
			//$this->form = [];
			//$this->form[] = ['label'=>'规则名称','name'=>'name','type'=>'text','validation'=>'required|string|min:1|max:50','width'=>'col-sm-10'];
			//$this->form[] = ['label'=>'规则描述','name'=>'description','type'=>'textarea','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			//$this->form[] = ['label'=>'模版类型','name'=>'type','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-9','dataenum'=>'1|通用;2|自定义'];
			//$this->form[] = ['label'=>'资源URL','name'=>'url','type'=>'text','validation'=>'alpha_spaces','width'=>'col-sm-10'];
			//$this->form[] = ['label'=>'选择器','name'=>'selectors','type'=>'textarea','width'=>'col-sm-10'];
			//$this->form[] = ['label'=>'关键词','name'=>'keywords','type'=>'textarea','width'=>'col-sm-10'];
			//$this->form[] = ['label'=>'数据类型','name'=>'data_type','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|html;2|json'];
			//$this->form[] = ['label'=>'内容类型','name'=>'content_type','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|list;2|content'];
			//$this->form[] = ['label'=>'是否支持代理','name'=>'is_proxy','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|需要;2|不需要'];
			//$this->form[] = ['label'=>'响应类型','name'=>'response_type','type'=>'hidden','validation'=>'required','width'=>'col-sm-10'];
			//$this->form[] = ['label'=>'状态','name'=>'status','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|可用;2|不可用'];
			//$this->form[] = ['label'=>'脚本代码','name'=>'content','type'=>'textarea','width'=>'col-sm-9'];
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

        public function getDetail($id) {
            $this->cbLoader();
             $row = DB::table('t_crawl_task_setting')->where('id', $id)->first();
             if ( $row->data_type == self::DATA_TYPE_HTML) {
                    $row->data_type = 'html';
                } else if( $row->data_type == self::DATA_TYPE_JSON) {
                    $row->data_type = 'json';
             }
             if ( $row->content_type == self::CONTENT_TYPE_LIST) {
                    $row->content_type = 'list';
                } else if( $row->content_type == self::CONTENT_TYPE_CONTENT) {
                    $row->content_type = 'content';
            }
            if ( $row->status == self::STATUS_SHOW) {
                $row->status = '可用';
                } else if( $row->status == self::STATUS_HIDE) {
                $row->status = '不可用';
            }
            if ( $row->type == self::TEMPLATE_GENERAL_PURPOSE) {
                $row->type = '通用';
            } else if( $row->type == self::TEMPLATE_CUSTOM) {
                $row->type = '自定义';
            }
            if ( $row->is_proxy == self::IS_PROXY_YES) {
                $row->is_proxy = 'html';
            } else if( $row->is_proxy == self::IS_PROXY_NO) {
                $row->is_proxy = 'json';
            }
            // $row->test_result = implode(" ", json_decode($row->test_result));
            // dd($row);
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

	    //By the way, you can still create your own method in here... :)


	}
