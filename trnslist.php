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
if (empty($_GET['count'])) {
	$_GET['count'] = 5;
}
if (empty($_GET['offset'])) {
	$_GET['offset'] = 0;
}
if (is_numeric($_GET['count'])){
	$limit = $_GET['count'];
} else {
	$limit = 5;
}
if (is_numeric($_GET['offset'])){
	$offset = $_GET['offset'];
} else {
	$offset = 0;
}
 
$dir = 'sqlite:/home/ethereum/transactions.db';
 
$dbh = new PDO($dir) or die("cannot open database");
 
$query = "SELECT _from, _to, time, (CASE WHEN _from == \"$addr\" THEN sent ELSE recieved END) AS AMOUNT, type, hash, block FROM TRANSACTIONS WHERE (_FROM = \"$addr\" OR _TO =\"$addr\") COLLATE NOCASE ORDER BY CAST(TIME AS REAL) DESC LIMIT $limit OFFSET $offset";

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
