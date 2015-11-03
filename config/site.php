<?php

return [
    'api_url'   => (env('APP_ENV', 'local') === 'production') ? 'http://api.faithpromise.org' : 'http://api.faithpromise.192.168.10.10.xip.io',
    'admin_url' => (env('APP_ENV', 'local') === 'production') ? 'http://admin.faithpromise.org' : 'http://admin.faithpromise.192.168.10.10.xip.io',
];
