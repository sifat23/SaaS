<?php

namespace App\Handlers;

use Illuminate\Support\Str;

class SlugHandler
{
    public static final function generate(
        mixed $model,
        string $text
    ) {
        $slug = Str::slug($text);
        $originalSlug = $slug;
        $count = 1;

        while ($model::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }
}
