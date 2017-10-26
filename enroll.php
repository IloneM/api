/*

    Copyright © 2016-2017 Dominique Climent, Florian Dubath

    This file is part of Monnaie-Leman Wallet.

    Monnaie-Leman Wallet is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Monnaie-Leman Wallet is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Monnaie-Leman Wallet.  If not, see <http://www.gnu.org/licenses/>.

*/
<?php
header('Access-Control-Allow-Origin: *');
$maxAccounts=50000;
class MyDB extends SQLite3
   {
      function __construct()
      {
         $this->open('/var/www/html/admin/leman-admin/membres.db');
      }
   }
$db = new MyDB();
if(!$db){
   echo $db->lastErrorMsg();
}
function base64url_encode($data) { 
  return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
} 

function base64url_decode($data) { 
  return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)); 
} 


$data = json_decode($_POST['data'], true);
$uid = filter_var($data['id'], FILTER_SANITIZE_STRING);
$sign = filter_var($data['signature'], FILTER_SANITIZE_STRING);

$pubkeyid = openssl_pkey_get_public("file:///var/www/html/api/leman-admin/pubkey.pem");
$pkeyid = openssl_pkey_get_private("file:///var/www/privkey.pem");
$signature = base64url_decode($sign);

// state whether signature is okay or not
$ok = openssl_verify($uid, $signature, $pubkeyid, OPENSSL_ALGO_SHA1);
if ($ok == 1) {
    $result = "OK";
} elseif ($ok == 0) {
    $result = "KO";
} else {
    $result = "ERROR";
}
$idstr = $uid . ":MySecr3tT0cken";
openssl_sign($idstr, $temp, $pkeyid, "sha1WithRSAEncryption");
$idstr = base64url_encode($temp);
$res = array('result' => $result, 'token' => $idstr);
$json = json_encode($res);
if (isset($data['adresse'])) {
	$id = filter_var($data['id'], FILTER_SANITIZE_STRING);
	$adresse = filter_var($data['adresse'], FILTER_SANITIZE_STRING);
	$token = base64url_decode($data['token']);
	$idstr = $id .  ":MySecr3tT0cken";
	$ok = openssl_verify($idstr, $token, $pubkeyid, OPENSSL_ALGO_SHA1);

	$sql ="SELECT Adresses,Count from Membres where Code = \"$id\"";
   	$ret = $db->query($sql);
   	while($row = $ret->fetchArray(SQLITE3_ASSOC) ){
		$maxAccounts=$maxAccounts+1;
		$count = $row['Count']+1;
		if ($count<$maxAccounts) {
			$updateCount = "UPDATE Membres SET Count=$count WHERE Code = \"$id\"";
			$ret = $db->query($updateCount);
			if ($row['Adresses'] != ""){ 
				$addr = $row['Adresses'] . "," . $adresse;
			} else {
				$addr = $adresse;
			}
			$updateAddr = "UPDATE Membres SET Adresses=\"$addr\" WHERE Code = \"$id\"";
			$ret = $db->query($updateAddr);
		} else {
			$ok = 0;
		}
   	}

	if ($ok == 1) {
	    $result = "OK";
	} elseif ($ok == 0) {
	    $result = "KO";
	} else {
	    $result = "ERROR";
	}
	$res = array('adresse' => $adresse, 'result' => $result);
	$json = json_encode($res);
	$db->close();
}
echo $json;
openssl_free_key($pubkeyid);
?>
