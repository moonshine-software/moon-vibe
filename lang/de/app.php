<?php

return [
    'schema' => [
        'generation' => 'Generierung',
        'success' => 'Erfolgreich',
        'error' => 'Fehler',
        'server_error' => 'Serverfehler',
        'saving' => 'Speichern',
        'correction' => 'Schemakorrektur',
        'json_schema' => 'JSON-Schema',
        'preview' => 'Schema',
        'status' => 'Status',
        'project' => 'Projekt',
        'generate_job' => 'Anfrage...',
        'generate_job_attempt' => 'Anfrage, Versuch :tries...',
        'correct_job' => 'Schemakorrektur...',
        'correct_job_attempt' => 'Schemakorrektur, Versuch :tries...',
        'already_pending' => 'Sie haben bereits ein ausstehendes Schema. Bitte warten Sie, bis es generiert wurde.',
        'schema_not_found' => 'Schema nicht gefunden.',
    ],
    'prompt_resource' => [
        'title'  => 'Titel',
        'prompt' => 'Prompt',
        'order'  => 'Reihenfolge',
    ],
    'menu' => [
        'generation'    => 'Generierung',
        'projects'      => 'Projekte',
        'settings'      => 'Einstellungen',
        'about'         => 'Über',
        'llm'           => 'LLM',
        'prompts'       => 'Prompts',
        'subscriptions' => 'Abonnements',
    ],
    'settings' => [
        'profile'       => 'Profil',
        'generation'    => 'Generierung',
        'max_attempts'  => 'Maximale Anzahl der Generierungsversuche',
        'repository'    => 'Basis-Repository',
        'deployment'    => 'Deployment',
        'language'      => 'Sprache',
    ],
    'dashboard' => [
        'project_name'       => 'Projektname',
        'prompt'             => 'Anfrage',
        'submit'             => 'Schema-Generierung starten',
        'prompt_placeholder' => 'Beschreiben Sie Ihr Projekt...',
        'types'              => 'Typen',
        'fields'             => 'Felder',
        'templates'          => 'Vorlagen',
    ],
    'project' => [
        'schemas'         => 'Schemata',
        'download'        => 'Projekt herunterladen',
        'download_confirm'=> 'Projektaufbau starten, um es herunterzuladen?',
        'test'            => 'Projekt testen',
        'test_confirm'    => 'Projektaufbau starten, um es zu testen?',
        'correct'         => 'Korrigieren',
        'correction'      => 'Schemakorrektur',
        'prompt'          => 'Prompt',
    ],
    'build' => [
        'cloning_repository'       => 'Repository klonen',
        'installing_dependencies'  => 'Abhängigkeiten installieren',
        'installing_moonshine_builder' => 'moonshine-builder installieren',
        'installing_markdown'      => 'Markdown installieren',
        'installing_tinymce'       => 'TinyMCE installieren',
        'publishing_moonshine_builder' => 'moonshine-builder veröffentlichen',
        'creating_builds_directory'=> 'builds-Verzeichnis erstellen',
        'copying_file'             => 'Datei kopieren',
        'building_administrator'   => 'Administrator bauen',
        'optimization'             => 'Optimierung',
        'removing_vendor_directory'=> 'vendor-Verzeichnis entfernen',
        'archiving_directory'      => 'Verzeichnis archivieren',
        'removing_directory'       => 'Verzeichnis entfernen',
        'component_title'          => 'Projektaufbau',
        'copy_env'                 => '.env kopieren',
        'env_settings'             => '.env-Werte setzen',
        'migrate_fresh'            => 'Frische Testdatenbank',
        'chmod'                    => 'Berechtigungen setzen',
        'status' => [
            'in_progress' => 'In Bearbeitung',
            'error'       => 'Fehler',
            'for_download'=> 'Bereit zum Herunterladen',
            'for_test'    => 'Bereit zum Testen',
        ],
    ],
    'auth' => [
        'password'         => 'Passwort',
        'remember_me'      => 'Angemeldet bleiben',
        'log_in'           => 'Einloggen',
        'create_account'   => 'Konto erstellen',
        'forgot_password'  => 'Passwort vergessen',
        'name'             => 'Name',
        'reset_password'   => 'Passwort zurücksetzen',
        'repeat_password'  => 'Passwort wiederholen',
    ],

    'generations_left'            => 'Verbleibende Generationen: :generations',
    'generations_limit_exceeded'  => 'Sie haben das Generierungslimit überschritten.',
    'subscription_expired'        => 'Ihr Abonnement ist abgelaufen.',
    'subscription_plan_not_found' => 'Abonnementplan nicht gefunden.',
];
