<?php

declare(strict_types=1);

namespace Domain\Project\Model;

use Domain\User\Model\User;
use Domain\Project\ValueObject\ProjectRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = ['name', 'description'];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withCasts(['role' => ProjectRole::class])
            ->withTimestamps();
    }
}
