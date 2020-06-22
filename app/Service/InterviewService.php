<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\BusinessException;
use App\Helper\NeoLog;
use App\Model\File;
use App\Model\Interview;
use App\Model\Member;
use Hyperf\DbConnection\Db;

/**
 * Class InterviewService.
 */
class InterviewService
{
    /**
     * 检查访谈记录
     *
     * @param int $interviewid
     *
     * @return null|array|\Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Builder[]|\Hyperf\Database\Model\Collection|\Hyperf\Database\Model\Model
     */
    public function checkInterview(int $interviewid)
    {
        if (! $interviewid) {
            return [];
        }

        $interview = Interview::query()->find($interviewid);

        if (! isset($interview->interviewid)) {
            throw new BusinessException('访谈不存在。');
        }

        return $interview;
    }

    /**
     * 获取某个客户的访谈记录.
     *
     * @param $memberid
     *
     * @return array
     */
    public function getInterviws($memberid)
    {
        $items = Db::table('interview')
            ->where('memberid', $memberid)
            ->orderBy('interviewday', 'DESC')
            ->get()
            ->toArray();

        $fileids = explode(',', implode(',', array_column($items, 'files')));

        $files = array_column(File::query()
            ->find($fileids, ['fileid', 'url']), 'url', 'fileid');

        $i = count($items);
        foreach ($items as &$item) {
            $item['files'] = $this->getInterviewImages($files, $item['files']);

            $br2nl = function ($str) {
                return preg_replace(['/<br(\s*)?\/?>/i', '/<\/p>/i'], "\n", $str);
            };

            $item['content'] = strip_tags($br2nl($item['content']));

            $item['sequence'] = $i--;

            unset($item['starttime'], $item['endtime']);
        }

        return $items;
    }

    /**
     * 添加或者编辑访谈记录.
     *
     * @param array $data          访谈记录
     * @param array $new_files     新文件
     * @param array $deleted_files 删除的文件
     *
     * @return int $interviewid
     */
    public function update(array $data, array $new_files, array $deleted_files)
    {
        $interview = $this->checkInterview((int) $data['interviewid']);

        NeoLog::info(__FUNCTION__, [$data, $new_files, $deleted_files]);

        $files = $interview['files'] ? explode(',', $interview['files']) : [];

        if ($deleted_files) {
            array_walk(
                $deleted_files,
                function (&$item) {
                    $item = str_ireplace(env('ABSURL', ''), '', $item);
                }
            );

            if ($files) {
                $oldFiles = array_column(File::query()
                    ->find($files, ['fileid', 'url']), 'url', 'fileid');

                $files = [];
                foreach ($oldFiles as $fileid => $url) {
                    if (in_array($url, $deleted_files)) {
                        File::destroy($fileid);
                    } else {
                        $files[] = $fileid;
                    }
                }
            }
        }

        if ($new_files) {
            array_walk(
                $new_files,
                function (&$item) {
                    $item = str_ireplace(env('ABSURL', ''), '', $item);
                }
            );

            NeoLog::info(__FUNCTION__, [$new_files, $deleted_files]);

            foreach ($new_files as $new_file) {
                if ($deleted_files && in_array($new_file, $deleted_files)) {
                    continue;
                }

                $_newFile = new File();
                $_newFile->memberid = $interview['memberid'];
                $_newFile->url = $new_file;
                $_newFile->description = "访谈@{$interview['interviewday']}";
                $_newFile->dateline = time();
                $saved = $_newFile->save();
                if ($saved) {
                    $files[] = $_newFile->fileid;
                }
            }
        }

        $interviewid = (int) $interview['interviewid'];

        $_newIv = new Interview();
        $_newIv->memberid = $interview['memberid'];
        $_newIv->interviewday = $interview['interviewday'];
        $_newIv->address = $interview['address'];
        $_newIv->content = trim($interview['content']);
        $_newIv->files = implode(',', $files);

        if ($interviewid) {
            $_newIv->interviewid = $interviewid;
        }

        $saved = $_newIv->save();
        if ($saved && ! $interviewid) {
            $interviewid = $_newIv->interviewid;
        }

        // 更新最后沟通时间
        $interview_dateline = stringToUtcTime("{$interview['interviewday']} 10:10:10");
        if ($interview['lastvisit'] < $interview_dateline) {
            $member = Member::query()->find($interview['memberid']);
            if ($member) {
                $member->lastvisit = $interview_dateline;
                $member->save();
            }
        }

        return $interviewid;
    }

    /**
     * 移除访谈的文件
     *
     * @param int    $interviewid
     * @param int    $fileid
     * @param string $url
     */
    public function deleteFiles(int $interviewid, int $fileid, $url = '')
    {
        if ($url) {
            @unlink(env('CONTENT_DIR', '') . $url);
        }

        $interview = $this->checkInterview($interviewid);

        if ($interview && $fileid) {
            $files = trim(str_replace(',' . $fileid . ',', ',', ',' . $interview['files'] . ','), ',');

            $interview->files = $files;
            $interview->save();

            FileService::deleteFile($fileid);
        }
    }

    /**
     * 生成访谈的缩略图和预览图（原图）
     *
     * @param array  $files
     * @param string $fileids
     *
     * @return array
     */
    protected function getInterviewImages(array $files, string $fileids)
    {
        if ($fileids) {
            $_files = [];
            foreach (explode(',', $fileids) as $fileid) {
                $file = ['url' => $files[$fileid]];

                FileService::makeImageGroup($file);

                $_files[] = $file;
            }

            $images = [
                'thumbnail' => array_column($_files, 'url_thumbnail'),
                'url' => array_column($_files, 'url'),
            ];
        } else {
            $images = [
                'thumbnail' => [],
                'url' => [],
            ];
        }

        return $images;
    }
}
