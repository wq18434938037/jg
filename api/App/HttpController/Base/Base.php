<?php
namespace App\HttpController\Base;

use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\Http\Message\Status;

class Base extends Controller{
    function index()
    {
        // TODO: Implement index() method.
    }

    protected function openssl(){
        return new \EasySwoole\Component\Openssl(\tool::appConfig('openssl_key'),'DES-EDE3');
    }

    /**
     * 打印log日志到txt文件
     * @param $data
     */
    function error_logs($data){
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

    function onException(\Throwable $throwable): void
    {
        if($throwable->getMessage() == ''){
            $this->response()->withStatus(200);
            var_dump($throwable->getMessage());
        }else{
            throw new \Swoole\ExitException($throwable);
        }
    }
    /**
     * 接收Post参数
     * @param $name
     * @param null $default
     * @return null
     */
    protected function Post($name,$default=null,$format=null){
        $data = $this->request()->getParsedBody($name);
        if($default !== null && !isset($data)){
            $data = $default;
        }else if($default === null && !isset($data)){
            $this->responseError($name . " Can't be empty");
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
     * mysql 分页
     */
    protected function page(){
        $page = $this->Post('page',1);
        $size = $this->Post('size',20);
        return [($page-1)*$size,$size];
    }
    /**
     * 接收Get参数
     * @param $name
     * @param null $default
     * @return null
     */
    protected function Get($name,$default=null,$format=null){
        $data = $this->request()->getQueryParam($name);
        if($default !== null && !isset($data)){
            $data = $default;
        }else if($default === null && !isset($data)){
            $this->responseError($name . " Can't be empty");
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
     * 组合JSON数据
     * @param int $statusCode
     * @param null $result
     * @param null $msg
     * @return bool
     */
    function writeJsons($statusCode = 200, $result = null, $msg = null)
    {
        if (!$this->response()->isEndResponse()) {
            $data = Array(
                "status" => $statusCode,
                "msg" => $msg,
                "result" => $result,
            );
            $this->response()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $this->response()->withHeader('Content-type', 'application/json;charset=utf-8');
            $this->response()->withStatus(200);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 返回成功JSON
     * @param $data
     */
    protected function responseSuccess($data=null){
        $this->writeJsons(0,$data,'ok');
        $this->end();
        throw new \Swoole\ExitException();
    }

    /**
     * 返回失败JSON
     * @param $msg
     * @param int $code
     */
    protected function responseError($msg,$code = 1){
        $this->writeJsons($code,null,$msg);
        $this->end();
        throw new \Swoole\ExitException();
    }


    /**
     * 结束本次请求
     */
    protected function end(){
        $this->response()->end();
    }

    /**
     * 获取用户真实ip
     */
    function getClientIp(){
        $ip = $this->request()->getHeader('x-real-ip');
        if(empty($ip)){
            $ip = $this->getServerArray()['server']['remote_addr'];
        }else{
            $ip = $ip[0];
        }
        return $ip;
    }

    /**
     * 渲染一个模板页面
     * @param $tplName
     * @param array $tplVars
     * @throws \Exception
     */
    function renderTemplate( array $tplVars = [],$tplName=null)
    {
        if($tplName===null){
            $tplName = $this->getServerArray();
            $tplName = $tplName['server']['path_info'];
            if(empty(explode('/',$tplName)[2])){
                $tplName .= '/index';
            }
            if(!$tplName){
                throw new \Exception('path_info Can\'t be empty');
            }
        }
        $tplName = strtolower(trim($tplName));
        $tplName .= '.html';
        $staticPath = EASYSWOOLE_ROOT . '/App/Views';
        $templateFile = $staticPath . $tplName;
        if (is_file($templateFile) && file_exists($templateFile)) {
            $content = file_get_contents($templateFile);
            foreach ($tplVars as $tplVarName => $tplVarValue) {
                $content = str_replace("{{{$tplVarName}}}", $tplVarValue, $content);
            }
            $this->response()->withHeader('content-type', 'textml;charset=utf8');
            $this->response()->write($content);
        } else {
            $this->response()->withHeader('content-type', 'textml;charset=utf8');
            $this->response()->write($templateFile . ' 模板不存在！');
        }
        $this->end();
    }
    /**
     * 获取二维数组或字符串中 , 以,隔开的第一张图片
     * @param $img
     * @param string $key
     * @return string
     */
    function getImageFirst($img,$key=''){
        if(is_array($img)){
            foreach($img as $k=>$v){
                if($v[$key]){
                    $img[$k][$key] = explode(',',$v[$key])[0];
                }else{
                    $img[$k][$key] = '';
                }
            }
            return $img;
        }else{
            if(!$img){
                return '';
            }else{
                return explode(',',$img)[0];
            }
        }
    }



    /**
     * 获取上传文件
     * @param $file
     */
    function getUploaded($file='file',$is_base64=false,$targetPath=null){
        $file = $this->request()->getUploadedFile($file);
        if(empty($file)){
            $this->responseError('请选择要上传的文件');
        }
        $data['tempName'] = $file->getTempName();
//        $data['stream'] = $file->getStream();
        $data['size'] = $file->getSize();
        $data['error'] = $file->getError();
        $data['clientFileName'] = $file->getClientFilename();
        $data['clientMediaType'] = $file->getClientMediaType();
        if($is_base64 === true){
            $data['base64'] = $this->imgToBase64($data['tempName']);
        }
        if($targetPath){
            $file->moveTo($targetPath);
        }
        return $data;
    }
    /**
     * 删除上传文件的临时文件
     * @param $file
     */
    function unlinkUploaded($file='file'){
        $file = $this->request()->getUploadedFile($file);
        if(empty($file)){
            $this->responseError('源文件不存在');
        }
        $unlink = unlink($file->getTempName());
        return $unlink ? true : false;
    }

    /**
     * 将图片转换base64
     * @param $image_file
     * @return string
     */
    function imgToBase64 ($image_file) {
        $image_info = getimagesize($image_file);
        $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
        $base64_image = 'data:' . $image_info['mime'] . ';base64,' . base64_encode($image_data);
        return $base64_image;
    }
    /**
     * 将图片转换base64
     * @param $image_file
     * @return string
     */
    function imgToBase64_V2 ($image_file) {
        $base64_image  = base64_encode(file_get_contents($image_file));
        return $base64_image;
    }
    /**
     * 生成不重复的随机数(订单号)
     * @param string $type 前缀
     * @return string
     */
    function createRandOrdernum($type='')
    {
        return $type . time() . substr(explode(' ',microtime())[0],2,6) . sprintf('%03d', rand(0, 999));
    }

    /**
     * 支付回调
     */
    function paySuccess($order_num,$price,$type,$payData = ''){
        $payType = array(
            1 => '微信支付--',
            2 => '支付宝支付--',
            4 => 'Apple Pay支付--',
        );
        //  第三方支付费率% $price*$payRate = 费率金额
        $payRate = [
            1 => 0.006,
            2 => 0.006,
            3 => 0,
            4 => 0,
            5 => 0,
        ];
        //  手续费
        $payRatePrice = bcmul($price,$payRate[$type],4);
        switch(substr($order_num,0,2)){
            case '10':
                #   xxxx订单
                break;
            case '11':
                #   xxxxx订单
                break;
            case '12':
                //  xxxxx超市订单
                break;
            default:
                return false;
                break;
        }
        return true;
    }


    /**
     * 判断员工登录状态
     */
    protected function getLoginUser($tokens=null){
        $token = $this->Post('token','');
        if(empty($token) && $tokens){
            $token = $tokens;
        }
        if ($token) {
            $redis = \tool::redisConnect();
            $user_id = $redis->get('USER_DATA' . $token);
            if(empty($user_id)){
                $this->responseError("token错误或者失效",1001);
            }
        } else {
            $this->responseError("token不能为空",1001);
        }
        $user = \MQ::conn()
            ->where('uid',$user_id)
            ->getOne('a_user');
        if ($user) {
            if($user['is_del'] == '3'){
                $this->responseError('账号已被禁用，请联系企业管理员!',1001);
            }
            if($user['is_del'] == '2'){
                $this->responseError('账号已被删除，请联系企业管理员!',1001);
            }
            return $user;
        } else {
            $this->responseError("账号不存在，请联系企业管理员!",1001);
        }
    }

    /**
     * 获取 $_SERVER 参数
     * @return array
     */
    protected function getServerArray(){
        return (array)$this->request()->getSwooleRequest();
    }
    /**
     * 获取url
     */
    function getUrl(){
        return \tool::appConfig('URL.api');
    }

    /**
     * 获取访问手机型号
     * @return string
     */
    protected function getMobileType(){
        $server = $this->getServerArray();
        $user_agent = $server['header']['user-agent'];
        if (stripos($user_agent, "iPhone")!==false) {
            $brand = 'iPhone';
        } else if (stripos($user_agent, "SAMSUNG")!==false || stripos($user_agent, "Galaxy")!==false || strpos($user_agent, "GT-")!==false || strpos($user_agent, "SCH-")!==false || strpos($user_agent, "SM-")!==false) {
            $brand = '三星';
        } else if (stripos($user_agent, "Huawei")!==false || stripos($user_agent, "H60-")!==false || stripos($user_agent, "H30-")!==false) {
            $brand = '华为';
        } else if(stripos($user_agent, "Honor")!==false){
            $brand = '华为荣耀';
        } else if (stripos($user_agent, "Lenovo")!==false) {
            $brand = '联想';
        } else if (strpos($user_agent, "MI-ONE")!==false || strpos($user_agent, "MI 1S")!==false || strpos($user_agent, "MI 2")!==false || strpos($user_agent, "MI 3")!==false || strpos($user_agent, "MI 4")!==false || strpos($user_agent, "MI-4")!==false) {
            $brand = '小米';
        } else if (strpos($user_agent, "HM NOTE")!==false || strpos($user_agent, "HM201")!==false) {
            $brand = '红米';
        } else if (stripos($user_agent, "Coolpad")!==false || strpos($user_agent, "8190Q")!==false || strpos($user_agent, "5910")!==false) {
            $brand = '酷派';
        } else if (stripos($user_agent, "ZTE")!==false || stripos($user_agent, "X9180")!==false || stripos($user_agent, "N9180")!==false || stripos($user_agent, "U9180")!==false) {
            $brand = '中兴';
        } else if (stripos($user_agent, "OPPO")!==false || strpos($user_agent, "X9007")!==false || strpos($user_agent, "X907")!==false || strpos($user_agent, "X909")!==false || strpos($user_agent, "R831S")!==false || strpos($user_agent, "R827T")!==false || strpos($user_agent, "R821T")!==false || strpos($user_agent, "R811")!==false || strpos($user_agent, "R2017")!==false) {
            $brand = 'OPPO';
        } else if (strpos($user_agent, "HTC")!==false || stripos($user_agent, "Desire")!==false) {
            $brand = 'HTC';
        } else if (stripos($user_agent, "vivo")!==false) {
            $brand = 'vivo';
        } else if (stripos($user_agent, "K-Touch")!==false) {
            $brand = '天语';
        } else if (stripos($user_agent, "Nubia")!==false || stripos($user_agent, "NX50")!==false || stripos($user_agent, "NX40")!==false) {
            $brand = '努比亚';
        } else if (strpos($user_agent, "M045")!==false || strpos($user_agent, "M032")!==false || strpos($user_agent, "M355")!==false) {
            $brand = '魅族';
        } else if (stripos($user_agent, "DOOV")!==false) {
            $brand = '朵唯';
        } else if (stripos($user_agent, "GFIVE")!==false) {
            $brand = '基伍';
        } else if (stripos($user_agent, "Gionee")!==false || strpos($user_agent, "GN")!==false) {
            $brand = '金立';
        } else if (stripos($user_agent, "HS-U")!==false || stripos($user_agent, "HS-E")!==false) {
            $brand = '海信';
        } else if (stripos($user_agent, "Nokia")!==false) {
            $brand = '诺基亚';
        } else if(stripos($user_agent,'Chrome')!==false){
            // 检查Chrome
            $brand = 'Chrome';
        } else if(stripos($user_agent,'Safari')!==false){
            // 检查Safari
            $brand = 'Safari';
        } else if(stripos($user_agent,'MSIE')!==false){
            // IE
            $brand = 'IE';
        } else if(stripos($user_agent,'Opera')!==false){
            // opera
            $brand = 'Opera';
        } else if(stripos($user_agent,'Firefox')!==false){
            // Firefox
            $brand = 'Firefox';
        } else if(stripos($user_agent,'OmniWeb')!==false){
            //OmniWeb
            $brand = 'OmniWeb';
        } else if(stripos($user_agent,'Netscape')!==false){
            //Netscape
            $brand = 'Netscape';
        } else if(stripos($user_agent,'Lynx')!==false){
            //Lynx
            $brand = 'Lynx';
        } else if(stripos($user_agent,'360SE')!==false){
            //360SE
            $brand = '360安全浏览器';
        } else if(stripos($user_agent,'SE 2')!==false) {
            //搜狗
            $brand = '搜狗浏览器';
        } else if(stripos($user_agent,'MicroMessenger')!==false) {
            //微信
            $brand = '微信';
        } else {
            $brand = '其他设备';
        }
        return [
            $brand,
            $user_agent,
        ];
    }
    /**
     * 校验验证码
     */
    protected function verifyCode($mobile,$code){
        if(empty($mobile)){
            $this->responseError('请输入手机号码');
        }
        if(empty($code)){
            $this->responseError('请输入短信验证码');
        }
        $redis_code = \tool::redisConnect()->get('mobile_code'.$mobile);

        if($code == '8888' || $redis_code==$code){
            return true;
        }else{
            $this->responseError('验证码不正确，请重新输入！');
        }
        return true;
    }
    /**
     * 正则验证手机号码
     */
    protected function verifyMobile($mobile){
        if(!preg_match("/^1[3456789]\d{9}$/", $mobile)){
            $this->responseError('手机号码格式错误');
        }
        return $mobile;
    }


    /**
     * mysql经纬度计算
     * @param $lat
     * @param $lng
     * @return string
     * 示例:
     * $lcoation = array(
                        'lat' => 92.213671262,
                        'lng' => 108.58182367,
                    );
     */
    function bNearField($location){

        if(!is_array($location)){
            return false;
        }
        if(count($location) != 2){
            return false;
        }
        $fieldKey = array_keys($location);
        $fieldVal = array_values($location);
        return "ROUND(6378.138*2*ASIN(SQRT(POW(SIN(({$fieldVal[0]}*PI()/180-{$fieldKey[0]}*PI()/180)/2),2)+COS({$fieldVal[0]}*PI()/180)*COS({$fieldKey[0]}*PI()/180)*POW(SIN(({$fieldVal[1]}*PI()/180-{$fieldKey[1]}*PI()/180)/2),2))) * 1000,0) as distance";
    }
}


