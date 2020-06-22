<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\BusinessException;
use App\Helper\Utils;
use App\Model\Birthday;
use App\Model\Member;
use App\Model\Membertag;
use Hyperf\DbConnection\Db;
use Hyperf\Utils\Str;

/**
 * Class MemberService.
 */
class MemberService
{
    /**
     * 会员状态数据列表
     *
     * @return array
     */
    public static function statusData()
    {
        $signedList = SystemService::getCategoryByAlias('signed_status');
        $salerList = SystemService::getCategoryByAlias('saler_status');
        $maritalList = SystemService::getCategoryByAlias('marital_status');
        $tagList = SystemService::getCategoryByAlias('member_tag');

        $signedList = [0 => '不确定'] + $signedList;
        $salerList = [0 => '不确定'] + $salerList;
        $maritalList = [0 => '不确定'] + $maritalList;

        $genderList = SystemService::miscParams('gender');
        $genderList = [0 => '不确定'] + $genderList;

        $relationshipList = SystemService::getCategoryByAlias('family_relationship');

        return compact(
            'signedList',
            'salerList',
            'maritalList',
            'tagList',
            'genderList',
            'relationshipList'
        );
    }

    /**
     * 按照条件获取客户
     *
     * @param array $req
     *
     * @return array
     */
    public function getMembers(array $req)
    {
        $memberid = (int) $req['memberid'];
        $membername = $req['membername'];
        $signedstatus = (int) $req['signedstatus'];
        $salerstatus = (int) $req['salerstatus'];
        $ivt = $req['ivt'];

        $perpage = getOption('perpage', 20);

        $conditions = [
            'userid' => currentUserId(),
            'deleted' => 0,
        ];

        if ($memberid) {
            $conditions['member.memberid'] = $memberid;
        } else {
            if ($membername) {
                // 搜索某个月份的生日用户
                if (Str::startsWith($membername, 'm:')) {
                    $where = [
                        'userid' => currentUserId(),
                        'm' => (int) substr($membername, 2),
                        'deleted' => 0,
                    ];
                } else {
                    $where = [
                        'userid' => currentUserId(),
                        'membername LIKE' => $membername,
                        'deleted' => 0,
                    ];
                }
                $useridList = Db::table('birthday')
                    ->select(['memberid'])
                    ->where($where)
                    ->forPage(currentPage(), $perpage)
                    ->orderBy('memberid', 'DESC')
                    ->get()
                    ->toArray();

                if ($useridList) {
                    $conditions['member.memberid'] = $useridList;
                } else {
                    $conditions['member.memberid'] = 0;
                }
            }
        }

        if ($signedstatus) {
            $conditions['signed_status'] = $signedstatus;
        }
        if ($salerstatus) {
            $conditions['saler_status'] = $salerstatus;
        }
        if ($ivt) {
            $conditions['lastvisit >='] = time() - $ivt * SECONDS_MONTH;
        }

        // 获取用户
        $memberList = Db::table('member')
            ->select()
            ->where($conditions)
            ->forPage(currentPage(), getOption('perpage', 20))
            ->orderBy('memberid', 'DESC')
            ->get()
            ->toArray();

        $userCount = Db::table('member')
            ->select()
            ->where($conditions)
            ->count();

        // 分页数量
        $pageNum = ceil($userCount / $perpage);

        $memberidList = array_column($memberList, 'memberid');

        $statusList = $this->statusData();

        // 标签
        $_memberTags = Membertag::query()->find(['memberid' => $memberidList]);
        $memberTags = [];
        foreach ($_memberTags as $mt) {
            $memberTags[$mt['memberid']][] = $statusList['tagList'][$mt['tagid']];
        }

        foreach ($memberList as &$member) {
            $this->processMemberData($member);

            $member['tags'] = implode(', ', $memberTags[$member['memberid']] ?? []);
            $member['gender_title'] = $member['gender'] ? $statusList['genderList'][$member['gender']] : '👤';
        }

        return [
            'members' => array_values($memberList),
            'p' => currentPage(),
            'noMoreMembers' => currentPage() >= $pageNum,
        ];
    }

    /**
     * 获取用户信息
     *
     * @param int $memberid 用户ID
     *
     * @return array
     */
    public function getMember(int $memberid)
    {
        $memberid = (int) $memberid;

        if ($memberid) {
            $member = Member::query()
                ->where(['memberid' => $memberid, 'userid' => currentUserId()])
                ->first()
                ->toArray();
        } else {
            $member = Member::emptyMember();
        }

        return $member;
    }

    /**
     * 检查客户
     *
     * @param int  $memberid
     * @param bool $throw
     *
     * @throws BusinessException
     * @return array
     */
    public function checkMember(int $memberid, bool $throw = true)
    {
        if (! $memberid || ! is_numeric($memberid)) {
            if ($throw) {
                throw new BusinessException(__('Member is not existed.'));
            }
            return [];
        }

        $member = $this->getMember($memberid);

        if (! $member['memberid']) {
            if ($throw) {
                throw new BusinessException(__('Member is not existed.'));
            }
            return [];
        }

        $member['birthday'] = Utils::convertZeroDate($member['birthday']);

        return $member;
    }

    /**
     * 客户信息预处理
     *
     * @param array $member
     */
    public function processMemberData(array &$member)
    {
        $member['saler'] = (int) $member['saler_status'];
        $member['signed'] = (int) $member['signed_status'];
        $member['marital'] = (int) $member['marital_status'];
        $member['gender'] = (int) $member['gender'];

        unset($member['saler_status'], $member['signed_status'], $member['marital_status'], $member['lastvisit'], $member['userid'], $member['deleted'], $member['dateline']);
    }

    /**
     * 创建新用户/编辑用户
     *
     * @param array $oldMember 用户原信息
     * @param array $member    新用户信息
     *
     * @throws BusinessException
     * @return string            成功返回id，否则返回0
     */
    public function update(array $oldMember, array $member)
    {
        $member['saler_status'] = (int) $member['saler'];
        $member['signed_status'] = (int) $member['signed'];
        $member['marital_status'] = (int) $member['marital'];

        unset($member['saler'], $member['signed'], $member['marital']);

        if ($member['birthday'] && ! preg_match('/\d{4}-\d{1,2}-\d{1,2}/', $member['birthday'])) {
            throw new BusinessException('错误的生日格式。');
        }
        $member['birthday'] = Utils::zeroDate($member['birthday']);

        $tags = [];
        if (isset($member['tags'])) {
            $tags = (array) $member['tags'];
            unset($member['tags']);
        }

        if (isset($oldMember['memberid'])) {
            // 更新用户信息
            $newMember = Member::query()->find($oldMember['memberid']);
        } else {
            // 新用户
            $newMember = new Member();

            $newMember->dateline = time();
            $newMember->userid = currentUserId();
        }

        foreach ($member as $k => $v) {
            $newMember->{$k} = $v;
        }

        $saved = $newMember->save();

        if ($saved) {
            if ($tags) {
                $this->saveTags($tags, $newMember->memberid);
            }

            $this->updateBirthday($newMember->memberid, $member['birthday']);

            // 保存日志
            $log = [
                'type' => 'user',
                'action' => 'update',
                'id' => $newMember->memberid,
                'from' => array_diff_assoc($oldMember, $member),
                'to' => array_diff_assoc($member, $oldMember),
            ];
            actionLog($log);
        }

        return $saved ? $newMember->memberid : 0;
    }

    /**
     * 删除客户
     *
     * @param int $memberid
     *
     * @throws \Exception
     */
    public function delete($memberid)
    {
        $member = Member::query()->find($memberid);

        $member->deleted = 1;

        $saved = $member->save();

        if ($saved) {
            Birthday::query()->find('memberid', $memberid)->delete();
        }
    }

    /**
     * 用户标签.
     *
     * @param int $memberid
     *
     * @return \Hyperf\Database\Model\Builder[]|\Hyperf\Database\Model\Collection
     */
    public function getTags(int $memberid)
    {
        $tags = Membertag::query()
            ->where('memberid', $memberid)
            ->get('tagid')
            ->toArray();

        return array_column($tags, 'tagid');
    }

    /**
     * 标签
     *
     * @param array $membertags 标签
     * @param int   $memberid   用户ID
     *
     * @throws BusinessException
     */
    public function saveTags(array $membertags, int $memberid)
    {
        try {
            Membertag::query()->find('memberid', $memberid)->delete();

            foreach ($membertags as $tagid) {
                $mt = new Membertag();
                $mt->memberid = $memberid;
                $mt->tagid = $tagid;
                $mt->userid = currentUserId();

                $mt->save();
            }
        } catch (\Exception $ex) {
            throw new BusinessException($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * 更新生日，便于统计
     *
     * @param int    $memberid
     * @param string $birthday
     *
     * @throws BusinessException
     */
    public function updateBirthday($memberid, $birthday)
    {
        if (! $birthday || $birthday[0] == '0') {
            return;
        }

        try {
            Birthday::query()->find('memberid', $memberid)->delete();

            $ymd = str_ireplace('-', '', $birthday);

            $birthday = new Birthday();
            $birthday->userid = currentUserId();
            $birthday->memberid = $memberid;
            $birthday->ymd = (int) $ymd;
            $birthday->y = (int) substr($ymd, 0, 4);
            $birthday->m = (int) substr($ymd, 4, 2);
            $birthday->ym = (int) substr($ymd, 0, 6);
            $birthday->md = (int) substr($ymd, 4, 4);

            $birthday->save();
        } catch (\Exception $ex) {
            throw new BusinessException($ex->getMessage(), $ex->getCode());
        }
    }
}
