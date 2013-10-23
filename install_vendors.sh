#!/bin/bash

rm -fR ./vendor

git clone git://github.com/miknatr/grace-cache.git ./vendor/grace-cache
#cd ./vendor/grace-cache
#git checkout v2.1.10
#cd ../..

git clone git://github.com/miknatr/grace-dbal.git ./vendor/grace-dbal
#cd ./vendor/grace-dbal
#git checkout v2.1.10
#cd ../..

git clone git://github.com/symfony/symfony.git ./vendor/symfony
cd ./vendor/symfony
git checkout v2.1.10
cd ../..

mkdir -p ./vendor/sensio-extra-bundle/Sensio/Bundle/
git clone git://github.com/sensio/SensioFrameworkExtraBundle.git ./vendor/sensio-extra-bundle/Sensio/Bundle/FrameworkExtraBundle
cd ./vendor/sensio-extra-bundle/Sensio/Bundle/FrameworkExtraBundle
git checkout v2.1.10
cd ../../../../..

git clone git://github.com/doctrine/common.git ./vendor/doctrine-common
cd ./vendor/doctrine-common
git checkout 2.2.3
cd ../..

git clone git://github.com/Seldaek/monolog.git ./vendor/monolog
cd ./vendor/monolog
git checkout 1.2.1
cd ../..
