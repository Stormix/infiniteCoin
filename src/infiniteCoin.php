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
use infiniteCoin\Session;

$BlockChain = new BlockChain();
$infiniteCoin = Session::getInstance("port".$_SERVER['SERVER_PORT']);
if(!isset($infiniteCoin->BlockChain)){
	$infiniteCoin->BlockChain = $BlockChain;
}
