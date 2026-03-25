<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class SlugHelper
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
