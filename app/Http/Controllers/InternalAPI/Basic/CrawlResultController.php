<?php

namespace App\Http\Controllers\InternalAPI\Basic;

use App\Http\Requests\CrawlResultCreateRequest;
use App\Models\CrawlResult;

class CrawlResultController extends Controller
{
    /**
     * 返回爬虫结果接口
     * @param CrawlResultCreateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(CrawlResultCreateRequest $request)
    {
        $data = $request->postFillData();
        if (empty($data)) {
            return response('返回数据为空', 404);
        }
        if ($data['format_data'] && $data['crawl_task_id']) {
            $crawlResult = CrawlResult::where(['format_data' => $data['format_data'], 'crawl_task_id' => $data['crawl_task_id']])
                ->first();
            if ($crawlResult) {
                return response('数据已存在', 404);
            }

        }
        $result = CrawlResult::create($data);
        return $this->resObjectGet($result, 'crawl_result', $request->path());
    }
}
