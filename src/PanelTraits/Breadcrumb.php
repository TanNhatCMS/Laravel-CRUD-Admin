<?php

namespace Backpack\CRUD\PanelTraits;

trait Breadcrumb
{
    /*
    |--------------------------------------------------------------------------
    |                              BREADCRUMB
    |--------------------------------------------------------------------------
    */

    protected $items;

    /**
     * Parse the word.
     *
     * @return string the parsed word.
     **/
    protected function parseWord($word)
    {
        $words = explode('_', $word);
        $string = '';
        foreach ($words as $key => $word) {
            $string .= $key > 0 ? ' ' . ucfirst($word) : ucfirst($word);
        }
        return $string;
    }


    /**
     * Generate the actual breadcrumb.
     *
     * @return string The actual breadcrumb to be displayed.
     **/
    public function buildBreadcrumb()
    {
        if (!$this->route) {
          $this->setRoute(config('backpack.base.route_prefix'));
        }

        $this->items = new Collection(explode('.', $this->route));

        $string = '';
        foreach ($this->items as $key => $item) {
            $string .= $this->getTag($key, $this->items, $item);
        }
        return $string;
    }

    /**
     * Allow crumbs to be added at the beginning.
     * @param string $item string of new item(s) to be added.
     **/
    public function addBreadcrumbStart($item)
    {
      $this->setRoute($item.'.'.$this->route);
      $this->setBreadcrumb();
    }

    /**
     * Allow crumbs to be added at the beginning.
     * @param string $item string of new item(s) to be added.
     **/
    public function addBreadcrumbEnd($item)
    {
      $this->setRoute($this->route.'.'.$item);
      $this->setBreadcrumb();
    }

    /**
     * Determine wether this item is at the end or in the middle.
     * @param integer $key position of the item within the array.
     * @param Collection $items Collection of all items.
     * @param string $item name of the item being used.
     *
     * @return string The string to be displayed for that item.
     **/
    protected function getTag(int $key, Collection $items, $item)
    {
        return $key === ($items->count() - 1) ? $this->getLastItemTags($item) : $this->getItemTags($item);
    }

    /**
     * The output for an item if it is somewhere in the middle.
     * @param string $item Name of the item to be displayed.
     *
     * @return string The string to be displayed
     **/
    private function getItemTags($item)
    {
        $urlPrefix = config('app.url').'/';

        if($item !== ucfirst(config('backpack.base.route_prefix'))) {
          $urlPrefix = $urlPrefix.config('backpack.base.route_prefix').'/';
        }

        $url = $urlPrefix.$item;
        return '<li><a href="'.$url.'">'.$this->parseWord($item).'</a></li>';
    }

    /**
     * The output for an item if it is the last item within the collection.
     * @param string $item Name of the item to be displayed.
     *
     * @return string The string to be displayed
     **/
    private function getLastItemTags($item)
    {
        return '<li class="active">'.$this->parseWord($item).'</li>';
    }
}
