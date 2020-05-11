<?php
declare (strict_types = 1);

namespace jz;

/**
 * App 基础类
 * @property Route      $route
 * @property Http       $http
 *
 */
class App extends Container
{
    /**
     * 应用根目录
     * @var string
     */
    protected $rootPath = '';
    /**
     * 框架目录
     * @var string
     */
    protected $topjzPath = '';
    /**
     * 应用目录
     * @var string
     */
    protected $appPath = '';
    /**
     * Runtime目录
     * @var string
     */
    protected $runtimePath = '';

    /**
     * 容器绑定标识
     * @var array
     */
    protected $bind = [
        'app'                     => App::class,
        'cache'                   => Cache::class,
        'config'                  => Config::class,
        'console'                 => Console::class,
        'cookie'                  => Cookie::class,
        'db'                      => Db::class,
        'env'                     => Env::class,
        'event'                   => Event::class,
        'http'                    => Http::class,
        'lang'                    => Lang::class,
        'log'                     => Log::class,
        'middleware'              => Middleware::class,
        'request'                 => Request::class,
        'response'                => Response::class,
        'route'                   => Route::class,
        'session'                 => Session::class,
        'validate'                => Validate::class,
        'view'                    => View::class,
        'filesystem'              => Filesystem::class,
        'jz\DbManager'         => Db::class,
        'jz\LogManager'        => Log::class,
        'jz\CacheManager'      => Cache::class,
        'Psr\Log\LoggerInterface' => Log::class,// 接口依赖注入
    ];

    /**
     * 架构方法
     * @access public
     * @param string $rootPath 应用根目录
     */
    public function __construct(string $rootPath = '')
    {
        echo "1、construct开始<br>";
        echo "2、设置系统目录开始<br>";
        $this->topjzPath   = dirname(__DIR__) . DIRECTORY_SEPARATOR;//D:\dev\wwwroot\demo\vendor\topjz\framework\src\
        $this->rootPath    = $rootPath ? rtrim($rootPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR : $this->getDefaultRootPath();//D:\dev\wwwroot\demo\
        $this->appPath     = $this->rootPath . 'app' . DIRECTORY_SEPARATOR;//D:\dev\wwwroot\demo\app\
        $this->runtimePath = $this->rootPath . 'runtime' . DIRECTORY_SEPARATOR;//D:\dev\wwwroot\demo\runtime\
        echo "3、设置系统目录结束<br>";

        if (is_file($this->appPath . 'provider.php')) {
            echo "4、绑定provider.php中的类到容器开始<br>";
            $this->bind(include $this->appPath . 'provider.php');
            echo "5、绑定provider.php中的类到容器结束<br>";
        }

        echo "6、设置当前容器的实例<br>";
        static::setInstance($this);
        echo "7、设置当前容器的实例结束<br>";

        echo "8、绑定一个应用对象（App类）实例到容器<br>";
        $this->instance($this->bind['app'], $this);
        echo "9 绑定一个应用对象（App类）实例到容器结束<br>";

        echo "10、绑定另一个应用对象（Container类）实例到容器容器<br>";
        $this->instance('jz\Container', $this);
        echo "11、绑定另一个类实例到容器结束<br><br>";
    }

    /**
     * 获取应用根目录
     * @access protected
     * @since V1.0
     * @return string
     */
    protected function getDefaultRootPath(): string
    {
        $path = dirname(dirname(dirname(dirname($this->topjzPath))));
        return $path . DIRECTORY_SEPARATOR;
    }

    /**
     * 获取应用基础目录
     * @access public
     * @return string
     * @since V1.0
     */
    public function getBasePath(): string
    {
        return $this->rootPath . 'app' . DIRECTORY_SEPARATOR;
    }
}