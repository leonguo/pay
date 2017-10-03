<?php
namespace app\index\controller;

class Index
{
    public function index()
    {
        return '';
    }

    public function hello()
    {
        return json(array("test" => 2));
    }
}
