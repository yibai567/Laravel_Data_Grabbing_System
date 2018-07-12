<?php namespace App\Http\Controllers;

use App\Models\ScriptConfig;
use App\Services\FileService;
use Session;
use Request;
use Illuminate\Http\Request as HttpRequest;
use DB;
use CB;
use CRUDBooster;
use App\Models\Script;
use App\Services\InternalAPIV2Service;
use App\Models\Project;
use App\Models\Filter;
use App\Models\Action;

use Storage;
use Symfony\Component\HttpFoundation\File;
use Symfony\Component\HttpFoundation\File\UploadeFile;

class AdminTScriptController extends \crocodicstudio\crudbooster\controllers\CBController {

    public function cbInit() {

		# START CONFIGURATION DO NOT REMOVE THIS LINE
		$this->limit = "20";
		$this->orderby = "id,desc";
		$this->global_privilege = false;
		$this->button_table_action = true;
		$this->button_bulk_action = true;
		$this->button_action_style = "button_icon";
		$this->button_add = false;
		$this->button_edit = true;
		$this->button_delete = true;
		$this->button_detail = true;
		$this->button_show = false;
		$this->button_filter = true;
		$this->button_import = false;
		$this->button_export = false;
		$this->table = "t_script";
		# END CONFIGURATION DO NOT REMOVE THIS LINE

		# START COLUMNS DO NOT REMOVE THIS LINE
		$this->col = [];
        $this->col[] = ["label"=>"Id","name"=>"id"];
		$this->col[] = ["label"=>"name","name"=>"name"];
        $this->col[] = ["label"=>"数据类型","name"=>"data_type","callback"=>function ($row) {
            if( $row->data_type == Script::DATA_TYPE_CASPERJS) {
                return 'casperJs';
            } else if ($row->data_type == Script::DATA_TYPE_HTML) {
                return 'html';
            } else if ($row->data_type == Script::DATA_TYPE_API) {
                return 'api';
            }
        }];
		$this->col[] = ["label"=>"最后生成时间","name"=>"last_generate_at"];
        $this->col[] = ["label"=>"状态","name"=>"status","callback"=>function ($row) {
            if ( $row->status == Script::STATUS_INIT) {
                return '初始化';
            } else {
                return '已生成';
            }
        }];

		# END COLUMNS DO NOT REMOVE THIS LINE

		# START FORM DO NOT REMOVE THIS LINE
		$this->form = [];
        $this->form[] = ['label'=>'id','name'=>'id','type'=>'text'];
		$this->form[] = ['label'=>'名称','name'=>'name','type'=>'text','validation'=>'required|string|max:100','width'=>'col-sm-10'];
        $this->form[] = ['label'=>'语言类型','name'=>'data_type','type'=>'text','validation'=>'required|integer','width'=>'col-sm-10'];
        $this->form[] = ['label'=>'list_url','name'=>'list_url','type'=>'text','validation'=>'required|string','width'=>'col-sm-10'];
        $this->form[] = ['label'=>'描述','name'=>'description','type'=>'textarea','validation'=>'required|max:255','width'=>'col-sm-10'];
        $this->form[] = ['label'=>'发布内容','name'=>'content','type'=>'textarea','validation'=>'required|string','width'=>'col-sm-10'];
		$this->form[] = ['label'=>'load_images','name'=>'load_images','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|true;2|false;'];
        $this->form[] = ['label'=>'load_plugins','name'=>'load_plugins','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|true;2|false;'];
        $this->form[] = ['label'=>'log_level','name'=>'log_level','type'=>'radio','validation'=>'required|string','width'=>'col-sm-10','dataenum'=>'debug;error;'];
        $this->form[] = ['label'=>'verbose','name'=>'verbose','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|true;2|false;'];
        $this->form[] = ['label'=>'width','name'=>'width','type'=>'text','validation'=>'nullable|integer','width'=>'col-sm-10'];
        $this->form[] = ['label'=>'height','name'=>'height','type'=>'text','validation'=>'nullable|integer','width'=>'col-sm-10'];

        $this->form[] = ['label'=>'步骤','name'=>'modules','type'=>'textarea','validation'=>'nullable','width'=>'col-sm-10'];
        $this->form[] = ['name'=>'script_model_params','type'=>'text'];
		$this->form[] = ['label'=>'执行规则','name'=>'cron_type','type'=>'radio','validation'=>'nullable|integer','width'=>'col-sm-10','dataenum'=>'1|每分钟执行一次;2|每五分钟执行一次;3|每十五分钟执行一次;','value'=>'1'];
        $this->form[] = ['label'=>'是否上报','name'=>'is_report','type'=>'checkout','validation'=>'nullable|integer','width'=>'col-sm-10','value'=>'1'];
        $this->form[] = ['label'=>'是否下载','name'=>'is_download','type'=>'checkout','validation'=>'nullable|integer','width'=>'col-sm-10','value'=>'1'];
        $this->form[] = ['label'=>'是否翻墙','name'=>'is_proxy','type'=>'checkout','validation'=>'nullable|integer','width'=>'col-sm-10','value'=>'1'];
        $this->form[] = ['label'=>'需求池ID','name'=>'next_script_id','type'=>'text','validation'=>'nullable|integer','width'=>'col-sm-10'];
        $this->form[] = ['label'=>'下一步脚本ID','name'=>'requirement_pool_id','type'=>'text','validation'=>'nullable|integer','width'=>'col-sm-10'];
        $this->form[] = ['name'=>'projects','type'=>'text'];
        $this->form[] = ['name'=>'ext','type'=>'text'];
        $this->form[] = ['label'=>'project_config','name'=>'project_config','type'=>'text','validation'=>'nullable','width'=>'col-sm-10'];
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
        $this->index_statistic[] = ['label'=>'待发布','count'=>Script::where('status', Script::STATUS_INIT)->count(),'icon'=>'glyphicon glyphicon-send','color'=>'btn btn-xs btn-warning'];
        $this->index_statistic[] = ['label'=>'已发布','count'=>Script::where('status', Script::STATUS_GENERATE)->count(),'icon'=>'fa fa-check','color'=>'btn btn-xs btn-success'];


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

    //增加界面
    public function getAdd() {

        $dataType = Request::get('dataType');
        if (!$dataType) {
            $dataType = Script::DATA_TYPE_CASPERJS;
        }
        if(!CRUDBooster::isCreate() && $this->global_privilege==FALSE || $this->button_add==FALSE) {
            CRUDBooster::redirect(CRUDBooster::adminPath(),trans("crudbooster.denied_access"));
        }

        $data = [];
        $data['script_model'] = [];
        $data['new_step'] = [];
        $res = InternalAPIV2Service::get('/script_models/data_type/' . $dataType);
        if (!empty($res)) {
            $data['script_model'] = json_decode(json_encode($res));
            $step = [];
            foreach ($data['script_model'] as $key => $value) {
                if ($dataType == Script::DATA_TYPE_HTML) {
                    if ($value->id == 13) {
                        $step[] = $value;
                    }
                } else if ($dataType == Script::DATA_TYPE_API) {
                    if ($value->id == 17 || $value->id == 12) {
                        $step[] = $value;
                    }
                }
            }
            $data['new_step'] = $step;
            array_multisort($data['new_step'], SORT_DESC);
        }
        $data['page_title'] = '增加脚本生成信息';
        $data['data_type'] = $dataType;
        $this->cbView('script/script_add_view',$data);
    }

    //编辑界面
    public function getEdit($id) {
        if(!CRUDBooster::isCreate() && $this->global_privilege==FALSE || $this->button_add==FALSE) {
            CRUDBooster::redirect(CRUDBooster::adminPath(),trans("crudbooster.denied_access"));
        }

        $data = [];
        $data['page_title'] = '编辑脚本生成信息';
        try {
            $data['row'] = InternalAPIV2Service::get('/script', ['id' => $id]);
            if (!empty($data['row']['modules'])) {
                $modules = $data['row']['modules'];
                $newScriptModel = [];

                foreach ($modules as $key => $value) {
                    $scriptModelId .= $value[0] . ',';
                    $id = $value[0];
                    array_splice($value, 0, 1);
                    $newScriptModel[$id] = $value;
                }

                $data['row']['modules'] = $newScriptModel;
                $script_models = InternalAPIV2Service::get('/script_models/ids', ['ids' => rtrim($scriptModelId, ",")]);
                foreach ($script_models as $key => $value) {
                    $newScriptModelParams[$value['id']] = json_decode($value['parameters']);
                    $newScriptModelList[$value['id']] = $value;
                }

                $data['row']['script_model_params'] = $newScriptModelParams;
                $data['row']['script_model_list'] = $newScriptModelList;

                $data['script_model'] = [];
                $res = InternalAPIV2Service::get('/script_models/data_type/' . $data['row']['data_type']);
                if (!empty($res)) {
                    $data['script_model'] = json_decode(json_encode($res));
                }
            }
            // //查询项目信息
            // $projects = Project::get();
            // $data['projects'] = $projects->toArray();

            // //查询过滤器信息
            // $filter = Filter::get();
            // $data['filter'] = $filter->toArray();

            // //查询action信息
            // $action = Action::get();
            // $data['action_info'] = $action->toArray();
        } catch (\Dingo\Api\Exception\ResourceException $e) {
            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "该数据信息有误", "error");
        }

        $this->cbView('script/script_edit_view',$data);
    }

    //保存编辑信息
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
        $newData = [];

        $result = preg_match("/^(http|https):\/\//i", $formParams['list_url']);

        if (!$result) {
            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "list_url格式错误", "error");
        }

        if (empty($formParams['script_model_params']) && empty($formParams['content'])) {
            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "步骤必填", "error");
        }

        if (empty($formParams['content'])) {
            foreach ($formParams['script_model_params'] as $key => $value) {
                array_unshift($value, $key);
                $newData[] = $value;
            }
        } else {
            $data['content'] = $formParams['content'];
        }

        $data['modules'] = $newData;
        $data['status'] = Script::STATUS_INIT;
        $data['name'] = $formParams['name'];
        $data['description'] = $formParams['description'];
        $data['list_url'] = $formParams['list_url'];

        if (empty($formParams['content'])) {
            if (empty($formParams['load_images'])) {
                $formParams['load_images'] = ScriptConfig::DEFAULT_LOAD_IMAGES;
            }
            if (empty($formParams['load_plugins'])) {
                $formParams['load_plugins'] = ScriptConfig::DEFAULT_LOAD_PLUGINS;
            }
            if (empty($formParams['verbose'])) {
                $formParams['verbose'] = ScriptConfig::DEFAULT_VERBOSE;
            }
            $data['casper_config'] = [
                'load_images' => $formParams['load_images'],
                'load_plugins' => $formParams['load_plugins'],
                'log_level' => $formParams['log_level'],
                'verbose' => $formParams['verbose'],
                'width' => $formParams['width'],
                'height' => $formParams['height'],
            ];
        }

        $data['cron_type'] = $formParams['cron_type'];
        $data['data_type'] = $formParams['data_type'];
        $data['requirement_pool_id'] = $formParams['requirement_pool_id'];

        if (empty($formParams['is_proxy'])) {
            $data['is_proxy'] = Script::WALL_OVER_FALSE;
        } else {
            $data['is_proxy'] = $formParams['is_proxy'];
        }
        //更新projects字段
        if (!empty($formParams['projects'])) {
            //TODO调用接口
            $script = Script::find($formParams['id']);
            $script->projects = json_encode($formParams['projects']);
            $script->save();
            CRUDBooster::redirect($_SERVER['HTTP_ORIGIN'] . "/admin/t_script/filter-action/". $script->id, "成功");
        }
        if (!empty($formParams['project_config'])) {
            if (!empty($formParams['project_config']['filters'])) {
                //TODO调用接口
                $script = Script::find($formParams['id']);
                $script->filters = json_encode($formParams['project_config']['filters']);
                $script->save();
            }

            if (!empty($formParams['project_config']['actions'])) {
                //TODO调用接口
                $script = Script::find($formParams['id']);
                $script->actions = json_encode($formParams['project_config']['actions']);
                $script->save();
            }
            CRUDBooster::redirect($_SERVER['HTTP_ORIGIN'] . "/admin/t_script", "创建成功", "success");
        }

        $data['created_by'] = CRUDBooster::myName();
        $data['id'] = $id;
        try {
            $res = InternalAPIV2Service::post('/script/update', $data);
        } catch (\Dingo\Api\Exception\ResourceException $e) {
            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "error");
        }

        CRUDBooster::redirect($_SERVER['HTTP_ORIGIN'] . "/admin/t_script/dispatch/" . $id, "修改成功", "success");
    }

    //保存增加信息
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
        //更新projects字段
        if (!empty($formParams['projects'])) {
            //TODO调用接口
            $script = Script::find($formParams['id']);
            $script->projects = json_encode($formParams['projects']);
            $script->save();
            CRUDBooster::redirect($_SERVER['HTTP_ORIGIN'] . "/admin/t_script/filter-action/". $script->id, "成功");
        }
        if (!empty($formParams['id'])) {
            $script = Script::find($formParams['id']);
            $script->filters = "";
            $script->actions = "";
            if (!empty($formParams['project_config'])) {
                if (!empty($formParams['project_config']['filters'])) {
                    //TODO调用接口
                    $script->filters = json_encode($formParams['project_config']['filters']);
                }
                if (!empty($formParams['project_config']['actions'])) {
                    //TODO调用接口
                    $script->actions = json_encode($formParams['project_config']['actions']);
                }
            }
            $script->save();
            CRUDBooster::redirect($_SERVER['HTTP_ORIGIN'] . "/admin/t_script", "创建成功", "success");
        }

        $newData = [];

        $result = preg_match("/^(http|https):\/\//i", $formParams['list_url']);
        if (!$result) {
            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "list_url格式错误", "error");
        }
        if (empty($formParams['script_model_params'])) {
            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "步骤必填", "error");
        }

        foreach ($formParams['script_model_params'] as $key => $value) {
            array_unshift($value, $key);
            $newData[] = $value;
        }
        $data['modules'] = $newData;

        $data['name'] = $formParams['name'];
        $data['description'] = $formParams['description'];
        $data['list_url'] = $formParams['list_url'];

        if (empty($formParams['load_images'])) {
            $formParams['load_images'] = ScriptConfig::DEFAULT_LOAD_IMAGES;
        }
        if (empty($formParams['load_plugins'])) {
            $formParams['load_plugins'] = ScriptConfig::DEFAULT_LOAD_PLUGINS;
        }
        if (empty($formParams['verbose'])) {
            $formParams['verbose'] = ScriptConfig::DEFAULT_VERBOSE;
        }

        $data['casper_config'] = [
                        'load_images' => $formParams['load_images'],
                        'load_plugins' => $formParams['load_plugins'],
                        'log_level' => $formParams['log_level'],
                        'verbose' => $formParams['verbose'],
                        'width' => $formParams['width'],
                        'height' => $formParams['height'],
                         ];
        $data['cron_type'] = $formParams['cron_type'];
        $data['data_type'] = $formParams['data_type'];
        $data['requirement_pool_id'] = $formParams['requirement_pool_id'];

        if (empty($formParams['is_proxy'])) {
            $data['is_proxy'] = Script::WALL_OVER_FALSE;
        } else {
            $data['is_proxy'] = $formParams['is_proxy'];
        }

        $data['created_by'] = CRUDBooster::myName();
        try {
            $res = InternalAPIV2Service::post('/script', $data);
        } catch (\Dingo\Api\Exception\ResourceException $e) {
            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "error");
        }
        CRUDBooster::redirect($_SERVER['HTTP_ORIGIN'] . "/admin/t_script/dispatch/" . $res['id'], "创建成功", "success");
    }

    //详情
    public function getDetail($id) {
        if(!CRUDBooster::isRead() && $this->global_privilege==FALSE || $this->button_edit==FALSE) {
            CRUDBooster::redirect(CRUDBooster::adminPath(),trans("crudbooster.denied_access"));
        }

        $data = [];
        $data['page_title'] = '脚本详情';
        $data['row'] = InternalAPIV2Service::get('/script', ['id' => $id]);
        if (empty($data['row'])) {
            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "数据信息有误", "error");
        }

        if (!empty($data['row']['casper_config'])) {
            if ($data['row']['casper_config']['load_images'] == 1) {
                $data['row']['casper_config']['load_images'] = '是';
            } else {
                $data['row']['casper_config']['load_images'] = '否';
            }

            if ($data['row']['casper_config']['load_plugins'] == 2) {
                $data['row']['casper_config']['load_plugins'] = '是';
            } else {
                $data['row']['casper_config']['load_plugins'] = '否';
            }

            if ($data['row']['casper_config']['verbose'] == 2) {
                $data['row']['casper_config']['verbose'] = '是';
            } else {
                $data['row']['casper_config']['verbose'] = '否';
            }

            if ($data['row']['casper_config']['log_level'] == 1) {
                $data['row']['casper_config']['log_level'] = 'debug';
            } else if($data['row']['casper_config']['log_level'] == 2){
                $data['row']['casper_config']['log_level'] = 'info';
            } else {
                $data['row']['casper_config']['log_level'] = 'error';
            }
            $data['row']['casper_config'] = json_encode($data['row']['casper_config'], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
        }

        if (!empty($data['row']['modules'])) {
            $data['row']['modules'] =  "http://" . $_SERVER['HTTP_HOST'] . "/vendor/script/script_" . $id . '_' . $data['row']['last_generate_at'] . ".js";
        }
        if (!empty($data['row']['content'])) {
            $data['row']['content'] =  "http://" . $_SERVER['HTTP_HOST'] . "/vendor/script/script_" . $id . '_' . $data['row']['last_generate_at'] . ".js";
        }
        if ($data['row']['cron_type'] == Script::CRON_TYPE_KEEP) {
            $data['row']['cron_type'] = '每分钟执行一次';
        } else if($data['row']['cron_type'] == Script::CRON_TYPE_EVERY_FIVE_MINUTES) {
            $data['row']['cron_type'] = '每五分钟执行一次';
        } else if( $data['row']['cron_type'] == Script::CRON_TYPE_EVERY_TEN_MINUTES) {
            $data['row']['cron_type'] = '每十分钟执行一次';
        }

        if ($data['row']['data_type'] == Script::DATA_TYPE_CASPERJS) {
            $data['row']['data_type'] = 'casperJS';
        } else if($data['row']['data_type'] == Script::DATA_TYPE_HTML) {
            $data['row']['data_type'] = 'html';
        } else if( $data['row']['data_type'] == Script::DATA_TYPE_API) {
            $data['row']['data_type'] = 'api';
        }

        if ($data['row']['status'] == Script::STATUS_INIT) {
            $data['row']['status'] = '初始化';
        } else {
            $data['row']['status'] = '已生成';
        }

        if (!empty($data['row']['last_generate_at'])) {
            $data['row']['last_generate_at'] = date('Y-m-d H:i:s', $data['row']['last_generate_at']);
        }

        if (!empty($data['row']['projects'])) {
            foreach ($data['row']['projects'] as $key => $value) {
                if ($value == 1) {
                    $projects[] = '快讯列表';
                } else if ($value == 2) {
                    $projects[] = '快讯详情';
                } else if ($value == 3){
                    $projects[] = '行业新闻';
                } else if ($value == 4) {
                    $projects[] = '公告列表';
                } else if ($value == 5) {
                    $projects[] = '公告详情';
                }
            }
            $data['row']['projects'] = json_encode($projects, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
        }
        if (!empty($data['row']['filters'])) {
            $data['row']['filters'] = json_encode($data['row']['filters'], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
        }
        if (!empty($data['row']['actions'])) {
            $data['row']['actions'] = json_encode($data['row']['actions'], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
        }
        $this->cbView('script/script_detail_view',$data);
    }

    //列表
    public function getIndex() {
        $this->cbLoader();

        $module = CRUDBooster::getCurrentModule();
        if(!CRUDBooster::isView() && $this->global_privilege==FALSE) {
            CRUDBooster::insertLog(trans('crudbooster.log_try_view',['module'=>$module->name]));
            CRUDBooster::redirect(CRUDBooster::adminPath(),trans('crudbooster.denied_access'));
        }

        $data['table']    = $this->table;
        $data['table_pk'] = CB::pk($this->table);
        $data['page_title']       = $module->name;
        $data['page_description'] = trans('crudbooster.default_module_description');
        $data['date_candidate']   = $this->date_candidate;
        $data['limit'] = $limit   = (Request::get('limit'))?Request::get('limit'):$this->limit;

        $tablePK = $data['table_pk'];
        $table_columns = CB::getTableColumns($this->table);
        $result = DB::table($this->table)->select(DB::raw($this->table.".".$this->primary_key));
        $this->hook_query_index($result);

        if(in_array('deleted_at', $table_columns)) {
            $result->where($this->table.'.deleted_at',NULL);
        }

        $alias            = array();
        $join_alias_count = 0;
        $join_table_temp  = array();
        $table            = $this->table;
        $columns_table    = $this->columns_table;
        foreach($columns_table as $index => $coltab) {

            $join = @$coltab['join'];
            $join_where = @$coltab['join_where'];
            $join_id = @$coltab['join_id'];
            $field = @$coltab['name'];
            $join_table_temp[] = $table;

            if(!$field) die('Please make sure there is key `name` in each row of col');

            if(strpos($field, ' as ')!==FALSE) {
                $field = substr($field, strpos($field, ' as ')+4);
                $field_with = (array_key_exists('join', $coltab))?str_replace(",",".",$coltab['join']):$field;
                $result->addselect(DB::raw($coltab['name']));
                $columns_table[$index]['type_data']   = 'varchar';
                $columns_table[$index]['field']       = $field;
                $columns_table[$index]['field_raw']   = $field;
                $columns_table[$index]['field_with']  = $field_with;
                $columns_table[$index]['is_subquery'] = true;
                continue;
            }

            if(strpos($field,'.')!==FALSE) {
                $result->addselect($field);
            }else{
                $result->addselect($table.'.'.$field);
            }

            $field_array = explode('.', $field);

            if(isset($field_array[1])) {
                $field = $field_array[1];
                $table = $field_array[0];
            }else{
                $table = $this->table;
            }

            if($join) {

                $join_exp     = explode(',', $join);

                $join_table  = $join_exp[0];
                $joinTablePK = CB::pk($join_table);
                $join_column = $join_exp[1];
                $join_alias  = str_replace(".", "_", $join_table);

                if(in_array($join_table, $join_table_temp)) {
                    $join_alias_count += 1;
                    $join_alias = $join_table.$join_alias_count;
                }
                $join_table_temp[] = $join_table;

                $result->leftjoin($join_table.' as '.$join_alias,$join_alias.(($join_id)? '.'.$join_id:'.'.$joinTablePK),'=',DB::raw($table.'.'.$field. (($join_where) ? ' AND '.$join_where.' ':'') ) );
                $result->addselect($join_alias.'.'.$join_column.' as '.$join_alias.'_'.$join_column);

                $join_table_columns = CRUDBooster::getTableColumns($join_table);
                if($join_table_columns) {
                    foreach($join_table_columns as $jtc) {
                        $result->addselect($join_alias.'.'.$jtc.' as '.$join_alias.'_'.$jtc);
                    }
                }

                $alias[] = $join_alias;
                $columns_table[$index]['type_data']  = CRUDBooster::getFieldType($join_table,$join_column);
                $columns_table[$index]['field']      = $join_alias.'_'.$join_column;
                $columns_table[$index]['field_with'] = $join_alias.'.'.$join_column;
                $columns_table[$index]['field_raw']  = $join_column;

                @$join_table1  = $join_exp[2];
                @$joinTable1PK = CB::pk($join_table1);
                @$join_column1 = $join_exp[3];
                @$join_alias1  = $join_table1;

                if($join_table1 && $join_column1) {

                    if(in_array($join_table1, $join_table_temp)) {
                        $join_alias_count += 1;
                        $join_alias1 = $join_table1.$join_alias_count;
                    }

                    $join_table_temp[] = $join_table1;

                    $result->leftjoin($join_table1.' as '.$join_alias1,$join_alias1.'.'.$joinTable1PK,'=',$join_alias.'.'.$join_column);
                    $result->addselect($join_alias1.'.'.$join_column1.' as '.$join_column1.'_'.$join_alias1);
                    $alias[] = $join_alias1;
                    $columns_table[$index]['type_data']  = CRUDBooster::getFieldType($join_table1,$join_column1);
                    $columns_table[$index]['field']      = $join_column1.'_'.$join_alias1;
                    $columns_table[$index]['field_with'] = $join_alias1.'.'.$join_column1;
                    $columns_table[$index]['field_raw']  = $join_column1;
                }

            }else{

                $result->addselect($table.'.'.$field);
                $columns_table[$index]['type_data']  = CRUDBooster::getFieldType($table,$field);
                $columns_table[$index]['field']      = $field;
                $columns_table[$index]['field_raw']  = $field;
                $columns_table[$index]['field_with'] = $table.'.'.$field;
            }
        }

        if(Request::get('q')) {
            $result->where(function($w) use ($columns_table, $request) {
                foreach($columns_table as $col) {
                        if(!$col['field_with']) continue;
                        if($col['is_subquery']) continue;
                        $w->orwhere($col['field_with'],"like","%".Request::get("q")."%");
                }
            });
        }

        if(Request::get('where')) {
            foreach(Request::get('where') as $k=>$v) {
                $result->where($table.'.'.$k,$v);
            }
        }

        $filter_is_orderby = false;
        if(Request::get('filter_column')) {

            $filter_column = Request::get('filter_column');
            $result->where(function($w) use ($filter_column,$fc) {
                foreach($filter_column as $key=>$fc) {

                    $value = @$fc['value'];
                    $type  = @$fc['type'];

                    if($type == 'empty') {
                        $w->whereNull($key)->orWhere($key,'');
                        continue;
                    }

                    if($value=='' || $type=='') continue;

                    if($type == 'between') continue;

                    switch($type) {
                        default:
                            if($key && $type && $value) $w->where($key,$type,$value);
                        break;
                        case 'like':
                        case 'not like':
                            $value = '%'.$value.'%';
                            if($key && $type && $value) $w->where($key,$type,$value);
                        break;
                        case 'in':
                        case 'not in':
                            if($value) {
                                $value = explode(',',$value);
                                if($key && $value) $w->whereIn($key,$value);
                            }
                        break;
                    }


                }
            });

            foreach($filter_column as $key=>$fc) {
                $value = @$fc['value'];
                $type  = @$fc['type'];
                $sorting = @$fc['sorting'];

                if($sorting!='') {
                    if($key) {
                        $result->orderby($key,$sorting);
                        $filter_is_orderby = true;
                    }
                }

                if ($type=='between') {
                    if($key && $value) $result->whereBetween($key,$value);
                }else{
                    continue;
                }
            }
        }

    if($filter_is_orderby == true) {
        $data['result']  = $result->paginate($limit);

    }else{
        if($this->orderby) {
            if(is_array($this->orderby)) {
                foreach($this->orderby as $k=>$v) {
                    if(strpos($k, '.')!==FALSE) {
                        $orderby_table = explode(".",$k)[0];
                        $k = explode(".",$k)[1];
                    }else{
                        $orderby_table = $this->table;
                    }
                    $result->orderby($orderby_table.'.'.$k,$v);
                }
            }else{
                $this->orderby = explode(";",$this->orderby);
                foreach($this->orderby as $o) {
                    $o = explode(",",$o);
                    $k = $o[0];
                    $v = $o[1];
                    if(strpos($k, '.')!==FALSE) {
                        $orderby_table = explode(".",$k)[0];
                    }else{
                        $orderby_table = $this->table;
                    }
                    $result->orderby($orderby_table.'.'.$k,$v);
                }
            }
            $data['result'] = $result->paginate($limit);
        }else{
            $data['result'] = $result->orderby($this->table.'.'.$this->primary_key,'desc')->paginate($limit);
        }
    }

    $data['columns'] = $columns_table;

    if($this->index_return) return $data;

    //LISTING INDEX HTML
    $addaction     = $this->data['addaction'];

    if($this->sub_module) {
        foreach($this->sub_module as $s) {
            $table_parent = CRUDBooster::parseSqlTable($this->table)['table'];
            $addaction[] = [
                'label'=>$s['label'],
                'icon'=>$s['button_icon'],
                'url'=>CRUDBooster::adminPath($s['path']).'?parent_table='.$table_parent.'&parent_columns='.$s['parent_columns'].'&parent_columns_alias='.$s['parent_columns_alias'].'&parent_id=['.(!isset($s['custom_parent_id']) ? "id": $s['custom_parent_id']).']&return_url='.urlencode(Request::fullUrl()).'&foreign_key='.$s['foreign_key'].'&label='.urlencode($s['label']),
                'color'=>$s['button_color'],
                                    'showIf'=>$s['showIf']
            ];
        }
    }

    $mainpath      = CRUDBooster::mainpath();
    $orig_mainpath = $this->data['mainpath'];
    $title_field   = $this->title_field;
    $html_contents = array();
    $page = (Request::get('page'))?Request::get('page'):1;
    $number = ($page-1)*$limit+1;
    foreach($data['result'] as $row) {
        $html_content = array();

        if($this->button_bulk_action) {

            $html_content[] = "<input type='checkbox' class='checkbox' name='checkbox[]' value='".$row->{$tablePK}."'/>";
        }

        if($this->show_numbering) {
            $html_content[] = $number.'. ';
            $number++;
        }

        if ($row->status == Script::STATUS_INIT) {
            $row->status_name = '初始化';
        } else {
            $row->status_name = '已生成';
        }
        foreach($columns_table as $col) {
              if($col['visible']===FALSE) continue;

              $value = @$row->{$col['field']};
              $title = @$row->{$this->title_field};
              $label = $col['label'];

              if(isset($col['image'])) {
                    if($value=='') {
                      $value = "<a  data-lightbox='roadtrip' rel='group_{{$table}}' title='$label: $title' href='".asset('vendor/crudbooster/avatar.jpg')."'><img width='40px' height='40px' src='".asset('vendor/crudbooster/avatar.jpg')."'/></a>";
                    }else{
                        $pic = (strpos($value,'http://')!==FALSE)?$value:asset($value);
                        $value = "<a data-lightbox='roadtrip'  rel='group_{{$table}}' title='$label: $title' href='".$pic."'><img width='40px' height='40px' src='".$pic."'/></a>";
                    }
              }

              if(@$col['download']) {
                    $url = (strpos($value,'http://')!==FALSE)?$value:asset($value).'?download=1';
                    if($value) {
                        $value = "<a class='btn btn-xs btn-primary' href='$url' target='_blank' title='Download File'><i class='fa fa-download'></i> Download</a>";
                    }else{
                        $value = " - ";
                    }
              }

                if($col['str_limit']) {
                    $value = trim(strip_tags($value));
                    $value = str_limit($value,$col['str_limit']);
                }

                if($col['nl2br']) {
                    $value = nl2br($value);
                }

                if($col['callback_php']) {
                  foreach($row as $k=>$v) {
                        $col['callback_php'] = str_replace("[".$k."]",$v,$col['callback_php']);
                  }
                  @eval("\$value = ".$col['callback_php'].";");
                }

                //New method for callback
                if(isset($col['callback'])) {
                    $value = call_user_func($col['callback'],$row);
                }


                $datavalue = @unserialize($value);
                if ($datavalue !== false) {
                    if($datavalue) {
                        $prevalue = [];
                        foreach($datavalue as $d) {
                            if($d['label']) {
                                $prevalue[] = $d['label'];
                            }
                        }
                        if(count($prevalue)) {
                            $value = implode(", ",$prevalue);
                        }
                    }
                }

            $html_content[] = $value;
        } //end foreach columns_table
          if($this->button_table_action):

                $button_action_style = $this->button_action_style;
                $html_content[] = "<div class='button_action' style='text-align:right'>".view('crudbooster::components.action',compact('addaction','row','button_action_style','parent_field'))->render()."</div>";

          endif;//button_table_action


          foreach($html_content as $i=>$v) {
            $this->hook_row_index($i,$v);
            $html_content[$i] = $v;
          }

          $html_contents[] = $html_content;
        } //end foreach data[result]


        $html_contents = ['html'=>$html_contents,'data'=>$data['result']];

        $data['html_contents'] = $html_contents;
        $this->cbView("script/script_index", $data);
    }

    //发布
    public function getPublish($id)
    {
        try {
            $res = InternalAPIV2Service::post('/script/publish', ['id' => $id, 'publisher' => CRUDBooster::myName()]);
        } catch (\Dingo\Api\Exception\ResourceException $e) {
            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "error");
        }
        CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "创建成功", "success");
    }
    //发布脚本
    public function getUpload()
    {

        $data['page_title'] = '发布脚本';
        $this->cbView('script/upload_add_view', $data);

    }
    //上传脚本
    public function postUploadSave(HttpRequest $request)
    {

        $params = $request->all();

        if ($request->isMethod('post')) {

            $file = $request->file('files');


            if ($file->isValid()) {
                // 获取文件相关信息
                $originalName = $file->getClientOriginalName(); // 文件原名

                $ext = $file->getClientOriginalExtension();     // 扩展名

                $realPath = $file->getRealPath();   //临时文件的绝对路径

                $type = $file->getClientMimeType();     // image/jpeg

                $size = $file->getClientSize();    //文件大小

                $maxSize = 5*1024*1024;
                //大小判断
                if ($size > $maxSize) {

                    CRUDBooster::redirect($_SERVER['HTTP_REFERER'], '文件大小超出5M', "error");
                }
                //类型判断
                if ($ext == 'php' || $ext == 'js') {

                    // 生成上传的文件名
                    $fileName = date('Y-m-d-H-i-s') . '-' . uniqid() . '.' . $ext;
                    // 上传文件到指定目录
                    $path = $file->move(base_path() . '/uploadtest', $fileName);

                    //生成的文件路径
                    //$uploadName='/uploadtest/'.$fileName;

                    //上传判断
                    if (empty($path)) {

                        CRUDBooster::redirect($_SERVER['HTTP_REFERER'], '文件上传失败', "error");
                    } else {

                        CRUDBooster::redirect($_SERVER['HTTP_REFERER'], '文件上传成功', "success");
                    }


                } else {

                    CRUDBooster::redirect($_SERVER['HTTP_REFERER'], '文件类型不允许', "error");
                }

            }


        } else {

            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "请求失败", "error");
        }



    }
    //脚本发布
    public function postScriptSave()
    {

        $this->cbLoader();
        if(!CRUDBooster::isCreate() && $this->global_privilege==FALSE) {
            CRUDBooster::insertLog(trans('crudbooster.log_try_add_save',['name'=>Request::input($this->title_field),'module'=>CRUDBooster::getCurrentModule()->name ]));
            CRUDBooster::redirect(CRUDBooster::adminPath(),trans("crudbooster.denied_access"));
        }

        $this->validation();
        $this->input_assignment();

        $data = [];
        $formParams = $this->arr;
        $result = preg_match("/^(http|https):\/\//i", $formParams['list_url']);

        if (!$result) {
            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "list_url格式错误", "error");
        }

        $data['name'] = $formParams['name'];
        $data['content'] = $formParams['content'];
        $data['description'] = $formParams['description'];
        $data['list_url'] = $formParams['list_url'];

        $data['cron_type'] = $formParams['cron_type'];
        $data['data_type'] = $formParams['data_type'];
        $data['requirement_pool_id'] = $formParams['requirement_pool_id'];
        if (empty($formParams['is_proxy'])) {
            $data['is_proxy'] = Script::WALL_OVER_FALSE;
        } else {
            $data['is_proxy'] = $formParams['is_proxy'];
        }
        $data['ext'] = $formParams['ext'];
        $data['created_by'] = CRUDBooster::myName();
        $res = [];
        try {
            $res = InternalAPIV2Service::post('/script', $data);
        } catch (\Dingo\Api\Exception\ResourceException $e) {
            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "error");
        }

        if (empty($res)) {
            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "创建script信息失败", "error");
        }

        //命名文件名称
        $filename = '';
        if ($formParams['ext'] == Script::SCRIPT_TYPE_JS) {
            $filename = 'script_' . $res['id'] . '.js';
        } elseif ($formParams['ext'] == Script::SCRIPT_TYPE_PHP) {
            $filename = 'script_' . $res['id'] . '.php';
        }

        $fileService = new FileService();
        $result = $fileService->create($filename, $formParams['content']);

        //检查文件是否生成成功
        if (!$result) {
            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "生成脚本文件失败", "error");
        }
        CRUDBooster::redirect($_SERVER['HTTP_ORIGIN'] . "/admin/t_script/dispatch/" . $res['id'], "创建成功", "success");
    }

    /**
     * dispatchView
     * 分发器页面
     */
    public function getDispatch($id)
    {
        //查询script
        $script = Script::find($id);
        $scriptInfo = $script->toArray();
        $res['projects'] = $scriptInfo['projects'];
        //TODO 查询分发器数据
        $data = Project::get();
        $res['id'] = $id;
        $res['dispatch'] = $data->toArray();
        $this->cbView('script/script_dispatch_view', $res);
    }

    public function getFilterAction($id)
    {
        //查询script信息
        $script = Script::find($id);
        $scriptInfo = $script->toArray();
        $res['script'] = $scriptInfo;
        $projectIds = json_decode($scriptInfo['projects']);

        //查询项目信息
        $projects = new Project;
        $projectsInfo = $projects->whereIn('id',$projectIds)->get();
        $res['projects'] = $projectsInfo->toArray();

        //查询过滤器信息
        $filter = Filter::get();
        $res['filter'] = $filter->toArray();

        //查询action信息
        $action = Action::get();
        $res['action_info'] = $action->toArray();
        $res['id'] = $id;
        $this->cbView('script/script_filter_action_view', $res);
    }
    //By the way, you can still create your own method in here... :)


}