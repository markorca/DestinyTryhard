<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repository\BungieRepository;

class TestController extends Controller
{
    public function __construct() 
    {
        $this->bungieRepo = new BungieRepository;
    }

    public function test()
    {
        $this->bungieRepo->getDestiny2Manifest();
    }
}
