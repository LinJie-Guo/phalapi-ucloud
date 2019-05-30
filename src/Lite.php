<?php
/*
 * +----------------------------------------------------------------------
 * | 图片服务器上传接口
 * +----------------------------------------------------------------------
 * | Copyright (c) 2015 summer All rights reserved.
 * +----------------------------------------------------------------------
 * | Author: summer <aer_c@qq.com> <qq7579476>
 * +----------------------------------------------------------------------
 * | This is not a free software, unauthorized no use and dissemination.
 * +----------------------------------------------------------------------
 * | Date
 * +----------------------------------------------------------------------
 */

namespace PhalApi\UCloud;

class Lite {

    protected $config = array(

        //上传的API地址,不带http://
        'api' => '',

        //统一的key
        'accessKey' => '',
        'secretKey' => '',

        //自定义配置的空间
        'bucket' => '',
        'host' => '', //必带http://

        'timeout' => 60
    );

    //上传文件信息
    private $upload_file;

    //文件存储的默认路径
    // private $default_path = 'demo';

    //文件存储路径
    private $save_path;

    //上传文件名
    public $file_name;

    //上传文件后缀名
    private $ext;

    //错误信息
    public $error = '';
    
    public function __construct() {
        if(\PhalApi\DI()->config->get('app.UCloud'))
            $this->config = array_merge($this->config, \PhalApi\DI()->config->get('app.UCloud'));
    }

    /**
     * 设置
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function set($key,$value){
        $this->$key = $value;
    }

    /**
     * 读取
     */
    public function get($key){
        return $this->$key;
    }

    /**
     * 上传操作
     *
     * @param string $field 上传的文件信息
     * @return array or bool
     */
    public function upfile($fileData) {  
        //上传文件
        $this->upload_file = $fileData;
        if ($this->upload_file['tmp_name'] == ""){
            $this->setError('File is not empty！');
            return false;
        }

        //文件后缀名
        $tmp_ext = explode(".", $this->upload_file['name']);
        $tmp_ext = $tmp_ext[count($tmp_ext) - 1];
        $this->ext = strtolower($tmp_ext);

        //设置文件名称
        //if(empty($this->file_name)){
            $this->setFileName();
        //}

        $config = $this->config;

        //获取上传引擎信息
        //DI()->loader->addDirs('Library/UCloud');
        $engine = 'PhalApi\UCloud\Engine\\' . ucfirst(\PhalApi\DI()->config->get('app.UCloudEngine'));
        $upload = new $engine('',$config);

        //设置图片信息
        $file = $this->upload_file;
        $file['savepath'] = $this->setPath();
        $file['savename'] = $this->file_name;

        //开始上传
        $res = $upload->save($file);

        if (!$res) {
            \PhalApi\DI()->logger->debug('failed to upload file to '. $engine, 
                array('Err' => $upload->getError()));

            return false;
        } else {

            $fileName = $this->save_path . '/' . $this->file_name;
            $fileUrl = $config['host'] . '/' . $fileName;

            \PhalApi\DI()->logger->debug('succeed to upload file to '.$engine, $fileUrl);

            return array(
                'url' => $fileUrl,
                'file' => $fileName
            );
        }
    }

    /**
     * 设置文件名称 不包括 文件路径
     *
     * 生成(从2000-01-01 00:00:00 到现在的秒数+微秒+四位随机)
     */
    private function setFileName(){
        if(empty($this->file_name)){
            $tmp_name = sprintf('%010d',time() - 946656000)
                            . sprintf('%03d', microtime(true) * 1000)
                            . sprintf('%04d', mt_rand(0,9999));
        }else{
            $tmp_name = $this->file_name;
        }
        
        $this->file_name = $tmp_name . '.' . $this->ext;
    }

    /**
     * 设置文件存储路径
     */
    private function setPath(){
        $rootPath = $this->config['root_path'] ?? 'demo';
        if($this->save_path)
            return $rootPath . '/' . $this->save_path;
        else
            return $rootPath;
    }

    /**
     * 设置错误信息
     *
     * @param string $error 错误信息
     * @return bool 布尔类型的返回结果
     */
    private function setError($error){
        $this->error = $error;
    }
}
