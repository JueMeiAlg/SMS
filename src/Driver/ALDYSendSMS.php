<?php
/**
 * Created by PhpStorm.
 * User: ALG
 * Date: 2018/11/29
 * Time: 10:14
 */

namespace Alg\SMS\Driver;

use \Alg\SMS\Contract\SMS;
use Illuminate\Support\Facades\Redis;
use Mockery\Exception;

/**
 * 阿里大鱼发送短信服务
 *
 * Class ALDYSendSMS
 *
 * @category ALDYSendSMS
 * @package  App\Services\SMS
 * @author   ALG <513051043@qq.com>
 * @license  四川猪太帅科技公司 http://www.51zts.com
 * @link     接口文档链接
 */
class ALDYSendSMS extends SMS
{
    /**
     * 模板参数
     *
     * @var array
     */
    private $templateParam = [];

    private $accessKeyId, $accessKeySecret, $signName, $domain;

    /**
     * 得到一些初始不变的值
     *
     * ALDYSendSMS constructor.
     */
    public function __construct()
    {
        $this->accessKeyId = config('sms.drive.aldy.accessKeyId');
        $this->accessKeySecret = config('sms.drive.aldy.accessKeySecret');
        $this->signName = config('sms.drive.aldy.signName');
        $this->domain = config('sms.drive.aldy.domain');
    }

    /**
     * 发送短信内容
     *
     * @param $tel
     *
     * @return bool|mixed|\stdClass
     * @throws \Exception
     */
    public function send(string $tel)
    {
        $params = [];

        //接受内容的手机号
        $params["PhoneNumbers"] = $tel;

        //短信签名
        $params["SignName"] = $this->signName;

        // 短信模板Code
        $params["TemplateCode"] = config('sms.drive.aldy.templateCode');

        //模板参数
        $params['TemplateParam'] = $this->getTemplateParam($tel);

        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
        if (!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }
        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
        $helper = new SignatureHelper();
        // 此处可能会抛出异常，注意catch
        $content = $helper->request(
            $this->accessKeyId,
            $this->accessKeySecret,
            $this->domain,
            array_merge($params, array(
                "RegionId" => "cn-hangzhou",
                "Action" => "SendSms",
                "Version" => "2017-05-25",
            ))
        );
        return $this->checkSend($content);
    }

    /**
     * 检测是否发送成功
     *
     * @param $response
     *
     * @return bool
     */
    protected function checkSend($response)
    {
        if ($response->Message == 'OK') {
            return true;
        } else {
            $this->sendError = $response->Message;
            return false;
        }
    }

    /**
     * 获得模板参数
     *
     * @param $tel
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
                $needCacheData =  $this->templateParam[$this->templateParam['cache_key' . $i]];
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
        config(['sms.drive.aldy.templateCode' => $templateName]);
        return $this;
    }
}