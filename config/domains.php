<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Domain
    |--------------------------------------------------------------------------
    |
    | The domain where the admin panel is served. Only routes in routes/admin.php
    | are accessible on this domain. Example: admin.votingsystem.com
    |
    */

    'admin' => env('ADMIN_DOMAIN', 'admin.voting.test'),

    /*
    |--------------------------------------------------------------------------
    | Voter Panel Domain
    |--------------------------------------------------------------------------
    |
    | The domain where the voter panel is served. Only routes in routes/voter.php
    | are accessible on this domain. Example: vote.votingsystem.com
    |
    */

    'voter' => env('VOTER_DOMAIN', 'vote.voting.test'),

];
