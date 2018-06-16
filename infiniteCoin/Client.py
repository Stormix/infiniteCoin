from collections import OrderedDict

import binascii

import Crypto
import Crypto.Random
from Crypto.Hash import SHA
from Crypto.PublicKey import RSA
from Crypto.Signature import PKCS1_v1_5

import requests
from flask import Flask, jsonify, request, render_template


class Transaction:

    def __init__(self, fromAddress, fromPrivKey, toAddress, amount):
        self.fromAddress = fromAddress
        self.fromPrivKey = fromPrivKey
        self.toAddress = toAddress
        self.amount = amount

    def __getattr__(self, attr):
        return self.data[attr]

    def transactionDict(self):
        return OrderedDict({'fromAddress': self.fromAddress,
                            'toAddress': self.toAddress,
                            'amount': self.amount})

    def signTransaction(self):
        """
        Sign transaction with private key
        """
        private_key = RSA.importKey(
            binascii.unhexlify(self.fromPrivKey))
        signer = PKCS1_v1_5.new(private_key)
        h = SHA.new(str(self.transactionDict()).encode('utf8'))
        return binascii.hexlify(signer.sign(h)).decode('ascii')


app = Flask(__name__)


@app.route('/')
def index():
    return "Ok"


@app.route('/wallet/new', methods=['GET'])
def new_wallet():
    random_gen = Crypto.Random.new().read
    private_key = RSA.generate(1024, random_gen)
    public_key = private_key.publickey()
    response = {
        'private_key': binascii.hexlify(private_key.exportKey(format='DER')).decode('ascii'),
        'public_key': binascii.hexlify(public_key.exportKey(format='DER')).decode('ascii')
    }

    return jsonify(response), 200


@app.route('/generate/transaction', methods=['POST'])
def generate_transaction():

    fromAddress = request.form['fromAddress']
    fromPrivKey = request.form['fromPrivKey']
    toAddress = request.form['toAddress']
    amount = request.form['amount']

    transaction = Transaction(
        fromAddress, fromPrivKey, toAddress, amount)

    response = {'transaction': transaction.transactionDict(
    ), 'signature': transaction.signTransaction()}

    return jsonify(response), 200


if __name__ == '__main__':
    from argparse import ArgumentParser

    parser = ArgumentParser()
    parser.add_argument('-p', '--port', default=5001,
                        type=int, help='port to listen on')
    args = parser.parse_args()
    port = args.port

    app.run(host='127.0.0.1', port=port)
