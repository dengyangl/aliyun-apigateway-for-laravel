<?php

namespace AliYun\ApiGateWay\Requests;

use AliYun\ApiGateWay\Services\Core\Request\MyRequest;
use Dingo\Api\Exception\ResourceException;
use Illuminate\Foundation\Http\FormRequest;

class ApiGroupRequest extends FormRequest
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
            $group_name = request('group_name');

            if (!empty($group_name) && ((Chinese_str_length($group_name) < 4) || (Chinese_str_length($group_name) > 50)))
                throw new ResourceException('应用名称长度限制为4~50个字符，1个中文占2个字符');
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

        if ($method == 'GET' && $action == 'apiGroup') {
            $validate_arr = [
                'page_number'   =>  'integer|min:1',
                'page_size'     =>  'integer|min:1|max:100'
            ];
        }

        if ($method == 'POST') {
            if ($action == 'apiGroup') {
                $validate_arr = [
                    'group_name'    =>  ['required', 'regex:'.$regex],
                    'description'   =>  'max:180'
                ];
            }

            if ($action == 'env') {
                $validate_arr = [
                    'group_id'          =>  'required',
                    'stage_id'          =>  'required',
                    'variable_name'     =>  'required',
                    //'variable_value'    =>  ''
                ];
            }
        }

        if ($method == 'PUT') {
            if ($action == 'apiGroup') {
                $validate_arr = [
                    'group_id'      =>  'required',
                    'group_name'    =>  ['regex:'.$regex],
                    'description'   =>  'max:180'
                ];
            }
        }

        if ($method == 'DELETE') {
            if ($action == 'apiGroup') {
                $validate_arr = [
                    'group_id' => 'required'
                ];
            }

            if ($action == 'env') {
                $validate_arr = [
                    'group_id'      =>  'required',
                    'stage_id'      =>  'required',
                    'variable_name' =>  'required'
                ];
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
            'group_id'      =>  '分组ID',
            'group_name'    =>  '分组名称',
            'description'   =>  '分组描述',

            'stage_id'      =>  '环境ID',
            'variable_name' =>  '变量名',

            'page_number'   =>  '查询的页码',
            'page_size'     =>  '每页行数'
        ];
    }
}
