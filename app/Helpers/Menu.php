<?php


class Menu {

    public static function activeMenu($route)
    {

        if (Route::currentRouteName() == $route)
        {
            return 'active';
        }
    }

}