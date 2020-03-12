<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

trait Widgets
{
    /**
     * Mark widget type as loaded.
     *
     * @param string $widget Widget array
     * @return  bool Successful operation true/false.
     */
    public function markWidgetTypeAsLoaded($widget)
    {
        $alreadyLoaded = $this->getLoadedWidgetTypes();
        $type = $this->getWidgetTypeWithNamespace($widget);

        if (! in_array($type, $this->getLoadedWidgetTypes(), true)) {
            $alreadyLoaded[] = $type;
            $this->setLoadedWidgetTypes($alreadyLoaded);

            return true;
        }

        return false;
    }

    public function getWidgetTypeWithNamespace($widget)
    {
        $widgetType = $widget['type'];

        if (isset($widget['view_namespace'])) {
            $widgetType = implode('.', [$widget['view_namespace'], $widget['type']]);
        }

        return $widgetType;
    }

     /**
     * Set an array of widget type names as already loaded
     *
     * @param array $fieldTypes
     */
    public function setLoadedWidgetTypes($fieldTypes)
    {
        $this->set('widgets.loadedWidgetTypes', $fieldTypes);
    }

    /**
     * Get all the widget types whose resources (JS and CSS) have already been loaded on page.
     *
     * @return array Array with the names of the widget types.
     */
    public function getLoadedWidgetTypes()
    {
        return $this->get('widgets.loadedWidgetTypes') ?? [];
    }

    /**
     * Check if a widget type's reasources (CSS and JS) have already been loaded.
     *
     * @param string $widget Field array
     * @return  bool Whether the widget type has been marked as loaded.
     */
    public function widgetTypeLoaded($widget)
    {
        return in_array($this->getWidgetTypeWithNamespace($widget), $this->getLoadedWidgetTypes());
    }


}
