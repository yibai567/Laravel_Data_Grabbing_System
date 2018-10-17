<?php
/**
 * ProjectResultController
 * 项目结果控制器
 * @author liqi@jinse.com
 * @version 1.0
 * Date: 2018/10/16
 */

namespace App\Http\Controllers\InternalAPIV2;

use App\Models\V2\ProjectResult;
use App\Services\ValidatorService;
use Illuminate\Http\Request;
use Log;

class ProjectResultController extends Controller
{

    protected $limit = 20;

    /**
     * retrieve
     * 详情
     *
     * @param
     * @return array
     */
    public function all(Request $request)
    {
        $params = [
            'limit'    => $request->get('limit', $this->limit),
            'offset'   => $request->get('offset', 0),
        ];

        //检测参数
        ValidatorService::check($params, [
            'limit'    => 'integer',
            'offset'   => 'integer',
        ]);

        if($params['limit'] > $this->limit) {
            $params['limit'] = $this->limit;
        }

        $projectResult = ProjectResult::whereNotNull('content')
                                    ->take($params['limit'])
                                    ->skip($params['offset'])
                                    ->orderBy('id')
                                    ->get();
        $result = [];

        if (!empty($projectResult)) {
            $result = $projectResult->toArray();
        }

        return $this->resObjectGet($result, 'project_result', $request->path());
    }
}