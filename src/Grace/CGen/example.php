<?php
namespace Grace\CGen;
## CodeGenerator

#* Имеются две папки include_generatet и include.
#* Обе будут в include_path или аналоге для auto-loader'а.
#* В первую пишутся классы созданые генератором.
#* Во второй находятся классы, созданые руками (однако она используется генератором для некоторого анализа перед генерацией)


#Описано на примере сущности заказа Order:

### Абстрактные классы Record (домен)

#* Для каждой сущности в yaml создает и записывает абстрактный класс.
#* В абстрактных классах есть сеттеры, геттеры на поля в массиве fields (свойство Record).

$dirYaml = "./yaml"; //каталог с yml-файлами
$classesDir = "./classes"; //каталог, куда сохранятся все сгенерированные классы
$className = "someClass"; // Имя класса (2 уровень вложенности). Если значение переменной "*", то обрабатывает все имена.

$abstractGenerator = new DefaultAbstractClassGenerator($dirYaml, $classesDir, $className);
$abstractGenerator->generate();//return true if no errors
$abstractGenerator = null;


### Конкретные классы Record (домен)
#* Создает и записывает по конкретному классу унаследованому от абстрактного (Order extends OrderAbstract)
#* Пишутся в ту же папку для генерации, если не создан класс в папке с классами написаными руками.


$dirYaml = "./yaml"; //каталог с yml-файлами
$classesDir = "./classes"; //каталог, куда сохранятся все сгенерированные классы
$className = "someClass"; // Имя класса (2 уровень вложенности). Если значение переменной "*", то обрабатывает все имена.

$concreteGenerator = new DefaultConcreteClassGenerator($dirYaml, $classesDir, $className);
$concreteGenerator->generate();
$concreteGenerator = null;



### Классы коллекций для каждой сущности

#* Запоминает все методы абстрастного класса и написаного руками наследника (Record).
#* Генерирует абстрактный и конкретный классы OrderCollection extends OrderCollectionAbstract extends Grace\ORM\Collection.
#* В созданном классе OrderCollectionAbstract для каждого метода Record (кроме начинающихся с get) создает метод с такой же сигнатурой и циклом foreach внутри:

#```php
#<?php
#public closeOrder($price, $notifyClient = false) {
#    foreach ($this as $item) {
 #       $item->closeOrder($price, $notifyClient);
 #   }
#}
#```
$className = "testClass"; //имя класса, который мы обозреваем и из которого (и предка которого) собираем новый класс (два новых класса - пустого потомка и абстрактный класс предка)
$outputDir = "./forClasses"; //директория, куда пишем классы
$additionalClass = "Fooclass";// дополнительный клас, для extended
$classGenerator = new DefaultCollectionClassGenerator($classname, $outputDir, $additionalClass);
/*
 * alternate:
 * $classGenerator->getClassBody(); // gets contents of generated class
 * $classGenerator->getParentClassBody(); // gets contents of parent of generated class
 */
$classGenerator->generate();
$classGenerator = null;

### Класс-наследник класса Manager

#* Это только один класс для всех сущностей.
#* Для каждой сущности в нем есть метод получения Finder'а - getOrderFinder

#```php
#<?php
#/**
#    * @return OrderFinder
#    */
#public function getOrderFinder() {
#    return $this->getFinder('Order');
#}
#```

#* Php-doc с тегом @return обязателен (для автодополнения)


### Mapper'ы

#* Так же, как и выше, генерируются два mapper'а на каждую сущность (OrderMapper и OrderMapperAbstract)

#```php
#<?php
#OrderMapper extends OrderMapperAbstract {}
#OrderMapperAbstract extends \Grace\ORM\Mapper {
#    protected $fields = array(
#        'id',
#        'name',
#        'phone',
#        ...
#    );
#}
#```


### Finder'ы

#* Так же, как и выше, генерируются два Finder'а на каждую сущность (OrderFinder и OrderFinderAbstract)
#
#```php
#<?php
#OrderFinder extends OrderFinderAbstract {}
/**
 * @method Order getById($id)
 * @method Order fetchOne()
 * @method OrderCollection fetchAll()
 */
#OrderFinderAbstract extends \Grace\ORM\Finder {}
#```