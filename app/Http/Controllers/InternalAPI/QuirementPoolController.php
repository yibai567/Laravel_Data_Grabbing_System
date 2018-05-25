<?php

namespace App\Http\Controllers\InternalAPI;

use Illuminate\Http\Request;
use Log;
use App\Services\ValidatorService;
use App\Services\InternalAPIService;
use App\Models\Requirement;
use CRUDBooster;

/**
 * QuirementPoolController
 * 资源管理接口
 *
 * @author liqi1@jinse.com
 * @version 1.1
 * Date: 2018/05/23
 */
class QuirementPoolController extends Controller
{

    /**
     * create
     * 资源创建
     *
     * @param name (任务名称)
     * @param list_url (列表url)
     * @param description (描述)
     * @param img_description (图片描述)
     * @param subscription_type (订阅类型 1 列表 2 详情)
     * @param is_capture (是否截图 1 是 2 否)
     * @param is_download_img (是否下载图片 1 是 2 否)
     * @return array
     */
    public function create(Request $request)
    {

        $params = $request->all();

        Log::debug('[create] 创建任务信息', $params);

        ValidatorService::check($params, [
            'name'              => 'nullable|string|max:100',
            'list_url'          => 'required|string|max:255',
            'description'       => 'nullable|min:10|max:255',
            'subscription_type' => 'required|integer|between:1,2',
            'is_capture'        => 'required|integer|between:1,2',
            'is_download_img'   => 'required|integer|between:1,2',
            'img_description'   => 'required|max:255'
        ]);

        $resData = $params;

        $resData['status'] = Requirement::STATUS_TRUE;

        $res = Requirement::create($resData);

        $result = [];

        if (!empty($res)) {
            $result = $res->toArray();
        }

        return $this->resObjectGet($result, 'requirement', $request->path());
    }

     /**
     * update
     * 修改资源
     *
     * @param id (资源id)
     * @param name (任务名称)
     * @param list_url (列表url)
     * @param description (描述)
     * @param img_description (图片描述)
     * @param subscription_type (订阅类型 1 列表 2 详情)
     * @param is_capture (是否截图 1 是 2 否)
     * @param is_download_img (是否下载图片 1 是 2 否)
     * @return array
     */
    public function update(Request $request)
    {

        $params = $request->all();
        $resData = $params;

        ValidatorService::check($params, [
            'id'                => 'required|int',
            'name'              => 'nullable|string|max:100',
            'list_url'          => 'required|string|max:255',
            'description'       => 'nullable|min:10|max:255',
            'subscription_type' => 'required|integer|between:1,2',
            'is_capture'        => 'required|integer|between:1,2',
            'is_download_img'   => 'required|integer|between:1,2',
            'img_description'   => 'required|max:255'
        ]);

        //$item = Requirement::find($formatParams['id']);
        $resData['status'] = Requirement::STATUS_TRUE;
        $req = Requirement::find($resData['id']);

        if (empty($req)) {
            throw new \Dingo\Api\Exception\ResourceException("Requirement is not found");
        }

        $req->update($resData);

        $result = [];

        if (!empty($req)) {
            $result = $req->toArray();
        }

        return $this->resObjectGet($result, 'requirement', $request->path());
    }

    /**
     * retrieve
     * 获取资源详情
     *
     * @param
     * @return array
     */
    public function retrieve(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            "id" => "required|integer"
        ]);

        $res = Requirement::find($params['id']);
        $resData = [];

        if (!empty($res)) {
            $resData = $res->toArray();
        }

        return $this->resObjectGet($resData, 'Requirement', $request->path());
    }

    /**
     * all
     * 资源列表
     *
     * @param
     * @return array
     */
    public function all(Request $request)
    {

        Log::debug('[internal QuirementPoolController all] start!');
        $params = $request->all();
        //验证参数
        ValidatorService::check($params, [
            'limit' => 'nullable|integer|min:1|max:500',
            'offset' => 'nullable|integer|min:0',
        ]);

        if (empty($params['limit'])) {
            $params['limit'] = 20;
        }

        if (empty($params['offset'])) {
            $params['offset'] = 0;
        }

        //获取数据
        $items = Requirement::take($params['limit'])
                            ->skip($params['offset'])
                            ->orderBy('id', 'desc')
                            ->get();

        $result = [];
        if (!empty($items)) {
            $result = $items->toArray();
        }

        return $this->resObjectGet($result, 'script_model', $request->path());
    }

    /**
     * create
     * 执行资源
     *
     * @param id (资源id)
     * @return array
     */
     public function updateStatus(Request $request)
     {

        $params = $request->all();



        ValidatorService::check($params, [
            "id" => "required|integer",
            "user_id"=>"required|integer"
        ]);


        $req = Requirement::find($params['id']);
        if (empty($req)) {
            throw new \Dingo\Api\Exception\ResourceException("Requirement is not found");
        }

        $req->status = Requirement::STATUS_FALSE;
        $req->operate_by = $params['user_id'];


        $req->save();

        $result = [];

        if (!empty($req)) {
            $result = $req->toArray();
        }

        return $this->resObjectGet($result, 'requirement', $request->path());

     }

}
