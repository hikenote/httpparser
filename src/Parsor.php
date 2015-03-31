<?php
namespace HttpParser;

class Parser{
    /*
     * create a parser
     */
    public function __construct(){
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
                    $data[$name] = [];
                    $data[$name]['name'] = $filename;
                    $data[$name]['body'] = $body;
                    $data[$name]['size'] = strlen($body);
                    $data[$name]['type'] = $headers['content-type'];
                    if($data[$name]['size']/1024/1024 > (int)ini_get('upload_max_filesize')){
                        $data[$name]['error'] = UPLOAD_ERR_INI_SIZE;
                        continue;
                    }
                    if($data[$name]['size'] <= 0){
                        $data[$name]['error'] = UPLOAD_ERR_NO_FILE;
                        continue;
                    }
                    $data[$name]['error'] = UPLOAD_ERR_OK;
                    continue;
                }
                $data[$name] = substr($body, 0, strlen($body) - 2);
            }
        }
        return $data;
    }
}