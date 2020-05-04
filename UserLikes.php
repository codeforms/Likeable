<?php
namespace CodeForms\Repositories\Like;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
/**
 * Like trait for user model
 * 
 * @package CodeForms\Repositories\Like
 */
trait UserLikes
{
	/**
	 * @example $user->likes()
	 * 
	 * @return object|null
	 */
	public function likes()
	{
		return self::handleLikes('like');
	}

	/**
	 * @example $user->dislikes()
	 * 
	 * @return object|null
	 */
	public function dislikes()
	{
		return self::handleLikes('dislike');
	}

	/**
	 * @param string $type (like, dislike)
	 * 
	 * @return object|null
	 */
	private function handleLikes(string $type)
	{
		$likes = $this->userLikes()->where('response', $type)->get();

		if(count($likes) > 0)
			$collection = new Collection;
			foreach($likes as $like)
				$collection->push(app($like->likeable_type)->find($like->likeable_id));

			return $collection;

		return null;
	}

	/**
	 * @example $user->deleteLikes()
	 */
	public function deleteLikes()
	{
		return $this->userLikes()->delete();
	}

	/**
     * @return Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function userLikes()
    {
        return $this->hasMany(Like::class, 'user_id', 'id');
    }
}