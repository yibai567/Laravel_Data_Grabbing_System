<?php namespace App\Http\Controllers;

use Session;
use Request;
use DB;
use CRUDBooster;
use CB;
use App\Services\InternalAPIV2Service;
use App\Models\Task;

class AdminTTaskDetailListController extends \crocodicstudio\crudbooster\controllers\CBController {

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
        $this->button_detail = false;
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
        $this->col[] = ["label"=>"测试地址","name"=>"test_url"];
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
        $this->form[] = ['label'=>'Cron Type','name'=>'cron_type','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
        $this->form[] = ['label'=>'data Type','name'=>'data_type','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
        $this->form[] = ['label'=>'Status','name'=>'status','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
        $this->form[] = ['label'=>'Test Url','name'=>'test_url','type'=>'text','validation'=>'required','width'=>'col-sm-10'];
        $this->form[] = ['label'=>'Task Id','name'=>'task_id','type'=>'text','validation'=>'required|min:1','width'=>'col-sm-10'];
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

        $this->addaction[] = ['label'=>'测试', 'url'=>'', 'icon'=>'fa fa-play'];
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
        $query->where('cron_type', Task::CRON_TYPE_KEEP_ONCE);
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

    //By the way, you can still create your own method in here... :)

    public function getIndex() {
        $this->cbLoader();

        $module = CRUDBooster::getCurrentModule();
        if(!CRUDBooster::isView() && $this->global_privilege==FALSE) {
            CRUDBooster::insertLog(trans('crudbooster.log_try_view',['module'=>$module->name]));
            CRUDBooster::redirect(CRUDBooster::adminPath(),trans('crudbooster.denied_access'));
        }

        if(Request::get('parent_table')) {
            $parentTablePK = CB::pk(g('parent_table'));
            $data['parent_table'] = DB::table(Request::get('parent_table'))->where($parentTablePK,Request::get('parent_id'))->first();
            if(Request::get('foreign_key')) {
                $data['parent_field'] = Request::get('foreign_key');
            }else{
                $data['parent_field'] = CB::getTableForeignKey(g('parent_table'),$this->table);
            }

            if($parent_field) {
                foreach($this->columns_table as $i=>$col) {
                    if($col['name'] == $parent_field) {
                        unset($this->columns_table[$i]);
                    }
                }
            }
        }

        $data['table'] 	  = $this->table;
        $data['table_pk'] = CB::pk($this->table);
        $data['page_title']       = $module->name;
        $data['page_description'] = trans('crudbooster.default_module_description');
        $data['date_candidate']   = $this->date_candidate;
        $data['limit'] = $limit   = (Request::get('limit'))?Request::get('limit'):$this->limit;

        $tablePK = $data['table_pk'];
        $table_columns = CB::getTableColumns($this->table);
        $result = DB::table($this->table)->select(DB::raw($this->table.".".$this->primary_key));

        if(Request::get('parent_id')) {
            $table_parent = $this->table;
            $table_parent = CRUDBooster::parseSqlTable($table_parent)['table'];
            $result->where($table_parent.'.'.Request::get('foreign_key'),Request::get('parent_id'));
        }


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

        $this->cbView("task/task_index", $data);
    }

    public function postTestSave() {
        $this->cbLoader();
        if(!CRUDBooster::isCreate() && $this->global_privilege==FALSE) {
            CRUDBooster::insertLog(trans('crudbooster.log_try_add_save',['name'=>Request::input($this->title_field),'module'=>CRUDBooster::getCurrentModule()->name ]));
            CRUDBooster::redirect(CRUDBooster::adminPath(),trans("crudbooster.denied_access"));
        }

        $this->validation();
        $this->input_assignment();

        $formParams = $this->arr;

        if (!isset($formParams['test_url'])) {
            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "测试地址必填", "error");
        }

        $result = preg_match("/^(http|https):\/\//i", $formParams['test_url']);
        if (!$result) {
            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "测试地址格式错误", "error");
        }

        try {
            InternalAPIV2Service::post('/task/test', ['task_id' => $formParams['task_id'], 'test_url' => $formParams['test_url']]);
        } catch (\Dingo\Api\Exception\ResourceException $e) {
            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "error");
        }
        CRUDBooster::redirect($_SERVER['HTTP_ORIGIN'] . "/admin/t_task_detail_list", "测试已提交", "success");
    }
}