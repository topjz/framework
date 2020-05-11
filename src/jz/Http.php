<?php
declare (strict_types = 1);

namespace jz;

class Http
{
    /**
     * @var App
     */
    protected $app;

    /**
     * 是否多应用模式
     * @var bool
     */
    protected $multi = false;

    /**
     * 是否域名绑定应用
     * @var bool
     */
    protected $bindDomain = false;

    /**
     * Http 构造函数.
     * @param App $app
     */
    public function __construct(App $app,string $st1="",string $ru='1')
    {
        $this->app   = $app;
        $this->multi = is_dir($this->app->getBasePath() . 'controller') ? false : true;
        var_dump($this->multi);
    }

    /**
     * 是否域名绑定应用
     * @access public
     * @return bool
     */
    public function isBindDomain(): bool
    {
        return $this->bindDomain;
    }

    /**
     * 设置应用模式
     * @access public
     * @param bool $multi
     * @return $this
     */
    public function multi(bool $multi)
    {
        $this->multi = $multi;
        return $this;
    }

    /**
     * 执行应用程序
     * @access public
     * @param Request|null $request
     * @return Response
     */
    public function run(Request $request = null): Response
    {
        //自动创建request对象
        $request = $request ?? $this->app->make('request', [], true);
        $this->app->instance('request', $request);

        try {
            $response = $this->runWithRequest($request);
        } catch (Throwable $e) {
            $this->reportException($e);

            $response = $this->renderException($request, $e);
        }

        return $response->setCookie($this->app->cookie);
    }
}