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
     * @example $post->unLike()
     * 
     * @return boolean
     */
    public function unLike(): bool
    {
        return self::handleUndoLikes('like');
    }

    /**
     * @example $post->unDislike()
     * 
     * @return boolean
     */
    public function unDislike(): bool
    {
        return self::handleUndoLikes('dislike');
    }

    /**
     * @example $post->likePercentage()
     * 
     * @return float
     */
    public function likePercentage(): float
    {
        $total = self::countLikes('like') + self::countLikes('dislike');
        
        if($total != 0)
            return round((self::countLikes('like') / $total) * 100);

        return $total;        
    }

    /**
     * @param string $type
     * @example $post->countLikes('like')
     * @example $post->countLikes('dislike')
     * 
     * @return int
     */
    public function countLikes($type): int
    {
        return (int)$this->likeCounter()->avg($type);
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
        self::decrementLike($beta);

        return self::incrementLike($alpha);
    }

    /**
     * @param string $alpha 
     * 
     * @return boolean
     */
    private function createNewLike($alpha): bool
    {
        $this->likes()->create(['response' => $alpha]);

        return self::incrementLike($alpha);
    }

    /**
     * @param $type
     * 
     * @return boolean
     */
    private function incrementLike($type): bool
    {
        if(self::countLikes($type) > 0) {
            $this->likeCounter()->create()->increment($type);
            return true;
        }

        $this->likeCounter()->increment($type);
        return true;
    }

    /**
     * @param $type
     * 
     * @return boolean
     */
    private function decrementLike($type): bool
    {
        if(self::countLikes($type) > 0) {
            return $this->likeCounter()->decrement($type);
        }
        
        return false;
    }

    /**
     * @param string $type
     * 
     * @return boolean
     */
    private function handleUndoLikes($type): bool
    {
        if(self::hasLike($type, auth()->user()->id)) {
            self::decrementLike($type);
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