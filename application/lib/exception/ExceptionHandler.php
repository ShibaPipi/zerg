<?php
/**
 * Created by PhpStorm.
 * User: sunyaopeng
 * Date: 2018/7/6
 * Time: 12:53
 */

namespace app\lib\exception;


use think\exception\Handle;
use think\Log;
use think\Request;

class ExceptionHandler extends Handle
{
    private $code;

    private $msg;

    private $errorCode;

    // 需要返回客户端当前请求的 URL

    public function render(\Exception $e)
    {
        if ($e instanceof BaseException) {
            // 如果是自定义的异常
            $this->code = $e->code;
            $this->msg = $e->msg;
            $this->errorCode = $e->errorCode;
        } else {
            // 如果是开发模式，则使用 tp5 默认的验证机制
            if (config('app_debug')) {
                return parent::render($e);
            } else {
                $this->code = 500;
                $this->msg = 'Internal Server Error';
                $this->errorCode = 999;

                // 记录日志
                $this->recordErrorLog($e);
            }
        }

        $requestUrl = Request::instance()->url();

        $result = [
            'msg' => $this->msg,
            'error_code' => $this->errorCode,
            'request_url' => $requestUrl,
        ];

        return json($result, $this->code);
    }

    private function recordErrorLog(\Exception $e)
    {
        Log::init([
            'type' => 'File',
            'path'  => LOG_PATH,
            'level' => ['error'],
        ]);

        Log::record($e->getMessage(), 'error');
    }
}