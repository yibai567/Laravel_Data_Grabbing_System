<?php namespace App\Http\Controllers;

use App\Services\ImageService;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Hash;
use Session;
use DB;
use CRUDbooster;
use CB;

class AdminCmsUsersController extends \crocodicstudio\crudbooster\controllers\CBController {


	public function cbInit() {
		# START CONFIGURATION DO NOT REMOVE THIS LINE
		$this->table               = 'cms_users';
		$this->primary_key         = 'id';
		$this->title_field         = "name";
		$this->button_action_style = 'button_icon';	
		$this->button_import 	   = FALSE;	
		$this->button_export 	   = FALSE;	
		# END CONFIGURATION DO NOT REMOVE THIS LINE
	
		# START COLUMNS DO NOT REMOVE THIS LINE
		$this->col = array();
		$this->col[] = array("label"=>"Name","name"=>"name");
		$this->col[] = array("label"=>"Email","name"=>"email");
		$this->col[] = array("label"=>"Privilege","name"=>"id_cms_privileges","join"=>"cms_privileges,name");
		$this->col[] = array("label"=>"Photo","name"=>"photo","image"=>1);		
		# END COLUMNS DO NOT REMOVE THIS LINE

		# START FORM DO NOT REMOVE THIS LINE
		$this->form = array(); 		
		$this->form[] = array("label"=>"Name","name"=>"name",'required'=>true,'validation'=>'required|alpha_spaces|min:3');
		$this->form[] = array("label"=>"Email","name"=>"email",'required'=>true,'type'=>'email','validation'=>'required|email|unique:cms_users,email,'.CRUDBooster::getCurrentId());		
		$this->form[] = array("label"=>"Photo","name"=>"photo","type"=>"upload","help"=>"Recommended resolution is 200x200px",'required'=>true,'validation'=>'required|image|max:1000',"callback"=>function ($row) {
            if(!$row->photo){
                return $row->photo;
            }else{
                $string="<div class='col-sm-10' style='margin-left:208px;margin-bottom:15px'><a data-lightbox='roadtrip' href=' ".$row->photo."'><img style='max-width:160px'  src=".$row->photo."></a><div class='text-danger'></div></div>";
                return $string;
            }
        });
		$this->form[] = array("label"=>"Privilege","name"=>"id_cms_privileges","type"=>"select","datatable"=>"cms_privileges,name",'required'=>true);						
		$this->form[] = array("label"=>"Password","name"=>"password","type"=>"password","help"=>"Please leave empty if not change");
		# END FORM DO NOT REMOVE THIS LINE
				
	}

	public function getProfile() {			

		$this->button_addmore = FALSE;
		$this->button_cancel  = FALSE;
		$this->button_show    = FALSE;			
		$this->button_add     = FALSE;
		$this->button_delete  = FALSE;	
		$this->hide_form 	  = ['id_cms_privileges'];

		$data['page_title'] = trans("crudbooster.label_button_profile");
		$data['row']        = CRUDBooster::first('cms_users',CRUDBooster::myId());
		$this->cbView('crudbooster::default.form',$data);
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
}
