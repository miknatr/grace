rm -fR ./vendor
mkdir -p ./vendor/symfony/src/Symfony/Component/
git clone http://github.com/symfony/ClassLoader.git ./vendor/symfony/src/Symfony/Component/ClassLoader
rm -fR ./vendor/symfony/src/Symfony/Component/ClassLoader/.git
git clone http://github.com/symfony/Yaml ./vendor/symfony/src/Symfony/Component/Yaml
rm -fR ./vendor/symfony/src/Symfony/Component/Yaml/.git

#!/bin/sh
[ ! -e vendor ] && mkdir vendor
cd vendor

rm -fR ./zf1

mkdir -p ./zf1/library/Zend
svn -q export http://framework.zend.com/svn/framework/standard/trunk/library/Zend/CodeGenerator ./zf1/library/Zend/CodeGenerator
svn -q export http://framework.zend.com/svn/framework/standard/trunk/library/Zend/Reflection ./zf1/library/Zend/Reflection
svn -q export http://framework.zend.com/svn/framework/standard/trunk/library/Zend/Exception.php ./zf1/library/Zend/Exception.php
svn -q export http://framework.zend.com/svn/framework/standard/trunk/library/Zend/Loader ./zf1/library/Zend/Loader
svn -q export http://framework.zend.com/svn/framework/standard/trunk/library/Zend/Loader.php ./zf1/library/Zend/Loader.php

#remove includes follow manual
#http://framework.zend.com/manual/en/performance.classloading.html#performance.classloading.striprequires.sed
cd ./zf1/library/Zend
find . -name '*.php' -not -wholename '*/Loader/Autoloader.php' \
-not -wholename '*/Application.php' -print0 | \
xargs -0 sed --regexp-extended --in-place 's/(require_once)/\/\/ \1/g'
cd ../../../
