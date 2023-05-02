<?php

namespace App\API\V1\Controllers;

use App\Models\Article;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;
use Pion\Laravel\ChunkUpload\Exceptions\UploadMissingFileException;
use Pion\Laravel\ChunkUpload\Handler\AbstractHandler;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;

class UploadController extends BaseController
{

    /**
     * @OA\Post(
     *      path="/v1/upload",
     *      operationId="uploadMedia",
     *      tags={"Media"},
     *      summary="Upload new media",
     *      description="Upload new media",
     *      security={{"passport":{}}},
     *
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  type="object",
     *                  @OA\Property(
     *                      property="file",
     *                      description="File",
     *                      type="file"
     *                   ),
     *                  @OA\Property(
     *                      property="article_id",
     *                      description="Article Id",
     *                      type="text"
     *                   ),
     *               ),
     *           ),
     *      ),
     *      @OA\Response(response=200,description="successful operation",
     *          @OA\MediaType(mediaType="application/json")
     *      ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     * Handles the file upload
     *
     * @param FileReceiver $receiver
     * @return JsonResponse
     *
     * @throws UploadMissingFileException
     */
    public function upload(FileReceiver $receiver)
    {

        // check if the upload is success, throw exception or return response you need
        if ($receiver->isUploaded() === false) {
            throw new UploadMissingFileException();
        }

        // receive the file
        $save = $receiver->receive();

        if ($save->isFinished()) {
            // save the file and return any response you need, current example uses `move` function. If you are
            // not using move, you need to manually delete the file by unlink($save->getFile()->getPathname())
            return $this->saveFile($save->getFile());
        }

        // we are in chunk mode, lets send the current progress
        /** @var AbstractHandler $handler */
        $handler = $save->handler();

        return response()->json([
            "done" => $handler->getPercentageDone(),
            'status' => true,
        ]);
    }

    private function saveFile(UploadedFile $file)
    {
        try {
            $request = app('request');
            $params = Arr::only($request->all(), ['article_id']);
            $user = Auth::user();

            $article = Article::where('article_id', $params['article_id'])
                ->where('user_id', $user->getAuthIdentifier())->first();
            if (!$article) {
                $article = Article::create([
                    'article_id' => $params['article_id'],
                    'user_id' => $user->getAuthIdentifier(),
                    'user_email' => $user->email ?? '',
                    'user_name' => $user->name ?? '',
                ]);
            }

            $collectionName = (in_array($file->getMimeType(), ['image/webp', 'image/svg+xml', 'image/jpeg', 'image/gif', 'image/png'])) ? 'images' : 'default';
            if ($file->getMimeType() == 'application/pdf') {
                $collectionName = 'pdf';
            }
            if (in_array($file->getMimeType(), ['video/webm', 'video/mpeg', 'video/mp4', 'video/quicktime'])) {
                $collectionName = 'video';
            }

            $media = $article->addMedia($file->getPathname())
                ->toMediaCollection($collectionName);

            $baseUrlRoute = $collectionName == 'images'? 'image.resize' : 'media';
            if ($collectionName == 'default') {
                return response()->json([
                    'uuid' => $media->uuid,
                    'base_url' => route($baseUrlRoute, ['id' => $media->uuid]),
                    'collection' => $collectionName
                ]);
            } else {
                return response()->json([
                    'thumb' => $media->disk == 's3' ? $media->getTemporaryUrl(Carbon::now()->addDays(7), 'thumb') : $media->getUrl('thumb'),
                    'uuid' => $media->uuid,
                    'base_url' => route($baseUrlRoute, ['id' => $media->uuid]),
                    'collection' => $collectionName,
                ]);
            }
        } catch (FileCannotBeAdded $exception){
            return response()->json([
                'error'=> true,
                'message' => $exception->getMessage()
            ]);
        }
    }
}
