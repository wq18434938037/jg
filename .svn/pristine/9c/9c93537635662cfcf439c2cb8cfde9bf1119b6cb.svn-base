<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/14 0014
 * Time: 11:58
 */

namespace App\HttpController;
use App\HttpController\Base\Base;

use OSS\OssClient;
use OSS\Core\OssException;
class Upload extends Base{
    private static $config = null;
    function newOss(){
        if(self::$config === null){
            self::$config = \tool::appConfig('ALIOSS');
        }
        return true;
    }
    /**
     * 多文件上传
     */
    function uploads(){
        $this->newOss();
        $file_name = 'upload';
        $file = $this->request()->getUploadedFile($file_name);
        $max_size = (int)$this->Post('max_size',5);
        $min_num = (int)$this->Post('min_num',1);
        $max_num = (int)$this->Post('max_num',9);
        $file_type = strtolower($this->Post('file_type','png,jpg,jpeg'));
        $file_type = explode(',',$file_type);
        if($max_num > 30){
            $max_num = 30;
        }
        if($max_size > 1024){
            $max_size = 1024;
        }
        if(empty($file)){
            $this->responseError('请选择要上传的文件');
        }
        if(!is_array($file)){
            $this->responseError('本接口仅支持多文件上传');
        }
        if(count($file) > 20){
            foreach($file as $k=>$v){
                unlink($file[$k]->getTempName());
            }
            $this->responseError('最多支持上传20个文件');
        }

        foreach($file as $k=>$v){
            if($file[$k]->getSize() > ($max_size*1024*1024)){
                foreach($file as $k1=>$v1){
                    unlink($file[$k1]->getTempName());
                }
                $this->responseError($file[$k]->getClientFilename() . '超过文件最大限制,请压缩至'.$max_size.'MB以内。');
            }
            $suffix = substr(strrchr($file[$k]->getClientFilename(), '.'), 1);
            if(!in_array($suffix,$file_type)){
                foreach($file as $k1=>$v1){
                    unlink($file[$k1]->getTempName());
                }
                $this->responseError('请上传 '.implode('|',$file_type).' 类型的文件。');
            }
            $file_data[] = [
                'tempName' => $file[$k]->getTempName(),
                'size' => $file[$k]->getSize(),
                'error' => $file[$k]->getError(),
                'clientFileName' => $file[$k]->getClientFilename(),
                'clientMediaType' => $file[$k]->getClientMediaType(),
            ];
        }
        if(count($file_data) < $min_num){
            foreach($file as $k=>$v){
                unlink($file[$k]->getTempName());
            }
            $this->responseError('至少上传' .$min_num. '个文件');
        }
        if(count($file_data) > $max_num){
            foreach($file as $k=>$v){
                unlink($file[$k]->getTempName());
            }
            $this->responseError('最多上传' .$max_num. '个文件');
        }
        //  获取oss配置
//        if(self::$config === null){
//            self::$config = getConfigV2('ALIOSS');
//        }
        $config = self::$config;

        //  循环上传图片
        foreach($file_data as $k=>$v){

            $fileName = $v['clientFileName'];
            //  获取文件后缀名
            $suffix = substr(strrchr($fileName, '.'), 1);
            //  生成图片路径
            $object = "images/".date('Ym')."/".md5($this->createRandOrdernum('file_'))."." . $suffix;
            //  临时文件路径
            $filePath = $v['tempName'];
            try{
                $ossClient = new OssClient($config['accesskey_id'],$config['accesskey_secret'], $config['endpoint']);
                $ossClient->uploadFile($config['bucket'], $object,$filePath);
                unlink($filePath);
            } catch(OssException $e){
                foreach($file_data as $v1){
                    unlink($v1['tempName']);
                }
                $this->responseError('上传失败');
            }
            $returnArr[] = '/' . $object;
        }
        $this->responseSuccess($returnArr);
    }

    /**
     * 本地文件上传至阿里云OSS
     * @param $tempName 图片源文件
     * @param $suffix 默认png
     * @return string OSS返回路径
     */
    function localUpload($tempName,$suffix=null){
        $this->newOss();
        $config = self::$config;
        #   todo 使用oss上传图片
        if(empty($suffix)){
            $suffix = 'png';
        }
        $object = "images/".date('Ym')."/".md5($this->createRandOrdernum('file_'))."." . $suffix;
        try{
            $ossClient = new OssClient($config['accesskey_id'],$config['accesskey_secret'], $config['endpoint']);
            $ossClient->uploadFile($config['bucket'], $object,$tempName);
            //  删除临时文件
            unlink($tempName);
        } catch(OssException $e){
            unlink($tempName);
            return false;
        }
        return '/' . $object;
    }

    /**
     * 删除Oss文件
     */
    public function delFile($path){
        $this->newOss();
        $config = self::$config;
        if(is_string($path)){
            $path[] = $path;
        }
        if(!is_array($path)){
            return false;
        }
        try{
            $ossClient = new OssClient($config['accesskey_id'],$config['accesskey_secret'], $config['endpoint']);

            $ossClient->deleteObjects($config['bucket'], $path);
        } catch(OssException $e) {
            return false;
        }
        return true;
    }
}