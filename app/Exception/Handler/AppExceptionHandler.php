<?php

declare(strict_types=1);

namespace App\Exception\Handler;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class AppExceptionHandler extends ExceptionHandler
{
    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(StdoutLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $msg = $throwable->getMessage();

        $this->logger->error(sprintf('%s[%s] in %s', $msg, $throwable->getLine(), $throwable->getFile()));
        $this->logger->error($throwable->getTraceAsString());

        $data = [
            'code' => I_FAILURE,
            'msg' => $msg,
            'data' => [
                'file' => str_ireplace(BASE_PATH, '', $throwable->getFile()),
                'line' => $throwable->getLine(),
            ],
        ];
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);

        return $response
            ->withAddedHeader('content-type', 'application/json; charset=utf-8')
            ->withBody(new SwooleStream($json));
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
