# Likeable
Laravel tabanlı yapılar için 'beğen' / 'beğenme' işlevselliğine olanak tanıyan basit ve esnek trait yapısı.

[![GitHub license](https://img.shields.io/github/license/codeforms/Likeable)](https://github.com/codeforms/Likeable/blob/master/LICENSE)
![GitHub release (latest by date)](https://img.shields.io/github/v/release/codeforms/Likeable)
[![stable](http://badges.github.io/stability-badges/dist/stable.svg)](https://github.com/codeforms/Likeable/releases)

## Kurulum
* Migration dosyasını kullanarak veri tabanı için gerekli tabloları oluşturun;
``` php artisan migrate```
* Likeable trait dosyasını, kullanmak istediğiniz model dosyalarına ekleyiniz;
```php
namespace App\Post;

use CodeForms\Repositories\Like\Likeable;
use Illuminate\Database\Eloquent\Model;
/**
 * 
 */
class Post extends Model 
{
	use Likeable;
}
```

## Kullanım
```php
$post = Post::find(1);

$post->hasLike(); // like + dislike
$post->hasLike('like'); // sadece like'ı sorgular
$post->hasLike('dislike'); // sadece dislike'ı sorgular

$post->like(); // $post'u 'like' olarak kaydeder
$post->dislike(); // $post'u 'dislike' olarak kaydeder
$post->unLike(); // like'ı geri alır
$post->unDislike(); // dislike'ı geri alır

$post->likePercentage(); // $post'un beğenilme oranını yüzde olarak verir

$post->countLikes(); // $post'un like + dislike toplam sayısı
$post->countLikes('like'); // $post'un sadece 'like' sayısı
$post->countLikes('dislike'); // $post'un sadece 'dislike' sayısı

$post->deleteAllLikes(); // $post'a ait tüm like ve dislike kayıtlarını siler
``` 
---
* (Tercihen) UserLikes trait dosyasını User model'a ekleyin; UserLikes trait dosyası, kullanıcıların like ve dislike kayıtlarını object olarak almayı sağlar.
```php
namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use CodeForms\Repositories\Like\UserLikes;

class User extends Authenticatable
{
    use Notifiable, UserLikes;
```
#### UserLikes kullanımı
```php
$user = User::find(1);

$user->likes(); // bir kullanıcının beğendiği tüm model kaynakları
$user->dislikes(); // bir kullanıcının beğenmediği tüm model kaynakları
``` 