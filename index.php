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
include_once("src/Session.php");

//Logger
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// create a log channel
$transactionLog = new Logger('transaction');
$transactionLog->pushHandler(new StreamHandler('logs/transactions.log', Logger::INFO));
$minerLog = new Logger('miner');
$minerLog->pushHandler(new StreamHandler('logs/miner.log', Logger::INFO));

// System
$miner_address = "address";

// Router
$router = new \Klein\Klein();

header('Content-Type: application/json');
$router->respond('GET', '/', function () {
	global $infiniteCoin;
	$infiniteCoin->BlockChain->createTransaction(new \infiniteCoin\Transaction('address1', 'address2', 100));
	$infiniteCoin->BlockChain->createTransaction(new \infiniteCoin\Transaction('address2', 'address1', 50));
});

$router->respond('GET', '/mine', function () {
	global $infiniteCoin,$minerLog,$miner_address;
	$minerLog->info('Starting the miner...');
	$block = $infiniteCoin->BlockChain->minePendingTransactions($miner_address);
	$minerLog->info('A coin will be added to current balance of '.$miner_address.':'. $infiniteCoin->BlockChain->getBalanceOfAddress($miner_address));
	return json_encode(array(
        'message'=>"New Block Forged",
        'index'=>$block->index,
        'transactions'=>$block->transactions,
        'previous_hash'=>$block->previous_hash
    ));
});

$router->respond('POST', '/transactions/new', function () {
	global $infiniteCoin,$transactionLog;
	$new_txion = $_POST; // TODO secure this
	if(count($_POST) == 3 && isset($_POST["from"])&& isset($_POST["to"]) && isset($_POST["amount"])){
		# Then we add the transaction to our list
		$BlockID = $infiniteCoin->BlockChain->createTransaction(new \infiniteCoin\Transaction($_POST['from'], $_POST['to'], $_POST['amount']));
	    # Because the transaction was successfully
	    # submitted, we log it
	    $transactionLog->info("New transaction"."\n");
	    $transactionLog->info("FROM:".$_POST['from']."\n");
	    $transactionLog->info("TO:".$_POST['to']."\n");
	    $transactionLog->info("AMOUNT:".$_POST['amount']."\n");
	    # Then we let the client know it worked out
	    return json_encode(array(
			"message" => "Transaction submission successful\n Will be added to Block #".$BlockID
		));
	}else{
		return json_encode(array(
			"message" => "Failed to complete request!"
		));
	}
});
$router->respond('GET', '/blocks', function () {
	global $infiniteCoin, $transactionLog;
	$chainToSend = [];
	# Convert our blocks into associative arrays
	# so we can send them as json objects later
	foreach($infiniteCoin->BlockChain->chain as $block){
		$jsonBlock = array(
			"index" => $block->index,
			"timestamp" => $block->timestamp,
			"transactions" => $block->transactions,
			"hash" => $block->hash
		);
		array_push($chainToSend,$jsonBlock);
	}
	# Send our chain to whomever requested
	return json_encode($chainToSend);
});

$router->respond('GET', '/reset', function () {
	global $infiniteCoin;
	$infiniteCoin->destroy("port".$_SERVER['SERVER_PORT']);
});

$router->respond('POST', '/nodes/register', function () {
	global $infiniteCoin;
	if(isset($_POST['nodes'])){
		$nodes = explode(',',$_POST['nodes']);
		foreach($nodes as $node){
			$infiniteCoin->BlockChain->register_node($node);
		}
		return json_encode(array(
			"message" => "New nodes have been added",
			"total_nodes" => $infiniteCoin->BlockChain->nodes
		));
	}else{
		return json_encode(array(
			"message" => "Please supply a valid list of nodes separated by commas!"
		));
	}
});

$router->respond('GET', '/nodes/resolve', function () {
	global $infiniteCoin;
	$replaced = $infiniteCoin->BlockChain->resolve_conflicts();
	if($replaced){
	   return json_encode(array(
		   "message" => "Our chain was replaced!",
		   "chain" => $infiniteCoin->BlockChain->chain
	   ));
	}else{
	   return json_encode(array(
		   "message" => "Our chain is authoritative!",
		   "chain" => $infiniteCoin->BlockChain->chain
	   ));
   	}
});
$router->dispatch();
