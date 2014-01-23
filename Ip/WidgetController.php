<?php
/**
 * @package ImpressPages
 *
 *
 */
namespace Ip;

use Ip\Internal\Content\Model;

/**
 * Controller for widgets
 * @package Ip
 */
class WidgetController
{
    protected $name;
    protected $pluginName;

    /**
     * @var boolean - true if widget is installed by default
     */
    protected $core;
    const SKIN_DIR = 'skin';

    protected $widgetDir;
    protected $widgetAssetsDir;

    public function __construct($name, $pluginName, $core = false)
    {
        $this->name = $name;
        $this->pluginName = $pluginName;
        $this->core = $core;

        if ($this->core) {

            $this->widgetDir = 'Ip/Internal/' . $pluginName . '/' . Model::WIDGET_DIR . '/' . $this->name . '/';
        } else {
            $this->widgetDir = 'Plugin/' . $pluginName . '/' . Model::WIDGET_DIR . '/' . $this->name . '/';
        }

        $this->widgetAssetsDir = $this->widgetDir . \Ip\Application::ASSETS_DIR . '/';
    }

    /**
     * Gets widget title
     *
     * Override this method to set the widget name displayed in widget toolbar.
     *
     * @return string
     */
    public function getTitle()
    {
        return self::getName();
    }

    /**
     * Return a name, which is unique widget identifier
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function getWidgetDir()
    {
        return $this->widgetDir;
    }


    public function isCore()
    {
        return $this->core;
    }

    /**
     * Get widget icon URL
     *
     * Widget icon is displayed in widget toolbar of administration page.
     *
     * @return string
     */
    public function getIcon()
    {
        if ($this->core) {
            if (file_exists(ipFile($this->widgetAssetsDir . 'icon.svg'))) {
                return ipFileUrl($this->widgetAssetsDir . 'icon.svg');
            }
            if (file_exists(ipFile($this->widgetAssetsDir . 'icon.png'))) {
                return ipFileUrl($this->widgetAssetsDir . 'icon.png');
            }
        } else {
            if (file_exists(ipFile($this->widgetAssetsDir . 'icon.svg'))) {
                return ipFileUrl($this->widgetAssetsDir . 'icon.svg');
            }
            if (file_exists(ipFile($this->widgetAssetsDir . 'icon.png'))) {
                return ipFileUrl($this->widgetAssetsDir . 'icon.png');
            }
        }

        return ipFileUrl('Ip/Internal/Content/assets/img/iconWidget.png');
    }

    public function defaultData()
    {
        return array();
    }

    /**
     * @return array
     * @throws Internal\Content\Exception
     */
    public function getSkins()
    {

        $views = array();


        //collect default view files
        $skinDir = ipFile($this->widgetDir . self::SKIN_DIR . '/');


        if (!is_dir($skinDir)) {
            throw new \Ip\Internal\Content\Exception('Skins directory does not exist. ' . $skinDir, \Ip\Internal\Content\Exception::NO_SKIN);
        }

        $availableViewFiles = scandir($skinDir);
        foreach ($availableViewFiles as $viewFile) {
            if (is_file($skinDir . $viewFile) && substr($viewFile, -4) == '.php') {
                $views[substr($viewFile, 0, -4)] = 1;
            }
        }
        //collect overridden theme view files
        $themeViewsFolder = ipThemeFile(\Ip\View::OVERRIDE_DIR . '/' . $this->pluginName . '/' . Model::WIDGET_DIR . '/' . $this->name . '/' . self::SKIN_DIR);
        if (is_dir($themeViewsFolder)){
            $availableViewFiles = scandir($themeViewsFolder);
            foreach ($availableViewFiles as $viewFile) {
                if (is_file($themeViewsFolder . '/' . $viewFile) && substr($viewFile, -4) == '.php') {
                    $views[substr($viewFile, 0, -4)] = 1;
                }
            }
        }

        $layouts = array();
        foreach ($views as $viewKey => $view) {
            $translation = __('Skin_' . $viewKey, $this->pluginName, false);
            $layouts[] = array('name' => $viewKey, 'title' => $translation);
        }

        if (empty($layouts)) {
            throw new \Ip\Internal\Content\Exception('No layouts', Exception::NO_SKIN);
        }

        return $layouts;
    }

    /**
     *
     *
     * @param $widgetId
     * @param $postData
     * @param $currentData
     * @return array data to be stored to the database
     */
    public function update ($widgetId, $postData, $currentData)
    {
        return $postData;
    }

    /**
     *
     * You can make posts directly to your widget. If you will pass following parameters:
     * sa=Content.widgetPost
     * securityToken=actualSecurityToken
     * instanceId=actualWidgetInstanceId
     *
     * then that post request will be redirected to this method.
     *
     * Use return new \Ip\Response\Json($jsonArray) to return json.
     *
     * Be careful. This method is accessible for website visitor without admin login.
     *
     * @param int $instanceId
     * @param array $data widget data
     */
    public function post ($instanceId, $data)
    {

    }

    /**
     *
     * Duplicate widget action. This function is executed after the widget is being duplicated.
     * All widget data is duplicated automatically. This method is used only in case a widget
     * needs to do some maintenance tasks on duplication.
     * @param int $oldId old widget id
     * @param int $newId duplicated widget id
     * @param array $data data that has been duplicated from old widget to the new one
     */
    public function duplicate($oldId, $newId, $data)
    {

    }

    /**
     *
     * Delete a widget. This method is executed before actual deletion of widget.
     * It is used to remove widget data (photos, files, additional database records and so on).
     * Standard widget data is being deleted automatically. So you don't need to extend this method
     * if your widget does not upload files or add new records to the database manually.
     * @param int $widgetId
     * @param array $data data that is being stored in the widget
     */
    public function delete($widgetId, $data)
    {

    }


    public function adminSnippets()
    {

        //TODOXX scandir Model::SNIPPET_DIR and return snippets as an array #126
//        $answer = '';
//        try {
//            if ($this->core ) {
//                $adminView = ipConfig()->coreModuleFile($this->moduleName . '/' . Model::WIDGET_DIR . '/' . $this->name . '/' . self::SNIPPET_VIEW);
//            } else {
//                $adminView = ipConfig()->pluginFile($this->moduleName . '/' . Model::WIDGET_DIR . '/' . $this->name . '/' . self::SNIPPET_VIEW);
//            }
//            if (is_file($adminView)) {
//                $answer = ipView($adminView)->render();
//            }
//        } catch (\Ip\Exception $e){
//            return $e->getMessage();
//        }
//        return $answer;
        return array();
    }

    /**
     * Renders widget's HTML output
     *
     * Extend this method to generate widget's HTML.
     *
     * @param $revisionId Widget revision id
     * @param $widgetId Widget id
     * @param $instanceId Widget instance id
     * @param array|null $data Widget data array
     * @param string $layout Layout name
     * @return string Widget's HTML code
     */

    public function generateHtml($revisionId, $widgetId, $instanceId, $data, $layout)
    {
        $answer = '';
        try {
            if ($this->core) {
                $answer = ipView(ipFile('Ip/Internal/' . $this->pluginName . '/' . Model::WIDGET_DIR . '/' . $this->name . '/' . self::SKIN_DIR.'/'.$layout.'.php'), $data)->render();
            } else {
                $answer = ipView(ipFile('Plugin/' . $this->pluginName . '/' . Model::WIDGET_DIR . '/' . $this->name . '/' . self::SKIN_DIR.'/'.$layout.'.php'), $data)->render();
            }
        } catch (\Ip\Exception $e) {
            if (ipIsManagementState()) {
                $answer = $e->getTraceAsString();
            } else {
                $answer = '';
            }
        }
        return $answer;
    }

    /**
     * @param $revisionId
     * @param $widgetId
     * @param $instanceId
     * @param $data
     * @param $layout
     * @return mixed
     */
    public function dataForJs($revisionId, $widgetId, $instanceId, $data, $layout)
    {
        return $data;
    }


}
