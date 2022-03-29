<?php

namespace Backpack\CRUD\app\Models\Traits;

use Illuminate\Support\Facades\Schema;

trait CrudTrait
{
    use HasIdentifiableAttribute;
    use HasEnumFields;
    use HasRelationshipFields;
    use HasUploadFields;
    use HasFakeFields;
    use HasTranslatableFields;

    public static function hasCrudTrait()
    {
        return true;
    }
    
    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return with(new static)->getTable();
    }

    /**
     * @param $query
     * @return mixed
     */
    public static function scopeWithTrashedFiltered($query)
    {
        if (Schema::hasColumn(self::getTableName(), 'deleted_at')) {
            $query->withTrashed();
        }
        return $query;
    }
}
