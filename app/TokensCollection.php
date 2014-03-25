<?php
    /*
        The MIT License (MIT)

        Copyright (c) 2014 Julian Xhokaxhiu

        Permission is hereby granted, free of charge, to any person obtaining a copy of
        this software and associated documentation files (the "Software"), to deal in
        the Software without restriction, including without limitation the rights to
        use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
        the Software, and to permit persons to whom the Software is furnished to do so,
        subject to the following conditions:

        The above copyright notice and this permission notice shall be included in all
        copies or substantial portions of the Software.

        THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
        IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
        FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
        COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
        IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
        CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
    */

    class TokenCollection {
        var $list = array();

        public function __construct($postJson, $physicalPath, $baseUrl, $device, $after) {

            $dirIterator = new DirectoryIterator($physicalPath);
            foreach ($dirIterator as $fileinfo) {
                if ($fileinfo->isFile() && $fileinfo->getExtension() == 'zip') {
                    $fileName = $fileinfo->getFilename();
                    $token = new Token($fileName, $physicalPath, $baseUrl, $device);
                    if ($token->isValid($postJson['params'], $fileinfo, $after)) {
                        array_push($this->list, $token);
                    }
                }
            }
        }

        public function getUpdateList(){
            $ret = array();
            foreach ($this->list as $token) {
                array_push($ret, array(
                    'url' => $token->url,
                    'timestamp' => $token->timestamp,
                    'md5sum' => $token->md5file,
                    'filename' => $token->filename,
                    'incremental' => $token->incremental,
                    'channel' => $token->channel,
                    'changes' => $token->changelogUrl,
                    'api_level' => $token->api_level
                ));
            }
            return $ret;
        }
    };
