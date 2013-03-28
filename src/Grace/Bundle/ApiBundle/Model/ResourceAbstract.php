<?php

namespace Grace\Bundle\ApiBundle\Model;

use Grace\Bundle\ApiBundle\Type\ApiFieldObjectInterface;
use Grace\ORM\Collection;
use Grace\ORM\Record;
use Grace\Bundle\ApiBundle\Model\User;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Класс для управления доступом на уровне полей модели
 * Наследует Grace\ORM\Record и от него должны наследоваться абстрактные классы модели
 * Наследование для абстрактных классов задается в конфиге models.yml через параметр extends
 *
 * Абстрактные классы так же должны содержать статические поля editablies и visiblies, где для
 * каждого поля задан массив возможных привилегий,
 * либо полный доступ для всех - true,
 * либо полный запрет для всех - false.
 *
 * Для генерации полей editablies и visiblies используется AclGraceCommandPlugin
 *
 * @method \Grace\Bundle\CommonBundle\GraceContainer getContainer()
 */
abstract class ResourceAbstract extends Record implements ResourceInterface
{
    // ПЕРЕОПРЕДЕЛЯЕМЫЕ ПОЛЯ

    /**
     * вызывается в конструкторе объекта после присвоения начального списка полей из массива
     * может использоваться для инициализации каких-либо сложных полей, если нельзя
     */
    protected function initConstructor()
    {
        ;
    }
    /**
     * метод вызывается при создании объекта
     * должен использоваться только для задания связи между пользователем и объектом
     * т.е. в нем должны устанавливаться только такие поля как companyId и т.д.
     *
     * необходим, чтобы после установки связи у пользователя уже имелась
     * определенная привиллегиядля редактирования остальных полей с контролем через acl
     */
    protected function initPrivelegeOnCreateByUser(User $user)
    {
        ;
    }
    /**
     * метод вызывается перед редактированием полей при создании объекта
     * нужен для того, чтобы иметь возможность предоплределять некоторые поля при создании (вроде addedAt)
     */
    protected function initCreationFieldsOnCreateByUser(User $user, array $fields)
    {
        ;
    }
    /**
     * метод должен вернуть список необходимых дополнительных полей, при запросе одного конкретного ресурса
     * например для Order это будут статусы заказа
     */
    protected function asArrayByUserExtendedPartById(User $user)
    {
        return array();
    }
    /**
     * метод должен вернуть список необходимых дополнительных полей, при запросе коллекции ресурсов
     * например для Transaction это будет заказ
     */
    protected function asArrayByUserExtendedPartList(User $user)
    {
        return array();
    }



    protected static $apiBroadcastChanges = false; // is set by code generator
    public static function apiBroadcastChanges()
    {
        return static::$apiBroadcastChanges;
    }

    final protected function onCreate(array $params = array())
    {
        if (!isset($params['user'])) {
            throw new \LogicException('You must provide user to create ' . get_class($this));
        }

        $user = $params['user'];
        if (!($user instanceof User)) {
            throw new \LogicException('Parameter user must be instance of User to create ' . get_class($this));
        }

        $this->initPrivelegeOnCreateByUser($user);

        $this->throwIfNotAccessOnResource('add', $this->getPrivilegeForUser($user));

        return $this;
    }
    final protected function onInit()
    {
        //переопределяем этот метод дял единообразного вызова переопределяемых методов (не on*, как для Record, а init*)
        $this->initConstructor();
    }
    final public function asArrayByUserExtendedById(User $user)
    {
        //расширеный вид - доп. поля, используемые в апи только при запросе одного конкретного объекта
        return array_merge($this->asArrayByUser($user), $this->asArrayByUserExtendedPartList($user), $this->asArrayByUserExtendedPartById($user));
    }
    final public function asArrayByUserExtendedList(User $user)
    {
        //расширеный вид - доп. поля, используемые в апи только при запросе коллекции объектов
        return array_merge($this->asArrayByUser($user), $this->asArrayByUserExtendedPartList($user));
    }



    final public function getPrivilegeForUser(User $user)
    {
        //TODO убрать дебаг
//        if (strpos(get_class($this), 'Company')) {
//            echo '===============================================================================================';
//            p($user);
//        }

        foreach (static::$aclPrivileges as $privilege => $conditions) {
            foreach ($conditions as $condName => $cond) {
                //TODO убрать дебаг
//                file_put_contents('/tmp/grace_check_syntax', '<?php return (' . $params[1] . ');');
//                $syntax = shell_exec('php -l /tmp/grace_check_syntax');
//                if (strpos($syntax, 'No syntax errors detected') === false) {
//                    print_r($params[1]);
//                    print_r($syntax);
//                    die('DIE');
//                }

//                $str = 'return ((' . $params['resourceMatchPhp'] . (isset($params['accessConditionPhp']) ? ') and (' . $params['accessConditionPhp'] : '') . '));';
//                $ev = eval($str);
//
//                if(strpos(get_class($this), 'Company'))
//                echo "
//                === EVAL ===
//                $str
//                -> {$ev}
//                ";


                //use in evals, don't delete
                $resource = $this;

                $case = $cond;

                $case = preg_replace('/now\(\)/', 'dt()', $case);
                $case = preg_replace('/now\(([0-9\-]+)\)/', 'dt(time() - $1)', $case);
                $case = preg_replace('/ROLE_[A-Z_]+/', '$user->isRole("$0")', $case);
                #$case = preg_replace('/type:([A-Za-z0-9_]+)/', '$user->isType("$1")', $case);
                $case = preg_replace_callback('/same:([A-Za-z0-9_]+)/', function ($match) { return '$user->get' . ucfirst($match[1]) . '()' . ' == ' . '$resource->get' . ucfirst($match[1]) . '()'; }, $case);
                //$case = preg_replace_callback('/user:([A-Za-z0-9_]+):([A-Za-z0-9_]+)/', function ($match) { return '$user->get' . ucfirst($match[1]) . '()->get' . ucfirst($match[2]) . '()'; }, $case);
                $case = preg_replace_callback('/user:([A-Za-z0-9_]+)/', function ($match) { return '$user->get' . ucfirst($match[1]) . '()'; }, $case);
                $case = preg_replace_callback('/resource:([A-Za-z0-9_]+):([A-Za-z0-9_]+)/', function ($match) { return '$resource->get' . ucfirst($match[1]) . '()->get' . ucfirst($match[2]) . '()'; }, $case);
                $case = preg_replace_callback('/resource:([A-Za-z0-9_]+)/', function ($match) { return '$resource->get' . ucfirst($match[1]) . '()'; }, $case);

                if (eval("return ($case);")) {
                    return $privilege;
                }
            }
        }
        return 'unprivileged';
    }
    public function firstEditByUser(User $user, array $fields)
    {
        $this->initCreationFieldsOnCreateByUser($user, $fields);
        return $this->editOrInsertByUser($user, $fields, true);
    }
    public function editByUser(User $user, array $fields)
    {
        return $this->editOrInsertByUser($user, $fields, false);
    }
    private function editOrInsertByUser(User $user, array $fields, $isFirstEdit)
    {
        $privilege = $this->getPrivilegeForUser($user);
        $action = $isFirstEdit ? 'add' : 'edit';

        $this->throwIfNotAccessOnResource($action, $privilege);

        foreach ($fields as $fieldName => $value) {
            $getter = 'get' . ucfirst($fieldName);

            //правильная обработка булевых полей из js
            if ($value === 'true') {
                $value = true;
            }
            if ($value === 'false') {
                $value = false;
            }

            if (method_exists($this, $getter) and $this->$getter() != $value) {
                //только поля, которые вообще может хоть кто-то редактировать
                //только измененные поля
                $this->throwIfNotAccessOnField($action, $fieldName, $privilege);

                $setter = 'set' . ucfirst($fieldName);
                $this->$setter($value);
            }
        }
        return $this;
    }
    public function deleteByUser(User $user)
    {
        $this->throwIfNotAccessOnResource('delete', $this->getPrivilegeForUser($user));
        $this->delete();
    }

    public static function getParentsDefinition()
    {
        return static::$parents;
    }

    public static function getAccessDefinition()
    {
        $r = array();
        foreach (static::$aclPrivileges as $conditions) {
            foreach($conditions as $condName => $cond) {
                $r[] = $cond;
            }
        }
        return '((' . join(') or (', $r) . '))';
    }

	public function getReceiversNodejsSql()
	{
        $sql = array();
		foreach (static::$aclPrivileges as $conditions) {
			foreach($conditions as $condName => $cond) {
                $sql[] = "\n   /* $condName */ " . $cond;
			}
		}

		$sql =  '(' . join(') OR (', $sql) . ')';
        $sql = preg_replace('/same:([A-Za-z0-9_]+)/', 'user:$1 == resource:$1', $sql);
        // @todo сделать парсинг/конфиг нормально
        $sql = preg_replace('/==/', '=', $sql);
        $sql = preg_replace('/now\(\)/', 'NOW()', $sql);
        $sql = preg_replace('/now\(([0-9\-]+)\)/', 'date_add(NOW(), interval $1 second)', $sql);

        $sql = preg_replace_callback(
            '/ROLE_[A-Z_]+/',
            function ($match) {
                return 'find_in_set("'.$match[0].'", role) > 0';
            },
            $sql
        );
        $sql = preg_replace('/user:([A-Za-z0-9_]+)/', '$1', $sql);


        $placeholders = array();
        $sql = preg_replace_callback(
            '/resource:([A-Za-z0-9_]+):([A-Za-z0-9_]+)/',
            function ($match) use (&$placeholders) {
                $placeholders[] = $this->{'get' . ucfirst($match[1])}()->{'get' . ucfirst($match[2])}();
                return '?';
            },
            $sql
        );
        $sql = preg_replace_callback(
            '/resource:([A-Za-z0-9_]+)/',
            function ($match) use (&$placeholders) {
                $placeholders[] = $this->{'get' . ucfirst($match[1])}();
                return '?';
            },
            $sql
        );

        return array($sql, $placeholders);
	}

    final public function asArrayForNodejs()
    {
        $key = 'as_array_for_nodejs_' . md5(json_encode($this->fields));
        return $this->getContainer()->getCache()->get($key, 30, function() {
            $r = array();
            foreach ($this->fields as $fieldName => $v) {
                if (static::$aclFieldsView[$fieldName]) {

                    $getter = 'get' . ucfirst($fieldName);
                    $nodejsGetter = $getter . 'ForNodejs';

                    if (method_exists($this, $nodejsGetter)) {
						$value = array(
							'evalMe' => $this->$nodejsGetter(),
						);
                    } else {
                        $value = $this->$getter();
                    }

                    if (is_object($value)) {
                        if ($value instanceof ApiAsArrayAccessibleInterface) {
                            $value = $value->asArrayForNodejs();
                        } elseif ($value instanceof Record) {
                            $value = $value->asArray();
                        } elseif ($value instanceof Collection) {
                            $value = $value->asArray();
                        } elseif ($value instanceof ApiFieldObjectInterface) {
                            $value = $value->getApiValue();
                        } else {
                            throw new \LogicException('Api field object must be instance of ApiFieldObjectAbstract');
                        }
                    }
                    $r[$fieldName] = $value;

                }
            }

            return $r;
        });
    }
    final public function asArrayByUser(User $user)
    {
        $privilege = $this->getPrivilegeForUser($user);
        $this->throwIfNotAccessOnResource('view', $privilege);

        $key = 'as_array_' . md5($privilege . $user->getType() . $user->getId() . md5(json_encode($this->fields)));
        return $this->getContainer()->getCache()->get($key, 30, function() use ($user, $privilege) {
                $r = array();
                foreach ($this->fields as $fieldName => $v) {
                    if (static::$aclFieldsView[$fieldName]) {

                        $getter = 'get' . ucfirst($fieldName);
                        $getterByUser = $getter . 'ByUser';

                        if (method_exists($this, $getterByUser)) {
                            $value = $this->$getterByUser($user);
                            //$value = $this->fields[$fieldName];
                        } else {
                            $value = $this->$getter();
                            //$value = $this->fields[$fieldName];
                        }

                        if (is_object($value)) {
                            if ($value instanceof ApiAsArrayAccessibleInterface) {
                                $value = $value->asArrayByUser($user);
                            } elseif ($value instanceof Record) {
                                $value = $value->asArray();
                            } elseif ($value instanceof Collection) {
                                $value = $value->asArray();
                            } elseif ($value instanceof ApiFieldObjectInterface) {
                                $value = $value->getApiValue();
                            } else {
                                throw new \LogicException('Api field object must be instance of ApiFieldObjectAbstract');
                            }
                        }
                        $r[$fieldName] = $value;

                    }
                }
                $r['privilege'] = $privilege;

                return $r;
            });
    }
    protected function hasAccess($accessList, $privilege)
    {
        //$accessList can be true, false, array('privilege1', 'privilege2')
        return $accessList === true or (is_array($accessList) and in_array($privilege, $accessList));
    }
    protected function throwIfNotAccessOnResource($action, $privilege)
    {
        $aclEdit = static::${'acl' . ucfirst($action)};
        if (!$this->hasAccess($aclEdit, $privilege)) {
            throw new AccessDeniedException('Access denied to ' . $action . ' resource ' . get_class($this) . ' - ' . $this->getId() . ' for privilege "' . $privilege . '"');
        }
    }
    protected function throwIfNotAccessOnField($action, $fieldName, $privilege)
    {
        $aclFieldsEdit = static::${'aclFields' . ucfirst($action)};
        $edit = (isset($aclFieldsEdit[$fieldName]) ? $aclFieldsEdit[$fieldName] : false);

        //edit can be true, false, array('privilege1', 'privilege2')
        //могут придти другие поля, если например в js-модели добавились спец. поля
        if (!$this->hasAccess($edit, $privilege)) {
            throw new AccessDeniedException('Access denied to field ' . $fieldName . ' for "' . $privilege . '" on ' . $action . '');
        }
    }
}
