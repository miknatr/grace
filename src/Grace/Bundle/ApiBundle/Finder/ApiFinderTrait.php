<?php

namespace Grace\Bundle\ApiBundle\Finder;

use Grace\Bundle\ApiBundle\Model\ResourceAbstract;
use Grace\Bundle\ApiBundle\Model\User;
use Grace\Bundle\CommonBundle\GraceContainer;
use Grace\ORM\ClassNameProviderInterface;
use Grace\ORM\Collection;
use Grace\ORM\FinderSql;
use Grace\SQLBuilder\SelectBuilder;
use Intertos\ApiBundle\Filter\FilterInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

trait ApiFinderTrait
{
    // trait contract
    /** @return SelectBuilder */
    abstract public function getSelectBuilder();
    /** @return ClassNameProviderInterface */
    abstract protected function getClassNameProvider();
    /** @return GraceContainer */
    abstract protected function getContainer();

    public function countCache(User $user, array $params = array())
    {
        return $this->getContainer()->getCache()->get(
            md5('count' . get_class($this) . serialize($params)),
            '5m',
            function () use ($user, $params) {
                return $this->count($user, $params);
            }
        );
    }

    public function count(User $user, array $params = array())
    {
        return $this
            ->prepareBuilder($user, $params)
            ->count()
            ->fetchResult();
    }

    /**
     * @param User $user
     * @param array $params
     * @param int $start
     * @param int $number
     * @return Collection
     */
    public function get(User $user, array $params = array(), $start = null, $number = null)
    {
        $builder = $this->prepareBuilder($user, $params);

        if (!is_null($start) and !is_null($number)) {
            $builder->limit($start, $number);
        } else {
            $builder->limit(0, 1000);
        }

        return $builder->order('id DESC')->fetchAll();
    }

    /**
     * @param User $user
     * @param array $params
     * @return SelectBuilder
     */
    protected function prepareBuilder(User $user, array $params = array())
    {
        /** @var $modelClass ResourceAbstract */
        $builder    = $this->getSelectBuilder();

        $baseClass  = $this->getClassNameProvider()->getBaseClassFromFinderClass(get_class($this));
        $modelClass = $this->getClassNameProvider()->getModelClass($baseClass);
        $case       = $modelClass::getAccessDefinition();
        $parents    = $modelClass::getParentsDefinition();


        $placeholders = array();

        $case = preg_replace('/same:([A-Za-z0-9_]+)/', 'user:$1 == resource:$1', $case);
        // @todo сделать парсинг/конфиг нормально
        $case = preg_replace('/==/', '=', $case);

        $case = preg_replace_callback(
            '/ROLE_([A-Z_]+)/',
            function ($match) use ($user) {
                return ($user->isRole($match[0]) ? 'TRUE' : 'FALSE');
            },
            $case
        );
        $case = preg_replace_callback(
            //   1                   2                3
            '/(user|resource):([A-Za-z0-9_]+)(?::([A-Za-z0-9_]+))?+/',
            function ($match) use ($user, &$placeholders, $parents, $modelClass) {
                // страшная конструкция здесь потому, что надо соблюдать порядок плейсхолдеров
                // поэтому нельзя сделать раздельный replace для юзеров и ресурсов

                /** @var $modelClass ResourceAbstract */

                if ($match[1] == 'user') {
                    if (isset($match[3])) {
                        throw new \LogicException('Неверное условие: ' . $match[0]);
                    }

                    $placeholders[] = $user->{'get' . ucfirst($match[2])}();
                    return '?q';
                }

                if (!isset($match[3])) {
                    $methodName = 'get' . ucfirst($match[2]) . 'AsSql';
                    if (method_exists($modelClass, $methodName)) {
                        list($sql, $placeholdersToAdd) = $modelClass::$methodName(FinderSql::TABLE_ALIAS);
                        $placeholders = array_merge($placeholders, $placeholdersToAdd);
                        return '(' . $sql . ')';
                    }

                    $placeholders[] = $match[2];
                    return "?f";
                } else {
                    $parentGetter = $match[2];
                    $parentField  = $match[3];

                    $resourceParentId = $parentGetter . 'Id';
                    $parentTable = $parents[$resourceParentId];

                    $placeholders[] = $parentTable;      // SELECT ?f.?f
                    $placeholders[] = $parentField;      //
                    $placeholders[] = $parentTable;      // FROM ?f
                    $placeholders[] = $parentTable;      // WHERE ?f.id = ?f.?f
                    $placeholders[] = FinderSql::TABLE_ALIAS;            //
                    $placeholders[] = $resourceParentId; //
                    return "(SELECT ?f.?f FROM ?f WHERE ?f.id = ?f.?f)";
                }
            },
            $case
        );



        $builder->sql($case, $placeholders);


        //TODO сделать получение неймспейса фильтров нормально
        $filterNamespace = str_replace('Model\\', 'Filter\\', $modelClass) . '\\';

        //TODO сделать получение папочки фильтров нормально
        $filterDirectory = __DIR__ . '/../../../../../../src/Intertos/ApiBundle/Filter/' . $baseClass;
        foreach (glob($filterDirectory . '/*.php') as $filename) {
            $class = $filterNamespace . basename($filename, '.php');
            /** @var $filter FilterInterface */
            $filter = new $class;
            $filter->prepareBuilder($builder, $user, $params);
        }


        return $builder;
    }
}
