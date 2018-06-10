<?php
/**
 * Auto generate source code header.
 * Original File Name: IndexController.php
 * Author: Wangjian
 * Date: 2017/2/27
 * Time: 14:07
 */

namespace App\Http\Controllers;

class IndexController extends Controller
{
    public function index()
    {
        return $this->response->array([
            'message' => 'Ocicat-api-pre1'
        ]);
    }
}