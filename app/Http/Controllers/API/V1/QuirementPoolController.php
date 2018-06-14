<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use Log;
use App\Services\ValidatorService;
use App\Services\InternalAPIService;
use App\Services\ItemService;

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
            'description'       => 'nullable|max:255',
            'subscription_type' => 'required|integer|between:1,2',
            'is_capture'        => 'required|integer|between:1,2',
            'is_download_img'   => 'required|integer|between:1,2',
            'img_description'   => 'required|max:255'
        ]);

        $result = InternalAPIService::post('/quirement', $params);

        return $this->resObjectGet($result, 'quirement', $request->path());

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
            'id'                => 'required|int',
            'name'              => 'nullable|string|max:100',
            'list_url'          => 'required|string|max:255',
            'description'       => 'nullable|max:255',
            'subscription_type' => 'required|integer|between:1,2',
            'is_capture'        => 'required|integer|between:1,2',
            'is_download_img'   => 'required|integer|between:1,2',
            'img_description'   => 'required|max:255'
        ]);

        $result = InternalAPIService::post('/quirement/update', $params);

        return $this->resObjectGet($result, 'quirement', $request->path());
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
            "id" => "required|integer",
        ]);

        $res = InternalAPIService::get('/quirement', $params);
        return $this->resObjectGet($res, 'quirement', $request->path());
    }

    /**
     * all
     * 获取资源列表
     *
     * @return array
     */
    public function all(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'limit' => 'nullable|integer|min:1|max:500',
            'offset' => 'nullable|integer|min:0'
        ]);

        $res = InternalAPIService::get('/quirements', $params);
        return $this->resObjectGet($res, 'quirement', $request->path());
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

        $companies = InternalAPIService::get('/block_news/companies', $params);
        $total = InternalAPIService::get('/block_news/total', []);
        $blockNewsTotal = [];
        if (!empty($total)) {
            foreach ($total as $key => $value) {
                $blockNewsTotal[$value['requirement_id']]['total'] = $value['total'];
            }
        }
        $newCompanies = [];
        if (!empty($companies['data'])) {
            foreach ($companies['data'] as $companiesKey => $companiesValue) {
                $newTotal = 0;
                if (!empty($blockNewsTotal[$companiesValue['id']]['total'])) {
                    $newTotal = $blockNewsTotal[$companiesValue['id']]['total'];
                }
                $newCompanies[] = [
                    "id" => $companiesValue['id'],
                    "name" => $companiesValue['name'],
                    "list_url" => $companiesValue['list_url'],
                    "block_news_total" => $newTotal
                ];
            }
        }
        $companies['data'] = $newCompanies;
        return $this->resObjectList($companies, 'block_news', $request->path());
     }
}
