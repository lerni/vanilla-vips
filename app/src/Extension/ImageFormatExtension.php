<?php

namespace App\Extension;

use SilverStripe\Assets\Storage\AssetStore;
use SilverStripe\Assets\Storage\DBFile;
use SilverStripe\Core\Extension;

class ImageFormatExtension extends Extension
{
    /**
     * Create a variant of the image in a different format.
     *
     * @param string $newExtension The file extension of the formatted file, e.g. "webp"
     */
    public function format(string $newExtension): DBFile
    {
        $original = $this->getOwner();
        return $original->manipulateExtension(
            $newExtension,
            function (AssetStore $store, string $filename, string $hash, string $variant) use ($original) {
                $backend = $original->getImageBackend();
                $config = ['conflict' => AssetStore::CONFLICT_USE_EXISTING];
                $tuple = $backend->writeToStore($store, $filename, $hash, $variant, $config);
                return [$tuple, $backend];
            }
        );
    }
}
