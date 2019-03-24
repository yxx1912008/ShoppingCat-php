<?php
namespace app\news\controller;

use QL\QueryList;

//网页抓取依赖
//网页抓取依赖

class Index
{

    /**
     * 获取新闻列表
     */
    public function getNewsList($page = 1)
    {
        $res = requestUrl('https://m.huxiu.com/maction/article_list?page=' . $page, 'POST');
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
                $item['img'] = $result[1];
            }
            return $item;
        });
        return json($result);
    }

    /**
     * 获取文章信息
     */
    public function getNewsDetail($newId = "")
    {
        if (empty($newId)) {
            return json(['status' => '0', 'messange' => '操作失败', 'data' => '']);
        }
        $newUrl = 'https://m.huxiu.com/article/' . $newId;
        $html = QueryList::get($newUrl);

        $rules = [
            // 采集文章标题
            'title' => ['title', 'text'],
            'author' => ['.username.fl', 'text'],
            //  'favorites' => ['.rec-article-time.clearfix > .fr', 'text'],
            'updateTime' => ['.m-article-time', 'text'],
            //  'newsId' => ['.rec-article-pic.fr', 'href'],
            'content' => ['#article-detail-content', 'html','-.neirong-shouquan -.text-remarks'],
            'mainImg' => ['.article-content-img > img', 'data-original'],
        ];
        $result = $html->rules($rules)->queryData();
        print_r($result);

    }

}
