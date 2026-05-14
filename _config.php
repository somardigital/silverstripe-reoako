<?php

use Octavenz\Reoako\Extensions\ReoakoShortCodeExtension;
use SilverStripe\View\Parsers\ShortcodeParser;

ShortcodeParser::get('default')->register('reoako', ReoakoShortCodeExtension::ReoakoShortCode(...));
