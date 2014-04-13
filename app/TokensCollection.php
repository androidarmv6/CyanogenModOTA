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

        public function __construct($channels, $physicalPath, $device) {
            if (in_array('stable', $channels)) {
                $stableDir = $physicalPath . '/stable';
                $this->add($stableDir, $device, 'stable');
            }
            if (in_array('nightly', $channels)) {
                $this->add($physicalPath, $device, 'nightly');
            }
        }

        private function add($dir, $device, $channel) {
            if (!file_exists($dir))
                return;

            $dirIterator = new DirectoryIterator($dir);
            foreach ($dirIterator as $fileinfo) {
                if ($fileinfo->isFile() && $fileinfo->getExtension() == 'zip') {
                    $token = new Token($fileinfo->getFilename(), $dir, $device, $channel);
                    array_push($this->list, $token);
                }
            }
        }

        private static function tokenSort($tokenA, $tokenB) {
            // Reverse order
            return $tokenB->timestamp - $tokenA->timestamp;
        }

        public function getUpdateList($limit) {
            $ret = array();
            usort($this->list, array('TokenCollection','tokenSort'));
            $arrayCount = count($this->list);
            for ($count = 0;
                 $count < $limit && $count < $arrayCount;
                 $count++) {
                 $token = $this->list[$count];
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
