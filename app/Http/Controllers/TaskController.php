<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskStoreRequest;
use App\Http\Requests\TaskUpdateRequest;
use App\Http\Resources\TaskResource;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskController extends Controller
{
    public function __construct(
        protected TaskService $taskService
    ) {
    }

    /**
     * Retrieve a collection of all tasks.
     *
     * @return AnonymousResourceCollection Returns a resource collection of all tasks.
     */
    public function index(): AnonymousResourceCollection
    {
        $tasks = $this->taskService->getAllTasks();
        return TaskResource::collection($tasks);
    }

    /**
     * Handle the storage of a task.
     *
     * @param TaskStoreRequest $request The incoming request containing task data.
     * @return TaskResource Returns a resource of the stored task.
     */
    public function store(TaskStoreRequest $request): TaskResource
    {
        $task = $this->taskService->storeTask($request->validated());
        return new TaskResource($task);
    }

    /**
     * Handle the secure update of a task.
     *
     * @param TaskUpdateRequest $request The incoming request containing updated task data.
     * @param int $id The unique identifier of the task to update.
     * @return TaskResource Returns a resource of the updated task.
     */
    public function secureUpdate(TaskUpdateRequest $request, int $id): TaskResource
    {
        $task = $this->taskService->updateTask($id, $request->validated());
        return new TaskResource($task);
    }

    /**
     * Handle the secure deletion of a task.
     *
     * @param int $id The ID of the task to be deleted.
     * @return JsonResponse Returns a JSON response with the result.
     */
    public function secureDestroy(int $id): JsonResponse
    {
        $result = $this->taskService->deleteTask($id);

        return response()->json([
            'success' => $result,
            'message' => $result ? 'Task deleted successfully' : 'Failed to delete task'
        ], $result ? 200 : 400);
    }
}
