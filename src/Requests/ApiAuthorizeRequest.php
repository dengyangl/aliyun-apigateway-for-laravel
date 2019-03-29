<?php

namespace AliYun\ApiGateWay\Requests;

use AliYun\ApiGateWay\Services\Core\Request\MyRequest;
use Dingo\Api\Exception\ResourceException;
use Illuminate\Foundation\Http\FormRequest;

class ApiAuthorizeRequest extends FormRequest
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

        if ($method == 'POST' || $method == 'DELETE') {
            $api_ids = request('api_ids');

            if (!empty($api_ids)) {
                $api_ids_array = explode(',', $api_ids);
                if (count($api_ids_array) > 100) throw new ResourceException('操作的API,最多支持100个');
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
        //$regex = '/^[\p{Han}a-zA-Z][\p{Han}a-zA-Z0-9_]*$/u';

        $action_type = request('action_type');

        if ($method == 'GET') {
            $validate_arr = [
                'page_number'   =>  'integer|min:1',
                'page_size'     =>  'integer|min:1|max:100'
            ];

            //查询指定app已授权的API列表
            if (empty($action_type)) {
                $validate_arr = array_merge([
                    'app_id' => 'required'
                ]);
            }
        }

        if ($method == 'POST') {
            $validate_arr = [
                'stage_name'    =>  'required',
                'app_id'        =>  'required'
            ];

            $group_id = request('group_id');
            $api_ids = request('api_ids');

            if (empty($group_id)) $validate_arr['api_ids'] = 'required';

            if (empty($api_ids)) $validate_arr['group_id'] = 'required';
        }

        if ($method == 'DELETE') {
            $validate_arr = [
                'stage_name'    =>  'required',
                'api_ids'       =>  'required',
                'app_id'        =>  'required'
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
            'app_id'        =>  'App应用ID',
            'stage_name'    =>  '环境名称',
            'api_ids'       =>  'API编号',

            'page_number'   =>  '查询的页码',
            'page_size'     =>  '每页行数'
        ];
    }
}
