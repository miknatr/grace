<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Mikhail Natrov <miknatr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\Bundle;

use Grace\Bundle\GracePlusSymfony;
use Grace\Bundle\Validator\ValidationException;
use Grace\ORM\Grace;
use Grace\ORM\ModelAbstract;
use Intertos\CoreBundle\Security\Core\User\UserAbstract;

/** @property GracePlusSymfony $orm */
abstract class ModelAbstractPlusSymfony extends ModelAbstract
{
    public function ensureValid()
    {
        $constraintViolationList = $this->orm->validator->validate($this);
        if ($constraintViolationList->count() != 0) {
            $this->revert();
            throw new ValidationException($constraintViolationList);
        }
    }
}
