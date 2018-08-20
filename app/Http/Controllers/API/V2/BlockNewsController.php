<?php

namespace App\Http\Controllers\API\V2;

use Illuminate\Http\Request;
use App\Services\ValidatorService;
use App\Services\InternalAPIV2Service;
use Log;
use DB;

class BlockNewsController extends Controller
{
    /**
     * all
     * 获取列表
     *
     * @return array
     */
    public function getCompanies(Request $request)
    {
        $quirements = InternalAPIV2Service::get('/quirements', []);
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
        return $this->resObjectGet($data, 'block_news', $request->path());
    }

    /**
     * getCompanies
     * 根据需求池id查询行业新闻
     *
     * @return array
     */
    public function getNewsByRequirement(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'requirement_id' => 'required|integer',
            'offset' => 'nullable|integer',
            'limit' => 'nullable|integer'
        ]);

        $data = InternalAPIV2Service::get('/block_news', $params);

        return $this->resObjectGet($data, 'block_news', $request->path());
    }
}
