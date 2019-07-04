<?php

namespace App\Repository;

class BungieRepository 
{
	function __construct() 
	{
		$this->x_api_key = config('web.bungie.X-API-KEY');
	}

	public function execCurl($path, $data = []) 
	{
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://www.bungie.net/Platform" . $path,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_SSL_VERIFYHOST => false,
		  CURLOPT_SSL_VERIFYPEER => false,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array(
		    "Accept: */*",
		    "X-API-Key: {$this->x_api_key}",
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		if ($err) {
		    // echo "cURL Error #:" . $err;
			// TODO: write error log
			return false;
		} else {
			return json_decode($response, true);
		}
	}

	public function searchUsers($searchString) 
	{
		$path = "/User/SearchUsers/?q=" . urlencode($searchString);

		$response = $this->execCurl($path);

		echo json_encode($response);exit;

	}

	public function getDestiny2Manifest()
	{
		$path = "/Destiny2/Manifest/";

		$response = $this->execCurl($path);
		echo json_encode($response);exit;
	}

	public function searchDestinyPlayer($membershipType, $displayName)
	{
		$path = "/Destiny2/SearchDestinyPlayer/{$membershipType}/{$displayName}/";

		$response = $this->execCurl($path);
		echo json_encode($response);exit;
	}

	/*
	 * membershipType: xbox-1, psn-2, blizzard-4
	 *
	 */
	public function getDestiny2Profile($membershipType, $destinyMembershipId) 
	{
		$path = "/Destiny2/{$membershipType}/Profile/{$destinyMembershipId}/";

		$response = $this->execCurl($path);
		echo json_encode($response);exit;

	}
}