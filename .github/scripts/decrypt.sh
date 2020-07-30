#!/bin/sh

# Decrypt credentials using the secret
echo "Decrypting Firebase credentials..."

gpg --verbose --batch --yes --decrypt --passphrase="$PRIVATE_KEY_ID" --output ./keyfile.json ./.github/credentials/keyfile.json.gpg

ls -l ./keyfile.json
