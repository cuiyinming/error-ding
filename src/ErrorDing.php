<?php
/**
 * Created by PhpStorm.
 * User: jewdore
 * Date: 2018/6/19
 * Time: 下午2:24
 */

namespace Jewdore\ErrorDing;

class ErrorDing
{
    protected $config = null;

    public function __construct($config_arr)
    {
        $this->config = $config_arr;
    }

    public static function channel($config_name = 'self.error_handler')
    {
        $config = config($config_name);
        if (!$config || !isset($config['token'])) {
            return null;
        }

        if (isset($config['open']) && !$config['open']) {
            return null;
        }

        if(isset($config['env']) && $config['env'] != config('app.env')){
            return null;
        }
        if(!isset($config['env']) && config('app.env') != 'production'){
            return null;
        }

        return new static(config($config_name));
    }

    public function errorMsg($exception)
    {
        $str = config('app.name') . "发生异常(" . date('Y-m-d H:i:s') . ")：\n";
        $str .= '主机信息：' . gethostname() . "\n";
        $str .= '错误信息：' . $exception->getMessage() . "\n";
        $str .= '错误类型：' . get_class($exception) . "\n";
        $str .= '错误文件: ' . $exception->getFile() . "\n";
        $str .= '错误行号：' . $exception->getLine() . "\n";
        $str .= '错误代码：' . $exception->getCode() . "\n";
        $str .= '环境代码：' . config('app.env', 'unknown'). "\n";

        if(isset($this->config['error_log']) && is_file($this->config['error_log']) ){
             file_put_contents($this->config['error_log'],$str.$exception->getTraceAsString(),FILE_APPEND);
        }
        return $this->ding($str);

    }


    protected function ding($msg)
    {
        $dingTalk = new DingTalk($this->config['token']);
        if (isset($this->config['at']) && is_array($this->config['at'])) {
            return $dingTalk->sendTextMessage($msg, $this->config['at']);
        }
        return $dingTalk->sendTextMessage($msg);
    }


}