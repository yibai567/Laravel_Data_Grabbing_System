<?php
    namespace App\Http\Controllers;

    use App\Models\Item;
    use App\Models\ItemRunLog;
    use App\Services\InternalAPIService;
    use Session;
    use Request;
    use DB;
    use CRUDBooster;
    use Illuminate\Support\Facades\Route;


    class AdminTSystemItemController extends \crocodicstudio\crudbooster\controllers\CBController {

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
            $this->button_delete = false;
            $this->button_detail = false;
            $this->button_show = true;
            $this->button_filter = true;
            $this->button_import = false;
            $this->button_export = false;
            $this->table = "t_item";
            # END CONFIGURATION DO NOT REMOVE THIS LINE

            # START COLUMNS DO NOT REMOVE THIS LINE
            $this->col = [];
            $this->col[] = ["label"=>"ID","name"=>"id",'width'=>'300'];
            $this->col[] = ["label"=>"任务名称","name"=>"name","width"=>'500'];
            $this->col[] = ["label"=>"资源地址","name"=>"resource_url",'width'=>'300',"callback"=>function ($row) {
                return '<a href="' . $row->resource_url . '" target="_brank" style="width:200px;overflow: hidden; display: -webkit-box;text-overflow: ellipsis; word-break: break-all;-webkit-box-orient: vertical;-webkit-line-clamp: 1;">'. $row->resource_url .'</a>';
            }];
            $this->col[] = ["label"=>"关联结果ID","name"=>"associate_result_id","width"=>'100'];

            # END COLUMNS DO NOT REMOVE THIS LINE

            # START FORM DO NOT REMOVE THIS LINE
            $this->form = [];
            $this->form[] = ['label'=>'任务名称','name'=>'name','type'=>'text','width'=>'col-sm-10'];
            $this->form[] = ['label'=>'数据类型','name'=>'data_type','type'=>'radio','validation'=>'required|integer|between:1,2','width'=>'col-sm-10','dataenum'=>'1|html;2|json','value'=>'1'];
            $this->form[] = ['label'=>'内容类型','name'=>'content_type','type'=>'radio','validation'=>'required|integer|between:1,2','width'=>'col-sm-10','dataenum'=>'1|短内容;2|长内容','value'=>'1'];
            $this->form[] = ['label'=>'Type','name'=>'type','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|快讯','value'=>'1'];
            $this->form[] = ['label'=>'Cron Type','name'=>'cron_type','type'=>'radio','validation'=>'required|integer|in:1,2,3,4','width'=>'col-sm-10','dataenum'=>'1|持续执行;2|每分钟;3|每小时;4|每天;5|执行一次','value'=>'1'];
            $this->form[] = ['label'=>'是否翻墙','name'=>'is_proxy','type'=>'radio','validation'=>'required|integer|between:1,2','width'=>'col-sm-10','dataenum'=>'1|是;2|否','value'=>'2'];
            $this->form[] = ['label'=>'是否截图','name'=>'is_capture_image','type'=>'radio','validation'=>'required|integer|between:1,2','width'=>'col-sm-10','dataenum'=>'1|截图;2|不截图','value'=>'2'];
            $this->form[] = ['label'=>'Status','name'=>'status','type'=>'hidden','width'=>'col-sm-10'];

            $this->form[] = ['label'=>'资源URL','name'=>'resource_url','type'=>'text','validation'=>'required|string','width'=>'col-sm-10'];
            $this->form[] = ['label'=>'URL前缀','name'=>'pre_detail_url','type'=>'text','validation'=>'required|string','width'=>'col-sm-10'];

            $this->form[] = ['label'=>'行内选择器','name'=>'row_selector','type'=>'text','width'=>'col-sm-10'];
            $this->form[] = ['label'=>'短内容选择器','name'=>'short_content_selector','type'=>'textarea','width'=>'col-sm-10'];
            $this->form[] = ['label'=>'长内容选择器','name'=>'long_content_selector','type'=>'textarea','width'=>'col-sm-10'];
            $this->form[] = ['label'=>'header头','name'=>'header','type'=>'textarea','width'=>'col-sm-10'];
            # END FORM DO NOT REMOVE THIS LINE

            /*
            | ----------------------------------------------------------------------
            | Sub Module
            | ----------------------------------------------------------------------
            | @label          = Label of action
            | @path           = Path of sub module
            | @foreign_key    = foreign key of sub table/module
            | @button_color   = Bootstrap Class (primary,success,warning,danger)
            | @button_icon    = Font Awesome Class
            | @parent_columns = Sparate with comma, e.g : name,created_at
            |
            */
            $this->sub_module = array();
            // $this->sub_module[] = ['label'=>'任务结果', 'path'=>'t_item_result/detail/[associate_result_id]', 'button_color'=>'success','button_icon'=>'fa fa-bars', 'showIf'=>"[resource_type] != 0"];


            /*
            | ----------------------------------------------------------------------
            | Add More Action Button / Menu
            | ----------------------------------------------------------------------
            | @label       = Label of action
            | @url         = Target URL, you can use field alias. e.g : [id], [name], [title], etc
            | @icon        = Font awesome class icon. e.g : fa fa-bars
            | @color       = Default is primary. (primary, warning, succecss, info)
            | @showIf      = If condition when action show. Use field alias. e.g : [id] == 1
            |
            */
            $this->addaction = array();
            $this->addaction[] = ['label'=>'任务结果', 'url'=>CRUDBooster::adminPath('t_item_result/detail/[associate_result_id]'), 'showIf'=>"[resource_type] != 0"];
            // $this->addaction[] = ['label'=>'测试', 'url'=>CRUDBooster::mainpath('test/[id]'),'color'=>'info', 'icon'=>'fa fa-play', 'showIf'=>'[status] == ' . Item::STATUS_TEST_SUCCESS . '|| [status] == ' . Item::STATUS_STOP . '|| [status] == ' . Item::STATUS_TEST_FAIL . '|| [status] == ' . Item::STATUS_START . '|| [status] == ' . Item::STATUS_INIT];
            // $this->addaction[] = ['label'=>'启动', 'url'=>CRUDBooster::mainpath('start-up/[id]'),'color'=>'success', 'icon'=>'fa fa-play', 'showIf'=>'[status] == ' . Item::STATUS_TEST_SUCCESS . '|| [status] == ' . Item::STATUS_STOP];

            // $this->addaction[] = ['label'=>'停止', 'url'=>CRUDBooster::mainpath('stop-down/[id]'),'color'=>'warning', 'icon'=>'fa fa-stop', 'showIf'=>'[status] == ' . Item::STATUS_START];

            // $this->addaction[] = ['label'=>'测试结果', 'url'=>CRUDBooster::mainpath('test-result/[id]'),'color'=>'warning', 'icon'=>'ion-funnel'];



            /*
            | ----------------------------------------------------------------------
            | Add More Button Selected
            | ----------------------------------------------------------------------
            | @label       = Label of action
            | @icon        = Icon from fontawesome
            | @name        = Name of button
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
        public function actionButtonSelected($id_selected, $button_name) {
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
            $query->where('type', Item::TYPE_SYS);
            //Your code here

        }

        /*
        | ----------------------------------------------------------------------
        | Hook for manipulate row of index table html
        | ----------------------------------------------------------------------
        |
        */
        public function hook_row_index($column_index, &$column_value) {
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
        public function hook_before_edit(&$postdata, $id) {
            //Your code here
            //调用修改接口修改任务
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



        //By the way, you can still create your own method in here... :)

        private function __create($params)
        {
            try {
                $result = InternalAPIService::post('/item', $params);
                InternalAPIService::post('/item/test', ['id' => $result['id']]);
            } catch (\Dingo\Api\Exception\ResourceException $e) {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "error");
            }
            CRUDBooster::redirect($_SERVER['HTTP_ORIGIN'] . "/admin/t_item", "创建成功", "success");
        }

        private function __update($params, $id)
        {
            $params['id'] = $id;
            try {
                $result = InternalAPIService::post('/item/update', $params);
            } catch (\Dingo\Api\Exception\ResourceException $e) {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "error");
            }

            InternalAPIService::post('/item/test', ['id' => $result['id']]);
            CRUDBooster::redirect($_SERVER['HTTP_ORIGIN'] . "/admin/t_item", "修改成功", "success");
        }


        public function getTest($id)
        {
            $result = InternalAPIService::post('/item/test', ['id' => intval($id)]);

            if (empty($result)) {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "error");
            }
            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "测试提交成功，请稍后查看测试结果", "success");
        }

        public function getTestResult($id)
        {
            $itemRunLog = InternalAPIService::get('/item_run_log/item', ['item_id' => intval($id), 'type' => ItemRunLog::TYPE_TEST]);

            if (empty($itemRunLog)) {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "error");
            }

            $itemTestResult = InternalAPIService::get('/item/test_result', ['item_run_log_id' => $itemRunLog['id']]);

            if (!empty($itemTestResult['short_contents'])) {
                //$short_contents = jsonFormat($itemTestResult['short_contents']);
                //dd($itemTestResult['short_contents']);
                echo "<script type=\"text/javascript\" >alert(JSON.stringify(" . $itemTestResult['short_contents'] . "));</script>";
            } else {
                echo "<script type=\"text/javascript\" >alert( '暂无结果' );</script>";
            }
            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "", "success");
        }

        public function getStartUp($id)
        {
            $result = InternalAPIService::post('/item/start', ['id' => intval($id)]);
            if (empty($result)) {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "error");
            }
            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "启动成功", "success");
        }

        public function getStopDown($id)
        {

            $result = InternalAPIService::post('/item/stop', ['id' => intval($id)]);

            if (empty($result)) {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "error");
            }

            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "停止成功", "success");
        }

        public function getDetail($id) {
            $this->cbLoader();
            $row = Item::where('id', $id)->first();

            if ($row->data_type == Item::DATA_TYPE_HTML) {
                $row->data_type = 'html';
            } else if ($row->data_type == Item::DATA_TYPE_JSON) {
                $row->data_type = 'json';
            } else {
                $row->data_type = '截图';
            }

            if ($row->content_type == Item::CONTENT_TYPE_SHORT) {
                $row->content_type = '短内容';
            } else {
                $row->content_type = '长内容';
            }

            if ($row->type == Item::TYPE_OUT) {
                $row->type = '外部任务';
            } else {
                $row->type = '系统任务';
            }

            if ( $row->cron_type == Item::CRON_TYPE_KEEP) {
                $row->cron_type = '持续执行';
            } else if( $row->cron_type == Item::CRON_TYPE_ONLY_ONE) {
                $row->cron_type = '执行一次';
            } else if( $row->cron_type == Item::CRON_TYPE_EVERY_MINUTE) {
                $row->cron_type = '每分钟执行';
            } else if ($row->cron_type == Item::CRON_TYPE_EVERY_FIVE_MINIT) {
                $row->cron_type = '每五分钟执行';
            } else if ($row->cron_type == Item::CRON_TYPE_EVERY_FIFTHEEN_MINIT) {
                $row->cron_type = '每十五分钟执行';
            }

            if ($row->is_proxy == Item::IS_PROXY_YES) {
                $row->is_proxy = '翻墙';
            } else {
                $row->is_proxy = '不翻墙';
            }

            if (!empty($row->short_content_selector)) {
                $row->short_content_selector = json_encode(json_decode($row->short_content_selector), JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
            }
            if (!empty($row->long_content_selector)) {
                $row->long_content_selector = json_encode(json_decode($row->long_content_selector), JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
            }
            if (!empty($row->header)) {
                $row->header = json_encode(json_decode($row->header), JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
            }
            //dd($row);

            if(!CRUDBooster::isRead() && $this->global_privilege==FALSE || $this->button_detail==FALSE) {
                    CRUDBooster::insertLog(trans("crudbooster.log_try_view",['name'=>$row->{$this->title_field},'module'=>CRUDBooster::getCurrentModule()->name]));
                    CRUDBooster::redirect(CRUDBooster::adminPath(), trans('crudbooster.denied_access'));
                }
                $page_menu  = Route::getCurrentRoute()->getActionName();
                $page_title = trans("crudbooster.detail_data_page_title",['module'=>$module->name, 'name'=>$row->{$this->title_field}]);
                $command    = 'detail';
                Session::put('current_row_id', $id);
                return view('crudbooster::default.form',compact('row', 'page_menu', 'page_title', 'command', 'id'));
        }
    }