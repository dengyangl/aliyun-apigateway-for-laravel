# aliyun-apigateway-for-laravel
阿里云api网关，以接口形式进行管理(以Laravel 5.7为例，结合了dingo和jwt)

### 使用该扩展前，请先安装以下扩展
- 项目根目录composer.json文件的安装方式：

    "require": {
    
        ...    
        "dingo/api": "^2.1",
        "tymon/jwt-auth": "1.*@rc",
        "alibabacloud/sdk": "^1.2"
        ...
    }

- 命令行的安装方式：

    dingo扩展：
    composer require "dingo/api: ^2.1"

    jwt扩展：
    composer require "tymon/jwt-auth: 1.*@rc"

    阿里云api网关sdk：
    composer require alibabacloud/sdk

### 安装该composer包
    composer require aliyun-api-manage/apigateway

### 使用
- 在config/app.php文件中添加以下内容：
    'providers' => [
    
        ...
        AliYun\ApiGateWay\ServiceProvider::class,
        ...
    ]
    
- 发布config配置文件(选择相应的扩展包进行发布)

    php artisan vendor:publish --provider="AliYun\ApiGateWay\ServiceProvider"

- 配置config/apiGateWay.php文件中的信息

- 添加访问的路由

    注：

    1.v1：此处结合了dingo的路由版本控制；

    2.用postman请求时，如果dingo开启了严格模式，需要在Headers中添加 application/prs.xxx.v1+json，xxx替换为.env文件定义的API_SUBTYPE

    
    //阿里云API网关
    $api->version('v1', function ($api) {
    
        $api->group([
            'namespace' => 'AliYun\ApiGateWay\Controllers',
            'prefix' => 'apiGateWay'
        ], function ($api) {
    
            //VPC授权
            $api->group(['prefix' => 'vpc'], function ($api) {
                $api->get('/', 'VpcController@l');                              //列表
                $api->post('/', 'VpcController@add');                           //添加
                $api->delete('/', 'VpcController@del');                         //撤销
            });
    
            //应用
            $api->group(['prefix' => 'app'], function ($api) {
                $api->get('/', 'AppController@l');                              //列表
                $api->post('/', 'AppController@add');                           //添加
                $api->put('/', 'AppController@put');                            //修改
                $api->delete('/', 'AppController@del');                         //删除
    
                $api->get('/secret/{appId}', 'AppController@getSecretByAppId'); //根据App的编号,查询app密钥
                $api->put('/secret', 'AppController@putSecret');                //重置指定app(应用)密钥
            });
    
            //API分组+环境变量
            $api->group(['prefix' => 'apiGroup'], function ($api) {
    
                //API分组
                $api->get('/', 'ApiGroupController@l');                         //列表
                $api->get('/{groupId}', 'ApiGroupController@getByGroupId');     //详情
                $api->post('/', 'ApiGroupController@add');                      //添加
                $api->put('/', 'ApiGroupController@put');                       //修改
                $api->delete('/', 'ApiGroupController@del');                    //删除
    
                //环境变量
                $api->get('/env/{groupId}/{stageId}', 'ApiGroupController@getByGroupIdAndStageId'); //环境详情
                $api->post('/env', 'ApiGroupController@addEnv');                                    //添加
                $api->delete('/env', 'ApiGroupController@delEnv');                                  //删除
    
            });
    
            //API
            $api->group(['prefix' => 'api'], function ($api) {
                $api->get('/', 'ApiController@l');                              //查询定义中的API列表/查询已发布API列表
                $api->get('/{apiId}', 'ApiController@getByApiId');              //查询API定义(详情)
                $api->post('/', 'ApiController@add');                           //添加
                $api->put('/', 'ApiController@put');                            //修改
                $api->delete('/', 'ApiController@del');                         //删除
    
                $api->put('/publish', 'ApiController@publish');                 //发布
                $api->put('/offline', 'ApiController@offline');                 //下线
            });
    
            //API授权
            $api->group(['prefix' => 'apiAuthorize'], function ($api) {
                $api->get('/', 'ApiAuthorizeController@l');                     //可授权的app列表
                $api->post('/', 'ApiAuthorizeController@add');                  //给指定app添加多个API的访问权限
                $api->delete('/', 'ApiAuthorizeController@del');                //批量撤销指定app对多个API的访问权限
            });
    
        });
    
    });


- 接口的使用

    添加：POST
    
    读取：GET
    
    修改：PUT
    
    删除：DELETE

> * 一、应用
>> * 添加：/api/apiGateWay/app
>>> * 参数：

            app_name       应用名称       必填    
            description    APP描述信息
    
>> * 读取：/api/apiGateWay/app
>>> * 参数：

            app_id       App的编号   
            page_size    每页行数  
            page_number  页码
     
>> * 修改：/api/apiGateWay/app
>>> * 参数：

            app_id        应用ID    必填
            app_name      应用名称
            description   APP描述信息
      
>> * 删除：/api/apiGateWay/app
>>> * 参数：

            app_id        应用ID    必填
            app_name      应用名称
            description   APP描述信息

>> * 查询指定app密钥信息(GET)：/api/apiGateWay/app/secret/[AppId]
>>> * 参数：

            AppId        应用ID    必填

>> * 重置指定app(应用)密钥(PUT)：/api/apiGateWay/app/secret
>>> * 参数：

            app_key      APP应用的Key    必填
      

> * 二、API分组+环境
>> * 添加：/api/apiGateWay/apiGroup
>>> * 参数：

            group_name       分组名称       必填    
            description      分组描述
    
>> * 读取：/api/apiGateWay/apiGroup
>>> * 参数：

            group_id       API分组ID  
            group_name     API组名称
            page_size      每页行数  
            page_number    页码
      
>> * 查询API分组详情(GET)：/api/apiGateWay/apiGroup/[groupId]
>>> * 参数：

            group_id       API分组ID    必填
     
>> * 修改：/api/apiGateWay/apiGroup
>>> * 参数：

            group_id        API分组ID    必填
            group_name      分组名称
            description     分组描述
      
>> * 删除：/api/apiGateWay/apiGroup
>>> * 参数：

            group_id        API分组ID    必填


>> * 添加API分组环境变量：/api/apiGateWay/apiGroup/env
>>> * 参数：

            group_id          API分组ID            必填
            stage_id          环境ID               必填
            variable_name     变量名，区分大小写      必填
            variable_value    变量值             
      
>> * 查询API分组环境详情(GET)：/api/apiGateWay/apiGroup/env/[groupId]/[stageId]
>>> * 参数：

            groupId   API分组ID     必填
            stageId   环境ID        必填

>> * 删除API分组环境变量：/api/apiGateWay/apiGroup/env
>>> * 参数：

            group_id          API分组ID            必填
            stage_id          环境ID               必填
            variable_name     变量名，区分大小写      必填


> * 三、VPC授权
>> * 添加：/api/apiGateWay/vpc
>>> * 参数：

            vpc_id        专用网络Id，必须是同账户下可用的专用网络的ID(要先在阿里云api网关的VPC控制台创建)  必填
            instance_id   专用网络中的实例Id(ECS/负载均衡)(要先在阿里云购买ECS服务器)                   必填
            port          实例对应的端口号                                                        必填
            name          自定义授权名称                                                         必填
    
>> * 查询授权的Vpc列表：/api/apiGateWay/vpc
>>> * 参数：

            page_size      每页行数  
            page_number    页码
      
>> * 撤销授权：/api/apiGateWay/vpc
>>> * 参数：

            vpc_id        专用网络Id，必须是同账户下可用的专用网络的ID(要先在阿里云api网关的VPC控制台创建)  必填
            instance_id   专用网络中的实例Id(ECS/负载均衡)(要先在阿里云购买ECS服务器)                   必填
            port          实例对应的端口号                                                        必填


> * 四、API
>> * 添加：可顺便发布+授权
>>> * 参数：

            group_id              指定的分组编号                 必填
            api_name              设置API的名称                 必填
            description           API描述信息
            method                请求方法                      必填
                                    get-列表
                                    get_detail-详情
                                    post-添加
                                    put-修改
                                    delete-删除
            path                  请求路径                      必填
            params                提交的参数                    method不为get/get_detail时，必填
                                例子：
                                    列表：[
                                       {"name": "username", "type": "string", "description": "用户名", "is_required": 1},
                                       {"name": "email", "type": "string", "description": "邮箱", "is_required": 1}
                                       ]
                                    详情：[
                                       {"name": "id", "type": "int", "description": "ID", "is_required": 1, "is_path":1}
                                       ]
                                    添加/修改/删除：
                                        [
                                       {"name": "username", "type": "string", "description": "用户名", "is_required": 1},
                                       {"name": "email", "type": "string", "description": "邮箱", "is_required": 1}
                                       ]
                                name: api名称
                                type: 参数类型 string int long float double boolean
                                description: api描述
                                is_required：0-非必填 1-必填
                                is_path：0-请求详情接口时，非必传的参数(如 www.xxx.com/a?id=])；1-请求详情接口时，必传的参数(如 www.xxx.com/a/[id])
                                      
            authorization         是否需要加Authorization验证    
      
            publish_description   是否需要发布                  不为空-则发布               
            is_authorize          是否需要授权                  不为空-则授权
            app_id                应用ID                      is_authorize不为空-则必填

>> * 修改：可顺便发布
>>> * 参数：

            api_id                API的Id标识                  必填       
            group_id              指定的分组编号                 必填
            api_name              设置API的名称                 必填
            description           API描述信息
            method                请求方法                      必填
                                    get-列表
                                    get_detail-详情
                                    post-添加
                                    put-修改
                                    delete-删除
            path                  请求路径                      必填
            params                提交的参数                    method不为get/get_detail时，必填
                                例子：
                                    列表：[
                                       {"name": "username", "type": "string", "description": "用户名", "is_required": 1},
                                       {"name": "email", "type": "string", "description": "邮箱", "is_required": 1}
                                       ]
                                    详情：[
                                       {"name": "id", "type": "int", "description": "ID", "is_required": 1, "is_path":1}
                                       ]
                                    添加/修改/删除：
                                        [
                                       {"name": "username", "type": "string", "description": "用户名", "is_required": 1},
                                       {"name": "email", "type": "string", "description": "邮箱", "is_required": 1}
                                       ]
                                name: api名称
                                type: 参数类型 string int long float double boolean
                                description: api描述
                                is_required：0-非必填 1-必填
                                is_path：0-请求详情接口时，非必传的参数(如 www.xxx.com/a?id=])；1-请求详情接口时，必传的参数(如 www.xxx.com/a/[id])
                                      
            authorization         是否需要加Authorization验证    
      
            publish_description   是否需要发布                  不为空-则发布

>> * 查询定义中的API列表/查询已发布API列表
>>> * 参数：
      
            group_id          指定的分组编号
            stage_name        环境名称                action_type不为空，必填
            api_id            指定的API编号
            api_name          API名称
            page_size         指定分页查询时每页行数
            page_number       指定要查询的页码
            action_type       操作类型            

>> * 查询API定义(详情)
>>> * 参数：
     
            apiId             API的Id标识            必填
            group_id          API所在的分组编号
    
>> * 删除API(单个/多个)
>>> * 参数：
   
            api_id            API的Id标识            必填(多个用","隔开)
            group_id          API所在的分组编号
    
>> * 发布API(单个/多个)
>>> * 参数：
   
            api_id            API的Id标识            必填(多个用","隔开)
            group_id          API所在的分组编号
            stage_name        运行环境名称            必填
            description       本次发布备注说明         必填
    
>> * 下线API(单个/多个)
>>> * 参数：
   
            api_id            API的Id标识            必填(多个用","隔开)
            group_id          API所在的分组编号
            stage_name        运行环境名称            必填
            description       本次发布备注说明         必填


> * 五、API授权
>> * 添加：给指定app添加多个API的访问权限(单个/多个api)
>>> * 参数：

            group_id          API分组ID              api_ids为空,则必填
            stage_name        环境名称                必填
            app_id            应用编号                必填
            api_ids           指定要操作的API编号(支持输入多个，用","隔开)  group_id为空,则必填
            description       授权说明                
            auth_vaild_time   授权有效时间的截止时间
    
>> * 查询授权的Vpc列表：/api/apiGateWay/apiAuthorize
>>> * 参数：
      
            app_id         App的唯一标识(应用ID)   action_type为空,则必填
            action_type    操作接口类型  
            page_size      每页行数  
            page_number    页码
      
>> * 撤销授权：/api/apiGateWay/apiAuthorize
>>> * 参数：

            group_id          API分组ID              
            stage_name        环境名称                必填
            app_id            应用编号                必填
            api_ids           指定要操作的API编号(支持输入多个，用","隔开)  必填

### 注意
1.Laravel框架的不同版本，在安装其它扩展包时，要求的版本可能不一样，请根据实际情况进行版本的调整

2.接口的更多参数说明和限制，请查看代码

3.该扩展包仅供大家参考使用，请根据各自项目的实际情况，进行修改

4.该扩展包为基础版本，目前适用于使用了VPC通道的api网关，往后有时间再进行升级和完善

5.如果有写的不够好，或者有错误的地方，望各位大神能多给建议
