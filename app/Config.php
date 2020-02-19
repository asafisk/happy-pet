<?php

namespace app;

interface Config
{
    public static function feedScale();
    public static function strokeScale();
    public static function hungryStatusLabel();
    public static function happyStatusLabel();
    public static function overfeed();
    public static function overstroke();
}

?>