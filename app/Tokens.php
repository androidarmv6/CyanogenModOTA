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

    class Token {

        public $device = '';
        public $api_level = 0;
        public $incremental = '';
        public $timestamp = 0;
        public $md5sum = '';
        public $channel = '';
        public $filename = '';
        public $url = '';
        public $changes = '';

        public function __construct($fileName, $physicalPath, $device, $channel)
        {
            list(
            $this->device,
            $this->api_level,
            $this->incremental,
            $this->timestamp,
            $this->md5sum) = Cache::mcCacheProps($physicalPath.'/'.$fileName, $device, $channel);
            $this->channel = $channel;
            $this->filename = $fileName;
            $this->url = Utils::getUrl($fileName, $device, false, $channel);
            $this->changes = str_replace('.zip', '.txt', $this->url);
        }
    };
