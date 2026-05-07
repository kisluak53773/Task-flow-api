<?php

declare(strict_types=1);

namespace Domain\Project\Model;

use Domain\User\Model\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Domain\Project\ValueObject\TaskStatus;
use Domain\Project\ValueObject\TaskPriority;

class Task extends Model
{
    protected $fillable = [
        'title',
        'description',
        'due_date',
        'project_id',
        'user_id',
        'priority',
        'status',
    ];

    protected $casts = [
        'due_date' => 'date',
        'priority' => TaskPriority::class,
        'status' => TaskStatus::class,
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
