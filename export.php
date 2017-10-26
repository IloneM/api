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
if (strlen($_GET['addr']) == 42) {
	$addr = strtolower(preg_replace("/[^a-zA-Z0-9]+/", "", $_GET['addr']));
}else{
	echo "Bye!";
}
if (empty($_GET['start'])) {
	$_GET['start'] = 0;
}
if (empty($_GET['end'])) {
	$_GET['end'] = 1999999999;
}
if (is_numeric($_GET['start'])){
	$start = $_GET['start'];
} else {
	$start = 0;
}
if (is_numeric($_GET['end'])){
	$end = $_GET['end'];
} else {
	$end = 1999999999;
}
 
$dir = 'sqlite:/home/ethereum/transactions.db';
 
$dbh = new PDO($dir) or die("cannot open database");
 
$query = "SELECT _from, _to, time, (CASE WHEN _from == \"$addr\" THEN sent ELSE recieved END) AS AMOUNT, tax, type, hash, block FROM TRANSACTIONS WHERE ((_FROM = \"$addr\" OR _TO =\"$addr\") AND (time >= \"$start\" AND time <= \"$end\"))COLLATE NOCASE ORDER BY CAST(TIME AS REAL) DESC";

$counter=0;
foreach ($dbh->query($query) as $row) {
$jstring[$counter] = json_encode($row);
$counter++;
}
if ($jstring != null){ 
echo json_encode($jstring);
}else{
echo "[]";
}
?>
