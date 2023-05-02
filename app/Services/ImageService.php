<?php

namespace App\Services;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Spatie\Image\Image;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\MediaCollections\Filesystem;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\TemporaryDirectory;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ImageService
{
    //Original request
    private Request $originalRequest;

    private Image $media;

    private Media $record;
    /**
     * @var string[]
     */
    private array $params;

    private string $temporaryImageFile;

    /**
     * @param Request $request
     * @param $id
     * @param string $any
     * @return $this
     */
    public function createProcessor(Request $request, $id, string|null $any = ''): static
    {
        try {
            $this->originalRequest = $request;
            $media = Media::where('uuid', $id)->firstOrFail();
            $this->record = $media;

            $this->temporaryImageFile = $this->moveToTmp($media);

            $this->media = Image::load($this->temporaryImageFile);
            [$action, $params, $bg] = array_pad(explode('/', $any), 3, null);
            if ($bg) {
                $this->media->background($bg);
            }
            [$width, $height] = array_pad(explode('x', $params), 2, null);
            if ($width == '') $width = null;

            [$orgWidth, $orgHeight] = getimagesize($this->temporaryImageFile);

            $this->params = [
                'action' => $action ?: 'preview',
                'width' => $width ?: (($height ?? $orgHeight) / $orgHeight * $orgWidth),
                'height' => $height ?: (($width ?? $orgWidth) / $orgWidth * $orgHeight),
            ];

            return $this;
        } catch (ModelNotFoundException $exception){
            throw new NotFoundHttpException('Media missing');
        } catch (\Exception $exception) {
            throw new NotFoundHttpException('Used parameters are wrong');
        }
    }

    public function toMedia(): BinaryFileResponse
    {
        if (method_exists($this, $this->params['action'])) {
            $key = hash('sha256', implode('|', [...$this->params, $this->record->uuid]));
            $this->{$this->params['action']}($this->params['width'], $this->params['height'])->save($key);
            return response()->file($key)->deleteFileAfterSend();
        }
        throw new NotFoundHttpException('Used parameters are wrong');
    }

    private function resize($width, $height): Image
    {
        return $this->media->fit(Manipulations::FIT_STRETCH, $width, $height);
    }

    private function preview($width, $height): Image
    {
        return $this->media->fit(Manipulations::FIT_FILL_MAX, $width, $height);
    }

    private function fit_crop($width, $height): Image
    {
        return $this->media->fit(Manipulations::FIT_CROP, $width, $height);
    }

    private function crop($width, $height): Image
    {
        [$orgWidth, $orgHeight] = getimagesize($this->temporaryImageFile);
        $x = $orgWidth / 2 - $width/2;
        $y = $orgHeight / 2 - $height/2;
        return $this->media->manualCrop($width, $height, $x, $y);
    }

    /**
     * @param Media $media
     * @return string
     */
    private function moveToTmp(Media $media): string
    {
        $temporaryDirectory = TemporaryDirectory::create();
        $temporaryImageFile = $temporaryDirectory->path('/') . $media->file_name;
        /** @var \Spatie\MediaLibrary\MediaCollections\Filesystem $filesystem */
        $filesystem = app(Filesystem::class);
        $filesystem->copyFromMediaLibrary($media, $temporaryImageFile);

        return $temporaryImageFile;
    }
}
