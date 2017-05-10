# Security design

This tool works on a "one-client-per-server" paradigm. Of course, multiple clients can access the same server, and a single client can access multiple servers, as long as every client and server share the same secret.

# Prerequisites

The following two constraints MUST be respected at all times, for the tool to work properly.

## Time synchronisation

- client and server must be time-synchronized
- maximum allowed time difference is the longevity of the TOTP-key (see below)

The `TIMESTAMP` variable represents the number of seconds since the UNIX epoch (in UTC).

## Shared secret

The client and server share a *common secret* :

- a master 4096-bit shared-secret is used as "keys to the kingdom"
- a master 64-bit salt is used to prevent rainbow-table attacks

Shared secret generation :

- is done on the client side via the `createkey` operation
- must be replicated on the server side

The user is responsible for the secure distribution of the shared-secret amongst client(s) and server(s).

In doing so, the user is advised to respect best practices, which are outside of the scope of this document.

# Description

## Data structure

The data consists of 3 sections :

- 1 "payload" section
- 1 "signature" section
- 1 "TOTP" section

## Keys used

Following keys are used :

- `SIGN_KEY` is used to sign/verify
- `CRYPT_KEY` key is used to encrypt/decrypt
- `TOTP_KEY` is used to generate TOTP tokens

## Key rotation

Key longevity per operation type :

- `SIGN_KEY` is used for `DURATION_SIGN` = 3600 seconds
- `CRYPT_KEY` is used for `DURATION_CRYPT` = 600 seconds
- `TOTP_KEY` is used for `DURATION_TOTP` = 10 seconds

## Key derivation

Variables :

- `DERIVATION_TEXT` : an ASCII-encoded contextual string
- `N` : integral number of periods of `DURATION_*` seconds since epoch, rounded down
- `T` : operation name

Preparation :

- set `T` to operation name ("SIGN", "CRYPT", or "TOTP")
- compute `N = floor(TIMESTAMP / DURATION_${T})`
- `DERIVATION_TEXT = T | N` where `|` is the concatenation operator

Key derivation method :

- follows the recommendation described in `RFC 5869`
- uses the shared secret
- used the shared salt
- uses the `DERIVATION_TEXT` as defined above

The hashing function used as HMAC's hashing function is `SHA512`, and the combination is denoted HMAC-SHA512.

# Cryptographic operations

Because the whole scheme relies on a shared-secret, requires clock synchronisation, and use a common time-based key-derivation technique, both client and server can separately compute the common ephemeral operational keys used by both sides for every operations.

## Variables

Base binary string  :

- PAYLOAD: the data being transmitted
- SIGNATURE: a binary string
- TOTP: a binary string

Computed binary strings :

- `ENCRYPTED_*` where `*` is one of the binary strings above

Ascii-encoded text strings :

- `HEX_*` where `*` is one of the binary strings above

## Computations

    TODO

### TOTP

    TODO

### Signature

The signature of the payload is computed by applying the HMAC function to `PAYLOAD` using `SIGN_KEY` :

    SIGNATURE = HMAC-SHA512(data=PAYLOAD, key=SIGN_KEY)

### Encryption

The ciphertext of the payload is computed by applying the AES256 function to `PAYLOAD` using `CRYPT_KEY` :

    ENCRYPTED_PAYLOAD = AES256(data=PAYLOAD, key=CRYPT_KEY)

### Decryption

The cleartexxt of the encrypted payload is computed by applying the AES256 function to `ENCRYPTED_PAYLOAD` using `CRYPT_KEY` :

    PAYLOAD = AES256(data=ENCRYPTED_PAYLOAD, key=CRYPT_KEY)

## Validation

    TODO

### TOTP

    TODO

### Decryption

    TODO

### Signature

    TODO

# Endpoint operations

Each of these operations are to be done on both sides, so that the client authenticates and validates the server, and vice-versa.

## Emission

Upon sending a request or a reply, do the following operations :

- computes `TOTP` using `TOTP_KEY` and `TIMESTAMP`
- computes `SIGNATURE` using `PAYLOAD` and `SIGN_KEY`
- computes `ENCRYPTED_PAYLOAD` using `CRYPT_KEY`

## Reception

On reception, do the following operations :

- computes `TOTP` using `TIMESTAMP` and `TOTP_KEY`
- validate TOTP
- dismiss the entire request if both `TOTP` are different
- computes `PAYLOAD` using `ENCRYPTED_PAYLOAD` and `CRYPT_KEY`
- validate decryption
- computes `SIGNATURE` using `PAYLOAD` and `SIGN_KEY`
- validate signature
