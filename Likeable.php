<?php
namespace CodeForms\Repositories\Like;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
/**
 * @package CodeForms\Repositories\Like
 */
trait Likeable
{
    /**
     * @return Illuminate\Database\Eloquent\Model
     */
    public static function bootLikeable()
    {
        static::deleted(function (self $model) {
            $model->deleteAllLikes();
        });
    }

    /**
     * @param string|array $type (like, dislike)
     * @param integer $user_id
     * 
     * @example $post->hasLike() (like + dislike)
     * @example $post->hasLike('like') (only likes)
     * @example $post->hasLike('dislike') (only dislikes)
     * 
     * @return boolean
     */
    public function hasLike($type = null, $user_id = null): bool
    {
    	return $this->likes()->when(!is_null($type), function($query) use($type) {
            return $query->whereIn('response', (array)$type);
        })->when(!is_null($user_id), function($query) use($user_id) {
            return $query->where('user_id', $user_id);
        })->count();
    }

    /**
     * @example $post->like()
     * 
     * @return boolean
     */
    public function like(): bool
    {
    	return self::setLikes('like', auth()->user()->id);
    }

    /**
     * @example $post->dislike()
     * 
     * @return boolean
     */
    public function dislike(): bool
    {
    	return self::setLikes('dislike', auth()->user()->id);
    }

    /**
     * @example $post->undoLike()
     * 
     * @return boolean
     */
    public function undoLike(): bool
    {
        return self::handleUndoLikes('like');
    }

    /**
     * @example $post->undoDislike()
     * 
     * @return boolean
     */
    public function undoDislike(): bool
    {
        return self::handleUndoLikes('dislike');
    }

    /**
     * @example $post->likePercentage()
     * 
     * @return float
     */
    public function likePercentage()
    {
        $total = self::countLikes('like') + self::countLikes('dislike');

        return round((self::countLikes('like') / $total) * 100);
    }

    /**
     * @param string $type
     * @example $post->countLikes('like')
     * @example $post->countLikes('dislike')
     * 
     * @return int
     */
    public function countLikes($type)
    {
        return $this->likeCounter()->avg($type);
    }

    /**
     * @example $post->deleteAllLikes() 
     */
    public function deleteAllLikes()
    {
    	$this->likes()->delete();
		$this->likeCounter()->delete();
    }

    /**
     * @param string $type
     * @param integer $user_id
     * 
     * @return boolean
     */
    private function setLikes(string $type, int $user_id): bool
    {
        if(self::hasLike($type, $user_id))
            return false;

        switch ($type) {
            case 'like':
                return self::handleLikes('dislike', 'like', $user_id);
                break;
            case 'dislike':
                return self::handleLikes('like', 'dislike', $user_id);
                break;
        }
    }

    /**
     * @param string $beta
     * @param string $alpha
     * @param int $user_id
     * 
     * @return boolean
     */
    private function handleLikes($beta, $alpha, $user_id): bool
    {
        if(self::hasLike($beta, $user_id) or self::hasLike($alpha, $user_id))
            return self::updateCurrentLike($beta, $alpha);
            
        return self::createNewLike($alpha);
    }

    /**
     * 
     */
    private function updateCurrentLike($beta, $alpha)
    {
        $this->likes()->update(['response' => $alpha]);

        if(self::countLikes($beta) > 0) 
            $this->likeCounter()->decrement($beta);

        if(is_null(self::countLikes($alpha))) {
            $this->likeCounter()->create()->increment($alpha);
            return true;
        }

        $this->likeCounter()->increment($alpha);
        return true;
    }

    /**
     * @param string $alpha 
     * 
     * @return boolean
     */
    private function createNewLike($alpha): bool
    {
        $this->likes()->create(['response' => $alpha]);

        if(is_null(self::countLikes($alpha))) {
            $this->likeCounter()->create()->increment($alpha);
            return true;
        }

        $this->likeCounter()->increment($alpha);
        return true;
    }

    /**
     * @param string $type
     * 
     * @return boolean
     */
    private function handleUndoLikes($type): bool
    {
        if(self::hasLike($type, auth()->user()->id)) {
            $this->likeCounter()->decrement($type);
            $this->likes()->where('user_id', auth()->user()->id)->delete();

            return true;
        }

        return false;
    }

    /**
     * @return Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function likeCounter()
    {
        return $this->morphOne(LikeCounter::class, 'likeable');
    }

    /**
     * @return Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }
}