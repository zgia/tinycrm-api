<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\Log;
use App\Model\Logcontent;

/**
 * Class SystemService.
 */
class SystemService
{
    /**
     * 自定义参数.
     *
     * @param string $key
     *
     * @return array
     */
    public static function miscParams(?string $key = null)
    {
        $mps = [];

        //性别
        $mps['gender'] = [
            '1' => '男',
            '2' => '女',
        ];

        return $key ? $mps[$key] : $mps;
    }

    /**
     * 获取文件内容.
     *
     * @param string $key
     *
     * @return array
     */
    public static function getContentFromFile($key)
    {
        $file = env('FILE_CACHE_DIR') . DIRECTORY_SEPARATOR . $key . '.php';

        $cache = [];

        if (file_exists($file)) {
            $cache = @include $file;

            $cache || $cache = [];
        }

        return $cache;
    }

    /**
     * 获取分类.
     *
     * @param string $key
     *
     * @return array
     */
    public static function getCategory(?string $key = null)
    {
        static $content = null;

        if ($content === null) {
            $content = static::getContentFromFile('neo_categories');
        }

        return $key ? $content[$key] : $content;
    }

    /**
     * 通过ID获取分类.
     *
     * @param int $id
     *
     * @return array
     */
    public static function getCategoryById(?int $id = 0)
    {
        if (! $id) {
            return [];
        }

        return static::getCategory('categories')[$id];
    }

    /**
     * 通过别名获取分类.
     *
     * @param string $alias
     * @param bool   $onlytitle 为true时,只返回title
     * @param bool   $parent
     *
     * @return array
     */
    public static function getCategoryByAlias(?string $alias = null, bool $parent = false, bool $onlytitle = true)
    {
        $cats = [];

        if (! $alias) {
            return $cats;
        }

        $categories = static::getCategory();

        // ID
        $pid = static::getCategoryIdByAlias($alias);

        if ($parent) {
            $cats[$pid] = $categories['categories'][$pid];
        }

        $ids = $categories['categorylist'][$pid];

        if ($ids) {
            foreach ($ids as $id) {
                $cats[$id] = $categories['categories'][$id];
            }
        }

        return $onlytitle ? array_column($cats, 'title', 'categoryid') : $cats;
    }

    /**
     * 通过别名获取分类ID.
     *
     * @param string $alias
     *
     * @return int
     */
    public static function getCategoryIdByAlias(?string $alias = null)
    {
        return $alias ? (int) static::getCategory('alias')[$alias] : 0;
    }

    /**
     * 管理日志
     *
     * @param array $data 数据
     *
     * @return int 日志ID
     */
    public static function actionLog(array $data)
    {
        $log = new Log();

        $log->type = (string) $data['type'];
        $log->action = (string) $data['action'];
        $log->objectid = (int) $data['id'];
        $log->script = (string) container()->get('request_uri');
        $log->ipaddress = (string) container()->get('remote_addr');
        $log->userid = (int) $data['userid'] ?? currentUserId();
        $log->dateline = time();

        $saved = $log->save();

        if ($saved && (isset($data['from']) || isset($data['to']))) {
            if (isset($data['from']) && is_array($data['from'])) {
                $data['from'] = json_encode($data['from'], JSON_UNESCAPED_UNICODE);
            }

            if (isset($data['to']) && is_array($data['to'])) {
                $data['to'] = json_encode($data['to'], JSON_UNESCAPED_UNICODE);
            }

            $content = new Logcontent();
            $content->logid = $log->logid;
            $content->fromcontent = $data['from'];
            $content->tocontent = $data['to'];

            $content->save();
        }

        return $log->logid;
    }
}
