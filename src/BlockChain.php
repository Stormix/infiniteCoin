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

    }

    public function createTransaction($transaction){
        // There should be some validation here!
        // Push into onto the "pendingTransactions" array
        array_push($this->pendingTransactions,$transaction);
    }

    public function minePendingTransactions($miningRewardAddress) {
        // Create new block with all pending transactions and mine it..
        $block = new Block($this->getLastBlock()->index +1, time(), $this->pendingTransactions);
        $block->mineBlock($this->difficulty);
        print('Block successfully mined! \n');
        // Add the newly mined block to the chain
        array_push($this->chain,$block);
        // Reset the pending transactions and send the mining reward
        $this->pendingTransactions = [
            new Transaction("network", $miningRewardAddress, $this->miningReward)
        ];
    }

    public function nextBlock($lastBlock = NULL){
        $chainLength = count($this->chain);
        if($chainLength > 0){
            $lastBlock = $this->getLastBlock;
        }else if ($lastBlock == NULL){
            print("A last block should be provided since the chain is empty! \n");
        }
        return new Block($lastBlock->index+1,$lastBlock->timestamp,$lastBlock->transactions,$lastBlock->hash);
    }

    public function getBalanceOfAddress($address){
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
            if ($currentBlock->hash != $currentBlock->calculateHash()) {
                return false;
            }
            if ($currentBlock->previousHash != $previousBlock->hash) {
                return false;
            }
        }
        return true;
    }
}
