rm -fR ./vendors
mkdir -p ./vendors/symfony/src/Symfony/Component/
git clone http://github.com/symfony/ClassLoader.git ./vendors/symfony/src/Symfony/Component/ClassLoader
rm -fR ./vendors/symfony/src/Symfony/Component/ClassLoader/.git
git clone http://github.com/symfony/Yaml ./vendors/symfony/src/Symfony/Component/Yaml
rm -fR ./vendors/symfony/src/Symfony/Component/Yaml/.git
