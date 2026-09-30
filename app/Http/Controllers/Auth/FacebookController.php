<?php

namespace App\Http\Controllers\Auth;

class FacebookController extends SocialAuthController
{
    protected string $provider = 'facebook';
    protected string $idColumn = 'facebook_id';
}
