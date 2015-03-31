HttpParser
====
the httpparser is a php parsor to deal with http request.
the purpose is to solve a kind of http content types,such as **application/json application/xml 
application/x-www-form-urlencoded multipart/form-data** especially multipart/form-data.
we all know that the http request contain CONNECT DELETE GET HEAD OPTIONS PATCH POST PUT TRACE. for many frameworks such as laravel 
 slim use the php://input method to get http body,while this is not very well to deal with multipart/form-data as not POST method(patch put).

-----
 httpparser是一个处理http请求的php解析器,通过httpparsor来获取http请求数据
主要处理content types 为**application/json application/xml application/x-www-form-urlencoded multipart/form-data**，特别是在restful模式下面
经常需要处理multipart/form-data，而且请求方式不为POST的情况
##  特性
* 统一采用php://input方式获取数据
* 针对multipart/form-data进行分块解析

## 类的基本说明
* http 全局处理http 请求
* parser 处理各种content types
* collection a collection class一个集合类
* body an HTTP message body 一个消息体

## attentions 注意事项
* 当使用非POST方式上传文件时不再使用$_FILES数组处理
* 当使用非POST方式上传文件时文件内容不是文件名的方式而是采用字符串的方式保存

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

 
