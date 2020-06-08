<?php
namespace Chestnut\Dashboard\Uploader;

use Illuminate\Http\Request;

class Uploader
{
    protected $directory;

    public function constructor()
    {
        $this->directory = $config["upload_directory"];
    }

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
            $response[$name] = '/storage' . substr($request->file($name)->store('public/uploads'), 6);
        }

        return $response;
    }
}
