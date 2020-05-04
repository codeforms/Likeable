<?php
namespace CodeForms\Repositories\Like;

use Illuminate\Database\Eloquent\Model;
/**
 *  @package CodeForms\Repositories\Like
 */
class LikeCounter extends Model
{
	/**
	 * @var string
	 */
	protected $table = 'like_counter';

	/**
	 * @var boolean
	 */
	public $timestamps = false;

	/**
	 * @var array
	 */
	protected $fillable = ['like', 'dislike'];

    /**
     * @return Illuminate\Database\Eloquent\Relations\MorphTo
     */
	public function likeable()
	{
		return $this->morphTo();
	}
}