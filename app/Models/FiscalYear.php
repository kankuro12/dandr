<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiscalYear extends Model
{
    use HasFactory;

    public static function current(int $date):FiscalYear{
        return self::where('startdate','<=',$date)->where('enddate','>=',$date)->first();
        
    }
}
