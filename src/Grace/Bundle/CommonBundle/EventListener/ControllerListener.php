<?php
namespace Grace\Bundle\CommonBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Monolog\Logger;
use Grace\Generator\ModelsGenerator;
use Grace\ORM\ManagerAbstract;

class ControllerListener
{
    private $generator;
    private $orm;
    private $logger;
    private $kernel;
    public function __construct(ModelsGenerator $generator, ManagerAbstract $orm, Logger $logger, Kernel $kernel = null)
    {
        $this->generator = $generator;
        $this->orm = $orm;
        $this->logger = $logger;
        //в тестах кернела может и не быть
        $this->kernel = $kernel;
    }
    public function onKernelRequest(FilterControllerEvent $event)
    {
        //автоматическая перегенерация моделей, если изменились конфиги моделей

        //время на эту проверку 0.004
        //$time = time() + microtime(true);
        //$this->generator->needUpdate();
        //echo time() + microtime(true) - $time;

        //TODO переделать бы через команду generate --watch наподобие дампа ассетов
        #if ($this->kernel and $this->kernel->getEnvironment() == 'dev' and $this->generator->needUpdate()) {
            //автоматическая перегенерация, если конфиг изменялся
            //время на генерацию 1.569
            //$time = time() + microtime(true);
            #$this->generator->generate();
            //echo time() + microtime(true) - $time;
        #}
    }
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if ($this->kernel and $this->kernel->getEnvironment() == 'dev') {

            $queries = $this->orm->getSqlReadOnlyConnection()->getLogger()->getQueries();
            $time = array_reduce($queries, function($sum, $q) { return $sum += $q['time']; }, 0);

            if (count($queries) > 0) {
                $this->logger->info(count($queries) . ' queries in  ' . number_format($time, 5));
            }
        }

        //нужно для изолированности объектов UnitOfWork и IdentityMap в тестах, но думаю лишним не будет и вообще
        //TODO с другой стороны, если делаем подзапросы, то вроде нет смысла чистить IdentityMap, как быть?
        $this->orm->clean();
    }
}
