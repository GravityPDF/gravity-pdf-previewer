#!/usr/bin/env bash

if [ $# -lt 1 ]; then
	echo "usage: $0 <version> [branch]"
	exit 1
fi

VERSION=$1
BRANCH=${2-master}
PACKAGE_DIR="./tmp/package/${VERSION}"

# Create the working directory
mkdir -p ${PACKAGE_DIR}

# Get an archive of our plugin
git archive ${BRANCH} --output ${PACKAGE_DIR}/package.tar.gz
tar -zxf ${PACKAGE_DIR}/package.tar.gz --directory ${PACKAGE_DIR} && rm ${PACKAGE_DIR}/package.tar.gz

# Run Composer
composer install --quiet --no-dev  --prefer-dist --optimize-autoloader --working-dir ${PACKAGE_DIR}

# Cleanup Node JS
rm -R ${PACKAGE_DIR}/node_modules

# Cleanup additional build files
FILES=(
"${PACKAGE_DIR}/composer.json"
"${PACKAGE_DIR}/composer.lock"
"${PACKAGE_DIR}/package.json"
"${PACKAGE_DIR}/yarn.lock"
"${PACKAGE_DIR}/gulpfile.js"
)

for i in "${FILES[@]}"
do
    rm ${i}
done