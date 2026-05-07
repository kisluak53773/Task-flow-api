<?php

declare(strict_types=1);

namespace Domain\Project\ValueObject;

enum TaskPriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
}
