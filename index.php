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
$transactions = array();
$miner_address = "q3nf394hjg-random-miner-address-34nf3i4nflkn3oi";

function proofOfWork($lastProof){
	# Create a variable that we will use to find
	# our next proof of work
	$incrementor = $lastProof + 1;
	# Keep incrementing the incrementor until
	# it's equal to a number divisible by 9
	# and the proof of work of the previous
	# block in the chain
	while ($incrementor % 9 != 0 && $incrementor % $lastProof != 0){
		$incrementor++;
	}
	# Once that number is found,
	# we can return it as a proof
	# of our work
	return $incrementor;
}
// Router
$router = new \Klein\Klein();

$router->respond('GET', '/', function () {
	global $BlockChain;
	var_dump($BlockChain);
});
$router->respond('GET', '/mine', function () {
	// TODO log blocks
	global $BlockChain,$transactions,$transactionLog,$miner_address;
	$lastBlock = $BlockChain->getLastBlock();
	$lastProof = json_decode($lastBlock->data,true)["proofOfWork"];
	# Find the proof of work for
	# the current block being mined
	# Note: The program will hang here until a new
	#       proof of work is found
	$proof = proofOfWork($lastProof);
	# Once we find a valid proof of work,
	# we know we can mine a block so
	# we reward the miner by adding a transaction
	$transactions[] = array("from" => "network",
							"to" => $miner_address,
							"amount" => 1);
	$newBlockData = array(
		"proofOfWork" => 9,
		"transactions" => $transactions
	);
	$newBlockIndex = $lastBlock->index + 1;
	$newBlockHash = $lastBlock->hash;
	$newBlockTimestamp = time();
	# Empty transaction list
	$transactions = array();
	$minedBlock = new \infiniteCoin\Block($newBlockIndex,$newBlockTimestamp,$newBlockData,$lastBlock->hash);
	$BlockChain->addBlock($minedBlock);
	# Let the client know we mined a block
	header('Content-Type: application/json');
	return json_encode(array(
				"index" => $newBlockIndex,
				"timestamp" => $newBlockTimestamp,
				"data" => $newBlockData,
				"hash" => $newBlockHash
			));
});
$router->respond('POST', '/txion', function () {
	global $BlockChain,$transactions,$transactionLog;
	$new_txion = json_decode(json_encode($_POST),true); // TODO secure this
    # Then we add the transaction to our list
    array_push($transactions,$new_txion);
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
	global $BlockChain,$transactions,$transactionLog,$miner_address;
	$chainToSend = [];
	# Convert our blocks into associative arrays
	# so we can send them as json objects later
	foreach($BlockChain->chain as $block){
		$jsonBlock = array(
			"index" => $block->index,
			"timestamp" => $block->timestamp,
			"data" => json_decode($block->data),
			"hash" => $block->hash
		);
		array_push($chainToSend,$jsonBlock);
	}
	# Send our chain to whomever requested it
	header('Content-Type: application/json');
	return json_encode($chainToSend);
});
$router->dispatch();
