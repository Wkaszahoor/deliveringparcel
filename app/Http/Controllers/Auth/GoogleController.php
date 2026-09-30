<?php

namespace App\Http\Controllers\Auth;

class GoogleController extends SocialAuthController
{
    protected string $provider = 'google';
    protected string $idColumn = 'google_id';
}
