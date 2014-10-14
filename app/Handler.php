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

    // Handler class
    class Handler
    {
        public static function HandleApi()
        {
            require_once 'Cache.php';
            require_once 'Tokens.php';
            require_once 'TokensCollection.php';
            require_once 'Utils.php';

            $ret = array('id' => null, 'result' => array(), 'error' => null);
            $postJson = json_decode(Flight::request()->getBody());
            if ($postJson != NULL && !empty($postJson->params) && !empty($postJson->params->device)) {
                $device = $postJson->params->device;
                $devicePath = realpath('./_builds/'.$device);
                if (file_exists($devicePath)) {
                    $after = 0;
                    $limit = empty($postJson->params->limit) ? 25 : intval($postJson->params->limit);
                    $channels = empty($postJson->params->channels) ? array() : $postJson->params->channels;
                    Cache::registerMemcached();
                    // Source_incremental is provided by CMUpdater
                    if (!empty($postJson->params->source_incremental)) {
                        // Offer only new builds after source rom.
                        list(,$after,$releasetype,) = Cache::mcFind($postJson->params->source_incremental);
                        if (in_array('snapshot', $channels) && in_array('nightly', $channels)) {
                            $after = 0; // 'All versions' is selected, disable time check and offer older builds.
                        }
                        if (!in_array('stable', $channels)) {
                            $channels[] = 'stable'; // We offer stable releases by default for CMUpdater
                        }
                    }
                    $tokens = new TokenCollection($channels, $devicePath, $device, $after, $limit);
                    $ret['result'] = $tokens->getUpdateList();
                 }
            }
            Flight::json($ret);
        }

        public static function HandleGetDelta()
        {
           require_once 'Cache.php';
           require_once 'Delta.php';
           require_once 'Utils.php';

           $ret = array();
           $postJson = json_decode(Flight::request()->getBody());
           if ($postJson != NULL && !empty($postJson->source_incremental) && !empty($postJson->target_incremental)) {
               Cache::registerMemcached();
               $ret = Delta::find($postJson->source_incremental, $postJson->target_incremental);
           }
           if (empty($ret)) {
               $ret['errors'] = array('message' => 'Unable to find delta');
           }
           Flight::json($ret);
        }
    }

