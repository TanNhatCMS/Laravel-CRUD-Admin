<?php

namespace Backpack\CRUD\PanelTraits;

trait PageTitles
{
    public function initTitles() {
      $this->setCreateTitle(trans('backpack::crud.add').' '.$this->entity_name);
      $this->setEditTitle(trans('backpack::crud.edit').' '.$this->entity_name);
      $this->setShowTitle(trans('backpack::crud.preview').' '.$this->entity_name);
      $this->setRevisionsTitle(ucfirst($this->entity_name).' '.trans('backpack::crud.revisions'));
      $this->setReorderTitle(trans('backpack::crud.reorder').' '.$this->entity_name);
      $this->setListTitle(ucfirst($this->entity_name_plural));
    }

    // -------
    // CREATE
    // -------

    /**
     * Sets the list title.
     * @param string $title name of the page
     * @return string $title name of the page
     */
    public function setCreateTitle($title)
    {
        $this->createTitle = $title;

        return $this->createTitle;
    }

    /**
     * Gets the create title.
     * @return string name of the page
     */
    public function getCreateTitle()
    {
        return $this->createTitle;
    }

    // -------
    // READ
    // -------

    /**
     * Sets the list title.
     * @param string $title name of the page
     * @return string $title name of the page
     */
    public function setListTitle($title)
    {
        $this->listTitle = $title;

        return $this->listTitle;
    }

    /**
     * Gets the list title.
     * @return string name of the page
     */
    public function getListTitle()
    {
        return $this->listTitle;
    }

    /**
     * Sets the details row title.
     * @param string $title name of the page
     * @return string $title name of the page
     */
    public function setDetailsRowTitle($title)
    {
        $this->detailsRowTitle = $title;

        return $this->detailsRowTitle;
    }

    /**
     * Gets the details row title.
     * @return string name of the page
     */
    public function getDetailsRowTitle()
    {
        return $this->detailsRowTitle;
    }

    /**
     * Sets the show title.
     * @param string $title name of the page
     * @return string $title name of the page
     */
    public function setShowTitle($title)
    {
        $this->showTitle = $title;

        return $this->showTitle;
    }

    /**
     * Gets the show title.
     * @return string name of the page
     */
    public function getShowTitle()
    {
        return $this->showTitle;
    }

    // -------
    // UPDATE
    // -------

    /**
     * Sets the edit title.
     * @param string $title name of the page
     * @return string $title name of the page
     */
    public function setEditTitle($title)
    {
        $this->editTitle = $title;

        return $this->editTitle;
    }

    /**
     * Gets the edit title.
     * @return string name of the page
     */
    public function getEditTitle()
    {
        return $this->editTitle;
    }

    /**
     * Sets the reorder title.
     * @param string $title name of the page
     * @return string $title name of the page
     */
    public function setReorderTitle($title)
    {
        $this->reorderTitle = $title;

        return $this->reorderTitle;
    }

    /**
     * Gets the reorder title.
     * @return string name of the page
     */
    public function getReorderTitle()
    {
        return $this->reorderTitle;
    }

    /**
     * Sets the revision title.
     * @param string $title name of the page
     * @return string $title name of the page
     */
    public function setRevisionsTitle($title)
    {
        $this->revisionsTitle = $title;

        return $this->revisionsTitle;
    }

    /**
     * Gets the revisions title.
     * @return string name of the page
     */
    public function getRevisionsTitle()
    {
        return $this->revisionsTitle;
    }

    // -------
    // ALIASES
    // -------

    public function getPreviewTitle()
    {
        return $this->getShowTitle();
    }

    public function setPreviewitle($title)
    {
        return $this->setShowTitle($title);
    }

    public function getUpdateTitle()
    {
        return $this->getEditTitle();
    }

    public function setUpdateTitle($title)
    {
        return $this->setEditTitle($title);
    }
}
