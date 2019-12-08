<?php
namespace kilyakus\widgets;

use yii\base\InvalidConfigException;
use yii\base\Widget as YiiWidget;
use yii\helpers\Json;

class Widget extends YiiWidget implements BootstrapInterface
{
    use TranslationTrait;
    use WidgetTrait;

    public $options = [];
    
    /**
     * @var array the options for the underlying Engine JS plugin.
     * Please refer to the corresponding Engine plugin Web page for possible options.
     * For example, [this page](http://yii2engine.icron.org/javascript.html#portlet) shows
     * how to use the "Portlet" plugin and the supported options (e.g. "loadSuccess").
     */
    public $clientOptions = [];
    /**
     * @var array the event handlers for the underlying Engine JS plugin.
     * Please refer to the corresponding Engine plugin Web page for possible events.
     * For example, [this page](http://yii2engine.icron.org/javascript.html#portlet) shows
     * how to use the "Portlet" plugin and the supported events (e.g. "close.mr.portlet").
     */
    public $clientEvents = [];

    public function init()
    {
        $this->initBsVersion();
        parent::init();
        $this->mergeDefaultOptions();
        if (empty($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }
        $this->initDestroyJs();
    }

    /**
     для yii2-widget-portlet. Нужно проверить, удалить если появятся ошибки в других плагинах
     */

    protected function registerPlugin($name)
    {
        $view = $this->getView();
        $id = $this->options['id'];
        if ($this->clientOptions !== false) {
            $options = empty($this->clientOptions) ? '' : Json::encode($this->clientOptions);
            $js = "jQuery('#$id').$name($options);";
            $view->registerJs($js);
        }
        if (!empty($this->clientEvents)) {
            $js = [];
            foreach ($this->clientEvents as $event => $handler) {
                $js[] = "jQuery('#$id').on('$event', $handler);";
            }
            $view->registerJs(implode("\n", $js));
        }
    }
}
