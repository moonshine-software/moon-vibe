<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\ChangeLocale;
use MoonShine\Laravel\Models\MoonshineUser as BaseMoonShineUser;

class MoonShineUser extends BaseMoonShineUser
{
    protected $table = 'moonshine_users';

    protected $fillable = [
        'email',
        'moonshine_user_role_id',
        'password',
        'name',
        'avatar',
        'settings',
        'lang'
    ];

    protected $casts = [
        'settings' => 'json'
    ];

    protected static function booted(): void
    {
        static::saving(static function (self $model) {
            if(
                $model->hasAttribute('attempts')
                //&& $model->hasAttribute('repository')
            ) {
                $settings['generation'] = [
                    'attempts' => $model->getAttribute('attempts'),
                ];

                $settings['build'] = [
                    //'repository' => $model->getAttribute('repository')
                    'repository' => 'https://github.com/dev-lnk/moonshine-blank.git'
                ];

                unset($model->attributes['attempts']);
                //unset($model->attributes['repository']);

                $model->settings = $settings;
            }

            ChangeLocale::set((string) $model->getAttribute('lang'));
        });
    }

    public function getGenerationSetting(string $key, mixed $default = null): mixed
    {
        if($this->settings === null) {
            return $default;
        }
        if(! isset($this->settings['generation'][$key])) {
            return $default;
        }
        return $this->settings['generation'][$key];
    }

    public function getBuildSetting(string $key, mixed $default = null): mixed
    {
        if($this->settings === null) {
            return $default;
        }
        if(! isset($this->settings['build'][$key])) {
            return $default;
        }
        return $this->settings['build'][$key];
    }
}