<?php

namespace App\Http\Controllers;

use Dingo\Api\Http\Request;
use Dingo\Api\Routing\Helpers;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


abstract class Controller extends BaseController
{
    //use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    use Helpers;

    /**
     * 提取json请求中的data数组
     *
     * @param Request $request
     * @return mixed
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    protected function data(Request $request)
    {
        if (!$request->isJson()) throw new BadRequestHttpException();
        $data = $request->json('data');
        if (!$data) throw new BadRequestHttpException();
        return $data;
    }

    /**
     * @param Request $request
     * @param array $keys
     * @return mixed
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    protected function getData(Request $request, array $keys = [])
    {
        if (!$request->isJson())
            throw new BadRequestHttpException('Json only.');

        if (! $data = $request->json('data'))
            throw new BadRequestHttpException('Missing string key: data.');

        if (!empty($keys)) {
            return array_filter_keys($data, $keys);
        }

        return $data;
    }
}
