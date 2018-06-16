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

import json
import requests
from flask import Flask, jsonify, request
import infiniteCoin
import sys

BlockChain = infiniteCoin.BlockChain()
miner_address = "address"

node = Flask(__name__)


@node.route("/")
def hello():
    return "Ok"


@node.route("/mine")
def mine():
    # We run the proof of work algorithm to get the next proof...
    print('Starting the miner...')
    block = BlockChain.minePendingTransactions(miner_address)
    print('A coin will be added to current balance of ' +
          miner_address + ':'+str(BlockChain.getBalanceOfAddress(miner_address)))
    return jsonify({
        'message': "New Block Forged",
        'index': block.index,
        'transactions': block.transactionsJson(),
        'proof': block.nonce,
        'previous_hash': block.previous_hash
    })


@node.route('/transactions/new', methods=['POST'])
def new():
    data = dict(request.form)
    required = ['from', 'to', 'amount']
    if not all(k in data.keys() for k in required):
        return 'Missing values', 400
    # Create a new Transaction
    index = BlockChain.createTransaction(infiniteCoin.Transaction(
        data['from'][0], data['to'][0], data['amount'][0]))

    response = {'message': f'Transaction will be added to Block {index}'}
    return jsonify(response), 201


@node.route('/blocks')
def blocks():
    response = {
        'chain': BlockChain.chainDict(),
        'length': len(BlockChain.chain),
    }
    return jsonify(response), 200


@node.route('/nodes/register', methods=['POST'])
def register():
    data = dict(request.form)
    required = ['nodes']
    if not all(k in data.keys() for k in required):
        return 'Missing values', 400
    nodes = data['nodes'][0].split(",")
    for node in nodes:
        if not node in BlockChain.nodes:
            BlockChain.registerNode("http://"+node)
    response = {
        'message': 'New nodes have been added',
        'total_nodes': list(BlockChain.nodes),
    }
    return jsonify(response), 201


@node.route('/nodes/resolve')
def consensus():
    replaced = BlockChain.resolveConflicts()
    if replaced:
        response = {
            'message': 'Our chain was replaced',
            'new_chain': BlockChain.chainDict()
        }
    else:
        response = {
            'message': 'Our chain is authoritative',
            'chain': BlockChain.chainDict()
        }

    return jsonify(response), 200


if __name__ == '__main__':
    port = int(sys.argv[1])
    node.run(port=port, debug=True)
