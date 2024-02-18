<?php

namespace App\Models;

use Page;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;

class FocusPointImagePage extends Page
{
    private static $has_one = [
        'Image' => Image::class,
    ];

    private static $owns = [
        'Image'
    ];

    private static $table_name = 'FocusPointImagePage';

    private static $description = 'Has an image and call FocusFillMax in template';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->addFieldToTab('Root.Main', UploadField::create('Image', 'Image'));

        return $fields;
    }
}
