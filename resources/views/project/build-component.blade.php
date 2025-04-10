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
            @if($build->file_path !== null)
                <div>
                    <a href="{{ route('build.download', $build->id) }}" class="btn btn-primary">Download</a>
                </div>
            @endif
        </x-moonshine::layout.flex>
    </x-moonshine::box>
    <x-moonshine::layout.divider/>
@endif
