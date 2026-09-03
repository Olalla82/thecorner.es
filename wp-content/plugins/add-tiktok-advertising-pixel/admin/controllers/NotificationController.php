<?php
namespace Pagup\TiktokPixel\Controllers;

use Pagup\TiktokPixel\Core\Plugin;

class NotificationController
{
    public function support() 
    {
        return Plugin::view('notices/support');
    }
}