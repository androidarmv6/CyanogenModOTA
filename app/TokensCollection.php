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
        private $list = array();

        public function __construct($channels, $physicalPath, $device, $after, $limit)
        {
            $total = 0;
            $stable_limit = $limit;
            $nightly_limit = $limit;
            // Provide 25% stable build if all channels are requested
            if (in_array('stable', $channels) && in_array('nightly', $channels)) {
                $stable_limit = (int)($limit / 4);
            }
            if (in_array('stable', $channels)) {
                $total = $this->add($physicalPath.'/stable', $device, $after, 'stable', $stable_limit);
            }
            if (in_array('nightly', $channels)) {
                $nightly_limit = $limit - $total;
                $this->add($physicalPath, $device, $after, 'nightly', $nightly_limit);
            }
        }

        private function add($dir, $device, $after, $channel, $limit)
        {
            if (!file_exists($dir))
                return 0;

            $sortedArray = array();
            $dirIterator = new DirectoryIterator($dir);
            foreach ($dirIterator as $fileinfo) {
                if ($fileinfo->isFile() && $fileinfo->getExtension() == 'zip' &&
                    file_exists($dir.'/'.$fileinfo->getFilename().'.md5sum')) {
                    $token = new Token($fileinfo->getFilename(), $dir, $device, $channel);
                    if ($token->timestamp > $after) {
                        $sortedArray[] = $token;
                    }
                }
            }
            $count = count($sortedArray);
            $i = 0;
            if ($count > 0) {
                usort($sortedArray, function($a,$b){ /*Reverse order (b-a)*/ return $b->timestamp - $a->timestamp; });
                for($i = 0; $i < $count && $i < $limit; $i++) {
                    $this->list[] = $sortedArray[$i];
                }
            }
            return $i;
        }

        public function getUpdateList() {
            $ret = array();
            $count = count($this->list);
            for ($i = 0; $i < $count; $i++) {
                 $token = $this->list[$i];
                 $ret[] = array(
                    'url' => $token->url,
                    'timestamp' => $token->timestamp,
                    'md5sum' => $token->md5sum,
                    'filename' => $token->filename,
                    'incremental' => $token->incremental,
                    'channel' => $token->channel,
                    'changes' => $token->changes,
                    'api_level' => $token->api_level
                );
            }
            return $ret;
        }
    };
