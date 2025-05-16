<?php

namespace App\Services;

use App\Repositories\TaskRepository;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Task;

class TaskService
{
    private const CACHE_KEY_ALL_TASKS = 'tasks.all';
    private const CACHE_TTL_MINUTES = 5;

    public function __construct(
        protected TaskRepository $taskRepository,
        protected Cache $cache
    ) {
    }

    /**
     * Retrieve all tasks, caching the results for a specific duration.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllTasks(): Collection
    {
        return $this->cache->remember(self::CACHE_KEY_ALL_TASKS, 60 * self::CACHE_TTL_MINUTES, function () {
            return $this->taskRepository->getAllTasks();
        });
    }

    /**
     * Stores a task using the provided data.
     *
     * @param array $data The data used to create the task.
     * @return Task The stored task model.
     */
    public function storeTask(array $data): Task
    {
        $task = $this->taskRepository->store($data);
        $this->invalidateTasksCache();
        return $task;
    }

    /**
     * Updates an existing task using the provided ID and data.
     *
     * @param int $id The unique identifier of the task to update.
     * @param array $data The data to update the task with.
     * @return Task The updated task model.
     */
    public function updateTask(int $id, array $data): Task
    {
        $data['id'] = $id;
        $task = $this->taskRepository->store($data);
        $this->invalidateTasksCache();
        return $task;
    }

    /**
     * Deletes a task by its identifier.
     *
     * @param int $id The identifier of the task to be deleted.
     * @return bool Whether the deletion was successful.
     */
    public function deleteTask(int $id): bool
    {
        $result = $this->taskRepository->delete($id);
        $this->invalidateTasksCache();
        return $result;
    }

    /**
     * Invalidates the cached tasks.
     *
     * @return void
     */
    private function invalidateTasksCache(): void
    {
        $this->cache->forget(self::CACHE_KEY_ALL_TASKS);
    }
}
