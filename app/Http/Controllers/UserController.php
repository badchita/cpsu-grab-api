<?php

namespace App\Http\Controllers;

use App\Http\Resources\UsersResource;
use App\Models\User;

class UserController extends Controller
{
    private $status = 200;
    public function getUser($id)
    {
        $user = User::find($id);
        $data = new UsersResource(new UsersResource($user));
        return response($data, $this->status);
    }
}
