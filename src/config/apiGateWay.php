<?php

return [

    'accessKeyId'       =>  '',
    'accessKeySecret'   =>  '',

    'regionId'          =>  '',             //地区ID
    'product'           =>  'CloudAPI',     //产品
    'version'           =>  '2016-07-14',   //api网关版本

    'api_group_id'      =>  '',             //api分组ID
    'vpc_id'            =>  '',             //专用网络ID(要先在阿里云api网关的VPC控制台创建)
    'instance_id'       =>  '',             //专用网络中的实例ID(Ecs/SLB)(要先在阿里云购买ECS服务器)
    'port'              =>  '80',           //端口

    'app_owner'         =>  '',             //App拥有者的阿里云账号ID

    'accept'            =>  ''              //accept

];
