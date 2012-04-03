<?php

namespace Grace\ORM;

class EventDispatcher {
    /**
     * array(
     *     'closeOrder' => array('OrderLogger', 'OrderSmsSender'),
     *     ...
     * )
     * @var array
     */
    private $subscribedEvents = array();
    private $subscribers = array();
    private $subscriberBuilders = array();

    public function addSubscriberBuilder($className, \Closure $subscriberBuilder) {
        $this->setSubscribedEvents($className);
        $this->subscriberBuilders[$className] = $subscriberBuilder;
        return $this;
    }
    public function addSubscriberObject(EventSubscriberInterface $subscriber) {
        $className = get_class($subscriber);
        $this->setSubscribedEvents($className);
        $this->subscribers[$className] = $subscriber;
        return $this;
    }
    public function notify($eventName, $context = null) {
        if (isset($this->subscribedEvents[$eventName])) {
            foreach ($this->subscribedEvents[$eventName] as $subscriberClass) {
                $subscriber = $this->getSubscriber($subscriberClass);
                call_user_func(array($subscriber, $eventName), $context);
            }
        }
        return $this;
    }
    public function filter($eventName, $value, $context = null) {
        if (isset($this->subscribedEvents[$eventName])) {
            foreach ($this->subscribedEvents[$eventName] as $subscriberClass) {
                $subscriber = $this->getSubscriber($subscriberClass);
                $value = call_user_func_array(array($subscriber, $eventName),
                    array($value, $context));
            }
        }
        return $value;
    }
    private function setSubscribedEvents($className) {
        $methods = get_class_methods($className);
        foreach ($methods as $method) {
            $this->subscribedEvents[$method][] = $className;
        }
    }
    private function getSubscriber($className) {
        if (!isset($this->subscribers[$className])) {
            $builder = $this->subscriberBuilders[$className];
            $object = $builder();
            if (!($object instanceof EventSubscriberInterface)) {
                throw new ExceptionBadSubscriberBuilder('Subscribers must implement EventSubscriberInterface');
            }
            $this->subscribers[$className] = $object;
        }
        return $this->subscribers[$className];
    }
}