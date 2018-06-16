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

MINING_SENDER = "THE BLOCKCHAIN"
MINING_REWARD = 1
MINING_DIFFICULTY = 2
BlockChain = infiniteCoin.BlockChain(
    MINING_SENDER, MINING_REWARD, MINING_DIFFICULTY)

node = Flask(__name__)


@node.route("/")
def hello():
    return "Ok"


@node.route("/mine")
def mine():
    # We run the proof of work algorithm to get the next proof...
    print('Starting the miner...')
    block = BlockChain.minePendingTransactions()
    return jsonify({
        'message': "New Block Forged",
        'index': block.index,
        'transactions': block.transactionsJson(),
        'proof': block.nonce,
        'previous_hash': block.previous_hash
    }), 200


@node.route('/transactions/new', methods=['POST'])
def new():
    data = dict(request.form)
    required = ['from', 'to', 'amount', 'signature']
    if not all(k in data.keys() for k in required):
        return 'Missing values', 400
    # Create a new Transaction
    index = BlockChain.createTransaction(
        data['from'][0], data['to'][0], data['amount'][0], data['signature'][0])

    if index == False:
        response = {'message': 'Invalid Transaction!'}
        return jsonify(response), 406
    else:
        response = {
            'message': 'Transaction will be added to Block ' + str(index)}
        return jsonify(response), 201


@node.route('/transactions/get', methods=['GET'])
def get_transactions():
    # Get transactions from transactions pool
    transactions = BlockChain.pendingTransactions
    response = {'transactions': transactions}
    return jsonify(response), 200


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
    nodes = data['nodes'][0].replace(" ", "").split(",")
    if nodes is None:
        return "Error: Please supply a valid list of nodes", 400
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


@node.route('/nodes/get', methods=['GET'])
def get_nodes():
    nodes = list(BlockChain.nodes)
    response = {'nodes': nodes}
    return jsonify(response), 200


if __name__ == '__main__':
    from argparse import ArgumentParser

    parser = ArgumentParser()
    parser.add_argument('-p', '--port', default=5000,
                        type=int, help='port to listen on')
    args = parser.parse_args()
    port = args.port
    node.run(port=port, debug=True)
