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

    // Memcached
    global $MEMCACHED;
    $MEMCACHED = NULL;

    class Cache
    {
        // Map for shared memcached:
        // 1) [incrementalno] = array(device, channel, timestamp, fullpathnameofota.zip)
        // 2) [fullpathnameofota.zip] = array(device, api_level, incremental, timestamp, md5sum)
        public static function registerMemcached()
        {
            global $MEMCACHED;

            if ($MEMCACHED == NULL) {
                $MEMCACHED = new Memcached();
                $MEMCACHED->addServer('localhost', 11211);
            }
        }

        public static function mcFind($incremental)
        {
            global $MEMCACHED;

            list($device, $channel, $timestamp, $zip) = $MEMCACHED->get($incremental);
            if (!empty($zip) && !file_exists($zip))
            {
                $MEMCACHED->delete($zip);
                $MEMCACHED->delete($incremental);
                $zip = NULL;
                $timestamp = 0;
                $channel = NULL;
                $device = NULL;
            }
            return array($device, $channel, $timestamp, $zip);
        }

        public static function mcCacheProps($filePath, $device, $channel)
        {
            global $MEMCACHED;

            $cache = $MEMCACHED->get($filePath);
            if ($cache && $cache[0] != $device) {
                throw new Exception("$device != " . $cache[0] . " : cache corrupt");
            }
            elseif (!$cache && Memcached::RES_NOTFOUND == $MEMCACHED->getResultCode()) {
                $buildpropArray = explode("\n", file_get_contents('zip://'.$filePath.'#system/build.prop'));
                if ($device == Utils::getBuildPropValue($buildpropArray, 'ro.product.device')) {
                    $api_level = intval(Utils::getBuildPropValue($buildpropArray, 'ro.build.version.sdk'));
                    $incremental = Utils::getBuildPropValue($buildpropArray, 'ro.build.version.incremental');
                    $timestamp = intval(Utils::getBuildPropValue($buildpropArray, 'ro.build.date.utc'));
                    $cache = array($device, $api_level, $incremental, $timestamp, Utils::getMD5($filePath));
                    $MEMCACHED->set($filePath, $cache);
                    $MEMCACHED->set($incremental, array($device, $channel, $timestamp, $filePath));
                } else {
                    throw new Exception("$device: $filePath is in invalid path");
                }
            }
            return $cache;
        }
    };
