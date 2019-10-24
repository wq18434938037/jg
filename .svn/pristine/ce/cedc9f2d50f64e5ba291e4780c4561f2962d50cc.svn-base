<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/15 0015
 * Time: 9:59
 */

namespace App\HttpController\Base;
use App\HttpController\Base\Base;

class LoginBase extends Base{

    /**
     * 员工登录,生成token
     */
    protected function createToken($mobile=null,$token=null){
        $data = false;
        if($mobile){
            $data = \MQ::conn()
                ->where('mobile',$mobile)
                ->whereIn('is_del',[1,3])
                ->orderBy('uid','desc')
                ->getOne('a_user');
        }elseif($token){
            $data = $this->getLoginUser($token);
        }

        if(!$data){
            $this->responseError('系统没检测到您的信息，请前往注册!',1002);
        }elseif($data['is_del'] == '3'){
            $this->responseError('系统检测到您的账号已被禁用，请联系所在学校!',1002);
        }

        //  更新openid
        \MQ::conn()
            ->where('uid',$data['uid'])
            ->update('a_user',[
                'wx_openid' => $this->Post('openid')
            ]);

        //  生成token
        if(empty($token)){
            $Openssl = new \EasySwoole\Component\Openssl(\tool::appConfig('openssl_key'),'DES-EDE3');
            $token = md5($data['uid'] . time() . rand(1,1000));
            $token = $Openssl->encrypt($token);
        }

//        $token = hash('sha256',$data['uid'] . time() . rand(1,1000));

        $redis = \tool::redisConnect();
        //  查看用户的token
        $old_token = $redis->get('USER_TOKEN' . $data['uid']);
        //  销毁之前token
        if($old_token){
            $redis->delete('USER_TOKEN' . $data['uid']);
            $redis->delete('USER_DATA' . $old_token);
        }
        //生成新的缓存
        $redis->set('USER_TOKEN' . $data['uid'],$token);
        $redis->set('USER_DATA' . $token,$data['uid']);
        $data['cy_name'] = \MQ::conn()
            ->where('cy_id',$data['cy_id'])
            ->getValue('a_company','cy_name');
        $data['token'] = $token;
        return $data;
    }

    /**
     * 生成登录记录
     */
    protected function createLoginRecord($uid){
        $lately_record = \MQ::conn()
            ->where('uid',$uid)
            ->orderBy('ulr_addtime','desc')
            ->get('a_user_login_record',[0,4],'ulr_id');
        if($lately_record){
            foreach($lately_record as $k=>$v){
                $ulr_id[] = $v['ulr_id'];
            }

            \MQ::conn()
                ->where('uid',$uid)
                ->whereNotIn('ulr_id',$ulr_id)
                ->delete('a_user_login_record');
        }

        \MQ::conn()
            ->where("uid",$uid)
            ->update('a_user_login_record',[
                'ulr_type' => 2
            ]);
        $getMobileType = $this->getMobileType();
        $add = \MQ::conn()
            ->insert('a_user_login_record',[
                'uid' => $uid,
                'ulr_facility' => $getMobileType[0],
                'ulr_ip' => $this->getClientIp(),
                'ulr_type' => 1,
                'ulr_remark' => $getMobileType[1],
                'ulr_addtime' => date('Y-m-d H:i:s'),
            ]);
        if(!$add){
            $this->responseError('更新登录记录错误');
        }
        return true;
    }

}