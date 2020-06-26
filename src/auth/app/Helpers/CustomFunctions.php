<?php

if (!function_exists('bcrypt')) {
    function bcrypt($value, $options = []) {
        return app('hash')->make($value, $options);
    }
}
