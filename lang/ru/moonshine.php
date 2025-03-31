<?php

return [
    'schema' => [
        'generation' => 'Генерация...',
        'success' => 'Успешно',
        'error' => 'Ошибка',
        'server_error' => 'Ошибка сервера',
        'saving' => 'сохранение',
        'correction' => 'исправление схемы...',
        'json_schema' => 'Json схема',
        'preview' => 'Схема',
        'status' => 'Статус',
        'project' => 'Проект',
    ],
    'menu' => [
        'generation' => 'Генерация',
        'projects' => 'Проекты',
        'settings' => 'Настройки',
        'about' => 'О проекте',
    ],
    'settings' => [
        'profile' => 'Профиль',
        'generation' => 'Генерация',
        'max_attempts' => 'Максимальное количество попыток генерации',
        'repository' => 'Базовый репозиторий',
        'deployment' => 'Развертывание',
    ],
    'dashboard' => [
        'project_name' => 'Название проекта',
        'prompt' => 'Запрос',
        'submit' => 'Отправить',
        'prompt_placeholder' => 'Опишите ваш проект...',
        'templates' => 'Шаблоны',
        'example-1' => 'Пример 1',
        'example-2' => 'Пример 2',
        'example-3' => 'Пример 3',
    ],
    'about' => [
        'content' => "<h1>Цель.</h1><br>Данное приложение позволяет по описанию проекта сгенерировать схему админ-панели MoonShine и быстро развернуть базовый, рабочий вариант на своем окружении.<br><br><h1>Как работает прилложение.</h1><br>Данный проект позволяет с помощью ИИ создать схему для пакета <a href='https://github.com/dev-lnk/moonshine-builder' class='link'>MoonShineBuilder</a>, который сгенерирует ресурсы, модели и миграции в админ-панели MoonShine.<br><br>После генерации схемы вы можете выполнить построение проекта, который будет упакован в tar архив. В данном архиве будет содержаться проект Laravel, с предустановленным MoonShine и построенными сущностями. За основу проекта берется <a href='https://github.com/dev-lnk/moonshine-blank' class='link'>репозиторий</a> из <a href='/page/settings-page' class='link'>настроек</a>, но вы можете указать свой.",
    ],
    'project' => [
        'schemas' => 'Схемы',
        'create' => 'Создать проект',
        'build_confirm' => 'Выполнить построение проекта?',
        'correct' => 'Исправить',
        'correction' => 'Исправление схемы',
    ],
]; 