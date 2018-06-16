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


class Transaction():
    def __init__(self, fromAddress, toAddress, amount):
        self.fromAddress = fromAddress
        self.toAddress = toAddress
        self.amount = amount
