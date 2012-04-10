Grace ORM
=============================

## Конвенция

покрыто
12/17


## TODO

Вынести EventDispatcher в отдельный пакет и использовать его в DBAL (логгирование запросов и тд)

Некий CRUD модуль лучше чем SQLBuilder для операций по айди (кэшируется, заменяется на NoSQL и тд)


## Стандарт кодирования

Имена

1. Все имена, кроме имен костант пишутся в CamelCase

2. Имена классов и интерфейсов начинаются с большой буквы

3. Имена переменных, свойств, методов, функций начинаются с маленькой буквы

4. Дополняющие слова (Interface, Abstract, Factory etc.) должны быть в конце имени класса (e.g. MapperInterface)

5. Имена методов и функций должны начинаться с глагола (getSomeValue)

6. Имена констатн пишуться в верхнем регистре, подчеркивание как разделит (SOME_CONSTANT)

7. Сокращения не допускаются за исплючение общепринятых аббревиатур (DB, SQL, ORM)


Оформление

1. Закрывающая фигурная скобка всегда ставиться на той же строке (if {)

2. Закрывающий php тэг не использутся в файлых содержащих только php-код

3. ...



## Exceptions

Каждый подпакет (DBAL, SQLBuilder etc.) должен иметь свои классы исключений.

Необходима возможность:

1. наследовать разные исключения от разных базовых исключений (например BadMethodCallException)

2. ловить исключения по подпакетам (например все исплючения DBAL)

Для этого в каждом подпакете определяется интерфейс ExceptionInterface (пример Grace\DBAL\ExceptionInterface).

Все остальные исключения подпакета его реализуют и наследуют один из базовых типов исключений включая Exception.



## SQLBuilder

1. При составлении сложных запросов из различных частей (where, group, having 
части sql запроса), нужно делать один пробел вначале выражения и осталять
без пробела после. Пример:

```php
$this->orderSql = ' ORDER BY ' . $sql;
```

2. ...


## CodeGenerator

Имеются две папки include_generatet и include.

Обе будут в include_path или аналоге для auto-loader'а.

В первую пишутся классы созданые генератором.

Во второй находятся классы, созданые руками (однако она используется генератором для некоторого анализа перед генерацией)


Описано на примере сущности заказа Order:

Абстрактные классы Record (домен)

1. Для каждой сущности в yaml создает и записывает абстрактный класс.

2. В абстрактных классах есть сеттеры, геттеры на поля в массиве fields (свойство Record).

```php
<?php
public function setName($name) {
    $this->fields['name'] = $name;
    return $this;
}
public function getName($name) {
    return $this->fields['name'];
}
```
3. Абстрактные поля унаследованы от Record (OrderAbstract extends Record)


Конкретные классы Record (домен)

1. Создает и записывает по конкретному классу унаследованому от абстрактного (Order extends OrderAbstract)

2. Пишутся в ту же папку для генерации, если не создан класс в папке с классами написаными руками.


Классы коллекций для каждой сущности

1. Запоминает все методы абстрастного класса и написаного руками наследника (Record).

2. Генерирует абстрактный и конкретный классы OrderCollection extends OrderCollectionAbstract extends Grace\ORM\Collection.

3. В созданном классе OrderCollectionAbstract для каждого метода Record (кроме начинающихся с get) создает метод с такой же сигнатурой и циклом foreach внутри:

```php
<?php
public closeOrder($price, $notifyClient = false) {
    foreach ($this->items as $item) {
        $item->closeOrder($price, $notifyClient);
    }
}
```


Класс-наследник класса Manager

1. Это только один класс для всех сущностей.

2. Для каждой сущности в нем есть метод получения Finder'а - getOrderFinder

```php
<?php
/**
    * @return OrderFinder
    */
public function getOrderFinder() {
    return $this->getFinder('Order');
}
```

3. Php-doc с тегом @return обязателен (для автодополнения)


Mapper'ы

1. Так же, как и выше, генерируются два mapper'а на каждую сущность (OrderMapper и OrderMapperAbstract)

```php
<?php
OrderMapper extends OrderMapperAbstract {}
OrderMapperAbstract extends \Grace\ORM\Mapper {
    protected $fields = array(
        'id',
        'name',
        'phone',
        ...
    );
}
```


Finder'ы

1. Так же, как и выше, генерируются два Finder'а на каждую сущность (OrderFinder и OrderFinderAbstract)

```php
<?php
OrderFinder extends OrderFinderAbstract {}
/**
 * @method Order getById($id)
 * @method Order fetchOne()
 * @method OrderCollection fetchAll()
 */
OrderFinderAbstract extends \Grace\ORM\Finder {}
```