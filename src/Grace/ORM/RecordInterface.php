<?php

namespace Grace\ORM;

interface RecordInterface {
    public function delete();
    public function edit(array $fields);
    public function getId();
}