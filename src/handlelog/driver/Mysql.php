<?php
namespace app\admin\library\handlelog\driver;

use think\Db;
use think\Exception;
use think\exception\PDOException;
use think\Validate;

class Mysql
{
    /**
     * 数据表字段
     * @var [type]
     */
    protected $fields = [
        'user_id', 'route', 'msg', 'params', 'create_time', 'modify_time',
    ];
    /**
     * 配置项
     * @var [type]
     */
    protected $config = [
        'table_name' => 'handle_log',
    ];

    public function __construct($config = [])
    {
        $this->config = array_merge($this->config, $config);
        // 数据库不存在就新建数据库
        try {
            Db::connect()->getTables($this->config['table_name']);
        } catch (\PDOException $e) {
            $sql = <<<EOT
CREATE TABLE IF NOT EXISTS `{$this->config['table_name']}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '操作用户',
  `route` varchar(50) NOT NULL COMMENT '路由地址',
  `msg` varchar(255) NOT NULL COMMENT '操作说明',
  `params` text COMMENT '请求参数',
  `create_time` datetime DEFAULT NULL,
  `modify_time` datetime DEFAULT NULL COMMENT '日志表',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='操作日志表';
EOT;
            Db::connect()->execute($sql);
        }
    }
    /**
     *
     * @method   save
     * @DateTime 2017-04-08T12:09:19+0800
     * @param    [type]                   $data [description]
     * @return   [type]                         [description]
     */
    public function save($data)
    {
        $this->validate($data);
        $sql = "INSERT INTO `handle_log` (`user_id`, `route`, `msg`, `params`, `create_time`, `modify_time`) VALUES (:user_id, :route, :msg, :params, :create_time, :modify_time);";
        return Db::connect()->query($sql, $data);
    }

    /**
     * 验证字段
     * @method   validate
     * @DateTime 2017-04-08T12:11:30+0800
     * @param    [type]                   $data [description]
     * @return   [type]                         [description]
     */
    protected function validate(&$data)
    {
        $validate = new Validate([
            'user_id|操作用户' => ['require', 'integer'],
            'route|路由地址' => ['require'],
            'msg|操作说明' => ['require'],
        ]);

        if (!$validate->check($data)) {
            throw new Exception($validate->getError());
        }
        $data['modify_time'] = $data['create_time'] = date('Y-m-d H:i:s');
        $data['params'] = isset($data['params']) ? json_encode($data['params']) : null;

        foreach ($data as $key => $value) {
            if (!in_array($key, $this->fields)) {
                unset($data[$key]);
            }
        }
    }
}
