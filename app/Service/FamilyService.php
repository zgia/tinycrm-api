<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\BusinessException;
use App\Helper\Utils;
use App\Model\Family;

/**
 * Class FamilyService.
 */
class FamilyService
{
    /**
     * 亲属成员
     *
     * @param int $memberid
     *
     * @return array
     */
    public function getFamilies(int $memberid)
    {
        $families = Family::query()
            ->where('memberid', $memberid)
            ->get()
            ->toArray();

        foreach ($families as &$family) {
            $family['birthday'] = Utils::convertZeroDate($family['birthday']);

            unset($family['deleted']);
        }

        return $families;
    }

    /**
     * 创建新用户/编辑家属亲友
     *
     * @param array $data 家属亲友
     *
     * @return int 成功返回id，否则返回0
     */
    public function update(array $data)
    {
        $memberid = (int) $data['memberid'];

        if (! $memberid) {
            throw new BusinessException(__('Member is not existed.'));
        }

        $familyid = (int) $data['familyid'];
        if ($familyid) {
            $family = Family::query()->find($familyid);
        } else {
            $family = new Family();
        }

        $family->memberid = $memberid;
        $family->relationship = (int) $data['relationship'];
        $family->relationshipid = (int) $data['relationshipid'];
        $family->membername = (string) $data['membername'];
        $family->mobile = (string) $data['mobile'];
        $family->description = (string) $data['description'];
        $family->birthday = Utils::zeroDate($data['birthday']);

        $saved = $family->save();

        return $saved ? $family->familyid : 0;
    }
}
