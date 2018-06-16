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
from collections import OrderedDict


class Transaction():
    def __init__(self, fromAddress, toAddress, amount):
        self.fromAddress = fromAddress
        self.toAddress = toAddress
        self.amount = amount

    def transactionDict(self):
        return OrderedDict({"fromAddress": self.fromAddress, "toAddress": self.toAddress, "amount": self.amount})
