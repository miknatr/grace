<?php

namespace Grace\Bundle\ApiBundle\Model;

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
        $resource = $this;
        foreach (static::$aclPrivileges as $privilege => $conditions) {
            foreach ($conditions as $condition) {
                if (eval('return (' . $condition[1] . ');')) {
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
    final public function asArrayByUser(User $user)
    {
        $privilege = $this->getPrivilegeForUser($user);
        $this->throwIfNotAccessOnResource('view', $privilege);

        $key = 'as_array_' . md5($privilege . $user->getType() . $user->getId() . md5(json_encode($this->fields)));
        return $this->getContainer()->getCache()->get($key, 30, function() use ($user, $privilege) {
                $r = array();
                foreach ($this->fields as $fieldName => $v) {
                    if ($this->hasAccess(static::$aclFieldsView[$fieldName], $privilege)) {

                        $getter = 'get' . ucfirst($fieldName);
                        $getterByUser = $getter . 'ByUser';

                        if (method_exists($this, $getterByUser)) {
                            $value = $this->$getterByUser($user);
                            //$value = $this->fields[$fieldName];
                        } else {
                            $value = $this->$getter();
                            //$value = $this->fields[$fieldName];
                        }

                        if(is_object($value)) {
                            if(method_exists($value, 'asArrayByUser')) {
                                $value = $value->asArrayByUser($user);
                            } elseif (method_exists($value, 'asArray')) {
                                $value = $value->asArray($user);
                            } elseif (method_exists($value, '__toString')) {
                                $value = (string) $value;
                            } else {
                                throw new \LogicException('Подобъект должен иметь метод asArrayByUser(User $user) или asArray()');
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