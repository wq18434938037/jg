<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/28
 * Time: 下午6:33
 */

namespace EasySwoole\EasySwoole;

use App\Crontab\Shoptransfer;
use App\Crontab\Suptransfer;
use App\Crontab\Usersaletransfer;
use App\Utility\Pool\MysqlPool;
use EasySwoole\Component\Pool\PoolManager;
use EasySwoole\EasySwoole\Crontab\Crontab;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use App\Process\HotReload;
define('ES_circle','circle');
define('ES_talk','talk');
define('ES_junk','junk');
class EasySwooleEvent implements Event
{

    public static function initialize()
    {
        require_once EASYSWOOLE_COMMON . '/MQ.php';
        require_once EASYSWOOLE_COMMON . '/AliOss/autoload.php';
        require_once EASYSWOOLE_COMMON . '/wxpayTool.php';
        require_once EASYSWOOLE_COMMON . '/RongLian/REST.php';
        require_once EASYSWOOLE_COMMON . '/phpqrcode.php';
        require_once EASYSWOOLE_COMMON . '/Gateway.php';
        require_once EASYSWOOLE_COMMON . '/Alipay/AopSdk.php';
        require_once EASYSWOOLE_COMMON . '/es/autoload.php';
        require_once EASYSWOOLE_COMMON . '/tool.php';
        require_once EASYSWOOLE_COMMON . '/es.php';
        // TODO: Implement initialize() method.
        date_default_timezone_set('Asia/Shanghai');

        PoolManager::getInstance()->register(MysqlPool::class,Config::getInstance()->getConf('MYSQL.POOL_MAX_NUM'));
    }

    public static function mainServerCreate(EventRegister $register)
    {
        // TODO: Implement mainServerCreate() method.
        $swooleServer = ServerManager::getInstance()->getSwooleServer();
        $swooleServer->addProcess((new HotReload('HotReload', ['disableInotify' => false]))->getProcess());

        //  todo 创建定时任务
//        Crontab::getInstance()->addTask(Shoptransfer::class);
//        Crontab::getInstance()->addTask(Suptransfer::class);
//        Crontab::getInstance()->addTask(Usersaletransfer::class);

        //如何避免定时器因为进程重启而丢失
        $register->add(EventRegister::onWorkerStart, function (\swoole_server $server, $workerId) {
            if ($workerId == 0) {
                //  todo 创建毫秒级定时器
                \EasySwoole\Component\Timer::getInstance()->loop(30*1000, function () {

                });
            }
        });
    }

    public static function onRequest(Request $request, Response $response): bool
    {
        // TODO: Implement onRequest() method.
        $response->withHeader('Access-Control-Allow-Origin', '*');
        \tool::$request = $request;
        \tool::$response = $response;
        return true;
    }

    public static function afterRequest(Request $request, Response $response): void
    {
        // TODO: Implement afterAction() method.
    }
}