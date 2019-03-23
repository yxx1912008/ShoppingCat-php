<?php
namespace app\news\controller;

use QL\QueryList;

//网页抓取依赖
//网页抓取依赖

class Index
{
    public function index()
    {
        $res = requestUrl('https://m.huxiu.com/maction/article_list?page=1', 'POST');
        $res = json_decode($res)->data;
        $html = QueryList::html($res);

        $rules = [
            // 采集文章标题
            'title' => ['h2 > span', 'text'],
            'author' => ['.rec-author.fl', 'text'],
            'favorites' => ['.rec-article-time.clearfix > .fr', 'text'],
            'updateTime' => ['.rec-author.fl+.fl', 'text'],
            'newsId' => ['.rec-article-pic.fr', 'href'],
            'img' => ['.lazy', 'data-original'],
        ];

        $result = $html->rules($rules)->query()->getData(function ($item) {
            $item['title'] = trim($item['title'], " \r\n");
            $item['author'] = trim($item['author'], " \r\n");
            $item['favorites'] = trim($item['favorites'], " \r\n");
            if (!empty($item['updateTime'])) {
                $item['updateTime'] = trim($item['updateTime'], " \r\n");
            }
            if (preg_match('/\/article\/(\d+).html/', $item['newsId'], $result)) {
                $item['newsId'] = $result[1];
            }
            if (preg_match('/(.+?)\?/', $item['img'], $result)) {
                dump($result);
                $item['img'] = $result[1];
            }
            return $item;
        });
        print_r($result);
    }

}
