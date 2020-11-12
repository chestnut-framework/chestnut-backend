<?php

namespace App\Nuts;

use Chestnut\Auth\Models\Manager;
use Chestnut\Dashboard\Nut;
use Chestnut\Facades\Shell;

class Article extends Nut
{
    protected $with = ['publisher'];

    protected $search = ['title'];

    public $statistic = ['new' => ['day', 'month', 'year']];

    protected $icon = "book-open";

    public function fields(): array
    {
        return [
            Shell::ID(),
            Shell::Select("publisher_id", "作者")
                ->options(Manager::all(), 'name')->filter(),
            Shell::Image('cover_image', '封面')->hideInTable(),
            Shell::Text("title", "标题")
                ->rules('required'),
            Shell::Text("summary", "摘要")->hideInTable(),
            Shell::Text("author", "原作者")->readonly()->sortable()->hideInCreate(),
            Shell::Text("from", "来源")->readonly()->sortable()->hideInCreate(),
            Shell::Editor("content", "内容")->hideInTable(),
            Shell::CreatedAt("发布时间"),
        ];
    }
}
