<?php

use Illuminate\Support\Facades\Route;

/**Route::view('/driftsstatus', 'status.index')->name('status.index');*/

/**
 * We give the view a list of carriers with:
 * - name: shown as text
 * - logo: file name inside /public/images/carriers/
 * - has_issue: red/green indicator
 * - message: only shown if has_issue = true
 */
Route::view('/driftsstatus', 'status.index', [
    'carriers' => [
        [
            'name'      => 'GLS',
            'logo'      => 'gls.png',
            'has_issue' => true,
            'message'   => 'Label-generering fejler i øjeblikket.',
        ],
        [
            'name'      => 'DFM',
            'logo'      => 'dfm.png',
            'has_issue' => true,
            'message'   => 'Servicepoint-opslag fejler på visse adresser.',
        ],
        [
            'name'      => 'PACKETA',
            'logo'      => 'packeta.png',
            'has_issue' => true,
            'message'   => 'Packeta API svarer langsomt.',
        ],
        [
            'name'      => 'BRING',
            'logo'      => 'bring.png',
            'has_issue' => true,
            'message'   => 'Timeout mod Bring’s API for servicepoints.',
        ],
        [
            'name'      => 'POSTNORD',
            'logo'      => 'pdk.png',
            'has_issue' => false,
            'message'   => null,
        ],
        [
            'name'      => 'DAO',
            'logo'      => 'dao.png',
            'has_issue' => false,
            'message'   => null,
        ],
    ],
])->name('status.index');

Route::get('/', function () {
    return view('welcome');
});

