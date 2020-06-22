<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\BusinessException;
use App\Service\FileService;
use App\Service\MemberService;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

/**
 * Class FileController
 */
class FileController extends AbstractController
{
    /**
     * 客户服务
     *
     * @Inject
     * @var MemberService
     */
    private $memberService;

    /**
     * 文件服务
     *
     * @Inject
     * @var FileService
     */
    private $fileService;

    /**
     * @param int $memberid
     *
     * @return PsrResponseInterface
     */
    public function index($memberid = 0)
    {
        $memberid = (int) $memberid;
        $this->memberService->checkMember($memberid);

        $files = $this->fileService->getFiles($memberid);

        return $this->success(['files' => $files ?: new \stdClass()]);
    }

    /**
     * 保存文件
     */
    public function update()
    {
        $memberid = (int) $this->request->input('memberid', 0);
        $desc = (string) $this->request->input('description', '');
        $new_files = (array) $this->request->input('new_files', []);
        $deleted_files = (array) $this->request->input('deleted_files', []);

        $this->memberService->checkMember($memberid);

        if (! $new_files) {
            return $this->success([], '没有文件');
        }

        $this->fileService->update($memberid, $new_files, $deleted_files, $desc);

        return $this->success([], '成功上传文件');
    }

    /**
     * 更新备注
     *
     * @return PsrResponseInterface
     */
    public function updateDescription()
    {
        $fileid = (int) $this->request->input('fileid', 0);
        $desc = (string) $this->request->input('description', '');

        $updated = $this->fileService->updateDescription($fileid, $desc);

        if ($updated) {
            return $this->success([], '成功编辑备注');
        }

        throw new BusinessException('编辑文件备注错误，请重试');
    }

    /**
     * 删除文件
     *
     * @param int $fileid
     *
     * @return PsrResponseInterface
     */
    public function delete($fileid = 0)
    {
        FileService::deleteFile((int) $fileid);

        return $this->success([], '成功删除文件');
    }
}
