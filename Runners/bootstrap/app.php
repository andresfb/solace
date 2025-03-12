<?php

use App\Console\Commands\TaskRunnerCommand;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function (): void {

        Schedule::command(TaskRunnerCommand::class, ['h'])->hourlyAt(05);

        Schedule::command(TaskRunnerCommand::class, ['eth'])->everyTwoHours(10);

        // TODO: for task using Ollama AI (MacStudio machine) we can only run it when the machine is on, so we use this cron value: 45 12-23 * * 1-5 (from 12:45 noon to 11:45 pm monday through friday

    })->create();
