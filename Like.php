<?php
namespace CodeForms\Repositories\Like;

use Illuminate\Database\Eloquent\Model;
/**
 *  @package CodeForms\Repositories\Like
 */
class Like extends Model
{
	/**
	 * @var string
	 */
	protected $table = 'likes';

	/**
	 * @var array
	 */
	protected $fillable = ['user_id', 'response'];

	/**
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($like) {
            $like->user_id = auth()->user()->id;
        });
    }

    /**
     * 
     */
	public function likeable()
	{
		return $this->morphTo();
	}
}