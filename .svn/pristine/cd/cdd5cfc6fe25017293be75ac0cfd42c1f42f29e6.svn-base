<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/14 0014
 * Time: 17:57
 */

namespace App\HttpController;
use App\HttpController\Base\LoginBase;

class Login extends LoginBase{
    function index()
    {
        // TODO: Implement index() method.
    }

    /**
     * 手机号登录
     */
    function mobileLogin(){
        $mobile = $this->verifyMobile($this->Post('mobile'));
        $code = $this->Post('code');
        $this->verifyCode($mobile,$code);

        //  判断是否注册
        $user = \MQ::conn()
            ->where('mobile',$mobile)
            ->whereIn('is_del',[1,3])
            ->getOne('a_user','uid');
        if(empty($user)){
            $register = $this->register($mobile);
            if(!$register){
                $this->responseError('登录失败:未能注册成功');
            }
        }

        $data = $this->createToken($mobile);
        $this->createLoginRecord($data['uid']);
        $this->responseSuccess($data);
    }

    /**
     * 注册
     */
    private function register($mobile){
        $add = \MQ::conn()
            ->insert('a_user',[
                'nickname'=>'m_' . $mobile,
                'photo'=>'/default_avatar.png',
                'mobile'=>$mobile,
                'add_time' => date('Y-m-d H:i:s')
            ]);
        if($add){
            return true;
        }else{
            return false;
        }
    }
    /**
     * 自动登录
     */
    function autoLogin(){
        $token = $this->Post('token');
        $data = $this->createToken(null,$token);
        $this->createLoginRecord($data['uid']);
        $this->responseSuccess($data);
    }

    /**
     * 发送验证码
     */
    function sendCode(){
        $mobile = $this->verifyMobile($this->Post('mobile'));
        if(empty($mobile)){
            $this->responseError('请输入手机号');
        }
        $redis = \tool::redisConnect();

        if($redis->get('mobileCodeRestrict' . $mobile) == '1'){
            $this->responseError('请勿频繁发送验证码');
        }
        //  随机生成验证码
        for($i=1;$i<=4;$i++){
            $code[] = (string)rand(0,9);
        }
        $code = implode('',$code);
        $config = \tool::appConfig('RONGLIAN');

        $rest = new \RongLian\REST($config['serverIP'],$config['serverPort'],$config['softVersion']);
        $rest->setAccount($config['accountSid'], $config['accountToken']);
        $rest->setAppId($config['appId']);

        $result = $rest->sendTemplateSMS($mobile, [$code], $config['tempId'][0]);
        if ($result == NULL) {
            $this->responseError('发送失败');
        }
        if ($result->statusCode != 0) {
            $this->responseError('error : ' . $result->statusMsg);
        }
        $redis->setex('mobile_code'.$mobile,300,$code);
        $redis->setex('mobileCodeRestrict'.$mobile,60,1);
        $this->responseSuccess();
    }

    /**
     * 校验短信验证码 api
     */
    function verifyCodeApi(){

        $mobile = $this->verifyMobile($this->Post('mobile'));
        $code = $this->Post('code');
        $data = $this->verifyCode($mobile,$code);
        if($data){
            $this->responseSuccess();
        }else{
            $this->responseError('校验失败');
        }
    }

}