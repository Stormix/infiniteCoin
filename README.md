# infiniteCoin

Personal take on Crypto Currency 💰 &amp; Blockchain ⛓ - EXPERIMENTAL!

Although heavily inspired by the "Let’s Make the Tiniest Blockchain Bigger" article, this repo was inspired by [Satoshi Nakamoto's blockchain whitepaper](https://bitcoin.org/bitcoin.pdf)
and [Aunyks's tiny blockchain implementation in Python](https://medium.com/crypto-currently/lets-make-the-tiniest-blockchain-bigger-ac360a328f4d).

#### Why PHP ?
Because why the f*** not ? PHP IS ❤️

---

### Implementation TO BE EXPLAINED ! IGNORE THIS !

#### Blocks!
A block is an object that contains, in this implementation, a series of
transactional data. All previous transactions are available in a ledger called
the blockchain. A blockchain is a sequence of blocks agreed upon by a network of
workers. Thus the integrity of the whole system can be verified by using a
mathematical function in which the answer is difficult to obtain but easy to
check. This mathematical check can be performed on each block all the way back
to the first block. The validity of the blocks is determined by using a Proof of
Work algorithm. More on that later.

#### Nodes
Upon startup, nodes check for peers and their respective blockchains. If no
peers are found, an initial block is created. A node can process a transaction,
mine, and return the blockchain upon request.

#### Consensus Algorithm
A blockchain is decided to be the master chain by evaluating lengths of other
nodes' blockchains. The longest chain is decided to be the master blockchain. I
plan to implement the consensus algorithm outlined in Nakamoto's paper which
seeks the longest chain with the greatest proof of work.

#### Mining
Mining from a node is a transactional relationship where the miner is rewarded
for completing a very simple, yet verifiable mathematical task. Similar to the
Proof of Work outlined in Nakamoto's whitepaper, this implementation requires a
hash be created with a specified number of leading zero bits. This hash is a
result of passing a string, made by concatenating a Block's previous hash and a
nonce (the constant the miner is solving for), through the SHA256 hashing
function. Once the nonce is solved for, the miner is rewarded and a new block is
generated.  
