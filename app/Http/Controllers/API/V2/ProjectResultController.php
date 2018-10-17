<?php
/**
 * ProjectResultController
 * 项目结果控制器
 * @author liqi@jinse.com
 * @version 1.0
 * Date: 2018/10/16
 */

namespace App\Http\Controllers\API\V2;

use App\Services\InternalAPIV2Service;
use App\Services\ValidatorService;
use Illuminate\Http\Request;
use Log;

class ProjectResultController extends Controller
{

    protected $limit = 20;

    /**
     * all
     * 列表
     *
     * @param
     * @return array
     */
    public function all(Request $request)
    {
        $params = [
            'limit'    => $request->get('limit', $this->limit),
            'page'     => $request->get('page', 0),
            'metadata' => $request->get('metadata'),
        ];

        //检测参数
        ValidatorService::check($params, [
            'limit'    => 'integer',
            'page'     => 'integer',
            'metadata' => 'nullable'
        ]);

        if($params['limit'] > $this->limit) {
            $params['limit'] = $this->limit;
        }

        $queryData = [
            'limit'  => $params['limit'],
            'offset' => $params['page'] * $params['limit']
        ];

        $projectResults = InternalAPIV2Service::get('/project_results', $queryData);

        $result = $this->__formatProjectResults($projectResults, $params['metadata']);

        return $this->resObjectGet($result, 'project_result', $request->path());
    }


    /**
     * __formatProjectResults
     * 格式化返回数据
     *
     * @param
     * @return array
     */
    private function __formatProjectResults($projectResults, $metadata) {
        $result = [];
        foreach ($projectResults as $projectResult) {
            $requirementPool = InternalAPIV2Service::get('/quirements/task_id', ['task_id' => $projectResult['task_id']]);
            $result[] = $this->__formatProjectResult($projectResult, $requirementPool, $metadata);
        }

        return $result;
    }

    /**
     * __formatProjectResult
     * 格式化返回单个数据
     *
     * @param
     * @return array
     */
    private function __formatProjectResult($projectResult, $requirementPool, $metadata) {
       $data = [
           'source'       => [
               'domain' => '',
               'title'  => $requirementPool['company']['en_name'],
               'path'   => null
           ],
           'title'        => $projectResult['title'],
           'published_at' => $projectResult['show_time'],
           'slug'         => '',
           'url'          => $projectResult['detail_url'],
           'language_type' => $projectResult['language_type']
       ];

       $url = parse_url($requirementPool['list_url']);
       $data['source']['domain'] = $url['host'];
       $data['source']['path'] = $url['path'];

       $data['slug'] = end(explode('/', trim($projectResult['detail_url'], '/')));

       if ($metadata) {
           $data['metadata'] = [
               'content' => $projectResult['content'],
               'description' => $projectResult['description']
           ];
       }


       return $data;
    }

}