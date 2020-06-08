<?php

namespace Chestnut\Dashboard;

use Illuminate\Foundation\Application;
use Str;

/**
 * Laravel admin resource
 *
 * @author Leon Zhang <33543015@qq.com>
 */
class Nut extends Controller
{
    /**
     * Resource name
     */
    private $_name;

    /**
     * Resource eloquent model
     */
    protected $model;

    /**
     * Resource eloquent model namespace
     */
    protected $namespace = "App";

    /**
     * Configs
     */
    protected $config;

    protected $displayInSideBar = true;

    protected $actions = [];

    protected $rowActions = [];

    protected $with;

    /**
     * Resource Construct
     */
    public function __construct()
    {
        $this->model = $this->_getModelName();
        $this->_name = strtolower($this->model);
    }

    /**
     * Resource Register
     *
     * @param Illuminate\Foundation\Application $app Laravel Application
     *
     * @return void
     */
    public function register(Application $app)
    {
        $app->router->prefix('api/' . $this->getName())
            ->middleware('api', 'auth:api')
            ->group(
                function ($router) {
                    $this->registerDefaultRoutes($router);

                    if (method_exists($this, 'registerRoutes')) {
                        $this->registerRoutes($router);
                    }
                }
            );

        $this->config = $app->config->get('chestnut.dashboard');
    }

    /**
     * Register resource routes
     *
     * @return void
     */
    public function registerDefaultRoutes($router)
    {
        $router->get('', $this->getAction("getTable"));
        $router->get('/{id}', $this->getAction("getDetail"))->where('id', '[0-9]+');
        $router->put('/{id}', $this->getAction('putEdit'))->where('id', '[0-9]+');
        $router->post('', $this->getAction('postCreate'));
        $router->get('/create', $this->getAction('getCreate'));
        $router->delete('', $this->getAction('destroy'));
    }

    /**
     * Get resource name
     *
     * @return String
     */
    private function _getModelName()
    {
        $name = explode("\\", get_class($this));

        return array_pop($name);
    }

    public function getVueRoute($user)
    {
        $name = Str::plural($this->getName());

        $module = [
            [
                'path' => '',
                'name' => ucfirst($name),
                'props' => [
                    'api' => $this->getName(),
                    'text' => __('chestnut::chestnut.' . $this->getName()),
                    'actions' => $this->actions,
                    'rowActions' => $this->rowActions,
                    'breadcrumbs' => [
                        ["text" => __('chestnut::chestnut.index'), "to" => ["name" => "Dashboard"]],
                        ["text" => __('chestnut::chestnut.' . $this->getName())],
                    ],
                ],
            ],
        ];

        foreach (['create', 'edit', 'detail'] as $type) {
            if (!$user->can($this->getName() . $type)) {
                continue;
            }

            array_push($module, [
                'path' => $type == "create" ? "$type" : ":id/$type",
                'name' => ucfirst($name) . "." . $type,
                'props' => [
                    'api' => $this->getName(),
                    'breadcrumbs' => [
                        ["text" => __('chestnut::chestnut.index'), "to" => ["name" => "Dashboard"]],
                        ["text" => __('chestnut::chestnut.' . $this->getName()), "to" => ["name" => ucfirst($name)]],
                        ["text" => __("chestnut::chestnut.{$type}", ['name' => __("chestnut::chestnut.{$this->getName()}")])],
                    ],

                ],
            ]);
        }

        return $module;
    }

    public function getSideBar()
    {
        $name = Str::plural($this->getName());

        $sideBar = ["name" => __('chestnut::chestnut.' . $this->getName()), 'to' => ["name" => ucfirst($name)]];

        if (\method_exists($this, 'group')) {
            $sideBar['group'] = $this->group();
        }

        return $sideBar;
    }

    /**
     * Get Resource name
     *
     * @return String
     */
    public function getName()
    {
        return $this->_name;
    }

    public function getModel()
    {
        return $this->namespace . '\\' . $this->model;
    }
}
