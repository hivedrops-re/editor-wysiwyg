<?php

namespace Hivedrops\Editor\Http\Livewire;

use Livewire\Component;

class Editor extends Component
{
    public $content = '';
    public $toolbar;
    public $height;
    public $placeholder;
    public $formatOptions;

    public $options = [];
    public $eventName = 'contentUpdated';

    protected $rules = [
        'content' => 'nullable|string',
    ];

    public function mount(
        $content = '',
        $toolbar = null,
        $height = null,
        $placeholder = null,
        $formatOptions = null,
        $options = [],
        $eventName = 'contentUpdated'
    )
    {
        $this->content = $content;
        $this->toolbar = $toolbar ?? config('wysiwyg.toolbar');
        $this->height = $height ?? config('wysiwyg.height');
        $this->placeholder = $placeholder ?? config('wysiwyg.placeholder');
        $this->formatOptions = $formatOptions ?? config('wysiwyg.format_options', [
            ['value' => 'p', 'label' => 'Paragraphe'],
            ['value' => 'h1', 'label' => 'Titre 1'],
            ['value' => 'h2', 'label' => 'Titre 2'],
            ['value' => 'h3', 'label' => 'Titre 3'],
            ['value' => 'pre', 'label' => 'Bloc de code'],
        ]);

        $this->eventName = $eventName;

        $this->options = collect($options ?? [])
            ->map(function ($value, $key) {
                return [
                    'key' => $key,
                    'tag' => '{' . $key . '}',
                    'label' => ucfirst(str_replace('_', ' ', $key)),
                    'value' => $value,
                ];
            })
            ->values()
            ->toArray();
    }

    public function updatedContent($value)
    {
        $this->dispatch($this->eventName, $value);
    }

    // ✅ Fonction pour remplacer les tags
    public function renderContent()
    {
        $content = $this->content;

        foreach ($this->options as $key => $value) {
            $content = str_replace('{' . $key . '}', $value, $content);
        }

        return $content;
    }

    public function render()
    {
        return view('editor::livewire.editor');
    }
}