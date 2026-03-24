#!/bin/bash

# Exit on error
set -e

OUTPUT="dist/whmcs-customisations.zip"

echo "Cleaning old build..."
rm -f $OUTPUT

echo "Ensuring dist folder exists..."
mkdir -p dist

echo "Creating zip package..."

zip -r $OUTPUT \
  src/assets \
  src/includes \
  src/lang \
  -x "*.git*" \
  -x "*node_modules*" \
  -x "*.DS_Store"

echo "Done: $OUTPUT created 🥳"