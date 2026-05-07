<?php

declare(strict_types=1);

namespace Domain\Project\ValueObject;

enum ProjectRole: string
{
    case OWNER = 'owner';
    case MEMBER = 'member';
}
