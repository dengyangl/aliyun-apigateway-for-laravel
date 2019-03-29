<?php

namespace AliYun\ApiGateWay\Requests;

use AliYun\ApiGateWay\Services\Core\Request\MyRequest;
use Dingo\Api\Exception\ResourceException;
use Illuminate\Foundation\Http\FormRequest;

class AppRequest extends FormRequest
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
            $app_name = request('app_name');

            if (!empty($app_name) && ((Chinese_str_length($app_name) < 4) || (Chinese_str_length($app_name) > 26)))
                throw new ResourceException('应用名称长度限制为4~26个字符，1个中文占2个字符');
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

        if ($method == 'GET' && $action == 'app') {
            $validate_arr = [
                'page_number'   =>  'integer|min:1',
                'page_size'     =>  'integer|min:1|max:100'
            ];
        }

        if ($method == 'POST') {
            $validate_arr = [
                'app_name'      =>  ['required', 'regex:'.$regex],
                'description'   =>  'max:180'
            ];
        }

        if ($method == 'PUT') {

            if ($action == 'app') {
                $validate_arr = [
                    'app_id'        =>  'required',
                    'app_name'      =>  ['regex:'.$regex],
                    'description'   =>  'max:180'
                ];
            }

            if ($action == 'secret') {
                $validate_arr = [
                    'app_key'   =>  'required'
                ];
            }
        }

        if ($method == 'DELETE') {
            $validate_arr = [
                'app_id'    =>  'required'
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
            'app_id'        =>  'APP应用ID',
            'app_key'       =>  'APP应用key',

            'app_name'      =>  'APP应用名称',
            'description'   =>  'APP应用描述信息',

            'page_number'   =>  '查询的页码',
            'page_size'     =>  '每页行数'
        ];
    }
}
