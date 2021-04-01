<?php
namespace kilyakus\widgets;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\web\View;

class AssetBundle extends BaseAssetBundle implements BootstrapInterface
{
    use BootstrapTrait;

    public $bsDependencyEnabled;

    public $bsPluginEnabled = false;

    public $depends = [
        'yii\web\YiiAsset',
    ];

    public function init()
    {
        if (!isset($this->bsDependencyEnabled)) {
            $this->bsDependencyEnabled = ArrayHelper::getValue(Yii::$app->params, 'bsDependencyEnabled', true);
        }
        if ($this->bsDependencyEnabled) {
            $this->initBsAssets();
        }
        parent::init();
    }

    protected function initBsAssets()
    {
        $lib = 'bootstrap' . ($this->isBs4() ? '4' : '');
        $this->depends[] = "yii\\{$lib}\\BootstrapAsset";
        if ($this->bsPluginEnabled) {
            $this->depends[] = "yii\\{$lib}\\BootstrapPluginAsset";
        }
    }

    public static function registerBundle($view, $bsVer = null)
    {
        $currVer = ArrayHelper::getValue(Yii::$app->params, 'bsVersion', null);
        if (empty($bsVer) || static::isSameVersion($currVer, $bsVer)) {
            return static::register($view);
        }
        Yii::$app->params['bsVersion'] = $bsVer;
        $out = static::register($view);
        if (!empty($currVer)) {
            Yii::$app->params['bsVersion'] = $currVer;
        }
        return $out;
    }
}
