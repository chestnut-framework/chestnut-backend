## 关于栗子框架

栗子框架基于 Laravel 进行开发，是一个后台资源管理快速开发框架，提供方便的命令行指令创建资源管理。

## 开发指南

### 快速启动

1. 安装依赖

    composer install

2. 初始化环境配置

-   Linux / Mac OS

    cp .env.example .env

-   Windows / Linux / Mac OS

复制.env.example 并将.example 后缀去掉

3. 创建密钥

    php artisan key:generate

### 修改默认管理员配置

修改根目录下 /database/seeds/UserSeeder.php

    $user->info()->create([
            'nickname' => '管理员昵称',
            'avatar' => '',
        ]);

    $role = Role::create(['name' => '超级管理员角色名称']);

### 创建资源管理

    php artisan make:nut [资源名称] [-a/m/f/mi] [--force]

    -a 创建资源的同时创建 Model Factory migrate
    -M 创建资源的同时创建 Model
    -f 创建资源的同时创建 Factory
    -m 创建资源的同时创建 migrate

    --force 创建资源，即使资源已经存在仍旧创建

## License

The Chestnut framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
