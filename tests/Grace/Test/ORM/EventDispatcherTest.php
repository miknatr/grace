<?php

namespace Grace\Test\ORM;

use Grace\ORM\EventDispatcher;

class EventDispatcherTest extends \PHPUnit_Framework_TestCase {
    /** @var EventDispatcher */
    protected $dispatcher;

    protected function setUp() {
        $this->dispatcher = new EventDispatcher;
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
        $this->setExpectedException('Grace\ORM\ExceptionBadSubscriberBuilder');
        $this->dispatcher->addSubscriberBuilder('Grace\Test\ORM\EventSubscriberBadPlug',
            function() {
                return new EventSubscriberBadPlug;
            });
        $this->dispatcher->notify('badEvent');
    }
    public function testSubscribingWithClosure() {
        
        $plug = new EventSubscriberPlug;
        $this->dispatcher->addSubscriberBuilder('Grace\Test\ORM\EventSubscriberPlug',
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
        
        $plug = new EventSubscriberPlug;
        $this->dispatcher->addSubscriberBuilder('Grace\Test\ORM\EventSubscriberPlug',
            function() use ($plug) {
                return $plug;
            });
        $r = $this->dispatcher->filter('doubleFilter', 'qwe');
        $this->assertEquals('qweqwe', $r);
    }
    public function testSubscribing() {
        
        $plug = new EventSubscriberPlug;
        $this->dispatcher->addSubscriberObject($plug);
        $this->dispatcher
            ->notify('firstEvent')
            ->notify('secondEvent')
            ;
        $this->assertTrue($plug->wasFirstEvent);
        $this->assertTrue($plug->wasSecondEvent);
    }
    public function testFiltering() {
        
        $plug = new EventSubscriberPlug;
        $this->dispatcher->addSubscriberObject($plug);
        $r = $this->dispatcher->filter('doubleFilter', 'qwe');
        $this->assertEquals('qweqwe', $r);
    }
}
