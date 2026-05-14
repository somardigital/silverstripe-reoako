<?php

namespace Octavenz\Reoako\Controllers;

use SilverStripe\Model\ArrayData;
use SilverStripe\Model\List\ArrayList;
use SilverStripe\Control\Controller;
use Octavenz\Reoako\Client\ReoakoClient;
use SilverStripe\View\Requirements;


class ReoakoController extends Controller
{
    private static $allowed_actions = [
        'modal',
        'search'
    ];

    private static $url_handlers = [
        'GET /' => 'modal',
        'POST /' => 'modal',
    ];

    public function modal($params)
    {
    }

    public function search($request)
    {
        Requirements::css("silverstripe/admin:client/dist/styles/bundle.css");
        $rc = new ReoakoClient();
        $vars = $request->getVars();

        //TODO: just get search_term

        foreach ($vars as $key => $val) {
            if ($key == 'search_term') {

                //return blank page
                if (empty($val)) {
                    return $this->customise(ArrayData::create([]))->renderWith('reoako');
                }

                $results = $rc->search($val);

                if (isset($results['error'])) {
                    return $this->customise(ArrayData::create([
                        'search_term' => $val,
                        'error' => $results['error']
                    ]))->renderWith('reoako');
                }

                if ($request->isAjax()) {

                    return $this->customise(ArrayData::create([
                        'results' => $results
                    ]))->renderWith('ajax_results');
                }

                $data = ArrayList::create();
                foreach ($results as $rk => $rv) {
                    if ($rk == 'results') {
                        foreach ($rv as $e) {
                            $translations = ArrayList::create();
                            foreach (($e['translations'] ?? []) as $t) {
                                $translations->push(ArrayData::create([
                                    'url' => $t['url'] ?? null,
                                    'en' =>  $t['en'] ?? null,
                                    'mi' =>  $t['mi'] ?? null,
                                    'slug' => $t['slug'] ?? null,
                                    'audio_url' => $t['audio_url'] ?? null,
                                ]));
                            }

                            $r = ArrayData::create([
                                'headword' => $e['headword'],
                                'function' => $e['function'],
                                'definition' => $e['definition'],
                                'translations' => $translations,
                            ]);
                            $data->push($r);
                        }
                    }
                }

                return $this->customise(ArrayData::create([
                    'search_term' => $val,
                    'results' => $data,
                    'count' => count($data)
                ]))->renderWith('reoako');
            }
        }
    }
}
