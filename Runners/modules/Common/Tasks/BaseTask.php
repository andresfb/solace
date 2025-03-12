<?php

namespace Modules\Common\Tasks;

use Modules\Common\Dtos\ModelSettings;
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
        protected readonly TaskServiceInterface $taskService,
        protected readonly ModuleSettingsService $settingsService
    ) { }

    abstract protected function getModuleName(): string;

    abstract protected function getTaskName(): string;

    abstract protected function dispatchEvent(): void;

    public function execute(): void
    {
        if (!$this->isEnabled()) {
            $this->warning('The '.self::class.' is disabled.');

            return;
        }

        if ($this->queueable) {
            $this->dispatchEvent();

            return;
        }

        $this->line('Running '.$this->taskService::class);

        $this->taskService->setToScreen($this->toScreen)
            ->setQueueable($this->queueable)
            ->execute();
    }

    public function runSchedule(): TaskRunnerSchedule
    {
        $settings = $this->settingsService->getSetting(
            $this->getModuleSettings(['schedule'])
        );

        return TaskRunnerSchedule::from($settings['schedule']);
    }

    private function isEnabled(): bool
    {
        $settings = $this->settingsService->getSetting(
            $this->getModuleSettings([
                'is_enabled',
                'empty_runs_count',
            ])
        );

        $isEnabled = (bool) $settings->response['is_enabled'];
        if (! $isEnabled) {
            return false;
        }

        $emptyRunsCount = (int) $settings->response['empty_runs_count'];

        return $emptyRunsCount < config($this->getTaskName().'.max_empty_runs');
    }

    private function getModuleSettings(array $settingName): ModelSettings
    {
        $info = [
            'moduleName' => $this->getModuleName(),
            'taskName' => $this->getTaskName(),
            'settingName' => $settingName,
            'response' => [],
        ];

        return ModelSettings::from($info);
    }
}
