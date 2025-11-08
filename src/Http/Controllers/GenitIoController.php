<?php

namespace GenitIo\Chat\Http\Controllers;

use GenitIo\Chat\Services\GenitIoApiClient;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class GenitIoController extends Controller
{
    protected GenitIoApiClient $genitIoApiClient;

    public function __construct(GenitIoApiClient $genitIoApiClient)
    {
        $this->genitIoApiClient = $genitIoApiClient;
    }

    public function config(Request $request)
    {
        $config = $this->genitIoApiClient->getConfig();
        return response()->json($config);
    }
}
