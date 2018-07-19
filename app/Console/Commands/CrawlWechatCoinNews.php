<?php

namespace App\Console\Commands;

use App\Models\V2\WechatCoinNews;
use App\Services\HttpService;
use Illuminate\Console\Command;
use QL\QueryList;

class CrawlWechatCoinNews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:wechat:coin_news {coin_name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'crawl wechat coin news';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $coinName = $this->argument('coin_name');

        $data = [];
        $data['coin_name'] = $coinName;

        $html = QueryList::get('http://api.ip.data5u.com/dynamic/get.html?order=803e098ca53548cb6fb3f2a1b87ddb53&sep=3');
        $proxyIp = trim($html->getHtml());

        if (!preg_match('/^\d+/',$proxyIp)) {
            return false;
        }

        for ($i = 1; $i < 11; $i ++) {
            $this->info('proxyIp = ' . $proxyIp);
            $this->info('当前页数为' . $i);
            QueryList::get('http://weixin.sogou.com/weixin', [
                'query'      => '"' . $coinName . '"',
                '_sug_type_' => null,
                's_from'     => 'input',
                '_sug_'      => 'n',
                'type'       => 2,
                'page'       => $i,
                '&ie'        => 'utf8'
            ], ['proxy'   => 'http://' . $proxyIp,
                'timeout' => 60
                ]
                            );

            $list = pq('.news-list li');

            if (!$list->html()) {
                return true;
            }

            $data = [];
            $data['coin_name'] = $coinName;
            foreach ($list as $news) {

                $data['title'] = pq($news)->find('.txt-box h3 a')->html();
                $this->info(' title= ' . $data['title']);

                $data['detail_url'] = pq($news)->find('.txt-box h3 a')->attr('href');
                $data['description'] = pq($news)->find('.txt-info')->html();
                $data['wechat_subscription_number'] = trim(pq($news)->find('.s-p a')->text());
                $time = pq($news)->find('.s-p .s2')->text();
                if (preg_match_all('/(\d+)/is',$time,$result)) {
                    $data['publish_time'] = $result[1][0];
                }
                $data['md5_all'] = md5($data['coin_name'] . $data['title'] . $data['wechat_subscription_number'] . $data['publish_time']);

                $wechatCoinNews = WechatCoinNews::where('md5_all', $data['md5_all'])->first();

                if (!empty($wechatCoinNews)) {
                    continue;
                }

                WechatCoinNews::create($data);
            }
            sleep(5);

        }
        return true;
    }


}