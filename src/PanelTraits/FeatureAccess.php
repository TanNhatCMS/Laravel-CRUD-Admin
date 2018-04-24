<?php

namespace Backpack\CRUD\PanelTraits;

use Backpack\CRUD\Exception\AccessDeniedException;

trait FeatureAccess
{
    /*
    |--------------------------------------------------------------------------
    |                                   CRUD ACCESS
    |--------------------------------------------------------------------------
    */

    public function enable($feature)
    {
        // $this->addButtons((array)$access);
        return $this->enabled = array_merge(array_diff((array) $feature, $this->enabled), $this->enabled);
    }

    public function disable($feature)
    {
        // $this->removeButtons((array)$access);
        return $this->enabled = array_diff($this->enabled, (array) $feature);
    }

    /**
     * Check if a permission is enabled for a Crud Panel. Return false if not.
     *
     * @param  [string] Permission.
     *
     * @return bool
     */
    public function isEnabled($feature)
    {
        if (! in_array($feature, $this->enabled)) {
            return false;
        }

        return true;
    }

    /**
     * Check if any permission is enabled for a Crud Panel. Return false if not.
     *
     * @param  [array] Permissions.
     *
     * @return bool
     */
    public function anyAreEnabled($feature_array)
    {
        foreach ($feature_array as $key => $feature) {
            if (in_array($feature, $this->enabled)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if all permissions are enabled for a Crud Panel. Return false if not.
     *
     * @param  [array] Permissions.
     *
     * @return bool
     */
    public function allAreEnabled($feature_array)
    {
        foreach ($feature_array as $key => $feature) {
            if (! in_array($feature, $this->enabled)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if a permission is enabled for a Crud Panel. Fail if not.
     *
     * @param string $permission Permission
     * @return bool
     *
     * @throws \Backpack\CRUD\Exception\AccessDeniedException in case the permission is not enabled
     */
    public function isEnabledOrFail($feature)
    {
        if (! in_array($feature, $this->enabled)) {
            throw new AccessDeniedException(trans('backpack::crud.unauthorized_access'));
        }

        return true;
    }

    // -------
    // ALIASES (For backwards compatibility)
    // -------


    public function allowAccess($feature)
    {
        return $this->enable($feature);
    }

    public function denyAccess($feature)
    {
        return $this->disable($feature);
    }

    public function hasAccess($feature)
    {
        return $this->isEnabled($feature);
    }

    public function hasAccessToAny($feature_array)
    {
        return $this->anyAreEnabled($feature_array);
    }

    public function hasAccessToAll($feature_array)
    {
        return allAreEnabled($feature_array);
    }

    public function hasAccessOrFail($feature)
    {
        return isEnabledOrFail($feature);
    }
}
