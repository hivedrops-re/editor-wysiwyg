<?php

namespace Hivedrops\Editor;

use Hivedrops\Editor\Http\Livewire\Editor;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class EditorServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Charger les vues
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'editor');

        // Publier la configuration
        $this->publishes([
            __DIR__.'/../config/editor.php' => config_path('editor.php'),
        ], 'editor-config');

        // Publier les assets
        $this->publishes([
            __DIR__ . '/../resources/js' => public_path('vendor/hivedrops/editor/js'),
            __DIR__ . '/../resources/css' => public_path('vendor/hivedrops/editor/css'),
        ], 'editor-assets');

        // Enregistrer le composant Livewire
        Livewire::component('editor', Editor::class);
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/editor.php', 'editor');
    }
}