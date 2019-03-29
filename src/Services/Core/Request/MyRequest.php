<?php

namespace AliYun\ApiGateWay\Services\Core\Request;

trait MyRequest
{
    /**
     * 获取当前访问的方法
     * @author dyl
     * */
    public function getAction(){
        $pathInfo = $this->getPathInfo();
        return substr(strrchr($pathInfo, "/"), 1);
    }
}
