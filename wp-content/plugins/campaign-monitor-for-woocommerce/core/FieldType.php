<?php
/**
 * Created by PhpStorm.
 * User: SunriseIntegration4
 * Date: 6/2/2016
 * Time: 10:48 AM
 */

namespace core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Helper class
 *
 * Class FieldType
 * @package core
 */
abstract class FieldType
{
    const NUMBER = 'Number';
    const TEXT = 'Text';
    const DATE = 'Date';
    const ALL = 'All';
}