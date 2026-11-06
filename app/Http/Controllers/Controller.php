<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    // Habilita $this->authorize(...) para aplicar las Policies (RBAC) en cada acción.
    use AuthorizesRequests;
}
