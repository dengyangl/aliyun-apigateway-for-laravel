<?php

namespace AliYun\ApiGateWay\Requests;

use AliYun\ApiGateWay\Services\Core\Request\MyRequest;
use Dingo\Api\Exception\ResourceException;
use Illuminate\Foundation\Http\FormRequest;

class ApiRequest extends FormRequest
{
    use MyRequest;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $method = $this->method();
        $action = $this->getAction();

        if ($method == 'POST' || $method == 'PUT') {
            $api_name = request('api_name');
            if (!empty($api_name) && ((Chinese_str_length($api_name) < 4) || (Chinese_str_length($api_name) > 50)))
                throw new ResourceException('API名称长度限制为4~50个字符，1个中文占2个字符');

            $params = request('params');
            if (!empty($params)) {
                $params_array = is_json_format(request('params'), '参数');
                if (!empty($params_array)) {
                    foreach ($params_array as $k => $v) {
                        if (empty(key($v))) throw new ResourceException('参数的键不能为空');
                        if (empty(current($v)) && (current($v) != 0) && (current($v) != '0'))
                            throw new ResourceException('参数的值不能为空');
                    }
                }
            }
        }

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

        //字母或汉字开始,汉字、英文字母、数字、英文格式的下划线
        $regex = '/^[\p{Han}a-zA-Z][\p{Han}a-zA-Z0-9_]*$/u';

        $action_type = request('action_type');

        if ($method == 'GET' && $action == 'api') {
            $validate_arr = [
                'page_number'   =>  'integer|min:1',
                'page_size'     =>  'integer|min:1|max:100'
            ];

            //查询已发布API列表
            if (!empty($action_type)) {
                $validate_arr = array_merge([
                    'stage_name' => 'required'
                ],  $validate_arr);
            }
        }

        if ($method == 'POST') {
            if ($action == 'api') {
                $validate_arr = [
                    'group_id'      =>  'required',
                    'api_name'      =>  'required',
                    'description'   =>  'max:180',
                    'method'        =>  'required',
                    'path'          =>  'required',
                    //'params'        =>  'required'
                ];

                $request_method = request('method');
                if ($request_method != 'get' && $request_method != 'get_detail')
                    $validate_arr['params'] = 'required';

                //授权api
                $is_authorize = request('is_authorize');
                if (!empty($is_authorize)) $validate_arr['app_id'] = 'required';
            }
        }

        if ($method == 'PUT') {
            if ($action == 'api') {
                $validate_arr = [
                    'api_id'        =>  'required',
                    'api_name'      =>  ['required', 'regex:'.$regex],
                    'description'   =>  'max:180',
                    'method'        =>  'required',
                    'path'          =>  'required',
                    //'params'        =>  'required'
                ];

                $request_method = request('method');
                if ($request_method != 'get' && $request_method != 'get_detail')
                    $validate_arr['params'] = 'required';
            }

            if ($action == 'publish') {
                $validate_arr = [
                    'api_id'        =>  'required',
                    'stage_name'    =>  'required',
                    'description'   =>  'required'
                ];
            }

            if ($action == 'offline') {
                $validate_arr = [
                    'api_id'        =>  'required',
                    'stage_name'    =>  'required'
                ];
            }
        }

        if ($method == 'DELETE') {
            $validate_arr = [
                'api_id'    =>  'required'
            ];
        }

        return $validate_arr;
    }

    /**
     * @description 属性信息
     * @return array
     */
    public function attributes(){
        return [
            'stage_name'    =>  '环境名称',

            'group_id'      =>  '分组编号',
            'api_name'      =>  'API名称',
            'description'   =>  'API描述信息',

            'method'        =>  '请求类型',
            'path'          =>  '请求路径',
            'params'        =>  '参数',
            'authorization' =>  '是否需要验证',

            'api_id'        =>  'API编号',    //API的Id标识

            'page_number'   =>  '查询的页码',
            'page_size'     =>  '每页行数',

            'app_id'        =>  '应用ID'
        ];
    }
}
