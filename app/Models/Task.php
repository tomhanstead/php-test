<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class Task extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'edit_token',
        'delete_token',
    ];

    protected $hidden = [
        'edit_token',
        'delete_token',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($task) {
            $task->edit_token = $task->edit_token ?? Str::random(64);
            $task->delete_token = $task->delete_token ?? Str::random(64);
        });
    }

    /**
     * Generate a signed URL for editing a task using the task's ID and edit token.
     *
     * @return string
     */
    public function getEditUrlAttribute(): string
    {
        return URL::signedRoute(
            'tasks.edit',
            ['id' => $this->id, 'token' => $this->edit_token]
        );
    }

    /**
     * Generate a signed URL for deleting a task using the task's ID and delete token.
     *
     * @return string
     */
    public function getDeleteUrlAttribute(): string
    {
        return URL::signedRoute(
            'tasks.delete',
            ['id' => $this->id, 'token' => $this->delete_token]
        );
    }
}
