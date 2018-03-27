<?php

namespace App\Http\Controllers\API\V1;

use App\Services\APIService;
use App\Services\ValidatorService;
use Illuminate\Http\Request;

/**
 * CrawlTaskController
 * 任务控制器
 * @author huangxingxing@jinse.com
 * @version 1.1
 * Date: 2018/03/25
 */
class CrawlTaskController extends Controller
{
    /**
     * createForBatch
     * 保存抓取结果
     *
     * @param resource_url (任务资源URL)
     * @param cron_type 默认 1可持续的
     * @param selectors (选择器)
     * @param is_ajax (是否是ajax，默认1) 1|不是
     * @param is_login (是否登陆，默认1) 1|不是
     * @param is_wall (是否翻墙，默认1) 1|不是
     * @param is_proxy (是否使用协议，默认1) 1|http,2|https
     * @param type (类型) 1|http,2|https
     * @param header (头信息)
     * @return array
     */
    public function create(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'resource_url' => 'required|string',
            'cron_type' => 'integer|nullable',
            'selectors' => 'nullable',
            'is_ajax' => 'integer|nullable',
            'is_login' => 'integer|nullable',
            'is_wall' => 'integer|nullable',
            'is_proxy' => 'integer|nullable',
            'type' => 'integer|nullable',
            'header' => 'nullable',
        ]);

        $data = APIService::internalPost('/internal/crawl/task', $params);

        $result = [];
        if ($data) {
            $result = ['id'=> $data['id']];
        }

        return $this->resObjectGet($result, 'crawl_task', $request->path());
    }

    /**
     * stop
     * 任务停止
     *
     * @param id (任务ID)
     * @return array
     */
    public function stop(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'id' => 'integer|required',
        ]);

        $data = APIService::internalPost('/internal/crawl/task/stop', $params);

        $result = [];
        if ($data) {
            $result = $data['data'];
        }

        infoLog('[stop] end');
        return $this->resObjectGet($result, 'crawl_task', $request->path());
    }

    /**
     * start
     * 任务开始
     *
     * @param id (任务ID)
     * @return array
     */
    public function start(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'id' => 'integer|required',
        ]);

        $data = APIService::internalPost('/internal/crawl/task/start', $params);

        $result = [];
        if ($data) {
            $result = $data['data'];
        }

        return $this->resObjectGet($result, 'crawl_task', $request->path());
    }

    /**
     * test
     * 任务测试
     *
     * @param id (任务ID)
     * @return array
     */
    public function test(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'id' => 'integer|required',
        ]);

        APIService::internalPost('/internal/crawl/task/test', $params);

        return $this->resObjectGet('测试提交成功，请稍后查看结果！', 'crawl_task', $request->path());
    }

    /**
     * getByQueueName
     * 根据队列名获取列表数据
     *
     * @param name (队列名)
     * @return array
     */
    public function getByQueueName(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'name' => 'string|required|max:100',
        ]);

        $data = APIService::internalGet('/internal/crawl/task/queue/name?name=' . $params['name']);

        $result = [];
        if ($data) {
            $result = $data;
        }

        return $this->resObjectGet($result, 'list', $request->path());
    }

    /**
     * getQueueInfo
     * 获取队列信息-名字及任务数
     *
     * @return array
     */
    public function getQueueInfo(Request $request)
    {
        $data = APIService::internalGet('/internal/crawl/task/queue/info');

        $result = [];
        if ($data) {
            $result = $data;
        }

        return $this->resObjectGet($result, 'list', $request->path());
    }

    /**
     * listByIds
     * 根据ids获取任务列表
     *
     * @param ids (任务ids)
     * @return array
     */
    public function listByIds(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'ids' => 'string|required|max:100',
        ]);

        $data = APIService::internalGet('/internal/crawl/tasks/ids', $params);

        $result = [];
        if ($data) {
            $result = $data;
        }

        return $this->resObjectGet($result, 'list', $request->path());
    }

    /**
     * update
     * 更新任务详情
     *
     * @param id (任务id)
     * @param resource_url (任务资源URL)
     * @param cron_type 默认 1可持续的
     * @param selectors (选择器)
     * @param is_ajax (是否是ajax，默认1) 1|不是
     * @param is_login (是否登陆，默认1) 1|不是
     * @param is_wall (是否翻墙，默认1) 1|不是
     * @param is_proxy (是否使用协议，默认1) 1|http,2|https
     * @param type (类型) 1|http,2|https
     * @param header (头信息)
     * @return array
     */
    public function update(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'id' => 'required|integer',
            'resource_url' => 'nullable|string',
            'cron_type' => 'integer|nullable',
            'selectors' => 'nullable',
            'is_ajax' => 'integer|nullable',
            'is_login' => 'integer|nullable',
            'is_wall' => 'integer|nullable',
            'is_proxy' => 'integer|nullable',
            'type' => 'integer|nullable',
            'header' => 'nullable',
        ]);

        $data = APIService::internalPost('/internal/crawl/task/update', $params);

        $result = [];
        if ($data) {
            $result = ['id'=> $data['id']];
        }

        return $this->resObjectGet($result, 'crawl_task', $request->path());
    }
}
