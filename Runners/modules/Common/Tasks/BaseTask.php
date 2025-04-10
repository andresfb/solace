<?php

declare(strict_types=1);

namespace Modules\Common\Tasks;

use Modules\Common\Dtos\ModuleSettingsInfo;
use Modules\Common\Enum\TaskRunnerSchedule;
use Modules\Common\Interfaces\TaskInterface;
use Modules\Common\Interfaces\TaskServiceInterface;
use Modules\Common\Services\ModuleSettingsService;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;

abstract class BaseTask implements TaskInterface
{
    use Screenable;
    use SendToQueue;

    public function __construct(
        protected readonly TaskServiceInterface $taskTaskService,
        protected readonly ModuleSettingsService $settingsService
    ) {}

    abstract protected function getModuleName(): string;

    abstract protected function getTaskName(): string;

    abstract protected function dispatchEvent(): void;

    public function execute(): void
    {
        if (! $this->isEnabled()) {
            $this->warning('The '.__CLASS__.' is disabled.');

            return;
        }

        if ($this->queueable) {
            $this->dispatchEvent();

            return;
        }

        $this->line('Running '.$this->taskTaskService::class);

        $this->taskTaskService->setToScreen($this->toScreen)
            ->setQueueable($this->queueable)
            ->execute();
    }

    public function runSchedule(): TaskRunnerSchedule
    {
        $settings = $this->settingsService->getSetting(
            $this->getModuleSettings(['schedule'])
        );

        return TaskRunnerSchedule::from($settings->response['schedule']);
    }

    private function isEnabled(): bool
    {
        $this->line('Checking if the task is enabled...');

        $settings = $this->settingsService->getSetting(
            $this->getModuleSettings([
                'is_enabled',
                'empty_runs_count',
            ])
        );

        $isEnabled = (bool) $settings->response['is_enabled'];
        if (! $isEnabled) {
            $this->line('Not enabled.');

            return false;
        }

        $emptyRunsCount = (int) $settings->response['empty_runs_count'];

        $isEnabled = $emptyRunsCount < config($this->getTaskName().'.max_empty_runs');
        if (! $isEnabled) {
            $this->settingsService->disableTask(
                $this->getModuleSettings(['is_enabled'])
            );
        }

        $this->line('Enabled.');

        return $isEnabled;
    }

    /**
     * @param  array<string>  $settingName
     */
    private function getModuleSettings(array $settingName): ModuleSettingsInfo
    {
        $this->line('Getting module settings...');

        return ModuleSettingsInfo::from([
            'moduleName' => $this->getModuleName(),
            'taskName' => $this->getTaskName(),
            'settingNames' => $settingName,
            'response' => [],
        ]);
    }
}
