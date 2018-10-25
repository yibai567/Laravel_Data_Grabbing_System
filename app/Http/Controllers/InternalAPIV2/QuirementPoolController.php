<?php

namespace App\Http\Controllers\InternalAPIV2;

use App\Models\V2\Task;
use Illuminate\Http\Request;
use Log;
use App\Services\ValidatorService;
use App\Services\InternalAPIService;
use App\Models\V2\Requirement;
use GuzzleHttp\Client;
use App\Models\Image;
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
     * @param name (资源名称)
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

        ValidatorService::check($params, [
            'name'              => 'nullable|string|max:100',
            'list_url'          => 'required|string|max:255',
            'description'       => 'nullable|max:20000',
            'subscription_type' => 'required|integer|between:1,2',
            'language_type'     => 'required|integer|between:1,2',
            'is_capture'        => 'required|integer|between:1,2',
            'company_id'        => 'required|integer',
            'is_download_img'   => 'required|integer|between:1,2',
            'category'          => 'required|integer|between:1,4',
            // 'img_description'   => 'required|max:255',
            'operate_by'        => 'required|integer'
        ]);


        $params['status'] = Requirement::STATUS_TRUE;
        $params['status_identity'] = 1;
        $res = Requirement::create($params);

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
     * @param name (资源名称)
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
        ValidatorService::check($params, [
            'id'                => 'required|integer',
            'name'              => 'nullable|string|max:100',
            'list_url'          => 'required|string|max:255',
            'description'       => 'nullable|max:20000',
            'company_id'        => 'required|integer',
            'subscription_type' => 'required|integer|between:1,2',
            'language_type'     => 'required|integer|between:1,2',
            'is_capture'        => 'required|integer|between:1,2',
            'is_download_img'   => 'required|integer|between:1,2',
            'category'         => 'required|integer|between:1,4',
            // 'img_description'   => 'required|max:255'
        ]);
        //$item = Requirement::find($formatParams['id']);
        $params['status'] = Requirement::STATUS_TRUE;
        $req = Requirement::find($params['id']);
        if (empty($req)) {
            throw new \Dingo\Api\Exception\ResourceException("Requirement is not found");
        }
        $req->update($params);

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
     * @param id (资源id)
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

    // /**
    //  * all
    //  * 资源列表
    //  *
    //  * @param
    //  * @return array
    //  */
    // public function all(Request $request)
    // {

    //     Log::debug('[internal QuirementPoolController all] start!');
    //     $params = $request->all();
    //     //验证参数
    //     ValidatorService::check($params, [
    //         'limit' => 'nullable|integer|min:1|max:500',
    //         'offset' => 'nullable|integer|min:0',
    //     ]);

    //     if (empty($params['limit'])) {
    //         $params['limit'] = 20;
    //     }

    //     if (empty($params['offset'])) {
    //         $params['offset'] = 0;
    //     }

    //     //获取数据
    //     $items = Requirement::take($params['limit'])
    //                         ->select('name','list_url','subscription_type','is_capture','is_download_img','status','create_by','operate_by','created_at','updated_at')
    //                         ->skip($params['offset'])
    //                         ->orderBy('id', 'desc')
    //                         ->get();

    //     $result = [];
    //     if (!empty($items)) {
    //         $result = $items->toArray();
    //     }

    //     return $this->resObjectGet($result, 'script_model', $request->path());
    // }

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
            "user_id" => "required|integer",
            'status' => "required|integer",
            'status_reason' => "required_if:status," . Requirement::STATUS_STASH ."|string|max:255|"
        ]);

        $req = Requirement::find($params['id']);
        if (empty($req)) {
            throw new \Dingo\Api\Exception\ResourceException("Requirement is not found");
        }

        $req->status = $params['status'];

        $req->operate_by = $params['user_id'];

        if (!empty($params['status_reason']) && $params['status'] == Requirement::STATUS_STASH) {
            $req->status_reason = $params['status_reason'];
        }

        $req->save();

        $result = [];

        if (!empty($req)) {
            $result = $req->toArray();
        }
        return $this->resObjectGet($result, 'requirement', $request->path());

     }

    /**
     * getCompanies
     * 获取公司名称
     *
     * @return array
     */

     public function getCompanies(Request $request)
     {
        $params = $request->all();

        ValidatorService::check($params, [
            "offset" => "nullable|integer",
            "limit" => "nullable|integer",
            "order" => "nullable|string",
            "sort" => "nullable|string"
        ]);

        if (empty($params['offset'])) {
            $params['offset'] = 0;
        }

        if (empty($params['limit'])) {
            $params['limit'] = 100;
        }

        if (empty($params['order'])) {
            $params['order'] = 'created_at';
        }

        if (empty($params['sort'])) {
            $params['sort'] = 'desc';
        }

        $total = Requirement::count();

        $companies = Requirement::take($params['limit'])
                ->skip($params['offset'])
                ->orderBy($params['order'], $params['sort'])
                ->get();

        $result['total'] = $total;

        $data = [];
        if (!empty($companies)) {
            $data = $companies->toArray();
        }

        $result['data'] = $data;
        return $this->resObjectGet($result, 'block_news', $request->path());
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
        //获取数据
        $res = Requirement::where('category', 1)->whereIn('status', [Requirement::STATUS_TRUE, Requirement::STATUS_FALSE])->get();
        $result = [];
        if (!empty($res)) {
            $result = $res->toArray();
        }
        return $this->resObjectGet($result, 'quirement_pool', $request->path());
    }

    /**
     * getQuirementByCategoryId
     * 根据分类获取需求资源列表
     *
     * @param
     * @return array
     */
    public function getQuirementByCategoryId(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'category_id' => 'required|integer',
        ]);

        //获取数据
        $res = Requirement::where('category', $params['category_id'])->whereIn('status', [Requirement::STATUS_TRUE, Requirement::STATUS_FALSE])->get();

        $result = [];
        if (!empty($res)) {
            $result = $res->toArray();
        }
        return $this->resObjectGet($result, 'quirement_pool', $request->path());
    }
}
