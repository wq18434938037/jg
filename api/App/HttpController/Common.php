<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/16 0016
 * Time: 11:50
 */
namespace App\HttpController;
use App\HttpController\Base\Base;


class Common extends Base{
    /**
     * 支付回调测试
     */
    function payCeshi(){
        $order_num = $this->Post('order_num');
        $price = $this->Post('price');
        $mq = \MQ::conn();
        $mq->startTransaction();
        $pay_status = $this->paySuccess($order_num,$price,1);
        if($pay_status === true){
            $mq->commit();
            $this->responseSuccess();
        }else{
            $mq->rollback();
            $this->responseError('支付失败');
        }
    }

    /**
     * 获取用户token
     */
    function getUserToken(){
        $uid = $this->Post('uid');
        $redis = \tool::redisConnect();
        $token = $redis->get('USER_TOKEN' . $uid);
        $this->responseSuccess($token);
    }

    /**
     * 检测新版本
     */
    function detectionNewVersion(){
        $nv_id = $this->Post('nv_id');
        $nv_type = $this->Post('nv_type');
        $code = $this->Post('code');
        $data = \MQ::conn()
            ->where('nv_id',$nv_id)
            ->where('nv_type',$nv_type)
            ->getOne('a_new_version','nv_code,nv_url');

        if($data['nv_code'] != $code){
            if($nv_type == '1'){
                $data['nv_url'] = \tool::appConfig('ALIOSS.visiturl') . $data['nv_url'];
            }
            $this->responseSuccess([
                'nv_code' => $data['nv_code'],
                'nv_url' => $data['nv_url'],
            ]);
        }else{
            $this->responseError('没有最新版本',3);
        }
    }

    function errorlog(){
        $time = $this->Get('time',date('Ymd'));
        $data = file_get_contents(EASYSWOOLE_ROOT . '/error_log/'.$time.'.log');
        $this->response()->withHeader('Content-type', 'text/plain;charset=utf-8');
        $this->response()->write($data);
        return true;
    }
    /**
     * 将字符串生成二维码图片
     */
    function getQrcode(){
        $code = $this->Get('code');
        if(empty($code)){
            $this->responseError('code error');
        }
        $code = urldecode($code);
        if(empty($code)){
            $this->responseError('code error2');
        }
        ob_start();
        \QRcode::png($code,false,QR_ECLEVEL_L,10,1);
        $qrcode = ob_get_contents();
        ob_end_clean();
        $this->response()->withHeader('Content-type','image/jpg');
        $this->response()->write($qrcode);
        return;
    }

}