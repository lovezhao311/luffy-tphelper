<?php
namespace luffyzhao\helper;

use think\Config;
use think\db\Query;
use think\Exception;
use think\Loader;
use think\Model;
use think\View;

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
    /**
     * 请求方式
     * @var string
     */
    protected $method = 'GET';
    /**
     * 搜索规则
     * @var null
     */
    protected $rules = null;
    /**
     * 数据库Query对象
     * @var null
     */
    protected $query = null;
    /**
     * 请求参数
     * @var null
     */
    protected $params = null;
    /**
     * 配置
     * @var [type]
     */
    protected $config = [
        'method' => 'search/a',
    ];
    /**
     * 构造函数
     * @method   __construct
     * @DateTime 2017-06-24T12:11:06+0800
     * @param    [type]                   $query [description]
     */
    public function __construct()
    {
        // 合并配置
        if (Config::has('search')) {
            $this->config = array_merge($this->config, Config::get('search'));
        }

    }

    /**
     * 设置query
     * @param $query
     * @throws Exception
     */
    public function setQuery($query)
    {
        if (!($query instanceof Query)) {
            throw new Exception('参数错误！');
        }
        //
        $this->query = $query;
    }

    /**
     * 执行搜索
     * @method   check
     * @DateTime 2017-04-27T12:11:21+0800
     * @param    Query|Model                   $query [description]
     * @return   [type]                          [description]
     */
    public function run()
    {
        if (empty($this->rules)) {
            return $this->query;
        }

        foreach ($this->rules as $key => $value) {
            try {
                $rule = $this->getRule($value, $key);
            } catch (Exception $e) {
                continue;
            }

            $this->query = call_user_func_array([$this->query, 'where'], [$rule['field'], $rule['exp'], $rule['value']]);
        }

        View::instance(Config::get('template'), Config::get('view_replace_str'))->assign('search', $this->params());
        return $this->query;
    }

    /**
     * [getRule description]
     * @method   getRule
     * @DateTime 2017-06-24T12:20:20+0800
     * @param    [type]                   $value [description]
     * @return   [type]                          [description]
     */
    protected function getRule($rule, $searchKey)
    {
        $rule = $this->mergeDefault($rule, $searchKey);

        foreach ($rule as $key => $value) {
            $method = 'get' . Loader::parseName($key . '_' . $searchKey, 1) . 'Attr';
            if (method_exists($this, $method)) {
                $rule[$key] = $this->$method();
            }
        }

        $rule['value'] = $this->value($searchKey, $rule);

        return $rule;
    }

    /**
     * 搜索条件值
     * @method   value
     * @DateTime 2017-06-24T14:42:42+0800
     * @param    string                   $value [description]
     * @return   [type]                          [description]
     */
    public function value($searchKey, $rule)
    {
        $params = $this->params();
        $method = 'get' . Loader::parseName('value_' . $searchKey, 1) . 'Attr';

        if (method_exists($this, $method)) {
            $value = $this->$method();
        } else if (isset($params[$searchKey])) {
            $value = $params[$searchKey];
        } else if ($rule['default'] != null) {
            $value = $rule['default'];
        } else {
            throw new Exception('搜索条件不存在.');
        }

        return ($rule['exp'] == 'like') ? $value . '%' : $value;
    }
    /**
     * 合并默认参数
     * @method   mergeDefault
     * @DateTime 2017-06-24T14:41:05+0800
     * @param    [type]                   $rule [description]
     * @return   [type]                         [description]
     */
    protected function mergeDefault($rule, $searchKey)
    {
        $default = [
            'default' => null,
            'field' => $searchKey,
            'exp' => '=',
        ];

        return array_merge($default, $rule);
    }
    /**
     * 获取搜索参数
     * @method   params
     * @DateTime 2017-03-02T17:29:48+0800
     * @return   [type]                   [description]
     */
    protected function params()
    {
        if ($this->params === null) {
            $params = request()->param($this->config['method']);
            $this->removeEmpty($params);
            $this->params = $params;
        }
        return $this->params;
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
