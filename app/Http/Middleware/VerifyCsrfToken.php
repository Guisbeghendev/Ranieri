<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    // Aqui você pode adicionar exceções, se necessário:
    protected $except = [
        //
    ];
}
