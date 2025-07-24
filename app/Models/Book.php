<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = ['title', 'author', 'file_path', 'cover_path'];

    public function pages()
    {
        return $this->hasMany(BookPage::class)->orderBy('page_number');
    }
}
