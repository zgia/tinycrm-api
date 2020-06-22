<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\BusinessException;
use App\Service\ContractService;
use App\Service\MemberService;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

/**
 * Class ContractController
 */
class ContractController extends AbstractController
{
    /**
     * 客户服务
     *
     * @Inject
     * @var MemberService
     */
    private $memberService;

    /**
     * 签约服务
     *
     * @Inject
     * @var ContractService
     */
    private $contractService;

    /**
     * @param int $memberid
     *
     * @return PsrResponseInterface
     */
    public function index($memberid = 0)
    {
        $memberid = (int) $memberid;

        $this->memberService->checkMember($memberid);

        $items = $this->contractService->getContracts($memberid);

        return $this->success(['contracts' => $items]);
    }

    /**
     * 添加/编辑
     *
     * @param int $memberid   ID
     * @param int $contractid ID
     *
     * @return PsrResponseInterface
     */
    public function edit($memberid, $contractid = 0)
    {
        $memberid = (int) $memberid;

        $this->memberService->checkMember($memberid);

        $contract = $this->contractService->checkContract($contractid);

        return $this->success(['contract' => $contract ? $contract->toArray() : []]);
    }

    /**
     * 更新信息
     *
     * @return PsrResponseInterface
     */
    public function update()
    {
        $data = $this->request->input('contract', []);

        $this->memberService->checkMember((int) $data['memberid']);

        $contractid = $this->contractService->update($data);

        if ($contractid) {
            return $this->success(['contractid' => $contractid], '签约记录保存成功');
        }

        throw new BusinessException('签约记录保存错误，请重试。');
    }
}
