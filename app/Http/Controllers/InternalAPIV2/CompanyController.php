<?php

namespace App\Http\Controllers\InternalAPIV2;

use Illuminate\Http\Request;
use Log;
use App\Services\ValidatorService;
use App\Models\V2\Company;
/**
 * QuirementPoolController
 * 资源管理接口
 *
 * @author huangxingxing@jinse.com
 * @version 1.1
 * Date: 2018/06/28
 */
class CompanyController extends Controller
{

    /**
     * getById
     * 根据公司id获取公司信息
     *
     * @param
     * @return array
     */
    public function getById(Request $request, $id)
    {
        //获取数据
        $res = Company::find($id);
        $result = [];
        if (!empty($res)) {
            $result = $res->toArray();
        }
        return $this->resObjectGet($result, 'company', $request->path());
    }
}
