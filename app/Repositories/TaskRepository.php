<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class TaskRepository
{
    /**
     * Provides the base query builder instance for the Task model.
     *
     * This method returns a query builder object that can be used
     * to perform further query operations in here
     *
     * @return Builder The query builder instance.
     */
    private function baseQuery(): Builder
    {
        return Task::query();
    }

    /**
     * Get all tasks, ordered by creation date.
     * @return Collection
     * @throws \Exception
     */
    public function getAllTasks(): Collection
    {
        return $this->baseQuery()->latest()->get();
    }

    /**
     * Stores the provided validated data in the database.
     *
     * This method uses the `updateOrCreate` functionality
     * to either update an existing record with the matching
     * ID or create a new record if the ID does not exist.
     *
     * @param array $validatedData The validated data to store.
     * @return Task The stored or updated task instance.
     */
    public function store(array $validatedData): Task
    {
        return $this->baseQuery()->updateOrCreate([
            'id' => $validatedData['id'] ?? null,
        ], $validatedData);
    }

    /**
     * Deletes a record from the database by its ID.
     *
     * @param int $id The ID of the record to be deleted.
     * @return bool|null True if the deletion was successful, false or null otherwise.
     */
    public function delete(int $id): ?bool
    {
        return $this->baseQuery()->find($id)->delete();
    }
}
