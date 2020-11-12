<?php

namespace Chestnut\Dashboard;

use BadMethodCallException;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use JsonSerializable;
use ReflectionClass;

/**
 * Chestnut resource manager
 *
 * @method static Chestnut\Dashboard\Fields\Avatar Avatar($prop, $label)
 * @method static Chestnut\Dashboard\Fields\Checkbox Checkbox($prop, $label)
 * @method static Chestnut\Dashboard\Fields\CreatedAt CreatedAt($label)
 * @method static Chestnut\Dashboard\Fields\Datetime Datetime($prop, $label)
 * @method static Chestnut\Dashboard\Fields\Editor Editor($prop, $label)
 * @method static Chestnut\Dashboard\Fields\ID ID($label)
 * @method static Chestnut\Dashboard\Fields\Image Image($prop, $label)
 * @method static Chestnut\Dashboard\Fields\Password Password($prop, $label)
 * @method static Chestnut\Dashboard\Fields\Select Select($prop, $label)
 * @method static Chestnut\Dashboard\Fields\SoftDelete SoftDelete($label)
 * @method static Chestnut\Dashboard\Fields\Text Text($prop, $label)
 */
class Shell implements Jsonable, JsonSerializable
{
    public $app;
    public $container;

    /**
     * Laravel admin constructor
     *
     * @param {Illuminate\Foundation\Application} $app Laravel Application
     */
    public function __construct(Application $app)
    {
        $this->app       = $app;
        $this->container = collect([]);

        $this->boot();
    }

    /**
     * Laravel chestnut shell bootstrap
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Single resource register
     *
     * @param {Resource} $resource Laravel admin resource
     *
     * @return void
     */
    public function nut(Nut $nut)
    {
        $this->container->add($nut);

        $nut->register($this->app);
    }

    /**
     * Resources register
     *
     * @param {Array} $nuts array of resources
     *
     * @return void
     */
    public function nuts(?array $nuts)
    {
        foreach ($nuts as $nut) {
            $this->nut(is_string($nut) ? new $nut() : $nut);
        }
    }

    public function getNuts()
    {
        return $this->container;
    }

    /**
     * Register Nut resources in given directory
     *
     * @param String $directory Nuts directory
     *
     * @return void
     */
    public function nutsIn(string $directory = 'app/Nuts')
    {
        // $nuts = Cache::get('nutsClassCached');

        // if (Cache::get('nutsLastModified') != File::lastModified($directory)) {
        //     $nuts = $this->getNutsInDirectory($directory);

        //     Cache::forever('nutsLastModified', File::lastModified($directory));
        //     Cache::forever('nutsClassCached', $nuts);
        // }
        $nuts = $this->getNutsInDirectory($directory);

        $this->nuts($nuts);
    }

    public function getNutsInDirectory($directory)
    {
        $files = File::glob($directory . "/*.php");

        $nuts = [];
        foreach ($files as $file) {
            $fp    = fopen($file, 'r');
            $class = $namespace = $buffer = '';
            $i     = 0;
            while (!$class) {
                if (feof($fp)) {
                    break;
                }

                $buffer .= fread($fp, 512);
                $tokens = token_get_all($buffer);

                if (strpos($buffer, '{') === false) {
                    continue;
                }

                for (; $i < count($tokens); $i++) {
                    if ($tokens[$i][0] === T_NAMESPACE) {
                        for ($j = $i + 1; $j < count($tokens); $j++) {
                            if ($tokens[$j][0] === T_STRING) {
                                $namespace .= '\\' . $tokens[$j][1];
                            } else if ($tokens[$j] === '{' || $tokens[$j] === ';') {
                                break;
                            }
                        }
                    }

                    if ($tokens[$i][0] === T_CLASS) {
                        for ($j = $i + 1; $j < count($tokens); $j++) {
                            if ($tokens[$j] === '{') {
                                $class = $tokens[$i + 2][1];
                            }
                        }
                    }
                }
            }

            array_push($nuts, $namespace . "\\" . $class);
        }

        return $nuts;
    }

    public function toJson($options = 0)
    {
        $json = json_encode($this->jsonSerialize(), $options);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \Exception(json_last_error_msg());
        }

        return $json;
    }

    public function jsonSerialize()
    {
        return ["nuts" => $this->getModules()];
    }

    public function __toString()
    {
        return $this->toJson();
    }

    public function getModules()
    {
        $user = Auth::user() ?? Auth::guard("chestnut")->user();

        $modules = $this->container->map(function ($nut) use ($user) {
            if (!$user->can("{$nut->getName()}.*")) {
                return false;
            }

            return [$nut->getName() => ["components" => $nut->getComponents(), "actions" => $nut->getActions(), "statistic" => $nut->statistic, "text" => $nut->getName()]];
        });

        return Arr::collapse($modules);
    }

    public function __call($name, $arguments)
    {
        if (class_exists("Chestnut\\Dashboard\\Fields\\" . $name)) {
            $class = new ReflectionClass("Chestnut\\Dashboard\\Fields\\" . $name);

            return $class->newInstanceArgs($arguments);
        }

        throw new BadMethodCallException("Method [$name] not found in [" . get_class($this) . "]");
    }

    public static function __callStatic($name, $arguments)
    {
        if (class_exists("Chestnut\\Dashboard\\Fields\\" . $name)) {
            $class = new ReflectionClass("Chestnut\\Dashboard\\Fields\\" . $name);

            return $class->newInstanceArgs($arguments);
        }

        throw new BadMethodCallException("Method [$name] not found in [" . static::class . "]");
    }
}
