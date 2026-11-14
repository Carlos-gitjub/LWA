<?php

namespace App\Infrastructure\Library\Models;

use Illuminate\Database\Eloquent\Model;

class BookPage extends Model
{
    protected $fillable = ['book_id', 'page_number', 'content'];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
