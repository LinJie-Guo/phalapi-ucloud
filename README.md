# 图片上传扩展


## 安装和配置
修改项目下的composer.json文件，并添加：  
```
    "phalapi/ucloud":"dev-master"
```
在/path/to/phalapi/config/app.php文件中，配置： 
1. 本地上传配置：
```
    /**
     * 云上传引擎,支持local,oss,upyun
     */
    'UCloudEngine' => 'local',

    /**
     * 本地存储相关配置（UCloudEngine为local时的配置）
     */
    'UCloud' => array(
        //对应的文件路径
        'host' => 'http://localhost/PhalApi/Public/upload'
    ),
```
2. 云图片上传配置：
```
    /**
     * 云上传对应引擎相关配置
     * 如果UCloudEngine不为local,则需要按以下配置
     */
    'UCloud' => array(
        //上传的API地址,不带http://,以下api为阿里云OSS杭州节点
        'api' => 'oss-cn-hangzhou.aliyuncs.com',

        //统一的key
        'accessKey' => '',
        'secretKey' => '',

        //自定义配置的空间
        'bucket' => '',
        'host' => 'http://image.xxx.com', //必带http:// 末尾不带/

        'timeout' => 90
    ),
```

然后执行```composer update```。  

## 注册
在/path/to/phalapi/config/di.php文件中，注册：  
```php
$di->ucloud = function() {
    return new \PhalApi\Ucloud\Lite();
};
```

## 使用
先简单写个测试文件：
```php
<html>
    <form method="POST" action="./?service=Upload.upload" enctype="multipart/form-data">
        <input type="file" name="file">
        <input type="submit">
    </form>
</html>
```
2. 文件上传接口
```php
<?php
/*
 * +----------------------------------------------------------------------
 * | 上传接口
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


class Upload extends Api {

    /**
     * 获取参数
     * @return array 参数信息
     */
    public function getRules() {
        return array(
            'upload' => array(
                'file' => array(
                    'name' => 'file', 
                    'type' => 'file', 
                    'min' => 0, 
                    'max' => 1024 * 1024, 
                    'range' => array('image/jpg', 'image/jpeg', 'image/png'), 
                    'ext' => array('jpg', 'jpeg', 'png')
                ),
            ),
        );
    }

    /**
     * 上传文件
     * @return string $url 绝对路径
     * @return string $file 相对路径，用于保存至数据库，按项目情况自己决定吧
     */
    public function upload() {

        //设置上传路径 设置方法参考3.2
        \PhalApi\DI()->ucloud->set('save_path',date('Y/m/d'));

        //新增修改文件名设置上传的文件名称
        \PhalApi\DI()->ucloud->set('file_name', 'avatar');

        //上传表单名
        $res = \PhalApi\DI()->ucloud->upfile($this->file);

        return $rs;
    }
}
?>
```
3. 设置上传路径

按照以上设置，将会自动生成4层目录(demo/2015/13/7/aaa.jpg)，demo其实为项目名称，你可以在每个项目入口设置一个常量等于项目名称，然后打开拓展library/UCloud/Lite.php找到$default_path，将该值设置为你设定的常量，或者为空（不是NULL），为空后你可以在设置上传路径里面设置（项目名/2015/12/07）也是可以的!