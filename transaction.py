import json
import hashlib

from ecdsa import SigningKey, VerifyingKey, NIST256p
from hashlib import sha256

from flask import jsonify
from functools import reduce

COINBASE_AMOUNT = 50


class UnspentTxOut:
    txOutId = None
    txOutIndex = None
    address = None
    amount = None

    def __init__(self, txOutId, txOutIndex, address, amount):
        self.txOutId = txOutId
        self.txOutIndex = txOutIndex
        self.address = address
        self.amount = amount


class TxIn:
    txOut = None

    def __init__(self, txOut = None):
        if txOut is None :
            self.txOut = TxOut("", "", "")
            return
        self.txOut = txOut

class TxOut:

    id = None
    address = None
    amount = None

    def __init__(self, id, address, amount):
        self.id = id
        self.address = address
        self.amount = amount

class Transaction:

    def __init__(self):
        self.id = None
        self.txIns = []
        self.txOuts = []

    def toJSON(self):

        transactionJSON = {
            "id": self.id,
            "txIns": list(),
            "txOuts": list()
        }


        for txIn in self.txIns:
            transactionJSON['txIns'].append({
                "id": txIn.txOut.id,
                "amount": txIn.txOut.amount,
                "address": txIn.txOut.address,
            })

        for txOut in self.txOuts:
            transactionJSON['txOuts'].append({
                "id": txOut.id,
                "amount": txOut.amount,
                "address": txOut.address
            })

        return transactionJSON

def getTransactionId(transaction):
    txInContent = []
    txOutContent = []

    for txIn in transaction.txIns:
        txInContent.append(txIn.txOut.id + txIn.txOut.address + str(txIn.txOut.amount))

    txInContent = reduce((lambda x, y: x + y), txInContent)

    for txOut in transaction.txOuts:
        txOutContent.append(txOut.address + str(txOut.amount))

    txOutContent = reduce((lambda x, y: x + y), txOutContent)

    string = txInContent + txOutContent

    return hashlib.sha256(string.encode()).hexdigest()

def generateTxOutID(txIns, recieverAddress, amount):
    txInContent = []

    for txIn in txIns:
        print (txIn)
        txInContent.append(txIn.txOut.id + txIn.txOut.address + str(txIn.txOut.amount))

    txInContent = reduce((lambda x, y: x + y), txInContent)

    string = txInContent + recieverAddress + str(amount)
    return hashlib.sha256(string.encode()).hexdigest()

def validateTransaction(transaction, aUnspentTxOuts):
    if getTransactionId(transaction) != transaction.id:
        return False

    hasValidTxIns = []
    totalTxInValues = []

    for txIn in transaction.txIns:
        hasValidTxIns.append(validateTxIn(txIn, transaction, aUnspentTxOuts))
        totalTxInValues.append(getTxInAmount(txIn, aUnspentTxOuts))

    hasValidTxIns = reduce((lambda x, y: x and y), hasValidTxIns)
    totalTxInValues = reduce((lambda x, y: x + y), totalTxInValues)

    if not hasValidTxIns:
        return False

    totalTxOutValue = []

    for txOut in transaction.txOuts:
        totalTxOutValue.append(txOut.amount)

    totalTxOutValue = reduce((lambda x, y: x + y), totalTxOutValue)

    if totalTxOutValue != totalTxInValues:
        return False

    return True


def validateBlockTransactions(aTransactions, aUnspentTxOut, blockIndex):
    coinbaseTx = aTransactions[0]

    if not validateCoinbaseTx(coinbaseTx, blockIndex):
        return False

    return False


def validateCoinbaseTx(transaction, blockIndex):
    if transaction == None:
        return False

    if getTransactionId(transaction) != transaction.id:
        return False

    if transaction.txIns.length != 1:
        return False

    if transaction.txIns[0].txOutIndex != blockIndex:
        return False

    if transaction.txOuts.length != 1:
        return False


def getTxInAmount(txIn, aUnspentTxOuts):
    return 0


def findUnspentTxOut(transactionId, index, aUnspentTxOuts):
    for uTxO in aUnspentTxOuts:
        if uTxO.txOutId == transactionId and uTxO.txOutIndex == index:
            return uTxO

    return None


def getCoinbaseTransaction(address, blockIndex):
    transaction = Transaction()
    txIn = TxIn()

    transaction.txIns.append(txIn)
    transaction.txOuts.append(TxOut(str(blockIndex), address, COINBASE_AMOUNT))
    transaction.id = getTransactionId(transaction)

    return transaction


def signTxIn(transaction, txInIndex, privateKey, aUnspentTxOuts):
    txIn = transaction.txIns[txInIndex]

    dataToSign = transaction.id
    referencedUnspentTxOut = findUnspentTxOut(txIn.txOutId, txIn.txOutIndex, aUnspentTxOuts)

    if referencedUnspentTxOut is None:
        return None

    referencedAddress = referencedUnspentTxOut.address

    """
    if getPublicKey(privateKey) != referencedAddress:
        return None
    """

    pk = SigningKey.from_string(bytes.fromhex(privateKey), curve=NIST256p)

    return pk.sign(dataToSign.encode()).hex()


def updateUnspentTxOuts(aTransactions, aUnspentTxOuts):
    # newUnspentTxOuts = [map(lambda x: map(lambda i, y: UnspentTxOut(x.id, i, y.address, y.amount), enumerate(x.txOuts)), aTransactions)]

    newUnspentTxOuts = []
    consumedTxOuts = []
    resultingUnspentTxOuts = []

    for tx in aTransactions:
        for index, txO in enumerate(tx.txOuts):
            newUnspentTxOuts.append(UnspentTxOut(tx.id, index, txO.address, txO.amount))

        for txI in tx.txIns:
            consumedTxOuts.append(UnspentTxOut(txI.txOutId, txI.txOutIndex, '', 0))

    # newUnspentTxOuts = [reduce(lambda x, y: x + y, newUnspentTxOuts)]

    if aUnspentTxOuts:
        resultingUnspentTxOuts = list(filter(lambda x: not findUnspentTxOut(x.txOutId, x.txOutIndex, consumedTxOuts), aUnspentTxOuts))

    resultingUnspentTxOuts += newUnspentTxOuts

    return resultingUnspentTxOuts


def processTransaction(aTransactions, aUnspentTxOuts, blockIndex):
    """
    if not isValidTransactionsStructure(aTransactions):
        return None
    if not validateBlockTransactions(aTransactions, aUnspentTxOuts, blockIndex):
        return None
    """
    return updateUnspentTxOuts(aTransactions, aUnspentTxOuts)


def getPublicKey(aPrivateKey):
    return None


def isValidTxInStructure(txIn):
    return False


def isValidTxOutStructutre(txOut):
    return False


def isValidTransactionsStructure(transactions):
    return False


def isValidTransactionStructure(transaction):
    return False


def isValidAddress(address):
    return False