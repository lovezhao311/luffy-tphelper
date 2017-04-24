<?php
namespace luffyzhao\helper;

use think\Exception;
use think\exception\ClassNotFoundException;
use think\Hook;
use think\Loader;

class Controller extends \think\Controller
{

    protected function _initialize()
    {
    }
    /**
     * 重写控制器验证
     * @method   validate
     * @DateTime 2017-03-31T12:22:20+0800
     * @param    array                    $data  [description]
     * @param    string                   $scene [description]
     * @return   [type]                          [description]
     */
    protected function validate($data = [], $scene = 'add', $message = [], $batch = false, $callback = null)
    {
        $this->validateFailException();
        $v = Loader::validate($this->request->controller());
        $v->scene($scene);
        if (!$v->check($data) || !$v->checkField()) {
            if ($this->failException) {
                throw new Exception($v->getError());
            } else {
                return $v->getError();
            }
        } else {
            return true;
        }
    }
    /**
     * 获取搜索参数
     * 去除empty
     * @method   search
     * @DateTime 2017-03-02T15:28:09+0800
     * @return   [type]                   [description]
     */
    protected function search($query)
    {
        // 通过控制器去找对应search过滤规则
        $search = $this->request->controller();
        try {
            $class = Loader::model($search, 'search');
        } catch (ClassNotFoundException $e) {
            return $query;
        }
        return $class->check($query);
    }
    /**
     * 保存数据
     * 有 where 条件是更新
     * 没 where 条件是修改
     * @method   save
     * @DateTime 2017-03-04T09:26:12+0800
     * @param    [type]                   $query   更新模型
     * @param    array                    $where    更新条件
     * @return   [type]                            [description]
     */
    protected function save(&$query, $where = [], $scene = 'add', $method = 'post', $name = 'data/a', $allowField = true)
    {
        if (!($query instanceof \think\Model)) {
            throw new Exception("操作失败，请刷新页面重试！");
        }

        if ($method == 'post') {
            $data = $this->request->post($name, []);
        } else {
            $data = $this->request->get($name, []);
        }
        $data = array_merge($data, $where);

        $pk = $query->getPk();
        if (isset($query[$pk]) && $query[$pk] !== null) {
            $data[$pk] = $query[$pk];
        }
        // 验证数据
        $this->validate($data, $scene);
        // 设置回调
        $this->trigger($query, $data, $scene);

        if ($query->allowField($allowField)->save($data, $where) === false) {
            $error = $query->getError() ?: '操作失败，请刷新页面重试！';
            throw new Exception($error);
        }

        return $query;
    }
    /**
     * 删除数据
     * @method   delete
     * @DateTime 2017-04-05T17:44:59+0800
     * @return   [type]                   [description]
     */
    protected function delete(&$query, $where = [])
    {
        if (!($query instanceof \think\Model)) {
            throw new Exception("操作失败，请刷新页面重试！");
        }
        $this->trigger($query, $where, 'delete');
        if ($query->delete() === false) {
            $error = $query->getError() ?: '操作失败，请刷新页面重试！';
            throw new Exception($error);
        }
        return $query;
    }

    /**
     * 操作监听
     * @method   trigger
     * @DateTime 2017-04-05T17:47:35+0800
     * @param    [type]                   $query [description]
     * @param    string                   $scene [description]
     * @return   [type]                          [description]
     */
    protected function trigger(&$query, $data = [], $scene = 'add')
    {
        if (method_exists($query, 'trigger' . ucfirst($scene))) {
            $result = call_user_func_array([$query, 'trigger' . ucfirst($scene)], [$data]);
            if ($result === false) {
                throw new Exception("操作失败，请刷新页面重试！");
            }
        }
    }

    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param mixed     $msg 提示信息
     * @param string    $url 跳转的URL地址
     * @param mixed     $data 返回的数据
     * @param integer   $wait 跳转等待时间
     * @param array     $header 发送的Header信息
     * @return void
     */
    protected function success($msg = '', $url = null, $data = '', $wait = 3, array $header = [])
    {
        Hook::listen('handle_success', $msg);
        parent::success($msg . '成功', $url, $data, $wait, $header);
    }
    /**
     * [_empty description]
     * @method   _empty
     * @DateTime 2017-04-17T17:51:35+0800
     * @return   [type]                   [description]
     */
    public function _empty()
    {
        return '找不到控制器的方法！';
    }
}
