<?php namespace App\Http\Controllers;

	use Session;
	use Request;
	use DB;
	use CRUDBooster;
    use App\Models\V2\ScriptModel;
    use App\Services\InternalAPIV2Service;
    use Illuminate\Support\Facades\Route;

	class AdminTScriptModelController extends \crocodicstudio\crudbooster\controllers\CBController {

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
			$this->table = "t_script_model";
			# END CONFIGURATION DO NOT REMOVE THIS LINE

			# START COLUMNS DO NOT REMOVE THIS LINE
			$this->col = [];
			$this->col[] = ["label"=>"ID","name"=>"id"];
            $this->col[] = ["label"=>"模块名称","name"=>"name"];
			$this->col[] = ["label"=>"脚本语言","name"=>"data_type","callback"=>function ($row) {
            if ( $row->data_type == ScriptModel::DATA_TYPE_CASPERJS) {
                return 'casperjs';
            } elseif ($row->data_type == ScriptModel::DATA_TYPE_HTML) {
                return 'html';
            } elseif ($row->data_type == ScriptModel::DATA_TYPE_API) {
                return 'api';
            }
            }];
            $this->col[] = ["label"=>"类型","name"=>"system_type","callback"=>function ($row) {
            if ($row->system_type == ScriptModel::SYSTEM_TYPE_DEFAULT) {
                return '预生成模块';
            } elseif ($row->system_type == ScriptModel::SYSTEM_TYPE_BASE) {
                return '基础模版';
            } else {
                return '用户自定义模块';
            }
            }];
            $this->col[] = ["label"=>"排序","name"=>"sort"];
			$this->col[] = ["label"=>"操作用户","name"=>"operate_user"];
			# END COLUMNS DO NOT REMOVE THIS LINE

			# START FORM DO NOT REMOVE THIS LINE
			$this->form = [];
			$this->form[] = ['label'=>'模块名称','name'=>'name','type'=>'text','validation'=>'required|string|max:50','width'=>'col-sm-10'];
            $this->form[] = ['label'=>'模块描述','name'=>'description','type'=>'textarea','validation'=>'nullable|string','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'脚本语言','name'=>'data_type','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|casperjs;2|html;3|api','value'=>'1'];

			$this->form[] = ['label'=>'代码结构','name'=>'structure','type'=>'textarea','validation'=>'required|string','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'参数规则','name'=>'parameters','type'=>'textarea','validation'=>'required|json','width'=>'col-sm-10'];
            $this->form[] = ['label'=>'排序','name'=>'sort','type'=>'text','validation'=>'required|integer|max:99','width'=>'col-sm-10', 'value' => 99];
			$this->form[] = ['label'=>'类型','name'=>'system_type','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|预生成模块;2|用户自定义模块;3|基础模版','value'=>'1'];

			# END FORM DO NOT REMOVE THIS LINE

			# OLD START FORM
			//$this->form = [];
			//$this->form[] = ["label"=>"Name","name"=>"name","type"=>"text","required"=>TRUE,"validation"=>"required|string|min:3|max:70","placeholder"=>"请输入字母"];
			//$this->form[] = ["label"=>"Description","name"=>"description","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Structure","name"=>"structure","type"=>"textarea","required"=>TRUE,"validation"=>"required|string|min:5|max:5000"];
			//$this->form[] = ["label"=>"Parameters","name"=>"parameters","type"=>"textarea","required"=>TRUE,"validation"=>"required|string|min:5|max:5000"];
			//$this->form[] = ["label"=>"System Type","name"=>"system_type","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Operate User","name"=>"operate_user","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
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
            $query->where('system_type', '!=', ScriptModel::SYSTEM_TYPE_BASE);
            $query->orderBy('sort', 'asc');
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

        public function postAddSave() {
            $this->cbLoader();
            if(!CRUDBooster::isCreate() && $this->global_privilege==FALSE) {
                CRUDBooster::insertLog(trans('crudbooster.log_try_add_save',['name'=>Request::input($this->title_field),'module'=>CRUDBooster::getCurrentModule()->name ]));
                CRUDBooster::redirect(CRUDBooster::adminPath(),trans("crudbooster.denied_access"));
            }

            $this->validation();
            $this->input_assignment();

            $formParams = $this->arr;

            $formParams['operate_user'] = CRUDBooster::myName();

            try {
                $res = InternalAPIV2Service::post('/script_model', $formParams);
            } catch (\Dingo\Api\Exception\ResourceException $e) {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "error");
            }

            CRUDBooster::redirect($_SERVER['HTTP_ORIGIN'] . "/admin/t_script_model", "创建成功", "success");
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

            $data = [];
            $formParams = $this->arr;

            $formParams['id'] = $id;
            $formParams['operate_user'] = CRUDBooster::myName();

            try {
                $res = InternalAPIV2Service::post('/script_model/update', $formParams);
            } catch (\Dingo\Api\Exception\ResourceException $e) {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "error");
            }

            CRUDBooster::redirect($_SERVER['HTTP_ORIGIN'] . "/admin/t_script_model", "修改成功", "success");
        }

        public function getDetail($id) {
            $this->cbLoader();
            $row = ScriptModel::where('id', $id)->first();

            if ($row->system_type == ScriptModel::SYSTEM_TYPE_DEFAULT) {
                $row->system_type = '预生成模块';
            } elseif ($row->system_type == ScriptModel::SYSTEM_TYPE_BASE) {
                $row->system_type = '基础模版';
            } else {
                $row->system_type = '用户自定义模块';
            }

            if ($row->data_type == ScriptModel::DATA_TYPE_CASPERJS) {
                $row->data_type = 'casperjs';
            } elseif ($row->data_type == ScriptModel::DATA_TYPE_HTML) {
                $row->data_type = 'html';
            } elseif ($row->data_type == ScriptModel::DATA_TYPE_API) {
                $row->data_type = 'api';
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

	    //By the way, you can still create your own method in here... :)


	}