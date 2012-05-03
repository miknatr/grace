<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Mikhail Natrov <miknatr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\ORM;

/**
 * Services, you can define them in Manager construction and call them in finders and records
 *
 * There is a reason for special container inside grace
 * You don't have to provide your application container (e.g. Symfony Container)
 * which usually big and has too many services
 * You define small container only for your models and finders
 * and in this case it easier to create mocks of your
 * services and coverage models by unit tests.
 */
interface ServiceContainerInterface
{
}
