<?php
/**
 * @package ImpressPages

 *
 */
namespace Ip\Internal\Content\Widget\Map;




class Controller extends \Ip\WidgetController{


    public function getTitle() {
        return __('Map', 'ipAdmin', false);
    }


    public function update($widgetId, $postData, $currentData) {
        return $postData;
    }



    public function generateHtml($revisionId, $widgetId, $instanceId, $data, $skin) {

        return parent::generateHtml($revisionId, $widgetId, $instanceId, $data, $skin);
    }

    public function adminHtmlSnippet()
    {
        $variables = array (
            'settingsForm' => $this->settingsForm()
        );
        return ipView('snippet/map.php', $variables)->render();

    }

    protected function settingsForm()
    {
        $form = new \Ip\Form();



        $field = new \Ip\Form\Field\Text(
            array(
                'name' => 'title',
                'label' => __('Title', 'ipAdmin', false),
            ));
        $form->addField($field);


        $field = new \Ip\Form\Field\Textarea(
            array(
                'name' => 'description',
                'label' => __('Description', 'ipAdmin', false),
            ));
        $form->addField($field);



        return $form; // Output a string with generated HTML form
    }


}
