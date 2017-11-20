<?php

namespace Omadonex\Vaac\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Omadonex\Vaac\VaacService;

class VaacUserVerify extends Model
{
    use SoftDeletes;

    protected $dates = ['used_at'];

    /**
     * Query scope for retrieving today verifies
     *
     * @param $query
     * @return mixed
     */
    public function scopeToday($query)
    {
        return $query->where('created_at', '>=', Carbon::today());
    }

    /**
     * Query scope for retrieving only E-mail verifies
     *
     * @param $query
     * @return mixed
     */
    public function scopeEmail($query)
    {
        return $query->where('method', VaacService::METHOD_EMAIL);
    }

    /**
     * Query scope for retrieving only Mobile verifies
     *
     * @param $query
     * @return mixed
     */
    public function scopePhone($query)
    {
        return $query->where('method', VaacService::METHOD_PHONE);
    }

    /**
     * Query scope for retrieving verifies by method
     *
     * @param $query
     * @param $method
     * @return mixed
     */
    public function scopebyMethod($query, $method)
    {
        return $query->where('method', $method);
    }

    /**
     * Query scope for retrieving verifies by token
     *
     * @param $query
     * @param $token
     * @return mixed
     */
    public function scopeByToken($query, $token)
    {
        return $query->where('token', $token);
    }

    /**
     * Marks current verify as used
     *
     * @return void
     */
    public function markUsed()
    {
        $this->used_at = Carbon::now();
        $this->save();
    }
}
