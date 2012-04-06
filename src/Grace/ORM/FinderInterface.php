<?php

namespace Grace\ORM;

interface FinderInterface {
    public function create();
    public function getById($id);
}