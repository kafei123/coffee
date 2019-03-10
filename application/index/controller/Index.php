<?php

namespace app\index\controller;

use app\index\model\Index as IndexModel;
use coffee\Db;

class Index
{
    public function index ()
    {
        var_dump('index');

        Db::connect()->table('users')
            ->where('email', '123@qq.com')
            ->join([
                [
                    'table'         => 'users_money',
                    'primary_key'   => 'id',
                    'foreign_key'   => 'user_id',
                    'field'         => 'id,money',
                    'where'         => [
                        'id'        => [
                            '=' => 1,
                            'in' => 2
                        ]
                    ],
                    'order'         => [
                        'created_at' => 'desc'
                    ],
                    'type'          => 'leftJoin',
                    'is_join'       => true
                ],
                [
                    'table'         => 'users_pass',
                    'primary_key'   => 'id',
                    'foreign_key'   => 'user_id',
                    'field'         => 'id,password',
                    'where'         => [
                        'id' => [
                            '=' => 1,
                            'in' => 2
                        ]
                    ],
                    'order'         => [
                        'created_at' => 'desc'
                    ],
                    'type'          => 'leftJoin',
                    'is_join'       => true
                ]
            ], true)
            ->select();

        var_dump(1);

        $server = new IndexModel();

        $server->index();
    }

    public function config ()
    {
        echo 'config';
    }

    public function geta ()
    {
        echo 'geta';
    }
}
