<?php

namespace app\models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Bet
 * @package app\models
 *
 * @property integer $id
 * @property integer $number
 * @property string $win_code
 * @property string $date
 */
class Bet extends Model
{
    protected $table = 'bet';

    protected $fillable = ['number', 'win_code', 'date'];

    public $timestamps = false;
}