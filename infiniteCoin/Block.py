"""
    /**
    * @author Stormix <madadj4@gmail.com>
    * @license MIT
    *
    * @version 0.1
    *
    * @copyright Copyright (c) 2018, Stormix.co
    */
 """

import hashlib as hasher
import time
import json


class Block():
    def __init__(self, index, timestamp, transactions, previous_hash=None, nonce=0, hash=None):
        self.index = index
        self.timestamp = timestamp
        self.transactions = transactions
        self.previous_hash = previous_hash
        # https: // www.savjee.be/2017/09/Implementing-proof-of-work-javascript-blockchain
        self.nonce = nonce
        self.hash = self.hash_block() if not hash else hash

    def transactionsJson(self):
        return [{"fromAddress": trans.fromAddress, "toAddress": trans.toAddress, "amount": trans.amount} for trans in self.transactions]

    def hash_block(self):
        blockDict = {
            'index': self.index,
            'timestamp': self.timestamp,
            'transactions': self.transactionsJson(),
            'previous_hash': self.previous_hash,
            'nonce': self.nonce
        }
        sha = hasher.sha256(json.dumps(blockDict, sort_keys=True).encode())
        return sha.hexdigest()

    def mineBlock(self, difficulty):
        while self.hash[:difficulty] != "0" * difficulty:
            self.nonce += 1
            self.hash = self.hash_block()
