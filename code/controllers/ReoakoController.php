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
                    return $this->customise(new ArrayData([]))->renderWith('reoako');
                }

                $results = $rc->search($val);

                if (isset($results['error'])) {
                    return $this->customise(new ArrayData([
                        'search_term' => $val,
                        'error' => (isset($results['error']) && $results['error']) ? $results['error'] : 'An error occurred while searching. Please try again later.'
                    ]))->renderWith('reoako');
                }

                if ($request->isAjax()) {

                    $this->customise(new ArrayData([
                        'results' => $results
                    ]))->renderWith('ajax_results');
                }

                $data = new ArrayList();
                foreach ($results as $rk => $rv) {
                    if ($rk == 'results') {
                        foreach ($rv as $e) {
                            $translations = new ArrayList();
                            foreach ($e['translations'] as $t) {
                                $translations->push(new ArrayData([
                                    'url' => $t['url'],
                                    'en' =>  $t['en'],
                                    'mi' =>  $t['mi'],
                                    'slug' => $t['slug'],
                                    'audio_url' => $t['audio_url'],
                                ]));
                            }
                            $r = new ArrayData([
                                'headword' => $e['headword'],
                                'function' => $e['function'],
                                'definition' => $e['definition'],
                                'translations' => $translations,
                            ]);
                            $data->push($r);
                        }
                    }
                }

                return $this->customise(new ArrayData([
                    'search_term' => $val,
                    'results' => $data,
                    'count' => count($data)
                ]))->renderWith('reoako');
            }
        }
    }
}
