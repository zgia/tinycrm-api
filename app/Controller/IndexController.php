<?php

declare(strict_types=1);

namespace App\Controller;

use App\Helper\Utils;
use App\Model\Birthday;
use App\Model\Interview;
use App\Model\Member;
use App\Service\MemberService;
use App\Service\UserService;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

/**
 * Class IndexController
 */
class IndexController extends AbstractController
{
    /**
     * @return PsrResponseInterface
     */
    public function index()
    {
        $userid = 1;//$this->request->input('userid', currentUserId());
        $method = $this->request->getMethod();

        $user = UserService::getUser($userid);

        $data = [
            'method' => $method,
            'user' => $user,
            'userid' => 0,
            'page' => currentPage(),
        ];

        return $this->success('', $data);
    }

    /**
     * 用户信息
     *
     * @return PsrResponseInterface
     */
    public function user()
    {
        $user = UserService::getUser();
        $data = [
            'userid' => $user['userid'],
            'username' => $user['username'],
            'realname' => $user['realname'],
            'actived' => $user['actived'],
        ];

        return $this->success('',$data);
    }

    /**
     * 数据预拉取
     *
     * @return PsrResponseInterface
     */
    public function backgroundFetchData()
    {
        $data = [
            'statuslist' => MemberService::statusData(),
            'config' => [
                'file_image_type' => getOption('fileimagetype'),
                'file_allow_type' => getOption('fileallowtype'),
                'file_max_size' => getOption('filemaxsize'),
            ],
        ];

        return $this->success('世界真奇妙。', $data);
    }

    /**
     * 一些统计
     *
     * @return PsrResponseInterface
     */
    public function statistic()
    {
        $members = Member::query()
            ->where(
                [
                    'userid' => currentUserId(),
                    'deleted' => 0,
                ]
            )
            ->get(['memberid'])
            ->toArray();
        $members = array_map('intval', array_column($members, 'memberid'));
        // 客户总数
        $memberCount = count($members);

        // 访谈次数
        $interviewCount = Interview::query()
            ->where('memberid', $members)
            ->count('interviewid');

        // 今天
        $today = formatDate('Y-m-d');

        // 今天生日客户
        $todayMembers = Birthday::query()
            ->where(
                [
                    'userid' => currentUserId(),
                    'md' => formatDate('nd'),
                ]
            )
            ->get('memberid')
            ->toArray();

        if ($todayMembers) {
            $todayMembers = array_map('intval', array_column($todayMembers, 'memberid'));

            $todayBirthday = Member::query()
                ->where(
                    [
                        'userid' => currentUserId(),
                        'memberid IN' => array_map('intval', $todayMembers),
                        'deleted' => 0,
                    ]
                )
                ->get(['memberid', 'membername', 'birthday'])
                ->toArray();

            foreach ($todayBirthday as &$b) {
                $b['age'] = Utils::age($b['birthday']);
            }

            $todayTotal = count($todayBirthday);
        } else {
            $todayBirthday = [];
            $todayTotal = 0;
        }

        return $this->success('', compact(
            'memberCount',
            'interviewCount',
            'today',
            'todayBirthday',
            'todayTotal'
        ));
    }

    /**
     * 每个月过生日的客户
     *
     * @return PsrResponseInterface
     */
    public function yearbirthday()
    {
        $yb = Birthday::query()
            ->where(
                [
                    'userid' => currentUserId(),
                ]
            )
            ->groupBy('m')
            ->get(['m', 'COUNT(memberid) AS total'])
            ->toArray();
        $yb = array_column($yb, 'total', 'm');

        $year = [];
        for ($i = 1; $i < 13; ++$i) {
            $year[] = ['month' => $i, 'total' => (int) $yb[$i]];
        }

        return $this->success('', compact('year'));
    }
}
