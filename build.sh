#!/usr/bin/env bash

showHelp()
{
   echo ""
   echo "Usage: $0 -c master -p packageName"
   echo -e "\t-p The name of your package in which it should be zipped "
   exit 1 # Exit script after printing help
}

while getopts "p:" opt
do
   case "$opt" in
      p ) package="$OPTARG" ;;
      ? ) showHelp ;; # Print helpFunction in case parameter is non-existent
   esac
done

if [ -z ${package} ]; then
    package=mmeesSlackNotifier
fi

# Remove old release
rm -rf build ${package}-*.zip

# Build new release
cd ../../../
bin/console plugin:refresh
bin/console plugin:install --activate --clearCache ${package}
./psh.phar storefront:build
cd custom/plugins/${package}

mkdir -p build/${package}
cp composer.json build/${package}/composer.json
cp -r src build/${package}/src

( find ./build -type d -name ".git" && find ./build -name ".gitignore" && find ./build -name ".gitmodules"  && find ./build -name "build.sh" ) | xargs rm -r
cd build
zip -r ${package}-build.zip ${package}
mv ${package}-build.zip ../${package}-build.zip
