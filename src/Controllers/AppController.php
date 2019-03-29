<?php

namespace AliYun\ApiGateWay\Controllers;

use App\Http\Controllers\Controller;
use AliYun\ApiGateWay\Requests\AppRequest;
use AliYun\ApiGateWay\Services\ApiGateWay\ApiGateWay;

class AppController extends Controller
{
    use ApiGateWay;

    /**
     * @description 应用列表
     * @author dyl
     * @param AppRequest $request
     * @param app_id        App的编号
     * @param page_size     每页行数，最大值100，默认值为10
     * @param page_number   页码，默认是1，起始是1
     * @return mixed
     * @throws \AlibabaCloud\Client\Exception\ClientException
     */
    public function l(AppRequest $request){
        $filter = request(['app_id', 'page_size', 'page_number']);

        $result = $this->getAlibabaCloudRpcRequest();

        $options = [];

        if (!empty($filter['app_id'])) $options['query']['AppId'] = $filter['app_id'];
        if (!empty($filter['page_number'])) $options['query']['PageNumber'] = $filter['page_number'];
        if (!empty($filter['page_size'])) $options['query']['PageSize'] = $filter['page_size'];

        $result = $result->action('DescribeAppAttributes');

        if (!empty($options)) $result = $result->options($options);

        return $this->handleException($result);
    }

    /**
     * @description 添加应用
     * @author dyl
     * @param AppRequest $request
     * @param app_name      应用名称(支持汉字、英文字母、数字、英文格式的下划线，且必须以字母或汉字开始，长度限制为4~26个字符，1个中文占2个字符)
     * @param description   APP描述信息，长度不超过180个字符
     * @return mixed
     * @throws \AlibabaCloud\Client\Exception\ClientException
     */
    public function add(AppRequest $request){
        $data = request(['app_name', 'description']);

        $result = $this->getAlibabaCloudRpcRequest();

        $options = [
            'query' => [
                'AppName'   =>  $data['app_name']
            ]
        ];

        if (!empty($data['description'])) $options['query']['Description'] = $data['description'];

        $result = $result->action('CreateApp')->options($options);

        return $this->handleException($result);
    }

    /**
     * @description 修改应用
     * @author dyl
     * @param AppRequest $request
     * @param app_id        应用ID
     * @param app_name      应用名称(支持汉字、英文字母、数字、英文格式的下划线，且必须以字母或汉字开始，长度限制为4~26个字符，1个中文占2个字符)
     * @param description   APP描述信息，长度不超过180个字符
     * @return mixed
     * @throws \AlibabaCloud\Client\Exception\ClientException
     */
    public function put(AppRequest $request){
        $data = request(['app_id', 'app_name', 'description']);

        $result = $this->getAlibabaCloudRpcRequest();

        $options = [
            'query' => [
                'AppId'     =>  $data['app_id']
            ]
        ];

        if (!empty($data['app_name'])) $options['query']['AppName'] = $data['app_name'];
        if (!empty($data['description'])) $options['query']['Description'] = $data['description'];

        $result = $result->action('ModifyApp')->options($options);

        return $this->handleException($result);
    }

    /**
     * @description 删除应用
     * @author dyl
     * @param AppRequest $request
     * @param app_id        应用ID
     * @param app_name      应用名称(支持汉字、英文字母、数字、英文格式的下划线，且必须以字母或汉字开始，长度限制为4~26个字符，1个中文占2个字符)
     * @param description   APP描述信息，长度不超过180个字符
     * @return mixed
     * @throws \AlibabaCloud\Client\Exception\ClientException
     */
    public function del(AppRequest $request){
        $data = request(['app_id']);

        $result = $this->getAlibabaCloudRpcRequest();

        $options = [
            'query' => [
                'AppId' =>  $data['app_id']
            ]
        ];

        $result = $result->action('DeleteApp')->options($options);

        return $this->handleException($result);
    }


    /**
     * @description 查询指定app密钥信息
     * @author dyl
     * @param $appId    //应用ID
     * @return mixed
     * @throws \AlibabaCloud\Client\Exception\ClientException
     */
    public function getSecretByAppId($appId){
        $result = $this->getAlibabaCloudRpcRequest();

        $options = [
            'query' => [
                'AppId' => $appId
            ]
        ];

        $result = $result->action('DescribeAppSecurity')->options($options);

        return $this->handleException($result);
    }

    /**
     * @description 重置指定app(应用)密钥
     * @author dyl
     * @param AppRequest $request
     * @param app_key   App的Key，用于调用API时使用
     * @return mixed
     * @throws \AlibabaCloud\Client\Exception\ClientException
     */
    public function putSecret(AppRequest $request){
        $data = request(['app_key']);

        $result = $this->getAlibabaCloudRpcRequest();

        $options = [
            'query' => [
                'AppKey' => $data['app_key']
            ]
        ];

        $result = $result->action('ResetAppSecret')->options($options);

        return $this->handleException($result);
    }
}
