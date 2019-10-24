<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/14 0014
 * Time: 12:27
 */
namespace App\HttpController;
use App\HttpController\Base\Base;
class Wxpay extends Base{
    function index()
    {
        // TODO: Implement index() method.
    }

    /**
     * 调起支付
     */
    function callPay(){
        $url = \tool::appConfig('URL.api');
        $config = \tool::appConfig('WXPAY');
        //异步回调地址
        $notify_url = $url.'/Wxpay/success';
        $order_num = $this->Post('order_num');
        if(empty($order_num)){
            $this->responseError('order_num error');
        }
        $money = $this->Post('money');
        if(empty($money)){
            $this->responseError('money error');
        }
        $money = bcmul($money,100,0);
        if($config['debug'] === true){
            $money = 1;
        }
        $wxpayTool = new \wxpayTool();
        $wxpayTool->set_appid($config['appid']);
        $wxpayTool->set_key($config['key']);
        $wxpayTool->set_mchid($config['mchid']);
        $wxpayTool->set_body($config['paytitle']);
        $wxpayTool->set_nonce_str(md5($order_num));
        $wxpayTool->set_outOradeNo($order_num);
        $wxpayTool->set_totalFee($money);
        $wxpayTool->set_notifyUrl($notify_url);
        $wxpayTool->set_client_ip($this->getClientIp());
        $get_data = $wxpayTool->appPay();
        if($get_data == false){
            $this->responseError('调用失败');
        }else{
            $this->responseSuccess($get_data);
        }
    }

    /**
     * 支付回调
     */
    function success(){

        $return_xml = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
        $this->response()->withHeader('Content-type', 'text/xml');

        //获取回调数据
        $xml = $this->request()->getBody();
        //xml转换成数组
        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

        if($data['return_code'] == "SUCCESS" &&$data['result_code'] == "SUCCESS") {
            $payMoney = bcdiv($data['total_fee'],100,2);
            $order_on = $data['out_trade_no'];

            $mq = \MQ::conn();
            $mq->startTransaction();
            //  todo------------逻辑处理
            $status = $this->paySuccess($order_on,$payMoney,1,json_encode($data));

            if($status){
                $mq->commit();
                $this->response()->write($return_xml);return;
            }else{
                $mq->rollback();
                $this->response()->write($return_xml);return;
            }
        }else{
            $this->response()->write($return_xml);return;
        }
    }
}
