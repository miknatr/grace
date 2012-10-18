cd vendor

rm -fR ./zf1

mkdir ./zf1
mkdir ./zf1/library
svn export http://framework.zend.com/svn/framework/standard/trunk/library/Zend ./zf1/library/Zend

#remove includes follow manual
#http://framework.zend.com/manual/en/performance.classloading.html#performance.classloading.striprequires.sed

cd ./zf1/library/Zend
rm -fR `ls | grep -v CodeGenerator`
find . -name '*.php' -not -wholename '*/Loader/Autoloader.php' \
-not -wholename '*/Application.php' -print0 | \
xargs -0 sed --regexp-extended --in-place 's/(require_once)/\/\/ \1/g'
cd ../../../
