<?php
namespace luffyzhao\helper;

use think\Exception;
use think\exception\ClassNotFoundException;
use think\exception\ValidateException;
use think\Hook;
use think\Loader;

class Controller extends \think\Controller
{

    protected function _initialize()
    {
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
        parent::success($msg, $url, $data, $wait, $header);
    }

    /**
     * 验证数据
     * @access protected
     * @param array        $data     数据
     * @param string|array $validate 验证器名或者验证规则数组
     * @param array        $message  提示信息
     * @param bool         $batch    是否批量验证
     * @param mixed        $callback 回调方法（闭包）
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate($data, $validate, $message = [], $batch = false, $callback = null)
    {
        $this->validateFailException(true);
        $this->batchValidate = true;
        try {
            parent::validate($data, $validate, $message, $batch, $callback);
        } catch (ValidateException $e) {
            throw new Exception($e->getMessage());
        }
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
