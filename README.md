# Laravel 短信包

## 支持短信服务商:阿里大鱼,凌凯短信

本扩展包发送验证码需要redis环境,请确保运行环境中有redis正常运行

如果你的laravel 小于5.5则需要 `\Alg\SMS\SMSServiceProvider` 吧他加入 config\app provider中,大于5.5则忽略这个过程

发布配置文件

`php artisan vendor:publish --provider="Alg\SMS\SMSServiceProvider" 
`
#### 调用案例
```$xslt
class SMSServices
{
    private $smsServices = 'lk'; //短信服务商

    private $sms; //具体发送短信的服务对象

    public function __construct()
    {
        $this->sms = App::make('sms')->get($this->smsServices);
    }

    /**
     * 发送短信方法
     *
     * @param string $phone       接受短信的手机号
     * @param string $useTemplate 使用的短信模板
     *
     * @return mixed
     */
    public function send($phone, $useTemplate = 'login')
    {
        $templateParamFuncName = $useTemplate . 'SMSTemplateParam';
        $templateParam = $this->$templateParamFuncName(); //获得模板参数

        return $this->sms->useTemplate($useTemplate)
            ->setTemplateParam($templateParam)
            ->send($phone);
    }


    /**
     * 登录短信模板参数
     *
     * @return array
     */
    public function loginSMSTemplateParam()
    {
        return [
            'code' => $this->sms::getRandCode(),
            'time' => date('i', config('sms.EXPIRE')),
            'cache_key1' => 'code' //需要被缓存的键,可以继续增加 如cache_key2...
        ];
    }

}
```

#### 常用方法
```$xslt
$SMS::getRandCode()  获得预设置长度的随机数 return in
```
```$xslt
$SMS::useTemplate(string 'templateName')  使用指定模板 return $this
```
```$xslt
$SMS::setTemplateParam(array $param)  设置模板参数 return $this
```
```$xslt
$SMS::send(string $tel)  发送短信通知 return bool
```

```$xslt
$SMS::check(string $tel string $code) 
    check只是提供一个快速检测方法, 他检测检验码你传入的参数 需要有一个 code 键  才行,如果是需要检测其他键 需要自己实现一个check
    检测验证码是否一致  return int
    return 1 表示结果一致检验通过,
    return 0 验证码不存在,
    return -1 验证码不一致
```
```$xslt
$SMS::getSendError()  获取发送错误消息 return string
```
```$xslt
$SMS::get('driver') 获得短信发送驱动对象
```




