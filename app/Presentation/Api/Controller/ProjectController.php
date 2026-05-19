<?php

declare(strict_types=1);

namespace Presentation\Api\Controller;

use Action\Project\DeleteProjectAction;
use Action\Project\InviteUserToProjectAction;
use App\Dto\Project\DeleteProjectDto;
use App\Dto\Project\InviteUserToProjectDto;
use App\Dto\Project\ProjectDto;
use Action\Project\CreateProjectAction;
use Presentation\Api\Request\Project\InviteUserRequest;
use Presentation\Api\Request\Project\StoreProjectRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ProjectController extends Controller
{
    public function store(StoreProjectRequest $request, CreateProjectAction $action): JsonResponse
    {
        $data = ProjectDto::fromRequest($request);
        $project = $action->execute($data, $request->user());

        return response()->json(['message' => 'Project created successfully', 'project' => $project], Response::HTTP_CREATED);
    }

    public function invite(string $id, InviteUserRequest $request, InviteUserToProjectAction $action): JsonResponse
    {
        $data = InviteUserToProjectDto::fromReqeust($request, $id);
        $action->execute($data);

        return response()->json(['message' => 'User invited to project']);
    }

    public function destroy(string $id, DeleteProjectAction $action): JsonResponse
    {
        $dto = DeleteProjectDto::fromId($id);
        $action->execute($dto);

        return response()->json(status: Response::HTTP_NO_CONTENT);
    }
}
