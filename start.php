<?php

if (!app()->routesAreCached()) {
   require __DIR__ . '/Http/routes.php';
}

include(__DIR__ . '/helpers.php');