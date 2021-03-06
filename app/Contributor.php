<?php

namespace App;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Eloquent
 * @property \DateTime created_at
 * @property \DateTime updated_at
 * @property string name
 * @property string bio
 * @property string image_url
 * @property string url
 * @property string twitter
 * @property string facebook
 * @property Collection podcasts
 * @property Collection seasons
 * @property Collection episodes
 */
class Contributor extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'bio',
        'image_url',
        'url',
        'twitter',
        'facebook',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function podcasts()
    {
        return $this->morphedByMany('App\Podcast', 'contributable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function seasons()
    {
        return $this->morphedByMany('App\Season', 'contributable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function episodes()
    {
        return $this->morphedByMany('App\Episode', 'contributable');
    }
}
