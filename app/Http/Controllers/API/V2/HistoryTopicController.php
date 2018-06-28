<?php
/**
 * HistoryTopicController
 * 历史老数据控制器
 * @author liqi@jinse.com
 * @version 1.0
 * Date: 2018/06/26
 */

namespace App\Http\Controllers\API\V2;

use App\Services\InternalAPIV2Service;
use App\Services\ValidatorService;
use Illuminate\Http\Request;
use Log;

class HistoryTopicController extends Controller
{
    /**
     * create
     * 数据增加
     *
     * @param
     * @return boolean
     */
    public function create(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'category'   => 'required|string|max:100',
            'company'    => 'required|string|max:50',
            'company_id' => 'required|integer|max:10',
            'result'     => 'required|array'
        ]);

        InternalAPIV2Service::post('/history_topics', $params);

        return $this->resObjectGet(true, 'history_topic', $request->path());
    }

    /**
     * update
     * 数据更新
     *
     * @param
     * @return boolean
     */
    public function update(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'category'      => 'nullable|string|max:100',
            'company'       => 'nullable|string|max:50',
            'company_id'    => 'nullable|integer|max:1000',
            'title'         => 'required|string|max:2000',
            'content'       => 'required|string',
            'tags'          => 'nullable|string|max:255',
            'show_time'     => 'nullable|string|max:100',
            'author'        => 'nullable|string|max:50',
            'read_count'    => 'nullable|string|max:100',
            'comment_count' => 'nullable|string|max:100',
            'detail_url'    => 'required|string|max:500',
        ]);

        $historyTopics = InternalAPIV2Service::post('/history_topic/update', $params);

        if (empty($historyTopics)) {
            Log::debug('[v2 HistoryTopicController create]  $historyTopics update is failed');
            return $this->resObjectGet(false, 'history_topic', $request->path());
        }

        return $this->resObjectGet(true, 'history_topic', $request->path());
    }

    /**
     * listByCompanyId
     * 更新companyId获取数据
     *
     * @param
     * @return array
     */
    public function listByCompanyId(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'company_id' => 'required|integer|max:1000',
            'category'   => 'nullable|string|max:50',
        ]);

        $historyTopics = InternalAPIV2Service::get('/history_topics/company_id', $params);

        return $this->resObjectGet($historyTopics, 'history_topic', $request->path());
    }

}