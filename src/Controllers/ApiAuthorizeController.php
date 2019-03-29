<?php

namespace AliYun\ApiGateWay\Controllers;

use AliYun\ApiGateWay\Requests\ApiAuthorizeRequest;
use AliYun\ApiGateWay\Services\ApiGateWay\ApiGateWay;
use App\Http\Controllers\Controller;

class ApiAuthorizeController extends Controller
{
    use ApiGateWay;

    /**
     * @description 查询可授权的app列表/查询指定app已授权的API列表(默认)
     * @author dyl
     * @param ApiAuthorizeRequest $request
     * @param app_id        Long    App的唯一标识(应用ID)
     * @param page_size     Integer 指定分页查询时每页行数，最大值100，默认值为10
     * @param page_number   Integer 指定要查询的页码，默认是1，起始是1
     * @param action_type           操作接口类型,默认(不传):DescribeApps 有传东西:DescribeAuthorizedApis
     * @return mixed
     * @throws \AlibabaCloud\Client\Exception\ClientException
     */
    public function l(ApiAuthorizeRequest $request){
        $filter = request(['app_id', 'page_size', 'page_number', 'action_type']);

        $result = $this->getAlibabaCloudRpcRequest();

        //查询指定app已授权的API列表
        $action = 'DescribeAuthorizedApis';

        if (empty($filter['action_type'])) {    //查询指定app已授权的API列表
            $options = [
                'query' =>  [
                    'AppId' =>  $filter['app_id']
                ]
            ];
        } else {                                //查询可授权的app列表
            $action = 'DescribeApps';

            $options = [
                'query' => [
                    'AppOwner'  =>  config('apiGateWay.app_owner')
                ]
            ];

            if (!empty($filter['app_id'])) $options['query']['AppId'] = $filter['app_id'];
        }


        if (!empty($filter['page_size'])) $options['query']['PageSize'] = $filter['page_size'];
        if (!empty($filter['page_number'])) $options['query']['PageNumber'] = $filter['page_number'];
        //dd($options);

        $result = $result->action($action)->options($options);

        return $this->handleException($result);
    }

    /**
     * @description 给指定app添加多个API的访问权限(单个/多个api)
     * @author dyl
     * @param ApiAuthorizeRequest $request
     * @param group_id          String  API分组ID,系统生成,全局唯一
     * @param stage_name        String  环境名称，取值为：
     *                                      RELEASE：线上
     *                                      TEST：测试
     * @param app_id            Long    应用编号，系统生成，全局唯一(应用ID)
     * @param api_ids           String  指定要操作的API编号，支持输入多个，","分隔，最多支持100个(ApiIds＝baacc592e63a4cb6a41920d9d3f91f38,jkscc489e63a4cb6a41920d9d3f92d78)
     * @param description       String  授权说明
     * @param auth_vaild_time   String  授权有效时间的截止时间，请设置格林尼治标准时间(GMT), 如果为空，即为授权永久有效。
     * @return mixed
     * @throws \AlibabaCloud\Client\Exception\ClientException
     */
    public function add(ApiAuthorizeRequest $request){
        $data = request(['group_id', 'stage_name', 'app_id', 'api_ids', 'description', 'auth_vaild_time']);

        $result = $this->getAlibabaCloudRpcRequest();

        $options = [
            'query' => [
                'StageName' =>  $data['stage_name'],
                'AppId'     =>  $data['app_id']
            ]
        ];

        if (!empty($data['group_id'])) $options['query']['GroupId'] = $data['group_id'];
        if (!empty($data['api_ids'])) $options['query']['ApiIds'] = $data['api_ids'];
        if (!empty($data['description'])) $options['query']['Description'] = $data['description'];
        if (!empty($data['auth_vaild_time'])) $options['query']['AuthVaildTime'] = $data['auth_vaild_time'];

        $result = $result->action('SetApisAuthorities')->options($options);

        return $this->handleException($result);
    }

    /**
     * @description 批量撤销指定app对多个API的访问权限(单个/多个api)
     * @author dyl
     * @param ApiAuthorizeRequest $request
     * @param group_id      String  API分组ID，系统生成，全局唯一
     * @param stage_name    String  环境名称，取值为：
     *                                  RELEASE：线上
     *                                  TEST：测试
     * @param api_ids       String  指定要操作的API编号，支持输入多个，","分隔，最多支持100个(ApiIds＝baacc592e63a4cb6a41920d9d3f91f38,jkscc489e63a4cb6a41920d9d3f92d78)
     * @param app_id        Long    应用(app)编号，系统生成，全局唯一(应用ID)
     * @return mixed
     * @throws \AlibabaCloud\Client\Exception\ClientException
     */
    public function del(ApiAuthorizeRequest $request){
        $data = request(['group_id', 'stage_name', 'api_ids', 'app_id']);

        $result = $this->getAlibabaCloudRpcRequest();

        $options = [
            'query' => [
                'StageName' =>  $data['stage_name'],
                'ApiIds'    =>  $data['api_ids'],
                'AppId'     =>  $data['app_id']
            ]
        ];

        if (!empty($data['group_id'])) $options['query']['GroupId'] = $data['group_id'];

        $result = $result->action('RemoveApisAuthorities')->options($options);

        return $this->handleException($result);
    }
}
