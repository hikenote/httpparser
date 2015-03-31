HttpParser
====
the httpparser is a php parser to deal with http request data.
the purpose is to solve a kind of http content types,such as **application/json application/xml 
application/x-www-form-urlencoded multipart/form-data** especially multipart/form-data.
we all know that the http request contain  DELETE GET HEAD  PATCH POST PUT. for many frameworks such as laravel 
 slim use the php://input method to get the http data,while this is not very well to deal with multipart/form-data as not POST method(as PATCH PUT).it most appears in RESTful pattern.

-----
 httpparser是一个处理http请求数据的php解析器,通过httpparser来解析各种类型的http请求数据
content-type 为**application/json application/xml application/x-www-form-urlencoded multipart/form-data**，特别是在restful模式下面
经常需要处理multipart/form-data，而且请求方式不为POST的情况
##  特性
* 统一采用php://input方式获取数据
* 针对非POST方式时的multipart/form-data进行分块解析
* 文件或图片完全储存在内存中操作速度非常快

## 类的基本说明
* http 全局处理http 请求
* parser 处理各种content-type
* collection a collection class一个集合类
* body an HTTP message body 一个消息体

## attentions 注意事项
* 当使用非POST方式上传文件时不再使用$_FILES数组处理
* 当使用非POST方式上传文件时文件内容不是文件名的方式而是采用字符串的方式保存在内存中

## examples
```php
$httpparser = new \HttpParser\Http();
$method = $httpparser->getMethod();
if($method == 'GET'){
   $queryParams = $httpparser->getQueryParams();  //get方式的获取数据
}else{
   $parsedBody = $httpparser->getParsedBody();   //非get方式获取数据
}
```

 
