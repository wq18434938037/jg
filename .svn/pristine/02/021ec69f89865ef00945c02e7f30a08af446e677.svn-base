<?php
/**
 * 微信支付封装
 * User: Administrator
 * Date: 2018/11/6 0006
 * Time: 12:35
 */
class wxpayTool{
    private $appid;
    private $mchid;
    private $key;
    private $body;
    private $out_trade_no;
    private $total_fee;
    private $notify_url;
    private $paytitle;
    private $sign;
    private $nonce_str;
    private $client_ip;

    /**
     * 设置appid
     * @param $value
     */
    function set_appid($value){
        $this->appid = $value;
    }

    /**
     * 设置mchid
     * @param $value
     */
    function set_mchid($value){
        $this->mchid = $value;
    }

    /**
     * 设置key
     * @param $value
     */
    function set_key($value){
        $this->key = $value;
    }
    /**
     * 设置body
     * @param $value
     */
    function set_body($value){
        $this->body = $value;
    }

    /**
     * 设置商户订单号
     * @param $value
     */
    function set_outOradeNo($value){
        $this->out_trade_no = $value;
    }

    /**
     * 设置支付金额
     * @param $value
     */
    function set_totalFee($value){
        $this->total_fee = $value;
    }

    /**
     * 设置回调地址
     * @param $value
     */
    function set_notifyUrl($value){
        $this->notify_url = $value;
    }

    /**
     * 设置支付标题
     * @param $value
     */
    function set_paytitle($value){
        $this->paytitle = $value;
    }
    /**
     * 设置支付标题
     * @param $value
     */
    function set_nonce_str($value){
        $this->nonce_str = $value;
    }
    /**
     * 设置用户真实ip
     * @param $value
     */
    function set_client_ip($value){
        $this->client_ip = $value;
    }

    /**
     * 获取签名
     * @return mixed
     */
    function get_sign(){
        return $this->sign;
    }
    function get_out_trade_no(){
        return $this->out_trade_no;
    }


    /**
     * APP支付
     * @return bool|mixed
     */
    function appPay(){
        $newPara['appid'] = $this->appid; //微信appid
        $newPara['mch_id'] = $this->mchid; //商户号
        $newPara['nonce_str'] = $this->nonce_str; //32位随机字符串
        $newPara['body'] = \EasySwoole\EasySwoole\Config::getInstance()->getConf('WXPAY.paytitle'); //商品描述
        $newPara['out_trade_no'] = $this->out_trade_no; //商品订单号
        $newPara['total_fee'] = $this->total_fee; //商品总金额
        $newPara['spbill_create_ip'] = $this->client_ip; //用户终端IP
        $newPara['notify_url'] = $this->notify_url; //异步回调地址
        $newPara['trade_type'] = 'APP'; //交易类型H5 MWEB
        $this->sign = $this->getSign($newPara,$this->key);
        $newPara['sign'] = $this->sign; //签名
        $xmlData = $this->getWeChatXML($newPara); //把数组转化成xml格式
        //利用PHP的CURL包，将数据传给微信统一下单接口，返回正常的prepay_id
        $payUrl = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $get_data = $this->sendPrePayCurl($xmlData,$payUrl);
        if($get_data['return_code'] == "SUCCESS" && $get_data['result_code'] == "SUCCESS"){
            $data['appid'] = $get_data['appid'];
            $data['prepayid'] = $get_data['prepay_id'];
            $data['partnerid'] = $get_data['mch_id'];
            $data['package'] = 'Sign=WXPay';
            $data['timestamp'] = time();
            $data['noncestr'] = md5(time() . rand(1000,9999));
            $data['sign'] = $this->getSign($data,$this->key);
            $data['packages'] = $data['package'];
            $data['code'] = 1;
            return $data;
        }else{
            return false;
        }
    }


    /**
     * 微信退款
     */
    function wxrefund($out_refund_no,$price){
        $newPara['appid'] = $this->appid; //微信appid
        $newPara['mch_id'] = $this->mchid; //商户号
        $newPara['nonce_str'] = $this->nonce_str; //32位随机字符串
        $newPara['out_trade_no'] = $this->out_trade_no; //商品订单号
        $newPara['out_refund_no'] = $out_refund_no; //商户退款单号
        $newPara['total_fee'] = $this->total_fee; //订单总金额
        $newPara['refund_fee'] = $price; //退款总金额
        $this->sign = $this->getSign($newPara,$this->key);
        $newPara['sign'] = $this->sign; //签名
        $xmlData = $this->getWeChatXML($newPara); //把数组转化成xml格式
        //利用PHP的CURL包，将数据传给微信统一下单接口，返回正常的prepay_id
        $refundUrl = "https://api.mch.weixin.qq.com/secapi/pay/refund";
        $get_data = $this->sendRefundCurl($xmlData,$refundUrl);
        return $get_data;
    }

    /**
     * 付款码支付
     */
    function micropay($auth_code){//,$num=0
//        if($num == 60){
//            $this->reverse($this->out_trade_no);
//            return [false,'支付超时，请重新支付!'];
//        }
        $newPara['appid'] = $this->appid;
        $newPara['mch_id'] = $this->mchid;
        $newPara['nonce_str'] = $this->nonce_str;
        $newPara['body'] = \EasySwoole\EasySwoole\Config::getInstance()->getConf('WXPAY.paytitle'); //商品描述
        $newPara['out_trade_no'] = $this->out_trade_no; //商品订单号
        $newPara['total_fee'] = $this->total_fee; //订单总金额
        $newPara['spbill_create_ip'] = $this->client_ip; //用户终端IP
        $newPara['auth_code'] = $auth_code; //授权码
        $this->sign = $this->getSign($newPara,$this->key);
        $newPara['sign'] = $this->sign; //签名

        $xmlData = $this->getWeChatXML($newPara); //把数组转化成xml格式
        //利用PHP的CURL包，将数据传给微信统一下单接口，返回正常的prepay_id
        $payUrl = "https://api.mch.weixin.qq.com/pay/micropay";
        $get_data = $this->sendPrePayCurl($xmlData,$payUrl);

        if($get_data['return_code'] == 'SUCCESS' && $get_data['result_code'] == 'SUCCESS'){
            return [true,$get_data['return_msg']];
        }else{
            if($get_data['err_code_des']){
                return [false,$get_data['err_code_des']];
            }else{
                return [false,$get_data['return_msg']];
            }
        }

//        if($get_data['return_code'] == 'SUCCESS' && $get_data['result_code'] == 'SUCCESS'){
//            return [true,$get_data['return_msg']];
//        }else{
//            return [false,$get_data['err_code_des']];
////            usleep(500000);
////            $num++;
////            return $this->micropay($auth_code,$num);
//        }
    }

    /**
     * 如果收款码支付失败则撤销订单
     */
    function reverse($out_trade_no){
        $newPara['appid'] = $this->appid;
        $newPara['mch_id'] = $this->mchid;
        $newPara['out_trade_no'] = $out_trade_no;
        $newPara['nonce_str'] = $this->nonce_str;
        $sign = $this->getSign($newPara,$this->key);
        $newPara['sign'] = $sign;

        $xmlData = $this->getWeChatXML($newPara); //把数组转化成xml格式
        $payUrl = "https://api.mch.weixin.qq.com/secapi/pay/reverse";
        $get_data = $this->sendPrePayCurl($xmlData,$payUrl,true);
    }
    /**
     * 查询订单
     */
    function orderquery($out_trade_no){
        $newPara['appid'] = $this->appid;
        $newPara['mch_id'] = $this->mchid;
        $newPara['out_trade_no'] = $out_trade_no;
        $newPara['nonce_str'] = $this->nonce_str;
        $sign = $this->getSign($newPara,$this->key);
        $newPara['sign'] = $sign;

        $xmlData = $this->getWeChatXML($newPara); //把数组转化成xml格式
        $payUrl = "https://api.mch.weixin.qq.com/pay/orderquery";
        $get_data = $this->sendPrePayCurl($xmlData,$payUrl);
        if($get_data['return_code'] == 'SUCCESS' && $get_data['result_code'] == 'SUCCESS'){
            return [true,$get_data['trade_state']];
        }else{
            if($get_data['err_code_des']){
                return [false,$get_data['err_code_des']];
            }else{
                return [false,$get_data['return_msg']];
            }
        }
    }




    /**
     * 通过curl发送数据给微信接口的函数
     * @param $xmlData
     * @param $url
     * @return mixed
     */
    private function sendRefundCurl($xmlData,$url) {
        $header[] = "Content-type: text/xml";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        //设置证书
        //使用证书：cert 与 key 分别属于两个.pem文件
        curl_setopt($curl,CURLOPT_SSLCERTTYPE,'PEM');
        curl_setopt($curl,CURLOPT_SSLCERT, getcwd().'/apiclient_cert.pem');
        curl_setopt($curl,CURLOPT_SSLKEYTYPE,'PEM');
        curl_setopt($curl,CURLOPT_SSLKEY, getcwd().'/apiclient_key.pem');


        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $xmlData);
        $data = curl_exec($curl);
        if (curl_errno($curl)) {
            print curl_error($curl);
        }
        curl_close($curl);
        return $this->XMLDataParse($data);
    }










    /**
     * 签名
     * @param $Obj
     * @param $key
     * @return string
     */
    private function getSign($Obj,$key)
    {
        foreach ($Obj as $k => $v)
        {
            $Parameters[strtolower($k)] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //签名步骤二：在string后加入KEY
        $String = $String."&key=".$key; //填写key
        //签名步骤三：MD5加密
        $result_ = strtoupper(md5($String));
        return $result_;
    }

    /**
     * 将数组转成uri字符串
     * @param $paraMap
     * @param $urlencode
     * @return string
     */
    private function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v)
        {
            if($urlencode)
            {
                $v = urlencode($v);
            }
            $buff .= strtolower($k) . "=" . $v . "&";
        }
        $reqPar='';
        if (strlen($buff) > 0)
        {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }

    /**
     * 生成xml格式的函数
     * @param $newPara
     * @return string
     */
    private function getWeChatXML($newPara){
        $xmlData = "<xml>";
        foreach ($newPara as $key => $value) {
            $xmlData = $xmlData."<".$key.">".$value."</".$key.">";
        }
        $xmlData = $xmlData."</xml>";
        return $xmlData;
    }

    /**
     * 通过curl发送数据给微信接口的函数
     * @param $xmlData
     * @param $url
     * @return mixed
     */
    private function sendPrePayCurl($xmlData,$url,$useCert = false) {
        $header[] = "Content-type: text/xml";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $xmlData);

        if($useCert == true){
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            //证书文件请放入服务器的非web目录下
            $sslCertPath = EASYSWOOLE_COMMON . '/wx_cert/cert.pem';
            $sslKeyPath = EASYSWOOLE_COMMON . '/wx_cert/key.pem';
            curl_setopt($curl,CURLOPT_SSLCERTTYPE,'PEM');
            curl_setopt($curl,CURLOPT_SSLCERT, $sslCertPath);
            curl_setopt($curl,CURLOPT_SSLKEYTYPE,'PEM');
            curl_setopt($curl,CURLOPT_SSLKEY, $sslKeyPath);
        }

        $data = curl_exec($curl);
        if (curl_errno($curl)) {
            print curl_error($curl);
        }
        curl_close($curl);
        return $this->XMLDataParse($data);

    }
    /**
     * xml格式数据解析函数
     * @param $data
     * @return array
     */
    private function XMLDataParse($data){
        $msg = (array)simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
        return $msg;
    }
}