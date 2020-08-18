<?php
namespace Chestnut\Dashboard\Uploader;

use Illuminate\Http\Request;

class Uploader
{
    protected $directory;

    public static function registerRoute($router)
    {
        $router->prefix('api/')
            ->middleware('api', 'auth:api')
            ->post("upload_files", static::class . "@" . "fileUpload");
    }

    public function fileUpload(Request $request)
    {
        $files = $request->files;

        $response = [];

        foreach ($files as $name => $file) {
            $response[$name] = '/storage' . substr($file->store(config("chestnut.dashboard.upload_storage")), 6);
        }

        return $response;
    }
}
