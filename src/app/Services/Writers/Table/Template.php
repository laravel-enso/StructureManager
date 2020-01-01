<?php

namespace LaravelEnso\Cli\App\Services\Writers\Table;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LaravelEnso\Cli\App\Contracts\StubProvider;
use LaravelEnso\Cli\App\Services\Choices;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Directory;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Stub;
use LaravelEnso\Helpers\App\Classes\Obj;

class Template implements StubProvider
{
    private Obj $model;
    private string $group;

    public function __construct(Choices $choices)
    {
        $this->model = $choices->get('model');
        $this->group = $choices->get('permissionGroup')->get('name');
    }

    public function prepare(): void
    {
        Directory::prepare($this->path());
    }

    public function filePath(): string
    {
        $name = Str::camel(Str::plural($this->model->get('name')));

        return $this->path("{$name}.json");
    }

    public function fromTo(): array
    {
        $model = $this->model->get('name');
        $name = (new Collection(explode('_', Str::snake($model))))->implode(' ');

        return [
            '${permissionGroup}' => $this->group,
            '${name}' => Str::title($name),
            '${table}' => Str::snake(Str::plural($model)),
        ];
    }

    public function stub(): string
    {
        return Stub::get('template');
    }

    private function path(?string $filename = null): string
    {
        return Path::get(['app', 'Tables', 'Templates'], $filename);
    }
}
