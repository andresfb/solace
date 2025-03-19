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

        Schedule::command(TaskRunnerCommand::class, ['lf'])->everyThirtyMinutes();

        Schedule::command(TaskRunnerCommand::class, ['h'])->hourlyAt(05);

        Schedule::command(TaskRunnerCommand::class, ['odd'])->everyOddHour();

        Schedule::command(TaskRunnerCommand::class, ['eth'])->everyTwoHours(10);

        Schedule::command(TaskRunnerCommand::class, ['td'])->twiceDaily();

        // Every two hours from 11:45 am to 11:45 pm Weekdays
        Schedule::command(TaskRunnerCommand::class, ['cmw'])->cron('45 11-23/2 * * 1-5');

        // Every two hours from 1:30 pm to 11:30 pm Weekends
        Schedule::command(TaskRunnerCommand::class, ['cmd'])->cron('30 13-23/2 * * 6-7');

    })->create();
