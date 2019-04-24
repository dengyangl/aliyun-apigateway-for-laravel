<?php

namespace AliYun\ApiGateWay\Controllers;

use App\Http\Controllers\Controller;
use AliYun\ApiGateWay\Requests\ApiRequest;
use AliYun\ApiGateWay\Services\ApiGateWay\ApiGateWay;
use Illuminate\Support\Str;

class ApiController extends Controller
{
    use ApiGateWay;

    /**
     * @description 查询定义中的API列表(默认)/查询已发布API列表
     * @author dyl
     * @param ApiRequest $request
     * @param group_id      指定的分组编号
     * @param stage_name    环境名称(查询已发布API列表)
     *                          RELEASE：线上
     *                          TEST：测试
     * @param api_id        指定的API编号
     * @param api_name      API名称(模糊匹配)
     * @param page_size     指定分页查询时每页行数，最大值100，默认值为10
     * @param page_number   指定要查询的页码，默认是1，起始是1
     * @param action_type   操作类型(默认：不传-DescribeApis；有传-DescribeDeployedApis)
     * @return mixed
     * @throws \AlibabaCloud\Client\Exception\ClientException
     */
    public function l(ApiRequest $request){
        $filter = request(['group_id', 'stage_name', 'api_id', 'api_name', 'page_size', 'page_number', 'action_type']);

        $result = $this->getAlibabaCloudRpcRequest();

        $options = [];

        $action = 'DescribeApis';

        //查询已发布API列表
        if (!empty($filter['action_type'])) {
            $action = 'DescribeDeployedApis';
            $options['query']['StageName'] = $filter['stage_name'];
        }

        if (!empty($filter['group_id'])) $options['query']['GroupId'] = $filter['group_id'];
        if (!empty($filter['api_id'])) $options['query']['ApiId'] = $filter['api_id'];
        if (!empty($filter['api_name'])) $options['query']['ApiName'] = $filter['api_name'];
        if (!empty($filter['page_size'])) $options['query']['PageSize'] = $filter['page_size'];
        if (!empty($filter['page_number'])) $options['query']['PageNumber'] = $filter['page_number'];

        $result = $result->action($action);

        if (!empty($options)) $result = $result->options($options);

        return $this->handleException($result);
    }

    /**
     * @description 查询API定义(详情)
     * @author dyl
     * @param $apiId    API的Id标识
     * @param group_id  API所在的分组编号
     * @return mixed
     * @throws \AlibabaCloud\Client\Exception\ClientException
     */
    public function getByApiId($apiId){
        $filter = request(['group_id']);

        $result = $this->getAlibabaCloudRpcRequest();


        $options = [
            'query' =>  [
                'ApiId' =>  $apiId
            ]
        ];

        if (!empty($filter['group_id'])) $options['query']['GroupId'] = $filter['group_id'];

        $result = $result->action('DescribeApi')->options($options);

        return $this->handleException($result);
    }

    /**
     * @description 添加api接口
     * @author dyl
     * @param ApiRequest $request
     * @param GroupId       string      指定的分组编号(API分组ID)
     * @param ApiName       string      设置API的名称，组内不允许重复。支持汉字，英文，数字，下划线，且只能以英文和汉字开头，4~50个字符
     *
     * @param Visibility    string      API是否公开:
     *                                      PUBLIC：公开，如选择此类型，该API的线上环境定义，会在所有用户的控制台“发现API”页面可见
     *                                      PRIVATE：不公开，如选择此类型，当该组API在云市场上架时，私有类型的API不会上架
     *
     * @param Description   string      API描述信息，最多180个字符 ---非必填
     *
     * @param AuthType      string      API安全认证类型:
     *                                      APP：只允许已授权的APP调用
     *                                      ANONYMOUS：允许匿名调用，设置为允许匿名调用需要注意：
     *                                          任何能够获取该API服务信息的人，都将能够调用该API。网关不会对调用者做身份认证，也无法设置按用户的流量控制，若开放该API请设置好按API的流量控制。
     *                                      OPENID：是一套基于 OAuth 2.0 协议的轻量级规范，提供通过RESTful APIs 进行身份交互的框架。可以使用 OpenID Connect 和您的自有账号系统无缝对接。详细介绍请参见 OpenID Connect。
     *                                      APPOPENID：同时进行OpenID Connect和阿里云APP认证
     *
     * @param AllowSignatureMethod  string  当AuthType为 APP认证 时，需要传该值明确签名算法。可选值如下，不传默认是HmacSHA256： ---非必填
     *                                          HmacSHA256。
     *                                          HmacSHA1,HmacSHA256
     *
     * @param ForceNonceCheck   Boolean     设置ForceNonceCheck为true, 请求时强制检查X-Ca-Nonce，这个是请求的唯一标识，一般使用UUID来标识。
     *                                      API网关收到这个参数后会校验这个参数的有效性，同样的值，15分内只能被使用一次。可以有效防止API的重放攻击。
     *                                      设置ForceNonceCheck为false, 则不检查。创建API时默认为true ---非必填
     *
     * @param DisableInternet   Boolean     设置DisableInternet为true, 仅支持内网调用API。设置DisableInternet为false, 则不限制调用。创建API时默认为false。 ---非必填
     * @param OpenIdConnectConfig   string  第三方账号认证OpenID Connect相关配置项。 ---非必填
     *
     *
     * 前端信息项
     * @param RequestConfig     string      Consumer向网关发送API请求的相关配置项
     *                                          RequestProtocol	    String	API 支持的协议类型，可以多选，多选情况下以英文逗号隔开，如：”HTTP,HTTPS”，取值为：HTTP、HTTPS
     *                                          RequestHttpMethod	String	HTTP Method，取值为：GET、POST、DELETE、PUT、HEADER、TRACE、PATCH、CONNECT、OPTIONS
     *                                          RequestPath	        String	API path，比如API的完全地址为http://api.a.com：8080/object/add?key1=value1&key2=value2，path是指/object/add这一部分
     *                                          RequestMode	        String	请求的模式，取值为：MAPPING、PASSTHROUGH，分别表示入参映射、入参透传
     *                                          BodyFormat	        String	POST/PUT请求时，表示数据以何种方式传递给服务器，取值为：FORM、STREAM，分别表示表单形式(k-v对应)、字节流形式。当RequestMode值为MAPPING时有效。
     *                                          PostBodyDescription String	Body描述
     *                                      例：{"RequestProtocol":"HTTP", "RequestHttpMethod":"GET", "RequestPath":"/v3/getUserTest/[userId]", "BodyFormat":"FORM", "PostBodyDescription":""}
     *
     * 后端服务调用信息项
     * @param ServiceConfig     string      网关向后端服务发送API请求的相关配置项
     *                                          ServiceProtocol	    String	后端服务协议类型，目前只支持HTTP/HTTPS/FunctionCompute
     *                                          ServiceAddress	    String	调用后端服务地址，比如后端服务完全地址为http://api.a.com:8080/object/add?key1=value1&key2=value2，ServiceAddress是指http://api.a.com:8080这一部分
     *                                          ServicePath	        String	调用后端服务path，比如后端服务完全地址为http://api.a.com:8080/object/add?key1=value1&key2=value2，ServicePath是指/object/add这一部分
     *                                          ServiceHttpMethod	String	调用后端服务HTTP协议时的Method，取值为：GET、POST、DELETE、PUT、HEADER、TRACE、PATCH、CONNECT、OPTIONS
     *                                          ServiceTimeout	    String	后端服务超时时间，单位：毫秒
     *
     *                                          ContentTypeCatagory	String	调用后端服务HTTP服务时，ContentType头的取值策略：
     *                                                                          DEFAULT：使用API网关默认的值
     *                                                                          CUSTOM：自定义
     *                                                                          CLIENT：使用客户端上行的ContentType的头
     *                                          ContentTypeValue	String	调用后端服务HTTP服务，ContentTypeCatagory的值为DEFAULT或者CUSTOM时，ContentType头的取值
     *
     *                                          Mock	            String	是否采取Mock模式，目前可以取值：
     *                                                                          TRUE：启用Mock模式
     *                                                                          FALSE：不启用Mock模式
     *                                          MockResult	        String	如果启用Mock模式，返回的结果
     *                                          MockStatusCode	    Integer	状态码，以兼容HTTP 1.1 Response Status Code的格式返回及其状态
     *                                          MockHeaders	        String	启用Mock时，自定义的Mock响应头相关信息，详情见MockHeader
     *                                              HeaderName	String	响应头名称
     *                                              HeaderValue	String	响应头值
     *
     *                                          ServiceVpcEnable	String	是否启用VPC通道，目前可以取值：
     *                                                                          TRUE：启用VPC通道
     *                                                                          FALSE：不启用VPC通 必须先添加VPC授权成功后才能启用
     *                                          VpcConfig	String	如果启用VPC通道，VPC通道相关配置项，详情见ApiAttributesType.md#VpcConfig
     *                                              VpcId	    String	专用网络ID
     *                                              InstanceId	String	专用网络中的实例ID（Ecs/SLB）
     *                                              Port	    Integer	实例对应的端口号
     *
     *                                          FunctionComputeConfig	String	后端服务为函数计算，函数计算后端相关的配置项，详情见FunctionComputeConfig
     *                                              fcRegionId	    String	函数计算所在Region
     *                                              serviceName	    String	函数计算定义的ServiceName
     *                                              functionName	String	函数计算定义的FunctionName
     *                                              roleArn	        String	Ram授权给API网关访问函数计算的arn
     *                                      例：{"ServiceProtocol":"HTTP", "ServiceHttpMethod":"GET", "ServiceAddress":"http://www.customerdomain.com", "ServiceTimeout":"1000", "ServicePath":"/v3/getUserTest/[userId]"}  "ServiceAddress": "http://119.23.231.106:9100"
     *
     * 前端入参信息项的类型
     * @param RequestParameters string      Consumer向网关发送API请求的参数描述
     *                                          ApiParameterName	String	参数名
     *                                          Location	        String	参数位置，取值为：BODY、HEAD、QUERY、PATH
     *                                          ParameterType	    String	参数类型，取值为：String、Int、Long、Float、Double、Boolean，分别表示字符、整型、长整型、单精度浮点型、双精度浮点型、布尔
     *                                          Required	        String	是否必填，取值为：REQUIRED、OPTIONAL，分别表示必填、不必填
     *                                          DefaultValue	    String	默认值
     *                                          DemoValue	        String	示例
     *                                          MaxValue	        Long	当ParameterType=Int、Long、Float、Double，参数的最大值限定
     *                                          MinValue	        Long	当ParameterType=Int、Long、Float、Double，参数的最小值限定
     *                                          MaxLength	        Long	当ParameterType=String，参数的最大长度限定
     *                                          MinLength	        Long	当ParameterType=String，参数的最小长度限定
     *                                          RegularExpression	String	当ParameterType=String，参数验证（正则表达式）
     *                                          JsonScheme	        String	当ParameterType=String，JSON验证(Json Scheme)
     *                                          EnumValue	        String	当ParameterType=Int、Long、Float、Double或String，允许输入的散列值，不同的值用英文的逗号分隔，形如：1,2,3,4,9或A,B,C,E,F
     *                                          DocShow	            String	文档可见，取值为：PUBLIC、PRIVATE
     *                                          DocOrder	        Integer	文档中顺序
     *                                          Description	        String	参数描述
     *                                      例：[{"ParameterType":"Number", "Required":"OPTIONAL", "isHide":false, "ApiParameterName":"age", "DefaultValue":"20", "DemoValue":"20", "Description":"年龄", "MinValue":18, "MaxValue":100, "Location":"Head"},
     *                                          {"ParameterType":"String", "Required":"OPTIONAL", "isHide":false, "ApiParameterName":"sex", "DefaultValue":"boy", "DemoValue":"boy", "Description":"性别", "EnumValue":"boy,girl", "Location":"Query"},
     *                                          {"ParameterType":"Number", "Required":"REQUIRED", "isHide":false, "ApiParameterName":"userId", "MaxLength":10, "MinValue":10000000, "MaxValue":100000000, "Location":"Path"},
     *                                          {"ApiParameterName":"CaClientIp", "ParameterLocation":{"name":"Head","orderNumber":0}, "Location":"Head", "ParameterType":"String", "Required":"REQUIRED", "Description":"客户端IP"},
     *                                          {"ApiParameterName":"constance", "ParameterLocation":{"name":"Head","orderNumber":0}, "Location":"Head", "ParameterType":"String", "Required":"REQUIRED", "DefaultValue":"constance", "Description":"constance"}]
     *
     * 后端服务调用入参信息项的类型
     * @param ServiceParameters string      网关向后端服务发送API请求的参数描述
     *                                          ServiceParameterName    string  后端参数名称
     *                                          Location                string  参数位置，取值为：BODY、HEAD、QUERY、PATH
     *                                          ParameterType           string  后端参数数据类型，取值为：STRING、NUMBER、BOOLEAN，分别表示字符、数值、布尔
     *                                          ParameterCatalog        string  请求参数的类型，取值为：REQUEST、CONSTANT、SYSTEM，分别表示普通请求参数，常量参数和系统参数。
     *                                                                          其中REQUEST是需要API调用者传值，CONSTANT、SYSTEM两种类型对API调用者不可见
     *                                      例：[{"ServiceParameterName":"age", "Location":"Head", "Type":"Number", "ParameterCatalog":"REQUEST"},
     *                                          {"ServiceParameterName":"sex", "Location":"Query", "Type":"String", "ParameterCatalog":"REQUEST"},
     *                                          {"ServiceParameterName":"userId", "Location":"Path", "Type":"Number", "ParameterCatalog":"REQUEST"},
     *                                          {"ServiceParameterName":"clientIp", "Location":"Head", "Type":"String", "ParameterCatalog":"SYSTEM"},
     *                                          {"ServiceParameterName":"constance", "Location":"Head", "Type":"String", "ParameterCatalog":"CONSTANT"}]
     *
     * 后端服务调用入参和前端入参映射
     * @param ServiceParametersMap string   Consumer向网关发送请求的参数和网关向后端服务发送的请求的参数的映射关系
     *                                          ServiceParameterName    String  后端参数名称
     *                                          RequestParameterName    String  对应前端入参名称，这个值必须存在于RequestParametersObject中，匹配于RequestParam.ApiParameterName
     *                                      例：[{"ServiceParameterName":"age", "RequestParameterName":"age"},
     *                                          {"ServiceParameterName":"sex", "RequestParameterName":"sex"},
     *                                          {"ServiceParameterName":"userId", "RequestParameterName":"userId"},
     *                                          {"ServiceParameterName":"clientIp", "RequestParameterName":"CaClientIp"},
     *                                          {"ServiceParameterName":"constance", "RequestParameterName":"constance"}]
     *
     * @param ResultType        String      后端服务返回应答的格式，目前可以设置为：JSON、TEXT、BINARY、XML、HTML。默认 JSON ---非必填
     * @param ResultSample	    String      后端服务返回应答的示例      ---非必填
     * @param FailResultSample  String      后端服务失败返回应答的示例   ---非必填
     * @param ErrorCodeSamples  String      后端服务返回的错误码示例    ---非必填
     *                                          Code	    String	错误码
     *                                          Message	    String	错误信息
     *                                          Description String	描述
     *
     * @param method            请求类型
     * @param path              请求url
     * @param params            请求参数
     * @param authorization     是否需要加Authorization验证
     * @param mock              mock模式(暂时不用)
     * @param publish_description   发布描述(是否发布)
     * @param is_authorize      是否授权
     * @param app_id            应用ID
     * @return mixed
     * @throws \AlibabaCloud\Client\Exception\ClientException
     */
    public function add(ApiRequest $request){
//        $data = request(['group_id', 'api_name', 'description',
//                         'request_config', 'service_config', 'request_parameters', 'service_parameters', 'service_parameters_map']);

        $data = request(['group_id', 'api_name', 'description', 'method', 'path', 'params', 'authorization', 'mock']);

        if (empty($data['group_id'])) $data['group_id'] = config('apiGateWay.api_group_id');
        if (empty($data['description'])) $data['description'] = $data['api_name'];

        //是否需要Authorization
        $is_authorization = false;
        if (!empty($data['authorization'])) $is_authorization = true;

        $method = Str::upper($data['method']);


        //request_config(前端信息项-Consumer向网关发送API请求的相关配置项)
        $location = 'query';            //get(列表/详情),delete(删除)
        $query_location = 'query';      //get(详情-一些参数是path,一些参数是query)

        if ($method == 'GET_DETAIL') {  //详情
            $method = 'GET';
            //$location = 'path';
        }

        $request_config_array = [
            'RequestProtocol'   => 'HTTP,HTTPS',
            'RequestHttpMethod' => $method,
            'RequestPath'       => $data['path']
        ];

        if ($method == 'POST' || $method == 'PUT') {
            $request_config_array['BodyFormat'] = 'FORM';
            $location = 'body';
            $query_location = 'body';
        }

        $request_config = json_encode($request_config_array);
        //request_config,end(前端信息项-Consumer向网关发送API请求的相关配置项)
        //dd($request_config);


        //service_config(后端服务调用信息项-网关向后端服务发送API请求的相关配置项)
        $service_config_array = [
            'ServiceProtocol'   =>  'HTTP',
            'ServiceHttpMethod' =>  $method,

            'ServiceVpcEnable'  =>  'TRUE',
            'VpcConfig' => [
                'VpcId'         =>  config('apiGateWay.vpc_id'),
                'InstanceId'    =>  config('apiGateWay.instance_id'),
                'Port'          =>  config('apiGateWay.port'),
            ],

            'ServiceTimeout'    =>  5000,
            'ServicePath'       =>  $data['path']
        ];
        $service_config = json_encode($service_config_array);
        //service_config,end(后端服务调用信息项-网关向后端服务发送API请求的相关配置项)
        //dd($service_config);


        //request_parameters, service_parameters, service_parameters_map
        //request_parameters(前端入参信息项的类型-Consumer向网关发送API请求的参数描述)
        //$request_parameters_array = [];
        $request_parameters_array = [
            [
                'ApiParameterName'  =>  'Accept',
                'ParameterType'     =>  'string',
                'DefaultValue'      =>  config('apiGateWay.accept'),
                //'Description'       =>  '',
                'Location'          =>  'Head'
            ]
        ];

        //service_parameters(后端服务调用入参信息项的类型-网关向后端服务发送API请求的参数描述)
        //$service_parameters_array = [];
        $service_parameters_array = [
            [
                'ServiceParameterName'  =>  'Accept',
                'Type'                  =>  'string',
                'Location'              =>  'Head',
                'ParameterCatalog'      =>  'CONSTANT'
            ]
        ];

        //service_parameters_map(后端服务调用入参和前端入参映射-Consumer向网关发送请求的参数和网关向后端服务发送的请求的参数的映射关系)
        //$service_parameters_map_array = [];
        $service_parameters_map_array = [
            [
                'ServiceParameterName'  =>  'Accept',
                'RequestParameterName'  =>  'Accept'
            ]
        ];

        if ($is_authorization) {
            array_push($request_parameters_array, [
                'ApiParameterName'  =>  'Authorization',
                'ParameterType'     =>  'string',
                //'DefaultValue'      =>  '',
                'Required'          =>  'REQUIRED',
                //'Description'       =>  '',
                'Location'          =>  'Head'
            ]);

            array_push($service_parameters_array, [
                'ServiceParameterName'  =>  'Authorization',
                'Type'                  =>  'string',
                'Location'              =>  'Head',
                'ParameterCatalog'      =>  'REQUEST'
            ]);

            array_push($service_parameters_map_array, [
                'ServiceParameterName'  =>  'Authorization',
                'RequestParameterName'  =>  'Authorization'
            ]);
        }

        if (!empty($data['params'])) {
            $params_array = json_decode($data['params'], true);
            if (!empty($params_array)) {
                foreach ($params_array as $k => $v) {

                    if ($query_location == 'query') {
                        if (isset($v['is_path']) && $v['is_path']) {     //查询,且有设置is_path
                            $location = 'path';
                        } else {
                            $location = 'query';        //查询,没有设置is_path
                        }
                    }

                    $request_parameters_array[] = [
                        'ApiParameterName'  =>  $v['name'],
                        'ParameterType'     =>  $v['type'],
                        //'DefaultValue'      =>  '',
                        'Required'          =>  $v['is_required'] ? 'REQUIRED' : 'OPTIONAL',
                        'Description'       =>  $v['description'],
                        'Location'          =>  $location
                    ];

                    $service_parameters_array[] = [
                        'ServiceParameterName'  =>  $v['name'],
                        'Type'                  =>  $v['type'],
                        'Location'              =>  $location,
                        'ParameterCatalog'      =>  'REQUEST'
                    ];

                    $service_parameters_map_array[] = [
                        'ServiceParameterName'  =>  $v['name'],
                        'RequestParameterName'  =>  $v['name']
                    ];

                }
            }
        }

        $request_parameters = json_encode($request_parameters_array);
        $service_parameters = json_encode($service_parameters_array);
        $service_parameters_map = json_encode($service_parameters_map_array);
        //request_parameters, service_parameters, service_parameters_map,end
        //dd($request_parameters, $service_parameters, $service_parameters_map);


        //dd($data);

        $result = $this->getAlibabaCloudRpcRequest();

        $options = [
            'query' => [
                'GroupId'               =>  $data['group_id'],
                'ApiName'               =>  $data['api_name'],
                'Visibility'            =>  'PRIVATE',
                'Description'           =>  $data['description'],
                'AuthType'              =>  'APP',

                'RequestConfig'	        =>  $request_config,
                'ServiceConfig'	        =>  $service_config
            ]
        ];

        if (!empty($request_parameters)) $options['query']['RequestParameters'] = $request_parameters;
        if (!empty($service_parameters)) $options['query']['ServiceParameters'] = $service_parameters;
        if (!empty($service_parameters_map)) $options['query']['ServiceParametersMap'] = $service_parameters_map;


        $result = $result->action('CreateApi')->options($options);

        $handle_result = $this->handleException($result);


        $ApiId = !empty($handle_result['ApiId']) ? $handle_result['ApiId'] : 0;

        //发布接口
        $publish_description = request('publish_description');
        if (!empty($ApiId) && !empty($publish_description)) {
            $this->apiPublish($ApiId, 'RELEASE', $publish_description, $data['group_id']);
            $this->apiPublish($ApiId, 'TEST', $publish_description, $data['group_id']);
        }

        //授权接口
        $is_authorize = request('is_authorize');
        if (!empty($ApiId) && !empty($is_authorize)) {
            $app_id = request('app_id');
            $this->apiAuthorize($ApiId, 'RELEASE', $app_id, $data['group_id']);
            $this->apiAuthorize($ApiId, 'TEST', $app_id, $data['group_id']);
        }

        return $handle_result;
    }

    /**
     * @description 修改api接口
     * @author dyl
     * @param ApiRequest $request
     * @param GroupId       string      指定的分组编号(API分组ID)
     * @param ApiId	        String      API的Id标识
     * @param ApiName       string      设置API的名称，组内不允许重复。支持汉字，英文，数字，下划线，且只能以英文和汉字开头，4~50个字符
     *
     * @param Visibility    string      API是否公开:
     *                                      PUBLIC：公开，如选择此类型，该API的线上环境定义，会在所有用户的控制台“发现API”页面可见
     *                                      PRIVATE：不公开，如选择此类型，当该组API在云市场上架时，私有类型的API不会上架
     *
     * @param Description   string      API描述信息，最多180个字符 ---非必填
     *
     * @param AuthType      string      API安全认证类型:
     *                                      APP：只允许已授权的APP调用
     *                                      ANONYMOUS：允许匿名调用，设置为允许匿名调用需要注意：
     *                                          任何能够获取该API服务信息的人，都将能够调用该API。网关不会对调用者做身份认证，也无法设置按用户的流量控制，若开放该API请设置好按API的流量控制。
     *                                          “ANONYMOUS”API不建议上架云市场，网关无法对调用者区分计量，也无法限制调用次数，若所在分组要上架云市场，建议将该API转移至其他分组，或将类型设置为“私有”，或选择“阿里云APP”认证方式。
     *                                      OPENID：是一套基于 OAuth 2.0 协议的轻量级规范，提供通过RESTful APIs 进行身份交互的框架。可以使用 OpenID Connect 和您的自有账号系统无缝对接。详细介绍请参见 OpenID Connect。
     *                                      APPOPENID：同时进行OpenID Connect和阿里云APP认证
     *
     * @param AllowSignatureMethod  string  当AuthType为 APP认证 时，需要传该值明确签名算法。可选值如下，不传默认是HmacSHA256： ---非必填
     *                                          HmacSHA256。
     *                                          HmacSHA1,HmacSHA256
     *
     * @param ForceNonceCheck   Boolean     设置ForceNonceCheck为true, 请求时强制检查X-Ca-Nonce，这个是请求的唯一标识，一般使用UUID来标识。
     *                                      API网关收到这个参数后会校验这个参数的有效性，同样的值，15分内只能被使用一次。可以有效防止API的重放攻击。
     *                                      设置ForceNonceCheck为false, 则不检查。修改时，不设置则不修改原来的取值 ---非必填
     *
     * @param DisableInternet   Boolean     设置DisableInternet为true, 仅支持内网调用API。设置DisableInternet为false, 则不限制调用。修改API时，不设置则不修改原来的取值。 ---非必填
     * @param OpenIdConnectConfig   string  第三方账号认证OpenID Connect相关配置项。 ---非必填
     *
     *
     * 前端信息项
     * @param RequestConfig     string      Consumer向网关发送API请求的相关配置项
     *                                          RequestProtocol	    String	API 支持的协议类型，可以多选，多选情况下以英文逗号隔开，如：”HTTP,HTTPS”，取值为：HTTP、HTTPS
     *                                          RequestHttpMethod	String	HTTP Method，取值为：GET、POST、DELETE、PUT、HEADER、TRACE、PATCH、CONNECT、OPTIONS
     *                                          RequestPath	        String	API path，比如API的完全地址为http://api.a.com：8080/object/add?key1=value1&key2=value2，path是指/object/add这一部分
     *                                          RequestMode	        String	请求的模式，取值为：MAPPING、PASSTHROUGH，分别表示入参映射、入参透传
     *                                          BodyFormat	        String	POST/PUT请求时，表示数据以何种方式传递给服务器，取值为：FORM、STREAM，分别表示表单形式(k-v对应)、字节流形式。当RequestMode值为MAPPING时有效。
     *                                          PostBodyDescription String	Body描述
     *                                      例：{"RequestProtocol":"HTTP", "RequestHttpMethod":"GET", "RequestPath":"/v3/getUserTest/[userId]", "BodyFormat":"FORM", "PostBodyDescription":""}
     *
     * 后端服务调用信息项
     * @param ServiceConfig     string      网关向后端服务发送API请求的相关配置项
     *                                          ServiceProtocol	    String	后端服务协议类型，目前只支持HTTP/HTTPS/FunctionCompute
     *                                          ServiceAddress	    String	调用后端服务地址，比如后端服务完全地址为http://api.a.com:8080/object/add?key1=value1&key2=value2，ServiceAddress是指http://api.a.com:8080这一部分
     *                                          ServicePath	        String	调用后端服务path，比如后端服务完全地址为http://api.a.com:8080/object/add?key1=value1&key2=value2，ServicePath是指/object/add这一部分
     *                                          ServiceHttpMethod	String	调用后端服务HTTP协议时的Method，取值为：GET、POST、DELETE、PUT、HEADER、TRACE、PATCH、CONNECT、OPTIONS
     *                                          ServiceTimeout	    String	后端服务超时时间，单位：毫秒
     *
     *                                          ContentTypeCatagory	String	调用后端服务HTTP服务时，ContentType头的取值策略：
     *                                                                          DEFAULT：使用API网关默认的值
     *                                                                          CUSTOM：自定义
     *                                                                          CLIENT：使用客户端上行的ContentType的头
     *                                          ContentTypeValue	String	调用后端服务HTTP服务，ContentTypeCatagory的值为DEFAULT或者CUSTOM时，ContentType头的取值
     *
     *                                          Mock	            String	是否采取Mock模式，目前可以取值：
     *                                                                          TRUE：启用Mock模式
     *                                                                          FALSE：不启用Mock模式
     *                                          MockResult	        String	如果启用Mock模式，返回的结果
     *                                          MockStatusCode	    Integer	状态码，以兼容HTTP 1.1 Response Status Code的格式返回及其状态
     *                                          MockHeaders	        String	启用Mock时，自定义的Mock响应头相关信息，详情见MockHeader
     *                                              HeaderName	String	响应头名称
     *                                              HeaderValue	String	响应头值
     *
     *                                          ServiceVpcEnable	String	是否启用VPC通道，目前可以取值：
     *                                                                          TRUE：启用VPC通道
     *                                                                          FALSE：不启用VPC通 必须先添加VPC授权成功后才能启用
     *                                          VpcConfig	String	如果启用VPC通道，VPC通道相关配置项，详情见ApiAttributesType.md#VpcConfig
     *                                              VpcId	    String	专用网络ID
     *                                              InstanceId	String	专用网络中的实例ID（Ecs/SLB）
     *                                              Port	    Integer	实例对应的端口号
     *
     *                                          FunctionComputeConfig	String	后端服务为函数计算，函数计算后端相关的配置项，详情见FunctionComputeConfig
     *                                              fcRegionId	    String	函数计算所在Region
     *                                              serviceName	    String	函数计算定义的ServiceName
     *                                              functionName	String	函数计算定义的FunctionName
     *                                              roleArn	        String	Ram授权给API网关访问函数计算的arn
     *                                      例：{"ServiceProtocol":"HTTP", "ServiceHttpMethod":"GET", "ServiceAddress":"http://www.customerdomain.com", "ServiceTimeout":"1000", "ServicePath":"/v3/getUserTest/[userId]"}  "ServiceAddress": "http://119.23.231.106:9100"
     *
     * 前端入参信息项的类型
     * @param RequestParameters string      Consumer向网关发送API请求的参数描述
     *                                          ApiParameterName	String	参数名
     *                                          Location	        String	参数位置，取值为：BODY、HEAD、QUERY、PATH
     *                                          ParameterType	    String	参数类型，取值为：String、Int、Long、Float、Double、Boolean，分别表示字符、整型、长整型、单精度浮点型、双精度浮点型、布尔
     *                                          Required	        String	是否必填，取值为：REQUIRED、OPTIONAL，分别表示必填、不必填
     *                                          DefaultValue	    String	默认值
     *                                          DemoValue	        String	示例
     *                                          MaxValue	        Long	当ParameterType=Int、Long、Float、Double，参数的最大值限定
     *                                          MinValue	        Long	当ParameterType=Int、Long、Float、Double，参数的最小值限定
     *                                          MaxLength	        Long	当ParameterType=String，参数的最大长度限定
     *                                          MinLength	        Long	当ParameterType=String，参数的最小长度限定
     *                                          RegularExpression	String	当ParameterType=String，参数验证（正则表达式）
     *                                          JsonScheme	        String	当ParameterType=String，JSON验证(Json Scheme)
     *                                          EnumValue	        String	当ParameterType=Int、Long、Float、Double或String，允许输入的散列值，不同的值用英文的逗号分隔，形如：1,2,3,4,9或A,B,C,E,F
     *                                          DocShow	            String	文档可见，取值为：PUBLIC、PRIVATE
     *                                          DocOrder	        Integer	文档中顺序
     *                                          Description	        String	参数描述
     *                                      例：[{"ParameterType":"Number", "Required":"OPTIONAL", "isHide":false, "ApiParameterName":"age", "DefaultValue":"20", "DemoValue":"20", "Description":"年龄", "MinValue":18, "MaxValue":100, "Location":"Head"},
     *                                          {"ParameterType":"String", "Required":"OPTIONAL", "isHide":false, "ApiParameterName":"sex", "DefaultValue":"boy", "DemoValue":"boy", "Description":"性别", "EnumValue":"boy,girl", "Location":"Query"},
     *                                          {"ParameterType":"Number", "Required":"REQUIRED", "isHide":false, "ApiParameterName":"userId", "MaxLength":10, "MinValue":10000000, "MaxValue":100000000, "Location":"Path"},
     *                                          {"ApiParameterName":"CaClientIp", "ParameterLocation":{"name":"Head","orderNumber":0}, "Location":"Head", "ParameterType":"String", "Required":"REQUIRED", "Description":"客户端IP"},
     *                                          {"ApiParameterName":"constance", "ParameterLocation":{"name":"Head","orderNumber":0}, "Location":"Head", "ParameterType":"String", "Required":"REQUIRED", "DefaultValue":"constance", "Description":"constance"}]
     *
     * 后端服务调用入参信息项的类型
     * @param ServiceParameters string      网关向后端服务发送API请求的参数描述
     *                                          ServiceParameterName    string  后端参数名称
     *                                          Location                string  参数位置，取值为：BODY、HEAD、QUERY、PATH
     *                                          ParameterType           string  后端参数数据类型，取值为：STRING、NUMBER、BOOLEAN，分别表示字符、数值、布尔
     *                                          ParameterCatalog        string  请求参数的类型，取值为：REQUEST、CONSTANT、SYSTEM，分别表示普通请求参数，常量参数和系统参数。
     *                                                                          其中REQUEST是需要API调用者传值，CONSTANT、SYSTEM两种类型对API调用者不可见
     *                                      例：[{"ServiceParameterName":"age", "Location":"Head", "Type":"Number", "ParameterCatalog":"REQUEST"},
     *                                          {"ServiceParameterName":"sex", "Location":"Query", "Type":"String", "ParameterCatalog":"REQUEST"},
     *                                          {"ServiceParameterName":"userId", "Location":"Path", "Type":"Number", "ParameterCatalog":"REQUEST"},
     *                                          {"ServiceParameterName":"clientIp", "Location":"Head", "Type":"String", "ParameterCatalog":"SYSTEM"},
     *                                          {"ServiceParameterName":"constance", "Location":"Head", "Type":"String", "ParameterCatalog":"CONSTANT"}]
     *
     * 后端服务调用入参和前端入参映射
     * @param ServiceParametersMap string   Consumer向网关发送请求的参数和网关向后端服务发送的请求的参数的映射关系
     *                                          ServiceParameterName    String  后端参数名称
     *                                          RequestParameterName    String  对应前端入参名称，这个值必须存在于RequestParametersObject中，匹配于RequestParam.ApiParameterName
     *                                      例：[{"ServiceParameterName":"age", "RequestParameterName":"age"},
     *                                          {"ServiceParameterName":"sex", "RequestParameterName":"sex"},
     *                                          {"ServiceParameterName":"userId", "RequestParameterName":"userId"},
     *                                          {"ServiceParameterName":"clientIp", "RequestParameterName":"CaClientIp"},
     *                                          {"ServiceParameterName":"constance", "RequestParameterName":"constance"}]
     *
     * @param ResultType        String      后端服务返回应答的格式，目前可以设置为：JSON、TEXT、BINARY、XML、HTML ---非必填
     * @param ResultSample	    String      后端服务返回应答的示例      ---非必填
     * @param FailResultSample  String      后端服务失败返回应答的示例   ---非必填
     * @param ErrorCodeSamples  String      后端服务返回的错误码示例    ---非必填
     *                                          Code	    String	错误码
     *                                          Message	    String	错误信息
     *                                          Description String	描述
     *
     * @param method            请求类型
     * @param path              请求url
     * @param params            请求参数
     * @param authorization     是否需要加Authorization验证
     * @param mock              mock模式(暂时不用)
     * @param publish_description   发布描述(是否发布)
     * @return mixed
     * @throws \AlibabaCloud\Client\Exception\ClientException
     */
    public function put(ApiRequest $request){
        $data = request(['group_id', 'api_id', 'api_name', 'description', 'method', 'path', 'params', 'authorization', 'mock']);

        if (empty($data['group_id'])) $data['group_id'] = config('apiGateWay.api_group_id');
        if (empty($data['description'])) $data['description'] = $data['api_name'];

        //是否需要Authorization
        $is_authorization = false;
        if (!empty($data['authorization'])) $is_authorization = true;

        $method = Str::upper($data['method']);


        //request_config(前端信息项-Consumer向网关发送API请求的相关配置项)
        $location = 'query';            //get(列表/详情),delete(删除)
        $query_location = 'query';      //get(详情-一些参数是path,一些参数是query)

        if ($method == 'GET_DETAIL') {  //详情
            $method = 'GET';
            //$location = 'path';
        }

        $request_config_array = [
            'RequestProtocol'   => 'HTTP,HTTPS',
            'RequestHttpMethod' => $method,
            'RequestPath'       => $data['path']
        ];

        if ($method == 'POST' || $method == 'PUT') {
            $request_config_array['BodyFormat'] = 'FORM';
            $location = 'body';
            $query_location = 'body';
        }

        $request_config = json_encode($request_config_array);
        //request_config,end(前端信息项-Consumer向网关发送API请求的相关配置项)
        //dd($request_config);


        //service_config(后端服务调用信息项-网关向后端服务发送API请求的相关配置项)
        $service_config_array = [
            'ServiceProtocol'   =>  'HTTP',
            'ServiceHttpMethod' =>  $method,
            'ServiceVpcEnable'  =>  'TRUE',
            'VpcConfig'         =>  [
                'VpcId'         =>  config('apiGateWay.vpc_id'),
                'InstanceId'    =>  config('apiGateWay.instance_id'),
                'Port'          =>  config('apiGateWay.port'),
            ],
            'ServiceTimeout'    =>  5000,
            'ServicePath'       =>  $data['path']
        ];
        $service_config = json_encode($service_config_array);
        //service_config,end(后端服务调用信息项-网关向后端服务发送API请求的相关配置项)
        //dd($service_config);


        //request_parameters, service_parameters, service_parameters_map
        //request_parameters(前端入参信息项的类型-Consumer向网关发送API请求的参数描述)
        $request_parameters_array = [
            [
                'ApiParameterName'  =>  'Accept',
                'ParameterType'     =>  'string',
                'DefaultValue'      =>  config('apiGateWay.accept'),
                //'Description'       =>  '',
                'Location'          =>  'Head'
            ]
        ];

        //service_parameters(后端服务调用入参信息项的类型-网关向后端服务发送API请求的参数描述)
        $service_parameters_array = [
            [
                'ServiceParameterName'  =>  'Accept',
                'Type'                  =>  'string',
                'Location'              =>  'Head',
                'ParameterCatalog'      =>  'CONSTANT'
            ]
        ];

        //service_parameters_map(后端服务调用入参和前端入参映射-Consumer向网关发送请求的参数和网关向后端服务发送的请求的参数的映射关系)
        $service_parameters_map_array = [
            [
                'ServiceParameterName'  =>  'Accept',
                'RequestParameterName'  =>  'Accept'
            ]
        ];

        if ($is_authorization) {
            array_push($request_parameters_array, [
                'ApiParameterName'  =>  'Authorization',
                'ParameterType'     =>  'string',
                //'DefaultValue'      =>  '',
                'Required'          =>  'REQUIRED',
                //'Description'       =>  '',
                'Location'          =>  'Head'
            ]);

            array_push($service_parameters_array, [
                'ServiceParameterName'  =>  'Authorization',
                'Type'                  =>  'string',
                'Location'              =>  'Head',
                'ParameterCatalog'      =>  'REQUEST'
            ]);

            array_push($service_parameters_map_array, [
                'ServiceParameterName'  =>  'Authorization',
                'RequestParameterName'  =>  'Authorization'
            ]);
        }

        if (!empty($data['params'])) {
            $params_array = json_decode($data['params'], true);
            if (!empty($params_array)) {
                foreach ($params_array as $k => $v) {

                    if ($query_location == 'query') {
                        if (isset($v['is_path']) && $v['is_path']) {     //查询,且有设置is_path
                            $location = 'path';
                        } else {
                            $location = 'query';                        //查询,没有设置is_path
                        }
                    }

                    $request_parameters_array[] = [
                        'ApiParameterName'  =>  $v['name'],
                        'ParameterType'     =>  $v['type'],
                        //'DefaultValue'      =>  '',
                        'Required'          =>  $v['is_required'] ? 'REQUIRED' : 'OPTIONAL',
                        'Description'       =>  $v['description'],
                        'Location'          =>  $location
                    ];

                    $service_parameters_array[] = [
                        'ServiceParameterName'  =>  $v['name'],
                        'Type'                  =>  $v['type'],
                        'Location'              =>  $location,
                        'ParameterCatalog'      =>  'REQUEST'
                    ];

                    $service_parameters_map_array[] = [
                        'ServiceParameterName'  =>  $v['name'],
                        'RequestParameterName'  =>  $v['name']
                    ];

                }
            }
        }

        $request_parameters = json_encode($request_parameters_array);
        $service_parameters = json_encode($service_parameters_array);
        $service_parameters_map = json_encode($service_parameters_map_array);
        //request_parameters, service_parameters, service_parameters_map,end
        //dd($request_parameters, $service_parameters, $service_parameters_map);


        $result = $this->getAlibabaCloudRpcRequest();

        $options = [
            'query' => [
                'GroupId'               =>  $data['group_id'],
                'ApiId'                 =>  $data['api_id'],
                'ApiName'               =>  $data['api_name'],
                'Visibility'            =>  'PRIVATE',
                'Description'           =>  $data['description'],
                'AuthType'              =>  'APP',

                'RequestConfig'	        =>  $request_config,
                'ServiceConfig'	        =>  $service_config
            ]
        ];

        if (!empty($request_parameters)) $options['query']['RequestParameters'] = $request_parameters;
        if (!empty($service_parameters)) $options['query']['ServiceParameters'] = $service_parameters;
        if (!empty($service_parameters_map)) $options['query']['ServiceParametersMap'] = $service_parameters_map;

        $result = $result->action('ModifyApi')->options($options);

        $handle_result = $this->handleException($result);


        //发布接口
        $publish_description = request('publish_description');
        if (!empty($publish_description)) {
            $this->apiPublish($data['api_id'], 'RELEASE', $publish_description, $data['group_id']);
            $this->apiPublish($data['api_id'], 'TEST', $publish_description, $data['group_id']);
        }
        return $handle_result;
    }

    /**
     * @description 添加完api接口并发布
     * @author dyl
     * @param $ApiId                    //API编号
     * @param $stage_name               //运行环境名称
     *                                      RELEASE：线上
     *                                      PRE：预发
     *                                      TEST：测试
     * @param $publish_description      //发布描述
     * @param string $group_id          分组编号
     * @throws \AlibabaCloud\Client\Exception\ClientException
     */
    private function apiPublish($ApiId, $stage_name, $publish_description, $group_id = ''){

        $result = $this->getAlibabaCloudRpcRequest();

        $options = [
            'query' => [
                'ApiId'         =>  $ApiId,
                'StageName'     =>  $stage_name,
                'Description'   =>  $publish_description
            ]
        ];

        if (!empty($group_id)) $options['query']['GroupId'] = $group_id;

        $result = $result->action('DeployApi')->options($options);

        $this->handleException($result);
    }

    /**
     * @description 添加完api接口并授权
     * @author dyl
     * @param $api_ids              String  指定要操作的API编号
     * @param $stage_name           String  环境名称，取值为：
     *                                          RELEASE：线上
     *                                          TEST：测试
     * @param $app_id               //Long    应用(app)编号，系统生成，全局唯一(应用ID)
     * @param string $group_id      String  API分组ID，系统生成，全局唯一
     * @param string $description   String  授权说明
     * @param int $auth_vaild_time  String  授权有效时间的截止时间，请设置格林尼治标准时间(GMT), 如果为空，即为授权永久有效。
     * @return mixed
     * @throws \AlibabaCloud\Client\Exception\ClientException
     */
    private function apiAuthorize($api_ids, $stage_name, $app_id, $group_id = '', $description = '', $auth_vaild_time = 0){

        $result = $this->getAlibabaCloudRpcRequest();

        $options = [
            'query' => [
                'StageName' =>  $stage_name,
                'AppId'     =>  $app_id
            ]
        ];

        if (!empty($group_id)) $options['query']['GroupId'] = $group_id;
        if (!empty($api_ids)) $options['query']['ApiIds'] = $api_ids;
        if (!empty($description)) $options['query']['Description'] = $description;
        if (!empty($auth_vaild_time)) $options['query']['AuthVaildTime'] = $auth_vaild_time;

        $result = $result->action('SetApisAuthorities')->options($options);

        return $this->handleException($result);
    }


    /**
     * @description 删除API(单个/多个)
     * @author dyl
     * @param ApiRequest $request
     * @param group_id      分组编号
     * @param api_id        API编号
     * @return mixed
     * @throws \AlibabaCloud\Client\Exception\ClientException
     */
    public function del(ApiRequest $request){
        $data = request(['group_id', 'api_id']);

        $result = $this->getAlibabaCloudRpcRequest();

        $api_id_array = explode(',', $data['api_id']);
        if (count($api_id_array) > 1) {

            $handle_result_array = [];

            foreach ($api_id_array as $k => $v) {
                $options = [
                    'query' => [
                        'ApiId' => $v
                    ]
                ];

                if (!empty($data['group_id'])) $options['query']['GroupId'] = $data['group_id'];

                $result = $result->action('DeleteApi')->options($options);

                $handle_result = $this->handleException($result);

                $handle_result_array[]['RequestId'] = $handle_result['RequestId'];
            }

            return $handle_result_array;

        } else {

            $options = [
                'query' => [
                    'ApiId' => $data['api_id']
                ]
            ];

            if (!empty($data['group_id'])) $options['query']['GroupId'] = $data['group_id'];

            $result = $result->action('DeleteApi')->options($options);

            return $this->handleException($result);

        }
    }


    /**
     * @description 发布API(单个/多个)
     * @author dyl
     * @param ApiRequest $request
     * @param group_id      分组编号
     * @param api_id        API编号
     * @param stage_name    运行环境名称
     *                          RELEASE：线上
     *                          PRE：预发
     *                          TEST：测试
     * @param description   本次发布备注说明
     * @return mixed
     * @throws \AlibabaCloud\Client\Exception\ClientException
     */
    public function publish(ApiRequest $request){
        $data = request(['group_id', 'api_id', 'stage_name', 'description']);

        $result = $this->getAlibabaCloudRpcRequest();

        $api_id_array = explode(',', $data['api_id']);
        if (count($api_id_array) > 1) {

            $handle_result_array = [];

            foreach ($api_id_array as $k => $v) {
                $options = [
                    'query' => [
                        'ApiId'         =>  $v,
                        'StageName'     =>  $data['stage_name'],
                        'Description'   =>  $data['description']
                    ]
                ];

                if (!empty($data['group_id'])) $options['query']['GroupId'] = $data['group_id'];

                $result = $result->action('DeployApi')->options($options);

                $handle_result = $this->handleException($result);

                $handle_result_array[]['RequestId'] = $handle_result['RequestId'];
            }

            return $handle_result_array;

        } else {

            $options = [
                'query' => [
                    'ApiId'         =>  $data['api_id'],
                    'StageName'     =>  $data['stage_name'],
                    'Description'   =>  $data['description']
                ]
            ];

            if (!empty($data['group_id'])) $options['query']['GroupId'] = $data['group_id'];

            $result = $result->action('DeployApi')->options($options);

            return $this->handleException($result);

        }
    }

    /**
     * @description 下线API(单个/多个)
     * @author dyl
     * @param ApiRequest $request
     * @param group_id      分组编号
     * @param api_id        API编号
     * @param stage_name    运行环境名称
     *                          RELEASE：线上
     *                          TEST：测试
     * @return mixed
     * @throws \AlibabaCloud\Client\Exception\ClientException
     */
    public function offline(ApiRequest $request){
        $data = request(['group_id', 'api_id', 'stage_name']);

        $result = $this->getAlibabaCloudRpcRequest();

        $api_id_array = explode(',', $data['api_id']);
        if (count($api_id_array) > 1) {

            $handle_result_array = [];

            foreach ($api_id_array as $k => $v) {
                $options = [
                    'query' => [
                        'ApiId'     =>  $v,
                        'StageName' =>  $data['stage_name']
                    ]
                ];

                if (!empty($data['group_id'])) $options['query']['GroupId'] = $data['group_id'];

                $result = $result->action('AbolishApi')->options($options);

                $handle_result = $this->handleException($result);

                $handle_result_array[]['RequestId'] = $handle_result['RequestId'];
            }

            return $handle_result_array;

        } else {

            $options = [
                'query' => [
                    'ApiId'     =>  $data['api_id'],
                    'StageName' =>  $data['stage_name']
                ]
            ];

            if (!empty($data['group_id'])) $options['query']['GroupId'] = $data['group_id'];

            $result = $result->action('AbolishApi')->options($options);

            return $this->handleException($result);

        }
    }

}
