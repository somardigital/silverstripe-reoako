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

            // Use extended_valid_elements to avoid breaking the default TinyMCE schema
            $editor->setOption('extended_valid_elements', 'reoako[*]');
        }
    }
}
