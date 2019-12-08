<?php
namespace kilyakus\widgets;

use Yii;
use ReflectionClass;
use yii\base\InvalidConfigException;

class Config
{
    const VENDOR_NAME = 'kilyakus/';
    const NAMESPACE_PREFIX = '\\kilyakus\\';
    const DEFAULT_REASON = 'for your selected functionality';

    protected static $_validHtmlInputs = [
        'hiddenInput',
        'textInput',
        'passwordInput',
        'textArea',
        'checkbox',
        'radio',
        'listBox',
        'dropDownList',
        'checkboxList',
        'radioList',
        'input',
        'fileInput',
    ];
    protected static $_validDropdownInputs = [
        'listBox',
        'dropDownList',
        'checkboxList',
        'radioList',
        'checkboxButtonGroup',
        'radioButtonGroup',
    ];
    protected static $_validInputWidgets = [
        '\select2\Select2' => ['yii2-widgets', 'yii2-widget-select2'],
        '\kilyakus\depdrop\DepDrop' => ['yii2-widgets', 'yii2-widget-depdrop'],
        '\kilyakus\range\Range' => ['yii2-widgets', 'yii2-widget-range'],
        // '\kilyakus\typeahead\Typeahead' => ['yii2-widgets', 'yii2-widget-typeahead'],
        // '\kilyakus\touchspin\TouchSpin' => ['yii2-widgets', 'yii2-widget-touchspin'],
        // '\kilyakus\switchinput\SwitchInput' => ['yii2-widgets', 'yii2-widget-switchinput'],
        // '\kilyakus\rating\StarRating' => ['yii2-widgets', 'yii2-widget-rating'],
        // '\kilyakus\file\FileInput' => ['yii2-widgets', 'yii2-widget-fileinput'],
        // '\kilyakus\range\RangeInput' => ['yii2-widgets', 'yii2-widget-rangeinput'],
        // '\kilyakus\color\ColorInput' => ['yii2-widgets', 'yii2-widget-colorinput'],
        // '\kilyakus\date\DatePicker' => ['yii2-widgets', 'yii2-widget-datepicker'],
        // '\kilyakus\time\TimePicker' => ['yii2-widgets', 'yii2-widget-timepicker'],
        // '\kilyakus\datetime\DateTimePicker' => ['yii2-widgets', 'yii2-widget-datetimepicker'],
        // '\kilyakus\daterange\DateRangePicker' => 'yii2-date-range',
        // '\kilyakus\sortinput\SortableInput' => 'yii2-sortinput',
        // '\kilyakus\tree\TreeViewInput' => 'yii2-tree-manager',
        // '\kilyakus\money\MaskMoney' => 'yii2-money', // deprecated and replaced by yii2-number
        // '\kilyakus\number\NumberControl' => 'yii2-number',
        // '\kilyakus\checkbox\CheckboxX' => 'yii2-checkbox-x',
        // '\kilyakus\slider\Slider' => 'yii2-slider',
    ];

    public static function checkDependencies($extensions = [])
    {
        foreach ($extensions as $extension) {
            $name = empty($extension[0]) ? '' : $extension[0];
            $repo = empty($extension[1]) ? '' : $extension[1];
            $reason = empty($extension[2]) ? '' : self::DEFAULT_REASON;
            static::checkDependency($name, $repo, $reason);
        }
    }

    public static function checkDependency($name = '', $repo = '', $reason = self::DEFAULT_REASON)
    {
        if (empty($name)) {
            return;
        }
        $command = 'php composer.phar require ' . self::VENDOR_NAME;
        $version = ' \'@dev\'';
        $class = (substr($name, 0, 8) == self::NAMESPACE_PREFIX) ? $name : self::NAMESPACE_PREFIX . $name;

        if (is_array($repo)) {
            $repos = "one of '" . implode("' OR '", $repo) . "' extensions. ";
            $installs = $command . implode("{$version}\n\n--- OR ---\n\n{$command}", $repo) . $version;
        } else {
            $repos = "the '" . $repo . "' extension. ";
            $installs = $command . $repo . $version;
        }

        if (!class_exists($class)) {
            throw new InvalidConfigException(
                "The class '{$class}' was not found and is required {$reason}.\n\n" .
                "Please ensure you have installed {$repos}" .
                "To install, you can run this console command from your application root:\n\n{$installs}"
            );
        }
    }

    public static function getInputWidgets()
    {
        return static::$_validInputWidgets;
    }

    public static function isValidInput($type)
    {
        return static::isHtmlInput($type) || static::isInputWidget($type) || $type === 'widget';
    }

    public static function isHtmlInput($type)
    {
        return in_array($type, static::$_validHtmlInputs);
    }

    public static function isInputWidget($type)
    {
        return isset(static::$_validInputWidgets[$type]);
    }

    public static function isDropdownInput($type)
    {
        return in_array($type, static::$_validDropdownInputs);
    }

    public static function validateInputWidget($type, $reason = self::DEFAULT_REASON)
    {
        if (static::isInputWidget($type)) {
            static::checkDependency($type, static::$_validInputWidgets[$type], $reason);
        }
    }

    public static function getLang($language)
    {
        $pos = strpos($language, '-');
        return $pos > 0 ? substr($language, 0, $pos) : $language;
    }

    public static function getCurrentDir($object)
    {
        if (empty($object)) {
            return '';
        }
        $child = new ReflectionClass($object);
        return dirname($child->getFileName());
    }

    public static function fileExists($file)
    {
        $file = str_replace('/', DIRECTORY_SEPARATOR, $file);
        return file_exists($file);
    }

    public static function initModule($class)
    {
        $m = $class::MODULE;
        $module = $m ? static::getModule($m) : null;
        if ($module === null || !$module instanceof $class) {
            throw new InvalidConfigException("The '{$m}' module MUST be setup in your Yii configuration file and must be an instance of '{$class}'.");
        }
        return $module;
    }

    public static function getModule($m, $class = '')
    {
        $app = Yii::$app;
        $mod = isset($app->controller) && $app->controller->module ? $app->controller->module : null;
        $module = null;
        if ($mod) {
            $module = $mod->id === $m ? $mod : $mod->getModule($m);
        }
        if (!$module) {
            $module = $app->getModule($m);
        }
        if ($module === null) {
            throw new InvalidConfigException("The '{$m}' module MUST be setup in your Yii configuration file.");
        }
        if (!empty($class) && !$module instanceof $class) {
            throw new InvalidConfigException("The '{$m}' module MUST be an instance of '{$class}'.");
        }
        return $module;
    }

    public static function hasCssClass($options, $cssClass)
    {
        if (!isset($options['class'])) {
            return false;
        }
        $classes = is_array($options['class']) ? $options['class'] :
            preg_split('/\s+/', $options['class'], -1, PREG_SPLIT_NO_EMPTY);
        return in_array($cssClass, $classes);
    }
}
