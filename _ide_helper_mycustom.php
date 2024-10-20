<?php

namespace Illuminate\Support\Facades {
    /**
     * @see \Illuminate\Support\Facades\Redis
     */
    class Redis
    {
        /**
         * @return mixed
         */
        public static function get(string $key) {}

        /**
         * @return bool
         */
        public static function set(string $key, mixed $value) {}

        /**
         * @return bool
         */
        public static function setex(string $key, int $seconds, mixed $value) {}

        /**
         * @return int
         */
        public static function exists(string $key) {}

        /**
         * @return int
         */
        public static function del(string ...$keys) {}
    }
}
