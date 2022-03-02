<?php

namespace App\Http\Controllers\Crawler;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Link;
use App\Models\Document;

class SaveData extends Controller
{
    public function saveUrl($url)
    {
        $link = new Link();

        $link->url = $url;

        $link->save();
    }

    public function saveDocument($data)
    {
        $doc = new Document();

        $doc->title         = $data['title'];
        $doc->content       = $data['content'];
        $doc->author        = $data['author'];
        $doc->download_link = $data['download_link'];

        $doc->save();
    }

    public function updateStage($url, $stage)
    {
        $target = Link::where('url', '=', $url);
        $target->update(['visited' => $stage]);
    }
}
