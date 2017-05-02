<?php
namespace luffyzhao\helper;

use think\Db;

class Validate extends \think\Validate
{
    /**
     * 请求字段
     * @var array
     */
    protected $requireField = [];
    /**
     * 验证请求的合法性
     * @method   checkField
     * @DateTime 2017-04-22T15:02:35+0800
     * @param    [type]                   $data [description]
     * @return   [type]                         [description]
     */
    public function checkField(array $data = [], array $rules = [], string $scene = '')
    {
        if (empty($rules)) {
            $rules = $this->requireField;
        }
        if (empty($data)) {
            $data = request()->post('data/a');
        }
        if ($scene === '') {
            $scene = strtolower(request()->action());
        }
        if (!isset($rules[$scene])) {
            return true;
        }
        $request = array_keys($data);
        $regular = $rules[$scene];
        foreach ($request as $key => $value) {
            if (!in_array($value, $regular)) {
                $this->error = "请求非法:{$value}";
                return false;
            }
        }
        return true;
    }
    /**
     * 验证是否存在！
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-04-19T09:48:57+0800
     * @param    [type]                   $roleId [description]
     * @return   [type]                           [description]
     */
    protected function exist($value, $rule, $data, $field, $title)
    {
        if (is_string($rule)) {
            $rule = explode(',', $rule);
        }
        $db = Db::name($rule[0]);
        $field = isset($rule[1]) ? $rule[1] : 'id';
        $map = [$field => $value];

        if ($db->where($map)->field($field)->find()) {
            return true;
        }
        return $title . '不存在';
    }

    /**
     * 验证是否存在！
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-04-19T09:48:57+0800
     * @param    [type]                   $roleId [description]
     * @return   [type]                           [description]
     */
    protected function existPid($value, $rule, $data, $field, $title)
    {
        if ($value == 0) {
            return true;
        }
        if (is_string($rule)) {
            $rule = explode(',', $rule);
        }
        $db = Db::name($rule[0]);
        $field = isset($rule[1]) ? $rule[1] : 'id';
        $map = [$field => $value];

        if ($db->where($map)->field($field)->find()) {
            return true;
        }
        return $title . '不存在';
    }

    /**
     * 验证某个字段不等于某个值的时候必须
     * @access protected
     * @param mixed     $value  字段值
     * @param mixed     $rule  验证规则
     * @param array     $data  数据
     * @return bool
     */
    protected function requireIfNot($value, $rule, $data)
    {
        list($field, $val) = explode(',', $rule);
        if ($this->getDataValue($data, $field) != $val) {
            return !empty($value);
        } else {
            return true;
        }
    }

    /**
     * 后缀
     * @param  [type] $value [description]
     * @param  [type] $rule  [description]
     * @return [type]        [description]
     */
    protected function postfix($value, $rule, $data, $field, $title)
    {
        $rules = explode(',', $rule);
        $postfix = strtolower(pathinfo($value, PATHINFO_EXTENSION));

        if (in_array($postfix, $rules)) {
            return true;
        }
        return $title . '文件缀不正确';
    }

    /**
     * 密码验证
     * @method   password
     * @DateTime 2017-03-31T12:07:23+0800
     * @return   [type]                   [description]
     */
    protected function password($value, $rule, $data, $field, $title)
    {
        if (!preg_match('/^(.+){6,12}$/', $value)) {
            return $title . '只能在 6 - 12 之间';
        }
        return true;
    }

    /**
     * 手机号码
     * @method   money
     * @DateTime 2016-11-04T16:22:33+0800
     * @param    string                   $value [description]
     * @return   [type]                          [description]
     */
    protected function phone($value, $rule, $data, $field, $title)
    {
        if (preg_match('/^1(3|4|5|7|8)\d{9}$/', $value)) {
            return true;
        } else {
            return $title . '不正确';
        }
    }

    /**
     * 电话号码
     * @method   money
     * @DateTime 2016-11-04T16:22:33+0800
     * @param    string                   $value [description]
     * @return   [type]                          [description]
     */
    protected function tel($value, $rule, $data, $field, $title)
    {
        if (preg_match('/^(\(\d{3,4}\)|\d{3,4}-|\s)?\d{7,14}$/', $value)) {
            return true;
        } else {
            return $title . '不正确';
        }
    }

    /**
     * 邮编
     * @method   money
     * @DateTime 2016-11-04T16:22:33+0800
     * @param    string                   $value [description]
     * @return   [type]                          [description]
     */
    protected function postiveInt($value, $rule, $data, $field, $title)
    {
        if (preg_match('/^[1-9]*[1-9][0-9]*$/', $value)) {
            return true;
        } else {
            return $title . '不正确';
        }
    }

    /**
     * 汉字、字母、数字和 , 号
     * @method   formerSpec
     * @DateTime 2017-04-22T11:03:14+0800
     * @return   [type]                   [description]
     */
    protected function chsAlphaNumComma($value, $rule, $data, $field, $title)
    {
        if ($this->regex($value, '/^[\x{4e00}-\x{9fa5}a-zA-Z0-9,]+$/u')) {
            return true;
        }
        return $title . '只能是汉字、字母、数字和 , 号';
    }
}
