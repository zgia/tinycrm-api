<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\BusinessException;
use App\Service\InterviewService;
use App\Service\MemberService;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

/**
 * Class InterviewController
 */
class InterviewController extends AbstractController
{
    /**
     * 客户服务
     *
     * @Inject
     * @var MemberService
     */
    private $memberService;

    /**
     * 访谈服务
     *
     * @Inject
     * @var InterviewService
     */
    private $interviewService;

    /**
     * 访谈列表页
     *
     * @param int $memberid
     *
     * @return PsrResponseInterface
     */
    public function index($memberid = 0)
    {
        $memberid = (int) $memberid;
        $this->memberService->checkMember($memberid);

        $items = $this->interviewService->getInterviws($memberid);

        return $this->success(['interviews' => array_values($items)]);
    }

    /**
     * 更新信息
     *
     * @return PsrResponseInterface
     */
    public function update()
    {
        $data = $this->request->input('interview', []);
        // 新加文件
        $new_files = $this->request->input('new_files', []);
        // 删除文件
        $deleted_files = $this->request->input('deleted_files', []);

        $this->memberService->checkMember((int) $data['memberid']);

        $interviewid = $this->interviewService->update($data, $new_files, $deleted_files);

        if ($interviewid) {
            return $this->success(['interviewid' => $interviewid], '成功保存访谈');
        }

        throw new BusinessException('保存访谈错误，请重试。');
    }

    /**
     * 删除文件
     *
     * @param int $interviewid
     * @param int $fileid
     *
     * @return PsrResponseInterface
     */
    public function deletefile($interviewid = 0, $fileid = 0)
    {
        $url = $this->request->input('url', '');

        $this->interviewService->deleteFiles($interviewid, $fileid, $url);

        return $this->success();
    }
}
