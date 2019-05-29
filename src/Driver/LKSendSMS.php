<?php
/**
 * Created by PhpStorm.
 * User: ALG
 * Date: 2019/3/4
 * Time: 19:17
 */

namespace Alg\SMS\Driver;

use \Alg\SMS\Contract\SMS;
use Illuminate\Support\Facades\Redis;

class LKSendSMS extends SMS
{
    /**
     * 模板参数
     *
     * @var array
     */
    private $templateParam = [];

    /**
     * 发送模板短信
     *
     * @param string $tel 接受短信的手机号码
     *
     * @return bool|mixed
     * @throws \Exception
     */
    public function send(string $tel)
    {
        $url = $this->getRequestAddress($tel, $this->getMsg($tel));
        $response = $this->execSend($url);
        return $this->checkSend($response);
    }

    /**
     * 是否发送成功
     *
     * @param $response
     *
     * @return bool
     */
    protected function checkSend($response)
    {
        $error = [
            "–1" => "账号未注册",
            "–2" => "网络访问超时，请稍后再试",
            "–3" => "帐号或密码错误",
            "–4" => "只支持单发",
            "–5" => "余额不足，请充值",
            "–6" => "定时发送时间不是有效的时间格式",
            "–7" => "提交信息末尾未加签名，请添加中文的企业签名【 】或未采用gb2312编码",
            "–8" => "发送内容需在1到300字之间",
            "–9" => "发送号码为空",
            "-10" => "定时时间不能小于系统当前时间",
            "-11" => "屏蔽手机号码",
            "-101" => "调用接口速度太快",
        ];
        if ((int)$response > 0) {
            return true;
        } else {
            $this->sendError = $error[$response];
            return false;
        }
    }

    /**
     * 获得请求地址
     *
     * @param string $tel 接受消息的电话号码
     * @param string $msg 发送的消息
     *
     * @return string
     * @throws \Exception
     */
    private function getRequestAddress(string $tel, string $msg)
    {
        return config('sms.drive.lk.domain') . '?CorpID=' .
            config('sms.drive.lk.corpID') .
            '&Pwd=' . config('sms.drive.lk.pwd') .
            '&Mobile=' . $tel . '&Content=' . $msg . '&Cell=&SendTime=';
    }

    /**
     * 获得消息模板内容
     *
     * @param $tel
     *
     * @return string
     * @throws \Exception
     */
    private function getMsg($tel)
    {
        //模板内容
        $templateSTR = config('sms.templateStore')[config('sms.drive.lk.useTemplate')];
        //模板参数
        $param = $this->getTemplateParam($tel);
        //模板内容替换
        $templateSTR = $this->templateReplace($param, $templateSTR) . "【" . config('sms.drive.lk.signName') . "】";
        return rawurlencode(mb_convert_encoding($templateSTR, "gb2312", "utf-8"));
    }

    /**
     * 获得模板参数
     *
     * @return array
     * @throws \Exception
     */
    private function getTemplateParam($tel)
    {
        if (empty($this->templateParam)) {
            throw new \Exception('没有设置模板参数');
        }

        $prefix = config('sms.prefix');
        $arrayLength = count($this->templateParam);

        for ($i = 1; $i < $arrayLength; $i++) {
            if (key_exists('cache_key' . $i, $this->templateParam)) {
                $needCacheData = $this->templateParam[$this->templateParam['cache_key' . $i]];
                Redis::setex($prefix . ':' . $tel . $this->templateParam['cache_key' . $i], config('sms.EXPIRE'), $needCacheData);
                unset($this->templateParam['cache_key' . $i]);
            }
        }

        return $this->templateParam;
    }

    /**
     * 设置模板参数
     *
     * @param array $param
     *
     * @return $this
     */
    public function setTemplateParam(array $param)
    {
        $this->templateParam = $param;
        return $this;
    }

    /**
     * 使用模板
     *
     * @param string $templateName
     *
     * @return $this
     */
    public function useTemplate(string $templateName)
    {
        config(['sms.drive.lk.useTemplate' => $templateName]);
        return $this;
    }
}