<?php

return [
    'accessKeyId'           =>  '',                     //api网关的app_id(点击右上角AccessKey管理，在里面获取)
    'accessKeySecret'       =>  '',                     //api网关的app_secret

    'regionId'              =>  '',                     //地区ID(如：cn-shenzhen)
    'product'               =>  'CloudAPI',             //产品
    'version'               =>  '2016-07-14',           //api网关版本

    'api_group_id'          =>  '',                     //api分组ID
    'vpc_id'                =>  '',                     //专用网络ID，必须是同账户下可用的专用网络的ID(要先在阿里云api网关的VPC控制台创建，在专有网络VPC中获取：实例ID/名称)
    'instance_id'           =>  '',                     //专用网络中的实例ID(Ecs/SLB)
    'port'                  =>  '',                     //端口
    'vpc_name'              =>  '',                     //VPC授权名称

    'app_owner'             =>  '',                     //App拥有者的阿里云账号ID

    //定义返回结果
    'result_type'           =>  'JSON',                 //返回ContentType(JSON、TEXT、BINARY、XML、HTML)
    'result_sample'         =>  '{}',                   //返回结果示例
    'fail_result_sample'    =>  '',                     //失败返回结果示例

    'accept'                =>  ''                      //accept
];
