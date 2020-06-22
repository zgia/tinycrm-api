<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\BusinessException;
use App\Model\Contract;

/**
 * Class ContractService.
 */
class ContractService
{
    /**
     * 获取某个会员的所有签约记录
     *
     * @param int $memberid
     *
     * @return array
     */
    public function getContracts(int $memberid)
    {
        return Contract::query()
            ->where('memberid', $memberid)
            ->orderBy('contractid', 'DESC')
            ->get()
            ->toArray();
    }

    /**
     * 检查签约记录
     *
     * @param $contractid
     *
     * @return null|array|\Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Builder[]|\Hyperf\Database\Model\Collection|\Hyperf\Database\Model\Model
     */
    public function checkContract(int $contractid)
    {
        if (! $contractid) {
            return [];
        }

        $contract = Contract::query()->find($contractid);

        if (! isset($contract->contractid)) {
            throw new BusinessException('签约不存在。');
        }

        return $contract;
    }

    /**
     * 更新签约记录
     *
     * @param array $data
     *
     * @return int
     */
    public function update(array $data)
    {
        $contract = $this->checkContract((int) $data['contractid']);

        if (! $contract) {
            $contract = new Contract();
            $contract->memberid = (int) $data['memberid'];
        }

        $contract->signed_day = $data['signed_day'];
        $contract->insurance_title = $data['insurance_title'];
        $contract->insured_amount = $data['insured_amount'];
        $contract->premium = $data['premium'];
        $contract->policy_holder = $data['policy_holder'];
        $contract->recognizee = $data['recognizee'];

        $saved = $contract->save();

        return $saved ? $contract->contractid : 0;
    }
}
