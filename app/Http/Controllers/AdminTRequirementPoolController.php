<?php namespace App\Http\Controllers;

	use App\Models\V2\AlarmResult;
    use App\Models\V2\Company;
    use App\Models\V2\Requirement;
    use App\Services\ImageService;
    use Session;
	use Request;
	use DB;
    use CB;
	use CRUDBooster;
    use Config;
    use App\Services\InternalAPIV2Service;
    use Illuminate\Support\Facades\Route;
    use Log;

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
            $this->col[] = ["label"=>"任务ID","name"=>"id"];
			$this->col[] = ["label"=>"任务名称","name"=>"name"];
            $this->col[] = ["label"=>"任务地址","name"=>"list_url",'width'=>'200',"callback"=>function ($row) {
                return '<a href="' . $row->list_url . '" target="_brank" style="width:200px;overflow: hidden; display: -webkit-box;text-overflow: ellipsis; word-break: break-all;-webkit-box-orient: vertical;-webkit-line-clamp: 1;">'. $row->list_url .'</a>';
            }];
            $this->col[] = ["label"=>"公司名称","name"=>"company_id","callback"=>function ($row){
                $company = DB::table('t_company')->where('id', $row->company_id)->first();
                return $company->cn_name;
            }];
            $this->col[] = ["label"=>"分类","name"=>"category","callback"=>function ($row){
                if ( $row->category == 1) {
                    return '新闻';
                } else if($row->category == 2) {
                    return '历史数据';
                } else if($row->category == 3){
                   return '订阅';
                } else {
                   return '行业快讯';
                }
            }];
			$this->col[] = ["label"=>"订阅类型","name"=>"subscription_type","callback"=>function ($row){
                if ( $row->subscription_type == Requirement::SUBSCRIPTION_TYPE_LIST) {
                    return '列表';
                } else {
                    return '详情';
                }
            }];


            $this->col[] = ["label"=>"语言类型","name"=>"language_type","callback"=>function ($row) {
                if ( $row->language_type == Requirement::LANGUAGE_TYPE_ENGLISH) {
                    return '英文';
                } elseif ($row->language_type == Requirement::LANGUAGE_TYPE_CHINESE){
                    return '中文';
                }
            }];

			$this->col[] = ["label"=>"截图","name"=>"is_capture","callback"=>function ($row) {
                if ( $row->is_capture == Requirement::IS_CAPTURE_TRUE) {
                    return '需要';
                } else {
                    return '不需要';
                }
            }];

			$this->col[] = ["label"=>"图片资源","name"=>"is_download_img","callback"=>function ($row) {
                if ( $row->is_download_img == Requirement::IS_DOWNLOAD_TRUE) {
                    return '需要';
                } else {
                    return '不需要';
                }
            }];

            $this->col[] = ["label"=>"状态","name"=>"status","callback"=>function ($row) {
                if ($row->status == Requirement::STATUS_TRUE) {
                    return "<a class='btn btn-xs btn-warning'><i></i>未处理</a>";
                } else {
                    return "<a class='btn btn-xs btn-success'><i></i>已处理</a>";
                }
            }];
            $this->col[] = ["label"=>"全部数据","name"=>"status_identity","callback"=>function ($row) {
                if ($row->status_identity == 1) {
                    return "<a class='btn btn-xs btn-warning'><i></i>未获取</a>";
                } else {
                    return "<a class='btn btn-xs btn-success'><i></i>已获取</a>";
                }
            }];
            $this->col[] = ["label"=>"操作人","name"=>"operate_by","callback"=>function ($row) {
                 return $this->__getUser($row->operate_by);
            }];

            $this->col[] = ["label"=>"创建时间","name"=>"created_at"];
            $this->col[] = ["label"=>"状态理由","name"=>"status_reason"];

			# END COLUMNS DO NOT REMOVE THIS LINE

			# START FORM DO NOT REMOVE THIS LINE
			$this->form = [];
			$this->form[] = ['label'=>'任务名称','name'=>'name','type'=>'text','validation'=>'nullable|string|max:100','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'任务地址','name'=>'list_url','type'=>'text','validation'=>'required|string|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'描述','name'=>'description','type'=>'textarea','validation'=>'nullable|max:20000','width'=>'col-sm-10'];
            $this->form[] = ['label'=>'公司列表','name'=>'company_id','type'=>'select2','datatable'=>'t_company,cn_name'];
            $this->form[] = ['label'=>'公司中文名','name'=>'cn_name','type'=>'text','validation'=>'nullable|string|max:255','width'=>'col-sm-10','placeholder'=>'公司列表中未找到时可填写'];
            $this->form[] = ['label'=>'公司英文名','name'=>'en_name','type'=>'text','validation'=>'nullable|string|max:255','width'=>'col-sm-10','placeholder'=>'公司列表中未找到时可填写'];

            $this->form[] = ['label'=>'图片描述','name'=>'img_description','type'=>'upload','width'=>'col-sm-10',"callback"=>function ($row) {

                    $string="<div class='col-sm-10' style='margin-left:208px;margin-bottom:15px'><a data-lightbox='roadtrip' href=' ".$row->img_description."'><img style='max-width:160px'  src=".$row->img_description."></a><div class='text-danger'></div></div>";
                    echo $string;
            }];
            $this->form[] = ['label'=>'分类','name'=>'category','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|新闻;2|历史数据;3|订阅;4|行业快讯','value'=>3];
			$this->form[] = ['label'=>'订阅类型','name'=>'subscription_type','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|列表;2|详情','value'=>2];
			$this->form[] = ['label'=>'截图','name'=>'is_capture','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|需要;2|不需要','value'=>2];
			$this->form[] = ['label'=>'图片资源','name'=>'is_download_img','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|需要;2|不需要','value'=>2];
            $this->form[] = ['label'=>'需求类型','name'=>'requirement_type','type'=>'radio','validation'=>'nullable|integer','width'=>'col-sm-10','dataenum'=>'1|快讯;2|公告','value'=>'1'];
            $this->form[] = ['label'=>'语言类型','name'=>'language_type','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'1|英文;2|中文','value'=>'1'];
            $this->form[] = ['label'=>'执行规则','name'=>'cron_type','type'=>'radio','validation'=>'nullable|integer','width'=>'col-sm-10','dataenum'=>'1|每分钟执行一次;2|每五分钟执行一次;3|每十分钟执行一次;4|只执行一次','value'=>'1'];
			$this->form[] = ['label'=>'创建人','name'=>'create_by','type'=>'select','validation'=>'required','width'=>'col-sm-9','dataenum'=>'4|guoyuemin@jinse.com;1|liqi@jinse.com;2|huangxingxing@jinse.com;3|wangbo@jinse.com',];
			$this->form[] = ['label' => 'ID','name'=>'id','type'=>'hidden'];
            $this->form[] = ['label' => '状态理由','name'=>'status_reason','type'=>'hidden'];
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
            $this->addaction[] = ['label'=>'done', 'url'=>CRUDBooster::mainpath('modify-state/[id]/' . Requirement::STATUS_FALSE),'color'=>'warning', 'icon'=>'ion-arrow-right-c', 'showIf'=>'[status] == ' . Requirement::STATUS_TRUE];
            $this->addaction[] = ['label'=>'restart', 'url'=>CRUDBooster::mainpath('modify-state/[id]/' . Requirement::STATUS_TRUE),'color'=>'info', 'icon'=>'ion-arrow-right-c', 'showIf'=>'[status] != ' . Requirement::STATUS_TRUE];

            $this->addaction[] = ['label'=>'获取全部数据', 'url'=>CRUDBooster::mainpath('save-status-identity/[id]/2'),'color'=>'info', 'icon'=>'ion-arrow-right-c', 'showIf'=>'[status_identity] == 1'];

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
            $this->index_statistic[] = ['label'=>'新闻','count'=>Requirement::where('category', 1)->count(),'color'=>'btn btn-xs btn-success'];
            $this->index_statistic[] = ['label'=>'历史数据','count'=>Requirement::where('category', 2)->count(),'color'=>'btn btn-xs btn-warning'];
            $this->index_statistic[] = ['label'=>'订阅','count'=>Requirement::where('category', 3)->count(),'color'=>'btn btn-xs btn-info'];
            $this->index_statistic[] = ['label'=>'行业快讯','count'=>Requirement::where('category', 4)->count(),'color'=>'btn btn-xs btn-info'];
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
	    public function hook_before_edit(&$postdata,$id) {

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
                    $columns_table[$index]['type_data']	 = CRUDBooster::getFieldType($join_table,$join_column);
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
                        $columns_table[$index]['type_data']	 = CRUDBooster::getFieldType($join_table1,$join_column1);
                        $columns_table[$index]['field']      = $join_column1.'_'.$join_alias1;
                        $columns_table[$index]['field_with'] = $join_alias1.'.'.$join_column1;
                        $columns_table[$index]['field_raw']  = $join_column1;
                    }

                }else{

                    $result->addselect($table.'.'.$field);
                    $columns_table[$index]['type_data']	 = CRUDBooster::getFieldType($table,$field);
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
                        $w->orwhere($col['field_with'],"like binary","%".Request::get("q")."%");
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
            foreach($data['result'] as $index => $row) {
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
                $company = DB::table('t_company')->where('id', $row->company_id)->first();

                $data['result'][$index]->company_id = $company->cn_name;
                $data['result'][$index]->operate_by = $this->__getUser($row->operate_by);
                $html_contents[] = $html_content;
            } //end foreach data[result]


            $html_contents = ['html'=>$html_contents,'data'=>$data['result']];

            $data['html_contents'] = $html_contents;

            return view("requirement/requirement_index",$data);
        }

        public function getModifyState($id,$status) {

            $params['id'] = $id;
            $params['status'] = $status;
            $params['user_id'] = CRUDBooster::myId();

            try {
                $result = InternalAPIV2Service::post('/quirement/update_status', $params);
            } catch (\Dingo\Api\Exception\ResourceException $e) {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "error");
            }
            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "状态修改成功", "info");
        }

        public function getSaveStatusIdentity($id,$statusIdentity) {
            try {
                $requirement = Requirement::find($id);
                $requirement->status_identity = $statusIdentity;
                $requirement->operate_by = CRUDBooster::myId();
                $requirement->save();
            } catch (\Dingo\Api\Exception\ResourceException $e) {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "error");
            }
           CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "状态修改成功", "info");
        }

        private function __getUser($id) {
            $res=DB::table('cms_users')->select('name')->where('id', $id)->first();

            return $res->name;
        }


        private function __create($params)
        {
            $newData = [
                "name"              => $params['name'],
                "list_url"          => $params['list_url'],
                "company_id"        => $params['company_id'],
                "img_description"   => $params['img_description'],
                "category"          => $params['category'],
                "subscription_type" => $params['subscription_type'],
                "language_type"     => $params['language_type'],
                "is_capture"        => $params['is_capture'],
                "is_download_img"   => $params['is_download_img'],
                "create_by"         => $params['create_by'],
                "created_at"        => $params['created_at'],
                "operate_by"        => CRUDBooster::myId(),
            ];
            try {
                if (empty($params['company_id'])) {
                    $params['company_id'] = $this->__getCompanyId($params);
                }

                $params['operate_by'] = CRUDBooster::myId();
                $result = InternalAPIV2Service::post('/quirement', $params);

                if ($result) {
                    $data = [
                        'type' => AlarmResult::TYPE_WEWORK,
                        'content' => '有新的需求，请及时处理！',
                        'wework' => config('alarm.alarm_recipient')
                    ];

                    $alarmResult = InternalAPIV2Service::post('/alarm_result', $data);
                    if (!$alarmResult) {
                        Log::debug('[Requirement Create] create alarm_result is failed');
                    }
                }

            } catch (\Dingo\Api\Exception\ResourceException $e) {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "error");
            }
            CRUDBooster::redirect($_SERVER['HTTP_ORIGIN'] . "/admin/t_requirement_pool", "创建成功", "success");
        }

        private function __update($params, $id)
        {

            $params['id'] = (int)$id;
            try {
                InternalAPIV2Service::post('/quirement/update', $params);

            } catch (\Dingo\Api\Exception\ResourceException $e) {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "error");
            }


            CRUDBooster::redirect($_SERVER['HTTP_ORIGIN'] . "/admin/t_requirement_pool", "修改成功", "success");
        }
        public function getDetail($id)
        {
            $this->cbLoader();
            $row = Requirement::where('id', $id)->first();

            if ($row->subscription_type == Requirement::SUBSCRIPTION_TYPE_LIST) {
                $row->subscription_type = '列表';
            } else {
                $row->subscription_type = '详情';
            }
            if ($row->is_capture == Requirement::IS_CAPTURE_TRUE) {
                $row->is_capture = '是';
            } else {
                $row->is_capture = '否';
            }

            if ($row->is_download_img == Requirement::IS_DOWNLOAD_TRUE) {
                $row->is_download_img = '是';
            } else {
                $row->is_download_img = '否';
            }

            if ($row->language_type == Requirement::LANGUAGE_TYPE_ENGLISH) {
                $row->language_type = '英文';
            } elseif ($row->language_type == Requirement::LANGUAGE_TYPE_CHINESE) {
                $row->language_type = '中文';
            }

            $createBy = config('user');

            $row->create_by=$createBy[$row->create_by];

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

        private function __getCompanyId($params)
        {
            $validator = validator($params, [
                'cn_name' => 'required|string|max:255',
                'en_name' => 'required|string|max:255'
            ]);

            if ($validator->fails()) {
                $message = $validator->getMessageBag()->all();
                return CRUDBooster::redirect($_SERVER['HTTP_REFERER'], trans('crudbooster.alert_validation_error',['error'=>implode(', ',$message)]));
            }

            $enName = preg_replace('/ +/', '_', $params['en_name']);

            $id = DB::table('t_company')->where('en_name', $enName)->value('id');
            //没有找到数据就创建新数据
            if (empty($id)) {
                $data = [
                    'cn_name'=>$params['cn_name'],
                    'en_name'=>$enName,
                    'type' => 1,
                    'url' => $params['list_url']
                ];
                $company = new Company();
                $company->setRawAttributes($data);
                $company->save();
                $id = $company->id;
            }
            return $id;
        }

        public function input_assignment($id=null) {
            $hide_form = (Request::get('hide_form'))?unserialize(Request::get('hide_form')):array();

            foreach($this->data_inputan as $ro) {
                $name = $ro['name'];

                if(!$name) continue;

                if($ro['exception']) continue;

                if($name=='hide_form') continue;

                if(count($hide_form)) {
                    if(in_array($name, $hide_form)) {
                        continue;
                    }
                }

                if($ro['type']=='checkbox' && $ro['relationship_table']) {
                    continue;
                }

                if($ro['type']=='select2' && $ro['relationship_table']) {
                    continue;
                }

                $inputdata = Request::get($name);

                if($ro['type']=='money') {
                    $inputdata = preg_replace('/[^\d-]+/', '', $inputdata);
                }

                if($ro['type']=='child') continue;

                if($name) {
                    if($inputdata!='') {
                        $this->arr[$name] = $inputdata;
                    }else{
                        if(CB::isColumnNULL($this->table,$name) && $ro['type']!='upload') {
                            continue;
                        }else{
                            $this->arr[$name] = "";
                        }
                    }
                }

                $password_candidate = explode(',',config('crudbooster.PASSWORD_FIELDS_CANDIDATE'));
                if(in_array($name, $password_candidate)) {
                    if(!empty($this->arr[$name])) {
                        $this->arr[$name] = Hash::make($this->arr[$name]);
                    }else{
                        unset($this->arr[$name]);
                    }
                }

                if($ro['type']=='checkbox') {

                    if(is_array($inputdata)) {
                        if($ro['datatable'] != '') {
                            $table_checkbox = explode(',',$ro['datatable'])[0];
                            $field_checkbox = explode(',',$ro['datatable'])[1];
                            $table_checkbox_pk = CB::pk($table_checkbox);
                            $data_checkbox = DB::table($table_checkbox)->whereIn($table_checkbox_pk,$inputdata)->pluck($field_checkbox)->toArray();
                            $this->arr[$name] = implode(";",$data_checkbox);
                        }else{
                            $this->arr[$name] = implode(";",$inputdata);
                        }
                    }
                }

                //multitext colomn
                if($ro['type']=='multitext') {
                    $name = $ro['name'];
                    $multitext="";

                    for($i=0;$i<=count($this->arr[$name])-1;$i++) {
                        $multitext .= $this->arr[$name][$i]."|";
                    }
                    $multitext=substr($multitext,0,strlen($multitext)-1);
                    $this->arr[$name]=$multitext;
                }

                if($ro['type']=='googlemaps') {
                    if($ro['latitude'] && $ro['longitude']) {
                        $latitude_name = $ro['latitude'];
                        $longitude_name = $ro['longitude'];
                        $this->arr[$latitude_name] = Request::get('input-latitude-'.$name);
                        $this->arr[$longitude_name] = Request::get('input-longitude-'.$name);
                    }
                }

                if($ro['type']=='select' || $ro['type']=='select2') {
                    if($ro['datatable']) {
                        if($inputdata=='') {
                            $this->arr[$name] = 0;
                        }
                    }
                }


                if(@$ro['type']=='upload') {
                    if (Request::hasFile($name))
                    {
                        $file = Request::file($name);
                        $imageService = new ImageService();
                        $imgResult = $imageService->uploadByFile($file);
                        if (empty($imgResult['id'])) {
                            if (!empty($imgResult['msg'])) {
                                return response(500, $imgResult['msg']);
                            }
                            return response(500, '上传图片失败');
                        }

                        $this->arr[$name] = $imgResult['oss_url'];
                    }

                    if(!$this->arr[$name]) {
                        $this->arr[$name] = Request::get('_'.$name);
                    }
                }

                if(@$ro['type']=='filemanager') {
                    $filename = str_replace('/'.config('lfm.prefix').'/'.config('lfm.files_folder_name').'/','',$this->arr[$name]);
                    $url = 'uploads/'.$filename;
                    $this->arr[$name] = $url;
                }
            }
        }

        public function postStashSave()
        {

            $this->cbLoader();
            if(!CRUDBooster::isCreate() && $this->global_privilege==FALSE) {
                CRUDBooster::insertLog(trans('crudbooster.log_try_add_save',['name'=>Request::input($this->title_field),'module'=>CRUDBooster::getCurrentModule()->name ]));
                CRUDBooster::redirect(CRUDBooster::adminPath(),trans("crudbooster.denied_access"));
            }

            $this->validation();
            $this->input_assignment();

            $formParams = $this->arr;
            $formParams['status'] = Requirement::STATUS_STASH;
            $formParams['user_id'] = CRUDBooster::myId();

            try {
                $result = InternalAPIV2Service::post('/quirement/update_status', $formParams);
            } catch (\Dingo\Api\Exception\ResourceException $e) {
                CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "系统错误，请重试", "error");
            }
            CRUDBooster::redirect($_SERVER['HTTP_REFERER'], "状态修改成功", "info");
        }
	}