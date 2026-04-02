<?php

namespace App\Repositories\Eloquent;

use App\Models\Shop;
use App\Repositories\Interfaces\ShopRepositoryInterface;
// use Illuminate\Database\Eloquent\Collection;
// use Illuminate\Database\Eloquent\Model;

class ShopRepository extends BaseRepository implements ShopRepositoryInterface
{
    public function __construct(Shop $model)
    {
        parent::__construct($model);
    }
}
