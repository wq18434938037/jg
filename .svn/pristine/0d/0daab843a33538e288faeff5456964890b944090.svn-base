<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/16 0016
 * Time: 17:52
 */

namespace App\HttpController;
use App\HttpController\Base\Base;

class Alipay extends Base{

    /**
     * 调起支付宝支付
     */
    function callPay(){

        $order_num = $this->Post('order_num');
        if(empty($order_num)){
            $this->responseError('order_num error');
        }
        $money = $this->Post('money');
        if(empty($money)){
            $this->responseError('money error');
        }
        $conf = \tool::appConfig('ALIPAY');
        $url = \tool::appConfig('URL.api');
        $notify_url = $url . '/Alipay/success';
        if($conf['debug'] === true){
            $money = 0.01;
        }
        $aop = new \AopClient ();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = $conf['appid'];
        $aop->rsaPrivateKey = $conf['PrivateKey'];
        $aop->alipayrsaPublicKey = $conf['PublicKey'];
        $aop->signType = 'RSA2';
        $request = new \AlipayTradeAppPayRequest();
        $request->setNotifyUrl($notify_url);
        $request->setBizContent(json_encode([
            'out_trade_no' => $order_num,
            'total_amount' => $money,
            'subject' => $conf['paytitle'],
            'body' => $conf['paytitle'],
            'timeout_express' => '30m',
            'product_code' => 'QUICK_MSECURITY_PAY',
        ]));
        $response = $aop->sdkExecute($request);
        $this->responseSuccess(str_replace('amp;','',htmlspecialchars($response)));
    }
    /**
     * 支付回调
     */
    function success(){
        $data = $this->request()->getParsedBody();
        if(empty($data)){
            return false;
        }
        if($data['trade_status'] == 'WAIT_BUYER_PAY' || $data['trade_status'] == 'TRADE_CLOSED'){
            return false;
        }
//        支付金额
        $payMoney = floatval($data['buyer_pay_amount']);
        $order_on = $data['out_trade_no'];

        $mq = \MQ::conn();
        $mq->startTransaction();
        //  todo------------逻辑处理
        $status = $this->paySuccess($order_on,$payMoney,2,json_encode($data));
        if($status){
            $mq->commit();
            $this->response()->write('success');return;
        }else{
            $mq->rollback();
            return false;
        }
    }
}