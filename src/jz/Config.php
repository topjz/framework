<?php


namespace jz;


class Config
{
    /**
     * 配置参数
     * @var array
     */
    protected $config = [];

    /**
     * 配置文件目录
     * @var string
     */
    protected $path;

    /**
     * 配置文件后缀
     * @var string
     */
    protected $ext;

    /**
     * 构造方法
     * @access public
     */
    public function __construct(string $path = null, string $ext = '.php')
    {
//        $this->path = $path ?: '';
//        $this->ext = $ext;
        
    }
}