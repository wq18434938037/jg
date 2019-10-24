<?php
namespace App\HttpController;
use App\HttpController\Base\Base;
use Grafika\Grafika;
use think\Exception;

class Index extends Base{
    function index(){
        $this->responseSuccess([
            $this->getClientIp(),
            $this->getMobileType(),
        ]);
    }
}
