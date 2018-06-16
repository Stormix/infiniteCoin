<?php
/**
 * Transaction class | src/Transaction.php.
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
 * Transaction
 *
 */
class Transaction
{
    public function __construct($fromAddress, $toAddress, $amount){
        $this->fromAddress = $fromAddress;
        $this->toAddress = $toAddress;
        $this->amount = $amount;
    }
}
