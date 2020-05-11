<?php
declare (strict_types = 1);
namespace jz;


use Closure;
use ReflectionClass;
use jz\exception\ClassNotFoundException;
use jz\exception\InvalidArgumentException;

class Container
{
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
        echo "4.1、绑定**到容器中开始<br>";

        if (is_array($abstract)) {
            //判断是否是数组
            echo "4.2、传入的".$abstract."为数组<br>";
            foreach ($abstract as $key => $val) {
                $this->bind($key, $val);
            }
        } else {
            echo "4.3、传入的".$abstract."为其它<br>";
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
        echo "4.3.1、根据别名获取真实类名开始(getAlias)<br>";
        if (isset($this->bind[$abstract])) {
            echo "4.3.2、判断**已绑定到容器<br>";
            $bind = $this->bind[$abstract];
            echo "4.3.3、判断".$bind."是否为字符串开始<br>";
            if (is_string($bind)) {
                echo "4.3.4、".$bind."为字符串<br>";
                return $this->getAlias($bind);
            }
            echo "4.3.5、判断".$bind."是否为字符串结束<br>";
        }
        echo "4.3.6、根据别名获取真实类名结束<br>";
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
        echo "6.1、设置当前容器的实例开始<br>";
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
        echo "8.1、绑定一个应用对象（App类）实例到容器开始<br>";
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
        echo "12.1、获取容器中的对象实例(get)<br>";
        if ($this->has($abstract)) {
            echo "12.2 获取容器中的对象实例开始<br>";
            return $this->make($abstract);
        }
        throw new ClassNotFoundException('class not exists:ssss ' . $abstract, $abstract);
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
        echo "12.1.1、判断容器中是否存在类及标识(has)<br>";
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
        echo "12.2.1、创建类的实例 已经存在则直接获取(make)<br>";
        //根据别名获取真实类名
        $abstract = $this->getAlias($abstract);
        //检测变量是否已设置$this->instances（容器中的对象实例）
        if (isset($this->instances[$abstract]) && !$newInstance) {
            echo "12.2.2、返回容器中已存在对象$this->instances[$abstract]实例<br>";
            return $this->instances[$abstract];
        }
        //判断容器中存存对象实例并且绑定标识数组中该实例为闭包
        if (isset($this->bind[$abstract]) && $this->bind[$abstract] instanceof Closure) {
            echo "12.2.3、调用反射执行方法<br>";
            //调用反射执行方法
            $object = $this->invokeFunction($this->bind[$abstract], $vars);
        } else {
            echo "12.2.4、调用反射执行类的实例化<br>";
            //调用反射执行类的实例化
            $object = $this->invokeClass($abstract, $vars);
            echo "12.2.5 调用反射执行类的实例化结束<br>";
        }

        if (!$newInstance) {
            echo "12.2.6 将实例化的类保存在容器中开始<br>";
            $this->instances[$abstract] = $object;
            echo "12.2.7 将实例化的类保存在容器中结束<br>";
        }
        echo "12.2.8 获取容器中的对象实例结束<br>";
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
        echo "12.2.4.1、调用反射执行类的实例化开始(invokeClass)<br>";
        try {
            //调用反射查看类的有关信息
            $reflect = new ReflectionClass($class);
            //判断类中是否有__make方法
            if ($reflect->hasMethod('__make')) {
                //调用反射查看方法的有关信息
                $method = new ReflectionMethod($class, '__make');
                //判断该方法是否是Public并且是Static
                if ($method->isPublic() && $method->isStatic()) {
                    echo "12.2.4.2、bindParams<br>";
                    //获取该方法参数
                    $args = $this->bindParams($method, $vars);
                    echo "12.2.4.3、bindParams结束<br>";
                    echo "12.2.4.4、invokeArgs<br>";
                    //带参数执行该方法
                    return $method->invokeArgs(null, $args);
                }
            }
            //获取类的构造函数
            $constructor = $reflect->getConstructor();
            echo "12.2.4.5、bindParams<br>";
            //获取类的构造函数的参数
            $args = $constructor ? $this->bindParams($constructor, $vars) : [];
            echo "12.2.4.6、bindParams 结束<br>";
            echo "12.2.4.7、newInstanceArgs 开始<br>";
            //用给出的参数创建一个新的类实例
            $object = $reflect->newInstanceArgs($args);
            echo "12.2.4.8、newInstanceArgs 结束<br>";
            echo "12.2.4.9 invokeAfter 开始<br>";
            //$this->invokeAfter($class, $object);
            echo "12.2.4.10 invokeAfter结束<br>";
            return $object;
        } catch (ReflectionException $e) {
            throw new ClassNotFoundException('class not exists: ' . $class, $class, $e);
        }
    }

    /**
     * 执行函数或者闭包方法 支持参数调用
     * @access public
     * @param string|array|Closure $function 函数或者闭包
     * @param array                $vars     参数
     * @return mixed
     */
    public function invokeFunction()
    {
        try {
            $reflect = new ReflectionFunction($function);

            $args = $this->bindParams($reflect, $vars);

            if ($reflect->isClosure()) {
                // 解决在`php7.1`调用时会产生`$this`上下文不存在的错误 (https://bugs.php.net/bug.php?id=66430)
                return $function->__invoke(...$args);
            } else {
                return $reflect->invokeArgs($args);
            }
        } catch (ReflectionException $e) {
            // 如果是调用闭包时发生错误则尝试获取闭包的真实位置
            if (isset($reflect) && $reflect->isClosure() && $function instanceof Closure) {
                $function = "{Closure}@{$reflect->getFileName()}#L{$reflect->getStartLine()}-{$reflect->getEndLine()}";
            } else {
                $function .= '()';
            }
            throw new Exception('function not exists: ' . $function, 0, $e);
        }
    }

    /**
     * 执行invokeClass回调
     * @access protected
     * @param string $class  对象类名
     * @param object $object 容器对象实例
     * @return void
     */
    protected function invokeAfter(string $class, $object): void
    {
        if (isset($this->invokeCallback['*'])) {
            foreach ($this->invokeCallback['*'] as $callback) {
                $callback($object, $this);
            }
        }

        if (isset($this->invokeCallback[$class])) {
            foreach ($this->invokeCallback[$class] as $callback) {
                $callback($object, $this);
            }
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
        echo "12.2.4.5.1、bindParams开始(bindParams)<br>";
        //调用反射查看查看参数数目
        if ($reflect->getNumberOfParameters() == 0) {
            return [];
        }

        // 函数将内部指针指向数组中的第一个元素并输出，若数组为空则返回 FALSE
        reset($vars);
        // 从当前内部指针位置返回元素键名，若参数为空则返回null
        $type   = key($vars) === 0 ? 1 : 0;
        //调用反射查看获取方法的参数
        $params = $reflect->getParameters();
        $args   = [];
        foreach ($params as $param) {
            //调用反射获取对象的参数名称
            $name      = $param->getName();
            //调用反射获取对象的类名
            $class     = $param->getClass();
            //将获取对象的参数名称驼峰转下划线
            //$lowerName = Str::snake($name);
            if ($class) {
                //根据对象的类名名获取参数
                $args[] = $this->getObjectParam($class->getName(), $vars);
            } elseif (1 == $type && !empty($vars)) {
                //合并参数
                $args[] = array_shift($vars);
            } elseif (0 == $type && isset($vars[$name])) {
                $args[] = $vars[$name];
            } elseif (0 == $type && isset($vars[$lowerName])) {
                $args[] = $vars[$lowerName];
            } elseif ($param->isDefaultValueAvailable()) {
                //获取默认参数
                $args[] = $param->getDefaultValue();
            } else {
                throw new \InvalidArgumentException('method param miss:' . $name);
            }
        }
        return $args;
    }

    /**
     * 获取对象类型的参数值
     * @access protected
     * @param string $className 类名
     * @param array  $vars      参数
     * @return mixed
     */
    protected function getObjectParam(string $className, array &$vars)
    {
        echo "12.2.4.5.1.1、getObjectParam开始(getObjectParam)<br>";
        $array = $vars;
        $value = array_shift($array);
        if ($value instanceof $className) {
            $result = $value;
            array_shift($vars);
        } else {
            echo "12.2.4.5.1.2、getObjectParam进入make<br>";
            $result = $this->make($className);
            echo "12.2.4.5.1.3、返回<br>";
        }
        return $result;
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
        echo "12、调用".$name."开始(__get)<br>";
        return $this->get($name);
    }
}