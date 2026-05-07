<?php

declare(strict_types=1);

namespace Presentation\Api\Controller;

use App\Dto\ProjectDto;
use Action\Project\CreateProjectAction;
use Presentation\Api\Request\StoreProjectRequest;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    public function store(StoreProjectRequest $request, CreateProjectAction $action): JsonResponse
    {
        $data = ProjectDto::fromRequest($request);
        $project = $action->execute($data, $request->user());

        return response()->json(['message' => 'Project created successfully', 'project' => $project], 201);
    }
}
