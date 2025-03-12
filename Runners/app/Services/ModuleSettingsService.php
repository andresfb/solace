<?php

namespace App\Services;

use Modules\Common\Dtos\ModuleSettingsInfo;
use Modules\Common\Models\ModuleSetting;

class ModuleSettingsService
{
    public function getSetting(ModuleSettingsInfo $request): ModuleSettingsInfo
    {
        $setting = ModuleSetting::select(['name', 'value'])
            ->where('module_name', $request->moduleName)
            ->where('task_name', $request->taskName)
            ->where('name', $request->settingNames[0])
            ->firstOrFail();

        $request->response[$setting->name] = $setting->value;

        return $request;
    }

    public function getSettings(ModuleSettingsInfo $request): ModuleSettingsInfo
    {
        $settings = ModuleSetting::select(['name', 'value'])
            ->where('module_name', $request->moduleName)
            ->where('task_name', $request->taskName)
            ->whereIn('name', $request->settingNames)
            ->get();

        if ($settings->isEmpty()) {
            throw new \RuntimeException('No settings found');
        }

        foreach ($settings as $setting) {
            $request->response[$setting->name] = $setting->value;
        }

        return $request;
    }

    public function updateSetting(ModuleSettingsInfo $request): void
    {
        $settingName = $request->settingNames[0];

        if ($request->action === 'increment') {
            $setting = $this->getSetting($request);
            $updateValue = (int) $setting->response[$settingName]++;
        } else {
            $updateValue = $request->response[$settingName];
        }

        ModuleSetting::where('module_name', $request->moduleName)
            ->where('task_name', $request->taskName)
            ->where('name', $settingName)
            ->update(['value' => $updateValue]);
    }
}
