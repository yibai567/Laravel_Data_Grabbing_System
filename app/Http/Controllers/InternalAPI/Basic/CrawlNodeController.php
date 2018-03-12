<?php

namespace App\Http\Controllers\InternalAPI\Basic;

use App\Models\CrawlNode;
use Illuminate\Http\Request;
use App\Http\Controllers\InternalAPI\Controller;

class CrawlNodeController extends Controller
{
    public function getUsableNode(Request $request)
    {
        infoLog('getUsableNode start.', $request);
        $nodes = CrawlNode::withCount('crawlNodeTasks as crawl_node_tasks_count')
                            ->where('status', CrawlNode::IS_USABLE)
                            ->get();
        infoLog('getUsableNode get usable node.', $nodes);
        if (empty($nodes)) {
            infoLog('getUsableNode node usable not exist.');
            return $this->resError(401, '没有可用节点');
        }
        $node = array_first(array_where($nodes->toArray(), function($node) {
           if ($node['max_task_num'] > $node['crawl_node_tasks_count']) {
               return true;
           }
           return false;
        }));
        infoLog('getUsableNode check node usable.');
        if (empty($node)) {
            return $this->resError(401, '没有可用节点');
        }
        infoLog('getUsableNode end.');
        return $this->resObjectGet($node, 'crawl_node', $request->path());
    }
}
