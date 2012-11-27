mkdir vendor
cd vendor

rm -fR ./zf1

mkdir -p ./zf1/library/Zend
svn export http://framework.zend.com/svn/framework/standard/trunk/library/Zend/CodeGenerator ./zf1/library/Zend/CodeGenerator
svn export http://framework.zend.com/svn/framework/standard/trunk/library/Zend/Reflection ./zf1/library/Zend/Reflection
svn export http://framework.zend.com/svn/framework/standard/trunk/library/Zend/Exception.php ./zf1/library/Zend/Exception.php
svn export http://framework.zend.com/svn/framework/standard/trunk/library/Zend/Loader ./zf1/library/Zend/Loader
svn export http://framework.zend.com/svn/framework/standard/trunk/library/Zend/Loader.php ./zf1/library/Zend/Loader.php

#remove includes follow manual
#http://framework.zend.com/manual/en/performance.classloading.html#performance.classloading.striprequires.sed
cd ./zf1/library/Zend
find . -name '*.php' -not -wholename '*/Loader/Autoloader.php' \
-not -wholename '*/Application.php' -print0 | \
xargs -0 sed --regexp-extended --in-place 's/(require_once)/\/\/ \1/g'
cd ../../../
