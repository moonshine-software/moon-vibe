<?php

namespace App\Jobs;

use App\Enums\BuildStatus;
use App\Exceptions\BuildException;
use App\Models\Build;
use App\Models\MoonShineUser;
use App\Models\ProjectSchema;
use App\MoonShine\Components\ProjectBuildComponent;
use App\Services\MakeAdmin\MakeAdmin;
use App\Services\SchemaValidator;
use App\Support\ChangeLocale;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use MoonShine\Rush\Services\Rush;
use Throwable;

class ProcessTestBuildJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public function __construct(
        protected int $schemaId,
        protected int $userId,
        protected string $lang
    ) {

    }

    /**
     * @throws BuildException
     */
    public function handle(ChangeLocale $changeLocale): void
    {
        $user = MoonShineUser::query()->where('id', $this->userId)->first();
        if($user === null) {
            throw new BuildException('User not found');
        }

        $buildRepository = (string) $user->getBuildSetting('repository', '');
        if($buildRepository === '') {
            throw new BuildException('The settings do not indicate a repository for creating an admin panel');
        }

        $changeLocale->set($this->lang, isSetCookie: false);

        $projectSchema = ProjectSchema::query()->where('id', $this->schemaId)->first();

        if ($projectSchema === null) {
            throw new BuildException('Schema not found');
        }

        Build::query()->where('moonshine_user_id', $this->userId)->delete();

        $build = Build::create([
            'project_schema_id' => $projectSchema->id,
            'moonshine_user_id' => $this->userId,
            'status_id' => BuildStatus::IN_PROGRESS,
        ]);

        Rush::events()->htmlReload(
            '#build-component-' . $projectSchema->project->id,
            (string) ProjectBuildComponent::fromBuild($build)
        );

        try {
            $projectSchema = $build->projectSchema;

            $errors = (new SchemaValidator())->validate($projectSchema->schema);
            if($errors !== '') {
                $build->update([
                    'status_id' => BuildStatus::ERROR,
                    'errors' => $errors
                ]);
                return;
            }

            $makeAdmin = new MakeAdmin(
                $projectSchema->schema,
                Storage::disk('local')->path('builds/' . $build->moonshine_user_id),
                alertFunction: function(string $alert, int $percent) use ($build): void {
                    Rush::events()->htmlReload(
                        '#build-component-' . $build->projectSchema->project->id,
                        (string) ProjectBuildComponent::fromBuild($build, $percent, $alert)
                    );
                },
                appProjectDirectory: base_path('generate'),
            );

            try {
                $path = $makeAdmin->handleForTest($buildRepository);
                $build->update([
                    'status_id' => BuildStatus::FOR_TEST,
                    'file_path' => $path
                ]);
            } catch (Throwable $e) {
                $build->update([
                    'status_id' => BuildStatus::ERROR,
                    'errors' => 'Server error',
                ]);
                report($e);
            }

            $build->refresh();

            Rush::events()->htmlReload(
                '#build-component-' . $projectSchema->project->id,
                (string) ProjectBuildComponent::fromBuild($build)
            );
        } catch (Exception $e) {
            Log::error('Build error: ' . $e->getMessage(), [
                'build_id' => $build->id,
                'exception' => $e
            ]);
            
            $build->update([
                'status_id' => BuildStatus::ERROR,
                'errors' => $e->getMessage()
            ]);
        }
    }

    public function uniqueId(): int
    {
        return $this->userId;
    }
} 