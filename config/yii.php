<?php

use Illuminatech\ArrayFactory\FactoryContract;
use Yii2tech\Illuminate\Http\YiiApplicationMiddleware;

/**
 * Configuration for Yii2 to Laravel Migration Package.
 *
 * @see https://github.com/yii2tech/illuminate
 */

return [
    /**
     * Configuration for Yii application middleware.
     *
     * @see YiiApplicationMiddleware
     * @see FactoryContract
     */
    'middleware' => [
        'defaultEntryScript' => 'legacy/web/index.php',
        'cleanup' => true,
        // 'bootstrap' => 'config/bootstrap.php',
        /*'container' => [
            '__class' => Yii2tech\Illuminate\Yii\Di\Container::class,
        ],*/
        /*'logger' => [
            '__class' => Yii2tech\Illuminate\Yii\Log\Logger::class,
        ],*/
    ],
];
