<?php

namespace app\models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class BetItem
 * @package app\models
 *
 * @property integer $id
 * @property integer $bet_id
 * @property integer $minute
 * @property string $code
 * @property integer $count
 */
class BetItem extends Model
{
    protected $table = 'bet_item';

    protected $fillable = ['bet_id', 'minute', 'code', 'count'];

    public $timestamps = false;
}