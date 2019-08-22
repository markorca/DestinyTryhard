<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repository\BungieRepository;

class TestController extends Controller
{
    public function __construct(BungieRepository $bungieRepository) 
    {
        $this->bungieRepo = $bungieRepository;
    }

    public function test()
    {
        $response = $this->bungieRepo->searchDestinyPlayer(2, 'Markorca');
        $membershipId = $response['Response']['0']['membershipId'];

        $response = $this->bungieRepo->getDestiny2Profile(2, $membershipId);
        $characterIds = $response['Response']['profile']['data']['characterIds'];

        // $response = $this->bungieRepo->getHistoricalStatsDefinition();
        $response = $this->bungieRepo->getHistoricalStatsForAccount(2, $membershipId, 'General');
        echo json_encode($response);exit;
        // $this->bungieRepo->getActivityHistory(2, $membershipId, $characterIds[0], 5);

        $response = $this->bungieRepo->getDefinition('DestinyInventoryItemDefinition');
        echo json_encode($response);exit;
    }
}
