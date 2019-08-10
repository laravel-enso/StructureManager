<?php

namespace LaravelEnso\Cli\app\Services;

use Illuminate\Support\Str;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Cli\app\Writers\FormWriter;
use LaravelEnso\Cli\app\Writers\TableWriter;
use LaravelEnso\Cli\app\Writers\ViewsWriter;
use LaravelEnso\Cli\app\Writers\RoutesWriter;
use LaravelEnso\Cli\app\Writers\OptionsWriter;
use LaravelEnso\Cli\app\Writers\PackageWriter;
use LaravelEnso\Cli\app\Writers\ValidatorWriter;
use LaravelEnso\Cli\app\Writers\ModelAndMigrationWriter;
use LaravelEnso\Cli\app\Writers\StructureMigrationWriter;

class Structure
{
    private $choices;
    private $params;

    public function __construct(Obj $choices, Obj $params)
    {
        $this->choices = $choices;
        $this->params = $params;
        $this->prepareModel();
    }

    public function handle()
    {
        if (optional($this->choices->get('package'))->get('name')) {
            $this->params->set('root', $this->packageRoot());
            $this->params->set('namespace', $this->packageNamespace());
            $this->choices->get('model')->set('namespace', $this->modelNamespace());
            $this->writePackage();
        }

        $this->writeStructure();

        if (! $this->choices->has('files')) {
            return;
        }

        $this->writeModelAndMigration()
            ->writeRoutes()
            ->writeViews()
            ->writeForm()
            ->writeTable()
            ->writeOptions();
    }

    private function writeStructure()
    {
        (new StructureMigrationWriter(
            $this->choices, $this->params
        ))->run();

        return $this;
    }

    public function writePackage()
    {
        (new PackageWriter(
            $this->choices, $this->params
        ))->run();

        return $this;
    }

    private function writeModelAndMigration()
    {
        if ($this->choices->get('files')->has('model')
            || $this->choices->get('files')->has('table migration')) {
            (new ModelAndMigrationWriter(
                $this->choices, $this->params
            ))->run();
        }

        return $this;
    }

    private function writeRoutes()
    {
        if ($this->choices->get('files')->has('routes')) {
            (new RoutesWriter(
                $this->choices, $this->params
            ))->run();
        }

        return $this;
    }

    private function writeViews()
    {
        if ($this->choices->get('files')->has('views')) {
            (new ViewsWriter(
                $this->choices, $this->params
            ))->run();
        }

        return $this;
    }

    private function writeForm()
    {
        if ($this->choices->get('files')->has('form')) {
            (new FormWriter(
                $this->choices, $this->params
            ))->run();

            (new ValidatorWriter(
                $this->choices, $this->params
            ))->run();
        }

        return $this;
    }

    private function writeTable()
    {
        if ($this->choices->get('files')->has('table')) {
            (new TableWriter(
                $this->choices, $this->params
            ))->run();
        }

        return $this;
    }

    private function writeOptions()
    {
        if ($this->choices->get('files')->has('options')) {
            (new OptionsWriter(
                $this->choices, $this->params
            ))->run();
        }

        return $this;
    }

    private function prepareModel()
    {
        $model = $this->choices->get('model');

        if (! Str::contains($model->get('name'), '\\\\')) {
            $model->set('namespace', 'App');

            return;
        }

        $segments = collect(explode('\\\\', $model->get('name')));
        $model->set('name', $segments->pop());
        $model->set('namespace', $segments->implode('\\'));
        $model->set('path', $segments->implode(DIRECTORY_SEPARATOR));
    }

    private function packageNamespace()
    {
        return collect(explode(DIRECTORY_SEPARATOR, $this->packageRoot().'app'.DIRECTORY_SEPARATOR))
            ->reduce(function ($namespace, $word) {
                if (collect(['src', 'vendor'])->contains($word)) {
                    return $namespace;
                }

                if ($word === 'app') {
                    return $namespace->push($word);
                }

                return $namespace->push(ucfirst(Str::camel($word)));
            }, collect())->implode('\\');
    }

    private function packageRoot()
    {
        return 'vendor'.DIRECTORY_SEPARATOR
        .Str::kebab($this->choices->get('package')->get('vendor'))
        .DIRECTORY_SEPARATOR
        .Str::kebab($this->choices->get('package')->get('name'))
        .DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR;
    }

    private function modelNamespace()
    {
        return $this->packageNamespace().'Models';
    }
}