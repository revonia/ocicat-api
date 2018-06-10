<?php
use Dingo\Api\Routing\UrlGenerator;

/**
 * Created by PhpStorm.
 * User: wangjian
 * Date: 2016/12/8
 * Time: 11:44
 */

if ( ! function_exists('array_filter_keys'))
{
    /**
     * Return only needed item by given keys
     *
     * @param $input array
     * @param $keys array
     * @return array
     */
    function array_filter_keys($input, $keys)
    {
        return array_filter($input, function ($key) use ($keys) {
            return in_array($key, $keys);
        }, ARRAY_FILTER_USE_KEY);
    }
}

if (! function_exists('route_api')) {
    /**
     * Generate a URL to a named api route.
     *
     * @param  string $name
     * @param string $version
     * @param  array $parameters
     * @return string
     */
    function route_api($name, $version = null, $parameters = [])
    {
        $version = $version ?: getenv('API_VERSION');
        return app(UrlGenerator::class)
            ->version($version)
            ->route($name, $parameters);
    }
}

if (! function_exists('getvar')) {
    /**
     * @param $var
     * @param null $default
     * @return null
     */
    function getvar(&$var, $default = null)
    {
        return isset($var) ? $var : $default;
    }
}
