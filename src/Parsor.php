<?php
namespace HttpParsor;

class Parsor{
    /*
     * the file/image saved path
     */
    protected $dir;
    /*
     * create a parsor
     */
    public function __construct($dir){
        $this->dir = $dir;
    }
    /*
     * parse a application/xml type input
     */
    public function xmlParse($input){
        if(!$input){
           return $input;
        }
        return simplexml_load_string($input);
    }

    /*
     * parse a application/json type input
     */
    public function jsonParse($input){
        if(!$input){
            return $input;
        }
        return json_decode($input, 1);
    }

    /*
     * parse a application/x-www-form-urlencoded type input
     */
    public function urlencodedParse($input){
        if(!$input){
            return $input;
        }
        parse_str($input, $data);
        return $data;
    }

    /*
     * parse a multipart/form-data type input
     */
    public function multipartParse($input, $method='POST'){
        if($method == 'POST'){  //in this condition the php://input method will be null
            return $_POST;
        }
        $boundary = substr($input, 0, strpos($input, "\r\n"));
        $parts = array_slice(explode($boundary, $input), 1);
        $data = array();
        foreach ($parts as $part) {
            // If this is the last part, break
            if ($part == "--\r\n") break;

            // Separate content from headers
            $part = ltrim($part, "\r\n");
            list($raw_headers, $body) = explode("\r\n\r\n", $part, 2);

            // Parse the headers list
            $raw_headers = explode("\r\n", $raw_headers);
            $headers = array();
            foreach ($raw_headers as $header) {
                list($name, $value) = explode(':', $header);
                $headers[strtolower($name)] = ltrim($value, ' ');
            }

            // Parse the Content-Disposition to get the field name, etc.
            if (isset($headers['content-disposition'])) {
                $filename = null;
                preg_match(
                    '/^(.+); *name="([^"]+)"(; *filename="([^"]+)")?/',
                    $headers['content-disposition'],
                    $matches
                );
                list(, $type, $name) = $matches;
                isset($matches[4]) and $filename = $matches[4];
                if($filename){
                    $body = substr($body, 0, -2);
                    $tmp_name = $this->dir . '/' . md5($body);
                    //fill the $_FILES array
                    $_FILES[$name]['name'] = $filename;
                    $_FILES[$name]['tmp_name'] = $tmp_name;
                    $_FILES[$name]['size'] = strlen($body);
                    $_FILES[$name]['error'] = null;
                    $_FILES[$name]['type'] = $headers['content-type'];
                    file_put_contents($tmp_name, $body);  //make sure this dir is writeable otherwise will throw exception
                    continue;
                }
                $data[$name] = substr($body, 0, strlen($body) - 2);

            }

        }
        return $data;
    }
}