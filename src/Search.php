<?php
namespace luffyzhao\helper;

use think\Config;

/**
 * 用法
 * $rules[index] 对应控制器 Car::index
 * $rules[index] 的key对应哪些参数参与查询
 * $rules[index][key][0] 的值 查询方法
 * $rules[index][key][1] 的值 查询表达式
 * $rules[index][key][2] 的值 查询字段
 * @var [type]
 */
class Search
{

    protected $rules = null;
    protected $request = null;
    protected $config = [
        'method_suffix' => 'search/a',
    ];

    public function __construct()
    {
        $this->request = request();
    }
    /**
     * [getRules description]
     * @method   getRules
     * @DateTime 2017-03-02T17:01:59+0800
     * @return   [type]                   [description]
     */
    public function check(Query $query)
    {
        if (empty($this->params())) {
            return $query;
        }
        if (empty($this->rules)) {
            return $query;
        }
        $action = $this->request->action();
        if (!isset($this->rules[$action])) {
            return $query;
        }

        $rules = $this->rules[$action];

        foreach ($rules as $key => $value) {
            $this->checkItem($query, $key, $value);
        }

        View::instance(Config::get('template'), Config::get('view_replace_str'))->assign('search', $this->params());
        return $query;
    }
    /**
     * 获取查询条件
     * @method   getItem
     * @DateTime 2017-04-25T10:58:01+0800
     * @param    [type]                   $key  [description]
     * @param    [type]                   $item [description]
     * @return   [type]                         [description]
     */
    protected function checkItem(&$query, $key, $item)
    {
        $data = $this->params();
        if (!isset($data[$key])) {
            return false;
        }

        $value = $this->item('value', $key, $item, $data);
        $field = $this->item('field', $key, $item, $data);
        $function = $this->item('function', $key, $item, $data);
        $op = $this->item('op', $key, $item, $data);

        switch ($function) {
            case 'whereLike':
                $value = "{$value}%";
                call_user_func_array([$query, 'whereLike'], [$field, $value]);
                break;
            case 'order':
                call_user_func_array([$query, 'order'], [$value]);
                break;
            default:
                call_user_func_array([$query, $function], [$field, $op, $value]);
                break;
        }

    }
    /**
     * [value description]
     * @method   value
     * @DateTime 2017-04-25T10:59:44+0800
     * @param    [type]                   $value [description]
     * @param    [type]                   $item  [description]
     * @param    [type]                   $data  [description]
     * @return   [type]                          [description]
     */
    protected function item($type, $key, $item, array $data)
    {
        $action = request()->action();
        $method = 'get' . Loader::parseName($action . '_' . $key . '_' . $type, 1) . 'Attr';
        $method1 = 'get' . Loader::parseName($key . '_' . $type, 1) . 'Attr';
        if (method_exists($this, $method)) {
            $default = call_user_func_array([$this, $method], [$data[$key], $item, $data]);
        } else if (method_exists($this, $method1)) {
            $default = call_user_func_array([$this, $method1], [$data[$key], $item, $data]);
        } else {
            switch ($type) {
                case 'value':
                    $default = $data[$key];
                    break;
                case 'function':
                    $default = isset($item[0]) ? $item[0] : 'where';
                    break;
                case 'op':
                    $default = isset($item[2]) ? $item[2] : '=';
                    break;
                case 'field':
                    $default = isset($item[1]) ? $item[1] : $key;
            }
        }
        return $default;
    }

    /**
     * 获取搜索参数
     * @method   params
     * @DateTime 2017-03-02T17:29:48+0800
     * @return   [type]                   [description]
     */
    protected function params()
    {
        $params = $this->request->param($this->config['method_suffix']);
        $this->removeEmpty($params);
        view()->assign('search', $params);
        return $params;
    }
    /**
     * 删除空数组
     * @method   removeEmpty
     * @DateTime 2017-04-24T17:55:11+0800
     * @param    [type]                   &$arr [description]
     * @param    bool                     $trim [description]
     * @return   [type]                         [description]
     */
    protected function removeEmpty(&$arr, $trim = true)
    {
        if (empty($arr)) {
            return [];
        }
        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                self::removeEmpty($arr[$key]);
            } else {
                $value = ($trim && is_string($value)) ? trim($value) : $value;
                if ($value === '' || $value === null) {
                    unset($arr[$key]);
                } elseif ($trim) {
                    $arr[$key] = $value;
                }
            }
        }
    }
}
