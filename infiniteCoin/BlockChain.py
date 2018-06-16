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
from .Block import Block
from .Transaction import Transaction
import time
import requests


class BlockChain():
    def __init__(self):
        genesisBlock = Block(0, time.time(), [], "0")
        self.chain = [genesisBlock]
        self.difficulty = 2
        self.pendingTransactions = []
        self.miningReward = 1
        self.nodes = []

    def chainDict(self):
        result = []
        for block in self.chain:
            blockDict = {
                'index': block.index,
                'timestamp': block.timestamp,
                'transactions': block.transactionsJson(),
                'previous_hash': block.previous_hash,
                'hash': block.hash,
                'nonce': block.nonce
            }
            result += [blockDict]
        return result

    def registerNode(self, address):
        self.nodes += [address]

    def createTransaction(self, transaction):
        self.pendingTransactions += [transaction]
        return self.getLastBlock().index + 1

    def minePendingTransactions(self, miningRewardAddress):
        block = Block(self.getLastBlock().index + 1, time.time(),
                      self.pendingTransactions, self.getLastBlock().hash)
        block.mineBlock(self.difficulty)
        self.chain += [block]
        self.pendingTransactions = [
            Transaction("network", miningRewardAddress, self.miningReward)
        ]
        return block

    def getBalanceOfAddress(self, address, pending=False):
        balance = 0
        for block in self.chain:
            if len(block.transactions) > 0:
                for trans in block.transactions:
                    if trans.fromAddress == address:
                        balance -= trans.amount
                    if trans.toAddress == address:
                        balance += trans.amount
        if len(self.pendingTransactions) > 0 and pending:
            for trans in self.pendingTransactions:
                if trans.fromAddress == address:
                    balance -= trans.amount
                if trans.toAddress == address:
                    balance += trans.amount
        return balance

    def getLastBlock(self):
        return self.chain[-1] if len(self.chain) > 0 else None

    def valid_chain(self):
        """
        Determine if a given blockchain is valid
        :param chain: A blockchain
        :return: True if valid, False if not
        """

        last_block = self.chain[0]
        for block in self.chain[1:]:
            # Check that the hash of the block is correct
            last_block_hash = last_block.hash
            if block.previous_hash != last_block_hash:
                return False
            last_block = block
        return True

    def resolveConflicts(self):
        """
        This is our consensus algorithm, it resolves conflicts
        by replacing our chain with the longest one in the network.
        :return: True if our chain was replaced, False if not
        """

        neighbours = self.nodes
        new_chain = None

        # We're only looking for chains longer than ours
        max_length = len(self.chain)

        # Grab and verify the chains from all the nodes in our network
        for node in neighbours:
            response = requests.get('{}/blocks'.format(node))

            if response.status_code == 200:
                length = response.json()['length']
                chain = response.json()['chain']
                # Check if the length is longer and the chain is valid
                if length > max_length and self.valid_chain(chain):
                    max_length = length
                    new_chain = chain

        # Replace our chain if we discovered a new, valid chain longer than ours
        if new_chain:
            self.chain = new_chain
            return True
        return False
