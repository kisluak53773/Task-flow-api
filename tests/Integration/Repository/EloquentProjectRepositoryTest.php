<?php

declare(strict_types=1);

namespace Tests\Integration\Repository;

use Domain\Project\Model\Project;
use Domain\Project\ValueObject\ProjectRole;
use Domain\User\Model\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Infrastructure\Persistence\Eloquent\EloquentProjectRepository;
use Tests\TestCase;

class EloquentProjectRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentProjectRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentProjectRepository();
    }

    public function test_create_with_member_persists_project_and_attaches_user(): void
    {
        $user = User::factory()->create();

        $project = $this->repository->createWithMember(
            data: ['name' => 'Test Project', 'description' => 'A test'],
            user: $user,
            role: ProjectRole::OWNER->value,
        );

        $this->assertInstanceOf(Project::class, $project);
        $this->assertDatabaseHas('projects', ['name' => 'Test Project']);
        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id'    => $user->id,
            'role'       => ProjectRole::OWNER->value,
        ]);
    }

    public function test_create_with_member_returns_project_with_id(): void
    {
        $user = User::factory()->create();

        $project = $this->repository->createWithMember(
            data: ['name' => 'New Project', 'description' => null],
            user: $user,
            role: ProjectRole::MEMBER->value,
        );

        $this->assertNotNull($project->id);
        $this->assertIsInt($project->id);
    }

    public function test_create_with_member_executes_inside_a_transaction(): void
    {
        $user = User::factory()->create();

        $initialProjectCount = Project::count();

        $project = $this->repository->createWithMember(
            data: ['name' => 'Transactional Project', 'description' => null],
            user: $user,
            role: ProjectRole::OWNER->value,
        );

        $this->assertSame($initialProjectCount + 1, Project::count());
        $this->assertSame(1, $project->users()->count());
    }

    public function test_find_by_id_returns_the_correct_project(): void
    {
        $project = Project::factory()->create(['name' => 'Found Project']);

        $found = $this->repository->findById($project->id);

        $this->assertInstanceOf(Project::class, $found);
        $this->assertSame('Found Project', $found->name);
        $this->assertSame($project->id, $found->id);
    }

    public function test_find_by_id_returns_null_for_non_existent_id(): void
    {
        /** @var \Domain\Project\Model\Project|null $result */
        $result = $this->repository->findById(99999);

        $this->assertNull($result);
    }

    public function test_find_by_id_accepts_string_id(): void
    {
        $project = Project::factory()->create(['name' => 'String ID Project']);

        $found = $this->repository->findById((string) $project->id);

        $this->assertInstanceOf(Project::class, $found);
        $this->assertSame($project->id, $found->id);
    }

    public function test_has_member_returns_true_when_user_belongs_to_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $project->users()->attach($user->id, ['role' => ProjectRole::MEMBER->value]);

        $result = $this->repository->hasMemeBer($project, $user);

        $this->assertTrue($result);
    }

    public function test_has_member_returns_false_when_user_does_not_belong_to_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $result = $this->repository->hasMemeBer($project, $user);

        $this->assertFalse($result);
    }

    public function test_has_member_returns_false_for_user_in_a_different_project(): void
    {
        $user = User::factory()->create();
        $projectA = Project::factory()->create();
        $projectB = Project::factory()->create();

        $projectA->users()->attach($user->id, ['role' => ProjectRole::MEMBER->value]);

        $result = $this->repository->hasMemeBer($projectB, $user);

        $this->assertFalse($result);
    }

    public function test_add_member_attaches_user_to_project_with_given_role(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $this->repository->addMember($project, $user, ProjectRole::MEMBER);

        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id'    => $user->id,
            'role'       => ProjectRole::MEMBER->value,
        ]);
    }

    public function test_add_member_can_attach_with_owner_role(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $this->repository->addMember($project, $user, ProjectRole::OWNER);

        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id'    => $user->id,
            'role'       => ProjectRole::OWNER->value,
        ]);
    }

    public function test_delete_removes_project_from_database(): void
    {
        $project = Project::factory()->create(['name' => 'To Be Deleted']);

        $this->repository->delete($project);

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_delete_also_removes_associated_pivot_rows(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $project->users()->attach($user->id, ['role' => ProjectRole::OWNER->value]);

        $this->assertDatabaseHas('project_user', ['project_id' => $project->id]);

        $this->repository->delete($project);

        $this->assertDatabaseMissing('project_user', ['project_id' => $project->id]);
    }

    public function test_delete_does_not_remove_other_projects(): void
    {
        $projectA = Project::factory()->create(['name' => 'Keep This']);
        $projectB = Project::factory()->create(['name' => 'Delete This']);

        $this->repository->delete($projectB);

        $this->assertDatabaseHas('projects', ['id' => $projectA->id]);
    }
}
