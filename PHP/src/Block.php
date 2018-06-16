<?php
/**
 * Block class | src/Block.php.
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
 * Block
 *
 */
class Block
{
    public function __construct($index,$timestamp,$transactions,$previous_hash = NULL)
    {
        $this->index = $index;
        $this->timestamp = $timestamp;
        $this->transactions = $transactions;
        $this->previous_hash = $previous_hash;
        $this->nonce = 0; //https://www.savjee.be/2017/09/Implementing-proof-of-work-javascript-blockchain/
        $this->hash = $this->hash_block();
    }
    public function hash_block(){
        $data = $this->index.$this->previous_hash.$this->timestamp.json_encode($this->transactions).$this->nonce;
        return hash('sha256',$data);
    }
    public function mineBlock($difficulty) {
        while (substr($this->hash, 0, $difficulty) !== str_repeat("0", $difficulty)) {
            $this->nonce++;
            $this->hash = $this->hash_block();
        }
    }

}
