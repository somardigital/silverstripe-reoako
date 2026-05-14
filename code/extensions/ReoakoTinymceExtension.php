<?php

namespace Octavenz\Reoako\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Core\Manifest\ModuleResourceLoader;
use SilverStripe\Forms\HTMLEditor\HTMLEditorConfig;


class ReoakoTinymceExtension extends Extension
{
    public function onInit()
    {
        $editor = HTMLEditorConfig::get('cms');

        if ($editor) {
            $pluginPath = ModuleResourceLoader::singleton()
                ->resolveResource('octavenz/reoako:dist/js/reoako-tinymce-plugin.js');

            $editor->enablePlugins([
                'reoako' => $pluginPath,
            ]);
            $editor->addButtonsToLine(2, 'reoako-button');

            $extendedValidElements = $editor->getOption('extended_valid_elements');
            if (!str_contains((string) $extendedValidElements, 'reoako[*]')) {
                $extendedValidElements = $extendedValidElements
                    ? $extendedValidElements . ',reoako[*]'
                    : 'reoako[*]';

                $editor->setOption('extended_valid_elements', $extendedValidElements);
            }
        }
    }
}
