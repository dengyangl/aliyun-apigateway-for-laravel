<?php

namespace AliYun\ApiGateWay\Controllers;

use App\Http\Controllers\Controller;
use AliYun\ApiGateWay\Requests\ApiGroupRequest;
use AliYun\ApiGateWay\Services\ApiGateWay\ApiGateWay;

class ApiGroupController extends Controller
{
    use ApiGateWay;

    /**
     * @description 查询API分组列表
     * @author dyl
     * @param ApiGroupRequest $request
     * @param group_id      API分组ID
     * @param group_name    API组名称
     * @param page_size     每页行数，最大值100，默认值为10
     * @param page_number   页码，默认是1，起始是1
     * @return mixed
     * @throws \AlibabaCloud\Client\Exception\ClientException
     */
    public function l(ApiGroupRequest $request){
        $filter = request(['group_id', 'group_name', 'page_number', 'page_size']);

        $result = $this->getAlibabaCloudRpcRequest();

        $options = [];

        if (!empty($filter['group_id'])) $options['query']['GroupId'] = $filter['group_id'];
        if (!empty($filter['group_name'])) $options['query']['GroupName'] = $filter['group_name'];
        if (!empty($filter['page_number'])) $options['query']['PageNumber'] = $filter['page_number'];
        if (!empty($filter['page_size'])) $options['query']['PageSize'] = $filter['page_size'];

        $result = $result->action('DescribeApiGroups');

        if (!empty($options)) $result = $result->options($options);

        return $this->handleException($result);
    }

    /**
     * @description 查询API分组详情
     * @author dyl
     * @param $groupId      API分组ID
     * @return mixed
     * @throws \AlibabaCloud\Client\Exception\ClientException
     */
    public function getByGroupId($groupId){
        $result = $this->getAlibabaCloudRpcRequest();

        $options = [
            'query' => [
                'GroupId' => $groupId
            ]
        ];

        $result = $result->action('DescribeApiGroup')->options($options);

        return $this->handleException($result);
    }

    /**
     * @description 创建API分组
     * @author dyl
     * @param ApiGroupRequest $request
     * @param group_name    分组名称
     * @param description   分组描述(不超过180个字符)
     * @return mixed
     * @throws \AlibabaCloud\Client\Exception\ClientException
     */
    public function add(ApiGroupRequest $request){
        $data = request(['group_name', 'description']);

        $result = $this->getAlibabaCloudRpcRequest();

        $options = [
            'query' => [
                'GroupName' => $data['group_name']
            ]
        ];

        if (!empty($data['description'])) $options['query']['Description'] = $data['description'];

        $result = $result->action('CreateApiGroup')->options($options);

        return $this->handleException($result);
    }

    /**
     * @description 修改API分组
     * @author dyl
     * @param ApiGroupRequest $request
     * @param group_id      API分组ID
     * @param group_name    分组名称
     * @param description   分组描述
     * @return mixed
     * @throws \AlibabaCloud\Client\Exception\ClientException
     */
    public function put(ApiGroupRequest $request){
        $data = request(['group_id', 'group_name', 'description']);

        $result = $this->getAlibabaCloudRpcRequest();

        $options = [
            'query' => [
                'GroupId' => $data['group_id']
            ]
        ];

        if (!empty($data['group_name'])) $options['query']['GroupName'] = $data['group_name'];
        if (!empty($data['description'])) $options['query']['Description'] = $data['description'];

        $result = $result->action('ModifyApiGroup')->options($options);

        return $this->handleException($result);
    }

    /**
     * @description 删除API分组
     * @author dyl
     * @param ApiGroupRequest $request
     * @param group_id      API分组ID
     * @return mixed
     * @throws \AlibabaCloud\Client\Exception\ClientException
     */
    public function del(ApiGroupRequest $request){
        $data = request(['group_id']);

        $result = $this->getAlibabaCloudRpcRequest();

        $options = [
            'query' => [
                'GroupId' => $data['group_id']
            ]
        ];

        $result = $result->action('DeleteApiGroup')->options($options);

        return $this->handleException($result);
    }



    /**
     * @description 查询API分组环境详情
     * @author dyl
     * @param group_id          API分组ID
     * @param stage_id          环境ID
     * @return mixed
     * @throws \AlibabaCloud\Client\Exception\ClientException
     */
    public function getByGroupIdAndStageId($groupId, $stageId){
        $result = $this->getAlibabaCloudRpcRequest();

        $options = [
            'query' => [
                'GroupId'   =>  $groupId,
                'StageId'   =>  $stageId
            ]
        ];

        $result = $result->action('DescribeApiStage')->options($options);

        return $this->handleException($result);
    }

    /**
     * @description 添加API分组环境变量
     * @author dyl
     * @param ApiGroupRequest $request
     * @param group_id          API分组ID
     * @param stage_id          环境ID
     * @param variable_name     变量名，区分大小写
     * @param variable_value    变量值
     * @return mixed
     * @throws \AlibabaCloud\Client\Exception\ClientException
     */
    public function addEnv(ApiGroupRequest $request){
        $data = request(['group_id', 'stage_id', 'variable_name', 'variable_value']);

        $result = $this->getAlibabaCloudRpcRequest();

        $options = [
            'query' => [
                'GroupId'       =>  $data['group_id'],
                'StageId'       =>  $data['stage_id'],
                'VariableName'  =>  $data['variable_name']
            ]
        ];

        if (!empty($data['variable_value'])) $options['query']['VariableValue'] = $data['variable_value'];

        $result = $result->action('CreateApiStageVariable')->options($options);

        return $this->handleException($result);
    }

    /**
     * @description 删除API分组环境变量
     * @author dyl
     * @param ApiGroupRequest $request
     * @param group_id          API分组ID
     * @param stage_id          环境ID
     * @param variable_name     变量名，区分大小写
     * @return mixed
     * @throws \AlibabaCloud\Client\Exception\ClientException
     */
    public function delEnv(ApiGroupRequest $request){
        $data = request(['group_id', 'stage_id', 'variable_name']);

        $result = $this->getAlibabaCloudRpcRequest();

        $options = [
            'query' => [
                'GroupId'       =>  $data['group_id'],
                'StageId'       =>  $data['stage_id'],
                'VariableName'  =>  $data['variable_name']
            ]
        ];

        $result = $result->action('DeleteApiStageVariable')->options($options);

        return $this->handleException($result);
    }
}
