<?php
namespace luffyzhao\helper;

use think\Loader;

class Service
{
    protected $model = null;

    protected $allow = [
        'add' => [],
        'edit' => [],
    ];

    public function __construct()
    {
        $model = $this->class();
        $this->model = Loader::model($model);
    }

    /**
     * 获取当前操作文档
     * @method   table
     * @DateTime 2017-03-10T18:02:49+0800
     * @return   [type]                   [description]
     */
    function class ()
    {
        $name = StrReplace('\\', '/', get_class($this));
        return basename($name);
    }
    /**
     * 允许写入字段
     * @method   filedset
     * @DateTime 2017-06-26T17:40:10+0800
     * @param    [type]                   $type [description]
     * @return   [type]                         [description]
     */
    protected function filedset(string $type)
    {

        $allow = isset($this->allow[$type]) ? $this->allow[$type] : false;
        if ($allow !== false) {
            $this->model->allowField($allow);
        }
        return $this->model;
    }
    /**
     * 编辑
     * @method   edit
     * @DateTime 2017-06-26T17:41:13+0800
     * @param    string                   $value [description]
     * @return   [type]                          [description]
     */
    public function handle(array $data, array $where = [], string $type = 'add')
    {
        $this->filedset($type)->save($data, $where);
    }
}
