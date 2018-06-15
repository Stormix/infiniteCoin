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
        $this->chain = array();
    }

    public function addBlock($Block){
        $this->chain[] = $Block;
    }

    public function nextBlock($lastBlock = NULL){
        $chainLength = count($this->chain);
        if($chainLength > 0){
            $lastBlock = $this->getLastBlock;
        }else if ($lastBlock == NULL){
            print("A last block should be provided since the chain is empty!");
        }
        return new Block($lastBlock->index+1,$lastBlock->timestamp,$lastBlock->data,$lastBlock->hash);
    }

    public function getLastBlock(){
        $chainLength = count($this->chain);
        if($chainLength == 0){
            return NULL;
        }else{
            return $this->chain[$chainLength-1];
        }
    }


}
