<?php
namespace kilyakus\widgets;

class WidgetAsset extends AssetBundle
{
	public function init()
	{
		$this->setSourcePath(__DIR__ . '/assets');
		$this->setupAssets('css', ['css/kv-widgets']);
		$this->setupAssets('js', ['js/kv-widgets']);
		parent::init();
	}
}
