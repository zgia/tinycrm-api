<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\BusinessException;
use App\Service\FamilyService;
use App\Service\MemberService;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

/**
 * Class MemberController
 */
class MemberController extends AbstractController
{
    /**
     * 客户服务
     *
     * @Inject
     * @var MemberService
     */
    private $memberService;

    /**
     * 客户服务
     *
     * @Inject
     * @var FamilyService
     */
    private $familyService;

    /**
     * 获取用户状态列表
     *
     * @return PsrResponseInterface
     */
    public function statuslist()
    {
        $data = MemberService::statusData();

        return $this->success($data);
    }

    /**
     * 客户列表
     *
     * @return PsrResponseInterface
     */
    public function index()
    {
        $req = $this->request->all();

        $members = $this->memberService->getMembers($req);

        return $this->success($members);
    }

    /**
     * 查看用户
     *
     * @param int $memberid 用户ID
     *
     * @return PsrResponseInterface
     */
    public function view($memberid = 0)
    {
        $memberid = (int) $memberid;

        $member = $this->memberService->checkMember($memberid, false);

        //$member = '{"userid":"1","membername":"\u5f20\u4e09\u4e30","mobile":"13466779858","tel":"13901295207","birthday":"2009-02-25","lastvisit":"1543725931","dateline":"0","deleted":"0","signed_status":"0","saler_status":"26","gender":"1","marital_status":"16","idcard":"320827197909120873","personality":"\u4e50\u89c2","financing_habit":"\u8c28\u614e","hobby":"\u5c0f\u8bf4","home_address":"\u6d77\u8fd0\u4ed3","home_address_postcode":"100000","company":"\u6211\u7231\u5c0f\u57ce","company_title":"\u6280\u672f\u603b\u76d1","company_address":"\u4eae\u9a6c\u6865","company_address_postcode":"123432","annual_income":"40","description":"\u8fd9\u51e0\u4e2a\u5bb6\u4f19\u5927\u5927\r\n\u6d6e\u70b9\u6570\u8270\u82e6\u594b\u6597\u5c31\u5f00\u59cb\r\n\u5272\u53d1\u4ee3\u9996\u8be5\u53d1\u7684"}';
        //$member = json_decode($member, true);

        // 标签
        if ($member) {
            $member['tags'] = $this->memberService->getTags($memberid);

            $this->memberService->processMemberData($member);
        } else {
            $member['tags'] = [];
            $member['saler'] = 0;
            $member['signed'] = 0;
            $member['marital'] = 0;
            $member['gender'] = 0;
            $member['birthday'] = '';
        }

        return $this->success(['member' => $member]);
    }

    /**
     * 更新用户信息
     *
     * @throws BusinessException
     * @return PsrResponseInterface
     */
    public function update()
    {
        $memberid = (int) $this->request->input('memberid', 0);

        $member = $this->request->input('member', []);

        $old = $this->memberService->checkMember($memberid, $memberid ? true : false);

        $memberid = $this->memberService->update($old, $member);

        if ($memberid) {
            return $this->success(['memberid' => $memberid], '成功保存客户');
        }

        throw new BusinessException('保存客户错误，请重试。');
    }

    /**
     * 删除客户
     *
     * @param $memberid
     *
     * @throws \Exception
     * @return PsrResponseInterface
     */
    public function delete($memberid)
    {
        $memberid = (int) $memberid;

        $this->memberService->checkMember($memberid);

        $this->memberService->delete($memberid);

        return $this->success([], '删除客户成功');
    }

    /**
     * 用户亲属
     *
     * @param int $memberid 用户ID
     *
     * @return PsrResponseInterface
     */
    public function family($memberid)
    {
        $memberid = (int) $memberid;

        $this->memberService->checkMember($memberid);

        // 家庭
        $families = $this->familyService->getFamilies($memberid);

        return $this->success(['families' => $families]);
    }

    /**
     * 更新用户亲属信息
     *
     * @return PsrResponseInterface
     */
    public function updatefamily()
    {
        $family = $this->request->input('family', []);

        $this->memberService->checkMember($family['memberid']);

        $familyid = $this->familyService->update($family);

        if ($familyid) {
            return $this->success(['familyid' => $familyid], '成功保存亲属');
        }

        return $this->fail('亲属保存失败');
    }
}
