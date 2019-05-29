<?php
/**
 * Created by PhpStorm.
 * User: ALG
 * Date: 2019/3/22
 * Time: 10:44
 */
namespace Alg\SMS\Facades;

use Alg\SMS\Contract\SMS as abstractSMS;

/**
 * 短信类解析
 *
 * Class SMS
 *
 * @category SMS
 * @author   ALG <513051043@qq.com>
 * @license  四川猪太帅科技公司 http://www.51zts.com
 * @link     接口文档链接
 * @method static mixed getRandCode()
 * @method static mixed check(string $tel, string $code)
 * @method static mixed getSendError()
 * @method static mixed useTemplate(string $template)
 * @method static mixed setTemplateParam(array $templateParam)
 */
class SMS
{
    //具体的发送短信服务
    private static  $obj = null;

    //使用的驱动
    private static $drive = [];

    /**
     * 调用具体服务的方法
     *
     * @param $method
     * @param $args
     *
     * @return mixed
     * @throws \Exception
     */
    public static function __callStatic($method, $args)
    {
        if (empty(self::$obj)) {
            self::$obj = self::drive();
        }
        return self::$obj->$method(...$args);
    }

    /**
     * 获得默认服务
     *
     * @return mixed
     */
    private static function getDefault()
    {
        return config('sms.default');
    }

    /**
     * 获得具体服务
     *
     * @param null $drive
     *
     * @return null
     * @throws \Exception
     */
    private static function drive($drive = null)
    {
        return self::get($drive ?? self::getDefault());
    }

    /**
     * 获得指定服务
     *
     * @param $drive
     *
     * @return null
     * @throws \Exception
     */
    public static function get($drive)
    {
        $class = config('sms.drive')[$drive]['drive'];
        //实现单列模式
        if (!isset(self::$drive[$class])) {
            self::$drive[$class] = new $class();
            if (!(self::$drive[$class] instanceof abstractSMS)) {
                throw new \Exception('没有实现短信验证接口');
            }
        }
        return self::$drive[$class];
    }
}