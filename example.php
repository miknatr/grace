<?php

abstract class OrderAbstract extends Grace_Record {

    private $id;
    private $phone;

    public function setPhone($phone) {
        $this->phone = $phone;
        return $this;
    }
    public function getPhone() {
        return $phone;
    }
}

class Order extends OrderAbstract {
    public function closeOrder($price) {
        
    }
}

class SomeController {
    public function ormBaseExamplesAction() {
        //выборка по айди
        $this->getOrm()->getOrderFinder()->getById(12);

        //выборка с условиями
        $this->getOrm()->getOrderFinder()->getAuctionCollection();

        //при этом внутри getAuctionCollection
        return $this->getSQLBuilder()
            ->eq('isPublic', 1)
            ->gt('addedAt', date())
            ->lt('addedAt', date() - 10)
            ->fetchAll();
        

        //вставка
        $this->getOrm()->getOrderFinder()->create()
            ->edit(array(
                'id' => 123,
                'clientPhone' => '+723423233',
                'clientName' => 'Андрей',
            )
        );
        //либо
        $this->getOrm()->getOrderFinder()->create()
            ->setName('Андрей')
            ->setPhone('+71234567890');
        ;



        //апдейт по айди
        $this->getOrm()->getOrderFinder()->getById(12)
            ->edit(array(
                'clientPhone' => '+723423233',
                'clientName' => 'Андрей',
            )
        );
        //либо
        $this->getOrm()->getOrderFinder()->getById(12)
            ->setName('Андрей')
            ->setPhone('+71234567890');
            ;


        //удаление по айди
        $this->getOrm()->getOrderFinder()->getById(12)->delete();



        //апдейт по условию (коллекция поддерживает методы объекта, автогенерация)
        $this->getOrm()->getOrderFinder()->getAuctionCollection()
            ->edit(array(
                'clientPhone' => '+723423233',
                'clientName' => 'Андрей',
            )
        );
        //либо
        $this->getOrm()->getOrderFinder()->getAuctionCollection()
            ->setName('Андрей')
            ->setPhone('+71234567890');
            ;
        //либо
        $this->getOrm()->getOrderFinder()->getAuctionCollection()->closeOrder();


        //удаление по условию
        $this->getOrm()->getOrderFinder()->getAuctionCollection()->delete();


        //Сохраняем изменения
        $this->getOrm()->commit();
    }
}
class SomeClass2 {
    public function dbExamplesAction() {
        $this->getService('db')->execute;
        
        //выборка с джоинами
        $this->getOrm()->getByCondition()
            ->from('Order')
            ->join('Company', 'owner', 'ownerId')
            ->join('Company', 'executer', 'executerId')
            ->join('Region', 'executer', 'executerId')
            ->isPublic->eq(1)
            ->addedAt->gt(date())
            ->addedAt->lt(date() - 10)
            ->owner->isBlocked->eq(0)
            ->region->population->gt(200000)
            ->getAll();
    }
}