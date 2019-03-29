<?php

namespace AliYun\ApiGateWay\Requests;

use AliYun\ApiGateWay\Services\Core\Request\MyRequest;
use Illuminate\Foundation\Http\FormRequest;

class VpcRequest extends FormRequest
{
    use MyRequest;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $method = $this->method();
        $action = $this->getAction();

        $validate_arr = [];

        if ($method == 'GET') {
            $validate_arr = [
                'page_number'   =>  'integer|min:1',
                'page_size'     =>  'integer|min:1|max:100'
            ];
        }

        if ($method == 'POST' || $method == 'DELETE') {
            $validate_arr = [
                'vpc_id'        =>  'required',
                'instance_id'   =>  'required',
                'port'          =>  'required|integer'
            ];

            if ($method == 'POST') {
                $validate_arr['name'] = 'required';
            }
        }

        return $validate_arr;
    }

    /**
     * @description 属性信息
     * @return array
     */
    public function attributes(){
        return [
            'vpc_id'        =>  '专用网络Id',          //专用网络Id，必须是同账户下可用的专用网络的ID
            'instance_id'   =>  '专用网络中的实例Id',   //专用网络中的实例Id(ECS/负载均衡)
            'port'          =>  '实例对应的端口号',     //实例对应的端口号
            'name'          =>  '自定义授权名称',       //自定义授权名称，需要保持唯一，不能重复

            'page_number'   =>  '查询的页码',
            'page_size'     =>  '每页行数'
        ];
    }

}
