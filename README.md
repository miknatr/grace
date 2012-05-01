# Grace ORM

## Features

* Event dispatcher is provided inside every record object (any event dispatcher acceptable)

* Generation of abstract records, mappers, finders and collections (getters, setters, cycles in collections etc.)

* Generation doesn't touch you manual written code.

* Provides saving objects in different db at the same time (and caching if necessary).

* Provides working with collection as record object.


## Structure

Package contains 4 stable layers

* DBAL - db abstract layer, provides sql access to databases

* SQLBuilder - helps to write sql queries, uses DBAL

* CRUD - provides crud access to record in sql-db or nosql storage (memcache, redis). Or you can combine sql and
nosql storages to cache access to db. Uses DBAL and SQLBuilder in case with sql-db.

* ORM - provides object to db mapping. Depends on all layers above.


## Examples - work with DBAL and SQLBuilder

Work with DBAL:

```php
//Creating new connection
$connection = new \Grace\DBAL\MysqliConnection('localhost', 3306, 'root', 'password', 'your_db');

//Update query - will return bool as like as delete query, insert query and etc.
$connection->execute('UPDATE Post SET published=?q WHERE id=?q', array(1, 123));

//Select query - will return MysqliResult which has methods to fetch information
//Fetching all posts from Post table
$r = $connection
    ->execute('SELECT * FROM Post')
    ->fetchAll();

//Fetching one post
$r = $connection
    ->execute('SELECT * FROM Post WHERE id=?q', array(123))
    ->fetchOne();

//Fetching column with post titles
$r = $connection
    ->execute('SELECT title FROM Post')
    ->fetchColumn();

//Fetching one value result
$r = $connection
    ->execute('SELECT COUNT(id) FROM Post')
    ->fetchResult();
```

Do things above with SQLBuilder:

```php
$connection
    ->getSQLBuilder()
    ->update('Post')
    ->values(array('published' => 1))
    ->eq('id', 123)
    ->execute();

$r = $connection
    ->getSQLBuilder()
    ->select('Post')
    ->fetchAll();

$r = $connection
    ->getSQLBuilder()
    ->select('Post')
    ->eq('id', 123)
    ->fetchOne();

$r = $connection
    ->getSQLBuilder()
    ->select('Post')
    ->fields('title')
    ->fetchColumn();

$r = $connection
    ->getSQLBuilder()
    ->select('Post')
    ->fields('COUNT("id")')
    ->fetchResult();
```

Yes, SQLBuilder takes more space but in some cases it provides more flexible syntax and gets auto-completion in your
IDE


## ORM layer

### Configuration

It needs some preparations:

Create config file for your models:

```yaml
namespace:
  record: Your\AppNamespace\Model
  finder: Your\AppNamespace\Finder
  mapper: Your\AppNamespace\Mapper
  collection: Your\AppNamespace\Collection
models:
  User:
    properties:
      id:
        dbtype: 'int(10) unsigned'
      name:
        dbtype: 'varchar(255)'
  Post:
    properties:
      id:
        dbtype: 'int(10) unsigned'
      userId:
        dbtype: 'int(10) unsigned'
      title:
        dbtype: 'varchar(255)'
      text:
        dbtype: 'text'
      published:
        dbtype: 'tinyint(1)'
```

TODO описать скрипты из ямла в базу и абстрактный классы и параметры для всего этого


### Operation by id:

```php
//getting by id
$company = $this
    ->getOrm()
    ->getCompanyFinder()
    ->getById(123);

//creation
$this
    ->getOrm()
    ->getCompanyFinder()
    ->create()
    ->setName('Company One')
    ->setEmail('qwe@asd.zxc')
    ->insert();
//or
$this
    ->getOrm()
    ->getCompanyFinder()
    ->create()
    ->edit(array(
        'name' => 'Company One',
        'email' => 'qwe@asd.zxc',
    ))
    ->insert();


//editing
$this
    ->getOrm()
    ->getCompanyFinder()
    ->getById(123)
    ->setName('Company One')
    ->setEmail('qwe@asd.zxc')
    ->save();
//or
$this
    ->getOrm()
    ->getCompanyFinder()
    ->getById(123)
    ->edit(array(
        'name' => 'Company One',
        'email' => 'qwe@asd.zxc',
    ))
    ->save();

//deleting
$this
    ->getOrm()
    ->getCompanyFinder()
    ->getById(123)
    ->delete();

//save all changes
$this
    ->getOrm()
    ->commit();
```


### Collections

You can define your own selects from db in collection class

```php
//CompanyCollection.php
//...
public function getNewActiveCompanies()
{
    return $this
        ->getSQLBuilder()
        ->eq('isPublic', 1)
        ->gt('addedAt', 'NOW() - INTERVAL 10 DAY')
        ->fetchAll();
}
//...
```

After that you can get this collection and make all operations with it as one record

```php
$companies = $this
    ->getOrm()
    ->getCompanyFinder()
    ->getNewActiveCompanies();

//updates
$companies
    ->setName('Company One')
    ->setEmail('qwe@asd.zxc')
    ->save();
//or alternate syntax
$companies
    ->edit(array(
        'name' => 'Company One',
        'email' => 'qwe@asd.zxc',
    ))
    ->save();

//deletion all collection
$companies->delete();

//or calls some specific methods of your records
//collection calls same method for every record object inside
$companies->deactivate();
$companies->setType('gold', $periodInDays = 30);

//don't forget to commit changes
$this
    ->getOrm()
    ->commit();
```