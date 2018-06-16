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
import binascii
from collections import OrderedDict

import time
import requests
import Crypto
import Crypto.Random
from Crypto.Hash import SHA
from Crypto.PublicKey import RSA
from Crypto.Signature import PKCS1_v1_5
from uuid import uuid4
import json
from flask_cors import CORS


class BlockChain():
    def __init__(self, MINING_SENDER="Network", MINING_REWARD=1, MINING_DIFFICULTY=2):
        genesisBlock = Block(0, time.time(), [], "0")
        self.chain = [genesisBlock]
        self.difficulty = MINING_DIFFICULTY
        self.pendingTransactions = []
        self.miningReward = MINING_REWARD
        self.miningSender = MINING_SENDER
        self.nodes = []
        # Generate random number to be used as node_id
        self.node_id = str(uuid4()).replace('-', '')

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
        """
        Add a new node to the list of nodes
        """
        # Checking node_url has valid format
        parsed_url = urlparse(address)
        if parsed_url.netloc:
            self.nodes += [parsed_url.netloc]
        elif parsed_url.path:
            # Accepts an URL without scheme like '192.168.0.5:5000'.
            self.nodes += [parsed_url.path]
        else:
            raise ValueError('Invalid URL')

    def verifyTransactionSignature(self, sender_address, signature, transaction):
        """
        Check that the provided signature corresponds to transaction
        signed by the public key (sender_address)
        """
        try:
            public_key = RSA.importKey(binascii.unhexlify(sender_address))
            verifier = PKCS1_v1_5.new(public_key)
            h = SHA.new(str(transaction.transactionDict()).encode('utf8'))
            return verifier.verify(h, binascii.unhexlify(signature))
        except ValueError:
            return False
    def createTransaction(self, sender_address, recipient_address, value, signature):
        transaction = Transaction(sender_address, recipient_address, value)
        # Reward for mining a block
        if sender_address == self.miningSender:
            self.pendingTransactions.append(transaction)
            return self.getLastBlock().index + 1
        # Manages transactions from wallet to another wallet
        else:
            transaction_verification = self.verifyTransactionSignature(
                sender_address, signature, transaction)
            if transaction_verification:
                self.pendingTransactions.append(transaction)
                return self.getLastBlock().index + 1
            else:
                return False

    def minePendingTransactions(self):
        block = Block(self.getLastBlock().index + 1, time.time(),
                      self.pendingTransactions, self.getLastBlock().hash)
        block.mineBlock(self.difficulty)
        self.chain += [block]
        # only send reward after the user has mined the coin , doing it before could have him
        # cancel the mining and still earn coins!
        self.pendingTransactions = []
        self.createTransaction(
            self.miningSender, self.node_id, self.miningReward, "")
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

    def valid_chain(self, chain):
        """
        Determine if a given blockchain is valid
        :param chain: A blockchain
        :return: True if valid, False if not
        """

        last_block = chain[0]
        for block in chain[1:]:
            # Check that the hash of the block is correct
            last_block_hash = last_block['hash']
            if block['previous_hash'] != last_block_hash:
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
            self.chain = self.toObject(new_chain)
            return True
        return False

    def toObject(self, chain):
        output = []
        for block in chain:
            blockTransactions = [Transaction(
                trans["fromAddress"], trans["toAddress"], trans["amount"]) for trans in block["transactions"]]
            output += [Block(block['index'], block['timestamp'],
                             blockTransactions, block['previous_hash'], block['nonce'])]
        return output
