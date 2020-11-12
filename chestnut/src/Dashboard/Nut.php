<?php

namespace Chestnut\Dashboard;

use Illuminate\Foundation\Application;

/**
 * Laravel admin resource
 *
 * @author Leon Zhang <33543015@qq.com>
 */
class Nut extends Controller
{
    /**
     * Display nut in menu
     *
     * @var boolean
     */
    protected $showInMenu = true;

    /**
     * Get model with relations
     *
     * @var array
     */
    protected $with;

    /**
     * Nut's front end components
     *
     * @var array
     */
    protected $components = [];

    /**
     * Nut's front end actions
     *
     * @var array
     */
    protected $actions = [];

    /**
     * Nut's statistic
     *
     * @var array
     */
    public $statistic = [];

    /**
     * Nut's icon
     *
     * @var string
     */
    protected $icon;

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
            ->middleware('api', 'auth:chestnut')
            ->group(
                function ($router) {
                    $this->registerDefaultRoutes($router);

                    $this->registerRoutes($router);
                }
            );

        $this->registerDefaultComponents();
        $this->registerComponents();
        $this->registerDefaultActions();
    }

    /**
     * Register route
     *
     * @param Illuminate\Routing\Router $router
     * @return void
     */
    public function registerRoutes($router)
    {
    }

    /**
     * Register resource routes
     *
     * @param Illuminate\Routing\Router $router
     * @return void
     */
    private function registerDefaultRoutes($router)
    {
        $router->get('', $this->getAction("getTable"));
        $router->get('/{id}', $this->getAction("getEdit"))->where('id', '[0-9]+');
        $router->put('/{id}', $this->getAction('putEdit'))->where('id', '[0-9]+');
        $router->put('/restore', $this->getAction('putRestore'))->where('id', '[0-9]+');
        $router->post('', $this->getAction('postCreate'));
        $router->delete('', $this->getAction('destroy'));
        $router->get('/columns', $this->getAction("getColumns"));
    }

    /**
     * Register Nut front end components
     *
     * @return void
     */
    public function registerComponents()
    {

    }

    /**
     * Register Nut default front end components
     *
     * @return void
     */
    private function registerDefaultComponents()
    {
        $name = $this->getName();

        $this->registerComponent("Contents", [
            'name'       => $name,
            'icon'       => $this->icon,
            'group'      => $this->group(),
            'showInMenu' => $this->showInMenu,
        ]);

        $this->registerComponent("Content", [
            ["path" => $name . "/create", "name" => $name . ".create"],
            ["path" => $name . "/:id/edit", "name" => $name . ".edit"],
        ]);
    }

    /**
     * Register component to nut front end
     *
     * @param string $component Component name
     * @param array $options Component options
     * @return void
     */
    public function registerComponent(string $component, array $options)
    {
        $this->components[$component] = $options;
    }

    /**
     * Register action fo front end
     *
     * @param string $action
     * @return void
     */
    public function registerAction(string $action)
    {
        array_push($this->actions, $this->getName() . "." . $action);
    }

    /**
     * Register nut actions for front end
     *
     * @param array $actions
     * @return void
     */
    public function registerActions(array $actions)
    {
    }

    /**
     * Register default actions
     *
     * @return void
     */
    private function registerDefaultActions()
    {
        $actions = ['delete'];

        if ($this->isSoftDelete()) {
            array_push($actions, 'restore');
        }

        foreach ($actions as $action) {
            $this->registerAction($action);
        }
    }

    /**
     * Nut's Group
     *
     * @return void
     */
    public function group()
    {
        return null;
    }

    /**
     * Get nut front end components
     *
     * @return array
     */
    public function getComponents(): array
    {
        return $this->components;
    }

    /**
     * Get nut front end actions
     *
     * @return array
     */
    public function getActions(): array
    {
        return $this->actions;
    }
}
