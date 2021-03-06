<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit918058ea06e06dc8494dcb060fa35ad4
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Symfony\\Component\\Process\\' => 26,
        ),
        'P' => 
        array (
            'PHPHtmlParser\\' => 14,
        ),
        'F' => 
        array (
            'Firebase\\JWT\\' => 13,
            'Facebook\\WebDriver\\' => 19,
        ),
        'B' => 
        array (
            'Browser\\' => 8,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Symfony\\Component\\Process\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/process',
        ),
        'PHPHtmlParser\\' => 
        array (
            0 => __DIR__ . '/..' . '/paquettg/php-html-parser/src/PHPHtmlParser',
        ),
        'Firebase\\JWT\\' => 
        array (
            0 => __DIR__ . '/..' . '/firebase/php-jwt/src',
        ),
        'Facebook\\WebDriver\\' => 
        array (
            0 => __DIR__ . '/..' . '/php-webdriver/webdriver/lib',
        ),
        'Browser\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpcasperjs/phpcasperjs/src',
        ),
    );

    public static $prefixesPsr0 = array (
        's' => 
        array (
            'stringEncode' => 
            array (
                0 => __DIR__ . '/..' . '/paquettg/string-encode/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit918058ea06e06dc8494dcb060fa35ad4::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit918058ea06e06dc8494dcb060fa35ad4::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit918058ea06e06dc8494dcb060fa35ad4::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
