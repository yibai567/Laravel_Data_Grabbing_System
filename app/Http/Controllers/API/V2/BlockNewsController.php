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
    public function all(Request $request)
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
                $news = InternalAPIV2Service::get('/news/' . $value['id']);
                $value['result'] = $news;
                $data[] = $value;
            }
        }
        return $this->resObjectGet($data, 'block_news', $request->path());
    }

    /**
     * getByRequirementId
     * 获取新闻列表
     *
     * @param $requirement_id
     * @return array
     */
    public function getByRequirementId(Request $request, $requirement_id)
    {
        $result = InternalAPIV2Service::get('/block_news/' . $requirement_id);
        return $this->resObjectGet($result, 'block_news', $request->path());
    }
}
