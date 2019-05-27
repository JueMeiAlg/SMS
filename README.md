# Laravel 短信包

## 支持短信服务商:阿里大鱼,凌凯短信

本扩展包发送验证码需要redis环境,请确保运行环境中有redis正常运行

如果你的laravel 小于5.5则需要 `\Alg\SMS\SMSServiceProvider` 吧他加入 config\app provider中,大于5.5则忽略这个过程

发布配置文件

`php artisan vendor:publish --provider="Alg\SMS\SMSServiceProvider" 
`
#### 发送验证码
```$xslt
use Alg\SMS\Facades\SMS;

        public function example(SMS $SMS)
           {
               $SMS::get('lk')->useTemplate('example')->setTemplateParam([
                   'code'=>$SMS::getRandCode()
               ])->sendCode('12345678911');
           }
```

#### 发送短信通知
```$xslt
use Alg\SMS\Facades\SMS;

        public function example(SMS $SMS)
           {
               $SMS::get('lk')->useTemplate('goodsBuySuccess')->setTemplateParam([
                   'product'=>'上衣',
                   'order'=>'20180322',
               ])->send('12345678911');
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
$SMS::sendCode(string $tel)  发送短信验证码 return bool
```
```$xslt
$SMS::check(string $tel string $code)  检测验证码是否一致 return bool|string
```
```$xslt
$SMS::getSendError()  获取发送错误消息 return string
```
```$xslt
$SMS::get('driver') 获得短信发送驱动对象
```




