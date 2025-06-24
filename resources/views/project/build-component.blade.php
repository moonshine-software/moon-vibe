@props([
    'build' => null,
    'buildPercent' => 0,
    'status' => '',
])

@if($build)
    <x-moonshine::layout.box>
        <x-moonshine::layout.flex :justifyAlign="'start'">
            <div>{{  __('app.build.component_title') }}</div>
            <div style="width: 88%">
                <x-moonshine::progress-bar
                    color="gray"
                    style="height: 2rem"
                    :value="$buildPercent"
                >
                    {{ $status }}: {{ $buildPercent }}%
                </x-moonshine::progress-bar>
            </div>
            @if($build->status_id === \App\Enums\BuildStatus::FOR_DOWNLOAD)
                <div>
                    <a href="{{ route('build.download', $build->id) }}" class="btn btn-primary">Download</a>
                </div>
            @endif
            @if($build->status_id === \App\Enums\BuildStatus::FOR_TEST)
                <div>
                    <a href="{{ config('app.url') }}/generate/" target="_blank" class="btn btn-primary">Test</a>
                </div>
            @endif
            @if($build->status_id === \App\Enums\BuildStatus::ERROR)
                <div>
                    <button class="btn btn-error" style="cursor: inherit" disabled>{{ $build->errors ?? 'Error' }}</button>
                </div>
            @endif
        </x-moonshine::layout.flex>
    </x-moonshine::box>
    <x-moonshine::layout.divider/>
@endif
