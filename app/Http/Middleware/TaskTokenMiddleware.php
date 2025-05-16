<?php

namespace App\Http\Middleware;

use App\Models\Task;
use Closure;
use Illuminate\Http\Request;

class TaskTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  string  $tokenType  Either 'edit' or 'delete'
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $tokenType)
    {
        $id = $request->route('id');
        $token = $request->route('token');

        $task = Task::find($id);

        if (! $task) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        $tokenField = $tokenType.'_token';

        if ($token !== $task->$tokenField) {
            return response()->json(['error' => 'Invalid token'], 403);
        }

        return $next($request);
    }
}
