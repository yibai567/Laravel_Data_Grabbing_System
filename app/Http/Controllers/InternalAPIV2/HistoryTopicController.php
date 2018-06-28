<?php
/**
 * HistoryTopicController
 * 历史老数据控制器
 * @author liqi@jinse.com
 * @version 1.0
 * Date: 2018/06/26
 */

namespace App\Http\Controllers\InternalAPIV2;

use App\Models\HistoryTopic;
use App\Services\ValidatorService;
use Illuminate\Http\Request;
use Log;

class HistoryTopicController extends Controller
{
    /**
     * batchHandle
     * 采集数据处理
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

        $historyTopics = [];

        foreach ($params['result'] as $data) {

            ValidatorService::check($data, [
                'title'      => 'nullable|string|max:2000',
                'detail_url' => 'required|string|max:1000',
            ]);

            $md5Url = md5($data['detail_url']);

            $historyTopic = HistoryTopic::where('md5_url', $md5Url)->first();

            if (!empty($historyTopic)) {
                continue;
            }

            $newData = [
                'category'    => $params['category'],
                'company'     => $params['company'],
                'company_id'  => $params['company_id'],
                'title'       => $data['title'],
                'detail_url'  => $data['detail_url'],
                'md5_url'     => $md5Url,
                'create_time' => time(),
                'status'      => HistoryTopic::STATUS_INIT,
            ];

            $historyTopics[] = HistoryTopic::create($newData)->toArray();
        }

        return $this->resObjectGet(true, 'history_topic', $request->path());
    }

    /**
     * update
     * 数据更新
     *
     * @param
     * @return array
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

        $params['show_time'] = $this->__formatShowTime($params['show_time']);

        $params['md5_url'] = md5($params['detail_url']);

        $historyTopic = HistoryTopic::where('md5_url', $params['md5_url'])->first();

        if (empty($historyTopic)) {
            $historyTopic = new HistoryTopic();
            $historyTopic->create_time = time();
        }

        if ($historyTopic->status !== HistoryTopic::STATUS_FINISH) {
            foreach ($params as $field => $param) {
                $historyTopic->$field = $param;
            }
            $historyTopic->status = HistoryTopic::STATUS_FINISH;
            $historyTopic->save();
        }

//        $result = [];
//        if (!empty($historyTopic)) {
//            $result = $historyTopic->toArray();
//        }


        return $this->resObjectGet(true, 'history_topic', $request->path());
    }

    /**
     * listByCompanyId
     * 更新companyId获取数据
     *
     * @param
     * @return boolean
     */
    public function listByCompanyId(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'company_id' => 'required|integer|max:1000',
            'category'   => 'nullable|string|max:50',
            'status'     => 'nullable|integer|between:1,2',
            'limit'      => 'nullable|integer|min:1|max:500',
            'offset'     => 'nullable|integer|min:0',
        ]);

        if (empty($params['limit'])) {
            $params['limit'] = 10;
        }

        if (empty($params['offset'])) {
            $params['offset'] = 0;
        }

        $status = $params['status'];

        if (empty($status)) {
            $status = HistoryTopic::STATUS_INIT;
        }

        $wheres = [];
        $wheres[] = ['company_id', $params['company_id']];
        $wheres[] = ['status', $status];

        if (!empty($params['category'])) {
            $wheres[] = ['category', $params['category']];
        }

        $historyTopics = HistoryTopic::where($wheres)
                                    ->take($params['limit'])
                                    ->skip($params['offset'])
                                    ->orderBy('id')
                                    ->get(['id','detail_url']);

        $result = [];
        if (!empty($historyTopics)) {
            $result = $historyTopics->toArray();
            $ids = array_column($result,'id');
            HistoryTopic::whereIn('id',$ids)->update(['status'=>HistoryTopic::STATUS_RUN]);
        }

        return $this->resObjectGet($result, 'history_topic', $request->path());
    }

    /**
     * crawlDataByCompanyId
     * 根据company_id爬取网站详情
     *
     * @param
     * @return boolean
     */
    public function crawlDataByCompanyId(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'company_id' => 'required|integer|max:1000',
            'category'   => 'nullable|string|max:50',
        ]);

//        $historyTopics = InternalAPIV2Service::get('/history_topics/company_id',$params);

//        foreach ($historyTopics as $historyTopic) {
//            $ql = QueryList::getInstance();
//           $ql->use(PhantomJs::class);
//        $html = $ql->browser('https://m.toutiao.com')->getHtml();
//            dd($html);
//        }

//        return $this->resObjectGet($result, 'history_topic', $request->path());
    }

    /**
     * __formatShowTime
     * 更新companyId获取数据
     *
     * @param
     * @return int
     */
    public function __formatShowTime($time)
    {
        $showTime = strtotime($time);
        if ($showTime) {
            return $showTime;
        }
        if (preg_match('/^\d+分钟/', $time)) {
            preg_match('/^\d+/', $time, $result);
            return time() - $result[0] * 60;
        } elseif (preg_match('/^\d+小时/', $time)) {
            preg_match('/^\d+/', $time, $result);
            return time() - $result[0] * 60 * 60;
        } elseif (preg_match('/^\d+天/', $time)) {
            preg_match('/^\d+/', $time, $result);
            return time() - $result[0] * 60 * 60 * 24;
        }

        return 0;
    }
}