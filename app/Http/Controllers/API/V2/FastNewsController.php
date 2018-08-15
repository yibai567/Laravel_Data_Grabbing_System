<?php
/**
 * FastNewsController
 * 模块控制器
 * @author liqi@jinse.com
 * @version 1.0
 * Date: 2018/08/14
 */

namespace App\Http\Controllers\API\V2;

use App\Models\V2\Requirement;
use App\Services\InternalAPIV2Service;
use App\Services\ValidatorService;
use Illuminate\Http\Request;
use Log;
use DB;

class FastNewsController extends Controller
{

    /**
     * getCompanies
     * 获取行业列表
     *
     * @return array
     */
    public function getCompanies(Request $request)
    {
        $params = [];
        $params['category_id'] = Requirement::CATEGORY_FAST_NEWS;

        $quirements = InternalAPIV2Service::get('/quirements/category_id', $params);

        $data = [];
        if (!empty($quirements)) {
            foreach ($quirements as $key => $value) {
                $company = InternalAPIV2Service::get('/company/' . $value['company_id']);
                $value['company_name'] = '';
                if (!empty($company)) {
                    $value['company_name'] = $company['cn_name'];
                }
                $data[] = $value;
            }
        }
        return $this->resObjectGet($data, 'fast_news', $request->path());
    }

    /**
     * getCompanies
     * 根据需求池id查询快讯列表
     *
     * @return array
     */
    public function getNewsByRequirement(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'requirement_id' => 'required|integer',
            'offset'         => 'nullable|integer',
            'limit'          => 'nullable|integer'
        ]);

        $data = InternalAPIV2Service::get('/fast_news/', $params);

        return $this->resObjectGet($data, 'fast_news', $request->path());
    }


}