<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/25 0025
 * Time: 14:39
 */
use App\Utility\Pool\MysqlPool;
class MQ{
    private static $MQ = null;
    public static function conn(){
//        if(self::$MQ == null){
//            $conf = new \EasySwoole\Mysqli\Config(\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL'));
//            self::$MQ = new \EasySwoole\Mysqli\Mysqli($conf);
//        }
//        return self::$MQ;

        return MysqlPool::defer();
    }
}