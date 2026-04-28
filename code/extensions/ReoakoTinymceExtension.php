<?php

namespace Octavenz\Reoako\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\HTMLEditor\HTMLEditorConfig;
use SilverStripe\Core\Manifest\ModuleResourceLoader;

class ReoakoTinymceExtension extends Extension
{
    public function onInit()
    {
        // Target the 'cms' config specifically, or use get_active() if preferred
        $editor = HTMLEditorConfig::get('cms');

        if ($editor) {
            // resolveResource() returns a ModuleResource so TinyMCEConfig builds the
            // correct public /_resources/… URL rather than a filesystem path
            $pluginPath = ModuleResourceLoader::singleton()
                ->resolveResource('octavenz/reoako:dist/js/reoako-tinymce-plugin.js');

            $editor->enablePlugins([
                'reoakotranslationdialog' => $pluginPath
            ]);

            // Add button to the second line
            $editor->addButtonsToLine(2, 'reoakotranslationdialog');

            // Append to extended_valid_elements rather than overwriting any existing elements configured by other modules or project config.
            $extendedValidElements = $editor->getOption('extended_valid_elements');

            if (strpos((string) $extendedValidElements, 'reoako[*]') === false) {
                $extendedValidElements = $extendedValidElements
                    ? $extendedValidElements . ',reoako[*]'
                    : 'reoako[*]';

                $editor->setOption('extended_valid_elements', $extendedValidElements);
            }
        }
    }
}
