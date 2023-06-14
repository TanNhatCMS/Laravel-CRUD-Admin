<?php

namespace Backpack\CRUD\app\Library\CrudPanel;

use Illuminate\Support\Str;

class QuickButton extends CrudButton
{
    public $type = 'view';
    public $content = 'crud::buttons.quick';

    public $access;
    public $url = null;
    public $classes = null;
    public $icon = null;
    public $text = null;

    /**
     * Only show the button if there's access to this operation.
     *
     * @param string|bool $access
     * @return QuickButton
     */
    public function access(string|bool $access)
    {
        $this->access = $access;

        return $this->save();
    }

    /**
     * Set the url of the button (eg. url('home')).
     *
     * @param string $url
     * @return QuickButton
     */
    public function url(string $url)
    {
        $this->url = $url;

        return $this->save();
    }

    /**
     * Set the CSS classes of the button (eg. btn btn-outline-primary).
     *
     * @param string $classes
     * @return QuickButton
     */
    public function classes(string $classes)
    {
        $this->classes = $classes;

        return $this->save();
    }

    /**
     * Set the icon class of the button (eg. la la-home).
     *
     * @param string $icon
     * @return QuickButton
     */
    public function icon(string $icon)
    {
        $this->icon = $icon;

        return $this->save();
    }

    /**
     * Set the text of the button (eg. Moderate).
     *
     * @param string $text
     * @return QuickButton
     */
    public function text(string $text)
    {
        $this->text = $text;

        return $this->save();
    }

    /**
     * Get the end result that should be displayed to the user.
     * The HTML itself of the button.
     *
     * @param  object|null  $entry  The eloquent Model for the current entry or null if no current entry.
     * @return HTML
     */
    public function getHtml($entry = null)
    {
        $button = $this;
        $crud = $this->crud();

        $button->url = $button->url ?? $button->getDefaultUrl($entry);
        $button->access = $button->access ?? Str::of($button->name)->studly()->toString();
        $button->classes = $button->classes ?? ($button->stack == 'top' ? 'btn btn-outline-primary' : 'btn btn-sm btn-link');
        $button->icon = $button->icon ?? null;
        $button->text = $button->text ?? Str::of($button->name)->headline()->toString();

        if ($this->type == 'view') {
            return view($button->getFinalViewPath(), compact('button', 'crud', 'entry'));
        }
    }

    protected function getDefaultUrl($entry)
    {
        $id = ($entry != null) ? $entry->getKey() : false;
        $idUrlSegment = ($id ? '/'.$id .'/' : '/');

        return url($this->crud()->route . $idUrlSegment . Str::of($this->name)->kebab());
    }
}
