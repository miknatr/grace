<?php
namespace Grace\Bundle\CommonBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Grace\Bundle\CommonBundle\Generator\ModelsGenerator;
use Grace\ORM\ManagerAbstract;

class ControllerListener
{
    private $generator;
    private $orm;
    private $kernel;
    public function __construct(ModelsGenerator $generator, ManagerAbstract $orm, Kernel $kernel = null)
    {
        $this->generator = $generator;
        $this->orm = $orm;
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
        if ($this->kernel and $this->kernel->getEnvironment() == 'dev' and $this->generator->needUpdate()) {
            //автоматическая перегенерация, если конфиг изменялся
            //время на генерацию 1.569
            //$time = time() + microtime(true);
            $this->generator->generate();
            //echo time() + microtime(true) - $time;
        }
    }
    public function onKernelResponse(FilterResponseEvent $event)
    {
        //нужно для изолированности объектов UnitOfWork и IdentityMap в тестах, но думаю лишним не будет и вообще
        $this->orm->clean();
    }
}