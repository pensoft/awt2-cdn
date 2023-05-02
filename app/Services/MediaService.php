<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MediaService
{
    //Original request
    private Request $originalRequest;

    private Media $media;

    public function createProcessor(Request $request, $id): static
    {
        try {
            $this->originalRequest = $request;
            $media = Media::where('uuid', $id)->firstOrFail();
            $this->media = $media;

            return $this;
        } catch (ModelNotFoundException $exception){
            throw new NotFoundHttpException('Media missing');
        } catch (\Exception $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        }
    }

    public function toMedia(): \Symfony\Component\HttpFoundation\StreamedResponse|\Symfony\Component\HttpFoundation\Response
    {
        return $this->media->toInlineResponse($this->originalRequest);
    }
}
