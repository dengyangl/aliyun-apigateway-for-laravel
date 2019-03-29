<?php

namespace AliYun\ApiGateWay\Controllers;

use App\Http\Controllers\Controller;
use AliYun\ApiGateWay\Requests\VpcRequest;
use AliYun\ApiGateWay\Services\ApiGateWay\ApiGateWay;

class VpcController extends Controller
{
    use ApiGateWay;

    /**
     * @description 查询授权的Vpc列表
     * @author dyl
     * @param VpcRequest $request
     * @param page_size     指定分页查询时每页行数，最大值100，默认值为10
     * @param page_number   指定要查询的页码，默认是1，起始是1
     * @return mixed
     * @throws \AlibabaCloud\Client\Exception\ClientException
     */
    public function l(VpcRequest $request){
        $filter = request(['page_size', 'page_number']);

        $result = $this->getAlibabaCloudRpcRequest();

        $options = [];

        if (!empty($filter['page_number'])) $options['query']['PageNumber'] = $filter['page_number'];
        if (!empty($filter['page_size'])) $options['query']['PageSize'] = $filter['page_size'];

        $result = $result->action('DescribeVpcAccesses');

        if (!empty($options)) $result = $result->options($options);

        return $this->handleException($result);
    }

    /**
     * @description 添加授权
     * @author dyl
     * @param VpcRequest $request
     * @param vpc_id        专用网络Id，必须是同账户下可用的专用网络的ID(要先在阿里云api网关的VPC控制台创建)
     * @param instance_id   专用网络中的实例Id(ECS/负载均衡)(要先在阿里云购买ECS服务器)
     * @param port          实例对应的端口号
     * @param name          自定义授权名称，需要保持唯一，不能重复
     * @return mixed
     * @throws \AlibabaCloud\Client\Exception\ClientException
     */
    public function add(VpcRequest $request){
        $data = request(['vpc_id', 'instance_id', 'port', 'name']);

        $result = $this->getAlibabaCloudRpcRequest();

        $options = [
            'query' => [
                'VpcId'         =>  $data['vpc_id'],
                'InstanceId'    =>  $data['instance_id'],
                'Port'          =>  $data['port'],
                'Name'          =>  $data['name']
            ]
        ];

        $result = $result->action('SetVpcAccess')->options($options);

        return $this->handleException($result);
    }

    /**
     * @description 撤销授权
     * @author dyl
     * @param VpcRequest $request
     * @param vpc_id        专用网络Id，必须是同账户下可用的专用网络的ID(要先在阿里云api网关的VPC控制台创建)
     * @param instance_id   专用网络中的实例Id(ECS/负载均衡)(要先在阿里云购买ECS服务器)
     * @param port          实例对应的端口号
     * @return mixed
     * @throws \AlibabaCloud\Client\Exception\ClientException
     */
    public function del(VpcRequest $request){
        $data = request(['vpc_id', 'instance_id', 'port']);

        $result = $this->getAlibabaCloudRpcRequest();

        $options = [
            'query' => [
                'VpcId'         =>  $data['vpc_id'],
                'InstanceId'    =>  $data['instance_id'],
                'Port'          =>  $data['port']
            ]
        ];

        $result = $result->action('RemoveVpcAccess')->options($options);

        return $this->handleException($result);
    }
}
