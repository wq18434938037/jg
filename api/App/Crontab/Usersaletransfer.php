<?php
/**
 * 销售人员定时提现
 */

namespace App\Crontab;
use EasySwoole\EasySwoole\Crontab\AbstractCronTask;

class Usersaletransfer extends AbstractCronTask
{

    public static function getRule(): string
    {
        // TODO: Implement getRule() method.
        return '0 3 * * 0';
//        return '*/1 * * * *';
    }

    public static function getTaskName(): string
    {
        // TODO: Implement getTaskName() method.
        // 定时任务名称
        return 'Usersaletransfer';
    }

    static function run(\swoole_server $server, int $taskId, int $fromWorkerId,$flags=null){
        //  todo 业务逻辑处理
    }
}