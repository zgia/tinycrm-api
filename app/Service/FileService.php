<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\BusinessException;
use App\Model\File;
use Hyperf\DbConnection\Db;

/**
 * Class FileService.
 */
class FileService extends BaseService
{
    /**
     * 获取某个客户的所有文件
     *
     * @param $memberid
     *
     * @return array
     */
    public function getFiles($memberid)
    {
        $files = Db::table('file')
            ->select(['fileid', 'url', 'description', 'dateline'])
            ->where(['memberid' => $memberid, 'deleted' => 0])
            ->orderBy('fileid', 'DESC')
            ->get();

        $items = [];
        foreach ($files as $file) {
            $file['cover'] = static::makeCover($file['url']);
            static::makeImageGroup($file);

            $file['datetime'] = formatLongDate($file['dateline']);

            $day = formatDate('Y-m-d', $file['dateline']);
            unset($file['dateline']);
            $items[$day][] = $file;
        }

        return $items;
    }

    /**
     * 更新文件
     *
     * @param int    $memberid
     * @param array  $new_files
     * @param array  $deleted_files
     * @param string $desc
     */
    public function update(int $memberid, array $new_files, array $deleted_files, $desc = '')
    {
        foreach ($new_files as $new_file) {
            if (in_array($new_file, $deleted_files)) {
                continue;
            }

            $file = new File();
            $file->memberid = $memberid;
            $file->url = str_ireplace(env('ABSURL', ''), '', $new_file);
            $file->description = $desc;
            $file->dateline = time();

            $file->save();
        }
    }

    /**
     * 更新备注
     *
     * @param int    $fileid
     * @param string $desc
     *
     * @return bool
     */
    public function updateDescription(int $fileid, string $desc)
    {
        $file = $this->checkFile($fileid);

        $file->description = $desc;

        return $file->save();
    }

    /**
     * 图片有原图、大图(medium)和缩略图(thumbnail)，用于不同的展示位置
     *
     * url: /assets/files/file/22/95e21b3d98825dda5c6d1e2fd5e2475c.png
     * medium: /assets/files/file/22/medium/95e21b3d98825dda5c6d1e2fd5e2475c.png
     * thumbnail: /assets/files/file/22/thumbnail/95e21b3d98825dda5c6d1e2fd5e2475c.png
     *
     * @param array $file
     */
    public static function makeImageGroup(array &$file)
    {
        if (empty($file['url'])) {
            return;
        }

        $pathinfo = pathinfo($file['url']);

        foreach (['medium', 'thumbnail'] as $k) {
            $tmp = $pathinfo['dirname'] . "/{$k}/" . $pathinfo['basename'];
            if (file_exists(env('STATIC_DIR') . $tmp)) {
                $file["url_{$k}"] = env('ABSURL') . $tmp;
            } else {
                $file["url_{$k}"] = '';
            }
        }

        $file['url'] = env('ABSURL') . $file['url'];

        $file['url_thumbnail'] = $file['cover'] ?: ($file['url_thumbnail'] ?: $file['url']);
        $file['url_medium'] = $file['cover'] ?: ($file['url_medium'] ?: $file['url']);
    }

    /**
     * 为非图片（可以预览）文件提供一个logo作为封面
     *
     * @param string $url
     *
     * @return string
     */
    public static function makeCover(string $url = '')
    {
        if (empty($url)) {
            return '';
        }

        $imageType = getOption('fileimagetype');
        $allowType = getOption('fileallowtype');

        if (preg_match("/{$imageType}$/i", $url)) {
            $type = '';
        } elseif (preg_match("/{$allowType}$/i", $url)) {
            $type = strtolower(pathinfo($url, PATHINFO_EXTENSION)) . '.png';
        } else {
            $type = 'file.png';
        }

        if ($type) {
            $cover = env('ABS_CONTENT_URL') . '/icons/' . $type;
        } else {
            $cover = '';
        }

        return $cover;
    }

    /**
     * 从file表删除一个文件
     *
     * @param int $fileid
     */
    public static function deleteFile(int $fileid)
    {
        $fileid = (int) $fileid;

        $file = File::query()->find($fileid);
        if ($file) {
            $file->deleted = 1;
            $file->save();

            @unlink(env('CONTENT_DIR') . $file->url);
        }
    }

    /**
     * 构建文件路径
     *
     * @param string $subdir
     * @param string $prefix
     *
     * @return string
     */
    public function _buildUploadPath(string $subdir = '', string $prefix = '')
    {
        $path = [];
        if ($prefix) {
            $path[] = $prefix;
        }

        $path[] = date('Ym');

        if ($subdir) {
            $path[] = is_int($subdir) ? $subdir % 10 : $subdir;
        }

        return '/' . implode('/', $path);
    }

    /**
     * 检查文件
     *
     * @param int  $fileid
     * @param bool $throw
     *
     * @return null|array|File
     */
    public function checkFile(int $fileid, bool $throw = true)
    {
        if (! $fileid || ! is_numeric($fileid)) {
            if ($throw) {
                throw new BusinessException(__('File is not existed.'));
            }

            return [];
        }

        $file = File::query()->find($fileid);

        if (! $file || ! $file->fileid || ! $file->memberid) {
            if ($throw) {
                throw new BusinessException(__('File is not existed.'));
            }

            return [];
        }

        return $file;
    }
}
