<?php
/**
 * Created by PhpStorm.
 * User: wangjian
 * Date: 2016/12/4
 * Time: 15:22
 */

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use Dingo\Api\Exception\DeleteResourceFailedException;
use Dingo\Api\Http\Request;


abstract class ResourceController extends Controller
{
    const VERSION = 'v1';
    const RESOURCE_MODEL = '';

    public function presetGet(Resource $resource)
    {
        $transformer = $resource::TRANSFORMER;
        /** @var object $resource */
        return $this->response->item($resource, new $transformer());
    }

    public function presetAdd(Request $request, Resource $res)
    {
        $data = $this->getData($request);

        $res_name = $res->getMorphClass();

        if (! $res->fill($data)->save()) {
            throw new DeleteResourceFailedException("Could not add $res_name.");
        } else {
            return $this->response->created(route_api("$res_name.get", self::VERSION, ['id' => $res->id]));
        }
    }

    public function presetDelete(Resource $res)
    {
        $res_name = $res->getMorphClass();

        if (!$res->delete()) {
            throw new DeleteResourceFailedException("Could not delete $res_name.");
        } else {
            return $this->response->accepted(route_api("$res_name.get", self::VERSION, ['id' => $res->id]));
        }
    }

    public function presetUpdate(Request $request, Resource $res)
    {
        $data = $this->getData($request);
        $res_name = $res->getMorphClass();

        if (!$res->fill($data)->save()) {
            throw new DeleteResourceFailedException("Could not update $res_name.");
        } else {
            return $this->response->accepted(route_api("$res_name.get", self::VERSION, ['id' => $res->id]));
        }
    }
}