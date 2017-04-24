<?php
namespace luffyzhao\helper;

use think\Config;
use think\Exception;
use think\Loader;

class HandleLog
{
    /**
     * @var object 对象实例
     */
    protected static $instance;
    /**
     * 操作日志驱动
     * @var null
     */
    protected $driver = null;
    /**
     * 初始化
     * @access public
     * @param array $options 参数
     * @return \think\Request
     */
    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }
        return self::$instance;
    }

    /**
     * 保存
     * @method   save
     * @DateTime 2017-04-08T11:35:16+0800
     * @param    [type]                   $data [description]
     * @return   [type]                         [description]
     */
    public function save($data)
    {
        if (method_exists($this->driver, 'save')) {
            $this->driver->save($data);
        } else {
            throw new Exception("操作日志驱动的 save method 不存在！");
        }
    }

    /**
     * 构造函数
     * @access private
     * @param array $options 参数
     */
    private function __construct($options = [])
    {
        $options = array_merge(Config::get('handle_log', []), $options);
        $class = "\\luffyzhao\\helper\\handlelog\\driver\\" . ucfirst($options['type']);
        if (!class_exists($class)) {
            $module = request()->module();
            $class = Loader::parseClass($module, 'handlelog', 'driver\\' . ucfirst($options['type']));
            if (!class_exists($class)) {
                throw new Exception("操作日志驱动[{$class}]不存在！");
            }
        }

        $this->driver = new $class($options);
    }
    private function __clone()
    {}
}
