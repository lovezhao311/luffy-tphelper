<?php
namespace luffyzhao\helper;

use think\Config;
use think\Model;

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
        $config = (array) Config::get('search');
        $this->config = array_merge($this->config, $config);
    }
    /**
     * [getRules description]
     * @method   getRules
     * @DateTime 2017-03-02T17:01:59+0800
     * @return   [type]                   [description]
     */
    public function check(Model $query)
    {

        $params = $this->params();
        if (empty($params)) {
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
            if (isset($params[$key])) {
                // 查询方法
                if (!isset($params[$key][0])) {
                    continue;
                }
                $this->checkItem($query, $value, $params[$key], $key);
            }
        }
        return $query;
    }

    /**
     * [checkItem description]
     * @method   checkItem
     * @DateTime 2017-03-02T17:45:38+0800
     * @param    [type]                   $item  [description]
     * @param    [type]                   $value 值
     * @param    [type]                   $field 字段
     * @param    [type]                   $query [description]
     * @return   [type]                          [description]
     */
    protected function checkItem(&$query, $item, $value, $field)
    {
        switch ($this->getFun($item)) {
            case 'whereLike':
                $query->whereLike($this->getField($item, $field), $value . '%');
                break;
            case 'whereTime':
                $query->whereTime($this->getField($item, $field), $this->getOp($item), $value);
                break;
            case 'where':
                $query->where($this->getField($item, $field), $this->getOp($item), $value);
            case 'scope':
                $query->scope($value);
            default:
                # code...
                break;
        }
    }

    /**
     * 获取字段
     * @method   getField
     * @DateTime 2017-03-02T18:03:11+0800
     * @param    [type]                   $item    [description]
     * @param    [type]                   $default [description]
     * @return   [type]                            [description]
     */
    protected function getField($item, $default)
    {
        return isset($item[1]) ? $item[1] : $default;
    }

    /**
     * 获取查询方法
     * @method   getFun
     * @DateTime 2017-03-02T18:01:06+0800
     * @param    [type]                   $item [description]
     * @return   [type]                         [description]
     */
    protected function getFun($item, $default = 'where')
    {
        return isset($item[0]) ? $item[0] : $default;
    }
    /**
     * 获取表达式
     * @method   analysisItem
     * @DateTime 2017-03-02T17:59:27+0800
     * @return   [type]                   [description]
     */
    protected function getOp($item, $default = '=')
    {
        return isset($item[2]) ? $item[2] : $default;
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
        ArrayHelper::removeEmpty($params);
        view()->assign('search', $params);
        return $params;
    }
}
