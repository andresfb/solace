<?php

use App\Console\Commands\TaskRunnerCommand;
use App\Jobs\RiddlesCollectorJob;
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

        Schedule::command(TaskRunnerCommand::class, ['h'])->hourly();

        Schedule::command(TaskRunnerCommand::class, ['h'])->hourlyAt(05);

        Schedule::command(TaskRunnerCommand::class, ['odd'])->everyOddHour(10);

        Schedule::command(TaskRunnerCommand::class, ['eth'])->everyTwoHours(15);

        Schedule::command(TaskRunnerCommand::class, ['td'])->twiceDaily(3, 15);

        // Three Times a day
        Schedule::command(TaskRunnerCommand::class, ['ttd'])->cron('20 8,14,20 * * *');

        // Every two hours from 11:45 am to 11:45 pm Weekdays
        Schedule::command(TaskRunnerCommand::class, ['cm'])->cron('25 11-23/2 * * 1-5');

        // Every two hours from 12:30 pm to 11:30 pm Weekends
        Schedule::command(TaskRunnerCommand::class, ['cm'])->cron('30 12-23/2 * * 6-7');

        // Three times a week Mon, Wed, Fri at 2:43 am
        Schedule::job(app(RiddlesCollectorJob::class), 'riddles')->cron('43 2 * * 1,3,5');

        // Three times a week Tue, Thu, Sat at 7:35 am
        Schedule::command(TaskRunnerCommand::class, ['ttw'])->cron('35 7 * * 2,4,6');

        // Week days at 7 am
        Schedule::command(TaskRunnerCommand::class, ['od'])->weekdays()->at('7:00');

    })->create();
