<?php

namespace App\Repository;

use Cache;
use ZipArchive;
use SQLite3;

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

	// public function searchUsers($searchString) 
	// {
	// 	$path = "/User/SearchUsers/?q=" . urlencode($searchString);

	// 	$response = $this->execCurl($path);

	// 	echo json_encode($response);exit;

	// }

	public function checkManifest() 
	{
		$result = $this->getManifest("/Destiny2/Manifest/");

		$database = $result['Response']['mobileWorldContentPaths']['zh-chs'];

		if ($database != Cache::get('database')) {
			$tables = $this->updateManifest($database);

			Cache::put('database', $database);
			Cache::put('tables', $tables);
		}
	}

	private function getManifest($path)
	{
		$response = $this->execCurl($path);
		return $response;
	}

	private function updateManifest($url) 
	{
		// $ch = curl_init('https://www.bungie.net' . $url);
		// curl_setopt_array($ch, array(
		// 	CURLOPT_RETURNTRANSFER => true
		// ));
		// $data = curl_exec($ch);
		// curl_close($ch);

		$data = file_get_contents('https://www.bungie.net' . $url);

		$cacheFilePath = '../storage/'.pathinfo($url, PATHINFO_BASENAME);
		if (!file_exists(dirname($cacheFilePath))) mkdir(dirname($cacheFilePath), 0777, true);
		file_put_contents($cacheFilePath.'.zip', $data);
	
		$zip = new ZipArchive();
		if ($zip->open($cacheFilePath.'.zip') === TRUE) {
			$zip->extractTo('../storage/');
			$zip->close();
		}
	
		$tables = array();
		if ($db = new SQLite3($cacheFilePath)) {
			$result = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
			while($row = $result->fetchArray()) {
				$table = array();
				$result2 = $db->query("PRAGMA table_info(".$row['name'].")");
				while($row2 = $result2->fetchArray()) {
					$table[] = $row2[1];
				}
				$tables[$row['name']] = $table;
			}
		}
	
		return $tables;
	}

	private function queryManifest($query) {
		$database = Cache::get('database');
		$cacheFilePath = '../storage/'.pathinfo($database, PATHINFO_BASENAME);
	
		$results = array();
		if ($db = new SQLite3($cacheFilePath)) {
			$result = $db->query($query);
			
			while($row = $result->fetchArray()) {
				$key = is_numeric($row[0]) ? sprintf('%u', $row[0] & 0xFFFFFFFF) : $row[0];
				$results[$key] = json_decode($row[1]);
			}
		}
		return $results;
	}

	public function getDefinition($tableName, $id = false)
	{
		if ($id !== false) {
			$tables = Cache::get('tables');
			
			$key = $tables[$tableName][0];
			$where = ' WHERE ' . (is_numeric($id) ? $key . '=' . $id . ' OR ' . $key . '=' . ($id-4294967296) : $key . '="' . $id . '"');

			$results = $this->queryManifest('SELECT * FROM ' . $tableName . $where);

			return isset($results[$id]) ? $results[$id] : false;
		} else {
			return $this->queryManifest('SELECT * FROM ' . $tableName . ' LIMIT 10');
		}
	}

	public function searchDestinyPlayer($membershipType, $displayName)
	{
		$path = "/Destiny2/SearchDestinyPlayer/{$membershipType}/{$displayName}/";

		$response = $this->execCurl($path);
		return $response;
	}

	/*
	 * membershipType: xbox-1, psn-2, blizzard-4
	 *
	 */
	public function getDestiny2Profile($membershipType, $destinyMembershipId, $components = 100) 
	{
		$path = "/Destiny2/{$membershipType}/Profile/{$destinyMembershipId}/?components={$components}";

		$response = $this->execCurl($path);
		return $response;
	}

	public function getHistoricalStatsDefinition()
	{
		$path = "/Destiny2/Stats/Definition/";

		$response = $this->execCurl($path);
		return $response;
	}

	public function getHistoricalStatsForAccount($membershipType, $destinyMembershipId, $groups = "General,Weapons,Medals")
	{
		$path = "/Destiny2/{$membershipType}/Account/{$destinyMembershipId}/Stats/?groups={$groups}";

		$response = $this->execCurl($path);
		return $response;		
	}

	public function getActivityHistory($membershipType, $destinyMembershipId, $characterId, $mode, $count = 10, $page = 0)
	{
		$path = "/Destiny2/{$membershipType}/Account/{$destinyMembershipId}/Character/{$characterId}/Stats/Activities/?mode={$mode}&count={$count}&page={$page}";

		$response = $this->execCurl($path);
		echo json_encode($response);exit;
	}
}