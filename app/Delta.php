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

    class Delta {

        public static function find($source_incremental, $target_incremental)
        {
            $ret = array();
            if (empty($source_incremental) || empty($target_incremental) ||
                $source_incremental == $target_incremental) {
                return $ret;
            }
            list($source_device, $source_timestamp, $source_releasetype) = Cache::mcFind($source_incremental);
            if (empty($source_device)) {
                return $ret;
            }
            list($target_device, $target_timestamp, $target_releasetype) = Cache::mcFind($target_incremental);
            if (empty($target_device)) {
                return $ret;
            }
            if (($source_timestamp > $target_timestamp) ||
                ($source_releasetype != $target_releasetype) ||
                ($source_device != $target_device)) {
                return $ret;
            }
            $channelDir = ($target_releasetype == 'RELEASE') ? 'stable' : '';
            $deltaFile = 'incremental-'.$source_incremental.'-'.$target_incremental.'.zip';
            $deltaFullPath = realpath('./_deltas/'. $target_device . '/' . $channelDir) . '/' . $deltaFile;
            if (file_exists($deltaFullPath) && file_exists($deltaFullPath.'.md5sum')) {
                $ret = array(
                   'date_created_unix' => filemtime($deltaFullPath),
                   'filename' => $deltaFile,
                   'download_url' => Utils::getUrl($deltaFile, $target_device, true, $target_releasetype),
                   'md5sum' => Utils::getMD5($deltaFullPath),
                   'incremental' => $target_incremental
                );
            }
            return $ret;
        }
    };
