<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/5 0005
 * Time: 19:14
 */
class tool{
    static $request;
    static $response;
    private static $redisConnect = null;

    /**
     * 连接redis
     * @return null|Redis
     */
    static function redisConnect(){
        if(self::$redisConnect == null){
            $config = \EasySwoole\EasySwoole\Config::getInstance()->getConf('REDIS');
            self::$redisConnect = new Redis();
            self::$redisConnect->connect($config['host'],$config['port']);
            unset($config);
        }
        return self::$redisConnect;
    }

    static function appConfig($name){
        return \EasySwoole\EasySwoole\Config::getInstance()->getConf($name);
    }
    /**
     * 接收Post参数
     * @param $name
     * @param null $default
     * @param string $format
     * @return float|int|null|string
     */
    static function Post($name,$default = null,$format = ''){
        $data = self::$request->getParsedBody($name);
        if($default !== null && !isset($data)){
            $data = $default;
        }else if($default === null && !isset($data)){
            self::responseError($name . " Can't be empty");
        }
        $format = strtolower(trim($format));
        switch($format){
            case 'int':
                $data = intval($data);
                break;
            case 'float':
                $data = floatval($data);
                break;
            case 'str':
                $data = (string)$data;
                break;
        }
        return $data;
    }

    /**
     * 接收Get参数
     * @param $name
     * @param null $default
     * @param string $format
     * @return float|int|null|string
     */
    static function Get($name,$default = null,$format = ''){
        $data = self::$request->getQueryParam($name);
//        self::responseError('ssadas');
        if($default !== null && !isset($data)){
            $data = $default;
        }else if($default === null && !isset($data)){
            self::responseError($name . " Can't be empty");
        }
        $format = strtolower(trim($format));
        switch($format){
            case 'int':
                $data = intval($data);
                break;
            case 'float':
                $data = floatval($data);
                break;
            case 'str':
                $data = (string)$data;
                break;
        }
        return $data;
    }


    /**
     * 返回成功JSON
     * @param $data
     */
    static function responseSuccess($data=null){
        self::writeJsons(0,$data,'ok');
        self::$response->end();
        throw new \Swoole\ExitException();
    }

    /**
     * 返回失败JSON
     * @param $msg
     * @param int $code
     */
    static function responseError($msg,$code = 1){
        self::writeJsons($code,null,$msg);
        self::$response->end();
        throw new \Swoole\ExitException();
    }

    /**
     * 组合JSON数据
     * @param int $statusCode
     * @param null $result
     * @param null $msg
     * @return bool
     */
    static function writeJsons($statusCode = 200, $result = null, $msg = null)
    {
        if (!self::$response->isEndResponse()) {
            $data = Array(
                "status" => $statusCode,
                "msg" => $msg,
                "result" => $result,
            );
            self::$response->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            self::$response->withHeader('Content-type', 'application/json;charset=utf-8');
            self::$response->withStatus(200);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取用户真实ip
     * @return mixed
     */
    static function getClientIp(){
        $ip = self::$request->getHeader('x-real-ip');
        if(empty($ip)){
            $ip = self::Server()['server']['remote_addr'];
        }else{
            $ip = $ip[0];
        }
        return $ip;
    }

    /**
     * 获取 $_SERVER 参数
     * @return array
     */
    static function Server(){
        return (array)self::$request->getSwooleRequest();
    }

    /**
     * 获取url
     */
    static function getUrl(){
        return tool::appConfig('URL.api');
    }
    /**
     * 获取执行后的sql
     */
    static function getLastSql(){
        return \MQ::conn()->getLastQuery();
    }
    /**
     * 打印log日志
     * @param $data
     */
    static function error_logs($data){
        if(is_array($data)){
            $data = json_encode($data);
        }
        $path = EASYSWOOLE_ROOT . '/error_log';
        if(!file_exists($path)){
            mkdir($path,0777,true);
        }
        $path .= '/'.date('Ymd').'.log';
        file_put_contents($path,'时间: ' . date('Y-m-d H:i:s') .'--------------------' . "\n",FILE_APPEND);
        file_put_contents($path,$data . "\n",FILE_APPEND);
    }

    /**
     * 时间转换 xx秒前、xx分钟前、xx小时前、xx天前
     * @param $time
     * @return string
     */
    static function format_date($time){
        $t=time()-strtotime($time);
        $f=array(
            '31536000'=>'年',
            '2592000'=>'个月',
            '604800'=>'星期',
            '86400'=>'天',
            '3600'=>'小时',
            '60'=>'分钟',
            '1'=>'秒'
        );
        foreach ($f as $k=>$v)    {
            if (0 !=$c=floor($t/(int)$k)) {
                return $c.$v.'前';
            }
        }
    }

    /**
     * 单笔转账到支付宝账户
     */
    static function transferAliAccount($price,$account,$name,$order_num){
        $conf['appid'] = 'xxxxx';

        $conf['PrivateKey'] = 'xxxxxx';

        $conf['PublicKey'] = 'xxxxxx';

        $price = 0.1;
        $aop = new AopClient ();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = $conf['appid'];
        $aop->rsaPrivateKey = $conf['PrivateKey'];
        $aop->alipayrsaPublicKey= $conf['PublicKey'];
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->format='json';
        $request = new AlipayFundTransToaccountTransferRequest ();
        $request->setBizContent(json_encode([
            'out_biz_no' => $order_num,
            'payee_type' => 'ALIPAY_LOGONID',
            'payee_account' => $account,
            'amount' => $price,
            'payer_show_name' => 'xxxx科技有限公司',
            'payee_real_name' => $name,
            'remark' => date('Y年m月d日 H点i分') . ' xxxxxx科技有限公司-自动提现',
        ]));
        $result = $aop->execute($request);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            return true;
        } else {
            self::error_logs('单笔转账到支付宝账户失败 : ('.$resultCode.')' . $result->$responseNode->sub_msg);
            return false;
        }
    }

    /**
     * 生成不重复的随机数(订单号)
     * @param string $type 前缀
     * @return string
     */
    static function createRandOrdernum($type=''){
        return $type . time() . substr(explode(' ',microtime())[0],2,6) . sprintf('%03d', rand(0, 999));
    }

    /**
     * 敏感词替换
     * @param $content
     * @return string
     */
    static function sensitiveReplace($content){
        $badword = json_decode(file_get_contents(EASYSWOOLE_COMMON . '/words4.txt'),true);
        return strtr($content, $badword);
    }

    /**
     * 实例化es操作类
     * @param $index
     */
    static function es($index){
        return new \Estool\es($index);
    }

    /**
     * 按照指定长度截取字符串
     * @param $str
     * @param $len
     * @return string
     */
    static function stringCut($str,$len,$ellipsis = true){
        if(mb_strlen($str,'utf-8') > $len){
            return mb_substr($str,0,$len,'utf-8') . ($ellipsis===true?'...':'');
        }else{
            return $str;
        }
    }

}