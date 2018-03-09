<?php

namespace App\Http\Controllers\InternalAPI\Basic;

use App\Models\CrawlNode;
use Illuminate\Http\Request;
use App\Http\Controllers\InternalAPI\Controller;

class CrawlNodeController extends Controller
{
    public function getUsableNode(Request $request)
    {
        $nodes = CrawlNode::withCount('crawlNodeTasks as crawl_node_tasks_count')
                            ->where('status', CrawlNode::IS_USABLE)
                            ->get();
        if (empty($nodes)) {
            return $this->resError(401, '没有可用节点');
        }
        $node = array_first(array_where($nodes->toArray(), function($node) {
           if ($node['max_task_num'] > $node['crawl_node_tasks_count']) {
               return true;
           }
           return false;
        }));

        if (empty($node)) {
            return $this->resError(401, '没有可用节点');
        }
        return $this->resObjectGet($node, 'crawl_node', $request->path());
    }
}
