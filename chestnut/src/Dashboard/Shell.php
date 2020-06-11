<?php

namespace Chestnut\Dashboard;

use Arr;
use BadMethodCallException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use Str;

/**
 * Laravel administrator
 */
class Shell
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
        $this->app = $app;
        $this->container = collect([]);
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
        $nuts = Cache::get('nutsClassCached');

        if (Cache::get('nutsLastModified') != File::lastModified($directory)) {
            $nuts = $this->getNutsInDirectory($directory);

            Cache::forever('nutsLastModified', File::lastModified($directory));
            Cache::forever('nutsClassCached', $nuts);
        }

        $this->nuts($nuts);
    }

    public function getNutsInDirectory($directory)
    {
        $files = File::glob($directory . "/*.php");

        $nuts = [];
        foreach ($files as $file) {
            $fp = fopen($file, 'r');
            $class = $namespace = $buffer = '';
            $i = 0;
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

    public function modules()
    {
        $user = auth()->user();

        $modules = $this->container->map(function ($nut) use ($user) {
            if (!$user->can("{$nut->getName()}.*")) {
                return false;
            }

            return [$nut->getName() => $nut->getVueRoute($user)];
        });

        return Arr::collapse($modules);
    }

    public function sidebars()
    {
        $user = auth()->user();

        $modules = $this->container->map(function ($nut) use ($user) {
            if (!$user->can("{$nut->getName()}.*")) {
                return false;
            }

            return $nut->getSideBar();
        });

        return $modules;

    }

    public function __call($name, $arguments)
    {
        if (class_exists("Chestnut\\Dashboard\\Fields\\" . $name)) {
            $class = new ReflectionClass("Chestnut\\Dashboard\\Fields\\" . $name);

            return $class->newInstanceArgs($arguments);
        }

        throw new BadMethodCallException("Method [$name] not found in [" . get_class($this) . "]");
    }
}
