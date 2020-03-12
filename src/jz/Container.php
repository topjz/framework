<?php
declare (strict_types = 1);
namespace jz;


use Closure;
use ReflectionClass;

class Container
{
//array(2) {
//    ["jz\App"]=> object(jz\App)#3 (7) {
//        ["rootPath":protected]=>    string(20) "D:\dev\wwwroot\demo\"
//        ["topjzPath":protected]=>    string(47) "D:\dev\wwwroot\demo\vendor\topjz\framework\src\"
//        ["appPath":protected]=> string(24) "D:\dev\wwwroot\demo\app\"
//        ["runtimePath":protected]=>    string(28) "D:\dev\wwwroot\demo\runtime\"
//        ["bind":protected]=>    array(4) {
//            ["app"]=>      string(6) "jz\App"
//            ["cache"]=>      string(8) "jz\Cache"
//            ["think\Request"]=>      string(7) "Request"
//            ["think\exception\Handle"]=>      string(15) "ExceptionHandle"
//        }
//        ["instances"]=>    array(2) {
//                ["jz\App"]=>      *RECURSION*
//                ["jz\Container"]=>      *RECURSION*
//        }
//        ["invokeCallback":protected]=>    array(0) {
//        }
//    }
//
//    ["jz\Container"]=>  object(jz\App)#3 (7) {
//        ["rootPath":protected]=>    string(20) "D:\dev\wwwroot\demo\"
//        ["topjzPath":protected]=>    string(47) "D:\dev\wwwroot\demo\vendor\topjz\framework\src\"
//        ["appPath":protected]=>    string(24) "D:\dev\wwwroot\demo\app\"
//        ["runtimePath":protected]=>    string(28) "D:\dev\wwwroot\demo\runtime\"
//        ["bind":protected]=>    array(4) {
//            ["app"]=>      string(6) "jz\App"
//            ["cache"]=>      string(8) "jz\Cache"
//            ["think\Request"]=>      string(7) "Request"
//            ["think\exception\Handle"]=>      string(15) "ExceptionHandle"
//        }
//        ["instances"]=>    array(2) {
//            ["jz\App"]=>      *RECURSION*
//            ["jz\Container"]=>      *RECURSION*
//        }
//        ["invokeCallback":protected]=>    array(0) {
//            }
//        }
//    }
//
//object(jz\App)#3 (7) {
//  ["rootPath":protected]=>  string(20) "D:\dev\wwwroot\demo\"
//  ["topjzPath":protected]=>  string(47) "D:\dev\wwwroot\demo\vendor\topjz\framework\src\"
//  ["appPath":protected]=>  string(24) "D:\dev\wwwroot\demo\app\"
//  ["runtimePath":protected]=>  string(28) "D:\dev\wwwroot\demo\runtime\"
//  ["bind":protected]=>  array(4) {
//    ["app"]=>    string(6) "jz\App"
//    ["cache"]=>    string(8) "jz\Cache"
//    ["think\Request"]=>    string(7) "Request"
//    ["think\exception\Handle"]=>    string(15) "ExceptionHandle"
//  }
//  ["instances":protected]=>
//      array(2) {
//        ["jz\App"]=>    *RECURSION*
//        ["jz\Container"]=>    *RECURSION*
//      }
//  ["invokeCallback":protected]=>
//    array(0) {  }
//}

    /**
     * 容器对象实例（保存容器类的实例如：App.php中的app类）
     * @var Container|Closure
     */
    protected static $instance;

    /**
     * 容器中的对象实例（保存"jz\App"=>{},"jz\Container"={}两个数组）
     * @var array
     */
    protected $instances = [];

    /**
     * 容器绑定标识
     * @var array
     */
    protected $bind = [];

    /**
     * 容器回调
     * @var array
     */
    protected $invokeCallback = [];

    /**
     * 获取当前容器的实例（单例）
     * @access public
     * @since V1.0
     * @return static
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        if (static::$instance instanceof Closure) {
            return (static::$instance)();
        }

        return static::$instance;
    }

    /**
     * 绑定一个类、闭包、实例、接口实现到容器
     * @access public
     * @param string|array $abstract 类标识、接口
     * @param mixed        $concrete 要绑定的类、闭包或者实例
     * @since V1.0
     * @return $this
     */
    public function bind($abstract, $concrete = null)
    {
        echo "300_001 绑定**到容器中开始<br>";

        if (is_array($abstract)) {
            //判断是否是数组
            echo "300_001_001传入的\$abstract为数组<br>";
            foreach ($abstract as $key => $val) {
                $this->bind($key, $val);
            }
        } else {
            echo "300_001_002传入的\$abstract为其它<br>";
            $abstract = $this->getAlias($abstract);
            $this->bind[$abstract] = $concrete;
        }
        return $this;
    }

    /**
     * 根据别名获取真实类名
     * @param string $abstract
     * @since V1.0
     * @return string
     */
    public function getAlias(string $abstract): string
    {
        echo "300_001_002_001根据别名获取真实类名开始<br>";
        if (isset($this->bind[$abstract])) {
            echo "300_001_002_001_001判断**已绑定到容器<br>";
            $bind = $this->bind[$abstract];

            if (is_string($bind)) {
                return $this->getAlias($bind);
            }
        }
        echo "300_001_002_002根据别名获取真实类名结束<br>";
        return $abstract;
    }

    /**
     * 设置当前容器的实例
     * @access public
     * @param object|Closure $instances
     * @since 1.0
     * @return void
     */
    public static function setInstance($instances): void
    {
        echo "400_001设置当前容器的实例开始<br>";
        static::$instance = $instances;
    }

    /**
     * 绑定一个类实例到容器
     * @access public
     * @param string $abstract 类名或者标识
     * @param object $instance 类的实例
     * @since 1.0
     * @return $this
     */
    public function instance(string $abstract, $instance)
    {
        echo "500_001 绑定一个应用对象（App类）实例到容器开始<br>";
        $abstract = $this->getAlias($abstract);
        $this->instances[$abstract] = $instance;
        return $this;
    }

    /**
     * 获取容器中的对象实例
     * @access public
     * @param string $abstract 类名或者标识
     * @since V1.0
     * @return object
     */
    public function get($abstract)
    {
        echo "700_001 获取容器中的对象_实例<br>";
        if ($this->has($abstract)) {
            echo "700_002 获取容器中的对象实例开始<br>";
            return $this->make($abstract);
            echo "700_001 获取容器中的对象实例结束<br>";
        }
        //throw new ClassNotFoundException('class not exists: ' . $abstract, $abstract);
    }

    /**
     * 判断容器中是否存在类及标识
     * @access public
     * @param string $abstract 类名或者标识
     * @since V1.0
     * @return bool
     */
    public function has($abstract): bool
    {
        echo "700_001_001 判断容器中是否存在类及标识<br>";
        return isset($this->bind[$abstract]) || isset($this->instances[$abstract]);
    }

    /**
     * 创建类的实例 已经存在则直接获取
     * @access public
     * @param string $abstract 类名或者标识
     * @param array $vars 变量
     * @param bool $newInstance 是否每次创建新的实例
     * @return mixed
     */
    public function make(string $abstract, array $vars = [], bool $newInstance = false)
    {
        echo "700_002_001 创建类的实例 已经存在则直接获取<br>";
        $abstract = $this->getAlias($abstract);//类名
        //检测变量是否已设置$this->instances（容器中的对象实例）
        if (isset($this->instances[$abstract]) && !$newInstance) {
            //echo "700_002_001_001<br>";
            return $this->instances[$abstract];
        }

        if (isset($this->bind[$abstract]) && $this->bind[$abstract] instanceof Closure) {
            var_dump("function");exit;
            $object = $this->invokeFunction($this->bind[$abstract], $vars);
        } else {
            echo "700_002_002 调用反射执行类的实例化<br>";
            //var_dump($abstract);exit;
            $object = $this->invokeClass($abstract, $vars);
            echo "700_002_003 调用反射执行类的实例化结束<br>";
        }

        if (!$newInstance) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }


    /**
     * 调用反射执行类的实例化 支持依赖注入
     * access public
     * @param string $class 类名
     * @param array $vars 参数
     * @return mixed
     * @throws \ReflectionException
     */
    public function invokeClass(string $class, array $vars = [])
    {

        //echo "700_002_002_001 调用反射执行类的实例化开始<br>";
        try {
            $reflect = new ReflectionClass($class);

            if ($reflect->hasMethod('__make')) {
                $method = new ReflectionMethod($class, '__make');

                if ($method->isPublic() && $method->isStatic()) {
                    echo "700_002_002_002 bindParams<br>";
                    $args = $this->bindParams($method, $vars);
                    echo "700_002_002_003 bindParams结束<br>";
                    echo "700_002_002_004 invokeArgs<br>";
                    return $method->invokeArgs(null, $args);
                }
            }

            $constructor = $reflect->getConstructor();

            //echo "700_002_002_002 bindParams<br>";
            $args = $constructor ? $this->bindParams($constructor, $vars) : [];
            //echo "700_002_002_003 bindParams结束<br>";

            $object = $reflect->newInstanceArgs($args);

            //echo "700_002_002_004 invokeAfter<br>";
            //$this->invokeAfter($class, $object);
            //echo "700_002_002_005 invokeAfter结束<br>";

            return $object;
        } catch (ReflectionException $e) {
            throw new ClassNotFoundException('class not exists: ' . $class, $class, $e);
        }
    }

    /**
     * 绑定参数
     * @access protected
     * @param \ReflectionMethod|\ReflectionFunction $reflect 反射类
     * @param array                                 $vars    参数
     * @return array
     */
    protected function bindParams($reflect, array $vars = []): array
    {
        //echo "700_002_002_002_001 bindParams开始<br>";
        if ($reflect->getNumberOfParameters() == 0) {
            return [];
        }

        // 判断数组类型 数字数组时按顺序绑定参数
        reset($vars);
        $type   = key($vars) === 0 ? 1 : 0;
        $params = $reflect->getParameters();
        $args   = [];
        foreach ($params as $param) {

            $name      = $param->getName();
            $lowerName = Str::snake($name);
            $class     = $param->getClass();
            if ($class) {
                $args[] = $this->getObjectParam($class->getName(), $vars);
            } elseif (1 == $type && !empty($vars)) {
                $args[] = array_shift($vars);
            } elseif (0 == $type && isset($vars[$name])) {
                $args[] = $vars[$name];
            } elseif (0 == $type && isset($vars[$lowerName])) {
                $args[] = $vars[$lowerName];
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                throw new InvalidArgumentException('method param miss:' . $name);
            }
        }
        return $args;
    }

    /**
     *
     * @access public
     * @param string $name 方法名
     * @since V1.0
     * @return mixed
     */
    public function __get($name)
    {
        echo "700 调用Route开始<br>";
        return $this->get($name);
    }
}