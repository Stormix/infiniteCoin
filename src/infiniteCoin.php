<?php
/**
 * @author Stormix <madadj4@gmail.com>
 * @license MIT
 *
 * @version 0.1
 *
 * @copyright Copyright (c) 2018, Stormix.co
 */
namespace infiniteCoin;

include "Block.php";
include "BlockChain.php";

use infiniteCoin\Block;
use infiniteCoin\BlockChain;
use infiniteCoin\Transaction;

$BlockChain = new BlockChain();
$peer_nodes = [];
function findNewChains(){
  # Get the blockchains of every
  # other node
  $other_chains = [];
  foreach($peer_nodes as $node_url){
	    # Get their chains using a GET request
	    # Convert the JSON object to an associative array
	    $block = json_decode(file_get_contents($node_url . "/blocks"),true);
	    # Add it to our list
	    $other_chains[] = $block;
	}
  return $other_chains;
}
function consensus(){
	# Get the blockchains of every
	# other node
	$other_chains = findNewChains();
	# If our chain isn't longest,
	# then we store the longest chain
	$longestChain = $BlockChain;
	foreach($other_chains as $chain){
	    if(count($longestChain->chain) < count($chain->chain)){
		    $longestChain = $chain;
		}
	}
	# If the longest chain wasn't ours,
	# then we set our chain to the longest
	$BlockChain = $longestChain;
}
