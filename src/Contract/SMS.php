<?php
/**
 * Created by PhpStorm.
 * User: ALG
 * Date: 2019/3/22
 * Time: 10:42
 */

namespace Alg\SMS\Contract;

use Illuminate\Support\Facades\Redis;

abstract class SMS
{
    /**
     * 验证码不存在
     */
    const VERIFICATION_CODE_DOES_NOT_EXIST = 0;

    /**
     * 验证码不一致
     */
    const VERIFICATION_CODE_INCONSISTENCY = -1;

    /**
     * 验证码检验通过
     */
    const CALIBRATION_PASS = 1;

    /**
     * 发送失败的错误消息
     *
     * @var
     */
    protected $sendError;

    /**
     * 获得发送错误信息
     *
     * @return mixed
     */
    public function getSendError()
    {
        return $this->sendError;
    }

    /**
     * 验证手机号码和填写的信息是否对应
     *
     * @param        $tel
     * @param        $code
     * @param string $cacheKey
     *
     * @return int
     */
    public function check($tel, $code, $cacheKey = 'code')
    {
        $prefix = config('sms.prefix');
        $cache = Redis::get($prefix . ':' . $tel.$cacheKey);
        if (empty($cache)) {
            return self::VERIFICATION_CODE_DOES_NOT_EXIST;
        }
        if ($cache != $code) {
            return self::VERIFICATION_CODE_INCONSISTENCY;
        };
        return self::CALIBRATION_PASS;
    }

    /**
     * 清除手机的缓存信息
     *
     * @param $phone
     * @param $cacheKey
     */
    public function clearCache($phone, $cacheKey)
    {
        $prefix = config('sms.prefix');
         Redis::del($prefix . ':' . $phone.$cacheKey);
    }

    /**
     * 根据配置短信验证码的长度获得随机验证码
     *
     * @return array
     */
    public function getRandCode()
    {
        $length = config('sms.length');
        $result = [];
        for ($i = 0; $i < $length; $i++) {
            $result[] = rand(0, 9);
        }
        $result = implode('', $result);
        return $result;
    }

    /**
     * todo 需要实现send方法
     *
     * @param $tel
     *
     * @return mixed
     */
    abstract function send(string $tel);

    /**
     * 模板递归替换
     *
     * @param array  $param       模板参数
     * @param string $templateSTR 替换模板
     *
     * @return mixed
     */
    public function templateReplace(array $param, string $templateSTR)
    {
        $templateParam = array_splice($param, 0, 1);
        if (empty($templateParam)) {
            return $templateSTR;
        }
        //得到键名, 键名作为替换模板的依据
        $varName = array_keys($templateParam)[0];
        //替换值
        $varValue = $templateParam[$varName];
        $templateSTR = str_replace('{' . $varName . '}', $varValue, $templateSTR);
        return $this->templateReplace($param, $templateSTR);
    }

    /**
     * 执行发送命令
     *
     * @param string $url 请求网址
     *
     * @return mixed
     */
    protected function execSend(string $url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "x-sdk-client" => "php/2.0.0"
        ));
        if (substr($url, 0, 5) == 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        $rtn = curl_exec($ch);

        if ($rtn === false) {
            trigger_error("[CURL_" . curl_errno($ch) . "]: " . curl_error($ch), E_USER_ERROR);
        }
        curl_close($ch);
        return $rtn;
    }

    /**
     * 检测是否发送成功
     *
     * @param $response
     *
     * @return mixed
     */
    abstract protected function checkSend($response);
}