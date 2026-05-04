<div
        x-data="wysiwygEditor({
        content: @entangle('content'),
        toolbar: {{ json_encode($toolbar) }},
        height: '{{ $height }}',
        placeholder: '{{ $placeholder }}',
        formatOptions: {{ json_encode($formatOptions) }},
        colors: @js([
            '#000000', '#444444', '#666666', '#999999',
            '#ff0000', '#ff9900', '#ffff00', '#00ff00',
            '#00ffff', '#0000ff', '#9900ff', '#ff00ff'
        ])
    })"
        x-init="init()"
        wire:ignore
>
    <div class="wysiwyg-wrapper">
        <div class="wysiwyg-toolbar" x-ref="toolbar">
            <!-- Sélecteur de format -->
            <div x-show="formatOptions.length" class="toolbar-group">
                <select x-model="selectedFormat" @change="applyFormat(selectedFormat)">
                    <template x-for="option in formatOptions" :key="option.value">
                        <option :value="option.value" x-text="option.label"></option>
                    </template>
                </select>
            </div>
            @if(!empty($options))
                <div class="toolbar-group" x-data="{ open: false }" style="position: relative;">

                    <!-- Bouton -->
                    <button type="button" @click="open = !open" title="Insérer un tag dynamique">
                        <i class="fa-regular fa-brackets-curly"></i>
                    </button>

                    <!-- Dropdown -->
                    <div
                            x-show="open"
                            @click.outside="open = false"
                            x-transition
                            style="
                            position: absolute;
                            top: 100%;
                            left: 0;
                            background: white;
                            border: 1px solid #ddd;
                            border-radius: 8px;
                            padding: 6px;
                            max-width: 500px;
                            min-width: 300px;
                            max-height: 250px;
                            overflow-y: auto;
                            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
                            z-index: 9999;
                        "
                    >
                        @foreach($options as $option)
                            <button type="button" @click="insertTag('{{ $option['value'] }}');open = false;"
                                    class="inline-flex items-center w-full justify-between">
                                <span class="text-xs text-left">{{ $option['label'] }}</span>
                                <span class="text-blue-600 text-sm">{{ $option['value'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Couleurs -->
            <div class="toolbar-group" x-data="{ open: false }" style="position: relative;">

                <button type="button" @click="open = !open" title="Couleur du texte">
                    <i class="fa-regular fa-palette"></i>
                </button>

                <div
                        x-show="open"
                        x-transition
                        @click.outside="open = false"
                        class="color-dropdown"
                        style="
            position: absolute;
            top: 100%;
            left: 0;
            width: 150px;
            background: white;
            border: 1px solid #ccc;
            padding: 8px;
            grid-template-columns: repeat(6, 1fr);
            gap: 6px;
            z-index: 9999;
        "
                >
                    <template x-for="color in colors" :key="color">
                        <button
                                type="button"
                                :style="'background-color:' + color"
                                @click="setColor(color); open = false"
                                style="
                    width: 20px;
                    height: 20px;
                    cursor: pointer;
                    border: 1px solid #ddd;
                "
                        ></button>
                    </template>
                </div>

            </div>

            <!-- Fin de couleur -->

            <!-- Boutons dynamiques -->
            <template x-for="group in toolbar">
                <div class="toolbar-group">
                    <template x-for="tool in group">
                        <button
                                type="button"
                                @click="executeCommand(tool)"
                                :class="{'active': isActive(tool)}"
                                x-html="getIcon(tool)"
                                :title="getTooltip(tool)"
                        ></button>
                    </template>
                </div>
            </template>

        </div>
        <div
                class="wysiwyg-editor"
                contenteditable="true"
                x-ref="editor"
                @input="updateContent"
                @keydown="handleKeydown"
                @paste="handlePaste"
                :style="`height: ${height}; overflow-y: auto;`"
                :data-placeholder="placeholder"
        ></div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('wysiwygEditor', (config) => ({
                content: config.content,
                toolbar: config.toolbar,
                height: config.height,
                placeholder: config.placeholder,
                formatOptions: config.formatOptions,   // récupération des options de format
                selectedFormat: 'p',                    // valeur par défaut
                editor: null,
                colors: config.colors || [],

                init() {
                    this.editor = this.$refs.editor;
                    // Initialiser le contenu
                    this.editor.innerHTML = this.content;
                    // Focus sur l'éditeur au clic sur la barre d'outils
                    this.$refs.toolbar.addEventListener('click', (e) => {
                        if (e.target.tagName === 'BUTTON') {
                            this.editor.focus();
                        }
                    });

                    this.editor.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            document.execCommand('insertParagraph', false, null);
                        }
                    });

                    // Mettre à jour le sélecteur de format lors des interactions
                    this.editor.addEventListener('mouseup', () => this.updateFormatSelection());
                    this.editor.addEventListener('keyup', () => this.updateFormatSelection());
                    this.editor.addEventListener('click', () => this.updateFormatSelection());

                    // Initialisation
                    this.updateFormatSelection();
                },

                updateContent() {
                    this.content = this.editor.innerHTML;
                    if (this.$wire) {
                        this.$wire.set('content', this.content);
                    }
                },

                executeCommand(command) {
                    switch (command) {
                        case 'bold':
                            document.execCommand('bold', false, null);
                            break;
                        case 'italic':
                            document.execCommand('italic', false, null);
                            break;
                        case 'underline':
                            document.execCommand('underline', false, null);
                            break;
                        case 'unorderedList':
                            document.execCommand('insertUnorderedList', false, null);
                            break;
                        case 'orderedList':
                            document.execCommand('insertOrderedList', false, null);
                            break;
                        case 'indent':
                            document.execCommand('indent', false, null);
                            break;
                        case 'outdent':
                            document.execCommand('outdent', false, null);
                            break;
                        case 'strikethrough':
                            document.execCommand('strikeThrough', false, null);
                            break;
                        case 'subscript':
                            document.execCommand('subscript', false, null);
                            break;
                        case 'superscript':
                            document.execCommand('superscript', false, null);
                            break;
                        case 'link':
                            let url = prompt('URL du lien :', 'https://');
                            if (url) document.execCommand('createLink', false, url);
                            break;
                        case 'unlink':
                            document.execCommand('unlink', false, null);
                            break;
                        case 'image':
                            let imgUrl = prompt('URL de l\'image :', 'https://');
                            if (imgUrl) {
                                let img = `<img src="${imgUrl}" style="max-width:100%;">`;
                                document.execCommand('insertHTML', false, img);
                            }
                            break;
                        default:
                            console.warn('Commande inconnue :', command);
                    }
                    this.editor.focus();
                    this.updateContent();
                },

                isActive(command) {
                    return document.queryCommandState(command);
                },

                getIcon(tool) {
                    const icons = {
                        bold: '<i class="fa-regular fa-bold"></i>',
                        italic: '<i class="fa-regular fa-italic"></i>',
                        underline: '<i class="fa-regular fa-underline"></i>',
                        unorderedList: '<i class="fa-regular fa-list"></i>',
                        orderedList: '<i class="fa-regular fa-list-ol"></i>',
                        indent: '<i class="fa-regular fa-indent"></i>',
                        outdent: '<i class="fa-regular fa-outdent"></i>',
                        subscript: '<i class="fa-regular fa-subscript"></i>',
                        superscript: '<i class="fa-regular fa-superscript"></i>',
                        strikethrough: '<i class="fa-regular fa-strikethrough"></i>',
                        link: '<i class="fa-regular fa-link"></i>',
                        unlink: '<i class="fa-regular fa-link-slash"></i>',
                        image: '<i class="fa-regular fa-file-image"></i>',
                    };
                    return icons[tool] || tool;
                },

                getTooltip(tool) {
                    const tips = {
                        bold: 'Gras',
                        italic: 'Italique',
                        underline: 'Souligné',
                        unorderedList: 'Liste à puces',
                        orderedList: 'Liste numérotée',
                        indent: 'Décaler sur la droite',
                        outdent: 'Décaler sur la gauche',
                        subscript: 'Indice',
                        superscript: 'Exposant',
                        strikethrough: 'Barré',
                        link: 'Ajouter un lien',
                        unlink: 'Supprimer le lien',
                        image: 'Insérer une image',
                    };
                    return tips[tool] || tool;
                },

                applyFormat(format) {
                    if (!format) return;
                    // Appliquer le format de bloc
                    document.execCommand('formatBlock', false, `<${format}>`);
                    this.updateFormatSelection();
                    this.editor.focus();
                    this.updateContent();
                },

                updateFormatSelection() {
                    const selection = window.getSelection();
                    if (selection.rangeCount === 0) return;
                    const range = selection.getRangeAt(0);
                    let node = range.commonAncestorContainer;
                    while (node && node !== this.editor) {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            const tag = node.tagName.toLowerCase();
                            if (['p', 'h1', 'h2', 'h3', 'pre'].includes(tag)) {
                                this.selectedFormat = tag;
                                return;
                            }
                        }
                        node = node.parentNode;
                    }
                    this.selectedFormat = 'p';
                },

                insertTag(tag) {
                    if (!tag) return;

                    this.editor.focus();

                    document.execCommand('insertText', false, tag);

                    this.updateContent();
                },

                setColor(color) {
                    this.editor.focus();
                    document.execCommand('foreColor', false, color);
                    this.updateContent();
                },


                handleKeydown(e) {
                    if (e.ctrlKey || e.metaKey) {
                        switch (e.key) {
                            case 'b':
                                e.preventDefault();
                                this.executeCommand('bold');
                                break;
                            case 'i':
                                e.preventDefault();
                                this.executeCommand('italic');
                                break;
                            case 'u':
                                e.preventDefault();
                                this.executeCommand('underline');
                                break;
                        }
                    }
                },

                handlePaste(e) {
                    e.preventDefault();
                    let text = (e.originalEvent || e).clipboardData.getData('text/plain');
                    document.execCommand('insertText', false, text);
                }
            }));
        });
    </script>
@endpush

@push('styles')
    <link href="{{ asset('vendor/hivedrops/editor/css/editor.css') }}" rel="stylesheet">
@endpush
