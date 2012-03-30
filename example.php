


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
    pub
    public function closeOrder($price) {
        
    }
}