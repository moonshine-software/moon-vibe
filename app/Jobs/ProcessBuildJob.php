<?php

namespace App\Jobs;

use App\MoonShine\Components\ProjectBuildComponent;
use App\Support\ChangeLocale;
use Exception;
use App\Models\Build;
use Illuminate\Bus\Queueable;
use App\Support\SchemaValidator;
use Illuminate\Support\Facades\Log;
use App\Services\MakeAdmin\MakeAdmin;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Enums\BuildStatus;
use MoonShine\Laravel\Models\MoonshineUser;
use MoonShine\Rush\Services\Rush;

class ProcessBuildJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Build $build,
        protected string $lang
    ) {

    }

    public function handle(): void
    {
        ChangeLocale::set($this->lang, isSetCookie: false);

        try {
            $projectSchema = $this->build->projectSchema;

            $errors = (new SchemaValidator($projectSchema->schema))->validate();
            if($errors !== '') {
                $this->build->update([
                    'status_id' => BuildStatus::ERROR,
                    'errors' => $errors
                ]);
                return;
            }

            $makeAdmin = new MakeAdmin(
                $projectSchema->schema,
                Storage::disk('local')->path('builds/' . $this->build->moonshine_user_id),
                alertFunction: function(string $alert, int $percent): void {
                    Rush::events()->htmlReload(
                        '#build-component-' . $this->build->projectSchema->project->id,
                        (string) ProjectBuildComponent::fromBuild($this->build, $percent, $alert)
                    );
                }
            );

            $path = $makeAdmin->handle();
            
            $this->build->update([
                'status_id' => BuildStatus::COMPLETED,
                'file_path' => $path
            ]);

            $this->build->refresh();

            Rush::events()->htmlReload(
                '#build-component-' . $projectSchema->project->id,
                (string) ProjectBuildComponent::fromBuild($this->build)
            );
        } catch (Exception $e) {
            Log::error('Build error: ' . $e->getMessage(), [
                'build_id' => $this->build->id,
                'exception' => $e
            ]);
            
            $this->build->update([
                'status_id' => BuildStatus::ERROR,
                'errors' => $e->getMessage()
            ]);
        }
    }
} 