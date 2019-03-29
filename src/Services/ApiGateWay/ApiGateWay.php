<?php

namespace AliYun\ApiGateWay\Services\ApiGateWay;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use Dingo\Api\Exception\ResourceException;

trait ApiGateWay
{
    /**
     * ApiGateWay constructor.
     * @description 初始化API网关(全局)客户端
     * @author dyl
     */
   public function __construct(){
       AlibabaCloud::accessKeyClient(config('apiGateWay.accessKeyId'), config('apiGateWay.accessKeySecret'))
           ->regionId(config('apiGateWay.regionId'))
           //->asGlobalClient();
           ->asDefaultClient();
   }

    /**
     * @description 获取Rpc接口风格的请求对象
     * @author dyl
     * @return \AlibabaCloud\CloudAPI\V20160714\DescribeRegions
     */
   public function getAlibabaCloudRpcRequest(){
       //return AlibabaCloud::rpcRequest()->product(config('apiGateWay.product'))->version(config('apiGateWay.version'));

//       $options = [
//           'debug'           => true,
//           'connect_timeout' => 0.01,
//           'timeout'         => 0.01,
//           'query'           => [
//               'ResourceType' => 'type',
//               'InstanceChargeType' => 'type',
//           ],
//       ];

       return AlibabaCloud::cloudAPI()->v20160714()->describeRegions();     //->describeRegions($options);
   }

    /**
     * @description 执行请求,处理错误
     * @author dyl
     * @param $result
     * @return mixed
     */
   public function handleException($result){
       try {
           $result = $result->request(); // 执行请求

           //print_r($result['Regions']);
           //$result->RequestId; / $result['RequestId'];
           //return $result->toJson();
           //echo $result->toArray();

           return json_decode($result->toJson(), true);

       } catch (ClientException $exception) {
           echo $exception->getMessage() . PHP_EOL;

           //throw new ResourceException($exception->getMessage() . PHP_EOL);
       } catch (ServerException $exception) {
            echo $exception->getMessage() . PHP_EOL;
            echo $exception->getErrorCode() . PHP_EOL;
            echo $exception->getRequestId() . PHP_EOL;
            echo $exception->getErrorMessage() . PHP_EOL;

//           $error_msg = $exception->getMessage() . PHP_EOL;
//           $error_msg .= $exception->getErrorCode() . PHP_EOL;
//           $error_msg .= $exception->getRequestId() . PHP_EOL;
//           $error_msg .= $exception->getErrorMessage() . PHP_EOL;
//
//           throw new ResourceException($error_msg);
       }
   }

}
