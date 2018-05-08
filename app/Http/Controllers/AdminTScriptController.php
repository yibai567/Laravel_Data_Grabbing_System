<?php namespace App\Http\Controllers;

use Session;
use Request;
use DB;
use CRUDBooster;
use App\Models\Script;
use App\Services\InternalAPIService;

class AdminTScriptController extends \crocodicstudio\crudbooster\controllers\CBController {

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
		$this->table = "t_script";
		# END CONFIGURATION DO NOT REMOVE THIS LINE

		# START COLUMNS DO NOT REMOVE THIS LINE
		$this->col = [];
		$this->col[] = ["label"=>"Id","name"=>"id"];
		$this->col[] = ["label"=>"名称","name"=>"name"];
        $this->col[] = ["label"=>"执行规则","name"=>"cron_type","callback"=>function ($row) {
            if ( $row->cron_type == Script::CRON_TYPE_KEEP) {
                return '持续执行';
            } else if( $row->cron_type == Script::CRON_TYPE_EVERY_MINUTE) {
                return '每分钟执行';
            } else if ($row->cron_type == Script::CRON_TYPE_EVERY_FIVE_MINIT) {
                return '每五分钟执行';
            } else if ($row->cron_type == Script::CRON_TYPE_EVERY_FIFTHEEN_MINIT) {
                return '每十五分钟执行';
            }
        }];

		$this->col[] = ["label"=>"最后生成时间","name"=>"last_generate_at"];
        $this->col[] = ["label"=>"状态","name"=>"status","callback"=>function ($row) {
            if ( $row->status == Script::STATUS_INIT) {
                return '初始化';
            } else {
                return '以生成';
            }
        }];

		# END COLUMNS DO NOT REMOVE THIS LINE

		# START FORM DO NOT REMOVE THIS LINE
		$this->form = [];
		$this->form[] = ['label'=>'名称','name'=>'name','type'=>'text','validation'=>'required|string|max:100','width'=>'col-sm-10'];
		$this->form[] = ['label'=>'描述','name'=>'description','type'=>'textarea','validation'=>'required|max:255','width'=>'col-sm-10'];
		$this->form[] = ['label'=>'load_images','name'=>'load_images','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|true;2|false;'];
        $this->form[] = ['label'=>'load_plugins','name'=>'load_plugins','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|true;2|false;'];
        $this->form[] = ['label'=>'log_level','name'=>'log_level','type'=>'radio','validation'=>'required|string','width'=>'col-sm-10','dataenum'=>'debug;error;'];
        $this->form[] = ['label'=>'verbose','name'=>'verbose','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|true;2|false;'];
        $this->form[] = ['label'=>'width','name'=>'width','type'=>'text','validation'=>'nullable|integer','width'=>'col-sm-10'];
        $this->form[] = ['label'=>'height','name'=>'height','type'=>'text','validation'=>'nullable|integer','width'=>'col-sm-10'];

		$this->form[] = ['label'=>'步骤','name'=>'step','type'=>'textarea','validation'=>'required','width'=>'col-sm-10'];
		$this->form[] = ['label'=>'执行规则','name'=>'cron_type','type'=>'radio','validation'=>'nullable|integer','width'=>'col-sm-10','dataenum'=>'1|持续执行;2|每分钟执行一次;3|每小时执行一次;4|每天执行一次','value'=>'1'];
		# END FORM DO NOT REMOVE THIS LINE

		# OLD START FORM
		//$this->form = [];
		//$this->form[] = ["label"=>"Name","name"=>"name","type"=>"text","required"=>TRUE,"validation"=>"required|string|min:3|max:70","placeholder"=>"请输入字母"];
		//$this->form[] = ["label"=>"Description","name"=>"description","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
		//$this->form[] = ["label"=>"Script Init Id","name"=>"script_init_id","type"=>"select2","required"=>TRUE,"validation"=>"required|integer|min:0","datatable"=>"script_init,id"];
		//$this->form[] = ["label"=>"Step","name"=>"step","type"=>"textarea","required"=>TRUE,"validation"=>"required|string|min:5|max:5000"];
		//$this->form[] = ["label"=>"Cron Type","name"=>"cron_type","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
		//$this->form[] = ["label"=>"Last Generate At","name"=>"last_generate_at","type"=>"datetime","required"=>TRUE,"validation"=>"required|date_format:Y-m-d H:i:s"];
		//$this->form[] = ["label"=>"Status","name"=>"status","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
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
        $this->addaction[] = ['label'=>'发布', 'url'=>CRUDBooster::mainpath('publish/[id]'),'color'=>'warning', 'icon'=>'glyphicon glyphicon-send', 'showIf'=>'[status] == ' . Script::STATUS_INIT];

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
        dd($postdata);
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

    public function getAdd() {

        if(!CRUDBooster::isCreate() && $this->global_privilege==FALSE || $this->button_add==FALSE) {
            CRUDBooster::redirect(CRUDBooster::adminPath(),trans("crudbooster.denied_access"));
        }

        $data = [];
        $data['page_title'] = '增加脚本生成信息';

        $this->cbView('script/script_add_view',$data);
    }

    public function getEdit($id) {

        if(!CRUDBooster::isCreate() && $this->global_privilege==FALSE || $this->button_add==FALSE) {
            CRUDBooster::redirect(CRUDBooster::adminPath(),trans("crudbooster.denied_access"));
        }

        $data = [];
        $data['page_title'] = '编辑脚本生成信息';
        $data['row'] = InternalAPIService::get('/script', ['id' => $id]);
        if (empty($data['row'])) {
            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "数据信息有误", "error");
        }
        $this->cbView('script/script_add_view',$data);
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
        $data['status'] = Script::STATUS_INIT;
        $data['name'] = $formParams['name'];
        $data['description'] = $formParams['description'];

        $data['init'] = json_encode([
                                    'load_images' => $formParams['load_images'],
                                    'load_plugins' => $formParams['load_plugins'],
                                    'log_level' => $formParams['log_level'],
                                    'verbose' => $formParams['verbose'],
                                    'width' => $formParams['width'],
                                    'height' => $formParams['height'],
                                     ]);
        $data['step'] = json_decode($formParams['step']);
        $data['cron_type'] = $formParams['cron_type'];
        $data['operate_user'] = CRUDBooster::myName();
        $data['id'] = $id;

        try {
            $res = InternalAPIService::post('/script/update', $data);
        } catch (\Dingo\Api\Exception\ResourceException $e) {
            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "error");
        }

        CRUDBooster::redirect($_SERVER['HTTP_ORIGIN'] . "/admin/t_script", "修改成功", "success");
    }

    public function postAddSave() {
        $this->cbLoader();
        if(!CRUDBooster::isCreate() && $this->global_privilege==FALSE) {
            CRUDBooster::insertLog(trans('crudbooster.log_try_add_save',['name'=>Request::input($this->title_field),'module'=>CRUDBooster::getCurrentModule()->name ]));
            CRUDBooster::redirect(CRUDBooster::adminPath(),trans("crudbooster.denied_access"));
        }

        $this->validation();
        $this->input_assignment();

        $data = [];
        $formParams = $this->arr;
        $data['name'] = $formParams['name'];
        $data['description'] = $formParams['description'];

        $data['init'] = json_encode([
                                    'load_images' => $formParams['load_images'],
                                    'load_plugins' => $formParams['load_plugins'],
                                    'log_level' => $formParams['log_level'],
                                    'verbose' => $formParams['verbose'],
                                    'width' => $formParams['width'],
                                    'height' => $formParams['height'],
                                     ]);
        $data['step'] = json_decode($formParams['step']);
        $data['cron_type'] = $formParams['cron_type'];
        $data['operate_user'] = CRUDBooster::myName();

        try {
            $res = InternalAPIService::post('/script', $data);
        } catch (\Dingo\Api\Exception\ResourceException $e) {
            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "error");
        }

        CRUDBooster::redirect($_SERVER['HTTP_ORIGIN'] . "/admin/t_script", "创建成功", "success");
    }

    public function getDetail($id) {

        if(!CRUDBooster::isRead() && $this->global_privilege==FALSE || $this->button_edit==FALSE) {
            CRUDBooster::redirect(CRUDBooster::adminPath(),trans("crudbooster.denied_access"));
        }

        $data = [];
        $data['page_title'] = '脚本详情';

        $data['row'] = InternalAPIService::get('/script', ['id' => $id]);
        if (empty($data['row'])) {
            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "数据信息有误", "error");
        }

        if (!empty($data['row']['init'])) {
            $data['row']['init'] = json_encode($data['row']['init'],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
        }

        if (!empty($data['row']['step'])) {
            $data['row']['step'] = json_encode(json_decode($data['row']['step']),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
        }

        if ($data['row']['cron_type'] == Script::CRON_TYPE_KEEP) {
            $data['row']['cron_type'] = '持续执行';
        } else if($data['row']['cron_type'] == Script::CRON_TYPE_EVERY_MINUTE) {
            $data['row']['cron_type'] = '每分钟执行一次';
        } else if( $data['row']['cron_type'] == Script::CRON_TYPE_EVERY_FIVE_MINIT) {
            $data['row']['cron_type'] = '每五分钟执行一次';
        } else if ($data['row']['cron_type'] == Script::CRON_TYPE_EVERY_FIFTHEEN_MINIT) {
            $data['row']['cron_type'] = '每十五分钟执行';
        }

        if ($data['row']['status'] == Script::STATUS_INIT) {
            $data['row']['status'] = '初始化';
        } else {
            $data['row']['status'] = '已生成';
        }


        $this->cbView('script/script_detail_view',$data);
    }

    public function getPublish($id) {
        try {
            $res = InternalAPIService::get('/script/generate', ['id' => $id]);
        } catch (\Dingo\Api\Exception\ResourceException $e) {
            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "error");
        }
        CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "创建成功", "success");
    }

    //By the way, you can still create your own method in here... :)


}