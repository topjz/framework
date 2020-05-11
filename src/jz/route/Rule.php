<?php
// +----------------------------------------------------------------------
// | Topjz
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://topjz.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: chen3jian <chen3jian@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace jz\route;

/**
 * 路由规则基础类
 */
abstract class Rule
{
    /**
     * 设置路由参数
     * @access public
     * @param  string $method 方法名
     * @param  array  $args   调用参数
     * @return $this
     */
    public function __call($method, $args)
    {
        if (count($args) > 1) {
            $args[0] = $args;
        }
        array_unshift($args, $method);

        return call_user_func_array([$this, 'setOption'], $args);
    }
}
