<?php
/**
 * @author Stormix <madadj4@gmail.com>
 * @license MIT
 *
 * @version 0.1
 *
 * @copyright Copyright (c) 2018, Stormix.co
 */
require_once __DIR__ . '/vendor/autoload.php';
include_once("src/infiniteCoin.php");
//Logger
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// create a log channel
$transactionLog = new Logger('transaction');
$transactionLog->pushHandler(new StreamHandler('logs/transactions.log', Logger::INFO));

// System
$miner_address = "q3nf394hjg-random-miner-address-34nf3i4nflkn3oi";


// Router
$router = new \Klein\Klein();

$router->respond('GET', '/', function () {
	global $BlockChain;
	print('Creating some transactions...');
	$BlockChain->createTransaction(new \infiniteCoin\Transaction('address1', 'address2', 100));
	$BlockChain->createTransaction(new \infiniteCoin\Transaction('address2', 'address1', 50));
	print('Starting the miner...');
	$BlockChain->minePendingTransactions('address');
	print('Balance of address is'. $BlockChain->getBalanceOfAddress('address'));
	print('Starting the miner again!');
	$BlockChain->minePendingTransactions("address");
	print('Balance of address is'.$BlockChain->getBalanceOfAddress('address'));
});
$router->respond('GET', '/mine', function () {
	// TODO log blocks
	global $BlockChain,$transactionLog,$miner_address;
	print('Starting the miner...');
	$BlockChain->minePendingTransactions($miner_address);
	print('Balance is'. $BlockChain->getBalanceOfAddress($miner_address));
});

$router->respond('POST', '/txion', function () {
	global $BlockChain,$transactionLog;
	$new_txion =$_POST; // TODO secure this
    # Then we add the transaction to our list
	$BlockChain->createTransaction(new \infiniteCoin\Transaction($_POST['from'], $_POST['to'], $_POST['amount']));
    # Because the transaction was successfully
    # submitted, we log it
    $transactionLog->info("New transaction"."\n");
    $transactionLog->info("FROM:".$_POST['from']."\n");
    $transactionLog->info("TO:".$_POST['to']."\n");
    $transactionLog->info("AMOUNT:".$_POST['amount']."\n");
    # Then we let the client know it worked out
    return "Transaction submission successful\n";

});
$router->respond('GET', '/blocks', function () {
	global $BlockChain,$transactionLog;
	$chainToSend = [];
	# Convert our blocks into associative arrays
	# so we can send them as json objects later
	foreach($BlockChain->chain as $block){
		$jsonBlock = array(
			"index" => $block->index,
			"timestamp" => $block->timestamp,
			"transactions" => $block->transactions,
			"hash" => $block->hash
		);
		array_push($chainToSend,$jsonBlock);
	}
	# Send our chain to whomever requested it
	header('Content-Type: application/json');
	return json_encode($chainToSend);
});
$router->dispatch();
