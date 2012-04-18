<?php

namespace Grace\Test\EventDispatcher;

use Grace\EventDispatcher\Dispatcher;

class DispatcherTest extends \PHPUnit_Framework_TestCase {
    /** @var Dispatcher */
    protected $dispatcher;

    protected function setUp() {
        $this->dispatcher = new Dispatcher;
    }
    protected function tearDown() {
        
    }
    public function testNotifyWithoutSubscribers() {
        $r = $this->dispatcher
            ->notify('somethingHappend')
            ->notify('somethingOtherHappend');
        $this->assertEquals($this->dispatcher, $r);
    }
    public function testFilterWithoutSubscribers() {
        $r = $this->dispatcher->filter('emailRegister', 'some value');
        $this->assertEquals('some value', $r);
    }
    public function testBadBuilderClosure() {
        $this->setExpectedException('Grace\EventDispatcher\ExceptionBadSubscriberBuilder');
        $this->dispatcher->addSubscriberBuilder('Grace\Test\EventDispatcher\SubscriberBadPlug',
            function() {
                return new SubscriberBadPlug;
            });
        $this->dispatcher->notify('badEvent');
    }
    public function testSubscribingWithClosure() {
        
        $plug = new SubscriberPlug;
        $this->dispatcher->addSubscriberBuilder('Grace\Test\EventDispatcher\SubscriberPlug',
            function() use ($plug) {
                return $plug;
            });
        $this->dispatcher
            ->notify('firstEvent')
            ->notify('secondEvent')
            ;
        $this->assertTrue($plug->wasFirstEvent);
        $this->assertTrue($plug->wasSecondEvent);
    }
    public function testFilteringWithClosure() {
        
        $plug = new SubscriberPlug;
        $this->dispatcher->addSubscriberBuilder('Grace\Test\EventDispatcher\SubscriberPlug',
            function() use ($plug) {
                return $plug;
            });
        $r = $this->dispatcher->filter('doubleFilter', 'qwe');
        $this->assertEquals('qweqwe', $r);
    }
    public function testSubscribingTwoSubscribers() {
        
        $plug = new SubscriberPlug;
        $plug2 = new SubscriberPlug;
        $this->dispatcher
                ->addSubscriberObject($plug)
                ->addSubscriberObject($plug2);
        $this->dispatcher
            ->notify('firstEvent')
            ->notify('secondEvent')
            ;
        $this->assertTrue($plug->wasFirstEvent);
        $this->assertTrue($plug->wasSecondEvent);
        $this->assertTrue($plug2->wasFirstEvent);
        $this->assertTrue($plug2->wasSecondEvent);
    }
    public function testFiltering() {
        
        $plug = new SubscriberPlug;
        $this->dispatcher->addSubscriberObject($plug);
        $r = $this->dispatcher->filter('doubleFilter', 'qwe');
        $this->assertEquals('qweqwe', $r);
    }
    public function testFilteringTwoSubscribers() {
        
        $plug = new SubscriberPlug;
        $plug2 = new SubscriberPlug;
        $this->dispatcher
                ->addSubscriberObject($plug)
                ->addSubscriberObject($plug2);
        $r = $this->dispatcher->filter('doubleFilter', 'qwe');
        $this->assertEquals('qweqweqweqwe', $r);
    }
}
