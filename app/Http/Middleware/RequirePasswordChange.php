<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePasswordChange
{
   public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // إذا الـ user موظف وما غيّر كلمة المرور بعد
        if ($user && $user->hasRole('employee') && $user->is_first_login) {
            return response()->json([
                'message'                  => 'يجب تغيير كلمة المرور أولاً.',
                'requires_password_change' => true,
            ], 403);
        }

        return $next($request);
    }
}
