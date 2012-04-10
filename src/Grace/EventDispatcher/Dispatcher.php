<?php

namespace Grace\EventDispatcher;

class Dispatcher {
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
        $id = spl_object_hash($subscriberBuilder);
        $this->setSubscribedEvents($id, $className);
        $this->subscriberBuilders[$id] = $subscriberBuilder;
        return $this;
    }
    public function addSubscriberObject(SubscriberInterface $subscriber) {
        $className = get_class($subscriber);
        $id = spl_object_hash($subscriber);
        $this->setSubscribedEvents($id, $className);
        $this->subscribers[$id] = $subscriber;
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
    private function setSubscribedEvents($id, $className) {
        $methods = get_class_methods($className);
        foreach ($methods as $method) {
            $this->subscribedEvents[$method][] = $id;
        }
    }
    private function getSubscriber($id) {
        if (!isset($this->subscribers[$id])) {
            $builder = $this->subscriberBuilders[$id];
            $object = $builder();
            if (!($object instanceof SubscriberInterface)) {
                throw new ExceptionBadSubscriberBuilder('Subscribers must implement EventSubscriberInterface');
            }
            $this->subscribers[$id] = $object;
        }
        return $this->subscribers[$id];
    }
}