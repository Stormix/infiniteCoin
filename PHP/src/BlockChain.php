<?php
/**
 * Chain class | src/BlockChain.php.
 *
 * @author Stormix <madadj4@gmail.com>
 * @license MIT
 *
 * @version 0.1
 *
 * @copyright Copyright (c) 2018, Stormix.co
 */

namespace infiniteCoin;
/**
 * BlockChain
 *
 */
class BlockChain
{
    public function __construct(){
        $genesisBlock = new Block(0,time(),[],"0");
        $this->chain = array($genesisBlock);
        $this->difficulty = 2;
        // Place to store transactions in between block creation
        $this->pendingTransactions = [];
        // How many coins a miner will get as a reward for his/her efforts
        $this->miningReward = 1;
        $this->nodes = array();

    }

    public function urlParse($url){
        return parse_url($url, PHP_URL_HOST).":".parse_url($url, PHP_URL_PORT);
    }
    public function register_node($address){
        /**
         * Add a new node to the list of nodes
         *  :param address: <str> Address of node. Eg. 'http://192.168.0.5:5000'
         *  :return: None
         **/
        $this->nodes[] = $this->urlParse($address);
    }
    public function createTransaction($transaction){
        // There should be some validation here!
        // Push into onto the "pendingTransactions" array
        array_push($this->pendingTransactions,$transaction);
        return $this->getLastBlock()->index+1;
    }

    public function minePendingTransactions($miningRewardAddress) {
        // Create new block with all pending transactions and mine it..
        $block = new Block($this->getLastBlock()->index +1, time(), $this->pendingTransactions,$this->getLastBlock()->hash);
        $block->mineBlock($this->difficulty);
        // Add the newly mined block to the chain
        array_push($this->chain,$block);
        // Reset the pending transactions and send the mining reward
        $this->pendingTransactions = [
            new Transaction("network", $miningRewardAddress, $this->miningReward)
        ];
        return $block;
    }

    public function getBalanceOfAddress($address,$pending = False){
        $balance = 0; // you start at zero!
        // Loop over each block and each transaction inside the block
        foreach($this->chain as $block){
            if(count($block->transactions) > 0){
                foreach($block->transactions as $trans){
                    // If the given address is the sender -> reduce the balance
                    if($trans->fromAddress === $address){
                        $balance -= $trans->amount;
                    }
                    // If the given address is the receiver -> increase the balance
                    if($trans->toAddress === $address){
                        $balance += $trans->amount;
                    }
                }
            }
        }
        // include the pending transactions if asked for
        if(count($this->pendingTransactions) > 0 && $pending){
            foreach($this->pendingTransactions as $trans){
                // If the given address is the sender -> reduce the balance
                if($trans->fromAddress === $address){
                    $balance -= $trans->amount;
                }
                // If the given address is the receiver -> increase the balance
                if($trans->toAddress === $address){
                    $balance += $trans->amount;
                }
            }
        }

        return $balance;
    }
    public function getLastBlock(){
        $chainLength = count($this->chain);
        if($chainLength == 0){
            return NULL;
        }else{
            return $this->chain[$chainLength-1];
        }
    }

    /**
     * Validates the blockchain's integrity. True if the blockchain is valid, false otherwise.
     */
    public function isValid(){
        for ($i = 1; $i < count($this->chain); $i++) {
            $currentBlock = $this->chain[$i];
            $previousBlock = $this->chain[$i-1];
            if ($currentBlock->hash != $currentBlock->hash_block()) {
                return false;
            }
            if ($currentBlock->previousHash != $previousBlock->hash) {
                return false;
            }
        }
        return true;
    }

    public function resolve_conflicts(){
        /**
         * This is our Consensus Algorithm, it resolves conflicts
         * by replacing our chain with the longest one in the network.
         * :return: <bool> True if our chain was replaced, False if not
         **/
        // Find neighbours blockchains
        # Get the blockchains of every
        # other node
        $other_chains = [];
        foreach($this->nodes as $node_url){
      	    # Get their chains using a GET request
      	    # Convert the JSON object to an associative array
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, "http://".$node_url . "/blocks");
            $result = curl_exec($ch);
            curl_close($ch);
      	    $chain = json_decode($result,true);
            print_r($result);exit();
      	    # Add it to our list
      	    $other_chains[] = $chain;
      	}

        # If our chain isn't longest,
    	# then we store the longest chain
    	$longestChain = $this->chain;
    	foreach($other_chains as $chain){
    	    if(count($longestChain) < count($chain)){
    		    $longestChain = $chain;
    		}
    	}
    	# If the longest chain wasn't ours,
    	# then we set our chain to the longest
        if($this->chain != $longestChain){
            $this->chain = $longestChain;
            return True;
        }
        return False;
    }
}
