<?php

namespace Grace\DBAL;

interface InterfaceResult {
    public function fetchOne();
    public function fetchAll();
    public function fetchResult();
    public function fetchColumn();
}