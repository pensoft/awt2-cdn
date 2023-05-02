<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Article extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'article_id',
        'user_id',
        'user_email',
        'user_name',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->useDisk(config('filesystems.storage_driver'));

        $this->addMediaCollection('pdf')
            ->useDisk(config('filesystems.storage_driver'));

        $this->addMediaCollection('video')
            ->useDisk(config('filesystems.storage_driver'));
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->nonQueued()
            ->width(350)
            ->height(350)
            ->pdfPageNumber(1)
            ->extractVideoFrameAtSecond(20);
    }

    public function getLastMedia($collectionName = 'images'): Media
    {
        return $this->getMedia($collectionName)->last();
    }
}
