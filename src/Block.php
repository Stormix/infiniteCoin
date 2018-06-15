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
    public function __construct($index,$timestamp,$data,$previous_hash)
    {
        $this->index = $index;
        $this->timestamp = $timestamp;
        $this->data = $data;
        $this->previous_hash = $previous_hash;
        $this->hash = $this->hash_block();
    }
    public function hash_block(){
        $data = $this->index.$this->timestamp.json_encode($this->data).$this->previous_hash;
        return hash('sha256',$data);
    }

}
